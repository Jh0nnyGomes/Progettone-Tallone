<!--NOTE gestire da qui tutti i vari redirect, crea meno confusione raggiungere le pagine quando si cambiano/aggiungono -->

<?php
function goToPage($page){
  ob_start();
  header("Location:".$page);
  ob_end_flush();
  die();
}

function goToLogin(){
  goToPage("login.php");
}

function goToDataView(){
  goToPage("DataView.php");
}

//DEBUG:
function goToIndex(){
  goToPage("index.html");
}
?>
