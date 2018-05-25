<?php
Class DbHandler {
  protected $servername;
  protected $port;
  protected $username;
  protected $password;
  protected $dbName;
  protected $conn;

  //default constructor
  function __construct(){
    $this->servername = 'localhost';
    $this->port = 3306;
    $this->username = 'root';
    $this->password = 'root';//julian DEBUG
    //$this->password = 'mysql';//jhonny DEBUG
    $this->dbName = 'corsisicurezzadb';
    //crea nuova connessione
    try {
      $this->conn = new PDO("mysql:host=$this->servername;dbname=$this->dbName", $this->username, $this->password);
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e) {
      echo "Connection failed: " . $e->getMessage();
    }
  }

  public function countRows($tabName, $filter){
    try {
      $sth = $this->query("SELECT count(*) FROM ".$tabName." ".$filter);
      return $sth->fetchColumn();
    }
    catch(PDOException $e){
      return -1;
    }
  }

  public function query($query){
    try {
      $sth = $this->conn->prepare($query);
      $sth->execute();
      return $sth;
    }
    catch(PDOException $e){
      return null;
    }
  }

  public function insert($tabName, $columns_values){
    //generazione query
    $cNames;
    $param;
    $arrayP = array();
    foreach ($columns_values as $key => $value) {
      $cNames = $cNames.$key.", ";
      $param = $param.":".$key.", ";
      $arrayP[':'.$key] = $value;
    }
    $cNames = substr($cNames, 0, strlen($cNames) - 2);
    $param = substr($param, 0, strlen($param) - 2);
    $query = "insert into ".$tabName." (".$cNames.") values (".$param.")";
    try{
      $sth = $this->conn->prepare($query);
      $sth->execute($arrayP);
      return true;
    }
    catch(PDOException $e){
      //echo $e;
      return false;
    }
  }
}

Class DetailsHandler extends DbHandler{
  function __construct(){
    parent::__construct();
  }

  function getCorsoDetails($corsoId){
    //PRELEVA I NOMI DEI FORMATORI
    $result = [];
    $nfQuery = $this->query(
      "SELECT formatori.Cognome
      from corsisicurezzadb.formatori join corsisicurezzadb.corsi_formatori on formatori.Id = corsi_formatori.Id_Formatore
      where corsi_formatori.Id_Corso = '$corsoId'"
      );
    $sede = $this->query(
      "SELECT distinct sedi.Nome
      from corsisicurezzadb.sedi join corsisicurezzadb.corsi_personale on sedi.Id = corsi_personale.Id_Sede
      where corsi_personale.Id_Corso = '$corsoId'"
      )->fetchColumn();

    $nomiFormatori = [];
    while ($n = $nfQuery->fetch())
      array_push($nomiFormatori, $n[0]);

    $result["Formatori"] = $nomiFormatori;
    $result["Sede"] = $sede;

    return $result;
  }
}

Class DataViewHandler extends DbHandler{
  private $x_pag;
  private $all_pages;
  private $all_rows;

  function __construct(){
    parent::__construct();
    $search = $this->getSearchedText();
    $this->all_rows = $this->query($this->countQuery($search))->fetchColumn();
    $this->x_pag = $this->getRecordPerPag();
    $this->all_pages = ceil($this->all_rows / $this->x_pag);
    //DEBUG echo "all_rows:".$this->all_rows." x_pag:".$this->x_pag."all_pages:".$this->all_pages;
  }

  public function buildQuery($searchText){
    $sql = "SELECT corsisicurezzadb.personale.Cognome, corsisicurezzadb.personale.Nome, corsisicurezzadb.personale.CF, corsisicurezzadb.corsi_personale.Id_Corso, corsisicurezzadb.corsi_personale.Ore, corsisicurezzadb.corsi_personale.Mod1, corsisicurezzadb.corsi_personale.Mod2, corsisicurezzadb.corsi_personale.Mod3, corsisicurezzadb.corsi_personale.Aggiornamento".
    " from corsi_personale, personale".
    " where corsisicurezzadb.corsi_personale.Id_Personale = corsisicurezzadb.personale.Id".
    " and ( personale.CF LIKE '%$searchText%'".
    " or personale.Cognome LIKE '%$searchText%'".
    " or personale.Nome LIKE '%$searchText%'".
    " or corsi_personale.Id_Corso LIKE '%$searchText%' )".
    " order by corsi_personale.Id_Corso";
    return $sql;
  }

  public function countQuery($searchText){
    $sql = "SELECT count(corsisicurezzadb.personale.Id)".
    " from corsi_personale, personale".
    " where corsisicurezzadb.corsi_personale.Id_Personale = corsisicurezzadb.personale.Id".
    " and ( personale.CF LIKE '%$searchText%'".
    " or personale.Cognome LIKE '%$searchText%'".
    " or personale.Nome LIKE '%$searchText%'".
    " or corsi_personale.Id_Corso LIKE '%$searchText%' )";
    return $sql;
  }

  public function getSearchedText(){
    return $_GET['src'];
  }

  public function getPag(){
    //sistema la variabile nell'url e la ritorna
    $pag = isset($_GET['pag']) ? $_GET['pag'] : 1;
    if (!$pag || !is_numeric($pag) || $pag > $this->all_pages || $pag <= 0) $pag = 1;
    return $pag;
  }

  public function getRecordPerPag(){
    $x_pag = isset($_GET['rpp']) ? $_GET['rpp'] : 20;
    if (!$x_pag || !is_numeric($x_pag) || $x_pag <= 0)
      $x_pag = 20;
    return $x_pag;
  }

  public function search(){
    $first = ($this->getPag()-1) * $this->x_pag;
    $sth = $this->query($this->buildQuery($this->getSearchedText()).' LIMIT '.$first.', '.$this->x_pag);
    $tab = [];
    while($r = $sth->fetch(PDO::FETCH_ASSOC))
      array_push($tab, $r);
    return $tab;
  }

  //ritorna una array associativo con le pagine caricabili (indici e nomi relativi in values & keys)
  public function getPagLinks(){
    $list = array();
    $pag = $this->getPag();
    //pagina precedente
    if($this->all_pages > 1){
      if ($pag > 1)
        $list["prev"] = $pag -1;
    //pagina successiva
    if ($this->all_pages > $pag)
      $list["next"] = $pag +1;
    //successive (max 5) pagine
    $npgs = 5;
    while ($npgs >= $this->all_pages - $pag)
      $npgs--;
    for ($i = 0; $i <= $npgs; $i++)
        $list[$i] = $i + $pag;
    //5 pagine piu in la
    if ($this->all_pages > $pag + 5)
      $list["..."] = $pag +5;

    //passa il testo cercato
    $list["src"] = $this->getSearchedText();
    return $list;
    }
  }
}


Class UserHandler extends DbHandler{
  public $tabName;
  public $pswField;
  public $usrField;
  public $emailField;
  public $accessLvField;

  function __construct(){
    parent::__construct();
    //mappa la tabella
    $this->tabName = "Utenti";
    $this->pswField = "Password";
    $this->usrField = "Username";
    $this->emailField = "email";
    $this->accessLvField = "Accesso";
  }

  public function sessionSafeStart(){
    if (session_status() == PHP_SESSION_NONE)
      session_start();
  }

  public function sessionSafeUnset(){
    if (session_status() == PHP_SESSION_ACTIVE)
      session_unset();
  }

  // NOTE:usa l'md5
  public function login($user, $psw){
    //preleva la psw dove l'email o lo username sono uguali a quello inserito
    $query;
    if (strpos($user, '@'))
      $query = "select ".$this->pswField.", ".$this->accessLvField." from ".$this->tabName." where ".$this->emailField.' = "'.$user.'"';
    else
      $query = "select ".$this->pswField.", ".$this->accessLvField." from ".$this->tabName." where ".$this->usrField.' = "'.$user.'"';

    $qPsw = $this->query($query);

    //"username o email non trovato"
    if ($qPsw->rowCount() < 1)
      return $this->codifyLoginResult(0);

    //"Loggato!" e aggiunge alla sessione
    $record = $qPsw->fetchAll()[0];
    if (md5($psw) == $record[0]){
      $this->sessionSafeStart();
      $_SESSION["username"] = $user;
      $_SESSION["logged"] = 1;
      $_SESSION["accessLv"] = $record[1];
      return $this->codifyLoginResult(1);
    }

    //"password errata"
    else return $this->codifyLoginResult(2);
  }

  //interpreta il risultato del login
  // NOTE: logga
  private function codifyLoginResult($result){
    $resultObj = [];
    switch ($result) {
      case 0:
        array_push($resultObj, 0);
        array_push($resultObj, "Username o indirizzo email non trovato");
        break;

      case 1:
        array_push($resultObj, 1);
        $this->logAction("Login");
        array_push($resultObj, "Login effettuato con successo");
        break;

      case 2:
        array_push($resultObj, 2);
        array_push($resultObj, "Password errata");
        break;
    }
    return $resultObj;
  }

  public function logAction($action){
    $this->sessionSafeStart();
    $usr = $_SESSION['username'];
    $usrId = $this->query("select id from utenti where Username = '$usr'")->fetchColumn();
    $val = [];
    $query;
    if (isset($usrId[0])){
      $val = [":IdUtente"=>$usrId[0],":Action"=>$action];
      $query = "INSERT INTO log_utenti (idUtente, Data, Action) VALUES (:IdUtente, now(), :Action)";
    }
    else {
      $val = [":Action"=>$action];
      $query = "INSERT INTO log_utenti (Data, Action) VALUES (now(), :Action)";
    }

    try {
      $sth = $this->conn->prepare($query);
      $sth->execute($val);
      return true;
    }
    catch (PDOException $e){
      echo $e;
      return false;
    }
  }

// NOTE: logga
  public function logout(){
    $this->sessionSafeStart();
    $this->logAction("Logout");
    $this->sessionSafeUnset();
  }

  public function isLogged(){
    $this->sessionSafeStart();
    return $_SESSION["logged"] == 1;
  }

  public function getAcLv(){
    $this->sessionSafeStart();
    return $_SESSION["accessLv"];
  }

  public function verifySession(){
    if(!$this->isLogged()){
      require_once("Redirect.php");
      goToLogin();
    }
  }
}

class InsertHandler extends DbHandler{
  private $log;

  public function __construct(){
    $log = new UserHandler();
    parent::__construct();
  }

  public function isDate($date){
    $dt = DateTime::createFromFormat("Y-m-d", $date);
    return ($dt !== false && !array_sum($dt->getLastErrors()));
    //https://stackoverflow.com/questions/13194322/php-regex-to-check-date-is-in-yyyy-mm-dd-format
  }

// NOTE: logga
  public function addPerson($nome, $cognome, $dataNascita, $cf, $pNascita, $dateCorso, $ore, $idCorso, $sede){
    $errorCode = ["corso"=>false, "sede"=>false, "dateFormat"=>false, "cf"=>false, "generic"=>false];
    $interruptFlag = false;

    //convalida cf
    if (strlen($cf) != 16){
      $errorCode["cf"] = true;//cf inesatto
      $interruptFlag = true;
    }

    //convalida date
    if (count($dateCorso) != 4) {
      $errorCode["dateFormat"] = true;
      $interruptFlag = true;
    }
    else{
      //controlla formato date aaaa-mm-gg
      foreach ($dateCorso as $key => $value) {
        if (!$this->isDate($value)){
          $errorCode["dateFormat"] = true;
          $interruptFlag = true;
        }
      }
    }

    //convalida data di pNascita
    if (!$this->isDate($dataNascita)){
      $errorCode["dateFormat"] = true;
      $interruptFlag = true;
    }

    //convalida corso
    $corso = $this->query("SELECT Id FROM corsi WHERE Id = '".$idCorso."'")->fetchColumn();
    if (!isset($corso[0])) {
      $errorCode["corso"] = true;//corso errato o non trovato
      $interruptFlag = true;
    }

    //convalida Sede
    $idSede = $this->query("SELECT id FROM sedi WHERE Nome = '".$sede."'")->fetchColumn();
    if (!isset($idSede[0])) {
      $errorCode["sede"] = true;//sede errata o non trovata
      $interruptFlag = true;
    }

    //controllo parziale 1
    if($interruptFlag)
      return $errorCode;

    //inserimento dati persona
    $Field_val = ["Cognome"=>$cognome, "Nome"=>$nome, "DataNascita"=>$dataNascita, "ComuneNascita"=>$pNascita, "CF"=>$cf];
    if(!$this->insert("Personale", $Field_val)) {
      $errorCode["generic"] = true;
      return $errorCode; //controllo parziale 2: impedisce 'inserimento delle date e delle ore'
    }

    //prelevamento id record personale appena aggiunto
    $idPersonale = $this->query("SELECT Id FROM personale WHERE CF = '$cf'")->fetchColumn();

    //inserimento ore e date
    $Field_val = [
      "Id_Sede"=>$idSede,
      "Id_Personale"=>$idPersonale,
      "Id_Corso"=>$idCorso,
      "Ore"=>$ore,
      "Mod1"=>$dateCorso["Mod1"],
      "Mod2"=>$dateCorso["Mod2"],
      "Mod3"=>$dateCorso["Mod3"],
      "Aggiornamento"=>$dateCorso["Aggiornamento"]
    ];
    if(!$this->insert("corsi_personale", $Field_val))
      $errorCode["generic"] = true;
    else
      $this->log->logAction("addP:".$idPersonale);

    return $errorCode;
  }

// NOTE: logga
  public function addCorso($id, $formatori){
    $errorCode = ["formatori"=>false, "corso"=>false, "generic"=>false];
    $interruptFlag = false;

    //convalida Corso
    if (null != $this->query("SELECT Id FROM corsi WHERE Id = '$id'")->fetchColumn()) {
      $errorCode["corso"] = true;//corso già esistente
      $interruptFlag = true;
    }

    //convalida formatore
    $queryF = "SELECT Id FROM formatori WHERE ";
    foreach ($formatori as $key => $value) {
      $queryF = $queryF." Cognome = '$value' OR";
    }
    $queryF = substr($queryF, 0, -3);
    $idsF = $this->query($queryF)->fetchAll();
    if (count($idsF) != count($formatori)) {
      $errorCode["formatori"] = true;//formatori non trovati
      $interruptFlag = true;
    }

    //controllo inserimento
    if(!$interruptFlag) {
      //inserimento in Corso
      $Field_val = ["Id"=>$id];
      if(!$this->insert("corsi", $Field_val)) {
        $errorCode["generic"] = true;
        return $errorCode;
      }
      //inserimento Corso_Formatori
      $Field_val = ["Id_Formatore"=> '', "Id_Corso"=>$id];
      foreach ($idsF as $key => $value) {
        $Field_val["Id_Formatore"] = $value[0];
        if(!$this->insert("corsi_formatori", $Field_val)) {
          $errorCode["generic"] = true;
          return $errorCode;
        }
      }
    }
    $this->log->logAction("addC:".$id);
    return $errorCode;
  }

// NOTE: logga
  public function addFormatore($cognome){
    //controllo nome Sede
    $id = $this->query("SELECT Id FROM formatori WHERE Cognome = '$cognome'")->fetchColumn();
    if (null != $id)
      return 1;//Error: nome già esistente

    $Field_val = ["Cognome"=>$cognome];
    if(!$this->insert("formatori", $Field_val))
      return 2;//Error: generico
    else{
      $this->log->logAction("addF:".$cognome);
      return 0;//esito positivo
    }
  }

// NOTE: logga
  public function addSede($nome){
    //controllo nome Sede
    $s = $this->query("SELECT Nome FROM sedi WHERE Nome = '$nome'")->fetchColumn();
    if ($s == $nome)
      return 1;//Error: nome già esistente

    //inserimento
    $Field_val = ["Nome"=>$nome];
    if(!$this->insert("sedi", $Field_val))
      return 2;//Error: generico
    else{
      $this->log->logAction("addS:".$nome);
      return 0;//esito positivo
    }
  }

  public function getCorsi(){
    $result = [];
    $idCorsi = $this->query("SELECT Id FROM corsi");
    while ($c = $idCorsi->fetch())
      array_push($result, $c[0]);
    return $result;
  }

  public function getSedi(){
    $result = [];
    $sedi = $this->query("SELECT id, Nome FROM sedi");
    while ($c = $sedi->fetch())
      array_push($result, $c);
    return $result;
  }

// TODO: sistemare
  public function codifyError($errorCode){
    $str = "";
    $errorFlag = false;
    foreach ($errorCode as $key => $value) {
      if ($value){
        $errorFlag = true;
        $str = $str."Error:".$key."; ";
      }
    }
    if ($errorFlag) return $str;
    else return "Record inserito correttamente";
  }
}
?>
