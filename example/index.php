<?php

  session_start();

  ini_set('display_errors', 'on');

  require_once("pl_client.php");

  const CLIENT_ID     = 'YjQ4NmJlNGMtMDFlMS00ZDQ0LThhNjUtYjZlNjA4MmRiYTlj';
  const CLIENT_SECRET = 'YjI1ZDNjNGItOTM1Mi00ZWVmLWFhMGUtNDM4MGQ2ZmZmODUzNzE0ZjUzZDAtNDU3Mi0xMWU0LTg3MWQtYTk4YjE3YjJiYzk1';
  $player_id = 'test2';

  $client = new Playlyfe\Client(array('client_id' => CLIENT_ID, 'client_secret' => CLIENT_SECRET));

  // If we can't find an access token fetch it
  if (!isset($_SESSION['access_token']) || empty($_SESSION['access_token'])) {
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
