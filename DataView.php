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
        //verifica il login
        require_once('dbHandler.php');
        $u = new UserHandler();
        $u->verifySession();

        //Stampa la tabella
        $dv_handler = new DataViewHandler();
        $pag = $dv_handler->getPag();
        $src = $dv_handler->getSearchedText();

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
              $i = 0;
        foreach ($dv_handler->search() as $record) {
          $i = $i + 1;
          echo "<tr>
                <td>".$record['Cognome']."</td>
                <td>".$record['Nome']."</td>
                <td>".$record['CF']."</td>
                <td>
                  <form id='".$i."' action='Corso_details.php' method='POST'>
                    <input type='hidden' name='corsoId' value='".$record['Id_Corso']."'/>
                    <input type='hidden' name='pag' value='".$pag."'/>
                    <input type='hidden' name='src' value='".$src."'/>
                    <input type='hidden' name='details' value='submit'/>".
                    '<a href="#" onclick="submit('.$i.');">'.$record['Id_Corso']."</a>
                  </form>
                </td>
                <td>".$record['Ore']."</td>
                <td>".$record['Mod1']."</td>
                <td>".$record['Mod2']."</td>
                <td>".$record['Mod3']."</td>
                <td>".$record['Aggiornamento']."</td>
              </tr>";
        }
        echo "</tbody>".
        "</table>";
        echo "<div class='pagcontainer'>";
        //Stampa le pagine
        $l = $dv_handler->getPagLinks();
        if ($l != null){
            $echo;

        if(isset($l['prev'])) //Setta la pagina precedente
          $echo = $echo."<a class = 'page-btn arrows'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['prev'] .'&src='.$l["src"]. "\">&laquo; Previous</a>";

        if(isset($l['src']) && $pag != 1)  //Setta la prima pagina
          $echo = $echo."<a class = 'page-btn'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=0&src=".$l["src"]. "\">...</a>";

        //setta gli indici delle 5 pagine succesive
        foreach ($dv_handler->getPagLinks() as $key => $value) {
          if ($key != 'src' && $key != 'prev' && $key != 'next' && $key != '...'){
            $echo = $echo."<a class = 'page-btn'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $value .'&src='.$l["src"]. "\">".$value."</a>";
            unset($value);
          }
        }

        if(isset($l['...'])) //Setta [...]
          $echo = $echo."<a class = 'page-btn'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['...'] .'&src='.$l["src"]. "\">...</a>";

        if(isset($l['next'])) //Setta la pagina successiva
          $echo = $echo."<a class = 'page-btn arrows'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['next'] .'&src='.$l["src"]. "\">Next &raquo;</a>";

        echo $echo;
      }

      echo "</div>";
      ?>
          <script>
              function submit(id) {
                  document.getElementById(id).submit();
              }

          </script>
    </div>
</body>

</html>
