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

  //preleva dati POST
  $idCorso = $_POST["IdCorso"];
  $idsFormatori = [];
  foreach ($_POST as $key => $value) {
    if (0 === strpos($key, "IdFormatore")){
      $id = substr($key, strpos($key, ':') + 1); //l'id e' dopo i due punti
      array_push($idsFormatori, $id);
    }
  }

  //inserisce i dati e ritorna gli errori
  $h = new InsertHandler();
  $back = [];
  $result = $h->addCorso($idCorso, $idsFormatori);

  //se trova errori ritorna il post con i dati precedentemente inseriti
  foreach ($result as $key => $value) {
    if ($value){
      $back['tmp'] = $_POST;
      break;
    }
  }
  $back['result'] = $h->codifyError($result);

  echo "<form id='response' action='addCorso.php' method='POST'>
          <input type='hidden' name='response' value='".serialize($back)."'>
        </form>";
 ?>
 <script type="text/javascript">
   document.getElementById('response').submit();
 </script>
