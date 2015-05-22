<?php
  namespace Playlyfe\Sdk;

  class PlaylyfeException extends \Exception {

    const CURL_NOT_FOUND = 0x01;
    const CURL_ERROR = 0x02;
    const CERTIFICATE_NOT_FOUND = 0x03;

    public $name;
    public $message;

    function __construct($name, $message) {
      $this->name = $name;
      $this->message = $message;
    }
  }

  class Playlyfe {
    private $version;
    private $client_id;
    private $client_secret;
    private $type;
    private $redirect_uri;
    private $code;

    private $store;
    private $load;

    private $certificate_file;

    const AUTHORIZATION_ENDPOINT = 'https://playlyfe.com/auth/token';
    const API_ENDPOINT = 'https://api.playlyfe.com/';

    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_PATCH   = 'PATCH';
    const HTTP_METHOD_PUT   = 'PUT';

    function __construct(array $params) {
      if (!extension_loaded('curl')) {
        throw new Exception('The PHP exention curl must be installed to use this library.', PlaylyfeException::CURL_NOT_FOUND);
      }
      #$certificate_file = '../lib/DigiCertGlobalRootCA';
      #if (!is_file($certificate_file)) {
      #  throw new InvalidArgumentException('The certificate file was not found', PlaylyfeException::CERTIFICATE_NOT_FOUND);
      #}
      if(array_key_exists('version', $params)) {
        $this->version = $params['version'];
      }
      else {
        $this->version = 'v2';
      }
      $this->client_id = $params['client_id'];
      $this->client_secret = $params['client_secret'];

      if(array_key_exists('type', $params)) {
        $this->type = $params['type'];
      }
      else {
        throw new PlaylyfeException('init_failed', "You must pass in a type whether 'client' for client credentials flow or 'code' for auth code flow");
      }

      if(array_key_exists('store', $params)) {
        $this->store = $params['store'];
      }
      else {
        $this->store = function($access_token) {
        };
      }

      if(array_key_exists('load', $params)) {
        $this->load = $params['load'];
      }
      if ($this->type == 'code') {
        if(array_key_exists('redirect_uri', $params)) {
          $this->redirect_uri = $params['redirect_uri'];
        }
        else {
          throw new PlaylyfeException('init_failed', "You must pass in a redirect_uri for the auth code flow");
        }
      }
    }

    private function get_access_token() {
      if($this->type == 'client') {
        $data = array(
          'client_id' => $this->client_id,
          'client_secret' => $this->client_secret,
          'grant_type' => 'client_credentials'
        );
        $access_token = $this->executeRequest('POST', self::AUTHORIZATION_ENDPOINT, null, $data);
      }
      else {
        $data = array(
          'client_id' => $this->client_id,
          'client_secret' => $this->client_secret,
          'grant_type' => 'authorization_code',
          'code' => $this->code,
          'redirect_uri' => $this->redirect_uri
        );
        $access_token = $this->executeRequest('POST', self::AUTHORIZATION_ENDPOINT, null, $data);
      }
      $expires_in = intval($access_token['expires_in']);
      $expires_at = time() + $expires_in;
      unset($access_token['expires_in']);
      $access_token['expires_at'] = $expires_at;

      $this->store->__invoke($access_token);

      if(is_null($this->load)) {
        $this->load = function() use ($access_token) {
          return $access_token;
        };
      }
    }

    private function check_token(&$query) {
      $token = null;
      if($this->load) {
        $token = $this->load->__invoke();
      }
      if(is_null($token) and $this->type == 'client') {
        $this->get_access_token();
        $token = $this->load->__invoke();
      }
      if (time() >= $token['expires_at']){
        $this->get_access_token();
        $token = $this->load->__invoke();
      }
      $query['access_token'] = $token['access_token'];
    }

    public function api($http_method = self::HTTP_METHOD_GET, $route , $query = array(), $body = array(), $raw = false) {
      $this->check_token($query);
      return $this->executeRequest($http_method, self::API_ENDPOINT.$this->version.$route, $query, $body, $raw);
    }

    public function get($route, $query = array(), $raw = false) {
      $this->check_token($query);
      return $this->executeRequest(self::HTTP_METHOD_GET, self::API_ENDPOINT.$this->version.$route, $query, null, $raw);
    }

    public function post($route, $query = array(), $body = array()) {
      $this->check_token($query);
      if(count($body) == 0) {
        $body = (object) array();
      }
      return $this->executeRequest(self::HTTP_METHOD_POST, self::API_ENDPOINT.$this->version.$route, $query, $body);
    }

    public function patch($route, $query = array(), $body = array()) {
      $this->check_token($query);
      return $this->executeRequest(self::HTTP_METHOD_PATCH, self::API_ENDPOINT.$this->version.$route, $query, $body);
    }

    public function put($route, $query = array(), $body = array()) {
      $this->check_token($query);
      return $this->executeRequest(self::HTTP_METHOD_PUT, self::API_ENDPOINT.$this->version.$route, $query, $body);
    }

    public function delete($route, $query = array()) {
      $this->check_token($query);
      return $this->executeRequest(self::HTTP_METHOD_DELETE, self::API_ENDPOINT.$this->version.$route, $query);
    }

    /**
     * Execute a request (with curl)
     *
     * @param string $url URL
     * @param array  $query Array of parameters to pass as body
     * @param array  $body Array of parameters to pass as body
     * @param string $http_method HTTP Method
     * @param array  $http_headers HTTP Headers
     * @return array
     */
    private function executeRequest($http_method = self::HTTP_METHOD_GET, $url, $query = array(), $body = array(), $raw = false)
    {
        $curl_options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CUSTOMREQUEST  => $http_method
        );

        switch($http_method) {
            case self::HTTP_METHOD_PUT:
            case self::HTTP_METHOD_PATCH:
            case self::HTTP_METHOD_POST:
                $curl_options[CURLOPT_POST] = true;
                $curl_options[CURLOPT_POSTFIELDS] = json_encode($body);
                $curl_options[CURLOPT_HTTPHEADER] = array(
                  'Content-Type: application/json',
                  'Content-Length: ' . strlen($curl_options[CURLOPT_POSTFIELDS])
                );
                /* No break */
            case self::HTTP_METHOD_DELETE:
            case self::HTTP_METHOD_GET:
                if (is_array($query)) {
                    $url .= '?' . http_build_query($query, null, '&');
                } elseif ($query) {
                    $url .= '?' . $query;
                }
                break;
            default:
                break;
        }

        $curl_options[CURLOPT_URL] = $url;
        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        //https handling
        if (!empty($certificate_file)) {
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
          curl_setopt($ch, CURLOPT_CAINFO, $certificate_file);
        } else {
          //bypass ssl verification
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if($raw == true) {
          curl_setopt($ch, CURLOPT_HEADER, 0);
          curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        }
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        if ($curl_error = curl_error($ch)) {
          throw new \Exception($curl_error, PlaylyfeException::CURL_ERROR);
        } else {
          if($raw === true){
            return $result;
          }
          else {
            $json_decode = json_decode($result, true);
          }
        }
        curl_close($ch);
        if(array_key_exists('error', $json_decode)) {
          throw new PlaylyfeException($json_decode['error'], $json_decode['error_description']);
        }
        else {
          #print_r($json_decode);
          return $json_decode;
        }
    }

    public function exchange_code($code) {
      if($code == null) {
        throw new PlaylyfeException('invalid_request', "You must pass in a code in exchange_code for the auth code flow");
      }
      $this->code = $code;
      $this->get_access_token();
    }

    public function get_login_url() {
      $query = array( 'redirect_uri' => $this->redirect_uri, 'response_type' => 'code', 'client_id' => $this->client_id);
      return "https://playlyfe.com/auth?" . http_build_query($query, null, '&');
    }

    public function get_logout_url() {
      return "";
    }

    public function upload_image($file) {
      $query = array();
      $this->check_token($query);
      $url = self::API_ENDPOINT.$this->version.'/design/images?' . http_build_query($query, null, '&');
      $ch = curl_init();
      $cfile = new \CURLFile($file, 'image/png','upload'); # for older versions use '@'.$file
      $data = array('file' => $cfile);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
      $result = curl_exec($ch);
      curl_close ($ch);
      $json_decode = json_decode($result, true);
      return $json_decode['id'];
    }

    public function read_image($image_id, $query = array()) {
      $this->check_token($query);
      $url = self::API_ENDPOINT.$this->version.'/design/images/'.$image_id.'?' . http_build_query($query, null, '&');
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
      $picture = curl_exec($ch);
      curl_close($ch);
      return $picture;
    }

    # Taken from https://github.com/firebase/php-jwt. Please watch this repo and update as necessary
    public static function createJWT(array $params) {
      if(!array_key_exists('expires', $params)) {
        $params['expires'] = 3600;
      }
      if(!array_key_exists('scopes', $params)) {
        $params['scopes'] = array();
      }
      $payload = array(
        'player_id' => $params['player_id'],
        'scopes' => $params['scopes'],
        'exp' => time() + $params['expires']
      );
      $header = array('typ' => 'JWT', 'alg' => 'HS256');
      $segments = array();
      $segments[] = Playlyfe::urlsafeB64Encode(json_encode($header));
      $segments[] = Playlyfe::urlsafeB64Encode(json_encode($payload));
      $signing_input = implode('.', $segments);

      $signature = hash_hmac('SHA256', $signing_input, $params['client_secret'], true);
      $segments[] = Playlyfe::urlsafeB64Encode($signature);
      $token = implode('.', $segments);
      $token = $params['client_id'] . ':' . $token;
      return $token;
    }

    public static function urlsafeB64Encode($input) {
      return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
  }
?>
