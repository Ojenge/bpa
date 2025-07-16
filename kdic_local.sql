-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jul 16, 2025 at 07:03 AM
-- Server version: 8.0.35
-- PHP Version: 8.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kdic_local`
--

-- --------------------------------------------------------

--
-- Table structure for table `audittrail`
--

CREATE TABLE `audittrail` (
  `id` int NOT NULL,
  `dateTime` datetime DEFAULT NULL,
  `user` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `browser` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `ipAddress` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `os` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bookmark`
--

CREATE TABLE `bookmark` (
  `id` int NOT NULL,
  `name` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `menuType` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `itemType` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `itemId` varchar(9) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `userId` varchar(9) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `bookmark`
--

INSERT INTO `bookmark` (`id`, `name`, `menuType`, `itemType`, `itemId`, `userId`) VALUES
(2, '', 'Initiatives', 'initiative', '', 'ind439');

-- --------------------------------------------------------

--
-- Table structure for table `cc_picture`
--

CREATE TABLE `cc_picture` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `name` varchar(900) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `description` varchar(900) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `county` varchar(240) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `cc_picture`
--

INSERT INTO `cc_picture` (`id`, `project_id`, `name`, `description`, `county`) VALUES
(1, 151, 'Dashboard Chart Use Graphic.jpeg', '', '1'),
(2, 2445, 'bomet.jpg', '', '1'),
(3, 2445, 'bomet.jpg', '', '1'),
(4, 2446, 'laikipia.jpg', '', '1'),
(5, 2446, 'ndaragwa.jpg', '', '1'),
(6, 11566, 'wamingu.jpg', '', '2'),
(7, 11446, 'mwea.jpg', '', '3');

-- --------------------------------------------------------

--
-- Table structure for table `cc_remarks`
--

CREATE TABLE `cc_remarks` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `remarks` varchar(9000) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `updater` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `update_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `cc_remarks`
--

INSERT INTO `cc_remarks` (`id`, `project_id`, `remarks`, `updater`, `update_date`) VALUES
(1, 151, 'test', 'Commissioner X', '2016-11-17'),
(2, 2445, 'Example comments from the ground.', 'Commissioner X', '2016-11-18'),
(3, 2467, 'This project is in progress', 'Commissioner X', '2016-11-18');

-- --------------------------------------------------------

--
-- Table structure for table `cc_remarks_public`
--

CREATE TABLE `cc_remarks_public` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `remarks` varchar(9000) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `updater` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `update_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `commentary`
--

CREATE TABLE `commentary` (
  `id` int NOT NULL,
  `note` varchar(900) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `senderId` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `date` datetime NOT NULL,
  `linkedId` int NOT NULL,
  `projectId` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `commentarydataitem`
--

CREATE TABLE `commentarydataitem` (
  `id` int NOT NULL,
  `name` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `commentarygoal`
--

CREATE TABLE `commentarygoal` (
  `id` int NOT NULL,
  `name` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `commentaryprocess`
--

CREATE TABLE `commentaryprocess` (
  `id` int NOT NULL,
  `name` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `commentaryworkunit`
--

CREATE TABLE `commentaryworkunit` (
  `id` int NOT NULL,
  `name` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `conversation`
--

CREATE TABLE `conversation` (
  `id` int NOT NULL,
  `note` varchar(9000) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `senderId` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `linkedId` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `core_value`
--

CREATE TABLE `core_value` (
  `id` int NOT NULL,
  `value` varchar(1200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` varchar(1200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `core_value`
--

INSERT INTO `core_value` (`id`, `value`, `description`) VALUES
(1, 'Weka 3', 'Hapa 3'),
(2, 'Game', 'On Kabisa'),
(4, 'Tuko', 'Ndani sasa');

-- --------------------------------------------------------

--
-- Table structure for table `core_value_attribute`
--

CREATE TABLE `core_value_attribute` (
  `id` int NOT NULL,
  `attribute` varchar(300) NOT NULL,
  `description` varchar(1200) NOT NULL,
  `core_value_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `core_value_attribute`
--

INSERT INTO `core_value_attribute` (`id`, `attribute`, `description`, `core_value_id`) VALUES
(1, 'Weka', 'Volume', 1),
(2, 'Tena', 'Ingine', 1),
(3, 'Hapa Pia', 'Mambo', 2),
(4, 'Tuko', 'Ehe Teba', 2);

-- --------------------------------------------------------

--
-- Table structure for table `core_value_attribute_score`
--

CREATE TABLE `core_value_attribute_score` (
  `id` int NOT NULL,
  `attribute_id` int NOT NULL,
  `score` varchar(12) NOT NULL,
  `date` date NOT NULL,
  `updater` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `core_value_attribute_score`
--

INSERT INTO `core_value_attribute_score` (`id`, `attribute_id`, `score`, `date`, `updater`) VALUES
(1, 1, '15', '2025-07-05', 'ind1'),
(2, 1, '120', '2025-07-05', 'ind1'),
(4, 4, '100', '2025-07-05', 'ind1'),
(5, 1, '180', '2025-07-05', 'ind1'),
(6, 2, '30', '2025-07-11', 'ind7'),
(7, 2, '33', '2025-07-11', 'ind7');

-- --------------------------------------------------------

--
-- Table structure for table `counties`
--

CREATE TABLE `counties` (
  `id` int NOT NULL,
  `name` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `schoolPower` int DEFAULT NULL,
  `schoolPowerTarget` int DEFAULT NULL,
  `schoolTraining` int DEFAULT NULL,
  `schoolTrainingTarget` int DEFAULT NULL,
  `schoolDistribution` int DEFAULT NULL,
  `schoolDistributionTarget` int DEFAULT NULL,
  `landTitle` int DEFAULT NULL,
  `landTitleTarget` int DEFAULT NULL,
  `landRegistry` int DEFAULT NULL,
  `landRegistryTarget` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `county`
--

CREATE TABLE `county` (
  `id` int NOT NULL,
  `name` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dashboard`
--

CREATE TABLE `dashboard` (
  `id` varchar(9) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `name` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `content` varchar(900) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `linkedId` varchar(9) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `dashboard`
--

INSERT INTO `dashboard` (`id`, `name`, `content`, `linkedId`) VALUES
('1', 'Custom Dashboard', 'Custom Dashboard', 'org2'),
('2', 'Strategy Map', 'Strategy Map', 'org2');

-- --------------------------------------------------------

--
-- Table structure for table `formats`
--

CREATE TABLE `formats` (
  `id` int NOT NULL,
  `row` int NOT NULL,
  `col` int NOT NULL,
  `format` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `agency` varchar(480) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `import_data`
--

CREATE TABLE `import_data` (
  `id` int NOT NULL,
  `file` varchar(90) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `measureId` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `measureName` varchar(90) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `value` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `period` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sender` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date` varchar(90) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `import_links`
--

CREATE TABLE `import_links` (
  `id` int NOT NULL,
  `measureId` varchar(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `linkedId` varchar(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `import_map`
--

CREATE TABLE `import_map` (
  `id` int NOT NULL,
  `kpi` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `emailSubject` varchar(90) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `frequency` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sender` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fireDay` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `import_months`
--

CREATE TABLE `import_months` (
  `id` int NOT NULL,
  `measureId` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `month` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `value` varchar(90) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `target` varchar(90) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `individual`
--

CREATE TABLE `individual` (
  `id` varchar(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `name` varchar(240) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `cascadedFrom` varchar(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `photo` varchar(240) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `individual`
--

INSERT INTO `individual` (`id`, `name`, `cascadedFrom`, `photo`) VALUES
('ind3', 'Hellen Chepkwony', 'org1', 'Error'),
('ind4', 'Peter Ibrae', 'org6', 'Error'),
('ind5', 'Paul Manga', 'org7', 'Error'),
('ind6', 'Mary Kiragu', 'org5', 'Error'),
('ind7', 'Lawrence Shoona', 'org4', 'Error'),
('ind8', 'Eunice Kitche', 'org2', ''),
('ind9', 'Crispus Yankem', 'org8', 'Error');

-- --------------------------------------------------------

--
-- Table structure for table `initiative`
--

CREATE TABLE `initiative` (
  `id` int NOT NULL,
  `name` varchar(900) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `sponsor` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `projectManager` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `budget` int DEFAULT NULL,
  `damage` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `dueDate` date DEFAULT NULL,
  `jsDueDate` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `completionDate` date DEFAULT NULL,
  `completionStatus` float DEFAULT NULL,
  `deliverable` varchar(900) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `deliverableStatus` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `scope` varchar(900) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `type` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `parent` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `no_score` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `weight` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `archive` varchar(9) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `lastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `initiative`
--

INSERT INTO `initiative` (`id`, `name`, `sponsor`, `projectManager`, `budget`, `damage`, `startDate`, `dueDate`, `jsDueDate`, `completionDate`, `completionStatus`, `deliverable`, `deliverableStatus`, `scope`, `type`, `parent`, `no_score`, `weight`, `archive`, `lastUpdated`) VALUES
(1, 'Develop, implement & monitor implementation of the departmental work plan', '', 'ind7', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Actioned Departmental Work Plan', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-13 09:15:34'),
(2, 'Develop & monitor implementation of the departmental budget', '', 'ind7', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Fully absorbed budget', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-13 09:35:11'),
(3, 'Make requests for the supplementary budget', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Sufficient budget', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 07:08:30'),
(4, 'Develop, monitor and report status on implementation of the departmental procurement plan', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Procurement plan', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 07:10:55'),
(5, 'Develop and disseminate the Board Action Tracking Log &amp; provide quarterly status updates to the Board', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Active Board Action Tracking Log', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 08:03:12'),
(6, 'Implement &amp; monitor departmental Board Action Points', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Implemented Board Action Points', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 08:05:18'),
(7, 'Assess legal fees &amp; recommend negotiations with advocates', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Reviewed legal fees', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 08:07:29'),
(8, 'Develop revised SLAs', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Revised SLAs', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 08:09:12'),
(9, 'Adopt the use of in-house Counsels', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Increased use of in-house counsels', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 08:11:16'),
(10, 'Undertake stakeholder engagements', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Stakeholder forums', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 08:34:01'),
(11, 'Adhere to and track compliance with the service charter', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Service charter compliance', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 08:55:00'),
(12, 'Develop and implement the DI Academy work-plan', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Implemented DI Academy work-plan', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 08:56:39'),
(13, 'Provide legal advisory services on proposed guidelines for protection of Trust Accounts', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, '', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 12:35:51'),
(14, 'Review requests and make recommendations for the discharge of securities', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, '', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 12:37:47'),
(15, 'Seal discharge of charges and transfer by chargee documentation', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, '', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 12:38:56'),
(16, 'Management of contracts', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, '', 'off', '<div>- Develop &amp; review contracts</div><div>- Witness executed contracts</div><div>- Maintain copies of executed contracts</div>', 'Initiative', '', '', NULL, 'No', '2025-07-02 12:41:07'),
(17, 'Automate board meetings', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Automated board meetings', 'off', '<ul><li>Use of the Eboard system for board meetings</li><li>Digitize board minutes</li><li>Source for new Eboard service provider</li></ul>', 'Initiative', '', '', NULL, 'No', '2025-07-02 16:37:24'),
(18, 'Develop ERP Module for the Legal Department', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Functional Legal Department ERP', 'off', '<ul><li>Digitize executed contracts</li><li>Digitize staff mortgage records</li></ul>', 'Initiative', '', '', NULL, 'No', '2025-07-02 16:39:50'),
(19, 'Implement QMS, ERM, BCMS and other risk management frameworks', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, '', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 16:40:48'),
(20, 'Implement recommendations of the Internal Audit report', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Zero fault audits', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 16:43:39'),
(21, 'Implement a risk management framework', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Robust risk management framework', 'off', '<ul><li>Regular review and update of the legal and departmental risks</li><li>Monitor the departmental internal controls and level of compliance with the Risk Management Policies</li></ul>', 'Initiative', '', '', NULL, 'No', '2025-07-02 16:47:41'),
(22, 'Litigation management', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, '', 'off', '<ul><li>Develop and submit the draft litigation policy to the Board</li><li>Undertake litigation for institutions and the Corporation</li></ul>', 'Initiative', '', '', NULL, 'No', '2025-07-02 16:49:49'),
(23, 'Implement a Board management framework', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Implementable Board resolutions', 'off', '<ul><li>Develop &amp; implement an annual Board work-plan</li><li>Facilitate four Board meetings</li><li>Undertake an annual Board evaluation</li></ul>', 'Initiative', '', '', NULL, 'No', '2025-07-02 16:52:52'),
(24, 'Implementation of Policies', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Approved policies', 'off', '<ul><li>Submission of draft policies for review by the Board</li><li>Collating and disseminating Board feedback on draft policies</li><li>Communicate approval of Board policies</li><li>Facilitate standardization of approved policies</li></ul>', 'Initiative', '', '', NULL, 'No', '2025-07-02 16:55:30'),
(25, 'Provide a memorandum and amendment of the Act', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Amended Act', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 16:56:54'),
(26, 'Submit request for approval of proposed DPS model', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Approved DPS model', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 16:57:54'),
(27, 'Undertake a legal and governance audit', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Legal and governance audit report', 'off', '<ul><li>Carry out a legal and governance audit</li><li>Submit results of the legal and governance audit to the Board for consideration</li></ul>', 'Initiative', '', '', NULL, 'No', '2025-07-02 16:59:52'),
(28, 'KDIC Legal Advisory', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Compliance with law, circulars and gazette notices', 'off', '<ul><li>Maintain and disseminate government circulars</li><li>Provide legal advisories on changes in legislation and gazette notices</li></ul>', 'Initiative', '', '', NULL, 'No', '2025-07-02 17:06:47'),
(29, 'Implement staff mortgage policy', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Operational staff mortgage policy', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 17:08:14'),
(30, 'Develop and implement BSC', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Cascaded scorecard', 'off', '', 'Initiative', '', '', NULL, 'No', '2025-07-02 17:26:34'),
(31, 'Carry out corporate culture survey', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Corporate culture survey report', 'off', '<ul><li>Undertake a corporate culture survey</li><li>implement recommendations of the corporate culture survey report</li></ul>', 'Initiative', '', '', NULL, 'No', '2025-07-02 17:28:13'),
(32, 'Training Needs Analysis', '', 'ind8', NULL, NULL, '2025-07-01', '2026-06-30', NULL, NULL, NULL, 'Trained staff', 'off', '<ul><li>Develop TNA for the department</li><li>implement TNA approvals</li></ul>', 'Initiative', '', '', NULL, 'No', '2025-07-02 17:31:40');

-- --------------------------------------------------------

--
-- Table structure for table `initiativeimpact`
--

CREATE TABLE `initiativeimpact` (
  `id_impact` int NOT NULL,
  `initiativeid` int NOT NULL,
  `linkedobjectid` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `initiativeimpact`
--

INSERT INTO `initiativeimpact` (`id_impact`, `initiativeid`, `linkedobjectid`) VALUES
(1, 1, 'obj121'),
(2, 2, 'obj121'),
(3, 3, 'obj121'),
(4, 4, 'obj121'),
(5, 5, 'obj121'),
(6, 6, 'obj121'),
(7, 7, 'obj121'),
(8, 8, 'obj121'),
(9, 9, 'obj121'),
(10, 10, 'obj122'),
(11, 11, 'obj123'),
(12, 12, 'obj124'),
(13, 13, 'obj125'),
(14, 14, 'obj126'),
(15, 15, 'obj126'),
(16, 16, 'obj127'),
(17, 17, 'obj128'),
(18, 18, 'obj128'),
(19, 19, 'obj129'),
(20, 20, 'obj129'),
(21, 21, 'obj130'),
(22, 22, 'obj131'),
(23, 23, 'obj132'),
(24, 24, 'obj129'),
(25, 25, 'obj133'),
(26, 26, 'obj133'),
(27, 27, 'obj133'),
(28, 28, 'obj133'),
(29, 29, 'obj134'),
(30, 30, 'obj135'),
(31, 31, 'obj136'),
(32, 32, 'obj137');

-- --------------------------------------------------------

--
-- Table structure for table `initiativelinks`
--

CREATE TABLE `initiativelinks` (
  `initiativeId` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `linkedId` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `initiativeteam`
--

CREATE TABLE `initiativeteam` (
  `id` int NOT NULL,
  `user_id` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `initiative_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `initiative_audit`
--

CREATE TABLE `initiative_audit` (
  `id` int NOT NULL,
  `initId` int NOT NULL,
  `name` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `sponsor` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `projectManager` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `budget` int DEFAULT NULL,
  `damage` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `jsStartDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dueDate` date DEFAULT NULL,
  `jsDueDate` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `completionDate` date DEFAULT NULL,
  `completionStatus` float DEFAULT NULL,
  `deliverable` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `deliverableStatus` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `type` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `parent` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `PeriodAgreement` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `KRA` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Manifesto` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Contract` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Grant` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `gok` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Purpose` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Agreement` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `PMP` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `WorkPlan` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Report` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `SiteVisit` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `OriginalBudget` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Funding` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Accruals` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `BurnRate` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `ActualBurnRate` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Achievement` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Challenge` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Forward` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Other` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `executiveNotes` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `period` date DEFAULT NULL,
  `time` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `updater` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `initiative_evidence`
--

CREATE TABLE `initiative_evidence` (
  `id` int NOT NULL,
  `initiativeId` varchar(12) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `name` varchar(900) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `location` varchar(900) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `size` int NOT NULL,
  `type` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `initiative_issue`
--

CREATE TABLE `initiative_issue` (
  `id` int NOT NULL,
  `initiativeId` int NOT NULL,
  `issue` varchar(3000) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `wayForward` varchar(3000) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `owner` varchar(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `status` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `severity` varchar(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `updatedBy` varchar(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `updatedOn` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `initiative_status`
--

CREATE TABLE `initiative_status` (
  `id` int NOT NULL,
  `initiativeId` int NOT NULL,
  `status` varchar(90) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `percentageCompletion` varchar(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `details` varchar(9000) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `notes` varchar(3000) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `updatedOn` datetime NOT NULL,
  `updatedBy` varchar(90) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `initiative_status`
--

INSERT INTO `initiative_status` (`id`, `initiativeId`, `status`, `percentageCompletion`, `details`, `notes`, `updatedOn`, `updatedBy`) VALUES
(3, 3, 'On Track', '33', '', '', '2025-07-11 09:53:57', 'ind1'),
(4, 4, 'On Track', '53', '', '', '2025-07-11 09:53:57', 'ind1'),
(5, 1, 'On Track', '10', '', '', '2025-07-11 09:53:57', 'ind1');

-- --------------------------------------------------------

--
-- Table structure for table `investments`
--

CREATE TABLE `investments` (
  `id` int NOT NULL,
  `name` varchar(900) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `activity` varchar(900) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `amount` varchar(300) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `location` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kpicommentary`
--

CREATE TABLE `kpicommentary` (
  `id` int NOT NULL,
  `kpiName` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiQuantitative` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `dateCreated` date NOT NULL,
  `defStatus` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `dateModified` date NOT NULL,
  `reportStatus` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `abbreviation` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `lifePriority` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiIntegrity` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiObjective` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `kpiLevel` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiGoal` varchar(600) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiUnit` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiDescr` varchar(600) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiIntent` varchar(600) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiProcess` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiStakeholder` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiRelationship` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiFormula` varchar(300) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiFrequency` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiScope` varchar(600) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiDrill` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiComparison` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiMethod` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiPresentNotes` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiFrequency2` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiResponse` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiOwnerDefinition` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiOwnerPerformance` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiNotes` varchar(600) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kpiOwnerReporting` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `linkedTo` varchar(9) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `treeId` varchar(9) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kpidesign`
--

CREATE TABLE `kpidesign` (
  `id` int NOT NULL,
  `objName` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `sensory` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `potential` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `picture` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `kpiName` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `kpiLinkedTo` varchar(9) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kpi_audit`
--

CREATE TABLE `kpi_audit` (
  `id` int NOT NULL,
  `measureId` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `date` date NOT NULL,
  `actual` int DEFAULT NULL,
  `red` int DEFAULT NULL,
  `blue` int DEFAULT NULL,
  `green` int DEFAULT NULL,
  `darkgreen` int DEFAULT NULL,
  `2score` int DEFAULT NULL,
  `3score` float DEFAULT NULL,
  `4score` int DEFAULT NULL,
  `5score` int DEFAULT NULL,
  `UNPL` int DEFAULT NULL,
  `LNPL` int DEFAULT NULL,
  `centralLine` int DEFAULT NULL,
  `mR` int DEFAULT NULL,
  `signalPointer` int DEFAULT NULL,
  `updater` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `time` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `kpi_audit`
--

INSERT INTO `kpi_audit` (`id`, `measureId`, `date`, `actual`, `red`, `blue`, `green`, `darkgreen`, `2score`, `3score`, `4score`, `5score`, `UNPL`, `LNPL`, `centralLine`, `mR`, `signalPointer`, `updater`, `time`) VALUES
(86954, 'kpi2889', '2024-07-01', NULL, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281128),
(86955, 'kpi2889', '2024-08-01', NULL, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281128),
(86956, 'kpi2889', '2024-09-01', NULL, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281128),
(86957, 'kpi2889', '2024-10-01', NULL, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281128),
(86958, 'kpi2889', '2024-11-01', NULL, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281128),
(86959, 'kpi2889', '2024-12-01', NULL, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281128),
(86960, 'kpi2889', '2025-01-01', NULL, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281128),
(86961, 'kpi2889', '2025-02-01', NULL, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281128),
(86962, 'kpi2889', '2025-03-01', NULL, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281128),
(86963, 'kpi2889', '2025-04-01', NULL, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281128),
(86964, 'kpi2889', '2025-05-01', 75, 30, 0, 50, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281128),
(86965, 'kpi2889', '2025-06-01', 55, 30, 0, 50, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281128),
(86966, 'kpi2890', '2024-07-01', NULL, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281150),
(86967, 'kpi2890', '2024-08-01', NULL, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281150),
(86968, 'kpi2890', '2024-09-01', NULL, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281150),
(86969, 'kpi2890', '2024-10-01', NULL, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281150),
(86970, 'kpi2890', '2024-11-01', NULL, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281150),
(86971, 'kpi2890', '2024-12-01', NULL, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281150),
(86972, 'kpi2890', '2025-01-01', 15, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281150),
(86973, 'kpi2890', '2025-02-01', 25, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281150),
(86974, 'kpi2890', '2025-03-01', 35, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281150),
(86975, 'kpi2890', '2025-04-01', 85, 60, 0, 90, 0, 0, 8.88889, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281150),
(86976, 'kpi2890', '2025-05-01', 45, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281150),
(86977, 'kpi2890', '2025-06-01', 98, 60, 0, 90, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281150),
(86978, 'kpi2891', '2024-07-01', NULL, 30, 0, 80, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281174),
(86979, 'kpi2891', '2024-08-01', NULL, 30, 0, 80, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281174),
(86980, 'kpi2891', '2024-09-01', NULL, 30, 0, 80, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281174),
(86981, 'kpi2891', '2024-10-01', NULL, 30, 0, 80, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281174),
(86982, 'kpi2891', '2024-11-01', NULL, 30, 0, 80, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281174),
(86983, 'kpi2891', '2024-12-01', NULL, 30, 0, 80, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281174),
(86984, 'kpi2891', '2025-01-01', NULL, 30, 0, 80, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281174),
(86985, 'kpi2891', '2025-02-01', 89, 30, 0, 80, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281174),
(86986, 'kpi2891', '2025-03-01', 66, 30, 0, 80, 0, 0, 8.13333, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281174),
(86987, 'kpi2891', '2025-04-01', 45, 30, 0, 80, 0, 0, 5.33333, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281174),
(86988, 'kpi2891', '2025-05-01', 78, 30, 0, 80, 0, 0, 9.73333, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281174),
(86989, 'kpi2891', '2025-06-01', 90, 30, 0, 80, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281174),
(86990, 'kpi2892', '2024-07-01', NULL, 5, 0, 20, 0, 0, 1.11111, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281208),
(86991, 'kpi2892', '2024-08-01', NULL, 5, 0, 20, 0, 0, 1.11111, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281208),
(86992, 'kpi2892', '2024-09-01', NULL, 5, 0, 20, 0, 0, 1.11111, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281208),
(86993, 'kpi2892', '2024-10-01', NULL, 5, 0, 20, 0, 0, 1.11111, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281208),
(86994, 'kpi2892', '2024-11-01', NULL, 5, 0, 20, 0, 0, 1.11111, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281208),
(86995, 'kpi2892', '2024-12-01', NULL, 5, 0, 20, 0, 0, 1.11111, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281208),
(86996, 'kpi2892', '2025-01-01', 8, 5, 0, 20, 0, 0, 4.66667, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281208),
(86997, 'kpi2892', '2025-02-01', 4, 5, 0, 20, 0, 0, 2.88889, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281208),
(86998, 'kpi2892', '2025-03-01', 8, 5, 0, 20, 0, 0, 4.66667, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281208),
(86999, 'kpi2892', '2025-04-01', 15, 5, 0, 20, 0, 0, 7.77778, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281208),
(87000, 'kpi2892', '2025-05-01', 20, 5, 0, 20, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281208),
(87001, 'kpi2892', '2025-06-01', 45, 5, 0, 20, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750281208),
(87002, 'kpi2894', '2024-07-01', NULL, 32, 0, 87, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283172),
(87003, 'kpi2894', '2024-08-01', NULL, 32, 0, 87, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283172),
(87004, 'kpi2894', '2024-09-01', NULL, 32, 0, 87, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283172),
(87005, 'kpi2894', '2024-10-01', NULL, 32, 0, 87, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283172),
(87006, 'kpi2894', '2024-11-01', NULL, 32, 0, 87, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283172),
(87007, 'kpi2894', '2024-12-01', NULL, 32, 0, 87, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283172),
(87008, 'kpi2894', '2025-01-01', 100, 32, 0, 87, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283172),
(87009, 'kpi2894', '2025-02-01', 99, 32, 0, 87, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283172),
(87010, 'kpi2894', '2025-03-01', 98, 32, 0, 87, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283172),
(87011, 'kpi2894', '2025-04-01', 90, 32, 0, 87, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283172),
(87012, 'kpi2894', '2025-05-01', 89, 32, 0, 87, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283172),
(87013, 'kpi2894', '2025-06-01', 89, 32, 0, 87, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283172),
(87014, 'kpi2893', '2024-07-01', 0, 32, 0, 54, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283197),
(87015, 'kpi2893', '2024-07-01', 0, 32, 0, 54, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283247),
(87016, 'kpi2893', '2024-08-01', 0, 32, 0, 54, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750283247),
(87017, 'kpi2893', '2024-07-01', 0, 32, 0, 54, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417109),
(87018, 'kpi2893', '2024-08-01', 0, 32, 0, 54, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417109),
(87019, 'kpi2893', '2024-09-01', 0, 32, 0, 54, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417109),
(87020, 'kpi2894', '2024-07-01', 0, 32, 0, 87, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417153),
(87021, 'kpi2894', '2024-08-01', 0, 32, 0, 87, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417153),
(87022, 'kpi2894', '2024-09-01', 0, 32, 0, 87, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417153),
(87023, 'kpi2894', '2024-10-01', 0, 32, 0, 87, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417153),
(87024, 'kpi2894', '2024-11-01', 0, 32, 0, 87, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417153),
(87025, 'kpi2894', '2024-12-01', 0, 32, 0, 87, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417153),
(87026, 'kpi2894', '2025-01-01', 100, 32, 0, 87, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417153),
(87027, 'kpi2894', '2025-02-01', 99, 32, 0, 87, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417153),
(87028, 'kpi2894', '2025-03-01', 98, 32, 0, 87, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417153),
(87029, 'kpi2894', '2025-04-01', 90, 32, 0, 87, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417153),
(87030, 'kpi2894', '2025-05-01', 89, 32, 0, 87, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417153),
(87031, 'kpi2894', '2025-06-01', 600, 32, 0, 87, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417153),
(87032, 'kpi2893', '2024-07-01', 0, 32, 0, 54, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417171),
(87033, 'kpi2893', '2024-08-01', 0, 32, 0, 54, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417171),
(87034, 'kpi2893', '2024-09-01', 0, 32, 0, 54, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417171),
(87035, 'kpi2893', '2024-10-01', 0, 32, 0, 54, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750417171),
(87036, 'kpi2861', '2024-01-01', 100, 50, 95, 80, 90, 0, 0, 0, 8, NULL, NULL, NULL, NULL, NULL, 'test', 1750421168),
(87037, 'kpi2890', '2024-07-01', 0, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750421344),
(87038, 'kpi2890', '2024-08-01', 0, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750421344),
(87039, 'kpi2890', '2024-09-01', 0, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750421344),
(87040, 'kpi2890', '2024-10-01', 0, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750421344),
(87041, 'kpi2890', '2024-11-01', 0, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750421344),
(87042, 'kpi2890', '2024-12-01', 0, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750421344),
(87043, 'kpi2890', '2025-01-01', 15, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750421344),
(87044, 'kpi2890', '2025-02-01', 25, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750421344),
(87045, 'kpi2890', '2025-03-01', 35, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750421344),
(87046, 'kpi2890', '2025-04-01', 85, 60, 0, 90, 0, 0, 8.88889, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750421344),
(87047, 'kpi2890', '2025-05-01', 45, 60, 0, 90, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750421344),
(87048, 'kpi2890', '2025-06-01', 120, 60, 0, 90, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750421344),
(87049, 'kpi2889', '2024-07-01', 0, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750422599),
(87050, 'kpi2889', '2024-08-01', 0, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750422599),
(87051, 'kpi2889', '2024-09-01', 0, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750422599),
(87052, 'kpi2889', '2024-10-01', 0, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750422599),
(87053, 'kpi2889', '2024-11-01', 0, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750422599),
(87054, 'kpi2889', '2024-12-01', 0, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750422599),
(87055, 'kpi2889', '2025-01-01', 0, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750422599),
(87056, 'kpi2889', '2025-02-01', 0, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750422599),
(87057, 'kpi2889', '2025-03-01', 0, 30, 0, 50, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750422599),
(87058, 'kpi2889', '2025-04-01', 90, 30, 0, 50, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750422599),
(87059, 'kpi2889', '2025-05-01', 75, 30, 0, 50, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750422599),
(87060, 'kpi2889', '2025-06-01', 55, 30, 0, 50, 0, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1750422599),
(87061, 'kpi29', '2024-08-01', NULL, 78, 0, 100, NULL, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1752180461),
(87062, 'kpi29', '2024-09-01', NULL, 78, 0, 100, NULL, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1752180461),
(87063, 'kpi29', '2024-10-01', NULL, 78, 0, 100, NULL, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1752180461),
(87064, 'kpi29', '2024-11-01', NULL, 78, 0, 100, NULL, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1752180461),
(87065, 'kpi29', '2024-12-01', NULL, 78, 0, 100, NULL, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1752180461),
(87066, 'kpi29', '2025-01-01', NULL, 78, 0, 100, NULL, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1752180461),
(87067, 'kpi29', '2025-02-01', NULL, 78, 0, 100, NULL, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1752180461),
(87068, 'kpi29', '2025-03-01', NULL, 78, 0, 100, NULL, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1752180461),
(87069, 'kpi29', '2025-04-01', NULL, 78, 0, 100, NULL, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1752180461),
(87070, 'kpi29', '2025-05-01', 75, 78, 0, 100, NULL, 0, 2.42424, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1752180461),
(87071, 'kpi29', '2025-06-01', 90, 78, 0, 100, NULL, 0, 6.9697, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1752180461),
(87072, 'kpi29', '2025-07-01', 100, 78, 0, 100, NULL, 0, 10, 0, 0, NULL, NULL, NULL, NULL, NULL, 'Accent Import', 1752180461);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `user_id` int NOT NULL,
  `time` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`user_id`, `time`) VALUES
(1, '1385995353'),
(1, '1386011064'),
(1, '1400245722');

-- --------------------------------------------------------

--
-- Table structure for table `mail`
--

CREATE TABLE `mail` (
  `id` int NOT NULL,
  `title` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `message` varchar(9000) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `sender` varchar(240) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `recipient` varchar(240) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `time` datetime NOT NULL,
  `type` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `user_id` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `measure`
--

CREATE TABLE `measure` (
  `id` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `name` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `calendarType` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `measureType` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `description` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `linkedObject` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `dataType` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `aggregationType` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `owner` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `updater` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `location` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `red` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `blue` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `green` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `darkgreen` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `parentMeasure` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `gaugeType` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `weight` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `archive` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `sort` int NOT NULL DEFAULT '0',
  `tags` varchar(9000) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `measure`
--

INSERT INTO `measure` (`id`, `name`, `calendarType`, `measureType`, `description`, `linkedObject`, `dataType`, `aggregationType`, `owner`, `updater`, `location`, `red`, `blue`, `green`, `darkgreen`, `parentMeasure`, `gaugeType`, `weight`, `archive`, `sort`, `tags`) VALUES
('kpi1', 'Growth of the Investment income', 'Yearly', 'Standard KPI', '', 'obj111', 'Currency', 'Last Value', 'ind0', 'ind0', 'pc', '26960000000', '0', '31200000000', '', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi10', '% of Fund to Insured Deposit (Coverage Ratio)', 'Yearly', 'Standard KPI', '', 'obj116', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '25.49', '0', '25.8', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi100', 'Approved Knowledge Management Framewoork', 'Yearly', 'Standard KPI', '', 'obj169', 'Standard', 'Last Value', 'ind4', 'ind4', NULL, '0', '0', '2', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]'),
('kpi101', 'Team Building Activities', 'Yearly', 'Standard KPI', '', 'obj169', 'Standard', 'Last Value', 'ind4', 'ind4', NULL, '0', '0', '3', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]'),
('kpi102', 'Approved Staff Wellness Program', 'Yearly', 'Standard KPI', '', 'obj169', 'Standard', 'Last Value', 'ind4', 'ind4', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]'),
('kpi103', 'Workload Analysis Report', 'Yearly', 'Standard KPI', '', 'obj169', 'Standard', 'Last Value', 'ind4', 'ind4', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]'),
('kpi104', 'Approved Records Management Policy', 'Yearly', 'Standard KPI', '', 'obj169', 'Standard', 'Last Value', 'ind4', 'ind4', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]'),
('kpi105', 'Interns and Attachess Recruited', 'Yearly', 'Standard KPI', '', 'obj169', 'Standard', 'Last Value', '', '', NULL, '28', '0', '20', '', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi106', 'Monthly Payrolls', 'Yearly', 'Standard KPI', '', 'obj169', 'Standard', 'Last Value', 'ind4', 'ind4', NULL, '0', '0', '12', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]'),
('kpi107', 'Approved Salary Survey Report', 'Yearly', 'Standard KPI', '', 'obj169', 'Standard', 'Last Value', '', '', NULL, '0', '0', '1', '', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi108', 'Level of Performance Management Implementation', 'Yearly', 'Standard KPI', '', 'obj170', 'Percentage(%)', 'Last Value', 'ind4', 'ind4', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]'),
('kpi109', 'Level of Implementation of HR Training Plan', 'Yearly', 'Standard KPI', '', 'obj170', 'Percentage(%)', 'Last Value', 'ind4', 'ind4', NULL, '22', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]'),
('kpi11', '% of Insured Deposits to Total Deposits', 'Yearly', 'Standard KPI', '', 'obj116', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '15.69', '0', '15.80', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi110', 'Growth of Investment Income', 'Monthly', 'Standard KPI', '', 'obj172', 'Currency', 'Last Value', 'ind5', 'ind5', NULL, '26960000000', '0', '31200000000', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi111', 'Budget Absorption Rate', 'Monthly', 'Standard KPI', '', 'obj173', 'Percentage(%)', 'Last Value', 'ind5', 'ind5', NULL, '78', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi112', 'Level of Adherence to Procurement Plan', 'Monthly', 'Standard KPI', '', 'obj173', 'Percentage(%)', 'Last Value', 'ind5', 'ind5', NULL, '93', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi113', 'Implementation of Board Resolutions', 'Monthly', 'Standard KPI', '', 'obj173', 'Percentage(%)', 'Last Value', 'ind5', 'ind5', NULL, '79.6', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi114', 'Public Awareness Index', 'Yearly', 'Standard KPI', '', 'obj174', 'Percentage(%)', 'Last Value', 'ind5', 'ind5', NULL, '14', '0', '23', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi115', 'Number of Engagements', 'Yearly', 'Standard KPI', '', 'obj175', 'Standard', 'Last Value', 'ind5', 'ind5', NULL, '0', '0', '4', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi116', 'Approval of the deposit Insurance Academy', 'Yearly', 'Standard KPI', '', 'obj175', 'Percentage(%)', 'Last Value', 'ind5', 'ind5', NULL, '70', '0', '90', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi117', 'Technical Assistance Offered', 'Yearly', 'Standard KPI', '', 'obj175', 'Standard', 'Last Value', 'ind5', 'ind5', NULL, '0', '0', '2', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi118', '% of Fund to Insured Deposit', 'Yearly', 'Standard KPI', '', 'obj176', 'Percentage(%)', 'Last Value', 'ind5', 'ind5', NULL, '25.49', '0', '25.80', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi119', '% of insured Deposits to Total Deposits', 'Yearly', 'Standard KPI', '', 'obj176', 'Percentage(%)', 'Last Value', 'ind5', 'ind5', NULL, '15.69', '0', '15.80', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi12', 'No. of targeted institutions for winding up', 'Yearly', 'Standard KPI', '', 'obj117', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '2', '0', '6', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi120', 'Certification Retained', 'Yearly', 'Standard KPI', '', 'obj177', 'Standard', 'Last Value', 'ind5', 'ind5', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi121', 'Living Wills', 'Yearly', 'Standard KPI', '', 'obj178', 'Standard', 'Last Value', 'ind5', 'ind5', NULL, '0', '0', '2', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi122', 'AI Report', 'Yearly', 'Standard KPI', '', 'obj178', 'Standard', 'Last Value', 'ind5', 'ind5', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi123', 'CAMEL Reports', 'Yearly', 'Standard KPI', '', 'obj179', 'Standard', 'Last Value', 'ind5', 'ind5', NULL, '52', '0', '104', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi124', 'Simulation Exercises', 'Yearly', 'Standard KPI', '', 'obj180', 'Standard', 'Last Value', 'ind5', 'ind5', NULL, '0', '0', '2', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi125', 'Implemented Regulations', 'Yearly', 'Standard KPI', '', 'obj181', 'Standard', 'Last Value', 'ind5', 'ind5', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi126', 'Productivity Reports', 'Yearly', 'Standard KPI', '', 'obj182', 'Standard', 'Last Value', 'ind5', 'ind5', NULL, '0', '0', '4', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi127', 'Recommendations Implemented', 'Yearly', 'Standard KPI', '', 'obj182', 'Standard', 'Last Value', 'ind5', 'ind5', NULL, '0', '0', '2', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind5\\\",\\\"label\\\":\\\"Paul Manga\\\"}]'),
('kpi128', 'ERM Assurance Reports', 'Yearly', 'Standard KPI', '', 'obj184', 'Standard', 'Last Value', 'ind6', 'ind6', NULL, '0', '0', '4', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind6\\\",\\\"label\\\":\\\"Mary Kiragu\\\"}]'),
('kpi129', 'Level of Conformance to ISO 9001 and ISO 27001', 'Yearly', 'Standard KPI', '', 'obj185', 'Percentage(%)', 'Last Value', 'ind6', 'ind6', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind6\\\",\\\"label\\\":\\\"Mary Kiragu\\\"}]'),
('kpi13', 'No of institutions wound up', 'Yearly', 'Standard KPI', '', 'obj117', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '3', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi130', '% implementation fo the internal audit standards', 'Yearly', 'Standard KPI', '', 'obj186', 'Percentage(%)', 'Last Value', 'ind6', 'ind6', NULL, '0', '0', '70', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind6\\\",\\\"label\\\":\\\"Mary Kiragu\\\"}]'),
('kpi131', 'Level of utilization of internal audit software', 'Yearly', 'Standard KPI', '', 'obj160', 'Percentage(%)', 'Last Value', 'ind6', 'ind6', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind6\\\",\\\"label\\\":\\\"Mary Kiragu\\\"}]'),
('kpi132', 'Absorption of budget', 'Yearly', 'Standard KPI', '', 'obj187', 'Percentage(%)', 'Last Value', 'ind9', 'ind9', NULL, '78', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind9\\\",\\\"label\\\":\\\"Crispus Yankem\\\"}]'),
('kpi133', 'implementation of the department\\\'s procurement plan', 'Yearly', 'Standard KPI', '', 'obj187', 'Percentage(%)', 'Last Value', 'ind9', 'ind9', NULL, '93', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind9\\\",\\\"label\\\":\\\"Crispus Yankem\\\"}]'),
('kpi134', 'Implementation of Board Resolutions', 'Yearly', 'Standard KPI', '', 'obj187', 'Percentage(%)', 'Last Value', 'ind9', 'ind9', NULL, '79.6', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind9\\\",\\\"label\\\":\\\"Crispus Yankem\\\"}]'),
('kpi135', 'Public Awareness Index', 'Yearly', 'Standard KPI', '', 'obj188', 'Percentage(%)', 'Last Value', 'ind9', 'ind9', NULL, '14', '0', '20', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind9\\\",\\\"label\\\":\\\"Crispus Yankem\\\"}]'),
('kpi136', 'Brand Perception Index', 'Yearly', 'Standard KPI', '', 'obj188', 'Percentage(%)', 'Last Value', 'ind9', 'ind9', NULL, '69', '0', '73', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind9\\\",\\\"label\\\":\\\"Crispus Yankem\\\"}]'),
('kpi137', 'Departmental Reports', 'Yearly', 'Standard KPI', '', 'obj189', 'Standard', 'Last Value', 'ind9', 'ind9', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind9\\\",\\\"label\\\":\\\"Crispus Yankem\\\"}]'),
('kpi138', 'Productivity Index Report', 'Yearly', 'Standard KPI', '', 'obj190', 'Standard', 'Last Value', 'ind9', 'ind9', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind9\\\",\\\"label\\\":\\\"Crispus Yankem\\\"}]'),
('kpi139', 'Documentation reports', 'Yearly', 'Standard KPI', '', 'obj191', 'Standard', 'Last Value', 'ind9', 'ind9', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind9\\\",\\\"label\\\":\\\"Crispus Yankem\\\"}]'),
('kpi14', 'Amount of dividend declared', 'Yearly', 'Standard KPI', '', 'obj117', 'Currency', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '150000000', '', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi140', 'Grow Investment Income of the institutions in liquidation', 'Yearly', 'Standard KPI', '', 'obj192', 'Currency', 'Last Value', 'ind3', 'ind3', NULL, '524000000', '0', '550000000', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi141', 'Budget Absorption rate', 'Yearly', 'Standard KPI', '', 'obj193', 'Percentage(%)', 'Last Value', 'ind3', 'ind3', NULL, '92', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi142', 'Level of adherence to procurement plan', 'Yearly', 'Standard KPI', '', 'obj193', 'Percentage(%)', 'Last Value', 'ind3', 'ind3', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi143', 'Board Resolutions Implemented', 'Yearly', 'Standard KPI', '', 'obj193', 'Percentage(%)', 'Last Value', 'ind3', 'ind3', NULL, '88', '0', '100', '', NULL, 'threeColor', '', 'No', 3000, '[{\"value\":\"ind3\",\"label\":\"Hellen Chepkwony\"}]'),
('kpi144', 'Ratio of Total Direct Costs/ Total Interest Income', 'Yearly', 'Standard KPI', '', 'obj193', 'Percentage(%)', 'Last Value', 'ind3', 'ind3', NULL, '80', '0', '78', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi145', 'Amount of Loans', 'Yearly', 'Standard KPI', '', 'obj194', 'Currency', 'Last Value', 'ind3', 'ind3', NULL, '790000000', '0', '850000000', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi146', 'Number of Engagements', 'Yearly', 'Standard KPI', '', 'obj195', 'Standard', 'Last Value', 'ind3', 'ind3', NULL, '3', '0', '4', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi147', 'Number of Complaints Resolved', 'Yearly', 'Standard KPI', '', 'obj195', 'Percentage(%)', 'Last Value', 'ind3', 'ind3', NULL, '93', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi148', 'Number of Engagements', 'Yearly', 'Standard KPI', '', 'obj196', 'Standard', 'Last Value', 'ind3', 'ind3', NULL, '0', '0', '2', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi149', 'No of targeted institutions', 'Yearly', 'Standard KPI', '', 'obj197', 'Standard', 'Last Value', 'ind3', 'ind3', NULL, '1', '0', '2', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi15', 'Number of processes automated', 'Yearly', 'Standard KPI', '', 'obj118', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '29', '0', '95', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi150', 'No of Institutions Wound Up', 'Yearly', 'Standard KPI', '', 'obj197', 'Standard', 'Last Value', 'ind3', 'ind3', NULL, '1', '0', '2', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi151', 'Amount of Dividends Declared', 'Yearly', 'Standard KPI', '', 'obj197', 'Currency', 'Last Value', 'ind3', 'ind3', NULL, '0', '0', '150000000', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi152', 'Functional Digitized Channel', 'Yearly', 'Standard KPI', '', 'obj198', 'Standard', 'Last Value', 'ind3', 'ind3', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi153', 'No of Identified Records Digitized', 'Yearly', 'Standard KPI', '', 'obj198', 'Standard', 'Last Value', 'ind3', 'ind3', NULL, '500000', '0', '1000000', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi154', 'Certifications Retained', 'Yearly', 'Standard KPI', '', 'obj199', 'Standard', 'Last Value', 'ind3', 'ind3', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi155', 'Simulation Exercises', 'Yearly', 'Standard KPI', '', 'obj200', 'Standard', 'Last Value', 'ind3', 'ind3', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi156', 'Implemented Recommendations', 'Yearly', 'Standard KPI', '', 'obj201', 'Percentage(%)', 'Last Value', 'ind3', 'ind3', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi157', 'Productivity Index Improved', 'Yearly', 'Standard KPI', '', 'obj202', 'Percentage(%)', 'Last Value', 'ind3', 'ind3', NULL, '2.323', '0', '2.50', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi158', 'Culture Entropy', 'Yearly', 'Standard KPI', '', 'obj203', 'Percentage(%)', 'Last Value', '', '', NULL, '20', '0', '22', '', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi159', 'Recommendations Implemented', 'Yearly', 'Standard KPI', '', 'obj203', 'Standard', 'Last Value', 'ind3', 'ind3', NULL, '13', '0', '25', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind3\\\",\\\"label\\\":\\\"Hellen Chepkwony\\\"}]'),
('kpi16', 'Number of identified records digitized', 'Yearly', 'Standard KPI', '', 'obj118', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '500000', '0', '1000000', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi17', 'Certifications Retained', 'Yearly', 'Standard KPI', '', 'obj118', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi18', 'Living Wills', 'Yearly', 'Standard KPI', '', 'obj119', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '2', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi19', 'AI Report', 'Yearly', 'Standard KPI', '', 'obj119', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi2', 'Budget absorption rate', 'Monthly', 'Standard KPI', '', 'obj112', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '78', '0', '100', '', NULL, 'threeColor', '', 'No', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('kpi20', 'CAMEL Reports', 'Yearly', 'Standard KPI', '', 'obj119', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '52', '0', '104', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi21', 'Simulation exercises', 'Yearly', 'Standard KPI', '', 'obj119', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '1', '0', '2', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi22', 'Implemented Recommendations', 'Yearly', 'Standard KPI', '', 'obj119', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi23', 'Retention Rate', 'Yearly', 'Standard KPI', '', 'obj120', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '95', '0', '96', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi24', 'Staff Recruited', 'Yearly', 'Standard KPI', '', 'obj120', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '11', '0', '24', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi25', 'Implemented Recommendations', 'Yearly', 'Standard KPI', '', 'obj120', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi26', 'Productivity Index Improved', 'Yearly', 'Standard KPI', '', 'obj120', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '2.323', '0', '2.5', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi27', 'Culture Entropy', 'Yearly', 'Standard KPI', '', 'obj120', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '22', '0', '20', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi28', 'Corporate Culture: Recommendations', 'Yearly', 'Standard KPI', 'Corporate Culture', 'obj120', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '13', '0', '25', '', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi29', 'Budget absorption rate', 'Monthly', 'Standard KPI', '', 'obj121', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '78', '0', '100', '', NULL, 'threeColor', '', 'No', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('kpi3', 'Level of adherence to procurement plan', 'Monthly', 'Standard KPI', '', 'obj112', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '100', '0', '93', '', NULL, 'threeColor', '', 'No', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('kpi30', 'Level of adherence to procurement plan', 'Monthly', 'Standard KPI', '', 'obj121', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '100', '0', '93', '', NULL, 'threeColor', '', 'No', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('kpi31', 'Implementation of board resolutions', 'Monthly', 'Standard KPI', '', 'obj121', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '79.6', '0', '100', '', NULL, 'threeColor', '', 'No', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('kpi32', 'Management of Litigation Costs', 'Monthly', 'Standard KPI', '', 'obj121', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '79.6', '0', '100', '', NULL, 'threeColor', '', 'No', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('kpi33', 'Number of engagements', 'Monthly', 'Standard KPI', '', 'obj122', '', 'Last Value', 'ind8', 'ind8', NULL, '2', '0', '2', '', NULL, 'goalOnly', '', 'No', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('kpi34', 'Level of compliance with service delivery timelines', 'Monthly', 'Standard KPI', '', 'obj123', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '69', '0', '71', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi35', 'Approval of the Deposit Insurance Academy', 'Monthly', 'Standard KPI', '', 'obj124', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '70', '0', '90', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi36', 'Development & implementation of proposed guidelines on Trust Accounts', 'Monthly', 'Standard KPI', '', 'obj125', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '15.69', '0', '15.80', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi37', 'Percentage of discharges processed against requests made', 'Monthly', 'Standard KPI', '', 'obj126', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '50', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi38', 'Percentage of contracts reviewed against requests made', 'Monthly', 'Standard KPI', '', 'obj127', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '50', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi39', 'Percentage level of digitization of legal & board records', 'Monthly', 'Standard KPI', '', 'obj128', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '30', '0', '60', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi4', 'Implementation of board resolutions', 'Monthly', 'Standard KPI', '', 'obj112', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '79.6', '0', '100', '', NULL, 'threeColor', '', 'No', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('kpi40', 'Standardise processes', 'Monthly', 'Standard KPI', '', 'obj129', 'Standard', 'Last Value', 'ind8', 'ind8', NULL, '26', '0', '53', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi41', 'Closure of internal audit findings', 'Monthly', 'Standard KPI', '', 'obj129', 'Standard', 'Last Value', 'ind8', 'ind8', NULL, '26', '0', '53', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi42', 'Implementation of policies', 'Monthly', 'Standard KPI', '', 'obj129', 'Standard', 'Last Value', 'ind8', 'ind8', NULL, '26', '0', '53', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi43', 'Percentage of assessment undertaken against requests made', 'Monthly', 'Standard KPI', '', 'obj130', 'Standard', 'Last Value', 'ind8', 'ind8', NULL, '26', '0', '53', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi44', 'Litigation management', 'Monthly', 'Standard KPI', '', 'obj131', 'Standard', 'Last Value', 'ind8', 'ind8', NULL, '26', '0', '53', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi45', 'Management of board affairs', 'Monthly', 'Standard KPI', '', 'obj132', 'Standard', 'Last Value', 'ind8', 'ind8', NULL, '26', '0', '53', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi46', 'Recommend & seek review of the sections of the KDI Act', 'Monthly', 'Standard KPI', '', 'obj133', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '50', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi47', 'Seek approval for proposed DPS model', 'Monthly', 'Standard KPI', '', 'obj133', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '50', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi48', 'Implement recommendations of the compliance & governance audit', 'Monthly', 'Standard KPI', '', 'obj133', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '50', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi49', 'Enhance corporate governance', 'Monthly', 'Standard KPI', '', 'obj133', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '26', '0', '53', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi5', 'Management of litigation costs', 'Yearly', 'Standard KPI', '', 'obj112', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '79.6', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi50', 'Percentage of staff charges/discharges undertaken against requests made', 'Monthly', 'Standard KPI', '', 'obj134', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '50', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi51', 'Enhance staff productivity', 'Monthly', 'Standard KPI', '', 'obj135', 'Standard', 'Last Value', 'ind8', 'ind8', NULL, '2.323', '0', '2.5', '', NULL, 'threeColor', '', 'No', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('kpi52', 'Culture entropy', 'Monthly', 'Standard KPI', '', 'obj136', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '22', '0', '20', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi53', 'Staff retention', 'Monthly', 'Standard KPI', '', 'obj137', 'Percentage(%)', 'Last Value', 'ind8', 'ind8', NULL, '94', '0', '95', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind8\\\",\\\"label\\\":\\\"Eunice Kitche\\\"}]'),
('kpi54', 'Absorption rate of allocated funds', 'Monthly', 'Standard KPI', '', 'obj138', 'Percentage(%)', 'Last Value', 'ind7', 'ind7', NULL, '95', '0', '100', '', NULL, 'threeColor', '', 'No', 3000, '[{\"value\":\"ind7\",\"label\":\"Lawrence Shoona\"}]'),
('kpi55', 'Percentage level of compliance', 'Monthly', 'Standard KPI', '', 'obj139', 'Percentage(%)', 'Last Value', 'ind7', 'ind7', NULL, '90', '0', '100', '', NULL, 'threeColor', '', 'No', 3000, '[{\"value\":\"ind7\",\"label\":\"Lawrence Shoona\"}]'),
('kpi56', 'Enhance corporate performance management', 'Monthly', 'Standard KPI', 'corporate planning implementation rate', 'obj140', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '90', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi57', 'Promote good corporate governance practices', 'Monthly', 'Standard KPI', 'Strategic plan quarterly reports', 'obj140', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '3', '0', '4', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi58', 'Improve corporate performance', 'Monthly', 'Standard KPI', 'Performance contracting quarterly reports', 'obj140', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '3', '0', '4', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi59', 'Corporate planning implementattion rate', 'Monthly', 'Standard KPI', '', 'obj142', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '90', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi6', 'Amount of loans/ debts collected', 'Yearly', 'Standard KPI', '', 'obj113', 'Currency', 'Last Value', 'ind0', 'ind0', NULL, '770000000', '0', '850000000', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi60', 'Performance contracting quarterly reports', 'Yearly', 'Standard KPI', '', 'obj143', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '4', '0', '4', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi61', 'strategic plan quarterly reports', 'Yearly', 'Standard KPI', '', 'obj144', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '4', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi62', 'Percentage level of automated processes', 'Yearly', 'Standard KPI', '', 'obj145', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '1', '0', '9', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi63', 'Percentage completion rate for ISMS', 'Yearly', 'Standard KPI', '', 'obj146', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '99', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi64', 'No of internal audits', 'Yearly', 'Standard KPI', '', 'obj147', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '2', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi65', 'ERM Quarterly Reports', 'Yearly', 'Standard KPI', '', 'obj148', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '4', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi66', 'Annual staff education training  and sensitisation on ERM and BCP table-top testing', 'Yearly', 'Standard KPI', '', 'obj148', '', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi67', 'No of MoUs signed', 'Yearly', 'Standard KPI', '', 'obj149', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi68', 'Compliance level', 'Monthly', 'Standard KPI', '', 'obj150', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi69', 'Implementation level of PMS for the departments staff', 'Monthly', 'Standard KPI', '', 'obj151', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi7', 'Public Awareness Index', 'Yearly', 'Standard KPI', '', 'obj114', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '14', '0', '20', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi71', 'Trainings attended', 'Yearly', 'Standard KPI', '', 'obj153', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '2', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi72', 'Competency Level', 'Yearly', 'Standard KPI', '', 'obj154', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi73', '% of workplan implementation', 'Yearly', 'Standard KPI', '', 'obj155', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi74', 'Budget Absorbtion rate', 'Monthly', 'Standard KPI', '', 'obj156', 'Percentage(%)', 'Last Value', 'ind6', 'ind6', NULL, '0', '0', '100', '', NULL, 'goalOnly', '', 'No', 3000, '[{\"value\":\"ind6\",\"label\":\"Mary Kiragu\"}]'),
('kpi75', 'Implementation of board resolutions', 'Monthly', 'Standard KPI', '', 'obj156', 'Percentage(%)', 'Last Value', 'ind6', 'ind6', NULL, '0', '0', '100', '', NULL, 'goalOnly', '', 'No', 3000, '[{\"value\":\"ind6\",\"label\":\"Mary Kiragu\"}]'),
('kpi76', 'Service charter compliance', 'Monthly', 'Standard KPI', '', 'obj157', 'Percentage(%)', 'Last Value', 'ind6', 'ind6', NULL, '0', '0', '100', '', NULL, 'goalOnly', '', 'No', 3000, '[{\"value\":\"ind6\",\"label\":\"Mary Kiragu\"}]'),
('kpi77', '% level of satisfaction by audit clients', 'Monthly', 'Standard KPI', '', 'obj158', 'Percentage(%)', 'Last Value', 'ind6', 'ind6', NULL, '85', '0', '90', '', NULL, 'threeColor', '', 'No', 3000, '[{\"value\":\"ind6\",\"label\":\"Mary Kiragu\"}]'),
('kpi78', 'Preparation and uploading of BAC and board papers', 'Monthly', 'Standard KPI', '', 'obj158', 'Standard', 'Last Value', 'ind6', 'ind6', NULL, '0', '0', '7', '', NULL, 'goalOnly', '', 'No', 3000, '[{\"value\":\"ind6\",\"label\":\"Mary Kiragu\"}]'),
('kpi79', '% annual audit work plan implemented ', 'Yearly', 'Standard KPI', '', 'obj160', 'Percentage(%)', 'Last Value', 'ind6', 'ind6', NULL, '0', '0', '100', '', NULL, 'goalOnly', '', 'No', 3000, '[{\"value\":\"ind6\",\"label\":\"Mary Kiragu\"}]'),
('kpi8', 'Number of Engagements', 'Yearly', 'Standard KPI', '', 'obj115', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '5', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi80', 'Quarterly board audit committee', 'Yearly', 'Standard KPI', '', 'obj183', 'Standard', 'Last Value', 'ind6', 'ind6', NULL, '0', '0', '4', '', NULL, 'goalOnly', '', 'No', 3000, '[{\"value\":\"ind6\",\"label\":\"Mary Kiragu\"}]'),
('kpi81', '% of staff retained', 'Yearly', 'Standard KPI', '', 'obj161', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi82', 'Performance appraisals - Quarterly', 'Yearly', 'Standard KPI', '', 'obj162', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '4', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi83', 'Performance appraisals - Annual', 'Yearly', 'Standard KPI', '', 'obj162', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi84', 'Productivity Index - Improved', 'Yearly', 'Standard KPI', '', 'obj162', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '0', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi85', '% implementation of the approved departmental training and performance gaps ', 'Yearly', 'Standard KPI', '', 'obj163', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi86', 'Budegt absorbtion rate', 'Monthly', 'Standard KPI', '', 'obj171', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi87', 'Employee satisfaction rate', 'Yearly', 'Standard KPI', '', 'obj165', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '72', '0', '76.50', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi88', 'Level of adherence to procurement plans', 'Yearly', 'Standard KPI', '', 'obj164', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '80', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi89', 'Implementation of board resolutions', 'Monthly', 'Standard KPI', '', 'obj164', 'Percentage(%)', 'Last Value', 'ind6', 'ind6', NULL, '0', '0', '100', '', NULL, 'goalOnly', '', 'No', 3000, '[{\"value\":\"ind6\",\"label\":\"Mary Kiragu\"}]'),
('kpi9', 'Approval of the Deposit Insurance Academy', 'Yearly', 'Standard KPI', '', 'obj115', 'Pe', 'Last Value', 'ind0', 'ind0', NULL, '70', '0', '90', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi90', 'No. of staff recruited', 'Monthly', 'Standard KPI', '', 'obj166', 'Standard', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '20', '0', NULL, 'goalOnly', '', 'No', 3000, '[]'),
('kpi91', 'Culture entropy', 'Monthly', 'Standard KPI', '', 'obj167', 'Percentage(%)', 'Last Value', 'ind0', 'ind0', NULL, '23', '0', '22', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi92', 'Corporate culture exir survey report\\\\', 'Monthly', 'Standard KPI', '', 'obj167', '', 'Last Value', 'ind0', 'ind0', NULL, '0', '0', '0', '0', NULL, 'threeColor', '', 'No', 3000, '[]'),
('kpi93', 'Staff Productivity Index', 'Yearly', 'Standard KPI', '', 'obj168', 'Percentage(%)', 'Last Value', 'ind4', 'ind4', NULL, '90', '0', '100', '0', NULL, 'threeColor', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]'),
('kpi94', 'Corporate Performance Index', 'Yearly', 'Standard KPI', '', 'obj168', 'Percentage(%)', 'Last Value', 'ind4', 'ind4', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]'),
('kpi95', 'Staff Engagement Report and Index', 'Yearly', 'Standard KPI', '', 'obj168', 'Percentage(%)', 'Last Value', 'ind4', 'ind4', NULL, '0', '0', '57', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]'),
('kpi96', 'Level of implementation of the training plan', 'Yearly', 'Standard KPI', '', 'obj169', 'Percentage(%)', 'Last Value', 'ind4', 'ind4', NULL, '0', '0', '100', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]'),
('kpi97', 'Skills Mapping Report', 'Yearly', 'Standard KPI', '', 'obj169', 'Standard', 'Last Value', 'ind4', 'ind4', NULL, '0', '0', '2', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]'),
('kpi98', 'Skill Gap Analysis Report', 'Yearly', 'Standard KPI', '', 'obj169', 'Standard', 'Last Value', 'ind4', 'ind4', NULL, '0', '0', '1', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]'),
('kpi99', 'Approved Succession Planning and Talent Management Framework', 'Yearly', 'Standard KPI', '', 'obj169', 'Standard', 'Last Value', 'ind4', 'ind4', NULL, '0', '0', '2', '0', NULL, 'goalOnly', '', 'No', 3000, '[{\\\"value\\\":\\\"ind4\\\",\\\"label\\\":\\\"Peter Ibrae\\\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `measuredays`
--

CREATE TABLE `measuredays` (
  `id` int NOT NULL,
  `measureId` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `date` date NOT NULL,
  `actual` int DEFAULT NULL,
  `red` int DEFAULT NULL,
  `blue` int DEFAULT NULL,
  `green` int DEFAULT NULL,
  `darkgreen` int DEFAULT NULL,
  `2score` int DEFAULT NULL,
  `3score` float DEFAULT NULL,
  `4score` int DEFAULT NULL,
  `5score` int DEFAULT NULL,
  `UNPL` int DEFAULT NULL,
  `LNPL` int DEFAULT NULL,
  `centralLine` int DEFAULT NULL,
  `mR` int DEFAULT NULL,
  `signalPointer` int DEFAULT NULL,
  `updater` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `measurehalfyear`
--

CREATE TABLE `measurehalfyear` (
  `id` int NOT NULL,
  `measureId` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `date` date NOT NULL,
  `actual` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `red` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `blue` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `green` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `darkgreen` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `2score` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `3score` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `4score` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `5score` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `UNPL` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `LNPL` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `centralLine` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `mR` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `signalPointer` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `updater` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `measurelinks`
--

CREATE TABLE `measurelinks` (
  `id` int NOT NULL,
  `measure_id` varchar(9) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `linked_id` varchar(9) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `link_type` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `measurelinks`
--

INSERT INTO `measurelinks` (`id`, `measure_id`, `linked_id`, `link_type`) VALUES
(20, 'kpi85', 'obj42', 'Measure'),
(21, 'kpi83', 'obj40', 'Measure'),
(22, 'kpi58', 'obj30', 'Measure'),
(23, 'kpi79', 'obj29', 'Measure'),
(24, 'kpi80', 'obj31', 'Measure'),
(27, 'kpi109', 'obj32', 'Measure'),
(28, 'kpi157', 'ind19', 'Measure'),
(30, 'kpi146', 'ind33', 'Measure'),
(31, 'kpi147', 'ind33', 'Measure');

-- --------------------------------------------------------

--
-- Table structure for table `measuremonths`
--

CREATE TABLE `measuremonths` (
  `id` int NOT NULL,
  `measureId` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `date` date NOT NULL,
  `actual` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `red` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `blue` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `green` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `darkgreen` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `2score` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `3score` float DEFAULT NULL,
  `4score` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `5score` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `UNPL` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `LNPL` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `centralLine` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `mR` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `signalPointer` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `updater` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `measuremonths`
--

INSERT INTO `measuremonths` (`id`, `measureId`, `date`, `actual`, `red`, `blue`, `green`, `darkgreen`, `2score`, `3score`, `4score`, `5score`, `UNPL`, `LNPL`, `centralLine`, `mR`, `signalPointer`, `updater`) VALUES
(2, 'kpi29', '2024-08-01', NULL, '78', '0', '100', NULL, '0', 0, '0', '0', '0', '0', '0', NULL, NULL, 'Accent Import'),
(3, 'kpi29', '2024-09-01', NULL, '78', '0', '100', NULL, '0', 0, '0', '0', '0', '0', '0', NULL, NULL, 'Accent Import'),
(4, 'kpi29', '2024-10-01', NULL, '78', '0', '100', NULL, '0', 0, '0', '0', '0', '0', '0', NULL, NULL, 'Accent Import'),
(5, 'kpi29', '2024-11-01', NULL, '78', '0', '100', NULL, '0', 0, '0', '0', '0', '0', '0', NULL, NULL, 'Accent Import'),
(6, 'kpi29', '2024-12-01', NULL, '78', '0', '100', NULL, '0', 0, '0', '0', '0', '0', '0', NULL, NULL, 'Accent Import'),
(7, 'kpi29', '2025-01-01', NULL, '78', '0', '100', NULL, '0', 0, '0', '0', '0', '0', '0', NULL, NULL, 'Accent Import'),
(8, 'kpi29', '2025-02-01', NULL, '78', '0', '100', NULL, '0', 0, '0', '0', '0', '0', '0', NULL, NULL, 'Accent Import'),
(9, 'kpi29', '2025-03-01', NULL, '78', '0', '100', NULL, '0', 0, '0', '0', '0', '0', '0', NULL, NULL, 'Accent Import'),
(10, 'kpi29', '2025-04-01', NULL, '78', '0', '100', NULL, '0', 0, '0', '0', '0', '0', '0', NULL, NULL, 'Accent Import'),
(11, 'kpi29', '2025-05-01', '75', '78', '0', '100', NULL, '0', 2.42424, '0', '0', '0', '0', '0', NULL, NULL, 'Accent Import'),
(12, 'kpi29', '2025-06-01', '90', '78', '0', '100', NULL, '0', 6.9697, '0', '0', '0', '0', '0', NULL, NULL, 'Accent Import'),
(13, 'kpi29', '2025-07-01', '100', '78', '0', '100', NULL, '0', 10, '0', '0', '0', '0', '0', NULL, NULL, 'Accent Import');

-- --------------------------------------------------------

--
-- Table structure for table `measurequarters`
--

CREATE TABLE `measurequarters` (
  `id` int NOT NULL,
  `measureId` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `date` date NOT NULL,
  `actual` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `red` int DEFAULT NULL,
  `blue` int DEFAULT NULL,
  `green` int DEFAULT NULL,
  `darkgreen` int DEFAULT NULL,
  `2score` int DEFAULT NULL,
  `3score` float DEFAULT NULL,
  `4score` int DEFAULT NULL,
  `5score` int DEFAULT NULL,
  `UNPL` int DEFAULT NULL,
  `LNPL` int DEFAULT NULL,
  `centralLine` int DEFAULT NULL,
  `mR` int DEFAULT NULL,
  `signalPointer` int DEFAULT NULL,
  `updater` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `measureweeks`
--

CREATE TABLE `measureweeks` (
  `id` int NOT NULL,
  `measureId` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `date` date NOT NULL,
  `actual` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `red` int DEFAULT NULL,
  `blue` int DEFAULT NULL,
  `green` int DEFAULT NULL,
  `darkgreen` int DEFAULT NULL,
  `2score` int DEFAULT NULL,
  `3score` float DEFAULT NULL,
  `4score` int DEFAULT NULL,
  `5score` int DEFAULT NULL,
  `UNPL` int DEFAULT NULL,
  `LNPL` int DEFAULT NULL,
  `centralLine` int DEFAULT NULL,
  `mR` int DEFAULT NULL,
  `signalPointer` int DEFAULT NULL,
  `updater` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `measureyears`
--

CREATE TABLE `measureyears` (
  `id` int NOT NULL,
  `measureId` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `date` date NOT NULL,
  `actual` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `red` float DEFAULT NULL,
  `blue` float DEFAULT NULL,
  `green` float DEFAULT NULL,
  `darkgreen` float DEFAULT NULL,
  `2score` float DEFAULT NULL,
  `3score` float DEFAULT NULL,
  `4score` float DEFAULT NULL,
  `5score` float DEFAULT NULL,
  `UNPL` float DEFAULT NULL,
  `LNPL` float DEFAULT NULL,
  `centralLine` float DEFAULT NULL,
  `mR` float DEFAULT NULL,
  `signalPointer` float DEFAULT NULL,
  `updater` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `measureyears`
--

INSERT INTO `measureyears` (`id`, `measureId`, `date`, `actual`, `red`, `blue`, `green`, `darkgreen`, `2score`, `3score`, `4score`, `5score`, `UNPL`, `LNPL`, `centralLine`, `mR`, `signalPointer`, `updater`) VALUES
(999, 'kpi2861', '2024-01-01', '100', 50, 95, 80, 90, 0, 8.10526, 0, 8.10526, 73.2, -33.2, 20, NULL, NULL, 'test');

-- --------------------------------------------------------

--
-- Table structure for table `note`
--

CREATE TABLE `note` (
  `id` int NOT NULL,
  `objectId` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `interpretation` varchar(3000) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `wayForward` varchar(3000) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `measureNote` longblob,
  `period` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `date` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `creator` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `replyId` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `notification_execution_log`
--

CREATE TABLE `notification_execution_log` (
  `id` int NOT NULL,
  `schedule_id` int NOT NULL COMMENT 'Reference to notification schedule',
  `executed_at` datetime NOT NULL COMMENT 'When schedule was executed',
  `sent_count` int NOT NULL DEFAULT '0' COMMENT 'Number of notifications sent',
  `failed_count` int NOT NULL DEFAULT '0' COMMENT 'Number of notifications that failed',
  `execution_time` decimal(10,3) DEFAULT NULL COMMENT 'Execution time in seconds',
  `notes` text COMMENT 'Additional execution notes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Schedule execution summary logs';

-- --------------------------------------------------------

--
-- Table structure for table `notification_logs`
--

CREATE TABLE `notification_logs` (
  `id` int NOT NULL,
  `schedule_id` int NOT NULL COMMENT 'Reference to notification schedule',
  `user_id` int NOT NULL COMMENT 'User who received the notification',
  `subject` varchar(500) NOT NULL COMMENT 'Email subject that was sent',
  `sent_date` datetime NOT NULL COMMENT 'When notification was sent',
  `status` enum('sent','failed') NOT NULL DEFAULT 'sent' COMMENT 'Send status',
  `error_message` text COMMENT 'Error message if failed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Individual notification send logs';

-- --------------------------------------------------------

--
-- Table structure for table `notification_queue`
--

CREATE TABLE `notification_queue` (
  `id` int NOT NULL,
  `schedule_id` int NOT NULL COMMENT 'Reference to notification schedule',
  `user_id` int NOT NULL COMMENT 'Target user',
  `template_data` json NOT NULL COMMENT 'Processed template data',
  `priority` tinyint NOT NULL DEFAULT '5' COMMENT 'Priority (1=highest, 10=lowest)',
  `status` enum('pending','processing','sent','failed') NOT NULL DEFAULT 'pending',
  `attempts` tinyint NOT NULL DEFAULT '0' COMMENT 'Number of send attempts',
  `scheduled_for` datetime NOT NULL COMMENT 'When to send this notification',
  `created_date` datetime NOT NULL COMMENT 'When queued',
  `processed_date` datetime DEFAULT NULL COMMENT 'When processed',
  `error_message` text COMMENT 'Error if failed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Notification queue for batch processing';

-- --------------------------------------------------------

--
-- Table structure for table `notification_schedules`
--

CREATE TABLE `notification_schedules` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Schedule name for identification',
  `description` text COMMENT 'Schedule description',
  `template_id` int NOT NULL COMMENT 'Reference to notification template',
  `frequency` enum('hourly','daily','weekly','monthly') NOT NULL COMMENT 'How often to send',
  `frequency_value` int NOT NULL DEFAULT '1' COMMENT 'Frequency multiplier (e.g., every 2 weeks)',
  `start_date` datetime NOT NULL COMMENT 'When schedule becomes active',
  `end_date` datetime DEFAULT NULL COMMENT 'When schedule expires (NULL = never)',
  `last_executed` datetime DEFAULT NULL COMMENT 'When schedule was last executed',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Whether schedule is active',
  `target_users` json NOT NULL COMMENT 'JSON defining target users (all_users, specific_users, department, role)',
  `created_by` int NOT NULL COMMENT 'User ID who created the schedule',
  `created_date` datetime NOT NULL COMMENT 'When schedule was created',
  `modified_date` datetime NOT NULL COMMENT 'When schedule was last modified'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Scheduled notification configurations';

--
-- Dumping data for table `notification_schedules`
--

INSERT INTO `notification_schedules` (`id`, `name`, `description`, `template_id`, `frequency`, `frequency_value`, `start_date`, `end_date`, `last_executed`, `is_active`, `target_users`, `created_by`, `created_date`, `modified_date`) VALUES
(1, 'Weekly Measure Reminder', 'Weekly reminder for users to update their measures', 1, 'weekly', 1, '2025-06-17 16:44:06', NULL, NULL, 1, '{\"type\": \"all_users\"}', 1, '2025-06-17 16:44:06', '2025-06-17 16:44:06'),
(2, 'Monthly Initiative Update', 'Monthly reminder for initiative status updates', 2, 'monthly', 1, '2025-06-17 16:44:06', NULL, NULL, 1, '{\"type\": \"all_users\"}', 1, '2025-06-17 16:44:06', '2025-06-17 16:44:06'),
(3, 'Weekly Performance Summary', 'Weekly performance summary for all users', 3, 'weekly', 1, '2025-06-17 16:44:06', NULL, NULL, 0, '{\"type\": \"all_users\"}', 1, '2025-06-17 16:44:06', '2025-06-17 16:44:06'),
(4, 'sample schedule', 'deadline alert sample', 7, 'monthly', 1, '2025-06-18 16:50:00', '2025-06-24 16:50:00', NULL, 1, '{\"type\": \"specific_users\", \"user_ids\": [\"1\"]}', 1, '2025-06-17 16:50:44', '2025-06-17 16:50:44');

--
-- Triggers `notification_schedules`
--
DELIMITER $$
CREATE TRIGGER `tr_notification_schedules_update` BEFORE UPDATE ON `notification_schedules` FOR EACH ROW BEGIN
    SET NEW.modified_date = NOW();
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Template name for identification',
  `description` text COMMENT 'Template description',
  `email_type` varchar(50) NOT NULL COMMENT 'Type of notification (measure_reminder, initiative_update, etc.)',
  `subject` varchar(500) NOT NULL COMMENT 'Email subject line with placeholders',
  `body_template` text NOT NULL COMMENT 'Email body template with placeholders',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Whether template is active',
  `created_by` int NOT NULL COMMENT 'User ID who created the template',
  `created_date` datetime NOT NULL COMMENT 'When template was created',
  `modified_date` datetime NOT NULL COMMENT 'When template was last modified'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Email templates for notifications';

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `name`, `description`, `email_type`, `subject`, `body_template`, `is_active`, `created_by`, `created_date`, `modified_date`) VALUES
(1, 'Measure Reminder', 'Reminds users to update overdue measures', 'measure_reminder', 'Reminder: Update Your Measures - {{current_date}}', 'Dear {{user_name}},\n\nThis is a friendly reminder that you have {{pending_measures_count}} measure(s) that need updating:\n\n{{pending_measures_list}}\n\nPlease log into the system to update your measures.\n\nBest regards,\nKDIC Analytics Platform', 1, 1, '2025-06-17 16:44:06', '2025-06-17 16:44:06'),
(2, 'Initiative Update Reminder', 'Reminds users to update initiative status', 'initiative_update', 'Initiative Status Update Required - {{current_date}}', 'Dear {{user_name}},\n\nYou have {{pending_initiatives_count}} initiative(s) that need status updates:\n\n{{pending_initiatives_list}}\n\nPlease update the status of your initiatives in the system.\n\nBest regards,\nKDIC Analytics Platform', 1, 1, '2025-06-17 16:44:06', '2025-06-17 16:44:06'),
(3, 'Performance Summary', 'Weekly/Monthly performance summary', 'performance_summary', 'Your Performance Summary - {{current_date}}', 'Dear {{user_name}},\n\nHere is your performance summary:\n\nPerformance Score: {{performance_score}}\nTrend: {{performance_trend}}\n\nKeep up the great work!\n\nBest regards,\nKDIC Analytics Platform', 1, 1, '2025-06-17 16:44:06', '2025-06-17 16:44:06'),
(4, 'System Announcement', 'General system announcements', 'system_announcement', '{{announcement_title}} - {{current_date}}', 'Dear {{user_name}},\n\n{{announcement_details}}\n\nFor more information, please contact the system administrator.\n\nBest regards,\nKDIC Analytics Platform', 1, 1, '2025-06-17 16:44:06', '2025-06-17 16:44:06'),
(5, 'Weekly Digest', 'Weekly activity summary', 'weekly_digest', 'Weekly Activity Digest - Week of {{current_date}}', 'Dear {{user_name}},\n\nHere is your weekly activity summary:\n\n- Measures updated: {{measures_updated_count}}\n- Initiatives progressed: {{initiatives_updated_count}}\n- Performance score: {{performance_score}}\n\nHave a great week ahead!\n\nBest regards,\nKDIC Analytics Platform', 1, 1, '2025-06-17 16:44:06', '2025-06-17 16:44:06'),
(6, 'Monthly Report', 'Monthly performance report', 'monthly_report', 'Monthly Performance Report - {{current_date}}', 'Dear {{user_name}},\n\nYour monthly performance report is ready:\n\n- Overall Score: {{performance_score}}\n- Measures Completed: {{measures_completed}}\n- Initiatives Delivered: {{initiatives_completed}}\n- Department Ranking: {{department_ranking}}\n\nDetailed report is available in the system.\n\nBest regards,\nKDIC Analytics Platform', 1, 1, '2025-06-17 16:44:06', '2025-06-17 16:44:06'),
(7, 'Deadline Alert', 'Upcoming deadline notifications', 'deadline_alert', 'Upcoming Deadlines - Action Required', 'Dear {{user_name}},\n\nYou have upcoming deadlines that require attention:\n\n{{upcoming_deadlines_list}}\n\nPlease ensure timely completion of these items.\n\nBest regards,\nKDIC Analytics Platform', 1, 1, '2025-06-17 16:44:06', '2025-06-17 16:44:06');

--
-- Triggers `notification_templates`
--
DELIMITER $$
CREATE TRIGGER `tr_notification_templates_update` BEFORE UPDATE ON `notification_templates` FOR EACH ROW BEGIN
    SET NEW.modified_date = NOW();
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `objcommentary`
--

CREATE TABLE `objcommentary` (
  `id` int NOT NULL,
  `name` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `perspective` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `owner` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `team` varchar(300) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `description` varchar(600) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `outcome` varchar(600) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `from` varchar(600) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `to` varchar(600) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `measure` varchar(600) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `target` varchar(300) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `initiative` varchar(600) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `linkedTo` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `treeId` varchar(9) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `objective`
--

CREATE TABLE `objective` (
  `id` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0',
  `name` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `description` varchar(900) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `outcome` varchar(900) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `linkedObject` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `linkedObjectType` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `owner` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `notes` varchar(900) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `chartType` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `organization` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `cascadedfrom` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `weight` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `sortColumn` int NOT NULL,
  `tags` varchar(9000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `objective`
--

INSERT INTO `objective` (`id`, `name`, `description`, `outcome`, `linkedObject`, `linkedObjectType`, `owner`, `notes`, `chartType`, `organization`, `cascadedfrom`, `weight`, `sortColumn`, `tags`) VALUES
('obj111', 'Deposit Insurance', 'Prudent Management of the Deposit Insurance Fund', '', 'persp8', NULL, 'ind7', NULL, NULL, 'pc', '', '3300', 3000, '[{\"value\":\"ind7\",\"label\":\"Lawrence Shoona\"}]'),
('obj112', 'Institutional Capacity Development', 'Enhance prudence in the utilisation of resources', '', 'persp8', NULL, '[]', NULL, NULL, NULL, '', '0.33', 3000, NULL),
('obj113', 'Resolution of Problem Banks', 'Reduce the amount owed by debtors and wind up financial institutions in liquidation', '', 'persp8', NULL, '[]', NULL, NULL, NULL, '', '0.33', 3000, NULL),
('obj114', 'Institutional Capacity Development', 'Improve Public Awareness Index from 14% in 2023 to 28% in 2028 ', '', 'persp9', NULL, '[]', NULL, NULL, NULL, '', '0.50', 3000, NULL),
('obj115', 'Risk Minimization', 'Establish and enhance strategic collaborations and partnerships with stakeholders', '', 'persp9', NULL, '[]', NULL, NULL, NULL, '', '0.50', 3000, NULL),
('obj116', 'Deposit Insurance', 'Enhance depositor protection and compensation', '', 'persp10', NULL, '[]', NULL, NULL, NULL, '', '0.25', 3000, NULL),
('obj117', 'Resolution of Problem Banks', 'Wind-up financial institutions in liquidation', '', 'persp10', NULL, '[]', NULL, NULL, NULL, '', '0.25', 3000, NULL),
('obj118', 'Institutional Capacity Development', 'To automate processes and digitize records', '', 'persp10', NULL, '[]', NULL, NULL, NULL, '', '0.25', 3000, NULL),
('obj119', 'Risk Minimization', 'Strengthen Early Intervention Framework', '', 'persp10', NULL, '[]', NULL, NULL, NULL, '', '0.25', 3000, NULL),
('obj120', 'Institutional Capacity Development', '', '', 'persp11', NULL, '[]', NULL, NULL, NULL, '', '1.00', 3000, NULL),
('obj121', 'Enhance prudence in the utilisation of resources', '', 'Institutional Capacity Development', 'persp12', NULL, 'ind6', NULL, NULL, NULL, '', '100', 3000, '[{\"value\":\"ind6\",\"label\":\"Mary Kiragu\"},{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj122', 'Establish & enhance strategic collaborations & partnerships with stakeholders', '', 'Risk minimization', 'persp13', NULL, 'ind8', NULL, NULL, NULL, '', '0.33', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj123', 'Promote confidence & trust among stakeholders', '', 'Institutional Capacity Development', 'persp13', NULL, 'ind8', NULL, NULL, NULL, '', '0.33', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj124', 'Establish & enhance strategic collaborations & partnerships with stakeholders', '', 'Risk minimization', 'persp13', NULL, 'ind8', NULL, NULL, NULL, '', '0.33', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj125', 'Enhance depositor protection & compensation', '', 'Deposit insurance', 'persp14', NULL, 'ind8', NULL, NULL, NULL, '', '0.11', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj126', 'Provision of legal advisory services in the Discharge of Securities', '', 'Resolution of Problem Banks', 'persp14', NULL, 'ind8', NULL, NULL, NULL, '', '0.11', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj127', 'Provision of legal advisory in Contract Management', '', 'Resolution of Problem Banks', 'persp14', NULL, 'ind8', NULL, NULL, NULL, '', '0.11', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj128', 'Automate process & digitize records', '', 'To automate process & digitize records', 'persp14', NULL, 'ind8', NULL, NULL, NULL, '', '0.11', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj129', 'Enhance risk minimisation', '', 'Institutional capacity development', 'persp14', NULL, 'ind8', NULL, NULL, NULL, '', '0.11', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj130', 'Provision of legal advisory services in assessment of fees', '', 'Resolution of Problem Banks', 'persp14', NULL, 'ind8', NULL, NULL, NULL, '', '0.11', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj131', 'Strengthen litigation', '', 'Resolution of Problem Banks', 'persp14', NULL, 'ind8', NULL, NULL, NULL, '', '0.11', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj132', 'Compliance with existing regulatory framework', '', 'Institutional Capacity Development', 'persp14', NULL, 'ind8', NULL, NULL, NULL, '', '0.11', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj133', 'Improve the regulatory framework', '', 'Risk minimization', 'persp14', NULL, 'ind8', NULL, NULL, NULL, '', '0.11', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj134', 'Staff retention', '', 'Build high performance culture', 'persp15', NULL, 'ind8', NULL, NULL, NULL, '', '0.25', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj135', 'Institutionalize performance management & staff productivity', '', 'Build High Performance Culture', 'persp15', NULL, 'ind8', NULL, NULL, NULL, '', '0.25', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj136', 'Build a vibrant & cohesive organizational culture', '', 'Institutional Capacity Development', 'persp15', NULL, 'ind8', NULL, NULL, NULL, '', '0.25', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj137', 'Enhance staff productivity', '', 'Build High Performance Culture', 'persp15', NULL, 'ind8', NULL, NULL, NULL, '', '0.25', 3000, '[{\"value\":\"ind8\",\"label\":\"Eunice Kitche\"}]'),
('obj138', 'Enhance prudence in the utilisation of the allocated resources', '', '', 'persp16', NULL, '', NULL, NULL, NULL, '', '1.00', 3000, '[]'),
('obj139', 'Institutional Capacity Development', '', '', 'persp17', NULL, '', NULL, NULL, NULL, '', '1.00', 3000, '[]'),
('obj142', 'Enhance Corporate Performance', '', 'Institutional Capacity Development', 'persp18', NULL, '', NULL, NULL, NULL, '', '0.10', 3000, '[]'),
('obj143', 'Corporate governance practices', '', 'Institutional Capacity Development', 'persp18', NULL, '', NULL, NULL, NULL, '', '0.10', 3000, '[]'),
('obj144', 'Improve corporate performance', '', 'Institutional Capacity Development', 'persp18', NULL, '', NULL, NULL, NULL, '', '0.10', 3000, '[]'),
('obj145', 'Implement depertmental identified processes for automstion', '', 'Institutional Capacity Development', 'persp18', NULL, '', NULL, NULL, NULL, '', '0.10', 3000, '[]'),
('obj146', 'Percentage of processes standardized', '', 'Institutional Capacity Development', 'persp18', NULL, '', NULL, NULL, NULL, '', '0.10', 3000, '[]'),
('obj147', 'Maintenance of QMS', '', 'Institutional Capacity Development', 'persp18', NULL, '', NULL, NULL, NULL, '', '0.10', 3000, '[]'),
('obj148', 'Enhance implementation and maintenance of the ERM', '', 'Risk Minimization', 'persp18', NULL, '', NULL, NULL, NULL, '', '0.10', 3000, '[]'),
('obj149', 'Establish and enhance collaborations', '', 'Institutional Capacity Development', 'persp18', NULL, '', NULL, NULL, NULL, '', '0.10', 3000, '[]'),
('obj150', 'Strengthen corporate compliance ', '', 'Institutional Capacity Development', 'persp18', NULL, '', NULL, NULL, NULL, '', '0.10', 3000, '[]'),
('obj151', 'Enhance departmental performance', '', 'Institutional Capacity Development', 'persp19', NULL, '', NULL, NULL, NULL, '', '0.25', 3000, '[]'),
('obj152', 'Attend trainings for capacity', '', 'Institutional Capacity Development', 'persp18', NULL, '', NULL, NULL, NULL, '', '0.10', 3000, '[]'),
('obj153', 'Trainings for capacity building', '', 'Institutional Capacity Development', 'persp19', NULL, '', NULL, NULL, NULL, '', '0.25', 3000, '[]'),
('obj154', 'Development and implementation of PMS', '', 'Institutional Capacity Development', 'persp19', NULL, '', NULL, NULL, NULL, '', '0.25', 3000, '[]'),
('obj155', 'Enhance staff productivity', '', 'Institutional Capacity Development', 'persp19', NULL, '', NULL, NULL, NULL, '', '0.25', 3000, '[]'),
('obj156', 'Utilization of resources', '', 'Institutional Capacity Development', 'persp20', NULL, '', NULL, NULL, NULL, '', '1.00', 3000, '[]'),
('obj157', 'Compliance with service delivery', '', 'Stakeholder Management/PC', 'persp21', NULL, '', NULL, NULL, NULL, '', '0.25', 3000, '[]'),
('obj158', 'Customer Satisfaction', '', 'Stakeholder Management/PC', 'persp21', NULL, '', NULL, NULL, NULL, '', '0.25', 3000, '[]'),
('obj160', 'Enhance operational capacity', '', 'Organizational Capacity', 'persp30', NULL, '', NULL, NULL, NULL, '', '0.20', 3000, '[]'),
('obj161', 'Attract acquire& retain internal audit staff', '', 'Institutional capacity develeopment', 'persp22', NULL, '', NULL, NULL, NULL, '', '0.33', 3000, '[]'),
('obj162', 'Strengthen performance management', '', 'Institutional Capacity', 'persp22', NULL, '', NULL, NULL, NULL, '', '0.33', 3000, '[]'),
('obj163', 'Enhance staff capacity', '', 'Institutional Capacity', 'persp22', NULL, '', NULL, NULL, NULL, '', '0.33', 3000, '[]'),
('obj164', 'Enhance prudence in utilisational resources', '', 'Institutional Capacity Development', 'persp23', NULL, '', NULL, NULL, NULL, '', '0.17', 3000, '[]'),
('obj165', 'Strengthen Employee Morale', '', 'Institutional Capacity Development', 'persp26', NULL, '', NULL, NULL, NULL, '', '17', 3000, '[]'),
('obj166', 'Populate the staff establishment', '', 'Institutional Capacity Development', 'persp23', NULL, '', NULL, NULL, NULL, '', '0.17', 3000, '[]'),
('obj167', 'Build a vibrant and cohesive organizational culture', '', 'Institutional Capacity Development', 'persp23', NULL, '', NULL, NULL, NULL, '', '0.17', 3000, '[]'),
('obj168', 'Institutionalise Performance Management and Staff Productivity', '', 'Institutional Capacity Development', 'persp23', NULL, 'ind4', NULL, NULL, NULL, '', '17', 3000, '[{\"value\":\"ind4\",\"label\":\"Peter Ibrae\"}]'),
('obj169', 'Undertake Capacity Building Program', 'Undertake capacity building program for all staff in their core areas to address performance gaps', 'Institutional Capacity Development', 'persp23', NULL, 'ind4', NULL, NULL, NULL, '', '0.17', 3000, '[{\"value\":\"ind4\",\"label\":\"Peter Ibrae\"}]'),
('obj170', 'Staff Performance and Management', '', 'Institutional Capacity Development', 'persp24', NULL, 'ind4', NULL, NULL, NULL, '', '1.00', 3000, '[{\"value\":\"ind4\",\"label\":\"Peter Ibrae\"}]'),
('obj171', 'Enhance Prudence in the utilisation of resources', '', 'Institutional Capacity Development', 'persp25', NULL, 'ind4', NULL, NULL, NULL, '', '1.00', 3000, '[{\"value\":\"ind4\",\"label\":\"Peter Ibrae\"}]'),
('obj172', 'Prudent Management of Deposit Insurance Fund', '', 'Deposit Insurance', 'persp27', NULL, 'ind5', NULL, NULL, NULL, '', '0.50', 3000, '[{\"value\":\"ind5\",\"label\":\"Paul Manga\"}]'),
('obj173', 'Enhance Prudence in the utilisation of resources', '', 'Institutional Capacity Development', 'persp27', NULL, 'ind5', NULL, NULL, NULL, '', '0.50', 3000, '[{\"value\":\"ind5\",\"label\":\"Paul Manga\"}]'),
('obj174', 'Public Awareness Index', '', 'Institutional Capacity Development', 'persp28', NULL, 'ind5', NULL, NULL, NULL, '', '0.50', 3000, '[{\"value\":\"ind5\",\"label\":\"Paul Manga\"}]'),
('obj175', 'Establish and enhance strategic collaborations and partnerships', '', 'Risk Minimization', 'persp28', NULL, 'ind5', NULL, NULL, NULL, '', '0.50', 3000, '[{\"value\":\"ind5\",\"label\":\"Paul Manga\"}]'),
('obj176', 'Enhance Depositor Protections', '', 'Deposit Insurance', 'persp29', NULL, 'ind5', NULL, NULL, NULL, '', '0.14', 3000, '[{\"value\":\"ind5\",\"label\":\"Paul Manga\"}]'),
('obj177', 'Standardize Processes', '', 'Institutional Capacity Development', 'persp29', NULL, 'ind5', NULL, NULL, NULL, '', '0.14', 3000, '[{\"value\":\"ind5\",\"label\":\"Paul Manga\"}]'),
('obj178', 'Strengthen Early Intervention Framework', '', 'Risk Minimization', 'persp29', NULL, 'ind5', NULL, NULL, NULL, '', '0.14', 3000, '[{\"value\":\"ind5\",\"label\":\"Paul Manga\"}]'),
('obj179', 'Enhance Risk Minimization', '', 'Risk Minimization', 'persp29', NULL, 'ind5', NULL, NULL, NULL, '', '0.14', 3000, '[{\"value\":\"ind5\",\"label\":\"Paul Manga\"}]'),
('obj180', 'Improve Crisis Management Framwework', '', 'Risk Minimization', 'persp29', NULL, 'ind5', NULL, NULL, NULL, '', '0.14', 3000, '[{\"value\":\"ind5\",\"label\":\"Paul Manga\"}]'),
('obj181', 'Strengthen Regulatory Framework', '', 'Risk Minimization', 'persp29', NULL, 'ind5', NULL, NULL, NULL, '', '0.14', 3000, '[{\"value\":\"ind5\",\"label\":\"Paul Manga\"}]'),
('obj182', 'Institutionalize Performance Management', '', 'Institutional Capacity Developement', 'persp29', NULL, 'ind5', NULL, NULL, NULL, '', '0.14', 3000, '[{\"value\":\"ind5\",\"label\":\"Paul Manga\"}]'),
('obj183', 'Strengthen Governance in the corporation', '', 'Institutional Capacity Development', 'persp30', NULL, '', NULL, NULL, NULL, '', '0.20', 3000, '[]'),
('obj184', 'Strengthen Enterprise Risk Management', '', 'Institutional Capacity Development', 'persp30', NULL, '', NULL, NULL, NULL, '', '0.20', 3000, '[]'),
('obj185', 'Maintenance of Quality Management System', '', 'Institutional Capacity Development', 'persp30', NULL, 'ind6', NULL, NULL, NULL, '', '0.20', 3000, '[{\"value\":\"ind6\",\"label\":\"Mary Kiragu\"}]'),
('obj186', 'Effective and Efficient Internal Audit Process', '', 'Institutional Capacity Development', 'persp30', NULL, 'ind6', NULL, NULL, NULL, '', '20', 3000, '[{\"value\":\"ind6\",\"label\":\"Mary Kiragu\"}]'),
('obj187', 'Enhance Prudence in the utilisation of resources', '', 'Institutional Capacity Development', 'persp31', NULL, '', NULL, NULL, NULL, '', '1.00', 3000, '[]'),
('obj188', 'Improve Public Awareness Index', '', 'Institutional Capacity Development', 'persp32', NULL, 'ind9', NULL, NULL, NULL, '', '1.00', 3000, '[{\"value\":\"ind9\",\"label\":\"Crispus Yankem\"}]'),
('obj189', 'strengthen Employee Moarale and Motivation', '', 'Institutional Capacity Development', 'persp33', NULL, 'ind9', NULL, NULL, NULL, '', '0.33', 3000, '[{\"value\":\"ind9\",\"label\":\"Crispus Yankem\"}]'),
('obj190', 'Institutional Performance Management', '', 'Institutional Capacity Development', 'persp33', NULL, 'ind9', NULL, NULL, NULL, '', '0.33', 3000, '[{\"value\":\"ind9\",\"label\":\"Crispus Yankem\"}]'),
('obj191', 'Build a vibrant & cohesive ', '', 'Institutional Capacity Development', 'persp33', NULL, 'ind9', NULL, NULL, NULL, '', '0.33', 3000, '[{\"value\":\"ind9\",\"label\":\"Crispus Yankem\"}]'),
('obj192', 'Prudent Management of the Deposit Insurance Fund', '', 'Deposit Insurance', 'ind3', NULL, 'ind3', NULL, NULL, NULL, '', '0.08', 3000, '[{\"value\":\"ind3\",\"label\":\"Hellen Chepkwony\"}]'),
('obj193', 'Enhance prudence in the utilisation of utilisation of resources', '', 'Institutional Capacity Development', 'ind3', NULL, 'ind3', NULL, NULL, NULL, '', '0.08', 3000, '[{\"value\":\"ind3\",\"label\":\"Hellen Chepkwony\"}]'),
('obj194', 'Reduce amount owed by debtors and wind up financial institutions in liquidation', '', 'Resolution of Problem Banks', 'ind3', NULL, 'ind3', NULL, NULL, NULL, '', '0.08', 3000, '[{\"value\":\"ind3\",\"label\":\"Hellen Chepkwony\"}]'),
('obj195', 'Improve Public Awareness Index', '', 'Institutional Capacity Development', 'ind3', NULL, 'ind3', NULL, NULL, NULL, '', '0.08', 3000, '[{\"value\":\"ind3\",\"label\":\"Hellen Chepkwony\"}]'),
('obj196', 'Establish and enhance strategic collaborations', '', 'Risk Minimizations', 'ind3', NULL, 'ind3', NULL, NULL, NULL, '', '0.08', 3000, '[{\"value\":\"ind3\",\"label\":\"Hellen Chepkwony\"}]'),
('obj197', 'Wind up Financial Instituions in Liquidations', '', 'Resolution of Problem Banks', 'ind3', NULL, 'ind3', NULL, NULL, NULL, '', '0.08', 3000, '[{\"value\":\"ind3\",\"label\":\"Hellen Chepkwony\"}]'),
('obj198', 'Automate Processes and Digitize Records', '', 'Institutional Capacity Development', 'ind3', NULL, 'ind3', NULL, NULL, NULL, '', '0.08', 3000, '[{\"value\":\"ind3\",\"label\":\"Hellen Chepkwony\"}]'),
('obj199', 'Standardize Processes', '', 'Institutional Capacity Development', 'ind3', NULL, 'ind3', NULL, NULL, NULL, '', '0.08', 3000, '[{\"value\":\"ind3\",\"label\":\"Hellen Chepkwony\"}]'),
('obj200', 'Improve Crisis Management Framework', '', 'Risk Minimization', 'ind3', NULL, 'ind3', NULL, NULL, NULL, '', '0.08', 3000, '[{\"value\":\"ind3\",\"label\":\"Hellen Chepkwony\"}]'),
('obj201', 'Strengthen Employee Morale', '', 'Institutional Capacity Development', 'ind3', NULL, 'ind3', NULL, NULL, NULL, '', '0.08', 3000, '[{\"value\":\"ind3\",\"label\":\"Hellen Chepkwony\"}]'),
('obj202', 'Institutionalize Performance Management and Staff Productivity', '', 'Institutional Capacity Development', 'ind3', NULL, 'ind3', NULL, NULL, NULL, '', '0.08', 3000, '[{\"value\":\"ind3\",\"label\":\"Hellen Chepkwony\"}]'),
('obj203', 'Build a vibrant and cohesive organizational culture', '', 'Institutional Capacity Development', 'ind3', NULL, 'ind3', NULL, NULL, NULL, '', '0.08', 3000, '[{\"value\":\"ind3\",\"label\":\"Hellen Chepkwony\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `objectiveteam`
--

CREATE TABLE `objectiveteam` (
  `id` int NOT NULL,
  `userId` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `objectiveId` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `objective_kra_map`
--

CREATE TABLE `objective_kra_map` (
  `id` int NOT NULL,
  `objectiveId` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `kraId` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `objective_kra_map`
--

INSERT INTO `objective_kra_map` (`id`, `objectiveId`, `kraId`) VALUES
(1, 'obj121', '4'),
(2, 'obj111', ''),
(3, 'obj111', '');

-- --------------------------------------------------------

--
-- Table structure for table `organization`
--

CREATE TABLE `organization` (
  `id` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `name` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `mission` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `vision` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `valuez` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `cascadedfrom` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `weight` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `showInTree` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `organization`
--

INSERT INTO `organization` (`id`, `name`, `mission`, `vision`, `valuez`, `cascadedfrom`, `weight`, `showInTree`) VALUES
('org0', 'Accent Analytics', '', '', '', 'root', '', 'No'),
('org1', 'KDIC', 'To protect depositors and enhance public confidence in the financial system by promoting sound risk management and timely resolution', 'A reliable, effective Deposit Insurer and Resolution Authority', 'Professionalism | Integrity | Teamwork | Innovativeness | Customer Focus | Accountability	', 'root', '10000', 'Yes'),
('org2', 'Legal Services Directorate', 'To promote public confidence in the financial system through deposit insurance, risk management and timely resolution', 'To be a premier deposit insurance scheme', 'Professionalism | Integrity | Customer Centric | Fairness | Innovativeness | Teamwork', 'org1', 'NaN', NULL),
('org4', 'Strategy Planning & Research', '', '', '', 'org1', 'NaN', NULL),
('org5', 'Internal Audit', '', '', '', 'org1', 'NaN', NULL),
('org6', 'HR & Admin', '', '', '', 'org1', 'NaN', NULL),
('org7', 'Deposit Insurance & Bank Surveillance', '', '', '', 'org1', 'NaN', NULL),
('org8', 'CEO Office', '', '', '', 'org1', 'NaN', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pdp`
--

CREATE TABLE `pdp` (
  `id` int NOT NULL,
  `indId` varchar(9) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `skillGap` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `intervention` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `dueDate` date DEFAULT NULL,
  `completionDate` date DEFAULT NULL,
  `resource` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `comments` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `archive` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `lastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `perspective`
--

CREATE TABLE `perspective` (
  `id` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `name` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `parentId` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `weight` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `icon` varchar(90) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `perspective`
--

INSERT INTO `perspective` (`id`, `name`, `parentId`, `weight`, `icon`) VALUES
('persp10', 'Internal Business Process Perspective', 'org1', '16', '<i class=\'bi bi-diagram-3 float-end fs-4\'></i>'),
('persp11', 'Organisational Capacity', 'org1', '18', '<i class=\'bi bi-graph-up-arrow float-end fs-4\'></i>'),
('persp12', 'Financial Perspective', 'org2', '0.25', '<i class=\'bi bi-cash-stack float-end fs-4\''),
('persp13', 'Customer Perspective', 'org2', '0.25', '<i class=\'bi bi-people float-end fs-4\'></i>'),
('persp14', 'Internal Business Process', 'org2', '0.25', '<i class=\'bi bi-diagram-3 float-end fs-4\'></i>'),
('persp15', 'Learning & Growth', 'org2', '0.25', '<i class=\'bi bi-graph-up-arrow float-end fs-4\'></i>'),
('persp16', 'Financial Perspective', 'org4', '0.25', '<i class=\'bi bi-cash-stack float-end fs-4\''),
('persp17', 'Customer Perspective ', 'org4', '0.25', '<i class=\'bi bi-people float-end fs-4\'></i>'),
('persp18', 'Internal Business Process', 'org4', '0.25', '<i class=\'bi bi-diagram-3 float-end fs-4\'></i>'),
('persp19', 'Organisational Capacity', 'org4', '0.25', '<i class=\'bi bi-graph-up-arrow float-end fs-4\'></i>'),
('persp20', 'Financial Perspective', 'org5', '0.25', '<i class=\'bi bi-cash-stack float-end fs-4\''),
('persp21', 'Customer Perspective', 'org5', '0.25', '<i class=\'bi bi-people float-end fs-4\'></i>'),
('persp22', 'Learning & Growth Perspective', 'org5', '0.25', '<i class=\'bi bi-graph-up-arrow float-end fs-4\'></i>'),
('persp23', 'Internal Business Process Perspective', 'org6', '0.25', '<i class=\'bi bi-diagram-3 float-end fs-4\'></i>'),
('persp24', 'Learning & Growth', 'org6', '0.25', '<i class=\'bi bi-graph-up-arrow float-end fs-4\'></i>'),
('persp25', 'Financial Perspective', 'org6', '0.25', '<i class=\'bi bi-cash-stack float-end fs-4\''),
('persp26', 'Customer Perspective', 'org6', '0.25', '<i class=\'bi bi-people float-end fs-4\'></i>'),
('persp27', 'Financial Perspective', 'org7', '0.33', '<i class=\'bi bi-cash-stack float-end fs-4\''),
('persp28', 'Customer Perspective', 'org7', '0.33', '<i class=\'bi bi-people float-end fs-4\'></i>'),
('persp29', 'Internal Business Process Perspective', 'org7', '0.33', '<i class=\'bi bi-diagram-3 float-end fs-4\'></i>'),
('persp3', 'Internal Processes', 'org1', '0.13', '<i class=\'bi bi-diagram-3 float-end fs-4\'></i>'),
('persp30', 'Internal Business Process Perspective', 'org5', '0.25', '<i class=\'bi bi-diagram-3 float-end fs-4\'></i>'),
('persp31', 'Financial Perspective', 'org8', '0.33', '<i class=\'bi bi-cash-stack float-end fs-4\''),
('persp32', 'Customer Perspective', 'org8', '0.33', '<i class=\'bi bi-people float-end fs-4\'></i>'),
('persp33', 'Internal Business Process Perspective', 'org8', '0.33', '<i class=\'bi bi-diagram-3 float-end fs-4\'></i>'),
('persp4', 'Organisational Capacity', 'org1', '0.13', '<i class=\'bi bi-graph-up-arrow float-end fs-4\'></i>'),
('persp8', 'Financial Perspective', 'org1', '0.13', '<i class=\'bi bi-cash-stack float-end fs-4\''),
('persp9', 'Customer Perspective', 'org1', '0.13', '<i class=\'bi bi-people float-end fs-4\'></i>');

-- --------------------------------------------------------

--
-- Table structure for table `phpjobscheduler`
--

CREATE TABLE `phpjobscheduler` (
  `id` int NOT NULL,
  `scriptpath` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `name` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `time_interval` int DEFAULT NULL,
  `fire_time` int NOT NULL DEFAULT '0',
  `time_last_fired` int DEFAULT NULL,
  `run_only_once` tinyint(1) NOT NULL DEFAULT '0',
  `currently_running` tinyint(1) NOT NULL DEFAULT '0',
  `paused` tinyint(1) NOT NULL DEFAULT '0',
  `selectedStaff` varchar(9000) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `phpjobscheduler`
--

INSERT INTO `phpjobscheduler` (`id`, `scriptpath`, `name`, `time_interval`, `fire_time`, `time_last_fired`, `run_only_once`, `currently_running`, `paused`, `selectedStaff`) VALUES
(1, 'this sample.php', 'Email site stats to team lead', 7257600, 1730909507, 1723651907, 0, 0, 0, ''),
(2, 'Schedule 6.php', 'Sample Schedule', 36000, 1622087720, 0, 0, 0, 1, ''),
(15, 'https://kdic.accent-analytics.com/analytics/mail/mailMeasuresTasks.php', 'Email My Measures and Initiatives', 2419200, 1640596875, 1638177675, 0, 0, 1, '[{\"value\":\"ind2\",\"label\":\"Nicholas Kihara\"},{\"value\":\"ind8\",\"label\":\"Stephen Maina\"},{\"value\":\"ind9\",\"label\":\"Joan Muturi\"},{\"value\":\"ind10\",\"label\":\"Irene Maranga\"},{\"value\":\"ind11\",\"label\":\"Peter Ndichu\"},{\"value\":\"ind12\",\"label\":\"Catherine Obwino\"},{\"value\":\"ind13\",\"label\":\"Feisal Ahmed\"},{\"value\":\"ind14\",\"label\":\"Collins Ojenge\"},{\"value\":\"ind15\",\"label\":\"Teresiah Karanja\"},{\"value\":\"ind16\",\"label\":\"Felister Chege\"},{\"value\":\"ind17\",\"label\":\"Faith Wairimu\"},{\"value\":\"ind18\",\"label\":\"Valentine Ochieng\"},{\"value\":\"ind19\",\"label\":\"Edward Kibugi\"},{\"value\":\"ind20\",\"label\":\"Test User\"},{\"value\":\"ind21\",\"label\":\"Joseph Gichuhi\"},{\"value\":\"ind22\",\"label\":\"Machel Kiugu\"},{\"value\":\"ind23\",\"label\":\"Wambui Muhoro\"},{\"value\":\"ind24\",\"label\":\"Daniel Kinogu\"},{\"value\":\"ind25\",\"label\":\"Grace Awaka\"},{\"value\":\"ind26\",\"label\":\"Iddah  Membo\"},{\"value\":\"ind27\",\"label\":\"Kennedy Kihahu Ndungu\"},{\"value\":\"ind28\",\"label\":\"Elizabeth Kiama\"},{\"value\":\"ind29\",\"label\":\"Faith Ndungu\"},{\"value\":\"ind30\",\"label\":\"Dorcas Wambui\"},{\"value\":\"ind31\",\"label\":\"Brian Munga Karau\"},{\"value\":\"ind32\",\"label\":\"Berita Kalya\"},{\"value\":\"ind33\",\"label\":\"Caroline Ribui\"},{\"value\":\"ind34\",\"label\":\"Charles Wahome\"},{\"value\":\"ind35\",\"label\":\"Lucy Wamuyu Mageria\"},{\"value\":\"ind36\",\"label\":\"Geoffrey Tinega\"},{\"value\":\"ind37\",\"label\":\"Andrew Kamau\"},{\"value\":\"ind38\",\"label\":\"Henry Njenga\"},{\"value\":\"ind39\",\"label\":\"Bernard Kiarie\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `phpjobscheduler_logs`
--

CREATE TABLE `phpjobscheduler_logs` (
  `id` int NOT NULL,
  `date_added` int DEFAULT NULL,
  `script` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `output` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `execution_time` varchar(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `pictures`
--

CREATE TABLE `pictures` (
  `id` int NOT NULL,
  `projectId` int NOT NULL,
  `name` varchar(900) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `description` varchar(900) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `url` varchar(900) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `county` varchar(240) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `size` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `Id` int NOT NULL,
  `reportName` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `selectedObjects` varchar(300) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `displayId` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Measure` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Organization` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `orgScore` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Perspective` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `perspScore` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Objective` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `objScore` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Owner` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Updater` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Score` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Actual` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Red` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Green` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Variance` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `PercentVariance` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `linkedTo` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `Type` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `redFilter` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `greyFilter` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `greenFilter` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `initiativeFilter` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `initiativeGroup` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`Id`, `reportName`, `selectedObjects`, `displayId`, `Measure`, `Organization`, `orgScore`, `Perspective`, `perspScore`, `Objective`, `objScore`, `Owner`, `Updater`, `Score`, `Actual`, `Red`, `Green`, `Variance`, `PercentVariance`, `linkedTo`, `Type`, `redFilter`, `greyFilter`, `greenFilter`, `initiativeFilter`, `initiativeGroup`) VALUES
(1, 'Cascading Report', 'org1', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'org1', 'cascadeReport', '', '', '', NULL, NULL),
(2, 'Initiatives Report', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'org1', 'initiativeReport', '', '', '', NULL, NULL),
(3, 'ICT Scorecard Summary', 'org8', '', 'true', 'true', 'true', 'false', 'false', 'true', 'true', 'false', 'false', 'true', 'true', '', 'false', 'false', 'false', 'org1', 'customReport', 'false', 'false', 'false', 'true', 'false'),
(4, 'Scorecard Summary', 'org1', '', 'true', 'true', 'true', 'true', 'true', 'true', 'true', 'false', 'false', 'false', 'true', '', 'false', 'false', 'false', 'org1', 'customReport', 'false', 'false', 'false', 'true', 'false');

-- --------------------------------------------------------

--
-- Table structure for table `report_init`
--

CREATE TABLE `report_init` (
  `id` varchar(9) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `sponsor` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `owner` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `budget` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `cost` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `start` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `due` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `completed` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `deliverable` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `deliverableStatus` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `parent` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `red` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `yellow` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `green` varchar(6) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `report_init`
--

INSERT INTO `report_init` (`id`, `sponsor`, `owner`, `budget`, `cost`, `start`, `due`, `completed`, `deliverable`, `deliverableStatus`, `parent`, `red`, `yellow`, `green`) VALUES
('2', 'true', 'true', 'true', 'true', 'true', 'true', 'false', 'false', 'false', 'false', 'false', 'false', 'false');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `item` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `value` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `item`, `value`) VALUES
(1, 'Financial Year Start Date', '2014-07-01'),
(2, 'Currency', 'KShs');

-- --------------------------------------------------------

--
-- Table structure for table `strategic_results`
--

CREATE TABLE `strategic_results` (
  `id` int NOT NULL,
  `priority` varchar(1200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `result` varchar(1200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `strategic_results`
--

INSERT INTO `strategic_results` (`id`, `priority`, `result`) VALUES
(1, 'Deposit Insurance Scheme - Build and sustain financial strength', 'Deposit Insurance'),
(2, 'Early detection and timely intervention', 'Risk Minimization'),
(3, 'Problem banks / failed institutions - Enhance efficiency in receivership, liquidation and widening up of member institutions', 'Resolution of Problem Banks'),
(4, 'Insitutional capacity - strengthened institutional capacity for effective service delivery', 'Institutional Capacity Development');

-- --------------------------------------------------------

--
-- Table structure for table `supervisor_score`
--

CREATE TABLE `supervisor_score` (
  `id` int NOT NULL,
  `object_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `supervisor_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `score` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `period` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tree`
--

CREATE TABLE `tree` (
  `idTree` int NOT NULL,
  `id` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `name` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `parent` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `type` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `linked` varchar(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `sort` int NOT NULL,
  `bscType` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tree`
--

INSERT INTO `tree` (`idTree`, `id`, `name`, `parent`, `type`, `linked`, `sort`, `bscType`) VALUES
(1, 'root', 'Root', '', '', NULL, 1000, 'pc'),
(2, 'org1', 'KDIC', 'root', 'organization', 'no', 1000, 'pc'),
(68, 'persp1', 'Financial', 'org15', 'perspective', 'no', 1000, ''),
(69, 'persp2', 'Customer', 'org15', 'perspective', 'no', 1000, ''),
(70, 'persp3', 'Internal Processes', 'org15', 'perspective', 'no', 1000, ''),
(71, 'persp4', 'Organizational Capacity', 'org15', 'perspective', 'no', 1000, ''),
(202, 'org12', 'Manufacturing', 'org1', 'organization', 'no', 1000, ''),
(225, 'org19', 'Strategy', 'org1', 'organization', 'no', 1000, ''),
(226, 'org20', 'Supply Chain', 'org1', 'organization', 'no', 1000, ''),
(262, 'persp8', 'Financial Perspective', 'org1', 'perspective', 'no', 3000, ''),
(263, 'persp9', 'Customer Perspective', 'org1', 'perspective', 'no', 3000, ''),
(264, 'persp10', 'Internal Business Process Perspective', 'org1', 'perspective', 'no', 3000, ''),
(265, 'obj111', 'Deposit Insurance', 'persp8', 'objective', 'no', 3000, ''),
(266, 'kpi1', 'Growth of the Investment income', 'obj111', 'measure', 'no', 3000, ''),
(267, 'obj112', 'Institutional Capacity Development', 'persp8', 'objective', 'no', 3000, ''),
(268, 'kpi2', 'Budget absorption rate', 'obj112', 'measure', 'no', 3000, ''),
(269, 'kpi3', 'Level of adherence to procurement plan', 'obj112', 'measure', 'no', 3000, ''),
(270, 'kpi4', 'Implementation of board resolutions', 'obj112', 'measure', 'no', 3000, ''),
(271, 'kpi5', 'Management of litigation costs', 'obj112', 'measure', 'no', 3000, ''),
(272, 'obj113', 'Resolution of Problem Banks', 'persp8', 'objective', 'no', 3000, ''),
(273, 'kpi6', 'Amount of loans/ debts collected', 'obj113', 'measure', 'no', 3000, ''),
(274, 'obj114', 'Institutional Capacity Development', 'persp9', 'objective', 'no', 3000, ''),
(275, 'kpi7', 'Public Awareness Index', 'obj114', 'measure', 'no', 3000, ''),
(276, 'obj115', 'Risk Minimization', 'persp9', 'objective', 'no', 3000, ''),
(277, 'kpi8', 'Number of Engagements', 'obj115', 'measure', 'no', 3000, ''),
(278, 'kpi9', 'Approval of the Deposit Insurance Academy', 'obj115', 'measure', 'no', 3000, ''),
(279, 'obj116', 'Deposit Insurance', 'persp10', 'objective', 'no', 3000, ''),
(280, 'kpi20', '% of Fund to Insured Deposit (Coverage Ratio)', 'obj116', 'measure', 'no', 3000, ''),
(281, 'kpi11', '% of Insured Deposits to Total Deposits', 'obj116', 'measure', 'no', 3000, ''),
(282, 'obj117', 'Resolution of Problem Banks', 'persp10', 'objective', 'no', 3000, ''),
(283, 'kpi12', 'No. of targeted institutions for winding up', 'obj117', 'measure', 'no', 3000, ''),
(284, 'kpi13', 'No of institutions wound up', 'obj117', 'measure', 'no', 3000, ''),
(285, 'kpi14', 'Amount of dividend declared', 'obj117', 'measure', 'no', 3000, ''),
(286, 'obj118', 'Institutional Capacity Development', 'persp10', 'objective', 'no', 3000, ''),
(287, 'kpi15', 'Number of processes automated', 'obj118', 'measure', 'no', 3000, ''),
(288, 'kpi16', 'Number of identified records digitized', 'obj118', 'measure', 'no', 3000, ''),
(289, 'kpi17', 'Certifications Retained', 'obj118', 'measure', 'no', 3000, ''),
(290, 'persp11', 'Organisational Capacity', 'org1', 'perspective', 'no', 3000, ''),
(291, 'obj119', 'Risk Minimization', 'persp10', 'objective', 'no', 3000, ''),
(292, 'kpi18', 'Living Wills', 'obj119', 'measure', 'no', 3000, ''),
(293, 'kpi19', 'AI Report', 'obj119', 'measure', 'no', 3000, ''),
(294, 'kpi20', 'CAMEL Reports', 'obj119', 'measure', 'no', 3000, ''),
(295, 'kpi21', 'Simulation exercises', 'obj119', 'measure', 'no', 3000, ''),
(296, 'kpi22', 'Implemented Recommendations', 'obj119', 'measure', 'no', 3000, ''),
(297, 'obj120', 'Institutional Capacity Development', 'persp11', 'objective', 'no', 3000, ''),
(298, 'kpi23', 'Retention Rate', 'obj120', 'measure', 'no', 3000, ''),
(299, 'kpi24', 'Staff Recruited', 'obj120', 'measure', 'no', 3000, ''),
(300, 'kpi25', 'Implemented Recommendations', 'obj120', 'measure', 'no', 3000, ''),
(301, 'kpi26', 'Productivity Index Improved', 'obj120', 'measure', 'no', 3000, ''),
(302, 'kpi27', 'Culture Entropy', 'obj120', 'measure', 'no', 3000, ''),
(303, 'kpi28', 'Corporate Culture: Recommendations', 'obj120', 'measure', 'no', 3000, ''),
(335, 'org2', 'Legal Services Directorate', 'org1', 'organization', 'no', 3000, ''),
(336, 'persp12', 'Financial Perspective', 'org2', 'perspective', 'no', 1, ''),
(337, 'obj121', 'Enhance prudence in the utilisation of resources', 'persp12', 'objective', 'no', 3000, ''),
(338, 'kpi29', 'Budget absorption rate', 'obj121', 'measure', 'no', 3000, ''),
(339, 'kpi30', 'Level of adherence to procurement plan', 'obj121', 'measure', 'no', 3000, ''),
(340, 'ind8', 'Eunice Kitche', 'org2', 'individual', 'no', 3000, ''),
(341, 'kpi31', 'Implementation of board resolutions', 'obj121', 'measure', 'no', 3000, ''),
(342, 'kpi32', 'Management of Litigation Costs', 'obj121', 'measure', 'no', 3000, ''),
(343, 'persp13', 'Customer Perspective', 'org2', 'perspective', 'no', 2, ''),
(344, 'obj122', 'Establish & enhance strategic collaborations & partnerships with stakeholders', 'persp13', 'objective', 'no', 3000, ''),
(345, 'obj123', 'Promote confidence & trust among stakeholders', 'persp13', 'objective', 'no', 3000, ''),
(346, 'obj124', 'Establish & enhance strategic collaborations & partnerships with stakeholders', 'persp13', 'objective', 'no', 3000, ''),
(347, 'kpi33', 'Number of engagements', 'obj122', 'measure', 'no', 3000, ''),
(348, 'kpi34', 'Level of compliance with service delivery timelines', 'obj123', 'measure', 'no', 3000, ''),
(349, 'kpi35', 'Approval of the Deposit Insurance Academy', 'obj124', 'measure', 'no', 3000, ''),
(350, 'persp14', 'Internal Business Process', 'org2', 'perspective', 'no', 3000, ''),
(351, 'obj125', 'Enhance depositor protection & compensation', 'persp14', 'objective', 'no', 3000, ''),
(352, 'kpi36', 'Development & implementation of proposed guidelines on Trust Accounts', 'obj125', 'measure', 'no', 3000, ''),
(353, 'obj126', 'Provision of legal advisory services in the Discharge of Securities', 'persp14', 'objective', 'no', 3000, ''),
(354, 'kpi37', 'Percentage of discharges processed against requests made', 'obj126', 'measure', 'no', 3000, ''),
(355, 'obj127', 'Provision of legal advisory in Contract Management', 'persp14', 'objective', 'no', 3000, ''),
(356, 'kpi38', 'Percentage of contracts reviewed against requests made', 'obj127', 'measure', 'no', 3000, ''),
(357, 'obj128', 'Automate process & digitize records', 'persp14', 'objective', 'no', 3000, ''),
(358, 'kpi39', 'Percentage level of digitization of legal & board records', 'obj128', 'measure', 'no', 3000, ''),
(359, 'obj129', 'Enhance risk minimisation', 'persp14', 'objective', 'no', 3000, ''),
(360, 'kpi40', 'Standardise processes', 'obj129', 'measure', 'no', 3000, ''),
(361, 'kpi41', 'Closure of internal audit findings', 'obj129', 'measure', 'no', 3000, ''),
(362, 'kpi42', 'Implementation of policies', 'obj129', 'measure', 'no', 3000, ''),
(363, 'obj130', 'Provision of legal advisory services in assessment of fees', 'persp14', 'objective', 'no', 3000, ''),
(364, 'kpi43', 'Percentage of assessment undertaken against requests made', 'obj130', 'measure', 'no', 3000, ''),
(365, 'obj131', 'Strengthen litigation', 'persp14', 'objective', 'no', 3000, ''),
(366, 'kpi44', 'Litigation management', 'obj131', 'measure', 'no', 3000, ''),
(367, 'obj132', 'Compliance with existing regulatory framework', 'persp14', 'objective', 'no', 3000, ''),
(368, 'kpi45', 'Management of board affairs', 'obj132', 'measure', 'no', 3000, ''),
(369, 'obj133', 'Improve the regulatory framework', 'persp14', 'objective', 'no', 3000, ''),
(370, 'kpi46', 'Recommend & seek review of the sections of the KDI Act', 'obj133', 'measure', 'no', 3000, ''),
(371, 'kpi47', 'Seek approval for proposed DPS model', 'obj133', 'measure', 'no', 3000, ''),
(372, 'kpi48', 'Implement recommendations of the compliance & governance audit', 'obj133', 'measure', 'no', 3000, ''),
(373, 'kpi49', 'Enhance corporate governance', 'obj133', 'measure', 'no', 3000, ''),
(374, 'persp15', 'Learning & Growth', 'org2', 'perspective', 'no', 3000, ''),
(375, 'obj134', 'Staff retention', 'persp15', 'objective', 'no', 3000, ''),
(376, 'kpi50', 'Percentage of staff charges/discharges undertaken against requests made', 'obj134', 'measure', 'no', 3000, ''),
(377, 'obj135', 'Institutionalize performance management & staff productivity', 'persp15', 'objective', 'no', 3000, ''),
(378, 'kpi51', 'Enhance staff productivity', 'obj135', 'measure', 'no', 3000, ''),
(379, 'obj136', 'Build a vibrant & cohesive organizational culture', 'persp15', 'objective', 'no', 3000, ''),
(380, 'kpi52', 'Culture entropy', 'obj136', 'measure', 'no', 3000, ''),
(381, 'obj137', 'Enhance staff productivity', 'persp15', 'objective', 'no', 3000, ''),
(382, 'kpi53', 'Staff retention', 'obj137', 'measure', 'no', 3000, ''),
(384, 'org4', 'Strategy Planning & Research', 'org1', 'organization', 'no', 3000, ''),
(385, 'persp16', 'Financial Perspective', 'org4', 'perspective', 'no', 3000, ''),
(386, 'obj138', 'Enhance prudence in the utilisation of the allocated resources', 'persp16', 'objective', 'no', 3000, ''),
(387, 'kpi54', 'Absorption rate of allocated funds', 'obj138', 'measure', 'no', 3000, ''),
(388, 'persp17', 'Customer Perspective ', 'org4', 'perspective', 'no', 3000, ''),
(389, 'obj139', 'Institutional Capacity Development', 'persp17', 'objective', 'no', 3000, ''),
(390, 'kpi55', 'Percentage level of compliance', 'obj139', 'measure', 'no', 3000, ''),
(391, 'persp18', 'Internal Business Process', 'org4', 'perspective', 'no', 3000, ''),
(394, 'kpi56', 'Enhance corporate performance management', 'obj140', 'measure', 'no', 3000, ''),
(395, 'kpi57', 'Promote good corporate governance practices', 'obj140', 'measure', 'no', 3000, ''),
(396, 'kpi58', 'Improve corporate performance', 'obj140', 'measure', 'no', 3000, ''),
(397, 'obj142', 'Enhance Corporate Performance', 'persp18', 'objective', 'no', 3000, ''),
(398, 'kpi59', 'Corporate planning implementattion rate', 'obj142', 'measure', 'no', 3000, ''),
(399, 'obj143', 'Corporate governance practices', 'persp18', 'objective', 'no', 3000, ''),
(400, 'kpi60', 'Performance contracting quarterly reports', 'obj143', 'measure', 'no', 3000, ''),
(401, 'obj144', 'Improve corporate performance', 'persp18', 'objective', 'no', 3000, ''),
(402, 'kpi61', 'strategic plan quarterly reports', 'obj144', 'measure', 'no', 3000, ''),
(403, 'obj145', 'Implement depertmental identified processes for automstion', 'persp18', 'objective', 'no', 3000, ''),
(404, 'kpi62', 'Percentage level of automated processes', 'obj145', 'measure', 'no', 3000, ''),
(405, 'obj146', 'Percentage of processes standardized', 'persp18', 'objective', 'no', 3000, ''),
(406, 'kpi63', 'Percentage completion rate for ISMS', 'obj146', 'measure', 'no', 3000, ''),
(407, 'obj147', 'Maintenance of QMS', 'persp18', 'objective', 'no', 3000, ''),
(408, 'kpi64', 'No of internal audits', 'obj147', 'measure', 'no', 3000, ''),
(409, 'obj148', 'Enhance implementation and maintenance of the ERM', 'persp18', 'objective', 'no', 3000, ''),
(410, 'kpi65', 'ERM Quarterly Reports', 'obj148', 'measure', 'no', 3000, ''),
(411, 'kpi66', 'Annual staff education training  and sensitisation on ERM and BCP table-top testing', 'obj148', 'measure', 'no', 3000, ''),
(412, 'obj149', 'Establish and enhance collaborations', 'persp18', 'objective', 'no', 3000, ''),
(413, 'kpi67', 'No of MoUs signed', 'obj149', 'measure', 'no', 3000, ''),
(414, 'obj150', 'Strengthen corporate compliance ', 'persp18', 'objective', 'no', 3000, ''),
(415, 'kpi68', 'Compliance level', 'obj150', 'measure', 'no', 3000, ''),
(416, 'persp19', 'Organisational Capacity', 'org4', 'perspective', 'no', 3000, ''),
(417, 'obj151', 'Enhance departmental performance', 'persp19', 'objective', 'no', 3000, ''),
(418, 'kpi69', 'Implementation level of PMS for the departments staff', 'obj151', 'measure', 'no', 3000, ''),
(420, 'obj152', 'Attend trainings for capacity', 'persp18', 'objective', 'no', 3000, ''),
(421, 'obj153', 'Trainings for capacity building', 'persp19', 'objective', 'no', 3000, ''),
(422, 'kpi71', 'Trainings attended', 'obj153', 'measure', 'no', 3000, ''),
(423, 'obj154', 'Development and implementation of PMS', 'persp19', 'objective', 'no', 3000, ''),
(424, 'kpi72', 'Competency Level', 'obj154', 'measure', 'no', 3000, ''),
(425, 'obj155', 'Enhance staff productivity', 'persp19', 'objective', 'no', 3000, ''),
(426, 'kpi73', '% of workplan implementation', 'obj155', 'measure', 'no', 3000, ''),
(427, 'org5', 'Internal Audit', 'org1', 'organization', 'no', 3000, ''),
(428, 'persp20', 'Financial Perspective', 'org5', 'perspective', 'no', 3000, ''),
(429, 'obj156', 'Utilization of resources', 'persp20', 'objective', 'no', 3000, ''),
(430, 'kpi74', 'Budget Absorbtion rate', 'obj156', 'measure', 'no', 3000, ''),
(431, 'kpi75', 'Implementation of board resolutions', 'obj156', 'measure', 'no', 3000, ''),
(432, 'persp21', 'Customer Perspective', 'org5', 'perspective', 'no', 3000, ''),
(433, 'obj157', 'Compliance with service delivery', 'persp21', 'objective', 'no', 3000, ''),
(434, 'kpi76', 'Service charter compliance', 'obj157', 'measure', 'no', 3000, ''),
(435, 'obj158', 'Customer Satisfaction', 'persp21', 'objective', 'no', 3000, ''),
(436, 'kpi77', '% level of satisfaction by audit clients', 'obj158', 'measure', 'no', 3000, ''),
(437, 'kpi78', 'Preparation and uploading of BAC and board papers', 'obj158', 'measure', 'no', 3000, ''),
(439, 'obj160', 'Enhance operational capacity', 'persp30', 'objective', 'no', 3000, ''),
(440, 'kpi79', '% annual audit work plan implemented ', 'obj160', 'measure', 'no', 3000, ''),
(441, 'kpi80', 'Quarterly board audit committee', 'obj183', 'measure', 'no', 3000, ''),
(442, 'persp22', 'Learning & Growth Perspective', 'org5', 'perspective', 'no', 3000, ''),
(443, 'obj161', 'Attract acquire& retain internal audit staff', 'persp22', 'objective', 'no', 3000, ''),
(444, 'kpi81', '% of staff retained', 'obj161', 'measure', 'no', 3000, ''),
(445, 'obj162', 'Strengthen performance management', 'persp22', 'objective', 'no', 3000, ''),
(446, 'kpi82', 'Performance appraisals - Quarterly', 'obj162', 'measure', 'no', 3000, ''),
(447, 'kpi83', 'Performance appraisals - Annual', 'obj162', 'measure', 'no', 3000, ''),
(448, 'kpi84', 'Productivity Index - Improved', 'obj162', 'measure', 'no', 3000, ''),
(449, 'obj163', 'Enhance staff capacity', 'persp22', 'objective', 'no', 3000, ''),
(450, 'kpi85', '% implementation of the approved departmental training and performance gaps ', 'obj163', 'measure', 'no', 3000, ''),
(451, 'ind7', 'Lawrence Shoona', 'org4', 'individual', 'no', 3000, ''),
(452, 'org6', 'HR & Admin', 'org1', 'organization', 'no', 3000, ''),
(453, 'persp23', 'Internal Business Process Perspective', 'org6', 'perspective', 'no', 3000, ''),
(454, 'obj164', 'Enhance prudence in utilisational resources', 'persp23', 'objective', 'no', 3000, ''),
(455, 'kpi86', 'Budegt absorbtion rate', 'obj171', 'measure', 'no', 3000, ''),
(456, 'obj165', 'Strengthen Employee Morale', 'persp26', 'objective', 'no', 3000, ''),
(457, 'kpi87', 'Employee satisfaction rate', 'obj165', 'measure', 'no', 3000, ''),
(458, 'kpi88', 'Level of adherence to procurement plans', 'obj164', 'measure', 'no', 3000, ''),
(459, 'kpi89', 'Implementation of board resolutions', 'obj164', 'measure', 'no', 3000, ''),
(460, 'obj166', 'Populate the staff establishment', 'persp23', 'objective', 'no', 3000, ''),
(461, 'kpi90', 'No. of staff recruited', 'obj166', 'measure', 'no', 3000, ''),
(462, 'obj167', 'Build a vibrant and cohesive organizational culture', 'persp23', 'objective', 'no', 3000, ''),
(463, 'kpi91', 'Culture entropy', 'obj167', 'measure', 'no', 3000, ''),
(464, 'kpi92', 'Corporate culture exir survey report\\\\', 'obj167', 'measure', 'no', 3000, ''),
(465, 'ind4', 'Peter Ibrae', 'org6', 'individual', 'no', 3000, ''),
(466, 'obj168', 'Institutionalise Performance Management and Staff Productivity', 'persp23', 'objective', 'no', 3000, ''),
(467, 'kpi93', 'Staff Productivity Index', 'obj168', 'measure', 'no', 3000, ''),
(468, 'kpi94', 'Corporate Performance Index', 'obj168', 'measure', 'no', 3000, ''),
(469, 'kpi95', 'Staff Engagement Report and Index', 'obj168', 'measure', 'no', 3000, ''),
(470, 'obj169', 'Undertake Capacity Building Program', 'persp23', 'objective', 'no', 3000, ''),
(471, 'kpi96', 'Level of implementation of the training plan', 'obj169', 'measure', 'no', 3000, ''),
(472, 'kpi97', 'Skills Mapping Report', 'obj169', 'measure', 'no', 3000, ''),
(473, 'kpi98', 'Skill Gap Analysis Report', 'obj169', 'measure', 'no', 3000, ''),
(474, 'kpi99', 'Approved Succession Planning and Talent Management Framework', 'obj169', 'measure', 'no', 3000, ''),
(475, 'kpi100', 'Approved Knowledge Management Framewoork', 'obj169', 'measure', 'no', 3000, ''),
(476, 'kpi101', 'Team Building Activities', 'obj169', 'measure', 'no', 3000, ''),
(477, 'kpi102', 'Approved Staff Wellness Program', 'obj169', 'measure', 'no', 3000, ''),
(478, 'kpi103', 'Workload Analysis Report', 'obj169', 'measure', 'no', 3000, ''),
(479, 'kpi104', 'Approved Records Management Policy', 'obj169', 'measure', 'no', 3000, ''),
(480, 'kpi105', 'Interns and Attachess Recruited', 'obj169', 'measure', 'no', 3000, ''),
(481, 'kpi106', 'Monthly Payrolls', 'obj169', 'measure', 'no', 3000, ''),
(482, 'kpi107', 'Approved Salary Survey Report', 'obj169', 'measure', 'no', 3000, ''),
(483, 'persp24', 'Learning & Growth', 'org6', 'perspective', 'no', 3000, ''),
(484, 'obj170', 'Staff Performance and Management', 'persp24', 'objective', 'no', 3000, ''),
(485, 'kpi108', 'Level of Performance Management Implementation', 'obj170', 'measure', 'no', 3000, ''),
(486, 'kpi109', 'Level of Implementation of HR Training Plan', 'obj170', 'measure', 'no', 3000, ''),
(487, 'persp25', 'Financial Perspective', 'org6', 'perspective', 'no', 3000, ''),
(488, 'obj171', 'Enhance Prudence in the utilisation of resources', 'persp25', 'objective', 'no', 3000, ''),
(489, 'persp26', 'Customer Perspective', 'org6', 'perspective', 'no', 3000, ''),
(490, 'org7', 'Deposit Insurance & Bank Surveillance', 'org1', 'organization', 'no', 3000, ''),
(491, 'ind5', 'Paul Manga', 'org7', 'individual', 'no', 3000, ''),
(492, 'persp27', 'Financial Perspective', 'org7', 'perspective', 'no', 3000, ''),
(493, 'obj172', 'Prudent Management of Deposit Insurance Fund', 'persp27', 'objective', 'no', 3000, ''),
(494, 'kpi110', 'Growth of Investment Income', 'obj172', 'measure', 'no', 3000, ''),
(495, 'obj173', 'Enhance Prudence in the utilisation of resources', 'persp27', 'objective', 'no', 3000, ''),
(496, 'kpi111', 'Budget Absorption Rate', 'obj173', 'measure', 'no', 3000, ''),
(497, 'kpi112', 'Level of Adherence to Procurement Plan', 'obj173', 'measure', 'no', 3000, ''),
(498, 'kpi113', 'Implementation of Board Resolutions', 'obj173', 'measure', 'no', 3000, ''),
(499, 'persp28', 'Customer Perspective', 'org7', 'perspective', 'no', 3000, ''),
(500, 'obj174', 'Public Awareness Index', 'persp28', 'objective', 'no', 3000, ''),
(501, 'kpi114', 'Public Awareness Index', 'obj174', 'measure', 'no', 3000, ''),
(502, 'obj175', 'Establish and enhance strategic collaborations and partnerships', 'persp28', 'objective', 'no', 3000, ''),
(503, 'kpi115', 'Number of Engagements', 'obj175', 'measure', 'no', 3000, ''),
(504, 'kpi116', 'Approval of the deposit Insurance Academy', 'obj175', 'measure', 'no', 3000, ''),
(505, 'kpi117', 'Technical Assistance Offered', 'obj175', 'measure', 'no', 3000, ''),
(506, 'persp29', 'Internal Business Process Perspective', 'org7', 'perspective', 'no', 3000, ''),
(507, 'obj176', 'Enhance Depositor Protections', 'persp29', 'objective', 'no', 3000, ''),
(508, 'kpi118', '% of Fund to Insured Deposit', 'obj176', 'measure', 'no', 3000, ''),
(509, 'kpi119', '% of insured Deposits to Total Deposits', 'obj176', 'measure', 'no', 3000, ''),
(510, 'obj177', 'Standardize Processes', 'persp29', 'objective', 'no', 3000, ''),
(511, 'kpi120', 'Certification Retained', 'obj177', 'measure', 'no', 3000, ''),
(512, 'obj178', 'Strengthen Early Intervention Framework', 'persp29', 'objective', 'no', 3000, ''),
(513, 'kpi121', 'Living Wills', 'obj178', 'measure', 'no', 3000, ''),
(514, 'kpi122', 'AI Report', 'obj178', 'measure', 'no', 3000, ''),
(515, 'obj179', 'Enhance Risk Minimization', 'persp29', 'objective', 'no', 3000, ''),
(516, 'kpi123', 'CAMEL Reports', 'obj179', 'measure', 'no', 3000, ''),
(517, 'obj180', 'Improve Crisis Management Framwework', 'persp29', 'objective', 'no', 3000, ''),
(518, 'kpi124', 'Simulation Exercises', 'obj180', 'measure', 'no', 3000, ''),
(519, 'obj181', 'Strengthen Regulatory Framework', 'persp29', 'objective', 'no', 3000, ''),
(520, 'kpi125', 'Implemented Regulations', 'obj181', 'measure', 'no', 3000, ''),
(521, 'obj182', 'Institutionalize Performance Management', 'persp29', 'objective', 'no', 3000, ''),
(522, 'kpi126', 'Productivity Reports', 'obj182', 'measure', 'no', 3000, ''),
(523, 'kpi127', 'Recommendations Implemented', 'obj182', 'measure', 'no', 3000, ''),
(524, 'persp30', 'Internal Business Process Perspective', 'org5', 'perspective', 'no', 3000, ''),
(526, 'obj183', 'Strengthen Governance in the corporation', 'persp30', 'objective', 'no', 3000, ''),
(527, 'obj184', 'Strengthen Enterprise Risk Management', 'persp30', 'objective', 'no', 3000, ''),
(528, 'kpi128', 'ERM Assurance Reports', 'obj184', 'measure', 'no', 3000, ''),
(529, 'obj185', 'Maintenance of Quality Management System', 'persp30', 'objective', 'no', 3000, ''),
(530, 'kpi129', 'Level of Conformance to ISO 9001 and ISO 27001', 'obj185', 'measure', 'no', 3000, ''),
(531, 'obj186', 'Effective and Efficient Internal Audit Process', 'persp30', 'objective', 'no', 3000, ''),
(532, 'kpi130', '% implementation fo the internal audit standards', 'obj186', 'measure', 'no', 3000, ''),
(533, 'kpi131', 'Level of utilization of internal audit software', 'obj160', 'measure', 'no', 3000, ''),
(534, 'ind6', 'Mary Kiragu', 'org5', 'individual', 'no', 3000, ''),
(535, 'org8', 'CEO Office', 'org1', 'organization', 'no', 3000, ''),
(536, 'ind9', 'Crispus Yankem', 'org8', 'individual', 'no', 3000, ''),
(537, 'persp31', 'Financial Perspective', 'org8', 'perspective', 'no', 3000, ''),
(538, 'obj187', 'Enhance Prudence in the utilisation of resources', 'persp31', 'objective', 'no', 3000, ''),
(539, 'kpi132', 'Absorption of budget', 'obj187', 'measure', 'no', 3000, ''),
(540, 'kpi133', 'implementation of the department\\\'s procurement plan', 'obj187', 'measure', 'no', 3000, ''),
(541, 'kpi134', 'Implementation of Board Resolutions', 'obj187', 'measure', 'no', 3000, ''),
(542, 'persp32', 'Customer Perspective', 'org8', 'perspective', 'no', 3000, ''),
(543, 'obj188', 'Improve Public Awareness Index', 'persp32', 'objective', 'no', 3000, ''),
(544, 'kpi135', 'Public Awareness Index', 'obj188', 'measure', 'no', 3000, ''),
(545, 'kpi136', 'Brand Perception Index', 'obj188', 'measure', 'no', 3000, ''),
(546, 'persp33', 'Internal Business Process Perspective', 'org8', 'perspective', 'no', 3000, ''),
(547, 'obj189', 'strengthen Employee Moarale and Motivation', 'persp33', 'objective', 'no', 3000, ''),
(548, 'kpi137', 'Departmental Reports', 'obj189', 'measure', 'no', 3000, ''),
(549, 'obj190', 'Institutional Performance Management', 'persp33', 'objective', 'no', 3000, ''),
(550, 'kpi138', 'Productivity Index Report', 'obj190', 'measure', 'no', 3000, ''),
(551, 'obj191', 'Build a vibrant & cohesive ', 'persp33', 'objective', 'no', 3000, ''),
(552, 'kpi139', 'Documentation reports', 'obj191', 'measure', 'no', 3000, ''),
(553, 'ind3', 'Hellen Chepkwony', 'org1', 'individual', 'no', 3000, ''),
(554, 'obj192', 'Prudent Management of the Deposit Insurance Fund', 'ind3', 'objective', 'no', 3000, ''),
(555, 'kpi140', 'Grow Investment Income of the institutions in liquidation', 'obj192', 'measure', 'no', 3000, ''),
(556, 'obj193', 'Enhance prudence in the utilisation of utilisation of resources', 'ind3', 'objective', 'no', 3000, ''),
(557, 'kpi141', 'Budget Absorption rate', 'obj193', 'measure', 'no', 3000, ''),
(558, 'kpi142', 'Level of adherence to procurement plan', 'obj193', 'measure', 'no', 3000, ''),
(559, 'kpi143', 'Board Resolutions Implemented', 'obj193', 'measure', 'no', 3000, ''),
(560, 'kpi144', 'Ratio of Total Direct Costs/ Total Interest Income', 'obj193', 'measure', 'no', 3000, ''),
(561, 'obj194', 'Reduce amount owed by debtors and wind up financial institutions in liquidation', 'ind3', 'objective', 'no', 3000, ''),
(562, 'kpi145', 'Amount of Loans', 'obj194', 'measure', 'no', 3000, ''),
(563, 'obj195', 'Improve Public Awareness Index', 'ind3', 'objective', 'no', 3000, ''),
(564, 'kpi146', 'Number of Engagements', 'obj195', 'measure', 'no', 3000, ''),
(565, 'kpi147', 'Number of Complaints Resolved', 'obj195', 'measure', 'no', 3000, ''),
(566, 'obj196', 'Establish and enhance strategic collaborations', 'ind3', 'objective', 'no', 3000, ''),
(567, 'kpi148', 'Number of Engagements', 'obj196', 'measure', 'no', 3000, ''),
(568, 'obj197', 'Wind up Financial Instituions in Liquidations', 'ind3', 'objective', 'no', 3000, ''),
(569, 'kpi149', 'No of targeted institutions', 'obj197', 'measure', 'no', 3000, ''),
(570, 'kpi150', 'No of Institutions Wound Up', 'obj197', 'measure', 'no', 3000, ''),
(571, 'kpi151', 'Amount of Dividends Declared', 'obj197', 'measure', 'no', 3000, ''),
(572, 'obj198', 'Automate Processes and Digitize Records', 'ind3', 'objective', 'no', 3000, ''),
(573, 'kpi152', 'Functional Digitized Channel', 'obj198', 'measure', 'no', 3000, ''),
(574, 'kpi153', 'No of Identified Records Digitized', 'obj198', 'measure', 'no', 3000, ''),
(575, 'obj199', 'Standardize Processes', 'ind3', 'objective', 'no', 3000, ''),
(576, 'kpi154', 'Certifications Retained', 'obj199', 'measure', 'no', 3000, ''),
(577, 'obj200', 'Improve Crisis Management Framework', 'ind3', 'objective', 'no', 3000, ''),
(578, 'kpi155', 'Simulation Exercises', 'obj200', 'measure', 'no', 3000, ''),
(579, 'obj201', 'Strengthen Employee Morale', 'ind3', 'objective', 'no', 3000, ''),
(580, 'kpi156', 'Implemented Recommendations', 'obj201', 'measure', 'no', 3000, ''),
(581, 'obj202', 'Institutionalize Performance Management and Staff Productivity', 'ind3', 'objective', 'no', 3000, ''),
(582, 'kpi157', 'Productivity Index Improved', 'obj202', 'measure', 'no', 3000, ''),
(583, 'obj203', 'Build a vibrant and cohesive organizational culture', 'ind3', 'objective', 'no', 3000, ''),
(584, 'kpi158', 'Culture Entropy', 'obj203', 'measure', 'no', 3000, ''),
(585, 'kpi159', 'Recommendations Implemented', 'obj203', 'measure', 'no', 3000, '');

-- --------------------------------------------------------

--
-- Table structure for table `uc_configuration`
--

CREATE TABLE `uc_configuration` (
  `id` int NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `value` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `uc_configuration`
--

INSERT INTO `uc_configuration` (`id`, `name`, `value`) VALUES
(1, 'website_name', 'KDIC'),
(2, 'website_url', 'https://kdic.accent-analytics.com/analytics/'),
(3, 'email', 'lkyonze@gmail.com'),
(4, 'activation', 'true'),
(5, 'resend_activation_threshold', '3'),
(6, 'language', 'models/languages/en.php'),
(7, 'template', 'models/site-templates/default.css');

-- --------------------------------------------------------

--
-- Table structure for table `uc_pages`
--

CREATE TABLE `uc_pages` (
  `id` int NOT NULL,
  `page` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `private` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `uc_pages`
--

INSERT INTO `uc_pages` (`id`, `page`, `private`) VALUES
(1, 'account.php', 0),
(2, 'activate-account.php', 0),
(3, 'admin_configuration.php', 0),
(4, 'admin_page.php', 0),
(5, 'admin_pages.php', 1),
(6, 'admin_permission.php', 1),
(7, 'admin_permissions.php', 1),
(8, 'admin_user.php', 1),
(9, 'admin_users.php', 1),
(10, 'forgot-password.php', 0),
(11, 'index.php', 0),
(12, 'left-nav.php', 0),
(14, 'logout.php', 0),
(15, 'register.php', 0),
(16, 'resend-activation.php', 0),
(17, 'user_settings.php', 1),
(18, 'XmR_singleData.php', 0),
(19, 'bpa.php', 1),
(20, 'calendar.php', 0),
(21, 'config.php', 0),
(23, 'delete-tree.php', 0),
(27, 'edit-tree.php', 0),
(29, 'functions.php', 0),
(30, 'get-XmR-data.php', 0),
(31, 'get-content.php', 0),
(32, 'get-dashboard-list.php', 0),
(33, 'get-dashboard.php', 0),
(34, 'get-data-change.php', 0),
(36, 'get-initiative-list.php', 0),
(37, 'get-initiative.php', 0),
(38, 'get-kpi-gauge.php', 0),
(39, 'get-kpi-scores.php', 0),
(40, 'get-kpi-update.php', 0),
(41, 'get-obj-gauge.php', 0),
(42, 'get-org-content.php', 0),
(43, 'get-org-gauge.php', 0),
(44, 'get-org-scores.php', 0),
(45, 'get-persp-gauge.php', 0),
(46, 'get-persp-scores.php', 0),
(47, 'get-report-list.php', 0),
(48, 'get-report.php', 0),
(49, 'get-scorecard.php', 0),
(50, 'get-summary-scorecard.php', 0),
(51, 'get-test.php', 0),
(52, 'get-users.php', 0),
(53, 'grid.php', 0),
(56, 'initiative.php', 0),
(59, 'protected_page.php', 0),
(60, 'register_success.php', 0),
(61, 'report.php', 0),
(63, 'save-editor-content.php', 0),
(64, 'save-initiative.php', 0),
(65, 'save-kpi.php', 0),
(66, 'save-report.php', 0),
(67, 'save-svg.php', 0),
(68, 'save-tree.php', 0),
(72, 'change-pwd.php', 0),
(73, 'get-user.php', 0),
(74, 'get-permission.php', 0),
(85, 'save-commentary.php', 0),
(86, 'ddtKpi.php', 0),
(87, 'ddtObj.php', 0),
(88, 'get-commentary.php', 0),
(89, 'get-kpi-commentary.php', 0),
(90, 'save-kpi-commentary.php', 0),
(91, 'get-bookmarks.php', 0),
(92, 'get-calendar-items.php', 0),
(93, 'get-pdp.php', 0),
(94, 'get_admin_user_permissions.php', 0),
(95, 'get_admin_users.php', 0),
(96, 'indDashboard.php', 0),
(98, 'myDataEntry.php', 0),
(100, 'save-bookmark.php', 0),
(101, 'save-charts.php', 0),
(102, 'save-individual.php', 0),
(103, 'save-pdp.php', 0),
(106, 'ddtKpiDesign.php', 0),
(107, 'delete-data-item.php', 0),
(108, 'delete-goal.php', 0),
(109, 'delete-process.php', 0),
(110, 'delete-unit.php', 0),
(111, 'get-data-items.php', 0),
(112, 'get-db-color.php', 0),
(113, 'get-dnd.php', 0),
(114, 'get-goals.php', 0),
(116, 'get-kpi-design.php', 0),
(117, 'get-obj-scores.php', 0),
(119, 'get-organizations.php', 0),
(120, 'get-perspectives.php', 0),
(121, 'get-processes.php', 0),
(122, 'get-work-units.php', 0),
(123, 'initial_setup.php', 0),
(124, 'save-data-item.php', 0),
(125, 'save-goal.php', 0),
(127, 'save-process.php', 0),
(128, 'save-unit.php', 0),
(130, 'viewer.php', 0),
(133, 'get-measures.php', 0),
(136, 'pdu-get-components.php', 0),
(137, 'pdu-get-counties.php', 0),
(138, 'pdu-get-directive.php', 0),
(139, 'pdu-get-directives-summary.php', 0),
(140, 'pdu-get-directives.php', 0),
(141, 'pdu-get-media-days.php', 0),
(142, 'pdu-get-media-db.php', 0),
(143, 'pdu-get-media-summary.php', 0),
(144, 'pdu-get-media.php', 0),
(145, 'pdu-get-positive-mentions.php', 0),
(146, 'pdu-get-positive-projects.php', 0),
(147, 'pdu-get-project-mentions.php', 0),
(148, 'pdu-get-projectStatus.php', 0),
(149, 'pdu-get-publications.php', 0),
(150, 'pdu-get-users.php', 0),
(151, 'pdu-get-writers.php', 0),
(152, 'pdu-save-component.php', 0),
(153, 'pdu-save-directive-comments.php', 0),
(154, 'pdu-save-editor.php', 0),
(156, 'save-pdu-initiative.php', 0),
(157, 'save-pdu-kpi.php', 0),
(161, 'db_functions.php', 0),
(162, 'pdu-delete-commentary.php', 0),
(163, 'pdu-get-accident-age.php', 0),
(164, 'pdu-get-accident-roads.php', 0),
(165, 'pdu-get-accident-victim.php', 0),
(166, 'pdu-get-accidents.php', 0),
(167, 'pdu-get-commentary.php', 0),
(168, 'pdu-get-corruption-duration.php', 0),
(169, 'pdu-get-corruption-status.php', 0),
(170, 'pdu-get-corruption-trend.php', 0),
(171, 'pdu-get-corruption.php', 0),
(172, 'pdu-get-crime-county.php', 0),
(173, 'pdu-get-crime-trend.php', 0),
(174, 'pdu-get-crime-type.php', 0),
(175, 'pdu-get-distribution-counties.php', 0),
(176, 'pdu-get-issued-titles.php', 0),
(177, 'pdu-get-land-registries.php', 0),
(178, 'pdu-get-media-code.php', 0),
(179, 'pdu-get-ministry.php', 0),
(180, 'pdu-get-mw.php', 0),
(181, 'pdu-get-oldest-directives.php', 0),
(182, 'pdu-get-positive-directives.php', 0),
(183, 'pdu-get-power-counties.php', 0),
(184, 'pdu-get-project.php', 0),
(185, 'pdu-get-projects-summary.php', 0),
(186, 'pdu-get-projects.php', 0),
(187, 'pdu-get-roads-county.php', 0),
(188, 'pdu-get-roads-county1.php', 0),
(189, 'pdu-get-roads-details.php', 0),
(190, 'pdu-get-score-directives.php', 0),
(191, 'pdu-get-titles-county.php', 0),
(192, 'pdu-get-training-counties.php', 0),
(193, 'pdu-save-directive.php', 0),
(194, 'pdu-save-projecct.php', 0),
(195, 'pdu-update-commentary.php', 0),
(196, 'pdu_5000mw.php', 0),
(197, 'pdu_db_5000.php', 0),
(198, 'pdu_db_accidents.php', 0),
(199, 'pdu_db_accidents_map.php', 0),
(200, 'pdu_db_corruption.php', 0),
(201, 'pdu_db_corruption_eacc.php', 0),
(202, 'pdu_db_crime.php', 0),
(203, 'pdu_db_crime_map.php', 0),
(204, 'pdu_db_cs_score.php', 0),
(205, 'pdu_db_directives.php', 0),
(206, 'pdu_db_directives_ind.php', 0),
(207, 'pdu_db_home.php', 1),
(208, 'pdu_db_konza.php', 0),
(209, 'pdu_db_lapsset_map.php', 0),
(210, 'pdu_db_laptop.php', 0),
(211, 'pdu_db_managed_healthcare.php', 0),
(212, 'pdu_db_media.php', 0),
(213, 'pdu_db_media_ind.php', 0),
(214, 'pdu_db_projects.php', 0),
(215, 'pdu_db_projects_ind.php', 0),
(216, 'pdu_db_roads.php', 0),
(217, 'pdu_db_sgr.php', 0),
(218, 'pdu_db_titles.php', 0),
(219, 'pdu_db_titles_old.php', 0),
(220, 'pdu_sgr.php', 0),
(221, 'pdu_user_settings.php', 0),
(224, 'pdu-get-code-stories.php', 0),
(225, 'pdu-get-houses-process.php', 0),
(226, 'pdu-get-houses-stages.php', 0),
(227, 'pdu-get-houses.php', 0),
(228, 'pdu-get-ifmis-culprits.php', 0),
(229, 'pdu-get-ifmis-pos.php', 0),
(230, 'pdu-get-ifmis-procure-manual.php', 0),
(231, 'pdu-get-ifmis-procure.php', 0),
(232, 'pdu-get-ifmis-salaries.php', 0),
(233, 'pdu-get-ifmis-transfers.php', 0),
(234, 'pdu-get-ifmis.php', 0),
(235, 'pdu-get-tvets-county.php', 0),
(236, 'pdu-get-tvets-list.php', 0),
(237, 'pdu-get-tvets-stage.php', 0),
(238, 'pdu-get-world-bank.php', 0),
(239, 'pdu-publication-codes.php', 0),
(240, 'pdu_db_housing.php', 0),
(241, 'pdu_db_ifmis.php', 0),
(242, 'pdu_db_ifmis_culprits.php', 0),
(243, 'pdu_db_ifmis_master.php', 0),
(244, 'pdu_db_tvet.php', 0),
(245, 'add-spreadsheet-row.php', 0),
(246, 'delete-spreadsheet-format.php', 0),
(247, 'delete-spreadsheet-row.php', 0),
(248, 'get-delivery-book.php', 0),
(250, 'get-spreadsheet-audit.php', 0),
(251, 'get-spreadsheet-format.php', 0),
(252, 'pdu-delete-directive.php', 0),
(253, 'pdu_delivery_book.php', 0),
(254, 'save-delivery-book.php', 0),
(255, 'save-spreadsheet-format.php', 0),
(258, 'admin_forgot_password.php', 0),
(259, 'admin_user_scores.php', 0),
(260, 'admin_users_list.php', 0),
(261, 'advocacyKEPSA.php', 0),
(262, 'advocacy_trend.php', 0),
(263, 'calculated-kpi2.php', 0),
(264, 'cba_balanceSheet.php', 0),
(265, 'cba_map.php', 0),
(266, 'cba_pnl.php', 0),
(267, 'cba_pnl_waterfall.php', 0),
(268, 'change_password_form.php', 0),
(269, 'config_pdo.php', 0),
(270, 'connect-run-sql.php', 0),
(271, 'connect.php', 0),
(272, 'countrySummary.php', 0),
(273, 'csvToGrid.php', 0),
(274, 'csvToGrid2.php', 0),
(275, 'db-get-expenses.php', 0),
(276, 'db-get-income.php', 0),
(277, 'db_executive_overview.php', 0),
(278, 'db_finance.php', 0),
(279, 'db_org_structure_longhorn.php', 0),
(280, 'db_sales.php', 0),
(281, 'delete-advocacy-category.php', 0),
(282, 'delete-bookmark.php', 0),
(283, 'delete-conversation.php', 0),
(284, 'delete-initiative.php', 0),
(285, 'delete-report.php', 0),
(286, 'delete-team.php', 0),
(287, 'functionsAdvocacy.php', 0),
(288, 'get-advocacy-list.php', 0),
(289, 'get-advocacy-summary.php', 0),
(290, 'get-advocacy.php', 0),
(291, 'get-balance-sheet-trend.php', 0),
(292, 'get-balance-sheet.php', 0),
(293, 'get-categories.php', 0),
(294, 'get-commentary (2).php', 0),
(295, 'get-conversation.php', 0),
(296, 'get-departments-select.php', 0),
(297, 'get-departments.php', 0),
(298, 'get-initiative-elements.php', 0),
(299, 'get-initiatives-for-select.php', 0),
(300, 'get-kpi-audit.php', 0),
(301, 'get-kpi-details.php', 0),
(302, 'get-kpi-summary.php', 0),
(303, 'get-kpi-trend-two-months.php', 0),
(304, 'get-kpi-trend.php', 0),
(305, 'get-map-colors.php', 0),
(306, 'get-ministries.php', 0),
(307, 'get-next-id.php', 0),
(308, 'get-objective-list.php', 0),
(309, 'get-org-colors.php', 0),
(310, 'get-org-structure-colors.php', 0),
(311, 'get-previous-id.php', 0),
(312, 'get-report-elements.php', 0),
(313, 'get-trend.php', 0),
(314, 'get_db_dashboard.php', 0),
(315, 'get_db_links.php', 0),
(316, 'get_db_objectives.php', 0),
(317, 'get_db_objectives_to.php', 0),
(318, 'get_links.php', 0),
(319, 'labChart.php', 0),
(320, 'labDate.php', 0),
(321, 'labDragNDrop.php', 0),
(322, 'mail-send.php', 0),
(323, 'org_structure.php', 0),
(324, 'org_structureCFO.php', 0),
(325, 'org_structure_kepsa.php', 0),
(326, 'org_structure_kepsa_vertical-old.php', 0),
(327, 'org_structure_kepsa_vertical.php', 0),
(328, 'pdu-change-selection.php', 0),
(329, 'pdu-get-author-mentions.php', 0),
(330, 'pdu-get-code-stories-he.php', 0),
(331, 'pdu-get-county-projects.php', 0),
(332, 'pdu-get-headline-publications.php', 0),
(333, 'pdu-get-headlines-code.php', 0),
(334, 'pdu-get-headlines-days.php', 0),
(335, 'pdu-get-headlines.php', 0),
(336, 'pdu-get-huduma-centers.php', 0),
(337, 'pdu-get-huduma-summary.php', 0),
(338, 'pdu-get-ifmis-accomodation.php', 0),
(339, 'pdu-get-ifmis-vendors.php', 0),
(340, 'pdu-get-ketraco-list.php', 0),
(341, 'pdu-get-maternity-compare.php', 0),
(342, 'pdu-get-maternity-county.php', 0),
(343, 'pdu-get-maternity-deliveries.php', 0),
(344, 'pdu-get-maternity-reimbursed.php', 0),
(345, 'pdu-get-maternity-totals.php', 0),
(346, 'pdu-get-media-code-he.php', 0),
(347, 'pdu-get-media-days-he.php', 0),
(348, 'pdu-get-media-db-he.php', 0),
(349, 'pdu-get-media-he.php', 0),
(350, 'pdu-get-media-summary-he.php', 0),
(351, 'pdu-get-mes.php', 0),
(352, 'pdu-get-ministry-department.php', 0),
(353, 'pdu-get-mw-stage.php', 0),
(354, 'pdu-get-parent-directive.php', 0),
(355, 'pdu-get-project-cost.php', 0),
(356, 'pdu-get-project-status.php', 0),
(357, 'pdu-get-publications-he.php', 0),
(358, 'pdu-get-roads-status.php', 0),
(359, 'pdu-get-social-inclusion.php', 0),
(360, 'pdu-get-tariffs.php', 0),
(361, 'pdu-get-titles-registries.php', 0),
(362, 'pdu-get-titles-totals.php', 0),
(363, 'pdu-get-transmission-stage.php', 0),
(364, 'pdu-get-user-access-delivery.php', 0),
(365, 'pdu-get-writers-he.php', 0),
(366, 'pdu-undelete-directive.php', 0),
(367, 'pdu_db_5000_11_11_2016.php', 0),
(368, 'pdu_db_5000_2.php', 0),
(369, 'pdu_db_5000_last_mile.php', 0),
(370, 'pdu_db_5000_original.php', 0),
(371, 'pdu_db_counties.php', 0),
(372, 'pdu_db_fertiliser.php', 0),
(373, 'pdu_db_galana.php', 0),
(374, 'pdu_db_headlines.php', 0),
(375, 'pdu_db_huduma.php', 0),
(376, 'pdu_db_ifmis_vendors.php', 0),
(377, 'pdu_db_lapsset_map copy.php', 0),
(378, 'pdu_db_lastmile.php', 0),
(379, 'pdu_db_livestock.php', 0),
(380, 'pdu_db_managed_healthcare2.php', 0),
(381, 'pdu_db_maternity.php', 0),
(382, 'pdu_db_media_he.php', 0),
(383, 'pdu_db_mw.php', 0),
(384, 'pdu_db_sgr_park.php', 0),
(385, 'pdu_db_social_inclusion.php', 0),
(386, 'pnl_waterfall_longhorn.php', 0),
(387, 'reportDepartments.php', 0),
(388, 'save-advocacy-category.php', 0),
(389, 'save-advocacy.php', 0),
(390, 'save-kpi-dbXmR.php', 0),
(391, 'save-linked-tree.php', 0),
(392, 'save-objective-team.php', 0),
(393, 'save-pictures.php', 0),
(394, 'sendMail.php', 0),
(395, 'stratMap.php', 0),
(396, 'stratMap2.php', 0),
(397, 'stratMap3.php', 0),
(398, 'stratMap4.php', 0),
(399, 'stratMapAuto.php', 0),
(400, 'stratMapCBA.php', 0),
(401, 'test.php', 0),
(402, 'tree.php', 0),
(403, 'update-advocacy-category.php', 0),
(404, 'update-conversation.php', 0),
(405, 'update-dashboard.php', 0),
(406, 'update-tree-pareQnt.php', 0),
(407, 'update-tree-parent.php', 0),
(408, 'xmr.php', 0);

-- --------------------------------------------------------

--
-- Table structure for table `uc_permissions`
--

CREATE TABLE `uc_permissions` (
  `id` int NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `orgId` varchar(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `status` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `callFunction` varchar(90) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `url` varchar(90) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `home` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `icon` varchar(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `uc_permissions`
--

INSERT INTO `uc_permissions` (`id`, `name`, `orgId`, `status`, `callFunction`, `url`, `home`, `icon`) VALUES
(1, 'Viewer', NULL, 'Active', NULL, NULL, 'No', ''),
(2, 'Administrator', NULL, 'Active', NULL, NULL, 'No', ''),
(3, 'Application', NULL, 'Active', NULL, NULL, 'No', ''),
(4, 'Updater', NULL, 'Active', NULL, NULL, 'No', ''),
(5, 'Communication', NULL, 'Inactive', NULL, NULL, 'No', ''),
(6, 'Cabinet', NULL, 'Inactive', NULL, NULL, 'No', ''),
(7, 'Presidency', NULL, 'Inactive', NULL, NULL, 'No', ''),
(8, 'Directives', NULL, 'Inactive', NULL, NULL, 'No', ''),
(9, 'Data Entry Matrix', NULL, 'Inactive', NULL, NULL, 'No', ''),
(10, 'Root', 'root', 'Active', NULL, NULL, 'No', ''),
(11, 'KDIC', 'org1', 'Active', NULL, NULL, 'No', ''),
(22, 'Home Page', '', 'Active', 'homePage()', 'dashboards/strategy-house.php', 'Yes', ''),
(23, 'Department Staff Scores', 'func15', 'Active', 'departmentStaffScores()', NULL, 'No', '<i class=\'bi bi-trophy text-info\'></i>'),
(24, 'Individual Staff Scores', 'func16', 'Active', 'staffScores()', '', 'No', '<i class=\'bi bi-person-square text-warning\'></i>'),
(25, 'Accent Usage', 'func17', 'Active', 'accentUsage()', '', 'No', '<i class=\'bi bi-speedometer2 text-danger\'></i>'),
(31, 'Admin', 'func6', 'Inactive', NULL, NULL, 'No', ''),
(100, 'Legal Services Directorate', 'org2', 'Active', '', '', 'No', NULL),
(540, 'Eunice Kitche', 'ind8', 'Active', NULL, NULL, 'No', NULL),
(542, 'Strategy Planning & Research', 'org4', 'Active', '', '', 'No', NULL),
(543, 'Internal Audit', 'org5', 'Active', '', '', 'No', NULL),
(544, 'Lawrence Shoona', 'ind7', 'Active', NULL, NULL, 'No', NULL),
(545, 'HR & Admin', 'org6', 'Active', '', '', 'No', NULL),
(546, 'Peter Ibrae', 'ind4', 'Active', NULL, NULL, 'No', NULL),
(547, 'Deposit Insurance & Bank Surveillance', 'org7', 'Active', '', '', 'No', NULL),
(548, 'Paul Manga', 'ind5', 'Active', NULL, NULL, 'No', NULL),
(549, 'Mary Kiragu', 'ind6', 'Active', NULL, NULL, 'No', NULL),
(550, 'CEO Office', 'org8', 'Active', '', '', 'No', NULL),
(551, 'Crispus Yankem', 'ind9', 'Active', NULL, NULL, 'No', NULL),
(552, 'Hellen Chepkwony', 'ind3', 'Active', NULL, NULL, 'No', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `uc_permission_page_matches`
--

CREATE TABLE `uc_permission_page_matches` (
  `id` int NOT NULL,
  `permission_id` int NOT NULL,
  `page_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `uc_permission_page_matches`
--

INSERT INTO `uc_permission_page_matches` (`id`, `permission_id`, `page_id`) VALUES
(1, 1, 1),
(2, 1, 14),
(3, 1, 17),
(4, 2, 1),
(5, 2, 3),
(6, 2, 4),
(7, 2, 5),
(8, 2, 6),
(9, 2, 7),
(10, 2, 8),
(11, 2, 9),
(12, 2, 14),
(13, 2, 17),
(23, 1, 19),
(24, 2, 19),
(25, 7, 19),
(26, 6, 19),
(27, 7, 207),
(28, 6, 207),
(29, 8, 19),
(30, 9, 19),
(31, 3, 19);

-- --------------------------------------------------------

--
-- Table structure for table `uc_users`
--

CREATE TABLE `uc_users` (
  `id` int NOT NULL,
  `user_id` varchar(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `user_name` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `display_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `reportsTo` varchar(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `weight` int DEFAULT NULL,
  `photo` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `password` varchar(225) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `activation_token` varchar(225) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `last_activation_request` int DEFAULT NULL,
  `lost_password_request` tinyint(1) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `title` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `department` varchar(900) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `sign_up_stamp` int DEFAULT NULL,
  `last_sign_in_stamp` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `uc_users`
--

INSERT INTO `uc_users` (`id`, `user_id`, `user_name`, `display_name`, `reportsTo`, `weight`, `photo`, `password`, `email`, `activation_token`, `last_activation_request`, `lost_password_request`, `active`, `title`, `department`, `sign_up_stamp`, `last_sign_in_stamp`) VALUES
(1, 'ind1', 'admin', 'Administrator', NULL, NULL, 'images/profilePics/adminProfile.png', 'd2979b7003f56f457a0d0412ecee7639f08accdbf704c85e051fb1683bff56a0c', 'lkyonze@gmail.com', 'ded5bc0361090fbf5fe50b5276f70f22', 1584885154, 0, 1, 'Accent Admin', 'org0', 1584885154, 1752216764),
(2, 'ind2', 'collins.ojenge', 'Collins Ojenge', 'ind1', NULL, NULL, '86ffcf44d5e80a5de974172db618a17afedd3eb213b6f8194eeb9ab8059222b82', 'collinsojenge@gmail.com', 'bfd387a5db3337bbce350420e83a6e51', 1750173314, 0, 1, 'Test User', 'org0', 1750173314, 1750173620),
(3, 'ind3', 'hellen.chepkwony', 'Hellen Chepkwony', 'ind1', NULL, 'images/profilePics/hellenChepkwony.jpg', 'de8556fdeba19f9a7e545013c0ed343e6e4ff66708769b86bbc3e6aa487889310', 'hellen.chepkwony@kdic.go.ke', '179639ba2d206dbb7a7f146c706693e2', 1750945122, 0, 1, 'CEO', 'org1', 1750945122, 1751025033),
(4, 'ind4', 'peter.ibrae', 'Peter Ibrae', 'ind3', NULL, NULL, '38e4663dd94df73b9f114e654cf7b55a51be63ca47db5e41aad1dbd47631da6f7', 'peter.ibrae@kdic.go.ke', '8366f7330bb289474e35b1f75254ab5e', 1750946897, 0, 0, 'Deputy Director HR & Admin', 'org6', 1750946897, 0),
(5, 'ind5', 'paul.manga', 'Paul Manga', 'ind3', NULL, 'images/profilePics/paulManga.jpg', '210834fd6f1ef9b291a042d07af0edbeb3c0f55a93b119fdb20644b33d438fd69', 'paul.manga@kdic.go.ke', '02ef5815238a8f55de48895e57492d2c', 1750947032, 0, 0, 'Director, DI & BS', 'org7', 1750947032, 0),
(6, 'ind6', 'mary.kiragu', 'Mary Kiragu', 'ind3', NULL, 'images/profilePics/maryKiragu.jpg', '7bd5023971c7be225b06c28a08887f12f3343bd6629618ed164ccfe55e3ffc2bb', 'mary.kiragu@kdic.go.ke', 'e2cf2c2ceb65685592681910395da70b', 1750947360, 0, 0, 'DIrector, Internal Audit', 'org5', 1750947360, 0),
(7, 'ind7', 'lawrence.shoona', 'Lawrence Shoona', 'ind3', NULL, 'images/profilePics/shoona.png', '09f4aee4435860484065276ea5635f2fc54840da2197002e57c6e2166e39f2c35', 'shoonal@kdic.go.ke', 'a152f9508538933e9540221c13a81ae9', 1750947447, 0, 0, 'Deputy Director, Strategy & Planning', 'org5', 1750947447, 0),
(8, 'ind8', 'eunice.kitche', 'Eunice Kitche', 'ind3', NULL, 'images/profilePics/EuniceKitche.jpg', '73cb3e121cc0e052ccae6597e271170f82355f9d2d7434a6839272a269119a319', 'eunice.kitche@kdic.go.ke', 'e930154a3d9fea3d48d2f867ce258fc1', 1750947811, 0, 0, 'Director, Legal', 'org2', 1750947811, 0),
(9, 'ind9', 'crispus.yankem', 'Crispus Yankem', 'ind3', NULL, NULL, 'e67e4c642dd52fda2b7887afc22fac6dfff50a6b1245744ee3a4349fa621b2333', 'crispus.yankem@kdic.go.ke', '342467eccd6484dc9c84ff9d10d50754', 1751021078, 0, 0, 'Assistant Director', 'org8', 1751021078, 0);

-- --------------------------------------------------------

--
-- Table structure for table `uc_user_logs`
--

CREATE TABLE `uc_user_logs` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `last_login_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uc_user_permission_matches`
--

CREATE TABLE `uc_user_permission_matches` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `permission_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `uc_user_permission_matches`
--

INSERT INTO `uc_user_permission_matches` (`id`, `user_id`, `permission_id`) VALUES
(1, 1, 2),
(2, 1, 10),
(3, 1, 11),
(4, 1, 22),
(5, 1, 100),
(6, 1, 540),
(7, 1, 542),
(8, 1, 543),
(9, 1, 544),
(10, 1, 541),
(11, 1, 545),
(12, 1, 546),
(13, 1, 547),
(14, 1, 548),
(15, 1, 549),
(16, 1, 551),
(17, 1, 550),
(18, 1, 552),
(19, 1, 23),
(20, 1, 25),
(21, 1, 24);

-- --------------------------------------------------------

--
-- Table structure for table `user_backup`
--

CREATE TABLE `user_backup` (
  `id` int NOT NULL,
  `username` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `password` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `name` varchar(90) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_backup`
--

INSERT INTO `user_backup` (`id`, `username`, `password`, `name`) VALUES
(424, 'hellen.chepkwony', '12345678', 'Hellen Chepkwony'),
(425, 'peter.ibrae', '12345678', 'Peter Ibrae'),
(426, 'paul.manga', '12345678', 'Paul Manga'),
(427, 'mary.kiragu', '12345678', 'Mary Kiragu'),
(428, 'lawrence.shoona', '12345678', 'Lawrence Shoona'),
(429, 'eunice.kitche', '12345678', 'Eunice Kitche'),
(430, 'crispus.yankem', '12345678', 'Crispus Yankem');

-- --------------------------------------------------------

--
-- Table structure for table `user_notification_preferences`
--

CREATE TABLE `user_notification_preferences` (
  `id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'Reference to user',
  `notification_type` varchar(50) NOT NULL COMMENT 'Type of notification',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Whether user wants this type',
  `frequency_override` varchar(20) DEFAULT NULL COMMENT 'User-specific frequency override',
  `created_date` datetime NOT NULL COMMENT 'When preference was created',
  `modified_date` datetime NOT NULL COMMENT 'When preference was last modified'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='User notification preferences';

--
-- Dumping data for table `user_notification_preferences`
--

INSERT INTO `user_notification_preferences` (`id`, `user_id`, `notification_type`, `is_enabled`, `frequency_override`, `created_date`, `modified_date`) VALUES
(1, 1, 'measure_reminder', 1, NULL, '2025-06-17 16:45:48', '2025-06-17 16:47:01'),
(2, 1, 'initiative_update', 1, NULL, '2025-06-17 16:45:48', '2025-06-17 16:47:01'),
(3, 1, 'performance_summary', 1, NULL, '2025-06-17 16:45:48', '2025-06-17 16:47:01'),
(4, 1, 'system_announcement', 1, NULL, '2025-06-17 16:45:48', '2025-06-17 16:47:01'),
(5, 1, 'weekly_digest', 1, NULL, '2025-06-17 16:45:48', '2025-06-17 16:47:01'),
(6, 1, 'monthly_report', 1, NULL, '2025-06-17 16:45:48', '2025-06-17 16:47:01'),
(7, 1, 'deadline_alert', 1, NULL, '2025-06-17 16:45:48', '2025-06-17 16:47:01');

--
-- Triggers `user_notification_preferences`
--
DELIMITER $$
CREATE TRIGGER `tr_user_notification_preferences_update` BEFORE UPDATE ON `user_notification_preferences` FOR EACH ROW BEGIN
    SET NEW.modified_date = NOW();
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_notification_stats`
-- (See below for the actual view)
--
CREATE TABLE `v_notification_stats` (
`schedule_id` int
,`schedule_name` varchar(255)
,`email_type` varchar(50)
,`total_sent` bigint
,`successful_sent` bigint
,`failed_sent` bigint
,`last_sent` datetime
,`is_active` tinyint(1)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_user_notification_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_user_notification_summary` (
`user_id` int
,`display_name` varchar(50)
,`email` varchar(150)
,`total_notifications_received` bigint
,`notifications_last_30_days` bigint
,`last_notification_date` datetime
);

-- --------------------------------------------------------

--
-- Structure for view `v_notification_stats`
--
DROP TABLE IF EXISTS `v_notification_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`accenta0_NGIwY`@`localhost` SQL SECURITY DEFINER VIEW `v_notification_stats`  AS SELECT `ns`.`id` AS `schedule_id`, `ns`.`name` AS `schedule_name`, `nt`.`email_type` AS `email_type`, count(`nl`.`id`) AS `total_sent`, count((case when (`nl`.`status` = 'sent') then 1 end)) AS `successful_sent`, count((case when (`nl`.`status` = 'failed') then 1 end)) AS `failed_sent`, max(`nl`.`sent_date`) AS `last_sent`, `ns`.`is_active` AS `is_active` FROM ((`notification_schedules` `ns` left join `notification_templates` `nt` on((`ns`.`template_id` = `nt`.`id`))) left join `notification_logs` `nl` on((`ns`.`id` = `nl`.`schedule_id`))) GROUP BY `ns`.`id`, `ns`.`name`, `nt`.`email_type`, `ns`.`is_active` ;

-- --------------------------------------------------------

--
-- Structure for view `v_user_notification_summary`
--
DROP TABLE IF EXISTS `v_user_notification_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`accenta0_NGIwY`@`localhost` SQL SECURITY DEFINER VIEW `v_user_notification_summary`  AS SELECT `u`.`id` AS `user_id`, `u`.`display_name` AS `display_name`, `u`.`email` AS `email`, count(`nl`.`id`) AS `total_notifications_received`, count((case when (`nl`.`sent_date` >= (now() - interval 30 day)) then 1 end)) AS `notifications_last_30_days`, max(`nl`.`sent_date`) AS `last_notification_date` FROM (`uc_users` `u` left join `notification_logs` `nl` on((`u`.`id` = `nl`.`user_id`))) WHERE (`u`.`active` = 1) GROUP BY `u`.`id`, `u`.`display_name`, `u`.`email` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audittrail`
--
ALTER TABLE `audittrail`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookmark`
--
ALTER TABLE `bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cc_picture`
--
ALTER TABLE `cc_picture`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cc_remarks`
--
ALTER TABLE `cc_remarks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cc_remarks_public`
--
ALTER TABLE `cc_remarks_public`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `commentary`
--
ALTER TABLE `commentary`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `commentarydataitem`
--
ALTER TABLE `commentarydataitem`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `commentarygoal`
--
ALTER TABLE `commentarygoal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `commentaryprocess`
--
ALTER TABLE `commentaryprocess`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `commentaryworkunit`
--
ALTER TABLE `commentaryworkunit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversation`
--
ALTER TABLE `conversation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `core_value_attribute_score`
--
ALTER TABLE `core_value_attribute_score`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `counties`
--
ALTER TABLE `counties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `county`
--
ALTER TABLE `county`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `formats`
--
ALTER TABLE `formats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `import_data`
--
ALTER TABLE `import_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `import_links`
--
ALTER TABLE `import_links`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `import_map`
--
ALTER TABLE `import_map`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `import_months`
--
ALTER TABLE `import_months`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `individual`
--
ALTER TABLE `individual`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `initiative`
--
ALTER TABLE `initiative`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `initiativeimpact`
--
ALTER TABLE `initiativeimpact`
  ADD PRIMARY KEY (`id_impact`);

--
-- Indexes for table `initiativeteam`
--
ALTER TABLE `initiativeteam`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `initiative_audit`
--
ALTER TABLE `initiative_audit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `initiative_evidence`
--
ALTER TABLE `initiative_evidence`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `initiative_issue`
--
ALTER TABLE `initiative_issue`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `initiative_status`
--
ALTER TABLE `initiative_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `investments`
--
ALTER TABLE `investments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kpicommentary`
--
ALTER TABLE `kpicommentary`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kpidesign`
--
ALTER TABLE `kpidesign`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kpi_audit`
--
ALTER TABLE `kpi_audit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mail`
--
ALTER TABLE `mail`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `measure`
--
ALTER TABLE `measure`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idMeasure_UNIQUE` (`id`);

--
-- Indexes for table `measuredays`
--
ALTER TABLE `measuredays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `measurehalfyear`
--
ALTER TABLE `measurehalfyear`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `measurelinks`
--
ALTER TABLE `measurelinks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `measuremonths`
--
ALTER TABLE `measuremonths`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `measurequarters`
--
ALTER TABLE `measurequarters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `measureweeks`
--
ALTER TABLE `measureweeks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `measureyears`
--
ALTER TABLE `measureyears`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `note`
--
ALTER TABLE `note`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_execution_log`
--
ALTER TABLE `notification_execution_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_id` (`schedule_id`),
  ADD KEY `idx_executed_at` (`executed_at`),
  ADD KEY `idx_execution_log_date_schedule` (`executed_at`,`schedule_id`);

--
-- Indexes for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_id` (`schedule_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_sent_date` (`sent_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_logs_user_date` (`user_id`,`sent_date`);

--
-- Indexes for table `notification_queue`
--
ALTER TABLE `notification_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_id` (`schedule_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_scheduled_for` (`scheduled_for`),
  ADD KEY `idx_queue_status_priority` (`status`,`priority`,`scheduled_for`);

--
-- Indexes for table `notification_schedules`
--
ALTER TABLE `notification_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_template_id` (`template_id`),
  ADD KEY `idx_frequency` (`frequency`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_last_executed` (`last_executed`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_schedules_active_frequency` (`is_active`,`frequency`,`start_date`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_type` (`email_type`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `objcommentary`
--
ALTER TABLE `objcommentary`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `objective`
--
ALTER TABLE `objective`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `objectiveteam`
--
ALTER TABLE `objectiveteam`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `objective_kra_map`
--
ALTER TABLE `objective_kra_map`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `organization`
--
ALTER TABLE `organization`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pdp`
--
ALTER TABLE `pdp`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `perspective`
--
ALTER TABLE `perspective`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `phpjobscheduler`
--
ALTER TABLE `phpjobscheduler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fire_time` (`fire_time`) USING BTREE;

--
-- Indexes for table `phpjobscheduler_logs`
--
ALTER TABLE `phpjobscheduler_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pictures`
--
ALTER TABLE `pictures`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `report_init`
--
ALTER TABLE `report_init`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `strategic_results`
--
ALTER TABLE `strategic_results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supervisor_score`
--
ALTER TABLE `supervisor_score`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tree`
--
ALTER TABLE `tree`
  ADD PRIMARY KEY (`idTree`);

--
-- Indexes for table `uc_configuration`
--
ALTER TABLE `uc_configuration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `uc_pages`
--
ALTER TABLE `uc_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `uc_permissions`
--
ALTER TABLE `uc_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `uc_permission_page_matches`
--
ALTER TABLE `uc_permission_page_matches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `uc_users`
--
ALTER TABLE `uc_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `uc_user_permission_matches`
--
ALTER TABLE `uc_user_permission_matches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_backup`
--
ALTER TABLE `user_backup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_notification_preferences`
--
ALTER TABLE `user_notification_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_type` (`user_id`,`notification_type`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_notification_type` (`notification_type`),
  ADD KEY `idx_is_enabled` (`is_enabled`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookmark`
--
ALTER TABLE `bookmark`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cc_picture`
--
ALTER TABLE `cc_picture`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `cc_remarks`
--
ALTER TABLE `cc_remarks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cc_remarks_public`
--
ALTER TABLE `cc_remarks_public`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `commentary`
--
ALTER TABLE `commentary`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commentarydataitem`
--
ALTER TABLE `commentarydataitem`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `commentarygoal`
--
ALTER TABLE `commentarygoal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commentaryprocess`
--
ALTER TABLE `commentaryprocess`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `commentaryworkunit`
--
ALTER TABLE `commentaryworkunit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `conversation`
--
ALTER TABLE `conversation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `core_value_attribute_score`
--
ALTER TABLE `core_value_attribute_score`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `formats`
--
ALTER TABLE `formats`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `import_data`
--
ALTER TABLE `import_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `import_links`
--
ALTER TABLE `import_links`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `import_map`
--
ALTER TABLE `import_map`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `import_months`
--
ALTER TABLE `import_months`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `initiativeimpact`
--
ALTER TABLE `initiativeimpact`
  MODIFY `id_impact` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `initiativeteam`
--
ALTER TABLE `initiativeteam`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `initiative_audit`
--
ALTER TABLE `initiative_audit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `initiative_evidence`
--
ALTER TABLE `initiative_evidence`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2907;

--
-- AUTO_INCREMENT for table `initiative_issue`
--
ALTER TABLE `initiative_issue`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `initiative_status`
--
ALTER TABLE `initiative_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `investments`
--
ALTER TABLE `investments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kpicommentary`
--
ALTER TABLE `kpicommentary`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kpidesign`
--
ALTER TABLE `kpidesign`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kpi_audit`
--
ALTER TABLE `kpi_audit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87073;

--
-- AUTO_INCREMENT for table `mail`
--
ALTER TABLE `mail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `measuredays`
--
ALTER TABLE `measuredays`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `measurehalfyear`
--
ALTER TABLE `measurehalfyear`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `measurelinks`
--
ALTER TABLE `measurelinks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `measuremonths`
--
ALTER TABLE `measuremonths`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `measurequarters`
--
ALTER TABLE `measurequarters`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `measureweeks`
--
ALTER TABLE `measureweeks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `measureyears`
--
ALTER TABLE `measureyears`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000;

--
-- AUTO_INCREMENT for table `note`
--
ALTER TABLE `note`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_execution_log`
--
ALTER TABLE `notification_execution_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_logs`
--
ALTER TABLE `notification_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_queue`
--
ALTER TABLE `notification_queue`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_schedules`
--
ALTER TABLE `notification_schedules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `objcommentary`
--
ALTER TABLE `objcommentary`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `objectiveteam`
--
ALTER TABLE `objectiveteam`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `objective_kra_map`
--
ALTER TABLE `objective_kra_map`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pdp`
--
ALTER TABLE `pdp`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `phpjobscheduler`
--
ALTER TABLE `phpjobscheduler`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `phpjobscheduler_logs`
--
ALTER TABLE `phpjobscheduler_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pictures`
--
ALTER TABLE `pictures`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `Id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `strategic_results`
--
ALTER TABLE `strategic_results`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `supervisor_score`
--
ALTER TABLE `supervisor_score`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tree`
--
ALTER TABLE `tree`
  MODIFY `idTree` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=586;

--
-- AUTO_INCREMENT for table `uc_configuration`
--
ALTER TABLE `uc_configuration`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `uc_pages`
--
ALTER TABLE `uc_pages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=409;

--
-- AUTO_INCREMENT for table `uc_permissions`
--
ALTER TABLE `uc_permissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=553;

--
-- AUTO_INCREMENT for table `uc_permission_page_matches`
--
ALTER TABLE `uc_permission_page_matches`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `uc_users`
--
ALTER TABLE `uc_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=431;

--
-- AUTO_INCREMENT for table `uc_user_permission_matches`
--
ALTER TABLE `uc_user_permission_matches`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_backup`
--
ALTER TABLE `user_backup`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=907;

--
-- AUTO_INCREMENT for table `user_notification_preferences`
--
ALTER TABLE `user_notification_preferences`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notification_execution_log`
--
ALTER TABLE `notification_execution_log`
  ADD CONSTRAINT `fk_execution_log_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `notification_schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD CONSTRAINT `fk_logs_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `notification_schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_queue`
--
ALTER TABLE `notification_queue`
  ADD CONSTRAINT `fk_queue_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `notification_schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_schedules`
--
ALTER TABLE `notification_schedules`
  ADD CONSTRAINT `fk_schedules_template` FOREIGN KEY (`template_id`) REFERENCES `notification_templates` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
