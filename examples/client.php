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
          <a class="navbar-brand" href="#">Playlyfe Client Credential Flow</a>
        </div>
        <div class="navbar-collapse collapse">
          <form class="navbar-form navbar-right" role="form">
            <div class="form-group">
              <input type="text" placeholder="Email" class="form-control">
            </div>
            <div class="form-group">
              <input type="password" placeholder="Password" class="form-control">
            </div>
            <button type="submit" class="btn btn-success">Sign in</button>
          </form>
        </div><!--/.navbar-collapse -->
      </div>
    </div>

    <div class="jumbotron">
      <div class="container">
        <ul>
        <?php
          use Playlyfe\Sdk\Playlyfe;
          use Playlyfe\Sdk\PlaylyfeException;

          session_start();
          ini_set('display_errors', 'on');
          require_once("../src/playlyfe.php");
          if(array_key_exists('logout', $_GET)) {
            session_destroy();
          }
          $pl = new Playlyfe(
            array(
              'client_id' => "Zjc0MWU0N2MtODkzNS00ZWNmLWEwNmYtY2M1MGMxNGQ1YmQ4",
              'client_secret' => "YzllYTE5NDQtNDMwMC00YTdkLWFiM2MtNTg0Y2ZkOThjYTZkMGIyNWVlNDAtNGJiMC0xMWU0LWI2NGEtYjlmMmFkYTdjOTI3",
              'type' => 'client',
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
          $players = $pl->get('/players', array('player_id' => 'student1'));
          echo "<li class='list-group-item disabled'><h2>Players</h2></li>";
          foreach($players["data"] as $value){
            $id = $value["id"];
            echo "<li class='list-group-item'><h3>$id</h3></li>";
          }

          #$picture = Playlyfe::get('/assets/metrics/knowledge', array('player_id' => 'student1'), true);
          #$bin = base64_encode($picture);
          #print "<img src='data:image/jpg;base64,$bin'>"
        ?>
        </ul>
      </div>
    </div>
    <img src='../src/image.php?metric=knowledge' />
    <img src='../src/image.php?metric=levels' />
    <img src='../src/image.php?metric=badges' />
  </body>
 </html>

