
<form id="F" action="login.php" method="post">
  <?php
  $usr = $_POST["username"];
  $psw = md5($_POST["psw"]);

  include("dbHandler.php");
  $u = new UserHandler();
  $return = $u->login($usr, $psw);

  echo "<input type='hidden' name='alert' value='".$return."'></form>";
  ?>
</form>

<script type="text/javascript">
  //submit dell'esito alla pagina di login
  document.getElementById('F').submit();
</script>
