<?php
  //verifica il login
  require_once('dbHandler.php');
  $u = new UserHandler();
  $u->verifySession();

  //ulteriore controllo sul livello di Accesso
  if ($u->getAcLv() < 1) {
    echo "<script type='text/javascript'>alert('Livello di accesso non valido');</script>";
    require_once("Redirect.php");
    goToDataView();
  }

  //esito aggiunta corso/Formatore
  if (isset($_POST['response'])) {
    $back = unserialize($_POST['msg']);
    $tmp = $back['tmp'];
  }
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
           <a href="DataView.php">Tabella</a>
         </li>
        <li>
          <a href="PrintFilter.php">Stampa</a>
        </li>
        <!-- inserimento per administrator -->
        <li><a href="addPersonale.php">Aggiungi personale</a></li>
        <li><a href="addCorso.php">Aggiungi corso</a></li>
        <li><a href="addSede.php">Sedi</a></li>

      </ul>
        <ul class="logout-bar">
          <li>
            <!-- Logout -->
            <a href="LogoutResponse.php">Logout</a>
          </li>
		  <?php echo "<li><form action='setting.php'><input class='settingImg' type='image' src='resources/img/setting.png'></form></li>"; ?>
        </ul>
    </div>

    <div class="row">
        <div class="col-12 container">
            <div>
                <div>
                  <?php
                    echo "<form action='addFormatoreResponse.php' method='post' id='addFform'>
                            <input type='text' name='cognome' placeholder='Cognome Formatore' value='".$tmp['cognome']."' class='coursetxt'>
                            <input type='hidden' name='from' value='addFormatori.php'>".'
                            <input type="submit" name="submit" value="Aggiungi Formatore" class="addCorso">
                          </form>';
                  ?>
                </div>
            </div>
        </div>
    </div>
    <div>
    <table class='table'>
      <thead>
        <tr>
          <th scope='col'>Formatori</th>
          <th scope='col'></th>
        </tr>
      <?php
        $in_handler = new InsertHandler();
        $list = $in_handler->getFormatori();
        if (isset($list) && sizeof($list) == 0)
          echo "</table><div>nessun formatore trovato</div>";
        else
          foreach ($list as $u) {
            echo "<tr>
                    <td>".$u['Cognome']."</td>
                    <td>
                    <form id='".$u["Id"]."-deleteF' onclick='del(this)' method='post'>
                      <input type='hidden' name='Id' value='".$u["Id"]."'>
                      <input type='image' src='resources/img/trash.png'>
                    </form>
                    </td>
                  </tr>";
          }
      ?>
    </table>
    </div>
    <script>
    function del(form){
      if(confirm("Cancellare?")){
        form.action='deleteFormatore.php';
        form.submit();
      } else form.focus();
    }
    </script>
</body>
</html>

<?php
  //riceve messaggi esito operazioni
  if (isset($back))
    echo "<script type='text/javascript'>alert('".$back['result']."');</script>";
?>
