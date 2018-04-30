<form id="F" action="login.php" method="post">
  <?php
  $usr = $_POST["username"];
  $psw = $_POST["psw"];

  require_once("dbHandler.php");
  $u = new UserHandler();
  $return = $u->login($usr, $psw);
  echo "<input type='hidden' name='response' value='".serialize($return)."'></form>";
  ?>
</form>

<script type="text/javascript">
  //submit dell'esito alla pagina di login
  document.getElementById('F').submit();
</script>
