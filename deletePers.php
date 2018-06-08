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
  $idPers = $_POST["Id"];
  $src = $_POST["src"];
  $pag = $_POST["pag"];

  //cancella il record e ritorna l'esito
  $d = new InsertHandler();
  $result = $d->deletePersonale($idPers);
  $msg = ($result['deleted'] == 1) ? 'Record eliminato con successo' : 'operazione fallita';
  
  echo "<form id='response' action='DataView.php?pag=$pag&src=$src' method='POST'>
          <input type='hidden' name='response' value='deleteP'>
          <input type='hidden' name='msg' value='$msg'>
        </form>
        <script type='text/javascript'>
          document.getElementById('response').submit();
        </script>";
?>
