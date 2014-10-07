<?php
    namespace Playlyfe;


    require_once("OAuth2/Client.php");
    require_once("OAuth2/GrantType/IGrantType.php");
    require_once("OAuth2/GrantType/ClientCredentials.php");

    class Client
    {

        const AUTHORIZATION_ENDPOINT = 'https://playlyfe.com/auth/token';
        const API_ENDPOINT = 'https://api.playlyfe.com/v1';

        public function __construct(array $params) {
            $this->client = new \OAuth2\Client($params['client_id'], $params['client_secret']);
        }

        public function setAccessToken($token) {
            $this->client->setAccessToken($token);
        }

        public function getAccessToken() {
            $response = $this->client->getAccessToken(self::AUTHORIZATION_ENDPOINT, 'client_credentials', array());
            $token = $response['result']->access_token;
            return $token;
        }

        public function api($method = 'GET', $route, $query = array(), $body = array()) {
            return $this->client->fetch($method, self::API_ENDPOINT . $route, $query, $body);
        }

    }


