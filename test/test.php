<?php
  session_start();
  ini_set('display_errors', 'on');
  require_once("../lib/playlyfe.php");

  Playlyfe::init(
    array(
      'client_id' => "Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4",
      'client_secret' => "YzllYTE5NDQtNDMwMC00YTdkLWFiM2MtNTg0Y2ZkOThjYTZkMGIyNWVlNDAtNGJiMC0xMWU0LWI2NGEtYjlmMmFkYTdjOTI3",
      'type' => 'client'
    )
  );
  $players = Playlyfe::get('/players', array( 'player_id' => 'student1' ));
  $players = Playlyfe::get('/player', array( 'player_id' => 'student1' ));
  #print_r($players);
?>
