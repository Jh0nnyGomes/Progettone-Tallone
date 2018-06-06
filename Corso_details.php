<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style.css">
  </head>
  <body>
    <div class="col-12">
      <div class="navbar">
        <ul class="navbar-list-course">
          <li>
            <a href="https://www.isisbisuschio.gov.it/">Home</a>
          </li>
          <li>
            <a href="DataView.php">Tabella</a>
          </li>
          <!-- inserimento per moderator ed administrator -->
          <?php
            //preleva il livello di acceso e aggiunge le funzioni relative
            require_once('dbHandler.php');
            $u = new UserHandler();
            $lv = $u->getAcLv();
            if ($lv > 0) //moderator
              echo '<li><a href="addPersonale.php">Aggiungi personale</a></li>';
            if ($lv > 1) //admin
              echo '<li><a href="addCorso.php">Aggiungi corso</a></li>';
          ?>
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
            $str = "Corso:$corsoId.<br>
                    Sede: ".$result["Sede"]."<br>
                    Formatori<br>
                    <ul>";
            foreach ($result["Formatori"] as $key => $value)
              $str = $str."<li>$value</li>";
            $str = $str."</ul>
                  <form action='DataView.php?pag=$pag&src=$src' method='POST'>
                    <input type='submit' name='submit' value='Torna alla tabella'>
                  </form>";

            echo $str;
          } catch(Exception $e) {
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
