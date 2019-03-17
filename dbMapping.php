<?php
/**
 * mappa il database
 */
class DB
{
  public function __construct()
  {
    //variabili del db
    $this->name = 'corsisicurezzadb';
    $this->servername = 'localhost';
    $this->user = 'root';
    $this->password = 'root';
    //$this->password = 'mysql'; DEBUG jhonny
    $this->port = 3306;

    //tabelle
    $this->corsi = new Tab( 'corsi',
      [
        'id' => 'Id'
      ] );

    $this->formatori = new Tab( 'formatori',
      [
        'id' => 'Id',
        'cognome' => 'Cognome'
      ] );

    $this->personale = new Tab( 'personale',
      [
        'id' => 'Id',
        'cognome' => 'Cognome',
        'nome' => 'Nome',
        'dataNascita' => 'DataNascita',
        'comuneNascita' => 'ComuneNascita',
        'cf'=> 'CF'
      ] );

    $this->sedi = new Tab( 'sedi',
      [
        'id' => 'Id',
        'nome' => 'Nome'
      ] );

    $this->corsiFormatori = new Tab( 'corsi_formatori',
      [
        'idFormatore' => 'Id_Formatore',
        'idCorso' => 'Id_Corso'
      ] );

    $this->corsiPersonale = new Tab( 'corsi_personale',
      [
        'idSede' => 'Id_Sede',
        'idPersonale' => 'Id_Personale',
        'idCorso' => 'Id_Corso',
        'ore' => 'Ore',
        'mod1' => 'Mod1',
        'mod2' => 'Mod2',
        'mod3' => 'Mod3',
        'agg1' => 'Agg1',
        'agg2' => 'Agg2',
        'protocollo' => 'Protocollo',
        'dateProto' => 'DateProtocollo'
      ] );

    $this->utenti = new Tab( 'utenti',
      [
        'id' => 'Id',
        'username' => 'Username',
        'psw' => 'Password',
        'accesso' => 'Accesso'
      ] );

    $this->log = new Tab( 'log_utenti',
      [
        'idUser' => 'IdUtente',
        'data' => 'Data',
        'action' => 'Action'
      ] );


  }
}
/**
 * tabella generica
 */
class Tab
{
  public function __construct($tabName, array $columns = array())  //il nome della variabile colonna va messo in $columns ['varname'=>'nomecolonna']; il nome della tabella in $names
  {
    if (!empty($columns)) {
      foreach ($columns as $columnname => $value) {
          $this->{$columnname} = $value;
      }
    }
    $this->tabName = $tabName;
  }
}



 ?>
