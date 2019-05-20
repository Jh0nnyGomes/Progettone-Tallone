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
  $id = $_POST["Id"];

  //cancella il record e ritorna l'esito
  $back = [];
  $d = new InsertHandler();
  $result = $d->deleteFormatore($id);
  if ($result['deleted'] == 1)
    $back["result"] = 'formatore eliminato';
  else
    $back['result'] = 'operazione fallita';

  $back = serialize($back);

  echo "<form id='response' action='addFormatori.php' method='POST'>
          <input type='hidden' name='response' value='deleteFormatore'>          
          <input type='hidden' name='msg' value='$back'>
        </form>
        <script type='text/javascript'>
          document.getElementById('response').submit();
        </script>";
?>
