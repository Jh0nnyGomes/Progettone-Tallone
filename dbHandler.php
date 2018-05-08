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
  function __construct(){
    parent::__construct();
  }

  function getCorsoDetails($corsoId){
    //PRELEVA I NOMI DEI FORMATORI
    $result = [];
    $nfQuery = $this->query(
      "SELECT formatori.Nome
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
      $query = "select ".$this->pswField." from ".$this->tabName." where ".$this->emailField.' = "'.$user.'"';
    else
      $query = "select ".$this->pswField." from ".$this->tabName." where ".$this->usrField.' = "'.$user.'"';

    $qPsw = $this->query($query);

    //"username o email non trovato"
    if ($qPsw->rowCount() < 1)
      return $this->codifyLoginResult(0);

    //"Loggato!" e aggiunge alla sessione
    if (md5($psw) == $qPsw->fetchAll()[0][0]){
      $this->sessionSafeStart();
      $_SESSION["username"] = $usr;
      $_SESSION["logged"] = 1;
      $_SESSION["acLevel"] = $qPsw->fetchAll()[0][1];
      return $this->codifyLoginResult(1);
    }

    //"password errata"
    else return $this->codifyLoginResult(2);
  }

  //interpreta il risultato del login
  private function codifyLoginResult($result){
    $resultObj = [];
    switch ($result) {
      case 0:
        array_push($resultObj, 0);
        array_push($resultObj, "Username o indirizzo email non trovato");
        break;

      case 1:
        array_push($resultObj, 1);
        array_push($resultObj, "Login effettuato con successo");
        break;

      case 2:
        array_push($resultObj, 2);
        array_push($resultObj, "Password errata");
        break;
    }
    return $resultObj;
  }

  public function logout(){
    //TODO Log
    $this->sessionSafeStart();
    $this->sessionSafeUnset();
  }

  public function isLogged(){
    $this->sessionSafeStart();
    return $_SESSION["logged"] == 1;
  }

  public function verifySession(){
    if(!$this->isLogged()){
      require_once("Redirect.php");
      goToLogin();
    }
  }
}
?>
