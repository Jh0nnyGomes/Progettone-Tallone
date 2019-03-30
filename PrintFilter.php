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
           <a href="index.html">Tabella</a>
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

      <?php
          //preleva i dati da POST per resettare i parametri già cercati
          $pt_handler = new printHandler();
          //$param = json_decode($_POST['param'], true); // TODO: elimina

          //stampa form per mostrare i dati filtrati
          $str = "<form action='PrintFilter.php' method='POST' id='filterForm'>";

          //lista corsi
          $i = new InsertHandler();
          $l = $i->getCorsi();
          $str = $str."<select class='listaCorsi' name='Id_Corso' onchange=filter()> <option value='-1' disabled selected>Filtra per corso</option> ";
          foreach ($l as $key => $value){
            if ($value != null){
              $str = $str."<option value='".$value."'";
              if ($value == $_POST["Id_Corso"])
                $str = $str." selected ";
              $str = $str."'>".$value."</option> ";
            }
          }
          $str = $str."</select>";

          //lista sedi
          $l = $i->getSedi();
          $str = $str." <select class='listaSedi' name='Id_Sede' onchange=filter()> <option value='-1' disabled selected>Sede</option> ";
          foreach ($l as $key => $value){
            if ($value != ''){
              $str = $str." <option value='".$value['id']."'";
              if ($value['id'] == $_POST["Id_Sede"])
                $str = $str." selected ";
              $str = $str.">".$value['Nome']."</option> ";
            }
          }
		  $str = $str . "</select>";

          //periodo
          $str = $str."
          <label>Da</label>
          <input type='date' class='fromDate' name='dateStart' onchange='limitEndDate()' value='".$_POST["dateStart"]."'/>
          <label> A </label>
          <input type='date' class='toDate' name='dateEnd' onchange='limitStartDate()' value='".$_POST["dateEnd"]."'/>";

          //barra di ricerca
          $str = $str."<input class='searchBar' type='text' name='src' placeholder='Cerca' value='".$_POST["src"]."'/>";

          //reset button
          $str = $str."<input class='btnReset' type='button' onclick=resetAll() value='Reset'/>";//input type='reset' non funziona...

          //submit->filtra i risultati con i parametri selezionati
          $str = $str."<input class='btnFilter' type='submit' value='filtra risultati'/>"; //ricarica la pagina postando a sè stessa

          echo $str."</form>";

          //form per la stampa/download dei dati // TODO: implementare la stampa
          $str = "<form id='subDataForm' action='PrintFilterResponse.php' target='_blank' method='POST'>
          <input type='hidden' name='ids'/>
          <input type='hidden' name='scope'/>";
          if($lv > 0){
            $str = $str."
            <input class='btnSave' type='button' name='saveBtn' onclick=saveSelectded() value='salva selezionati' />";
          }
          $str = $str."</form>";

          echo $str;

          //costruisce la tabella per mostrare i dati
          $param = array(
            'Id_Corso' => $_POST['Id_Corso'],
            'Id_Sede' => $_POST['Id_Sede'],
            'dateStart' => $_POST['dateStart'],
            'dateEnd' => $_POST['dateEnd'],
            'src' => $_POST['src']
          );
          $list = $pt_handler->filter($param);

          if (isset($list) && count($list) > 0){
            $str = "<br><table class='table'><thead><tr>";
            if($lv > 0){
              $selectedCbx = unserialize($_POST["checkedRecord"]);
              $checked = (is_array($selectedCbx)) ? null : "checked";
              $str = $str."<th scope='col'><input type='checkbox' id='chbox' onclick='checkAll()' $checked ></th>";
            }
            $str = $str.
                  "<th scope='col'>Cognome</th>".
                  "<th scope='col'>Nome</th>".
                  "<th scope='col'>CF</th>".
                  "<th scope='col'>Data di nascita</th>".
                  "<th scope='col'>Luogo di nascita</th>".
                  "<th scope='col'>Corso</th>".
                  "<th scope='col'>Ore</th>".
                  "<th scope='col'>Mod1</th>".
                  "<th scope='col'>Mod2</th>".
                  "<th scope='col'>Mod3</th>".
                  "<th scope='col'>Aggiornamento 1</th>".
                  "<th scope='col'>Aggiornamento 2</th>".
                  "<th scope='col'>Protocollo</th>".
                  "<th scope='col'>Data</th>".
                "</tr>".
            "</thead>".
            "<tbody>";
            foreach ($list as $record) {
              if ($lv > 0){
                if (is_array($selectedCbx))
                  $checked = (in_array($record["id"], $selectedCbx)) ? "checked" : null ;
                else $checked = "checked";
                $str = $str."<tr><td><input type='checkbox' name='cbx' value='".$record["id"]. "' $checked onclick='uncheck()'/></td>";
              }
              else $str = $str."<tr id='".$record["id"]."' onclick=confirmBox(this.id)>";
              $str = $str.
                "<td id='".$record["id"]."_s'>".$record['surname']."</td>
                <td id='".$record["id"]."_n'>".$record['name']."</td>
                <td>".$record['cf']."</td>
                <td>".$record['birth_date']."</td>
                <td>".$record['birth_place']."</td>
                <td>".$record['corso']."</td>
                <td>".$record['tot_ore']."</td>
                <td>".$record['mod1']."</td>
                <td>".$record['mod2']."</td>
                <td>".$record['mod3']."</td>
                <td>".$record['agg1']."</td>
                <td>".$record['agg2']."</td>
                <td>".$record['protocollo']."</td>
                <td>".$record['date_proto']."</td>";
            }
            $str = $str."</tbody> </table> <div class='pagcontainer'></div>";

            echo $str;
          }
          else echo "<br><p class='wrapPrint'>Selezionare dei parametri e/o cliccare su filtra per un'anteprima del personale da certificare</p>";

        ?>
      </div>
      <script>
        //routine di Setting
        var ds = document.getElementsByName('dateStart')[0];
        var de = document.getElementsByName('dateEnd')[0];
        ds.setAttribute('max', today());
        de.setAttribute('max', today());

        function today(){
          var D = new Date(),
              m = '' + (D.getMonth() + 1),
              d = '' + D.getDate(),
              y = '' + D.getFullYear();
          if (m.length < 2) m = '0' + m;
          if (d.length < 2) d = '0' + d;
          var today = [y, m, d].join('-');
          return today;
        }
        function limitEndDate(){
          var min = document.getElementsByName('dateStart')[0].value;
          var end = document.getElementsByName('dateEnd')[0];
          end.setAttribute('min', min);
          end.setAttribute('max', today());
        }
        function limitStartDate(){
          var max = document.getElementsByName('dateEnd')[0].value;
          if (max === '') max = today();
          document.getElementsByName('dateStart')[0].setAttribute('max', max);
        }
        function checkAll(){
           var checkBox = document.getElementById("chbox");
           var status = checkBox.checked;
           var list = document.querySelectorAll("input[name=cbx]");
           for (var i = 0; i < list.length; i++)
             list[i].checked = status;
        }
        function uncheck(){
          document.getElementById("chbox").checked = false;;
        }
        function resetAll(){
          //document.getElementById('firstform').reset(); non va... :(
          document.getElementsByName("src")[0].value = null;
          document.getElementsByName("dateStart")[0].value = null;
          document.getElementsByName("dateEnd")[0].value = null;
          document.getElementsByName("Id_Corso")[0].selectedIndex  = -1;
          document.getElementsByName("Id_Sede")[0].selectedIndex  = -1;
          filter();
        }
        function filter() {   //ricarica la tabella(la pagina) filtrando
          document.getElementById('filterForm').submit();
        }
        function saveSelectded(){   //certifica i record selezionati
          //ricava i record segnati (le checkBox contengono gli id del personale)
          var data = [];
          var cbx = document.getElementsByName('cbx');

          if (cbx.length == 0) {
            alert('nessun record selezionato');
            return;
          }

          for (var i = 0; i < cbx.length; i++)
            if (cbx[i].checked)
              data.push(cbx[i].value);

          //serializza
          var send = JSON.stringify(Object.assign({}, data));

          //invia al response
          var sub =  document.getElementById('subDataForm');
          sub.elements.namedItem('ids').value = send;
          sub.elements.namedItem('scope').value = "download";
          sub.submit();
        }
        function confirmBox(id){
          var name = document.getElementById(id+"_n").textContent;
          var surname = document.getElementById(id+"_s").textContent;
          if (confirm("Salvare il certificato di "+surname+" "+name+" ?")){
            //formatta per il download
            var data = [id];
            var send = JSON.stringify(Object.assign({}, data));
            var sub = document.getElementById('subDataForm');
            sub.elements.namedItem('ids').value = send;
            sub.elements.namedItem('scope').value = "download";
            sub.submit();
          }
        }
      </script>

    </body>
</html>
<?php
  //riceve messaggi esito operazioni
  $back = $_POST['msg'];
  if (isset($back))
    echo "<script type='text/javascript'>alert('".$back."');</script>";
?>
