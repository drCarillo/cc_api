<?php
error_reporting(E_ALL);	
ini_set('display_errors', '1');

/**
* 
*
* @author Chris Carillo <drcarillo@gmail.com> 2017-02-10
*/  
class KivaLoanApiExample
{
    /**
    * Storage handle for CRUD to db, file, etc.
    *
    * @var DbStorage $db
    */
    protected $db;
    
    /**
    * Set a stogage source for CRUD actions.
    *
    * @param DbStorage $dbs
    */
    public function __construct(DbStorage $dbs)
    {
        $this->setStorage($dbs);
    }
    
    /**
    * Set a db storage handle for Kiva API GET data.
    *
    * @param DbStorage $dbs
    *
    * @return null
    */
    public function setStorage(DbStorage $dbs)
    {
        $this->db = $dbs;
    }
    
    public function __destruct() {}
}