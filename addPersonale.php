<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>
    <div class="row">
        <div class="col-12 container">
            <div>
                <div>
                    <form action="addPersonaleResponse.php" method="post">
                      <input type="text" name="nome" placeholder="Nome" class="">
                      <input type="text" name="cognome" placeholder="Cognome" class="">
                      <input type="text" name="cf" placeholder="Codice fiscale" class="">
                      <input type="date" name="data" placeholder="Data di nascita" class="">
                      <input type="text" name="pNascita" placeholder="Luogo di nascita" class="">
                      <br>
                      <input type="text" name="idCorso" placeholder="Corso" class=""><?php // TODO: combobox ?>
                      <!--ore mod123, agg TODO-->
                      <input type="submit" name="submit" value="Aggiungi" class="">
                      <!-- Per registrare gli utenti, implementazione fututa
                        <input type="text" name="username" placeholder="Username" class="">
                        <input type="password" name="password" placeholder="Password" class="">
                        <input type="password" name="cPassword" placeholder="Conferma password" class="">
                        <input type="text" name="nome" placeholder="Nome" class="">
                        <input type="text" name="cognome" placeholder="Cognome" class="">
                        <input type="text" name="cf" placeholder="Codice fiscale" class="">
                        <input type="text" name="email" placeholder="Email" class="">
                        se accede l'admin può impostare il livello di accesso, altrimenti è "user" di default
                        <?php /*
                          require_once('dbHandler.php');
                          $u = new UserHandler();
                          if ($u->getAcLv() > 1)
                            echo '<input type="text" name="Lv" placeholder="Livello di accesso" class="">';*/
                        ?>-->
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php
//verifica il login
require_once('dbHandler.php');
$u = new UserHandler();
$u->verifySession();

//ulteriore controllo sul livello di Accesso
if ($u->getAcLv() < 1) {
  echo "<script type='text/javascript'>alert('Livello di accesso non valido');</script>";
  require_once("Redirect.php");
  goToDataView();
}
?>
</body>
</html>
