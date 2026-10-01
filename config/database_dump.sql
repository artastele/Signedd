-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: sped_lms
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_attempt_log`
--

DROP TABLE IF EXISTS `activity_attempt_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_attempt_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` int unsigned NOT NULL,
  `student_id` int unsigned NOT NULL,
  `question_index` tinyint unsigned NOT NULL,
  `selected_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correct_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `attempted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activity_student` (`activity_id`,`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_attempt_log`
--

LOCK TABLES `activity_attempt_log` WRITE;
/*!40000 ALTER TABLE `activity_attempt_log` DISABLE KEYS */;
INSERT INTO `activity_attempt_log` VALUES (37,2,4,0,'Good Morning (Magandang Umaga)','Good Morning (Magandang Umaga)',1,'2026-09-19 19:24:23'),(38,2,4,1,'Nanay (Mother)','Nanay (Mother)',1,'2026-09-19 19:24:23'),(39,2,4,2,'Thank You (Salamat)','Thank You (Salamat)',1,'2026-09-19 19:24:23'),(40,3,4,0,'School (Tunghaan / Paaralan)','School (Tunghaan / Paaralan)',1,'2026-09-19 19:24:23'),(41,3,4,1,'Teacher (Guro)','Teacher (Guro)',1,'2026-09-19 19:24:23'),(42,3,4,2,'Maghugas ng kamay nang malinis','Maghugas ng kamay nang malinis',1,'2026-09-19 19:24:23'),(43,4,5,0,'Good Morning (Magandang Umaga)','Good Morning (Magandang Umaga)',1,'2026-09-19 19:24:27'),(44,4,5,1,'Nanay (Mother)','Nanay (Mother)',1,'2026-09-19 19:24:27'),(45,4,5,2,'Thank You (Salamat)','Thank You (Salamat)',1,'2026-09-19 19:24:27'),(46,5,5,0,'School (Tunghaan / Paaralan)','School (Tunghaan / Paaralan)',1,'2026-09-19 19:24:27'),(47,5,5,1,'Teacher (Guro)','Teacher (Guro)',1,'2026-09-19 19:24:27'),(48,5,5,2,'Maghugas ng kamay nang malinis','Maghugas ng kamay nang malinis',1,'2026-09-19 19:24:27'),(49,6,6,0,'Good Morning (Magandang Umaga)','Good Morning (Magandang Umaga)',1,'2026-09-19 19:24:36'),(50,6,6,1,'Nanay (Mother)','Nanay (Mother)',0,'2026-09-19 19:24:36'),(51,6,6,2,'Thank You (Salamat)','Thank You (Salamat)',1,'2026-09-19 19:24:36'),(52,7,6,0,'School (Tunghaan / Paaralan)','School (Tunghaan / Paaralan)',1,'2026-09-19 19:24:36'),(53,7,6,1,'Teacher (Guro)','Teacher (Guro)',0,'2026-09-19 19:24:36'),(54,7,6,2,'Maghugas ng kamay nang malinis','Maghugas ng kamay nang malinis',1,'2026-09-19 19:24:36'),(55,8,7,0,'Good Morning (Magandang Umaga)','Good Morning (Magandang Umaga)',1,'2026-09-19 19:24:44'),(56,8,7,1,'Nanay (Mother)','Nanay (Mother)',1,'2026-09-19 19:24:44'),(57,8,7,2,'Thank You (Salamat)','Thank You (Salamat)',1,'2026-09-19 19:24:44'),(58,9,7,0,'School (Tunghaan / Paaralan)','School (Tunghaan / Paaralan)',1,'2026-09-19 19:24:44'),(59,9,7,1,'Teacher (Guro)','Teacher (Guro)',0,'2026-09-19 19:24:44'),(60,9,7,2,'Maghugas ng kamay nang malinis','Maghugas ng kamay nang malinis',1,'2026-09-19 19:24:44'),(61,10,8,0,'Good Morning (Magandang Umaga)','Good Morning (Magandang Umaga)',1,'2026-09-19 19:24:52'),(62,10,8,1,'Nanay (Mother)','Nanay (Mother)',1,'2026-09-19 19:24:52'),(63,10,8,2,'Thank You (Salamat)','Thank You (Salamat)',1,'2026-09-19 19:24:52'),(64,11,8,0,'School (Tunghaan / Paaralan)','School (Tunghaan / Paaralan)',1,'2026-09-19 19:24:52'),(65,11,8,1,'Teacher (Guro)','Teacher (Guro)',1,'2026-09-19 19:24:52'),(66,11,8,2,'Maghugas ng kamay nang malinis','Maghugas ng kamay nang malinis',1,'2026-09-19 19:24:52');
/*!40000 ALTER TABLE `activity_attempt_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_attempts`
--

DROP TABLE IF EXISTS `activity_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `activity_id` int NOT NULL,
  `student_id` int NOT NULL,
  `attempt_number` int DEFAULT '1',
  `answers` json NOT NULL,
  `score` int DEFAULT '0',
  `total_points` int DEFAULT '0',
  `percentage` decimal(5,2) DEFAULT '0.00',
  `time_spent_minutes` int DEFAULT '0',
  `completed` tinyint(1) DEFAULT '0',
  `started_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  `is_remedial_attempt` tinyint(1) NOT NULL DEFAULT '0',
  `needs_remediation` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_activity_id` (`activity_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_completed` (`completed`),
  CONSTRAINT `activity_attempts_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activity_templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_attempts_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_attempts`
--

LOCK TABLES `activity_attempts` WRITE;
/*!40000 ALTER TABLE `activity_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `affected_table` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `affected_record_id` int DEFAULT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (1,1,'meeting.list','iep_meetings',NULL,'Viewed meeting list','::1','2026-08-20 18:50:24'),(2,1,'availability.view','user_availability',NULL,'Viewed availability calendar','::1','2026-08-20 18:50:27'),(3,6,'verification.list','enrollment_submissions',NULL,'Viewed verification dashboard','::1','2026-08-20 18:52:31'),(4,6,'verification.list','enrollment_submissions',NULL,'Viewed verification dashboard','::1','2026-08-20 18:52:39'),(5,6,'verification.list','enrollment_submissions',NULL,'Viewed verification dashboard','::1','2026-08-20 18:52:42'),(6,6,'availability.view','user_availability',NULL,'Viewed availability calendar','::1','2026-08-20 18:52:44'),(7,6,'verification.list','enrollment_submissions',NULL,'Viewed verification dashboard','::1','2026-08-20 18:52:45'),(8,6,'verification.list','enrollment_submissions',NULL,'Viewed verification dashboard','::1','2026-08-20 18:52:47'),(9,6,'verification.list','enrollment_submissions',NULL,'Viewed verification dashboard','::1','2026-08-20 18:52:52'),(10,6,'verification.list','enrollment_submissions',NULL,'Viewed verification dashboard','::1','2026-08-20 18:53:03'),(11,6,'assessment.form','assessment_records',NULL,'Opened assessment form','::1','2026-08-20 18:53:07'),(12,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-08-20 18:53:12'),(13,6,'meeting.list','iep_meetings',NULL,'Viewed meeting list','::1','2026-08-20 18:53:44'),(14,6,'meeting.list','iep_meetings',NULL,'Viewed meeting list','::1','2026-08-20 18:58:04'),(15,6,'assessment.form','assessment_records',NULL,'Opened assessment form','::1','2026-08-20 19:08:17'),(16,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-08-20 19:08:21'),(17,6,'assessment.form','assessment_records',NULL,'Opened assessment form','::1','2026-08-20 19:08:27'),(18,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-08-20 19:08:28'),(19,6,'verification.list','enrollment_submissions',NULL,'Viewed verification dashboard','::1','2026-08-20 19:47:12'),(20,6,'availability.view','user_availability',NULL,'Viewed availability calendar','::1','2026-08-20 19:47:17'),(21,6,'verification.list','enrollment_submissions',NULL,'Viewed verification dashboard','::1','2026-08-20 19:47:22'),(22,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-08-20 19:47:40'),(23,6,'assessment.form','assessment_records',NULL,'Opened assessment form','::1','2026-08-20 20:28:15'),(24,6,'meeting.list','iep_meetings',NULL,'Viewed meeting list','::1','2026-08-20 20:28:20'),(25,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-08-20 20:28:30'),(26,6,'assessment.form','assessment_records',NULL,'Opened assessment form','::1','2026-08-20 20:28:32'),(27,6,'assessment.form','assessment_records',NULL,'Opened assessment form','::1','2026-08-20 21:03:44'),(28,6,'assessment.form','assessment_records',NULL,'Opened assessment form for student: 2','::1','2026-08-20 21:03:52'),(29,6,'assessment.submit','assessment_records',1,'Submitted Part I assessment for student: 2','::1','2026-08-20 21:04:00'),(30,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-08-20 21:04:00'),(31,6,'assessment.view','assessment_records',1,'Viewed assessment for review','::1','2026-08-20 21:04:03'),(32,6,'meeting.list','iep_meetings',NULL,'Viewed meeting list','::1','2026-08-20 21:04:16'),(33,6,'meeting.schedule_form','iep_meetings',NULL,'Opened meeting scheduler','::1','2026-08-20 21:04:18'),(34,6,'meeting.create','iep_meetings',1,'Created IEP meeting for student: 2 on 2026-08-22','::1','2026-08-20 21:04:33'),(35,6,'meeting.list','iep_meetings',NULL,'Viewed meeting list','::1','2026-08-20 21:04:33'),(36,6,'pdsp.view','pdsp_records',1,'Viewed PDSP form','::1','2026-08-20 21:04:36'),(37,6,'pdsp.upload_signed_document','pdsp_records',1,'Uploaded signed handwritten document','::1','2026-08-20 21:04:41'),(38,6,'pdsp.upload_signed_document','pdsp_records',1,'Uploaded signed handwritten document','::1','2026-08-20 21:04:48'),(39,6,'pdsp.submit','pdsp_records',1,'Submitted PDSP with signatories: ;hjhk, hkhjk. Meeting marked as completed.','::1','2026-08-20 21:04:52'),(40,6,'pdsp.view','pdsp_records',1,'Viewed PDSP form','::1','2026-08-20 21:04:53'),(41,6,'meeting.list','iep_meetings',NULL,'Viewed meeting list','::1','2026-08-20 21:04:56'),(42,6,'meeting.list','iep_meetings',NULL,'Viewed meeting list','::1','2026-08-20 21:04:57'),(43,6,'meeting.view','iep_meetings',1,'Viewed meeting details','::1','2026-08-20 21:04:59'),(44,6,'meeting.list','iep_meetings',NULL,'Viewed meeting list','::1','2026-08-20 21:05:04'),(45,6,'meeting.view','iep_meetings',1,'Viewed meeting details','::1','2026-08-20 21:05:05'),(46,6,'meeting.list','iep_meetings',NULL,'Viewed meeting list','::1','2026-08-20 21:05:19'),(47,6,'iep.created','iep_records',1,'Created IEP draft for student: 2','::1','2026-08-20 21:05:26'),(48,6,'availability.view','user_availability',NULL,'Viewed availability calendar','::1','2026-09-08 17:54:10'),(49,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-09-08 17:54:13'),(50,6,'assessment.form','assessment_records',NULL,'Opened assessment form','::1','2026-09-08 17:54:18'),(51,6,'assessment.form','assessment_records',NULL,'Opened assessment form','::1','2026-09-08 17:56:09'),(52,6,'assessment.form','assessment_records',NULL,'Opened assessment form','::1','2026-09-08 17:58:08'),(53,6,'assessment.form','assessment_records',NULL,'Opened assessment form','::1','2026-09-08 17:58:08'),(54,6,'assessment.form','assessment_records',NULL,'Opened assessment form for student: 4','::1','2026-09-08 17:58:11'),(55,6,'assessment.form','assessment_records',NULL,'Opened assessment form for student: 4','::1','2026-09-08 18:02:26'),(56,6,'assessment.submit','assessment_records',2,'Submitted Part I assessment for student: 4','::1','2026-09-08 18:02:34'),(57,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-09-08 18:02:34'),(58,6,'meeting.list','iep_meetings',NULL,'Viewed meeting list','::1','2026-09-08 18:02:41'),(59,6,'meeting.schedule_form','iep_meetings',NULL,'Opened meeting scheduler','::1','2026-09-08 18:02:44'),(60,6,'assessment.form','assessment_records',NULL,'Opened assessment form','::1','2026-09-08 18:05:06'),(61,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-09-08 18:05:09'),(62,6,'assessment.view','assessment_records',2,'Viewed assessment for review','::1','2026-09-08 18:05:11'),(63,6,'assessment.view','assessment_records',2,'Viewed assessment for review','::1','2026-09-08 18:07:34'),(64,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-09-08 18:07:40'),(65,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-09-08 18:10:05'),(66,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-09-08 18:11:15'),(67,6,'meeting.list','iep_meetings',NULL,'Viewed meeting list','::1','2026-09-08 18:14:19'),(68,6,'assessment.form','assessment_records',NULL,'Opened assessment form','::1','2026-09-08 18:14:20'),(69,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-09-08 18:14:22'),(70,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-09-08 18:14:23'),(71,6,'meeting.schedule_form','iep_meetings',NULL,'Opened meeting scheduler','::1','2026-09-08 18:14:24'),(72,6,'meeting.create','iep_meetings',2,'Created IEP meeting for student: 4 on 2026-09-10','::1','2026-09-08 18:14:57'),(73,6,'meeting.list','iep_meetings',NULL,'Viewed meeting list','::1','2026-09-08 18:14:57'),(74,6,'pdsp.view','pdsp_records',2,'Viewed PDSP form','::1','2026-09-08 18:15:04'),(75,6,'pdsp.upload_signed_document','pdsp_records',2,'Uploaded signed handwritten document','::1','2026-09-08 18:15:08'),(76,6,'pdsp.upload_signed_document','pdsp_records',2,'Uploaded signed handwritten document','::1','2026-09-08 18:15:28'),(77,6,'pdsp.submit','pdsp_records',2,'Submitted PDSP with signatories: ada, ads, asdad. Meeting marked as completed.','::1','2026-09-08 18:15:32'),(78,6,'iep.created','iep_records',2,'Created IEP draft for student: 4','::1','2026-09-08 18:15:36'),(79,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 18:17:43'),(80,6,'file_access','pdsp_document',2,'{\"action\":\"inline\",\"type\":\"pdsp_document\"}','::1','2026-09-08 18:18:07'),(81,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 18:18:14'),(82,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 18:18:19'),(83,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 18:19:19'),(84,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 18:21:10'),(85,6,'file_access','pdsp_document',2,'{\"action\":\"inline\",\"type\":\"pdsp_document\"}','::1','2026-09-08 18:21:50'),(86,6,'file_access','pdsp_document',2,'{\"action\":\"inline\",\"type\":\"pdsp_document\"}','::1','2026-09-08 18:21:53'),(87,6,'file_access','pdsp_document',2,'{\"action\":\"inline\",\"type\":\"pdsp_document\"}','::1','2026-09-08 18:21:54'),(88,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 18:22:12'),(89,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 18:22:14'),(90,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 18:22:15'),(91,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 18:23:18'),(92,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 18:23:24'),(93,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 18:26:19'),(94,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 18:29:27'),(95,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 18:51:56'),(96,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 18:59:40'),(97,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-08 19:00:24'),(98,6,'file_access','pdsp_document',2,'{\"action\":\"inline\",\"type\":\"pdsp_document\"}','::1','2026-09-08 19:00:29'),(99,6,'file_access','lesson_material',1,'{\"action\":\"inline\",\"type\":\"lesson_material\"}','::1','2026-09-08 19:23:42'),(100,6,'file_access','lesson_material',1,'{\"action\":\"inline\",\"type\":\"lesson_material\"}','::1','2026-09-08 19:29:59'),(101,6,'file_access','lesson_material',1,'{\"action\":\"inline\",\"type\":\"lesson_material\"}','::1','2026-09-08 19:51:08'),(102,6,'availability.view','user_availability',NULL,'Viewed availability calendar','::1','2026-09-09 22:22:11'),(103,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-09 22:37:08'),(104,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-11 16:57:06'),(105,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-11 16:57:26'),(106,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-11 16:57:34'),(107,10,'file_access','lesson_material',2,'{\"action\":\"inline\",\"type\":\"lesson_material\"}','::1','2026-09-11 16:59:52'),(108,6,'iep.viewed','iep_records',2,'Viewed IEP form','::1','2026-09-11 21:56:06'),(109,6,'iep.viewed','iep_records',3,'Viewed IEP form','::1','2026-09-14 18:01:21'),(110,6,'iep.viewed','iep_records',3,'Viewed IEP form','::1','2026-09-14 18:36:39'),(111,6,'iep.viewed','iep_records',3,'Viewed IEP form','::1','2026-09-19 17:00:05'),(112,6,'iep.viewed','iep_records',3,'Viewed IEP form','::1','2026-09-19 17:09:17'),(113,6,'iep.viewed','iep_records',3,'Viewed IEP form','::1','2026-09-19 17:09:28'),(114,6,'iep.viewed','iep_records',3,'Viewed IEP form','::1','2026-09-19 18:45:37'),(115,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-09-20 19:23:34'),(116,6,'meeting.schedule_form','iep_meetings',NULL,'Opened meeting scheduler','::1','2026-09-20 19:23:37'),(117,6,'iep.viewed','iep_records',6,'Viewed IEP form','::1','2026-09-20 19:25:16'),(118,6,'assessment.form','assessment_records',NULL,'Opened assessment form','::1','2026-09-20 19:25:23'),(119,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-09-20 19:25:24'),(120,6,'assessment.form','assessment_records',NULL,'Opened assessment form','::1','2026-09-20 19:25:25'),(121,6,'assessment.form','assessment_records',NULL,'Opened assessment form for student: 4','::1','2026-09-20 19:25:29'),(122,6,'assessment.submit','assessment_records',7,'Submitted Part I assessment for student: 4','::1','2026-09-20 19:26:17'),(123,6,'assessment.list','assessment_records',NULL,'Viewed assessment dashboard','::1','2026-09-20 19:26:17'),(124,21,'meeting.list','iep_meetings',NULL,'Viewed meeting list','::1','2026-09-21 18:17:28');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_records`
--

DROP TABLE IF EXISTS `activity_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `learner_iep_id` int NOT NULL,
  `activity_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity_data` json DEFAULT NULL,
  `performance_notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` int NOT NULL,
  `recorded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `learner_iep_id` (`learner_iep_id`),
  KEY `recorded_by` (`recorded_by`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_recorded_at` (`recorded_at`),
  CONSTRAINT `activity_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_records_ibfk_2` FOREIGN KEY (`learner_iep_id`) REFERENCES `learner_iep` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_records_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_records`
--

LOCK TABLES `activity_records` WRITE;
/*!40000 ALTER TABLE `activity_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_stars`
--

DROP TABLE IF EXISTS `activity_stars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_stars` (
  `id` int NOT NULL AUTO_INCREMENT,
  `submission_id` int NOT NULL,
  `student_id` int NOT NULL,
  `stars` tinyint NOT NULL,
  `calculated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_submission_stars` (`submission_id`),
  KEY `idx_student_id` (`student_id`),
  CONSTRAINT `activity_stars_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `lms_submissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_stars_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_stars` CHECK ((`stars` in (1,2,3)))
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_stars`
--

LOCK TABLES `activity_stars` WRITE;
/*!40000 ALTER TABLE `activity_stars` DISABLE KEYS */;
INSERT INTO `activity_stars` VALUES (3,13,4,3,'2026-09-19 19:24:23'),(4,14,4,3,'2026-09-19 19:24:23'),(5,15,5,3,'2026-09-19 19:24:27'),(6,16,5,3,'2026-09-19 19:24:27'),(7,17,6,1,'2026-09-19 19:24:36'),(8,18,6,1,'2026-09-19 19:24:36'),(9,19,7,3,'2026-09-19 19:24:44'),(10,20,7,1,'2026-09-19 19:24:44'),(11,21,8,3,'2026-09-19 19:24:52'),(12,22,8,3,'2026-09-19 19:24:52');
/*!40000 ALTER TABLE `activity_stars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_templates`
--

DROP TABLE IF EXISTS `activity_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `material_id` int NOT NULL,
  `activity_type` enum('multiple_choice','true_false','fill_blanks','matching','drag_drop_sort','image_label','sequencing','flashcards') COLLATE utf8mb4_unicode_ci NOT NULL,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `activity_data` json NOT NULL,
  `total_points` int DEFAULT '0',
  `time_limit_minutes` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_material_id` (`material_id`),
  KEY `idx_activity_type` (`activity_type`),
  CONSTRAINT `activity_templates_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `learning_materials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_templates`
--

LOCK TABLES `activity_templates` WRITE;
/*!40000 ALTER TABLE `activity_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assessment_checklists`
--

DROP TABLE IF EXISTS `assessment_checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessment_checklists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assessment_id` int NOT NULL,
  `service_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checked` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assessment_service` (`assessment_id`,`service_type`),
  KEY `idx_assessment_id` (`assessment_id`),
  KEY `idx_service_type` (`service_type`),
  CONSTRAINT `assessment_checklists_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessment_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assessment_checklists`
--

LOCK TABLES `assessment_checklists` WRITE;
/*!40000 ALTER TABLE `assessment_checklists` DISABLE KEYS */;
/*!40000 ALTER TABLE `assessment_checklists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assessment_documents`
--

DROP TABLE IF EXISTS `assessment_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessment_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assessment_service_id` int NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_service_id` (`assessment_service_id`),
  KEY `idx_file_type` (`file_type`),
  CONSTRAINT `assessment_documents_ibfk_1` FOREIGN KEY (`assessment_service_id`) REFERENCES `assessment_services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assessment_documents`
--

LOCK TABLES `assessment_documents` WRITE;
/*!40000 ALTER TABLE `assessment_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `assessment_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assessment_records`
--

DROP TABLE IF EXISTS `assessment_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessment_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `parent_id` int DEFAULT NULL,
  `assessed_by` int NOT NULL,
  `conducted_by` int DEFAULT NULL,
  `assessment_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assessment_data` json DEFAULT NULL,
  `submitted_data` json DEFAULT NULL,
  `education_history` json DEFAULT NULL,
  `assessment_info` json DEFAULT NULL,
  `section_a_data` json DEFAULT NULL,
  `services_checked` json DEFAULT NULL,
  `screening_types` json DEFAULT NULL,
  `status` enum('draft','finalized','pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `reviewed_by` int DEFAULT NULL,
  `review_note` text COLLATE utf8mb4_unicode_ci,
  `quarter` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `version` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `assessed_by` (`assessed_by`),
  KEY `conducted_by` (`conducted_by`),
  KEY `reviewed_by` (`reviewed_by`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_version` (`version`),
  KEY `idx_assessment_status` (`status`),
  KEY `idx_assessment_quarter` (`quarter`),
  KEY `idx_assessment_submitted` (`submitted_at`),
  CONSTRAINT `assessment_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assessment_records_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assessment_records_ibfk_3` FOREIGN KEY (`assessed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assessment_records_ibfk_4` FOREIGN KEY (`conducted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assessment_records_ibfk_5` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assessment_records`
--

LOCK TABLES `assessment_records` WRITE;
/*!40000 ALTER TABLE `assessment_records` DISABLE KEYS */;
INSERT INTO `assessment_records` VALUES (2,4,NULL,6,6,NULL,NULL,NULL,NULL,NULL,'{\"age\": \"12\", \"lrn\": \"\", \"sex\": \"Female\", \"school\": \"Piedad Central Elementary School\", \"religion\": \"\", \"with_iep\": \"no\", \"last_name\": \"TEST\", \"birth_date\": \"2014-02-09\", \"first_name\": \"ONE\", \"father_name\": \"PAPA  TEST\", \"middle_name\": \"\", \"mother_name\": \"MAMA  TEST\", \"school_year\": \"2026-2027\", \"adviser_name\": \"Zere d\'Apchier\", \"home_address\": \"Don Lorenzo, Toril, Davao City, Davao del Sur 8000\", \"guardian_name\": \"\", \"extension_name\": \"\", \"father_contact\": \"0944322445\", \"mother_contact\": \"09332983752\", \"previous_school\": \"\", \"guardian_contact\": \"\", \"father_occupation\": \"\", \"mother_occupation\": \"\", \"guardian_occupation\": \"\", \"previous_grade_level\": \"\", \"previous_school_year\": \"\", \"with_support_services\": \"no\", \"support_services_detail\": \"\"}','[]','[]','finalized',NULL,NULL,NULL,NULL,NULL,1,'2026-09-08 18:02:34','2026-09-08 18:02:34'),(3,5,NULL,6,6,NULL,NULL,NULL,NULL,NULL,'{\"age\": \"12\", \"lrn\": \"129688260004\", \"sex\": \"Male\", \"school\": \"Piedad Central Elementary School\", \"with_iep\": \"yes\", \"last_name\": \"2\", \"birth_date\": \"2014-05-14\", \"first_name\": \"TEST\", \"school_year\": \"2026-2027\", \"adviser_name\": \"Zere d\'Apchier\", \"home_address\": \"Purok 5, Crossing Bayabas, Toril, Davao City\"}',NULL,NULL,'finalized',NULL,NULL,NULL,NULL,NULL,1,'2026-09-13 06:46:42','2026-09-13 06:46:42'),(4,6,NULL,6,6,NULL,NULL,NULL,NULL,NULL,'{\"age\": \"12\", \"lrn\": \"129688260005\", \"sex\": \"Female\", \"school\": \"Piedad Central Elementary School\", \"with_iep\": \"yes\", \"last_name\": \"3\", \"birth_date\": \"2014-08-22\", \"first_name\": \"TEST\", \"school_year\": \"2026-2027\", \"adviser_name\": \"Zere d\'Apchier\", \"home_address\": \"Blk 12 Lot 4, Deca Homes Resort, Resort Drive, Tacunan, Davao City\"}',NULL,NULL,'finalized',NULL,NULL,NULL,NULL,NULL,1,'2026-09-13 06:47:14','2026-09-13 06:47:14'),(5,7,NULL,6,6,NULL,NULL,NULL,NULL,NULL,'{\"age\": \"11\", \"lrn\": \"129688260006\", \"sex\": \"Male\", \"school\": \"Piedad Central Elementary School\", \"with_iep\": \"yes\", \"last_name\": \"4\", \"birth_date\": \"2015-01-10\", \"first_name\": \"TEST\", \"school_year\": \"2026-2027\", \"adviser_name\": \"Zere d\'Apchier\", \"home_address\": \"Sitio San Jose, Barangay Dumoy, Toril District, Davao City\"}',NULL,NULL,'finalized',NULL,NULL,NULL,NULL,NULL,1,'2026-09-13 06:47:21','2026-09-13 06:47:21'),(6,8,NULL,6,6,NULL,NULL,NULL,NULL,NULL,'{\"age\": \"11\", \"lrn\": \"129688260007\", \"sex\": \"Female\", \"school\": \"Piedad Central Elementary School\", \"with_iep\": \"yes\", \"last_name\": \"5\", \"birth_date\": \"2015-03-30\", \"first_name\": \"TEST\", \"school_year\": \"2026-2027\", \"adviser_name\": \"Zere d\'Apchier\", \"home_address\": \"Phase 2, Wellspring Highlands, Catalunan Pequeño, Davao City\"}',NULL,NULL,'finalized',NULL,NULL,NULL,NULL,NULL,1,'2026-09-13 06:47:29','2026-09-13 06:47:29'),(7,4,NULL,6,6,NULL,NULL,NULL,NULL,NULL,'{\"age\": \"12\", \"lrn\": \"\", \"sex\": \"Female\", \"school\": \"Piedad Central Elementary School\", \"religion\": \"\", \"with_iep\": \"no\", \"last_name\": \"TEST\", \"birth_date\": \"2014-02-09\", \"first_name\": \"ONE\", \"father_name\": \"PAPA  TEST\", \"middle_name\": \"\", \"mother_name\": \"MAMA  TEST\", \"school_year\": \"2026-2027\", \"adviser_name\": \"Zere d\'Apchier\", \"home_address\": \"Don Lorenzo, Toril, Davao City, Davao del Sur 8000\", \"guardian_name\": \"\", \"extension_name\": \"\", \"father_contact\": \"0944322445\", \"mother_contact\": \"09332983752\", \"previous_school\": \"\", \"guardian_contact\": \"\", \"father_occupation\": \"\", \"mother_occupation\": \"\", \"guardian_occupation\": \"\", \"previous_grade_level\": \"\", \"previous_school_year\": \"\", \"with_support_services\": \"no\", \"support_services_detail\": \"\"}','[]','[]','finalized',NULL,NULL,NULL,NULL,NULL,2,'2026-09-20 19:26:17','2026-09-20 19:26:17');
/*!40000 ALTER TABLE `assessment_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assessment_services`
--

DROP TABLE IF EXISTS `assessment_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessment_services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assessment_id` int NOT NULL,
  `service_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mdt_members` json DEFAULT NULL,
  `date_of_assessment` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_assessment_id` (`assessment_id`),
  KEY `idx_service_name` (`service_name`),
  CONSTRAINT `assessment_services_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessment_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assessment_services`
--

LOCK TABLES `assessment_services` WRITE;
/*!40000 ALTER TABLE `assessment_services` DISABLE KEYS */;
/*!40000 ALTER TABLE `assessment_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignment_submissions`
--

DROP TABLE IF EXISTS `assignment_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assignment_submissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `material_id` int NOT NULL,
  `student_id` int NOT NULL,
  `submission_type` enum('file','text','both') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_answer` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `graded` tinyint(1) DEFAULT '0',
  `grade` int DEFAULT NULL,
  `teacher_feedback` text COLLATE utf8mb4_unicode_ci,
  `graded_at` timestamp NULL DEFAULT NULL,
  `graded_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `graded_by` (`graded_by`),
  KEY `idx_material_id` (`material_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_graded` (`graded`),
  CONSTRAINT `assignment_submissions_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `learning_materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_submissions_ibfk_3` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignment_submissions`
--

LOCK TABLES `assignment_submissions` WRITE;
/*!40000 ALTER TABLE `assignment_submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `assignment_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_records`
--

DROP TABLE IF EXISTS `attendance_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','tardy','excused') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present',
  `source` enum('manual','auto_activity') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `recorded_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_date_src` (`student_id`,`date`,`source`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `attendance_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_records_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_records`
--

LOCK TABLES `attendance_records` WRITE;
/*!40000 ALTER TABLE `attendance_records` DISABLE KEYS */;
INSERT INTO `attendance_records` VALUES (63,4,'2026-09-01','present','manual',6,'2026-09-19 19:24:23'),(64,4,'2026-09-02','present','manual',6,'2026-09-19 19:24:23'),(65,4,'2026-09-03','present','manual',6,'2026-09-19 19:24:23'),(66,4,'2026-09-04','present','manual',6,'2026-09-19 19:24:23'),(67,4,'2026-09-07','present','manual',6,'2026-09-19 19:24:23'),(68,4,'2026-09-08','present','manual',6,'2026-09-19 19:24:23'),(69,4,'2026-09-09','present','manual',6,'2026-09-19 19:24:23'),(70,4,'2026-09-10','present','manual',6,'2026-09-19 19:24:23'),(71,4,'2026-09-11','present','manual',6,'2026-09-19 19:24:23'),(72,4,'2026-09-14','present','manual',6,'2026-09-19 19:24:23'),(73,4,'2026-09-15','present','manual',6,'2026-09-19 19:24:23'),(74,4,'2026-09-16','present','manual',6,'2026-09-19 19:24:23'),(75,4,'2026-09-17','present','manual',6,'2026-09-19 19:24:23'),(76,4,'2026-09-18','present','manual',6,'2026-09-19 19:24:23'),(77,5,'2026-09-01','present','manual',6,'2026-09-19 19:24:27'),(78,5,'2026-09-02','present','manual',6,'2026-09-19 19:24:27'),(79,5,'2026-09-03','present','manual',6,'2026-09-19 19:24:27'),(80,5,'2026-09-04','present','manual',6,'2026-09-19 19:24:28'),(81,5,'2026-09-07','present','manual',6,'2026-09-19 19:24:28'),(82,5,'2026-09-08','present','manual',6,'2026-09-19 19:24:28'),(83,5,'2026-09-09','present','manual',6,'2026-09-19 19:24:28'),(84,5,'2026-09-10','present','manual',6,'2026-09-19 19:24:28'),(85,5,'2026-09-11','present','manual',6,'2026-09-19 19:24:28'),(86,5,'2026-09-14','present','manual',6,'2026-09-19 19:24:28'),(87,5,'2026-09-15','present','manual',6,'2026-09-19 19:24:28'),(88,5,'2026-09-16','present','manual',6,'2026-09-19 19:24:28'),(89,5,'2026-09-17','present','manual',6,'2026-09-19 19:24:28'),(90,5,'2026-09-18','present','manual',6,'2026-09-19 19:24:28'),(91,6,'2026-09-01','present','manual',6,'2026-09-19 19:24:36'),(92,6,'2026-09-02','present','manual',6,'2026-09-19 19:24:36'),(93,6,'2026-09-03','present','manual',6,'2026-09-19 19:24:36'),(94,6,'2026-09-04','present','manual',6,'2026-09-19 19:24:36'),(95,6,'2026-09-07','present','manual',6,'2026-09-19 19:24:36'),(96,6,'2026-09-08','present','manual',6,'2026-09-19 19:24:36'),(97,6,'2026-09-09','present','manual',6,'2026-09-19 19:24:36'),(98,6,'2026-09-10','present','manual',6,'2026-09-19 19:24:36'),(99,6,'2026-09-11','present','manual',6,'2026-09-19 19:24:36'),(100,6,'2026-09-14','present','manual',6,'2026-09-19 19:24:36'),(101,6,'2026-09-15','present','manual',6,'2026-09-19 19:24:36'),(102,6,'2026-09-16','present','manual',6,'2026-09-19 19:24:36'),(103,6,'2026-09-17','present','manual',6,'2026-09-19 19:24:36'),(104,6,'2026-09-18','present','manual',6,'2026-09-19 19:24:36'),(105,7,'2026-09-01','present','manual',6,'2026-09-19 19:24:44'),(106,7,'2026-09-02','present','manual',6,'2026-09-19 19:24:44'),(107,7,'2026-09-03','present','manual',6,'2026-09-19 19:24:44'),(108,7,'2026-09-04','present','manual',6,'2026-09-19 19:24:44'),(109,7,'2026-09-07','present','manual',6,'2026-09-19 19:24:44'),(110,7,'2026-09-08','present','manual',6,'2026-09-19 19:24:44'),(111,7,'2026-09-09','present','manual',6,'2026-09-19 19:24:44'),(112,7,'2026-09-10','present','manual',6,'2026-09-19 19:24:44'),(113,7,'2026-09-11','present','manual',6,'2026-09-19 19:24:44'),(114,7,'2026-09-14','present','manual',6,'2026-09-19 19:24:44'),(115,7,'2026-09-15','present','manual',6,'2026-09-19 19:24:44'),(116,7,'2026-09-16','present','manual',6,'2026-09-19 19:24:44'),(117,7,'2026-09-17','present','manual',6,'2026-09-19 19:24:44'),(118,7,'2026-09-18','present','manual',6,'2026-09-19 19:24:44'),(119,8,'2026-09-01','present','manual',6,'2026-09-19 19:24:52'),(120,8,'2026-09-02','present','manual',6,'2026-09-19 19:24:52'),(121,8,'2026-09-03','present','manual',6,'2026-09-19 19:24:52'),(122,8,'2026-09-04','present','manual',6,'2026-09-19 19:24:52'),(123,8,'2026-09-07','present','manual',6,'2026-09-19 19:24:52'),(124,8,'2026-09-08','present','manual',6,'2026-09-19 19:24:52'),(125,8,'2026-09-09','present','manual',6,'2026-09-19 19:24:52'),(126,8,'2026-09-10','present','manual',6,'2026-09-19 19:24:52'),(127,8,'2026-09-11','present','manual',6,'2026-09-19 19:24:52'),(128,8,'2026-09-14','present','manual',6,'2026-09-19 19:24:52'),(129,8,'2026-09-15','present','manual',6,'2026-09-19 19:24:52'),(130,8,'2026-09-16','present','manual',6,'2026-09-19 19:24:52'),(131,8,'2026-09-17','present','manual',6,'2026-09-19 19:24:52'),(132,8,'2026-09-18','present','manual',6,'2026-09-19 19:24:52');
/*!40000 ALTER TABLE `attendance_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_placements`
--

DROP TABLE IF EXISTS `class_placements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_placements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `itgp_id` int NOT NULL,
  `reviewed_by` int NOT NULL,
  `status` enum('confirmed','on_hold') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'confirmed',
  `hold_reason` text COLLATE utf8mb4_unicode_ci,
  `confirmed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `itgp_id` (`itgp_id`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `class_placements_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_placements_ibfk_2` FOREIGN KEY (`itgp_id`) REFERENCES `itgp_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_placements_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_placements`
--

LOCK TABLES `class_placements` WRITE;
/*!40000 ALTER TABLE `class_placements` DISABLE KEYS */;
/*!40000 ALTER TABLE `class_placements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classroom_observations`
--

DROP TABLE IF EXISTS `classroom_observations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classroom_observations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `observer_id` int NOT NULL,
  `observed_teacher_id` int NOT NULL,
  `school_year` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quarter` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observation_number` int NOT NULL,
  `subject_grade_level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `status` enum('scheduled','in_progress','pending_signoff','finalized') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `other_comments` text COLLATE utf8mb4_unicode_ci,
  `average_score` decimal(5,2) DEFAULT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `teacher_signed_at` datetime DEFAULT NULL,
  `teacher_signature_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_observer_id` (`observer_id`),
  KEY `idx_observed_teacher_id` (`observed_teacher_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `classroom_observations_ibfk_1` FOREIGN KEY (`observer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `classroom_observations_ibfk_2` FOREIGN KEY (`observed_teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classroom_observations`
--

LOCK TABLES `classroom_observations` WRITE;
/*!40000 ALTER TABLE `classroom_observations` DISABLE KEYS */;
INSERT INTO `classroom_observations` VALUES (1,19,6,'SY 2026-2027','1st Quarter',1,'Life Skills - SPED Program','2026-09-22 09:00:00','scheduled',NULL,NULL,NULL,NULL,NULL,'2026-09-19 20:00:23','2026-09-19 20:00:23');
/*!40000 ALTER TABLE `classroom_observations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cot_indicator_sets`
--

DROP TABLE IF EXISTS `cot_indicator_sets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cot_indicator_sets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `school_year` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `indicator_number` int NOT NULL,
  `indicator_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `competency_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_school_year` (`school_year`),
  CONSTRAINT `cot_indicator_sets_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cot_indicator_sets`
--

LOCK TABLES `cot_indicator_sets` WRITE;
/*!40000 ALTER TABLE `cot_indicator_sets` DISABLE KEYS */;
INSERT INTO `cot_indicator_sets` VALUES (1,'SY 2025-2026',1,'Apply knowledge of content within and across curriculum teaching areas','1.1.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(2,'SY 2025-2026',2,'Use a range of teaching strategies that enhance learner achievement in literacy and numeracy skills','1.4.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(3,'SY 2025-2026',3,'Apply a range of teaching strategies to develop critical and creative thinking, as well as other higher-order thinking skills','1.5.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(4,'SY 2025-2026',4,'Manage classroom structure to engage learners, individually or in groups, in meaningful exploration, discovery and hands-on activities within a range of physical learning environments','2.3.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(5,'SY 2025-2026',5,'Manage learner behavior constructively by applying positive and non-violent discipline to ensure learning-focused environments','2.6.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(6,'SY 2025-2026',6,'Use differentiated, developmentally appropriate learning experiences to address learners\' gender, needs, strengths, interests and experiences','3.1.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(7,'SY 2025-2026',7,'Plan, manage and implement developmentally sequenced teaching and learning process to meet curriculum requirements and varied teaching contexts','4.1.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(8,'SY 2025-2026',8,'Select, develop, organize and use appropriate teaching and learning resources, including ICT, to address learning goals','4.5.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(9,'SY 2025-2026',9,'Design, select, organize and use diagnostic, formative and summative assessment strategies consistent with curriculum requirements','5.1.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(10,'SY 2026-2027',1,'Apply knowledge of content within and across curriculum teaching areas','1.1.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(11,'SY 2026-2027',2,'Use a range of teaching strategies that enhance learner achievement in literacy and numeracy skills','1.4.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(12,'SY 2026-2027',3,'Apply a range of teaching strategies to develop critical and creative thinking, as well as other higher-order thinking skills','1.5.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(13,'SY 2026-2027',4,'Display proficient use of Mother Tongue, Filipino, and English to facilitate teaching and learning','1.6.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(14,'SY 2026-2027',5,'Establish safe and secure learning environments to enhance learning through the consistent implementation of policies, guidelines, and procedures','2.1.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(15,'SY 2026-2027',6,'Maintain learning environments that promote fairness, respect, and care to encourage learning','2.2.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(16,'SY 2026-2027',7,'Establish a learner-centered culture by using teaching strategies that respond to their linguistic, cultural, socio-economic, and religious backgrounds','3.2.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(17,'SY 2026-2027',8,'Adapt and use culturally appropriate teaching strategies to address the needs of learners from indigenous groups','3.5.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(18,'SY 2026-2027',9,'Use effective strategies for providing timely, accurate, and constructive feedback to improve learner performance','5.3.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(19,'SY 2027-2028',1,'Apply knowledge of content within and across curriculum teaching areas','1.1.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(20,'SY 2027-2028',2,'Use a range of teaching strategies that enhance learner achievement in literacy and numeracy skills','1.4.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(21,'SY 2027-2028',3,'Ensure the positive use of ICT to facilitate the teaching and learning process','1.3.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(22,'SY 2027-2028',4,'Use effective verbal and non-verbal classroom communication strategies to support learner understanding, participation, engagement, and achievement','1.7.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(23,'SY 2027-2028',5,'Maintain supportive learning environments that nurture and inspire learners to participate, cooperate, and collaborate in continued learning','2.4.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(24,'SY 2027-2028',6,'Apply a range of successful strategies that maintain learning environments that motivate learners to work productively by assuming responsibility for their own learning','2.5.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(25,'SY 2027-2028',7,'Design, adapt, and implement teaching strategies that are responsive to learners with disabilities, giftedness, and talents','3.3.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13'),(26,'SY 2027-2028',8,'Plan and deliver teaching strategies that are responsive to the special educational needs of learners in difficult circumstances, including: geographic isolation; chronic illness; displacement due to armed conflict, urban resettlement or disasters; child abuse and child labor practices','3.4.2',NULL,'2026-08-20 18:53:13','2026-08-20 18:53:13');
/*!40000 ALTER TABLE `cot_indicator_sets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cot_observations`
--

DROP TABLE IF EXISTS `cot_observations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cot_observations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `iep_record_id` int NOT NULL,
  `observed_teacher_id` int NOT NULL,
  `created_by` int NOT NULL,
  `lesson_plan_id` int DEFAULT NULL,
  `school_year` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quarter` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observation_date` date DEFAULT NULL,
  `ratings` json DEFAULT NULL,
  `strengths` text COLLATE utf8mb4_unicode_ci,
  `recommendations` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','finalized') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notification_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_cot_iep` (`iep_record_id`),
  KEY `idx_cot_teacher` (`observed_teacher_id`),
  CONSTRAINT `cot_observations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cot_observations_ibfk_2` FOREIGN KEY (`iep_record_id`) REFERENCES `iep_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cot_observations_ibfk_3` FOREIGN KEY (`observed_teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cot_observations_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cot_observations`
--

LOCK TABLES `cot_observations` WRITE;
/*!40000 ALTER TABLE `cot_observations` DISABLE KEYS */;
/*!40000 ALTER TABLE `cot_observations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `csrf_tokens`
--

DROP TABLE IF EXISTS `csrf_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `csrf_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `used` tinyint(1) DEFAULT '0',
  `used_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_token` (`token`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `csrf_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=154 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `csrf_tokens`
--

LOCK TABLES `csrf_tokens` WRITE;
/*!40000 ALTER TABLE `csrf_tokens` DISABLE KEYS */;
INSERT INTO `csrf_tokens` VALUES (1,'jj7kv4c6191psv6u1kbdhgki22','4bf670884f5a8dc6c50d65c9e0c9c8b61e20c2f055d380072f91e2e0ae6da254',NULL,'2026-08-12 17:46:37','2026-08-12 10:46:37',0,NULL),(2,'jj7kv4c6191psv6u1kbdhgki22','08bd77eb91d5ae178b7a61fc10091add0b6927bf7d9cf3e91b182ae5f61192c7',NULL,'2026-08-12 17:51:41','2026-08-12 10:51:41',0,NULL),(3,'gl6nt4id31vb12bcjg75180ld5','ac720b99abe533e14dcd960030b068f1c84a5a067f61094d7bb6202b05085cf7',NULL,'2026-08-12 18:27:30','2026-08-12 11:27:30',0,NULL),(4,'6iobjqd22s2hafcnjkoukd6g32','aa9a0472bcb2964557f137986af48c40c404d00816a33eb3a44fcd7148f21937',NULL,'2026-08-12 18:57:45','2026-08-12 11:57:45',0,NULL),(5,'n6st3534bn44s15s1lfk0e8u0q','b4f6e7711c1b383cf76792ccab2346b85f7c3c6e3a373bef4e32507a2f44445d',NULL,'2026-08-19 23:36:15','2026-08-19 16:36:15',0,NULL),(6,'3fdfppdhs162f122mrgtkul95h','62e6a947c45f4c2371c40b119db468dc2fc2cdd4432ac4764c6f72e64dc2d1c9',NULL,'2026-08-19 23:37:09','2026-08-19 16:37:09',0,NULL),(7,'717v625bqtp9oje6cq0tp91qdp','215bed05798ee7a4af2404fa4290b9e2390f7c8f3eb34a1f4a039d5f9e6a8717',NULL,'2026-08-19 23:38:01','2026-08-19 16:38:01',0,NULL),(8,'smtgplel3m7qgqiahhgus7g5r4','baedacba1aa6da592a72db1c0df244838d67b242efdf2267871e99fb768ab78d',NULL,'2026-08-19 23:39:37','2026-08-19 16:39:37',0,NULL),(9,'smtgplel3m7qgqiahhgus7g5r4','b59335d62683006ff73090bd9f236bc441899406980dec08b74dd71c34eeeca3',NULL,'2026-08-19 23:39:44','2026-08-19 16:39:44',0,NULL),(10,'smtgplel3m7qgqiahhgus7g5r4','1dd981cf07c508e082be07f81e54aefe706bbae2e046f6a6d38fd7317c59f67a',NULL,'2026-08-19 23:39:53','2026-08-19 16:39:53',0,NULL),(11,'56g9iqguonopu619pt7hc9vbb9','f5bd8b137f1094e71f63a37a7b2c0381642b937779112a0fde8fca0b43ab428a',NULL,'2026-08-19 23:41:59','2026-08-19 16:41:59',0,NULL),(12,'uddqaps0j98k79qiju23s3vl5f','eabbc8c9bee03910eeee0a653816953fa78916f6a2e0d939e656e3ddb17cc37e',NULL,'2026-08-20 00:02:01','2026-08-19 17:02:01',0,NULL),(13,'uddqaps0j98k79qiju23s3vl5f','a58b786f039444ce0396a954481952ddac9dce1975f4804a01157eb5ab946d93',NULL,'2026-08-20 00:02:08','2026-08-19 17:02:08',0,NULL),(14,'m8u742n0o7ttd4dkd09682s217','84880892f6c4b9cfd4847372a41a962cf7049c8472cff90a9b46f29ebea03d01',NULL,'2026-08-20 00:02:59','2026-08-19 17:02:59',0,NULL),(15,'39t5fmmb550c0mvi04vao7hn3f','005439191b23ecc62a0c4e33d998a02c404fb2959029dc2452373f908a2191fe',NULL,'2026-08-20 00:10:45','2026-08-19 17:10:45',0,NULL),(16,'g1g7nqrgsh3gs78vpiou6vghql','0e423216e70a6401af48c8d4924488545450f5130cdcf11119742073b1b85125',NULL,'2026-08-20 00:11:23','2026-08-19 17:11:23',0,NULL),(17,'k8ft89n01q216kv14lbifdoaho','d280f1e2d0377d63c9b2826afa7911477dac5e369c67db388a10a60fa2af4d8e',NULL,'2026-08-20 07:36:32','2026-08-20 00:36:32',0,NULL),(18,'t808b5pah2tmo8tvvn1j6g2bjf','f39beab863857c5f64cc31e88230136aeda380ac4dc09fda930fc32a51169eab',NULL,'2026-08-20 18:35:06','2026-08-20 11:35:06',0,NULL),(19,'t808b5pah2tmo8tvvn1j6g2bjf','846fb160a20a681cc9c8dd1b17ff4821eeba06eff027c25eb79cc26db1dbf224',NULL,'2026-08-20 18:35:13','2026-08-20 11:35:13',0,NULL),(20,'t808b5pah2tmo8tvvn1j6g2bjf','6be895b19f2f6e2b9f0ab1c25b55ebbd0df143f0289d5ba8616b81acebd9fa6a',NULL,'2026-08-20 18:35:23','2026-08-20 11:35:23',0,NULL),(21,'u6udkrdgse2st8t7e6dltvvmvk','a5f1656912cb4ad99835f3b19cb626fc776fb2e14de13c923a804fa5824cf69e',NULL,'2026-08-20 18:36:14','2026-08-20 11:36:14',0,NULL),(22,'1k7gm0fr11l1hapoqugs02odiq','fa41212dbabe6cfa20cdbe0c6bbd99c92d957a65cf53f23049a9b3ed95c71291',NULL,'2026-08-20 18:41:42','2026-08-20 11:41:42',0,NULL),(23,'jsc2qb7ldsukurv6mhst4ukvdc','d6c4f5849b7a0d3f4e3104fb9e873f5ecfd46c4bcd0cd0c6edeedec376429d1d',NULL,'2026-08-20 18:50:54','2026-08-20 11:50:54',0,NULL),(24,'5549n6dbp3ldatj6vq64q3ndf7','102f20b70114468cad15f282f82d1ff2f8030462d89eccba983cc1d97cb8cabe',NULL,'2026-08-20 19:46:57','2026-08-20 12:46:57',0,NULL),(25,'e9hi9ujo6bs0ld1e8fiem4ckjc','aa998071760efd8d0e180a6397ab98efd1b8ec9fa9eabcaff94a5b0fbffa5d57',NULL,'2026-08-31 12:06:55','2026-08-31 05:06:55',0,NULL),(26,'0nekvekeu8rvrrqgrh6od9186f','3b584a357054fce51b14ac59acc3313bfe2f9cf85dd6501d44fee94f3245d29f',NULL,'2026-09-08 13:29:00','2026-09-08 06:29:00',0,NULL),(27,'0nekvekeu8rvrrqgrh6od9186f','2fb7ff831afac63c6d5f76a9ee03dacb2c53c9622efa1c255398953abbc373fc',NULL,'2026-09-08 13:31:22','2026-09-08 06:31:22',0,NULL),(28,'vfss25f76tl9vvojiq55tda45a','b43347bb4c6e54d5bb9403677f21e14d245521540431f614556f2b79016ad26f',NULL,'2026-09-08 14:26:18','2026-09-08 07:26:18',0,NULL),(29,'cpohs1mdn8ce6g8naia1d6dgq8','7c6345118ad7eddd821683820fbd382e3fec0d35f305abfeec13f764b4a7cf99',NULL,'2026-09-08 14:27:40','2026-09-08 07:27:40',0,NULL),(30,'cpohs1mdn8ce6g8naia1d6dgq8','271e3662022eec00c3cc4ec48d87ee16e82ca9c9234b899ce2820de5fcc87db2',NULL,'2026-09-08 14:27:55','2026-09-08 07:27:55',0,NULL),(31,'cpohs1mdn8ce6g8naia1d6dgq8','76ed03994832c9d23d6cdee58edbd70ed0f0534d8a1a78f6c9d9cb67c0328524',NULL,'2026-09-08 14:29:31','2026-09-08 07:29:31',0,NULL),(32,'3oa12dafnbfd7ub327mf22htk1','7a789d9c226ceec10641cb81af55d65d27b906b3a0467382413922c5af0629a5',NULL,'2026-09-08 17:16:19','2026-09-08 10:16:19',0,NULL),(33,'8jlm8iqsessh53j0su0s9kktov','0dbe104236ca03478d0641fc74c8666e86c52e91f7abe92671cfe42941a386c4',NULL,'2026-09-08 18:53:37','2026-09-08 11:53:37',0,NULL),(34,'8jlm8iqsessh53j0su0s9kktov','fc13de8cc4489c05a0a7dcc0c301a157d628a2f699dd1d572158032197f43b6b',NULL,'2026-09-08 18:53:53','2026-09-08 11:53:53',0,NULL),(35,'8jlm8iqsessh53j0su0s9kktov','90c85251f89aec1527f96d0182cc50c0ea06d9fa67a56b5f9588624d76c9b9d1',NULL,'2026-09-08 18:54:17','2026-09-08 11:54:17',0,NULL),(36,'8jlm8iqsessh53j0su0s9kktov','7fccc122aef5bff2541d370a2c92592a0a07c5b38d29b4639de2c675fe4439ec',NULL,'2026-09-08 18:54:53','2026-09-08 11:54:53',0,NULL),(37,'8jlm8iqsessh53j0su0s9kktov','5fbe72c75bb8476187c5cfc542a08c13aab4184e21a309f3340a03f96c963439',NULL,'2026-09-08 18:55:38','2026-09-08 11:55:38',0,NULL),(38,'8jlm8iqsessh53j0su0s9kktov','a0f9e6b3773427f1be66a940f103745a511e57f8236be5a05d25c1146304fb77',NULL,'2026-09-08 18:56:46','2026-09-08 11:56:46',0,NULL),(39,'ug7jgkr1i256lokou7e51sq1o3','da231c3300e6a9889e049816cf4674d378e7daf1ccda7e5018f57e38facc5a4c',NULL,'2026-09-09 17:14:12','2026-09-09 10:14:12',0,NULL),(40,'r3gonchhi24iis7eg9of08h060','9d8910d69931cb67f8846ccd77e7e18b5c56aa551023ee7f02813846a4886b8e',NULL,'2026-09-10 13:25:13','2026-09-10 06:25:13',0,NULL),(41,'n8dj0gbg8poff7vd2nabc21eld','d9b3db183a2f9c44ffd23b8aeb41008c9537c45df302eb9c6c191895fa4558c8',NULL,'2026-09-10 18:03:32','2026-09-10 11:03:32',0,NULL),(42,'nqnvqjpkm69bjg6jupk321j1lt','90516052a723e34cde61f2950757745aa5eb722c13824d598572d3668dbf0c4c',NULL,'2026-09-10 20:21:46','2026-09-10 13:21:46',0,NULL),(43,'n8dj0gbg8poff7vd2nabc21eld','bd52c47a2f54801e636be2cb03c193754cf2436c61789d073d54c3fb0bee9423',NULL,'2026-09-10 20:59:26','2026-09-10 13:59:26',0,NULL),(44,'r9kkjiju3gujcohmbd5rmavoik','3e1a748ef29987b96df27d85337c7e659fc11f293345c225f8f9b2c827289384',NULL,'2026-09-10 21:27:44','2026-09-10 14:27:44',0,NULL),(45,'r9kkjiju3gujcohmbd5rmavoik','c9f804641bdda398cd9ea160ad060a0ef3b3a619ed2b03c21e60f22d4edb287a',NULL,'2026-09-10 21:27:50','2026-09-10 14:27:50',0,NULL),(46,'r9kkjiju3gujcohmbd5rmavoik','a07f9e54c32c53a69ce18a0eede0772fbc94d6b6ffd820bdc961c085ae58cd9f',NULL,'2026-09-10 21:27:55','2026-09-10 14:27:55',0,NULL),(47,'gk99kotl247n5jp3i0qlcckbsa','c47c7bca86b83b1b82efd3ae0ec6ae96060b9c4101dd38de849d2d38621960f2',NULL,'2026-09-10 21:28:46','2026-09-10 14:28:46',0,NULL),(48,'5j64in7sqb0v6nal2n2jn4p8n1','36316fb345527022c256a15659eb3c854fafde2ea6ac4f4e4b0e0f351724b384',NULL,'2026-09-11 09:56:52','2026-09-11 02:56:52',0,NULL),(49,'5j64in7sqb0v6nal2n2jn4p8n1','f493f9944ecaead37f8a5f93fb08a24c0137393a6ce901d6fb432fcafe519dbe',NULL,'2026-09-11 10:04:18','2026-09-11 03:04:18',0,NULL),(50,'93ku14jkbohf7lb189ngkhc705','c5569fe4843b8c06ed68a14f499fc12ad51a666d9d4cc7f7055e8c64f1f12188',NULL,'2026-09-11 10:24:17','2026-09-11 03:24:17',0,NULL),(51,'93ku14jkbohf7lb189ngkhc705','07c8f4a8a1b233d7fd7457ed54c97ed8026586565954ecfbd5660294fb6f5d41',NULL,'2026-09-11 10:24:29','2026-09-11 03:24:29',0,NULL),(52,'k5am0sev9p7rdl1fj04hmjuaap','b3c4650bded3d1bfde3b36390f18c519b1cdd3d7c4fe5fe61fe7f4fb080e1192',NULL,'2026-09-11 12:09:35','2026-09-11 05:09:35',0,NULL),(53,'k5am0sev9p7rdl1fj04hmjuaap','6f2cad662188a9ac888d599e18f78e80581444b79060061a46d57b9155309c33',NULL,'2026-09-11 12:09:39','2026-09-11 05:09:39',0,NULL),(54,'k5am0sev9p7rdl1fj04hmjuaap','e555eff34566424f29d3c64b5983923ff61ef88b2d2617e9b6e86e92bfc89e5c',NULL,'2026-09-11 12:09:40','2026-09-11 05:09:40',0,NULL),(55,'k5am0sev9p7rdl1fj04hmjuaap','9f8c02042495c733611c0c26190b5f1e98aeafb0299c06a9543fe4336b8fd206',NULL,'2026-09-11 12:09:46','2026-09-11 05:09:46',0,NULL),(56,'c110994jnsgehcbs4k09fbgi69','4cee7ad010098433eb9a44215ab89112a7e5f64ed07356a68cb52ca17e09ca47',NULL,'2026-09-11 13:00:57','2026-09-11 06:00:57',0,NULL),(57,'c110994jnsgehcbs4k09fbgi69','4d5e14d53cef7195a954dc9f7e810b891e22f41d7a13447c47c79d21cce4fcdd',NULL,'2026-09-11 13:01:05','2026-09-11 06:01:05',0,NULL),(58,'c110994jnsgehcbs4k09fbgi69','dab4bf08fb0f9be205c5a566ba04e409a0d9783e73fdee6d124839c36f86f324',NULL,'2026-09-11 13:01:05','2026-09-11 06:01:05',0,NULL),(59,'c110994jnsgehcbs4k09fbgi69','f8aa3014395d01a4b32797220f167d0e3b81f148b1ed7a6bc66cfb92bb93ee46',NULL,'2026-09-11 13:01:09','2026-09-11 06:01:09',0,NULL),(60,'5j64in7sqb0v6nal2n2jn4p8n1','d3f8df0eacac6bfb9c91ec1412ad010fd1048a85c5a572652be1adb3a36d1c5a',NULL,'2026-09-11 13:23:25','2026-09-11 06:23:25',0,NULL),(61,'nh20c3v81sg0lep8jo7v0lcnj2','1f9d576ea8d29e772c0827caf1d64b10989366c63dbec6c7c47b9175f0024ec1',NULL,'2026-09-11 14:20:52','2026-09-11 07:20:52',0,NULL),(62,'nh20c3v81sg0lep8jo7v0lcnj2','cc49ec26dbc59b335172939064e2badee48d71dd0a40743f267ba3203805bcf2',NULL,'2026-09-11 14:20:59','2026-09-11 07:20:59',0,NULL),(63,'nh20c3v81sg0lep8jo7v0lcnj2','630c406afbabd6004ac9979fc6ad1f791364ff1254186b5ee5d1aa7e7e025725',NULL,'2026-09-11 14:21:00','2026-09-11 07:21:00',0,NULL),(64,'nh20c3v81sg0lep8jo7v0lcnj2','7ccdb6c20f65ae80269903a8b1f62c017611120eff976d721f88ea58060ed1a0',NULL,'2026-09-11 14:21:43','2026-09-11 07:21:43',0,NULL),(65,'5hep3htkil4cej3f5m8v5n85j9','32b9cc371d4a4aea1d5ded6aa408081d82ed657ec9f833aa9e66c98cd8d3861f',NULL,'2026-09-11 15:05:47','2026-09-11 08:05:47',0,NULL),(66,'5hep3htkil4cej3f5m8v5n85j9','3dd3943772902a409f0f2e8b1ff093c8c5e580e2adcf6d3f6e7bfb49e43fd986',NULL,'2026-09-11 15:05:51','2026-09-11 08:05:51',0,NULL),(67,'5hep3htkil4cej3f5m8v5n85j9','28321398b8a706f67f6f38c6e245f02d5f08314cffdd3daba316fcd36f31f846',NULL,'2026-09-11 15:05:52','2026-09-11 08:05:52',0,NULL),(68,'vfub9bbmquo4qr5vfrkdvhu2t7','487fa123d8c28c9373e45cd701bef5f694f78bd7665b98f4790c892a73181c60',NULL,'2026-09-11 16:58:01','2026-09-11 09:58:01',0,NULL),(69,'vfub9bbmquo4qr5vfrkdvhu2t7','b1b65ef6ce612315f5cd81ad42299ef2f02e03ce3188940165dc2715c91b5b85',NULL,'2026-09-11 16:58:06','2026-09-11 09:58:06',0,NULL),(70,'vfub9bbmquo4qr5vfrkdvhu2t7','07b11ee65e1db92e7ce52bffd6bbc5ed4eda8b0244de40ae2add100b565ae124',NULL,'2026-09-11 16:59:39','2026-09-11 09:59:39',0,NULL),(71,'vfub9bbmquo4qr5vfrkdvhu2t7','18377d6547c6bec46790cec6bb6e761d7ac90dfd9cc25eecf9b7a8dcc5876a13',NULL,'2026-09-11 16:59:43','2026-09-11 09:59:43',0,NULL),(72,'0hbh6ecmvl7175l5v91fcmefvm','c0183c142b115995c3b20fdddb5f72b91d5f191d5c918e944a52ce7107ca2006',NULL,'2026-09-11 17:56:12','2026-09-11 10:56:12',0,NULL),(73,'0hbh6ecmvl7175l5v91fcmefvm','80388d80bbbf7e143019e04665ac851acf6754e22d3b2cf6cd2527db7fe06249',NULL,'2026-09-11 17:57:45','2026-09-11 10:57:45',0,NULL),(74,'tcep6k34r046kr3egpcdpd202k','41ffaa76fb79bb2d6df9ccc0ae2317b59060df81f77eb3cfdd77f573739cf2bb',NULL,'2026-09-11 18:18:14','2026-09-11 11:18:14',0,NULL),(75,'3hks0pv16mo33t7uh46b8cg487','584656cc604d174865226c39a8fdd03dc9a7df8e3d29c4aa32614b06f4a9667e',NULL,'2026-09-11 18:20:10','2026-09-11 11:20:10',0,NULL),(76,'r0ojjcuqg6p4239m110eqbai9h','2dc0c1b610971965746babc39d10684103867c9da401570f3630fb2922367899',NULL,'2026-09-11 18:20:23','2026-09-11 11:20:23',0,NULL),(77,'fl2ps3ajoa28l1sg3p2djajluu','62692d19d6b80b5018f35c18726c037b553b97f22292b2db1c9e032d0a1084df',NULL,'2026-09-11 18:20:32','2026-09-11 11:20:32',0,NULL),(78,'fl2ps3ajoa28l1sg3p2djajluu','9e1e191f24042d250d14542fd74b97ba149047dbc36396c2a70359a143e7799c',NULL,'2026-09-11 18:26:48','2026-09-11 11:26:48',0,NULL),(79,'4fjek456bsf2b2kh63dqusjabn','3a05345477e1e3ffa4dde89569d28d2fdb78535804208105c4c60ced2cb96608',NULL,'2026-09-11 19:45:29','2026-09-11 12:45:29',0,NULL),(80,'4fjek456bsf2b2kh63dqusjabn','33e9c45040959642b2ad19803342750619c47e7e0b6c18ed21ca91c3017c8490',NULL,'2026-09-11 21:49:04','2026-09-11 14:49:04',0,NULL),(81,'4fjek456bsf2b2kh63dqusjabn','df7533f12699fdf13c55c285ac05c23b7ea1f1aaa53b6026ca40013a6a210c34',NULL,'2026-09-11 21:49:04','2026-09-11 14:49:04',0,NULL),(82,'dr1uu9ud3dbkfmlns6jk47vs74','be40bb5bc9e3d1ba5589d529aead432fa25d2bf5b177254fdea2abb6a100abd2',NULL,'2026-09-13 06:27:49','2026-09-12 23:27:49',0,NULL),(83,'5ekm8j1e7vp9ml0f9ul1r94n9k','cd3fa24763a106aa6a43c078dce2b8521a60b6befedfc8ae638b5dcdba0eae2a',NULL,'2026-09-13 06:32:38','2026-09-12 23:32:38',0,NULL),(84,'cp6vf5j2pd04e972pkp8hb58b6','263233f5a102c9f92812b18ddecf0d9dad2be494e093cc130cefb39b10360827',NULL,'2026-09-13 06:32:43','2026-09-12 23:32:43',0,NULL),(85,'l105vik8c85uaf8easas3072jn','45c920a67b18b00f6ed40bcb1575bb3d92fd0e866818402a9cb00e6b7ca77bc2',NULL,'2026-09-13 06:33:04','2026-09-12 23:33:04',0,NULL),(86,'fg2rn2t505hu3ten4drv1hneim','6dc85841c88735600b5ffa6ecc40a93bb92fbaec61d2e4979c20dabb3ebb228d',NULL,'2026-09-13 06:33:05','2026-09-12 23:33:05',0,NULL),(87,'tefsk8gp3s1alsv1mrcs2nsnma','c3f40fd400e95f3bf417afb2840362913fb3679606e7beb50be09c8aabeabc98',NULL,'2026-09-13 06:33:28','2026-09-12 23:33:28',0,NULL),(88,'70sgvl8e9ihc5fu52te5pe0cr4','51fc69ea0876797ae20e04b8a1f78a116925367297c10525dc54eaca7669ea3a',NULL,'2026-09-13 06:33:42','2026-09-12 23:33:42',0,NULL),(89,'lerho92qfkrvqiaan3cjrqc8nd','0cd01f73846609d4d9c4a321a844d93a53b6db8f707ad8f3cb5a8970a275e40f',NULL,'2026-09-13 06:33:45','2026-09-12 23:33:45',0,NULL),(90,'u3pelqgvdbk72fv67cogkdogjr','f805828cfe6fc06d4873a7ad164a929e5614c9c53caf8b6c55d0e82967ada40f',NULL,'2026-09-13 06:33:46','2026-09-12 23:33:46',0,NULL),(91,'ha86hlds9u73rf1v5pac6j740n','ae2f6ae38c080864316555c10b2d9317149016308ee264c6aff8983703f94047',NULL,'2026-09-13 06:34:19','2026-09-12 23:34:19',0,NULL),(92,'clhn7mhl2v6loecjqel8c6o71s','77b527d61eafa8c080e602bd1a0058eb13524f1d91964a6d4ee1b5b8bf01f542',NULL,'2026-09-13 06:34:21','2026-09-12 23:34:21',0,NULL),(93,'4be33uf8ettsr63b4qclpkqst8','9345c29029eabb157e40ac02bf4fd704f0f92973f0949e30488901d4aa8feb05',NULL,'2026-09-13 06:34:30','2026-09-12 23:34:30',0,NULL),(94,'1256sob56uepgl3ct046rp01gs','05a83ebf1817adb1a59369b8ab4664fbfa7fee0a347bf1421b6bff64a699ff8a',NULL,'2026-09-13 06:34:31','2026-09-12 23:34:31',0,NULL),(95,'4ctb14h22agdsbhvvjj46kbshb','38af890060f7d8871b889d3e0ff41785aca2c7b441d7093b13aa8b100ff6fdd7',NULL,'2026-09-13 06:48:52','2026-09-12 23:48:52',0,NULL),(96,'kosm090ou36p3hllna45joovql','0d1733948754fe3c1d0a751c713e6ed669f4c60f953e388e692b7d98ea0bb5fb',NULL,'2026-09-13 06:49:03','2026-09-12 23:49:03',0,NULL),(97,'6h4mdolfb9323ucqopvi7udbnq','79f36ebcb0b73dbc772d01339d3a5a81dee422fce63baf2a193b1d3b168ebe94',NULL,'2026-09-13 13:55:09','2026-09-13 06:55:09',0,NULL),(98,'6h4mdolfb9323ucqopvi7udbnq','c83a24ff4d6938e8dd9278b62ffba2be5fa62de56a1231e62170df560fb49b5e',NULL,'2026-09-13 16:04:25','2026-09-13 09:04:25',0,NULL),(99,'1ldkfjhghek3g3va28ipoqt0p9','6b5ffadd68712987bcd70661acc17dfe4eae398d32fbc692acbd5fd492fa1666',NULL,'2026-09-13 16:18:00','2026-09-13 09:18:00',0,NULL),(100,'be6cidrcoqpll0mjkjihvbe8l3','419a1156c47823489acdd4054287781bdddf3dafbccb88ab80da889d9524f67f',NULL,'2026-09-13 16:18:00','2026-09-13 09:18:00',0,NULL),(101,'mlucmfikg5gqnmlm7hj8renfhm','5e35f56c4f3505347734d0df6264d2daf128a078ce61e6043261bdf84c43adef',NULL,'2026-09-13 18:54:48','2026-09-13 11:54:48',0,NULL),(102,'mlucmfikg5gqnmlm7hj8renfhm','b19595148a75b2c5692ec1936d89335943dd7df6a652a122045167ca85e23a41',NULL,'2026-09-13 20:48:47','2026-09-13 13:48:47',0,NULL),(103,'mu784d5ag3td12ljqamnoogrer','c9872e9a53a35e16a30dedbf829aff000c48cac12c9b8db9d8139af47b58282e',NULL,'2026-09-14 17:10:08','2026-09-14 10:10:08',0,NULL),(104,'d26cqrtogndc2qrnsvps266bc6','bbe8d76f3b14b586a8fee016ab86e98d8efd46c90d21c475c9fea5f20b817c67',NULL,'2026-09-14 17:10:38','2026-09-14 10:10:38',0,NULL),(105,'tdp5rfcjjf01pdom2de2ka7jr1','df1b43e7543b34d9787d8ad15e6a9334e51f42200d1d3b6b8369d5740125aef3',NULL,'2026-09-14 17:10:45','2026-09-14 10:10:45',0,NULL),(106,'fgmhe7u08v1u0fhnc48d2dnrff','d4ce0f6ebed1250b70287e1c3f156f20a676fad21b62189763c78361e7224843',NULL,'2026-09-14 17:10:57','2026-09-14 10:10:57',0,NULL),(107,'08t9skh7afmuvujct6u6l58q21','29373041cc15c08142d556a2573ea1d0f473a34232e681e3c83dc209102a7532',NULL,'2026-09-14 17:11:07','2026-09-14 10:11:07',0,NULL),(108,'cajdsvmbqre6rph6p4d448jp3k','d333c59b13c754d412be339b690482374adda21998631bd26dd11dd072e97f57',NULL,'2026-09-14 17:11:21','2026-09-14 10:11:21',0,NULL),(109,'o5hqhokoq7aom0uuomiv87vq6t','a99ee4466397b8babb6dace8dad631d7b62b2f3d759bcb28c86a6a87def7394d',NULL,'2026-09-14 17:16:42','2026-09-14 10:16:42',0,NULL),(110,'fm8elfudsmimeqcis4v713h4f2','ca57446ff1e2b015d90688dbd6152fc495179919f92d172ffaba93a3149117f9',NULL,'2026-09-19 10:29:32','2026-09-19 03:29:32',0,NULL),(111,'h9gjevt9mq78hg77d13kbsb8ni','61c96bc25f9d2e0a17215302e7c4939c3113c70f5691c86304278540d0f0dec7',NULL,'2026-09-19 13:47:39','2026-09-19 06:47:39',0,NULL),(112,'h9gjevt9mq78hg77d13kbsb8ni','dba71b23148973d8cf1a803bb4c8c238ed515b2200735e488ee2e63839227d05',NULL,'2026-09-19 15:48:07','2026-09-19 08:48:07',0,NULL),(113,'pdjl59run4cp718oit1i1uoiee','43d0e9483d54a43160442b21b98498a8fbf1e39316b4d414c2abe4c36efe6ff0',NULL,'2026-09-19 16:46:58','2026-09-19 09:46:58',0,NULL),(114,'s5uk29m0hmj8kconobu05difop','c2cec0bf81d9f4b1fbca91105b0232a62ebafa37bc128762a9e98d3cbf25addc',NULL,'2026-09-19 16:54:13','2026-09-19 09:54:13',0,NULL),(115,'558n7dbi8blpqei0gmv9uqcr1v','d9775268d5bd4c65ef1c353f98f18ed104733a2ecb98ab129c83d4a6ba4de623',NULL,'2026-09-19 16:54:22','2026-09-19 09:54:22',0,NULL),(116,'iq2uvhi720njpgr94t2l1lspoe','c0a0f51398ac30676d9a5fc80ed7542e5d4f9e328f6c39554776e623b0dacde1',NULL,'2026-09-19 19:02:03','2026-09-19 12:02:03',0,NULL),(117,'iq2uvhi720njpgr94t2l1lspoe','a388f9946568688b7856c5989a26821e30aa3dfb8b5759f9962560e2e3ac3fe8',NULL,'2026-09-19 19:02:38','2026-09-19 12:02:38',0,NULL),(118,'iq2uvhi720njpgr94t2l1lspoe','849fa41d6ea51b2ccd5148fc131451098e57666a41102352a6b8fff489519dde',NULL,'2026-09-19 19:03:27','2026-09-19 12:03:27',0,NULL),(119,'iq2uvhi720njpgr94t2l1lspoe','832321d8cf0c9d020f6951b1e9eb3b7d5d08ad304fd2e38ba041dd61066b89af',NULL,'2026-09-19 19:04:14','2026-09-19 12:04:14',0,NULL),(120,'iq2uvhi720njpgr94t2l1lspoe','bdf1caf622670b210e07a9a338960dee34e8e003401cc19cc38508d3b7959b07',NULL,'2026-09-19 19:05:20','2026-09-19 12:05:20',0,NULL),(121,'v1rcfv3rmj73vpjseou7ch8o07','0aee3c5e7e2c0835bafdcbad794bb65f1d28a6fea5a3fe5972be989c530e0fec',NULL,'2026-09-19 19:56:00','2026-09-19 12:56:00',0,NULL),(122,'v1rcfv3rmj73vpjseou7ch8o07','701e0b174f782ac8c5cdc685d7e42b984d0886a990f440b588025f043b0e67dc',NULL,'2026-09-19 19:56:03','2026-09-19 12:56:03',0,NULL),(123,'1jpvd6uecoons6fdtbrl5rg7tt','eb16fc442645841b6a5c2dd4031d34f337e3446500d16e60d7760e26f4c3e1b9',NULL,'2026-09-20 13:14:06','2026-09-20 06:14:06',0,NULL),(124,'1jpvd6uecoons6fdtbrl5rg7tt','2345662e26d4958fdb46ec4aa0abe6e8aa30ccd4b7e3d95e86eebe99d412cd69',NULL,'2026-09-20 19:22:21','2026-09-20 12:22:21',0,NULL),(125,'iv816tj72beqmn3u2q8o9f710h','bc801fa5d08cae36bfbf16a60b9c686c361c54eaf6003ca98f0df8d83816a304',NULL,'2026-09-21 13:40:16','2026-09-21 06:40:16',0,NULL),(126,'r71c92of098kbh77mo3ts1usls','5ca44b8b9abf3fad7422a8bf11a125ec91975e916db14d64ce8acaa35b288d58',NULL,'2026-09-21 15:39:22','2026-09-21 08:39:22',0,NULL),(127,'3d063o15aatfl2kp4ibmd5boo9','59d7e6c034bd2b74df9459c9314bc9244f8e891b1c73d691539af16688ad5c00',NULL,'2026-09-21 17:45:30','2026-09-21 10:45:30',0,NULL),(128,'0ls9es2nivumdqpt4mfvi81cn1','9aa5d7a79d5d6edb9b50e13fc29aa505167b139a8cc704c37078445ae2516eee',NULL,'2026-09-21 18:31:42','2026-09-21 11:31:42',0,NULL),(129,'9fan4dgrft4b3t0mgl1ukj7t14','00f580be1d0b9f95a17a7dc83f48997855db9b090376c33cd3c862b93a6f18d5',NULL,'2026-09-21 19:20:02','2026-09-21 12:20:02',0,NULL),(130,'9fan4dgrft4b3t0mgl1ukj7t14','dacb2474bdbc555aa9c3d764bd07fdf19fc843b277724c51b3322e8bc7b97c17',NULL,'2026-09-21 19:20:37','2026-09-21 12:20:37',0,NULL),(131,'0ls9es2nivumdqpt4mfvi81cn1','d17725565070429d22bfea3d913937caf876995dbd6280bb2800b2671cab244d',NULL,'2026-09-21 19:48:34','2026-09-21 12:48:34',0,NULL),(132,'qqtuetdgbcsphmj5sipvp7844k','5d30826bf84e8990d0e7320372a609811026e4ec290f3fe558c4140d3264d74c',NULL,'2026-09-21 19:50:19','2026-09-21 12:50:19',0,NULL),(133,'9t8v0psqs2ong0t6rbqdd9gbum','ebe42bfbd423b0fc2eb0a96dbda65e1933e408bba1ae6356a5e5ec3c3661560a',NULL,'2026-09-21 19:56:21','2026-09-21 12:56:21',0,NULL),(134,'kmavugf8fl91bij7ble0p26c39','e9653e946dbd45c7c5aceb34e3cacf14723bbe3c255b9f236e446360dc8a2e7a',NULL,'2026-09-21 19:57:24','2026-09-21 12:57:24',0,NULL),(135,'u1uglqvhouo7uiq1bi99t0h0m3','98023f7e53ec63fc1b7d7217c430b73c324ea06298abe8efb725a2f7f9424fda',NULL,'2026-09-21 19:57:35','2026-09-21 12:57:35',0,NULL),(136,'u1uglqvhouo7uiq1bi99t0h0m3','2785841687fdc966d9585b9e6adef2407572a589ade6d4a81ea402a8c603ecf8',NULL,'2026-09-21 19:58:11','2026-09-21 12:58:11',0,NULL),(137,'u1uglqvhouo7uiq1bi99t0h0m3','dbeb992b54165c9346b5a18f4c8b5532df8ef97c4a3ad661afa1112cd59647db',NULL,'2026-09-21 19:59:03','2026-09-21 12:59:03',0,NULL),(138,'u1uglqvhouo7uiq1bi99t0h0m3','bfd993dc5a5bb659080724a78ca742458ee360c55235193c1cb34e4c3d4bf3a3',NULL,'2026-09-21 20:00:17','2026-09-21 13:00:17',0,NULL),(139,'bpksklqp3d82c89ni7kcfm07vv','2dc6897760004b87fd86a634729e7a097763c600c67d20640c25b3b213dc3906',NULL,'2026-09-23 16:23:54','2026-09-23 09:23:54',0,NULL),(140,'acf9j5bgpgchgkf3d93a8blkco','e7d72612bdf1767c66aef20ce7f9217effa4ccbb0cdb62ec856e45214acc2f7f',NULL,'2026-10-01 21:28:53','2026-10-01 14:28:53',0,NULL),(141,'e428ivcmhf9acvn90467m7nn0i','da5f574cd8fceabb9fc0a21e0fcab862c95ff6ee2bfe3c5dd3aa3805dfb0c97f',NULL,'2026-10-01 21:35:04','2026-10-01 14:35:04',0,NULL),(142,'','c707aabc8233de386984fa1a92fec304ca9fc85c0c2c0bba33dba701f722164e',NULL,'2026-10-01 21:43:51','2026-10-01 14:43:51',0,NULL),(143,'','3de5ed116bc9799f1fa7a575296fccdec7affc695cea1b8b02cd80a60df03b04',NULL,'2026-10-01 21:43:51','2026-10-01 14:43:51',0,NULL),(144,'eka1a838skl05ove4sa7l7g2dc','0116780c5a1fab028a7a8d6d1aa90de6a5887931421e6f656fb13033b0d07d6f',NULL,'2026-10-01 21:44:16','2026-10-01 14:44:16',0,NULL),(145,'a9l4sdbggqc1u2rmjo6ocem617','67c4206178ba2414f52fea66f1f0c472a667ed4ce9cb50def5987edb03ac3047',NULL,'2026-10-01 21:44:16','2026-10-01 14:44:16',0,NULL),(146,'3inlacocf6njue9p05andu9ufj','912d110d7be3f41726c54a2c905711d0a58a0e164db0f27d6fee0f25da284145',NULL,'2026-10-01 22:11:17','2026-10-01 15:11:17',0,NULL),(147,'3inlacocf6njue9p05andu9ufj','c1891d82cd9e1aee9846a46cafbcd6cf82b440cbffb2de57e4ce94db49623084',NULL,'2026-10-01 22:11:44','2026-10-01 15:11:44',0,NULL),(148,'3inlacocf6njue9p05andu9ufj','a57a2784744eb322f8a7f5805c408c9cc07b653dbda4c793ec604653266df808',NULL,'2026-10-01 22:11:56','2026-10-01 15:11:56',0,NULL),(149,'3inlacocf6njue9p05andu9ufj','8e774f77d571cb198eafb23c4de0623ff6ce1c957b4f44c01f8100946562aaf8',NULL,'2026-10-01 22:12:19','2026-10-01 15:12:19',0,NULL),(150,'3inlacocf6njue9p05andu9ufj','3cf38e6712ac05d0f81ae07c365338fb958935a21adca6ff20e3a44a165f0bd7',NULL,'2026-10-01 22:12:27','2026-10-01 15:12:27',0,NULL),(151,'3inlacocf6njue9p05andu9ufj','391bad06be4552b33fdd90298fb701b1c10a29e9c385d4e6ba231462c148c4ad',NULL,'2026-10-01 22:12:56','2026-10-01 15:12:56',0,NULL),(152,'3inlacocf6njue9p05andu9ufj','8b5d1c214837fa5e5561f8b98263154cf1a29fe395e539d1d6f7e5f36f6ffd0e',NULL,'2026-10-01 22:13:32','2026-10-01 15:13:32',0,NULL),(153,'3inlacocf6njue9p05andu9ufj','1059ef1014793f7dac0e9557635508298bb0f697d40cb9732f91f8a5c10be0dd',NULL,'2026-10-01 22:13:58','2026-10-01 15:13:58',0,NULL);
/*!40000 ALTER TABLE `csrf_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `db_version`
--

DROP TABLE IF EXISTS `db_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `db_version` (
  `version` int NOT NULL,
  `applied_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `db_version`
--

LOCK TABLES `db_version` WRITE;
/*!40000 ALTER TABLE `db_version` DISABLE KEYS */;
INSERT INTO `db_version` VALUES (58,'2026-08-12 17:46:26'),(59,'2026-08-12 17:46:37'),(60,'2026-08-12 17:46:37'),(61,'2026-08-12 17:46:37'),(62,'2026-08-12 17:46:37'),(63,'2026-09-20 19:22:21'),(64,'2026-09-20 19:22:21'),(65,'2026-09-21 16:30:36');
/*!40000 ALTER TABLE `db_version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dlp_settings`
--

DROP TABLE IF EXISTS `dlp_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dlp_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dlp_settings`
--

LOCK TABLES `dlp_settings` WRITE;
/*!40000 ALTER TABLE `dlp_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `dlp_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `education_history`
--

DROP TABLE IF EXISTS `education_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `education_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `previous_school` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade_level` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year_attended` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `education_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `education_history`
--

LOCK TABLES `education_history` WRITE;
/*!40000 ALTER TABLE `education_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `education_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `encryption_audit`
--

DROP TABLE IF EXISTS `encryption_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `encryption_audit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_id` int NOT NULL,
  `field_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` enum('encrypted','decrypted') COLLATE utf8mb4_unicode_ci NOT NULL,
  `performed_by` int DEFAULT NULL,
  `performed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `performed_by` (`performed_by`),
  KEY `idx_table_record` (`table_name`,`record_id`),
  KEY `idx_performed_at` (`performed_at`),
  CONSTRAINT `encryption_audit_ibfk_1` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `encryption_audit`
--

LOCK TABLES `encryption_audit` WRITE;
/*!40000 ALTER TABLE `encryption_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `encryption_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enrollment_documents`
--

DROP TABLE IF EXISTS `enrollment_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enrollment_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `enrollment_id` int NOT NULL,
  `document_type` enum('psa_birth_cert','pwd_id','medical_record','beef_form') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `reviewed_by` int DEFAULT NULL,
  `review_note` text COLLATE utf8mb4_unicode_ci,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviewed_by` (`reviewed_by`),
  KEY `idx_enrollment_id` (`enrollment_id`),
  KEY `idx_status` (`status`),
  KEY `idx_document_type` (`document_type`),
  CONSTRAINT `enrollment_documents_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollment_submissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enrollment_documents_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enrollment_documents`
--

LOCK TABLES `enrollment_documents` WRITE;
/*!40000 ALTER TABLE `enrollment_documents` DISABLE KEYS */;
INSERT INTO `enrollment_documents` VALUES (1,3,'psa_birth_cert','uploads/enrollment/psa_birth_cert_3_1788887673_6aa042795a6f6.pdf','approved',6,NULL,'2026-09-08 17:14:33','2026-09-08 17:53:44');
/*!40000 ALTER TABLE `enrollment_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enrollment_submissions`
--

DROP TABLE IF EXISTS `enrollment_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enrollment_submissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `target_school_id` int DEFAULT NULL,
  `assigned_teacher_id` int DEFAULT NULL,
  `enrollment_type` enum('new','transfer','returning') COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_year` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `previous_enrollment_id` int DEFAULT NULL,
  `is_draft` tinyint(1) DEFAULT '1',
  `status` enum('draft','pending','verified','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `lrn` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension_name` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date NOT NULL,
  `sex` enum('Male','Female') COLLATE utf8mb4_unicode_ci NOT NULL,
  `age` int DEFAULT NULL,
  `birth_place` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_tongue` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_indigenous_people` tinyint(1) DEFAULT '0',
  `indigenous_group` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_4ps_beneficiary` tinyint(1) DEFAULT '0',
  `fourps_household_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disability_visual` tinyint(1) DEFAULT '0',
  `disability_hearing` tinyint(1) DEFAULT '0',
  `disability_learning` tinyint(1) DEFAULT '0',
  `disability_speech` tinyint(1) DEFAULT '0',
  `disability_intellectual` tinyint(1) DEFAULT '0',
  `disability_physical` tinyint(1) DEFAULT '0',
  `disability_emotional` tinyint(1) DEFAULT '0',
  `disability_chronic_illness` tinyint(1) DEFAULT '0',
  `disability_others` tinyint(1) DEFAULT '0',
  `disability_others_specify` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_house_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_barangay` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_province` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_zip_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `same_as_current_address` tinyint(1) DEFAULT '0',
  `permanent_house_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permanent_barangay` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permanent_city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permanent_province` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permanent_zip_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_maiden_last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_school_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_school_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_school_address` text COLLATE utf8mb4_unicode_ci,
  `previous_grade_level` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_school_year` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_school_type` enum('Public','Private') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade_level_to_enroll` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_balik_aral` tinyint(1) DEFAULT '0',
  `is_pept_passer` tinyint(1) DEFAULT '0',
  `pept_rating` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_als_passer` tinyint(1) DEFAULT '0',
  `als_rating` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shs_track` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shs_strand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shs_semester` enum('1st','2nd') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modality_modular_print` tinyint(1) DEFAULT '0',
  `modality_modular_digital` tinyint(1) DEFAULT '0',
  `modality_online` tinyint(1) DEFAULT '0',
  `modality_educational_tv` tinyint(1) DEFAULT '0',
  `modality_radio` tinyint(1) DEFAULT '0',
  `modality_blended` tinyint(1) DEFAULT '0',
  `modality_face_to_face` tinyint(1) DEFAULT '0',
  `preferred_distance_modality` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `survey_has_internet` tinyint(1) DEFAULT '0',
  `survey_devices` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `survey_willing_online` tinyint(1) DEFAULT '0',
  `learning_track` enum('unassigned','lms','traditional') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unassigned',
  `signature_data` text COLLATE utf8mb4_unicode_ci,
  `date_signed` date DEFAULT NULL,
  `draft_saved_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `verified_by` int DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `review_note` text COLLATE utf8mb4_unicode_ci,
  `learner_account_created` tinyint(1) DEFAULT '0',
  `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `has_device` tinyint(1) NOT NULL DEFAULT '0',
  `willing_digital` tinyint(1) NOT NULL DEFAULT '0',
  `section_id` int DEFAULT NULL,
  `lms_track` enum('unset','lms_candidate','f2f_learner') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unset',
  `lms_invite_status` enum('none','sent','accepted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `lms_invite_sent_at` timestamp NULL DEFAULT NULL,
  `lms_invite_accepted_at` timestamp NULL DEFAULT NULL,
  `lms_credentials_generated_at` timestamp NULL DEFAULT NULL,
  `learner_user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `verified_by` (`verified_by`),
  KEY `previous_enrollment_id` (`previous_enrollment_id`),
  KEY `idx_status` (`status`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_target_school_id` (`target_school_id`),
  KEY `idx_assigned_teacher_id` (`assigned_teacher_id`),
  KEY `idx_enrollment_type` (`enrollment_type`),
  KEY `idx_school_year` (`school_year`),
  CONSTRAINT `enrollment_submissions_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enrollment_submissions_ibfk_2` FOREIGN KEY (`target_school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `enrollment_submissions_ibfk_3` FOREIGN KEY (`assigned_teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `enrollment_submissions_ibfk_4` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `enrollment_submissions_ibfk_5` FOREIGN KEY (`previous_enrollment_id`) REFERENCES `enrollment_submissions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enrollment_submissions`
--

LOCK TABLES `enrollment_submissions` WRITE;
/*!40000 ALTER TABLE `enrollment_submissions` DISABLE KEYS */;
INSERT INTO `enrollment_submissions` VALUES (3,8,1,6,'new','2026-2027',NULL,0,'verified','','TEST','ONE',NULL,'','2014-02-09','Female',12,'Cebu','',0,'',0,'',0,1,0,0,0,0,0,0,0,'','Don Lorenzo','Toril','Davao City','Davao del Sur','8000',1,'Don Lorenzo','Toril','Davao City','Davao del Sur','8000','TEST','PAPA',NULL,'0944322445','TEST','MAMA',NULL,'09332983752',NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'SPED Program',0,0,'',0,'','','','',0,1,1,0,0,0,0,'',1,'Smartphone,Tablet,Laptop/PC',1,'lms','data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAc8AAAB4CAYAAACUwHlSAAAQAElEQVR4AeydB9z1RJXG864ootgbduwNUdeyiyCi4lrWVbGvuooNsbdVsIKi6FpRwYKuiuKuCOqKa0MUVOyAHazYG1awAsrn8w933ju35aZMkpnk3N85d06SKWeemcxJJpOTf8jsZwgYAoaAIWAIGAKVEDDjWQkui2wILCKwsbjL9hgChsDAETDjGUMDmw7RIFDHEG6JRntTxBBYjUCdvr06txaOLCi4sKOFQutnWdF4xl2Z+jBYSkPgPATMEJ6Hg/0PD4Ho+/aCggs7omqUisYz7srUQ9YuCOrhNrhUViFDwBAwBEojUNF4ls43oYhDvCBICP6oVE3gQioBFaNq0nXKGJ7rELLjKxAw47kCmPh3+2e9L8evebwaJnAhlYCKxe27pq8WJw5/dB2ekakbHgDLsS4CZjzrIhcgXbPz0j/rfTmAYpaFIdAaAon11cTUba3ZLOMFBMx4LkDS3Y54zsvqZrx6iu5wtZIMAUOgEQIlE497FDDjWbKbDDtadTNePcWwEbTaGQJlECg0N4UHy+TedZxxjwJmPLvubyMor8Ux4KaCb/8JH6cQfpXCQ8Ts31fh1cVGhkCUCBSam8KDUVanpFItjgglNWgjWkPjuR6UNpS2PONGIMAYcH3VcB/xMeLviP8sJtsTFe434d0Uwo/Lso1HZ1nG/hcq/K6YuL9UeLj4JmIjQyA9BCbD6yRIT/9NjTkdNzcGIzQynhtZASjpt/hgGjmRitxJen5I/Dfx18UvEt9efA3xBcUFtLQfXloJHiA+Wfw58X3Ejfq70rdMdtK0DHBa2U+69SRIS/cRaNtoMCls1MKDI0A2qipGqQzG7RnS7KNiessHFN5B7PfJP2r7k+L3iw8UP098G/GlxFiaaylkmzvMj0vmrlPBAt1Ce44Qf158mHg7cYQEDBGqlbJK9JKU9Tfdo0XAH6iiVdIU6wGBJoPO6rQYO6ZXT1WNfiZ+gfi2YuhH+uPZ5X8o3FV8PvG2YuS7KHymmLTHK/yNGGJKl23SMIV7Te28jvju4peITxP7xDPTB2nHaVLxKQrJX4HRIBFQIxdNjg2yzlapzhAw49kZ1CUL4oQvGbVCtKsqLs8PuTN7qeT11OQmaDbtDVXYAeKviL8lZmHPdRVuJf6G+GDxLcVXET9WzF0kd5vnSq5DlPFeJXya+NrincRvEPPcVEFO20hFcMC4cve7Tb635T/LvmME1MirSmznNFtVmu1vC4E+29GMZ1utWjffghO+bpZKx4Ianh+yEvWh2m6bmFZ9hAr5shij+SyFGFEF2cf093TxDcTXEz9O/BlxG8Tz088q473ElxMzdXuGQkeXkcDdL3fBu0sW9Xk6qnijThBo5zTrRPWKhQy7P/fZjmY8K3bFRKOvWXATrFYYxNcoN6ZlD1W4o5j+/R6FGLArKbydmMVApyjskn6vwu4nvoL4kWLubhXkdDH9f0Ssu+It3BFLNBomAmOrFaff2OrcTX3HYzyHfQG2rrf8yYtwCU8OJd5bGX1K/DXxo8Tc0bGNfBFt30PM1OlPFPZNYIFh5078vlKGV1oU5MQzVVbmXjLfsj9DwBAIhMDwBuDxGM9xX4D9s3cG8BqIt1lb5A6NZ5R/VQ7vFPPckgU8z5XM9PAuCl8nZsWsgujoLGmE3jdX+Amxo3+UwFQvocT+aXjDTv+YmgZdIzA7AHddehvl5cbTTs42oA2ZZ9AW+ktDzZjifILy+L741WJWxX5T4T3FO4g19Zl9T2Eq9AMpynTuUQodsSr4WG3wPFZBv9TKsBO0S/WLj5VuCPSBQG48Wzk5+6jNYMsM2kK8rlEHKV7rwMD8XIkPEl9ezDbPNVk9+25tczenIDliwRAGlDtlt8qX6W1c/7HAiQuE5CpVqHDQLlVYkh2MCgG7agrVHLnxDJVZL/lYoWUQ4PUNP972/sYaGfd2b1ac08XcXTLs8poH71PyrPOr2j8EYmUuz2hx1PAlr0I4YaD+3q70RRtC02/DejXg9K2X0lLNItCJ8bQTdRb0HrbmF8DwKkmRGtx1sSIWo4t7uz0VmenelyvEaD5VIc83FQyOmK7Fld/bVDN3F4oTBqaotWsYZEPoMNrRatEfAp0Yz5UnagWrWiFqf2jGW/LOc6qxSGZuV76JM4U3ScJ5AI7ZefbHe5p7Cn+mafHKgycgRZmhoW18WxV6mJj3Uc9WCLE4iosIZONYEFDHjEWVvvQwCPpBvhPjOVs1r6lXWtXZFGxViEr0CuzpUyFVYlHnXxE5c05/FgE9X/t41eQhCnm++RaF/yq+sfgw4Z/q80ypX4vOUaqXiXGiwIpiiRnPQJ2zB7YHwIn3f3XMATRCoyrED0HifWxF6/RgPONq6sIvw6wArdTuuPrLF+Z0vqy3zR0VDgvwHYszBRbNsKgII4qz9rgazFN8lRgQep6D8rzTeUDinVWmdfFWtKr4/vbXKjl08wZEv1Z9LFF8CITuY3HUsAfjGUfFnRatNWtrGTvNK4WskPUT8JkvHLLzignP8vC6w2rZmykSi2aYqpWYJgWGnmnqfYXEH8QQFx7/g2C8DIHA6C8rwvYZAhEgMHrjGUEbFKgQ7CreXz1KeayS5VNgOE7nA9M4T2clLb5oOW48i8CntckiIgU58cz44rlkf4bALAKltoKd2aVKm4vUa+FzuiS8WdJ4Jox2wqpnWbCr+PlpW6Yd+awXXxTBcOJRJ+Fu3InqH1Qp3J0ryJi+fTyCsSFQB4G6Z3aZ4WxtnLqF16nogNOUNJ7tor22sZs0QLuqN9Gsy7QsfvntXIF8aeWF2ucWw0g0WoMA3pN+MYnznwr5jJqCJtRq72+i2MjTxtkuZYazMnGSbNwqTdJBBUsaz3Y1KW7syBBrF4rQuW+tDB8jZjqWdzd9qPfQfqNqCOAQAgf3XHBw94kBrZbDQmy/SeYOWtefA6TLzYJ26VINK2uKQGRNEoXxnKKzTIoMsWUqxrmP55l8CuxgqbedGCfovs9ZnnEOz+2cKtoy8U1QjCjF4DyBCxTk8GxdPzymlqMhEAiBBIxnoJrWzibChMV3JBeQxkeKWTF7J4XfFd9ZzOe3/lehI76Kcn+34cLirF2sUYenqvYsslKQsWiIT5tlo/m11kFay7izpkm/Bp1BNYiCzHim2Iyr70jwCHS0qnQvMbHwkIM7vQ9rO8s2sgOyLPO/X/lEbc+c8yTSPqPVCPDu56+9wziS8DYjEWdaNaBOrXWQ1jIOWPnirNKvQXH97OgsAmY8Z/FIeQunBiepAjg255udN5KMf1oGe4miLRlegvjepjZy4puVywf/tgbfvNj1f/PFr0/RaYwfeqXx2o+3GYloI3kkDdFcjf7Phf41aI5i+BzMeAbAtOeudVFV4SNifNIyFXugZAwnTt0lLtDh2vM7sSNc0DHV67bPC3sefOsV31lLcHFyHk5ZhmvDC7uNzbAzVTZLNGGgCNQ7F0KC0b8GIWsTKq8kjGfs41CPXetB6gh83WR3hThCuLtC3OxN7za1Y47O0DYGVkFOLCx6RS4l/9dZS3xCUPENUAUZFyzc7SNPuTNVpkW2L1kJnSAQ+4DXCQjxF5KE8Sw/Do2q12HwDlMX4/Nib1XI9OsxCssQK3B9l32888miojJpe4wTVfvyysqfJmAkgN1EUwviR6D8gBd/XQasYRLGszz+o+h1lxEe3Pmw2IdnmLiNe7D2/VRclv6siBhb7kIl5vQO/bO4SEGsFFX7niCULiSGWKCFU31kY0OgVQQs8zgQiMp4RnVf0bB9FuqysKNWAbxuwvTsrZSaz4fhY/VdksvRrA4nK9EBYke89H+cNi4tNlqPwPGKwgItLDrPPTGg2mU0SARmz51BVtEqVQ0BGc81vWLN4WrFFcdmFCqOkc7Rhbos7KhcF1bOcnfIF1BY9INPWveyfrnMFnV4uRIy/asgp8vrn/dD+eqKRKMCBHB5CFbuDNm7IK4dSh2BxXMn9RqZ/o0QyDIZzzW9Ys3hhuVb8vUI4AXoLYq2j5hVsvdQuKfYfSJLYm2idZ+s1K8XO8KA/r82riw2KkaANnGvreysqDuIjQyB5ghsNM/CcmgXARnPdguw3BshsK1Sv0/MM00ckt9aMi73ilbTKkpl4q7pVV6q60rmk2WRPwOVlv0SDif8b3virq9fjaz0YSDAZe0wajLYWpjx7L9pV2iwwbuXh+ggLvZwCcc0LdOE2tUKPVW5vlTsCG9F+MNlNa/bN9Kw8DaAWQH32sr9RgqQVdsQGA8Ck+EgSeM50X3aWAs7pocSlbbKsi1vk+68x3m6QlbU+k7dtSs4na0cnyZ+vtjRjhI+Lub7nwrGSHSuwtuAbwoV99rPVbJsY/Gdz8x+6xEA5/WxLIYh0DsCk+EgSeM50X2K4cKO6aEEpa2l89vFGExeP7mFZFbWKmidQHI/lcJ3PhXkdAP981oGzhQktku9DaErqwUkKw+6Ay92QpZt8RdgTXf3KcUH6hI0SuG8JJ3tMgT6QSBJ49kPVJ2V+kaV5AznrpJ/IK5MDcbLc1UYXor811iuqX3/J8aQKmiPEh1CP+MhwnNiLni8XT2LiYJaA7WLZhvZfykdjzvuqtAoJAINBpWQasSSlxnPWFoiy3jGieefB0qlX4lvI+ZzYgqqU8PxkuTPUakMRDhUkJhdT38fEg/Um06jkYELHKbZBQ8r2DO+ZoNsXA2BprF3yrZkPHrAYxb9t2l+SaRv1HOr1JBRoUp8xe1MN5XVNZnx7Brx1eUdpEOPEZ8p3kW8yrG7DnVGGAFejfnxpMQrKXy/GMcM51O4SemfJDVGhs3a5wIen7joYQMfw7QhsrEh0CoCjXtui9rFrFvTao/EeEY/tHOH9yg15k/Ee4hZhKKgK1qJD33f3W1+ztMGg8oK4Ku6fUR0cohwpUYhMm8nj98oW559/kUhhAMKQuPuEGD6nDZ4jYp8ntioDgKWZiUC/rgUj/H0tVqpet0DoYf2unosTcfrIEw1Mfhyx/KxpbFa3bkWHzwZ4RLwkVLDReZVltO0fTdxcHKFBM+43Qzx/HTKpAhcJ9rK2wkYHQXM2uC4ghmcozsqM1wxrY6B4dQcc07+uBSP8fS1Gk/rPE5V5WPKvCbCs8QTtR0r4Y7uUCl3MzHOARTkz/dYSMSVPtudcMRjDO978oUbd/e5byeAWCHDQKBgDIy4zw8D+xq1aNl4Fmk0+u6wv9BxXn0eJtmfFtVmtIRDee46/St7ppyPlMbbiFungjGm9bJLFPAmxXEXQbtJ5l1ZBUaGQH0EIu/z9SuWcMoejeeou8ND1Gf2EzNVe3uFTPcpSIb4lBnPPZkic64C+aoILv02n4MmU5uwiv5e2XFhwSs/EjPamdDYEDAEBoRAj8ZzQChWq8pTFJ27Ez6kjC/UY7XdKrWUOUaTxRm4D+RZE8XgQvDLEm4sHjMxvc3iFTDgfUNWKSMbGwKGQMQIVJkP7dh4VlEtYoTrq4a7PfzHYngeqmw+IO+XggAAEABJREFUIE6dPqIK8FqGc1HHty35KgtfGdGhURJ35m9QzZle2Uohz7YVGBkChkDMCHDCltWvY+NZRbWyVegqXmPDj9cgvAexOAgH4kd0pXndcqY1nkor8nKrcZ1Thysq3gnifxevoLV5rkiXzG6msL890XavLMsuOJEHHQy+VQfdela5sgjQzysZTxKUzXx48RoZfp5rMlULLCyuOQohDLfXKtMaT6UCnb+jY/8k/pTYEV53uGBY0s9K5enySTHEscQHJ4pfXCHtrmDYNPhWHXbzWe1KIkA/XzKorU5NgtVH7cgKBFhAwwISVqLiMswZ0RXRq+6OqlV+Le1vJ+YzXQoyvBCxkvjD2mCFroJR0ctU2++LId7lPT9CWryRbQRSOFQ+TdSxtIZAKAQqGc9KhaZ4poTX+dLCjGeCTNnxpZIXaXvodJYqyPPc+yp0K053l4y7Qdz9YVC12Q6Fb8Lqeno6/Eip3y2GttMfq6wVpERbslCXZ6HyiQk9r61jUst0CYJAceu2ZzxTPFPC64zB5I4LL0JjchcGkqrzxjXUh31Xgwdqm7vwbRW2QhTcSsYVMp3TgRXJJ02SP1dhgnef0tpoKQJzbb00TjI7i21FwGqkklVx67ZnPFPBpz09WRT0cGWPv1rc2rFQSJtjoi1MWd5INcZoKMgJT0qflXQF8RjoF6qkm6rn7pOpe+0yGisC0dqoLWNtkXr1NuNZD7d1qTAMuKzjhXn81f5uXYIBH2cad3/Vj2eh7nUWvguKD99LaP8YiIVTvL5CXe2dT1AYMZuNqt74/VxwFJda03gWZ1odmtopYk34AimGYThM4RfFRlmGscRR+tcmYFxH4SvFYyAuovg6DXU1d32gYDwsBFo2Cf1ccBSXWtN4Fmc6rF5RuTa3Voo9xV8X45oNhwgSjbKN7CtCgdd28EIkMcPD0mMR0uHaowRT1VQT43k1hEKuXUxhrnZwKQIG9lJYquwcoUmoaTyroJp23IqnFatqX68a05X4Rie+a7U5cCpbPVDJMqZu91ISd1HxEsk8F1WQAp1XieqabjhvUngcwkVjcRZ1iynOtd7RiidBvUL6TBUT2H3iYGVXQcCM5xq03GmVjx/5X2GCx+so05HvU/h2sZGHgAff57X7ADHEBQfu/HAkwPZAeQuv6uAwgvqxkGx7hCTYnQRJKGtKGgLrEPBGonVRC46b8SwAxz+Ujx/5n793Rr6Mtp4h5luOT1Do3nGUaAQCc/BhPPkWKIdYRHOMhKH3R5xHMBuxter6JHFIsrwMAUOgFAJzI1GpNIuRhj5YLda4vT1M0+IU/RUqglc0FBgVIMDFBQ7T3YKqmysuTvMVRE71L1xxXei+28rz3gtUrGk6d6sVK2bRDYHUEDDjGabFWASD9xje6eSl+DC5Dj8X/L/eUdV078CyGre+aVJGnVCzC1c8Tp0jPVmNTd0lrqWbKgauD7+n8M3iXin+BuoRnpiKTrah0lDcjGfzzs4U3MHKhiGVlaP13ulMo7+omsHpdOWIEwUM6PUl7yZOi6q13cmqnPMy9CzJZegQRbqkGGIlN84WkDtnqkpH77xgK7A6Am02FB2hukYlU7SpeEkVSkQz41kCpDVRGPivrTi8x+ie4WmzIqXRXypWiuilzrK3KibGU0H2xCy1X7W2c6/pUEumqtdNxeJggq/VEL93rlbV3tVNXIFS504/dYy7I3SCiRnPZjBfWckZ7Hl+x0pbbRrNIlDqLGP69gilI/JdFeITV0G81GBYY2biIK9m3El6mzMihpN3hf2dvPLC6z7+PpMHiQCnwyArNohKmfFs1ozPVHKmbQ9XeErWYERV+rETK1F/OwHhnpMw2qDhsOavtMU43ntJRVm9/eAl+1+7ZJ/tihABGw5WNMpAgDHjuaJ93e6C8FY6hsN3Xj14ueQs2Leb8sxG94f3Iec4Yedkal9/IOBiwVXzdRJYeaxgk/5F0vZzF2Q8IuCdWB0qolVKrdpflJcdq4tAwwususXGn24gwJjxrNfVWPDxqklSfLX6z7Emuy2oiAD+X92imHQ8DtUfCPCs5CCi3vSnE7VjbzF3oniq8i/IeAWKaVwdXkerlFq1f11+dtwQMATmETDjOY9Iue2HKdqNxX8QM3WrwKghAhdWevehbD4irk1HgwxPUa24FTxeoSNeSWFa9p3aAR4KNsk5lt/cYYIhYAj0h4AZz+rYX0hJ3B3ARyU7h98SlxDD45LdtmsBAd89X1XnAZPMkgT7NlL+aHERHauDx8xN4WpXi5QklC3iYVkbAnMImPGcA6TE5j6KczkxqyZfrfCv4tWU0ExZz+Plrh6INVVJCGyvshLvJmYGgy/xSNwk7kqZwsUJhz+FuxmhNWEJlK2VZRkbAlEjsHw4MuO5otGWw5VdV9H3FUOf1B+sYBjU83j5xzkUx9Y3D1T9dxDT9bgbdXyU9uXEgVwo9Vctdqksk49kmCTfhL1UYPnIOIIBqt4JsxyujI83M6V4ptrQf7Ffm0YNEcDTkMuCfulP47r9Ywm544Rn6ruiT87EmW5Uiz1NN2RpCJgMuX3SqhuDVFoaV9Y22AnD6xO8PoAG39Tfe8RG4RBgGtzlRr+8lNsYY1jvki8lpIZfw5Raw3StjgCDVPVU40zBO3bUnBW2z5bg3kmUaBQAgfm+OOrRNdglX4CGaSeL4dewHdws1xgQQIf5AYt9JXlUY9uOAuV2YohFHV9AMK6KgN9nfDnPZ5v8f/o3/wx0eqRHaUHroLq0m3tQVS0zQ2DkCDQwnqO6cnR3nXSXY/SHVyEFRtUQ8PuML+e5+I4RztCen4qjowWtg2rYbu5BVbXMDIHYEChz7VkmTsl6NTCeJUtIP9q1VIW7iyE+J/UOhGBsGTkEbukEhSeIzZIIBKN4EAg47sZTqSFpUmbEKBOnJCbxGM94eyZfTQHOs/THy+p4hpFoFBiBf/PyA2dv08TRIBDvOGCuq0fTCctVNLDxbNDzA14RlKt6qVi4SHOfjPqJUrxPbBQegV2UJV8RUZBT167o8kLtLwIE4hwHIgDGVKiOQAN7VKKw5cazdpmD6/l8Ggt3fHyv8zPC83Nio/AI7ORlyWrmb3jbJhoChoAhUAOBdu3RcuPZbpk1QOgtifue4tnSgE9mnaNwShUuMipEneY/HumBXlX5uoi3aeJoELCKGgJLEYhz9FxuPJdWYEg7SzXGdqrxbcXQn/W3OGVb4SKjQlQVNSrC+QSvAlHpH+qPL4ooMOobgVJnSd9KWvmbCAy3veqPnkExmcssXuOZK5r/bXaOcEKpxtjDK4/vLJ7qbZsYDoFHTbLirp4PPdu3USeA9B2UOkv6VtLK30QgUHtt5jcEISgmc5lFZzw3zWWuaP7XVxvewyv4JE82MRwC3HE+YJLd+RV+X2xkCDRCYHMMaZSLJTYEihGIznj2ai6nWF1M4q3EED5XD0VInuMaVS4oPI8UO8Jf8EvdhoWGQF0EuhlDOjqZOiqmLtZjTteq8UwYWKZst57o/yeFw7gj6mZUEVyl6DWKdW0xhCu+u0qorWGZMaZMHOlgZAiUQKB2Vy2Rtxelo2K8Ek0siYAZz+VA+VO271WUaRce9AjcWeWeJEwfIob+or9DxN8S16ZpA63Ookyc1antyBgQ6OwMGAOYA6+jGc/FBubZGx8idkfe5YQ8TG4EzrUu+ddJ5a4qZV4mdnSchAPERoZA7wh0cgYsq6VZ7WWoRL3PjOdi8/DC/raT3dwVfXIiW9AcAeG6wasobqj4rbLcW4xjBAVGzRBwsDbLJVzq2PQJV7PgOfVmtYPXZDQZmvFcbOp5H6s4SFiMZXvqIKA7zi238BLeTTLvdioYNnVTu9hG4Nj06aYVrJSOEejpGs2M52I739HbxXuH3qaJDRB4ltLuJXb0WAl2Vy8Q4qGeRqF4ADBNUkSgp2s0M56zneUm2txB7MgclDskmoX4CN7PywJcWW3r7TKxfwR6GoU6q7gVZAiEQ8CM5yyWu3qbv5b8A/ESsiv0JaCs2sWq2qN0cCsx9DH93V9sI7VAGDzZqTL4Jk6/gvU6qRnP2Za/hLf5dU+eE4c+7tfrTHMgsckq2jchTPiLCh8vZqGQguFRMOQCQdO7PkM/VQK105iyia+uVTrp9Iwy4znbktf3Nj/lySMTq3SmpdDcSHuZmuU5p8Sc+MzYoyUVXJToaOLUGLnA9Y9Nn8DVs+wMgY4RmJ5RZjxnob+htznoQd6rZ0gRr0zPVYZfEt9BDP1Nf4eLmRL/rEIjQ8AQMARmEZje0M3uj3grbeMZFlia7+pelqd5sonrEeCunQuO53hRfyWZFbZ7Kvyl2MgQMAQMgUUEpjd0i8ci3WPGc9ow55MIK8jpjPzf/tYhAGbPVKSTxdcQO3q1BO7keebJ3ac2KxKXMxWTWHRDwBAwBLpAwIznFOW/SuROSUFO2+f/9pcjsMSOXVwHeKb5U4XPFzNlqyBjMdCdJbAw6OcK61OCV6P1K2spDYFACCw5WQPlbNl4CJjx9MCQeKLY0c2cYGGWeXYMI/l0YcKXZlhNe1nJEF9GweH75bXxQbGRIVCAgI3wBeA0O+SdrM0yGkPq+v3QjOds/zjB2+QTWd7m6EUc5t9HKJwqPlDMN08VZBjNN0rA4ftBCs8SG3WNQHLl2QifXJMNUuH6/XBQxrP+NcRmr/jappRlV/HkMYvbqPKPFGM0j1B4NbGj90vYWfwIMU4lFBjVQiBA561VriVqhIA1WyP4kk48KONZ/xpisw39r3uU8Ls66FOHz7L9t5A5Xfw6sb8Y6GhtYzTvovDLYqOmCATovE1VsPTVEZhrtuoZWIpkEejOeKZnZ/zFQysaeDCnDq2DMcSV3htU2Z+JcaP3UIXbiqFz9fdWMUaVr6F8WrKRIWAIGAKjRKA745mGnfmO1wtu6slDFC+iSrEi9i0KMYw87+W1kodrezuxo69KeLb4yuIHi48XTwibOxEjD9LRNHIgTb0BIZD6WbFC/45aqDvj2VGFGhbzY6V3L/NjPPfXdhjqr51vrgpwB/kChceJvyA+W3ym+JViDKKCTeL91ndpax8xK453VMirKLySItEnXRH1Vy9fkbWyNF0bxyIYAuNCoOFZ0fu531D/ho0dpfHsuU0e42G6nyc3E9tvZ6ZX7yUlDxUfKcZAUurnJfPs8hkKdxNjEFk5KzF/A4XXc96sDe5C+SQbzvHJ58Xad5K4mCihOIYd7RCBns+dDmtqRfWOwMjP/d6MZ9FJ3nObYHiYysyyLO+ePPt8UC71+8erIHtLBYz7fRWy0vXdCvHeA2S/l4zurHzF+DkDqd054Trvo5Iwijg3uKNkVs66O1M8AuGTlrx0yChFBKzxUmw10zlFBHoznpGf5Cyc+c2kQS+l8DAxq2+vo7Ap4c4O44Yh5L3JFylDpkVZgINBdHeMQOQzTgleq7gHi98hxovPHlm2Md+GTK8em2XZC8VPFrPAB29AO0jeXUzU6mIAAACWSURBVMx0LFO4H5a84nulOmJUiEDRxV9hQjtoCBgCg0BgfuAdRKUCVYKvguCvFWNGlrvoj89qYdD4LiVu6JDP0X7CsowbQO4QMYS8N4kxwzfsTsoHg7h5x1gwQJPHxxVfvOUVCplexjDybuoVtX17MdO0HGOBD88xtStN6lLrAsxn1KCxZ3bYRkcIlG2hjtSxYkaLwN8BAAD///kzw1MAAAAGSURBVAMApuhCqRTWmmQAAAAASUVORK5CYII=','2026-09-08',NULL,'2026-09-08 09:14:33',6,'2026-09-08 17:53:53',NULL,1,'2026-09-10 20:04:53','2026-09-08 16:11:15','2026-09-10 20:04:53',1,1,NULL,'','none',NULL,NULL,NULL,10),(4,11,1,6,'new','2026-2027',NULL,1,'verified','129688260004','2','TEST',NULL,NULL,'2014-05-14','Male',12,NULL,NULL,0,NULL,0,NULL,0,1,0,0,0,0,0,0,0,NULL,'12','Crossing Bayabas','Davao City','Davao del Sur',NULL,0,NULL,NULL,NULL,NULL,NULL,'Santos','Father of TEST',NULL,NULL,'Santos','Maria',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',0,0,NULL,0,NULL,NULL,NULL,NULL,0,0,1,0,0,0,1,'online',1,'Tablet, Smartphone',1,'lms',NULL,'2026-09-13',NULL,'2026-09-13 06:45:59',6,'2026-09-13 06:45:59',NULL,1,'2026-09-13 06:45:59','2026-09-13 06:45:59','2026-09-13 06:45:59',1,1,3,'','none',NULL,NULL,NULL,12),(5,13,1,6,'new','2026-2027',NULL,1,'verified','129688260005','3','TEST',NULL,NULL,'2014-08-22','Female',12,NULL,NULL,0,NULL,0,NULL,0,1,0,0,0,0,0,0,0,NULL,'12','Crossing Bayabas','Davao City','Davao del Sur',NULL,0,NULL,NULL,NULL,NULL,NULL,'Dela Cruz','Father of TEST',NULL,NULL,'Dela Cruz','Roberto',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',0,0,NULL,0,NULL,NULL,NULL,NULL,0,0,1,0,0,0,1,'online',1,'Tablet, Smartphone',1,'lms',NULL,'2026-09-13',NULL,'2026-09-13 06:47:14',6,'2026-09-13 06:47:14',NULL,1,'2026-09-13 06:47:14','2026-09-13 06:47:14','2026-09-13 06:47:14',1,1,3,'','none',NULL,NULL,NULL,14),(6,15,1,6,'new','2026-2027',NULL,1,'verified','129688260006','4','TEST',NULL,NULL,'2015-01-10','Male',11,NULL,NULL,0,NULL,0,NULL,0,1,0,0,0,0,0,0,0,NULL,'12','Crossing Bayabas','Davao City','Davao del Sur',NULL,0,NULL,NULL,NULL,NULL,NULL,'Alcantara','Father of TEST',NULL,NULL,'Alcantara','Grace',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',0,0,NULL,0,NULL,NULL,NULL,NULL,0,0,1,0,0,0,1,'online',1,'Tablet, Smartphone',1,'lms',NULL,'2026-09-13',NULL,'2026-09-13 06:47:21',6,'2026-09-13 06:47:21',NULL,1,'2026-09-13 06:47:21','2026-09-13 06:47:21','2026-09-13 06:47:21',1,1,3,'','none',NULL,NULL,NULL,16),(7,17,1,6,'new','2026-2027',NULL,1,'verified','129688260007','5','TEST',NULL,NULL,'2015-03-30','Female',11,NULL,NULL,0,NULL,0,NULL,0,1,0,0,0,0,0,0,0,NULL,'12','Crossing Bayabas','Davao City','Davao del Sur',NULL,0,NULL,NULL,NULL,NULL,NULL,'Reyes','Father of TEST',NULL,NULL,'Reyes','Danilo',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'',0,0,NULL,0,NULL,NULL,NULL,NULL,0,0,1,0,0,0,1,'online',1,'Tablet, Smartphone',1,'lms',NULL,'2026-09-13',NULL,'2026-09-13 06:47:29',6,'2026-09-13 06:47:29',NULL,1,'2026-09-13 06:47:29','2026-09-13 06:47:29','2026-09-13 06:47:29',1,1,3,'','none',NULL,NULL,NULL,18),(10,NULL,1,NULL,'new','2026-2027',NULL,0,'verified','999999999999','Test','ClaimChild',NULL,NULL,'2018-05-10','Male',NULL,NULL,NULL,0,NULL,0,NULL,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Parent Test',NULL,'09123456789',NULL,NULL,NULL,NULL,NULL,NULL,'Grade 1',0,0,NULL,0,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,NULL,0,NULL,0,'unassigned',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-09-21 16:36:33','2026-09-21 16:36:33','2026-09-21 16:36:33',0,0,NULL,'unset','none',NULL,NULL,NULL,NULL),(13,NULL,1,6,'new','2026-2027',NULL,0,'verified','129688260008','6','TEST',NULL,NULL,'2018-06-22','Male',NULL,NULL,NULL,0,NULL,0,NULL,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Parent Six',NULL,'09171110006',NULL,NULL,NULL,NULL,NULL,NULL,'Grade 1',0,0,NULL,0,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,NULL,0,NULL,0,'traditional',NULL,NULL,NULL,'2026-09-21 17:46:17',6,'2026-09-21 17:46:17',NULL,0,'2026-09-21 17:46:17','2026-09-21 17:46:17','2026-09-21 17:46:17',0,0,NULL,'unset','none',NULL,NULL,NULL,NULL),(14,NULL,1,6,'new','2026-2027',NULL,0,'verified','129688260009','7','TEST',NULL,NULL,'2018-07-14','Female',NULL,NULL,NULL,0,NULL,0,NULL,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Parent Seven',NULL,'09171110007',NULL,NULL,NULL,NULL,NULL,NULL,'Grade 1',0,0,NULL,0,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,NULL,0,NULL,0,'traditional',NULL,NULL,NULL,'2026-09-21 17:46:17',6,'2026-09-21 17:46:17',NULL,0,'2026-09-21 17:46:17','2026-09-21 17:46:17','2026-09-21 17:46:17',0,0,NULL,'unset','none',NULL,NULL,NULL,NULL),(15,NULL,1,6,'new','2026-2027',NULL,0,'verified','129688260010','8','TEST',NULL,NULL,'2018-08-09','Male',NULL,NULL,NULL,0,NULL,0,NULL,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Parent Eight',NULL,'09171110008',NULL,NULL,NULL,NULL,NULL,NULL,'Grade 1',0,0,NULL,0,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,NULL,0,NULL,0,'traditional',NULL,NULL,NULL,'2026-09-21 17:46:17',6,'2026-09-21 17:46:17',NULL,0,'2026-09-21 17:46:17','2026-09-21 17:46:17','2026-09-21 17:46:17',0,0,NULL,'unset','none',NULL,NULL,NULL,NULL),(16,NULL,1,6,'new','2026-2027',NULL,0,'verified','129688260011','9','TEST',NULL,NULL,'2018-09-01','Female',NULL,NULL,NULL,0,NULL,0,NULL,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Parent Nine',NULL,'09171110009',NULL,NULL,NULL,NULL,NULL,NULL,'Grade 1',0,0,NULL,0,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,NULL,0,NULL,0,'traditional',NULL,NULL,NULL,'2026-09-21 17:46:17',6,'2026-09-21 17:46:17',NULL,0,'2026-09-21 17:46:17','2026-09-21 17:46:17','2026-09-21 17:46:17',0,0,NULL,'unset','none',NULL,NULL,NULL,NULL),(17,21,1,6,'new','2026-2027',NULL,0,'verified','129688260012','10','TEST',NULL,NULL,'2018-10-30','Male',NULL,NULL,NULL,0,NULL,0,NULL,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Parent Ten',NULL,'09171110010',NULL,NULL,NULL,NULL,NULL,NULL,'Grade 1',0,0,NULL,0,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0,NULL,0,NULL,0,'traditional',NULL,NULL,NULL,'2026-09-21 17:46:17',6,'2026-09-21 17:46:17',NULL,1,'2026-09-21 21:26:59','2026-09-21 17:46:17','2026-09-21 21:26:59',0,0,NULL,'unset','none',NULL,NULL,NULL,23);
/*!40000 ALTER TABLE `enrollment_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fsl_vocabulary`
--

DROP TABLE IF EXISTS `fsl_vocabulary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fsl_vocabulary` (
  `id` int NOT NULL AUTO_INCREMENT,
  `word` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'General',
  `video_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gif_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `word` (`word`),
  KEY `category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fsl_vocabulary`
--

LOCK TABLES `fsl_vocabulary` WRITE;
/*!40000 ALTER TABLE `fsl_vocabulary` DISABLE KEYS */;
INSERT INTO `fsl_vocabulary` VALUES (1,'enrollment','School Terms',NULL,'images/fsl/enrollment.svg',NULL,'Registration or entering into a school program','2026-08-27 11:00:35'),(2,'teacher','School Terms',NULL,'images/fsl/teacher.svg',NULL,'Guro o magtutudlo sa eskwelahan','2026-08-27 11:00:35'),(3,'student','School Terms',NULL,'images/fsl/student.svg',NULL,'Tinun-an o mag-aaral','2026-08-27 11:00:35'),(4,'school','School Terms',NULL,'images/fsl/school.svg',NULL,'Tunghaan o eskwelahan','2026-08-27 11:00:35'),(5,'family','Daily Living',NULL,'images/fsl/family.svg',NULL,'Pamilya o panimalay','2026-08-27 11:00:35'),(6,'help','Daily Living',NULL,'images/fsl/help.svg',NULL,'Tabang o pagtabang','2026-08-27 11:00:35'),(7,'friend','Socio-Emotional',NULL,'images/fsl/friend.svg',NULL,'Higala o barkada','2026-08-27 11:00:35'),(8,'read','Learning',NULL,'images/fsl/read.svg',NULL,'Pagbasa sa libro o teksto','2026-08-27 11:00:35'),(9,'write','Learning',NULL,'images/fsl/write.svg',NULL,'Pagsulat gamit ang lapis o papel','2026-08-27 11:00:35'),(10,'thank you','Social Courtesy','videos/fsl/7.mov','images/fsl/thank_you.svg',NULL,'Salamat o pagpasalamat','2026-08-27 11:00:35'),(11,'listen','Sensory & Comms',NULL,'images/fsl/listen.svg',NULL,'Paminaw o pagtagad','2026-08-27 11:00:35'),(12,'sign','Sign Language',NULL,'images/fsl/sign.svg',NULL,'Pagsenyas o Filipino Sign Language (FSL)','2026-08-27 11:00:35'),(13,'good morning','Greeting','videos/fsl/0.mov',NULL,NULL,'Magandang Umaga - Pagbati tuwing umaga','2026-09-10 21:02:40'),(14,'magandang umaga','Greeting','videos/fsl/0.mov',NULL,NULL,'Magandang Umaga - Pagbati tuwing umaga','2026-09-10 21:02:40'),(15,'good afternoon','Greeting','videos/fsl/1.mov',NULL,NULL,'Magandang Hapon - Pagbati tuwing hapon','2026-09-10 21:02:40'),(16,'magandang hapon','Greeting','videos/fsl/1.mov',NULL,NULL,'Magandang Hapon - Pagbati tuwing hapon','2026-09-10 21:02:40'),(17,'good evening','Greeting','videos/fsl/2.mov',NULL,NULL,'Magandang Gabi - Pagbati tuwing gabi','2026-09-10 21:02:40'),(18,'magandang gabi','Greeting','videos/fsl/2.mov',NULL,NULL,'Magandang Gabi - Pagbati tuwing gabi','2026-09-10 21:02:40'),(19,'hello','Greeting','videos/fsl/3.mov',NULL,NULL,'Kumusta / Hello - Magiliw na pagbati','2026-09-10 21:02:40'),(20,'kumusta','Greeting','videos/fsl/3.mov',NULL,NULL,'Kumusta / Hello - Magiliw na pagbati','2026-09-10 21:02:40'),(21,'how are you','Greeting','videos/fsl/4.mov',NULL,NULL,'Kumusta ka? - Pangangamusta sa kalagayan','2026-09-10 21:02:40'),(22,'kumusta ka?','Greeting','videos/fsl/4.mov',NULL,NULL,'Kumusta ka? - Pangangamusta sa kalagayan','2026-09-10 21:02:40'),(23,'im fine','Greeting','videos/fsl/5.mov',NULL,NULL,'Mabuti naman ako - Pagsasabing maayos ang lagay','2026-09-10 21:02:40'),(24,'mabuti naman ako','Greeting','videos/fsl/5.mov',NULL,NULL,'Mabuti naman ako - Pagsasabing maayos ang lagay','2026-09-10 21:02:40'),(25,'nice to meet you','Greeting','videos/fsl/6.mov',NULL,NULL,'Ikinagagalak kitang makilala - Pagbati sa bagong kaibigan','2026-09-10 21:02:40'),(26,'ikinagagalak kitang makilala','Greeting','videos/fsl/6.mov',NULL,NULL,'Ikinagagalak kitang makilala - Pagbati sa bagong kaibigan','2026-09-10 21:02:40'),(27,'thank you','Greeting','videos/fsl/7.mov',NULL,NULL,'Salamat - Pagpapakita ng pasasalamat','2026-09-10 21:02:40'),(28,'salamat','Greeting','videos/fsl/7.mov',NULL,NULL,'Salamat - Pagpapakita ng pasasalamat','2026-09-10 21:02:40'),(29,'youre welcome','Greeting','videos/fsl/8.mov',NULL,NULL,'Walang anuman - Tugon sa pasasalamat','2026-09-10 21:02:40'),(30,'walang anuman','Greeting','videos/fsl/8.mov',NULL,NULL,'Walang anuman - Tugon sa pasasalamat','2026-09-10 21:02:40'),(31,'see you tomorrow','Greeting','videos/fsl/9.mov',NULL,NULL,'Magkita tayo bukas - Pamamaalam','2026-09-10 21:02:40'),(32,'magkita tayo bukas','Greeting','videos/fsl/9.mov',NULL,NULL,'Magkita tayo bukas - Pamamaalam','2026-09-10 21:02:40'),(33,'understand','Survival','videos/fsl/10.mov',NULL,NULL,'Naiintindihan - Naunawaan ang aralin','2026-09-10 21:02:40'),(34,'naiintindihan','Survival','videos/fsl/10.mov',NULL,NULL,'Naiintindihan - Naunawaan ang aralin','2026-09-10 21:02:40'),(35,'don\'t understand','Survival','videos/fsl/11.mov',NULL,NULL,'Hindi Naiintindihan - Kailangan ng tulong sa aralin','2026-09-10 21:02:40'),(36,'hindi naiintindihan','Survival','videos/fsl/11.mov',NULL,NULL,'Hindi Naiintindihan - Kailangan ng tulong sa aralin','2026-09-10 21:02:40'),(37,'know','Survival','videos/fsl/12.mov',NULL,NULL,'Alam ko - May kaalaman sa bagay','2026-09-10 21:02:40'),(38,'alam ko','Survival','videos/fsl/12.mov',NULL,NULL,'Alam ko - May kaalaman sa bagay','2026-09-10 21:02:40'),(39,'don\'t know','Survival','videos/fsl/13.mov',NULL,NULL,'Hindi ko alam - Hindi pa nalalaman','2026-09-10 21:02:40'),(40,'hindi ko alam','Survival','videos/fsl/13.mov',NULL,NULL,'Hindi ko alam - Hindi pa nalalaman','2026-09-10 21:02:40'),(41,'no','Survival','videos/fsl/14.mov',NULL,NULL,'Hindi - Pagtanggi o hindi pagsang-ayon','2026-09-10 21:02:40'),(42,'hindi','Survival','videos/fsl/14.mov',NULL,NULL,'Hindi - Pagtanggi o hindi pagsang-ayon','2026-09-10 21:02:40'),(43,'yes','Survival','videos/fsl/15.mov',NULL,NULL,'Oo - Pagsang-ayon','2026-09-10 21:02:40'),(44,'oo','Survival','videos/fsl/15.mov',NULL,NULL,'Oo - Pagsang-ayon','2026-09-10 21:02:40'),(45,'wrong','Survival','videos/fsl/16.mov',NULL,NULL,'Mali - Hindi wasto','2026-09-10 21:02:40'),(46,'mali','Survival','videos/fsl/16.mov',NULL,NULL,'Mali - Hindi wasto','2026-09-10 21:02:40'),(47,'correct','Survival','videos/fsl/17.mov',NULL,NULL,'Tama - Wasto at tama','2026-09-10 21:02:40'),(48,'tama','Survival','videos/fsl/17.mov',NULL,NULL,'Tama - Wasto at tama','2026-09-10 21:02:40'),(49,'slow','Survival','videos/fsl/18.mov',NULL,NULL,'Mabagal / Dahan-dahan - Mababang bilis','2026-09-10 21:02:40'),(50,'mabagal','Survival','videos/fsl/18.mov',NULL,NULL,'Mabagal / Dahan-dahan - Mababang bilis','2026-09-10 21:02:40'),(51,'fast','Survival','videos/fsl/19.mov',NULL,NULL,'Mabilis - Mataas na bilis','2026-09-10 21:02:40'),(52,'mabilis','Survival','videos/fsl/19.mov',NULL,NULL,'Mabilis - Mataas na bilis','2026-09-10 21:02:40'),(53,'one','Number','videos/fsl/20.mov',NULL,NULL,'Isa (1) - Bilang 1','2026-09-10 21:02:40'),(54,'isa','Number','videos/fsl/20.mov',NULL,NULL,'Isa (1) - Bilang 1','2026-09-10 21:02:40'),(55,'two','Number','videos/fsl/21.mov',NULL,NULL,'Dalawa (2) - Bilang 2','2026-09-10 21:02:40'),(56,'dalawa','Number','videos/fsl/21.mov',NULL,NULL,'Dalawa (2) - Bilang 2','2026-09-10 21:02:40'),(57,'three','Number','videos/fsl/22.mov',NULL,NULL,'Tatlo (3) - Bilang 3','2026-09-10 21:02:40'),(58,'tatlo','Number','videos/fsl/22.mov',NULL,NULL,'Tatlo (3) - Bilang 3','2026-09-10 21:02:40'),(59,'four','Number','videos/fsl/23.mov',NULL,NULL,'Apat (4) - Bilang 4','2026-09-10 21:02:40'),(60,'apat','Number','videos/fsl/23.mov',NULL,NULL,'Apat (4) - Bilang 4','2026-09-10 21:02:40'),(61,'five','Number','videos/fsl/24.mov',NULL,NULL,'Lima (5) - Bilang 5','2026-09-10 21:02:40'),(62,'lima','Number','videos/fsl/24.mov',NULL,NULL,'Lima (5) - Bilang 5','2026-09-10 21:02:40'),(63,'six','Number','videos/fsl/25.mov',NULL,NULL,'Anim (6) - Bilang 6','2026-09-10 21:02:40'),(64,'anim','Number','videos/fsl/25.mov',NULL,NULL,'Anim (6) - Bilang 6','2026-09-10 21:02:40'),(65,'seven','Number','videos/fsl/26.mov',NULL,NULL,'Pito (7) - Bilang 7','2026-09-10 21:02:40'),(66,'pito','Number','videos/fsl/26.mov',NULL,NULL,'Pito (7) - Bilang 7','2026-09-10 21:02:40'),(67,'eight','Number','videos/fsl/27.mov',NULL,NULL,'Walo (8) - Bilang 8','2026-09-10 21:02:40'),(68,'walo','Number','videos/fsl/27.mov',NULL,NULL,'Walo (8) - Bilang 8','2026-09-10 21:02:40'),(69,'nine','Number','videos/fsl/28.mov',NULL,NULL,'Siyam (9) - Bilang 9','2026-09-10 21:02:40'),(70,'siyam','Number','videos/fsl/28.mov',NULL,NULL,'Siyam (9) - Bilang 9','2026-09-10 21:02:40'),(71,'ten','Number','videos/fsl/29.mov',NULL,NULL,'Sampu (10) - Bilang 10','2026-09-10 21:02:40'),(72,'sampu','Number','videos/fsl/29.mov',NULL,NULL,'Sampu (10) - Bilang 10','2026-09-10 21:02:40'),(73,'january','Calendar','videos/fsl/30.mov',NULL,NULL,'January - Senyas para sa January','2026-09-10 21:02:40'),(74,'february','Calendar','videos/fsl/31.mov',NULL,NULL,'February - Senyas para sa February','2026-09-10 21:02:40'),(75,'march','Calendar','videos/fsl/32.mov',NULL,NULL,'March - Senyas para sa March','2026-09-10 21:02:40'),(76,'april','Calendar','videos/fsl/33.mov',NULL,NULL,'April - Senyas para sa April','2026-09-10 21:02:40'),(77,'may','Calendar','videos/fsl/34.mov',NULL,NULL,'May - Senyas para sa May','2026-09-10 21:02:40'),(78,'june','Calendar','videos/fsl/35.mov',NULL,NULL,'June - Senyas para sa June','2026-09-10 21:02:40'),(79,'july','Calendar','videos/fsl/36.mov',NULL,NULL,'July - Senyas para sa July','2026-09-10 21:02:40'),(80,'august','Calendar','videos/fsl/37.mov',NULL,NULL,'August - Senyas para sa August','2026-09-10 21:02:40'),(81,'september','Calendar','videos/fsl/38.mov',NULL,NULL,'September - Senyas para sa September','2026-09-10 21:02:40'),(82,'october','Calendar','videos/fsl/39.mov',NULL,NULL,'October - Senyas para sa October','2026-09-10 21:02:40'),(83,'november','Calendar','videos/fsl/40.mov',NULL,NULL,'November - Senyas para sa November','2026-09-10 21:02:40'),(84,'december','Calendar','videos/fsl/41.mov',NULL,NULL,'December - Senyas para sa December','2026-09-10 21:02:40'),(85,'monday','Days','videos/fsl/42.mov',NULL,NULL,'Monday - Senyas para sa Monday','2026-09-10 21:02:40'),(86,'tuesday','Days','videos/fsl/43.mov',NULL,NULL,'Tuesday - Senyas para sa Tuesday','2026-09-10 21:02:40'),(87,'wednesday','Days','videos/fsl/44.mov',NULL,NULL,'Wednesday - Senyas para sa Wednesday','2026-09-10 21:02:40'),(88,'thursday','Days','videos/fsl/45.mov',NULL,NULL,'Thursday - Senyas para sa Thursday','2026-09-10 21:02:40'),(89,'friday','Days','videos/fsl/46.mov',NULL,NULL,'Friday - Senyas para sa Friday','2026-09-10 21:02:41'),(90,'saturday','Days','videos/fsl/47.mov',NULL,NULL,'Saturday - Senyas para sa Saturday','2026-09-10 21:02:41'),(91,'sunday','Days','videos/fsl/48.mov',NULL,NULL,'Sunday - Senyas para sa Sunday','2026-09-10 21:02:41'),(92,'today','Days','videos/fsl/49.mov',NULL,NULL,'Today - Senyas para sa Today','2026-09-10 21:02:41'),(93,'tomorrow','Days','videos/fsl/50.mov',NULL,NULL,'Tomorrow - Senyas para sa Tomorrow','2026-09-10 21:02:41'),(94,'yesterday','Days','videos/fsl/51.mov',NULL,NULL,'Yesterday - Senyas para sa Yesterday','2026-09-10 21:02:41'),(95,'father','Family','videos/fsl/52.mov',NULL,NULL,'Tatay / Ama - Magulang na lalaki','2026-09-10 21:02:41'),(96,'tatay','Family','videos/fsl/52.mov',NULL,NULL,'Tatay / Ama - Magulang na lalaki','2026-09-10 21:02:41'),(97,'mother','Family','videos/fsl/53.mov',NULL,NULL,'Nanay / Ina - Magulang na babae','2026-09-10 21:02:41'),(98,'nanay','Family','videos/fsl/53.mov',NULL,NULL,'Nanay / Ina - Magulang na babae','2026-09-10 21:02:41'),(99,'son','Family','videos/fsl/54.mov',NULL,NULL,'Anak na Lalaki - Lalaking supling','2026-09-10 21:02:41'),(100,'anak na lalaki','Family','videos/fsl/54.mov',NULL,NULL,'Anak na Lalaki - Lalaking supling','2026-09-10 21:02:41'),(101,'daughter','Family','videos/fsl/55.mov',NULL,NULL,'Anak na Babae - Babaeng supling','2026-09-10 21:02:41'),(102,'anak na babae','Family','videos/fsl/55.mov',NULL,NULL,'Anak na Babae - Babaeng supling','2026-09-10 21:02:41'),(103,'grandfather','Family','videos/fsl/56.mov',NULL,NULL,'Lolo - Matandang lolo','2026-09-10 21:02:41'),(104,'lolo','Family','videos/fsl/56.mov',NULL,NULL,'Lolo - Matandang lolo','2026-09-10 21:02:41'),(105,'grandmother','Family','videos/fsl/57.mov',NULL,NULL,'Lola - Matandang lola','2026-09-10 21:02:41'),(106,'lola','Family','videos/fsl/57.mov',NULL,NULL,'Lola - Matandang lola','2026-09-10 21:02:41'),(107,'uncle','Family','videos/fsl/58.mov',NULL,NULL,'Uncle - Senyas para sa Uncle','2026-09-10 21:02:41'),(108,'auntie','Family','videos/fsl/59.mov',NULL,NULL,'Auntie - Senyas para sa Auntie','2026-09-10 21:02:41'),(109,'cousin','Family','videos/fsl/60.mov',NULL,NULL,'Cousin - Senyas para sa Cousin','2026-09-10 21:02:41'),(110,'parents','Family','videos/fsl/61.mov',NULL,NULL,'Mga Magulang - Nanay at Tatay','2026-09-10 21:02:41'),(111,'mga magulang','Family','videos/fsl/61.mov',NULL,NULL,'Mga Magulang - Nanay at Tatay','2026-09-10 21:02:41'),(112,'boy','Relationships','videos/fsl/62.mov',NULL,NULL,'Batang Lalaki - Lalaki','2026-09-10 21:02:41'),(113,'batang lalaki','Relationships','videos/fsl/62.mov',NULL,NULL,'Batang Lalaki - Lalaki','2026-09-10 21:02:41'),(114,'girl','Relationships','videos/fsl/63.mov',NULL,NULL,'Batang Babae - Babae','2026-09-10 21:02:41'),(115,'batang babae','Relationships','videos/fsl/63.mov',NULL,NULL,'Batang Babae - Babae','2026-09-10 21:02:41'),(116,'man','Relationships','videos/fsl/64.mov',NULL,NULL,'Man - Senyas para sa Man','2026-09-10 21:02:41'),(117,'woman','Relationships','videos/fsl/65.mov',NULL,NULL,'Woman - Senyas para sa Woman','2026-09-10 21:02:41'),(118,'deaf','Relationships','videos/fsl/66.mov',NULL,NULL,'Bingi (Deaf) - Kapatid sa komunidad ng Deaf','2026-09-10 21:02:41'),(119,'bingi','Relationships','videos/fsl/66.mov',NULL,NULL,'Bingi (Deaf) - Kapatid sa komunidad ng Deaf','2026-09-10 21:02:41'),(120,'hard of hearing','Relationships','videos/fsl/67.mov',NULL,NULL,'Hard of Hearing - May bahagyang pandinig','2026-09-10 21:02:41'),(121,'wheelchair person','Relationships','videos/fsl/68.mov',NULL,NULL,'Wheelchair Person - Senyas para sa Wheelchair Person','2026-09-10 21:02:41'),(122,'blind','Relationships','videos/fsl/69.mov',NULL,NULL,'Blind - Senyas para sa Blind','2026-09-10 21:02:41'),(123,'deaf blind','Relationships','videos/fsl/70.mov',NULL,NULL,'Deaf Blind - Senyas para sa Deaf Blind','2026-09-10 21:02:41'),(124,'married','Relationships','videos/fsl/71.mov',NULL,NULL,'Married - Senyas para sa Married','2026-09-10 21:02:41'),(125,'blue','Color','videos/fsl/72.mov',NULL,NULL,'Asul (Blue) - Kulay asul','2026-09-10 21:02:41'),(126,'asul','Color','videos/fsl/72.mov',NULL,NULL,'Asul (Blue) - Kulay asul','2026-09-10 21:02:41'),(127,'green','Color','videos/fsl/73.mov',NULL,NULL,'Berde (Green) - Kulay berde','2026-09-10 21:02:41'),(128,'berde','Color','videos/fsl/73.mov',NULL,NULL,'Berde (Green) - Kulay berde','2026-09-10 21:02:41'),(129,'red','Color','videos/fsl/74.mov',NULL,NULL,'Pula (Red) - Kulay pula','2026-09-10 21:02:41'),(130,'pula','Color','videos/fsl/74.mov',NULL,NULL,'Pula (Red) - Kulay pula','2026-09-10 21:02:41'),(131,'brown','Color','videos/fsl/75.mov',NULL,NULL,'Brown - Senyas para sa Brown','2026-09-10 21:02:41'),(132,'black','Color','videos/fsl/76.mov',NULL,NULL,'Black - Senyas para sa Black','2026-09-10 21:02:41'),(133,'white','Color','videos/fsl/77.mov',NULL,NULL,'White - Senyas para sa White','2026-09-10 21:02:41'),(134,'yellow','Color','videos/fsl/78.mov',NULL,NULL,'Dilaw (Yellow) - Kulay dilaw','2026-09-10 21:02:41'),(135,'dilaw','Color','videos/fsl/78.mov',NULL,NULL,'Dilaw (Yellow) - Kulay dilaw','2026-09-10 21:02:41'),(136,'orange','Color','videos/fsl/79.mov',NULL,NULL,'Orange - Senyas para sa Orange','2026-09-10 21:02:41'),(137,'gray','Color','videos/fsl/80.mov',NULL,NULL,'Gray - Senyas para sa Gray','2026-09-10 21:02:41'),(138,'pink','Color','videos/fsl/81.mov',NULL,NULL,'Pink - Senyas para sa Pink','2026-09-10 21:02:41'),(139,'violet','Color','videos/fsl/82.mov',NULL,NULL,'Violet - Senyas para sa Violet','2026-09-10 21:02:41'),(140,'light','Color','videos/fsl/83.mov',NULL,NULL,'Light - Senyas para sa Light','2026-09-10 21:02:41'),(141,'dark','Color','videos/fsl/84.mov',NULL,NULL,'Dark - Senyas para sa Dark','2026-09-10 21:02:41'),(142,'bread','Food','videos/fsl/85.mov',NULL,NULL,'Tinapay - Pagkain','2026-09-10 21:02:41'),(143,'tinapay','Food','videos/fsl/85.mov',NULL,NULL,'Tinapay - Pagkain','2026-09-10 21:02:41'),(144,'egg','Food','videos/fsl/86.mov',NULL,NULL,'Itlog - Pagkain','2026-09-10 21:02:41'),(145,'itlog','Food','videos/fsl/86.mov',NULL,NULL,'Itlog - Pagkain','2026-09-10 21:02:41'),(146,'fish','Food','videos/fsl/87.mov',NULL,NULL,'Isda - Pagkain','2026-09-10 21:02:41'),(147,'isda','Food','videos/fsl/87.mov',NULL,NULL,'Isda - Pagkain','2026-09-10 21:02:41'),(148,'meat','Food','videos/fsl/88.mov',NULL,NULL,'Meat - Senyas para sa Meat','2026-09-10 21:02:41'),(149,'chicken','Food','videos/fsl/89.mov',NULL,NULL,'Chicken - Senyas para sa Chicken','2026-09-10 21:02:41'),(150,'spaghetti','Food','videos/fsl/90.mov',NULL,NULL,'Spaghetti - Senyas para sa Spaghetti','2026-09-10 21:02:41'),(151,'rice','Food','videos/fsl/91.mov',NULL,NULL,'Kanin - Pangunahing pagkain','2026-09-10 21:02:41'),(152,'kanin','Food','videos/fsl/91.mov',NULL,NULL,'Kanin - Pangunahing pagkain','2026-09-10 21:02:41'),(153,'longanisa','Food','videos/fsl/92.mov',NULL,NULL,'Longanisa - Senyas para sa Longanisa','2026-09-10 21:02:41'),(154,'shrimp','Food','videos/fsl/93.mov',NULL,NULL,'Shrimp - Senyas para sa Shrimp','2026-09-10 21:02:41'),(155,'crab','Food','videos/fsl/94.mov',NULL,NULL,'Crab - Senyas para sa Crab','2026-09-10 21:02:41'),(156,'hot','Drink','videos/fsl/95.mov',NULL,NULL,'Hot - Senyas para sa Hot','2026-09-10 21:02:41'),(157,'cold','Drink','videos/fsl/96.mov',NULL,NULL,'Cold - Senyas para sa Cold','2026-09-10 21:02:41'),(158,'juice','Drink','videos/fsl/97.mov',NULL,NULL,'Juice - Senyas para sa Juice','2026-09-10 21:02:41'),(159,'milk','Drink','videos/fsl/98.mov',NULL,NULL,'Gatas - Masustansyang inumin','2026-09-10 21:02:41'),(160,'gatas','Drink','videos/fsl/98.mov',NULL,NULL,'Gatas - Masustansyang inumin','2026-09-10 21:02:41'),(161,'coffee','Drink','videos/fsl/99.mov',NULL,NULL,'Coffee - Senyas para sa Coffee','2026-09-10 21:02:41'),(162,'tea','Drink','videos/fsl/100.mov',NULL,NULL,'Tea - Senyas para sa Tea','2026-09-10 21:02:41'),(163,'beer','Drink','videos/fsl/101.mov',NULL,NULL,'Beer - Senyas para sa Beer','2026-09-10 21:02:41'),(164,'wine','Drink','videos/fsl/102.mov',NULL,NULL,'Wine - Senyas para sa Wine','2026-09-10 21:02:41'),(165,'sugar','Drink','videos/fsl/103.mov',NULL,NULL,'Sugar - Senyas para sa Sugar','2026-09-10 21:02:41'),(166,'no sugar','Drink','videos/fsl/104.mov',NULL,NULL,'No Sugar - Senyas para sa No Sugar','2026-09-10 21:02:41');
/*!40000 ALTER TABLE `fsl_vocabulary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `general_teacher_assignments`
--

DROP TABLE IF EXISTS `general_teacher_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `general_teacher_assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `general_teacher_id` int NOT NULL,
  `assigned_by` int NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assignment` (`student_id`,`general_teacher_id`),
  KEY `general_teacher_id` (`general_teacher_id`),
  KEY `assigned_by` (`assigned_by`),
  CONSTRAINT `general_teacher_assignments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `general_teacher_assignments_ibfk_2` FOREIGN KEY (`general_teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `general_teacher_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `general_teacher_assignments`
--

LOCK TABLES `general_teacher_assignments` WRITE;
/*!40000 ALTER TABLE `general_teacher_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `general_teacher_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grade_entries`
--

DROP TABLE IF EXISTS `grade_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grade_entries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `quarter` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domain` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` enum('auto','manual') COLLATE utf8mb4_unicode_ci NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `recorded_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_quarter_domain_src` (`student_id`,`quarter`,`domain`,`source`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `grade_entries_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grade_entries_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grade_entries`
--

LOCK TABLES `grade_entries` WRITE;
/*!40000 ALTER TABLE `grade_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `grade_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_audit_log`
--

DROP TABLE IF EXISTS `iep_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_audit_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_type` enum('p2','p3') COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_id` int NOT NULL,
  `user_id` int NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_document` (`document_type`,`document_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `iep_audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_audit_log`
--

LOCK TABLES `iep_audit_log` WRITE;
/*!40000 ALTER TABLE `iep_audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `iep_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_copies`
--

DROP TABLE IF EXISTS `iep_copies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_copies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `iep_id` int NOT NULL,
  `sent_to` int NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `viewed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_iep_id` (`iep_id`),
  KEY `idx_sent_to` (`sent_to`),
  CONSTRAINT `iep_copies_ibfk_1` FOREIGN KEY (`iep_id`) REFERENCES `iep_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iep_copies_ibfk_2` FOREIGN KEY (`sent_to`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_copies`
--

LOCK TABLES `iep_copies` WRITE;
/*!40000 ALTER TABLE `iep_copies` DISABLE KEYS */;
INSERT INTO `iep_copies` VALUES (1,2,8,'2026-09-08 18:29:38',NULL),(2,2,1,'2026-09-08 18:29:38',NULL);
/*!40000 ALTER TABLE `iep_copies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_core`
--

DROP TABLE IF EXISTS `iep_core`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_core` (
  `id` int NOT NULL AUTO_INCREMENT,
  `iep_id` int NOT NULL,
  `developmental_domain` text COLLATE utf8mb4_unicode_ci,
  `priority_needs` text COLLATE utf8mb4_unicode_ci,
  `terminal_objectives` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_iep_core` (`iep_id`),
  CONSTRAINT `iep_core_ibfk_1` FOREIGN KEY (`iep_id`) REFERENCES `iep_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_core`
--

LOCK TABLES `iep_core` WRITE;
/*!40000 ALTER TABLE `iep_core` DISABLE KEYS */;
INSERT INTO `iep_core` VALUES (1,2,'Perceptuo-Cognitive; Psychosocial; Socio-Emotional','adasd','asdasd','2026-09-08 18:17:26','2026-09-08 18:22:12'),(3,3,'Perceptuo-Cognitive; Psychosocial; Socio-Emotional','Bilingual FSL fluency & daily life competencies','Master 50 core classroom signs and daily living skills','2026-09-13 06:46:42','2026-09-13 06:46:42'),(4,4,'Perceptuo-Cognitive; Psychosocial; Socio-Emotional','Bilingual FSL fluency & daily life competencies','Master 50 core classroom signs and daily living skills','2026-09-13 06:47:14','2026-09-13 06:47:14'),(5,5,'Perceptuo-Cognitive; Psychosocial; Socio-Emotional','Bilingual FSL fluency & daily life competencies','Master 50 core classroom signs and daily living skills','2026-09-13 06:47:21','2026-09-13 06:47:21'),(6,6,'Perceptuo-Cognitive; Psychosocial; Socio-Emotional','Bilingual FSL fluency & daily life competencies','Master 50 core classroom signs and daily living skills','2026-09-13 06:47:29','2026-09-13 06:47:29');
/*!40000 ALTER TABLE `iep_core` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_domains`
--

DROP TABLE IF EXISTS `iep_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_domains` (
  `id` int NOT NULL AUTO_INCREMENT,
  `iep_id` int NOT NULL,
  `domain_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_iep_id` (`iep_id`),
  CONSTRAINT `iep_domains_ibfk_1` FOREIGN KEY (`iep_id`) REFERENCES `iep_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_domains`
--

LOCK TABLES `iep_domains` WRITE;
/*!40000 ALTER TABLE `iep_domains` DISABLE KEYS */;
INSERT INTO `iep_domains` VALUES (1,2,'Perceptuo-Cognitive',0,'2026-09-08 18:22:12'),(2,2,'Psychosocial',1,'2026-09-08 18:22:12'),(3,2,'Socio-Emotional',2,'2026-09-08 18:22:12'),(4,3,'Daily Living Skills',1,'2026-09-13 06:46:42'),(5,3,'Language Development',2,'2026-09-13 06:46:42'),(6,3,'Socio-Emotional',3,'2026-09-13 06:46:42'),(7,3,'Perceptuo-Cognitive',4,'2026-09-13 06:46:42'),(8,4,'Daily Living Skills',1,'2026-09-13 06:47:14'),(9,4,'Language Development',2,'2026-09-13 06:47:14'),(10,4,'Socio-Emotional',3,'2026-09-13 06:47:14'),(11,4,'Perceptuo-Cognitive',4,'2026-09-13 06:47:14'),(12,5,'Daily Living Skills',1,'2026-09-13 06:47:21'),(13,5,'Language Development',2,'2026-09-13 06:47:21'),(14,5,'Socio-Emotional',3,'2026-09-13 06:47:21'),(15,5,'Perceptuo-Cognitive',4,'2026-09-13 06:47:21'),(16,6,'Daily Living Skills',1,'2026-09-13 06:47:29'),(17,6,'Language Development',2,'2026-09-13 06:47:29'),(18,6,'Socio-Emotional',3,'2026-09-13 06:47:29'),(19,6,'Perceptuo-Cognitive',4,'2026-09-13 06:47:29');
/*!40000 ALTER TABLE `iep_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_edit_logs`
--

DROP TABLE IF EXISTS `iep_edit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_edit_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `iep_id` int NOT NULL,
  `edited_by` int NOT NULL,
  `field_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_value` text COLLATE utf8mb4_unicode_ci,
  `new_value` text COLLATE utf8mb4_unicode_ci,
  `edited_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `edited_by` (`edited_by`),
  KEY `idx_iep_edited` (`iep_id`,`edited_at`),
  CONSTRAINT `iep_edit_logs_ibfk_1` FOREIGN KEY (`iep_id`) REFERENCES `iep_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iep_edit_logs_ibfk_2` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_edit_logs`
--

LOCK TABLES `iep_edit_logs` WRITE;
/*!40000 ALTER TABLE `iep_edit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `iep_edit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_meetings`
--

DROP TABLE IF EXISTS `iep_meetings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_meetings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `assessment_id` int NOT NULL,
  `scheduled_by` int DEFAULT NULL,
  `meeting_date` datetime NOT NULL,
  `meeting_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agenda` text COLLATE utf8mb4_unicode_ci,
  `guidance_id` int DEFAULT NULL,
  `principal_id` int DEFAULT NULL,
  `status` enum('scheduled','rescheduled','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'scheduled',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `reschedule_reason` text COLLATE utf8mb4_unicode_ci,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `scheduled_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `assessment_id` (`assessment_id`),
  KEY `scheduled_by` (`scheduled_by`),
  KEY `guidance_id` (`guidance_id`),
  KEY `principal_id` (`principal_id`),
  KEY `idx_status` (`status`),
  KEY `idx_meeting_date` (`meeting_date`),
  CONSTRAINT `iep_meetings_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iep_meetings_ibfk_2` FOREIGN KEY (`assessment_id`) REFERENCES `assessment_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iep_meetings_ibfk_3` FOREIGN KEY (`scheduled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `iep_meetings_ibfk_4` FOREIGN KEY (`guidance_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `iep_meetings_ibfk_5` FOREIGN KEY (`principal_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_meetings`
--

LOCK TABLES `iep_meetings` WRITE;
/*!40000 ALTER TABLE `iep_meetings` DISABLE KEYS */;
INSERT INTO `iep_meetings` VALUES (1,2,1,6,'2026-08-22 07:04:00','dasyuda','',NULL,NULL,'completed',NULL,NULL,NULL,'2026-08-20 21:04:31',NULL,NULL,'2026-08-20 21:04:31','2026-08-20 21:04:52'),(2,4,2,6,'2026-09-10 08:20:00','dasyuda','',NULL,NULL,'completed',NULL,NULL,NULL,'2026-09-08 18:14:47',NULL,NULL,'2026-09-08 18:14:47','2026-09-08 18:15:32'),(3,5,3,6,'2026-09-10 09:00:00','Piedad Central ES SPED Room',NULL,NULL,NULL,'completed',NULL,NULL,NULL,'2026-09-13 06:46:42',NULL,NULL,'2026-09-13 06:46:42','2026-09-13 06:46:42'),(4,6,4,6,'2026-09-10 09:00:00','Piedad Central ES SPED Room',NULL,NULL,NULL,'completed',NULL,NULL,NULL,'2026-09-13 06:47:14',NULL,NULL,'2026-09-13 06:47:14','2026-09-13 06:47:14'),(5,7,5,6,'2026-09-10 09:00:00','Piedad Central ES SPED Room',NULL,NULL,NULL,'completed',NULL,NULL,NULL,'2026-09-13 06:47:21',NULL,NULL,'2026-09-13 06:47:21','2026-09-13 06:47:21'),(6,8,6,6,'2026-09-10 09:00:00','Piedad Central ES SPED Room',NULL,NULL,NULL,'completed',NULL,NULL,NULL,'2026-09-13 06:47:29',NULL,NULL,'2026-09-13 06:47:29','2026-09-13 06:47:29');
/*!40000 ALTER TABLE `iep_meetings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_p2_documents`
--

DROP TABLE IF EXISTS `iep_p2_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_p2_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `meeting_id` int NOT NULL,
  `student_id` int NOT NULL,
  `iep_data` json NOT NULL,
  `pdf_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','pending_review','reviewed_signed') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_meeting_id` (`meeting_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `iep_p2_documents_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `iep_meetings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iep_p2_documents_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iep_p2_documents_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_p2_documents`
--

LOCK TABLES `iep_p2_documents` WRITE;
/*!40000 ALTER TABLE `iep_p2_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `iep_p2_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_p2_reviews`
--

DROP TABLE IF EXISTS `iep_p2_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_p2_reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `iep_p2_id` int NOT NULL,
  `reviewer_id` int NOT NULL,
  `reviewer_role` enum('guidance','principal','parent','sped_teacher','school_head','ilrc_supervisor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `signature_data` text COLLATE utf8mb4_unicode_ci,
  `reviewed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_reviewer_per_p2` (`iep_p2_id`,`reviewer_id`),
  KEY `reviewer_id` (`reviewer_id`),
  KEY `idx_reviewer_role` (`reviewer_role`),
  KEY `idx_reviewed_at` (`reviewed_at`),
  CONSTRAINT `iep_p2_reviews_ibfk_1` FOREIGN KEY (`iep_p2_id`) REFERENCES `iep_p2_documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iep_p2_reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_p2_reviews`
--

LOCK TABLES `iep_p2_reviews` WRITE;
/*!40000 ALTER TABLE `iep_p2_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `iep_p2_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_p3_documents`
--

DROP TABLE IF EXISTS `iep_p3_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_p3_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `meeting_id` int NOT NULL,
  `student_id` int NOT NULL,
  `iep_data` json NOT NULL,
  `pdf_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','pending_signatures','signed_approved') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_meeting_id` (`meeting_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `iep_p3_documents_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `iep_meetings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iep_p3_documents_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iep_p3_documents_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_p3_documents`
--

LOCK TABLES `iep_p3_documents` WRITE;
/*!40000 ALTER TABLE `iep_p3_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `iep_p3_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_p3_signatures`
--

DROP TABLE IF EXISTS `iep_p3_signatures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_p3_signatures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `iep_p3_id` int NOT NULL,
  `signer_id` int NOT NULL,
  `signer_role` enum('parent','guidance','teacher','sped_teacher','principal','school_head','ilrc_supervisor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `signature_data` text COLLATE utf8mb4_unicode_ci,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `signed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_signer_per_p3` (`iep_p3_id`,`signer_role`),
  KEY `signer_id` (`signer_id`),
  KEY `idx_signer_role` (`signer_role`),
  KEY `idx_signed_at` (`signed_at`),
  CONSTRAINT `iep_p3_signatures_ibfk_1` FOREIGN KEY (`iep_p3_id`) REFERENCES `iep_p3_documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iep_p3_signatures_ibfk_2` FOREIGN KEY (`signer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_p3_signatures`
--

LOCK TABLES `iep_p3_signatures` WRITE;
/*!40000 ALTER TABLE `iep_p3_signatures` DISABLE KEYS */;
/*!40000 ALTER TABLE `iep_p3_signatures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_records`
--

DROP TABLE IF EXISTS `iep_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `pdsp_id` int NOT NULL,
  `drafted_by` int NOT NULL,
  `school_year` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','signing','signed') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `signing_method` enum('print_upload','digital') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signed_document_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `re_evaluation_date` date DEFAULT NULL,
  `header_learner_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header_learner_age` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header_lrn` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header_section` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header_teacher_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header_school_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header_grade_level` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `header_student_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pdsp_id` (`pdsp_id`),
  KEY `drafted_by` (`drafted_by`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_status` (`status`),
  KEY `idx_school_year` (`school_year`),
  CONSTRAINT `iep_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iep_records_ibfk_2` FOREIGN KEY (`pdsp_id`) REFERENCES `pdsp_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iep_records_ibfk_3` FOREIGN KEY (`drafted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_records`
--

LOCK TABLES `iep_records` WRITE;
/*!40000 ALTER TABLE `iep_records` DISABLE KEYS */;
INSERT INTO `iep_records` VALUES (2,4,2,6,'2026-2027','signed','print_upload','uploads/iep/4/iep_meeting_proof_2_1788892178.pdf','2026-09-10','ONE TEST','12',NULL,'Maligaya','Zere d\'Apchier','Piedad Central Elementary School','SPED Program','2026-09-08 18:15:36','2026-09-08 18:29:38','20260003'),(3,5,3,6,'2026-2027','signed','print_upload',NULL,'2027-09-10','TEST 2','12','129688260004','Maligaya','Zere d\'Apchier','Piedad Central Elementary School','SPED Program','2026-09-13 06:46:42','2026-09-14 18:33:34','20260004'),(4,6,4,6,'2026-2027','signed','print_upload',NULL,'2027-09-10','TEST 3','12','129688260005','Maligaya','Zere d\'Apchier','Piedad Central Elementary School','SPED Program','2026-09-13 06:47:14','2026-09-14 18:33:34','20260005'),(5,7,5,6,'2026-2027','signed','print_upload',NULL,'2027-09-10','TEST 4','11','129688260006','Maligaya','Zere d\'Apchier','Piedad Central Elementary School','SPED Program','2026-09-13 06:47:21','2026-09-14 18:33:34','20260006'),(6,8,6,6,'2026-2027','signed','print_upload',NULL,'2027-09-10','TEST 5','11','129688260007','Maligaya','Zere d\'Apchier','Piedad Central Elementary School','SPED Program','2026-09-13 06:47:29','2026-09-14 18:33:34','20260007');
/*!40000 ALTER TABLE `iep_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_signatories`
--

DROP TABLE IF EXISTS `iep_signatories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_signatories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `iep_id` int NOT NULL,
  `signatory_role` enum('parent_guardian','guidance_counselor','teacher','sned_teacher','school_head','ilrc_supervisor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `signatory_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `send_status` enum('not_sent','pending','signed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_sent',
  `signature_request_sent_at` timestamp NULL DEFAULT NULL,
  `signature_image_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_iep_id` (`iep_id`),
  KEY `idx_role` (`signatory_role`),
  CONSTRAINT `iep_signatories_ibfk_1` FOREIGN KEY (`iep_id`) REFERENCES `iep_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_signatories`
--

LOCK TABLES `iep_signatories` WRITE;
/*!40000 ALTER TABLE `iep_signatories` DISABLE KEYS */;
INSERT INTO `iep_signatories` VALUES (1,2,'parent_guardian','asda','signed',NULL,NULL,'2026-09-08 10:29:38'),(2,2,'sned_teacher','asda','signed',NULL,NULL,'2026-09-08 10:29:38'),(3,3,'parent_guardian','Maria Santos','signed',NULL,NULL,'2026-09-13 06:46:42'),(4,3,'','Zere d\'Apchier','signed',NULL,NULL,'2026-09-13 06:46:42'),(5,4,'parent_guardian','Roberto Dela Cruz','signed',NULL,NULL,'2026-09-13 06:47:14'),(6,4,'','Zere d\'Apchier','signed',NULL,NULL,'2026-09-13 06:47:14'),(7,5,'parent_guardian','Grace Alcantara','signed',NULL,NULL,'2026-09-13 06:47:21'),(8,5,'','Zere d\'Apchier','signed',NULL,NULL,'2026-09-13 06:47:21'),(9,6,'parent_guardian','Danilo Reyes','signed',NULL,NULL,'2026-09-13 06:47:29'),(10,6,'','Zere d\'Apchier','signed',NULL,NULL,'2026-09-13 06:47:29');
/*!40000 ALTER TABLE `iep_signatories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_step_lesson_plans`
--

DROP TABLE IF EXISTS `iep_step_lesson_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_step_lesson_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `iep_step_id` int NOT NULL,
  `lesson_plan_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_step_lesson` (`iep_step_id`,`lesson_plan_id`),
  KEY `idx_lesson_plan_id` (`lesson_plan_id`),
  CONSTRAINT `iep_step_lesson_plans_ibfk_1` FOREIGN KEY (`iep_step_id`) REFERENCES `iep_steps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iep_step_lesson_plans_ibfk_2` FOREIGN KEY (`lesson_plan_id`) REFERENCES `lesson_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_step_lesson_plans`
--

LOCK TABLES `iep_step_lesson_plans` WRITE;
/*!40000 ALTER TABLE `iep_step_lesson_plans` DISABLE KEYS */;
INSERT INTO `iep_step_lesson_plans` VALUES (3,8,4,'2026-09-19 17:09:16'),(4,9,5,'2026-09-19 17:09:28');
/*!40000 ALTER TABLE `iep_step_lesson_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_step_materials`
--

DROP TABLE IF EXISTS `iep_step_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_step_materials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `iep_step_id` int NOT NULL,
  `material_id` int NOT NULL,
  `linked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_step_material` (`iep_step_id`,`material_id`),
  KEY `idx_material_id` (`material_id`),
  CONSTRAINT `iep_step_materials_ibfk_1` FOREIGN KEY (`iep_step_id`) REFERENCES `iep_steps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `iep_step_materials_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `lesson_materials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_step_materials`
--

LOCK TABLES `iep_step_materials` WRITE;
/*!40000 ALTER TABLE `iep_step_materials` DISABLE KEYS */;
/*!40000 ALTER TABLE `iep_step_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iep_steps`
--

DROP TABLE IF EXISTS `iep_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iep_steps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `iep_id` int NOT NULL,
  `step_number` int NOT NULL,
  `step_domain` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `step_objective` text COLLATE utf8mb4_unicode_ci,
  `duration_lp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instructional_evaluation` text COLLATE utf8mb4_unicode_ci,
  `observation` text COLLATE utf8mb4_unicode_ci,
  `observation_unlocked` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `pdsp_indicator_text` text COLLATE utf8mb4_unicode_ci COMMENT 'Linked PDSP skill indicator this step targets',
  PRIMARY KEY (`id`),
  KEY `idx_iep_id` (`iep_id`),
  KEY `idx_step_number` (`iep_id`,`step_number`),
  CONSTRAINT `iep_steps_ibfk_1` FOREIGN KEY (`iep_id`) REFERENCES `iep_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iep_steps`
--

LOCK TABLES `iep_steps` WRITE;
/*!40000 ALTER TABLE `iep_steps` DISABLE KEYS */;
INSERT INTO `iep_steps` VALUES (3,2,1,'Daily Living Skills','Master independent handwashing and hygiene routine using 5-step visual cues before and after snacks with 90% consistency.','2-3 weeks','Direct Observation / Visual Prompt Checklist','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Washes and dries hands properly'),(4,2,2,'Socio-Emotional','Take turns and share instructional manipulatives with at least one peer during 15-minute structured play without disruptive behavior.','3 weeks','Social Skills Rubric & Frequency Log','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Takes turns and shares materials'),(5,2,3,'Language Development','Accurately produce and receptively recognize 15 core Filipino Sign Language (FSL) survival signs (e.g., eat, water, bathroom, help, thank you).','4 weeks','FSL Expressive & Receptive Signing Checklist','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Communicates basic needs verbally or through AAC/FSL'),(6,2,4,'Psychomotor Development','Cut along straight and curved outlines within 1/4 inch accuracy using adapted safety scissors in 4 out of 5 trials.','3 weeks','Performance Work Sample Portfolio','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Demonstrates hand-eye coordination with fine motor tools'),(7,2,5,'Perceptuo-Cognitive','Sort and match colored attribute blocks by size, shape, and color into correct corresponding sorting bins with 80% accuracy.','3 weeks','Task Completion Record & Teacher Observation','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Identifies, sorts, and classifies concrete objects'),(8,3,1,'Daily Living Skills','Independently fasten, button, and unbutton large-sized shirt buttons and zip up school backpack.','2-3 weeks','Task Analysis Checklist','',0,'2026-09-14 18:33:34','2026-09-19 18:45:36','Buttons and unbuttons clothing and manages personal items'),(9,3,2,'Socio-Emotional','Identify and express basic emotional states (happy, sad, frustrated) using visual emotion cards instead of tantrums.','3 weeks','Behavioral Observation Log','',0,'2026-09-14 18:33:34','2026-09-19 18:45:36','Shows awareness of own feelings and emotions'),(10,3,3,'Language Development','Follow two-step classroom instructions (e.g., Get your notebook and sit down) with verbal and visual sign prompt.','4 weeks','Criterion-Referenced Checklist','',0,'2026-09-14 18:33:34','2026-09-19 18:45:36','Follows two-step verbal or signed directions'),(11,3,4,'Psychomotor Development','Trace letters and geometric lines with proper pincer pencil grasp on wide-ruled tactile paper.','3 weeks','Student Writing Portfolio','',0,'2026-09-14 18:33:34','2026-09-19 18:45:36','Uses appropriate grip on writing tools'),(12,3,5,'Perceptuo-Cognitive','Recognize and point to numerals 1 through 10 and pair them with the corresponding quantity of counter tokens.','3 weeks','Mathematics Skills Assessment','',0,'2026-09-14 18:33:34','2026-09-19 18:45:36','Demonstrates one-to-one numerical correspondence'),(13,4,1,'Daily Living Skills','Pack and organize personal school belongings into backpack before dismissal with 100% completion over 5 consecutive days.','2 weeks','Dismissal Routine Checklist','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Manages and organizes personal belongings'),(14,4,2,'Socio-Emotional','Transition between classroom learning stations without anxiety or protest within 2 minutes of the auditory/visual cue.','3 weeks','Transition Timing Sheet','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Follows classroom rules and daily routines'),(15,4,3,'Language Development','Recognize, fingerspell, and print own first name on class attendance cards and learning folders.','3 weeks','Daily Attendance Sheet Work Sample','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Recognizes own name in print / FSL'),(16,4,4,'Psychomotor Development','String 10 large wooden beads onto a lace following an alternating two-color sequence within 5 minutes.','2 weeks','Timed Practical Observation','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Demonstrates bilateral motor integration'),(17,4,5,'Perceptuo-Cognitive','Identify common community helpers (teacher, doctor, police, firefighter) using picture cards with 90% accuracy.','4 weeks','Flashcard Identification Drill','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Identifies familiar pictures and community symbols'),(18,5,1,'Daily Living Skills','Open food containers and eat snack using utensils with minimal spillage during morning recess.','2-3 weeks','Mealtime Observation Scale','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Eats and drinks independently with minimal mess'),(19,5,2,'Socio-Emotional','Seek teacher assistance appropriately by raising hand or pointing to the help symbol card when faced with difficulty.','3 weeks','Classroom Interaction Log','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Seeks help from adults when needed'),(20,5,3,'Language Development','Match 10 high-frequency sight words with their corresponding pictures and FSL signs.','4 weeks','Matching Task Worksheets','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Matches words with pictorial representations'),(21,5,4,'Psychomotor Development','Walk along a 2-inch wide taped floor line for 10 feet maintaining balance without stepping off in 3 consecutive trials.','2 weeks','Gross Motor Skill Evaluation','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Demonstrates gross motor balance and equilibrium'),(22,5,5,'Perceptuo-Cognitive','Complete a 6-piece wooden puzzle independently within 4 minutes.','3 weeks','Timed Puzzle Rubric','',0,'2026-09-14 18:33:34','2026-09-14 18:33:34','Demonstrates visual-spatial problem solving'),(23,6,1,'Daily Living Skills','Request to use the comfort room independently using verbal prompt or visual bathroom icon before accidents occur.','2 weeks','Toileting Schedule & Record','',0,'2026-09-14 18:33:34','2026-09-20 19:25:16','Indicates need to use the toilet'),(24,6,2,'Socio-Emotional','Greet the teacher and peers during morning circle using verbal greeting or FSL Good Morning sign with 80% prompting reduction.','3 weeks','Morning Routine Observation Form','',0,'2026-09-14 18:33:34','2026-09-20 19:25:16','Interacts appropriately with peers and adults'),(25,6,3,'Language Development','Distinguish between Yes and No questions related to immediate wants and needs with accurate nodding/shaking or FSL signs.','3 weeks','Question-Response Checklist','',0,'2026-09-14 18:33:34','2026-09-20 19:25:16','Answers simple functional questions appropriately'),(26,6,4,'Psychomotor Development','Catch a medium-sized bounced ball with both hands from a distance of 4 feet in 4 out of 5 attempts.','2 weeks','Physical Education Rubric','',0,'2026-09-14 18:33:34','2026-09-20 19:25:16','Demonstrates bilateral ball catching and visual tracking'),(27,6,5,'Perceptuo-Cognitive','Identify primary colors (Red, Blue, Yellow, Green) on flashcards with 100% accuracy across 3 sessions.','3 weeks','Color Recognition Checklist','',0,'2026-09-14 18:33:34','2026-09-20 19:25:16','Identifies and names primary colors');
/*!40000 ALTER TABLE `iep_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itgp_activities`
--

DROP TABLE IF EXISTS `itgp_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itgp_activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `itgp_id` int NOT NULL,
  `competency_skill` text COLLATE utf8mb4_unicode_ci,
  `activities` text COLLATE utf8mb4_unicode_ci,
  `time_frame` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `person_responsible` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `itgp_id` (`itgp_id`),
  CONSTRAINT `itgp_activities_ibfk_1` FOREIGN KEY (`itgp_id`) REFERENCES `itgp_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itgp_activities`
--

LOCK TABLES `itgp_activities` WRITE;
/*!40000 ALTER TABLE `itgp_activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `itgp_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itgp_comments`
--

DROP TABLE IF EXISTS `itgp_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itgp_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `itgp_id` int NOT NULL,
  `posted_by` int NOT NULL,
  `comment_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `itgp_id` (`itgp_id`),
  KEY `posted_by` (`posted_by`),
  CONSTRAINT `itgp_comments_ibfk_1` FOREIGN KEY (`itgp_id`) REFERENCES `itgp_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `itgp_comments_ibfk_2` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itgp_comments`
--

LOCK TABLES `itgp_comments` WRITE;
/*!40000 ALTER TABLE `itgp_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `itgp_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itgp_records`
--

DROP TABLE IF EXISTS `itgp_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itgp_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `itp_id` int NOT NULL,
  `general_teacher_id` int NOT NULL,
  `goal` text COLLATE utf8mb4_unicode_ci,
  `entry_point` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `learning_packages` text COLLATE utf8mb4_unicode_ci,
  `recommendations` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','finalized') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `finalized_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `itp_id` (`itp_id`),
  KEY `general_teacher_id` (`general_teacher_id`),
  CONSTRAINT `itgp_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `itgp_records_ibfk_2` FOREIGN KEY (`itp_id`) REFERENCES `itp_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `itgp_records_ibfk_3` FOREIGN KEY (`general_teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itgp_records`
--

LOCK TABLES `itgp_records` WRITE;
/*!40000 ALTER TABLE `itgp_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `itgp_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itp_narrative`
--

DROP TABLE IF EXISTS `itp_narrative`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itp_narrative` (
  `id` int NOT NULL AUTO_INCREMENT,
  `itp_id` int NOT NULL,
  `section` enum('strengths','interests','talents','skills','needs') COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `itp_id` (`itp_id`),
  CONSTRAINT `itp_narrative_ibfk_1` FOREIGN KEY (`itp_id`) REFERENCES `itp_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itp_narrative`
--

LOCK TABLES `itp_narrative` WRITE;
/*!40000 ALTER TABLE `itp_narrative` DISABLE KEYS */;
/*!40000 ALTER TABLE `itp_narrative` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itp_program_matrix`
--

DROP TABLE IF EXISTS `itp_program_matrix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itp_program_matrix` (
  `id` int NOT NULL AUTO_INCREMENT,
  `itp_id` int NOT NULL,
  `row_type` int NOT NULL,
  `column_type` int NOT NULL,
  `is_checked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_itp_matrix_cell` (`itp_id`,`row_type`,`column_type`),
  CONSTRAINT `itp_program_matrix_ibfk_1` FOREIGN KEY (`itp_id`) REFERENCES `itp_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itp_program_matrix`
--

LOCK TABLES `itp_program_matrix` WRITE;
/*!40000 ALTER TABLE `itp_program_matrix` DISABLE KEYS */;
/*!40000 ALTER TABLE `itp_program_matrix` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itp_recommendations`
--

DROP TABLE IF EXISTS `itp_recommendations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itp_recommendations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `itp_id` int NOT NULL,
  `timing` enum('beginning_of_sy','end_of_sy') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recommendation_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_itp_recommendation_timing` (`itp_id`,`timing`),
  CONSTRAINT `itp_recommendations_ibfk_1` FOREIGN KEY (`itp_id`) REFERENCES `itp_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itp_recommendations`
--

LOCK TABLES `itp_recommendations` WRITE;
/*!40000 ALTER TABLE `itp_recommendations` DISABLE KEYS */;
/*!40000 ALTER TABLE `itp_recommendations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itp_records`
--

DROP TABLE IF EXISTS `itp_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itp_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `transition_readiness_id` int NOT NULL,
  `school_year` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `point_of_entry` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `learner_information` json DEFAULT NULL,
  `status` enum('in_progress','finalized') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in_progress',
  `drafted_by` int NOT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_itp_student` (`student_id`),
  KEY `transition_readiness_id` (`transition_readiness_id`),
  KEY `drafted_by` (`drafted_by`),
  CONSTRAINT `itp_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `itp_records_ibfk_2` FOREIGN KEY (`transition_readiness_id`) REFERENCES `transition_readiness` (`id`) ON DELETE CASCADE,
  CONSTRAINT `itp_records_ibfk_3` FOREIGN KEY (`drafted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itp_records`
--

LOCK TABLES `itp_records` WRITE;
/*!40000 ALTER TABLE `itp_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `itp_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itp_signatures`
--

DROP TABLE IF EXISTS `itp_signatures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itp_signatures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `itp_id` int NOT NULL,
  `signatory_role` enum('parent_guardian') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'parent_guardian',
  `signature_image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `signed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_itp_signature_role` (`itp_id`,`signatory_role`),
  CONSTRAINT `itp_signatures_ibfk_1` FOREIGN KEY (`itp_id`) REFERENCES `itp_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itp_signatures`
--

LOCK TABLES `itp_signatures` WRITE;
/*!40000 ALTER TABLE `itp_signatures` DISABLE KEYS */;
/*!40000 ALTER TABLE `itp_signatures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itp_team_members`
--

DROP TABLE IF EXISTS `itp_team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itp_team_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `itp_id` int NOT NULL,
  `role` enum('itp_coordinator','school_head','sped_teacher','parent_guardian','learner','guidance_teacher','linkages') COLLATE utf8mb4_unicode_ci NOT NULL,
  `assigned_user_id` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_started` date DEFAULT NULL,
  `status` enum('pending','filled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_itp_role` (`itp_id`,`role`),
  KEY `assigned_user_id` (`assigned_user_id`),
  CONSTRAINT `itp_team_members_ibfk_1` FOREIGN KEY (`itp_id`) REFERENCES `itp_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `itp_team_members_ibfk_2` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itp_team_members`
--

LOCK TABLES `itp_team_members` WRITE;
/*!40000 ALTER TABLE `itp_team_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `itp_team_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `learner_access`
--

DROP TABLE IF EXISTS `learner_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `learner_access` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `access_mode` enum('direct','parent_managed') COLLATE utf8mb4_unicode_ci DEFAULT 'parent_managed',
  `assigned_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_access` (`student_id`),
  KEY `assigned_by` (`assigned_by`),
  CONSTRAINT `learner_access_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learner_access_ibfk_2` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `learner_access`
--

LOCK TABLES `learner_access` WRITE;
/*!40000 ALTER TABLE `learner_access` DISABLE KEYS */;
/*!40000 ALTER TABLE `learner_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `learner_badges`
--

DROP TABLE IF EXISTS `learner_badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `learner_badges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `badge_key` enum('first_activity','lesson_complete','perfect_score','five_in_a_row','all_done','star_collector') COLLATE utf8mb4_unicode_ci NOT NULL,
  `earned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_badge` (`student_id`,`badge_key`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_badge_key` (`badge_key`),
  CONSTRAINT `learner_badges_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `learner_badges`
--

LOCK TABLES `learner_badges` WRITE;
/*!40000 ALTER TABLE `learner_badges` DISABLE KEYS */;
/*!40000 ALTER TABLE `learner_badges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `learner_iep`
--

DROP TABLE IF EXISTS `learner_iep`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `learner_iep` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `iep_p3_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `implementation_status` enum('not_started','in_progress','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'not_started',
  `start_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `iep_p3_id` (`iep_p3_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_status` (`implementation_status`),
  CONSTRAINT `learner_iep_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learner_iep_ibfk_2` FOREIGN KEY (`iep_p3_id`) REFERENCES `iep_p3_documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learner_iep_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `learner_iep`
--

LOCK TABLES `learner_iep` WRITE;
/*!40000 ALTER TABLE `learner_iep` DISABLE KEYS */;
/*!40000 ALTER TABLE `learner_iep` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `learner_points`
--

DROP TABLE IF EXISTS `learner_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `learner_points` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `points` int NOT NULL DEFAULT '0',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_type` enum('view','submission','quiz','lesson_bonus','badge_bonus') COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_id` int DEFAULT NULL,
  `earned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_source_type` (`source_type`),
  KEY `idx_earned_at` (`earned_at`),
  CONSTRAINT `learner_points_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `learner_points`
--

LOCK TABLES `learner_points` WRITE;
/*!40000 ALTER TABLE `learner_points` DISABLE KEYS */;
INSERT INTO `learner_points` VALUES (9,4,150,'Activity completion','',2,'2026-09-19 19:24:23'),(10,5,180,'Activity completion','',4,'2026-09-19 19:24:27'),(11,6,90,'Activity completion','',6,'2026-09-19 19:24:36'),(12,7,130,'Activity completion','',8,'2026-09-19 19:24:44'),(13,8,200,'Activity completion','',10,'2026-09-19 19:24:52');
/*!40000 ALTER TABLE `learner_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `learner_progress`
--

DROP TABLE IF EXISTS `learner_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `learner_progress` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `material_id` int NOT NULL,
  `status` enum('not_started','in_progress','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'not_started',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `time_spent_minutes` int DEFAULT '0',
  `stars_earned` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_material` (`student_id`,`material_id`),
  KEY `material_id` (`material_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `learner_progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learner_progress_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `learning_materials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `learner_progress`
--

LOCK TABLES `learner_progress` WRITE;
/*!40000 ALTER TABLE `learner_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `learner_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `learning_materials`
--

DROP TABLE IF EXISTS `learning_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `learning_materials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `learner_iep_id` int NOT NULL,
  `material_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `material_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_assignment` tinyint(1) DEFAULT '0',
  `due_date` datetime DEFAULT NULL,
  `points` int DEFAULT '0',
  `uploaded_by` int NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `learner_iep_id` (`learner_iep_id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `learning_materials_ibfk_1` FOREIGN KEY (`learner_iep_id`) REFERENCES `learner_iep` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learning_materials_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `learning_materials`
--

LOCK TABLES `learning_materials` WRITE;
/*!40000 ALTER TABLE `learning_materials` DISABLE KEYS */;
/*!40000 ALTER TABLE `learning_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lesson_assignments`
--

DROP TABLE IF EXISTS `lesson_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lesson_assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lesson_plan_id` int NOT NULL,
  `student_id` int NOT NULL,
  `assigned_by` int NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assignment` (`lesson_plan_id`,`student_id`),
  KEY `assigned_by` (`assigned_by`),
  KEY `idx_lesson_plan_id` (`lesson_plan_id`),
  KEY `idx_student_id` (`student_id`),
  CONSTRAINT `lesson_assignments_ibfk_1` FOREIGN KEY (`lesson_plan_id`) REFERENCES `lesson_plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_assignments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_assignments`
--

LOCK TABLES `lesson_assignments` WRITE;
/*!40000 ALTER TABLE `lesson_assignments` DISABLE KEYS */;
INSERT INTO `lesson_assignments` VALUES (14,4,5,6,'2026-09-19 17:09:16'),(15,5,5,6,'2026-09-19 17:09:28'),(16,1,4,6,'2026-09-19 19:23:15'),(17,3,4,6,'2026-09-19 19:23:15'),(26,6,5,6,'2026-09-19 19:24:27'),(27,7,5,6,'2026-09-19 19:24:27'),(28,8,6,6,'2026-09-19 19:24:36'),(29,9,6,6,'2026-09-19 19:24:36'),(30,10,7,6,'2026-09-19 19:24:44'),(31,11,7,6,'2026-09-19 19:24:44'),(32,12,8,6,'2026-09-19 19:24:52'),(33,13,8,6,'2026-09-19 19:24:52');
/*!40000 ALTER TABLE `lesson_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lesson_materials`
--

DROP TABLE IF EXISTS `lesson_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lesson_materials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lesson_plan_id` int NOT NULL,
  `material_type` enum('file','link','embed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caption_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transcript_text` text COLLATE utf8mb4_unicode_ci,
  `external_url` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `embed_type` enum('youtube','gdrive','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int DEFAULT '0',
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lesson_plan_id` (`lesson_plan_id`),
  KEY `idx_display_order` (`display_order`),
  CONSTRAINT `lesson_materials_ibfk_1` FOREIGN KEY (`lesson_plan_id`) REFERENCES `lesson_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_materials`
--

LOCK TABLES `lesson_materials` WRITE;
/*!40000 ALTER TABLE `lesson_materials` DISABLE KEYS */;
INSERT INTO `lesson_materials` VALUES (2,3,'file','cgkj','uploads/materials/3/mat_1789145876_6aa4331451c1e.pdf',NULL,NULL,NULL,NULL,0,'2026-09-11 16:57:56');
/*!40000 ALTER TABLE `lesson_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lesson_page_progress`
--

DROP TABLE IF EXISTS `lesson_page_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lesson_page_progress` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `lesson_plan_id` int NOT NULL,
  `last_page_number` int NOT NULL DEFAULT '1',
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `completed_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_student_lesson` (`student_id`,`lesson_plan_id`),
  KEY `lesson_plan_id` (`lesson_plan_id`),
  CONSTRAINT `lesson_page_progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_page_progress_ibfk_2` FOREIGN KEY (`lesson_plan_id`) REFERENCES `lesson_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_page_progress`
--

LOCK TABLES `lesson_page_progress` WRITE;
/*!40000 ALTER TABLE `lesson_page_progress` DISABLE KEYS */;
INSERT INTO `lesson_page_progress` VALUES (96,4,1,2,1,'2026-09-20 03:24:23','2026-09-19 19:24:23'),(97,4,3,2,1,'2026-09-20 03:24:23','2026-09-19 19:24:23'),(98,5,6,2,1,'2026-09-20 03:24:27','2026-09-19 19:24:27'),(99,5,7,2,1,'2026-09-20 03:24:27','2026-09-19 19:24:27'),(100,6,8,2,1,'2026-09-20 03:24:36','2026-09-19 19:24:36'),(101,6,9,2,1,'2026-09-20 03:24:36','2026-09-19 19:24:36'),(102,7,10,2,1,'2026-09-20 03:24:44','2026-09-19 19:24:44'),(103,7,11,2,1,'2026-09-20 03:24:44','2026-09-19 19:24:44'),(104,8,12,2,1,'2026-09-20 03:24:52','2026-09-19 19:24:52'),(105,8,13,2,1,'2026-09-20 03:24:52','2026-09-19 19:24:52');
/*!40000 ALTER TABLE `lesson_page_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lesson_plan_pages`
--

DROP TABLE IF EXISTS `lesson_plan_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lesson_plan_pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lesson_plan_id` int NOT NULL,
  `page_number` int NOT NULL DEFAULT '1',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `guide_questions` mediumtext COLLATE utf8mb4_unicode_ci,
  `media_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `media_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lp_page` (`lesson_plan_id`,`page_number`),
  CONSTRAINT `lesson_plan_pages_ibfk_1` FOREIGN KEY (`lesson_plan_id`) REFERENCES `lesson_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_plan_pages`
--

LOCK TABLES `lesson_plan_pages` WRITE;
/*!40000 ALTER TABLE `lesson_plan_pages` DISABLE KEYS */;
INSERT INTO `lesson_plan_pages` VALUES (8,1,1,'Magiliw na Pagbati (Greetings)','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem;\">👋 ☀️</span>\n    <h3 class=\"fw-bold text-primary mt-2\">Magiliw na Pagbati sa Araw-araw</h3>\n</div>\n<p class=\"fs-5 lh-lg text-dark\">\n    Bawat umaga, masaya tayong bumabati sa ating guro at mga kaklase! \n    Pindutin ang mga salitang may kulay upang panoorin ang tunay na <strong>Filipino Sign Language (FSL)</strong> video:\n</p>\n<div class=\"d-flex flex-wrap gap-3 justify-content-center my-4\">\n    <button class=\"btn btn-outline-primary btn-lg rounded-pill shadow-sm fsl-word\" data-word=\"hello\">\n        <span style=\"font-size: 1.3rem;\">👋</span> <strong>Hello (Kumusta)</strong>\n    </button>\n    <button class=\"btn btn-outline-success btn-lg rounded-pill shadow-sm fsl-word\" data-word=\"good morning\">\n        <span style=\"font-size: 1.3rem;\">☀️</span> <strong>Good Morning (Magandang Umaga)</strong>\n    </button>\n    <button class=\"btn btn-outline-warning text-dark btn-lg rounded-pill shadow-sm fsl-word\" data-word=\"thank you\">\n        <span style=\"font-size: 1.3rem;\">🙏</span> <strong>Thank You (Salamat)</strong>\n    </button>\n</div>\n<div class=\"alert alert-info border-0 rounded-4 text-center\">\n    <i class=\"bi bi-lightbulb-fill me-2 text-warning\"></i> <strong>Tip para sa Mag-aaral:</strong> Panoorin at gayahin ang galaw ng kamay sa video!\n</div>',NULL,'none',NULL,'2026-09-10 21:02:55','2026-09-10 21:02:55'),(9,1,2,'Ang Aking Pamilya (Family)','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem;\">👨‍👩‍👧‍👦 🏡</span>\n    <h3 class=\"fw-bold text-primary mt-2\">Ang Aking Masayang Pamilya</h3>\n</div>\n<p class=\"fs-5 lh-lg text-dark\">\n    Mahalaga ang ating pamilya sa ating tahanan. Panoorin kung paano senyasin ang bawat kasapi ng pamilya:\n</p>\n<div class=\"row g-3 my-3\">\n    <div class=\"col-md-6\">\n        <div class=\"card border-warning border-opacity-50 rounded-4 p-3 text-center bg-light shadow-sm\">\n            <span style=\"font-size: 2.8rem;\">👨</span>\n            <h5 class=\"fw-bold text-dark mt-2\">Ama / Tatay</h5>\n            <button type=\"button\" class=\"btn-fsl-action mt-2 fsl-word\" data-word=\"father\">\n                <i class=\"ph-bold ph-play-circle me-1\"></i> Panoorin ang Senyas (Father)\n            </button>\n        </div>\n    </div>\n    <div class=\"col-md-6\">\n        <div class=\"card border-warning border-opacity-50 rounded-4 p-3 text-center bg-light shadow-sm\">\n            <span style=\"font-size: 2.8rem;\">👩</span>\n            <h5 class=\"fw-bold text-dark mt-2\">Ina / Nanay</h5>\n            <button type=\"button\" class=\"btn-fsl-action mt-2 fsl-word\" data-word=\"mother\">\n                <i class=\"ph-bold ph-play-circle me-1\"></i> Panoorin ang Senyas (Mother)\n            </button>\n        </div>\n    </div>\n</div>\n<div class=\"d-flex flex-wrap gap-3 justify-content-center mt-3\">\n    <button type=\"button\" class=\"fsl-word\" data-word=\"boy\">\n        👦 <strong>Batang Lalaki (Boy)</strong>\n    </button>\n    <button type=\"button\" class=\"fsl-word\" data-word=\"girl\">\n        👧 <strong>Batang Babae (Girl)</strong>\n    </button>\n</div>',NULL,'none',NULL,'2026-09-10 21:02:55','2026-09-11 15:01:18'),(10,1,3,'Mga Kulay at Tugon (Colors & Answers)','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem;\">🎨 🌟</span>\n    <h3 class=\"fw-bold text-primary mt-2\">Mga Kulay at Simpleng Tugon</h3>\n</div>\n<p class=\"fs-5 lh-lg text-dark\">\n    Alamin natin ang senyas ng mga paboritong kulay at ang pagsagot ng Oo o Hindi:\n</p>\n<div class=\"d-flex flex-wrap gap-3 justify-content-center my-4\">\n    <button class=\"btn btn-danger btn-lg rounded-pill shadow-sm fsl-word\" data-word=\"red\">\n        🔴 <strong>Pula (Red)</strong>\n    </button>\n    <button class=\"btn btn-primary btn-lg rounded-pill shadow-sm fsl-word\" data-word=\"blue\">\n        🔵 <strong>Asul (Blue)</strong>\n    </button>\n    <button class=\"btn btn-success btn-lg rounded-pill shadow-sm fsl-word\" data-word=\"green\">\n        🟢 <strong>Berde (Green)</strong>\n    </button>\n    <button class=\"btn btn-warning text-dark btn-lg rounded-pill shadow-sm fsl-word\" data-word=\"yellow\">\n        🟡 <strong>Dilaw (Yellow)</strong>\n    </button>\n</div>\n<div class=\"d-flex gap-3 justify-content-center mt-3\">\n    <button class=\"btn btn-outline-success btn-lg rounded-pill fsl-word\" data-word=\"yes\">\n        👍 <strong>Oo (Yes)</strong>\n    </button>\n    <button class=\"btn btn-outline-danger btn-lg rounded-pill fsl-word\" data-word=\"no\">\n        👎 <strong>Hindi (No)</strong>\n    </button>\n</div>',NULL,'none',NULL,'2026-09-10 21:02:55','2026-09-10 21:02:55'),(14,3,1,'Magandang Umaga sa Hapag-Kainan','<div class=\"text-center mb-4\">\r\n    <span style=\"font-size: 3.8rem; display: block; animation: bounce 2s infinite;\">☀️ 🍞 🥛</span>\r\n    <h3 class=\"fw-bold text-primary mt-2\" style=\"font-size: 1.5rem;\">Magandang Umaga sa Hapag-Kainan!</h3>\r\n    <p class=\"text-muted small\">Pindutin ang mga makukulay na pindutan para mapanood ang tunay na galaw ng kamay (FSL Video)!</p>\r\n</div>\r\n\r\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4\">\r\n    <p class=\"fs-5 lh-lg text-dark mb-3\">\r\n        Maagang nagising si Ana. Nakita niya ang kanyang mga magulang sa kusina. \r\n        Masaya siyang bumati ng:\r\n    </p>\r\n    \r\n    <div class=\"text-center my-3\">\r\n        <button class=\"btn btn-warning btn-lg px-4 py-3 rounded-pill shadow-sm fsl-word fw-bold text-dark\" data-word=\"good morning\" style=\"font-size: 1.15rem;\">\r\n            <span style=\"font-size: 1.4rem;\">☀️</span> Good Morning (Magandang Umaga)\r\n        </button>\r\n    </div>\r\n\r\n    <p class=\"fs-5 lh-lg text-dark mb-0\">\r\n        Nakangiti ring sumagot ang kanyang <button class=\"btn btn-outline-danger btn-sm rounded-pill fsl-word fw-bold mx-1\" data-word=\"mother\">👩 Nanay (Mother)</button> \r\n        at ang kanyang <button class=\"btn btn-outline-primary btn-sm rounded-pill fsl-word fw-bold mx-1\" data-word=\"father\">👨 Tatay (Father)</button>.\r\n    </p>\r\n</div>\r\n\r\n<div class=\"alert alert-primary border-0 rounded-4 d-flex align-items-center gap-3\">\r\n    <span style=\"font-size: 2rem;\">💡</span>\r\n    <div>\r\n        <strong>Subukan Natin:</strong> Pindutin ang <em>\"Good Morning\"</em> upang panoorin kung paano bumati gamit ang Filipino Sign Language!\r\n    </div>\r\n</div>',NULL,'none',NULL,'2026-09-10 21:24:18','2026-09-11 15:58:24'),(15,3,2,'Masustansyang Almusal','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.8rem; display: block;\">🍳 🥖 🥛</span>\n    <h3 class=\"fw-bold text-success mt-2\" style=\"font-size: 1.5rem;\">Masustansyang Almusal</h3>\n    <p class=\"text-muted small\">Ano ang mga nakahain sa mesa? Tuklasin ang kanilang mga senyas:</p>\n</div>\n\n<div class=\"row g-3 mb-4\">\n    <div class=\"col-md-4\">\n        <div class=\"card h-100 border-0 shadow-sm rounded-4 text-center p-3\" style=\"background: #fffbeb;\">\n            <span style=\"font-size: 3rem;\">🍞</span>\n            <h5 class=\"fw-bold text-dark mt-2\">Tinapay</h5>\n            <p class=\"small text-muted mb-2\">Mainit na pandesal</p>\n            <button class=\"btn btn-warning rounded-pill fsl-word fw-bold text-dark mt-auto\" data-word=\"bread\">\n                <i class=\"bi bi-play-circle-fill me-1\"></i> Senyas: Bread\n            </button>\n        </div>\n    </div>\n    <div class=\"col-md-4\">\n        <div class=\"card h-100 border-0 shadow-sm rounded-4 text-center p-3\" style=\"background: #fef2f2;\">\n            <span style=\"font-size: 3rem;\">🍳</span>\n            <h5 class=\"fw-bold text-dark mt-2\">Itlog</h5>\n            <p class=\"small text-muted mb-2\">Pritong itlog</p>\n            <button class=\"btn btn-danger rounded-pill fsl-word fw-bold text-white mt-auto\" data-word=\"egg\">\n                <i class=\"bi bi-play-circle-fill me-1\"></i> Senyas: Egg\n            </button>\n        </div>\n    </div>\n    <div class=\"col-md-4\">\n        <div class=\"card h-100 border-0 shadow-sm rounded-4 text-center p-3\" style=\"background: #eff6ff;\">\n            <span style=\"font-size: 3rem;\">🥛</span>\n            <h5 class=\"fw-bold text-dark mt-2\">Gatas</h5>\n            <p class=\"small text-muted mb-2\">Pampalakas ng katawan</p>\n            <button class=\"btn btn-primary rounded-pill fsl-word fw-bold text-white mt-auto\" data-word=\"milk\">\n                <i class=\"bi bi-play-circle-fill me-1\"></i> Senyas: Milk\n            </button>\n        </div>\n    </div>\n</div>\n\n<div class=\"p-3 bg-white rounded-4 border text-center\">\n    <p class=\"fs-5 text-dark mb-2\">Ilang baso ng gatas ang iinumin mo?</p>\n    <div class=\"d-flex justify-content-center gap-2 flex-wrap\">\n        <button class=\"btn btn-outline-secondary rounded-pill px-3 fsl-word fw-bold\" data-word=\"one\">1️⃣ Isa (One)</button>\n        <button class=\"btn btn-outline-secondary rounded-pill px-3 fsl-word fw-bold\" data-word=\"two\">2️⃣ Dalawa (Two)</button>\n        <button class=\"btn btn-outline-secondary rounded-pill px-3 fsl-word fw-bold\" data-word=\"three\">3️⃣ Tatlo (Three)</button>\n    </div>\n</div>',NULL,'none',NULL,'2026-09-10 21:24:18','2026-09-10 21:24:18'),(16,3,3,'Magalang na Pagpapasalamat','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.8rem; display: block;\">🙏 💖 ✨</span>\n    <h3 class=\"fw-bold text-danger mt-2\" style=\"font-size: 1.5rem;\">Magalang na Pananalita at Pagsagot</h3>\n    <p class=\"text-muted small\">Pagkatapos kumain, huwag kalimutang magpasalamat!</p>\n</div>\n\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">\n        Tinanong ni Tatay si Ana: <em>\"Busog ka na ba?\"</em>\n    </p>\n    <div class=\"d-flex justify-content-center gap-3 mb-4\">\n        <button class=\"btn btn-success btn-lg px-4 rounded-pill shadow-sm fsl-word fw-bold\" data-word=\"yes\">\n            👍 Oo (Yes)\n        </button>\n        <button class=\"btn btn-danger btn-lg px-4 rounded-pill shadow-sm fsl-word fw-bold\" data-word=\"no\">\n            👎 Hindi (No)\n        </button>\n    </div>\n\n    <p class=\"fs-5 lh-lg text-dark mb-3\">\n        Iniabot ni Nanay ang prutas, at magiliw na nagpasalamat si Ana:\n    </p>\n    <button class=\"btn btn-primary btn-lg px-5 py-3 rounded-pill shadow fsl-word fw-bold\" data-word=\"thank you\" style=\"font-size: 1.2rem;\">\n        🙏 Salamat po! (Thank You)\n    </button>\n</div>\n\n<div class=\"alert alert-success border-0 rounded-4 text-center\">\n    <span class=\"fs-4\">🎉</span> <strong>Magaling!</strong> Natapos mo ang kwento. Handa ka na bang maglaro sa pagsasanay?\n</div>',NULL,'none',NULL,'2026-09-10 21:24:18','2026-09-10 21:24:18'),(17,4,1,'Slide 1','',NULL,'none',NULL,'2026-09-19 18:54:58','2026-09-19 19:07:40'),(18,6,1,'Magiliw na Pagbati sa Umaga','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">☀️ 🍞 🥛</span>\n    <h3 class=\"fw-bold text-primary mt-2\">Magandang Umaga sa Hapag-Kainan!</h3>\n    <p class=\"text-muted small\">Pindutin ang mga makukulay na pindutan para mapanood ang tunay na galaw ng kamay (FSL Video)!</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">Maagang nagising si Ana. Masaya siyang bumati ng:</p>\n    <div class=\"my-3\">\n        <span class=\"fsl-word\" data-word=\"good morning\" title=\"FSL Sign: Good Morning\">☀️ Good Morning (Magandang Umaga)</span>\n    </div>\n    <p class=\"fs-5 lh-lg text-dark mb-0\">\n        Nakangiti ring sumagot si <span class=\"fsl-word\" data-word=\"mother\" title=\"FSL Sign: Mother\">👩 Nanay (Mother)</span> \n        at si <span class=\"fsl-word\" data-word=\"father\" title=\"FSL Sign: Father\">👨 Tatay (Father)</span>.\n    </p>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #eff6ff; border: 2px solid #93c5fd; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-primary mb-1\">💡 Subukan Natin:</div>\n    <p class=\"fs-6 text-dark mb-0\">Panoorin ang FSL video para sa <strong>Good Morning</strong> at gayahin ang tamang galaw ng kamay.</p>\n</div>','1. Paano mo babatiin ang iyong mga magulang pagkagising?','none',NULL,'2026-09-19 19:24:27','2026-09-19 19:24:27'),(19,6,2,'Ang Ating Paaralan at Kaibigan','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">🏫 📚 🤝</span>\n    <h3 class=\"fw-bold text-success mt-2\">Ang Ating Paaralan at mga Kaibigan</h3>\n    <p class=\"text-muted small\">Pindutin ang mga FSL signs para sa mga tao at lugar sa paaralan.</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">\n        Pumunta ang mag-aaral sa <span class=\"fsl-word\" data-word=\"school\" title=\"FSL Sign: School\">🏫 Paaralan (School)</span>.\n    </p>\n    <div class=\"my-3\">\n        Binati niya ang kanyang <span class=\"fsl-word\" data-word=\"teacher\" title=\"FSL Sign: Teacher\">👩‍🏫 Guro (Teacher)</span> \n        at sinabing <span class=\"fsl-word\" data-word=\"thank you\" title=\"FSL Sign: Thank You\">🙏 Salamat (Thank You)</span>.\n    </div>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #f0fdf4; border: 2px solid #86efac; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-success mb-1\">💬 Pagsasanay sa Klase:</div>\n    <p class=\"fs-6 text-dark mb-0\">I-sign ang \"Salamat\" kapag tinulungan ka ng iyong kaklase o guro.</p>\n</div>','2. Bakit mahalagang magpasalamat kapag may tumulong sa iyo?','none',NULL,'2026-09-19 19:24:27','2026-09-19 19:24:27'),(20,7,1,'Masustansyang Almusal at Pag-sign','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">☀️ 🍞 🥛</span>\n    <h3 class=\"fw-bold text-primary mt-2\">Magandang Umaga sa Hapag-Kainan!</h3>\n    <p class=\"text-muted small\">Pindutin ang mga makukulay na pindutan para mapanood ang tunay na galaw ng kamay (FSL Video)!</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">Maagang nagising si Ana. Masaya siyang bumati ng:</p>\n    <div class=\"my-3\">\n        <span class=\"fsl-word\" data-word=\"good morning\" title=\"FSL Sign: Good Morning\">☀️ Good Morning (Magandang Umaga)</span>\n    </div>\n    <p class=\"fs-5 lh-lg text-dark mb-0\">\n        Nakangiti ring sumagot si <span class=\"fsl-word\" data-word=\"mother\" title=\"FSL Sign: Mother\">👩 Nanay (Mother)</span> \n        at si <span class=\"fsl-word\" data-word=\"father\" title=\"FSL Sign: Father\">👨 Tatay (Father)</span>.\n    </p>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #eff6ff; border: 2px solid #93c5fd; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-primary mb-1\">💡 Subukan Natin:</div>\n    <p class=\"fs-6 text-dark mb-0\">Panoorin ang FSL video para sa <strong>Good Morning</strong> at gayahin ang tamang galaw ng kamay.</p>\n</div>','1. Anong masustansyang pagkain ang kinain mo ngayong umaga?','none',NULL,'2026-09-19 19:24:27','2026-09-19 19:24:27'),(21,7,2,'Kalinisan sa Hapag-Kainan','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">🏫 📚 🤝</span>\n    <h3 class=\"fw-bold text-success mt-2\">Ang Ating Paaralan at mga Kaibigan</h3>\n    <p class=\"text-muted small\">Pindutin ang mga FSL signs para sa mga tao at lugar sa paaralan.</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">\n        Pumunta ang mag-aaral sa <span class=\"fsl-word\" data-word=\"school\" title=\"FSL Sign: School\">🏫 Paaralan (School)</span>.\n    </p>\n    <div class=\"my-3\">\n        Binati niya ang kanyang <span class=\"fsl-word\" data-word=\"teacher\" title=\"FSL Sign: Teacher\">👩‍🏫 Guro (Teacher)</span> \n        at sinabing <span class=\"fsl-word\" data-word=\"thank you\" title=\"FSL Sign: Thank You\">🙏 Salamat (Thank You)</span>.\n    </div>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #f0fdf4; border: 2px solid #86efac; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-success mb-1\">💬 Pagsasanay sa Klase:</div>\n    <p class=\"fs-6 text-dark mb-0\">I-sign ang \"Salamat\" kapag tinulungan ka ng iyong kaklase o guro.</p>\n</div>','2. Ano ang dapat gawin bago at pagkatapos kumain?','none',NULL,'2026-09-19 19:24:27','2026-09-19 19:24:27'),(22,8,1,'Magiliw na Pagbati sa Umaga','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">☀️ 🍞 🥛</span>\n    <h3 class=\"fw-bold text-primary mt-2\">Magandang Umaga sa Hapag-Kainan!</h3>\n    <p class=\"text-muted small\">Pindutin ang mga makukulay na pindutan para mapanood ang tunay na galaw ng kamay (FSL Video)!</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">Maagang nagising si Ana. Masaya siyang bumati ng:</p>\n    <div class=\"my-3\">\n        <span class=\"fsl-word\" data-word=\"good morning\" title=\"FSL Sign: Good Morning\">☀️ Good Morning (Magandang Umaga)</span>\n    </div>\n    <p class=\"fs-5 lh-lg text-dark mb-0\">\n        Nakangiti ring sumagot si <span class=\"fsl-word\" data-word=\"mother\" title=\"FSL Sign: Mother\">👩 Nanay (Mother)</span> \n        at si <span class=\"fsl-word\" data-word=\"father\" title=\"FSL Sign: Father\">👨 Tatay (Father)</span>.\n    </p>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #eff6ff; border: 2px solid #93c5fd; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-primary mb-1\">💡 Subukan Natin:</div>\n    <p class=\"fs-6 text-dark mb-0\">Panoorin ang FSL video para sa <strong>Good Morning</strong> at gayahin ang tamang galaw ng kamay.</p>\n</div>','1. Paano mo babatiin ang iyong mga magulang pagkagising?','none',NULL,'2026-09-19 19:24:36','2026-09-19 19:24:36'),(23,8,2,'Ang Ating Paaralan at Kaibigan','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">🏫 📚 🤝</span>\n    <h3 class=\"fw-bold text-success mt-2\">Ang Ating Paaralan at mga Kaibigan</h3>\n    <p class=\"text-muted small\">Pindutin ang mga FSL signs para sa mga tao at lugar sa paaralan.</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">\n        Pumunta ang mag-aaral sa <span class=\"fsl-word\" data-word=\"school\" title=\"FSL Sign: School\">🏫 Paaralan (School)</span>.\n    </p>\n    <div class=\"my-3\">\n        Binati niya ang kanyang <span class=\"fsl-word\" data-word=\"teacher\" title=\"FSL Sign: Teacher\">👩‍🏫 Guro (Teacher)</span> \n        at sinabing <span class=\"fsl-word\" data-word=\"thank you\" title=\"FSL Sign: Thank You\">🙏 Salamat (Thank You)</span>.\n    </div>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #f0fdf4; border: 2px solid #86efac; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-success mb-1\">💬 Pagsasanay sa Klase:</div>\n    <p class=\"fs-6 text-dark mb-0\">I-sign ang \"Salamat\" kapag tinulungan ka ng iyong kaklase o guro.</p>\n</div>','2. Bakit mahalagang magpasalamat kapag may tumulong sa iyo?','none',NULL,'2026-09-19 19:24:36','2026-09-19 19:24:36'),(24,9,1,'Masustansyang Almusal at Pag-sign','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">☀️ 🍞 🥛</span>\n    <h3 class=\"fw-bold text-primary mt-2\">Magandang Umaga sa Hapag-Kainan!</h3>\n    <p class=\"text-muted small\">Pindutin ang mga makukulay na pindutan para mapanood ang tunay na galaw ng kamay (FSL Video)!</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">Maagang nagising si Ana. Masaya siyang bumati ng:</p>\n    <div class=\"my-3\">\n        <span class=\"fsl-word\" data-word=\"good morning\" title=\"FSL Sign: Good Morning\">☀️ Good Morning (Magandang Umaga)</span>\n    </div>\n    <p class=\"fs-5 lh-lg text-dark mb-0\">\n        Nakangiti ring sumagot si <span class=\"fsl-word\" data-word=\"mother\" title=\"FSL Sign: Mother\">👩 Nanay (Mother)</span> \n        at si <span class=\"fsl-word\" data-word=\"father\" title=\"FSL Sign: Father\">👨 Tatay (Father)</span>.\n    </p>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #eff6ff; border: 2px solid #93c5fd; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-primary mb-1\">💡 Subukan Natin:</div>\n    <p class=\"fs-6 text-dark mb-0\">Panoorin ang FSL video para sa <strong>Good Morning</strong> at gayahin ang tamang galaw ng kamay.</p>\n</div>','1. Anong masustansyang pagkain ang kinain mo ngayong umaga?','none',NULL,'2026-09-19 19:24:36','2026-09-19 19:24:36'),(25,9,2,'Kalinisan sa Hapag-Kainan','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">🏫 📚 🤝</span>\n    <h3 class=\"fw-bold text-success mt-2\">Ang Ating Paaralan at mga Kaibigan</h3>\n    <p class=\"text-muted small\">Pindutin ang mga FSL signs para sa mga tao at lugar sa paaralan.</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">\n        Pumunta ang mag-aaral sa <span class=\"fsl-word\" data-word=\"school\" title=\"FSL Sign: School\">🏫 Paaralan (School)</span>.\n    </p>\n    <div class=\"my-3\">\n        Binati niya ang kanyang <span class=\"fsl-word\" data-word=\"teacher\" title=\"FSL Sign: Teacher\">👩‍🏫 Guro (Teacher)</span> \n        at sinabing <span class=\"fsl-word\" data-word=\"thank you\" title=\"FSL Sign: Thank You\">🙏 Salamat (Thank You)</span>.\n    </div>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #f0fdf4; border: 2px solid #86efac; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-success mb-1\">💬 Pagsasanay sa Klase:</div>\n    <p class=\"fs-6 text-dark mb-0\">I-sign ang \"Salamat\" kapag tinulungan ka ng iyong kaklase o guro.</p>\n</div>','2. Ano ang dapat gawin bago at pagkatapos kumain?','none',NULL,'2026-09-19 19:24:36','2026-09-19 19:24:36'),(26,10,1,'Magiliw na Pagbati sa Umaga','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">☀️ 🍞 🥛</span>\n    <h3 class=\"fw-bold text-primary mt-2\">Magandang Umaga sa Hapag-Kainan!</h3>\n    <p class=\"text-muted small\">Pindutin ang mga makukulay na pindutan para mapanood ang tunay na galaw ng kamay (FSL Video)!</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">Maagang nagising si Ana. Masaya siyang bumati ng:</p>\n    <div class=\"my-3\">\n        <span class=\"fsl-word\" data-word=\"good morning\" title=\"FSL Sign: Good Morning\">☀️ Good Morning (Magandang Umaga)</span>\n    </div>\n    <p class=\"fs-5 lh-lg text-dark mb-0\">\n        Nakangiti ring sumagot si <span class=\"fsl-word\" data-word=\"mother\" title=\"FSL Sign: Mother\">👩 Nanay (Mother)</span> \n        at si <span class=\"fsl-word\" data-word=\"father\" title=\"FSL Sign: Father\">👨 Tatay (Father)</span>.\n    </p>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #eff6ff; border: 2px solid #93c5fd; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-primary mb-1\">💡 Subukan Natin:</div>\n    <p class=\"fs-6 text-dark mb-0\">Panoorin ang FSL video para sa <strong>Good Morning</strong> at gayahin ang tamang galaw ng kamay.</p>\n</div>','1. Paano mo babatiin ang iyong mga magulang pagkagising?','none',NULL,'2026-09-19 19:24:44','2026-09-19 19:24:44'),(27,10,2,'Ang Ating Paaralan at Kaibigan','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">🏫 📚 🤝</span>\n    <h3 class=\"fw-bold text-success mt-2\">Ang Ating Paaralan at mga Kaibigan</h3>\n    <p class=\"text-muted small\">Pindutin ang mga FSL signs para sa mga tao at lugar sa paaralan.</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">\n        Pumunta ang mag-aaral sa <span class=\"fsl-word\" data-word=\"school\" title=\"FSL Sign: School\">🏫 Paaralan (School)</span>.\n    </p>\n    <div class=\"my-3\">\n        Binati niya ang kanyang <span class=\"fsl-word\" data-word=\"teacher\" title=\"FSL Sign: Teacher\">👩‍🏫 Guro (Teacher)</span> \n        at sinabing <span class=\"fsl-word\" data-word=\"thank you\" title=\"FSL Sign: Thank You\">🙏 Salamat (Thank You)</span>.\n    </div>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #f0fdf4; border: 2px solid #86efac; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-success mb-1\">💬 Pagsasanay sa Klase:</div>\n    <p class=\"fs-6 text-dark mb-0\">I-sign ang \"Salamat\" kapag tinulungan ka ng iyong kaklase o guro.</p>\n</div>','2. Bakit mahalagang magpasalamat kapag may tumulong sa iyo?','none',NULL,'2026-09-19 19:24:44','2026-09-19 19:24:44'),(28,11,1,'Masustansyang Almusal at Pag-sign','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">☀️ 🍞 🥛</span>\n    <h3 class=\"fw-bold text-primary mt-2\">Magandang Umaga sa Hapag-Kainan!</h3>\n    <p class=\"text-muted small\">Pindutin ang mga makukulay na pindutan para mapanood ang tunay na galaw ng kamay (FSL Video)!</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">Maagang nagising si Ana. Masaya siyang bumati ng:</p>\n    <div class=\"my-3\">\n        <span class=\"fsl-word\" data-word=\"good morning\" title=\"FSL Sign: Good Morning\">☀️ Good Morning (Magandang Umaga)</span>\n    </div>\n    <p class=\"fs-5 lh-lg text-dark mb-0\">\n        Nakangiti ring sumagot si <span class=\"fsl-word\" data-word=\"mother\" title=\"FSL Sign: Mother\">👩 Nanay (Mother)</span> \n        at si <span class=\"fsl-word\" data-word=\"father\" title=\"FSL Sign: Father\">👨 Tatay (Father)</span>.\n    </p>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #eff6ff; border: 2px solid #93c5fd; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-primary mb-1\">💡 Subukan Natin:</div>\n    <p class=\"fs-6 text-dark mb-0\">Panoorin ang FSL video para sa <strong>Good Morning</strong> at gayahin ang tamang galaw ng kamay.</p>\n</div>','1. Anong masustansyang pagkain ang kinain mo ngayong umaga?','none',NULL,'2026-09-19 19:24:44','2026-09-19 19:24:44'),(29,11,2,'Kalinisan sa Hapag-Kainan','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">🏫 📚 🤝</span>\n    <h3 class=\"fw-bold text-success mt-2\">Ang Ating Paaralan at mga Kaibigan</h3>\n    <p class=\"text-muted small\">Pindutin ang mga FSL signs para sa mga tao at lugar sa paaralan.</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">\n        Pumunta ang mag-aaral sa <span class=\"fsl-word\" data-word=\"school\" title=\"FSL Sign: School\">🏫 Paaralan (School)</span>.\n    </p>\n    <div class=\"my-3\">\n        Binati niya ang kanyang <span class=\"fsl-word\" data-word=\"teacher\" title=\"FSL Sign: Teacher\">👩‍🏫 Guro (Teacher)</span> \n        at sinabing <span class=\"fsl-word\" data-word=\"thank you\" title=\"FSL Sign: Thank You\">🙏 Salamat (Thank You)</span>.\n    </div>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #f0fdf4; border: 2px solid #86efac; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-success mb-1\">💬 Pagsasanay sa Klase:</div>\n    <p class=\"fs-6 text-dark mb-0\">I-sign ang \"Salamat\" kapag tinulungan ka ng iyong kaklase o guro.</p>\n</div>','2. Ano ang dapat gawin bago at pagkatapos kumain?','none',NULL,'2026-09-19 19:24:44','2026-09-19 19:24:44'),(30,12,1,'Magiliw na Pagbati sa Umaga','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">☀️ 🍞 🥛</span>\n    <h3 class=\"fw-bold text-primary mt-2\">Magandang Umaga sa Hapag-Kainan!</h3>\n    <p class=\"text-muted small\">Pindutin ang mga makukulay na pindutan para mapanood ang tunay na galaw ng kamay (FSL Video)!</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">Maagang nagising si Ana. Masaya siyang bumati ng:</p>\n    <div class=\"my-3\">\n        <span class=\"fsl-word\" data-word=\"good morning\" title=\"FSL Sign: Good Morning\">☀️ Good Morning (Magandang Umaga)</span>\n    </div>\n    <p class=\"fs-5 lh-lg text-dark mb-0\">\n        Nakangiti ring sumagot si <span class=\"fsl-word\" data-word=\"mother\" title=\"FSL Sign: Mother\">👩 Nanay (Mother)</span> \n        at si <span class=\"fsl-word\" data-word=\"father\" title=\"FSL Sign: Father\">👨 Tatay (Father)</span>.\n    </p>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #eff6ff; border: 2px solid #93c5fd; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-primary mb-1\">💡 Subukan Natin:</div>\n    <p class=\"fs-6 text-dark mb-0\">Panoorin ang FSL video para sa <strong>Good Morning</strong> at gayahin ang tamang galaw ng kamay.</p>\n</div>','1. Paano mo babatiin ang iyong mga magulang pagkagising?','none',NULL,'2026-09-19 19:24:52','2026-09-19 19:24:52'),(31,12,2,'Ang Ating Paaralan at Kaibigan','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">🏫 📚 🤝</span>\n    <h3 class=\"fw-bold text-success mt-2\">Ang Ating Paaralan at mga Kaibigan</h3>\n    <p class=\"text-muted small\">Pindutin ang mga FSL signs para sa mga tao at lugar sa paaralan.</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">\n        Pumunta ang mag-aaral sa <span class=\"fsl-word\" data-word=\"school\" title=\"FSL Sign: School\">🏫 Paaralan (School)</span>.\n    </p>\n    <div class=\"my-3\">\n        Binati niya ang kanyang <span class=\"fsl-word\" data-word=\"teacher\" title=\"FSL Sign: Teacher\">👩‍🏫 Guro (Teacher)</span> \n        at sinabing <span class=\"fsl-word\" data-word=\"thank you\" title=\"FSL Sign: Thank You\">🙏 Salamat (Thank You)</span>.\n    </div>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #f0fdf4; border: 2px solid #86efac; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-success mb-1\">💬 Pagsasanay sa Klase:</div>\n    <p class=\"fs-6 text-dark mb-0\">I-sign ang \"Salamat\" kapag tinulungan ka ng iyong kaklase o guro.</p>\n</div>','2. Bakit mahalagang magpasalamat kapag may tumulong sa iyo?','none',NULL,'2026-09-19 19:24:52','2026-09-19 19:24:52'),(32,13,1,'Masustansyang Almusal at Pag-sign','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">☀️ 🍞 🥛</span>\n    <h3 class=\"fw-bold text-primary mt-2\">Magandang Umaga sa Hapag-Kainan!</h3>\n    <p class=\"text-muted small\">Pindutin ang mga makukulay na pindutan para mapanood ang tunay na galaw ng kamay (FSL Video)!</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">Maagang nagising si Ana. Masaya siyang bumati ng:</p>\n    <div class=\"my-3\">\n        <span class=\"fsl-word\" data-word=\"good morning\" title=\"FSL Sign: Good Morning\">☀️ Good Morning (Magandang Umaga)</span>\n    </div>\n    <p class=\"fs-5 lh-lg text-dark mb-0\">\n        Nakangiti ring sumagot si <span class=\"fsl-word\" data-word=\"mother\" title=\"FSL Sign: Mother\">👩 Nanay (Mother)</span> \n        at si <span class=\"fsl-word\" data-word=\"father\" title=\"FSL Sign: Father\">👨 Tatay (Father)</span>.\n    </p>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #eff6ff; border: 2px solid #93c5fd; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-primary mb-1\">💡 Subukan Natin:</div>\n    <p class=\"fs-6 text-dark mb-0\">Panoorin ang FSL video para sa <strong>Good Morning</strong> at gayahin ang tamang galaw ng kamay.</p>\n</div>','1. Anong masustansyang pagkain ang kinain mo ngayong umaga?','none',NULL,'2026-09-19 19:24:52','2026-09-19 19:24:52'),(33,13,2,'Kalinisan sa Hapag-Kainan','<div class=\"text-center mb-4\">\n    <span style=\"font-size: 3.5rem; display: block;\">🏫 📚 🤝</span>\n    <h3 class=\"fw-bold text-success mt-2\">Ang Ating Paaralan at mga Kaibigan</h3>\n    <p class=\"text-muted small\">Pindutin ang mga FSL signs para sa mga tao at lugar sa paaralan.</p>\n</div>\n<div class=\"p-4 bg-light rounded-4 border shadow-sm mb-4 text-center\">\n    <p class=\"fs-5 lh-lg text-dark mb-3\">\n        Pumunta ang mag-aaral sa <span class=\"fsl-word\" data-word=\"school\" title=\"FSL Sign: School\">🏫 Paaralan (School)</span>.\n    </p>\n    <div class=\"my-3\">\n        Binati niya ang kanyang <span class=\"fsl-word\" data-word=\"teacher\" title=\"FSL Sign: Teacher\">👩‍🏫 Guro (Teacher)</span> \n        at sinabing <span class=\"fsl-word\" data-word=\"thank you\" title=\"FSL Sign: Thank You\">🙏 Salamat (Thank You)</span>.\n    </div>\n</div>\n<div data-shape-box=\"true\" class=\"shape-container p-3 my-2 rounded-4 shadow-sm text-center\" style=\"background-color: #f0fdf4; border: 2px solid #86efac; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;\">\n    <div class=\"small fw-bold text-success mb-1\">💬 Pagsasanay sa Klase:</div>\n    <p class=\"fs-6 text-dark mb-0\">I-sign ang \"Salamat\" kapag tinulungan ka ng iyong kaklase o guro.</p>\n</div>','2. Ano ang dapat gawin bago at pagkatapos kumain?','none',NULL,'2026-09-19 19:24:52','2026-09-19 19:24:52');
/*!40000 ALTER TABLE `lesson_plan_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lesson_plans`
--

DROP TABLE IF EXISTS `lesson_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lesson_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `iep_id` int NOT NULL,
  `student_id` int DEFAULT NULL,
  `created_by` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pdsp_domain` enum('perceptuo_cognitive','psychosocial','socio_emotional','psychomotor','daily_living_skills','communication_language') COLLATE utf8mb4_unicode_ci NOT NULL,
  `assignment_type` enum('individual','shared') COLLATE utf8mb4_unicode_ci DEFAULT 'individual',
  `is_remedial` tinyint(1) NOT NULL DEFAULT '0',
  `remedial_for_lesson_id` int DEFAULT NULL,
  `score_threshold` int NOT NULL DEFAULT '70',
  `fsl_highlighted_words` text COLLATE utf8mb4_unicode_ci,
  `document_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_iep_id` (`iep_id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_status` (`status`),
  CONSTRAINT `lesson_plans_ibfk_1` FOREIGN KEY (`iep_id`) REFERENCES `iep_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_plans_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_plans`
--

LOCK TABLES `lesson_plans` WRITE;
/*!40000 ALTER TABLE `lesson_plans` DISABLE KEYS */;
INSERT INTO `lesson_plans` VALUES (1,2,4,6,'Aralin 1: Pagbati at Aking Pamilya (FSL Video Signs)','perceptuo_cognitive','individual',0,NULL,70,'hello,good morning,thank you,father,mother,boy,girl,yes,no,red,blue','uploads/lesson_plans/4/lp_1_1788891798.pdf','published','2026-09-19 19:24:23','2026-09-08 18:23:18','2026-09-19 19:24:23'),(3,2,4,6,'Aralin 2: Ang Masayang Almusal Kasama si Nanay at Tatay','daily_living_skills','individual',0,NULL,70,'good morning,mother,father,bread,egg,milk,thank you,yes,no,one,two,three','uploads/lesson_plans/4/dummy_lesson_2.pdf','published','2026-09-19 19:24:23','2026-09-10 21:24:18','2026-09-19 19:24:23'),(4,3,5,6,'lesson 1','daily_living_skills','individual',0,NULL,70,NULL,'uploads/lesson_plans/5/lp_4_1789837757.pdf','published','2026-09-19 17:09:36','2026-09-19 17:09:16','2026-09-19 17:09:36'),(5,3,5,6,'lesson 2','daily_living_skills','individual',0,NULL,70,NULL,'uploads/lesson_plans/5/lp_5_1789837768.pdf','published','2026-09-19 17:09:33','2026-09-19 17:09:28','2026-09-19 17:09:33'),(6,3,5,6,'Aralin 1: Pagbati at Aking Pamilya (FSL Video Signs)','','individual',0,NULL,70,NULL,NULL,'published','2026-09-19 19:24:27','2026-09-19 19:24:10','2026-09-19 19:24:27'),(7,3,5,6,'Aralin 2: Ang Masayang Almusal Kasama si Nanay at Tatay','daily_living_skills','individual',0,NULL,70,NULL,NULL,'published','2026-09-19 19:24:27','2026-09-19 19:24:27','2026-09-19 19:24:27'),(8,4,6,6,'Aralin 1: Pagbati at Aking Pamilya (FSL Video Signs)','','individual',0,NULL,70,NULL,NULL,'published','2026-09-19 19:24:36','2026-09-19 19:24:36','2026-09-19 19:24:36'),(9,4,6,6,'Aralin 2: Ang Masayang Almusal Kasama si Nanay at Tatay','daily_living_skills','individual',0,NULL,70,NULL,NULL,'published','2026-09-19 19:24:36','2026-09-19 19:24:36','2026-09-19 19:24:36'),(10,5,7,6,'Aralin 1: Pagbati at Aking Pamilya (FSL Video Signs)','','individual',0,NULL,70,NULL,NULL,'published','2026-09-19 19:24:44','2026-09-19 19:24:44','2026-09-19 19:24:44'),(11,5,7,6,'Aralin 2: Ang Masayang Almusal Kasama si Nanay at Tatay','daily_living_skills','individual',0,NULL,70,NULL,NULL,'published','2026-09-19 19:24:44','2026-09-19 19:24:44','2026-09-19 19:24:44'),(12,6,8,6,'Aralin 1: Pagbati at Aking Pamilya (FSL Video Signs)','','individual',0,NULL,70,NULL,NULL,'published','2026-09-19 19:24:52','2026-09-19 19:24:52','2026-09-19 19:24:52'),(13,6,8,6,'Aralin 2: Ang Masayang Almusal Kasama si Nanay at Tatay','daily_living_skills','individual',0,NULL,70,NULL,NULL,'published','2026-09-19 19:24:52','2026-09-19 19:24:52','2026-09-19 19:24:52');
/*!40000 ALTER TABLE `lesson_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lis_sync_logs`
--

DROP TABLE IF EXISTS `lis_sync_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lis_sync_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `sync_type` enum('sf1_export','sf2_export','sf2_import') COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `records_count` int DEFAULT '0',
  `performed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_sync_type` (`sync_type`),
  KEY `idx_performed_at` (`performed_at`),
  CONSTRAINT `lis_sync_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lis_sync_logs`
--

LOCK TABLES `lis_sync_logs` WRITE;
/*!40000 ALTER TABLE `lis_sync_logs` DISABLE KEYS */;
INSERT INTO `lis_sync_logs` VALUES (1,1,'sf1_export','test_sf1_export.csv',0,'2026-08-20 18:30:06'),(2,1,'sf1_export','test_sf1_export.csv',1,'2026-08-20 18:30:23'),(3,6,'sf1_export','SF1_School_Register_2026-08-20_190744.csv',0,'2026-08-20 19:07:44'),(4,6,'sf2_export','SF2_Daily_Attendance_2026-08_190806.csv',0,'2026-08-20 19:08:06'),(5,6,'sf1_export','SF1_School_Register_2026-09-21_152333.csv',5,'2026-09-21 15:23:33'),(6,6,'','Learner_Enrollment_Register_2026-09-21_174643.csv',11,'2026-09-21 17:46:43');
/*!40000 ALTER TABLE `lis_sync_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lms_activities`
--

DROP TABLE IF EXISTS `lms_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lms_activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lesson_plan_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instructions` longtext COLLATE utf8mb4_unicode_ci,
  `activity_type` enum('multiple_choice','true_false','fill_in_blanks','matching','drag_drop_sort','image_label','flashcards','sequencing') COLLATE utf8mb4_unicode_ci NOT NULL,
  `activity_data` json NOT NULL,
  `max_score` int DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `display_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_f2f` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Boolean flag for Face-to-Face / Direct Observation activity',
  PRIMARY KEY (`id`),
  KEY `idx_lesson_plan_id` (`lesson_plan_id`),
  KEY `idx_activity_type` (`activity_type`),
  KEY `idx_display_order` (`display_order`),
  CONSTRAINT `lms_activities_ibfk_1` FOREIGN KEY (`lesson_plan_id`) REFERENCES `lesson_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lms_activities`
--

LOCK TABLES `lms_activities` WRITE;
/*!40000 ALTER TABLE `lms_activities` DISABLE KEYS */;
INSERT INTO `lms_activities` VALUES (2,1,'Mission Activity 1: School & Community Sign Quiz','Read each question carefully. Click on underlined FSL words or hand icons to pop up the Filipino Sign Language demonstration video/gif. If you get a question wrong, you can retry to get 100% accuracy!','multiple_choice','{\"questions\": [{\"points\": 5, \"options\": [{\"text\": \"Teacher (Guro)\", \"is_correct\": 1}, {\"text\": \"Doctor\", \"is_correct\": 0}, {\"text\": \"Pilot\", \"is_correct\": 0}], \"fsl_hint\": \"teacher\", \"question\": \"Who guides and teaches the class inside the room? (Click the FSL sign if you need a clue!)\"}, {\"points\": 5, \"options\": [{\"text\": \"Market\", \"is_correct\": 0}, {\"text\": \"School (Tunghaan)\", \"is_correct\": 1}, {\"text\": \"Airport\", \"is_correct\": 0}], \"fsl_hint\": \"school\", \"question\": \"Where do students go to study and meet classmates?\"}, {\"points\": 5, \"options\": [{\"text\": \"Thank You (Salamat)\", \"is_correct\": 1}, {\"text\": \"Goodbye\", \"is_correct\": 0}, {\"text\": \"No\", \"is_correct\": 0}], \"fsl_hint\": \"thank you\", \"question\": \"What polite FSL sign do you do when a friend helps you with your lesson?\"}]}',15,'2026-09-18 04:05:17',1,'2026-09-10 20:05:17',0),(3,3,'Laro at Pagsasanay: Ang Ating Almusal at FSL Signs','Piliin ang tamang sagot. Pindutin ang video hint kung kailangan ng tulong sa senyas!','multiple_choice','{\"questions\": [{\"points\": 10, \"options\": [{\"text\": \"Good Morning (Magandang Umaga)\", \"is_correct\": 1}, {\"text\": \"Good Night (Magandang Gabi)\", \"is_correct\": 0}, {\"text\": \"No (Hindi)\", \"is_correct\": 0}], \"fsl_hint\": \"good morning\", \"question\": \"Ano ang sinasabi natin tuwing umaga bago mag-almusal? (Panoorin ang FSL Hint!)\"}, {\"points\": 10, \"options\": [{\"text\": \"Milk (Gatas)\", \"is_correct\": 1}, {\"text\": \"Coffee (Kape)\", \"is_correct\": 0}, {\"text\": \"Wine\", \"is_correct\": 0}], \"fsl_hint\": \"milk\", \"question\": \"Alin sa mga sumusunod ang masustansyang inuming pampalakas ng katawan?\"}, {\"points\": 10, \"options\": [{\"text\": \"Thank You (Salamat)\", \"is_correct\": 1}, {\"text\": \"Wrong (Mali)\", \"is_correct\": 0}, {\"text\": \"Fast (Mabilis)\", \"is_correct\": 0}], \"fsl_hint\": \"thank you\", \"question\": \"Ano ang tamang senyas ng pasasalamat kapag binigyan ka ng pagkain ni Nanay?\"}]}',30,NULL,0,'2026-09-10 21:24:18',0),(4,6,'Pagsasanay 1: Pagkilala sa mga Pagbati at Pamilya (FSL Quiz)','Piliin ang tamang sagot. Pindutin ang FSL hint kung kailangan ng gabay.','multiple_choice','{\"questions\": [{\"points\": 5, \"options\": [{\"text\": \"Good Morning (Magandang Umaga)\", \"is_correct\": 1}, {\"text\": \"Good Night\", \"is_correct\": 0}, {\"text\": \"No\", \"is_correct\": 0}], \"fsl_hint\": \"good morning\", \"question\": \"Ano ang tamang FSL sign kapag bumabati sa umaga paggising?\"}, {\"points\": 5, \"options\": [{\"text\": \"Nanay (Mother)\", \"is_correct\": 1}, {\"text\": \"Pulis\", \"is_correct\": 0}, {\"text\": \"Doktor\", \"is_correct\": 0}], \"fsl_hint\": \"mother\", \"question\": \"Sino ang nag-aalaga at naghahanda ng pagkain sa bahay kasama si Tatay?\"}, {\"points\": 5, \"options\": [{\"text\": \"Thank You (Salamat)\", \"is_correct\": 1}, {\"text\": \"Goodbye\", \"is_correct\": 0}, {\"text\": \"Sorry\", \"is_correct\": 0}], \"fsl_hint\": \"thank you\", \"question\": \"Ano ang magalang na FSL sign kapag binigyan ka ng tulong o regalo?\"}]}',15,NULL,1,'2026-09-19 19:24:27',0),(5,7,'Pagsasanay 2: Paaralan at Pang-araw-araw na Kasanayan','Subukan ang iyong natutunan sa pag-sign at kasanayan sa bahay.','multiple_choice','{\"questions\": [{\"points\": 5, \"options\": [{\"text\": \"School (Tunghaan / Paaralan)\", \"is_correct\": 1}, {\"text\": \"Palengke\", \"is_correct\": 0}, {\"text\": \"Sinehan\", \"is_correct\": 0}], \"fsl_hint\": \"school\", \"question\": \"Saan pumapasok ang mga mag-aaral upang magbasa at mag-sign?\"}, {\"points\": 5, \"options\": [{\"text\": \"Teacher (Guro)\", \"is_correct\": 1}, {\"text\": \"Drayber\", \"is_correct\": 0}, {\"text\": \"Kusinero\", \"is_correct\": 0}], \"fsl_hint\": \"teacher\", \"question\": \"Sino ang nagtuturo ng mga aralin sa loob ng silid-aralan?\"}, {\"points\": 5, \"options\": [{\"text\": \"Maghugas ng kamay nang malinis\", \"is_correct\": 1}, {\"text\": \"Maglaro ng bola\", \"is_correct\": 0}, {\"text\": \"Matulog agad\", \"is_correct\": 0}], \"fsl_hint\": \"eat\", \"question\": \"Ano ang nararapat gawin bago kumain sa hapag-kainan?\"}]}',15,NULL,2,'2026-09-19 19:24:27',0),(6,8,'Pagsasanay 1: Pagkilala sa mga Pagbati at Pamilya (FSL Quiz)','Piliin ang tamang sagot. Pindutin ang FSL hint kung kailangan ng gabay.','multiple_choice','{\"questions\": [{\"points\": 5, \"options\": [{\"text\": \"Good Morning (Magandang Umaga)\", \"is_correct\": 1}, {\"text\": \"Good Night\", \"is_correct\": 0}, {\"text\": \"No\", \"is_correct\": 0}], \"fsl_hint\": \"good morning\", \"question\": \"Ano ang tamang FSL sign kapag bumabati sa umaga paggising?\"}, {\"points\": 5, \"options\": [{\"text\": \"Nanay (Mother)\", \"is_correct\": 1}, {\"text\": \"Pulis\", \"is_correct\": 0}, {\"text\": \"Doktor\", \"is_correct\": 0}], \"fsl_hint\": \"mother\", \"question\": \"Sino ang nag-aalaga at naghahanda ng pagkain sa bahay kasama si Tatay?\"}, {\"points\": 5, \"options\": [{\"text\": \"Thank You (Salamat)\", \"is_correct\": 1}, {\"text\": \"Goodbye\", \"is_correct\": 0}, {\"text\": \"Sorry\", \"is_correct\": 0}], \"fsl_hint\": \"thank you\", \"question\": \"Ano ang magalang na FSL sign kapag binigyan ka ng tulong o regalo?\"}]}',15,NULL,1,'2026-09-19 19:24:36',0),(7,9,'Pagsasanay 2: Paaralan at Pang-araw-araw na Kasanayan','Subukan ang iyong natutunan sa pag-sign at kasanayan sa bahay.','multiple_choice','{\"questions\": [{\"points\": 5, \"options\": [{\"text\": \"School (Tunghaan / Paaralan)\", \"is_correct\": 1}, {\"text\": \"Palengke\", \"is_correct\": 0}, {\"text\": \"Sinehan\", \"is_correct\": 0}], \"fsl_hint\": \"school\", \"question\": \"Saan pumapasok ang mga mag-aaral upang magbasa at mag-sign?\"}, {\"points\": 5, \"options\": [{\"text\": \"Teacher (Guro)\", \"is_correct\": 1}, {\"text\": \"Drayber\", \"is_correct\": 0}, {\"text\": \"Kusinero\", \"is_correct\": 0}], \"fsl_hint\": \"teacher\", \"question\": \"Sino ang nagtuturo ng mga aralin sa loob ng silid-aralan?\"}, {\"points\": 5, \"options\": [{\"text\": \"Maghugas ng kamay nang malinis\", \"is_correct\": 1}, {\"text\": \"Maglaro ng bola\", \"is_correct\": 0}, {\"text\": \"Matulog agad\", \"is_correct\": 0}], \"fsl_hint\": \"eat\", \"question\": \"Ano ang nararapat gawin bago kumain sa hapag-kainan?\"}]}',15,NULL,2,'2026-09-19 19:24:36',0),(8,10,'Pagsasanay 1: Pagkilala sa mga Pagbati at Pamilya (FSL Quiz)','Piliin ang tamang sagot. Pindutin ang FSL hint kung kailangan ng gabay.','multiple_choice','{\"questions\": [{\"points\": 5, \"options\": [{\"text\": \"Good Morning (Magandang Umaga)\", \"is_correct\": 1}, {\"text\": \"Good Night\", \"is_correct\": 0}, {\"text\": \"No\", \"is_correct\": 0}], \"fsl_hint\": \"good morning\", \"question\": \"Ano ang tamang FSL sign kapag bumabati sa umaga paggising?\"}, {\"points\": 5, \"options\": [{\"text\": \"Nanay (Mother)\", \"is_correct\": 1}, {\"text\": \"Pulis\", \"is_correct\": 0}, {\"text\": \"Doktor\", \"is_correct\": 0}], \"fsl_hint\": \"mother\", \"question\": \"Sino ang nag-aalaga at naghahanda ng pagkain sa bahay kasama si Tatay?\"}, {\"points\": 5, \"options\": [{\"text\": \"Thank You (Salamat)\", \"is_correct\": 1}, {\"text\": \"Goodbye\", \"is_correct\": 0}, {\"text\": \"Sorry\", \"is_correct\": 0}], \"fsl_hint\": \"thank you\", \"question\": \"Ano ang magalang na FSL sign kapag binigyan ka ng tulong o regalo?\"}]}',15,NULL,1,'2026-09-19 19:24:44',0),(9,11,'Pagsasanay 2: Paaralan at Pang-araw-araw na Kasanayan','Subukan ang iyong natutunan sa pag-sign at kasanayan sa bahay.','multiple_choice','{\"questions\": [{\"points\": 5, \"options\": [{\"text\": \"School (Tunghaan / Paaralan)\", \"is_correct\": 1}, {\"text\": \"Palengke\", \"is_correct\": 0}, {\"text\": \"Sinehan\", \"is_correct\": 0}], \"fsl_hint\": \"school\", \"question\": \"Saan pumapasok ang mga mag-aaral upang magbasa at mag-sign?\"}, {\"points\": 5, \"options\": [{\"text\": \"Teacher (Guro)\", \"is_correct\": 1}, {\"text\": \"Drayber\", \"is_correct\": 0}, {\"text\": \"Kusinero\", \"is_correct\": 0}], \"fsl_hint\": \"teacher\", \"question\": \"Sino ang nagtuturo ng mga aralin sa loob ng silid-aralan?\"}, {\"points\": 5, \"options\": [{\"text\": \"Maghugas ng kamay nang malinis\", \"is_correct\": 1}, {\"text\": \"Maglaro ng bola\", \"is_correct\": 0}, {\"text\": \"Matulog agad\", \"is_correct\": 0}], \"fsl_hint\": \"eat\", \"question\": \"Ano ang nararapat gawin bago kumain sa hapag-kainan?\"}]}',15,NULL,2,'2026-09-19 19:24:44',0),(10,12,'Pagsasanay 1: Pagkilala sa mga Pagbati at Pamilya (FSL Quiz)','Piliin ang tamang sagot. Pindutin ang FSL hint kung kailangan ng gabay.','multiple_choice','{\"questions\": [{\"points\": 5, \"options\": [{\"text\": \"Good Morning (Magandang Umaga)\", \"is_correct\": 1}, {\"text\": \"Good Night\", \"is_correct\": 0}, {\"text\": \"No\", \"is_correct\": 0}], \"fsl_hint\": \"good morning\", \"question\": \"Ano ang tamang FSL sign kapag bumabati sa umaga paggising?\"}, {\"points\": 5, \"options\": [{\"text\": \"Nanay (Mother)\", \"is_correct\": 1}, {\"text\": \"Pulis\", \"is_correct\": 0}, {\"text\": \"Doktor\", \"is_correct\": 0}], \"fsl_hint\": \"mother\", \"question\": \"Sino ang nag-aalaga at naghahanda ng pagkain sa bahay kasama si Tatay?\"}, {\"points\": 5, \"options\": [{\"text\": \"Thank You (Salamat)\", \"is_correct\": 1}, {\"text\": \"Goodbye\", \"is_correct\": 0}, {\"text\": \"Sorry\", \"is_correct\": 0}], \"fsl_hint\": \"thank you\", \"question\": \"Ano ang magalang na FSL sign kapag binigyan ka ng tulong o regalo?\"}]}',15,NULL,1,'2026-09-19 19:24:52',0),(11,13,'Pagsasanay 2: Paaralan at Pang-araw-araw na Kasanayan','Subukan ang iyong natutunan sa pag-sign at kasanayan sa bahay.','multiple_choice','{\"questions\": [{\"points\": 5, \"options\": [{\"text\": \"School (Tunghaan / Paaralan)\", \"is_correct\": 1}, {\"text\": \"Palengke\", \"is_correct\": 0}, {\"text\": \"Sinehan\", \"is_correct\": 0}], \"fsl_hint\": \"school\", \"question\": \"Saan pumapasok ang mga mag-aaral upang magbasa at mag-sign?\"}, {\"points\": 5, \"options\": [{\"text\": \"Teacher (Guro)\", \"is_correct\": 1}, {\"text\": \"Drayber\", \"is_correct\": 0}, {\"text\": \"Kusinero\", \"is_correct\": 0}], \"fsl_hint\": \"teacher\", \"question\": \"Sino ang nagtuturo ng mga aralin sa loob ng silid-aralan?\"}, {\"points\": 5, \"options\": [{\"text\": \"Maghugas ng kamay nang malinis\", \"is_correct\": 1}, {\"text\": \"Maglaro ng bola\", \"is_correct\": 0}, {\"text\": \"Matulog agad\", \"is_correct\": 0}], \"fsl_hint\": \"eat\", \"question\": \"Ano ang nararapat gawin bago kumain sa hapag-kainan?\"}]}',15,NULL,2,'2026-09-19 19:24:52',0);
/*!40000 ALTER TABLE `lms_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lms_grades`
--

DROP TABLE IF EXISTS `lms_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lms_grades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `submission_id` int NOT NULL,
  `graded_by` int NOT NULL,
  `score` int DEFAULT NULL,
  `max_score` int DEFAULT NULL,
  `is_complete` tinyint(1) DEFAULT '0',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `graded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_grade` (`submission_id`),
  KEY `graded_by` (`graded_by`),
  KEY `idx_graded_at` (`graded_at`),
  CONSTRAINT `lms_grades_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `lms_submissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lms_grades_ibfk_2` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lms_grades`
--

LOCK TABLES `lms_grades` WRITE;
/*!40000 ALTER TABLE `lms_grades` DISABLE KEYS */;
INSERT INTO `lms_grades` VALUES (9,13,6,15,15,1,'Mahusay! Naipakita ang tamang FSL signs.','2026-09-19 19:24:23'),(10,14,6,15,15,1,'Naisakatuparan nang maayos ang interactive quiz.','2026-09-19 19:24:23'),(11,15,6,15,15,1,'Mahusay! Naipakita ang tamang FSL signs.','2026-09-19 19:24:27'),(12,16,6,15,15,1,'Naisakatuparan nang maayos ang interactive quiz.','2026-09-19 19:24:27'),(13,17,6,10,15,1,'Mahusay! Naipakita ang tamang FSL signs.','2026-09-19 19:24:36'),(14,18,6,10,15,1,'Naisakatuparan nang maayos ang interactive quiz.','2026-09-19 19:24:36'),(15,19,6,15,15,1,'Mahusay! Naipakita ang tamang FSL signs.','2026-09-19 19:24:44'),(16,20,6,10,15,1,'Naisakatuparan nang maayos ang interactive quiz.','2026-09-19 19:24:44'),(17,21,6,15,15,1,'Mahusay! Naipakita ang tamang FSL signs.','2026-09-19 19:24:52'),(18,22,6,15,15,1,'Naisakatuparan nang maayos ang interactive quiz.','2026-09-19 19:24:52');
/*!40000 ALTER TABLE `lms_grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lms_logs`
--

DROP TABLE IF EXISTS `lms_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lms_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `activity_id` int DEFAULT NULL,
  `material_id` int DEFAULT NULL,
  `action` enum('opened','submitted','graded') COLLATE utf8mb4_unicode_ci NOT NULL,
  `performed_by` int NOT NULL,
  `performed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `performed_by` (`performed_by`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_action` (`action`),
  KEY `idx_performed_at` (`performed_at`),
  CONSTRAINT `lms_logs_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lms_logs_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lms_logs`
--

LOCK TABLES `lms_logs` WRITE;
/*!40000 ALTER TABLE `lms_logs` DISABLE KEYS */;
INSERT INTO `lms_logs` VALUES (1,4,NULL,NULL,'opened',10,'2026-09-10 20:21:59'),(2,4,NULL,NULL,'opened',10,'2026-09-10 21:29:00'),(3,4,NULL,NULL,'opened',10,'2026-09-10 21:38:53'),(4,4,NULL,NULL,'opened',10,'2026-09-10 21:58:46'),(5,4,NULL,NULL,'opened',10,'2026-09-10 21:58:59'),(6,4,NULL,NULL,'opened',10,'2026-09-10 21:58:59'),(7,4,NULL,NULL,'opened',10,'2026-09-10 21:58:59'),(8,4,NULL,NULL,'opened',10,'2026-09-10 21:59:02'),(9,4,NULL,NULL,'opened',10,'2026-09-10 21:59:22'),(10,4,NULL,NULL,'opened',10,'2026-09-10 21:59:24'),(11,4,NULL,NULL,'opened',10,'2026-09-10 21:59:30'),(12,4,NULL,NULL,'opened',10,'2026-09-10 21:59:31'),(13,4,NULL,NULL,'opened',10,'2026-09-10 21:59:31'),(14,4,NULL,NULL,'opened',10,'2026-09-10 21:59:31'),(15,4,NULL,NULL,'opened',10,'2026-09-10 21:59:31'),(16,4,NULL,NULL,'opened',10,'2026-09-10 21:59:31'),(17,4,NULL,NULL,'opened',10,'2026-09-10 22:01:25'),(18,4,NULL,NULL,'opened',10,'2026-09-10 22:01:38'),(19,4,NULL,NULL,'opened',10,'2026-09-10 22:04:42'),(20,4,NULL,NULL,'opened',10,'2026-09-11 10:37:06'),(21,4,NULL,NULL,'opened',10,'2026-09-11 12:14:35'),(22,4,NULL,NULL,'opened',10,'2026-09-11 12:15:16'),(23,4,NULL,NULL,'opened',10,'2026-09-11 12:15:22'),(24,4,NULL,NULL,'opened',10,'2026-09-11 12:17:58'),(25,4,NULL,NULL,'opened',10,'2026-09-11 12:18:08'),(26,4,NULL,NULL,'opened',10,'2026-09-11 12:19:27'),(27,4,NULL,NULL,'opened',10,'2026-09-11 12:19:50'),(28,4,NULL,NULL,'opened',10,'2026-09-11 13:01:48'),(29,4,NULL,NULL,'opened',10,'2026-09-11 13:01:59'),(30,4,NULL,NULL,'opened',10,'2026-09-11 13:03:27'),(31,4,NULL,NULL,'opened',10,'2026-09-11 13:11:54'),(32,4,NULL,NULL,'opened',10,'2026-09-11 13:13:17'),(33,4,NULL,NULL,'opened',10,'2026-09-11 13:14:02'),(34,4,NULL,NULL,'opened',10,'2026-09-11 13:14:29'),(35,4,NULL,NULL,'opened',10,'2026-09-11 13:17:08'),(36,4,NULL,NULL,'opened',10,'2026-09-11 13:19:43'),(37,4,NULL,NULL,'opened',10,'2026-09-11 13:19:45'),(38,4,NULL,NULL,'opened',10,'2026-09-11 14:22:04'),(39,4,NULL,NULL,'opened',10,'2026-09-11 14:22:40'),(40,4,NULL,NULL,'opened',10,'2026-09-11 14:22:49'),(41,4,NULL,NULL,'opened',10,'2026-09-11 14:35:39'),(42,4,NULL,NULL,'opened',10,'2026-09-11 15:06:01'),(43,4,NULL,NULL,'opened',10,'2026-09-11 15:19:43'),(44,4,NULL,NULL,'opened',10,'2026-09-11 16:59:49'),(45,4,NULL,NULL,'opened',10,'2026-09-11 17:01:28'),(46,4,NULL,NULL,'opened',10,'2026-09-11 17:01:39'),(47,4,NULL,NULL,'opened',10,'2026-09-11 17:02:35'),(48,4,NULL,NULL,'opened',10,'2026-09-11 17:11:23'),(49,4,NULL,NULL,'opened',10,'2026-09-11 17:12:17'),(50,4,NULL,NULL,'opened',10,'2026-09-11 17:12:18'),(51,4,NULL,NULL,'opened',10,'2026-09-11 17:12:18'),(52,4,NULL,NULL,'opened',10,'2026-09-11 17:12:18'),(53,4,NULL,NULL,'opened',10,'2026-09-11 17:12:19'),(54,4,NULL,NULL,'opened',10,'2026-09-11 17:12:19'),(55,4,NULL,NULL,'opened',10,'2026-09-11 17:18:18'),(56,4,NULL,NULL,'opened',10,'2026-09-11 17:20:30'),(57,4,NULL,NULL,'opened',10,'2026-09-11 17:28:19'),(58,4,NULL,NULL,'opened',10,'2026-09-11 17:28:33'),(59,4,NULL,NULL,'opened',10,'2026-09-11 17:32:27'),(60,4,NULL,NULL,'opened',10,'2026-09-11 17:34:23'),(61,4,NULL,NULL,'opened',10,'2026-09-11 17:35:17'),(62,4,NULL,NULL,'opened',10,'2026-09-11 17:45:26'),(63,4,NULL,NULL,'opened',10,'2026-09-11 17:45:34'),(64,4,NULL,NULL,'opened',10,'2026-09-11 17:48:14'),(65,4,NULL,NULL,'opened',10,'2026-09-11 17:48:34'),(66,4,NULL,NULL,'opened',10,'2026-09-11 17:50:07'),(67,4,NULL,NULL,'opened',10,'2026-09-11 18:05:36'),(68,4,NULL,NULL,'opened',10,'2026-09-11 18:13:03'),(69,4,NULL,NULL,'opened',10,'2026-09-11 18:13:03'),(70,4,NULL,NULL,'opened',10,'2026-09-11 18:13:55'),(71,4,NULL,NULL,'opened',10,'2026-09-11 18:17:18'),(72,4,NULL,NULL,'opened',10,'2026-09-11 18:17:40'),(73,4,NULL,NULL,'opened',10,'2026-09-11 21:49:14');
/*!40000 ALTER TABLE `lms_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lms_submissions`
--

DROP TABLE IF EXISTS `lms_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lms_submissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `activity_id` int NOT NULL,
  `student_id` int NOT NULL,
  `submitted_by` int NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `answers` json DEFAULT NULL,
  `auto_score` int DEFAULT NULL,
  `flashcard_results` json DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_remedial_attempt` tinyint(1) NOT NULL DEFAULT '0',
  `needs_remediation` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_submission` (`activity_id`,`student_id`),
  KEY `submitted_by` (`submitted_by`),
  KEY `idx_activity_id` (`activity_id`),
  KEY `idx_student_id` (`student_id`),
  CONSTRAINT `lms_submissions_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `lms_activities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lms_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lms_submissions_ibfk_3` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lms_submissions`
--

LOCK TABLES `lms_submissions` WRITE;
/*!40000 ALTER TABLE `lms_submissions` DISABLE KEYS */;
INSERT INTO `lms_submissions` VALUES (13,2,4,10,NULL,'[0, 0, 0]',15,NULL,'2026-09-19 19:24:23',0,0),(14,3,4,10,NULL,'[0, 0, 0]',15,NULL,'2026-09-19 19:24:23',0,0),(15,4,5,12,NULL,'[0, 0, 0]',15,NULL,'2026-09-19 19:24:27',0,0),(16,5,5,12,NULL,'[0, 0, 0]',15,NULL,'2026-09-19 19:24:27',0,0),(17,6,6,14,NULL,'[0, 1, 0]',10,NULL,'2026-09-19 19:24:36',0,1),(18,7,6,14,NULL,'[0, 1, 0]',10,NULL,'2026-09-19 19:24:36',0,1),(19,8,7,16,NULL,'[0, 0, 0]',15,NULL,'2026-09-19 19:24:44',0,0),(20,9,7,16,NULL,'[0, 1, 0]',10,NULL,'2026-09-19 19:24:44',0,1),(21,10,8,18,NULL,'[0, 0, 0]',15,NULL,'2026-09-19 19:24:52',0,0),(22,11,8,18,NULL,'[0, 0, 0]',15,NULL,'2026-09-19 19:24:52',0,0);
/*!40000 ALTER TABLE `lms_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_log`
--

DROP TABLE IF EXISTS `login_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `result` enum('success','failure') COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_attempted_at` (`attempted_at`),
  CONSTRAINT `login_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_log`
--

LOCK TABLES `login_log` WRITE;
/*!40000 ALTER TABLE `login_log` DISABLE KEYS */;
INSERT INTO `login_log` VALUES (1,1,'allysacanonizado43@gmail.com','::1','success','2026-08-12 18:03:36'),(2,1,'allysacanonizado43@gmail.com','::1','success','2026-08-12 18:27:37'),(3,1,'allysacanonizado43@gmail.com','::1','success','2026-08-12 18:57:52'),(4,1,'allysacanonizado43@gmail.com','::1','success','2026-08-19 23:36:27'),(5,5,'allysacanonizado03@gmail.com','::1','success','2026-08-19 23:37:16'),(6,5,'allysacanonizado03@gmail.com','::1','success','2026-08-19 23:38:08'),(7,NULL,'demo.sped@spedlms.local','::1','failure','2026-08-19 23:39:44'),(8,NULL,'sped@spedlms.local','::1','failure','2026-08-19 23:39:53'),(9,1,'allysacanonizado43@gmail.com','::1','success','2026-08-19 23:40:41'),(10,5,'allysacanonizado03@gmail.com','::1','success','2026-08-19 23:42:04'),(11,6,'asteleackerman@gmail.com','::1','success','2026-08-20 00:02:55'),(12,1,'allysacanonizado43@gmail.com','::1','success','2026-08-20 00:03:05'),(13,1,'allysacanonizado43@gmail.com','::1','success','2026-08-20 00:09:27'),(14,6,'asteleackerman@gmail.com','::1','success','2026-08-20 00:10:49'),(15,1,'allysacanonizado43@gmail.com','::1','success','2026-08-20 00:11:29'),(16,NULL,'demo.sped@spedlms.local','::1','failure','2026-08-20 18:35:13'),(17,NULL,'sped@spedlms.local','::1','failure','2026-08-20 18:35:23'),(18,1,'allysacanonizado43@gmail.com','::1','success','2026-08-20 18:36:07'),(19,6,'asteleackerman@gmail.com','::1','success','2026-08-20 18:36:29'),(20,1,'allysacanonizado43@gmail.com','::1','success','2026-08-20 18:41:47'),(21,6,'asteleackerman@gmail.com','::1','success','2026-08-20 18:50:59'),(22,6,'asteleackerman@gmail.com','::1','success','2026-08-20 19:47:05'),(23,5,'allysacanonizado03@gmail.com','::1','success','2026-08-31 12:30:31'),(24,1,'allysacanonizado43@gmail.com','::1','success','2026-09-08 14:26:25'),(25,8,'xg40jo8fzv@ruutukf.com','::1','failure','2026-09-08 14:27:55'),(26,8,'xg40jo8fzv@ruutukf.com','::1','failure','2026-09-08 14:29:30'),(27,8,'xg40jo8fzv@ruutukf.com','::1','success','2026-09-08 14:29:39'),(28,6,'asteleackerman@gmail.com','::1','success','2026-09-08 17:16:25'),(29,6,'asteleackerman@gmail.com','::1','failure','2026-09-08 18:53:53'),(30,6,'asteleackerman@gmail.com','::1','failure','2026-09-08 18:54:53'),(31,6,'asteleackerman@gmail.com','::1','failure','2026-09-08 18:55:38'),(32,6,'asteleackerman@gmail.com','::1','failure','2026-09-08 18:56:46'),(33,6,'asteleackerman@gmail.com','::1','success','2026-09-09 17:14:21'),(34,6,'asteleackerman@gmail.com','::1','success','2026-09-10 13:25:22'),(35,10,'learner_20260003@spedlms.local','::1','success','2026-09-10 20:21:55'),(36,6,'asteleackerman@gmail.com','::1','success','2026-09-10 20:59:33'),(37,10,'learner_20260003@spedlms.local','::1','success','2026-09-10 21:28:16'),(38,10,'learner_20260003@spedlms.local','::1','success','2026-09-10 21:28:56'),(39,NULL,'learner_202605090001@spedlms.local','::1','failure','2026-09-11 10:24:29'),(40,10,'learner_20260003@spedlms.local','::1','success','2026-09-11 10:25:02'),(41,NULL,'learner_202605090003@spedlms.local','::1','failure','2026-09-11 12:09:46'),(42,10,'learner_20260003@spedlms.local','::1','success','2026-09-11 12:12:51'),(43,10,'learner_20260003@spedlms.local','::1','success','2026-09-11 13:01:32'),(44,6,'asteleackerman@gmail.com','::1','success','2026-09-11 13:23:33'),(45,10,'learner_20260003@spedlms.local','::1','failure','2026-09-11 14:21:43'),(46,10,'learner_20260003@spedlms.local','::1','success','2026-09-11 14:21:49'),(47,10,'learner_20260003@spedlms.local','::1','success','2026-09-11 15:05:58'),(48,10,'learner_20260003@spedlms.local','::1','failure','2026-09-11 16:59:39'),(49,10,'learner_20260003@spedlms.local','::1','success','2026-09-11 16:59:47'),(50,NULL,'onetest@example.com','::1','failure','2026-09-11 17:57:45'),(51,5,'allysacanonizado03@gmail.com','::1','success','2026-09-11 18:20:03'),(52,1,'allysacanonizado43@gmail.com','::1','success','2026-09-11 18:20:16'),(53,1,'allysacanonizado43@gmail.com','::1','success','2026-09-11 18:20:29'),(54,8,'xg40jo8fzv@ruutukf.com','::1','failure','2026-09-11 18:26:48'),(55,8,'xg40jo8fzv@ruutukf.com','::1','success','2026-09-11 18:27:00'),(56,10,'learner_20260003@spedlms.local','::1','success','2026-09-11 21:49:13'),(57,6,'asteleackerman@gmail.com','127.0.0.1','success','2026-09-13 06:33:04'),(58,8,'xg40jo8fzv@ruutukf.com','127.0.0.1','success','2026-09-13 06:33:05'),(59,6,'asteleackerman@gmail.com','127.0.0.1','success','2026-09-13 06:33:28'),(60,6,'asteleackerman@gmail.com','127.0.0.1','success','2026-09-13 06:33:42'),(61,6,'asteleackerman@gmail.com','127.0.0.1','success','2026-09-13 06:33:45'),(62,8,'xg40jo8fzv@ruutukf.com','127.0.0.1','success','2026-09-13 06:33:46'),(63,6,'asteleackerman@gmail.com','127.0.0.1','success','2026-09-13 06:33:58'),(64,6,'asteleackerman@gmail.com','127.0.0.1','success','2026-09-13 06:34:19'),(65,8,'xg40jo8fzv@ruutukf.com','127.0.0.1','success','2026-09-13 06:34:21'),(66,6,'asteleackerman@gmail.com','127.0.0.1','success','2026-09-13 06:34:30'),(67,8,'xg40jo8fzv@ruutukf.com','127.0.0.1','success','2026-09-13 06:34:32'),(68,6,'asteleackerman@gmail.com','127.0.0.1','success','2026-09-13 06:34:32'),(69,11,'maria.santos@gmail.com','127.0.0.1','success','2026-09-13 06:48:53'),(70,12,'learner_20260004@spedlms.local','127.0.0.1','success','2026-09-13 06:49:03'),(71,6,'asteleackerman@gmail.com','::1','success','2026-09-13 16:32:27'),(72,1,'allysacanonizado43@gmail.com','::1','success','2026-09-14 17:10:19'),(73,6,'asteleackerman@gmail.com','::1','success','2026-09-14 17:16:48'),(74,12,'learner_20260004@spedlms.local','::1','success','2026-09-14 17:56:33'),(75,6,'asteleackerman@gmail.com','::1','success','2026-09-19 10:29:40'),(76,6,'asteleackerman@gmail.com','::1','success','2026-09-19 15:48:16'),(77,6,'asteleackerman@gmail.com','::1','success','2026-09-19 16:47:03'),(78,12,'learner_20260004@spedlms.local','::1','success','2026-09-19 16:58:29'),(79,NULL,'teacher','::1','failure','2026-09-19 19:02:38'),(80,NULL,'admin','::1','failure','2026-09-19 19:03:27'),(81,NULL,'teacher@example.com','::1','failure','2026-09-19 19:04:14'),(82,NULL,'teacher@gmail.com','::1','failure','2026-09-19 19:05:20'),(83,6,'asteleackerman@gmail.com','::1','success','2026-09-20 19:22:30'),(84,6,'asteleackerman@gmail.com','::1','success','2026-09-21 13:40:23'),(85,6,'asteleackerman@gmail.com','::1','success','2026-09-21 15:40:30'),(86,6,'asteleackerman@gmail.com','::1','success','2026-09-21 17:45:36'),(87,6,'asteleackerman@gmail.com','::1','failure','2026-09-21 19:20:37'),(88,6,'asteleackerman@gmail.com','::1','success','2026-09-21 19:21:06'),(89,6,'asteleackerman@gmail.com','::1','success','2026-09-21 19:48:43'),(90,NULL,'admin@gmail.com','::1','failure','2026-09-21 19:58:11'),(91,NULL,'admin@signed.com','::1','failure','2026-09-21 19:59:03'),(92,NULL,'admin@example.com','::1','failure','2026-09-21 20:00:17'),(93,5,'allysacanonizado03@gmail.com','::1','success','2026-10-01 21:30:41'),(94,NULL,'rogedib297@aminavin.com','::1','failure','2026-10-01 22:11:44'),(95,NULL,'rogedib297@aminavin.com','::1','failure','2026-10-01 22:11:55'),(96,NULL,'rogedib297@aminavin.com','::1','failure','2026-10-01 22:12:19'),(97,NULL,'rogedib297@aminavin.com','::1','failure','2026-10-01 22:12:27'),(98,NULL,'rogedib297@aminavin.com','::1','failure','2026-10-01 22:12:56'),(99,NULL,'rogedib297@aminavin.com','::1','failure','2026-10-01 22:13:32');
/*!40000 ALTER TABLE `login_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meeting_notifications`
--

DROP TABLE IF EXISTS `meeting_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meeting_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `meeting_id` int NOT NULL,
  `user_id` int NOT NULL,
  `notified_via` enum('email','system','both') COLLATE utf8mb4_unicode_ci DEFAULT 'both',
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_meeting_user` (`meeting_id`,`user_id`),
  CONSTRAINT `meeting_notifications_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `iep_meetings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `meeting_notifications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meeting_notifications`
--

LOCK TABLES `meeting_notifications` WRITE;
/*!40000 ALTER TABLE `meeting_notifications` DISABLE KEYS */;
INSERT INTO `meeting_notifications` VALUES (1,1,1,'both','2026-08-20 21:04:32'),(2,1,7,'both','2026-08-20 21:04:33'),(3,2,1,'both','2026-09-08 18:14:52'),(4,2,8,'both','2026-09-08 18:14:57');
/*!40000 ALTER TABLE `meeting_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_access_logs`
--

DROP TABLE IF EXISTS `module_access_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `module_access_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `module_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accessed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `duration_minutes` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_accessed_at` (`accessed_at`),
  CONSTRAINT `module_access_logs_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_access_logs`
--

LOCK TABLES `module_access_logs` WRITE;
/*!40000 ALTER TABLE `module_access_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_access_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_read` (`user_id`,`is_read`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,1,'role_approved','Application Approved','Your application for Principal has been approved!','{\"role\": \"principal\", \"request_id\": \"1\"}',0,'2026-08-12 18:27:27'),(2,6,'role_approved','Application Approved','Your application for Sped Teacher has been approved!','{\"role\": \"sped_teacher\", \"request_id\": \"5\"}',0,'2026-08-20 18:49:27'),(3,6,'room_assigned','Classroom & Section Assigned','Your Principal has assigned you to SPED Program - Maligaya in Bldg 5, Room Room 204.','{\"grade_level\": \"SPED Program\", \"room_number\": \"Room 204\", \"section_name\": \"Maligaya\", \"building_name\": \"Bldg 5\"}',0,'2026-08-20 18:50:46'),(4,1,'iep_meeting_scheduled','IEP Meeting Scheduled','IEP meeting for Pedro Santos on August 22, 2026 7:04 AM. Venue: dasyuda','{\"meeting_id\": \"1\", \"meeting_date\": \"2026-08-22 07:04:00\", \"meeting_time\": null, \"student_name\": \"Pedro Santos\"}',0,'2026-08-20 21:04:32'),(5,7,'iep_meeting_scheduled','IEP Meeting Scheduled','IEP meeting for Pedro Santos on August 22, 2026 7:04 AM. Venue: dasyuda','{\"meeting_id\": \"1\", \"meeting_date\": \"2026-08-22 07:04:00\", \"meeting_time\": null, \"student_name\": \"Pedro Santos\"}',0,'2026-08-20 21:04:33'),(6,1,'pdsp_completed','PDSP Completed','PDSP for Pedro Santos has been completed. Signatories: ;hjhk, hkhjk.','{\"pdsp_id\": \"1\", \"meeting_id\": \"1\"}',0,'2026-08-20 21:04:52'),(7,8,'enrollment_submitted','Enrollment Submitted','Your enrollment application has been submitted successfully. A SPED teacher will review it soon.',NULL,0,'2026-09-08 17:14:33'),(8,6,'new_enrollment','New Enrollment Submission','A new enrollment application has been submitted and requires your review.','{\"enrollment_id\": 3}',0,'2026-09-08 17:14:33'),(10,8,'learner_account_created','Learner Account Created','Your child ONE TEST\'s learner account has been created. Student ID: 20260003. Check your email for login credentials.','{\"student_id\": \"20260003\", \"is_existing\": false, \"learner_name\": \"ONE TEST\", \"temp_password\": \"1a2f0412\"}',0,'2026-09-08 17:53:49'),(11,8,'enrollment_approved','Enrollment Approved! ✅','Enrollment approved for ONE TEST. Student ID: 20260003. SignED Learner temporary password: 1a2f0412','{\"student_id\": 4, \"enrollment_id\": \"3\"}',0,'2026-09-08 17:53:49'),(12,1,'iep_meeting_scheduled','IEP Meeting Scheduled','IEP meeting for ONE TEST on September 10, 2026 8:20 AM. Venue: dasyuda','{\"meeting_id\": \"2\", \"meeting_date\": \"2026-09-10 08:20:00\", \"meeting_time\": null, \"student_name\": \"ONE TEST\"}',0,'2026-09-08 18:14:52'),(13,8,'iep_meeting_scheduled','IEP Meeting Scheduled','IEP meeting for ONE TEST on September 10, 2026 8:20 AM. Venue: dasyuda','{\"meeting_id\": \"2\", \"meeting_date\": \"2026-09-10 08:20:00\", \"meeting_time\": null, \"student_name\": \"ONE TEST\"}',0,'2026-09-08 18:14:57'),(14,1,'pdsp_completed','PDSP Completed','PDSP for ONE TEST has been completed. Signatories: ada, ads, asdad.','{\"pdsp_id\": \"2\", \"meeting_id\": \"2\"}',0,'2026-09-08 18:15:32'),(15,8,'iep_signed','IEP Signed — ONE TEST','The IEP for ONE TEST has been signed and is now available for viewing.','\"{\\\"iep_id\\\":2}\"',0,'2026-09-08 18:29:38'),(16,1,'iep_signed','IEP Signed — ONE TEST','The IEP for ONE TEST has been signed and is now available for viewing.','\"{\\\"iep_id\\\":2}\"',0,'2026-09-08 18:29:38'),(17,6,'process6_unlocked','IEP Signed — Process 6 Unlocked','The IEP for ONE TEST has been signed. You can now implement the IEP (Process 6).','\"{\\\"iep_id\\\":2,\\\"student_id\\\":4}\"',0,'2026-09-08 18:29:38'),(18,8,'lesson_published','New Lesson Plan Published','Your teacher published: asdasd','{\"lesson_plan_id\": 1}',0,'2026-09-08 19:00:58'),(19,11,'lesson_published','New Lesson Plan Published','Your teacher published: lesson 2','{\"lesson_plan_id\": 5}',0,'2026-09-19 17:09:33'),(20,11,'lesson_published','New Lesson Plan Published','Your teacher published: lesson 1','{\"lesson_plan_id\": 4}',0,'2026-09-19 17:09:36');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `observation_ratings`
--

DROP TABLE IF EXISTS `observation_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `observation_ratings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `observation_id` int NOT NULL,
  `indicator_id` int NOT NULL,
  `rating` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_observation_indicator` (`observation_id`,`indicator_id`),
  KEY `indicator_id` (`indicator_id`),
  CONSTRAINT `observation_ratings_ibfk_1` FOREIGN KEY (`observation_id`) REFERENCES `classroom_observations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `observation_ratings_ibfk_2` FOREIGN KEY (`indicator_id`) REFERENCES `cot_indicator_sets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `observation_ratings`
--

LOCK TABLES `observation_ratings` WRITE;
/*!40000 ALTER TABLE `observation_ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `observation_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pdsp_domains`
--

DROP TABLE IF EXISTS `pdsp_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdsp_domains` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pdsp_id` int NOT NULL,
  `domain_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_domain` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skills_description` text COLLATE utf8mb4_unicode_ci,
  `mastered` tinyint(1) DEFAULT '0',
  `educational_recommendation` text COLLATE utf8mb4_unicode_ci,
  `q1_level` enum('Beginning','Developing','Approaching Proficiency','Proficient','Advanced') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `q2_level` enum('Beginning','Developing','Approaching Proficiency','Proficient','Advanced') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pdsp_domain` (`pdsp_id`,`domain_name`),
  CONSTRAINT `pdsp_domains_ibfk_1` FOREIGN KEY (`pdsp_id`) REFERENCES `pdsp_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pdsp_domains`
--

LOCK TABLES `pdsp_domains` WRITE;
/*!40000 ALTER TABLE `pdsp_domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `pdsp_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pdsp_records`
--

DROP TABLE IF EXISTS `pdsp_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdsp_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `meeting_id` int NOT NULL,
  `student_id` int NOT NULL,
  `filled_by` int NOT NULL,
  `status` enum('draft','signed') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `signed_document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signatories` text COLLATE utf8mb4_unicode_ci,
  `uploaded_image_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_meeting` (`meeting_id`),
  KEY `student_id` (`student_id`),
  KEY `filled_by` (`filled_by`),
  CONSTRAINT `pdsp_records_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `iep_meetings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pdsp_records_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pdsp_records_ibfk_3` FOREIGN KEY (`filled_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pdsp_records`
--

LOCK TABLES `pdsp_records` WRITE;
/*!40000 ALTER TABLE `pdsp_records` DISABLE KEYS */;
INSERT INTO `pdsp_records` VALUES (2,2,4,6,'signed','uploads/pdsp_signed/pdsp_signed_2_1788891328.pdf','[{\"role\":\"sped_teacher\",\"name\":\"ada\"},{\"role\":\"school_head\",\"name\":\"ads\"},{\"role\":\"parent_guardian\",\"name\":\"asdad\"}]',NULL,NULL,'2026-09-08 18:15:04','2026-09-08 18:15:29'),(3,3,5,6,'signed',NULL,'[{\"role\":\"sped_teacher\",\"name\":\"Zere d\'Apchier\"},{\"role\":\"school_head\",\"name\":\"Daisy Lyn A. Buenafe\"},{\"role\":\"parent_guardian\",\"name\":\"Maria Santos\"}]',NULL,NULL,'2026-09-13 06:46:42','2026-09-13 06:46:42'),(4,4,6,6,'signed',NULL,'[{\"role\":\"sped_teacher\",\"name\":\"Zere d\'Apchier\"},{\"role\":\"school_head\",\"name\":\"Daisy Lyn A. Buenafe\"},{\"role\":\"parent_guardian\",\"name\":\"Roberto Dela Cruz\"}]',NULL,NULL,'2026-09-13 06:47:14','2026-09-13 06:47:14'),(5,5,7,6,'signed',NULL,'[{\"role\":\"sped_teacher\",\"name\":\"Zere d\'Apchier\"},{\"role\":\"school_head\",\"name\":\"Daisy Lyn A. Buenafe\"},{\"role\":\"parent_guardian\",\"name\":\"Grace Alcantara\"}]',NULL,NULL,'2026-09-13 06:47:21','2026-09-13 06:47:21'),(6,6,8,6,'signed',NULL,'[{\"role\":\"sped_teacher\",\"name\":\"Zere d\'Apchier\"},{\"role\":\"school_head\",\"name\":\"Daisy Lyn A. Buenafe\"},{\"role\":\"parent_guardian\",\"name\":\"Danilo Reyes\"}]',NULL,NULL,'2026-09-13 06:47:29','2026-09-13 06:47:29');
/*!40000 ALTER TABLE `pdsp_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pdsp_signatories`
--

DROP TABLE IF EXISTS `pdsp_signatories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdsp_signatories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pdsp_id` int NOT NULL,
  `signatory_role` enum('sped_teacher','gen_ed_teacher','school_head','ilrc_supervisor','parent_guardian','medical_allied_1','medical_allied_2','medical_allied_3') COLLATE utf8mb4_unicode_ci NOT NULL,
  `signatory_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pdsp_id` (`pdsp_id`),
  CONSTRAINT `pdsp_signatories_ibfk_1` FOREIGN KEY (`pdsp_id`) REFERENCES `pdsp_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pdsp_signatories`
--

LOCK TABLES `pdsp_signatories` WRITE;
/*!40000 ALTER TABLE `pdsp_signatories` DISABLE KEYS */;
/*!40000 ALTER TABLE `pdsp_signatories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pdsp_signatures`
--

DROP TABLE IF EXISTS `pdsp_signatures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdsp_signatures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pdsp_id` int NOT NULL,
  `signatory_role` enum('sped_teacher','gen_ed_teacher','school_head','ilrc_supervisor','parent_guardian','medical_allied_1','medical_allied_2','medical_allied_3') COLLATE utf8mb4_unicode_ci NOT NULL,
  `signatory_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `signature_image_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `signed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_pdsp_signatory` (`pdsp_id`,`signatory_role`),
  CONSTRAINT `pdsp_signatures_ibfk_1` FOREIGN KEY (`pdsp_id`) REFERENCES `pdsp_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pdsp_signatures`
--

LOCK TABLES `pdsp_signatures` WRITE;
/*!40000 ALTER TABLE `pdsp_signatures` DISABLE KEYS */;
/*!40000 ALTER TABLE `pdsp_signatures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progress_reports`
--

DROP TABLE IF EXISTS `progress_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `progress_reports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `iep_record_id` int NOT NULL,
  `created_by` int NOT NULL,
  `school_year` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quarter` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attendance_summary` text COLLATE utf8mb4_unicode_ci,
  `progress_summary` text COLLATE utf8mb4_unicode_ci,
  `teacher_remarks` text COLLATE utf8mb4_unicode_ci,
  `ratings` json DEFAULT NULL,
  `status` enum('draft','finalized') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `transfer_details` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_progress_reports_iep` (`iep_record_id`),
  KEY `idx_progress_reports_student` (`student_id`),
  CONSTRAINT `progress_reports_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `progress_reports_ibfk_2` FOREIGN KEY (`iep_record_id`) REFERENCES `iep_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `progress_reports_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progress_reports`
--

LOCK TABLES `progress_reports` WRITE;
/*!40000 ALTER TABLE `progress_reports` DISABLE KEYS */;
INSERT INTO `progress_reports` VALUES (6,4,2,6,'2026-2027','1st Quarter','{\"school_days\":{\"Jun\":20,\"Jul\":22,\"Aug\":21,\"Sep\":21,\"Oct\":22,\"Nov\":20,\"Dec\":15,\"Jan\":21,\"Feb\":20,\"Mar\":22,\"Apr\":18}}','Demonstrates mastery of basic Filipino Sign Language greetings and daily living self-care routines. Highly active in interactive slide exercises.','Encourage continuous reinforcement of conversational sign language at home.','[]','draft','/uploads/progress_reports/signed_test.pdf','2026-09-20 03:19:50','{\"admitted_to\":\"Grade 1 SPED - Maligaya\",\"eligible_for_admission_to\":\"Grade 2 SPED\",\"cancellation_admitted_in\":\"\",\"cancellation_date\":\"\"}','2026-09-19 19:19:50','2026-09-19 19:24:23'),(7,5,3,6,'2026-2027','1st Quarter','{\"school_days\":{\"Jun\":20,\"Jul\":22,\"Aug\":21,\"Sep\":21,\"Oct\":22,\"Nov\":20,\"Dec\":15,\"Jan\":21,\"Feb\":20,\"Mar\":22,\"Apr\":18}}','Consistently demonstrates strong psychomotor coordination and bilingual comprehension. Excels in visual flashcards.','Ready for higher-level community and functional numeracy sign vocabulary.',NULL,'draft',NULL,NULL,'{\"admitted_to\":\"Grade 1 SPED - Maligaya\",\"eligible_for_admission_to\":\"Grade 2 SPED\",\"cancellation_admitted_in\":\"\",\"cancellation_date\":\"\"}','2026-09-19 19:24:28','2026-09-19 19:24:28'),(8,6,4,6,'2026-2027','1st Quarter','{\"school_days\":{\"Jun\":20,\"Jul\":22,\"Aug\":21,\"Sep\":21,\"Oct\":22,\"Nov\":20,\"Dec\":15,\"Jan\":21,\"Feb\":20,\"Mar\":22,\"Apr\":18}}','Shows keen interest in interactive stories and videos. Needs repetitive practice distinguishing between similar finger-spelling gestures.','Assigned remedial practice modules for basic family signs. Regular 10-minute daily review recommended.',NULL,'draft',NULL,NULL,'{\"admitted_to\":\"Grade 1 SPED - Maligaya\",\"eligible_for_admission_to\":\"Grade 2 SPED\",\"cancellation_admitted_in\":\"\",\"cancellation_date\":\"\"}','2026-09-19 19:24:36','2026-09-19 19:24:36'),(9,7,5,6,'2026-2027','1st Quarter','{\"school_days\":{\"Jun\":20,\"Jul\":22,\"Aug\":21,\"Sep\":21,\"Oct\":22,\"Nov\":20,\"Dec\":15,\"Jan\":21,\"Feb\":20,\"Mar\":22,\"Apr\":18}}','Good progress in socio-emotional interaction and peer greetings. Demonstrates growing confidence in classroom sign communication.','Continue encouraging spontaneous sign expression during group activities.',NULL,'draft',NULL,NULL,'{\"admitted_to\":\"Grade 1 SPED - Maligaya\",\"eligible_for_admission_to\":\"Grade 2 SPED\",\"cancellation_admitted_in\":\"\",\"cancellation_date\":\"\"}','2026-09-19 19:24:44','2026-09-19 19:24:44'),(10,8,6,6,'2026-2027','1st Quarter','{\"school_days\":{\"Jun\":20,\"Jul\":22,\"Aug\":21,\"Sep\":21,\"Oct\":22,\"Nov\":20,\"Dec\":15,\"Jan\":21,\"Feb\":20,\"Mar\":22,\"Apr\":18}}','Outstanding visual-spatial skills. Independent in completing slide exercises and actively helps peers imitate hand shapes.','Commendable performance across all 8 developmental domains.',NULL,'draft',NULL,NULL,'{\"admitted_to\":\"Grade 1 SPED - Maligaya\",\"eligible_for_admission_to\":\"Grade 2 SPED\",\"cancellation_admitted_in\":\"\",\"cancellation_date\":\"\"}','2026-09-19 19:24:52','2026-09-19 19:24:52');
/*!40000 ALTER TABLE `progress_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rate_limit_log`
--

DROP TABLE IF EXISTS `rate_limit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rate_limit_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attempt_type` enum('login','registration','password_reset') COLLATE utf8mb4_unicode_ci NOT NULL,
  `success` tinyint(1) DEFAULT '0',
  `attempted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email_time` (`email`,`attempted_at`),
  KEY `idx_ip_time` (`ip_address`,`attempted_at`),
  KEY `idx_attempted_at` (`attempted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_limit_log`
--

LOCK TABLES `rate_limit_log` WRITE;
/*!40000 ALTER TABLE `rate_limit_log` DISABLE KEYS */;
INSERT INTO `rate_limit_log` VALUES (1,'demo.sped@spedlms.local','::1','login',0,'2026-08-19 23:39:44'),(2,'sped@spedlms.local','::1','login',0,'2026-08-19 23:39:53'),(3,'demo.sped@spedlms.local','::1','login',0,'2026-08-20 18:35:13'),(4,'sped@spedlms.local','::1','login',0,'2026-08-20 18:35:23'),(5,'xg40jo8fzv@ruutukf.com','::1','registration',1,'2026-09-08 13:32:47'),(8,'xg40jo8fzv@ruutukf.com','::1','login',1,'2026-09-08 14:29:39'),(13,'learner_20260003@spedlms.local','::1','login',1,'2026-09-10 20:21:55'),(14,'learner_20260003@spedlms.local','::1','login',1,'2026-09-10 21:28:16'),(15,'learner_20260003@spedlms.local','::1','login',1,'2026-09-10 21:28:56'),(16,'learner_202605090001@spedlms.local','::1','login',0,'2026-09-11 10:24:29'),(17,'learner_20260003@spedlms.local','::1','login',1,'2026-09-11 10:25:02'),(18,'learner_202605090003@spedlms.local','::1','login',0,'2026-09-11 12:09:46'),(19,'learner_20260003@spedlms.local','::1','login',1,'2026-09-11 12:12:51'),(20,'learner_20260003@spedlms.local','::1','login',1,'2026-09-11 13:01:32'),(22,'learner_20260003@spedlms.local','::1','login',1,'2026-09-11 14:21:49'),(23,'learner_20260003@spedlms.local','::1','login',1,'2026-09-11 15:05:58'),(25,'learner_20260003@spedlms.local','::1','login',1,'2026-09-11 16:59:47'),(26,'onetest@example.com','::1','login',0,'2026-09-11 17:57:45'),(28,'xg40jo8fzv@ruutukf.com','::1','login',1,'2026-09-11 18:27:00'),(29,'learner_20260003@spedlms.local','::1','login',1,'2026-09-11 21:49:13'),(30,'asteleackerman@gmail.com','127.0.0.1','login',1,'2026-09-13 06:33:04'),(31,'xg40jo8fzv@ruutukf.com','127.0.0.1','login',1,'2026-09-13 06:33:05'),(32,'asteleackerman@gmail.com','127.0.0.1','login',1,'2026-09-13 06:33:28'),(33,'asteleackerman@gmail.com','127.0.0.1','login',1,'2026-09-13 06:33:42'),(34,'asteleackerman@gmail.com','127.0.0.1','login',1,'2026-09-13 06:33:45'),(35,'xg40jo8fzv@ruutukf.com','127.0.0.1','login',1,'2026-09-13 06:33:46'),(36,'asteleackerman@gmail.com','127.0.0.1','login',1,'2026-09-13 06:33:58'),(37,'asteleackerman@gmail.com','127.0.0.1','login',1,'2026-09-13 06:34:19'),(38,'xg40jo8fzv@ruutukf.com','127.0.0.1','login',1,'2026-09-13 06:34:21'),(39,'asteleackerman@gmail.com','127.0.0.1','login',1,'2026-09-13 06:34:30'),(40,'xg40jo8fzv@ruutukf.com','127.0.0.1','login',1,'2026-09-13 06:34:32'),(41,'asteleackerman@gmail.com','127.0.0.1','login',1,'2026-09-13 06:34:32'),(42,'maria.santos@gmail.com','127.0.0.1','login',1,'2026-09-13 06:48:53'),(43,'learner_20260004@spedlms.local','127.0.0.1','login',1,'2026-09-13 06:49:03'),(44,'learner_20260004@spedlms.local','::1','login',1,'2026-09-14 17:56:33'),(45,'learner_20260004@spedlms.local','::1','login',1,'2026-09-19 16:58:29'),(46,'teacher','::1','login',0,'2026-09-19 19:02:38'),(47,'admin','::1','login',0,'2026-09-19 19:03:27'),(48,'teacher@example.com','::1','login',0,'2026-09-19 19:04:14'),(49,'teacher@gmail.com','::1','login',0,'2026-09-19 19:05:20'),(51,'asteleackerman@gmail.com','::1','login',1,'2026-09-21 19:21:06'),(52,'admin@gmail.com','::1','login',0,'2026-09-21 19:58:10'),(53,'admin@signed.com','::1','login',0,'2026-09-21 19:59:03'),(54,'admin@example.com','::1','login',0,'2026-09-21 20:00:17'),(55,'rogedib297@aminavin.com','::1','login',0,'2026-10-01 22:11:44'),(56,'rogedib297@aminavin.com','::1','login',0,'2026-10-01 22:11:55'),(57,'rogedib297@aminavin.com','::1','login',0,'2026-10-01 22:12:19'),(58,'rogedib297@aminavin.com','::1','login',0,'2026-10-01 22:12:27'),(59,'rogedib297@aminavin.com','::1','login',0,'2026-10-01 22:12:56'),(60,'rogedib297@aminavin.com','::1','login',0,'2026-10-01 22:13:32'),(61,'ie11gd7wzo@yzcalo.com','::1','registration',1,'2026-10-01 22:14:30');
/*!40000 ALTER TABLE `rate_limit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_remarks`
--

DROP TABLE IF EXISTS `report_remarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_remarks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `progress_report_id` int NOT NULL,
  `quarter` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remark_type` enum('teacher','parent') COLLATE utf8mb4_unicode_ci NOT NULL,
  `remark_text` text COLLATE utf8mb4_unicode_ci,
  `signature_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature_data` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_report_quarter_type` (`progress_report_id`,`quarter`,`remark_type`),
  CONSTRAINT `report_remarks_ibfk_1` FOREIGN KEY (`progress_report_id`) REFERENCES `progress_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_remarks`
--

LOCK TABLES `report_remarks` WRITE;
/*!40000 ALTER TABLE `report_remarks` DISABLE KEYS */;
INSERT INTO `report_remarks` VALUES (13,6,'1st Quarter','teacher','Encourage continuous reinforcement of conversational sign language at home.','Zere d\'Apchier',NULL,'2026-09-19 19:24:23'),(14,6,'1st Quarter','parent','We are very proud of our child\'s progress in signing and daily morning routines.','Parent of ONE TEST','data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==','2026-09-19 19:24:23'),(15,7,'1st Quarter','teacher','Ready for higher-level community and functional numeracy sign vocabulary.','Zere d\'Apchier',NULL,'2026-09-19 19:24:28'),(16,7,'1st Quarter','parent','Nagpapasalamat kami sa tiyaga ng guro. Mas madali nang makipag-usap sa bahay gamit ang FSL.','Parent of TEST 2','data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==','2026-09-19 19:24:28'),(17,8,'1st Quarter','teacher','Assigned remedial practice modules for basic family signs. Regular 10-minute daily review recommended.','Zere d\'Apchier',NULL,'2026-09-19 19:24:36'),(18,8,'1st Quarter','parent','Tutulungan po namin siyang mag-ensayo tuwing hapon bago maghapunan.','Parent of TEST 3','data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==','2026-09-19 19:24:36'),(19,9,'1st Quarter','teacher','Continue encouraging spontaneous sign expression during group activities.','Zere d\'Apchier',NULL,'2026-09-19 19:24:44'),(20,9,'1st Quarter','parent','Napapansin namin na mas masayahin at palakaibigan na siya ngayon.','Parent of TEST 4','data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==','2026-09-19 19:24:44'),(21,10,'1st Quarter','teacher','Commendable performance across all 8 developmental domains.','Zere d\'Apchier',NULL,'2026-09-19 19:24:52'),(22,10,'1st Quarter','parent','Very satisfied with the DepEd SPED implementation and digital learning materials.','Parent of TEST 5','data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==','2026-09-19 19:24:52');
/*!40000 ALTER TABLE `report_remarks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_documents`
--

DROP TABLE IF EXISTS `role_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_request_id` int NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `role_request_id` (`role_request_id`),
  CONSTRAINT `role_documents_ibfk_1` FOREIGN KEY (`role_request_id`) REFERENCES `role_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_documents`
--

LOCK TABLES `role_documents` WRITE;
/*!40000 ALTER TABLE `role_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_requests`
--

DROP TABLE IF EXISTS `role_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `requested_role` enum('sped_teacher','guidance','principal','master_teacher') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `approver_role` enum('admin','principal') COLLATE utf8mb4_unicode_ci DEFAULT 'principal',
  `submitted_docs` json DEFAULT NULL,
  `reviewed_by` int DEFAULT NULL,
  `review_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reviewed_by` (`reviewed_by`),
  KEY `idx_status` (`status`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_approver_role` (`approver_role`),
  CONSTRAINT `role_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_requests_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_requests`
--

LOCK TABLES `role_requests` WRITE;
/*!40000 ALTER TABLE `role_requests` DISABLE KEYS */;
INSERT INTO `role_requests` VALUES (1,1,'principal','approved','admin','{\"files\": [], \"school_id\": \"1\", \"principal_rank\": \"Principal II\", \"employee_number\": \"1234567\"}',1,'','2026-08-12 18:05:11','2026-08-12 18:27:27'),(5,6,'sped_teacher','approved','principal','{\"files\": [], \"school_id\": \"1\", \"certifications\": [{\"path\": \"uploads/role_verification/fsl_cert_6_0_1787251295_6a874a5f5a9c1.pdf\", \"title\": \"FSL Training\", \"issue_date\": \"2026-08-21\"}], \"principal_rank\": \"\", \"employee_number\": \"1047292\"}',1,'','2026-08-20 18:41:35','2026-08-20 18:49:27');
/*!40000 ALTER TABLE `role_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schools` (
  `id` int NOT NULL AUTO_INCREMENT,
  `school_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `division` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `enrollment_sy` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '2026-2027',
  `enrollment_status` enum('open','upcoming','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `enrollment_start_date` date DEFAULT NULL,
  `enrollment_end_date` date DEFAULT NULL,
  `enrollment_guidelines` text COLLATE utf8mb4_unicode_ci,
  `enrollment_announcement` text COLLATE utf8mb4_unicode_ci,
  `guidelines_published` tinyint(1) DEFAULT '0',
  `logo_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pubmat_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sip_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_page` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_id` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schools`
--

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` VALUES (1,'129688','Piedad Central Elementary School','Division of Davao City','Region XI','Neptune St. Crossing Bayabas','2026-2027','open',NULL,NULL,'',NULL,1,'uploads/schools/school_1_1786557911.jfif','uploads/pubmats/pubmat_1_1786560505.jpg','uploads/role_verification/sip_1_1787185814.pdf',NULL,NULL,NULL,'2026-08-12 18:05:11');
/*!40000 ALTER TABLE `schools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `school_id` int NOT NULL DEFAULT '1',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade_level` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_capacity` int NOT NULL DEFAULT '10',
  `assigned_teacher_id` int DEFAULT NULL,
  `current_count` int NOT NULL DEFAULT '0',
  `adviser_teacher_id` int DEFAULT NULL,
  `school_year` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2026-2027',
  `status` enum('active','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_school_id` (`school_id`),
  KEY `idx_teacher` (`assigned_teacher_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sections`
--

LOCK TABLES `sections` WRITE;
/*!40000 ALTER TABLE `sections` DISABLE KEYS */;
INSERT INTO `sections` VALUES (1,1,'SPED Grade 1 - Rosal','SPED Grade 1 - Rosal','Grade 1',NULL,10,NULL,2,NULL,'2026-2027','active','2026-08-20 20:53:49','2026-09-08 17:53:44'),(2,1,'SPED Grade 1 - Sampaguita','SPED Grade 1 - Sampaguita','Grade 1',NULL,10,NULL,1,NULL,'2026-2027','active','2026-08-20 20:53:49','2026-09-08 17:39:07'),(3,1,'Maligaya','Maligaya','SPED Program',NULL,10,NULL,10,6,'2026-2027','active','2026-09-08 18:23:01','2026-09-21 17:49:46');
/*!40000 ALTER TABLE `sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_documents`
--

DROP TABLE IF EXISTS `student_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `process_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL,
  `uploaded_by` int NOT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_hidden` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_student_process` (`student_id`,`process_name`),
  KEY `idx_document_type` (`document_type`),
  KEY `idx_uploaded_at` (`uploaded_at`),
  CONSTRAINT `student_documents_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_documents`
--

LOCK TABLES `student_documents` WRITE;
/*!40000 ALTER TABLE `student_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_quarterly_ratings`
--

DROP TABLE IF EXISTS `student_quarterly_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_quarterly_ratings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `pdsp_record_id` int NOT NULL,
  `domain` enum('Daily Living Skills','Socio-Emotional','Language Development','Psychomotor','Cognitive','Aesthetic/Creative','Behavioral Development','Orientation and Mobility') COLLATE utf8mb4_unicode_ci NOT NULL,
  `indicator_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `quarter` tinyint NOT NULL COMMENT '1, 2, 3, or 4',
  `rating` enum('P','AP','D','B','NA') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observation` text COLLATE utf8mb4_unicode_ci,
  `source` enum('digital','f2f','manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sqr_student_indicator_quarter` (`student_id`,`pdsp_record_id`,`indicator_text`(150),`quarter`),
  KEY `pdsp_record_id` (`pdsp_record_id`),
  CONSTRAINT `student_quarterly_ratings_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`),
  CONSTRAINT `student_quarterly_ratings_ibfk_2` FOREIGN KEY (`pdsp_record_id`) REFERENCES `pdsp_records` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9329 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_quarterly_ratings`
--

LOCK TABLES `student_quarterly_ratings` WRITE;
/*!40000 ALTER TABLE `student_quarterly_ratings` DISABLE KEYS */;
INSERT INTO `student_quarterly_ratings` VALUES (4573,4,2,'Daily Living Skills','Holds and uses spoon/fork correctly',1,'P',NULL,'manual','2026-09-19 19:24:23'),(4574,4,2,'Daily Living Skills','Holds and uses spoon/fork correctly',2,NULL,NULL,'manual','2026-09-19 19:24:23'),(4575,4,2,'Daily Living Skills','Holds and uses spoon/fork correctly',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4576,4,2,'Daily Living Skills','Holds and uses spoon/fork correctly',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4577,4,2,'Daily Living Skills','Drinks from a glass without spilling',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4578,4,2,'Daily Living Skills','Drinks from a glass without spilling',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4579,4,2,'Daily Living Skills','Drinks from a glass without spilling',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4580,4,2,'Daily Living Skills','Drinks from a glass without spilling',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4581,4,2,'Daily Living Skills','Eats using hands with minimal mess',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4582,4,2,'Daily Living Skills','Eats using hands with minimal mess',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4583,4,2,'Daily Living Skills','Eats using hands with minimal mess',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4584,4,2,'Daily Living Skills','Eats using hands with minimal mess',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4585,4,2,'Daily Living Skills','Opens food containers independently',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4586,4,2,'Daily Living Skills','Opens food containers independently',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4587,4,2,'Daily Living Skills','Opens food containers independently',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4588,4,2,'Daily Living Skills','Opens food containers independently',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4589,4,2,'Daily Living Skills','Indicates need to use the toilet',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4590,4,2,'Daily Living Skills','Indicates need to use the toilet',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4591,4,2,'Daily Living Skills','Indicates need to use the toilet',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4592,4,2,'Daily Living Skills','Indicates need to use the toilet',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4593,4,2,'Daily Living Skills','Goes to the toilet independently',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4594,4,2,'Daily Living Skills','Goes to the toilet independently',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4595,4,2,'Daily Living Skills','Goes to the toilet independently',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4596,4,2,'Daily Living Skills','Goes to the toilet independently',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4597,4,2,'Daily Living Skills','Flushes the toilet after use',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4598,4,2,'Daily Living Skills','Flushes the toilet after use',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4599,4,2,'Daily Living Skills','Flushes the toilet after use',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4600,4,2,'Daily Living Skills','Flushes the toilet after use',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4601,4,2,'Daily Living Skills','Washes hands after toileting',1,'AP',NULL,'manual','2026-09-19 19:24:24'),(4602,4,2,'Daily Living Skills','Washes hands after toileting',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4603,4,2,'Daily Living Skills','Washes hands after toileting',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4604,4,2,'Daily Living Skills','Washes hands after toileting',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4605,4,2,'Daily Living Skills','Puts on and removes clothing independently',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4606,4,2,'Daily Living Skills','Puts on and removes clothing independently',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4607,4,2,'Daily Living Skills','Puts on and removes clothing independently',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4608,4,2,'Daily Living Skills','Puts on and removes clothing independently',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4609,4,2,'Daily Living Skills','Buttons and unbuttons shirt',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4610,4,2,'Daily Living Skills','Buttons and unbuttons shirt',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4611,4,2,'Daily Living Skills','Buttons and unbuttons shirt',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4612,4,2,'Daily Living Skills','Buttons and unbuttons shirt',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4613,4,2,'Daily Living Skills','Zips and unzips clothing',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4614,4,2,'Daily Living Skills','Zips and unzips clothing',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4615,4,2,'Daily Living Skills','Zips and unzips clothing',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4616,4,2,'Daily Living Skills','Zips and unzips clothing',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4617,4,2,'Daily Living Skills','Wears shoes and socks correctly',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4618,4,2,'Daily Living Skills','Wears shoes and socks correctly',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4619,4,2,'Daily Living Skills','Wears shoes and socks correctly',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4620,4,2,'Daily Living Skills','Wears shoes and socks correctly',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4621,4,2,'Daily Living Skills','Brushes teeth with minimal supervision',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4622,4,2,'Daily Living Skills','Brushes teeth with minimal supervision',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4623,4,2,'Daily Living Skills','Brushes teeth with minimal supervision',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4624,4,2,'Daily Living Skills','Brushes teeth with minimal supervision',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4625,4,2,'Daily Living Skills','Washes and dries hands properly',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4626,4,2,'Daily Living Skills','Washes and dries hands properly',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4627,4,2,'Daily Living Skills','Washes and dries hands properly',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4628,4,2,'Daily Living Skills','Washes and dries hands properly',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4629,4,2,'Daily Living Skills','Combs/brushes hair independently',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4630,4,2,'Daily Living Skills','Combs/brushes hair independently',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4631,4,2,'Daily Living Skills','Combs/brushes hair independently',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4632,4,2,'Daily Living Skills','Combs/brushes hair independently',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4633,4,2,'Daily Living Skills','Maintains personal cleanliness',1,'AP',NULL,'manual','2026-09-19 19:24:24'),(4634,4,2,'Daily Living Skills','Maintains personal cleanliness',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4635,4,2,'Daily Living Skills','Maintains personal cleanliness',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4636,4,2,'Daily Living Skills','Maintains personal cleanliness',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4637,4,2,'Socio-Emotional','Shows awareness of own feelings and emotions',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4638,4,2,'Socio-Emotional','Shows awareness of own feelings and emotions',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4639,4,2,'Socio-Emotional','Shows awareness of own feelings and emotions',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4640,4,2,'Socio-Emotional','Shows awareness of own feelings and emotions',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4641,4,2,'Socio-Emotional','Interacts appropriately with peers and adults',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4642,4,2,'Socio-Emotional','Interacts appropriately with peers and adults',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4643,4,2,'Socio-Emotional','Interacts appropriately with peers and adults',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4644,4,2,'Socio-Emotional','Interacts appropriately with peers and adults',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4645,4,2,'Socio-Emotional','Takes turns and shares materials',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4646,4,2,'Socio-Emotional','Takes turns and shares materials',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4647,4,2,'Socio-Emotional','Takes turns and shares materials',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4648,4,2,'Socio-Emotional','Takes turns and shares materials',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4649,4,2,'Socio-Emotional','Expresses needs and wants appropriately',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4650,4,2,'Socio-Emotional','Expresses needs and wants appropriately',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4651,4,2,'Socio-Emotional','Expresses needs and wants appropriately',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4652,4,2,'Socio-Emotional','Expresses needs and wants appropriately',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4653,4,2,'Socio-Emotional','Follows classroom rules and routines',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4654,4,2,'Socio-Emotional','Follows classroom rules and routines',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4655,4,2,'Socio-Emotional','Follows classroom rules and routines',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4656,4,2,'Socio-Emotional','Follows classroom rules and routines',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4657,4,2,'Socio-Emotional','Shows appropriate emotional responses',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4658,4,2,'Socio-Emotional','Shows appropriate emotional responses',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4659,4,2,'Socio-Emotional','Shows appropriate emotional responses',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4660,4,2,'Socio-Emotional','Shows appropriate emotional responses',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4661,4,2,'Socio-Emotional','Develops and maintains peer relationships',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4662,4,2,'Socio-Emotional','Develops and maintains peer relationships',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4663,4,2,'Socio-Emotional','Develops and maintains peer relationships',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4664,4,2,'Socio-Emotional','Develops and maintains peer relationships',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4665,4,2,'Socio-Emotional','Demonstrates self-control in social situations',1,'AP',NULL,'manual','2026-09-19 19:24:24'),(4666,4,2,'Socio-Emotional','Demonstrates self-control in social situations',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4667,4,2,'Socio-Emotional','Demonstrates self-control in social situations',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4668,4,2,'Socio-Emotional','Demonstrates self-control in social situations',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4669,4,2,'Socio-Emotional','Shows empathy toward others',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4670,4,2,'Socio-Emotional','Shows empathy toward others',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4671,4,2,'Socio-Emotional','Shows empathy toward others',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4672,4,2,'Socio-Emotional','Shows empathy toward others',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4673,4,2,'Socio-Emotional','Seeks help from adults when needed',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4674,4,2,'Socio-Emotional','Seeks help from adults when needed',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4675,4,2,'Socio-Emotional','Seeks help from adults when needed',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4676,4,2,'Socio-Emotional','Seeks help from adults when needed',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4677,4,2,'Language Development','Listens attentively during instruction',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4678,4,2,'Language Development','Listens attentively during instruction',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4679,4,2,'Language Development','Listens attentively during instruction',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4680,4,2,'Language Development','Listens attentively during instruction',4,NULL,NULL,'manual','2026-09-19 19:24:24'),(4681,4,2,'Language Development','Follows one-step verbal directions',1,'P',NULL,'manual','2026-09-19 19:24:24'),(4682,4,2,'Language Development','Follows one-step verbal directions',2,NULL,NULL,'manual','2026-09-19 19:24:24'),(4683,4,2,'Language Development','Follows one-step verbal directions',3,NULL,NULL,'manual','2026-09-19 19:24:24'),(4684,4,2,'Language Development','Follows one-step verbal directions',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4685,4,2,'Language Development','Follows two-step verbal directions',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4686,4,2,'Language Development','Follows two-step verbal directions',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4687,4,2,'Language Development','Follows two-step verbal directions',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4688,4,2,'Language Development','Follows two-step verbal directions',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4689,4,2,'Language Development','Identifies objects/pictures when named',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4690,4,2,'Language Development','Identifies objects/pictures when named',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4691,4,2,'Language Development','Identifies objects/pictures when named',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4692,4,2,'Language Development','Identifies objects/pictures when named',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4693,4,2,'Language Development','Communicates basic needs verbally or through AAC',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4694,4,2,'Language Development','Communicates basic needs verbally or through AAC',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4695,4,2,'Language Development','Communicates basic needs verbally or through AAC',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4696,4,2,'Language Development','Communicates basic needs verbally or through AAC',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4697,4,2,'Language Development','Uses simple sentences to express ideas',1,'AP',NULL,'manual','2026-09-19 19:24:25'),(4698,4,2,'Language Development','Uses simple sentences to express ideas',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4699,4,2,'Language Development','Uses simple sentences to express ideas',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4700,4,2,'Language Development','Uses simple sentences to express ideas',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4701,4,2,'Language Development','Participates in simple conversations',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4702,4,2,'Language Development','Participates in simple conversations',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4703,4,2,'Language Development','Participates in simple conversations',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4704,4,2,'Language Development','Participates in simple conversations',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4705,4,2,'Language Development','Answers simple questions appropriately',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4706,4,2,'Language Development','Answers simple questions appropriately',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4707,4,2,'Language Development','Answers simple questions appropriately',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4708,4,2,'Language Development','Answers simple questions appropriately',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4709,4,2,'Language Development','Recognizes own name in print',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4710,4,2,'Language Development','Recognizes own name in print',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4711,4,2,'Language Development','Recognizes own name in print',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4712,4,2,'Language Development','Recognizes own name in print',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4713,4,2,'Language Development','Identifies letters of the alphabet',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4714,4,2,'Language Development','Identifies letters of the alphabet',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4715,4,2,'Language Development','Identifies letters of the alphabet',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4716,4,2,'Language Development','Identifies letters of the alphabet',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4717,4,2,'Language Development','Matches letters to sounds (phonics)',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4718,4,2,'Language Development','Matches letters to sounds (phonics)',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4719,4,2,'Language Development','Matches letters to sounds (phonics)',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4720,4,2,'Language Development','Matches letters to sounds (phonics)',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4721,4,2,'Language Development','Reads simple words and short phrases',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4722,4,2,'Language Development','Reads simple words and short phrases',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4723,4,2,'Language Development','Reads simple words and short phrases',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4724,4,2,'Language Development','Reads simple words and short phrases',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4725,4,2,'Language Development','Holds pencil/crayon with correct grip',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4726,4,2,'Language Development','Holds pencil/crayon with correct grip',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4727,4,2,'Language Development','Holds pencil/crayon with correct grip',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4728,4,2,'Language Development','Holds pencil/crayon with correct grip',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4729,4,2,'Language Development','Copies simple shapes and lines',1,'AP',NULL,'manual','2026-09-19 19:24:25'),(4730,4,2,'Language Development','Copies simple shapes and lines',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4731,4,2,'Language Development','Copies simple shapes and lines',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4732,4,2,'Language Development','Copies simple shapes and lines',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4733,4,2,'Language Development','Writes own name legibly',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4734,4,2,'Language Development','Writes own name legibly',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4735,4,2,'Language Development','Writes own name legibly',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4736,4,2,'Language Development','Writes own name legibly',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4737,4,2,'Language Development','Writes simple words from dictation',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4738,4,2,'Language Development','Writes simple words from dictation',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4739,4,2,'Language Development','Writes simple words from dictation',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4740,4,2,'Language Development','Writes simple words from dictation',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4741,4,2,'Psychomotor','Walks with balance and coordination',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4742,4,2,'Psychomotor','Walks with balance and coordination',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4743,4,2,'Psychomotor','Walks with balance and coordination',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4744,4,2,'Psychomotor','Walks with balance and coordination',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4745,4,2,'Psychomotor','Runs with control and coordination',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4746,4,2,'Psychomotor','Runs with control and coordination',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4747,4,2,'Psychomotor','Runs with control and coordination',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4748,4,2,'Psychomotor','Runs with control and coordination',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4749,4,2,'Psychomotor','Climbs stairs using alternating feet',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4750,4,2,'Psychomotor','Climbs stairs using alternating feet',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4751,4,2,'Psychomotor','Climbs stairs using alternating feet',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4752,4,2,'Psychomotor','Climbs stairs using alternating feet',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4753,4,2,'Psychomotor','Catches and throws a ball',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4754,4,2,'Psychomotor','Catches and throws a ball',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4755,4,2,'Psychomotor','Catches and throws a ball',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4756,4,2,'Psychomotor','Catches and throws a ball',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4757,4,2,'Psychomotor','Jumps on two feet and hops on one foot',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4758,4,2,'Psychomotor','Jumps on two feet and hops on one foot',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4759,4,2,'Psychomotor','Jumps on two feet and hops on one foot',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4760,4,2,'Psychomotor','Jumps on two feet and hops on one foot',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4761,4,2,'Psychomotor','Participates in physical activities with peers',1,'AP',NULL,'manual','2026-09-19 19:24:25'),(4762,4,2,'Psychomotor','Participates in physical activities with peers',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4763,4,2,'Psychomotor','Participates in physical activities with peers',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4764,4,2,'Psychomotor','Participates in physical activities with peers',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4765,4,2,'Psychomotor','Cuts along a straight and curved line with scissors',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4766,4,2,'Psychomotor','Cuts along a straight and curved line with scissors',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4767,4,2,'Psychomotor','Cuts along a straight and curved line with scissors',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4768,4,2,'Psychomotor','Cuts along a straight and curved line with scissors',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4769,4,2,'Psychomotor','Strings beads and manipulates small objects',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4770,4,2,'Psychomotor','Strings beads and manipulates small objects',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4771,4,2,'Psychomotor','Strings beads and manipulates small objects',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4772,4,2,'Psychomotor','Strings beads and manipulates small objects',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4773,4,2,'Psychomotor','Completes puzzles with multiple pieces',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4774,4,2,'Psychomotor','Completes puzzles with multiple pieces',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4775,4,2,'Psychomotor','Completes puzzles with multiple pieces',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4776,4,2,'Psychomotor','Completes puzzles with multiple pieces',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4777,4,2,'Psychomotor','Colors within boundaries',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4778,4,2,'Psychomotor','Colors within boundaries',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4779,4,2,'Psychomotor','Colors within boundaries',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4780,4,2,'Psychomotor','Colors within boundaries',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4781,4,2,'Psychomotor','Copies simple geometric shapes',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4782,4,2,'Psychomotor','Copies simple geometric shapes',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4783,4,2,'Psychomotor','Copies simple geometric shapes',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4784,4,2,'Psychomotor','Copies simple geometric shapes',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4785,4,2,'Psychomotor','Demonstrates eye-hand coordination in tasks',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4786,4,2,'Psychomotor','Demonstrates eye-hand coordination in tasks',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4787,4,2,'Psychomotor','Demonstrates eye-hand coordination in tasks',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4788,4,2,'Psychomotor','Demonstrates eye-hand coordination in tasks',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4789,4,2,'Psychomotor','Identifies left and right body sides',1,'P',NULL,'manual','2026-09-19 19:24:25'),(4790,4,2,'Psychomotor','Identifies left and right body sides',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4791,4,2,'Psychomotor','Identifies left and right body sides',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4792,4,2,'Psychomotor','Identifies left and right body sides',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4793,4,2,'Psychomotor','Tracks moving objects with eyes',1,'AP',NULL,'manual','2026-09-19 19:24:25'),(4794,4,2,'Psychomotor','Tracks moving objects with eyes',2,NULL,NULL,'manual','2026-09-19 19:24:25'),(4795,4,2,'Psychomotor','Tracks moving objects with eyes',3,NULL,NULL,'manual','2026-09-19 19:24:25'),(4796,4,2,'Psychomotor','Tracks moving objects with eyes',4,NULL,NULL,'manual','2026-09-19 19:24:25'),(4797,4,2,'Cognitive','Identifies and matches colors',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4798,4,2,'Cognitive','Identifies and matches colors',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4799,4,2,'Cognitive','Identifies and matches colors',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4800,4,2,'Cognitive','Identifies and matches colors',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4801,4,2,'Cognitive','Identifies and matches basic shapes',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4802,4,2,'Cognitive','Identifies and matches basic shapes',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4803,4,2,'Cognitive','Identifies and matches basic shapes',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4804,4,2,'Cognitive','Identifies and matches basic shapes',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4805,4,2,'Cognitive','Sorts and classifies objects by attributes',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4806,4,2,'Cognitive','Sorts and classifies objects by attributes',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4807,4,2,'Cognitive','Sorts and classifies objects by attributes',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4808,4,2,'Cognitive','Sorts and classifies objects by attributes',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4809,4,2,'Cognitive','Counts objects up to 10',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4810,4,2,'Cognitive','Counts objects up to 10',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4811,4,2,'Cognitive','Counts objects up to 10',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4812,4,2,'Cognitive','Counts objects up to 10',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4813,4,2,'Cognitive','Identifies numbers 1–10',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4814,4,2,'Cognitive','Identifies numbers 1–10',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4815,4,2,'Cognitive','Identifies numbers 1–10',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4816,4,2,'Cognitive','Identifies numbers 1–10',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4817,4,2,'Cognitive','Understands concepts of more/less, big/small',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4818,4,2,'Cognitive','Understands concepts of more/less, big/small',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4819,4,2,'Cognitive','Understands concepts of more/less, big/small',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4820,4,2,'Cognitive','Understands concepts of more/less, big/small',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4821,4,2,'Cognitive','Completes simple cause-and-effect tasks',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4822,4,2,'Cognitive','Completes simple cause-and-effect tasks',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4823,4,2,'Cognitive','Completes simple cause-and-effect tasks',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4824,4,2,'Cognitive','Completes simple cause-and-effect tasks',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4825,4,2,'Cognitive','Demonstrates problem-solving in daily tasks',1,'AP',NULL,'manual','2026-09-19 19:24:26'),(4826,4,2,'Cognitive','Demonstrates problem-solving in daily tasks',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4827,4,2,'Cognitive','Demonstrates problem-solving in daily tasks',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4828,4,2,'Cognitive','Demonstrates problem-solving in daily tasks',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4829,4,2,'Cognitive','Identifies body parts correctly',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4830,4,2,'Cognitive','Identifies body parts correctly',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4831,4,2,'Cognitive','Identifies body parts correctly',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4832,4,2,'Cognitive','Identifies body parts correctly',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4833,4,2,'Cognitive','Understands time concepts (before/after, today/tomorrow)',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4834,4,2,'Cognitive','Understands time concepts (before/after, today/tomorrow)',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4835,4,2,'Cognitive','Understands time concepts (before/after, today/tomorrow)',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4836,4,2,'Cognitive','Understands time concepts (before/after, today/tomorrow)',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4837,4,2,'Aesthetic/Creative','Participates in music and rhythm activities',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4838,4,2,'Aesthetic/Creative','Participates in music and rhythm activities',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4839,4,2,'Aesthetic/Creative','Participates in music and rhythm activities',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4840,4,2,'Aesthetic/Creative','Participates in music and rhythm activities',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4841,4,2,'Aesthetic/Creative','Expresses appreciation for music and sound',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4842,4,2,'Aesthetic/Creative','Expresses appreciation for music and sound',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4843,4,2,'Aesthetic/Creative','Expresses appreciation for music and sound',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4844,4,2,'Aesthetic/Creative','Expresses appreciation for music and sound',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4845,4,2,'Aesthetic/Creative','Moves body in response to music/rhythm',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4846,4,2,'Aesthetic/Creative','Moves body in response to music/rhythm',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4847,4,2,'Aesthetic/Creative','Moves body in response to music/rhythm',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4848,4,2,'Aesthetic/Creative','Moves body in response to music/rhythm',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4849,4,2,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4850,4,2,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4851,4,2,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4852,4,2,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4853,4,2,'Aesthetic/Creative','Expresses creativity through play',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4854,4,2,'Aesthetic/Creative','Expresses creativity through play',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4855,4,2,'Aesthetic/Creative','Expresses creativity through play',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4856,4,2,'Aesthetic/Creative','Expresses creativity through play',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4857,4,2,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',1,'AP',NULL,'manual','2026-09-19 19:24:26'),(4858,4,2,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4859,4,2,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4860,4,2,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4861,4,2,'Aesthetic/Creative','Watches and enjoys dramatic play/media',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4862,4,2,'Aesthetic/Creative','Watches and enjoys dramatic play/media',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4863,4,2,'Aesthetic/Creative','Watches and enjoys dramatic play/media',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4864,4,2,'Aesthetic/Creative','Watches and enjoys dramatic play/media',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4865,4,2,'Aesthetic/Creative','Communicates feelings through facial expressions',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4866,4,2,'Aesthetic/Creative','Communicates feelings through facial expressions',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4867,4,2,'Aesthetic/Creative','Communicates feelings through facial expressions',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4868,4,2,'Aesthetic/Creative','Communicates feelings through facial expressions',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4869,4,2,'Behavioral Development','Uses appropriate verbal communication for social interaction',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4870,4,2,'Behavioral Development','Uses appropriate verbal communication for social interaction',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4871,4,2,'Behavioral Development','Uses appropriate verbal communication for social interaction',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4872,4,2,'Behavioral Development','Uses appropriate verbal communication for social interaction',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4873,4,2,'Behavioral Development','Learns how to speak in a lower tone',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4874,4,2,'Behavioral Development','Learns how to speak in a lower tone',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4875,4,2,'Behavioral Development','Learns how to speak in a lower tone',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4876,4,2,'Behavioral Development','Learns how to speak in a lower tone',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4877,4,2,'Behavioral Development','Familiarizes with and takes relocated direction',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4878,4,2,'Behavioral Development','Familiarizes with and takes relocated direction',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4879,4,2,'Behavioral Development','Familiarizes with and takes relocated direction',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4880,4,2,'Behavioral Development','Familiarizes with and takes relocated direction',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4881,4,2,'Behavioral Development','Follows classroom/court instructions',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4882,4,2,'Behavioral Development','Follows classroom/court instructions',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4883,4,2,'Behavioral Development','Follows classroom/court instructions',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4884,4,2,'Behavioral Development','Follows classroom/court instructions',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4885,4,2,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4886,4,2,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4887,4,2,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4888,4,2,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4889,4,2,'Behavioral Development','Puts body materials and used items in proper place',1,'AP',NULL,'manual','2026-09-19 19:24:26'),(4890,4,2,'Behavioral Development','Puts body materials and used items in proper place',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4891,4,2,'Behavioral Development','Puts body materials and used items in proper place',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4892,4,2,'Behavioral Development','Puts body materials and used items in proper place',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4893,4,2,'Behavioral Development','Follows teacher\'s commands/inspection',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4894,4,2,'Behavioral Development','Follows teacher\'s commands/inspection',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4895,4,2,'Behavioral Development','Follows teacher\'s commands/inspection',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4896,4,2,'Behavioral Development','Follows teacher\'s commands/inspection',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4897,4,2,'Behavioral Development','Participates well in the lesson executed by the teacher',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4898,4,2,'Behavioral Development','Participates well in the lesson executed by the teacher',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4899,4,2,'Behavioral Development','Participates well in the lesson executed by the teacher',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4900,4,2,'Behavioral Development','Participates well in the lesson executed by the teacher',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4901,4,2,'Behavioral Development','Responds to questions and activities given to him/her',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4902,4,2,'Behavioral Development','Responds to questions and activities given to him/her',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4903,4,2,'Behavioral Development','Responds to questions and activities given to him/her',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4904,4,2,'Behavioral Development','Responds to questions and activities given to him/her',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4905,4,2,'Behavioral Development','Attends to task without getting out from the chair',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4906,4,2,'Behavioral Development','Attends to task without getting out from the chair',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4907,4,2,'Behavioral Development','Attends to task without getting out from the chair',3,NULL,NULL,'manual','2026-09-19 19:24:26'),(4908,4,2,'Behavioral Development','Attends to task without getting out from the chair',4,NULL,NULL,'manual','2026-09-19 19:24:26'),(4909,4,2,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',1,'P',NULL,'manual','2026-09-19 19:24:26'),(4910,4,2,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',2,NULL,NULL,'manual','2026-09-19 19:24:26'),(4911,4,2,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4912,4,2,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4913,4,2,'Behavioral Development','Responds positively to behavior management procedures',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4914,4,2,'Behavioral Development','Responds positively to behavior management procedures',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4915,4,2,'Behavioral Development','Responds positively to behavior management procedures',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4916,4,2,'Behavioral Development','Responds positively to behavior management procedures',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4917,4,2,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4918,4,2,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4919,4,2,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4920,4,2,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4921,4,2,'Behavioral Development','Reduces separation anxiety during the session',1,'AP',NULL,'manual','2026-09-19 19:24:27'),(4922,4,2,'Behavioral Development','Reduces separation anxiety during the session',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4923,4,2,'Behavioral Development','Reduces separation anxiety during the session',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4924,4,2,'Behavioral Development','Reduces separation anxiety during the session',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4925,4,2,'Behavioral Development','Plays with other children',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4926,4,2,'Behavioral Development','Plays with other children',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4927,4,2,'Behavioral Development','Plays with other children',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4928,4,2,'Behavioral Development','Plays with other children',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4929,4,2,'Behavioral Development','Takes turn in game activities',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4930,4,2,'Behavioral Development','Takes turn in game activities',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4931,4,2,'Behavioral Development','Takes turn in game activities',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4932,4,2,'Behavioral Development','Takes turn in game activities',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4933,4,2,'Behavioral Development','Knows how to wait when playing games',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4934,4,2,'Behavioral Development','Knows how to wait when playing games',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4935,4,2,'Behavioral Development','Knows how to wait when playing games',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4936,4,2,'Behavioral Development','Knows how to wait when playing games',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4937,4,2,'Behavioral Development','Shares things/food without teacher prompt',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4938,4,2,'Behavioral Development','Shares things/food without teacher prompt',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4939,4,2,'Behavioral Development','Shares things/food without teacher prompt',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4940,4,2,'Behavioral Development','Shares things/food without teacher prompt',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4941,4,2,'Behavioral Development','Sits for 30 minutes to one hour',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4942,4,2,'Behavioral Development','Sits for 30 minutes to one hour',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4943,4,2,'Behavioral Development','Sits for 30 minutes to one hour',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4944,4,2,'Behavioral Development','Sits for 30 minutes to one hour',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4945,4,2,'Behavioral Development','Develops longer attention span to complete the task',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4946,4,2,'Behavioral Development','Develops longer attention span to complete the task',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4947,4,2,'Behavioral Development','Develops longer attention span to complete the task',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4948,4,2,'Behavioral Development','Develops longer attention span to complete the task',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4949,4,2,'Behavioral Development','Completes task on hand',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4950,4,2,'Behavioral Development','Completes task on hand',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4951,4,2,'Behavioral Development','Completes task on hand',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4952,4,2,'Behavioral Development','Completes task on hand',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4953,4,2,'Orientation and Mobility','Tells the difference between places (from/to)',1,'AP',NULL,'manual','2026-09-19 19:24:27'),(4954,4,2,'Orientation and Mobility','Tells the difference between places (from/to)',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4955,4,2,'Orientation and Mobility','Tells the difference between places (from/to)',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4956,4,2,'Orientation and Mobility','Tells the difference between places (from/to)',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4957,4,2,'Orientation and Mobility','Positions body parts on the right/left sides',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4958,4,2,'Orientation and Mobility','Positions body parts on the right/left sides',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4959,4,2,'Orientation and Mobility','Positions body parts on the right/left sides',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4960,4,2,'Orientation and Mobility','Positions body parts on the right/left sides',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4961,4,2,'Orientation and Mobility','Tells the spatial relations of objects between tables',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4962,4,2,'Orientation and Mobility','Tells the spatial relations of objects between tables',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4963,4,2,'Orientation and Mobility','Tells the spatial relations of objects between tables',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4964,4,2,'Orientation and Mobility','Tells the spatial relations of objects between tables',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4965,4,2,'Orientation and Mobility','Follows directions given to find objects',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4966,4,2,'Orientation and Mobility','Follows directions given to find objects',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4967,4,2,'Orientation and Mobility','Follows directions given to find objects',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4968,4,2,'Orientation and Mobility','Follows directions given to find objects',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4969,4,2,'Orientation and Mobility','Uses position of classroom objects as reference to self',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4970,4,2,'Orientation and Mobility','Uses position of classroom objects as reference to self',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4971,4,2,'Orientation and Mobility','Uses position of classroom objects as reference to self',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4972,4,2,'Orientation and Mobility','Uses position of classroom objects as reference to self',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4973,4,2,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4974,4,2,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4975,4,2,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4976,4,2,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4977,4,2,'Orientation and Mobility','Shows the body with balance and rhythm',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4978,4,2,'Orientation and Mobility','Shows the body with balance and rhythm',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4979,4,2,'Orientation and Mobility','Shows the body with balance and rhythm',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4980,4,2,'Orientation and Mobility','Shows the body with balance and rhythm',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4981,4,2,'Orientation and Mobility','Identifies landmarks as clues',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4982,4,2,'Orientation and Mobility','Identifies landmarks as clues',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4983,4,2,'Orientation and Mobility','Identifies landmarks as clues',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4984,4,2,'Orientation and Mobility','Identifies landmarks as clues',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4985,4,2,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',1,'AP',NULL,'manual','2026-09-19 19:24:27'),(4986,4,2,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4987,4,2,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4988,4,2,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4989,4,2,'Orientation and Mobility','Uses parallel walk as guide',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4990,4,2,'Orientation and Mobility','Uses parallel walk as guide',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4991,4,2,'Orientation and Mobility','Uses parallel walk as guide',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4992,4,2,'Orientation and Mobility','Uses parallel walk as guide',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4993,4,2,'Orientation and Mobility','Works independently',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4994,4,2,'Orientation and Mobility','Works independently',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4995,4,2,'Orientation and Mobility','Works independently',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(4996,4,2,'Orientation and Mobility','Works independently',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(4997,4,2,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',1,'P',NULL,'manual','2026-09-19 19:24:27'),(4998,4,2,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(4999,4,2,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(5000,4,2,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(5001,4,2,'Orientation and Mobility','Expresses appreciation for the dance that they learned',1,'P',NULL,'manual','2026-09-19 19:24:27'),(5002,4,2,'Orientation and Mobility','Expresses appreciation for the dance that they learned',2,NULL,NULL,'manual','2026-09-19 19:24:27'),(5003,4,2,'Orientation and Mobility','Expresses appreciation for the dance that they learned',3,NULL,NULL,'manual','2026-09-19 19:24:27'),(5004,4,2,'Orientation and Mobility','Expresses appreciation for the dance that they learned',4,NULL,NULL,'manual','2026-09-19 19:24:27'),(5873,5,3,'Daily Living Skills','Holds and uses spoon/fork correctly',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5874,5,3,'Daily Living Skills','Holds and uses spoon/fork correctly',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5875,5,3,'Daily Living Skills','Holds and uses spoon/fork correctly',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5876,5,3,'Daily Living Skills','Holds and uses spoon/fork correctly',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5877,5,3,'Daily Living Skills','Drinks from a glass without spilling',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5878,5,3,'Daily Living Skills','Drinks from a glass without spilling',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5879,5,3,'Daily Living Skills','Drinks from a glass without spilling',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5880,5,3,'Daily Living Skills','Drinks from a glass without spilling',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5881,5,3,'Daily Living Skills','Eats using hands with minimal mess',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5882,5,3,'Daily Living Skills','Eats using hands with minimal mess',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5883,5,3,'Daily Living Skills','Eats using hands with minimal mess',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5884,5,3,'Daily Living Skills','Eats using hands with minimal mess',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5885,5,3,'Daily Living Skills','Opens food containers independently',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5886,5,3,'Daily Living Skills','Opens food containers independently',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5887,5,3,'Daily Living Skills','Opens food containers independently',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5888,5,3,'Daily Living Skills','Opens food containers independently',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5889,5,3,'Daily Living Skills','Indicates need to use the toilet',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5890,5,3,'Daily Living Skills','Indicates need to use the toilet',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5891,5,3,'Daily Living Skills','Indicates need to use the toilet',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5892,5,3,'Daily Living Skills','Indicates need to use the toilet',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5893,5,3,'Daily Living Skills','Goes to the toilet independently',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5894,5,3,'Daily Living Skills','Goes to the toilet independently',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5895,5,3,'Daily Living Skills','Goes to the toilet independently',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5896,5,3,'Daily Living Skills','Goes to the toilet independently',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5897,5,3,'Daily Living Skills','Flushes the toilet after use',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5898,5,3,'Daily Living Skills','Flushes the toilet after use',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5899,5,3,'Daily Living Skills','Flushes the toilet after use',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5900,5,3,'Daily Living Skills','Flushes the toilet after use',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5901,5,3,'Daily Living Skills','Washes hands after toileting',1,'AP',NULL,'manual','2026-09-19 19:24:32'),(5902,5,3,'Daily Living Skills','Washes hands after toileting',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5903,5,3,'Daily Living Skills','Washes hands after toileting',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5904,5,3,'Daily Living Skills','Washes hands after toileting',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5905,5,3,'Daily Living Skills','Puts on and removes clothing independently',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5906,5,3,'Daily Living Skills','Puts on and removes clothing independently',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5907,5,3,'Daily Living Skills','Puts on and removes clothing independently',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5908,5,3,'Daily Living Skills','Puts on and removes clothing independently',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5909,5,3,'Daily Living Skills','Buttons and unbuttons shirt',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5910,5,3,'Daily Living Skills','Buttons and unbuttons shirt',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5911,5,3,'Daily Living Skills','Buttons and unbuttons shirt',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5912,5,3,'Daily Living Skills','Buttons and unbuttons shirt',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5913,5,3,'Daily Living Skills','Zips and unzips clothing',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5914,5,3,'Daily Living Skills','Zips and unzips clothing',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5915,5,3,'Daily Living Skills','Zips and unzips clothing',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5916,5,3,'Daily Living Skills','Zips and unzips clothing',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5917,5,3,'Daily Living Skills','Wears shoes and socks correctly',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5918,5,3,'Daily Living Skills','Wears shoes and socks correctly',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5919,5,3,'Daily Living Skills','Wears shoes and socks correctly',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5920,5,3,'Daily Living Skills','Wears shoes and socks correctly',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5921,5,3,'Daily Living Skills','Brushes teeth with minimal supervision',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5922,5,3,'Daily Living Skills','Brushes teeth with minimal supervision',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5923,5,3,'Daily Living Skills','Brushes teeth with minimal supervision',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5924,5,3,'Daily Living Skills','Brushes teeth with minimal supervision',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5925,5,3,'Daily Living Skills','Washes and dries hands properly',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5926,5,3,'Daily Living Skills','Washes and dries hands properly',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5927,5,3,'Daily Living Skills','Washes and dries hands properly',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5928,5,3,'Daily Living Skills','Washes and dries hands properly',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5929,5,3,'Daily Living Skills','Combs/brushes hair independently',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5930,5,3,'Daily Living Skills','Combs/brushes hair independently',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5931,5,3,'Daily Living Skills','Combs/brushes hair independently',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5932,5,3,'Daily Living Skills','Combs/brushes hair independently',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5933,5,3,'Daily Living Skills','Maintains personal cleanliness',1,'AP',NULL,'manual','2026-09-19 19:24:32'),(5934,5,3,'Daily Living Skills','Maintains personal cleanliness',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5935,5,3,'Daily Living Skills','Maintains personal cleanliness',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5936,5,3,'Daily Living Skills','Maintains personal cleanliness',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5937,5,3,'Socio-Emotional','Shows awareness of own feelings and emotions',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5938,5,3,'Socio-Emotional','Shows awareness of own feelings and emotions',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5939,5,3,'Socio-Emotional','Shows awareness of own feelings and emotions',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5940,5,3,'Socio-Emotional','Shows awareness of own feelings and emotions',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5941,5,3,'Socio-Emotional','Interacts appropriately with peers and adults',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5942,5,3,'Socio-Emotional','Interacts appropriately with peers and adults',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5943,5,3,'Socio-Emotional','Interacts appropriately with peers and adults',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5944,5,3,'Socio-Emotional','Interacts appropriately with peers and adults',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5945,5,3,'Socio-Emotional','Takes turns and shares materials',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5946,5,3,'Socio-Emotional','Takes turns and shares materials',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5947,5,3,'Socio-Emotional','Takes turns and shares materials',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5948,5,3,'Socio-Emotional','Takes turns and shares materials',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5949,5,3,'Socio-Emotional','Expresses needs and wants appropriately',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5950,5,3,'Socio-Emotional','Expresses needs and wants appropriately',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5951,5,3,'Socio-Emotional','Expresses needs and wants appropriately',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5952,5,3,'Socio-Emotional','Expresses needs and wants appropriately',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5953,5,3,'Socio-Emotional','Follows classroom rules and routines',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5954,5,3,'Socio-Emotional','Follows classroom rules and routines',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5955,5,3,'Socio-Emotional','Follows classroom rules and routines',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5956,5,3,'Socio-Emotional','Follows classroom rules and routines',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5957,5,3,'Socio-Emotional','Shows appropriate emotional responses',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5958,5,3,'Socio-Emotional','Shows appropriate emotional responses',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5959,5,3,'Socio-Emotional','Shows appropriate emotional responses',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5960,5,3,'Socio-Emotional','Shows appropriate emotional responses',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5961,5,3,'Socio-Emotional','Develops and maintains peer relationships',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5962,5,3,'Socio-Emotional','Develops and maintains peer relationships',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5963,5,3,'Socio-Emotional','Develops and maintains peer relationships',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5964,5,3,'Socio-Emotional','Develops and maintains peer relationships',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5965,5,3,'Socio-Emotional','Demonstrates self-control in social situations',1,'AP',NULL,'manual','2026-09-19 19:24:32'),(5966,5,3,'Socio-Emotional','Demonstrates self-control in social situations',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5967,5,3,'Socio-Emotional','Demonstrates self-control in social situations',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5968,5,3,'Socio-Emotional','Demonstrates self-control in social situations',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5969,5,3,'Socio-Emotional','Shows empathy toward others',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5970,5,3,'Socio-Emotional','Shows empathy toward others',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5971,5,3,'Socio-Emotional','Shows empathy toward others',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5972,5,3,'Socio-Emotional','Shows empathy toward others',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5973,5,3,'Socio-Emotional','Seeks help from adults when needed',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5974,5,3,'Socio-Emotional','Seeks help from adults when needed',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5975,5,3,'Socio-Emotional','Seeks help from adults when needed',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5976,5,3,'Socio-Emotional','Seeks help from adults when needed',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5977,5,3,'Language Development','Listens attentively during instruction',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5978,5,3,'Language Development','Listens attentively during instruction',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5979,5,3,'Language Development','Listens attentively during instruction',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5980,5,3,'Language Development','Listens attentively during instruction',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5981,5,3,'Language Development','Follows one-step verbal directions',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5982,5,3,'Language Development','Follows one-step verbal directions',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5983,5,3,'Language Development','Follows one-step verbal directions',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5984,5,3,'Language Development','Follows one-step verbal directions',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5985,5,3,'Language Development','Follows two-step verbal directions',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5986,5,3,'Language Development','Follows two-step verbal directions',2,NULL,NULL,'manual','2026-09-19 19:24:32'),(5987,5,3,'Language Development','Follows two-step verbal directions',3,NULL,NULL,'manual','2026-09-19 19:24:32'),(5988,5,3,'Language Development','Follows two-step verbal directions',4,NULL,NULL,'manual','2026-09-19 19:24:32'),(5989,5,3,'Language Development','Identifies objects/pictures when named',1,'P',NULL,'manual','2026-09-19 19:24:32'),(5990,5,3,'Language Development','Identifies objects/pictures when named',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(5991,5,3,'Language Development','Identifies objects/pictures when named',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(5992,5,3,'Language Development','Identifies objects/pictures when named',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(5993,5,3,'Language Development','Communicates basic needs verbally or through AAC',1,'P',NULL,'manual','2026-09-19 19:24:33'),(5994,5,3,'Language Development','Communicates basic needs verbally or through AAC',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(5995,5,3,'Language Development','Communicates basic needs verbally or through AAC',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(5996,5,3,'Language Development','Communicates basic needs verbally or through AAC',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(5997,5,3,'Language Development','Uses simple sentences to express ideas',1,'AP',NULL,'manual','2026-09-19 19:24:33'),(5998,5,3,'Language Development','Uses simple sentences to express ideas',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(5999,5,3,'Language Development','Uses simple sentences to express ideas',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6000,5,3,'Language Development','Uses simple sentences to express ideas',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6001,5,3,'Language Development','Participates in simple conversations',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6002,5,3,'Language Development','Participates in simple conversations',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6003,5,3,'Language Development','Participates in simple conversations',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6004,5,3,'Language Development','Participates in simple conversations',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6005,5,3,'Language Development','Answers simple questions appropriately',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6006,5,3,'Language Development','Answers simple questions appropriately',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6007,5,3,'Language Development','Answers simple questions appropriately',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6008,5,3,'Language Development','Answers simple questions appropriately',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6009,5,3,'Language Development','Recognizes own name in print',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6010,5,3,'Language Development','Recognizes own name in print',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6011,5,3,'Language Development','Recognizes own name in print',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6012,5,3,'Language Development','Recognizes own name in print',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6013,5,3,'Language Development','Identifies letters of the alphabet',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6014,5,3,'Language Development','Identifies letters of the alphabet',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6015,5,3,'Language Development','Identifies letters of the alphabet',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6016,5,3,'Language Development','Identifies letters of the alphabet',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6017,5,3,'Language Development','Matches letters to sounds (phonics)',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6018,5,3,'Language Development','Matches letters to sounds (phonics)',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6019,5,3,'Language Development','Matches letters to sounds (phonics)',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6020,5,3,'Language Development','Matches letters to sounds (phonics)',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6021,5,3,'Language Development','Reads simple words and short phrases',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6022,5,3,'Language Development','Reads simple words and short phrases',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6023,5,3,'Language Development','Reads simple words and short phrases',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6024,5,3,'Language Development','Reads simple words and short phrases',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6025,5,3,'Language Development','Holds pencil/crayon with correct grip',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6026,5,3,'Language Development','Holds pencil/crayon with correct grip',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6027,5,3,'Language Development','Holds pencil/crayon with correct grip',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6028,5,3,'Language Development','Holds pencil/crayon with correct grip',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6029,5,3,'Language Development','Copies simple shapes and lines',1,'AP',NULL,'manual','2026-09-19 19:24:33'),(6030,5,3,'Language Development','Copies simple shapes and lines',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6031,5,3,'Language Development','Copies simple shapes and lines',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6032,5,3,'Language Development','Copies simple shapes and lines',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6033,5,3,'Language Development','Writes own name legibly',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6034,5,3,'Language Development','Writes own name legibly',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6035,5,3,'Language Development','Writes own name legibly',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6036,5,3,'Language Development','Writes own name legibly',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6037,5,3,'Language Development','Writes simple words from dictation',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6038,5,3,'Language Development','Writes simple words from dictation',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6039,5,3,'Language Development','Writes simple words from dictation',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6040,5,3,'Language Development','Writes simple words from dictation',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6041,5,3,'Psychomotor','Walks with balance and coordination',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6042,5,3,'Psychomotor','Walks with balance and coordination',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6043,5,3,'Psychomotor','Walks with balance and coordination',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6044,5,3,'Psychomotor','Walks with balance and coordination',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6045,5,3,'Psychomotor','Runs with control and coordination',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6046,5,3,'Psychomotor','Runs with control and coordination',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6047,5,3,'Psychomotor','Runs with control and coordination',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6048,5,3,'Psychomotor','Runs with control and coordination',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6049,5,3,'Psychomotor','Climbs stairs using alternating feet',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6050,5,3,'Psychomotor','Climbs stairs using alternating feet',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6051,5,3,'Psychomotor','Climbs stairs using alternating feet',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6052,5,3,'Psychomotor','Climbs stairs using alternating feet',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6053,5,3,'Psychomotor','Catches and throws a ball',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6054,5,3,'Psychomotor','Catches and throws a ball',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6055,5,3,'Psychomotor','Catches and throws a ball',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6056,5,3,'Psychomotor','Catches and throws a ball',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6057,5,3,'Psychomotor','Jumps on two feet and hops on one foot',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6058,5,3,'Psychomotor','Jumps on two feet and hops on one foot',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6059,5,3,'Psychomotor','Jumps on two feet and hops on one foot',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6060,5,3,'Psychomotor','Jumps on two feet and hops on one foot',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6061,5,3,'Psychomotor','Participates in physical activities with peers',1,'AP',NULL,'manual','2026-09-19 19:24:33'),(6062,5,3,'Psychomotor','Participates in physical activities with peers',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6063,5,3,'Psychomotor','Participates in physical activities with peers',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6064,5,3,'Psychomotor','Participates in physical activities with peers',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6065,5,3,'Psychomotor','Cuts along a straight and curved line with scissors',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6066,5,3,'Psychomotor','Cuts along a straight and curved line with scissors',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6067,5,3,'Psychomotor','Cuts along a straight and curved line with scissors',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6068,5,3,'Psychomotor','Cuts along a straight and curved line with scissors',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6069,5,3,'Psychomotor','Strings beads and manipulates small objects',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6070,5,3,'Psychomotor','Strings beads and manipulates small objects',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6071,5,3,'Psychomotor','Strings beads and manipulates small objects',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6072,5,3,'Psychomotor','Strings beads and manipulates small objects',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6073,5,3,'Psychomotor','Completes puzzles with multiple pieces',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6074,5,3,'Psychomotor','Completes puzzles with multiple pieces',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6075,5,3,'Psychomotor','Completes puzzles with multiple pieces',3,NULL,NULL,'manual','2026-09-19 19:24:33'),(6076,5,3,'Psychomotor','Completes puzzles with multiple pieces',4,NULL,NULL,'manual','2026-09-19 19:24:33'),(6077,5,3,'Psychomotor','Colors within boundaries',1,'P',NULL,'manual','2026-09-19 19:24:33'),(6078,5,3,'Psychomotor','Colors within boundaries',2,NULL,NULL,'manual','2026-09-19 19:24:33'),(6079,5,3,'Psychomotor','Colors within boundaries',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6080,5,3,'Psychomotor','Colors within boundaries',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6081,5,3,'Psychomotor','Copies simple geometric shapes',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6082,5,3,'Psychomotor','Copies simple geometric shapes',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6083,5,3,'Psychomotor','Copies simple geometric shapes',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6084,5,3,'Psychomotor','Copies simple geometric shapes',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6085,5,3,'Psychomotor','Demonstrates eye-hand coordination in tasks',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6086,5,3,'Psychomotor','Demonstrates eye-hand coordination in tasks',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6087,5,3,'Psychomotor','Demonstrates eye-hand coordination in tasks',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6088,5,3,'Psychomotor','Demonstrates eye-hand coordination in tasks',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6089,5,3,'Psychomotor','Identifies left and right body sides',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6090,5,3,'Psychomotor','Identifies left and right body sides',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6091,5,3,'Psychomotor','Identifies left and right body sides',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6092,5,3,'Psychomotor','Identifies left and right body sides',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6093,5,3,'Psychomotor','Tracks moving objects with eyes',1,'AP',NULL,'manual','2026-09-19 19:24:34'),(6094,5,3,'Psychomotor','Tracks moving objects with eyes',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6095,5,3,'Psychomotor','Tracks moving objects with eyes',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6096,5,3,'Psychomotor','Tracks moving objects with eyes',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6097,5,3,'Cognitive','Identifies and matches colors',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6098,5,3,'Cognitive','Identifies and matches colors',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6099,5,3,'Cognitive','Identifies and matches colors',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6100,5,3,'Cognitive','Identifies and matches colors',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6101,5,3,'Cognitive','Identifies and matches basic shapes',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6102,5,3,'Cognitive','Identifies and matches basic shapes',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6103,5,3,'Cognitive','Identifies and matches basic shapes',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6104,5,3,'Cognitive','Identifies and matches basic shapes',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6105,5,3,'Cognitive','Sorts and classifies objects by attributes',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6106,5,3,'Cognitive','Sorts and classifies objects by attributes',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6107,5,3,'Cognitive','Sorts and classifies objects by attributes',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6108,5,3,'Cognitive','Sorts and classifies objects by attributes',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6109,5,3,'Cognitive','Counts objects up to 10',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6110,5,3,'Cognitive','Counts objects up to 10',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6111,5,3,'Cognitive','Counts objects up to 10',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6112,5,3,'Cognitive','Counts objects up to 10',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6113,5,3,'Cognitive','Identifies numbers 1–10',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6114,5,3,'Cognitive','Identifies numbers 1–10',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6115,5,3,'Cognitive','Identifies numbers 1–10',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6116,5,3,'Cognitive','Identifies numbers 1–10',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6117,5,3,'Cognitive','Understands concepts of more/less, big/small',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6118,5,3,'Cognitive','Understands concepts of more/less, big/small',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6119,5,3,'Cognitive','Understands concepts of more/less, big/small',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6120,5,3,'Cognitive','Understands concepts of more/less, big/small',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6121,5,3,'Cognitive','Completes simple cause-and-effect tasks',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6122,5,3,'Cognitive','Completes simple cause-and-effect tasks',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6123,5,3,'Cognitive','Completes simple cause-and-effect tasks',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6124,5,3,'Cognitive','Completes simple cause-and-effect tasks',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6125,5,3,'Cognitive','Demonstrates problem-solving in daily tasks',1,'AP',NULL,'manual','2026-09-19 19:24:34'),(6126,5,3,'Cognitive','Demonstrates problem-solving in daily tasks',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6127,5,3,'Cognitive','Demonstrates problem-solving in daily tasks',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6128,5,3,'Cognitive','Demonstrates problem-solving in daily tasks',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6129,5,3,'Cognitive','Identifies body parts correctly',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6130,5,3,'Cognitive','Identifies body parts correctly',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6131,5,3,'Cognitive','Identifies body parts correctly',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6132,5,3,'Cognitive','Identifies body parts correctly',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6133,5,3,'Cognitive','Understands time concepts (before/after, today/tomorrow)',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6134,5,3,'Cognitive','Understands time concepts (before/after, today/tomorrow)',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6135,5,3,'Cognitive','Understands time concepts (before/after, today/tomorrow)',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6136,5,3,'Cognitive','Understands time concepts (before/after, today/tomorrow)',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6137,5,3,'Aesthetic/Creative','Participates in music and rhythm activities',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6138,5,3,'Aesthetic/Creative','Participates in music and rhythm activities',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6139,5,3,'Aesthetic/Creative','Participates in music and rhythm activities',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6140,5,3,'Aesthetic/Creative','Participates in music and rhythm activities',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6141,5,3,'Aesthetic/Creative','Expresses appreciation for music and sound',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6142,5,3,'Aesthetic/Creative','Expresses appreciation for music and sound',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6143,5,3,'Aesthetic/Creative','Expresses appreciation for music and sound',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6144,5,3,'Aesthetic/Creative','Expresses appreciation for music and sound',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6145,5,3,'Aesthetic/Creative','Moves body in response to music/rhythm',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6146,5,3,'Aesthetic/Creative','Moves body in response to music/rhythm',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6147,5,3,'Aesthetic/Creative','Moves body in response to music/rhythm',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6148,5,3,'Aesthetic/Creative','Moves body in response to music/rhythm',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6149,5,3,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6150,5,3,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6151,5,3,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6152,5,3,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6153,5,3,'Aesthetic/Creative','Expresses creativity through play',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6154,5,3,'Aesthetic/Creative','Expresses creativity through play',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6155,5,3,'Aesthetic/Creative','Expresses creativity through play',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6156,5,3,'Aesthetic/Creative','Expresses creativity through play',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6157,5,3,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',1,'AP',NULL,'manual','2026-09-19 19:24:34'),(6158,5,3,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6159,5,3,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6160,5,3,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6161,5,3,'Aesthetic/Creative','Watches and enjoys dramatic play/media',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6162,5,3,'Aesthetic/Creative','Watches and enjoys dramatic play/media',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6163,5,3,'Aesthetic/Creative','Watches and enjoys dramatic play/media',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6164,5,3,'Aesthetic/Creative','Watches and enjoys dramatic play/media',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6165,5,3,'Aesthetic/Creative','Communicates feelings through facial expressions',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6166,5,3,'Aesthetic/Creative','Communicates feelings through facial expressions',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6167,5,3,'Aesthetic/Creative','Communicates feelings through facial expressions',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6168,5,3,'Aesthetic/Creative','Communicates feelings through facial expressions',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6169,5,3,'Behavioral Development','Uses appropriate verbal communication for social interaction',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6170,5,3,'Behavioral Development','Uses appropriate verbal communication for social interaction',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6171,5,3,'Behavioral Development','Uses appropriate verbal communication for social interaction',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6172,5,3,'Behavioral Development','Uses appropriate verbal communication for social interaction',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6173,5,3,'Behavioral Development','Learns how to speak in a lower tone',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6174,5,3,'Behavioral Development','Learns how to speak in a lower tone',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6175,5,3,'Behavioral Development','Learns how to speak in a lower tone',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6176,5,3,'Behavioral Development','Learns how to speak in a lower tone',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6177,5,3,'Behavioral Development','Familiarizes with and takes relocated direction',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6178,5,3,'Behavioral Development','Familiarizes with and takes relocated direction',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6179,5,3,'Behavioral Development','Familiarizes with and takes relocated direction',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6180,5,3,'Behavioral Development','Familiarizes with and takes relocated direction',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6181,5,3,'Behavioral Development','Follows classroom/court instructions',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6182,5,3,'Behavioral Development','Follows classroom/court instructions',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6183,5,3,'Behavioral Development','Follows classroom/court instructions',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6184,5,3,'Behavioral Development','Follows classroom/court instructions',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6185,5,3,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',1,'P',NULL,'manual','2026-09-19 19:24:34'),(6186,5,3,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',2,NULL,NULL,'manual','2026-09-19 19:24:34'),(6187,5,3,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',3,NULL,NULL,'manual','2026-09-19 19:24:34'),(6188,5,3,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',4,NULL,NULL,'manual','2026-09-19 19:24:34'),(6189,5,3,'Behavioral Development','Puts body materials and used items in proper place',1,'AP',NULL,'manual','2026-09-19 19:24:34'),(6190,5,3,'Behavioral Development','Puts body materials and used items in proper place',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6191,5,3,'Behavioral Development','Puts body materials and used items in proper place',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6192,5,3,'Behavioral Development','Puts body materials and used items in proper place',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6193,5,3,'Behavioral Development','Follows teacher\'s commands/inspection',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6194,5,3,'Behavioral Development','Follows teacher\'s commands/inspection',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6195,5,3,'Behavioral Development','Follows teacher\'s commands/inspection',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6196,5,3,'Behavioral Development','Follows teacher\'s commands/inspection',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6197,5,3,'Behavioral Development','Participates well in the lesson executed by the teacher',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6198,5,3,'Behavioral Development','Participates well in the lesson executed by the teacher',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6199,5,3,'Behavioral Development','Participates well in the lesson executed by the teacher',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6200,5,3,'Behavioral Development','Participates well in the lesson executed by the teacher',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6201,5,3,'Behavioral Development','Responds to questions and activities given to him/her',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6202,5,3,'Behavioral Development','Responds to questions and activities given to him/her',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6203,5,3,'Behavioral Development','Responds to questions and activities given to him/her',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6204,5,3,'Behavioral Development','Responds to questions and activities given to him/her',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6205,5,3,'Behavioral Development','Attends to task without getting out from the chair',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6206,5,3,'Behavioral Development','Attends to task without getting out from the chair',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6207,5,3,'Behavioral Development','Attends to task without getting out from the chair',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6208,5,3,'Behavioral Development','Attends to task without getting out from the chair',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6209,5,3,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6210,5,3,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6211,5,3,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6212,5,3,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6213,5,3,'Behavioral Development','Responds positively to behavior management procedures',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6214,5,3,'Behavioral Development','Responds positively to behavior management procedures',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6215,5,3,'Behavioral Development','Responds positively to behavior management procedures',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6216,5,3,'Behavioral Development','Responds positively to behavior management procedures',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6217,5,3,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6218,5,3,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6219,5,3,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6220,5,3,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6221,5,3,'Behavioral Development','Reduces separation anxiety during the session',1,'AP',NULL,'manual','2026-09-19 19:24:35'),(6222,5,3,'Behavioral Development','Reduces separation anxiety during the session',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6223,5,3,'Behavioral Development','Reduces separation anxiety during the session',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6224,5,3,'Behavioral Development','Reduces separation anxiety during the session',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6225,5,3,'Behavioral Development','Plays with other children',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6226,5,3,'Behavioral Development','Plays with other children',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6227,5,3,'Behavioral Development','Plays with other children',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6228,5,3,'Behavioral Development','Plays with other children',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6229,5,3,'Behavioral Development','Takes turn in game activities',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6230,5,3,'Behavioral Development','Takes turn in game activities',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6231,5,3,'Behavioral Development','Takes turn in game activities',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6232,5,3,'Behavioral Development','Takes turn in game activities',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6233,5,3,'Behavioral Development','Knows how to wait when playing games',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6234,5,3,'Behavioral Development','Knows how to wait when playing games',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6235,5,3,'Behavioral Development','Knows how to wait when playing games',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6236,5,3,'Behavioral Development','Knows how to wait when playing games',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6237,5,3,'Behavioral Development','Shares things/food without teacher prompt',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6238,5,3,'Behavioral Development','Shares things/food without teacher prompt',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6239,5,3,'Behavioral Development','Shares things/food without teacher prompt',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6240,5,3,'Behavioral Development','Shares things/food without teacher prompt',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6241,5,3,'Behavioral Development','Sits for 30 minutes to one hour',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6242,5,3,'Behavioral Development','Sits for 30 minutes to one hour',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6243,5,3,'Behavioral Development','Sits for 30 minutes to one hour',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6244,5,3,'Behavioral Development','Sits for 30 minutes to one hour',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6245,5,3,'Behavioral Development','Develops longer attention span to complete the task',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6246,5,3,'Behavioral Development','Develops longer attention span to complete the task',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6247,5,3,'Behavioral Development','Develops longer attention span to complete the task',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6248,5,3,'Behavioral Development','Develops longer attention span to complete the task',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6249,5,3,'Behavioral Development','Completes task on hand',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6250,5,3,'Behavioral Development','Completes task on hand',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6251,5,3,'Behavioral Development','Completes task on hand',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6252,5,3,'Behavioral Development','Completes task on hand',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6253,5,3,'Orientation and Mobility','Tells the difference between places (from/to)',1,'AP',NULL,'manual','2026-09-19 19:24:35'),(6254,5,3,'Orientation and Mobility','Tells the difference between places (from/to)',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6255,5,3,'Orientation and Mobility','Tells the difference between places (from/to)',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6256,5,3,'Orientation and Mobility','Tells the difference between places (from/to)',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6257,5,3,'Orientation and Mobility','Positions body parts on the right/left sides',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6258,5,3,'Orientation and Mobility','Positions body parts on the right/left sides',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6259,5,3,'Orientation and Mobility','Positions body parts on the right/left sides',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6260,5,3,'Orientation and Mobility','Positions body parts on the right/left sides',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6261,5,3,'Orientation and Mobility','Tells the spatial relations of objects between tables',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6262,5,3,'Orientation and Mobility','Tells the spatial relations of objects between tables',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6263,5,3,'Orientation and Mobility','Tells the spatial relations of objects between tables',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6264,5,3,'Orientation and Mobility','Tells the spatial relations of objects between tables',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6265,5,3,'Orientation and Mobility','Follows directions given to find objects',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6266,5,3,'Orientation and Mobility','Follows directions given to find objects',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6267,5,3,'Orientation and Mobility','Follows directions given to find objects',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6268,5,3,'Orientation and Mobility','Follows directions given to find objects',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6269,5,3,'Orientation and Mobility','Uses position of classroom objects as reference to self',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6270,5,3,'Orientation and Mobility','Uses position of classroom objects as reference to self',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6271,5,3,'Orientation and Mobility','Uses position of classroom objects as reference to self',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6272,5,3,'Orientation and Mobility','Uses position of classroom objects as reference to self',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6273,5,3,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6274,5,3,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6275,5,3,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6276,5,3,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6277,5,3,'Orientation and Mobility','Shows the body with balance and rhythm',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6278,5,3,'Orientation and Mobility','Shows the body with balance and rhythm',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6279,5,3,'Orientation and Mobility','Shows the body with balance and rhythm',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6280,5,3,'Orientation and Mobility','Shows the body with balance and rhythm',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6281,5,3,'Orientation and Mobility','Identifies landmarks as clues',1,'P',NULL,'manual','2026-09-19 19:24:35'),(6282,5,3,'Orientation and Mobility','Identifies landmarks as clues',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6283,5,3,'Orientation and Mobility','Identifies landmarks as clues',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6284,5,3,'Orientation and Mobility','Identifies landmarks as clues',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6285,5,3,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',1,'AP',NULL,'manual','2026-09-19 19:24:35'),(6286,5,3,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',2,NULL,NULL,'manual','2026-09-19 19:24:35'),(6287,5,3,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',3,NULL,NULL,'manual','2026-09-19 19:24:35'),(6288,5,3,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',4,NULL,NULL,'manual','2026-09-19 19:24:35'),(6289,5,3,'Orientation and Mobility','Uses parallel walk as guide',1,'P',NULL,'manual','2026-09-19 19:24:36'),(6290,5,3,'Orientation and Mobility','Uses parallel walk as guide',2,NULL,NULL,'manual','2026-09-19 19:24:36'),(6291,5,3,'Orientation and Mobility','Uses parallel walk as guide',3,NULL,NULL,'manual','2026-09-19 19:24:36'),(6292,5,3,'Orientation and Mobility','Uses parallel walk as guide',4,NULL,NULL,'manual','2026-09-19 19:24:36'),(6293,5,3,'Orientation and Mobility','Works independently',1,'P',NULL,'manual','2026-09-19 19:24:36'),(6294,5,3,'Orientation and Mobility','Works independently',2,NULL,NULL,'manual','2026-09-19 19:24:36'),(6295,5,3,'Orientation and Mobility','Works independently',3,NULL,NULL,'manual','2026-09-19 19:24:36'),(6296,5,3,'Orientation and Mobility','Works independently',4,NULL,NULL,'manual','2026-09-19 19:24:36'),(6297,5,3,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',1,'P',NULL,'manual','2026-09-19 19:24:36'),(6298,5,3,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',2,NULL,NULL,'manual','2026-09-19 19:24:36'),(6299,5,3,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',3,NULL,NULL,'manual','2026-09-19 19:24:36'),(6300,5,3,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',4,NULL,NULL,'manual','2026-09-19 19:24:36'),(6301,5,3,'Orientation and Mobility','Expresses appreciation for the dance that they learned',1,'P',NULL,'manual','2026-09-19 19:24:36'),(6302,5,3,'Orientation and Mobility','Expresses appreciation for the dance that they learned',2,NULL,NULL,'manual','2026-09-19 19:24:36'),(6303,5,3,'Orientation and Mobility','Expresses appreciation for the dance that they learned',3,NULL,NULL,'manual','2026-09-19 19:24:36'),(6304,5,3,'Orientation and Mobility','Expresses appreciation for the dance that they learned',4,NULL,NULL,'manual','2026-09-19 19:24:36'),(6737,6,4,'Daily Living Skills','Holds and uses spoon/fork correctly',1,'D',NULL,'manual','2026-09-19 19:24:40'),(6738,6,4,'Daily Living Skills','Holds and uses spoon/fork correctly',2,NULL,NULL,'manual','2026-09-19 19:24:40'),(6739,6,4,'Daily Living Skills','Holds and uses spoon/fork correctly',3,NULL,NULL,'manual','2026-09-19 19:24:40'),(6740,6,4,'Daily Living Skills','Holds and uses spoon/fork correctly',4,NULL,NULL,'manual','2026-09-19 19:24:40'),(6741,6,4,'Daily Living Skills','Drinks from a glass without spilling',1,'AP',NULL,'manual','2026-09-19 19:24:40'),(6742,6,4,'Daily Living Skills','Drinks from a glass without spilling',2,NULL,NULL,'manual','2026-09-19 19:24:40'),(6743,6,4,'Daily Living Skills','Drinks from a glass without spilling',3,NULL,NULL,'manual','2026-09-19 19:24:40'),(6744,6,4,'Daily Living Skills','Drinks from a glass without spilling',4,NULL,NULL,'manual','2026-09-19 19:24:40'),(6745,6,4,'Daily Living Skills','Eats using hands with minimal mess',1,'AP',NULL,'manual','2026-09-19 19:24:40'),(6746,6,4,'Daily Living Skills','Eats using hands with minimal mess',2,NULL,NULL,'manual','2026-09-19 19:24:40'),(6747,6,4,'Daily Living Skills','Eats using hands with minimal mess',3,NULL,NULL,'manual','2026-09-19 19:24:40'),(6748,6,4,'Daily Living Skills','Eats using hands with minimal mess',4,NULL,NULL,'manual','2026-09-19 19:24:40'),(6749,6,4,'Daily Living Skills','Opens food containers independently',1,'P',NULL,'manual','2026-09-19 19:24:40'),(6750,6,4,'Daily Living Skills','Opens food containers independently',2,NULL,NULL,'manual','2026-09-19 19:24:40'),(6751,6,4,'Daily Living Skills','Opens food containers independently',3,NULL,NULL,'manual','2026-09-19 19:24:40'),(6752,6,4,'Daily Living Skills','Opens food containers independently',4,NULL,NULL,'manual','2026-09-19 19:24:40'),(6753,6,4,'Daily Living Skills','Indicates need to use the toilet',1,'D',NULL,'manual','2026-09-19 19:24:40'),(6754,6,4,'Daily Living Skills','Indicates need to use the toilet',2,NULL,NULL,'manual','2026-09-19 19:24:40'),(6755,6,4,'Daily Living Skills','Indicates need to use the toilet',3,NULL,NULL,'manual','2026-09-19 19:24:40'),(6756,6,4,'Daily Living Skills','Indicates need to use the toilet',4,NULL,NULL,'manual','2026-09-19 19:24:40'),(6757,6,4,'Daily Living Skills','Goes to the toilet independently',1,'D',NULL,'manual','2026-09-19 19:24:40'),(6758,6,4,'Daily Living Skills','Goes to the toilet independently',2,NULL,NULL,'manual','2026-09-19 19:24:40'),(6759,6,4,'Daily Living Skills','Goes to the toilet independently',3,NULL,NULL,'manual','2026-09-19 19:24:40'),(6760,6,4,'Daily Living Skills','Goes to the toilet independently',4,NULL,NULL,'manual','2026-09-19 19:24:40'),(6761,6,4,'Daily Living Skills','Flushes the toilet after use',1,'AP',NULL,'manual','2026-09-19 19:24:40'),(6762,6,4,'Daily Living Skills','Flushes the toilet after use',2,NULL,NULL,'manual','2026-09-19 19:24:40'),(6763,6,4,'Daily Living Skills','Flushes the toilet after use',3,NULL,NULL,'manual','2026-09-19 19:24:40'),(6764,6,4,'Daily Living Skills','Flushes the toilet after use',4,NULL,NULL,'manual','2026-09-19 19:24:40'),(6765,6,4,'Daily Living Skills','Washes hands after toileting',1,'AP',NULL,'manual','2026-09-19 19:24:40'),(6766,6,4,'Daily Living Skills','Washes hands after toileting',2,NULL,NULL,'manual','2026-09-19 19:24:40'),(6767,6,4,'Daily Living Skills','Washes hands after toileting',3,NULL,NULL,'manual','2026-09-19 19:24:40'),(6768,6,4,'Daily Living Skills','Washes hands after toileting',4,NULL,NULL,'manual','2026-09-19 19:24:40'),(6769,6,4,'Daily Living Skills','Puts on and removes clothing independently',1,'P',NULL,'manual','2026-09-19 19:24:40'),(6770,6,4,'Daily Living Skills','Puts on and removes clothing independently',2,NULL,NULL,'manual','2026-09-19 19:24:40'),(6771,6,4,'Daily Living Skills','Puts on and removes clothing independently',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6772,6,4,'Daily Living Skills','Puts on and removes clothing independently',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6773,6,4,'Daily Living Skills','Buttons and unbuttons shirt',1,'D',NULL,'manual','2026-09-19 19:24:41'),(6774,6,4,'Daily Living Skills','Buttons and unbuttons shirt',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6775,6,4,'Daily Living Skills','Buttons and unbuttons shirt',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6776,6,4,'Daily Living Skills','Buttons and unbuttons shirt',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6777,6,4,'Daily Living Skills','Zips and unzips clothing',1,'D',NULL,'manual','2026-09-19 19:24:41'),(6778,6,4,'Daily Living Skills','Zips and unzips clothing',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6779,6,4,'Daily Living Skills','Zips and unzips clothing',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6780,6,4,'Daily Living Skills','Zips and unzips clothing',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6781,6,4,'Daily Living Skills','Wears shoes and socks correctly',1,'AP',NULL,'manual','2026-09-19 19:24:41'),(6782,6,4,'Daily Living Skills','Wears shoes and socks correctly',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6783,6,4,'Daily Living Skills','Wears shoes and socks correctly',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6784,6,4,'Daily Living Skills','Wears shoes and socks correctly',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6785,6,4,'Daily Living Skills','Brushes teeth with minimal supervision',1,'AP',NULL,'manual','2026-09-19 19:24:41'),(6786,6,4,'Daily Living Skills','Brushes teeth with minimal supervision',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6787,6,4,'Daily Living Skills','Brushes teeth with minimal supervision',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6788,6,4,'Daily Living Skills','Brushes teeth with minimal supervision',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6789,6,4,'Daily Living Skills','Washes and dries hands properly',1,'P',NULL,'manual','2026-09-19 19:24:41'),(6790,6,4,'Daily Living Skills','Washes and dries hands properly',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6791,6,4,'Daily Living Skills','Washes and dries hands properly',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6792,6,4,'Daily Living Skills','Washes and dries hands properly',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6793,6,4,'Daily Living Skills','Combs/brushes hair independently',1,'D',NULL,'manual','2026-09-19 19:24:41'),(6794,6,4,'Daily Living Skills','Combs/brushes hair independently',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6795,6,4,'Daily Living Skills','Combs/brushes hair independently',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6796,6,4,'Daily Living Skills','Combs/brushes hair independently',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6797,6,4,'Daily Living Skills','Maintains personal cleanliness',1,'D',NULL,'manual','2026-09-19 19:24:41'),(6798,6,4,'Daily Living Skills','Maintains personal cleanliness',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6799,6,4,'Daily Living Skills','Maintains personal cleanliness',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6800,6,4,'Daily Living Skills','Maintains personal cleanliness',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6801,6,4,'Socio-Emotional','Shows awareness of own feelings and emotions',1,'AP',NULL,'manual','2026-09-19 19:24:41'),(6802,6,4,'Socio-Emotional','Shows awareness of own feelings and emotions',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6803,6,4,'Socio-Emotional','Shows awareness of own feelings and emotions',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6804,6,4,'Socio-Emotional','Shows awareness of own feelings and emotions',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6805,6,4,'Socio-Emotional','Interacts appropriately with peers and adults',1,'AP',NULL,'manual','2026-09-19 19:24:41'),(6806,6,4,'Socio-Emotional','Interacts appropriately with peers and adults',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6807,6,4,'Socio-Emotional','Interacts appropriately with peers and adults',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6808,6,4,'Socio-Emotional','Interacts appropriately with peers and adults',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6809,6,4,'Socio-Emotional','Takes turns and shares materials',1,'P',NULL,'manual','2026-09-19 19:24:41'),(6810,6,4,'Socio-Emotional','Takes turns and shares materials',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6811,6,4,'Socio-Emotional','Takes turns and shares materials',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6812,6,4,'Socio-Emotional','Takes turns and shares materials',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6813,6,4,'Socio-Emotional','Expresses needs and wants appropriately',1,'D',NULL,'manual','2026-09-19 19:24:41'),(6814,6,4,'Socio-Emotional','Expresses needs and wants appropriately',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6815,6,4,'Socio-Emotional','Expresses needs and wants appropriately',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6816,6,4,'Socio-Emotional','Expresses needs and wants appropriately',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6817,6,4,'Socio-Emotional','Follows classroom rules and routines',1,'D',NULL,'manual','2026-09-19 19:24:41'),(6818,6,4,'Socio-Emotional','Follows classroom rules and routines',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6819,6,4,'Socio-Emotional','Follows classroom rules and routines',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6820,6,4,'Socio-Emotional','Follows classroom rules and routines',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6821,6,4,'Socio-Emotional','Shows appropriate emotional responses',1,'AP',NULL,'manual','2026-09-19 19:24:41'),(6822,6,4,'Socio-Emotional','Shows appropriate emotional responses',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6823,6,4,'Socio-Emotional','Shows appropriate emotional responses',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6824,6,4,'Socio-Emotional','Shows appropriate emotional responses',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6825,6,4,'Socio-Emotional','Develops and maintains peer relationships',1,'AP',NULL,'manual','2026-09-19 19:24:41'),(6826,6,4,'Socio-Emotional','Develops and maintains peer relationships',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6827,6,4,'Socio-Emotional','Develops and maintains peer relationships',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6828,6,4,'Socio-Emotional','Develops and maintains peer relationships',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6829,6,4,'Socio-Emotional','Demonstrates self-control in social situations',1,'P',NULL,'manual','2026-09-19 19:24:41'),(6830,6,4,'Socio-Emotional','Demonstrates self-control in social situations',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6831,6,4,'Socio-Emotional','Demonstrates self-control in social situations',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6832,6,4,'Socio-Emotional','Demonstrates self-control in social situations',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6833,6,4,'Socio-Emotional','Shows empathy toward others',1,'D',NULL,'manual','2026-09-19 19:24:41'),(6834,6,4,'Socio-Emotional','Shows empathy toward others',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6835,6,4,'Socio-Emotional','Shows empathy toward others',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6836,6,4,'Socio-Emotional','Shows empathy toward others',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6837,6,4,'Socio-Emotional','Seeks help from adults when needed',1,'D',NULL,'manual','2026-09-19 19:24:41'),(6838,6,4,'Socio-Emotional','Seeks help from adults when needed',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6839,6,4,'Socio-Emotional','Seeks help from adults when needed',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6840,6,4,'Socio-Emotional','Seeks help from adults when needed',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6841,6,4,'Language Development','Listens attentively during instruction',1,'AP',NULL,'manual','2026-09-19 19:24:41'),(6842,6,4,'Language Development','Listens attentively during instruction',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6843,6,4,'Language Development','Listens attentively during instruction',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6844,6,4,'Language Development','Listens attentively during instruction',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6845,6,4,'Language Development','Follows one-step verbal directions',1,'AP',NULL,'manual','2026-09-19 19:24:41'),(6846,6,4,'Language Development','Follows one-step verbal directions',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6847,6,4,'Language Development','Follows one-step verbal directions',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6848,6,4,'Language Development','Follows one-step verbal directions',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6849,6,4,'Language Development','Follows two-step verbal directions',1,'P',NULL,'manual','2026-09-19 19:24:41'),(6850,6,4,'Language Development','Follows two-step verbal directions',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6851,6,4,'Language Development','Follows two-step verbal directions',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6852,6,4,'Language Development','Follows two-step verbal directions',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6853,6,4,'Language Development','Identifies objects/pictures when named',1,'D',NULL,'manual','2026-09-19 19:24:41'),(6854,6,4,'Language Development','Identifies objects/pictures when named',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6855,6,4,'Language Development','Identifies objects/pictures when named',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6856,6,4,'Language Development','Identifies objects/pictures when named',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6857,6,4,'Language Development','Communicates basic needs verbally or through AAC',1,'D',NULL,'manual','2026-09-19 19:24:41'),(6858,6,4,'Language Development','Communicates basic needs verbally or through AAC',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6859,6,4,'Language Development','Communicates basic needs verbally or through AAC',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6860,6,4,'Language Development','Communicates basic needs verbally or through AAC',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6861,6,4,'Language Development','Uses simple sentences to express ideas',1,'AP',NULL,'manual','2026-09-19 19:24:41'),(6862,6,4,'Language Development','Uses simple sentences to express ideas',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6863,6,4,'Language Development','Uses simple sentences to express ideas',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6864,6,4,'Language Development','Uses simple sentences to express ideas',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6865,6,4,'Language Development','Participates in simple conversations',1,'AP',NULL,'manual','2026-09-19 19:24:41'),(6866,6,4,'Language Development','Participates in simple conversations',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6867,6,4,'Language Development','Participates in simple conversations',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6868,6,4,'Language Development','Participates in simple conversations',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6869,6,4,'Language Development','Answers simple questions appropriately',1,'P',NULL,'manual','2026-09-19 19:24:41'),(6870,6,4,'Language Development','Answers simple questions appropriately',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6871,6,4,'Language Development','Answers simple questions appropriately',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6872,6,4,'Language Development','Answers simple questions appropriately',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6873,6,4,'Language Development','Recognizes own name in print',1,'D',NULL,'manual','2026-09-19 19:24:41'),(6874,6,4,'Language Development','Recognizes own name in print',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6875,6,4,'Language Development','Recognizes own name in print',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6876,6,4,'Language Development','Recognizes own name in print',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6877,6,4,'Language Development','Identifies letters of the alphabet',1,'D',NULL,'manual','2026-09-19 19:24:41'),(6878,6,4,'Language Development','Identifies letters of the alphabet',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6879,6,4,'Language Development','Identifies letters of the alphabet',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6880,6,4,'Language Development','Identifies letters of the alphabet',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6881,6,4,'Language Development','Matches letters to sounds (phonics)',1,'AP',NULL,'manual','2026-09-19 19:24:41'),(6882,6,4,'Language Development','Matches letters to sounds (phonics)',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6883,6,4,'Language Development','Matches letters to sounds (phonics)',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6884,6,4,'Language Development','Matches letters to sounds (phonics)',4,NULL,NULL,'manual','2026-09-19 19:24:41'),(6885,6,4,'Language Development','Reads simple words and short phrases',1,'AP',NULL,'manual','2026-09-19 19:24:41'),(6886,6,4,'Language Development','Reads simple words and short phrases',2,NULL,NULL,'manual','2026-09-19 19:24:41'),(6887,6,4,'Language Development','Reads simple words and short phrases',3,NULL,NULL,'manual','2026-09-19 19:24:41'),(6888,6,4,'Language Development','Reads simple words and short phrases',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6889,6,4,'Language Development','Holds pencil/crayon with correct grip',1,'P',NULL,'manual','2026-09-19 19:24:42'),(6890,6,4,'Language Development','Holds pencil/crayon with correct grip',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6891,6,4,'Language Development','Holds pencil/crayon with correct grip',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6892,6,4,'Language Development','Holds pencil/crayon with correct grip',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6893,6,4,'Language Development','Copies simple shapes and lines',1,'D',NULL,'manual','2026-09-19 19:24:42'),(6894,6,4,'Language Development','Copies simple shapes and lines',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6895,6,4,'Language Development','Copies simple shapes and lines',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6896,6,4,'Language Development','Copies simple shapes and lines',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6897,6,4,'Language Development','Writes own name legibly',1,'D',NULL,'manual','2026-09-19 19:24:42'),(6898,6,4,'Language Development','Writes own name legibly',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6899,6,4,'Language Development','Writes own name legibly',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6900,6,4,'Language Development','Writes own name legibly',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6901,6,4,'Language Development','Writes simple words from dictation',1,'AP',NULL,'manual','2026-09-19 19:24:42'),(6902,6,4,'Language Development','Writes simple words from dictation',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6903,6,4,'Language Development','Writes simple words from dictation',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6904,6,4,'Language Development','Writes simple words from dictation',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6905,6,4,'Psychomotor','Walks with balance and coordination',1,'AP',NULL,'manual','2026-09-19 19:24:42'),(6906,6,4,'Psychomotor','Walks with balance and coordination',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6907,6,4,'Psychomotor','Walks with balance and coordination',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6908,6,4,'Psychomotor','Walks with balance and coordination',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6909,6,4,'Psychomotor','Runs with control and coordination',1,'P',NULL,'manual','2026-09-19 19:24:42'),(6910,6,4,'Psychomotor','Runs with control and coordination',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6911,6,4,'Psychomotor','Runs with control and coordination',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6912,6,4,'Psychomotor','Runs with control and coordination',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6913,6,4,'Psychomotor','Climbs stairs using alternating feet',1,'D',NULL,'manual','2026-09-19 19:24:42'),(6914,6,4,'Psychomotor','Climbs stairs using alternating feet',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6915,6,4,'Psychomotor','Climbs stairs using alternating feet',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6916,6,4,'Psychomotor','Climbs stairs using alternating feet',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6917,6,4,'Psychomotor','Catches and throws a ball',1,'D',NULL,'manual','2026-09-19 19:24:42'),(6918,6,4,'Psychomotor','Catches and throws a ball',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6919,6,4,'Psychomotor','Catches and throws a ball',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6920,6,4,'Psychomotor','Catches and throws a ball',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6921,6,4,'Psychomotor','Jumps on two feet and hops on one foot',1,'AP',NULL,'manual','2026-09-19 19:24:42'),(6922,6,4,'Psychomotor','Jumps on two feet and hops on one foot',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6923,6,4,'Psychomotor','Jumps on two feet and hops on one foot',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6924,6,4,'Psychomotor','Jumps on two feet and hops on one foot',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6925,6,4,'Psychomotor','Participates in physical activities with peers',1,'AP',NULL,'manual','2026-09-19 19:24:42'),(6926,6,4,'Psychomotor','Participates in physical activities with peers',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6927,6,4,'Psychomotor','Participates in physical activities with peers',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6928,6,4,'Psychomotor','Participates in physical activities with peers',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6929,6,4,'Psychomotor','Cuts along a straight and curved line with scissors',1,'P',NULL,'manual','2026-09-19 19:24:42'),(6930,6,4,'Psychomotor','Cuts along a straight and curved line with scissors',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6931,6,4,'Psychomotor','Cuts along a straight and curved line with scissors',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6932,6,4,'Psychomotor','Cuts along a straight and curved line with scissors',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6933,6,4,'Psychomotor','Strings beads and manipulates small objects',1,'D',NULL,'manual','2026-09-19 19:24:42'),(6934,6,4,'Psychomotor','Strings beads and manipulates small objects',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6935,6,4,'Psychomotor','Strings beads and manipulates small objects',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6936,6,4,'Psychomotor','Strings beads and manipulates small objects',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6937,6,4,'Psychomotor','Completes puzzles with multiple pieces',1,'D',NULL,'manual','2026-09-19 19:24:42'),(6938,6,4,'Psychomotor','Completes puzzles with multiple pieces',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6939,6,4,'Psychomotor','Completes puzzles with multiple pieces',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6940,6,4,'Psychomotor','Completes puzzles with multiple pieces',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6941,6,4,'Psychomotor','Colors within boundaries',1,'AP',NULL,'manual','2026-09-19 19:24:42'),(6942,6,4,'Psychomotor','Colors within boundaries',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6943,6,4,'Psychomotor','Colors within boundaries',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6944,6,4,'Psychomotor','Colors within boundaries',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6945,6,4,'Psychomotor','Copies simple geometric shapes',1,'AP',NULL,'manual','2026-09-19 19:24:42'),(6946,6,4,'Psychomotor','Copies simple geometric shapes',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6947,6,4,'Psychomotor','Copies simple geometric shapes',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6948,6,4,'Psychomotor','Copies simple geometric shapes',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6949,6,4,'Psychomotor','Demonstrates eye-hand coordination in tasks',1,'P',NULL,'manual','2026-09-19 19:24:42'),(6950,6,4,'Psychomotor','Demonstrates eye-hand coordination in tasks',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6951,6,4,'Psychomotor','Demonstrates eye-hand coordination in tasks',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6952,6,4,'Psychomotor','Demonstrates eye-hand coordination in tasks',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6953,6,4,'Psychomotor','Identifies left and right body sides',1,'D',NULL,'manual','2026-09-19 19:24:42'),(6954,6,4,'Psychomotor','Identifies left and right body sides',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6955,6,4,'Psychomotor','Identifies left and right body sides',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6956,6,4,'Psychomotor','Identifies left and right body sides',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6957,6,4,'Psychomotor','Tracks moving objects with eyes',1,'D',NULL,'manual','2026-09-19 19:24:42'),(6958,6,4,'Psychomotor','Tracks moving objects with eyes',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6959,6,4,'Psychomotor','Tracks moving objects with eyes',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6960,6,4,'Psychomotor','Tracks moving objects with eyes',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6961,6,4,'Cognitive','Identifies and matches colors',1,'AP',NULL,'manual','2026-09-19 19:24:42'),(6962,6,4,'Cognitive','Identifies and matches colors',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6963,6,4,'Cognitive','Identifies and matches colors',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6964,6,4,'Cognitive','Identifies and matches colors',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6965,6,4,'Cognitive','Identifies and matches basic shapes',1,'AP',NULL,'manual','2026-09-19 19:24:42'),(6966,6,4,'Cognitive','Identifies and matches basic shapes',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6967,6,4,'Cognitive','Identifies and matches basic shapes',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6968,6,4,'Cognitive','Identifies and matches basic shapes',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6969,6,4,'Cognitive','Sorts and classifies objects by attributes',1,'P',NULL,'manual','2026-09-19 19:24:42'),(6970,6,4,'Cognitive','Sorts and classifies objects by attributes',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6971,6,4,'Cognitive','Sorts and classifies objects by attributes',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6972,6,4,'Cognitive','Sorts and classifies objects by attributes',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6973,6,4,'Cognitive','Counts objects up to 10',1,'D',NULL,'manual','2026-09-19 19:24:42'),(6974,6,4,'Cognitive','Counts objects up to 10',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6975,6,4,'Cognitive','Counts objects up to 10',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6976,6,4,'Cognitive','Counts objects up to 10',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6977,6,4,'Cognitive','Identifies numbers 1–10',1,'D',NULL,'manual','2026-09-19 19:24:42'),(6978,6,4,'Cognitive','Identifies numbers 1–10',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6979,6,4,'Cognitive','Identifies numbers 1–10',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6980,6,4,'Cognitive','Identifies numbers 1–10',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6981,6,4,'Cognitive','Understands concepts of more/less, big/small',1,'AP',NULL,'manual','2026-09-19 19:24:42'),(6982,6,4,'Cognitive','Understands concepts of more/less, big/small',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6983,6,4,'Cognitive','Understands concepts of more/less, big/small',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6984,6,4,'Cognitive','Understands concepts of more/less, big/small',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6985,6,4,'Cognitive','Completes simple cause-and-effect tasks',1,'AP',NULL,'manual','2026-09-19 19:24:42'),(6986,6,4,'Cognitive','Completes simple cause-and-effect tasks',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6987,6,4,'Cognitive','Completes simple cause-and-effect tasks',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6988,6,4,'Cognitive','Completes simple cause-and-effect tasks',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6989,6,4,'Cognitive','Demonstrates problem-solving in daily tasks',1,'P',NULL,'manual','2026-09-19 19:24:42'),(6990,6,4,'Cognitive','Demonstrates problem-solving in daily tasks',2,NULL,NULL,'manual','2026-09-19 19:24:42'),(6991,6,4,'Cognitive','Demonstrates problem-solving in daily tasks',3,NULL,NULL,'manual','2026-09-19 19:24:42'),(6992,6,4,'Cognitive','Demonstrates problem-solving in daily tasks',4,NULL,NULL,'manual','2026-09-19 19:24:42'),(6993,6,4,'Cognitive','Identifies body parts correctly',1,'D',NULL,'manual','2026-09-19 19:24:43'),(6994,6,4,'Cognitive','Identifies body parts correctly',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(6995,6,4,'Cognitive','Identifies body parts correctly',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(6996,6,4,'Cognitive','Identifies body parts correctly',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(6997,6,4,'Cognitive','Understands time concepts (before/after, today/tomorrow)',1,'D',NULL,'manual','2026-09-19 19:24:43'),(6998,6,4,'Cognitive','Understands time concepts (before/after, today/tomorrow)',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(6999,6,4,'Cognitive','Understands time concepts (before/after, today/tomorrow)',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7000,6,4,'Cognitive','Understands time concepts (before/after, today/tomorrow)',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7001,6,4,'Aesthetic/Creative','Participates in music and rhythm activities',1,'AP',NULL,'manual','2026-09-19 19:24:43'),(7002,6,4,'Aesthetic/Creative','Participates in music and rhythm activities',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7003,6,4,'Aesthetic/Creative','Participates in music and rhythm activities',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7004,6,4,'Aesthetic/Creative','Participates in music and rhythm activities',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7005,6,4,'Aesthetic/Creative','Expresses appreciation for music and sound',1,'AP',NULL,'manual','2026-09-19 19:24:43'),(7006,6,4,'Aesthetic/Creative','Expresses appreciation for music and sound',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7007,6,4,'Aesthetic/Creative','Expresses appreciation for music and sound',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7008,6,4,'Aesthetic/Creative','Expresses appreciation for music and sound',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7009,6,4,'Aesthetic/Creative','Moves body in response to music/rhythm',1,'P',NULL,'manual','2026-09-19 19:24:43'),(7010,6,4,'Aesthetic/Creative','Moves body in response to music/rhythm',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7011,6,4,'Aesthetic/Creative','Moves body in response to music/rhythm',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7012,6,4,'Aesthetic/Creative','Moves body in response to music/rhythm',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7013,6,4,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',1,'D',NULL,'manual','2026-09-19 19:24:43'),(7014,6,4,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7015,6,4,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7016,6,4,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7017,6,4,'Aesthetic/Creative','Expresses creativity through play',1,'D',NULL,'manual','2026-09-19 19:24:43'),(7018,6,4,'Aesthetic/Creative','Expresses creativity through play',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7019,6,4,'Aesthetic/Creative','Expresses creativity through play',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7020,6,4,'Aesthetic/Creative','Expresses creativity through play',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7021,6,4,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',1,'AP',NULL,'manual','2026-09-19 19:24:43'),(7022,6,4,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7023,6,4,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7024,6,4,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7025,6,4,'Aesthetic/Creative','Watches and enjoys dramatic play/media',1,'AP',NULL,'manual','2026-09-19 19:24:43'),(7026,6,4,'Aesthetic/Creative','Watches and enjoys dramatic play/media',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7027,6,4,'Aesthetic/Creative','Watches and enjoys dramatic play/media',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7028,6,4,'Aesthetic/Creative','Watches and enjoys dramatic play/media',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7029,6,4,'Aesthetic/Creative','Communicates feelings through facial expressions',1,'P',NULL,'manual','2026-09-19 19:24:43'),(7030,6,4,'Aesthetic/Creative','Communicates feelings through facial expressions',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7031,6,4,'Aesthetic/Creative','Communicates feelings through facial expressions',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7032,6,4,'Aesthetic/Creative','Communicates feelings through facial expressions',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7033,6,4,'Behavioral Development','Uses appropriate verbal communication for social interaction',1,'D',NULL,'manual','2026-09-19 19:24:43'),(7034,6,4,'Behavioral Development','Uses appropriate verbal communication for social interaction',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7035,6,4,'Behavioral Development','Uses appropriate verbal communication for social interaction',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7036,6,4,'Behavioral Development','Uses appropriate verbal communication for social interaction',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7037,6,4,'Behavioral Development','Learns how to speak in a lower tone',1,'D',NULL,'manual','2026-09-19 19:24:43'),(7038,6,4,'Behavioral Development','Learns how to speak in a lower tone',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7039,6,4,'Behavioral Development','Learns how to speak in a lower tone',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7040,6,4,'Behavioral Development','Learns how to speak in a lower tone',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7041,6,4,'Behavioral Development','Familiarizes with and takes relocated direction',1,'AP',NULL,'manual','2026-09-19 19:24:43'),(7042,6,4,'Behavioral Development','Familiarizes with and takes relocated direction',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7043,6,4,'Behavioral Development','Familiarizes with and takes relocated direction',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7044,6,4,'Behavioral Development','Familiarizes with and takes relocated direction',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7045,6,4,'Behavioral Development','Follows classroom/court instructions',1,'AP',NULL,'manual','2026-09-19 19:24:43'),(7046,6,4,'Behavioral Development','Follows classroom/court instructions',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7047,6,4,'Behavioral Development','Follows classroom/court instructions',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7048,6,4,'Behavioral Development','Follows classroom/court instructions',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7049,6,4,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',1,'P',NULL,'manual','2026-09-19 19:24:43'),(7050,6,4,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7051,6,4,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7052,6,4,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7053,6,4,'Behavioral Development','Puts body materials and used items in proper place',1,'D',NULL,'manual','2026-09-19 19:24:43'),(7054,6,4,'Behavioral Development','Puts body materials and used items in proper place',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7055,6,4,'Behavioral Development','Puts body materials and used items in proper place',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7056,6,4,'Behavioral Development','Puts body materials and used items in proper place',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7057,6,4,'Behavioral Development','Follows teacher\'s commands/inspection',1,'D',NULL,'manual','2026-09-19 19:24:43'),(7058,6,4,'Behavioral Development','Follows teacher\'s commands/inspection',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7059,6,4,'Behavioral Development','Follows teacher\'s commands/inspection',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7060,6,4,'Behavioral Development','Follows teacher\'s commands/inspection',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7061,6,4,'Behavioral Development','Participates well in the lesson executed by the teacher',1,'AP',NULL,'manual','2026-09-19 19:24:43'),(7062,6,4,'Behavioral Development','Participates well in the lesson executed by the teacher',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7063,6,4,'Behavioral Development','Participates well in the lesson executed by the teacher',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7064,6,4,'Behavioral Development','Participates well in the lesson executed by the teacher',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7065,6,4,'Behavioral Development','Responds to questions and activities given to him/her',1,'AP',NULL,'manual','2026-09-19 19:24:43'),(7066,6,4,'Behavioral Development','Responds to questions and activities given to him/her',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7067,6,4,'Behavioral Development','Responds to questions and activities given to him/her',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7068,6,4,'Behavioral Development','Responds to questions and activities given to him/her',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7069,6,4,'Behavioral Development','Attends to task without getting out from the chair',1,'P',NULL,'manual','2026-09-19 19:24:43'),(7070,6,4,'Behavioral Development','Attends to task without getting out from the chair',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7071,6,4,'Behavioral Development','Attends to task without getting out from the chair',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7072,6,4,'Behavioral Development','Attends to task without getting out from the chair',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7073,6,4,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',1,'D',NULL,'manual','2026-09-19 19:24:43'),(7074,6,4,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7075,6,4,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7076,6,4,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7077,6,4,'Behavioral Development','Responds positively to behavior management procedures',1,'D',NULL,'manual','2026-09-19 19:24:43'),(7078,6,4,'Behavioral Development','Responds positively to behavior management procedures',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7079,6,4,'Behavioral Development','Responds positively to behavior management procedures',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7080,6,4,'Behavioral Development','Responds positively to behavior management procedures',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7081,6,4,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',1,'AP',NULL,'manual','2026-09-19 19:24:43'),(7082,6,4,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7083,6,4,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7084,6,4,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7085,6,4,'Behavioral Development','Reduces separation anxiety during the session',1,'AP',NULL,'manual','2026-09-19 19:24:43'),(7086,6,4,'Behavioral Development','Reduces separation anxiety during the session',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7087,6,4,'Behavioral Development','Reduces separation anxiety during the session',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7088,6,4,'Behavioral Development','Reduces separation anxiety during the session',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7089,6,4,'Behavioral Development','Plays with other children',1,'P',NULL,'manual','2026-09-19 19:24:43'),(7090,6,4,'Behavioral Development','Plays with other children',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7091,6,4,'Behavioral Development','Plays with other children',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7092,6,4,'Behavioral Development','Plays with other children',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7093,6,4,'Behavioral Development','Takes turn in game activities',1,'D',NULL,'manual','2026-09-19 19:24:43'),(7094,6,4,'Behavioral Development','Takes turn in game activities',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7095,6,4,'Behavioral Development','Takes turn in game activities',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7096,6,4,'Behavioral Development','Takes turn in game activities',4,NULL,NULL,'manual','2026-09-19 19:24:43'),(7097,6,4,'Behavioral Development','Knows how to wait when playing games',1,'D',NULL,'manual','2026-09-19 19:24:43'),(7098,6,4,'Behavioral Development','Knows how to wait when playing games',2,NULL,NULL,'manual','2026-09-19 19:24:43'),(7099,6,4,'Behavioral Development','Knows how to wait when playing games',3,NULL,NULL,'manual','2026-09-19 19:24:43'),(7100,6,4,'Behavioral Development','Knows how to wait when playing games',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7101,6,4,'Behavioral Development','Shares things/food without teacher prompt',1,'AP',NULL,'manual','2026-09-19 19:24:44'),(7102,6,4,'Behavioral Development','Shares things/food without teacher prompt',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7103,6,4,'Behavioral Development','Shares things/food without teacher prompt',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7104,6,4,'Behavioral Development','Shares things/food without teacher prompt',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7105,6,4,'Behavioral Development','Sits for 30 minutes to one hour',1,'AP',NULL,'manual','2026-09-19 19:24:44'),(7106,6,4,'Behavioral Development','Sits for 30 minutes to one hour',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7107,6,4,'Behavioral Development','Sits for 30 minutes to one hour',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7108,6,4,'Behavioral Development','Sits for 30 minutes to one hour',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7109,6,4,'Behavioral Development','Develops longer attention span to complete the task',1,'P',NULL,'manual','2026-09-19 19:24:44'),(7110,6,4,'Behavioral Development','Develops longer attention span to complete the task',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7111,6,4,'Behavioral Development','Develops longer attention span to complete the task',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7112,6,4,'Behavioral Development','Develops longer attention span to complete the task',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7113,6,4,'Behavioral Development','Completes task on hand',1,'D',NULL,'manual','2026-09-19 19:24:44'),(7114,6,4,'Behavioral Development','Completes task on hand',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7115,6,4,'Behavioral Development','Completes task on hand',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7116,6,4,'Behavioral Development','Completes task on hand',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7117,6,4,'Orientation and Mobility','Tells the difference between places (from/to)',1,'D',NULL,'manual','2026-09-19 19:24:44'),(7118,6,4,'Orientation and Mobility','Tells the difference between places (from/to)',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7119,6,4,'Orientation and Mobility','Tells the difference between places (from/to)',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7120,6,4,'Orientation and Mobility','Tells the difference between places (from/to)',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7121,6,4,'Orientation and Mobility','Positions body parts on the right/left sides',1,'AP',NULL,'manual','2026-09-19 19:24:44'),(7122,6,4,'Orientation and Mobility','Positions body parts on the right/left sides',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7123,6,4,'Orientation and Mobility','Positions body parts on the right/left sides',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7124,6,4,'Orientation and Mobility','Positions body parts on the right/left sides',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7125,6,4,'Orientation and Mobility','Tells the spatial relations of objects between tables',1,'AP',NULL,'manual','2026-09-19 19:24:44'),(7126,6,4,'Orientation and Mobility','Tells the spatial relations of objects between tables',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7127,6,4,'Orientation and Mobility','Tells the spatial relations of objects between tables',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7128,6,4,'Orientation and Mobility','Tells the spatial relations of objects between tables',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7129,6,4,'Orientation and Mobility','Follows directions given to find objects',1,'P',NULL,'manual','2026-09-19 19:24:44'),(7130,6,4,'Orientation and Mobility','Follows directions given to find objects',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7131,6,4,'Orientation and Mobility','Follows directions given to find objects',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7132,6,4,'Orientation and Mobility','Follows directions given to find objects',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7133,6,4,'Orientation and Mobility','Uses position of classroom objects as reference to self',1,'D',NULL,'manual','2026-09-19 19:24:44'),(7134,6,4,'Orientation and Mobility','Uses position of classroom objects as reference to self',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7135,6,4,'Orientation and Mobility','Uses position of classroom objects as reference to self',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7136,6,4,'Orientation and Mobility','Uses position of classroom objects as reference to self',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7137,6,4,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',1,'D',NULL,'manual','2026-09-19 19:24:44'),(7138,6,4,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7139,6,4,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7140,6,4,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7141,6,4,'Orientation and Mobility','Shows the body with balance and rhythm',1,'AP',NULL,'manual','2026-09-19 19:24:44'),(7142,6,4,'Orientation and Mobility','Shows the body with balance and rhythm',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7143,6,4,'Orientation and Mobility','Shows the body with balance and rhythm',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7144,6,4,'Orientation and Mobility','Shows the body with balance and rhythm',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7145,6,4,'Orientation and Mobility','Identifies landmarks as clues',1,'AP',NULL,'manual','2026-09-19 19:24:44'),(7146,6,4,'Orientation and Mobility','Identifies landmarks as clues',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7147,6,4,'Orientation and Mobility','Identifies landmarks as clues',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7148,6,4,'Orientation and Mobility','Identifies landmarks as clues',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7149,6,4,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',1,'P',NULL,'manual','2026-09-19 19:24:44'),(7150,6,4,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7151,6,4,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7152,6,4,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7153,6,4,'Orientation and Mobility','Uses parallel walk as guide',1,'D',NULL,'manual','2026-09-19 19:24:44'),(7154,6,4,'Orientation and Mobility','Uses parallel walk as guide',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7155,6,4,'Orientation and Mobility','Uses parallel walk as guide',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7156,6,4,'Orientation and Mobility','Uses parallel walk as guide',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7157,6,4,'Orientation and Mobility','Works independently',1,'D',NULL,'manual','2026-09-19 19:24:44'),(7158,6,4,'Orientation and Mobility','Works independently',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7159,6,4,'Orientation and Mobility','Works independently',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7160,6,4,'Orientation and Mobility','Works independently',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7161,6,4,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',1,'AP',NULL,'manual','2026-09-19 19:24:44'),(7162,6,4,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7163,6,4,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7164,6,4,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7165,6,4,'Orientation and Mobility','Expresses appreciation for the dance that they learned',1,'AP',NULL,'manual','2026-09-19 19:24:44'),(7166,6,4,'Orientation and Mobility','Expresses appreciation for the dance that they learned',2,NULL,NULL,'manual','2026-09-19 19:24:44'),(7167,6,4,'Orientation and Mobility','Expresses appreciation for the dance that they learned',3,NULL,NULL,'manual','2026-09-19 19:24:44'),(7168,6,4,'Orientation and Mobility','Expresses appreciation for the dance that they learned',4,NULL,NULL,'manual','2026-09-19 19:24:44'),(7601,7,5,'Daily Living Skills','Holds and uses spoon/fork correctly',1,'AP',NULL,'manual','2026-09-19 19:24:48'),(7602,7,5,'Daily Living Skills','Holds and uses spoon/fork correctly',2,NULL,NULL,'manual','2026-09-19 19:24:48'),(7603,7,5,'Daily Living Skills','Holds and uses spoon/fork correctly',3,NULL,NULL,'manual','2026-09-19 19:24:48'),(7604,7,5,'Daily Living Skills','Holds and uses spoon/fork correctly',4,NULL,NULL,'manual','2026-09-19 19:24:48'),(7605,7,5,'Daily Living Skills','Drinks from a glass without spilling',1,'AP',NULL,'manual','2026-09-19 19:24:48'),(7606,7,5,'Daily Living Skills','Drinks from a glass without spilling',2,NULL,NULL,'manual','2026-09-19 19:24:48'),(7607,7,5,'Daily Living Skills','Drinks from a glass without spilling',3,NULL,NULL,'manual','2026-09-19 19:24:48'),(7608,7,5,'Daily Living Skills','Drinks from a glass without spilling',4,NULL,NULL,'manual','2026-09-19 19:24:48'),(7609,7,5,'Daily Living Skills','Eats using hands with minimal mess',1,'AP',NULL,'manual','2026-09-19 19:24:48'),(7610,7,5,'Daily Living Skills','Eats using hands with minimal mess',2,NULL,NULL,'manual','2026-09-19 19:24:48'),(7611,7,5,'Daily Living Skills','Eats using hands with minimal mess',3,NULL,NULL,'manual','2026-09-19 19:24:48'),(7612,7,5,'Daily Living Skills','Eats using hands with minimal mess',4,NULL,NULL,'manual','2026-09-19 19:24:48'),(7613,7,5,'Daily Living Skills','Opens food containers independently',1,'P',NULL,'manual','2026-09-19 19:24:48'),(7614,7,5,'Daily Living Skills','Opens food containers independently',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7615,7,5,'Daily Living Skills','Opens food containers independently',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7616,7,5,'Daily Living Skills','Opens food containers independently',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7617,7,5,'Daily Living Skills','Indicates need to use the toilet',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7618,7,5,'Daily Living Skills','Indicates need to use the toilet',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7619,7,5,'Daily Living Skills','Indicates need to use the toilet',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7620,7,5,'Daily Living Skills','Indicates need to use the toilet',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7621,7,5,'Daily Living Skills','Goes to the toilet independently',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7622,7,5,'Daily Living Skills','Goes to the toilet independently',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7623,7,5,'Daily Living Skills','Goes to the toilet independently',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7624,7,5,'Daily Living Skills','Goes to the toilet independently',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7625,7,5,'Daily Living Skills','Flushes the toilet after use',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7626,7,5,'Daily Living Skills','Flushes the toilet after use',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7627,7,5,'Daily Living Skills','Flushes the toilet after use',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7628,7,5,'Daily Living Skills','Flushes the toilet after use',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7629,7,5,'Daily Living Skills','Washes hands after toileting',1,'P',NULL,'manual','2026-09-19 19:24:49'),(7630,7,5,'Daily Living Skills','Washes hands after toileting',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7631,7,5,'Daily Living Skills','Washes hands after toileting',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7632,7,5,'Daily Living Skills','Washes hands after toileting',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7633,7,5,'Daily Living Skills','Puts on and removes clothing independently',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7634,7,5,'Daily Living Skills','Puts on and removes clothing independently',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7635,7,5,'Daily Living Skills','Puts on and removes clothing independently',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7636,7,5,'Daily Living Skills','Puts on and removes clothing independently',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7637,7,5,'Daily Living Skills','Buttons and unbuttons shirt',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7638,7,5,'Daily Living Skills','Buttons and unbuttons shirt',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7639,7,5,'Daily Living Skills','Buttons and unbuttons shirt',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7640,7,5,'Daily Living Skills','Buttons and unbuttons shirt',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7641,7,5,'Daily Living Skills','Zips and unzips clothing',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7642,7,5,'Daily Living Skills','Zips and unzips clothing',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7643,7,5,'Daily Living Skills','Zips and unzips clothing',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7644,7,5,'Daily Living Skills','Zips and unzips clothing',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7645,7,5,'Daily Living Skills','Wears shoes and socks correctly',1,'P',NULL,'manual','2026-09-19 19:24:49'),(7646,7,5,'Daily Living Skills','Wears shoes and socks correctly',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7647,7,5,'Daily Living Skills','Wears shoes and socks correctly',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7648,7,5,'Daily Living Skills','Wears shoes and socks correctly',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7649,7,5,'Daily Living Skills','Brushes teeth with minimal supervision',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7650,7,5,'Daily Living Skills','Brushes teeth with minimal supervision',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7651,7,5,'Daily Living Skills','Brushes teeth with minimal supervision',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7652,7,5,'Daily Living Skills','Brushes teeth with minimal supervision',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7653,7,5,'Daily Living Skills','Washes and dries hands properly',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7654,7,5,'Daily Living Skills','Washes and dries hands properly',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7655,7,5,'Daily Living Skills','Washes and dries hands properly',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7656,7,5,'Daily Living Skills','Washes and dries hands properly',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7657,7,5,'Daily Living Skills','Combs/brushes hair independently',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7658,7,5,'Daily Living Skills','Combs/brushes hair independently',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7659,7,5,'Daily Living Skills','Combs/brushes hair independently',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7660,7,5,'Daily Living Skills','Combs/brushes hair independently',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7661,7,5,'Daily Living Skills','Maintains personal cleanliness',1,'P',NULL,'manual','2026-09-19 19:24:49'),(7662,7,5,'Daily Living Skills','Maintains personal cleanliness',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7663,7,5,'Daily Living Skills','Maintains personal cleanliness',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7664,7,5,'Daily Living Skills','Maintains personal cleanliness',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7665,7,5,'Socio-Emotional','Shows awareness of own feelings and emotions',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7666,7,5,'Socio-Emotional','Shows awareness of own feelings and emotions',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7667,7,5,'Socio-Emotional','Shows awareness of own feelings and emotions',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7668,7,5,'Socio-Emotional','Shows awareness of own feelings and emotions',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7669,7,5,'Socio-Emotional','Interacts appropriately with peers and adults',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7670,7,5,'Socio-Emotional','Interacts appropriately with peers and adults',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7671,7,5,'Socio-Emotional','Interacts appropriately with peers and adults',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7672,7,5,'Socio-Emotional','Interacts appropriately with peers and adults',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7673,7,5,'Socio-Emotional','Takes turns and shares materials',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7674,7,5,'Socio-Emotional','Takes turns and shares materials',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7675,7,5,'Socio-Emotional','Takes turns and shares materials',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7676,7,5,'Socio-Emotional','Takes turns and shares materials',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7677,7,5,'Socio-Emotional','Expresses needs and wants appropriately',1,'P',NULL,'manual','2026-09-19 19:24:49'),(7678,7,5,'Socio-Emotional','Expresses needs and wants appropriately',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7679,7,5,'Socio-Emotional','Expresses needs and wants appropriately',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7680,7,5,'Socio-Emotional','Expresses needs and wants appropriately',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7681,7,5,'Socio-Emotional','Follows classroom rules and routines',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7682,7,5,'Socio-Emotional','Follows classroom rules and routines',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7683,7,5,'Socio-Emotional','Follows classroom rules and routines',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7684,7,5,'Socio-Emotional','Follows classroom rules and routines',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7685,7,5,'Socio-Emotional','Shows appropriate emotional responses',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7686,7,5,'Socio-Emotional','Shows appropriate emotional responses',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7687,7,5,'Socio-Emotional','Shows appropriate emotional responses',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7688,7,5,'Socio-Emotional','Shows appropriate emotional responses',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7689,7,5,'Socio-Emotional','Develops and maintains peer relationships',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7690,7,5,'Socio-Emotional','Develops and maintains peer relationships',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7691,7,5,'Socio-Emotional','Develops and maintains peer relationships',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7692,7,5,'Socio-Emotional','Develops and maintains peer relationships',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7693,7,5,'Socio-Emotional','Demonstrates self-control in social situations',1,'P',NULL,'manual','2026-09-19 19:24:49'),(7694,7,5,'Socio-Emotional','Demonstrates self-control in social situations',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7695,7,5,'Socio-Emotional','Demonstrates self-control in social situations',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7696,7,5,'Socio-Emotional','Demonstrates self-control in social situations',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7697,7,5,'Socio-Emotional','Shows empathy toward others',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7698,7,5,'Socio-Emotional','Shows empathy toward others',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7699,7,5,'Socio-Emotional','Shows empathy toward others',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7700,7,5,'Socio-Emotional','Shows empathy toward others',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7701,7,5,'Socio-Emotional','Seeks help from adults when needed',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7702,7,5,'Socio-Emotional','Seeks help from adults when needed',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7703,7,5,'Socio-Emotional','Seeks help from adults when needed',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7704,7,5,'Socio-Emotional','Seeks help from adults when needed',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7705,7,5,'Language Development','Listens attentively during instruction',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7706,7,5,'Language Development','Listens attentively during instruction',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7707,7,5,'Language Development','Listens attentively during instruction',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7708,7,5,'Language Development','Listens attentively during instruction',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7709,7,5,'Language Development','Follows one-step verbal directions',1,'P',NULL,'manual','2026-09-19 19:24:49'),(7710,7,5,'Language Development','Follows one-step verbal directions',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7711,7,5,'Language Development','Follows one-step verbal directions',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7712,7,5,'Language Development','Follows one-step verbal directions',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7713,7,5,'Language Development','Follows two-step verbal directions',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7714,7,5,'Language Development','Follows two-step verbal directions',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7715,7,5,'Language Development','Follows two-step verbal directions',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7716,7,5,'Language Development','Follows two-step verbal directions',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7717,7,5,'Language Development','Identifies objects/pictures when named',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7718,7,5,'Language Development','Identifies objects/pictures when named',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7719,7,5,'Language Development','Identifies objects/pictures when named',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7720,7,5,'Language Development','Identifies objects/pictures when named',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7721,7,5,'Language Development','Communicates basic needs verbally or through AAC',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7722,7,5,'Language Development','Communicates basic needs verbally or through AAC',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7723,7,5,'Language Development','Communicates basic needs verbally or through AAC',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7724,7,5,'Language Development','Communicates basic needs verbally or through AAC',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7725,7,5,'Language Development','Uses simple sentences to express ideas',1,'P',NULL,'manual','2026-09-19 19:24:49'),(7726,7,5,'Language Development','Uses simple sentences to express ideas',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7727,7,5,'Language Development','Uses simple sentences to express ideas',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7728,7,5,'Language Development','Uses simple sentences to express ideas',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7729,7,5,'Language Development','Participates in simple conversations',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7730,7,5,'Language Development','Participates in simple conversations',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7731,7,5,'Language Development','Participates in simple conversations',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7732,7,5,'Language Development','Participates in simple conversations',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7733,7,5,'Language Development','Answers simple questions appropriately',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7734,7,5,'Language Development','Answers simple questions appropriately',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7735,7,5,'Language Development','Answers simple questions appropriately',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7736,7,5,'Language Development','Answers simple questions appropriately',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7737,7,5,'Language Development','Recognizes own name in print',1,'AP',NULL,'manual','2026-09-19 19:24:49'),(7738,7,5,'Language Development','Recognizes own name in print',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7739,7,5,'Language Development','Recognizes own name in print',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7740,7,5,'Language Development','Recognizes own name in print',4,NULL,NULL,'manual','2026-09-19 19:24:49'),(7741,7,5,'Language Development','Identifies letters of the alphabet',1,'P',NULL,'manual','2026-09-19 19:24:49'),(7742,7,5,'Language Development','Identifies letters of the alphabet',2,NULL,NULL,'manual','2026-09-19 19:24:49'),(7743,7,5,'Language Development','Identifies letters of the alphabet',3,NULL,NULL,'manual','2026-09-19 19:24:49'),(7744,7,5,'Language Development','Identifies letters of the alphabet',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7745,7,5,'Language Development','Matches letters to sounds (phonics)',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7746,7,5,'Language Development','Matches letters to sounds (phonics)',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7747,7,5,'Language Development','Matches letters to sounds (phonics)',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7748,7,5,'Language Development','Matches letters to sounds (phonics)',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7749,7,5,'Language Development','Reads simple words and short phrases',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7750,7,5,'Language Development','Reads simple words and short phrases',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7751,7,5,'Language Development','Reads simple words and short phrases',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7752,7,5,'Language Development','Reads simple words and short phrases',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7753,7,5,'Language Development','Holds pencil/crayon with correct grip',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7754,7,5,'Language Development','Holds pencil/crayon with correct grip',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7755,7,5,'Language Development','Holds pencil/crayon with correct grip',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7756,7,5,'Language Development','Holds pencil/crayon with correct grip',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7757,7,5,'Language Development','Copies simple shapes and lines',1,'P',NULL,'manual','2026-09-19 19:24:50'),(7758,7,5,'Language Development','Copies simple shapes and lines',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7759,7,5,'Language Development','Copies simple shapes and lines',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7760,7,5,'Language Development','Copies simple shapes and lines',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7761,7,5,'Language Development','Writes own name legibly',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7762,7,5,'Language Development','Writes own name legibly',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7763,7,5,'Language Development','Writes own name legibly',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7764,7,5,'Language Development','Writes own name legibly',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7765,7,5,'Language Development','Writes simple words from dictation',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7766,7,5,'Language Development','Writes simple words from dictation',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7767,7,5,'Language Development','Writes simple words from dictation',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7768,7,5,'Language Development','Writes simple words from dictation',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7769,7,5,'Psychomotor','Walks with balance and coordination',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7770,7,5,'Psychomotor','Walks with balance and coordination',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7771,7,5,'Psychomotor','Walks with balance and coordination',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7772,7,5,'Psychomotor','Walks with balance and coordination',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7773,7,5,'Psychomotor','Runs with control and coordination',1,'P',NULL,'manual','2026-09-19 19:24:50'),(7774,7,5,'Psychomotor','Runs with control and coordination',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7775,7,5,'Psychomotor','Runs with control and coordination',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7776,7,5,'Psychomotor','Runs with control and coordination',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7777,7,5,'Psychomotor','Climbs stairs using alternating feet',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7778,7,5,'Psychomotor','Climbs stairs using alternating feet',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7779,7,5,'Psychomotor','Climbs stairs using alternating feet',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7780,7,5,'Psychomotor','Climbs stairs using alternating feet',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7781,7,5,'Psychomotor','Catches and throws a ball',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7782,7,5,'Psychomotor','Catches and throws a ball',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7783,7,5,'Psychomotor','Catches and throws a ball',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7784,7,5,'Psychomotor','Catches and throws a ball',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7785,7,5,'Psychomotor','Jumps on two feet and hops on one foot',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7786,7,5,'Psychomotor','Jumps on two feet and hops on one foot',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7787,7,5,'Psychomotor','Jumps on two feet and hops on one foot',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7788,7,5,'Psychomotor','Jumps on two feet and hops on one foot',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7789,7,5,'Psychomotor','Participates in physical activities with peers',1,'P',NULL,'manual','2026-09-19 19:24:50'),(7790,7,5,'Psychomotor','Participates in physical activities with peers',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7791,7,5,'Psychomotor','Participates in physical activities with peers',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7792,7,5,'Psychomotor','Participates in physical activities with peers',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7793,7,5,'Psychomotor','Cuts along a straight and curved line with scissors',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7794,7,5,'Psychomotor','Cuts along a straight and curved line with scissors',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7795,7,5,'Psychomotor','Cuts along a straight and curved line with scissors',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7796,7,5,'Psychomotor','Cuts along a straight and curved line with scissors',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7797,7,5,'Psychomotor','Strings beads and manipulates small objects',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7798,7,5,'Psychomotor','Strings beads and manipulates small objects',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7799,7,5,'Psychomotor','Strings beads and manipulates small objects',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7800,7,5,'Psychomotor','Strings beads and manipulates small objects',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7801,7,5,'Psychomotor','Completes puzzles with multiple pieces',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7802,7,5,'Psychomotor','Completes puzzles with multiple pieces',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7803,7,5,'Psychomotor','Completes puzzles with multiple pieces',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7804,7,5,'Psychomotor','Completes puzzles with multiple pieces',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7805,7,5,'Psychomotor','Colors within boundaries',1,'P',NULL,'manual','2026-09-19 19:24:50'),(7806,7,5,'Psychomotor','Colors within boundaries',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7807,7,5,'Psychomotor','Colors within boundaries',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7808,7,5,'Psychomotor','Colors within boundaries',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7809,7,5,'Psychomotor','Copies simple geometric shapes',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7810,7,5,'Psychomotor','Copies simple geometric shapes',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7811,7,5,'Psychomotor','Copies simple geometric shapes',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7812,7,5,'Psychomotor','Copies simple geometric shapes',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7813,7,5,'Psychomotor','Demonstrates eye-hand coordination in tasks',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7814,7,5,'Psychomotor','Demonstrates eye-hand coordination in tasks',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7815,7,5,'Psychomotor','Demonstrates eye-hand coordination in tasks',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7816,7,5,'Psychomotor','Demonstrates eye-hand coordination in tasks',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7817,7,5,'Psychomotor','Identifies left and right body sides',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7818,7,5,'Psychomotor','Identifies left and right body sides',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7819,7,5,'Psychomotor','Identifies left and right body sides',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7820,7,5,'Psychomotor','Identifies left and right body sides',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7821,7,5,'Psychomotor','Tracks moving objects with eyes',1,'P',NULL,'manual','2026-09-19 19:24:50'),(7822,7,5,'Psychomotor','Tracks moving objects with eyes',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7823,7,5,'Psychomotor','Tracks moving objects with eyes',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7824,7,5,'Psychomotor','Tracks moving objects with eyes',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7825,7,5,'Cognitive','Identifies and matches colors',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7826,7,5,'Cognitive','Identifies and matches colors',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7827,7,5,'Cognitive','Identifies and matches colors',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7828,7,5,'Cognitive','Identifies and matches colors',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7829,7,5,'Cognitive','Identifies and matches basic shapes',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7830,7,5,'Cognitive','Identifies and matches basic shapes',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7831,7,5,'Cognitive','Identifies and matches basic shapes',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7832,7,5,'Cognitive','Identifies and matches basic shapes',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7833,7,5,'Cognitive','Sorts and classifies objects by attributes',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7834,7,5,'Cognitive','Sorts and classifies objects by attributes',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7835,7,5,'Cognitive','Sorts and classifies objects by attributes',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7836,7,5,'Cognitive','Sorts and classifies objects by attributes',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7837,7,5,'Cognitive','Counts objects up to 10',1,'P',NULL,'manual','2026-09-19 19:24:50'),(7838,7,5,'Cognitive','Counts objects up to 10',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7839,7,5,'Cognitive','Counts objects up to 10',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7840,7,5,'Cognitive','Counts objects up to 10',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7841,7,5,'Cognitive','Identifies numbers 1–10',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7842,7,5,'Cognitive','Identifies numbers 1–10',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7843,7,5,'Cognitive','Identifies numbers 1–10',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7844,7,5,'Cognitive','Identifies numbers 1–10',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7845,7,5,'Cognitive','Understands concepts of more/less, big/small',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7846,7,5,'Cognitive','Understands concepts of more/less, big/small',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7847,7,5,'Cognitive','Understands concepts of more/less, big/small',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7848,7,5,'Cognitive','Understands concepts of more/less, big/small',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7849,7,5,'Cognitive','Completes simple cause-and-effect tasks',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7850,7,5,'Cognitive','Completes simple cause-and-effect tasks',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7851,7,5,'Cognitive','Completes simple cause-and-effect tasks',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7852,7,5,'Cognitive','Completes simple cause-and-effect tasks',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7853,7,5,'Cognitive','Demonstrates problem-solving in daily tasks',1,'P',NULL,'manual','2026-09-19 19:24:50'),(7854,7,5,'Cognitive','Demonstrates problem-solving in daily tasks',2,NULL,NULL,'manual','2026-09-19 19:24:50'),(7855,7,5,'Cognitive','Demonstrates problem-solving in daily tasks',3,NULL,NULL,'manual','2026-09-19 19:24:50'),(7856,7,5,'Cognitive','Demonstrates problem-solving in daily tasks',4,NULL,NULL,'manual','2026-09-19 19:24:50'),(7857,7,5,'Cognitive','Identifies body parts correctly',1,'AP',NULL,'manual','2026-09-19 19:24:50'),(7858,7,5,'Cognitive','Identifies body parts correctly',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7859,7,5,'Cognitive','Identifies body parts correctly',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7860,7,5,'Cognitive','Identifies body parts correctly',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7861,7,5,'Cognitive','Understands time concepts (before/after, today/tomorrow)',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7862,7,5,'Cognitive','Understands time concepts (before/after, today/tomorrow)',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7863,7,5,'Cognitive','Understands time concepts (before/after, today/tomorrow)',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7864,7,5,'Cognitive','Understands time concepts (before/after, today/tomorrow)',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7865,7,5,'Aesthetic/Creative','Participates in music and rhythm activities',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7866,7,5,'Aesthetic/Creative','Participates in music and rhythm activities',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7867,7,5,'Aesthetic/Creative','Participates in music and rhythm activities',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7868,7,5,'Aesthetic/Creative','Participates in music and rhythm activities',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7869,7,5,'Aesthetic/Creative','Expresses appreciation for music and sound',1,'P',NULL,'manual','2026-09-19 19:24:51'),(7870,7,5,'Aesthetic/Creative','Expresses appreciation for music and sound',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7871,7,5,'Aesthetic/Creative','Expresses appreciation for music and sound',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7872,7,5,'Aesthetic/Creative','Expresses appreciation for music and sound',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7873,7,5,'Aesthetic/Creative','Moves body in response to music/rhythm',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7874,7,5,'Aesthetic/Creative','Moves body in response to music/rhythm',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7875,7,5,'Aesthetic/Creative','Moves body in response to music/rhythm',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7876,7,5,'Aesthetic/Creative','Moves body in response to music/rhythm',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7877,7,5,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7878,7,5,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7879,7,5,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7880,7,5,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7881,7,5,'Aesthetic/Creative','Expresses creativity through play',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7882,7,5,'Aesthetic/Creative','Expresses creativity through play',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7883,7,5,'Aesthetic/Creative','Expresses creativity through play',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7884,7,5,'Aesthetic/Creative','Expresses creativity through play',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7885,7,5,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',1,'P',NULL,'manual','2026-09-19 19:24:51'),(7886,7,5,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7887,7,5,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7888,7,5,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7889,7,5,'Aesthetic/Creative','Watches and enjoys dramatic play/media',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7890,7,5,'Aesthetic/Creative','Watches and enjoys dramatic play/media',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7891,7,5,'Aesthetic/Creative','Watches and enjoys dramatic play/media',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7892,7,5,'Aesthetic/Creative','Watches and enjoys dramatic play/media',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7893,7,5,'Aesthetic/Creative','Communicates feelings through facial expressions',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7894,7,5,'Aesthetic/Creative','Communicates feelings through facial expressions',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7895,7,5,'Aesthetic/Creative','Communicates feelings through facial expressions',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7896,7,5,'Aesthetic/Creative','Communicates feelings through facial expressions',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7897,7,5,'Behavioral Development','Uses appropriate verbal communication for social interaction',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7898,7,5,'Behavioral Development','Uses appropriate verbal communication for social interaction',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7899,7,5,'Behavioral Development','Uses appropriate verbal communication for social interaction',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7900,7,5,'Behavioral Development','Uses appropriate verbal communication for social interaction',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7901,7,5,'Behavioral Development','Learns how to speak in a lower tone',1,'P',NULL,'manual','2026-09-19 19:24:51'),(7902,7,5,'Behavioral Development','Learns how to speak in a lower tone',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7903,7,5,'Behavioral Development','Learns how to speak in a lower tone',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7904,7,5,'Behavioral Development','Learns how to speak in a lower tone',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7905,7,5,'Behavioral Development','Familiarizes with and takes relocated direction',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7906,7,5,'Behavioral Development','Familiarizes with and takes relocated direction',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7907,7,5,'Behavioral Development','Familiarizes with and takes relocated direction',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7908,7,5,'Behavioral Development','Familiarizes with and takes relocated direction',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7909,7,5,'Behavioral Development','Follows classroom/court instructions',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7910,7,5,'Behavioral Development','Follows classroom/court instructions',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7911,7,5,'Behavioral Development','Follows classroom/court instructions',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7912,7,5,'Behavioral Development','Follows classroom/court instructions',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7913,7,5,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7914,7,5,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7915,7,5,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7916,7,5,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7917,7,5,'Behavioral Development','Puts body materials and used items in proper place',1,'P',NULL,'manual','2026-09-19 19:24:51'),(7918,7,5,'Behavioral Development','Puts body materials and used items in proper place',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7919,7,5,'Behavioral Development','Puts body materials and used items in proper place',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7920,7,5,'Behavioral Development','Puts body materials and used items in proper place',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7921,7,5,'Behavioral Development','Follows teacher\'s commands/inspection',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7922,7,5,'Behavioral Development','Follows teacher\'s commands/inspection',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7923,7,5,'Behavioral Development','Follows teacher\'s commands/inspection',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7924,7,5,'Behavioral Development','Follows teacher\'s commands/inspection',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7925,7,5,'Behavioral Development','Participates well in the lesson executed by the teacher',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7926,7,5,'Behavioral Development','Participates well in the lesson executed by the teacher',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7927,7,5,'Behavioral Development','Participates well in the lesson executed by the teacher',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7928,7,5,'Behavioral Development','Participates well in the lesson executed by the teacher',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7929,7,5,'Behavioral Development','Responds to questions and activities given to him/her',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7930,7,5,'Behavioral Development','Responds to questions and activities given to him/her',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7931,7,5,'Behavioral Development','Responds to questions and activities given to him/her',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7932,7,5,'Behavioral Development','Responds to questions and activities given to him/her',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7933,7,5,'Behavioral Development','Attends to task without getting out from the chair',1,'P',NULL,'manual','2026-09-19 19:24:51'),(7934,7,5,'Behavioral Development','Attends to task without getting out from the chair',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7935,7,5,'Behavioral Development','Attends to task without getting out from the chair',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7936,7,5,'Behavioral Development','Attends to task without getting out from the chair',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7937,7,5,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7938,7,5,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7939,7,5,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7940,7,5,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7941,7,5,'Behavioral Development','Responds positively to behavior management procedures',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7942,7,5,'Behavioral Development','Responds positively to behavior management procedures',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7943,7,5,'Behavioral Development','Responds positively to behavior management procedures',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7944,7,5,'Behavioral Development','Responds positively to behavior management procedures',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7945,7,5,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7946,7,5,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7947,7,5,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7948,7,5,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7949,7,5,'Behavioral Development','Reduces separation anxiety during the session',1,'P',NULL,'manual','2026-09-19 19:24:51'),(7950,7,5,'Behavioral Development','Reduces separation anxiety during the session',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7951,7,5,'Behavioral Development','Reduces separation anxiety during the session',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7952,7,5,'Behavioral Development','Reduces separation anxiety during the session',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7953,7,5,'Behavioral Development','Plays with other children',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7954,7,5,'Behavioral Development','Plays with other children',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7955,7,5,'Behavioral Development','Plays with other children',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7956,7,5,'Behavioral Development','Plays with other children',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7957,7,5,'Behavioral Development','Takes turn in game activities',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7958,7,5,'Behavioral Development','Takes turn in game activities',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7959,7,5,'Behavioral Development','Takes turn in game activities',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7960,7,5,'Behavioral Development','Takes turn in game activities',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7961,7,5,'Behavioral Development','Knows how to wait when playing games',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7962,7,5,'Behavioral Development','Knows how to wait when playing games',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7963,7,5,'Behavioral Development','Knows how to wait when playing games',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7964,7,5,'Behavioral Development','Knows how to wait when playing games',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7965,7,5,'Behavioral Development','Shares things/food without teacher prompt',1,'P',NULL,'manual','2026-09-19 19:24:51'),(7966,7,5,'Behavioral Development','Shares things/food without teacher prompt',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7967,7,5,'Behavioral Development','Shares things/food without teacher prompt',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7968,7,5,'Behavioral Development','Shares things/food without teacher prompt',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7969,7,5,'Behavioral Development','Sits for 30 minutes to one hour',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7970,7,5,'Behavioral Development','Sits for 30 minutes to one hour',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7971,7,5,'Behavioral Development','Sits for 30 minutes to one hour',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7972,7,5,'Behavioral Development','Sits for 30 minutes to one hour',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7973,7,5,'Behavioral Development','Develops longer attention span to complete the task',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7974,7,5,'Behavioral Development','Develops longer attention span to complete the task',2,NULL,NULL,'manual','2026-09-19 19:24:51'),(7975,7,5,'Behavioral Development','Develops longer attention span to complete the task',3,NULL,NULL,'manual','2026-09-19 19:24:51'),(7976,7,5,'Behavioral Development','Develops longer attention span to complete the task',4,NULL,NULL,'manual','2026-09-19 19:24:51'),(7977,7,5,'Behavioral Development','Completes task on hand',1,'AP',NULL,'manual','2026-09-19 19:24:51'),(7978,7,5,'Behavioral Development','Completes task on hand',2,NULL,NULL,'manual','2026-09-19 19:24:52'),(7979,7,5,'Behavioral Development','Completes task on hand',3,NULL,NULL,'manual','2026-09-19 19:24:52'),(7980,7,5,'Behavioral Development','Completes task on hand',4,NULL,NULL,'manual','2026-09-19 19:24:52'),(7981,7,5,'Orientation and Mobility','Tells the difference between places (from/to)',1,'P',NULL,'manual','2026-09-19 19:24:52'),(7982,7,5,'Orientation and Mobility','Tells the difference between places (from/to)',2,NULL,NULL,'manual','2026-09-19 19:24:52'),(7983,7,5,'Orientation and Mobility','Tells the difference between places (from/to)',3,NULL,NULL,'manual','2026-09-19 19:24:52'),(7984,7,5,'Orientation and Mobility','Tells the difference between places (from/to)',4,NULL,NULL,'manual','2026-09-19 19:24:52'),(7985,7,5,'Orientation and Mobility','Positions body parts on the right/left sides',1,'AP',NULL,'manual','2026-09-19 19:24:52'),(7986,7,5,'Orientation and Mobility','Positions body parts on the right/left sides',2,NULL,NULL,'manual','2026-09-19 19:24:52'),(7987,7,5,'Orientation and Mobility','Positions body parts on the right/left sides',3,NULL,NULL,'manual','2026-09-19 19:24:52'),(7988,7,5,'Orientation and Mobility','Positions body parts on the right/left sides',4,NULL,NULL,'manual','2026-09-19 19:24:52'),(7989,7,5,'Orientation and Mobility','Tells the spatial relations of objects between tables',1,'AP',NULL,'manual','2026-09-19 19:24:52'),(7990,7,5,'Orientation and Mobility','Tells the spatial relations of objects between tables',2,NULL,NULL,'manual','2026-09-19 19:24:52'),(7991,7,5,'Orientation and Mobility','Tells the spatial relations of objects between tables',3,NULL,NULL,'manual','2026-09-19 19:24:52'),(7992,7,5,'Orientation and Mobility','Tells the spatial relations of objects between tables',4,NULL,NULL,'manual','2026-09-19 19:24:52'),(7993,7,5,'Orientation and Mobility','Follows directions given to find objects',1,'AP',NULL,'manual','2026-09-19 19:24:52'),(7994,7,5,'Orientation and Mobility','Follows directions given to find objects',2,NULL,NULL,'manual','2026-09-19 19:24:52'),(7995,7,5,'Orientation and Mobility','Follows directions given to find objects',3,NULL,NULL,'manual','2026-09-19 19:24:52'),(7996,7,5,'Orientation and Mobility','Follows directions given to find objects',4,NULL,NULL,'manual','2026-09-19 19:24:52'),(7997,7,5,'Orientation and Mobility','Uses position of classroom objects as reference to self',1,'P',NULL,'manual','2026-09-19 19:24:52'),(7998,7,5,'Orientation and Mobility','Uses position of classroom objects as reference to self',2,NULL,NULL,'manual','2026-09-19 19:24:52'),(7999,7,5,'Orientation and Mobility','Uses position of classroom objects as reference to self',3,NULL,NULL,'manual','2026-09-19 19:24:52'),(8000,7,5,'Orientation and Mobility','Uses position of classroom objects as reference to self',4,NULL,NULL,'manual','2026-09-19 19:24:52'),(8001,7,5,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',1,'AP',NULL,'manual','2026-09-19 19:24:52'),(8002,7,5,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',2,NULL,NULL,'manual','2026-09-19 19:24:52'),(8003,7,5,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',3,NULL,NULL,'manual','2026-09-19 19:24:52'),(8004,7,5,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',4,NULL,NULL,'manual','2026-09-19 19:24:52'),(8005,7,5,'Orientation and Mobility','Shows the body with balance and rhythm',1,'AP',NULL,'manual','2026-09-19 19:24:52'),(8006,7,5,'Orientation and Mobility','Shows the body with balance and rhythm',2,NULL,NULL,'manual','2026-09-19 19:24:52'),(8007,7,5,'Orientation and Mobility','Shows the body with balance and rhythm',3,NULL,NULL,'manual','2026-09-19 19:24:52'),(8008,7,5,'Orientation and Mobility','Shows the body with balance and rhythm',4,NULL,NULL,'manual','2026-09-19 19:24:52'),(8009,7,5,'Orientation and Mobility','Identifies landmarks as clues',1,'AP',NULL,'manual','2026-09-19 19:24:52'),(8010,7,5,'Orientation and Mobility','Identifies landmarks as clues',2,NULL,NULL,'manual','2026-09-19 19:24:52'),(8011,7,5,'Orientation and Mobility','Identifies landmarks as clues',3,NULL,NULL,'manual','2026-09-19 19:24:52'),(8012,7,5,'Orientation and Mobility','Identifies landmarks as clues',4,NULL,NULL,'manual','2026-09-19 19:24:52'),(8013,7,5,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',1,'P',NULL,'manual','2026-09-19 19:24:52'),(8014,7,5,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',2,NULL,NULL,'manual','2026-09-19 19:24:52'),(8015,7,5,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',3,NULL,NULL,'manual','2026-09-19 19:24:52'),(8016,7,5,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',4,NULL,NULL,'manual','2026-09-19 19:24:52'),(8017,7,5,'Orientation and Mobility','Uses parallel walk as guide',1,'AP',NULL,'manual','2026-09-19 19:24:52'),(8018,7,5,'Orientation and Mobility','Uses parallel walk as guide',2,NULL,NULL,'manual','2026-09-19 19:24:52'),(8019,7,5,'Orientation and Mobility','Uses parallel walk as guide',3,NULL,NULL,'manual','2026-09-19 19:24:52'),(8020,7,5,'Orientation and Mobility','Uses parallel walk as guide',4,NULL,NULL,'manual','2026-09-19 19:24:52'),(8021,7,5,'Orientation and Mobility','Works independently',1,'AP',NULL,'manual','2026-09-19 19:24:52'),(8022,7,5,'Orientation and Mobility','Works independently',2,NULL,NULL,'manual','2026-09-19 19:24:52'),(8023,7,5,'Orientation and Mobility','Works independently',3,NULL,NULL,'manual','2026-09-19 19:24:52'),(8024,7,5,'Orientation and Mobility','Works independently',4,NULL,NULL,'manual','2026-09-19 19:24:52'),(8025,7,5,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',1,'AP',NULL,'manual','2026-09-19 19:24:52'),(8026,7,5,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',2,NULL,NULL,'manual','2026-09-19 19:24:52'),(8027,7,5,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',3,NULL,NULL,'manual','2026-09-19 19:24:52'),(8028,7,5,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',4,NULL,NULL,'manual','2026-09-19 19:24:52'),(8029,7,5,'Orientation and Mobility','Expresses appreciation for the dance that they learned',1,'P',NULL,'manual','2026-09-19 19:24:52'),(8030,7,5,'Orientation and Mobility','Expresses appreciation for the dance that they learned',2,NULL,NULL,'manual','2026-09-19 19:24:52'),(8031,7,5,'Orientation and Mobility','Expresses appreciation for the dance that they learned',3,NULL,NULL,'manual','2026-09-19 19:24:52'),(8032,7,5,'Orientation and Mobility','Expresses appreciation for the dance that they learned',4,NULL,NULL,'manual','2026-09-19 19:24:52'),(8465,8,6,'Daily Living Skills','Holds and uses spoon/fork correctly',1,'P',NULL,'manual','2026-09-19 19:24:55'),(8466,8,6,'Daily Living Skills','Holds and uses spoon/fork correctly',2,NULL,NULL,'manual','2026-09-19 19:24:55'),(8467,8,6,'Daily Living Skills','Holds and uses spoon/fork correctly',3,NULL,NULL,'manual','2026-09-19 19:24:55'),(8468,8,6,'Daily Living Skills','Holds and uses spoon/fork correctly',4,NULL,NULL,'manual','2026-09-19 19:24:55'),(8469,8,6,'Daily Living Skills','Drinks from a glass without spilling',1,'P',NULL,'manual','2026-09-19 19:24:55'),(8470,8,6,'Daily Living Skills','Drinks from a glass without spilling',2,NULL,NULL,'manual','2026-09-19 19:24:55'),(8471,8,6,'Daily Living Skills','Drinks from a glass without spilling',3,NULL,NULL,'manual','2026-09-19 19:24:55'),(8472,8,6,'Daily Living Skills','Drinks from a glass without spilling',4,NULL,NULL,'manual','2026-09-19 19:24:55'),(8473,8,6,'Daily Living Skills','Eats using hands with minimal mess',1,'P',NULL,'manual','2026-09-19 19:24:55'),(8474,8,6,'Daily Living Skills','Eats using hands with minimal mess',2,NULL,NULL,'manual','2026-09-19 19:24:55'),(8475,8,6,'Daily Living Skills','Eats using hands with minimal mess',3,NULL,NULL,'manual','2026-09-19 19:24:55'),(8476,8,6,'Daily Living Skills','Eats using hands with minimal mess',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8477,8,6,'Daily Living Skills','Opens food containers independently',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8478,8,6,'Daily Living Skills','Opens food containers independently',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8479,8,6,'Daily Living Skills','Opens food containers independently',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8480,8,6,'Daily Living Skills','Opens food containers independently',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8481,8,6,'Daily Living Skills','Indicates need to use the toilet',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8482,8,6,'Daily Living Skills','Indicates need to use the toilet',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8483,8,6,'Daily Living Skills','Indicates need to use the toilet',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8484,8,6,'Daily Living Skills','Indicates need to use the toilet',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8485,8,6,'Daily Living Skills','Goes to the toilet independently',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8486,8,6,'Daily Living Skills','Goes to the toilet independently',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8487,8,6,'Daily Living Skills','Goes to the toilet independently',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8488,8,6,'Daily Living Skills','Goes to the toilet independently',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8489,8,6,'Daily Living Skills','Flushes the toilet after use',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8490,8,6,'Daily Living Skills','Flushes the toilet after use',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8491,8,6,'Daily Living Skills','Flushes the toilet after use',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8492,8,6,'Daily Living Skills','Flushes the toilet after use',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8493,8,6,'Daily Living Skills','Washes hands after toileting',1,'AP',NULL,'manual','2026-09-19 19:24:56'),(8494,8,6,'Daily Living Skills','Washes hands after toileting',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8495,8,6,'Daily Living Skills','Washes hands after toileting',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8496,8,6,'Daily Living Skills','Washes hands after toileting',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8497,8,6,'Daily Living Skills','Puts on and removes clothing independently',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8498,8,6,'Daily Living Skills','Puts on and removes clothing independently',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8499,8,6,'Daily Living Skills','Puts on and removes clothing independently',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8500,8,6,'Daily Living Skills','Puts on and removes clothing independently',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8501,8,6,'Daily Living Skills','Buttons and unbuttons shirt',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8502,8,6,'Daily Living Skills','Buttons and unbuttons shirt',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8503,8,6,'Daily Living Skills','Buttons and unbuttons shirt',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8504,8,6,'Daily Living Skills','Buttons and unbuttons shirt',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8505,8,6,'Daily Living Skills','Zips and unzips clothing',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8506,8,6,'Daily Living Skills','Zips and unzips clothing',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8507,8,6,'Daily Living Skills','Zips and unzips clothing',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8508,8,6,'Daily Living Skills','Zips and unzips clothing',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8509,8,6,'Daily Living Skills','Wears shoes and socks correctly',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8510,8,6,'Daily Living Skills','Wears shoes and socks correctly',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8511,8,6,'Daily Living Skills','Wears shoes and socks correctly',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8512,8,6,'Daily Living Skills','Wears shoes and socks correctly',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8513,8,6,'Daily Living Skills','Brushes teeth with minimal supervision',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8514,8,6,'Daily Living Skills','Brushes teeth with minimal supervision',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8515,8,6,'Daily Living Skills','Brushes teeth with minimal supervision',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8516,8,6,'Daily Living Skills','Brushes teeth with minimal supervision',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8517,8,6,'Daily Living Skills','Washes and dries hands properly',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8518,8,6,'Daily Living Skills','Washes and dries hands properly',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8519,8,6,'Daily Living Skills','Washes and dries hands properly',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8520,8,6,'Daily Living Skills','Washes and dries hands properly',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8521,8,6,'Daily Living Skills','Combs/brushes hair independently',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8522,8,6,'Daily Living Skills','Combs/brushes hair independently',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8523,8,6,'Daily Living Skills','Combs/brushes hair independently',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8524,8,6,'Daily Living Skills','Combs/brushes hair independently',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8525,8,6,'Daily Living Skills','Maintains personal cleanliness',1,'AP',NULL,'manual','2026-09-19 19:24:56'),(8526,8,6,'Daily Living Skills','Maintains personal cleanliness',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8527,8,6,'Daily Living Skills','Maintains personal cleanliness',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8528,8,6,'Daily Living Skills','Maintains personal cleanliness',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8529,8,6,'Socio-Emotional','Shows awareness of own feelings and emotions',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8530,8,6,'Socio-Emotional','Shows awareness of own feelings and emotions',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8531,8,6,'Socio-Emotional','Shows awareness of own feelings and emotions',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8532,8,6,'Socio-Emotional','Shows awareness of own feelings and emotions',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8533,8,6,'Socio-Emotional','Interacts appropriately with peers and adults',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8534,8,6,'Socio-Emotional','Interacts appropriately with peers and adults',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8535,8,6,'Socio-Emotional','Interacts appropriately with peers and adults',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8536,8,6,'Socio-Emotional','Interacts appropriately with peers and adults',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8537,8,6,'Socio-Emotional','Takes turns and shares materials',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8538,8,6,'Socio-Emotional','Takes turns and shares materials',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8539,8,6,'Socio-Emotional','Takes turns and shares materials',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8540,8,6,'Socio-Emotional','Takes turns and shares materials',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8541,8,6,'Socio-Emotional','Expresses needs and wants appropriately',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8542,8,6,'Socio-Emotional','Expresses needs and wants appropriately',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8543,8,6,'Socio-Emotional','Expresses needs and wants appropriately',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8544,8,6,'Socio-Emotional','Expresses needs and wants appropriately',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8545,8,6,'Socio-Emotional','Follows classroom rules and routines',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8546,8,6,'Socio-Emotional','Follows classroom rules and routines',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8547,8,6,'Socio-Emotional','Follows classroom rules and routines',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8548,8,6,'Socio-Emotional','Follows classroom rules and routines',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8549,8,6,'Socio-Emotional','Shows appropriate emotional responses',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8550,8,6,'Socio-Emotional','Shows appropriate emotional responses',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8551,8,6,'Socio-Emotional','Shows appropriate emotional responses',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8552,8,6,'Socio-Emotional','Shows appropriate emotional responses',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8553,8,6,'Socio-Emotional','Develops and maintains peer relationships',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8554,8,6,'Socio-Emotional','Develops and maintains peer relationships',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8555,8,6,'Socio-Emotional','Develops and maintains peer relationships',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8556,8,6,'Socio-Emotional','Develops and maintains peer relationships',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8557,8,6,'Socio-Emotional','Demonstrates self-control in social situations',1,'AP',NULL,'manual','2026-09-19 19:24:56'),(8558,8,6,'Socio-Emotional','Demonstrates self-control in social situations',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8559,8,6,'Socio-Emotional','Demonstrates self-control in social situations',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8560,8,6,'Socio-Emotional','Demonstrates self-control in social situations',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8561,8,6,'Socio-Emotional','Shows empathy toward others',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8562,8,6,'Socio-Emotional','Shows empathy toward others',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8563,8,6,'Socio-Emotional','Shows empathy toward others',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8564,8,6,'Socio-Emotional','Shows empathy toward others',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8565,8,6,'Socio-Emotional','Seeks help from adults when needed',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8566,8,6,'Socio-Emotional','Seeks help from adults when needed',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8567,8,6,'Socio-Emotional','Seeks help from adults when needed',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8568,8,6,'Socio-Emotional','Seeks help from adults when needed',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8569,8,6,'Language Development','Listens attentively during instruction',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8570,8,6,'Language Development','Listens attentively during instruction',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8571,8,6,'Language Development','Listens attentively during instruction',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8572,8,6,'Language Development','Listens attentively during instruction',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8573,8,6,'Language Development','Follows one-step verbal directions',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8574,8,6,'Language Development','Follows one-step verbal directions',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8575,8,6,'Language Development','Follows one-step verbal directions',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8576,8,6,'Language Development','Follows one-step verbal directions',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8577,8,6,'Language Development','Follows two-step verbal directions',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8578,8,6,'Language Development','Follows two-step verbal directions',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8579,8,6,'Language Development','Follows two-step verbal directions',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8580,8,6,'Language Development','Follows two-step verbal directions',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8581,8,6,'Language Development','Identifies objects/pictures when named',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8582,8,6,'Language Development','Identifies objects/pictures when named',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8583,8,6,'Language Development','Identifies objects/pictures when named',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8584,8,6,'Language Development','Identifies objects/pictures when named',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8585,8,6,'Language Development','Communicates basic needs verbally or through AAC',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8586,8,6,'Language Development','Communicates basic needs verbally or through AAC',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8587,8,6,'Language Development','Communicates basic needs verbally or through AAC',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8588,8,6,'Language Development','Communicates basic needs verbally or through AAC',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8589,8,6,'Language Development','Uses simple sentences to express ideas',1,'AP',NULL,'manual','2026-09-19 19:24:56'),(8590,8,6,'Language Development','Uses simple sentences to express ideas',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8591,8,6,'Language Development','Uses simple sentences to express ideas',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8592,8,6,'Language Development','Uses simple sentences to express ideas',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8593,8,6,'Language Development','Participates in simple conversations',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8594,8,6,'Language Development','Participates in simple conversations',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8595,8,6,'Language Development','Participates in simple conversations',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8596,8,6,'Language Development','Participates in simple conversations',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8597,8,6,'Language Development','Answers simple questions appropriately',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8598,8,6,'Language Development','Answers simple questions appropriately',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8599,8,6,'Language Development','Answers simple questions appropriately',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8600,8,6,'Language Development','Answers simple questions appropriately',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8601,8,6,'Language Development','Recognizes own name in print',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8602,8,6,'Language Development','Recognizes own name in print',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8603,8,6,'Language Development','Recognizes own name in print',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8604,8,6,'Language Development','Recognizes own name in print',4,NULL,NULL,'manual','2026-09-19 19:24:56'),(8605,8,6,'Language Development','Identifies letters of the alphabet',1,'P',NULL,'manual','2026-09-19 19:24:56'),(8606,8,6,'Language Development','Identifies letters of the alphabet',2,NULL,NULL,'manual','2026-09-19 19:24:56'),(8607,8,6,'Language Development','Identifies letters of the alphabet',3,NULL,NULL,'manual','2026-09-19 19:24:56'),(8608,8,6,'Language Development','Identifies letters of the alphabet',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8609,8,6,'Language Development','Matches letters to sounds (phonics)',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8610,8,6,'Language Development','Matches letters to sounds (phonics)',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8611,8,6,'Language Development','Matches letters to sounds (phonics)',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8612,8,6,'Language Development','Matches letters to sounds (phonics)',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8613,8,6,'Language Development','Reads simple words and short phrases',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8614,8,6,'Language Development','Reads simple words and short phrases',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8615,8,6,'Language Development','Reads simple words and short phrases',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8616,8,6,'Language Development','Reads simple words and short phrases',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8617,8,6,'Language Development','Holds pencil/crayon with correct grip',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8618,8,6,'Language Development','Holds pencil/crayon with correct grip',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8619,8,6,'Language Development','Holds pencil/crayon with correct grip',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8620,8,6,'Language Development','Holds pencil/crayon with correct grip',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8621,8,6,'Language Development','Copies simple shapes and lines',1,'AP',NULL,'manual','2026-09-19 19:24:57'),(8622,8,6,'Language Development','Copies simple shapes and lines',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8623,8,6,'Language Development','Copies simple shapes and lines',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8624,8,6,'Language Development','Copies simple shapes and lines',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8625,8,6,'Language Development','Writes own name legibly',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8626,8,6,'Language Development','Writes own name legibly',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8627,8,6,'Language Development','Writes own name legibly',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8628,8,6,'Language Development','Writes own name legibly',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8629,8,6,'Language Development','Writes simple words from dictation',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8630,8,6,'Language Development','Writes simple words from dictation',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8631,8,6,'Language Development','Writes simple words from dictation',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8632,8,6,'Language Development','Writes simple words from dictation',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8633,8,6,'Psychomotor','Walks with balance and coordination',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8634,8,6,'Psychomotor','Walks with balance and coordination',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8635,8,6,'Psychomotor','Walks with balance and coordination',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8636,8,6,'Psychomotor','Walks with balance and coordination',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8637,8,6,'Psychomotor','Runs with control and coordination',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8638,8,6,'Psychomotor','Runs with control and coordination',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8639,8,6,'Psychomotor','Runs with control and coordination',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8640,8,6,'Psychomotor','Runs with control and coordination',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8641,8,6,'Psychomotor','Climbs stairs using alternating feet',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8642,8,6,'Psychomotor','Climbs stairs using alternating feet',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8643,8,6,'Psychomotor','Climbs stairs using alternating feet',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8644,8,6,'Psychomotor','Climbs stairs using alternating feet',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8645,8,6,'Psychomotor','Catches and throws a ball',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8646,8,6,'Psychomotor','Catches and throws a ball',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8647,8,6,'Psychomotor','Catches and throws a ball',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8648,8,6,'Psychomotor','Catches and throws a ball',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8649,8,6,'Psychomotor','Jumps on two feet and hops on one foot',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8650,8,6,'Psychomotor','Jumps on two feet and hops on one foot',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8651,8,6,'Psychomotor','Jumps on two feet and hops on one foot',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8652,8,6,'Psychomotor','Jumps on two feet and hops on one foot',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8653,8,6,'Psychomotor','Participates in physical activities with peers',1,'AP',NULL,'manual','2026-09-19 19:24:57'),(8654,8,6,'Psychomotor','Participates in physical activities with peers',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8655,8,6,'Psychomotor','Participates in physical activities with peers',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8656,8,6,'Psychomotor','Participates in physical activities with peers',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8657,8,6,'Psychomotor','Cuts along a straight and curved line with scissors',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8658,8,6,'Psychomotor','Cuts along a straight and curved line with scissors',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8659,8,6,'Psychomotor','Cuts along a straight and curved line with scissors',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8660,8,6,'Psychomotor','Cuts along a straight and curved line with scissors',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8661,8,6,'Psychomotor','Strings beads and manipulates small objects',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8662,8,6,'Psychomotor','Strings beads and manipulates small objects',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8663,8,6,'Psychomotor','Strings beads and manipulates small objects',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8664,8,6,'Psychomotor','Strings beads and manipulates small objects',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8665,8,6,'Psychomotor','Completes puzzles with multiple pieces',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8666,8,6,'Psychomotor','Completes puzzles with multiple pieces',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8667,8,6,'Psychomotor','Completes puzzles with multiple pieces',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8668,8,6,'Psychomotor','Completes puzzles with multiple pieces',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8669,8,6,'Psychomotor','Colors within boundaries',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8670,8,6,'Psychomotor','Colors within boundaries',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8671,8,6,'Psychomotor','Colors within boundaries',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8672,8,6,'Psychomotor','Colors within boundaries',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8673,8,6,'Psychomotor','Copies simple geometric shapes',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8674,8,6,'Psychomotor','Copies simple geometric shapes',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8675,8,6,'Psychomotor','Copies simple geometric shapes',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8676,8,6,'Psychomotor','Copies simple geometric shapes',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8677,8,6,'Psychomotor','Demonstrates eye-hand coordination in tasks',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8678,8,6,'Psychomotor','Demonstrates eye-hand coordination in tasks',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8679,8,6,'Psychomotor','Demonstrates eye-hand coordination in tasks',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8680,8,6,'Psychomotor','Demonstrates eye-hand coordination in tasks',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8681,8,6,'Psychomotor','Identifies left and right body sides',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8682,8,6,'Psychomotor','Identifies left and right body sides',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8683,8,6,'Psychomotor','Identifies left and right body sides',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8684,8,6,'Psychomotor','Identifies left and right body sides',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8685,8,6,'Psychomotor','Tracks moving objects with eyes',1,'AP',NULL,'manual','2026-09-19 19:24:57'),(8686,8,6,'Psychomotor','Tracks moving objects with eyes',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8687,8,6,'Psychomotor','Tracks moving objects with eyes',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8688,8,6,'Psychomotor','Tracks moving objects with eyes',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8689,8,6,'Cognitive','Identifies and matches colors',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8690,8,6,'Cognitive','Identifies and matches colors',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8691,8,6,'Cognitive','Identifies and matches colors',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8692,8,6,'Cognitive','Identifies and matches colors',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8693,8,6,'Cognitive','Identifies and matches basic shapes',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8694,8,6,'Cognitive','Identifies and matches basic shapes',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8695,8,6,'Cognitive','Identifies and matches basic shapes',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8696,8,6,'Cognitive','Identifies and matches basic shapes',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8697,8,6,'Cognitive','Sorts and classifies objects by attributes',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8698,8,6,'Cognitive','Sorts and classifies objects by attributes',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8699,8,6,'Cognitive','Sorts and classifies objects by attributes',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8700,8,6,'Cognitive','Sorts and classifies objects by attributes',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8701,8,6,'Cognitive','Counts objects up to 10',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8702,8,6,'Cognitive','Counts objects up to 10',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8703,8,6,'Cognitive','Counts objects up to 10',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8704,8,6,'Cognitive','Counts objects up to 10',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8705,8,6,'Cognitive','Identifies numbers 1–10',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8706,8,6,'Cognitive','Identifies numbers 1–10',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8707,8,6,'Cognitive','Identifies numbers 1–10',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8708,8,6,'Cognitive','Identifies numbers 1–10',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8709,8,6,'Cognitive','Understands concepts of more/less, big/small',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8710,8,6,'Cognitive','Understands concepts of more/less, big/small',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8711,8,6,'Cognitive','Understands concepts of more/less, big/small',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8712,8,6,'Cognitive','Understands concepts of more/less, big/small',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8713,8,6,'Cognitive','Completes simple cause-and-effect tasks',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8714,8,6,'Cognitive','Completes simple cause-and-effect tasks',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8715,8,6,'Cognitive','Completes simple cause-and-effect tasks',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8716,8,6,'Cognitive','Completes simple cause-and-effect tasks',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8717,8,6,'Cognitive','Demonstrates problem-solving in daily tasks',1,'AP',NULL,'manual','2026-09-19 19:24:57'),(8718,8,6,'Cognitive','Demonstrates problem-solving in daily tasks',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8719,8,6,'Cognitive','Demonstrates problem-solving in daily tasks',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8720,8,6,'Cognitive','Demonstrates problem-solving in daily tasks',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8721,8,6,'Cognitive','Identifies body parts correctly',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8722,8,6,'Cognitive','Identifies body parts correctly',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8723,8,6,'Cognitive','Identifies body parts correctly',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8724,8,6,'Cognitive','Identifies body parts correctly',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8725,8,6,'Cognitive','Understands time concepts (before/after, today/tomorrow)',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8726,8,6,'Cognitive','Understands time concepts (before/after, today/tomorrow)',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8727,8,6,'Cognitive','Understands time concepts (before/after, today/tomorrow)',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8728,8,6,'Cognitive','Understands time concepts (before/after, today/tomorrow)',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8729,8,6,'Aesthetic/Creative','Participates in music and rhythm activities',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8730,8,6,'Aesthetic/Creative','Participates in music and rhythm activities',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8731,8,6,'Aesthetic/Creative','Participates in music and rhythm activities',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8732,8,6,'Aesthetic/Creative','Participates in music and rhythm activities',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8733,8,6,'Aesthetic/Creative','Expresses appreciation for music and sound',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8734,8,6,'Aesthetic/Creative','Expresses appreciation for music and sound',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8735,8,6,'Aesthetic/Creative','Expresses appreciation for music and sound',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8736,8,6,'Aesthetic/Creative','Expresses appreciation for music and sound',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8737,8,6,'Aesthetic/Creative','Moves body in response to music/rhythm',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8738,8,6,'Aesthetic/Creative','Moves body in response to music/rhythm',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8739,8,6,'Aesthetic/Creative','Moves body in response to music/rhythm',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8740,8,6,'Aesthetic/Creative','Moves body in response to music/rhythm',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8741,8,6,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8742,8,6,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8743,8,6,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8744,8,6,'Aesthetic/Creative','Engages in creative art activities (drawing, painting)',4,NULL,NULL,'manual','2026-09-19 19:24:57'),(8745,8,6,'Aesthetic/Creative','Expresses creativity through play',1,'P',NULL,'manual','2026-09-19 19:24:57'),(8746,8,6,'Aesthetic/Creative','Expresses creativity through play',2,NULL,NULL,'manual','2026-09-19 19:24:57'),(8747,8,6,'Aesthetic/Creative','Expresses creativity through play',3,NULL,NULL,'manual','2026-09-19 19:24:57'),(8748,8,6,'Aesthetic/Creative','Expresses creativity through play',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8749,8,6,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',1,'AP',NULL,'manual','2026-09-19 19:24:58'),(8750,8,6,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8751,8,6,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8752,8,6,'Aesthetic/Creative','Demonstrates appreciation for cultural arts',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8753,8,6,'Aesthetic/Creative','Watches and enjoys dramatic play/media',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8754,8,6,'Aesthetic/Creative','Watches and enjoys dramatic play/media',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8755,8,6,'Aesthetic/Creative','Watches and enjoys dramatic play/media',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8756,8,6,'Aesthetic/Creative','Watches and enjoys dramatic play/media',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8757,8,6,'Aesthetic/Creative','Communicates feelings through facial expressions',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8758,8,6,'Aesthetic/Creative','Communicates feelings through facial expressions',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8759,8,6,'Aesthetic/Creative','Communicates feelings through facial expressions',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8760,8,6,'Aesthetic/Creative','Communicates feelings through facial expressions',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8761,8,6,'Behavioral Development','Uses appropriate verbal communication for social interaction',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8762,8,6,'Behavioral Development','Uses appropriate verbal communication for social interaction',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8763,8,6,'Behavioral Development','Uses appropriate verbal communication for social interaction',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8764,8,6,'Behavioral Development','Uses appropriate verbal communication for social interaction',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8765,8,6,'Behavioral Development','Learns how to speak in a lower tone',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8766,8,6,'Behavioral Development','Learns how to speak in a lower tone',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8767,8,6,'Behavioral Development','Learns how to speak in a lower tone',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8768,8,6,'Behavioral Development','Learns how to speak in a lower tone',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8769,8,6,'Behavioral Development','Familiarizes with and takes relocated direction',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8770,8,6,'Behavioral Development','Familiarizes with and takes relocated direction',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8771,8,6,'Behavioral Development','Familiarizes with and takes relocated direction',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8772,8,6,'Behavioral Development','Familiarizes with and takes relocated direction',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8773,8,6,'Behavioral Development','Follows classroom/court instructions',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8774,8,6,'Behavioral Development','Follows classroom/court instructions',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8775,8,6,'Behavioral Development','Follows classroom/court instructions',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8776,8,6,'Behavioral Development','Follows classroom/court instructions',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8777,8,6,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8778,8,6,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8779,8,6,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8780,8,6,'Behavioral Development','Performs simple tasks (e.g., throwing trash in the garbage)',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8781,8,6,'Behavioral Development','Puts body materials and used items in proper place',1,'AP',NULL,'manual','2026-09-19 19:24:58'),(8782,8,6,'Behavioral Development','Puts body materials and used items in proper place',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8783,8,6,'Behavioral Development','Puts body materials and used items in proper place',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8784,8,6,'Behavioral Development','Puts body materials and used items in proper place',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8785,8,6,'Behavioral Development','Follows teacher\'s commands/inspection',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8786,8,6,'Behavioral Development','Follows teacher\'s commands/inspection',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8787,8,6,'Behavioral Development','Follows teacher\'s commands/inspection',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8788,8,6,'Behavioral Development','Follows teacher\'s commands/inspection',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8789,8,6,'Behavioral Development','Participates well in the lesson executed by the teacher',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8790,8,6,'Behavioral Development','Participates well in the lesson executed by the teacher',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8791,8,6,'Behavioral Development','Participates well in the lesson executed by the teacher',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8792,8,6,'Behavioral Development','Participates well in the lesson executed by the teacher',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8793,8,6,'Behavioral Development','Responds to questions and activities given to him/her',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8794,8,6,'Behavioral Development','Responds to questions and activities given to him/her',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8795,8,6,'Behavioral Development','Responds to questions and activities given to him/her',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8796,8,6,'Behavioral Development','Responds to questions and activities given to him/her',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8797,8,6,'Behavioral Development','Attends to task without getting out from the chair',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8798,8,6,'Behavioral Development','Attends to task without getting out from the chair',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8799,8,6,'Behavioral Development','Attends to task without getting out from the chair',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8800,8,6,'Behavioral Development','Attends to task without getting out from the chair',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8801,8,6,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8802,8,6,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8803,8,6,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8804,8,6,'Behavioral Development','Watches/listens to videos/music for 5 minutes or more',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8805,8,6,'Behavioral Development','Responds positively to behavior management procedures',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8806,8,6,'Behavioral Development','Responds positively to behavior management procedures',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8807,8,6,'Behavioral Development','Responds positively to behavior management procedures',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8808,8,6,'Behavioral Development','Responds positively to behavior management procedures',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8809,8,6,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8810,8,6,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8811,8,6,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8812,8,6,'Behavioral Development','Eliminates inappropriate and aggressive behavior during session',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8813,8,6,'Behavioral Development','Reduces separation anxiety during the session',1,'AP',NULL,'manual','2026-09-19 19:24:58'),(8814,8,6,'Behavioral Development','Reduces separation anxiety during the session',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8815,8,6,'Behavioral Development','Reduces separation anxiety during the session',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8816,8,6,'Behavioral Development','Reduces separation anxiety during the session',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8817,8,6,'Behavioral Development','Plays with other children',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8818,8,6,'Behavioral Development','Plays with other children',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8819,8,6,'Behavioral Development','Plays with other children',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8820,8,6,'Behavioral Development','Plays with other children',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8821,8,6,'Behavioral Development','Takes turn in game activities',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8822,8,6,'Behavioral Development','Takes turn in game activities',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8823,8,6,'Behavioral Development','Takes turn in game activities',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8824,8,6,'Behavioral Development','Takes turn in game activities',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8825,8,6,'Behavioral Development','Knows how to wait when playing games',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8826,8,6,'Behavioral Development','Knows how to wait when playing games',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8827,8,6,'Behavioral Development','Knows how to wait when playing games',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8828,8,6,'Behavioral Development','Knows how to wait when playing games',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8829,8,6,'Behavioral Development','Shares things/food without teacher prompt',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8830,8,6,'Behavioral Development','Shares things/food without teacher prompt',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8831,8,6,'Behavioral Development','Shares things/food without teacher prompt',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8832,8,6,'Behavioral Development','Shares things/food without teacher prompt',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8833,8,6,'Behavioral Development','Sits for 30 minutes to one hour',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8834,8,6,'Behavioral Development','Sits for 30 minutes to one hour',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8835,8,6,'Behavioral Development','Sits for 30 minutes to one hour',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8836,8,6,'Behavioral Development','Sits for 30 minutes to one hour',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8837,8,6,'Behavioral Development','Develops longer attention span to complete the task',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8838,8,6,'Behavioral Development','Develops longer attention span to complete the task',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8839,8,6,'Behavioral Development','Develops longer attention span to complete the task',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8840,8,6,'Behavioral Development','Develops longer attention span to complete the task',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8841,8,6,'Behavioral Development','Completes task on hand',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8842,8,6,'Behavioral Development','Completes task on hand',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8843,8,6,'Behavioral Development','Completes task on hand',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8844,8,6,'Behavioral Development','Completes task on hand',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8845,8,6,'Orientation and Mobility','Tells the difference between places (from/to)',1,'AP',NULL,'manual','2026-09-19 19:24:58'),(8846,8,6,'Orientation and Mobility','Tells the difference between places (from/to)',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8847,8,6,'Orientation and Mobility','Tells the difference between places (from/to)',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8848,8,6,'Orientation and Mobility','Tells the difference between places (from/to)',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8849,8,6,'Orientation and Mobility','Positions body parts on the right/left sides',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8850,8,6,'Orientation and Mobility','Positions body parts on the right/left sides',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8851,8,6,'Orientation and Mobility','Positions body parts on the right/left sides',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8852,8,6,'Orientation and Mobility','Positions body parts on the right/left sides',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8853,8,6,'Orientation and Mobility','Tells the spatial relations of objects between tables',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8854,8,6,'Orientation and Mobility','Tells the spatial relations of objects between tables',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8855,8,6,'Orientation and Mobility','Tells the spatial relations of objects between tables',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8856,8,6,'Orientation and Mobility','Tells the spatial relations of objects between tables',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8857,8,6,'Orientation and Mobility','Follows directions given to find objects',1,'P',NULL,'manual','2026-09-19 19:24:58'),(8858,8,6,'Orientation and Mobility','Follows directions given to find objects',2,NULL,NULL,'manual','2026-09-19 19:24:58'),(8859,8,6,'Orientation and Mobility','Follows directions given to find objects',3,NULL,NULL,'manual','2026-09-19 19:24:58'),(8860,8,6,'Orientation and Mobility','Follows directions given to find objects',4,NULL,NULL,'manual','2026-09-19 19:24:58'),(8861,8,6,'Orientation and Mobility','Uses position of classroom objects as reference to self',1,'P',NULL,'manual','2026-09-19 19:24:59'),(8862,8,6,'Orientation and Mobility','Uses position of classroom objects as reference to self',2,NULL,NULL,'manual','2026-09-19 19:24:59'),(8863,8,6,'Orientation and Mobility','Uses position of classroom objects as reference to self',3,NULL,NULL,'manual','2026-09-19 19:24:59'),(8864,8,6,'Orientation and Mobility','Uses position of classroom objects as reference to self',4,NULL,NULL,'manual','2026-09-19 19:24:59'),(8865,8,6,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',1,'P',NULL,'manual','2026-09-19 19:24:59'),(8866,8,6,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',2,NULL,NULL,'manual','2026-09-19 19:24:59'),(8867,8,6,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',3,NULL,NULL,'manual','2026-09-19 19:24:59'),(8868,8,6,'Orientation and Mobility','Performs bilateral arm and leg movements simultaneously with coordination',4,NULL,NULL,'manual','2026-09-19 19:24:59'),(8869,8,6,'Orientation and Mobility','Shows the body with balance and rhythm',1,'P',NULL,'manual','2026-09-19 19:24:59'),(8870,8,6,'Orientation and Mobility','Shows the body with balance and rhythm',2,NULL,NULL,'manual','2026-09-19 19:24:59'),(8871,8,6,'Orientation and Mobility','Shows the body with balance and rhythm',3,NULL,NULL,'manual','2026-09-19 19:24:59'),(8872,8,6,'Orientation and Mobility','Shows the body with balance and rhythm',4,NULL,NULL,'manual','2026-09-19 19:24:59'),(8873,8,6,'Orientation and Mobility','Identifies landmarks as clues',1,'P',NULL,'manual','2026-09-19 19:24:59'),(8874,8,6,'Orientation and Mobility','Identifies landmarks as clues',2,NULL,NULL,'manual','2026-09-19 19:24:59'),(8875,8,6,'Orientation and Mobility','Identifies landmarks as clues',3,NULL,NULL,'manual','2026-09-19 19:24:59'),(8876,8,6,'Orientation and Mobility','Identifies landmarks as clues',4,NULL,NULL,'manual','2026-09-19 19:24:59'),(8877,8,6,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',1,'AP',NULL,'manual','2026-09-19 19:24:59'),(8878,8,6,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',2,NULL,NULL,'manual','2026-09-19 19:24:59'),(8879,8,6,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',3,NULL,NULL,'manual','2026-09-19 19:24:59'),(8880,8,6,'Orientation and Mobility','Protects self from vertical and shoulder height obstacles using upper hand and forearm technique',4,NULL,NULL,'manual','2026-09-19 19:24:59'),(8881,8,6,'Orientation and Mobility','Uses parallel walk as guide',1,'P',NULL,'manual','2026-09-19 19:24:59'),(8882,8,6,'Orientation and Mobility','Uses parallel walk as guide',2,NULL,NULL,'manual','2026-09-19 19:24:59'),(8883,8,6,'Orientation and Mobility','Uses parallel walk as guide',3,NULL,NULL,'manual','2026-09-19 19:24:59'),(8884,8,6,'Orientation and Mobility','Uses parallel walk as guide',4,NULL,NULL,'manual','2026-09-19 19:24:59'),(8885,8,6,'Orientation and Mobility','Works independently',1,'P',NULL,'manual','2026-09-19 19:24:59'),(8886,8,6,'Orientation and Mobility','Works independently',2,NULL,NULL,'manual','2026-09-19 19:24:59'),(8887,8,6,'Orientation and Mobility','Works independently',3,NULL,NULL,'manual','2026-09-19 19:24:59'),(8888,8,6,'Orientation and Mobility','Works independently',4,NULL,NULL,'manual','2026-09-19 19:24:59'),(8889,8,6,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',1,'P',NULL,'manual','2026-09-19 19:24:59'),(8890,8,6,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',2,NULL,NULL,'manual','2026-09-19 19:24:59'),(8891,8,6,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',3,NULL,NULL,'manual','2026-09-19 19:24:59'),(8892,8,6,'Orientation and Mobility','Squeezes soft rubber ball of convenient size',4,NULL,NULL,'manual','2026-09-19 19:24:59'),(8893,8,6,'Orientation and Mobility','Expresses appreciation for the dance that they learned',1,'P',NULL,'manual','2026-09-19 19:24:59'),(8894,8,6,'Orientation and Mobility','Expresses appreciation for the dance that they learned',2,NULL,NULL,'manual','2026-09-19 19:24:59'),(8895,8,6,'Orientation and Mobility','Expresses appreciation for the dance that they learned',3,NULL,NULL,'manual','2026-09-19 19:24:59'),(8896,8,6,'Orientation and Mobility','Expresses appreciation for the dance that they learned',4,NULL,NULL,'manual','2026-09-19 19:24:59');
/*!40000 ALTER TABLE `student_quarterly_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_records`
--

DROP TABLE IF EXISTS `student_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `enrollment_id` int NOT NULL,
  `school_id` int DEFAULT NULL,
  `assigned_teacher_id` int DEFAULT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lrn` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `student_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `disability_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `psa_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pwd_id_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `section_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `section_id` int DEFAULT NULL,
  `learning_track` enum('unassigned','lms','traditional') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unassigned',
  `lms_invite_status` enum('none','sent','accepted','declined') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `lms_invited_at` datetime DEFAULT NULL,
  `lms_accepted_at` datetime DEFAULT NULL,
  `learner_user_id` int DEFAULT NULL,
  `track_strand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lis_status` enum('pending','synced','error') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `lis_synced_at` datetime DEFAULT NULL,
  `claim_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lrn` (`lrn`),
  KEY `enrollment_id` (`enrollment_id`),
  KEY `verified_by` (`verified_by`),
  KEY `idx_student_name` (`student_name`),
  KEY `idx_lrn` (`lrn`),
  KEY `idx_school_id` (`school_id`),
  KEY `idx_assigned_teacher_id` (`assigned_teacher_id`),
  KEY `idx_student_id_code` (`student_id`),
  KEY `idx_student_records_claim_token` (`claim_token`),
  CONSTRAINT `student_records_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollment_submissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_records_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_records_ibfk_3` FOREIGN KEY (`assigned_teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_records_ibfk_4` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_records`
--

LOCK TABLES `student_records` WRITE;
/*!40000 ALTER TABLE `student_records` DISABLE KEYS */;
INSERT INTO `student_records` VALUES (4,3,1,6,'20260003','129688260003','ONE TEST','2014-02-09','Hearing',NULL,NULL,6,'2026-09-08 17:53:44','2026-09-21 17:49:46','Maligaya',3,'lms','none',NULL,NULL,10,NULL,'pending',NULL,'186b39e0fd8ddbc6d945515e1654e4d8'),(5,4,1,6,'20260004','129688260004','TEST 2','2014-05-14','Hearing Impairment',NULL,NULL,6,'2026-09-13 06:45:59','2026-09-21 17:49:46','Maligaya',3,'lms','none',NULL,NULL,12,NULL,'synced','2026-09-13 14:45:59',NULL),(6,5,1,6,'20260005','129688260005','TEST 3','2014-08-22','Autism Spectrum',NULL,NULL,6,'2026-09-13 06:47:14','2026-09-21 17:49:46','Maligaya',3,'lms','none',NULL,NULL,14,NULL,'synced','2026-09-13 14:47:14',NULL),(7,6,1,6,'20260006','129688260006','TEST 4','2015-01-10','Hearing Impairment',NULL,NULL,6,'2026-09-13 06:47:21','2026-09-21 17:49:46','Maligaya',3,'lms','none',NULL,NULL,16,NULL,'synced','2026-09-13 14:47:21',NULL),(8,7,1,6,'20260007','129688260007','TEST 5','2015-03-30','Learning Disability',NULL,NULL,6,'2026-09-13 06:47:29','2026-09-21 17:49:46','Maligaya',3,'lms','none',NULL,NULL,18,NULL,'synced','2026-09-13 14:47:29',NULL),(13,13,1,6,'20260009','129688260008','TEST 6','2018-06-22','Hearing Impairment',NULL,NULL,6,'2026-09-21 17:46:17','2026-09-21 17:49:46','Maligaya',3,'traditional','none',NULL,NULL,NULL,NULL,'synced',NULL,'30df14c0f2db1d21afeb5f066a5b39a8'),(14,14,1,6,'20260010','129688260009','TEST 7','2018-07-14','Hearing Impairment',NULL,NULL,6,'2026-09-21 17:46:17','2026-09-21 17:49:46','Maligaya',3,'traditional','none',NULL,NULL,NULL,NULL,'synced',NULL,'1b3946dbf24fff79a93abe5fabeac8a7'),(15,15,1,6,'20260011','129688260010','TEST 8','2018-08-09','Speech Impairment',NULL,NULL,6,'2026-09-21 17:46:17','2026-09-21 17:49:46','Maligaya',3,'traditional','none',NULL,NULL,NULL,NULL,'synced',NULL,'86e34a3483faee0cd47790564d248f18'),(16,16,1,6,'20260012','129688260011','TEST 9','2018-09-01','Hearing Impairment',NULL,NULL,6,'2026-09-21 17:46:17','2026-09-21 17:49:46','Maligaya',3,'traditional','none',NULL,NULL,NULL,NULL,'synced',NULL,'4d2681aead28dc760b2213e62d4512d3'),(17,17,1,6,'20260013','129688260012','TEST 10','2018-10-30','Hearing Impairment',NULL,NULL,6,'2026-09-21 17:46:17','2026-09-21 21:26:59','Maligaya',3,'lms','accepted',NULL,'2026-09-22 02:17:18',23,NULL,'synced',NULL,'bc0012ea075067399db07a14f571fc9a');
/*!40000 ALTER TABLE `student_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_category` (`category`),
  KEY `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_assignments`
--

DROP TABLE IF EXISTS `teacher_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `school_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `grade_level` varchar(100) NOT NULL,
  `section_name` varchar(150) NOT NULL,
  `building_name` varchar(150) NOT NULL,
  `room_number` varchar(100) NOT NULL,
  `optional_message` text,
  `assigned_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_school_teacher` (`school_id`,`teacher_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `assigned_by` (`assigned_by`),
  CONSTRAINT `teacher_assignments_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_assignments_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_assignments`
--

LOCK TABLES `teacher_assignments` WRITE;
/*!40000 ALTER TABLE `teacher_assignments` DISABLE KEYS */;
INSERT INTO `teacher_assignments` VALUES (1,1,6,'SPED Program','Maligaya','Bldg 5','Room 204','',1,'2026-08-20 18:50:46','2026-08-20 18:50:46');
/*!40000 ALTER TABLE `teacher_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_fsl_modules`
--

DROP TABLE IF EXISTS `teacher_fsl_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_fsl_modules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Classroom Commands',
  `video_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gif_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `tips` text COLLATE utf8mb4_unicode_ci,
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `display_order` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_fsl_modules`
--

LOCK TABLES `teacher_fsl_modules` WRITE;
/*!40000 ALTER TABLE `teacher_fsl_modules` DISABLE KEYS */;
INSERT INTO `teacher_fsl_modules` VALUES (1,'Classroom Commands: Sit Down / Stand Up','Classroom Commands',NULL,'images/fsl/default_sign.svg','Essential command for starting and managing classroom transitions with DHH students.','Hold dominant hand flat palm down and move downwards for Sit; reverse upwards for Stand.',1,'2026-08-27 11:09:25'),(2,'Classroom Commands: Pay Attention / Look at Me','Classroom Commands',NULL,'images/fsl/default_sign.svg','Directs learner visual focus to the teacher or blackboard.','Point both index and middle fingers toward your eyes, then direct them toward the board.',2,'2026-08-27 11:09:25'),(3,'Classroom Commands: Quiet / Silence Please','Classroom Commands',NULL,'images/fsl/default_sign.svg','Gentle visual reminder for quiet focus during individual exercises.','Index finger vertically touching lips with calm, gentle facial expression.',3,'2026-08-27 11:09:25'),(4,'Fingerspelling: Filipino Sign Language A-Z Alphabet','Fingerspelling A-Z',NULL,'images/fsl/default_sign.svg','Complete fingerspelling guide used to spell names, locations, and unfamiliar words.','Keep your signing hand at shoulder height, relaxed, and facing the learner.',4,'2026-08-27 11:09:25'),(5,'IEP & Assessment Signs: Meeting & Goal','IEP Terms',NULL,'images/fsl/default_sign.svg','Signs commonly used during parent conferences and IEP student consultations.','Bring both index fingers together for Meeting; point to temple then forward for Goal.',5,'2026-08-27 11:09:25'),(6,'Social Courtesy: Good Morning / Thank You / You are Welcome','Social Courtesy',NULL,'images/fsl/default_sign.svg','Warm greetings and polite expressions to establish inclusive rapport.','Open flat hand moving outward from chin for Thank You accompanied by a warm smile.',6,'2026-08-27 11:09:25');
/*!40000 ALTER TABLE `teacher_fsl_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `traditional_iep_documents`
--

DROP TABLE IF EXISTS `traditional_iep_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `traditional_iep_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `uploaded_by` int NOT NULL,
  `document_type` enum('dll','physical_iep','assessment_report','progress_note','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int DEFAULT NULL,
  `school_year` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2026-2027',
  `quarter` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `document_type` (`document_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `traditional_iep_documents`
--

LOCK TABLES `traditional_iep_documents` WRITE;
/*!40000 ALTER TABLE `traditional_iep_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `traditional_iep_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transition_readiness`
--

DROP TABLE IF EXISTS `transition_readiness`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transition_readiness` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `iep_record_id` int NOT NULL,
  `progress_report_id` int DEFAULT NULL,
  `cot_observation_id` int DEFAULT NULL,
  `created_by` int NOT NULL,
  `readiness_result` enum('Ready for Inclusion','Needs More Support','Not Yet Ready','For Re-evaluation') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'For Re-evaluation',
  `evidence_summary` text COLLATE utf8mb4_unicode_ci,
  `teacher_recommendation` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','finalized') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `finalized_at` datetime DEFAULT NULL,
  `overall_status` enum('ready','partial','not_ready') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'partial',
  `overall_status_overridden` tinyint(1) NOT NULL DEFAULT '0',
  `overall_remarks` text COLLATE utf8mb4_unicode_ci,
  `evaluated_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_transition_readiness_iep` (`iep_record_id`),
  KEY `student_id` (`student_id`),
  KEY `progress_report_id` (`progress_report_id`),
  KEY `cot_observation_id` (`cot_observation_id`),
  KEY `created_by` (`created_by`),
  KEY `evaluated_by` (`evaluated_by`),
  KEY `idx_transition_result` (`readiness_result`),
  KEY `idx_transition_status` (`status`),
  KEY `idx_transition_overall_status` (`overall_status`),
  CONSTRAINT `transition_readiness_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transition_readiness_ibfk_2` FOREIGN KEY (`iep_record_id`) REFERENCES `iep_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transition_readiness_ibfk_3` FOREIGN KEY (`progress_report_id`) REFERENCES `progress_reports` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transition_readiness_ibfk_4` FOREIGN KEY (`cot_observation_id`) REFERENCES `cot_observations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transition_readiness_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transition_readiness_ibfk_6` FOREIGN KEY (`evaluated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transition_readiness`
--

LOCK TABLES `transition_readiness` WRITE;
/*!40000 ALTER TABLE `transition_readiness` DISABLE KEYS */;
/*!40000 ALTER TABLE `transition_readiness` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transition_readiness_goals`
--

DROP TABLE IF EXISTS `transition_readiness_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transition_readiness_goals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `transition_readiness_id` int NOT NULL,
  `iep_step_id` int NOT NULL,
  `goal_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `pdsp_domain` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `suggested_status` enum('ready','partial','not_ready') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'partial',
  `final_status` enum('ready','partial','not_ready') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'partial',
  `status_overridden` tinyint(1) NOT NULL DEFAULT '0',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_readiness_goal` (`transition_readiness_id`,`iep_step_id`),
  KEY `iep_step_id` (`iep_step_id`),
  CONSTRAINT `transition_readiness_goals_ibfk_1` FOREIGN KEY (`transition_readiness_id`) REFERENCES `transition_readiness` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transition_readiness_goals_ibfk_2` FOREIGN KEY (`iep_step_id`) REFERENCES `iep_steps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transition_readiness_goals`
--

LOCK TABLES `transition_readiness_goals` WRITE;
/*!40000 ALTER TABLE `transition_readiness_goals` DISABLE KEYS */;
/*!40000 ALTER TABLE `transition_readiness_goals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_availability`
--

DROP TABLE IF EXISTS `user_availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_availability` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` enum('recurring','exception') COLLATE utf8mb4_unicode_ci NOT NULL,
  `day_of_week` tinyint DEFAULT NULL COMMENT '0=Sunday ... 6=Saturday (recurring only)',
  `specific_date` date DEFAULT NULL COMMENT 'Exception dates only',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_recurring` (`user_id`,`type`,`day_of_week`),
  UNIQUE KEY `unique_exception` (`user_id`,`type`,`specific_date`),
  KEY `idx_user_type` (`user_id`,`type`),
  KEY `idx_specific_date` (`specific_date`),
  CONSTRAINT `user_availability_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_availability`
--

LOCK TABLES `user_availability` WRITE;
/*!40000 ALTER TABLE `user_availability` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_availability` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `school_id` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suffix` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','parent','sped_teacher','guidance','principal','master_teacher','learner','admin','general_teacher') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `profile_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `high_contrast` tinyint(1) NOT NULL DEFAULT '0',
  `fsl_popups_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('active','inactive','pending') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT '0',
  `email_verification_token` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verification_expires` datetime DEFAULT NULL,
  `verification_attempts` int DEFAULT '0',
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_provider` enum('local','google') COLLATE utf8mb4_unicode_ci DEFAULT 'local',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fsl_cert_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fsl_cert_issue_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `google_id` (`google_id`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_school_id` (`school_id`),
  KEY `idx_deleted_at` (`deleted_at`),
  KEY `idx_locked_until` (`locked_until`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'Allysa Canonizado','Allysa',NULL,'Canonizado',NULL,'allysacanonizado43@gmail.com',NULL,'$2y$10$/fC.PYvj8.5ajdfWbaPlMeQ8uD93nY12jfvd.cvPXQHt3wy3No/Bq','principal',NULL,NULL,NULL,1,1,'active',1,NULL,NULL,0,'114819199319052031501','https://lh3.googleusercontent.com/a/ACg8ocJNgbL-cYmXjKN_pXYV_wdqmjn-NEX59rZ5R0dZLLxcn_gcvl94Qg=s96-c','google',NULL,NULL,'2026-08-12 18:03:35','2026-09-13 06:31:03',NULL,NULL),(2,NULL,'System Admin','System',NULL,'Admin',NULL,'admin@spedlms.local',NULL,'$2y$10$/fC.PYvj8.5ajdfWbaPlMeQ8uD93nY12jfvd.cvPXQHt3wy3No/Bq','admin',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-08-20 18:46:10','2026-09-13 06:31:03',NULL,NULL),(5,NULL,'Allysa Canonizado','Allysa',NULL,'Canonizado',NULL,'allysacanonizado03@gmail.com',NULL,'','user',NULL,NULL,NULL,0,1,'active',1,'123456',NULL,0,'107471202491303836663','https://lh3.googleusercontent.com/a/ACg8ocLYbjZ3q1Dx7udYPK4ysAzl9FfdtcMoTey3hSAwWmx_8gocFA=s96-c','google',NULL,NULL,'2026-08-19 23:37:16','2026-10-01 21:41:50',NULL,NULL),(6,1,'Zere d\'Apchier','Zere',NULL,'d\'Apchier',NULL,'asteleackerman@gmail.com',NULL,'$2y$10$/fC.PYvj8.5ajdfWbaPlMeQ8uD93nY12jfvd.cvPXQHt3wy3No/Bq','sped_teacher',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,'102396473440496063860','https://lh3.googleusercontent.com/a/ACg8ocIu8M5RPfTy5xMtDhNhorBiGvJcYNrkk848YBlYRYIglo7r0kQ=s96-c','google',NULL,NULL,'2026-08-20 00:02:54','2026-09-13 06:31:03','uploads/role_verification/fsl_cert_6_0_1787251295_6a874a5f5a9c1.pdf','2026-08-21'),(7,NULL,'Demo Parent',NULL,NULL,NULL,NULL,'parent_demo@spedlms.local',NULL,'hash','parent',NULL,NULL,NULL,0,1,'active',0,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-08-20 18:30:20','2026-08-20 18:30:20',NULL,NULL),(8,1,'Test parent','Test','','parent','','xg40jo8fzv@ruutukf.com','09332093735','$2y$10$/fC.PYvj8.5ajdfWbaPlMeQ8uD93nY12jfvd.cvPXQHt3wy3No/Bq','parent',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-09-08 13:32:47','2026-09-13 06:31:03',NULL,NULL),(10,NULL,'ONE TEST','ONE',NULL,'TEST',NULL,'onetest_kid',NULL,'$2y$10$GuxrESlqD0HnAj.2RDHWT.whQZ7DTN/ctTyYNC3RThC8TDx2iBBZK','learner',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-09-08 17:53:44','2026-09-21 21:29:53',NULL,NULL),(11,1,'Maria Santos','Maria',NULL,'Santos',NULL,'maria.santos@gmail.com','09173012241','$2y$10$8KUoPOM3QKGYF2JiJUuQxuKozPtnI7cNjJySwrC1ckKy.tv959OGG','parent',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-09-13 06:45:59','2026-09-13 06:45:59',NULL,NULL),(12,1,'TEST 2','TEST',NULL,'2',NULL,'learner_20260004@spedlms.local',NULL,'$2y$10$8KUoPOM3QKGYF2JiJUuQxuKozPtnI7cNjJySwrC1ckKy.tv959OGG','learner',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-09-13 06:45:59','2026-09-13 06:45:59',NULL,NULL),(13,1,'Roberto Dela Cruz','Roberto',NULL,'Dela Cruz',NULL,'roberto.delacruz@gmail.com','09185423312','$2y$10$zkNeX6EIHEe9d2xMI6gUxuiXwg9ZTFtma7udPgRnvpuUOpbGKD5Iq','parent',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-09-13 06:47:14','2026-09-13 06:47:14',NULL,NULL),(14,1,'TEST 3','TEST',NULL,'3',NULL,'learner_20260005@spedlms.local',NULL,'$2y$10$zkNeX6EIHEe9d2xMI6gUxuiXwg9ZTFtma7udPgRnvpuUOpbGKD5Iq','learner',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-09-13 06:47:14','2026-09-13 06:47:14',NULL,NULL),(15,1,'Grace Alcantara','Grace',NULL,'Alcantara',NULL,'grace.alcantara@gmail.com','09204871923','$2y$10$zkNeX6EIHEe9d2xMI6gUxuiXwg9ZTFtma7udPgRnvpuUOpbGKD5Iq','parent',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-09-13 06:47:21','2026-09-13 06:47:21',NULL,NULL),(16,1,'TEST 4','TEST',NULL,'4',NULL,'learner_20260006@spedlms.local',NULL,'$2y$10$zkNeX6EIHEe9d2xMI6gUxuiXwg9ZTFtma7udPgRnvpuUOpbGKD5Iq','learner',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-09-13 06:47:21','2026-09-13 06:47:21',NULL,NULL),(17,1,'Danilo Reyes','Danilo',NULL,'Reyes',NULL,'danilo.reyes@gmail.com','09228945514','$2y$10$zkNeX6EIHEe9d2xMI6gUxuiXwg9ZTFtma7udPgRnvpuUOpbGKD5Iq','parent',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-09-13 06:47:29','2026-09-13 06:47:29',NULL,NULL),(18,1,'TEST 5','TEST',NULL,'5',NULL,'learner_20260007@spedlms.local',NULL,'$2y$10$zkNeX6EIHEe9d2xMI6gUxuiXwg9ZTFtma7udPgRnvpuUOpbGKD5Iq','learner',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-09-13 06:47:29','2026-09-13 06:47:29',NULL,NULL),(19,1,'Master Teacher Teresa Cruz','Teresa',NULL,'Cruz',NULL,'masterteacher@spedlms.local',NULL,'$2y$10$8KV2HBZ7E4KDHQD9pvAQUOkz7.Rjl/Q5iMb35CDGYd.DL3uKF1EBy','master_teacher',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-09-19 19:59:42','2026-09-19 19:59:42',NULL,NULL),(21,1,'Parent Ten',NULL,NULL,NULL,NULL,'aliastele0@gmail.com',NULL,'$2y$10$9YL6G/nnCelOSd4/vPp09.V6jZPm5q8PloThHKabiSNj41jAxjUo2','parent',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-09-21 18:17:18','2026-09-21 18:17:18',NULL,NULL),(23,1,'TEST 10',NULL,NULL,NULL,NULL,'batang_test10',NULL,'$2y$10$e2RPfejf/Whzbw2.jCB8NO7VFcKYdAwjkkU/tVM1CEEThXC8itvLC','learner',NULL,NULL,NULL,0,1,'active',1,NULL,NULL,0,NULL,NULL,'local',NULL,NULL,'2026-09-21 21:26:59','2026-09-21 21:27:09',NULL,NULL),(24,NULL,'ALLYSA JOYCE CANONIZADO','ALLYSA JOYCE','','CANONIZADO','','ie11gd7wzo@yzcalo.com','09332093735','$2y$10$/MMD7OoNwY/RQdBCbccF0uw3kyAQNi/Ed7AGtNPCFzzlVNv.FGfcW','user',NULL,NULL,NULL,0,1,'active',0,'141562','2026-10-01 22:24:30',0,NULL,NULL,'local',NULL,NULL,'2026-10-01 22:14:30','2026-10-01 22:14:30',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-02  6:44:53
