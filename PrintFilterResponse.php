<?php
  //verifica il login
  require_once('dbHandler.php');
  $u = new UserHandler();
  $u->verifySession();
  $pt_handler= new printHandler();

  //preleva dati POST
  if (isset($_POST['id'])){ //stampa singola ->arriva dal dataview
    $filepath = $pt_handler->saveSelect([$_POST['id']], "Certificato");
    if (isset($_POST['scope']) && $_POST['scope'] == 'download')
      $result = $pt_handler->download($filepath, "certificato");
    echo "<form id='response' action='DataView.php' method='POST'>
            <input type='hidden' name='response' value='downloadOrPrint'>
            <input type='hidden' name='msg' value='$filepath'>
            <input type='hidden' name='src' value='$src'>
            <input type='hidden' name='pag' value='$pag'>
          </form>
          <script type='text/javascript'>
            document.getElementById('response').submit();
          </script>";
   } else {  //arriva da prtintfilter
    $param = json_decode($_POST['ids'], true);
    if (count($param) > 0){
      $filepath = $pt_handler->saveSelect($param, "Certificato");
    }
      echo "<form id='response' action='PrintFilter.php' method='POST'>
              <input type='hidden' name='response' value='downloadOrPrint'>
              <input type='hidden' name='msg' value='$result'>
            </form>
            <script type='text/javascript'>
              document.getElementById('response').submit();
            </script>";
    }

?>
