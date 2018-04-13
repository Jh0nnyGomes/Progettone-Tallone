<form action="login.php" method="post">
  <?php
  $usr = $_POST["username"];
  $psw = $_POST["psw"];
  include("dbHandler.php");
  echo "DEBUG";
  $u = new UserHandler();
  $return = $u->login($usr, $psw);

  echo "<input type='hidden' name='alert' value='".$return."'></form>";

  //se logga -> aggiunge username ed email alla sessione
  if($return == 1){
    session_start();
    $_SESSION["username"] = $usr;
  }
  ?>
</form>
