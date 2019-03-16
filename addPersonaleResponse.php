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
  $nome = $_POST["Nome"];
  $cognome = $_POST["Cognome"];
  $cf = $_POST["CF"];
  $dataNascita = $_POST["DataNascita"];
  $luogoNascita = $_POST["ComuneNascita"];
  $idCorso = $_POST["Id_Corso"];
  $idSede = $_POST["Id_Sede"];
  $ore = (int)$_POST["Ore"];
  $dateCorso = ['Mod1'=>$_POST["Mod1"], 'Mod2'=>$_POST["Mod2"], 'Mod3'=>$_POST["Mod3"], 'Agg1'=>$_POST["Agg1"], 'Agg2'=>$_POST["Agg2"], 'DateProtocollo'=>$_POST['DateProtocollo']];
  $protocollo = $_POST["Protocollo"];

  //inserisce i dati e ritorna l'esito per POST
  $h = new InsertHandler();
  $back = [];
  $result = $h->addPerson($nome, $cognome, $dataNascita, $cf, $luogoNascita, $dateCorso, $ore, $idCorso, $idSede, $protocollo);

  //se trova errori ritorna il post con i dati precedentemente inseriti
  foreach ($result as $key => $value) {
    if ($value){
      $back['tmp'] = $_POST;
      break;
    }
  }
  $back['result'] = $h->codifyError($result);

  echo "<form id='response' action='addPersonale.php' method='POST'>
          <input type='hidden' name='response' value='addPerson'>
          <input type='hidden' name='msg' value='".serialize($back)."'>
        </form>
        <script type='text/javascript'>
          document.getElementById('response').submit();
        </script>";
?>
