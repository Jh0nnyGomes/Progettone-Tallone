<?php
  //verifica il login
  require_once('dbHandler.php');
  $u = new UserHandler();
  $u->verifySession();
  $lv = $u->getAcLv();
  //ulteriore controllo sul livello di Accesso
  if ($lv < 1) {
    echo "<script type='text/javascript'>alert('Livello di accesso non valido');</script>";
    require_once("Redirect.php");
    goToDataView();
  }

  //esito dell'operazione precedente->solo se c'è il redirect a questa pagina dal response
  if (isset($_POST['response'])) {
    $back = unserialize($_POST['msg']);
    $tmp = $back['tmp'];
    $src = $tmp['src'];
    $pag = $tmp['pag'];
  } else if (isset($_POST['person'])){
    $tmp = unserialize($_POST['person']);
    $src = $_POST['src'];
    $pag = $_POST['pag'];
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
              <!-- inserimento per moderator ed administrator -->
              <?php
              //Moderator & admin
              if ($u->getAcLv() > 1)
                echo '<li><a href="addCorso.php">Aggiungi corso</a></li>';
              ?>
              <!-- SearchBar -->
              <form action="DataView.php" method="get">
                  <input type="text" name="src" placeholder="Cerca" value="<?php echo (isset($_GET['src'])) ? $_GET['src'] : ''; ?>" class="searchbox" />
                  <input type="submit" value="Cerca" class="searchbtn" />
              </form>
          </ul>
          <ul class="logout-bar">
              <!-- Logout -->
              <li>
                  <a href="LogoutResponse.php">Logout</a>
              </li>
          </ul>
      </div>

    <div class="row">
        <div class="col-12 container">
            <div>
                <div>
                  <?php
                    //start form
                    $str = '<form action="updatePersResponse.php" method="post">
                      <input type="hidden" name="Id" value="'.$tmp['Id'].'" class="">
                      <input type="text" name="Nome" placeholder="Nome" value="'.$tmp['Nome'].'" class="">
                      <input type="text" name="Cognome" placeholder="Cognome" value="'.$tmp['Cognome'].'" class="">
                      <input type="text" name="CF" placeholder="Codice fiscale" value="'.$tmp['CF'].'" class="">
                      <input type="date" name="DataNascita" placeholder="Data di nascita" value="'.$tmp['DataNascita'].'" class="">
                      <input type="text" name="ComuneNascita" placeholder="Luogo di nascita" value="'.$tmp['ComuneNascita'].'" class="">
                      <br>';

                    //lista corsi
                    $i = new InsertHandler();
                    $l = $i->getCorsi();
                    $str = $str."<select name='Id_Corso'> <option value='' disabled selected>Corso</option> ";
                    foreach ($l as $key => $value)
                      if ($value == $tmp['Id_Corso'] && isset($value))
                        $str = $str."<option selected='selected' value='".$value."'>".$value."</option> ";
                      else
                        $str = $str."<option value='".$value."'>".$value."</option> ";
                    $str = $str."</select> ";

                    //lista sedi
                    $l = $i->getSedi();
                    $str = $str." <select name='Id_Sede'> <option value='' disabled selected>Sede</option> ";
                    foreach ($l as $key => $value)
                    if ($value['id'] == $tmp['Id_Sede'])
                      $str = $str." <option selected='selected' value='".$value['id']."'>".$value['Nome']."</option> ";
                    else
                      $str = $str." <option value='".$value['id']."'>".$value['Nome']."</option> ";

                    //end form
                    $str = $str." </select> <input type='text' name='Ore' placeholder='Ore' value='".$tmp['Ore']."' class=''>
                      <input type='date' name='Mod1' placeholder='Mod1' value='".$tmp['Mod1']."' class=''>
                      <input type='date' name='Mod2' placeholder='Mod2' value='".$tmp['Mod2']."' class=''>
                      <input type='date' name='Mod3' placeholder='Mod3' value='".$tmp['Mod3']."' class=''>
                      <input type='date' name='Aggiornamento' placeholder='Aggiornamento' value='".$tmp['Aggiornamento']."' class=''>
                      <br>
                      <input type='hidden' name='src' value='$src'>
                      <input type='hidden' name='pag' value='$pag'>
                      <input type='submit' name='submit' value='Modifica' class=''>
                    </form>
                    <form action='DataView.php?pag=$pag&src=$src' method='POST'>
                      <input type='submit' name='submit' value='Torna alla tabella'>
                    </form>";

                    echo $str;
                  ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
  //riceve messaggi esito operazioni
  if (isset($back))
    echo "<script type='text/javascript'>alert('".$back['result']."');</script>";
?>
