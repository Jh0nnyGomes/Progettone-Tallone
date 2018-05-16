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

  $nome = $_POST["nome"];
  $cognome = $_POST["cognome"];
  $cf = $_POST["cf"];
  $data = $_POST["data"];
  $luogoNascita = $_POST["pNascita"];

  $dbHandler = new DbHandler();
  
 ?>
