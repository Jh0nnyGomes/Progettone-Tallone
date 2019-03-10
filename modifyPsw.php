<?php
  //verifica il login
  require_once('dbHandler.php');
  $u = new UserHandler();
  $u->verifySession();

  $error;
  //controlla che le password combacino
  if ($_POST['newPsw'] !== $_POST['copyNewPsw'])
    $error = "Le password non combaciano";
  //tenta di cambiare la password
  if (!isset($error)){
    include_once('dbHandler.php');
    $ad_handler = new AdminHandler();
    $s = new UserHandler();
    $s->sessionSafeStart(); //per l'id
    if(!$ad_handler->modifyPsw($_POST['oldPsw'], $_POST['newPsw'], $_SESSION['username']))
      $error = 'operazione fallita';
  }

  $msg = isset($error) ? $error : 'operazione completata con successo';
  $resut = isset($error) ? "failure" : "success";
  $page = isset($_POST['isUser']) ? 'userSettings.php' : 'setting.php';
?>

<form id='response' action='<?php echo $page ?>' method='POST'>
  <input type='hidden' name='response' value='modPsw'>
  <input type='hidden' name='result' value='<?php echo $resut ?>'>
  <input type='hidden' name='msg' value='<?php echo $msg; ?>'>
</form>
<script type='text/javascript'>
  document.getElementById('response').submit();
</script>
