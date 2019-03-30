<?php
  //verifica il login
  require_once('dbHandler.php');
  $u = new UserHandler();
  $u->verifySession();
  $lv = $u->getAcLv();

  //ulteriore controllo sul livello di Accesso
  if ($u->getAcLv() != 1) {
    echo "<script type='text/javascript'>alert('Livello di accesso non valido');</script>";
    require_once("Redirect.php");
    goToPage('userSettings.php');
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
          <?php
            // admin
            if ($lv > 0){
              echo '<li><a href="addPersonale.php">Aggiungi personale</a></li>';
              echo '<li><a href="addCorso.php">Aggiungi corso</a></li>';
            }
          ?>
        </ul>
          <ul class="logout-bar">
          <li>
            <!-- Logout -->
            <a href="LogoutResponse.php">Logout</a>
          </li>
		  <?php echo "<li><form action='setting.php'><input class='settingImg' type='image' src='resources/img/setting.png'></form></li>"; ?>
        </ul>
      </div>

    <button id='modPBtn' onclick='modifyPsw()'>Modifica password</button>

    <div id='modP' style='display:none'>
      <form action='modifyPsw.php' method='POST'>
        <input class="oldPsw" type='password' name='oldPsw' placeholder='vecchia password'>
        <input class="newPsw" type='password' name='newPsw' placeholder='nuova password'>
        <input class="copyNewPsw" type='password' name='copyNewPsw' placeholder='reinserisci nuova password'>
        <input class="subNewPsw" type='submit'>
      </form>
      <button class="btnSave" onclick='back()'>annulla</button>
    </div>

    <div id='users'>
      <p class='pUsers'>Gestisci utenti
	    <button id='newUser' onclick='newUser()'>Aggiungi nuovo utente</button>
	  </p>
      <!--<button id='newUser' onclick='newUser()'>Aggiungi nuovo utente</button>-->
      <table class='table'>
        <?php
          //gestione utenti->aggiunta/modifica/eliminazione/reset account
          $ad_handler = new AdminHandler();
          $list = $ad_handler->getUsers();
          foreach ($list as $u) {
            echo "<tr>
                    <td>".$u['Username']."</td>
                    <td><form action='UpdateUsersResponse.php' onclick=submit(this) method='POST'>
                      <input type='hidden' name='usrn' value='".$u['Username']."'>
                      <input type='hidden' name='Id' value='".$u['Id']."'>
                      <input type='hidden' name='scope' value='reset'>
                      <a href='#' name='reset'>reset Password</a>
                    </form></td>
                    <td><div id='".$u['Id']."_modUsrn' onclick=modUsrname(this)>
                      <input type='hidden' name='usrn' value='".$u['Username']."'>
                      <input type='hidden' name='Id' value='".$u['Id']."'>
                      <input type='image' name='update' src='resources/img/modify.png'>
                    </div></td>
                    <td><form onclick='del(this)' method='POST'>
                      <input type='hidden' name='usrn' value='".$u['Username']."'>
                      <input type='hidden' name='Id' value='".$u['Id']."'>
                      <input type='hidden' name='scope' value='delete'>
                      <input type='image' name='delete' src='resources/img/trash.png'>
                    </form></td>
                  </tr>";
          }
        ?>
      </table>
    </div>

    <div id='newUserDiv' style='display:none'>
      <form id='modUsrForm' action='UpdateUsersResponse.php' method="POST">
        <input type="hidden" name='scope' value="newUser">
        <input type="hidden" name='Id'> <!-- per quando viene usata come update -->
        <input class="modUsername" type="text" name='usrn' placeholder="Username / codice meccanografico scuola" value="<?php echo isset($_POST['back']) ? unserialize($_POST['back'])['usrn'] : null; ?>" >
        <input type="submit" value='salva'>
      </form>
      <button class="btnSave" onclick='back()'>annulla</button>
    </div>


    <script>
    function modifyPsw(){
      document.getElementById('modPBtn').style='display:none';
      document.getElementById('modP').style='display:visible';
      document.getElementById('users').style='display:none';
    }
    function back(){
      document.getElementById('modPBtn').style='display:visible';
      document.getElementById('modP').style='display:none';
      document.getElementById('users').style='display:visible';
      document.getElementById('newUserDiv').style='display:none';
    }
    function submit(form) {
      form.submit();
    }
    function del(form){
      if(confirm("Cancellare il record?")){
        form.action='UpdateUsersResponse.php';
        form.submit();
      } else form.focus();
    }
    function newUser(){
      document.getElementById('modPBtn').style='display:none';
      document.getElementById('newUserDiv').style='display:visible';
      document.getElementById('users').style='display:none';

      //resetta la form per aggiungere utenti nel caso prima sia stata usata per modificare
      var subForm = document.getElementById('modUsrForm');
      subForm.elements.namedItem('scope').value = 'newUser';
      subForm.elements.namedItem('usrn').value = null;
      subForm.elements.namedItem('Id').value = null;
    }
    function modUsrname(div){
      //modifica la form di inserimento per modificare uno username
      var username = div.childNodes[1].value;
      var id = div.childNodes[3].value;

      var subForm = document.getElementById('modUsrForm');
      subForm.elements.namedItem('scope').value = 'update';
      subForm.elements.namedItem('usrn').value = username;
      subForm.elements.namedItem('Id').value = id;

      //visibilità
      document.getElementById('modPBtn').style='display:none';
      document.getElementById('newUserDiv').style='display:visible';
      document.getElementById('users').style='display:none';
    }
    </script>
    </body>
</html>


<?php
  //riceve messaggi esito operazioni
  if (isset($_POST['response'])){
    switch ($_POST['response']) {
      case 'modPsw':
        if (isset($_POST['result']) && $_POST['result'] == 'failure'){
          echo "<script type='text/javascript'>modifyPsw()</script>";
        }
        break;

      case 'update':
        if (isset($_POST['result']) && $_POST['result'] == 'failure'){
          $idDiv = isset($_POST['back']) ? unserialize($_POST['back'])['id'] : null;
          $idDiv = $idDiv.'_modUsrn';
          echo "
            <script type='text/javascript'>
              var div = document.getElementById('$idDiv');
              modUsrname(div);
            </script>";
        }
        break;

      case 'newUser':
        if (isset($_POST['result']) && $_POST['result'] == 'failure')
          echo "<script type='text/javascript'>newUser()</script>";
        break;
    }
    if (isset($_POST['msg']))
      echo "<script type='text/javascript'>alert('".$_POST['msg']."');</script>";
  }

?>
