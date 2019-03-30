<?php
  //verifica il login
  require_once('dbHandler.php');
  $u = new UserHandler();
  $u->verifySession();
  $lv = $u->getAcLv();

  //preleva dati POST
  if (isset($_POST['response'])){
    $back = unserialize($_POST["msg"]);
    $tmp = $back['tmp'];
    $src = $tmp['src'];
    $pag = $tmp['pag'];
    $corsoId = $tmp['Id_Corso'];
  } else {
    $corsoId = $_POST["Id_Corso"];
    $src = $_POST['src'];
    $pag = $_POST['pag'];
  }

  //gestisce i dati di questa pagina
  $d = new DetailsHandler();
?>

<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
    <div class="col-12">
      <div class="navbar">
        <ul class="navbar-list">
          <li>
            <a href="https://www.isisbisuschio.gov.it/">Home</a>
          </li>
          <li>
            <a href="DataView.php">Tabella</a>
          </li>
          <li>
            <a href="PrintFilter.php">Stampa</a>
          </li>
          <!-- inserimento per moderator ed administrator -->
          <?php
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
		  <?php echo "<li><form action='setting.php'><input class='settingImg' type='image' src='resources/img/setting.png'></form></li>"; ?>
        </ul>
      </div>

    <div class="col-12 container">
      <?php
        $result = $d->getCorsoDetails($corsoId);
        $str = "Corso:$corsoId.<br>
                Sede: ".$result["Sede"]."<br>
                Formatori<br>
                <ul>";
        foreach ($result["Formatori"] as $key => $value)
          $str = $str."<li>$value</li>";
        $str = $str."</ul>
              <form action='DataView.php?pag=$pag&src=$src' method='POST'>
                <input type='submit' name='submit' value='Torna alla tabella'>
              </form>";
        //aggunge le pagine di modifica / eliminazione per gli admin
        if ($lv > 0){
          //sistema $result per passare i dati del corso in un oggetto solo
          $result['Id_Corso'] = $corsoId;
          $str = $str."
                <form id='deleteForm' onsubmit='return del(this)' method='POST'>
                  <input type='hidden' name='src' value='$src'>
                  <input type='hidden' name='pag' value='$pag'>
                  <input type='hidden' name='Id_Corso' value='$corsoId'>
                  <input type='submit' name='deleteC' value='Cancella Corso'>
                </form>
                <form action='updateCorso.php' method='POST'>
                  <input type='hidden' name='src' value='$src'>
                  <input type='hidden' name='pag' value='$pag'>
                  <input type='hidden' name='Id_Corso' value='$corsoId'>
                  <input type='submit' name='updateC' value='Modifica Corso'>
                </form>";
        }
        echo $str;
      ?>
    </div>
  </body>
  <script>
    function del(form){
      if(confirm("Cancellare il record?"))
        form.action='deleteCorso.php';
       else return false;
      return true;
    }
  </script>
</html>

<?php
  //response
  if (isset($back))
    echo "<script type='text/javascript'>alert('".$back['result']."');</script>";
?>
