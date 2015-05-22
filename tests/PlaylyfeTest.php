<?php
  use Playlyfe\Sdk\Playlyfe;
  use Playlyfe\Sdk\PlaylyfeException;
  session_start();

  class PlaylyfeTest extends PHPUnit_Framework_TestCase {

    public function testErrors() {
      try {
        new Playlyfe(
          array(
            'client_id' => "Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4",
            'client_secret' => "YzllYTE5NDQtNDMwMC00YTdkLWFiM2MtNTg0Y2ZkOThjYTZkMGIyNWVlNDAtNGJiMC0xMWU0LWI2NGEtYjlmMmFkYTdjOTI3"
          )
        );
      }
      catch (PlaylyfeException $e) {
        $this->assertTrue($e->name == 'init_failed');
      }
      try {
        new Playlyfe(
          array(
            'client_id' => "",
            'client_secret' => "",
            'type' => 'client'
          )
        );
      }
      catch (PlaylyfeException $e) {
        $this->assertTrue($e->name == 'invalid_request');
      }

      try {
        $pl = new Playlyfe(
          array(
            'version' => 'v1',
            'client_id' => "Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4",
            'client_secret' => "YzllYTE5NDQtNDMwMC00YTdkLWFiM2MtNTg0Y2ZkOThjYTZkMGIyNWVlNDAtNGJiMC0xMWU0LWI2NGEtYjlmMmFkYTdjOTI3",
            'type' => 'client'
          )
        );
        $players = $pl->get('/unknown', array( 'player_id' => 'student1' ));
      }
      catch (PlaylyfeException $e) {
        $this->assertTrue($e->name == 'route_not_found');
      }
      try {
        $players = $pl->get('/players');
      }
      catch (PlaylyfeException $e) {
        $this->assertTrue($e->name == 'invalid_player');
      }
      try {
        $pl = new Playlyfe(
          array(
            'client_id' => "Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4",
            'client_secret' => "YzllYTE5NDQtNDMwMC00YTdkLWFiM2MtNTg0Y2ZkOThjYTZkMGIyNWVlNDAtNGJiMC0xMWU0LWI2NGEtYjlmMmFkYTdjOTI3",
            'type' => 'code',
            'redirect_uri' => 'http://localhost:3000/welcome/home'
          )
        );
        $pl->exchange_code(null);
      }
      catch (PlaylyfeException $e) {
        $this->assertTrue($e->name == 'invalid_request');
      }
    }

    public function testv1Routes() {
      $pl = new Playlyfe(
        array(
          'version' => 'v1',
          'client_id' => "Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4",
          'client_secret' => "YzllYTE5NDQtNDMwMC00YTdkLWFiM2MtNTg0Y2ZkOThjYTZkMGIyNWVlNDAtNGJiMC0xMWU0LWI2NGEtYjlmMmFkYTdjOTI3",
          'type' => 'client'
        )
      );

      $players = $pl->get('/players', array('player_id' => 'student1', 'limit' => 1 ));

      $this->assertTrue($players["data"] != null);
      $this->assertTrue($players["data"]["0"]["id"] != null);

      $players_raw = $pl->get('/players', array('player_id' => 'student1', 'limit' => 1 ), true);
      $this->assertTrue(gettype($players_raw) == 'string');

      $player_id = 'student1';
      $player = $pl->api('GET', '/player', array( 'player_id' => $player_id ));
      $this->assertTrue($player["id"] == "student1");
      $this->assertTrue($player["alias"] == "Student1");
      $this->assertTrue($player["enabled"] == true);

      $pl->get('/definitions/processes', array('player_id' => $player_id ));
      $pl->get('/definitions/teams', array( 'player_id' => $player_id ));
      $pl->get('/processes', array( 'player_id' => $player_id ));
      $pl->get('/teams', array('player_id' => $player_id ));

      $processes = $pl->get('/processes', array('player_id' => $player_id , 'limit' => 1, 'skip' => 4));
      $this->assertTrue($processes["data"][0]["definition"] == "module1");
      $this->assertTrue(count($processes["data"]) == 1);

      $new_process = $pl->post('/definitions/processes/module1', array('player_id' => $player_id));
      $this->assertTrue($new_process["definition"] == "module1");
      $this->assertTrue($new_process["state"] == "ACTIVE");

      $pid = $new_process['id'];
      $patched_process = $pl->patch("/processes/$pid",
        array('player_id' => $player_id),
        array('name' => 'patched_process', 'access' => 'PUBLIC')
      );
      $this->assertTrue($patched_process['name'] == 'patched_process');
      $this->assertTrue($patched_process['access'] == 'PUBLIC');

      $deleted_process = $pl->delete("/processes/$pid", array('player_id' => $player_id));
      $this->assertTrue($deleted_process['message'] != null);
    }

    public function testv2Routes() {
      $pl = new Playlyfe(
        array(
          'version' => 'v2',
          'client_id' => "Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4",
          'client_secret' => "YzllYTE5NDQtNDMwMC00YTdkLWFiM2MtNTg0Y2ZkOThjYTZkMGIyNWVlNDAtNGJiMC0xMWU0LWI2NGEtYjlmMmFkYTdjOTI3",
          'type' => 'client'
        )
      );

      $players = $pl->get('/runtime/players', array('player_id' => 'student1', 'limit' => 1 ));

      $this->assertTrue($players["data"] != null);
      $this->assertTrue($players["data"]["0"]["id"] != null);

      $players_raw = $pl->get('/runtime/players', array('player_id' => 'student1', 'limit' => 1 ), true);
      $this->assertTrue(gettype($players_raw) == 'string');

      $player_id = 'student1';
      $player = $pl->api('GET', '/runtime/player', array( 'player_id' => $player_id ));
      $this->assertTrue($player["id"] == "student1");
      $this->assertTrue($player["alias"] == "Student1");
      $this->assertTrue($player["enabled"] == true);

      $pl->get('/runtime/definitions/processes', array('player_id' => $player_id ));
      $pl->get('/runtime/definitions/teams', array( 'player_id' => $player_id ));
      $pl->get('/runtime/processes', array( 'player_id' => $player_id ));
      //$pl->get('/runtime/teams', array('player_id' => $player_id ));

      $processes = $pl->get('/runtime/processes', array('player_id' => $player_id , 'limit' => 1, 'skip' => 4));
      $this->assertTrue($processes["data"][0]["definition"] == "module1");
      $this->assertTrue(count($processes["data"]) == 1);

      $new_process = $pl->post('/runtime/processes', array('player_id' => $player_id), array('definition' => 'module1'));
      $this->assertTrue($new_process["definition"]["id"] == "module1");
      $this->assertTrue($new_process["state"] == "ACTIVE");

      $pid = $new_process['id'];
      $patched_process = $pl->patch("/runtime/processes/$pid",
        array('player_id' => $player_id),
        array('name' => 'patched_process', 'access' => 'PUBLIC')
      );
      $this->assertTrue($patched_process['name'] == 'patched_process');
      $this->assertTrue($patched_process['access'] == 'PUBLIC');

      $deleted_process = $pl->delete("/runtime/processes/$pid", array('player_id' => $player_id));
      $this->assertTrue($deleted_process['message'] != null);
    }

    public function testLoad() {
      $pl = new Playlyfe(
        array(
          'version' => 'v1',
          'client_id' => "Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4",
          'client_secret' => "YzllYTE5NDQtNDMwMC00YTdkLWFiM2MtNTg0Y2ZkOThjYTZkMGIyNWVlNDAtNGJiMC0xMWU0LWI2NGEtYjlmMmFkYTdjOTI3",
          'type' => 'client',
          'store' => function($access_token) {
            print('Storing');
            $_SESSION['access_token'] = $access_token;
          },
          'load' => function() {
            print('Retrieving');
            if(array_key_exists('access_token', $_SESSION)){
              return $_SESSION['access_token'];
            }
            else {
              return null;
            }
          }
        )
      );
      $players = $pl->get('/players', array('player_id' => 'student1', 'limit' => 1 ));
      $this->assertTrue($players["data"] != null);
      $this->assertTrue($players["data"]["0"]["id"] != null);
      $player = $pl->get('/player', array( 'player_id' => 'student1' ));
      $this->assertTrue($player["id"] == "student1");
    }

    public function testJWT() {
      $token = Playlyfe::createJWT(array(
        'client_id' => 'MWYwZGYzNTYtZGIxNy00OGM5LWExZGMtZjBjYTFiN2QxMTlh',
        'client_secret' => 'NmM2YTcxOGYtNGE2ZC00ZDdhLTkyODQtYTIwZTE4ZDc5YWNjNWFiNzBiYjAtZmZiMC0xMWU0LTg5YzctYzc5NWNiNzA1Y2E4',
        'player_id' => 'student1',
        'expires' => 3600
      ));
      echo $token;
    }

  }
?>
