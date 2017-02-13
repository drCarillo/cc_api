<?php
require_once('Storage.class.php');

/**
* PDO database credentials for local dev environment: no config file for this exercise
*/
const DBHOST = '127.0.0.1';
const DBUSER = 'root';
const DBPASS = 'root';
const DB     = 'slack_ttt';

/**
* This class creates a simple usable database handle
* for the purpose of this exercise.
*									 
* @author Chris Carillo <drcarillo@gmail.com> 2017-02-10
*/
class DbStorage implements Storage
{
    /**
    * PDO database object handle.
    * @var PDO $pdo
    */
    private $pdo = null;

    /**
	* Call the the db handle method the start a db handle to use for this class.
	*/
	public function __construct()
	{
		$this->connectOpen();
	}
	
	/**
	* Try to create a db connect handle to process data requests on
	* instaniation of an object of this class.
	*
	* @return boolean false on faiure
	*/
	public function connectOpen()
	{
	    try {
	        $this->pdo = new PDO("mysql:host=127.0.0.1;port=8889;dbname=kiva", DBUSER, DBPASS, array(PDO::ATTR_EMULATE_PREPARES => false));
	        echo "\n\nConnected to database....\n\n";
	    } catch (Exception $e) {
	        //error_log(json_encode(array('ERROR' => 'pdo_connect', 'errno: ' => 'initial_connect_issue', 'errmsg: ' => $e->getMessage(), 'FAIL' => true)));
	        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	        error_log(json_encode(array('ERROR' => 'pdo_connect', 'errno: ' => $this->pdo->errorCode(), 'errmsg: ' => $this->pdo->errorInfo(), 'FAIL' => true)));
	        return false;
	    }
	}
	
	// CRUD
	
	/**
	* Add loan details to the database for the repayment schedule table to reference.
	*
	* @param array $loan
	*
	* @return integer $id on success
	*/
	public function createLoan($loan)
	{
		// prepare
		$id 	= null;
		$sql 	= "INSERT INTO loans (loan_id, loan_amount, repayment_term, posted_date, date_recorded, status, active) VALUES (?, ?, ?, ?, NOW(), ?, ?)";
		$query  = $this->pdo->prepare($sql);
		$query->execute(array($loan['loan_id'], $loan['loan_amount'], $loan['repayment_term'], $loan['posted_date'], $loan['status'], 1));
		
		if ($query) {
		    return $id = $this->pdo->lastInsertId();
		} else {
			// something went wrong : db error
			error_log(json_encode(array('ERROR' => 'createLoan()', 'errno: ' . $this->pdo->errorCode() . ' , errmsg: ' . $this->pdo->errorInfo())));
			return false;
		}
	}
	
	/**
	* Add loan details to the database for the repayment schedlue to reference.
	*
	* @param array $details
	*
	* @return integer $id on success
	*/
	public function createLoanLenderRepaymentsSchedule($details)
	{
		// prepare
		$id 	= null;
		$sql 	= "INSERT INTO loan_lender_repayments_schedule (lender_id, loan_id, expected_payment_date, expected_payment_amount, date_created, active) VALUES (?, ?, ?, ?, NOW(), ?)";
		$query  = $this->pdo->prepare($sql);
		$query->execute(array($details['lender_id'], $details['loan_id'], $details['expected_payment_date'], $details['expected_payment_amount'], 1));
		
		if ($query) {
		    return $id = $this->pdo->lastInsertId();
		} else {
			// something went wrong : db error
			error_log(json_encode(array('ERROR' => 'createLoanLenderRepaymentsSchedule()', 'errno: ' . $this->pdo->errorCode() . ' , errmsg: ' . $this->pdo->errorInfo())));
			return false;
		}
	}
	
	/**
	* Create the borrowers' repayment record back to lenders.
	*
	* @param array $details
	*
	* @return integer $id on success
	*/
	public function createLoanLenderRepayments($details)
	{
		// prepare
		$id 	= null;
		$sql 	= "INSERT INTO loan_lender_repayments (loan_lender_repayments_schedule_id, lender_id, amount, date_posted, active) VALUES (?, ?, ?, NOW(), ?)";
		$query  = $this->pdo->prepare($sql);
		$query->execute(array($details['loan_lender_repayments_schedule_id'], $details['lender_id'], $details['amount'], 1));
		
		if ($query) {
		    return $id = $this->pdo->lastInsertId();
		} else {
			// something went wrong : db error
			error_log(json_encode(array('ERROR' => 'createLoanLenderRepayments()', 'errno: ' . $this->pdo->errorCode() . ' , errmsg: ' . $this->pdo->errorInfo())));
			return false;
		}
	}
	
	/**
	* Get the repayments made back to a lender for a loan.
	*
	* @param integer $loan_lender_repayments_schedule_id
	* @param integer $lender_id
	* @param integer $active
	*
	* @return double $sum_repayments on success
	*/
	public function selectLoanLenderRepayments($details)
	{
		$row 	        = null;
		$sum_repayments = 0.00;
		$sql 	= "SELECT SUM(amount) FROM loan_lender_repayments WHERE loan_lender_repayments_schedule_id = ? AND lender_id = ? AND active = ?";
		$query  = $this->pdo->prepare($sql);
		$query->execute(array($details['loan_lender_repayments_schedule_id'], $details['lender_id'], $details['active']));
		
		// something went wrong : db error?
		if (!$query) {
			error_log(json_encode(array('ERROR' => 'selectLoanLenderRepayments()', 'errno: ' . $this->pdo->errorCode() . ' , errmsg: ' . $this->pdo->errorInfo())));
			return false;
		}
		
		// check
		if ($query->rowCount() <= 0) return false;  // empty array dictates no matching row : ?
		
		// return game stats
		$row = $query->fetch();
		return $sum_repayments = $row['SUM(amount)'];
	}
	
	public function __destruct() {}
}