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
  $id = (int)$_POST['Id'];
  $param_p['Nome'] = $_POST["Nome"];
  $param_p['Cognome'] = $_POST["Cognome"];
  $param_p['CF'] = $_POST["CF"];
  $param_p['DataNascita'] = $ins->isDate($_POST["DataNascita"]) ? $_POST["DataNascita"] : null;
  $param_p['ComuneNascita'] = $_POST["ComuneNascita"];
  $param_c['Id_Corso'] = $_POST["Id_Corso"];
  $param_c['Id_Sede'] = $_POST["Id_Sede"];
  $param_c['Ore'] = (int)$_POST["Ore"];
  $param_c['Mod1'] = $ins->isDate($_POST["Mod1"]) ? $_POST["Mod1"] : null;
  $param_c['Mod2'] = $ins->isDate($_POST["Mod2"]) ? $_POST["Mod2"] : null;
  $param_c['Mod3'] = $ins->isDate($_POST["Mod3"]) ? $_POST["Mod3"] : null;
  $param_c['Agg1'] = $ins->isDate($_POST["Agg1"]) ? $_POST["Agg1"] : null;
  $param_c['Agg2'] = $ins->isDate($_POST["Agg2"]) ? $_POST["Agg2"] : null;
  $param_c['DateProtocollo'] = $ins->isDate($_POST["DateProtocollo"]) ? $_POST["DateProtocollo"] : null;
  $param_c['Protocollo'] = $_POST["Protocollo"];

  //controlla i campi ed evita di inviare campi non modificati
  $p = $ins->getPersona($id);

  $paramCorso = [];
  $paramPersona = [];

  foreach ($param_c as $key => $value)
    if ($value != $p[$key])
      $paramCorso[$key] = $value;

  foreach ($param_p as $key => $value)
    if ($value != $p[$key])
      $paramPersona[$key] = $value;

  //inserisce i dati e ritorna l'esito per POST
  $back = [];
  $result = $ins->updatePersonale($id, $paramPersona, $paramCorso);
  if ($result){
    $back = 'Record modificato con successo';
    $action = "DataView.php?src=$src&pag=$pag";
  } else {
    $back['result'] = 'operazione fallita';
    $back['tmp'] = $_POST;
    $action = "updatePers.php";
    $back = serialize($back);
  }

  echo "<form id='response' action='$action' method='POST'>
          <input type='hidden' name='response' value='addPerson'>
          <input type='hidden' name='msg' value='$back'>
        </form>
        <script type='text/javascript'>
          document.getElementById('response').submit();
        </script>";
?>
