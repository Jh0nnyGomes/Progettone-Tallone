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
  $sede = $_POST["sede"];
  if ($sede == '')
    $back = 'inserisci un nome';
  else{
    //inserisce i dati e ritorna gli errori
    $h = new InsertHandler();
    $back = $h->addSede($sede);
  }

  echo "<form id='response' action='addSede.php' method='POST'>
          <input type='hidden' name='response' value='addCorso'>
          <input type='hidden' name='msg' value='$back'>
        </form>
        <script type='text/javascript'>
          document.getElementById('response').submit();
        </script>";
?>
