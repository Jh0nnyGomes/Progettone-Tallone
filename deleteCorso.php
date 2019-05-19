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

  //cancella il record e ritorna l'esito
  $d = new InsertHandler();
  $result = $d->deleteCorso($id);
  if ($result['deleted'] == 1) {
    $back = 'Corso eliminato';
    $action = "DataView.php?src=$src&pag=$pag";
  } else {
    $back['result'] = 'operazione fallita';
    $back['tmp'] = $_POST;
    $action = "Corso_details.php";
    $back = serialize($back);
  }
/*
  echo "<form id='response' action='$action' method='POST'>
          <input type='hidden' name='response' value='addPerson'>
          <input type='hidden' name='msg' value='$back'>
        </form>
        <script type='text/javascript'>
          document.getElementById('response').submit();
        </script>";*/
?>
