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

  public function countRows($tabName, $filter){   // WARNING: FRAGILE ALL'INJECTION
    try {
      $sth = $this->query("SELECT count(*) FROM ".$tabName." ".$filter);
      return $sth->fetchColumn();
    }
    catch(PDOException $e){
      return -1;
    }
  }

  public function query($query){    // WARNING: DA UTILIZZARE SENZA PARAMETRIINSERITI RICEVUTI DALL'UTENTE, É FRAGILE ALL' INJECTION
    try {
      $sth = $this->conn->prepare($query);
      $sth->execute();
      return $sth;
    }
    catch(PDOException $e){
      return null;
    }
  }

  public function sQuery($query, $param){ //permette di eseguire una query sicura
    try {
      $sth = $this->conn->prepare($query);
      $sth->execute($param);
      return $sth;
    }
    catch(PDOException $e){
      return $e;
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
    $query = "insert into $tabName ($cNames) values ($param)";

    try{
      $sth = $this->conn->prepare($query);
      $sth->execute($arrayP);
      return true;
    }
    catch(PDOException $e){
      echo $query."<br>".$e;
      return false;
    }
  }

  public function delete($tabName, $wParam){ // DEBUG:

    //sistema la stringa di condizione e l'array con i prarametri
    $param = [];
    $q = '';
    foreach ($wParam as $key => $value){
      if (is_array($value)) { //associa ad ogni valore nel subArray un campo numerato per l'array associativo
        $i = 0;
        foreach ($value as $subVal) {
          $q = $q." $key = :$key"."_$i or";
          $param[$key."_$i"] = $subVal;
          $i++;
        }
      } else {
        $q = $q." $key = :$key or";
        $param[$key] = $value;
      }
    }
    $q = substr($q, 0, -2);

    try {
      $query = "delete from $tabName where $q";
      $del = $this->conn->prepare($query);
      $del->execute($param);
      return ['deleted'=>$del->rowCount()];
    } catch (PDOException $e) {
      return ['error'=>$e];
    }
  }

  public function update($tabName, $columns_values, $id_Field_Value){ // WARNING: $tabName non deve essere ricevuto dall'esterno
    $str;
    $arrayP = [];
    $arrayP[':'.$id_Field_Value['field']] = $id_Field_Value['value'];
    foreach ($columns_values as $key => $value) {
      $str = $str."$key=:$key"."_v, ";  //_v nel caso alcuni parametri da aggiornare fossero uguali a quelli del where
      $arrayP[':'.$key."_v"] = $value;
    }
    $str = substr($str, 0, strlen($str) - 2);
    $query = "update $tabName set $str where ".$id_Field_Value['field']."=:".$id_Field_Value['field'];

    try{
      $sth = $this->conn->prepare($query);
      $sth->execute($arrayP);
      return ['updated'=>$sth->rowCount()];
    } catch (PDOException $e) {
      return ['error'=>$e];
    }
  }

  protected function updWhere($tabName, $columns_values, $where){
    $str;
    $arrayP = [];
    foreach ($columns_values as $key => $value) {
      $str = $str."$key=:$key, ";
      $arrayP[':'.$key] = $value;
    }
    $str = substr($str, 0, strlen($str) - 2);
    $query = "update $tabName set $str where $where";
    try{
      $sth = $this->conn->prepare($query);
      $sth->execute($arrayP);
      return ['updated'=>$sth->rowCount()];
    } catch (PDOException $e) {
      return ['error'=>$e];
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
    $nfQuery = $this->sQuery(
      "SELECT distinct Cognome
      from formatori join corsi_formatori on Id = Id_Formatore
      where Id_Corso = :corsoId",
      ["corsoId" => $corsoId]
      );
    $sede = $this->sQuery(
      "SELECT distinct Nome
      from sedi join corsi_personale on Id = Id_Sede
      where Id_Corso = :corsoId",
      ["corsoId" => $corsoId]
      )->fetchColumn();

    $nomiFormatori = [];
    while ($n = $nfQuery->fetch()[0])
      array_push($nomiFormatori, $n);

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
    $sql =
    "SELECT count(personale.Id)
     from corsi_personale, personale
     where corsi_personale.Id_Personale = personale.Id
     and ( personale.CF LIKE :src
     or personale.Cognome LIKE :src
     or personale.Nome LIKE :src
     or corsi_personale.Id_Corso LIKE :src )";
    $search = $this->getSearchedText();
    $this->all_rows = $this->sQuery($sql, ['src'=>"%$search%"])->fetchColumn();
    $this->x_pag = $this->getRecordPerPag();
    $this->all_pages = ceil($this->all_rows / $this->x_pag);
    //DEBUG echo "all_rows:".$this->all_rows." x_pag:".$this->x_pag."all_pages:".$this->all_pages;
  }

  public function getSearchedText(){
    return (string)$_GET['src'];
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
    $searchText = $this->getSearchedText();
    $sql =
    "SELECT personale.*, corsi_personale.*
     from corsi_personale JOIN personale on (corsi_personale.Id_Personale = personale.Id)
     where personale.CF LIKE :src
     or personale.Cognome LIKE :src
     or personale.Nome LIKE :src
     or corsi_personale.Id_Corso LIKE :src
     order by corsi_personale.Id_Corso, personale.Cognome
     LIMIT $first, ".$this->x_pag;

    $sth = $this->sQuery($sql, ['src'=>"%$searchText%"]);
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
    $query = "select ".$this->pswField.", ".$this->accessLvField." from ".$this->tabName." where ".$this->usrField.' = :user';

    $qPsw = $this->sQuery($query, ['user' => $user]);

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
    $usrId = $this->sQuery("select id from utenti where Username = :user", ['user'=>$usr])->fetchColumn();
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
    parent::__construct();
    $this->log = new UserHandler();
  }

  public function isDate($date){
    $dt = DateTime::createFromFormat("Y-m-d", $date);
    return ($dt !== false && !array_sum($dt->getLastErrors()));
    //https://stackoverflow.com/questions/13194322/php-regex-to-check-date-is-in-yyyy-mm-dd-format
  }

  public function addPerson($nome, $cognome, $dataNascita, $cf, $pNascita, $dateCorso, $ore, $idCorso, $sede, $protocollo){
    $errorCode = ["corso"=>false, "sede"=>false, "dateFormat"=>false, "cf"=>false, "generic"=>false];
    $interruptFlag = false;

    //convalida cf
    if (strlen($cf) != 16){
      $errorCode["cf"] = true;//cf inesatto
      $interruptFlag = true;
    }

    //controlla formato date aaaa-mm-gg
    $tmp = [];
    foreach ($dateCorso as $key => $value) {
      if ($value == "")
        $tmp[$key] = null;
      else $tmp[$key] = $value;
      if (!$this->isDate($value) && $value != ""){
        $errorCode["dateFormat"] = true;
        $interruptFlag = true;
      }
    }
    $dateCorso = $tmp;

    //convalida data di pNascita
    if (!$this->isDate($dataNascita)){
      $errorCode["dateFormat"] = true;
      $interruptFlag = true;
    }

    //convalida corso
    $corso = $this->sQuery("SELECT Id FROM corsi WHERE Id = :idCorso", ['idCorso'=>$idCorso])->fetchColumn();
    if (!isset($corso[0])) {
      $errorCode["corso"] = true;//corso errato o non trovato
      $interruptFlag = true;
    }

    //convalida Sede
    $idSede = $this->sQuery("SELECT id FROM sedi WHERE id = :sede", ['sede'=>$sede])->fetchColumn();
    if (!isset($idSede[0])) {
      $errorCode["sede"] = true;//sede errata o non trovata
      $interruptFlag = true;
    }

    //controllo parziale 1
    if($interruptFlag)
      return ($errorCode);

    //inserimento dati persona
    $Field_val = ["Cognome"=>$cognome, "Nome"=>$nome, "DataNascita"=>$dataNascita, "ComuneNascita"=>$pNascita, "CF"=>$cf];
    if(!$this->insert("Personale", $Field_val)) {
      $errorCode["generic"] = true;
      return ($errorCode); //controllo parziale 2: impedisce 'inserimento delle date e delle ore'
    }

    //prelevamento id record personale appena aggiunto
    $idPersonale = $this->sQuery("SELECT Id FROM personale WHERE CF = :cf", ["cf"=>$cf])->fetchColumn();

    //inserimento corsi_personale
    $Field_val = [
      "Id_Sede"=>$idSede,
      "Id_Personale"=>$idPersonale,
      "Id_Corso"=>$idCorso,
      "Ore"=>$ore,
      "Mod1"=>$dateCorso["Mod1"],
      "Mod2"=>$dateCorso["Mod2"],
      "Mod3"=>$dateCorso["Mod3"],
      "Agg1"=>$dateCorso["Agg1"],
      "Agg2"=>$dateCorso["Agg2"],
      "Protocollo"=>$protocollo,
    ];
    if(!$this->insert("corsi_personale", $Field_val))
      $errorCode["generic"] = true;
    else
      $this->log->logAction("addP:".$idPersonale);

    return ($errorCode);
  }

  public function addCorso($id, $idsFormatori){
    $errorCode = ["formatori"=>false, "corso"=>false, "generic"=>false];
    $interruptFlag = false;

    //convalida Corso
    if (null != $this->sQuery("SELECT Id FROM corsi WHERE Id = :id", ['id'=>$id])->fetchColumn()) {
      $errorCode["corso"] = true;//corso già esistente
      $interruptFlag = true;
    }

    //convalida formatore
    $fparam = [];
    $queryF = "SELECT Id FROM formatori WHERE ";
    for ($i=0; $i < count($idsFormatori); $i++) {
      $queryF = $queryF." Id = :f$i OR";
      $fparam["f$i"] = $idsFormatori[$i];
    }

    $queryF = substr($queryF, 0, -3);
    $idsF = $this->sQuery($queryF, $fparam)->fetchAll();
    if (count($idsF) != count($idsFormatori)) {
      $errorCode["formatori"] = true;//formatori non trovati
      $interruptFlag = true;
    }

    //controllo inserimento
    if(!$interruptFlag) {
      //inserimento in Corso
      $Field_val = ["Id"=>$id];
      if(!$this->insert("corsi", $Field_val)) {
        $errorCode["generic"] = true;
        return ($errorCode);
      }
      //inserimento Corso_Formatori
      $Field_val = ["Id_Formatore"=> '', "Id_Corso"=>$id];
      foreach ($idsFormatori as $key => $value) {
        $Field_val['Id_Formatore'] = $value;
        if(!$this->insert("corsi_formatori", $Field_val)) {
          $errorCode["generic"] = true;
          return ($errorCode);
        }
      }
    }
    $this->log->logAction("addC:".$id);
    return ($errorCode);
  }

  public function addFormatore($cognome){
    //gestione errori
    $errorCode = ['formatore'=>false, 'generic'=>false];
    $interruptFlag = false;

    //controllo se esiste gia
    $id = $this->sQuery("SELECT Id FROM formatori WHERE Cognome = :surname", ['surname'=>$cognome])->fetchColumn();
    if (null != $id){
      $errorCode['formatore'] = true;
      return $errorCode;
    }

    $Field_val = ["Cognome"=>$cognome];
    if(!$this->insert("formatori", $Field_val)){
      $errorCode['generic'] = true;
      return $errorCode;
    } else{
      $this->log->logAction("addF:".$cognome);
      return $errorCode;
    }
  }

  public function addSede($nome){
    //controllo nome Sede
    $s = $this->query("SELECT Nome FROM sedi WHERE Nome = :name", ['name'=>$nome])->fetchColumn();
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

  public function deletePersonale($id){
    $result = $this->delete('personale', ['Id'=>$id]);
    if (isset($result['deleted']))
      $this->log->logAction('delP:'.$id);
    return $result;
  }

  public function deleteCorso($id){
    $result = $this->delete('corsi', ['Id'=>$id]);
    if (isset($result['deleted']))
      $this->log->logAction('delC:'.$id);
    return $result;
  }

  public function deleteSede($id){
    $result = $this->delete('sedi', ['Id'=>$id]);
    if (isset($result['deleted']))
      $this->log->logAction('delS:'.$id);
    return $result;
  }

  public function deleteFormatore($id){
    $result = $this->delete('formatori', ['Id'=>$id]);
    if (isset($result['deleted']))
      $this->log->logAction('delF:'.$id);
    return $result;
  }

  public function updatePersonale($id, $paramPersona, $paramCorso){
    //aggiorna la tabella Personale
    if (count($paramPersona) != 0){
      $result = $this->update('personale', $paramPersona, ['field'=>'Id', 'value'=>$id]);
      if (!isset($result['updated']))
        return false;
    }

    //aggiorna la tabella corsi_personale
    if (count($paramCorso) != 0){
      $result = $this->update('corsi_personale', $paramCorso, ['field'=>'Id_Personale', 'value'=>$id]);
      if (!isset($result['updated']))
        return false;
    }

    $this->log->logAction('updP:'.$id);
    return true;
  }

  public function updateCorso($oldId, $newId, $idsFormatori){
    //aggiorna la tabella Corsi
    if ($oldId != $newId){
      $result = $this->updWhere('corsi', ['Id'=>$newId], "Id = '$oldId'");
      $id = $newId;
      if (!isset($result['updated']))
        return false;

      //modifica la tabella corsi_personale:update
      $result = $this->update('corsi_personale', ['Id_Corso'=>$newId ], ['field'=>'Id_Corso', 'value'=>$oldId]);
      if (!isset($result['updated']))
        return false;

    } else $id = $oldId;

    //aggiorna la tabella corsi_formatori
    //ottiene le vecchie relazioni
    $r = $this->sQuery("SELECT Id_Formatore FROM corsi_formatori WHERE Id_Corso =:id", ['id'=>$id]);
    $oldFormatori = [];
    while ($v = $r->fetch())
      array_push($oldFormatori, $v[0]);

    //stabilisce, date le vecchie relazioni e le nuove: quali cancellare  quali aggiungere
    $toAdd = array_diff($idsFormatori, $oldFormatori);
    $toDelete = array_diff($oldFormatori, $idsFormatori);

    //elimina
    if (count($toDelete) > 0){
      $dParam = ['Id_Formatore' => $toDelete];
      $result = $this->delete('corsi_formatori', $dParam);
      if (!isset($result['deleted']))
        return false;
    }

    //aggiunge
    if (count($toAdd) > 0){
      foreach ($toAdd as $key => $value)
        if (!$this->insert('corsi_formatori', ['Id_Corso'=>$id, 'Id_Formatore'=>$value]))
          return false;
    }

    $this->log->logAction('updc:'.$id);
    return true;
  }

  public function updateSede($id, $param){
    $result = $this->update('sedi', $param, ['field'=>'id', 'value'=>$id]);
    if (isset($result['updated']))
      $this->log->logAction('updS:'.$id);
    return $result;
  }

  public function updateFormatore($id, $param){
    $result = $this->update('formatori', $param, ['field'=>'Id', 'value'=>$id]);
    if (isset($result['updated']))
      $this->log->logAction('updF:'.$id);
    return $result;
  }

  public function getCorsi(){
    $result = [];
    $idCorsi = $this->query("SELECT Id FROM corsi ORDER BY Id");
    while ($c = $idCorsi->fetch())
      array_push($result, $c[0]);
    return $result;
  }

  public function getSedi(){
    $result = [];
    $sedi = $this->query("SELECT * FROM sedi ORDER BY Nome");
    while ($c = $sedi->fetch())
      array_push($result, $c);
    return $result;
  }

  public function getFormatori(){
    $result = [];
    $sedi = $this->query("SELECT * FROM formatori ORDER BY Cognome");
    while ($c = $sedi->fetch())
      array_push($result, $c);
    return $result;
  }

  public function getPersona($id){
    $result = [];
    $query = "
      SELECT personale.*, corsi_personale.*
      FROM corsi_personale JOIN personale on (corsi_personale.Id_Personale = personale.id)
      WHERE corsi_personale.Id_Personale = :id";
    $p = $this->sQuery($query, ['id' => $id]);
    return $p->fetch();
  }

  public function codifyError($errorCode){// todd: da fare piu` carino e cuccioloso
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

class printHandler extends DataViewHandler{

  public function printCert($param){    // stampa certificati TODO: termina
    $doc =  $this->save($corso, $sede, "Certificato");
    /*
    $fileContent = file_get_contents($doc);
    try {
      print $fileContent;
      echo "<script>window.print();</script>";
      return true;
    } catch (Exception $e) {
      return false;
    }*/


  }

  function save($data, $filename){   //salva più file e li unisce
    $data = array_reverse($data);
    $merged;
    foreach ($data as $key => $value) {
      if (!isset($merged)) $merged = $this->savePersFile($value, $filename);
      else {
        $tmp = $this->savePersFile($value, "tmp");
        $merged = $this->mergeDocs($merged, $tmp, $filename);
      }
    }
    return $merged;
  }

  function saveSelect($ids, $filename){    //preleva da db record del personale con id€$ids, produce certificati per tutti, salva in tmp e ritorna il path
    //prepara la query con tutti gli id
    $qParam = [":0"=>$ids[0]];
    $query =
    "SELECT personale.*, sedi.Nome as sede, corsi_personale.*
     from corsi_personale JOIN personale on (corsi_personale.Id_Personale = personale.Id) JOIN sedi on (corsi_personale.Id_Sede = sedi.id)
     where personale.Id = :0";
    for ($i=1; $i < count($ids); $i++){
      $query = $query." or personale.Id = :$i";
      $qParam[":$i"] = $ids[$i];
    }
    $query = $query." order by corsi_personale.Id_Corso, personale.Cognome";
    $sth = $this->sQuery($query, $qParam);
    $tab = [];
    while($r = $sth->fetch(PDO::FETCH_ASSOC))
      array_push($tab, $r);

    $data = $this->codify($tab);
    return $this->save($data, $filename);
  }

  function filter($param){    //preleva e normalizza i dati per la sostituzione nel doc o per il dataview
    //query in funzione del testo cercato: se è l'unico parametro settato controlla su tutto il db, altrimenti filtra per i soliti parametri a all'interno del risultato cerca quelli col testo corrispondente
    $qParam = ['idSede'=>$param['Id_Sede'], 'idCorso'=>$param['Id_Corso'], 'dateStart'=>$param['dateStart'], 'dateEnd'=>$param['dateEnd'], 'src'=>"%".$param['src']."%"];
    if ($param['Id_Sede'] != null || $param['Id_Corso'] != null || $param['dateStart'] != null || $param['dateEnd'] != null){
      $sql =
      "SELECT * from (SELECT personale.*, sedi.Nome as sede, corsi_personale.*
       from corsi_personale JOIN personale on (corsi_personale.Id_Personale = personale.Id) JOIN sedi on (corsi_personale.Id_Sede = sedi.id)
       where corsi_personale.Id_Sede = :idSede
       or corsi_personale.Id_Corso = :idCorso
       or corsi_personale.Mod1 between :dateStart and :dateEnd
       or corsi_personale.Mod2 between :dateStart and :dateEnd
       or corsi_personale.Mod3 between :dateStart and :dateEnd
       or corsi_personale.Agg1 between :dateStart and :dateEnd
       or corsi_personale.Agg2 between :dateStart and :dateEnd) as tab
       where tab.CF LIKE :src
       or tab.Cognome LIKE :src
       or tab.Nome LIKE :src
       order by tab.sede, tab.Id_Corso, tab.Cognome";
    }else{
      $sql =
      "SELECT personale.*, sedi.Nome as sede, corsi_personale.*
       from corsi_personale JOIN personale on (corsi_personale.Id_Personale = personale.Id) JOIN sedi on (corsi_personale.Id_Sede = sedi.id)
       where corsi_personale.Id_Sede = :idSede
       or corsi_personale.Id_Corso = :idCorso
       or corsi_personale.Mod1 between :dateStart and :dateEnd
       or corsi_personale.Mod2 between :dateStart and :dateEnd
       or corsi_personale.Mod3 between :dateStart and :dateEnd
       or corsi_personale.Agg1 between :dateStart and :dateEnd
       or corsi_personale.Agg2 between :dateStart and :dateEnd";
      if (strlen($param['src']) > 0){
        $sql = $sql.
        " or personale.CF LIKE :src
          or personale.Cognome LIKE :src
          or personale.Nome LIKE :src";
      }
      $sql = $sql." order by sede, corsi_personale.Id_Corso, personale.Cognome";
    }

    $sth = $this->sQuery($sql, $qParam);
    if ($sth == null) return;
    $tab = [];
    while($r = $sth->fetch(PDO::FETCH_ASSOC))
      array_push($tab, $r);

    return $this->codify($tab);
  }

  function codify($tab){    //riceve in $tab un array di record del db -> li converte in dati digeribili dalle altre funzioni per salvarli su file
    //prepara l'array con i risultati
    $filtered = [];
    $pers = [];
    foreach ($tab as $record) {
      $pers['name'] = $record["Nome"];$record["Cognome"];
      $pers['surname'] = $record["Cognome"];
      $pers['cf'] = $record["CF"];
      $pers['birth_place'] = $record["ComuneNascita"];
      $pers['birth_date'] = $record["DataNascita"];
      $pers['tot_ore'] = $record["Ore"];
      $pers['mod1'] = $record["Mod1"];
      $pers['mod2'] = $record["Mod2"];
      $pers['mod3'] = $record["Mod3"];
      $pers['agg1'] = $record["Agg1"];
      $pers['agg2'] = $record["Agg2"];
      $pers['protocollo'] = $record["Protocollo"];
      $pers['id'] = $record["Id"];
      $pers['sede'] = $record["sede"];
      $pers['corso'] = $record["Id_Corso"];

      array_push($filtered, $pers);
    }
    return $filtered;
  }

  public function savePersFile($pers, $name){   //salva i dati di $pers in un file "$name.doc", ritorna il path del file
    $template_file_name = getcwd().'\resources\master.doc';
    $tmpFile = getcwd().'\\tmp\\'.$name.'.doc';

    //Copy the Template file to the Result Directory
    copy($template_file_name, $tmpFile);

    // add class Zip Archive
    $zip_val = new ZipArchive;

    //Docx file is nothing but a zip file. Open this Zip File
    if($zip_val->open($tmpFile) == true)
    {
        // In the Open XML Wordprocessing format content is stored.
        // In the document.xml file located in the word directory.

        $key_file_name = 'word/document.xml';
        $xml = $zip_val->getFromName($key_file_name);

        $timestamp = date('d/m/Y');

        // this data Replace the placeholders with actual values
        $xml = str_replace("[name]", $pers["name"]." ".$pers["surname"], $xml);
        $xml = str_replace("[cf]", $pers["cf"], $xml);
        $xml = str_replace("[birth_place]", $pers["birth_place"], $xml);
        $xml = str_replace("[birth_date]", $pers["birth_date"], $xml);
        $xml = str_replace("[tot_ore]", $pers["tot_ore"], $xml);
        $xml = str_replace("[mod1]", $pers["mod1"], $xml);
        $xml = str_replace("[mod2]", $pers["mod2"], $xml);
        $xml = str_replace("[mod3]", $pers["mod3"], $xml);
        $xml = str_replace("[agg]", $pers["agg"], $xml);
        $xml = str_replace("[sede]", $pers["sede"], $xml);
        $xml = str_replace("[id]", $pers["id"], $xml);
        $xml = str_replace("[date]", $timestamp, $xml);

        //Replace the content with the new content created above.
        $zip_val->addFromString($key_file_name, $xml);
        $zip_val->close();

        return $tmpFile;
    }
  }

  function mergeDocs($doc1, $doc2, $name){    //attacca doc1 in fondo a doc2 in una nuova pagina

    include_once('\resources\tbszip.php');

    $zip = new clsTbsZip();

    // Open the first document
    $zip->Open($doc1);
    $content1 = $zip->FileRead('word/document.xml');
    $zip->Close();

    // Extract the content of the first document
    $p = strpos($content1, '<w:body');
    if ($p===false) exit("Tag <w:body> not found in document 1.");
    $p = strpos($content1, '>', $p);
    $content1 = substr($content1, $p+1);
    $p = strpos($content1, '</w:body>');
    if ($p===false) exit("Tag </w:body> not found in document 1.");
    $content1 = '<w:p><w:r><w:br w:type="page" /><w:lastRenderedPageBreak/></w:r></w:p>'.substr($content1, 0, $p);  //page break

    // Insert into the second document
    $zip->Open($doc2);
    $content2 = $zip->FileRead('word/document.xml');
    $p = strpos($content2, '</w:body>');
    if ($p===false) exit("Tag </w:body> not found in document 2.");
    $content2 = substr_replace($content2, $content1, $p, 0);
    $zip->FileReplace('word/document.xml', $content2, TBSZIP_STRING);

    // Save the merge into a third file
    $filePath = 'tmp\\'.$name.'.doc';
    $zip->Flush(TBSZIP_FILE, $filePath);
    return getcwd()."\\".$filePath;
  }

  public function download($filePath, $filename){    //forza il download del file via header e ritorna l'esito

    if(!file_exists($filePath)){ // file does not exist
        return false;
    } else {
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename.doc");
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: binary");

        // read the file from disk
        readfile($filePath);
        return true;
    }
  }

  function convertToPDF($file){   // TODO: sistemare, non va
    /*php word -> non funziona
    include_once('\resources\PhpWord\Settings.php');
    include_once('\resources\PhpWord\PhpWord.php');
    include_once('\resources\PhpWord\Media.php');
    include_once('\resources\PhpWord\Style.php');
    include_once('\resources\PhpWord\Collection\AbstractCollection.php');
    include_once('\resources\PhpWord\Collection\Bookmarks.php');
    include_once('\resources\PhpWord\Collection\Titles.php');
    include_once('\resources\PhpWord\Collection\Footnotes.php');
    include_once('\resources\PhpWord\Collection\EndNotes.php');
    include_once('\resources\PhpWord\Collection\Charts.php');
    include_once('\resources\PhpWord\Collection\Comments.php');
    include_once('\resources\PhpWord\Metadata\DocInfo.php');
    include_once('\resources\PhpWord\Metadata\Settings.php');
    include_once('\resources\PhpWord\Metadata\Compatibility.php');
    include_once('\resources\PhpWord\TemplateProcessor.php');
    include_once('\resources\PhpWord\Shared\ZipArchive.php');*/

    \PhpOffice\PhpWord\Settings::setPdfRendererPath('/resources/TCPDF');
    \PhpOffice\PhpWord\Settings::setPdfRendererName('TCPDF');

    $phpWord = new \PhpOffice\PhpWord\PhpWord();

    //Open template and save it as docx
    $document = $phpWord->loadTemplate($file);
    $document->saveAs('temp.docx');

    //Load temp file
    $phpWord = \PhpOffice\PhpWord\IOFactory::load('temp.docx');

    //Save it
    $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord , 'PDF');
    $xmlWriter->save('result.pdf');
    //return $file;
  }
}

class AdminHandler extends DbHandler{

  function modifyPsw($old, $new, $username){     //modifica psw
    //controlla la vecchia Password
    $sql = "select Password from utenti where Username = :Username";
    $sth = $this->sQuery($sql, ['Username' => $username]);
    if ($sth == null) return false;
    $r = $sth->fetchAll()[0];
    if($r[0] !== md5($old)) return false;

    //psw controllata: setta la nuova
    $columns_values = ["Password" => md5($new)];
    $echo = $this->updWhere("utenti", $columns_values, 'Username="admin"');
    if(isset($echo['updated'])) return true;
    else return false;
  }

  function getUsers(){
    $sth = $this->query("select Id, Username from utenti where not Username = 'admin'");
    $tab = [];
    while($r = $sth->fetch(PDO::FETCH_ASSOC))
      array_push($tab, $r);
    return $tab;
  }

  function newUser($username){  //aggiunge utente
    //controlla se esiste
    $s = $this->sQuery("SELECT Username FROM utenti WHERE Username = :Username", ['Username' => $username])->fetchColumn();
    if ($s == $username)
      return false;//Error: nome già esistente

    //inserimento
    $Field_val = ["Username"=>$username, "Password"=>md5($username)];
    if(!$this->insert("utenti", $Field_val))
      return false;//Error: generico
    else return true;//esito positivo
  }

  function updateUser($id, $username){  //modifica username
    $result = $this->update('utenti', ['Username' => $username], ['field'=>'Id', 'value'=>$id]);
    return (isset($result['updated'])) ? true : false;
  }

  function resetUserPsw($id, $username){
    $result = $this->update('utenti', ['Password' => md5($username)], ['field'=>'Id', 'value'=>$id]);
    return (isset($result['updated'])) ? true : false;
  }

  function deleteUser($id){   //cancella utente
    $result = $this->delete('utenti', ['Id'=>$id]);
    return $result;
  }

}

?>
