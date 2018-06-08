<?php
  //verifica il login
  require_once('dbHandler.php');
  $u = new UserHandler();
  $u->verifySession();
  $lv = $u->getAcLv();
?>

<html>
  <head>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
    <div class="">
      <div class="">
        <ul class="">
          <!-- DEBUG Home: TODO collegare alla pagina principale della scuola-->
          <li>
            <a href="index.html">Home</a>
          </li>
          <!-- inserimento per moderator ed administrator -->
          <?php
            //Moderator & admin
            if ($lv > 0)
              echo '<li><a href="addPersonale.php">Aggiungi personale</a></li>';
            if ($lv > 1)
              echo '<li><a href="addCorso.php">Aggiungi corso</a></li>';
          ?>
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
        //costruisce la tabella
        $dv_handler = new DataViewHandler();
        $pag = $dv_handler->getPag();
        $src = $dv_handler->getSearchedText();

        $str = "<table class='table'>".
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
          $str = $str."<tr>
                <td>".$record['Cognome']."</td>
                <td>".$record['Nome']."</td>
                <td>".$record['CF']."</td>
                <td>
                  <form id='$i-details' onclick='submit(this)' action='Corso_details.php' method='POST'>
                    <input type='hidden' name='Id_Corso' value='".$record['Id_Corso']."'/>
                    <input type='hidden' name='pag' value='".$pag."'/>
                    <input type='hidden' name='src' value='".$src."'/>
                    <input type='hidden' name='details' value='submit'/>
                    <a href='#'>".$record['Id_Corso']."</a>
                  </form>
                </td>
                <td>".$record['Ore']."</td>
                <td>".$record['Mod1']."</td>
                <td>".$record['Mod2']."</td>
                <td>".$record['Mod3']."</td>
                <td>".$record['Aggiornamento']."</td>";
          if ($lv > 0)
            $str = $str."<td>
                          <form id='$i-deleteP' onclick='del(this)' method='post'>
                            <input type='hidden' name='Id' value='".$record['Id']."'>
                            <input type='hidden' name='src' value='$src'>
                            <input type='hidden' name='pag' value='$pag'>
                            <input type='image' value='C'>
                          </form>
                        </td>
                        <td>
                          <form id='$i-updateP' action='updatePers.php' method='post'>
                            <input type='hidden' name='person' value='".serialize($record)."'>
                            <input type='hidden' name='src' value='$src'>
                            <input type='hidden' name='pag' value='$pag'>
                            <input type='image' value='U'>
                          </form>
                        </td>";
            $str = $str."</tr>";
        }
        $str = $str."</tbody> </table> <div class='pagcontainer'>";

        //Stampa gli indici a pie' di pagina
        $l = $dv_handler->getPagLinks();
        if ($l != null){
          $echo;
          //Setta la pagina precedente
          if(isset($l['prev']))
            $echo = $echo."<a class = 'page-btn'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['prev'] .'&src='.$l["src"]. "\">&laquo; Previous</a>";

          //Setta la prima pagina
          if(isset($l['src']) && $pag != 1)
            $echo = $echo."<a class = 'page-btn'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=0&src=".$l["src"]. "\">...</a>";

          //setta gli indici delle 5 pagine succesive
          foreach ($dv_handler->getPagLinks() as $key => $value) {
            if ($key != 'src' && $key != 'prev' && $key != 'next' && $key != '...'){
              $echo = $echo."<a class = 'page-btn'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $value .'&src='.$l["src"]. "\">".$value."</a>";
              unset($value);
            }
          }

          //Setta [...]
          if(isset($l['...']))
            $echo = $echo."<a class = 'page-btn'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['...'] .'&src='.$l["src"]. "\">...</a>";

          //Setta la pagina successiva
          if(isset($l['next']))
            $echo = $echo."<a class = 'page-btn'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['next'] .'&src='.$l["src"]. "\">Next &raquo;</a>";

          $str = $str.$echo;
      }

      $str = $str."</div>";
      echo $str;
      ?>
    </div>
    <script>
      function submit(form) {
        form.submit();
      }

      function del(form){
        if(confirm("Cancellare il record?")){
          form.action='deletePers.php';
          form.submit();
        } else form.focus();
      }

    </script>
  </body>
</html>

<?php
  //riceve messaggi esito operazioni
  if (isset($_POST['response']))
    echo "<script type='text/javascript'>alert('".$_POST['msg']."');</script>";
?>
