<?php
  //verifica il login
  require_once('dbHandler.php');
  $u = new UserHandler();
  $d = new DetailsHandler();
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
    $tmp = $d->getCorsoDetails($back['tmp']['Id_Corso']);
    $tmp['Id_Corso'] = $back['tmp']['Id_Corso'];
    $src = $tmp['src'];
    $pag = $tmp['pag'];
  } else if (isset($_POST['updateC'])){
    $tmp = $d->getCorsoDetails($_POST['Id_Corso']);// TODO: PERCHE` NON FUNXIONA
    $tmp['Id_Corso'] = $_POST['Id_Corso'];
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
              <li>
                <a href="DataView.php">Tabella</a>
              </li>
              <li>
                <a href="PrintFilter.php">Stampa</a>
              </li>
              <!-- inserimento per moderator ed administrator -->
              <li>
                <a href="addPersonale.php">Aggiungi personale</a>
              </li>
              <li>
                <a href="addCorso.php">Aggiungi corso</a>
              </li>
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
                    $str = "<form action='updateCorsoResponse.php' method='post'>
                              <input type='hidden' name='src' value='$src'>
                              <input type='hidden' name='pag' value='$pag'>
                              <input type='hidden' name='oldId' value='".$_POST['Id_Corso']."'>
                              <input type='text' name='Id_Corso' placeholder='Corso' value='".$tmp['Id_Corso']."' class=''>
                              <div class='multiselect'>
                                <div class='selectBox'>
                                  <select id='select'>
                                    <option>Formatori</option>
                                  </select>
                                  <div class='overSelect'></div>
                                </div>
                                <div id='checkboxes'>";

                    //aggiunge le checkbox
                    $i = new InsertHandler();
                    $l = $i->getFormatori();
                    $c = 0;
                    foreach ($l as $key => $value){
                      $str = $str." <label id='$value[0]L' for='$value[0]' class='a'> <input type='checkbox' name='IdFormatore_$c:$value[0]' class='i' ";
                      //check delle checkbox precedentemente inviate
                      if (isset($tmp))
                        foreach ($tmp['Formatori'] as $k => $v)
                          if ($v == $value['Cognome'])
                            $str = $str."checked";

                      $str = $str." >$value[1]</label>";
                      $c++;
                    }

                    //form  per l'aggiunta dei Formatori
                    $str = $str.'</div>
                                </div>
                                <input type="submit" name="submit" value="modifica Corso">
                              </form>
                              <input id="addFormatoriBtn" type="button" onclick="addFormatore()" value="+">
                              <div id="addFormatori" style="display:none">
                                <form id="addFormatoriForm" action="addFormatoreResponse.php" method="post">
                                  <input type="text" name="cognome" placeholder="Cognome Formatore" value="'.$tmp['cognome'].'" class="">
                                  <input type="submit" value="aggiungi formatore">
                                </form>
                              </div>';

                    //form goback
                    $str = $str.'<form action="Corso_details.php" method="POST">
                                <input type="hidden" name="Id_Corso" value="'.$tmp['Id_Corso'].'">
                                <input type="hidden" name="src" value="'.$tmp['src'].'">
                                <input type="hidden" name="pag" value="'.$tmp['pag'].'">
                                <input type="submit" value="Annulla">
                              </form>';
                    echo $str;
                  ?>
                </div>
            </div>
        </div>
    </div><script>
    var addingFormatore = false;

    function showList(event) {
      if (!event.target.matches('.overSelect, .multiselect, .checkboxes, .selectBox, .a, .i'))
        document.getElementById("checkboxes").style.display = "none";
      else
        document.getElementById("checkboxes").style.display = "block";
    }
    document.body.addEventListener('click', showList);

    function addFormatore(){
      var btn = document.getElementById("addFormatoriBtn");
      var form = document.getElementById("addFormatori");
      if (!addingFormatore){
        form.style.display = 'block';
        btn.value = " - ";
        grayer("addCform", true);
        addingFormatore = true;
      } else {
        form.style.display = 'none';
        btn.value = " + ";
        grayer("addCform", false);
        addingFormatore = false;
      }
    }

    function grayer(formId, yesNo) {
       var f = document.getElementById(formId), s, opacity;
       s = f.style;
       opacity = yesNo? '40' : '100';
       s.opacity = s.MozOpacity = s.KhtmlOpacity = opacity/100;
       s.filter = 'alpha(opacity='+opacity+')';
       for(var i=0; i<f.length; i++) f[i].disabled = yesNo;
    }
    </script>
</body>
</html>

<?php
  //riceve messaggi esito operazioni
  if (isset($back))
    echo "<script type='text/javascript'>alert('".$back['result']."');</script>";
?>
