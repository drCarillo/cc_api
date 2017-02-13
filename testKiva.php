<?php
error_reporting(E_ALL);	
ini_set('display_errors', '1');

require_once('./libs/DbStorage.class.php');
require_once('./libs/KivaLoanApiExample.class.php');

$dbs  = new DBstorage(); // Database object
$test = new KivaLoanApiExample($dbs); // Kiva API example object


// Test loan record insertion
$loan = array('loan_id' => 123456, 'loan_amount' => 100.00, 'repayment_term' => 6, 'posted_date' => "NOW()", 'status' => 'funded');
if ($expected_loan_id = $dbs->createLoan($loan)) echo "\n\nInserted expected loan record: " . $expected_loan_id . "\n\n";
else echo "Loan record insertion failed.\n\n";


// Test loan_lender_repayments_schedule (llrs) record insertion
$details = array('lender_id' => 1, 'loan_id' => '123456', 'expected_payment_date' => "NOW()", 'expected_payment_amount' => 100.00, 'payment' => 100.00, 'payment_date' => "NOW()");
if ($expected_llrs_id = $dbs->createLoanLenderRepaymentsSchedule($details)) echo "\n\nInserted loan_lender_repayments_schedule record: " . $expected_llrs_id . "\n\n";
else echo "Loan_lender_repayment_schedule record insertion failed.\n\n";


// Test loan_lender_repayments (llr) record insertion
$details = array('loan_lender_repayments_schedule_id' => 1, 'lender_id' => '1', 'amount' => 25.00);
if ($expected_llr_id = $dbs->createLoanLenderRepayments($details)) echo "\n\nInserted loan_lender_repayments record: " . $expected_llr_id . "\n\n";
else echo "Loan_lender_repayments record insertion failed.\n\n";


// Test loan_lender_repayments data integrity (lender gets expected scheduled repayments for a loan).
$details = array('loan_lender_repayments_schedule_id' => 1, 'lender_id' => '1', 'active' => 1, 'expected_amount' => 25.00);
if ($loan_repayments_amount = $dbs->selectLoanLenderRepayments($details)) {
    echo "\n\nExpected loan repayment amount: " . $details['expected_amount'] . "\n";
    echo "Loan repayment amount: " . $loan_repayments_amount . "\n";
    if ($loan_repayments_amount == $details['expected_amount']) echo "PASSED\n\n";
        else echo "FAILED\n\n";
} else {
    echo "Loan_lender_repayments record insertion failed.\n\n";
}

// Test API returned JSON and decoded into an array
$test->curlKivaFundedLoansApi('http://api.kivaws.org/v1/loans/search.json?status=funded');
$is_funded_loan_array = $test->getFundedLoans();
if (!empty($is_funded_loan_array) && is_array($is_funded_loan_array)) {
    echo "\n\ncurlKivaFundedLoansApi() test: PASSED\n\n";
} else {
    echo "\n\ncurlKivaFundedLoansApi() test: FAILED\n\n";
}

// Make sure we have picked a loan
$test->setCurrentLoan();
$is_current_loan_array = $test->getCurrentLoan();
if (!empty($is_current_loan_array) && is_array($is_current_loan_array)) {
    echo "\n\nsetCurrentLoan() test: PASSED\n\n";
} else {
    echo "\n\nsetCurrentLoan() test: FAILED\n\n";
}

// Test API returned JSON and decoded into an array
$test->curlKivaCurrentLoanDetailsApi();
$is_current_loan_details_array = $test->getCurrentLoanDetails();
if (!empty($is_current_loan_details_array) && is_array($is_current_loan_details_array)) {
    echo "\n\ncurlKivaCurrentLoanDetailsApi() test: PASSED\n\n";
} else {
    echo "\n\ncurlKivaCurrentLoanDetailsApi() test: FAILED\n\n";
}

// Test API returned JSON and decoded into an array
$test->curlKivaCurrentLoanLendersApi();
$is_current_loan_lenders_array = $test->getCurrentLoanLender();
if (!empty($is_current_loan_lenders_array) && is_array($is_current_loan_lenders_array)) {
    echo "\n\ncurlKivaCurrentLoanLendersApi() test: PASSED\n\n";
} else {
    echo "\n\ncurlKivaCurrentLoanLendersApi() test: FAILED\n\n";
}

$test->createFundedLoanRecord();

$test->createFundedLoanLendersRepaymentScheduleRecord();
