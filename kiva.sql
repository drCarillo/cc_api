-- Server version: 5.6.28
-- PHP Version: 7.0.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `kiva`
--

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) UNSIGNED NOT NULL,
  `loan_id` int(11) UNSIGNED NOT NULL,
  `loan_amount` double UNSIGNED NOT NULL,
  `repayment_term` int(3) UNSIGNED NOT NULL,
  `posted_date` datetime DEFAULT NULL,
  `date_recorded` datetime DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `active` tinyint(1) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `loan_lender_repayments`
--

CREATE TABLE `loan_lender_repayments` (
  `id` int(11) UNSIGNED NOT NULL,
  `loan_lender_repayments_schedule_id` int(11) UNSIGNED NOT NULL,
  `amount` float UNSIGNED NOT NULL,
  `date_posted` datetime DEFAULT NULL,
  `active` tinyint(1) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `loan_lender_repayments_schedule`
--

CREATE TABLE `loan_lender_repayments_schedule` (
  `id` int(11) UNSIGNED NOT NULL,
  `lender_id` int(11) UNSIGNED NOT NULL,
  `loan_id` int(11) UNSIGNED NOT NULL,
  `expected_payment_date` datetime DEFAULT NULL,
  `expected_payment_amount` double DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `active` smallint(1) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loan_lender_repayments`
--
ALTER TABLE `loan_lender_repayments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loan_lender_repayments_schedule`
--
ALTER TABLE `loan_lender_repayments_schedule`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `loan_lender_repayments`
--
ALTER TABLE `loan_lender_repayments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `loan_lender_repayments_schedule`
--
ALTER TABLE `loan_lender_repayments_schedule`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;