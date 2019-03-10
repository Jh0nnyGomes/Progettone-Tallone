<?php
  //verifica il login
  require_once('dbHandler.php');
  $u = new UserHandler();
  $u->verifySession();
  $ins = new InsertHandler();

  //ulteriore controllo sul livello di Accesso
  if ($u->getAcLv() < 1) {
    echo "<script type='text/javascript'>alert('Livello di accesso non valido');</script>";
    require_once("Redirect.php");
    goToDataView();
  }

  //preleva dati POST
  $src = $_POST['src'];
  $pag = $_POST['pag'];
  $newId = $_POST['Id_Corso'];
  $oldId = $_POST['oldId'];
  $idsFormatori = [];
  foreach ($_POST as $key => $value) {
    if (0 === strpos($key, "IdFormatore")){
      $id = substr($key, strpos($key, ':') + 1); //l'id e' dopo i due punti
      array_push($idsFormatori, $id);
    }
  }

  //inserisce i dati e ritorna l'esito per POST
  $back = [];
  $result = $ins->updateCorso($oldId, $newId, $idsFormatori);
  $back['tmp'] = $_POST;
  if ($result){
    $back['result'] = 'Corso modificato con successo';
    $action = "Corso_details.php";
  } else {
    $back['result'] = 'operazione fallita';
    $action = "updateCorso.php";
  }

  echo "<form id='response' action='$action' method='POST'>
          <input type='hidden' name='response' value='updateC'>
          <input type='hidden' name='msg' value='".serialize($back)."'>
        </form>
        <script type='text/javascript'>
          document.getElementById('response').submit();
        </script>";
?>
