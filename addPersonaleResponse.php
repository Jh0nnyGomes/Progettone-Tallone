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
  $nome = $_POST["nome"];
  $cognome = $_POST["cognome"];
  $cf = $_POST["cf"];
  $dataNascita = $_POST["data"];
  $luogoNascita = $_POST["pNascita"];
  $idCorso = $_POST["idCorso"];
  $idSede = $_POST["idSede"];
  $ore = (int)$_POST["ore"];
  $dateCorso = ['Mod1'=>$_POST["mod1"], 'Mod2'=>$_POST["mod2"], 'Mod3'=>$_POST["mod3"], 'Aggiornamento'=>$_POST["agg"]];

  //inserisce i dati e ritorna l'esito per POST
  $h = new InsertHandler();
  $back = [];
  $result = $h->addPerson($nome, $cognome, $dataNascita, $cf, $luogoNascita, $dateCorso, $ore, $idCorso, $idSede);

  //se trova errori ritorna il post con i dati precedentemente inseriti
  foreach ($result as $key => $value) {
    if ($value){
      $back['tmp'] = $_POST;
      break;
    }
  }
  $back['result'] = $h->codifyError($result);

  echo "<form id='response' action='addPersonale.php' method='POST'>
          <input type='hidden' name='response' value='".serialize($back)."'>
        </form>";
 ?>
 <script type="text/javascript">
   document.getElementById('response').submit();
 </script>
