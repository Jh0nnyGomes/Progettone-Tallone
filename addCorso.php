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
  <div class="container">
      <div class="navbar">
          <ul class="navbar-list">
              <!-- DEBUG Home: TODO collegare alla pagina principale della scuola-->
              <li>
                  <a href="index.html">Home</a>
              </li>
              <!-- inserimento per moderator ed administrator -->
              <?php
              require_once('dbHandler.php');
              $u = new UserHandler();
              $lv = $u->getAcLv();
              echo '<li><a href="addPersonale.php">Aggiungi personale</a></li>';
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
                    
                    //alert esito aggiunta corso
                    $resp = unserialize($_POST['response']);
                    if(isset($resp)){
                      if (isset($resp['result']))
                        echo "<script type='text/javascript'>alert('".$resp['result']."');</script>";
                    }

                    //alert esito inserimento nuovo formatore
                    $respF = unserialize($_POST['responseF']);
                    if(isset($respF)){
                      if (isset($respF['result']))
                        echo "<script type='text/javascript'>alert('".$respF['result']."');</script>";
                    }

                    // TODO: metter il bottone 'addFormatoreBtn' a lato della select
                    $str = "<form action='addCorsoResponse.php' method='post' id='addCform'>
                              <input type='text' name='IdCorso' placeholder='Corso' value='".$resp['tmp']['IdCorso']."' class=''>
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
                      if (isset($resp['tmp']))
                        foreach ($resp['tmp'] as $k => $v)
                          if ($k == "IdFormatore_$c:".$value[0])
                            $str = $str."checked";

                      $str = $str." >$value[1]</label>";
                      $c++;
                    }

                    //form  per l'aggiunta dei Formatori
                    $str = $str.'</div>
                                </div>
                                <input type="submit" name="submit" value="aggiungi Corso">
                              </form>
                              <input id="addFormatoriBtn" type="button" onclick="addFormatore()" value="+">
                              <div id="addFormatori" style="display:none">
                                <form id="addFormatoriForm" action="addFormatoreResponse.php" method="post">
                                  <input type="text" name="cognome" placeholder="Cognome Formatore" value="'.$respF['tmp']['cognome'].'" class="">
                                  <input type="submit" value="aggiungi formatore">
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
