<?php
  //verifica il login
  require_once('dbHandler.php');
  $u = new UserHandler();
  $u->verifySession();
  $lv = $u->getAcLv();
  if ($u->getAcLv() < 1) {
    echo "<script type='text/javascript'>alert('Livello di accesso non valido');</script>";
    require_once("Redirect.php");
    goToDataView();
  }
?>
<html>
  <head>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" type="text/css" href="css/style.css">
  </head>
  <body>
    <nav>
      <div>
        <!-- torna alla tabella -->
        <a href="DataView.php" style="margin-top:2%;margin-left: 84%;">Tabella</a>
        <!-- Logout -->
        <a href="LogoutResponse.php" style="margin-top:15%;">Logout</a>
      </div>
    </nav>

    <div id='buttons'>
      <button id='addCorso' onclick='show(1)'>Aggiungi corso</button>
      <button id='addPersonale' onclick='show(2)'>Aggiungi personale</button>
    </div>

    <div id='forms'>
      <form id='corso'>
        dgadgadg
      </form>
      <form id='personale'>

      </form>
    </div>

    <script>
      function show(s){
        if(s==1){
          document.getElementById('')
        }
      }
    </script>
    <?php
      //verifica il login
      require_once('dbHandler.php');
      $u = new UserHandler();
      $u->verifySession();

    ?>
  </body>
</html>
