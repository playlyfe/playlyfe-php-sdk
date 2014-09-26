Playlyfe PHP SDK
================

This is a basic OAuth 2.0 PHP client SDK for the Playlyfe API. It currently only support the `client_credentials` flow.

Given below is a simple example of using this client to check if a player exists and if not create it.

For more documentation on the Playlyfe API visit [https://dev.playlyfe.com](https://dev.playlyfe.com)

```
<?php

  session_start();

  ini_set('display_errors', 'on');

  require_once("pl_client.php");

  const CLIENT_ID     = 'YOUR_CLIENT_ID';
  const CLIENT_SECRET = 'YOUR_CLIENT_SECRET';

  $player_id = 'test';

  $client = new Playlyfe\Client(array('client_id' => CLIENT_ID, 'client_secret' => CLIENT_SECRET));

  // If we can't find an access token fetch it
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

Methods
=======

setAccessToken(token)
---------------------
Set the access token to use for all requests from the client.

getAccessToken()
----------------
Fetch a new access token from the server

api(method, path, query, body)
------------------------------
Make an API request using the specified `method` to `path`. Query parameters can be put in `query` and the request body in `body`.

