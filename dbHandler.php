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
    //$this->password = 'root';//julian DEBUG
    $this->password = 'mysql';//jhonny DEBUG
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

Class DetailsHandler extends DbHandler{
  //// TODO: finire
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
    " or corsi_personale.Id_Corso LIKE '%$searchText%' )";
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
    if ($psw == $qPsw->fetchColumn()){
      session_start();
      $_SESSION["username"] = $usr;
      $_SESSION["logged"] = 1;
      return 1; //"Loggato!" e aggiunge alla sessione
    }
    else
      return 2; //"password errata"
  }

  public function logout(){
    //TODO log
    session_start();
    session_unset();
  }
}
?>
