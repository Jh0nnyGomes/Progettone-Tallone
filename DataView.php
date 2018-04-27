<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>

  <div class="col-12">
    <div class="navbar">
        <ul class="navbar-list">
          <!-- DEBUG Home -->
            <li>
                <a href="index.html">Home</a>
            </li>
          <!-- TODO Inserisci -->
            <li>
                <a href="">Aggiungi</a>
            </li>
          <!-- SearchBar -->
                <form action="DataView.php" method="get">
                  <input type="text" name="src" placeholder="Cerca" value="<?php echo (isset($_GET['src']))?$_GET['src']:''; ?>" class="searchbox" />
                  <input type="submit" value="Cerca" class="searchbtn" />
                </form>
        </ul>
        <ul class="logout-bar">
          <!-- Logout -->
            <li>
                <a href="LogoutResponse.php">Logout</a>
            </li>
        </ul>
    </div>
      <?php
      //Stampa la tabella
      require_once('dbHandler.php');
      $dv_handler = new DataViewHandler();
      echo "<table class='table'>".
            "<thead>".
                "<tr>".
                  "<th scope='col'>Cognome</th>".
                  "<th scope='col'>Nome</th>".
                  "<th scope='col'>CF</th>".
                  "<th scope='col'>Corso</th>".
                  "<th scope='col'>Ore</th>".
                  "<th scope='col'>Mod1</th>".
                  "<th scope='col'>Mod2</th>".
                  "<th scope='col'>Mod3</th>".
                  "<th scope='col'>Aggiornamento</th>".
                "</tr>".
            "</thead>".
            "<tbody>";
      foreach ($dv_handler->search() as $record) {
        echo "<tr>".
              "<td>".$record['Cognome']."</td>".
              "<td>".$record['Nome']."</td>".
              "<td>".$record['CF']."</td>".
              "<td>".
                "<form action='DataView.php' method='post'>".
                  $record['Id_Corso'].
                  "<input type='hidden'>".//TODO finire
                "</form>".
              "</td>".
              "<td>".$record['Ore']."</td>".
              "<td>".$record['Mod1']."</td>".
              "<td>".$record['Mod2']."</td>".
              "<td>".$record['Mod3']."</td>".
              "<td>".$record['Aggiornamento']."</td>".
            "</tr>";
      }
      echo "</tbody>".
        "</table>";

      //Stampa le pagine
      $l = $dv_handler->getPagLinks();
      if ($l != null){
          $echo;

          if(isset($l['src']))  //Setta la prima pagina
            $echo = $echo."<a class = 'page-link'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['src'] .'&src='.$l["src"]. "\">First</a>";

          if(isset($l['last'])) //Setta la pagina precedente
            $echo = $echo."<a class = 'page-link'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['last'] .'&src='.$l["src"]. "\">Last</a>";

          if(isset($l['next'])) //Setta la pagina successiva
            $echo = $echo."<a class = 'page-link'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['next'] .'&src='.$l["src"]. "\">Next</a>";

          //setta gli indici delle 5 pagine succesive
          foreach ($dv_handler->getPagLinks() as $key => $value) {
            if ($key != 'src' && $key != 'last' && $key != 'next' && $key != '...'){
              $echo = $echo."<a class = 'page-link'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $value .'&src='.$l["src"]. "\">".$value."</a>";
              unset($value);
            }
          }

          if(isset($l['...'])) //Setta [...]
            $echo = $echo."<a class = 'page-link'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['...'] .'&src='.$l["src"]. "\">...</a>";

          echo $echo;
        }
    ?>
  </div>
</body>

</html>
