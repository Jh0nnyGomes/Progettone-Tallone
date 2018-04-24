  <?php
  include("dbHandler.php");
  $u = new UserHandler();
  $u->logout();
  ob_start();
  header("Location:login.php");
  ob_end_flush();
  die();
  ?>
