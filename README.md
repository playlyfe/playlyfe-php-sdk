Playlyfe PHP SDK
================
This is the official OAuth 2.0 PHP client SDK for the Playlyfe API.  
It supports the `client_credentials` and `authorization code` OAuth 2.0 flows.    
For a complete API Reference checkout [Playlyfe Developers](https://dev.playlyfe.com/docs/api) for more information.

Requires
--------
PHP >= 5.5.9  
libcurl3

Install
----------
Just include the file in your project like this
```php
<?php
require_once("lib/playlyfe.php");
?>
```

Using
-----
### Create a client 
  If you haven't created a client for your game yet just head over to [Playlyfe](http://playlyfe.com) and login into your account, and go to the game settings and click on client  
  **1.Client Credentials Flow**  
    In the client page click on whitelabel client  
    ![alt text](https://github.com/pyros2097/playlyfe-ruby-sdk/raw/master/images/client.png "")

  **2.Authorization Code Flow**  
    In the client page click on backend client and specify the redirect uri this will be the url where you will be redirected to get the token
    ![alt text](https://github.com/pyros2097/playlyfe-ruby-sdk/raw/master/images/auth.png "")

> Note: If you want to test the sdk in staging you can click the Test Client button. You need to pass the player_id in the query in every request also.

  And then note down the client id and client secret you will need it later for using it in the sdk

The Playlyfe class allows you to make rest api calls like GET, POST, .. etc
Example: GET
```ruby
# To get infomation of the player johny
player = Playlyfe.get(
  route: '/player',
  query: { player_id: 'johny' }
)
puts player['id']
puts player['scores']

# To get all available processes with query
processes = Playlyfe.get(
  route: '/processes',
  query: { player_id: 'johny' }
)
puts processes
```

Example: POST
```ruby
# To start a process
process =  Playlyfe.post(
  route: "/definitions/processes/collect",
  query: { player_id: 'johny' },
  body: { name: "My First Process" }
)

#To play a process
Playlyfe.post(
  route: "/processes/#{@process_id}/play",
  query: { player_id: 'johny' },
  body: { trigger: "#{@trigger}" }
)
```

# Examples
## 1. Client Credentials Flow
```php
<?php
  session_start();
  ini_set('display_errors', 'on');
  require_once("pl_client.php");

  Playlyfe::init(
    array(
      'client_id' => "Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4",
      'client_secret' => "YzllYTE5NDQtNDMwMC00YTdkLWFiM2MtNTg0Y2ZkOThjYTZkMGIyNWVlNDAtNGJiMC0xMWU0LWI2NGEtYjlmMmFkYTdjOTI3",
      'type' => 'client'
    )
  );

  $players = Playlyfe::get('/players', array('player_id' => 'student1'));

?>
```

## 2. Authorization Code Flow
```php
<?php
  session_start();
  ini_set('display_errors', 'on');
  require_once("pl_client.php");

?>
```

# Documentation
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
        "store" => function($access_token) {}, # The closure which will persist the access token to a database. You have to persist the token to a database if you want the access token to remain the same in every request
        "retrieve" => function() {return $access_token} # The lambda which will retrieve the access token. This is called internally by the sdk on every request so the 
        #the access token can be persisted between requests
    );
);
?>
```
In development the sdk caches the access token in memory so you don't need to provide the store and retrieve lambdas. But in production it is highly recommended to persist the token to a database. It is very simple and easy to do it with redis. You can see the test cases for more examples.

## Get
```php
<?php
Playlyfe::get(
array(
    'route': => '', # The api route to get data from
    'query' => array( 'player_id' => 'stud'), # The query params that you want to send to the route
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

License
=======
Playlyfe PHP SDK v0.5.2  
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
