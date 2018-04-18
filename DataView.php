<html>
  <head></head>
  <body>
    <form action="DataView.php" method="get">
      <input type="text" name="src" placeholder="Search" value = "<?php echo (isset($_GET['src']))?$_GET['src']:''; ?>"/>
      <input type="submit" value="cerca"/>
    </form>
    <?php
      //Stampa la tabella
      require_once('dbHandler.php');
      $dv_handler = new DataViewHandler();
      echo "<table class='table table-striped table-dark'>".
            "<tr>".
              "<th>Cognome</th>".
              "<th>Nome</th>".
              "<th>CF</th>".
              "<th>Corso</th>".
              "<th>Ore</th>".
              "<th>Mod1</th>".
              "<th>Mod2</th>".
              "<th>Mod3</th>".
              "<th>Aggiornamento</th>".
            "</tr>";
      foreach ($dv_handler->search() as $record) {
        echo "<tr>".
              "<td>".$record['Cognome']."</td>".
              "<td>".$record['Nome']."</td>".
              "<td>".$record['CF']."</td>".
              "<td>".$record['Id_Corso']."</td>".
              "<td>".$record['Ore']."</td>".
              "<td>".$record['Mod1']."</td>".
              "<td>".$record['Mod2']."</td>".
              "<td>".$record['Mod3']."</td>".
              "<td>".$record['Aggiornamento']."</td>".
            "</tr>";
      }
      echo "</table>";

      //Stampa le pagine
      $l = $dv_handler->getPagLinks();
      if ($l != null){
          $echo;

          if(isset($l['src']))  //Setta la prima pagina
            $echo = $echo."<a class = 'page-link'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['src'] .'&src='.$l["src"]. "\">First</a><br>";

          if(isset($l['last'])) //Setta la pagina precedente
            $echo = $echo."<a class = 'page-link'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['last'] .'&src='.$l["src"]. "\">Last</a><br>";

          if(isset($l['next'])) //Setta la pagina successiva
            $echo = $echo."<a class = 'page-link'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['next'] .'&src='.$l["src"]. "\">Next</a><br>";

          //setta gli indici delle 5 pagine succesive
          foreach ($dv_handler->getPagLinks() as $key => $value) {
            if ($key != 'src' && $key != 'last' && $key != 'next' && $key != '...'){
              $echo = $echo."<a class = 'page-link'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $value .'&src='.$l["src"]. "\">".$value."</a><br>";
              unset($value);
            }
          }

          if(isset($l['...'])) //Setta [...]
            $echo = $echo."<a class = 'page-link'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['...'] .'&src='.$l["src"]. "\">...</a><br>";

          echo $echo;
        }

      ?>
    </body>
</html>
