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
		  <?php echo "<li><form action='setting.php'><input class='settingImg' type='image' src='resources/img/setting.png'></form></li>"; ?>
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
                    foreach ($l as $key => $value){
                      echo "<script>console.log('".($value)."')</script>";
                      if ($value === $tmp['Id_Corso'])
                        $str = $str."<option selected='selected' value='".$value."'>".$value."</option> ";
                      else
                        $str = $str."<option value='".$value."'>".$value."</option> ";
                      }
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
                      <p>Aggiornamento 1<input type='date' name='Agg1' placeholder='Aggiornamento' value='".$tmp['Agg1']."' class='mod-date'></p><br>
                      <p>Aggiornamento 2<input type='date' name='Agg2' placeholder='Aggiornamento' value='".$tmp['Agg2']."' class='mod-date'></p><br>
                      <input type='text' name='Protocollo' placeholder='Protocollo' value='".$tmp['Protocollo']."' class='nametxt'><br>
                      <p>Data Protocollo<input type='date' name='DateProtocollo' placeholder='Data Protocollo' value='".$tmp['DateProtocollo']."' class='mod-date'></p><br>
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
    <script>
    setAll();
    function setAll(){
      var all = document.getElementsByTagName('input');
      for (n of all)
        if(n.type == 'date')
          n.max = today();
    }
    function today(){
      var D = new Date(),
          m = '' + (D.getMonth() + 1),
          d = '' + D.getDate(),
          y = '' + D.getFullYear();
      if (m.length < 2) m = '0' + m;
      if (d.length < 2) d = '0' + d;
      var today = [y, m, d].join('-');
      return today;
    }
    </script>
</body>
</html>

<?php
  //riceve messaggi esito operazioni
  if (isset($back))
    echo "<script type='text/javascript'>alert('".$back['result']."');</script>";
?>
