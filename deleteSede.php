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
  $id = $_POST["Id_Sede"];

  //cancella il record e ritorna l'esito
  $d = new InsertHandler();
  $result = $d->deleteSede($id);
  if ($result['deleted'] == 1)
    $back = 'Sede eliminata';

  else
    $back = 'operazione fallita';

  echo "<form id='response' action='addSede.php' method='POST'>
          <input type='hidden' name='response' value='addPerson'>
          <input type='hidden' name='msg' value='$back'>
        </form>
        <script type='text/javascript'>
          document.getElementById('response').submit();
        </script>";
?>
