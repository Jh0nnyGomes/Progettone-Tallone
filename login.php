<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>
    <div class="row">
        <div class="col-12 logincontainer">
            <div class="loginbox">
                <div class="logincontent">
                    <form action="LoginResponse.php" method="post">
                        <input type="text" name="username" placeholder="Username" class="login textbox">
                        <input type="password" name="psw" placeholder="Password" class="login textbox">
                        <input type="submit" name="submit" value="Accedi" class="login btnlogin">
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php
if (isset($_POST["alert"])) {
  $message;
  switch ($_POST["alert"]) {
    case 0:
      $message = "username non trovato";
      break;
    case 1:
      $message = "Login effettuato con successo";
      break;
    case 2:
      $message = "password errata";
      break;
  }
  echo "<script type='text/javascript'>alert('$message');</script>";
  //dopo la convalida dell'alert reindirizza ai dati
  if ($_POST['alert'] == 1){
    ob_start();
    header("Location:DataView.php");
    ob_end_flush();
    die();
  }
}
?>
</body>
</html>
