<?php
error_reporting(E_ALL);	
ini_set('display_errors', '1');

/**
* This a absic GET API and save data calss for Kiva code examaple.
* No frameworks for dev: Laravel or other or PHPUnit.
* No namespaces or autoload for simplicity.
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
    * List of funded loans form the Kiva API
    * for this exercise.
    *
    * @var array $funded_loans
    */
    protected $funded_loans;
    
    /**
    * A current funded loan for this exercise
    * to use for required tasks (db storage etc).
    *
    * @var array $current_loan
    */
    protected $current_loan;
    
    /**
    * Get the current loan id then GET the loan details form the Kiva API.
    *
    * @var array $current_loan_details
    */
    protected $current_loan_details;
    
    /**
    * Get the current loan details then GET the loan lenders from the Kiva API.
    *
    * @var array $current_loan_lenders
    */
    protected $current_loan_lenders;
    
    /**
    * THe loan schedule id for each lender to use in payments (repaments table).
    *
    * @var array $current_loan_lender_schedule_ids
    */
    protected $current_loan_lender_schedule_ids;
    
    /**
    * The loan id record from the db or storage used here.
    *
    * @var integer $current_loan_record_id
    */
    protected $current_loan_record_id;
    
    /**
    * The expected repayment date for each lender (posted_date + repayment_terms (in months)).
    *
    * @var double $currentLoanRepaymentExpectedAmount
    */
    protected $currentLoanRepaymentExpectedDate;
    
    /**
    * The expected repayment amount for each lender (loan_amount/lenders).
    *
    * @var double $currentLoanRepaymentExpectedAmount
    */
    protected $currentLoanRepaymentExpectedAmount;
    
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
    private function setStorage(DbStorage $dbs)
    {
        $this->db = $dbs;
    }
    
    /**
    * A curl method to get the funded loans from the Kiva API.
    *
    * @param string $kiva_api_url
    *
    * @return boolean true on success
    */
    public function curlKivaFundedLoansApi($kiva_api_url)
    {
        if (empty($kiva_api_url)) return false;  // or throw new Exception()?
        
        $response = $this->curlKivaApis($kiva_api_url);  // generic curl method
        
        if ($response) $this->setFundedLoans($response); //set the JSON decoded array
          else return false;
        
        $this->setFundedLoans($response);
         
        return true;
    }
    
    /**
    * A curl method to get funded loan details from the Kiva API.
    * Shouldn't hard code URLS though.
    *
    * @param string $kiva_api_url
    *
    * @return boolean true on success
    */
    public function curlKivaCurrentLoanDetailsApi()
    {
        if (empty($this->current_loan)) return false;  // didn't curl funded loans first
        
        $kiva_api_url = 'http://api.kivaws.org/v1/loans/' . $this->current_loan['id'] . '.json';
        $response     = $this->curlKivaApis($kiva_api_url);
        
        if ($response) {
            $this->setCurrentLoanDetails($response);  // set the JSON decoded array
            $this->setCurrentLoanRepaymentExpectedAmount(); // needed for creating repayment schedule record
            $this->setCurrentLoanRepaymentExpectedDate();
        } else {
            return false;
        }
            
        return true;
    }
    
    /**
    * A curl method to get funded loan lenders from the Kiva API.
    *
    * @param string $kiva_api_url
    *
    * @return boolean true on success
    */
    public function curlKivaCurrentLoanLendersApi()
    {
        if (empty($this->current_loan)) return false;  // didn't curl funded loans first
        
        $kiva_api_url = 'http://api.kivaws.org/v1/loans/' . $this->current_loan['id'] . '/lenders.json';  //Shouldn't hard code URLS though.
        $response     = $this->curlKivaApis($kiva_api_url);
        
        if ($response) $this->setCurrentLoanLenders($response);  // set the JSON decoded array
          else return false;
            
        return true;
    }
    
    /**
    * curl Kiva APIs as needed.
    *
    * @param string $kiva_api_url
    *
    * @return JSON $response on success
    */
    protected function curlKivaApis($kiva_api_url)
    {
        $response     = null;
        $curl_err     = null;
        
        try {
            $curl     = curl_init();
            
            curl_setopt_array($curl, array(
              CURLOPT_URL => $kiva_api_url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => array('cache-control: no-cache')
            ));
            
            $response = curl_exec($curl);
            $curl_err = curl_error($curl);
            return $response;
        } catch (Exception $e) {
            error_log(json_encode(array('ERROR' => 'curlKivaApis()', 'errmsg: ' => $e->GetMessage(), 'FAIL' => true)));
	        return false;
        }
    }
    
    /**
    * Create a funded loan recorded.
    *
    * @return integer $row_id on success
    */
    public function createFundedLoanRecord()
    {
        if (!empty($this->current_loan_details)) {
            $loan = array('loan_id' => $this->current_loan_details['loans'][0]['id'],
                          'loan_amount' => $this->current_loan_details['loans'][0]['loan_amount'],
                          'repayment_term' => $this->current_loan_details['loans'][0]['terms']['repayment_term'],
                          'posted_date' => $this->current_loan_details['loans'][0]['posted_date'], // should be funded_date?
                          'status' => $this->current_loan_details['loans'][0]['status']
                    );
            
            if ($id = $this->db->createLoan($loan)) {
                $this->setCurrentLoanRecordId($id);
                return true;
            }
        }
        
        return false;  // oops?
    }
    
    /**
    * Create a funded loan lenders repayment recorded: foreign key for borrower repayments.
    *
    * @return boolean true on success
    */
    public function createFundedLoanLendersRepaymentScheduleRecord()
    {
        if (!empty($this->current_loan_details)) {
            foreach($this->funded_loan_lenders['lenders'] as $lender) {
                $loan = array('lender_id' => $lender['lender_id'],
                              'loan_id' => $this->current_loan_details['loans'][0]['id'],
                              'expected_payment_date' => $this->currentLoanRepaymentExpectedDate,
                              'expected_payment_amount' => $this->currentLoanRepaymentExpectedAmount
                        );
                    
                if(!$this->db->createLoanLenderRepaymentsSchedule($loan)) return false;
            }
            
            return true;
        }
        
        return false;  // oops?
    }
    
    /**
    * Create a funded loan lenders repayments from borrower distributed equally.
    * Assuming one payment as there was no payment schedule for loan.
    *
    * @return boolean true on success
    */
    public function createFundedLoanLendersRepaymentRecord()
    {
        if (!empty($this->current_loan_lender_schedule_ids)) {
            foreach($this->current_loan_lender_schedule_ids as $schedule_id) {
                $loan = array('loan_lender_repayments_schedule_id' => $schedule_id,
                              'amount' => $this->currentLoanRepaymentExpectedAmount
                        );
                    
                if(!$this->db->createLoanLenderRepayments($loan)) return false;
            }
            
            return true;
        }
        
        return false;  // oops?
    }
    
    /**
    * Setter JSON decode method for funded loans.
    *
    * @param string $api_funded_loans
    *
    * @return boolean true on success
    */
    public function setFundedLoans($api_funded_loans)
    {
        if (!empty($api_funded_loans)) {
            $this->funded_loans = json_decode($api_funded_loans, true);
            return true;
        }
        
        return false;  // something went wrong: shouldn't get here
    }
    
    /**
    * Get the funded loan list from the API.
    *
    * @return array $this->funded_loans on success
    */
    public function getFundedLoans()
    {
        if (!empty($this->funded_loans)) {
            return $this->funded_loans;
        }
        
        return false;  // something went wrong: shouldn't get here
    }
    
    /**
    * Set the current funded loan from the list to use for this exercise.
    * Just use the first loan in the return list for this exercise.
    *
    * @return boolean true on success
    */
    public function setCurrentLoan()
    {
        if (!empty($this->funded_loans)) {
            $this->current_loan = $this->funded_loans['loans'][0];
            return true;
        }
        
        return false; // something went wrong: shouldn't get here
    }
    
    /**
    * Get the current funded loan.
    *
    * @return array $this->current_loan on success
    */
    public function getCurrentLoan()
    {
        if (!empty($this->current_loan)) {
            return $this->current_loan;
        }
        
        return false;  // something went wrong: shouldn't get here
    }
    
    /**
    * Setter the current funded loan record id in this storge: db
    *
    * @return boolean true on success
    */
    public function setCurrentLoanRecordId($id)
    {
        if (!empty($id)) {
            $this->current_loan_record_id = $id;
            return true;
        }
        
        return false; // something went wrong: shouldn't get here
    }
    
    /**
    * Setter JSON decode method for funded loan detail.
    *
    * @param string $funded_loan_details
    *
    * @return boolean true on success
    */
    public function setCurrentLoanDetails($funded_loan_details)
    {
        if (!empty($funded_loan_details)) {
            $this->current_loan_details = json_decode($funded_loan_details, true);
            return true;
        }
        
        return false;  // something went wrong
    }
    
    /**
    * Get the current funded loan details.
    *
    * @return array $current_loan_details on success
    */
    public function getCurrentLoanDetails()
    {
        if (!empty($this->current_loan_details)) {
            return $this->current_loan_details;
        }
        
        return false;  // something went wrong: shouldn't get here
    }
    
    /**
    * Set the expected amount of repayment for each lender (loan_amount/lenders).
    *
    * @return boolean true on success
    */
    public function setCurrentLoanRepaymentExpectedAmount()
    {
        if (!empty($this->current_loan_details)) {
            $this->currentLoanRepaymentExpectedAmount = $this->current_loan_details['loans'][0]['loan_amount'] / $this->current_loan_details['loans'][0]['lender_count'];
            return true;
        }
        
        return false; // no, shouldn't be
    }
    
    /**
    * Set the expected repayment date (posted_date + repayment_terms) for each lender.
    *
    * @return boolean true on success
    */
    public function setCurrentLoanRepaymentExpectedDate()
    {
        if (!empty($this->current_loan_details)) {
            $date_time_add_month_str = '+' . $this->current_loan_details['loans'][0]['terms']['repayment_term'] . ' month';
            // Instantiate a DateTime object
            $date_time_zone = new DateTimeZone('UTC');
            $date           = new DateTime("2017-02-13T05:40:05Z", $date_time_zone);
            $date->modify("$date_time_add_month_str");
            $this->currentLoanRepaymentExpectedDate = $date->format('Y-m-d H:i:s');
            
           return true;
        }
        
        return false;  // hopefully not
    }
    
    /**
    * Setter JSON decode method for funded loan lenders.
    *
    * @param string $funded_loan_lenders
    *
    * @return boolean true on success
    */
    public function setCurrentLoanLenders($funded_loan_lenders)
    {
        if (!empty($funded_loan_lenders)) {
            $this->funded_loan_lenders = json_decode($funded_loan_lenders, true);
            return true;
        }
        
        return false;  // something went wrong
    }
    
    /**
    * Get the current funded loan lenders.
    *
    * @return array $funded_loan_lenders on success
    */
    public function getCurrentLoanLender()
    {
        if (!empty($this->funded_loan_lenders)) {
            return $this->funded_loan_lenders;
        }
        
        return false;  // something went wrong: shouldn't get here
    }
    
    public function __destruct() {}
}