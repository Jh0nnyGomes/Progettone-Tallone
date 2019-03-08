<?php
  //verifica il login
  require_once('dbHandler.php');
  $u = new UserHandler();
  $u->verifySession();
  $pt_handler= new printHandler();

  //preleva dati POST, verifica che i record non siano già stati selezionati se no vengono filtrati in funzione dei parametri passati
  $param = json_decode($_POST['ids'], true);
  if (count($param) > 0){
    $filepath = $pt_handler->saveSelect($param, "Certificato");
    if ($_POST["scope"] == 'save'){
      //echo $filepath;
      $result = $pt_handler->download($filepath, "certificato");
    }
    else if ($_POST["scope"] == 'print'){
      /* TODO:
      $app= new COM("Word.Application");
      $app->visible = true;
      $app->Documents->Open($filepath);
      $app->ActiveDocument->PrintOut();
      $app->ActiveDocument->Close();
      $app->Quit();*/
    }
  }
/*
  echo "<form id='response' action='PrintFilter.php' method='POST'>
          <input type='hidden' name='response' value='addPerson'>
          <input type='hidden' name='msg' value='$file'>
        </form>
        <script type='text/javascript'>
          document.getElementById('response').submit();
        </script>";*/
?>
