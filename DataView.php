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
    <div class="container">
      <div class="navbar">
        <ul class="navbar-list">
          <!-- DEBUG Home: TODO collegare alla pagina principale della scuola-->
          <li>
            <a href="index.html">Home</a>
          </li>
          <li>
            <a href="PrintFilter.php">Stampa</a>
          </li>
          <!-- inserimento per administrator -->
          <?php
            // admin
            if ($lv > 0){
              echo '<li><a href="addPersonale.php">Aggiungi personale</a></li>';
              echo '<li><a href="addCorso.php">Aggiungi corso</a></li>';
              echo '<li><a href="addSede.php">Aggiungi Sede</a></li>';
            }
          ?>
          <!-- SearchBar -->
          <form action="DataView.php" method="get">
            <input type="text" name="src" placeholder="Cerca" value="<?php echo (isset($_GET['src']))?$_GET['src']:''; ?>" class="searchbox" />
            <input type="submit" value="Cerca" class="searchbtn" />
          </form>
        </ul>
          <ul class="logout-bar">
          <li>
            <!-- Logout -->
            <a href="LogoutResponse.php">Logout</a>
          </li>
		  <?php echo "<li><form action='setting.php'><input class='settingImg' type='image' src='resources/img/setting.png'></form></li>"; ?>
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
                  "<th scope='col'>Aggiornamento 1</th>".
                  "<th scope='col'>Aggiornamento 2</th>".
                  "<th scope='col'>Protocollo</th>".
                  "<th scope='col'>Data</th>".
				  "<th scope='col'></th>".
				  "<th scope='col'></th>".
				  "<th scope='col'></th>".
                "</tr>".
            "</thead>".
            "<tbody>";
        $i = 0;
        $ll = $dv_handler->search();
        if (isset($ll))
          foreach ($ll as $record) {
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
                <td>".$record['Agg1']."</td>
                <td>".$record['Agg2']."</td>
                <td>".$record['Protocollo']."</td>
                <td>".$record['DateProtocollo']."</td>";
          if ($lv > 0)
            $str = $str."<td>
                          <form id='$i-deleteP' onclick='del(this)' method='post'>
                            <input type='hidden' name='Id' value='".$record['Id']."'>
                            <input type='hidden' name='src' value='$src'>
                            <input type='hidden' name='pag' value='$pag'>
                            <input type='image' src='resources/img/trash.png'>
                          </form>
                        </td>
                        <td>
                          <form id='$i-updateP' action='updatePers.php' method='post'>
                            <input type='hidden' name='person' value='".serialize($record)."'>
                            <input type='hidden' name='src' value='$src'>
                            <input type='hidden' name='pag' value='$pag'>
                            <input type='image' src='resources/img/modify.png'>
                          </form>
                        </td>";
            $str = $str."<td>
              <form action='PrintFilterResponse.php' target='_blank' method='post'>
                <input type='hidden' name='id' value='".$record['Id']."'>
                <input type='hidden' name='scope' value='download'>
                <input type='hidden' name='src' value='$src'>
                <input type='hidden' name='pag' value='$pag'>
                <input type='image' src='resources/img/download.png'>
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
            $echo = $echo."<a class = 'page-btn'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['prev'] .'&src='.$l["src"]. "\">&laquo;</a>";

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
            $echo = $echo."<a class = 'page-btn'  href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $l['next'] .'&src='.$l["src"]. "\">&raquo;</a>";

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
