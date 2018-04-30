  <?php
  require_once("dbHandler.php");
  require_once("Redirect.php");
  $u = new UserHandler();
  $u->logout();
  goToLogin();
  ?>
