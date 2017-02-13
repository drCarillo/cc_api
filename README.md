# cc_api

Overview:

#Task 1

# With this information create a script (using any language you feel most comfortable in) that queries the API for funded status loans, e.g. http://api.kivaws.org/v1/loans/search.json?status=funded
NOTE: I did not use any frameworks or IDE for development or testing: just wrote out of a text editor.

File: /libs/KivaLoanApiExample.class.php: class for object to make API calls and process using storage layer
taking advantage of injection/composition for a db storage layer.
File: DbStorage.class.php: PDO db layer.

File: /libs/KivaLoanApiExample.class.php
Specific method call: curlKivaFundedLoansApi($kiva_api_url).
Generic curl method: curlKivaApis($kiva_api_url) - used by all API call methods.

#Choose a loan from the list and pull its information, e.g. http://api.kivaws.org/v1/loans/300000.json
File: /libs/KivaLoanApiExample.class.php
Method: curlKivaCurrentLoanDetailsApi()

#Then, also pull a list of that loan’s lenders, e.g http://api.kivaws.org/v1/loans/300000/lenders.json
File: /libs/KivaLoanApiExample.class.php
Method: curlKivaCurrentLoanLendersApi()

NOTE: some lenders were Anonymous and didn't have a lender_id in its array of data.
This is an issue and we did not have time to discuss so I didn't address it but it will cause
issue creating a lender repayment schedule and lender repayments since there is no lender_id for
proper db records and foreign keys.


#Task 2

#Use the loan information you gathered above ( e.g:  "loan_amount":100 (in USD), "repayment_term":7 (in months)) to build out a loan repayment schedule into a database table.  
File: /libs/KivaLoanApiExample.class.php
Method: createFundedLoanRecord()
Table: loans

#Then use the list of lenders and determine an estimated repayment schedule for each one. Create a database schema to hold this information and a script that distributes the repayments equally across the lenders. This creates an audit trail that each lender received back the amount they put into the loan.
File: /libs/KivaLoanApiExample.class.php
Method: createFundedLoanLendersRepaymentScheduleRecord()
Table: loan_lender_repayments_schedule

#Then use the list of lenders and determine an estimated repayment schedule for each one. Create a database schema to hold this information and a script that distributes the repayments equally across the lenders. This creates an audit trail that each lender received back the amount they put into the loan.
NOTE: There were no payments_schedule for the loan. So I took the loan amount and divied it by the number
of lenders presuming one payment made (expected_payment_amount (repayment)) on or before expected_payment_date (posted_date + repayment_terms).
Then I take the posted date (presume when clock starts for borrower) and added the months from repayment_terms as the repayment expected date.
File: /libs/KivaLoanApiExample.class.php
Method: createFundedLoanLendersRepaymentRecord()
Table: loan_lender_repayments

#Then, write unit tests for the code as well as an integrity test for the data that ensures each lender got back their expected amount over the course of the loan.
File: testKiva.php