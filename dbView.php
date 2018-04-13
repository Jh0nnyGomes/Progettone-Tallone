<html>
  <head></head>
  <body>
    <form class="control-form" action="dbView.php" method="get">
      <input type="text" name="src"/>
      <input type="submit" value="cerca"/>
    </form>
    <?php
    //TODO SISTEMARE PER VISTA DATI
      include('dbHandler.php');
      $dv_handler = new DataViewHandler();
      echo "<table class='table table-striped table-dark'>".
            "<tr>".
              "<th>FoTo</th>".
              "<th>NoMMe</th>".
              "<th>BaSeZzA</th>".
              "<th>cYccYeZzza</th>".
            "</tr>";
      foreach ($dv_handler->search() as $record) {
        echo "<tr>".
              "<td>
                <form action='pokedetails.php' method='POST'>
                  <input type='image' name='img' src='DB POKEMON/main-sprites/".$record['id'].".png' />
                  <input type='hidden' name='idPoke' value='".$record['id']."' />
                  <input type='hidden' name='submit' value='Submit poke' />
                </form>
              </td>".
              "<td>".$record['identifier']."</td>".
              "<td>".$record['height']."kM</td>".
              "<td>".$record['weight']."Kg*m/s^2</td>".
            "</tr>";
      }
      echo "</table>";
      //TODO sistemare graficamente (mettere in linea)
      $l = $dv_handler->getPagLinks();
      if ($l != null){
        $echo;
        $srcVal = $l["src"];
        foreach ($dv_handler->getPagLinks() as $key => $value) {
          $echo = $echo."<a class = 'page-link'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $value .'&src='.$srcVal. "\">";
          switch ($key) {
            case "last":
              $echo = $echo."PrReCcEdenTEE</a>";
              unset($value);
              break;
            case "next":
              $echo = $echo."sUcEsSsYvAAA</a>";
              unset($value);
              break;
            case "...":
              $echo = $echo."...</a>";
              unset($value);
              break;
            case "src":
              $echo = $echo."Pr!m0!!!1!</a>";
              unset($value);
              break;
            default:
              $echo = $echo.$value . "</a>";
              unset($value);
              break;
          }
        }
      }
      echo $echo;
      ?>
    </body>
</html>
