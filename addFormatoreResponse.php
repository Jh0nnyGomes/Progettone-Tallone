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
  $cognome = $_POST["cognome"];
  $from = $_POST["from"];
  //inserisce i dati e ritorna gli errori
  $h = new InsertHandler();
  $back = [];
  $result = $h->addFormatore($cognome);

  //se trova errori ritorna il post con i dati precedentemente inseriti
  foreach ($result as $key => $value) {
    if ($value){
      $back['tmp'] = $_POST;
      break;
    }
  }
  $back['result'] = $h->codifyError($result);

  echo "<form id='response' action='$from' method='POST'>
          <input type='hidden' name='response' value='addFormatore'>
          <input type='hidden' name='msg' value='".serialize($back)."'>
        </form>
        <script type='text/javascript'>
          document.getElementById('response').submit();
        </script>";
?>
