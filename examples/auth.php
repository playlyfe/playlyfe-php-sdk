 <html>
 <head>
  <title>Playlyfe</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
 </head>
 <body style= "background">
  <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Playlyfe Authorization Code Flow</a>
        </div>
        <div class="navbar-collapse collapse">
          <form class="navbar-form navbar-right" role="form">
            <div class="form-group">
              <input type="text" placeholder="Email" class="form-control">
            </div>
            <div class="form-group">
              <input type="password" placeholder="Password" class="form-control">
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="jumbotron">
      <div class="container">
        <?php
          use Playlyfe\Sdk\Playlyfe;
          use Playlyfe\Sdk\PlaylyfeException;

          session_start();
          ini_set('display_errors', 'on');
          require_once("../src/playlyfe.php");

          $pl = new Playlyfe(
            array(
              "client_id" => "NzQ3OTExNTEtM2UxZC00N2IyLTgxM2YtZWJkNWFlYTg3YjBm",
              "client_secret" => "ODc4YzQxYmItYzk1NS00Y2I3LWFjNWItZDI0YzczYTI2MjRiMjQ5YzUxZjAtNGVlMS0xMWU0LTg3YWMtNmRhODZiZjAyMmUx",
              "type" => 'code',
              "redirect_uri" => 'http://example.playlyfe.com/auth.php',
              'store' => function($access_token) {
                print 'Storing';
                $_SESSION['access_token'] = $access_token;
              },
              'load' => function() {
                print 'Retrieving';
                if(array_key_exists('access_token', $_SESSION)){
                  return $_SESSION['access_token'];
                }
                else {
                  return null;
                }
              }
            )
          );

          if(array_key_exists('logout', $_GET)) {
            session_destroy();
          }

          if(array_key_exists('code', $_GET) or array_key_exists('access_token', $_SESSION)){
            if(array_key_exists('code', $_GET)){
              $pl->exchange_code($_GET['code']);
            }
            $players = $pl->get('/players', array('player_id' => 'student1'));
            echo "<ul>";
            echo "<li class='list-group-item disabled'><h2>Players</h2></li>";
            foreach($players["data"] as $value){
              $id = $value["id"];
              echo "<li class='list-group-item'><h3>$id</h3></li>";
            }
            echo "</ul>";
          }
          else {
            $login_url = $pl->get_login_url();
            echo '<h2>Please Login using your Playlyfe Account</h2>';
            echo "<a href='$login_url'>Sign in</a>";
          }
        ?>
      </div>
    </div>
  </body>
 </html>

