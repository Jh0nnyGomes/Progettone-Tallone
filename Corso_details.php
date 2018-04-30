<html>
  <head>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" type="text/css" href="css/style.css">
  </head>
  <body>
    <nav>
      <div>
        <!-- Logout -->
        <a href="LogoutResponse.php" style="margin-top:15%;">Logout</a>
      </div>
    </nav>

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
