<?php
 error_reporting(E_ALL);	
 ini_set('display_errors', '1');
 
 /**
 * This interface is a base for data storage/retrieval: db, file, etc.
 *
 * @author Chris Carillo <drcarillo@gmail.com> 2017-02-10
 */
 interface Storage { 
   /**
   * Provides a common interface to build a storage layer.
   */
   public function connectOpen();
 }