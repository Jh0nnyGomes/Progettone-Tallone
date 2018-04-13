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
    $this->password = 'root';
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

/*ROBA
public function __construct(){
  $a = func_get_args();
  $i = func_num_args();
  if (method_exists($this,$f='__construct'.$i))
      call_user_func_array(array($this,$f),$a);
}  //si collega al db e ritorna l'oggetto di connessione
  public function setNewConnection(){
    try {
      $this->conn = new PDO("mysql:host=$this->servername;dbname=$this->dbName", $tis->username, $this->password);
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e) {
      echo "Connection failed: " . $e->getMessage();
    }
  }
  function __construct5($servername, $port, $username, $password, $dbName){
    $this->servername = $servername;
    $this->port = $port;
    $this->username = $username;
    $this->password = $password;
    $this->dbName = $dbName;
    $this->newConnection($servername, $dbName, $username, $password, $port);
  }

  public getters&setters
  public function __get($property) {
    if (property_exists($this, $property)) {
      return $this->$property;
    }
  }

  public function __set($property, $value) {
    if (property_exists($this, $property)) {
      $this->$property = $value;
    }
  }


  //overload
  public function __call($method, $arguments) {
      switch($method){
        case 'newConnection':
          if(count($arguments) == 0)
             return call_user_func_array(array($this,'newConnection0'), $arguments);
          else if(count($arguments) == 5)
             return call_user_func_array(array($this,'newConnection5'), $arguments);
        }
   }

  //si collega al db e integra l'oggetto di connessione nell'oggetto
  public function newConnection0(){
    if (!isset($this->conn))
      $this->newConnection($this->servername, $this->dbName, $this->username, $this->password, $this->port);
  }*/

  public function countRows($tabName, $filter){
    $sth = $this->query("SELECT count(*) FROM ".$tabName." ".$filter);
    return $sth->fetchColumn();
  }

  public function query($query){
    $sth = $this->conn->prepare($query);
    $sth->execute();
    return $sth;
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
      echo $e;
      return false;
    }
  }
}

Class DataViewHandler extends DbHandler{
  private $x_pag;
  private $all_pages;
  private $all_rows;
  private $search;

  private $tabPersonale;
  private $tabFormatori;
  private $tabSedi;
  private $tabCorsi;
  private $tabCorsi_Formatore;
  private $tabCorsi_Personale;

  function __construct(){
    parent::__construct();
    $this->tabPersonale = "personale";
    $this->tabFormatori = "formatori";
    $this->tabSedi = "sedi";
    $this->tabCorsi = "corsi";
    $this->tabCorsi_Formatore = "corsi_formatori";
    $this->tabCorsi_Personale = "corsi_personale";
    $this->setAll($_GET['src']);
  }

  private function buildQuery($searchText){
    // TODO: query che preleva dati da tutte le tabelle
  }

  private function setAll($search){
    $this->search = $search;
    $this->all_rows = $this->countRows($this->tabName, ' WHERE identifier LIKE "%'.$this->search.'%"');//TODO implementare buildQuery
    $this->x_pag = $this->getRecordPerPag();
    $this->all_pages = ceil($this->all_rows / $this->x_pag);
  }

  public function getPag(){
    //sistema la variabile nell'url e la ritorna
    $pag = isset($_GET['pag']) ? $_GET['pag'] : 1;
    if (!$pag || !is_numeric($pag) || $pag > $this->all_pages || $pag <= 0) $pag = 1;
    return $pag;
  }

  public function getRecordPerPag(){
    $x_pag = isset($_GET['rpp']) ? $_GET['rpp'] : 10;
    if (!$x_pag || !is_numeric($x_pag) || $x_pag <= 0)
      $x_pag = 10;
    return $x_pag;
  }

  public function search(){
    //echo "src:".$this->getSearchText()." allR:".$this->all_rows." allP:".$this->all_pages." xP:".$this->x_pag;
    $first = ($this->getPag()-1) * $this->x_pag;
    $sth = $this->query('SELECT * FROM '.$this->tabName.' WHERE identifier LIKE "%'.$this->search.'%" LIMIT '.$first.', '.$this->x_pag);//TODO implementare buildQuery
    $tab = [];
    while($r = $sth->fetch(PDO::FETCH_ASSOC))
      array_push($tab, $r);
    return $tab;
  }
  //ritorna una array associativo con le pagine caricabili (indici e nomi relativi in values & keys)
  public function getPagLinks(){
    $list = array();
    //$this->filter();
    $pag = $this->getPag();
    //pagina precedente
    if($this->all_pages > 1){
      if ($pag > 1)
        $list["last"] = $pag -1;
    //pagina successiva
    if ($this->all_pages > $pag)
      $list["next"] = $pag +1;
    //successive (max 5) pagine
    $npgs = 5;
    while ($npgs >= $this->all_pages - $pag)
      $npgs--;
    for ($i = 2; $i <= $npgs; $i++)
        $list[$i - 1] = $i + $pag;
    //5 pagine piu in la
    if ($this->all_pages > $pag + 5)
      $list["..."] = $pag +5;

    //passa il testo cercato
    $list["src"] = $this->search;
    return $list;
    }
  }

}


Class UserHandler extends DbHandler{
  public $tabName;
  public $pswField;
  public $usrField;
  public $emailField;
  /*
  public $user;
  public $email;*/

  function __construct(){
    parent::__construct();
    //mappa la tabella
    $this->tabName = "Utenti";
    $this->pswField = "Password";
    $this->usrField = "Username";
    $this->emailField = "email";
  }

  public function login($user, $psw){
    //preleva la psw dove l'email o lo username sono uguali a quello inserito
    $query;
    if (strpos($user, '@'))
      $query = "select ".$this->pswField." from ".$this->tabName." where ".$this->emailField.' = "'.$user.'"';
    else
      $query = "select ".$this->pswField." from ".$this->tabName." where ".$this->usrField.' = "'.$user.'"';
    $qPsw = $this->query($query);
    if ($qPsw->rowCount() < 1)
      return 0; //"username o email non trovato"
    if ($psw == $qPsw->fetchColumn())
      return 1; //"Loggato!"
    else
      return 2; //"password errata"
  }
/*
  public function SignIn($user, $psw, $email){
    //controllo caratteri speciali
    $all = array($user, $psw, $email);
    foreach ($all as $key => $value)
      if (
        strpos($value, ' ') === true ||
        strpos($value, '$') === true ||
        strpos($value, '£') === true ||
        strpos($value, '%') === true ||
        strpos($value, '&') === true ||
        strpos($value, '"') === true ||
        strpos($value, "'") === true ||
        strpos($value, '?') === true ||
        strpos($value, '!') === true ||
        strpos($value, '@') === true ||
        strpos($value, ':') === true ||
        strpos($value, '.') === true ||
        strpos($value, ',') === true ||
        strpos($value, ';') === true
      ) return 0;//"caratteri non validi";

    //controllo email
    if (strpos($email, '@') === false)
      return 2;//"email non valida";

    //controllo psw
    if (strlen($psw) < 6)
      return 1;//"password troppo corta";

    $column_value = array(
      $this->usrField => $user,
      $this->pswField => $psw,
      $this->emailField => $email
    );
    if($this->insert($this->tabName, $column_value))
      return 4;//"registrazione completata";
    else
      return 3;//"errore";
  }*/
}
?>
