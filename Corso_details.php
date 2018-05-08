<html>
  <head>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" type="text/css" href="css/style.css">
  </head>
  <body>
    <div class="col-12">
        <div class="navbar">
            <ul class="navbar-list">
                <!-- DEBUG Home: TODO collegare alla pagina principale della scuola-->
                <li>
                    <a href="index.html">Home</a>
                </li>
                <!-- TODO Inserisci -->
                <li>
                    <a href="">Aggiungi</a>
                </li>
            </ul>
            <ul class="logout-bar">
                <!-- Logout -->
                <li>
                    <a href="LogoutResponse.php">Logout</a>
                </li>
            </ul>
        </div>

    <div class="col-12 container">
        <?php
        require_once("dbHandler.php");
        require_once("Redirect.php");

        if($_POST['details'] == "submit"){
          //da comunicare per andare alla pagina precedente e non quella iniziale
          $src = $_POST["src"];
          $pag = $_POST["pag"];

          $corsoId = $_POST['corsoId'];
          $d = new DetailsHandler();
          try
          {
            $result = $d->getCorsoDetails($corsoId);

            echo "Corso: ".$corsoId."<br>";
            echo "Sede: ".$result["Sede"]."<br>";
            echo "Formatori<br>";
            echo "<ul>";
            foreach ($result["Formatori"] as $key => $value)
              echo "<li>".$value."</li>";
            echo "</ul>";
            echo "<form action='DataView.php?pag=".$pag."&src=".$src."' method='POST'>
                    <input type='submit' name='submit' value='Torna alla tabella'>
                  </form>";
          }
          catch(Exception $e)
          {
            echo "error ".$e;
          }
        }
        else {
          echo "invalid page request";
        }
      ?>
    </div>
  </body>
</html>
