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
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>
  <div class="">
      <div class="">
          <ul class="">
              <!-- DEBUG Home: TODO collegare alla pagina principale della scuola-->
              <li>
                  <a href="index.html">Home</a>
              </li>
              <!-- inserimento per moderator ed administrator -->
              <?php
              require_once('dbHandler.php');
              $u = new UserHandler();
              $lv = $u->getAcLv();
              //Moderator & admin
              if ($lv > 1)
                echo '<li><a href="addCorso.php">Aggiungi corso</a></li>';
              ?>
              <!-- SearchBar -->
              <form action="DataView.php" method="get">
                  <input type="text" name="src" placeholder="Cerca" value="<?php echo (isset($_GET['src']))?$_GET['src']:''; ?>" class="searchbox" />
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
                    require_once("dbHandler.php");

                    //alert con l'esito dell'operazione precedente->solo se c'è il redirect a questa pagina dal response
                    $resp = unserialize($_POST['response']);
                    $temp = [];
                    if(isset($resp))
                      if (isset($resp['result']))
                        echo "<script type='text/javascript'>alert('".$resp['result']."');</script>";

                    //tart form
                    $str = '<form action="addPersonaleResponse.php" method="post">
                      <input type="text" name="nome" placeholder="Nome" value="'.$resp['tmp']['nome'].'" class="">
                      <input type="text" name="cognome" placeholder="Cognome" value="'.$resp['tmp']['cognome'].'" class="">
                      <input type="text" name="cf" placeholder="Codice fiscale" value="'.$resp['tmp']['cf'].'" class="">
                      <input type="date" name="data" placeholder="Data di nascita" value="'.$resp['tmp']['data'].'" class="">
                      <input type="text" name="pNascita" placeholder="Luogo di nascita" value="'.$resp['tmp']['pNascita'].'" class="">
                      <br>';

                    //lista corsi
                    $i = new InsertHandler();
                    $l = $i->getCorsi();
                    $str = $str."<select name='idCorso'> <option value='' disabled selected>Corso</option> ";
                    foreach ($l as $key => $value)
                      if ($value == $resp['tmp']['idCorso'] && isset($value))
                        $str = $str."<option selected='selected' value='".$value."'>".$value."</option> ";
                      else
                        $str = $str."<option value='".$value."'>".$value."</option> ";
                    $str = $str."</select> ";

                    //lista sedi
                    $l = $i->getSedi();
                    $str = $str." <select name='idSede'> <option value='' disabled selected>Sede</option> ";
                    foreach ($l as $key => $value)
                    if ($value['id'] == $resp['tmp']['idSede'])
                      $str = $str." <option selected='selected' value='".$value['id']."'>".$value['Nome']."</option> ";
                    else
                      $str = $str." <option value='".$value['id']."'>".$value['Nome']."</option> ";

                    //end form
                    $str = $str." </select> <input type='text' name='ore' placeholder='Ore' value='".$resp['tmp']['ore']."' class=''>
                      <input type='date' name='mod1' placeholder='Mod1' value='".$resp['tmp']['mod1']."' class=''>
                      <input type='date' name='mod2' placeholder='Mod2' value='".$resp['tmp']['mod2']."' class=''>
                      <input type='date' name='mod3' placeholder='Mod3' value='".$resp['tmp']['mod3']."' class=''>
                      <input type='date' name='agg' placeholder='Aggiornamento' value='".$resp['tmp']['agg']."' class=''>
                      <br>
                      <input type='submit' name='submit' value='Aggiungi' class=''>
                    </form>";

                    echo $str;
                  ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
