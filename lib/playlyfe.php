<?php

  class PlaylyfeException extends Exception {

    const CURL_NOT_FOUND          = 0x01;
    const CURL_ERROR              = 0x02;
    const INVALID_GRANT_TYPE      = 0x03;
    const CERTIFICATE_NOT_FOUND   = 0x04;
    const REQUIRE_PARAMS_AS_ARRAY = 0x05;
    const MISSING_PARAMETER       = 0x06;
    const GRANT_TYPE_ERROR                   = 0x07;
    const INVALID_CLIENT_AUTHENTICATION_TYPE = 0x08;
    const INVALID_ACCESS_TOKEN_TYPE          = 0x09;

    private $name;
    protected $message;

    function __contruct($name, $message) {
      $this->name = $name;
      $this->message = $message;
    }
  }

  class Playlyfe
  {
    private static $client_id;
    private static $client_secret;
    private static $type;
    private static $redirect_uri;
    private static $code;

    private static $store;
    private static $retrieve;

    public static $certificate_file;

    const AUTHORIZATION_ENDPOINT = 'https://playlyfe.com/auth/token';
    const API_ENDPOINT = 'https://api.playlyfe.com/v1';

    /**
     * HTTP Methods
     */
    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_PATCH   = 'PATCH';

    private function __construct() {}

    public static function init(array $params) {
      if (!extension_loaded('curl')) {
        throw new Exception('The PHP exention curl must be installed to use this library.', PlaylyfeException::CURL_NOT_FOUND);
      }

      self::$client_id = $params['client_id'];
      self::$client_secret = $params['client_secret'];

      if(array_key_exists('type', $params)) {
        self::$type = $params['type'];
      }
      else {
        throw new PlaylyfeException("You must pass in a type whether 'client' for client credentials flow or 'code' for auth code flow");
      }

      if(self::$type == 'code'){
        if(array_key_exists('redirect_uri', $params)) {
          self::$redirect_uri = $params['redirect_uri'];
        }
        else {
          throw new PlaylyfeException("You must pass in a redirect_uri for the auth code flow");
        }
      }
      else {
        self::get_access_token();
      }

      if(array_key_exists('store', $params)) {
        self::$store = $params['store'];
      }
      else {
        self::$store = function($access_token) {
          print 'Storing access token';
          print_r($access_token);
        };
      }

      if(array_key_exists('retrieve', $params)) {
        self::$retrieve = $params['retrieve'];
      }
    }

    public static function exchange_code($code) {
      self::$code = $code;
      self::get_access_token();
    }

    public static function get_login_url() {
      $query = array( 'redirect_uri' => self::$redirect_uri, 'response_type' => 'code', 'client_id' => self::$client_id);
      return "https://playlyfe.com/auth?" . http_build_query($query, null, '&');
    }

    private static function get_access_token() {
      if(self::$type == 'client'){
        print("Getting Access Token");
        $data = array(
          'client_id' => self::$client_id,
          'client_secret' => self::$client_secret,
          'grant_type' => 'client_credentials'
        );
        $access_token = self::executeRequest('POST', self::AUTHORIZATION_ENDPOINT, null, $data);
        $expires_in = intval($access_token['expires_in']);
        $expires_at = time() + $expires_in;
        unset($access_token['expires_in']);
        $access_token['expires_at'] = $expires_at;

        #self::$store->__invoke($access_token);

        if(is_null(self::$retrieve)) {
          self::$retrieve = function() use ($access_token) {
            return $access_token;
          };
        }
      }
    }

    private static function check_token(&$query) {
      $token = self::$retrieve->__invoke();
      if (time() >= $token['expires_at']){
        print('Token Expired');
        self::get_access_token();
        $token = self::$retrieve->__invoke();
      }
      $query['access_token'] = $token['access_token'];
    }

    public static function get($route = '', $query = array()) {
      self::check_token($query);
      self::executeRequest(self::HTTP_METHOD_GET, self::API_ENDPOINT . $route, $query);
    }

    public static function post($route = '', $query = array(), $body = array()) {
      self::check_token($query);
      self::executeRequest(self::HTTP_METHOD_POST, self::API_ENDPOINT . $route, $query, $body);
    }

    public static function patch($route = '', $query = array(), $body = array()) {
      self::check_token($query);
      self::executeRequest(self::HTTP_METHOD_PATCH, self::API_ENDPOINT . $route, $query, $body);
    }

    public static function delete($route = '', $query = array(), $body = array()) {
      self::check_token($query);
      self::executeRequest(self::HTTP_METHOD_DELETE, self::API_ENDPOINT . $route, $query, $body);
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
    private static function executeRequest($http_method = self::HTTP_METHOD_GET, $url, $query = array(), $body = array())
    {
        $curl_options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CUSTOMREQUEST  => $http_method
        );

        switch($http_method) {
            case self::HTTP_METHOD_POST:
                $curl_options[CURLOPT_POST] = true;
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

        print($url);
        print("\n");
        $curl_options[CURLOPT_URL] = $url;
        $curl_options[CURLOPT_POSTFIELDS] = json_encode($body);
        $curl_options[CURLOPT_HTTPHEADER] = array(
          'Content-Type: application/json',
          'Content-Length: ' . strlen($curl_options[CURLOPT_POSTFIELDS])
        );

        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        //https handling
        if (!empty(self::$certificate_file)) {
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
          curl_setopt($ch, CURLOPT_CAINFO, self::$certificate_file);
        } else {
          //bypass ssl verification
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        if ($curl_error = curl_error($ch)) {
          throw new Exception($curl_error, PlaylyfeException::CURL_ERROR);
        } else {
          $json_decode = json_decode($result, true);
        }
        curl_close($ch);
        if(array_key_exists('error', $json_decode)) {
          throw new PlaylyfeException("Error: " . $json_decode['error'] . "  ". $json_decode['error_description'],  1);
        }
        else {
          print_r($json_decode);
          return $json_decode;
        }
    }
  }
?>
