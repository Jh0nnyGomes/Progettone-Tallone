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
  $id = $_POST["Id_Corso"];
  $src = $_POST["src"];
  $pag = $_POST["pag"];
  $from = $_POST["from"];

  //cancella il record e ritorna l'esito
  $d = new InsertHandler();
  $result = $d->deleteCorso($id);
  if ($result['deleted'] == 1) {
    $back["result"] = 'Corso eliminato';
    if ($from == 'DataView.php')
      $action = "DataView.php?src=$src&pag=$pag";
    else
      $action = "addCorso.php";
  } else {
    $back['result'] = 'operazione fallita';
    $back['tmp'] = $_POST;
    if ($from == 'DataView.php')
      $action = "Corso_details.php";
    else
      $action = "addCorso.php";
  }

  echo "<form id='response' action='$action' method='POST'>
          <input type='hidden' name='response' value='addPerson'>
          <input type='hidden' name='msg' value='".serialize($back)."'>
        </form>
        <script type='text/javascript'>
          document.getElementById('response').submit();
        </script>";
?>
