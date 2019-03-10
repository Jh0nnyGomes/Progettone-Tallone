<?php
  include_once('dbHandler.php');
  $u = new UserHandler();
  $u->verifySession();

  if ($u->getAcLv() != 1) {
    echo "<script type='text/javascript'>alert('Livello di accesso non valido');</script>";
    require_once("Redirect.php");
    goToDataView();
  }

  $ad_handler = new AdminHandler();
  $scope = $_POST['scope'];
  if (isset($scope))
    switch ($scope) {
      case 'reset':
        $result = $ad_handler->resetUserPsw($_POST['Id'], $_POST['usrn']);
        $msg = $result ? "Password resettata con successo" : "operazione fallita";
        break;

      case 'delete':
        $result = $ad_handler->deleteUser($_POST['Id']);
        $msg = $result ? "Account eliminato con successo" : "operazione fallita";
        break;

      case 'newUser':
        $result = $ad_handler->newUser($_POST['usrn']);
        if (!$result){
          $msg = "operazione fallita";
          $back = ["usrn" => $_POST['usrn']];
        }
        else $msg = "Account aggiunto con successo";
        break;

      case 'update':
        $result = $ad_handler->updateUser($_POST['Id'], $_POST['usrn']);
        if (!$result){
          $msg = "operazione fallita";
          $back = ["id" => $_POST['Id']];
        }
        else $msg = "Username modificato con successo";
        break;
    }
?>

<form id='response' action='setting.php' method='POST'>
  <input type='hidden' name='response' value='<?php echo $scope; ?>'>
  <input type='hidden' name='result' value='<?php echo (!$result) ? "failure" : "success"; ?>'>
  <input type='hidden' name='back' value='<?php echo isset($back) ? serialize($back) : null; ?>'>
  <input type='hidden' name='msg' value='<?php echo $msg; ?>'>
</form>
<script type='text/javascript'>
  document.getElementById('response').submit();
</script>
