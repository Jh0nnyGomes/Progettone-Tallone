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
                    $str = "<form action='addCorsoResponse.php' method='post' id='addCform'>
                              <input type='text' name='IdCorso' placeholder='Corso' value='".$tmp['IdCorso']."' class='coursetxt'>
                              <div class='multiselect'>
                                <div class='selectBox' onclick='showList()'>
                                  <select id='select' >
                                    <option>Formatori</option>
                                  </select>
                                  <div class='overSelect'></div>
                                </div>
                                <div id='checkboxes' class='hidden'>";

                    //aggiunge le checkbox
                    $i = new InsertHandler();
                    $l = $i->getFormatori();
                    $c = 0; //id a.i. per i nomi delle checkbox
                    foreach ($l as $key => $value){
                      $str = $str." <label><input type='checkbox' name='IdFormatore_$c:$value[0]' class='i' ";
                      //check delle checkbox precedentemente inviate
                      if (isset($tmp))
                        foreach ($tmp as $k => $v)
                          if ($k == "IdFormatore_$c:".$value[0])
                            $str = $str."checked";

                      $str = $str." >$value[1]</label>";
                      $c++;
                    }

                    //form  per l'aggiunta dei Formatori
                    $str = $str.'</div>
                                </div>
                                <input type="submit" name="submit" value="Aggiungi Corso" class="addcourse">
                              </form>
                              <input id="addFormatoriBtn" type="button" onclick="addFormatore()" value="Aggiungi Formatore" class="addtrainer">
                              <div id="addFormatori" style="display:none">
                                <form id="addFormatoriForm" action="addFormatoreResponse.php" method="post">
                                  <input type="text" name="cognome" placeholder="Cognome Formatore" value="'.$tmp['cognome'].'" class="trainertxt">
                                  <input type="submit" value="Aggiungi Formatore" class="trainerbtn">
                                </form>
                              </div>';

                    echo $str;
                  ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    var addingFormatore = false;

    function showList(){
        document.getElementById("checkboxes").classList.toggle("show");
    }

    function addFormatore(){
      var btn = document.getElementById("addFormatoriBtn");
      var form = document.getElementById("addFormatori");
      if (!addingFormatore){
        form.style.display = 'block';
        btn.value = " Annulla ";
        grayer("addCform", true);
        addingFormatore = true;
      } else {
        form.style.display = 'none';
        btn.value = " Aggiungi Formatore ";
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
