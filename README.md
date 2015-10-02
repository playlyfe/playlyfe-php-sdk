![Playlyfe PHP SDK](https://dev.playlyfe.com/images/assets/pl-php-sdk.png "Playlyfe PHP SDK")

Playlyfe PHP SDK    [![PHP version](https://badge.fury.io/ph/playlyfe%2Fplaylyfe.svg)](http://badge.fury.io/ph/playlyfe%2Fplaylyfe)
================
This is the official OAuth 2.0 PHP client SDK for the Playlyfe API.
It supports the `client_credentials` and `authorization code` OAuth 2.0 flows.
For a complete API Reference checkout [Playlyfe Developers](https://dev.playlyfe.com/docs/api) for more information.

>Note: Breaking Changes this is the new version of the sdk which uses the Playlyfe api v2 by default if you still want to use the v1 api you can do that so by passing a version param with 'v1'

# Examples
The Playlyfe class allows you to make rest api calls like GET, POST, .. etc
**For api v2**
```php
<?php
    use Playlyfe\Sdk\Playlyfe;
    use Playlyfe\Sdk\PlaylyfeException;

    $playlyfe = new Playlyfe(
        array(
          'version' => 'v2',
          'client_id' => "Your client id",
          'client_secret' => "Your client secret",
          'type' => 'client'
        )
    );

    # To get infomation of the player johny
    $player = $playlyfe->get('/runtime/player', array( 'player_id' => 'johny' ));

    print_r($player['id']);
    print_r($player['scores']);

    # To get all available processes with query
    $processes = $playlyfe->get('/runtime/processes', array( 'player_id' => 'johny' ));
    print_r($processes);

    # To start a process
    $process =  $playlyfe->post("/runtime/processes",
      array( 'player_id' => 'johny'),
      array( 'name' => "My First Process", 'definition' => 'collect' )
    );

    #To play a process
    $playlyfe->post("/runtime/processes/$process_id/play",
      array( 'player_id' => 'johny'),
      array( 'trigger' => "$trigger" )
    );
?>
```

There is also a blog series on using this SDK here https://blog.playlyfe.com/gamify-moodle-laying-the-base/

And an easy way to run the examples in the examples folder is using [docker-moodle](https://github.com/playlyfe/docker-moodle) like this,
```
docker run -d --name moodle -p 3000:3000 -p 3306:3306 -v /path/to/playlyfe-php-sdk:/var/www/html playlyfe/moodle
```
Then navigate to http://localhost:3000/examples/client.php


Requires
--------
PHP >= 5.5.9  
libcurl3

Install
----------
Add this to your composer.json file
```json
"require": {
    "playlyfe/playlyfe": "0.8.0"
}
```
Using
-----
### Create a client
  If you haven't created a client for your game yet just head over to [Playlyfe](http://playlyfe.com) and login into your account, and go to the game settings and click on client

## 1. Client Credentials Flow
In the client page select Yes for both the first and second questions
![client](https://cloud.githubusercontent.com/assets/1687946/7930229/2c2f14fe-0924-11e5-8c3b-5ba0c10f066f.png)
```php
<?php
  use Playlyfe\Sdk\Playlyfe;
  use Playlyfe\Sdk\PlaylyfeException;

  $playlyfe = new Playlyfe(
    array(
      'client_id' => "Your client id",
      'client_secret' => "Your client secret",
      'type' => 'client',
      'version' => 'v2'
    )
  );

  $players = $playlyfe->get('/runtime/players', array('player_id' => 'student1'));

?>
```
## 2. Authorization Code Flow
In the client page select yes for the first question and no for the second
![auth](https://cloud.githubusercontent.com/assets/1687946/7930231/2c31c1fe-0924-11e5-8cb5-73ca0a002bcb.png)
```php
<?php
  new Playlyfe(
    array(
      "client_id" => "Your client id",
      "client_secret" => "Your client secret",
      "type" => 'code',
      "version" => 'v2',
      "redirect_uri" => 'http://example.playlyfe.com/auth.php'
    )
  );
?>
```
## 3. Custom Login Flow using JWT(JSON Web Token)
In the client page select no for the first question and yes for the second
![jwt](https://cloud.githubusercontent.com/assets/1687946/7930230/2c2f2caa-0924-11e5-8dcf-aed914a9dd58.png)
```php
<?php
$token = Playlyfe::createJWT(array(
    'client_id' => 'Your client id',
    'client_secret' => 'Your client secret',
    'player_id' => 'johny',  # The player id associated with your user
    'scopes' => array('player.runtime.read', 'player.runtime.write'), # The scopes the player has access to
    'expires' => 3600 # 1 hour
));
echo $token;
?>
```
This is used to create jwt token which can be created when your user is authenticated. This token can then be sent to the frontend and or stored in your session. With this token the user can directly send requests to the Playlyfe API as the player.

# Client Scopes
![Client](https://cloud.githubusercontent.com/assets/1687946/9349193/e00fe91c-465f-11e5-8094-6e03c64a662c.png)

Your client has certain access control restrictions. There are 3 kind of resources in the Playlyfe REST API they are,

1.`/admin` -> routes for you to perform admin actions like making a player join a team

2.`/design` -> routes for you to make design changes programmatically

3.`/runtime` -> routes which the users will generally use like getting a player profile, playing an action

The resources accessible to this client can be configured to have a read permission that means only `GET` requests will work.

The resources accessible to this client can be configured to have a write permission that means only `POST`, `PATCH`, `PUT`, `DELETE` requests will work.

The version restriction is only for the design resource and can be used to restrict the client from accessing any version of the game design other than the one specified. By default it allows all.

If access to a route is not allowed and then you make a request to that route then you will get an error like this,
```json
{
  "error": "access_denied",
  "error_description": "You are not allowed to access this api route"
}
```

# Documentation
You can initiate a client by giving the client_id and client_secret params
```php
<?php
  new Playlyfe(
    array(
        "client_id" => "Your client id",
        "client_secret" => "Your client secret",
        "type" => "client" or "code",
        "version" => "the version of the api you would like to use use v2 for now",
        "redirect_uri" => "The url to redirect to", #only for auth code flow
        "store" => function($access_token) {}, # The function which will persist the access token to a database. You have to persist the token to a database if you want the access token to remain the same in every request
        "load" => function() {return $access_token} # The function which will retrieve the access token. This is called internally by the sdk on every request so the
        #the access token can be persisted between requests
    );
);
?>
```
In development the sdk caches the access token in memory so you don't need to provide the store and retrieve lambdas. But in production it is highly recommended to persist the token to a database. It is very simple and easy to do it with redis. You can see the test cases for more examples.


**API**
```php
<?php
api('GET', # can be GET/POST/PUT/PATCH/DELETE
    '', # The api route to get data from
    array(), # The query params that you want to send to the route
    array() # The body data
);
?>
```
**Get**
```php
<?php
get('', # The api route to get data from
    array(), # The query params that you want to send to the route
    false # Whether you want the response to be in raw string form or json
);
?>
```
**Post**
```php
<?php
post('' # The api route to post data to
    array(), # The query params that you want to send to the route
    array(). # The data you want to post to the api this will be automagically converted to json
)
?>
```
**Patch**
```php
<?php
patch('' # The api route to patch data
    array() # The query params that you want to send to the route
    array() # The data you want to update in the api this will be automagically converted to json
);
?>
```
**Put**
```php
<?php
put('' # The api route to patch data
    array() # The query params that you want to send to the route
    array() # The data you want to update in the api this will be automagically converted to json
);
?>
```
**Delete**
```php
<?php
delete('' # The api route to delete the component
    array() # The query params that you want to send to the route
);
?>
```
**Get Login Url**
```php
<?php
get_login_url();
#This will return the url to which the user needs to be redirected for the user to login. You can use this directly in your views.
?>
```

**Exchange Code**
```php
<?php
exchange_code($code);
#This is used in the auth code flow so that the sdk can get the access token.
#Before any request to the playlyfe api is made this has to be called atleast once.
#This should be called in the the script/route which you specified in your redirect_uri
?>
```

**Read Image**
```php
<?php
read_image($image_id, query = array( 'size' => 'small' ));
# This is a convienience method to read your design images
?>
```

**Upload Image**
```php
<?php
upload_image($file);
# This uploads a file to your image album and returns the image_id
# $file is the path to the image file
?>
```

**Errors**  
A ```PlaylyfeException``` is thrown whenever an error occurs in each call.The Exception contains a name and message field which can be used to determine the type of error that occurred.

License
=======
Playlyfe PHP SDK
http://dev.playlyfe.com/  
Copyright(c) 2013-2014, Playlyfe IT Solutions Pvt. Ltd, support@playlyfe.com  

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
