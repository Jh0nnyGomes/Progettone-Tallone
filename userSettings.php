<?php
  //verifica il login
  require_once('dbHandler.php');
  $u = new UserHandler();
  $u->verifySession();
  $lv = $u->getAcLv();
?>

<html>
  <head>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
    <div class="container">
      <div class="navbar">
        <ul class="navbar-list">
          <!-- DEBUG Home: TODO collegare alla pagina principale della scuola-->
          <li>
            <a href="index.html">Home</a>
          </li>
          <li>
            <a href="DataView.php">Tabella</a>
          </li>
          <li>
            <a href="PrintFilter.php">Stampa</a>
          </li>
          <!-- inserimento per administrator -->
          <?php
            // admin
            if ($lv > 0){
              echo '<li><a href="addPersonale.php">Aggiungi personale</a></li>';
              echo '<li><a href="addCorso.php">Aggiungi corso</a></li>';
            }
          ?>
        </ul>
          <ul class="logout-bar">
          <li>
            <!-- Logout -->
            <a href="LogoutResponse.php">Logout</a>
          </li>
        </ul>
      </div>

    <button id='modPBtn' onclick='modifyPsw()'>modifica password</button>

    <div id='modP' style='display:none'>
      <form action='modifyPsw.php' method='POST'>
        <input type='password' name='oldPsw' placeholder='vecchia password'>
        <input type='password' name='newPsw' placeholder='nuova password'>
        <input type='password' name='copyNewPsw' placeholder='reinserisci nuova password'>
        <input type='hidden' name='isUser'>
        <input type='submit' />
      </form>
      <button onclick='back()'>annulla</button>
    </div>

    <script>
    function modifyPsw(){
      document.getElementById('modPBtn').style='display:none';
      document.getElementById('modP').style='display:visible';
    }
    function back(){
      document.getElementById('modPBtn').style='display:visible';
      document.getElementById('modP').style='display:none';
    }
    function submit(form) {
      form.submit();
    }
    </script>
    </body>
</html>


<?php
  //riceve messaggi esito operazioni
  if (isset($_POST['response'])){
    if (isset($_POST['result']) && $_POST['result'] == 'failure')
      echo "<script type='text/javascript'>modifyPsw()</script>";
    if (isset($_POST['msg']))
      echo "<script type='text/javascript'>alert('".$_POST['msg']."');</script>";
  }

?>
