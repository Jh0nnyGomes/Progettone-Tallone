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

                    //esito dell'operazione precedente->solo se c'è il redirect a questa pagina dal response
                    if (isset($_POST['response'])) {
                      $back = unserialize($_POST['msg']);
                      $tmp = $back['tmp'];
                    }

                    //tart form
                    $str = '<form action="addPersonaleResponse.php" method="post">
                      <input type="text" name="Nome" placeholder="Nome" value="'.$tmp['Nome'].'" class="nametxt">
                      <input type="text" name="Cognome" placeholder="Cognome" value="'.$tmp['Cognome'].'" class="nametxt">
                      <input type="text" name="CF" placeholder="Codice fiscale" value="'.$tmp['CF'].'" class="cftxt">
                      <input type="date" name="DataNascita" placeholder="Data di nascita" value="'.$tmp['DataNascita'].'" class="borndate">
                      <input type="text" name="ComuneNascita" placeholder="Luogo di nascita" value="'.$tmp['ComuneNascita'].'" class="borntxt">
                      <br>';

                    //lista corsi
                    $i = new InsertHandler();
                    $l = $i->getCorsi();
                    $str = $str."<select name='Id_Corso' class='coursecmb'> <option value='' disabled selected>Corso</option> ";
                    foreach ($l as $key => $value)
                      if ($value == $tmp['Id_Corso'] && isset($value))
                        $str = $str."<option selected='selected' value='".$value."'>".$value."</option> ";
                      else
                        $str = $str."<option value='".$value."'>".$value."</option> ";
                    $str = $str."</select> ";

                    //lista sedi
                    $l = $i->getSedi();
                    $str = $str." <select name='Id_Sede'class='sedecmb'> <option value='' disabled selected>Sede</option> ";
                    foreach ($l as $key => $value)
                    if ($value['id'] == $tmp['Id_Sede'])
                      $str = $str." <option selected='selected' value='".$value['id']."'>".$value['Nome']."</option> ";
                    else
                      $str = $str." <option value='".$value['id']."'>".$value['Nome']."</option> ";

                    //end form
                    $str = $str." </select> <input type='text' name='Ore' placeholder='Ore' value='".$tmp['Ore']."' class='hourtxt'><br>
                      <p>Mod 1<input type='date' name='Mod1' placeholder='Mod1' value='".$tmp['Mod1']."' class='mod-date'><br>
                      <p>Mod 2<input type='date' name='Mod2' placeholder='Mod2' value='".$tmp['Mod2']."' class='mod-date'><br>
                      <p>Mod 3<input type='date' name='Mod3' placeholder='Mod3' value='".$tmp['Mod3']."' class='mod-date'><br>
                      <p>Aggiornamento 1<input type='date' name='Agg1' placeholder='Aggiornamento' value='".$tmp['Agg1']."' class='mod-date'></p><br>
                      <p>Aggiornamento 2<input type='date' name='Agg2' placeholder='Aggiornamento' value='".$tmp['Agg2']."' class='mod-date'></p><br>
                      <input type='text' name='Protocollo' placeholder='Protocollo' value='".$tmp['Protocollo']."' class='nametxt'><br>
                      <input type='submit' name='submit' value='Aggiungi' class='addstaff'>
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
