<html>

  <head>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" type="text/css" href="style.css">
  </head>

  <body>
    <div class="row">
      <div class="col-12 container">
        <div class="loginbox">
          <div class="logincontent">
            <form action="LoginResponse.php" method="post">
              <input type="text" name="username" placeholder="Username" class="login textbox">
              <input type="password" name="psw" placeholder="Password" class="login textbox">
              <input type="submit" name="submit" value="Accedi" class="login btnlogin">
            </form>
          </div>
        </div>
      </div>
    </div>

  <?php
  require_once("dbHandler.php");
  require_once("Redirect.php");

  $usrHandler = new UserHandler();
  if ($usrHandler->isLogged())  //verifica login, altrimenti reindirizza al DataView
    goToDataView();

  //mostra esito login
  $result = isset($_POST["response"]) ? unserialize($_POST["response"]) : null; //riconverte in array l'esito
  if(isset($result))
    echo "<script type='text/javascript'>alert('".$result[1]."');</script>"; //alert esito con il messaggio
  ?>
  </body>
</html>
