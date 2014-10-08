<?php
  // Active assert and make it quiet
  assert_options(ASSERT_ACTIVE, 1);
  assert_options(ASSERT_WARNING, 0);
  assert_options(ASSERT_QUIET_EVAL, 1);

  // Create a handler function
  function my_assert_handler($file, $line, $code)
  {
      echo "Assertion Failed:
          File '$file'
          Line '$line'
          Code '$code'";
  }

  assert_options(ASSERT_CALLBACK, 'my_assert_handler');

  session_start();
  ini_set('display_errors', 'on');
  require_once("../lib/playlyfe.php");

  try {
    Playlyfe::init(
      array(
        'client_id' => "Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4",
        'client_secret' => "YzllYTE5NDQtNDMwMC00YTdkLWFiM2MtNTg0Y2ZkOThjYTZkMGIyNWVlNDAtNGJiMC0xMWU0LWI2NGEtYjlmMmFkYTdjOTI3"
      )
    );
  }
  catch (PlaylyfeException $e) {
    assert($e->name == 'init_failed');
  }

  try {
    Playlyfe::init(
      array(
        'client_id' => "Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4",
        'client_secret' => "YzllYTE5NDQtNDMwMC00YTdkLWFiM2MtNTg0Y2ZkOThjYTZkMGIyNWVlNDAtNGJiMC0xMWU0LWI2NGEtYjlmMmFkYTdjOTI3",
        'type' => 'code'
      )
    );
  }
  catch (PlaylyfeException $e) {
    assert($e->name == 'init_failed');
  }

  try {
    Playlyfe::init(
      array(
        'client_id' => "",
        'client_secret' => "",
        'type' => 'client'
      )
    );
  }
  catch (PlaylyfeException $e) {
    assert($e->name == 'invalid_request');
  }

  Playlyfe::init(
    array(
      'client_id' => "Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4",
      'client_secret' => "YzllYTE5NDQtNDMwMC00YTdkLWFiM2MtNTg0Y2ZkOThjYTZkMGIyNWVlNDAtNGJiMC0xMWU0LWI2NGEtYjlmMmFkYTdjOTI3",
      'type' => 'client'
    )
  );
  try {
    $players = Playlyfe::get('/unknown', array( 'player_id' => 'student1' ));
  }
  catch (PlaylyfeException $e) {
    assert($e->name == 'route_not_found');
  }

  try {
    $players = Playlyfe::get('/players');
  }
  catch (PlaylyfeException $e) {
    assert($e->name == 'invalid_player');
  }

  $players = Playlyfe::get('/players', array('player_id' => 'student1', 'limit' => 1 ));

  assert($players["data"] != null);
  assert($players["data"]["0"]["id"] != null);

  $player_id = 'student1';
  $player = Playlyfe::get('/player', array( 'player_id' => $player_id ));
  assert($player["id"] == "student1");
  assert($player["alias"] == "Student1");
  assert($player["enabled"] == true);

  Playlyfe::get('/definitions/processes', array('player_id' => $player_id ));
  Playlyfe::get('/definitions/teams', array( 'player_id' => $player_id ));
  Playlyfe::get('/processes', array( 'player_id' => $player_id ));
  Playlyfe::get('/teams', array('player_id' => $player_id ));

  $processes = Playlyfe::get('/processes', array('player_id' => $player_id , 'limit' => 1, 'skip' => 4));
  assert($processes["data"][0]["definition"] == "module1");
  assert(count($processes["data"]) == 1);

  $new_process = Playlyfe::post('/definitions/processes/module1', array('player_id' => $player_id));
  assert($new_process["definition"] == "module1");
  assert($new_process["state"] == "ACTIVE");

  $pid = $new_process['id'];
  $patched_process = Playlyfe::patch("/processes/$pid",
    array('player_id' => $player_id),
    array('name' => 'patched_process', 'access' => 'PUBLIC')
  );
  assert($patched_process['name'] == 'patched_process');
  assert($patched_process['access'] == 'PUBLIC');

  $deleted_process = Playlyfe::delete("/processes/$pid", array('player_id' => $player_id));
  assert($deleted_process['message'] != null);

  Playlyfe::init(
    array(
      'client_id' => "Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4",
      'client_secret' => "YzllYTE5NDQtNDMwMC00YTdkLWFiM2MtNTg0Y2ZkOThjYTZkMGIyNWVlNDAtNGJiMC0xMWU0LWI2NGEtYjlmMmFkYTdjOTI3",
      'type' => 'client',
      'store' => function($access_token) {
        print 'Storing';
        $_SESSION['access_token'] = $access_token;
      },
      'retrieve' => function() {
        print 'Retrieving';
        return $_SESSION['access_token'];
      }
    )
  );

  $players = Playlyfe::get('/players', array('player_id' => 'student1', 'limit' => 1 ));
  assert($players["data"] != null);
  assert($players["data"]["0"]["id"] != null);
  $player = Playlyfe::get('/player', array( 'player_id' => 'student1' ));
  assert($player["id"] == "student1");

  Playlyfe::init(
    array(
      'client_id' => "Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4",
      'client_secret' => "YzllYTE5NDQtNDMwMC00YTdkLWFiM2MtNTg0Y2ZkOThjYTZkMGIyNWVlNDAtNGJiMC0xMWU0LWI2NGEtYjlmMmFkYTdjOTI3",
      'type' => 'code',
      'redirect_uri' => 'http://localhost:3000/welcome/home'
    )
  );
  assert(Playlyfe::get_login_url() == "https://playlyfe.com/auth?redirect_uri=http%3A%2F%2Flocalhost%3A3000%2Fwelcome%2Fhome&response_type=code&client_id=Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4");
?>
