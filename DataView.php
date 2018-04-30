<html>
  <head>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" type="text/css" href="css/style.css">
  </head>
  <body>
    <nav>
      <div>
        <!-- Inserimento dati -->
        <a href="Insert.php" style="margin-top:2%;margin-left: 84%;">Aggiungi</a>
        <!-- Logout -->
        <a href="LogoutResponse.php" style="margin-top:15%;">Logout</a>
      </div>
    </nav>

    <div class="col-12 container">
        <form action="DataView.php" method="get">
            <input type="text" name="src" placeholder="Search" value="<?php echo (isset($_GET['src']))?$_GET['src']:''; ?>" />
            <input type="submit" value="cerca" />
        </form>
        <?php
        //verifica il login
        require_once('dbHandler.php');
        $u = new UserHandler();
        $u->verifySession();

        //Stampa la tabella
        $dv_handler = new DataViewHandler();
        $pag = $dv_handler->getPag();
        $src = $dv_handler->getSearchedText();

        echo "<table class='table table-striped table-dark'>
              <tr>
                <th>Cognome</th>
                <th>Nome</th>
                <th>CF</th>
                <th>Corso</th>
                <th>Ore</th>
                <th>Mod1</th>
                <th>Mod2</th>
                <th>Mod3</th>
                <th>Aggiornamento</th>
              </tr>";
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
      <script>
        function submit(id){
          document.getElementById(id).submit();
        }
      </script>
    </div>
  </body>
</html>
