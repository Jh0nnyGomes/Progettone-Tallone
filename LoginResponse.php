<?php
$usr = $_POST["username"];
$psw = $_POST["psw"];

//tenta il login ritorna l'esito
require_once("dbHandler.php");
$u = new UserHandler();
$return = $u->login($usr, $psw);

//posta l'esito alla pagina di login
echo "<form id='response' action='login.php' method='post'>
        <input type='hidden' name='response' value='".serialize($return)."'></form>
      </form>";
?>

<script type="text/javascript">
  //submit dell'esito alla pagina di login
  document.getElementById('response').submit();
</script>
