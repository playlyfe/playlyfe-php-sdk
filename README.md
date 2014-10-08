Playlyfe PHP SDK
================
This is a basic OAuth 2.0 PHP client SDK for the Playlyfe API. It currently only supports the `client_credentials` flow and `authorization code` flow.  
To understand how the complete api works checkout [The Playlyfe Api](http://dev.playlyfe.com/docs/api) for more information.

Requires
--------
Php >= 5.5.9
libcurl3

Install
----------
Just include the file in your project like this
```php
<?php
require_once("lib/playlyfe.php");
?>
```

Documentation
-------------------------------
## Init
You can initiate a client by giving the client_id and client_secret params
```php
<?php
Playlyfe.init(
    array(
        "client_id" => "",
        "client_secret" => "",
        "type" => "client" or "code",
        "redirect_uri" => "The url to redirect to", #only for auth code flow
        "store" => function($access_token) {}; # The closure which will persist the access token to a database. You have to persist the token to a database if you want the access token to remain the same in every request
        "retrieve" => function() {return $access_token}; # The lambda which will retrieve the access token. This is called internally by the sdk on every request so the 
        #the access token can be persisted between requests
    );
);
?>
```
In development the sdk caches the access token in memory so you don't need to provide the store and retrieve lambdas. But in production it is highly recommended to persist the token to a database. It is very simple and easy to do it with redis. You can see the test cases for more examples.

## Get
```php
<?php
Playlyfe::get('', # The api route to get data from
    array(), # The query params that you want to send to the route
    false # Whether you want the response to be in raw string form or json
)
?>
```
## Post
```php
<?php
Playlyfe::post(
    route: '' # The api route to post data to
    query: {}, # The query params that you want to send to the route
    body: {}. # The data you want to post to the api this will be automagically converted to json
)
?>
```
## Patch
```php
<?php
Playlyfe::patch(
    route: '' # The api route to patch data
    query: {} # The query params that you want to send to the route
    body: {} # The data you want to update in the api this will be automagically converted to json
)
?>
```
## Delete
```php
<?php
Playlyfe::delete(
    route: '' # The api route to delete the component
    query: {} # The query params that you want to send to the route
    body: {} # The data which will specify which component you will want to delete in the route
)
?>
```
## Get Login Url
```php
<?php
Playlyfe::get_login_url()
#This will return the url to which the user needs to be redirected for the user to login. You can use this directly in your views.
?>
```

## Exchange Code
```php
<?php
Playlyfe::exchange_code($code)
#This is used in the auth code flow so that the sdk can get the access token.
#Before any request to the playlyfe api is made this has to be called atleast once. 
#This should be called in the the route/controller which you specified in your redirect_uri
?>
```

## Errors
A ```PlaylyfeException``` is thrown whenever a curl error occurs in each call.The Exception contains a name and message field which can be used to determine the type of error that occurred.

## Example Useage
Given below is a simple example of using this client to check if a player exists and create it if it doesn't.

```php
<?php

  session_start();

  ini_set('display_errors', 'on');

  require_once("pl_client.php");

  const CLIENT_ID     = 'YOUR_CLIENT_ID';
  const CLIENT_SECRET = 'YOUR_CLIENT_SECRET';

  $player_id = 'test';

  $client = new Playlyfe\Client(array('client_id' => CLIENT_ID, 'client_secret' => CLIENT_SECRET));

  // If we cant find an access token fetch it
  if (!isset($_SESSION['access_token']) || empty($_SESSION['access_token'])) {

      // IMPORTANT: You should ideally store the access token in a external persistence layer
      // like a cache or database. Over here we use the session object for demonstration purposes.
      $_SESSION['access_token'] = $client->getAccessToken();

  } else {

      $client->setAccessToken($_SESSION['access_token']);
  }

  // Fetch the profile of a test player
  $response = $client->api('GET', '/player', array('player_id' => $player_id));

  // Player profile does not exist, create it
  if ($response['code'] == 404) {
    $response = $client->api('POST', '/game/players', array(), array('id' => $player_id));
    if ($response['code'] == 200) {
      $profile = $response['result'];
    } else {
      print "ERROR: ". json_encode($response);
    }
  } else {
    $profile = $response['result'];

  }

  print "PLAYER PROFILE:\n" . json_encode($profile);

?>
```

License
=======
Playlyfe PHP SDK v0.4.1  
http://dev.playlyfe.com/  
Copyright(c) 2013-2014, Playlyfe Technologies, developers@playlyfe.com  

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:  

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.  

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
