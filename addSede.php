<?php
  //verifica il login
  require_once('dbHandler.php');
  $u = new UserHandler();
  $u->verifySession();

  //ulteriore controllo sul livello di Accesso
  if ($u->getAcLv() < 1) {
    echo "<script type='text/javascript'>alert('Livello di accesso non valido');</script>";
    require_once("Redirect.php");
    goToDataView();
  }

  //esito aggiunta corso/Formatore
  if (isset($_POST['response'])) {
    $back = unserialize($_POST['msg']);
    $tmp = $back['tmp'];
  }
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
        <li><a href="addPersonale.php">Aggiungi personale</a></li>
        <li><a href="addCorso.php">Aggiungi corso</a></li>
      </ul>
        <ul class="logout-bar">
          <li>
            <!-- Logout -->
            <a href="LogoutResponse.php">Logout</a>
          </li>
		  <?php echo "<li><form action='setting.php'><input class='settingImg' type='image' src='resources/img/setting.png'></form></li>"; ?>
        </ul>
    </div>

    <div class="row">
        <div class="col-12 container">
            <div>
                <div>
                  <?php
                    // TODO: metter il bottone 'addFormatoreBtn' a lato della select
                    echo "<form action='addSedeResponse.php' method='post' id='addSform'>
                              <input type='text' name='sede' placeholder='Nome Sede' value='".$tmp['sede']."' class='coursetxt'>
                              </div>".
                                '</div>
                                <input type="submit" name="submit" value="Aggiungi Sede" class="addsede">
                              </form>';
                  ?>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

<?php
  //riceve messaggi esito operazioni
  $back = $_POST['msg'];
  if (isset($back))
    echo "<script type='text/javascript'>alert('".$back."');</script>";
?>
