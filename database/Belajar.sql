-- ============================================================
-- BELAJAR OS - Database MySQL (Belajar.sql)
-- Aplikasi: AI Personal Learning OS - Kelas 12 SMA/MA
-- Dihasilkan dari Laravel migrations + seeders (mysqldump)
-- Karakter set : utf8mb4 / utf8mb4_unicode_ci
--
-- CARA IMPORT:
--   1) phpMyAdmin (Hosting): Buat database baru dulu (mis. nama_db),
--      pilih database tsb, menu Import, pilih file Belajar.sql.
--   2) Terminal/SSH:
--      mysql -u USER -p NAMA_DATABASE < Belajar.sql
--
-- File ini TIDAK mengandung CREATE DATABASE/USE,
-- sehingga bisa diimpor ke database dengan nama apapun.
--
-- CATATAN KEAMANAN:
--   Akun Superadmin default sudah termasuk dalam data users.
--   Password terenkripsi bcrypt. Segera ganti setelah login pertama.
-- ============================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


DROP TABLE IF EXISTS `achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `achievements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(32) NOT NULL DEFAULT 'trophy',
  `criteria` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`criteria`)),
  `points` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `achievements_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `achievements` WRITE;
/*!40000 ALTER TABLE `achievements` DISABLE KEYS */;
/*!40000 ALTER TABLE `achievements` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ai_conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_conversations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `topic_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ai_conversations_uuid_unique` (`uuid`),
  KEY `ai_conversations_user_id_foreign` (`user_id`),
  KEY `ai_conversations_subject_id_foreign` (`subject_id`),
  KEY `ai_conversations_topic_id_foreign` (`topic_id`),
  CONSTRAINT `ai_conversations_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ai_conversations_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ai_conversations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `ai_conversations` WRITE;
/*!40000 ALTER TABLE `ai_conversations` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_conversations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ai_generated_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_generated_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ai_request_id` bigint(20) unsigned DEFAULT NULL,
  `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_payload`)),
  `review_status` enum('pending','validated','published','rejected','archived') NOT NULL DEFAULT 'pending',
  `validation_error` text DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_generated_questions_question_id_foreign` (`question_id`),
  KEY `ai_generated_questions_user_id_foreign` (`user_id`),
  KEY `ai_generated_questions_reviewed_by_foreign` (`reviewed_by`),
  CONSTRAINT `ai_generated_questions_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ai_generated_questions_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ai_generated_questions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `ai_generated_questions` WRITE;
/*!40000 ALTER TABLE `ai_generated_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_generated_questions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ai_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ai_conversation_id` bigint(20) unsigned NOT NULL,
  `role` enum('user','assistant','system_note') NOT NULL DEFAULT 'user',
  `content` longtext NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_messages_ai_conversation_id_foreign` (`ai_conversation_id`),
  CONSTRAINT `ai_messages_ai_conversation_id_foreign` FOREIGN KEY (`ai_conversation_id`) REFERENCES `ai_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `ai_messages` WRITE;
/*!40000 ALTER TABLE `ai_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_messages` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ai_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `request_type` enum('chat','question_generation','explanation','remedial_plan') NOT NULL DEFAULT 'chat',
  `model` varchar(80) DEFAULT NULL,
  `status` enum('success','failed','rate_limited','invalid_output') NOT NULL DEFAULT 'success',
  `duration_ms` int(10) unsigned NOT NULL DEFAULT 0,
  `token_usage` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`token_usage`)),
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_requests_user_id_foreign` (`user_id`),
  CONSTRAINT `ai_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `ai_requests` WRITE;
/*!40000 ALTER TABLE `ai_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_requests` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `answer_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `answer_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `tryout_session_id` bigint(20) unsigned DEFAULT NULL,
  `question_id` bigint(20) unsigned NOT NULL,
  `topic_id` bigint(20) unsigned DEFAULT NULL,
  `selected_option_key` varchar(10) DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `time_spent_seconds` int(10) unsigned NOT NULL DEFAULT 0,
  `source` enum('practice','tryout','tka','remedial','ai_practice') NOT NULL DEFAULT 'practice',
  `client_request_id` varchar(100) DEFAULT NULL,
  `answered_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `answer_attempts_client_request_id_unique` (`client_request_id`),
  KEY `answer_attempts_tryout_session_id_foreign` (`tryout_session_id`),
  KEY `answer_attempts_question_id_foreign` (`question_id`),
  KEY `answer_attempts_user_id_topic_id_answered_at_index` (`user_id`,`topic_id`,`answered_at`),
  KEY `answer_attempts_user_id_source_index` (`user_id`,`source`),
  CONSTRAINT `answer_attempts_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `answer_attempts_tryout_session_id_foreign` FOREIGN KEY (`tryout_session_id`) REFERENCES `tryout_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `answer_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `answer_attempts` WRITE;
/*!40000 ALTER TABLE `answer_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `answer_attempts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `api_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(80) NOT NULL DEFAULT 'android',
  `token_hash` varchar(64) NOT NULL,
  `device_id` varchar(120) DEFAULT NULL,
  `abilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`abilities`)),
  `last_used_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_access_tokens_token_hash_unique` (`token_hash`),
  KEY `api_access_tokens_user_id_foreign` (`user_id`),
  CONSTRAINT `api_access_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `api_access_tokens` WRITE;
/*!40000 ALTER TABLE `api_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `actor_user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `target_type` varchar(120) DEFAULT NULL,
  `target_id` bigint(20) unsigned DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_actor_user_id_foreign` (`actor_user_id`),
  CONSTRAINT `audit_logs_actor_user_id_foreign` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `backups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `device_id` varchar(120) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `size_bytes` bigint(20) unsigned NOT NULL DEFAULT 0,
  `storage_path` varchar(500) NOT NULL,
  `checksum_sha256` char(64) DEFAULT NULL,
  `status` enum('uploading','ready','corrupted','deleted') NOT NULL DEFAULT 'uploading',
  `record_count` int(10) unsigned NOT NULL DEFAULT 0,
  `idempotency_key` varchar(120) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `backups_uuid_unique` (`uuid`),
  UNIQUE KEY `backup_idem_uq` (`user_id`,`idempotency_key`),
  CONSTRAINT `backups_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `backups` WRITE;
/*!40000 ALTER TABLE `backups` DISABLE KEYS */;
/*!40000 ALTER TABLE `backups` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookmarks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `bookmarkable_type` varchar(255) NOT NULL,
  `bookmarkable_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bookmark_uq` (`user_id`,`bookmarkable_type`,`bookmarkable_id`),
  CONSTRAINT `bookmarks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `bookmarks` WRITE;
/*!40000 ALTER TABLE `bookmarks` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookmarks` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `chapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chapters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `curriculum_subject_id` bigint(20) unsigned NOT NULL,
  `number` int(10) unsigned NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chapter_cs_num_uq` (`curriculum_subject_id`,`number`),
  CONSTRAINT `chapters_curriculum_subject_id_foreign` FOREIGN KEY (`curriculum_subject_id`) REFERENCES `curriculum_subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `chapters` WRITE;
/*!40000 ALTER TABLE `chapters` DISABLE KEYS */;
INSERT INTO `chapters` VALUES (1,1,1,'Integral','Bab Integral — Matematika kelas XII.','published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `chapters` VALUES (2,1,2,'Turunan','Bab Turunan — Matematika kelas XII.','published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `chapters` VALUES (3,2,1,'Integral','Bab Integral — Matematika Tingkat Lanjut kelas XII.','published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `chapters` VALUES (4,2,2,'Turunan','Bab Turunan — Matematika Tingkat Lanjut kelas XII.','published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `chapters` VALUES (5,3,1,'Teks Editorial','Bab Teks Editorial — Bahasa Indonesia kelas XII.','published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `chapters` VALUES (6,3,2,'Puisi','Bab Puisi — Bahasa Indonesia kelas XII.','published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `chapters` VALUES (7,4,1,'Analytical Exposition','Bab Analytical Exposition — Bahasa Inggris kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (8,4,2,'Narrative Text','Bab Narrative Text — Bahasa Inggris kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (9,5,1,'Listrik Arus Kuat','Bab Listrik Arus Kuat — Fisika kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (10,5,2,'Induksi Elektromagnetik','Bab Induksi Elektromagnetik — Fisika kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (11,6,1,'Laju Reaksi','Bab Laju Reaksi — Kimia kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (12,6,2,'Kesetimbangan Kimia','Bab Kesetimbangan Kimia — Kimia kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (13,7,1,'Pertumbuhan & Perkembangan','Bab Pertumbuhan & Perkembangan — Biologi kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (14,7,2,'Metabolisme','Bab Metabolisme — Biologi kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (15,8,1,'Pembangunan Ekonomi','Bab Pembangunan Ekonomi — Ekonomi kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (16,8,2,'Anggaran & Perpajakan','Bab Anggaran & Perpajakan — Ekonomi kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (17,9,1,'Pembangunan Berkelanjutan','Bab Pembangunan Berkelanjutan — Geografi kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (18,9,2,'Negara Maju & Berkembang','Bab Negara Maju & Berkembang — Geografi kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (19,10,1,'Perubahan Sosial','Bab Perubahan Sosial — Sosiologi kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (20,10,2,'Globalisasi','Bab Globalisasi — Sosiologi kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (21,11,1,'Reformasi 1998','Bab Reformasi 1998 — Sejarah kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (22,11,2,'Indonesia Masa Orde Baru','Bab Indonesia Masa Orde Baru — Sejarah kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (23,12,1,'Konsep Dasar','Bab Konsep Dasar — Fikih kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (24,12,2,'Penerapan','Bab Penerapan — Fikih kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (25,13,1,'Konsep Dasar','Bab Konsep Dasar — Sejarah Kebudayaan Islam kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (26,13,2,'Penerapan','Bab Penerapan — Sejarah Kebudayaan Islam kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (27,14,1,'Konsep Dasar','Bab Konsep Dasar — Akidah Akhlak kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (28,14,2,'Penerapan','Bab Penerapan — Akidah Akhlak kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (29,15,1,'Konsep Dasar','Bab Konsep Dasar — Al-Qur\'an Hadis kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (30,15,2,'Penerapan','Bab Penerapan — Al-Qur\'an Hadis kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (31,16,1,'Konsep Dasar','Bab Konsep Dasar — Bahasa Arab kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `chapters` VALUES (32,16,2,'Penerapan','Bab Penerapan — Bahasa Arab kelas XII.','published','2026-10-02 21:41:19','2026-10-02 21:41:19');
/*!40000 ALTER TABLE `chapters` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `curriculum_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `curriculum_subjects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `curriculum_id` bigint(20) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `grade` varchar(8) NOT NULL DEFAULT 'XII',
  `semester` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `curr_subj_grade_sem_uq` (`curriculum_id`,`subject_id`,`grade`,`semester`),
  KEY `curriculum_subjects_subject_id_foreign` (`subject_id`),
  CONSTRAINT `curriculum_subjects_curriculum_id_foreign` FOREIGN KEY (`curriculum_id`) REFERENCES `curriculums` (`id`) ON DELETE CASCADE,
  CONSTRAINT `curriculum_subjects_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `curriculum_subjects` WRITE;
/*!40000 ALTER TABLE `curriculum_subjects` DISABLE KEYS */;
INSERT INTO `curriculum_subjects` VALUES (1,1,1,'XII',NULL,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `curriculum_subjects` VALUES (2,1,2,'XII',NULL,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `curriculum_subjects` VALUES (3,1,3,'XII',NULL,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `curriculum_subjects` VALUES (4,1,4,'XII',NULL,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `curriculum_subjects` VALUES (5,1,5,'XII',NULL,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `curriculum_subjects` VALUES (6,1,6,'XII',NULL,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `curriculum_subjects` VALUES (7,1,7,'XII',NULL,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `curriculum_subjects` VALUES (8,1,8,'XII',NULL,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `curriculum_subjects` VALUES (9,1,9,'XII',NULL,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `curriculum_subjects` VALUES (10,1,10,'XII',NULL,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `curriculum_subjects` VALUES (11,1,11,'XII',NULL,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `curriculum_subjects` VALUES (12,1,12,'XII',NULL,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `curriculum_subjects` VALUES (13,1,13,'XII',NULL,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `curriculum_subjects` VALUES (14,1,14,'XII',NULL,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `curriculum_subjects` VALUES (15,1,15,'XII',NULL,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `curriculum_subjects` VALUES (16,1,16,'XII',NULL,'2026-10-02 21:41:19','2026-10-02 21:41:19');
/*!40000 ALTER TABLE `curriculum_subjects` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `curriculums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `curriculums` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(30) NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `curriculums_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `curriculums` WRITE;
/*!40000 ALTER TABLE `curriculums` DISABLE KEYS */;
INSERT INTO `curriculums` VALUES (1,'Kurikulum Merdeka','KM-2025',1,1,'Kurikulum aktif untuk kelas XII SMA/MA.','2026-10-02 21:41:18','2026-10-02 21:41:18');
/*!40000 ALTER TABLE `curriculums` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `device_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `device_id` varchar(120) NOT NULL,
  `platform` varchar(30) NOT NULL DEFAULT 'android',
  `app_version` varchar(20) DEFAULT NULL,
  `token_identifier` varchar(100) DEFAULT NULL,
  `last_seen_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_sessions_user_id_device_id_unique` (`user_id`,`device_id`),
  CONSTRAINT `device_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `device_sessions` WRITE;
/*!40000 ALTER TABLE `device_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_sessions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `hero_slides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hero_slides` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `video_url` varchar(255) NOT NULL,
  `cta_text` varchar(255) DEFAULT NULL,
  `cta_url` varchar(255) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `duration_seconds` smallint(5) unsigned NOT NULL DEFAULT 7,
  `overlay_opacity` decimal(3,2) NOT NULL DEFAULT 0.78,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `hero_slides` WRITE;
/*!40000 ALTER TABLE `hero_slides` DISABLE KEYS */;
/*!40000 ALTER TABLE `hero_slides` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `landing_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `landing_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `landing_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `landing_settings` WRITE;
/*!40000 ALTER TABLE `landing_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `landing_settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `learning_targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `learning_targets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `topic_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','achieved','abandoned') NOT NULL DEFAULT 'active',
  `target_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `learning_targets_user_id_foreign` (`user_id`),
  KEY `learning_targets_subject_id_foreign` (`subject_id`),
  KEY `learning_targets_topic_id_foreign` (`topic_id`),
  CONSTRAINT `learning_targets_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `learning_targets_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL,
  CONSTRAINT `learning_targets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `learning_targets` WRITE;
/*!40000 ALTER TABLE `learning_targets` DISABLE KEYS */;
/*!40000 ALTER TABLE `learning_targets` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `learning_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `learning_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `learning_target_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `kind` enum('study_material','practice','review_mistakes','remedial','mini_test','tryout') NOT NULL DEFAULT 'practice',
  `status` enum('pending','in_progress','completed','overdue') NOT NULL DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `learning_tasks_learning_target_id_foreign` (`learning_target_id`),
  KEY `learning_tasks_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `learning_tasks_learning_target_id_foreign` FOREIGN KEY (`learning_target_id`) REFERENCES `learning_targets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learning_tasks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `learning_tasks` WRITE;
/*!40000 ALTER TABLE `learning_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `learning_tasks` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `material_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `material_sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `material_id` bigint(20) unsigned NOT NULL,
  `heading` varchar(255) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `kind` enum('text','image','video','formula','example','exercise') NOT NULL DEFAULT 'text',
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `material_sections_material_id_foreign` (`material_id`),
  CONSTRAINT `material_sections_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `material_sections` WRITE;
/*!40000 ALTER TABLE `material_sections` DISABLE KEYS */;
INSERT INTO `material_sections` VALUES (1,1,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Integral Tak Tentu pada mata pelajaran Matematika. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `material_sections` VALUES (2,2,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Integral Tertentu pada mata pelajaran Matematika. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `material_sections` VALUES (3,3,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Aplikasi Integral pada mata pelajaran Matematika. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `material_sections` VALUES (4,4,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Aturan Turunan pada mata pelajaran Matematika. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `material_sections` VALUES (5,5,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Aplikasi Turunan pada mata pelajaran Matematika. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `material_sections` VALUES (6,6,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Integral Tak Tentu pada mata pelajaran Matematika Tingkat Lanjut. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `material_sections` VALUES (7,7,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Integral Tertentu pada mata pelajaran Matematika Tingkat Lanjut. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `material_sections` VALUES (8,8,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Aplikasi Integral pada mata pelajaran Matematika Tingkat Lanjut. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `material_sections` VALUES (9,9,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Aturan Turunan pada mata pelajaran Matematika Tingkat Lanjut. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `material_sections` VALUES (10,10,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Aplikasi Turunan pada mata pelajaran Matematika Tingkat Lanjut. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `material_sections` VALUES (11,11,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Struktur Editorial pada mata pelajaran Bahasa Indonesia. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `material_sections` VALUES (12,12,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Kebahasaan pada mata pelajaran Bahasa Indonesia. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `material_sections` VALUES (13,13,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Unsur Puisi pada mata pelajaran Bahasa Indonesia. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `material_sections` VALUES (14,14,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Apresiasi Puisi pada mata pelajaran Bahasa Indonesia. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `material_sections` VALUES (15,15,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Generic Structure pada mata pelajaran Bahasa Inggris. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (16,16,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Language Features pada mata pelajaran Bahasa Inggris. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (17,17,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Elements pada mata pelajaran Bahasa Inggris. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (18,18,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Comprehension pada mata pelajaran Bahasa Inggris. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (19,19,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Hukum Ohm pada mata pelajaran Fisika. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (20,20,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Energi & Daya Listrik pada mata pelajaran Fisika. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (21,21,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Fluks pada mata pelajaran Fisika. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (22,22,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Hukum Faraday pada mata pelajaran Fisika. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (23,23,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Orde Reaksi pada mata pelajaran Kimia. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (24,24,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Teori Tumbukan pada mata pelajaran Kimia. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (25,25,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Geseran Kesetimbangan pada mata pelajaran Kimia. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (26,26,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Hukum Henry pada mata pelajaran Kimia. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (27,27,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Faktor Internal pada mata pelajaran Biologi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (28,28,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Faktor Eksternal pada mata pelajaran Biologi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (29,29,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Enzim pada mata pelajaran Biologi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (30,30,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Katabolisme Karbohidrat pada mata pelajaran Biologi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (31,31,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Teori Pembangunan pada mata pelajaran Ekonomi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (32,32,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Permasalahan pada mata pelajaran Ekonomi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (33,33,'Pendahuluan','Contoh materi pembelajaran (sample) tentang APBN pada mata pelajaran Ekonomi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (34,34,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Policy Fiscal pada mata pelajaran Ekonomi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (35,35,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Konsep pada mata pelajaran Geografi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (36,36,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Indikator pada mata pelajaran Geografi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (37,37,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Klasifikasi pada mata pelajaran Geografi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (38,38,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Indikator pada mata pelajaran Geografi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (39,39,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Teori pada mata pelajaran Sosiologi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (40,40,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Faktor Pendorong pada mata pelajaran Sosiologi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (41,41,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Bentuk pada mata pelajaran Sosiologi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (42,42,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Dampak pada mata pelajaran Sosiologi. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (43,43,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Latar Belakang pada mata pelajaran Sejarah. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (44,44,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Dinamika pada mata pelajaran Sejarah. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (45,45,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Pembangunan pada mata pelajaran Sejarah. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (46,46,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Kebijakan pada mata pelajaran Sejarah. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (47,47,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Pengertian pada mata pelajaran Fikih. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (48,48,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Ruang Lingkup pada mata pelajaran Fikih. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (49,49,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Studi Kasus pada mata pelajaran Fikih. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (50,50,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Evaluasi pada mata pelajaran Fikih. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (51,51,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Pengertian pada mata pelajaran Sejarah Kebudayaan Islam. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (52,52,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Ruang Lingkup pada mata pelajaran Sejarah Kebudayaan Islam. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (53,53,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Studi Kasus pada mata pelajaran Sejarah Kebudayaan Islam. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (54,54,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Evaluasi pada mata pelajaran Sejarah Kebudayaan Islam. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (55,55,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Pengertian pada mata pelajaran Akidah Akhlak. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (56,56,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Ruang Lingkup pada mata pelajaran Akidah Akhlak. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (57,57,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Studi Kasus pada mata pelajaran Akidah Akhlak. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (58,58,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Evaluasi pada mata pelajaran Akidah Akhlak. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (59,59,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Pengertian pada mata pelajaran Al-Qur\'an Hadis. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (60,60,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Ruang Lingkup pada mata pelajaran Al-Qur\'an Hadis. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (61,61,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Studi Kasus pada mata pelajaran Al-Qur\'an Hadis. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (62,62,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Evaluasi pada mata pelajaran Al-Qur\'an Hadis. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (63,63,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Pengertian pada mata pelajaran Bahasa Arab. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (64,64,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Ruang Lingkup pada mata pelajaran Bahasa Arab. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (65,65,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Studi Kasus pada mata pelajaran Bahasa Arab. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `material_sections` VALUES (66,66,'Pendahuluan','Contoh materi pembelajaran (sample) tentang Evaluasi pada mata pelajaran Bahasa Arab. Materi lengkap dapat ditambahkan melalui dashboard admin.','text',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
/*!40000 ALTER TABLE `material_sections` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` bigint(20) unsigned NOT NULL,
  `chapter_id` bigint(20) unsigned NOT NULL,
  `topic_id` bigint(20) unsigned DEFAULT NULL,
  `grade` varchar(8) NOT NULL DEFAULT 'XII',
  `title` varchar(255) NOT NULL,
  `type` enum('teori','contoh','latihan','rangkuman','video','modul') NOT NULL DEFAULT 'teori',
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'published',
  `content_version` bigint(20) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `materials_chapter_id_foreign` (`chapter_id`),
  KEY `materials_topic_id_foreign` (`topic_id`),
  KEY `materials_subject_id_grade_status_index` (`subject_id`,`grade`,`status`),
  CONSTRAINT `materials_chapter_id_foreign` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `materials_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `materials_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `materials` WRITE;
/*!40000 ALTER TABLE `materials` DISABLE KEYS */;
INSERT INTO `materials` VALUES (1,1,1,1,'XII','Materi: Integral Tak Tentu','teori','published',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `materials` VALUES (2,1,1,2,'XII','Materi: Integral Tertentu','teori','published',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `materials` VALUES (3,1,1,3,'XII','Materi: Aplikasi Integral','teori','published',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `materials` VALUES (4,1,2,4,'XII','Materi: Aturan Turunan','teori','published',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `materials` VALUES (5,1,2,5,'XII','Materi: Aplikasi Turunan','teori','published',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `materials` VALUES (6,2,3,6,'XII','Materi: Integral Tak Tentu','teori','published',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `materials` VALUES (7,2,3,7,'XII','Materi: Integral Tertentu','teori','published',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `materials` VALUES (8,2,3,8,'XII','Materi: Aplikasi Integral','teori','published',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `materials` VALUES (9,2,4,9,'XII','Materi: Aturan Turunan','teori','published',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `materials` VALUES (10,2,4,10,'XII','Materi: Aplikasi Turunan','teori','published',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `materials` VALUES (11,3,5,11,'XII','Materi: Struktur Editorial','teori','published',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `materials` VALUES (12,3,5,12,'XII','Materi: Kebahasaan','teori','published',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `materials` VALUES (13,3,6,13,'XII','Materi: Unsur Puisi','teori','published',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `materials` VALUES (14,3,6,14,'XII','Materi: Apresiasi Puisi','teori','published',1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `materials` VALUES (15,4,7,15,'XII','Materi: Generic Structure','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (16,4,7,16,'XII','Materi: Language Features','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (17,4,8,17,'XII','Materi: Elements','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (18,4,8,18,'XII','Materi: Comprehension','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (19,5,9,19,'XII','Materi: Hukum Ohm','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (20,5,9,20,'XII','Materi: Energi & Daya Listrik','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (21,5,10,21,'XII','Materi: Fluks','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (22,5,10,22,'XII','Materi: Hukum Faraday','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (23,6,11,23,'XII','Materi: Orde Reaksi','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (24,6,11,24,'XII','Materi: Teori Tumbukan','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (25,6,12,25,'XII','Materi: Geseran Kesetimbangan','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (26,6,12,26,'XII','Materi: Hukum Henry','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (27,7,13,27,'XII','Materi: Faktor Internal','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (28,7,13,28,'XII','Materi: Faktor Eksternal','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (29,7,14,29,'XII','Materi: Enzim','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (30,7,14,30,'XII','Materi: Katabolisme Karbohidrat','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (31,8,15,31,'XII','Materi: Teori Pembangunan','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (32,8,15,32,'XII','Materi: Permasalahan','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (33,8,16,33,'XII','Materi: APBN','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (34,8,16,34,'XII','Materi: Policy Fiscal','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (35,9,17,35,'XII','Materi: Konsep','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (36,9,17,36,'XII','Materi: Indikator','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (37,9,18,37,'XII','Materi: Klasifikasi','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (38,9,18,38,'XII','Materi: Indikator','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (39,10,19,39,'XII','Materi: Teori','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (40,10,19,40,'XII','Materi: Faktor Pendorong','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (41,10,20,41,'XII','Materi: Bentuk','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (42,10,20,42,'XII','Materi: Dampak','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (43,11,21,43,'XII','Materi: Latar Belakang','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (44,11,21,44,'XII','Materi: Dinamika','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (45,11,22,45,'XII','Materi: Pembangunan','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (46,11,22,46,'XII','Materi: Kebijakan','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (47,12,23,47,'XII','Materi: Pengertian','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (48,12,23,48,'XII','Materi: Ruang Lingkup','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (49,12,24,49,'XII','Materi: Studi Kasus','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (50,12,24,50,'XII','Materi: Evaluasi','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (51,13,25,51,'XII','Materi: Pengertian','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (52,13,25,52,'XII','Materi: Ruang Lingkup','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (53,13,26,53,'XII','Materi: Studi Kasus','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (54,13,26,54,'XII','Materi: Evaluasi','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (55,14,27,55,'XII','Materi: Pengertian','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (56,14,27,56,'XII','Materi: Ruang Lingkup','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (57,14,28,57,'XII','Materi: Studi Kasus','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (58,14,28,58,'XII','Materi: Evaluasi','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (59,15,29,59,'XII','Materi: Pengertian','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (60,15,29,60,'XII','Materi: Ruang Lingkup','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (61,15,30,61,'XII','Materi: Studi Kasus','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (62,15,30,62,'XII','Materi: Evaluasi','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (63,16,31,63,'XII','Materi: Pengertian','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (64,16,31,64,'XII','Materi: Ruang Lingkup','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (65,16,32,65,'XII','Materi: Studi Kasus','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `materials` VALUES (66,16,32,66,'XII','Materi: Evaluasi','teori','published',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
/*!40000 ALTER TABLE `materials` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` VALUES (4,'2026_03_18_000100_create_hero_slides_table',1);
INSERT INTO `migrations` VALUES (5,'2026_03_18_000200_create_landing_settings_table',1);
INSERT INTO `migrations` VALUES (6,'2026_03_18_000300_add_advanced_fields_to_hero_slides_table',1);
INSERT INTO `migrations` VALUES (7,'2026_03_18_000400_create_video_assets_table',1);
INSERT INTO `migrations` VALUES (8,'2026_03_20_120000_add_conversion_fields_to_video_assets_table',1);
INSERT INTO `migrations` VALUES (9,'2026_03_20_210000_alter_landing_settings_value_to_longtext',1);
INSERT INTO `migrations` VALUES (10,'2026_03_20_230000_add_user_onboarding_fields_and_audit_logs_table',1);
INSERT INTO `migrations` VALUES (11,'2026_03_20_231500_set_default_super_users',1);
INSERT INTO `migrations` VALUES (12,'2026_03_20_233000_add_multi_role_columns_to_users_table',1);
INSERT INTO `migrations` VALUES (13,'2026_10_03_000100_create_modul_belajar_tables',1);
INSERT INTO `migrations` VALUES (14,'2026_10_03_100000_create_learning_os_tables',1);
INSERT INTO `migrations` VALUES (15,'2026_10_03_100010_add_learning_os_columns_to_users_table',1);
INSERT INTO `migrations` VALUES (16,'2026_10_03_120000_add_auth_landing_fields_to_users_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `modul_chapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `modul_chapters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mapel_id` bigint(20) unsigned NOT NULL,
  `judul` varchar(255) NOT NULL,
  `ringkasan` text DEFAULT NULL,
  `nomor` int(10) unsigned NOT NULL DEFAULT 1,
  `terbit` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `modul_chapters_mapel_id_nomor_unique` (`mapel_id`,`nomor`),
  CONSTRAINT `modul_chapters_mapel_id_foreign` FOREIGN KEY (`mapel_id`) REFERENCES `modul_mapels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `modul_chapters` WRITE;
/*!40000 ALTER TABLE `modul_chapters` DISABLE KEYS */;
INSERT INTO `modul_chapters` VALUES (1,1,'Limit Fungsi Aljabar dan Trigonometri','Konsep limit, sifat-sifat, limit tak hingga, dan limit trigonometri.',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_chapters` VALUES (2,1,'Turunan Fungsi dan Penerapannya','Aturan turunan, garis singgung, fungsi naik/turun, nilai stasioner, dan masalah optimisasi.',2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_chapters` VALUES (3,1,'Integral Tak Tentu dan Tertentu','Anti-derivatif, teknik substitusi, luas daerah, dan volume benda putar.',3,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_chapters` VALUES (4,2,'Listrik Statis dan Dinamis','Hukum Coulomb, medan listrik, potensial, kapasitor, arus, hukum Ohm, dan rangkaian.',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_chapters` VALUES (5,2,'Induksi Elektromagnetik','Hukum Faraday, Lenz, GGL induksi, transformator, dan generator.',2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_chapters` VALUES (6,2,'Gelombang, Optik, dan Fisika Kuantum','Cahaya sebagai gelombang & partikel, interferensi, difraksi, efek fotolistrik, dan model atom Bohr.',3,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_chapters` VALUES (7,3,'Struktur Atom & Sistem Periodik','Model Bohr, mekanika kuantum, bilangan kuantum, konfigurasi elektron, dan sifat periodik.',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_chapters` VALUES (8,3,'Laju Reaksi & Kesetimbangan','Teori tumbukan, ordo reaksi, faktor pergeseran kesetimbangan, dan prinsip Le Chatelier.',2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_chapters` VALUES (9,3,'Kimia Organik','Golongan fungsi alkana-alkuna, benzena, polimer, makromolekul (karbohidrat, protein, lemak).',3,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_chapters` VALUES (10,4,'Metabolisme Sel','Enzim, katabolisme karbohidrat (glikolisis sampai transpor elektron), anabolisme fotosintesis.',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_chapters` VALUES (11,4,'Pembagian Sel & Genetika','Mitosis, meiosis, pola inheritance Mendel, linkage, pautan seks, dan mutasi.',2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_chapters` VALUES (12,5,'Analytical Exposition Text','Struktur thesis–argument–reiteration, language features, dan soal reading tipe HOTS.',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
/*!40000 ALTER TABLE `modul_chapters` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `modul_mapels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `modul_mapels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(16) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `ikon` varchar(32) NOT NULL DEFAULT 'book',
  `warna` varchar(16) NOT NULL DEFAULT '#6366f1',
  `urutan` int(10) unsigned NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `modul_mapels_kode_unique` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `modul_mapels` WRITE;
/*!40000 ALTER TABLE `modul_mapels` DISABLE KEYS */;
INSERT INTO `modul_mapels` VALUES (1,'mtk','Matematika (Wajib & Peminatan)','Limit fungsi, turunan, integral, matriks, vektor, statistika, dan peluang untuk kelas 12.','sigma','#6366f1',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_mapels` VALUES (2,'fis','Fisika','Listrik statis & dinamis, magnetisme, induksi elektromagnetik, gelombang, optik, dan fisika kuantum.','atom','#0ea5e9',2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_mapels` VALUES (3,'kim','Kimia','Struktur atom & sistem periodik, laju reaksi, kesetimbangan, larutan penyangga, koloid, dan kimia organik.','flask','#10b981',3,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_mapels` VALUES (4,'bio','Biologi','Pertumbuhan & perkembangan, metabolisme, pembagian sel, genetika, evolusi, dan ekologi.','leaf','#f59e0b',4,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_mapels` VALUES (5,'eng','Bahasa Inggris','Analytical exposition, discussion text, narrative, dan keterampilan academic reading untuk SNBT.','globe','#ef4444',5,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
/*!40000 ALTER TABLE `modul_mapels` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `modul_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `modul_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `chapter_id` bigint(20) unsigned NOT NULL,
  `judul` varchar(255) NOT NULL,
  `tipe` enum('teori','contoh','latihan','rangkuman','video') NOT NULL DEFAULT 'teori',
  `isi` longtext DEFAULT NULL,
  `perkiraan_menit` int(10) unsigned NOT NULL DEFAULT 15,
  `nomor` int(10) unsigned NOT NULL DEFAULT 1,
  `terbit` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `modul_materials_chapter_id_foreign` (`chapter_id`),
  CONSTRAINT `modul_materials_chapter_id_foreign` FOREIGN KEY (`chapter_id`) REFERENCES `modul_chapters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `modul_materials` WRITE;
/*!40000 ALTER TABLE `modul_materials` DISABLE KEYS */;
INSERT INTO `modul_materials` VALUES (1,1,'Pengertian dan Sifat Limit','teori','Materi limit fungsi kelas 12: definisi intuitif, operasi aljabar limit, teorema substitusi, limit kiri/kanan, serta bentuk tak tentu 0/0. Termasuk contoh penyelesaian dengan faktorisasi dan perkalian sekawan.',15,1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (2,1,'Contoh Soal Limit Bentuk Akar & Pecahan','contoh','Kumpulan soal limit dengan akar dan pecahan linear lengkap dengan pembahasan langkah demi langkah, termasuk trik cepat L\'Hospital sebagai alternatif.',15,2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (3,1,'Latihan Limit (20 Soal)','latihan','20 soal latihan limit fungsi aljabar dan trigonometri bertingkat dari mudah ke HOTS, disertai kunci jawaban.',15,3,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (4,1,'Rangkuman + Rumus Cepat Limit','rangkuman','Ringkasan rumus limit dasar, limit trigonometri, dan limit tak hingga dalam satu halaman untuk belajar cepat sebelum ujian.',15,4,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (5,2,'Aturan Turunan & Garis Singgung','teori','Definisi turunan sebagai limit, aturan hasil kali, hasil bagi, rantai, serta penerapan pada persamaan garis singgung kurva.',15,1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (6,2,'Contoh Soal Nilai Stasioner','contoh','Pembahasan menentukan titik balik maksimum/minimum, uji turunan pertama dan kedua.',15,2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (7,2,'Latihan Optimisasi','latihan','Soal cerita optimisasi (luas maksimum, volume minimum) khas UTBK/SNBT dengan kunci jawaban.',15,3,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (8,3,'Konsep Integral sebagai Anti-Turunan','teori','Notasi integral, sifat linier, integral fungsi aljabar dan trigonometri dasar, serta metode substitusi.',15,1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (9,3,'Contoh Luas Daerah & Benda Putar','contoh','Pembahasan grafik irisan daerah, integral tertentu untuk luas, dan rumus cincin/cakram untuk volume.',15,2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (10,3,'Latihan Integral (15 Soal)','latihan','Latihan integral tak tentu dan tertentu dengan tingkat kesulitan bertahap plus pembahasan singkat.',15,3,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (11,4,'Medan Listrik & Hukum Coulomb','teori','Gaya antar muatan, kuat medan listrik, potensial listrik, energi potensial, dan kapasitor keping sejajar.',15,1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (12,4,'Contoh Rangkaian Arus Searah','contoh','Pembahasan hukum Kirchhoff I & II pada rangkaian majemuk dengan angka terurai.',15,2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (13,4,'Latihan Listrik Dinamis','latihan','15 soal rangkaian, daya listrik, dan pengukuran alat ukur beserta kunci.',15,3,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (14,5,'Flux Magnet & Hukum Faraday','teori','Perubahan flux magnet, GGL induksi diri dan mutual, serta aplikasi pada trafo step-up/step-down.',15,1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (15,5,'Peta Konsep Elektromagnetik','rangkuman','Ringkasan satu halaman hubungan magnet-listrik-gaya untuk persiapan ujian.',15,2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (16,6,'Interferensi & Difraksi Cahaya','teori','Eksperimen Young, kisi difraksi, polarisasi, dispersi, serta dualisme gelombang-partikel.',15,1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (17,6,'Animasi Efek Fotolistrik','video','Video simulasi percobaan efek fotolistrik Millikan dan interpretasi grafik energi kinetik vs frekuensi.',15,2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (18,7,'Bilangan Kuantum & Konfigurasi Elektron','teori','Prinsip Aufbau, larangan Pauli, kaidah Hund, serta penentuan blok s/p/d/f dari konfigurasi.',15,1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (19,7,'Latihan SPME & Biloks','latihan','Soal menentukan empat bilangan kuantum elektron terakhir suatu unsur.',15,2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (20,8,'Contoh Perhitungan Ordo Reaksi','contoh','Menentukan laju reaksi dan orde dari data eksperimen dengan pembahasan tabel.',15,1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (21,8,'Hubungan Kc, Kp, dan Derajat Disosiasi','teori','Rumus kesetimbangan gas, tekanan parsial, dan faktor yang menggeser kesetimbangan.',15,2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (22,9,'Tabel Gugus Fungsi & Reaksi Khas','rangkuman','Rangkuman alkohol, eter, aldehid, keton, asam karboksilat, ester: rumus umum, nama, dan reaksinya.',15,1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (23,10,'Respirasi Aerob & Fosforilasi Oksidatif','teori','Tahapan glikolisis, dekarboksilasi oksidatif, siklus Krebs, rantai transpor elektron, dan total ATP.',15,1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (24,10,'Contoh Soal Perhitungan ATP','contoh','Pembahasan yield ATP dari NADH/FADH2 dengan pendekatan modern.',15,2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (25,11,'Latihan Persilangan Monohibrid–Polihibrid','latihan','20 soal persilangan lengkap dengan diagram papan catur dan rasio F1/F2.',15,1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (26,11,'Animasi Meiosis & Crossover','video','Video profase I sampai telofase II menjelaskan pembentukan gamet dan variasi genetik.',15,2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (27,12,'Structure & Language Features','teori','Generic structure, simple present, causal conjunction, modality, dan contoh teks bertema lingkungan.',15,1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `modul_materials` VALUES (28,12,'Reading Comprehension Set 1','latihan','Dua teks eksposisi + 10 soal pilihan ganda model UTBK dengan pembahasan.',15,2,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
/*!40000 ALTER TABLE `modul_materials` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `modul_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `modul_progress` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `material_id` bigint(20) unsigned NOT NULL,
  `selesai` tinyint(1) NOT NULL DEFAULT 0,
  `skor_latihan` tinyint(3) unsigned DEFAULT NULL,
  `diselesaikan_pada` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `modul_progress_user_id_material_id_unique` (`user_id`,`material_id`),
  KEY `modul_progress_material_id_foreign` (`material_id`),
  CONSTRAINT `modul_progress_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `modul_materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `modul_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `modul_progress` WRITE;
/*!40000 ALTER TABLE `modul_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `modul_progress` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `noteable_type` varchar(255) DEFAULT NULL,
  `noteable_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `deleted_locally_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notes_user_id_foreign` (`user_id`),
  KEY `notes_noteable_type_noteable_id_index` (`noteable_type`,`noteable_id`),
  CONSTRAINT `notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `progress_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `progress_snapshots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `day` date NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `progress_snapshots_user_id_day_unique` (`user_id`,`day`),
  CONSTRAINT `progress_snapshots_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `progress_snapshots` WRITE;
/*!40000 ALTER TABLE `progress_snapshots` DISABLE KEYS */;
/*!40000 ALTER TABLE `progress_snapshots` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `question_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_options` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) unsigned NOT NULL,
  `option_key` varchar(5) NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_options_question_id_option_key_unique` (`question_id`,`option_key`),
  CONSTRAINT `question_options_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=991 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `question_options` WRITE;
/*!40000 ALTER TABLE `question_options` DISABLE KEYS */;
INSERT INTO `question_options` VALUES (1,1,'A','Pilihan A untuk soal Integral Tak Tentu',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (2,1,'B','Pilihan B untuk soal Integral Tak Tentu',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (3,1,'C','Pilihan C untuk soal Integral Tak Tentu',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (4,1,'D','Pilihan D untuk soal Integral Tak Tentu',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (5,1,'E','Pilihan E untuk soal Integral Tak Tentu',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (6,2,'A','Pilihan A untuk soal Integral Tak Tentu',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (7,2,'B','Pilihan B untuk soal Integral Tak Tentu',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (8,2,'C','Pilihan C untuk soal Integral Tak Tentu',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (9,2,'D','Pilihan D untuk soal Integral Tak Tentu',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (10,2,'E','Pilihan E untuk soal Integral Tak Tentu',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (11,3,'A','Pilihan A untuk soal Integral Tak Tentu',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (12,3,'B','Pilihan B untuk soal Integral Tak Tentu',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (13,3,'C','Pilihan C untuk soal Integral Tak Tentu',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (14,3,'D','Pilihan D untuk soal Integral Tak Tentu',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (15,3,'E','Pilihan E untuk soal Integral Tak Tentu',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (16,4,'A','Pilihan A untuk soal Integral Tertentu',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (17,4,'B','Pilihan B untuk soal Integral Tertentu',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (18,4,'C','Pilihan C untuk soal Integral Tertentu',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (19,4,'D','Pilihan D untuk soal Integral Tertentu',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (20,4,'E','Pilihan E untuk soal Integral Tertentu',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (21,5,'A','Pilihan A untuk soal Integral Tertentu',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (22,5,'B','Pilihan B untuk soal Integral Tertentu',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (23,5,'C','Pilihan C untuk soal Integral Tertentu',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (24,5,'D','Pilihan D untuk soal Integral Tertentu',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (25,5,'E','Pilihan E untuk soal Integral Tertentu',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (26,6,'A','Pilihan A untuk soal Integral Tertentu',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (27,6,'B','Pilihan B untuk soal Integral Tertentu',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (28,6,'C','Pilihan C untuk soal Integral Tertentu',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (29,6,'D','Pilihan D untuk soal Integral Tertentu',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (30,6,'E','Pilihan E untuk soal Integral Tertentu',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (31,7,'A','Pilihan A untuk soal Aplikasi Integral',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (32,7,'B','Pilihan B untuk soal Aplikasi Integral',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (33,7,'C','Pilihan C untuk soal Aplikasi Integral',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (34,7,'D','Pilihan D untuk soal Aplikasi Integral',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (35,7,'E','Pilihan E untuk soal Aplikasi Integral',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (36,8,'A','Pilihan A untuk soal Aplikasi Integral',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (37,8,'B','Pilihan B untuk soal Aplikasi Integral',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (38,8,'C','Pilihan C untuk soal Aplikasi Integral',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (39,8,'D','Pilihan D untuk soal Aplikasi Integral',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (40,8,'E','Pilihan E untuk soal Aplikasi Integral',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (41,9,'A','Pilihan A untuk soal Aplikasi Integral',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (42,9,'B','Pilihan B untuk soal Aplikasi Integral',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (43,9,'C','Pilihan C untuk soal Aplikasi Integral',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (44,9,'D','Pilihan D untuk soal Aplikasi Integral',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (45,9,'E','Pilihan E untuk soal Aplikasi Integral',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (46,10,'A','Pilihan A untuk soal Aturan Turunan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (47,10,'B','Pilihan B untuk soal Aturan Turunan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (48,10,'C','Pilihan C untuk soal Aturan Turunan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (49,10,'D','Pilihan D untuk soal Aturan Turunan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (50,10,'E','Pilihan E untuk soal Aturan Turunan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (51,11,'A','Pilihan A untuk soal Aturan Turunan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (52,11,'B','Pilihan B untuk soal Aturan Turunan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (53,11,'C','Pilihan C untuk soal Aturan Turunan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (54,11,'D','Pilihan D untuk soal Aturan Turunan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (55,11,'E','Pilihan E untuk soal Aturan Turunan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (56,12,'A','Pilihan A untuk soal Aturan Turunan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (57,12,'B','Pilihan B untuk soal Aturan Turunan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (58,12,'C','Pilihan C untuk soal Aturan Turunan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (59,12,'D','Pilihan D untuk soal Aturan Turunan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (60,12,'E','Pilihan E untuk soal Aturan Turunan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (61,13,'A','Pilihan A untuk soal Aplikasi Turunan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (62,13,'B','Pilihan B untuk soal Aplikasi Turunan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (63,13,'C','Pilihan C untuk soal Aplikasi Turunan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (64,13,'D','Pilihan D untuk soal Aplikasi Turunan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (65,13,'E','Pilihan E untuk soal Aplikasi Turunan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (66,14,'A','Pilihan A untuk soal Aplikasi Turunan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (67,14,'B','Pilihan B untuk soal Aplikasi Turunan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (68,14,'C','Pilihan C untuk soal Aplikasi Turunan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (69,14,'D','Pilihan D untuk soal Aplikasi Turunan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (70,14,'E','Pilihan E untuk soal Aplikasi Turunan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (71,15,'A','Pilihan A untuk soal Aplikasi Turunan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (72,15,'B','Pilihan B untuk soal Aplikasi Turunan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (73,15,'C','Pilihan C untuk soal Aplikasi Turunan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (74,15,'D','Pilihan D untuk soal Aplikasi Turunan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (75,15,'E','Pilihan E untuk soal Aplikasi Turunan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (76,16,'A','Pilihan A untuk soal Integral Tak Tentu',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (77,16,'B','Pilihan B untuk soal Integral Tak Tentu',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (78,16,'C','Pilihan C untuk soal Integral Tak Tentu',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (79,16,'D','Pilihan D untuk soal Integral Tak Tentu',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (80,16,'E','Pilihan E untuk soal Integral Tak Tentu',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (81,17,'A','Pilihan A untuk soal Integral Tak Tentu',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (82,17,'B','Pilihan B untuk soal Integral Tak Tentu',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (83,17,'C','Pilihan C untuk soal Integral Tak Tentu',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (84,17,'D','Pilihan D untuk soal Integral Tak Tentu',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (85,17,'E','Pilihan E untuk soal Integral Tak Tentu',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (86,18,'A','Pilihan A untuk soal Integral Tak Tentu',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (87,18,'B','Pilihan B untuk soal Integral Tak Tentu',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (88,18,'C','Pilihan C untuk soal Integral Tak Tentu',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (89,18,'D','Pilihan D untuk soal Integral Tak Tentu',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (90,18,'E','Pilihan E untuk soal Integral Tak Tentu',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (91,19,'A','Pilihan A untuk soal Integral Tertentu',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (92,19,'B','Pilihan B untuk soal Integral Tertentu',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (93,19,'C','Pilihan C untuk soal Integral Tertentu',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (94,19,'D','Pilihan D untuk soal Integral Tertentu',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (95,19,'E','Pilihan E untuk soal Integral Tertentu',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (96,20,'A','Pilihan A untuk soal Integral Tertentu',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (97,20,'B','Pilihan B untuk soal Integral Tertentu',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (98,20,'C','Pilihan C untuk soal Integral Tertentu',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (99,20,'D','Pilihan D untuk soal Integral Tertentu',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (100,20,'E','Pilihan E untuk soal Integral Tertentu',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (101,21,'A','Pilihan A untuk soal Integral Tertentu',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (102,21,'B','Pilihan B untuk soal Integral Tertentu',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (103,21,'C','Pilihan C untuk soal Integral Tertentu',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (104,21,'D','Pilihan D untuk soal Integral Tertentu',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (105,21,'E','Pilihan E untuk soal Integral Tertentu',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (106,22,'A','Pilihan A untuk soal Aplikasi Integral',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (107,22,'B','Pilihan B untuk soal Aplikasi Integral',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (108,22,'C','Pilihan C untuk soal Aplikasi Integral',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (109,22,'D','Pilihan D untuk soal Aplikasi Integral',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (110,22,'E','Pilihan E untuk soal Aplikasi Integral',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (111,23,'A','Pilihan A untuk soal Aplikasi Integral',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (112,23,'B','Pilihan B untuk soal Aplikasi Integral',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (113,23,'C','Pilihan C untuk soal Aplikasi Integral',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (114,23,'D','Pilihan D untuk soal Aplikasi Integral',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (115,23,'E','Pilihan E untuk soal Aplikasi Integral',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (116,24,'A','Pilihan A untuk soal Aplikasi Integral',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (117,24,'B','Pilihan B untuk soal Aplikasi Integral',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (118,24,'C','Pilihan C untuk soal Aplikasi Integral',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (119,24,'D','Pilihan D untuk soal Aplikasi Integral',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (120,24,'E','Pilihan E untuk soal Aplikasi Integral',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (121,25,'A','Pilihan A untuk soal Aturan Turunan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (122,25,'B','Pilihan B untuk soal Aturan Turunan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (123,25,'C','Pilihan C untuk soal Aturan Turunan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (124,25,'D','Pilihan D untuk soal Aturan Turunan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (125,25,'E','Pilihan E untuk soal Aturan Turunan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (126,26,'A','Pilihan A untuk soal Aturan Turunan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (127,26,'B','Pilihan B untuk soal Aturan Turunan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (128,26,'C','Pilihan C untuk soal Aturan Turunan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (129,26,'D','Pilihan D untuk soal Aturan Turunan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (130,26,'E','Pilihan E untuk soal Aturan Turunan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (131,27,'A','Pilihan A untuk soal Aturan Turunan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (132,27,'B','Pilihan B untuk soal Aturan Turunan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (133,27,'C','Pilihan C untuk soal Aturan Turunan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (134,27,'D','Pilihan D untuk soal Aturan Turunan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (135,27,'E','Pilihan E untuk soal Aturan Turunan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (136,28,'A','Pilihan A untuk soal Aplikasi Turunan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (137,28,'B','Pilihan B untuk soal Aplikasi Turunan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (138,28,'C','Pilihan C untuk soal Aplikasi Turunan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (139,28,'D','Pilihan D untuk soal Aplikasi Turunan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (140,28,'E','Pilihan E untuk soal Aplikasi Turunan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (141,29,'A','Pilihan A untuk soal Aplikasi Turunan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (142,29,'B','Pilihan B untuk soal Aplikasi Turunan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (143,29,'C','Pilihan C untuk soal Aplikasi Turunan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (144,29,'D','Pilihan D untuk soal Aplikasi Turunan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (145,29,'E','Pilihan E untuk soal Aplikasi Turunan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (146,30,'A','Pilihan A untuk soal Aplikasi Turunan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (147,30,'B','Pilihan B untuk soal Aplikasi Turunan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (148,30,'C','Pilihan C untuk soal Aplikasi Turunan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (149,30,'D','Pilihan D untuk soal Aplikasi Turunan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (150,30,'E','Pilihan E untuk soal Aplikasi Turunan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (151,31,'A','Pilihan A untuk soal Struktur Editorial',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (152,31,'B','Pilihan B untuk soal Struktur Editorial',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (153,31,'C','Pilihan C untuk soal Struktur Editorial',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (154,31,'D','Pilihan D untuk soal Struktur Editorial',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (155,31,'E','Pilihan E untuk soal Struktur Editorial',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (156,32,'A','Pilihan A untuk soal Struktur Editorial',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (157,32,'B','Pilihan B untuk soal Struktur Editorial',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (158,32,'C','Pilihan C untuk soal Struktur Editorial',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (159,32,'D','Pilihan D untuk soal Struktur Editorial',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (160,32,'E','Pilihan E untuk soal Struktur Editorial',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (161,33,'A','Pilihan A untuk soal Struktur Editorial',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (162,33,'B','Pilihan B untuk soal Struktur Editorial',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (163,33,'C','Pilihan C untuk soal Struktur Editorial',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (164,33,'D','Pilihan D untuk soal Struktur Editorial',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (165,33,'E','Pilihan E untuk soal Struktur Editorial',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (166,34,'A','Pilihan A untuk soal Kebahasaan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (167,34,'B','Pilihan B untuk soal Kebahasaan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (168,34,'C','Pilihan C untuk soal Kebahasaan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (169,34,'D','Pilihan D untuk soal Kebahasaan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (170,34,'E','Pilihan E untuk soal Kebahasaan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (171,35,'A','Pilihan A untuk soal Kebahasaan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (172,35,'B','Pilihan B untuk soal Kebahasaan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (173,35,'C','Pilihan C untuk soal Kebahasaan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (174,35,'D','Pilihan D untuk soal Kebahasaan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (175,35,'E','Pilihan E untuk soal Kebahasaan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (176,36,'A','Pilihan A untuk soal Kebahasaan',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (177,36,'B','Pilihan B untuk soal Kebahasaan',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (178,36,'C','Pilihan C untuk soal Kebahasaan',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (179,36,'D','Pilihan D untuk soal Kebahasaan',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (180,36,'E','Pilihan E untuk soal Kebahasaan',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (181,37,'A','Pilihan A untuk soal Unsur Puisi',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (182,37,'B','Pilihan B untuk soal Unsur Puisi',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (183,37,'C','Pilihan C untuk soal Unsur Puisi',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (184,37,'D','Pilihan D untuk soal Unsur Puisi',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (185,37,'E','Pilihan E untuk soal Unsur Puisi',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (186,38,'A','Pilihan A untuk soal Unsur Puisi',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (187,38,'B','Pilihan B untuk soal Unsur Puisi',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (188,38,'C','Pilihan C untuk soal Unsur Puisi',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (189,38,'D','Pilihan D untuk soal Unsur Puisi',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (190,38,'E','Pilihan E untuk soal Unsur Puisi',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (191,39,'A','Pilihan A untuk soal Unsur Puisi',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (192,39,'B','Pilihan B untuk soal Unsur Puisi',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (193,39,'C','Pilihan C untuk soal Unsur Puisi',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (194,39,'D','Pilihan D untuk soal Unsur Puisi',0,4,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (195,39,'E','Pilihan E untuk soal Unsur Puisi',0,5,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (196,40,'A','Pilihan A untuk soal Apresiasi Puisi',1,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (197,40,'B','Pilihan B untuk soal Apresiasi Puisi',0,2,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (198,40,'C','Pilihan C untuk soal Apresiasi Puisi',0,3,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `question_options` VALUES (199,40,'D','Pilihan D untuk soal Apresiasi Puisi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (200,40,'E','Pilihan E untuk soal Apresiasi Puisi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (201,41,'A','Pilihan A untuk soal Apresiasi Puisi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (202,41,'B','Pilihan B untuk soal Apresiasi Puisi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (203,41,'C','Pilihan C untuk soal Apresiasi Puisi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (204,41,'D','Pilihan D untuk soal Apresiasi Puisi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (205,41,'E','Pilihan E untuk soal Apresiasi Puisi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (206,42,'A','Pilihan A untuk soal Apresiasi Puisi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (207,42,'B','Pilihan B untuk soal Apresiasi Puisi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (208,42,'C','Pilihan C untuk soal Apresiasi Puisi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (209,42,'D','Pilihan D untuk soal Apresiasi Puisi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (210,42,'E','Pilihan E untuk soal Apresiasi Puisi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (211,43,'A','Pilihan A untuk soal Generic Structure',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (212,43,'B','Pilihan B untuk soal Generic Structure',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (213,43,'C','Pilihan C untuk soal Generic Structure',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (214,43,'D','Pilihan D untuk soal Generic Structure',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (215,43,'E','Pilihan E untuk soal Generic Structure',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (216,44,'A','Pilihan A untuk soal Generic Structure',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (217,44,'B','Pilihan B untuk soal Generic Structure',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (218,44,'C','Pilihan C untuk soal Generic Structure',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (219,44,'D','Pilihan D untuk soal Generic Structure',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (220,44,'E','Pilihan E untuk soal Generic Structure',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (221,45,'A','Pilihan A untuk soal Generic Structure',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (222,45,'B','Pilihan B untuk soal Generic Structure',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (223,45,'C','Pilihan C untuk soal Generic Structure',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (224,45,'D','Pilihan D untuk soal Generic Structure',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (225,45,'E','Pilihan E untuk soal Generic Structure',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (226,46,'A','Pilihan A untuk soal Language Features',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (227,46,'B','Pilihan B untuk soal Language Features',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (228,46,'C','Pilihan C untuk soal Language Features',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (229,46,'D','Pilihan D untuk soal Language Features',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (230,46,'E','Pilihan E untuk soal Language Features',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (231,47,'A','Pilihan A untuk soal Language Features',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (232,47,'B','Pilihan B untuk soal Language Features',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (233,47,'C','Pilihan C untuk soal Language Features',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (234,47,'D','Pilihan D untuk soal Language Features',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (235,47,'E','Pilihan E untuk soal Language Features',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (236,48,'A','Pilihan A untuk soal Language Features',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (237,48,'B','Pilihan B untuk soal Language Features',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (238,48,'C','Pilihan C untuk soal Language Features',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (239,48,'D','Pilihan D untuk soal Language Features',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (240,48,'E','Pilihan E untuk soal Language Features',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (241,49,'A','Pilihan A untuk soal Elements',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (242,49,'B','Pilihan B untuk soal Elements',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (243,49,'C','Pilihan C untuk soal Elements',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (244,49,'D','Pilihan D untuk soal Elements',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (245,49,'E','Pilihan E untuk soal Elements',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (246,50,'A','Pilihan A untuk soal Elements',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (247,50,'B','Pilihan B untuk soal Elements',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (248,50,'C','Pilihan C untuk soal Elements',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (249,50,'D','Pilihan D untuk soal Elements',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (250,50,'E','Pilihan E untuk soal Elements',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (251,51,'A','Pilihan A untuk soal Elements',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (252,51,'B','Pilihan B untuk soal Elements',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (253,51,'C','Pilihan C untuk soal Elements',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (254,51,'D','Pilihan D untuk soal Elements',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (255,51,'E','Pilihan E untuk soal Elements',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (256,52,'A','Pilihan A untuk soal Comprehension',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (257,52,'B','Pilihan B untuk soal Comprehension',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (258,52,'C','Pilihan C untuk soal Comprehension',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (259,52,'D','Pilihan D untuk soal Comprehension',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (260,52,'E','Pilihan E untuk soal Comprehension',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (261,53,'A','Pilihan A untuk soal Comprehension',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (262,53,'B','Pilihan B untuk soal Comprehension',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (263,53,'C','Pilihan C untuk soal Comprehension',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (264,53,'D','Pilihan D untuk soal Comprehension',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (265,53,'E','Pilihan E untuk soal Comprehension',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (266,54,'A','Pilihan A untuk soal Comprehension',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (267,54,'B','Pilihan B untuk soal Comprehension',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (268,54,'C','Pilihan C untuk soal Comprehension',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (269,54,'D','Pilihan D untuk soal Comprehension',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (270,54,'E','Pilihan E untuk soal Comprehension',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (271,55,'A','Pilihan A untuk soal Hukum Ohm',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (272,55,'B','Pilihan B untuk soal Hukum Ohm',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (273,55,'C','Pilihan C untuk soal Hukum Ohm',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (274,55,'D','Pilihan D untuk soal Hukum Ohm',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (275,55,'E','Pilihan E untuk soal Hukum Ohm',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (276,56,'A','Pilihan A untuk soal Hukum Ohm',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (277,56,'B','Pilihan B untuk soal Hukum Ohm',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (278,56,'C','Pilihan C untuk soal Hukum Ohm',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (279,56,'D','Pilihan D untuk soal Hukum Ohm',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (280,56,'E','Pilihan E untuk soal Hukum Ohm',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (281,57,'A','Pilihan A untuk soal Hukum Ohm',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (282,57,'B','Pilihan B untuk soal Hukum Ohm',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (283,57,'C','Pilihan C untuk soal Hukum Ohm',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (284,57,'D','Pilihan D untuk soal Hukum Ohm',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (285,57,'E','Pilihan E untuk soal Hukum Ohm',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (286,58,'A','Pilihan A untuk soal Energi & Daya Listrik',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (287,58,'B','Pilihan B untuk soal Energi & Daya Listrik',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (288,58,'C','Pilihan C untuk soal Energi & Daya Listrik',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (289,58,'D','Pilihan D untuk soal Energi & Daya Listrik',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (290,58,'E','Pilihan E untuk soal Energi & Daya Listrik',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (291,59,'A','Pilihan A untuk soal Energi & Daya Listrik',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (292,59,'B','Pilihan B untuk soal Energi & Daya Listrik',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (293,59,'C','Pilihan C untuk soal Energi & Daya Listrik',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (294,59,'D','Pilihan D untuk soal Energi & Daya Listrik',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (295,59,'E','Pilihan E untuk soal Energi & Daya Listrik',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (296,60,'A','Pilihan A untuk soal Energi & Daya Listrik',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (297,60,'B','Pilihan B untuk soal Energi & Daya Listrik',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (298,60,'C','Pilihan C untuk soal Energi & Daya Listrik',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (299,60,'D','Pilihan D untuk soal Energi & Daya Listrik',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (300,60,'E','Pilihan E untuk soal Energi & Daya Listrik',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (301,61,'A','Pilihan A untuk soal Fluks',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (302,61,'B','Pilihan B untuk soal Fluks',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (303,61,'C','Pilihan C untuk soal Fluks',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (304,61,'D','Pilihan D untuk soal Fluks',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (305,61,'E','Pilihan E untuk soal Fluks',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (306,62,'A','Pilihan A untuk soal Fluks',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (307,62,'B','Pilihan B untuk soal Fluks',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (308,62,'C','Pilihan C untuk soal Fluks',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (309,62,'D','Pilihan D untuk soal Fluks',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (310,62,'E','Pilihan E untuk soal Fluks',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (311,63,'A','Pilihan A untuk soal Fluks',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (312,63,'B','Pilihan B untuk soal Fluks',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (313,63,'C','Pilihan C untuk soal Fluks',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (314,63,'D','Pilihan D untuk soal Fluks',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (315,63,'E','Pilihan E untuk soal Fluks',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (316,64,'A','Pilihan A untuk soal Hukum Faraday',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (317,64,'B','Pilihan B untuk soal Hukum Faraday',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (318,64,'C','Pilihan C untuk soal Hukum Faraday',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (319,64,'D','Pilihan D untuk soal Hukum Faraday',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (320,64,'E','Pilihan E untuk soal Hukum Faraday',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (321,65,'A','Pilihan A untuk soal Hukum Faraday',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (322,65,'B','Pilihan B untuk soal Hukum Faraday',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (323,65,'C','Pilihan C untuk soal Hukum Faraday',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (324,65,'D','Pilihan D untuk soal Hukum Faraday',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (325,65,'E','Pilihan E untuk soal Hukum Faraday',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (326,66,'A','Pilihan A untuk soal Hukum Faraday',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (327,66,'B','Pilihan B untuk soal Hukum Faraday',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (328,66,'C','Pilihan C untuk soal Hukum Faraday',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (329,66,'D','Pilihan D untuk soal Hukum Faraday',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (330,66,'E','Pilihan E untuk soal Hukum Faraday',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (331,67,'A','Pilihan A untuk soal Orde Reaksi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (332,67,'B','Pilihan B untuk soal Orde Reaksi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (333,67,'C','Pilihan C untuk soal Orde Reaksi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (334,67,'D','Pilihan D untuk soal Orde Reaksi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (335,67,'E','Pilihan E untuk soal Orde Reaksi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (336,68,'A','Pilihan A untuk soal Orde Reaksi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (337,68,'B','Pilihan B untuk soal Orde Reaksi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (338,68,'C','Pilihan C untuk soal Orde Reaksi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (339,68,'D','Pilihan D untuk soal Orde Reaksi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (340,68,'E','Pilihan E untuk soal Orde Reaksi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (341,69,'A','Pilihan A untuk soal Orde Reaksi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (342,69,'B','Pilihan B untuk soal Orde Reaksi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (343,69,'C','Pilihan C untuk soal Orde Reaksi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (344,69,'D','Pilihan D untuk soal Orde Reaksi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (345,69,'E','Pilihan E untuk soal Orde Reaksi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (346,70,'A','Pilihan A untuk soal Teori Tumbukan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (347,70,'B','Pilihan B untuk soal Teori Tumbukan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (348,70,'C','Pilihan C untuk soal Teori Tumbukan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (349,70,'D','Pilihan D untuk soal Teori Tumbukan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (350,70,'E','Pilihan E untuk soal Teori Tumbukan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (351,71,'A','Pilihan A untuk soal Teori Tumbukan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (352,71,'B','Pilihan B untuk soal Teori Tumbukan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (353,71,'C','Pilihan C untuk soal Teori Tumbukan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (354,71,'D','Pilihan D untuk soal Teori Tumbukan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (355,71,'E','Pilihan E untuk soal Teori Tumbukan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (356,72,'A','Pilihan A untuk soal Teori Tumbukan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (357,72,'B','Pilihan B untuk soal Teori Tumbukan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (358,72,'C','Pilihan C untuk soal Teori Tumbukan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (359,72,'D','Pilihan D untuk soal Teori Tumbukan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (360,72,'E','Pilihan E untuk soal Teori Tumbukan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (361,73,'A','Pilihan A untuk soal Geseran Kesetimbangan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (362,73,'B','Pilihan B untuk soal Geseran Kesetimbangan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (363,73,'C','Pilihan C untuk soal Geseran Kesetimbangan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (364,73,'D','Pilihan D untuk soal Geseran Kesetimbangan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (365,73,'E','Pilihan E untuk soal Geseran Kesetimbangan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (366,74,'A','Pilihan A untuk soal Geseran Kesetimbangan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (367,74,'B','Pilihan B untuk soal Geseran Kesetimbangan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (368,74,'C','Pilihan C untuk soal Geseran Kesetimbangan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (369,74,'D','Pilihan D untuk soal Geseran Kesetimbangan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (370,74,'E','Pilihan E untuk soal Geseran Kesetimbangan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (371,75,'A','Pilihan A untuk soal Geseran Kesetimbangan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (372,75,'B','Pilihan B untuk soal Geseran Kesetimbangan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (373,75,'C','Pilihan C untuk soal Geseran Kesetimbangan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (374,75,'D','Pilihan D untuk soal Geseran Kesetimbangan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (375,75,'E','Pilihan E untuk soal Geseran Kesetimbangan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (376,76,'A','Pilihan A untuk soal Hukum Henry',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (377,76,'B','Pilihan B untuk soal Hukum Henry',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (378,76,'C','Pilihan C untuk soal Hukum Henry',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (379,76,'D','Pilihan D untuk soal Hukum Henry',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (380,76,'E','Pilihan E untuk soal Hukum Henry',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (381,77,'A','Pilihan A untuk soal Hukum Henry',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (382,77,'B','Pilihan B untuk soal Hukum Henry',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (383,77,'C','Pilihan C untuk soal Hukum Henry',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (384,77,'D','Pilihan D untuk soal Hukum Henry',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (385,77,'E','Pilihan E untuk soal Hukum Henry',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (386,78,'A','Pilihan A untuk soal Hukum Henry',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (387,78,'B','Pilihan B untuk soal Hukum Henry',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (388,78,'C','Pilihan C untuk soal Hukum Henry',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (389,78,'D','Pilihan D untuk soal Hukum Henry',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (390,78,'E','Pilihan E untuk soal Hukum Henry',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (391,79,'A','Pilihan A untuk soal Faktor Internal',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (392,79,'B','Pilihan B untuk soal Faktor Internal',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (393,79,'C','Pilihan C untuk soal Faktor Internal',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (394,79,'D','Pilihan D untuk soal Faktor Internal',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (395,79,'E','Pilihan E untuk soal Faktor Internal',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (396,80,'A','Pilihan A untuk soal Faktor Internal',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (397,80,'B','Pilihan B untuk soal Faktor Internal',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (398,80,'C','Pilihan C untuk soal Faktor Internal',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (399,80,'D','Pilihan D untuk soal Faktor Internal',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (400,80,'E','Pilihan E untuk soal Faktor Internal',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (401,81,'A','Pilihan A untuk soal Faktor Internal',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (402,81,'B','Pilihan B untuk soal Faktor Internal',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (403,81,'C','Pilihan C untuk soal Faktor Internal',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (404,81,'D','Pilihan D untuk soal Faktor Internal',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (405,81,'E','Pilihan E untuk soal Faktor Internal',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (406,82,'A','Pilihan A untuk soal Faktor Eksternal',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (407,82,'B','Pilihan B untuk soal Faktor Eksternal',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (408,82,'C','Pilihan C untuk soal Faktor Eksternal',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (409,82,'D','Pilihan D untuk soal Faktor Eksternal',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (410,82,'E','Pilihan E untuk soal Faktor Eksternal',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (411,83,'A','Pilihan A untuk soal Faktor Eksternal',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (412,83,'B','Pilihan B untuk soal Faktor Eksternal',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (413,83,'C','Pilihan C untuk soal Faktor Eksternal',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (414,83,'D','Pilihan D untuk soal Faktor Eksternal',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (415,83,'E','Pilihan E untuk soal Faktor Eksternal',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (416,84,'A','Pilihan A untuk soal Faktor Eksternal',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (417,84,'B','Pilihan B untuk soal Faktor Eksternal',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (418,84,'C','Pilihan C untuk soal Faktor Eksternal',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (419,84,'D','Pilihan D untuk soal Faktor Eksternal',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (420,84,'E','Pilihan E untuk soal Faktor Eksternal',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (421,85,'A','Pilihan A untuk soal Enzim',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (422,85,'B','Pilihan B untuk soal Enzim',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (423,85,'C','Pilihan C untuk soal Enzim',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (424,85,'D','Pilihan D untuk soal Enzim',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (425,85,'E','Pilihan E untuk soal Enzim',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (426,86,'A','Pilihan A untuk soal Enzim',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (427,86,'B','Pilihan B untuk soal Enzim',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (428,86,'C','Pilihan C untuk soal Enzim',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (429,86,'D','Pilihan D untuk soal Enzim',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (430,86,'E','Pilihan E untuk soal Enzim',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (431,87,'A','Pilihan A untuk soal Enzim',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (432,87,'B','Pilihan B untuk soal Enzim',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (433,87,'C','Pilihan C untuk soal Enzim',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (434,87,'D','Pilihan D untuk soal Enzim',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (435,87,'E','Pilihan E untuk soal Enzim',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (436,88,'A','Pilihan A untuk soal Katabolisme Karbohidrat',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (437,88,'B','Pilihan B untuk soal Katabolisme Karbohidrat',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (438,88,'C','Pilihan C untuk soal Katabolisme Karbohidrat',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (439,88,'D','Pilihan D untuk soal Katabolisme Karbohidrat',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (440,88,'E','Pilihan E untuk soal Katabolisme Karbohidrat',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (441,89,'A','Pilihan A untuk soal Katabolisme Karbohidrat',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (442,89,'B','Pilihan B untuk soal Katabolisme Karbohidrat',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (443,89,'C','Pilihan C untuk soal Katabolisme Karbohidrat',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (444,89,'D','Pilihan D untuk soal Katabolisme Karbohidrat',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (445,89,'E','Pilihan E untuk soal Katabolisme Karbohidrat',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (446,90,'A','Pilihan A untuk soal Katabolisme Karbohidrat',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (447,90,'B','Pilihan B untuk soal Katabolisme Karbohidrat',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (448,90,'C','Pilihan C untuk soal Katabolisme Karbohidrat',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (449,90,'D','Pilihan D untuk soal Katabolisme Karbohidrat',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (450,90,'E','Pilihan E untuk soal Katabolisme Karbohidrat',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (451,91,'A','Pilihan A untuk soal Teori Pembangunan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (452,91,'B','Pilihan B untuk soal Teori Pembangunan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (453,91,'C','Pilihan C untuk soal Teori Pembangunan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (454,91,'D','Pilihan D untuk soal Teori Pembangunan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (455,91,'E','Pilihan E untuk soal Teori Pembangunan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (456,92,'A','Pilihan A untuk soal Teori Pembangunan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (457,92,'B','Pilihan B untuk soal Teori Pembangunan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (458,92,'C','Pilihan C untuk soal Teori Pembangunan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (459,92,'D','Pilihan D untuk soal Teori Pembangunan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (460,92,'E','Pilihan E untuk soal Teori Pembangunan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (461,93,'A','Pilihan A untuk soal Teori Pembangunan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (462,93,'B','Pilihan B untuk soal Teori Pembangunan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (463,93,'C','Pilihan C untuk soal Teori Pembangunan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (464,93,'D','Pilihan D untuk soal Teori Pembangunan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (465,93,'E','Pilihan E untuk soal Teori Pembangunan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (466,94,'A','Pilihan A untuk soal Permasalahan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (467,94,'B','Pilihan B untuk soal Permasalahan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (468,94,'C','Pilihan C untuk soal Permasalahan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (469,94,'D','Pilihan D untuk soal Permasalahan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (470,94,'E','Pilihan E untuk soal Permasalahan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (471,95,'A','Pilihan A untuk soal Permasalahan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (472,95,'B','Pilihan B untuk soal Permasalahan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (473,95,'C','Pilihan C untuk soal Permasalahan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (474,95,'D','Pilihan D untuk soal Permasalahan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (475,95,'E','Pilihan E untuk soal Permasalahan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (476,96,'A','Pilihan A untuk soal Permasalahan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (477,96,'B','Pilihan B untuk soal Permasalahan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (478,96,'C','Pilihan C untuk soal Permasalahan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (479,96,'D','Pilihan D untuk soal Permasalahan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (480,96,'E','Pilihan E untuk soal Permasalahan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (481,97,'A','Pilihan A untuk soal APBN',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (482,97,'B','Pilihan B untuk soal APBN',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (483,97,'C','Pilihan C untuk soal APBN',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (484,97,'D','Pilihan D untuk soal APBN',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (485,97,'E','Pilihan E untuk soal APBN',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (486,98,'A','Pilihan A untuk soal APBN',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (487,98,'B','Pilihan B untuk soal APBN',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (488,98,'C','Pilihan C untuk soal APBN',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (489,98,'D','Pilihan D untuk soal APBN',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (490,98,'E','Pilihan E untuk soal APBN',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (491,99,'A','Pilihan A untuk soal APBN',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (492,99,'B','Pilihan B untuk soal APBN',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (493,99,'C','Pilihan C untuk soal APBN',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (494,99,'D','Pilihan D untuk soal APBN',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (495,99,'E','Pilihan E untuk soal APBN',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (496,100,'A','Pilihan A untuk soal Policy Fiscal',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (497,100,'B','Pilihan B untuk soal Policy Fiscal',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (498,100,'C','Pilihan C untuk soal Policy Fiscal',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (499,100,'D','Pilihan D untuk soal Policy Fiscal',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (500,100,'E','Pilihan E untuk soal Policy Fiscal',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (501,101,'A','Pilihan A untuk soal Policy Fiscal',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (502,101,'B','Pilihan B untuk soal Policy Fiscal',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (503,101,'C','Pilihan C untuk soal Policy Fiscal',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (504,101,'D','Pilihan D untuk soal Policy Fiscal',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (505,101,'E','Pilihan E untuk soal Policy Fiscal',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (506,102,'A','Pilihan A untuk soal Policy Fiscal',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (507,102,'B','Pilihan B untuk soal Policy Fiscal',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (508,102,'C','Pilihan C untuk soal Policy Fiscal',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (509,102,'D','Pilihan D untuk soal Policy Fiscal',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (510,102,'E','Pilihan E untuk soal Policy Fiscal',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (511,103,'A','Pilihan A untuk soal Konsep',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (512,103,'B','Pilihan B untuk soal Konsep',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (513,103,'C','Pilihan C untuk soal Konsep',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (514,103,'D','Pilihan D untuk soal Konsep',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (515,103,'E','Pilihan E untuk soal Konsep',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (516,104,'A','Pilihan A untuk soal Konsep',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (517,104,'B','Pilihan B untuk soal Konsep',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (518,104,'C','Pilihan C untuk soal Konsep',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (519,104,'D','Pilihan D untuk soal Konsep',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (520,104,'E','Pilihan E untuk soal Konsep',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (521,105,'A','Pilihan A untuk soal Konsep',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (522,105,'B','Pilihan B untuk soal Konsep',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (523,105,'C','Pilihan C untuk soal Konsep',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (524,105,'D','Pilihan D untuk soal Konsep',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (525,105,'E','Pilihan E untuk soal Konsep',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (526,106,'A','Pilihan A untuk soal Indikator',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (527,106,'B','Pilihan B untuk soal Indikator',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (528,106,'C','Pilihan C untuk soal Indikator',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (529,106,'D','Pilihan D untuk soal Indikator',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (530,106,'E','Pilihan E untuk soal Indikator',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (531,107,'A','Pilihan A untuk soal Indikator',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (532,107,'B','Pilihan B untuk soal Indikator',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (533,107,'C','Pilihan C untuk soal Indikator',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (534,107,'D','Pilihan D untuk soal Indikator',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (535,107,'E','Pilihan E untuk soal Indikator',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (536,108,'A','Pilihan A untuk soal Indikator',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (537,108,'B','Pilihan B untuk soal Indikator',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (538,108,'C','Pilihan C untuk soal Indikator',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (539,108,'D','Pilihan D untuk soal Indikator',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (540,108,'E','Pilihan E untuk soal Indikator',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (541,109,'A','Pilihan A untuk soal Klasifikasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (542,109,'B','Pilihan B untuk soal Klasifikasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (543,109,'C','Pilihan C untuk soal Klasifikasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (544,109,'D','Pilihan D untuk soal Klasifikasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (545,109,'E','Pilihan E untuk soal Klasifikasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (546,110,'A','Pilihan A untuk soal Klasifikasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (547,110,'B','Pilihan B untuk soal Klasifikasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (548,110,'C','Pilihan C untuk soal Klasifikasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (549,110,'D','Pilihan D untuk soal Klasifikasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (550,110,'E','Pilihan E untuk soal Klasifikasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (551,111,'A','Pilihan A untuk soal Klasifikasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (552,111,'B','Pilihan B untuk soal Klasifikasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (553,111,'C','Pilihan C untuk soal Klasifikasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (554,111,'D','Pilihan D untuk soal Klasifikasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (555,111,'E','Pilihan E untuk soal Klasifikasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (556,112,'A','Pilihan A untuk soal Indikator',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (557,112,'B','Pilihan B untuk soal Indikator',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (558,112,'C','Pilihan C untuk soal Indikator',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (559,112,'D','Pilihan D untuk soal Indikator',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (560,112,'E','Pilihan E untuk soal Indikator',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (561,113,'A','Pilihan A untuk soal Indikator',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (562,113,'B','Pilihan B untuk soal Indikator',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (563,113,'C','Pilihan C untuk soal Indikator',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (564,113,'D','Pilihan D untuk soal Indikator',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (565,113,'E','Pilihan E untuk soal Indikator',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (566,114,'A','Pilihan A untuk soal Indikator',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (567,114,'B','Pilihan B untuk soal Indikator',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (568,114,'C','Pilihan C untuk soal Indikator',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (569,114,'D','Pilihan D untuk soal Indikator',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (570,114,'E','Pilihan E untuk soal Indikator',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (571,115,'A','Pilihan A untuk soal Teori',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (572,115,'B','Pilihan B untuk soal Teori',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (573,115,'C','Pilihan C untuk soal Teori',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (574,115,'D','Pilihan D untuk soal Teori',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (575,115,'E','Pilihan E untuk soal Teori',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (576,116,'A','Pilihan A untuk soal Teori',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (577,116,'B','Pilihan B untuk soal Teori',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (578,116,'C','Pilihan C untuk soal Teori',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (579,116,'D','Pilihan D untuk soal Teori',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (580,116,'E','Pilihan E untuk soal Teori',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (581,117,'A','Pilihan A untuk soal Teori',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (582,117,'B','Pilihan B untuk soal Teori',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (583,117,'C','Pilihan C untuk soal Teori',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (584,117,'D','Pilihan D untuk soal Teori',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (585,117,'E','Pilihan E untuk soal Teori',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (586,118,'A','Pilihan A untuk soal Faktor Pendorong',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (587,118,'B','Pilihan B untuk soal Faktor Pendorong',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (588,118,'C','Pilihan C untuk soal Faktor Pendorong',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (589,118,'D','Pilihan D untuk soal Faktor Pendorong',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (590,118,'E','Pilihan E untuk soal Faktor Pendorong',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (591,119,'A','Pilihan A untuk soal Faktor Pendorong',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (592,119,'B','Pilihan B untuk soal Faktor Pendorong',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (593,119,'C','Pilihan C untuk soal Faktor Pendorong',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (594,119,'D','Pilihan D untuk soal Faktor Pendorong',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (595,119,'E','Pilihan E untuk soal Faktor Pendorong',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (596,120,'A','Pilihan A untuk soal Faktor Pendorong',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (597,120,'B','Pilihan B untuk soal Faktor Pendorong',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (598,120,'C','Pilihan C untuk soal Faktor Pendorong',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (599,120,'D','Pilihan D untuk soal Faktor Pendorong',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (600,120,'E','Pilihan E untuk soal Faktor Pendorong',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (601,121,'A','Pilihan A untuk soal Bentuk',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (602,121,'B','Pilihan B untuk soal Bentuk',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (603,121,'C','Pilihan C untuk soal Bentuk',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (604,121,'D','Pilihan D untuk soal Bentuk',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (605,121,'E','Pilihan E untuk soal Bentuk',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (606,122,'A','Pilihan A untuk soal Bentuk',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (607,122,'B','Pilihan B untuk soal Bentuk',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (608,122,'C','Pilihan C untuk soal Bentuk',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (609,122,'D','Pilihan D untuk soal Bentuk',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (610,122,'E','Pilihan E untuk soal Bentuk',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (611,123,'A','Pilihan A untuk soal Bentuk',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (612,123,'B','Pilihan B untuk soal Bentuk',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (613,123,'C','Pilihan C untuk soal Bentuk',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (614,123,'D','Pilihan D untuk soal Bentuk',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (615,123,'E','Pilihan E untuk soal Bentuk',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (616,124,'A','Pilihan A untuk soal Dampak',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (617,124,'B','Pilihan B untuk soal Dampak',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (618,124,'C','Pilihan C untuk soal Dampak',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (619,124,'D','Pilihan D untuk soal Dampak',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (620,124,'E','Pilihan E untuk soal Dampak',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (621,125,'A','Pilihan A untuk soal Dampak',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (622,125,'B','Pilihan B untuk soal Dampak',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (623,125,'C','Pilihan C untuk soal Dampak',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (624,125,'D','Pilihan D untuk soal Dampak',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (625,125,'E','Pilihan E untuk soal Dampak',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (626,126,'A','Pilihan A untuk soal Dampak',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (627,126,'B','Pilihan B untuk soal Dampak',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (628,126,'C','Pilihan C untuk soal Dampak',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (629,126,'D','Pilihan D untuk soal Dampak',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (630,126,'E','Pilihan E untuk soal Dampak',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (631,127,'A','Pilihan A untuk soal Latar Belakang',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (632,127,'B','Pilihan B untuk soal Latar Belakang',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (633,127,'C','Pilihan C untuk soal Latar Belakang',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (634,127,'D','Pilihan D untuk soal Latar Belakang',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (635,127,'E','Pilihan E untuk soal Latar Belakang',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (636,128,'A','Pilihan A untuk soal Latar Belakang',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (637,128,'B','Pilihan B untuk soal Latar Belakang',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (638,128,'C','Pilihan C untuk soal Latar Belakang',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (639,128,'D','Pilihan D untuk soal Latar Belakang',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (640,128,'E','Pilihan E untuk soal Latar Belakang',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (641,129,'A','Pilihan A untuk soal Latar Belakang',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (642,129,'B','Pilihan B untuk soal Latar Belakang',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (643,129,'C','Pilihan C untuk soal Latar Belakang',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (644,129,'D','Pilihan D untuk soal Latar Belakang',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (645,129,'E','Pilihan E untuk soal Latar Belakang',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (646,130,'A','Pilihan A untuk soal Dinamika',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (647,130,'B','Pilihan B untuk soal Dinamika',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (648,130,'C','Pilihan C untuk soal Dinamika',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (649,130,'D','Pilihan D untuk soal Dinamika',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (650,130,'E','Pilihan E untuk soal Dinamika',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (651,131,'A','Pilihan A untuk soal Dinamika',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (652,131,'B','Pilihan B untuk soal Dinamika',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (653,131,'C','Pilihan C untuk soal Dinamika',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (654,131,'D','Pilihan D untuk soal Dinamika',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (655,131,'E','Pilihan E untuk soal Dinamika',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (656,132,'A','Pilihan A untuk soal Dinamika',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (657,132,'B','Pilihan B untuk soal Dinamika',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (658,132,'C','Pilihan C untuk soal Dinamika',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (659,132,'D','Pilihan D untuk soal Dinamika',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (660,132,'E','Pilihan E untuk soal Dinamika',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (661,133,'A','Pilihan A untuk soal Pembangunan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (662,133,'B','Pilihan B untuk soal Pembangunan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (663,133,'C','Pilihan C untuk soal Pembangunan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (664,133,'D','Pilihan D untuk soal Pembangunan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (665,133,'E','Pilihan E untuk soal Pembangunan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (666,134,'A','Pilihan A untuk soal Pembangunan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (667,134,'B','Pilihan B untuk soal Pembangunan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (668,134,'C','Pilihan C untuk soal Pembangunan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (669,134,'D','Pilihan D untuk soal Pembangunan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (670,134,'E','Pilihan E untuk soal Pembangunan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (671,135,'A','Pilihan A untuk soal Pembangunan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (672,135,'B','Pilihan B untuk soal Pembangunan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (673,135,'C','Pilihan C untuk soal Pembangunan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (674,135,'D','Pilihan D untuk soal Pembangunan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (675,135,'E','Pilihan E untuk soal Pembangunan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (676,136,'A','Pilihan A untuk soal Kebijakan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (677,136,'B','Pilihan B untuk soal Kebijakan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (678,136,'C','Pilihan C untuk soal Kebijakan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (679,136,'D','Pilihan D untuk soal Kebijakan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (680,136,'E','Pilihan E untuk soal Kebijakan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (681,137,'A','Pilihan A untuk soal Kebijakan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (682,137,'B','Pilihan B untuk soal Kebijakan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (683,137,'C','Pilihan C untuk soal Kebijakan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (684,137,'D','Pilihan D untuk soal Kebijakan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (685,137,'E','Pilihan E untuk soal Kebijakan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (686,138,'A','Pilihan A untuk soal Kebijakan',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (687,138,'B','Pilihan B untuk soal Kebijakan',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (688,138,'C','Pilihan C untuk soal Kebijakan',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (689,138,'D','Pilihan D untuk soal Kebijakan',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (690,138,'E','Pilihan E untuk soal Kebijakan',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (691,139,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (692,139,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (693,139,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (694,139,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (695,139,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (696,140,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (697,140,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (698,140,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (699,140,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (700,140,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (701,141,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (702,141,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (703,141,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (704,141,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (705,141,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (706,142,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (707,142,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (708,142,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (709,142,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (710,142,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (711,143,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (712,143,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (713,143,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (714,143,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (715,143,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (716,144,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (717,144,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (718,144,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (719,144,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (720,144,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (721,145,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (722,145,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (723,145,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (724,145,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (725,145,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (726,146,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (727,146,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (728,146,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (729,146,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (730,146,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (731,147,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (732,147,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (733,147,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (734,147,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (735,147,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (736,148,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (737,148,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (738,148,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (739,148,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (740,148,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (741,149,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (742,149,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (743,149,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (744,149,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (745,149,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (746,150,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (747,150,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (748,150,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (749,150,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (750,150,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (751,151,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (752,151,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (753,151,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (754,151,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (755,151,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (756,152,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (757,152,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (758,152,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (759,152,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (760,152,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (761,153,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (762,153,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (763,153,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (764,153,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (765,153,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (766,154,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (767,154,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (768,154,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (769,154,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (770,154,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (771,155,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (772,155,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (773,155,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (774,155,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (775,155,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (776,156,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (777,156,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (778,156,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (779,156,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (780,156,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (781,157,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (782,157,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (783,157,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (784,157,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (785,157,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (786,158,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (787,158,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (788,158,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (789,158,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (790,158,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (791,159,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (792,159,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (793,159,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (794,159,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (795,159,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (796,160,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (797,160,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (798,160,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (799,160,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (800,160,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (801,161,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (802,161,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (803,161,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (804,161,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (805,161,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (806,162,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (807,162,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (808,162,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (809,162,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (810,162,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (811,163,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (812,163,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (813,163,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (814,163,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (815,163,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (816,164,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (817,164,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (818,164,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (819,164,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (820,164,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (821,165,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (822,165,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (823,165,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (824,165,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (825,165,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (826,166,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (827,166,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (828,166,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (829,166,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (830,166,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (831,167,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (832,167,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (833,167,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (834,167,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (835,167,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (836,168,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (837,168,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (838,168,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (839,168,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (840,168,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (841,169,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (842,169,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (843,169,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (844,169,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (845,169,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (846,170,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (847,170,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (848,170,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (849,170,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (850,170,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (851,171,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (852,171,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (853,171,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (854,171,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (855,171,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (856,172,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (857,172,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (858,172,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (859,172,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (860,172,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (861,173,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (862,173,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (863,173,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (864,173,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (865,173,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (866,174,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (867,174,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (868,174,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (869,174,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (870,174,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (871,175,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (872,175,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (873,175,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (874,175,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (875,175,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (876,176,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (877,176,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (878,176,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (879,176,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (880,176,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (881,177,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (882,177,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (883,177,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (884,177,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (885,177,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (886,178,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (887,178,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (888,178,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (889,178,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (890,178,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (891,179,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (892,179,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (893,179,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (894,179,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (895,179,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (896,180,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (897,180,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (898,180,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (899,180,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (900,180,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (901,181,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (902,181,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (903,181,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (904,181,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (905,181,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (906,182,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (907,182,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (908,182,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (909,182,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (910,182,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (911,183,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (912,183,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (913,183,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (914,183,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (915,183,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (916,184,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (917,184,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (918,184,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (919,184,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (920,184,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (921,185,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (922,185,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (923,185,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (924,185,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (925,185,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (926,186,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (927,186,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (928,186,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (929,186,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (930,186,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (931,187,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (932,187,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (933,187,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (934,187,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (935,187,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (936,188,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (937,188,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (938,188,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (939,188,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (940,188,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (941,189,'A','Pilihan A untuk soal Pengertian',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (942,189,'B','Pilihan B untuk soal Pengertian',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (943,189,'C','Pilihan C untuk soal Pengertian',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (944,189,'D','Pilihan D untuk soal Pengertian',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (945,189,'E','Pilihan E untuk soal Pengertian',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (946,190,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (947,190,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (948,190,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (949,190,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (950,190,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (951,191,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (952,191,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (953,191,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (954,191,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (955,191,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (956,192,'A','Pilihan A untuk soal Ruang Lingkup',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (957,192,'B','Pilihan B untuk soal Ruang Lingkup',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (958,192,'C','Pilihan C untuk soal Ruang Lingkup',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (959,192,'D','Pilihan D untuk soal Ruang Lingkup',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (960,192,'E','Pilihan E untuk soal Ruang Lingkup',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (961,193,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (962,193,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (963,193,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (964,193,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (965,193,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (966,194,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (967,194,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (968,194,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (969,194,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (970,194,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (971,195,'A','Pilihan A untuk soal Studi Kasus',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (972,195,'B','Pilihan B untuk soal Studi Kasus',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (973,195,'C','Pilihan C untuk soal Studi Kasus',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (974,195,'D','Pilihan D untuk soal Studi Kasus',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (975,195,'E','Pilihan E untuk soal Studi Kasus',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (976,196,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (977,196,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (978,196,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (979,196,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (980,196,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (981,197,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (982,197,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (983,197,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (984,197,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (985,197,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (986,198,'A','Pilihan A untuk soal Evaluasi',1,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (987,198,'B','Pilihan B untuk soal Evaluasi',0,2,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (988,198,'C','Pilihan C untuk soal Evaluasi',0,3,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (989,198,'D','Pilihan D untuk soal Evaluasi',0,4,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `question_options` VALUES (990,198,'E','Pilihan E untuk soal Evaluasi',0,5,'2026-10-02 21:41:19','2026-10-02 21:41:19');
/*!40000 ALTER TABLE `question_options` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `question_set_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_set_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_set_id` bigint(20) unsigned NOT NULL,
  `question_id` bigint(20) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `qs_question_uq` (`question_set_id`,`question_id`),
  KEY `question_set_questions_question_id_foreign` (`question_id`),
  CONSTRAINT `question_set_questions_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `question_set_questions_question_set_id_foreign` FOREIGN KEY (`question_set_id`) REFERENCES `question_sets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `question_set_questions` WRITE;
/*!40000 ALTER TABLE `question_set_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `question_set_questions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `question_sets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_sets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `grade` varchar(8) NOT NULL DEFAULT 'XII',
  `purpose` enum('practice','tryout','tka','simulation','remedial') NOT NULL DEFAULT 'practice',
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `duration_minutes` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `question_sets_subject_id_foreign` (`subject_id`),
  KEY `question_sets_created_by_foreign` (`created_by`),
  CONSTRAINT `question_sets_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `question_sets_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `question_sets` WRITE;
/*!40000 ALTER TABLE `question_sets` DISABLE KEYS */;
/*!40000 ALTER TABLE `question_sets` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `question_tag_relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_tag_relations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) unsigned NOT NULL,
  `tag_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_tag_relations_question_id_tag_id_unique` (`question_id`,`tag_id`),
  KEY `question_tag_relations_tag_id_foreign` (`tag_id`),
  CONSTRAINT `question_tag_relations_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `question_tag_relations_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `question_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `question_tag_relations` WRITE;
/*!40000 ALTER TABLE `question_tag_relations` DISABLE KEYS */;
/*!40000 ALTER TABLE `question_tag_relations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `question_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_tags_name_unique` (`name`),
  UNIQUE KEY `question_tags_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `question_tags` WRITE;
/*!40000 ALTER TABLE `question_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `question_tags` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` bigint(20) unsigned NOT NULL,
  `chapter_id` bigint(20) unsigned DEFAULT NULL,
  `topic_id` bigint(20) unsigned DEFAULT NULL,
  `curriculum_id` bigint(20) unsigned DEFAULT NULL,
  `grade` varchar(8) NOT NULL DEFAULT 'XII',
  `type` enum('multiple_choice','true_false','short_answer','essay','complex_multiple_choice','matching','numeric') NOT NULL DEFAULT 'multiple_choice',
  `difficulty` enum('easy','medium','hard','mixed') NOT NULL DEFAULT 'medium',
  `cognitive_level` enum('C1','C2','C3','C4','C5','C6') DEFAULT NULL,
  `estimated_time` int(10) unsigned DEFAULT NULL,
  `question_text` longtext NOT NULL,
  `explanation` longtext DEFAULT NULL,
  `answer_key` varchar(500) DEFAULT NULL,
  `source` enum('official','curated','teacher_created','ai_generated','imported','sample') NOT NULL DEFAULT 'curated',
  `status` enum('pending','validated','published','rejected','archived','draft') NOT NULL DEFAULT 'published',
  `content_hash` char(64) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `content_version` bigint(20) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `questions_content_hash_unique` (`content_hash`),
  KEY `questions_chapter_id_foreign` (`chapter_id`),
  KEY `questions_curriculum_id_foreign` (`curriculum_id`),
  KEY `questions_created_by_foreign` (`created_by`),
  KEY `q_filter_idx` (`subject_id`,`grade`,`difficulty`,`status`),
  KEY `questions_topic_id_status_index` (`topic_id`,`status`),
  CONSTRAINT `questions_chapter_id_foreign` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `questions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `questions_curriculum_id_foreign` FOREIGN KEY (`curriculum_id`) REFERENCES `curriculums` (`id`) ON DELETE SET NULL,
  CONSTRAINT `questions_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `questions_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=199 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES (1,1,1,1,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Integral Tak Tentu (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','859f0e4870bb6a663ed1f56fc96e92d36a0bc1be7d06ca34d3823c6e9d58e4a7',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (2,1,1,1,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Integral Tak Tentu (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','2f649e064a9f0f4ac20eea531c5a4efa7b98c3751b95e112e91d791a98b62367',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (3,1,1,1,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Integral Tak Tentu (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','10683250be3ebd0926c524408820dace02fdda266d9a0f7895990a13fa9235b5',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (4,1,1,2,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Integral Tertentu (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','fdbbdac332dc42b4357f1849ef5d7b8f0287ea4c4ed71033d2ce7983cfddbf88',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (5,1,1,2,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Integral Tertentu (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','b0f51331e695f4f825def77d4f145114913a109e247ba7010de26ee6a69e1161',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (6,1,1,2,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Integral Tertentu (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','6fa58132e4fe3414167011db1f2acfdf8515f9a606abcf6f43b8e7bb080ea443',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (7,1,1,3,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Aplikasi Integral (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','158795521e645d674aad899827bbef3db5ffaeee99ed17aeae36926e5fdda9b5',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (8,1,1,3,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Aplikasi Integral (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','bcb92d712198f0a2b38f21a2fa7490fdfbc1ec4b8951524cdb9f366e1b145481',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (9,1,1,3,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Aplikasi Integral (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','76fa6c2db8a754040293791f4d6b1cc9670b4ea547db7b81b2af5125d0907f17',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (10,1,2,4,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Aturan Turunan (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','7fa95086ec7dbba4d9a2980e42691e164bbc36caf3b1393745895418c37c272d',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (11,1,2,4,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Aturan Turunan (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','713caa99b70aeddbde7cdd2975112a0140e4fc63a42180d2a67731d8361a05a2',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (12,1,2,4,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Aturan Turunan (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','bb68d795dd23245df08a20549e2b11c0769167f8443bd9bdcffa43ff30c8654b',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (13,1,2,5,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Aplikasi Turunan (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','050efa2ab81ad533d76a7e90c152a73e812aa66d45a84eab7d5a9d43c8dde34f',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (14,1,2,5,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Aplikasi Turunan (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','844e74781fa435dc21ff872eee1082da7f1bead9e6caa5f5c0c9cca6b77b57ad',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (15,1,2,5,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Aplikasi Turunan (Matematika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','391f9ed3a6d807a38282be9318a6c5193b3ff643a8861bafb929f9633cc0de15',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (16,2,3,6,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Integral Tak Tentu (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','530802be30007c861d13ccd3990fb71dcef8f494d25afd90827ae32aebceb34c',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (17,2,3,6,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Integral Tak Tentu (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','a0ac6b11e40bc47518c78f0a0d100c028d6daf4f5bd74b9212910b86d518b61d',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (18,2,3,6,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Integral Tak Tentu (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','47cf0587b3ca9f65f3875ab7280cfd2ddfb3102b41d767e6becba9150523ba4f',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (19,2,3,7,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Integral Tertentu (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','fd0315c5a910472cdba719d3c95daf78a0b5587327720bcff21190b43e1bba49',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (20,2,3,7,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Integral Tertentu (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','a785879b108e91b7b16dd8eb93b5957d7caf07b48ba019525520d9c4b7d082cb',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (21,2,3,7,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Integral Tertentu (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','138c54c0d3c0c28308b12ed75ec51e4b2d49ecd64fa2a313b328b91cd389f35f',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (22,2,3,8,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Aplikasi Integral (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','cdd1b401f60f783fd51e746e7279378734d1fe021e484470207ed47c44cc9770',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (23,2,3,8,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Aplikasi Integral (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','21768d6230cc87214114bbdf6ec3f4ef85d33b0317c9c3ba9dfe0879b03d4e92',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (24,2,3,8,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Aplikasi Integral (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','e96d4768840094e8b5da13b9295c76797e6ca81d62f1a9e5d102997d53fcf14b',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (25,2,4,9,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Aturan Turunan (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','04e63d572e02ffeff3eb2abca328707ebfa539fe038ac84664f061d63cffeb1d',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (26,2,4,9,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Aturan Turunan (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','8bf500a4446747e802f4cf49396f97dcde746a3541ee633774008f14a03288d1',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (27,2,4,9,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Aturan Turunan (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','cc285fe8b874f6b1772cf87b36bdfc3c8c047217d01b2c3791cce40008e64565',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (28,2,4,10,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Aplikasi Turunan (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','b2e5f856a86795f4505e2e18066dbbfd59c54adbca0e9a6887f4fc59b01542f4',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (29,2,4,10,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Aplikasi Turunan (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','d507cf9502b0700515340020dafbc73a6cb2ac9e8159242d6cbfa57d6b30e6e2',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (30,2,4,10,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Aplikasi Turunan (Matematika Tingkat Lanjut): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','e719517edf2be7b6467940569cb388e11dcbcdcb7348d5df836dc383e1a99914',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (31,3,5,11,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Struktur Editorial (Bahasa Indonesia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','dcfaa5dfe977adcd967b566a3e2922ff8e4a0f0baaf1a32902a720085f00102b',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (32,3,5,11,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Struktur Editorial (Bahasa Indonesia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','117725e6bc1d9b0f3fe68e77c40e44cb1dd803966d521e8ed91079b9fbbc9ab4',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (33,3,5,11,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Struktur Editorial (Bahasa Indonesia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','82176dca1931214b458a47f1bff31f564214d83ebb42d45a0d4c4d3a72ee676c',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (34,3,5,12,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Kebahasaan (Bahasa Indonesia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','d7770cc3516c0671e67157798181bff5a7d41bd3c3fd18166b8c7bd73e365198',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (35,3,5,12,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Kebahasaan (Bahasa Indonesia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','9ce096dcf22b1f896739674fe83551d39a2925b2ba3e33c9c29fb2d809b44628',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (36,3,5,12,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Kebahasaan (Bahasa Indonesia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','701e62b960e57676da6f839c1f7c12c45b025a1643e97c954fc1bd944437630c',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (37,3,6,13,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Unsur Puisi (Bahasa Indonesia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','4b884f9c7d2cc6e7b1903ec6e53c0ba9868953fc40dd5c8bc73be926d6b3af34',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (38,3,6,13,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Unsur Puisi (Bahasa Indonesia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','0ce6ab8f34052a6b22f03b47a702e9445f0a5cd351a649ae913f814217d1e317',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (39,3,6,13,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Unsur Puisi (Bahasa Indonesia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','a071dcce57d11476eb41c09e5d7ea98512da1dd164df0a9276bf65a6b48e72e6',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (40,3,6,14,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Apresiasi Puisi (Bahasa Indonesia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','0e915b90770ed5cf15a491c7fc3398f0263683aa9d161b26e707870dee042903',NULL,1,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `questions` VALUES (41,3,6,14,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Apresiasi Puisi (Bahasa Indonesia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','7c308c3040c58a8bc73fa423b3eb5980d928003f8e6003bfc2de63bf96cb91e2',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (42,3,6,14,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Apresiasi Puisi (Bahasa Indonesia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','e0d8d1406ba602a5b64823b290797e9837a0f7f957d8fa65afd7d211df27b8df',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (43,4,7,15,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Generic Structure (Bahasa Inggris): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','9a2bb0f8217a6704d13b6aa753652681f3a08dd35d5ca8a14269ceb7925a05b7',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (44,4,7,15,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Generic Structure (Bahasa Inggris): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','0ea54321284783ffdb801ee8995bd8804d392a00757a2fcd5302dc0cecbcab93',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (45,4,7,15,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Generic Structure (Bahasa Inggris): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','055f1b595fa493e09bda81a54366c3324b131c9f3d0eec556431e629c554a1b2',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (46,4,7,16,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Language Features (Bahasa Inggris): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','60a68d240f7908236b7ed673392a80ec32608cb59cf575665addbbe8fd9d81ae',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (47,4,7,16,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Language Features (Bahasa Inggris): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','ba4201f02b00994dba81e8ab2189d0c7a4cbd2f4320c0ad2409959550ccf6839',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (48,4,7,16,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Language Features (Bahasa Inggris): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','9b3227571bf0dfd344e1238e15d2839129f7ca466fce405accf300f1f3dd00b6',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (49,4,8,17,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Elements (Bahasa Inggris): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','9fb0b6a2e6a53f74b89f96ac4646699d6ef1753a4a8b91de600644bce420b128',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (50,4,8,17,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Elements (Bahasa Inggris): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','daa412570afc69364ea5f79b6f5adcd85139b21ec17118ba06f487800456a5e5',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (51,4,8,17,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Elements (Bahasa Inggris): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','d23b11e680afc4248675a1ec73f1bff9dabf44070907cd43ee4fbac14cea13b0',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (52,4,8,18,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Comprehension (Bahasa Inggris): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','aa5d832ced4b7e8eee9cd5df77eaaf131d365ee79c4b2ed5b9015e4ff49bf9a3',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (53,4,8,18,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Comprehension (Bahasa Inggris): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','199f857f2b9cca5207e2860351d3f7699a9f4e7f317ff3455708562a18ff96d0',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (54,4,8,18,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Comprehension (Bahasa Inggris): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','6fdcaee31dcfede62f590253f999274379ea919a882f6ba3bdc8ea6a8770cf25',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (55,5,9,19,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Hukum Ohm (Fisika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','775ea6335b8133ca3465fbbb109155f12edae37cbcba84bd494320f8027ad3a2',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (56,5,9,19,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Hukum Ohm (Fisika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','4bf970927ee945b725b689eb46980baadf734f17e5018b610201c3ad73a6202a',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (57,5,9,19,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Hukum Ohm (Fisika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','5afca3ff4c8d9740624682d7008d8340c5e2d6172827eb740a0d22aee807492a',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (58,5,9,20,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Energi & Daya Listrik (Fisika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','3f1403f023e6b4e09e5714894de7cab49197906093975377fa6ced438cfafc51',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (59,5,9,20,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Energi & Daya Listrik (Fisika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','d1ac1f5d5890c5e1f4a7562a0f5be8b88972dd429db1a29db564deb4794754f3',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (60,5,9,20,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Energi & Daya Listrik (Fisika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','ed0f748c7d4830301ead032d65b6cdd771e232afcf339a115f6be36fe5e00799',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (61,5,10,21,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Fluks (Fisika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','13756b4a15d29a7c295ec38abba28d90eeb896ed98b854057c882c8256ddc1ca',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (62,5,10,21,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Fluks (Fisika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','b07f1827196df9651dca6a651c690c2d84ac797c783044e6448ec3669dd00729',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (63,5,10,21,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Fluks (Fisika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','d389191fba945dbbc1b0399fa56e5edc4b3374adf126871022288776cf5465ee',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (64,5,10,22,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Hukum Faraday (Fisika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','37fae696f7d03970575b3b108dff7a3237c0de17539fd8b5ae8704724446a15a',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (65,5,10,22,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Hukum Faraday (Fisika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','bc14a394644e43765a2e6a67d97670ac3957ea0f894ebc8941cc9287f0fbb2ca',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (66,5,10,22,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Hukum Faraday (Fisika): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','ac744c9f1b0c03891cbe446bedc66af51fac3389cd893b27a963381500f011d9',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (67,6,11,23,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Orde Reaksi (Kimia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','2591ab09a27771fa6ddc49774713184737343d040da1d160e48217b176cdd623',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (68,6,11,23,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Orde Reaksi (Kimia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','0f54a06303a70ae8c2a76433f8269bab6ec0d0deda85a2483fc5fe12cc6499d9',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (69,6,11,23,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Orde Reaksi (Kimia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','60f9ad202439b6ef91363994b726da1bc652f3606a494a1518bc3f7342a872ea',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (70,6,11,24,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Teori Tumbukan (Kimia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','ddde6644cfb22fb0deba2d11f434f39b28cdacb56881654775906151ae34aaef',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (71,6,11,24,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Teori Tumbukan (Kimia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','3a4b7f5ddd96ebd9fdf3a018139a5e4bcf45fd74009f05176327313a2f59e0c1',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (72,6,11,24,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Teori Tumbukan (Kimia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','b3f4493e912fefcfa1dc788b7e3b1ef79c087421c7be7b1946b87f8d7148c851',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (73,6,12,25,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Geseran Kesetimbangan (Kimia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','4d142649436285a5a3897227695cee602320149c483c1793a990909666a7ab84',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (74,6,12,25,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Geseran Kesetimbangan (Kimia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','df6998f5ec3df3cbcaf6238374adab2c192bd81dcad295d046a7c66456a8b3c5',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (75,6,12,25,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Geseran Kesetimbangan (Kimia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','46764ba96397e48588ac6cca33d2721b0725e864c541b75ff521280589c5a971',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (76,6,12,26,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Hukum Henry (Kimia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','1a43b11d864e6a949a93eeac82db19ec1ce246d7b7ef223111b612eb063aa523',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (77,6,12,26,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Hukum Henry (Kimia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','ad991cb8eb641cbcd54e87d70940012b1f49e42304035c1bd48c0f09b03abe7f',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (78,6,12,26,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Hukum Henry (Kimia): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','2ff3f27c83c998913750f0de4525419f037f3d80aa190b5551f395504caaad95',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (79,7,13,27,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Faktor Internal (Biologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','96f0d2f1f26e28b0db46c2a688f18cc344567660e740dbcadac1f499e1bca666',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (80,7,13,27,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Faktor Internal (Biologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','e541a6abf1806e7bc8099aae5fe28d56b825660f913a28fde9a3fe7010c909d9',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (81,7,13,27,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Faktor Internal (Biologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','d72d6343db459b03d351732a7b567e4d275aa5d8889ceb92394fda516940e311',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (82,7,13,28,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Faktor Eksternal (Biologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','e3d20789ad6056e4524c57f992fdb912781835a1569f75d0c97cb52f9abe3ef1',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (83,7,13,28,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Faktor Eksternal (Biologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','361ff07358d1fd92a7ed550f1d87697db16cc61b1d9b01dc0cfe9d4ff8fe967a',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (84,7,13,28,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Faktor Eksternal (Biologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','b204a8a559894e1fac72d248fbccd4fb00aabdb66cb3b137f3e444b13d4beddb',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (85,7,14,29,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Enzim (Biologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','5b4df60bed35dc29cabc4241c085254e440246524f487466d9c22c27ff5d9e8c',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (86,7,14,29,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Enzim (Biologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','4ce619fe95f8392b87b2632603f38baf6b7584a7dff3c784693623052c282111',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (87,7,14,29,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Enzim (Biologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','64785fe2919ad875ef6f0e3fc67e150857907e74c4cfbbc2f4c98b0f33f49b76',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (88,7,14,30,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Katabolisme Karbohidrat (Biologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','a15d2191016609b64319a27285b6823fb9fa15c99805939ace81c991493d6234',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (89,7,14,30,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Katabolisme Karbohidrat (Biologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','71d08c6ae101897fa627029958d9b669f2e2636f4926a178592a0fb9b2048503',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (90,7,14,30,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Katabolisme Karbohidrat (Biologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','d8b9cdcdd3c0210cf3d2009179cc51abee4bb1132cff71d41e58447e6f1344f1',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (91,8,15,31,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Teori Pembangunan (Ekonomi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','10fb84b888c4b4c4645ba037f2b6138688690f23d187f592624a67cfc9d9547a',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (92,8,15,31,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Teori Pembangunan (Ekonomi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','ded5d94870aec4169bce6287c0e685559742189aba5af6fb58b1548ce8d489e9',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (93,8,15,31,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Teori Pembangunan (Ekonomi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','e1ac50e33557aaa7f922a2519eaa928a3f5e9301ada7286486a41aba378930b5',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (94,8,15,32,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Permasalahan (Ekonomi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','2ff55321a9db903a9be59cb2439f1282964bdfb1379c790721d7c5cab8069a7a',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (95,8,15,32,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Permasalahan (Ekonomi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','fd2a5c05a827b6def5795539f07f9a53592ac24d230fc57be0a03266ad4dc730',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (96,8,15,32,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Permasalahan (Ekonomi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','5d4a02f6b465f49e054e57d8dab1ef5abd56a92b45fd0b060f0b5fe2744defd5',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (97,8,16,33,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang APBN (Ekonomi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','126073aaf5910cb512c1d6cd98a9ac720951073fe83b80ecbbd5e33b05c948a9',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (98,8,16,33,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang APBN (Ekonomi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','4299be83683524111d544b5f17c5be0874fb42aa93986190abb752835c9548c8',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (99,8,16,33,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang APBN (Ekonomi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','b46036c8a74401a29d7d1ff9ba842a91c61a15c5a3bd44eb1d6d38fe1ac0449a',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (100,8,16,34,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Policy Fiscal (Ekonomi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','af27928235911b50e59534b3a7dddad523016077f7189c14e31ea582593c33ec',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (101,8,16,34,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Policy Fiscal (Ekonomi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','6a9749add8dc7b2c326cae01f090059bc4e7e32afc0f7e1b5ae953dffd5b99e4',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (102,8,16,34,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Policy Fiscal (Ekonomi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','a973d8087805327cf3a6d21bee465bfeb2c49a78ec83e4f5bce338deffb89ce8',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (103,9,17,35,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Konsep (Geografi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','5b5cf4ee8ea80e6f56ab3a1f9544f55506f3f6c08d7cb226e3fc214154ece9e3',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (104,9,17,35,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Konsep (Geografi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','35bc8f85514e604dad5e3c45a2be65dee5e974b9747f1fcd509ed5592f72d459',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (105,9,17,35,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Konsep (Geografi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','ae962b5ab068e3860cb8da3aad0b3b446a37863e44ad60f0854310b6faaf5595',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (106,9,17,36,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Indikator (Geografi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','72247db4b1da1b80031e26a2ebfc65892e21d43c6185ecb47686c0adc616f660',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (107,9,17,36,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Indikator (Geografi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','1883dbcb16b7bc98582eb6c1348da933ef3197aad0c0bdc8937a75d780228742',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (108,9,17,36,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Indikator (Geografi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','4ae31dd351c5487b18af275f3851a50115171bdd2186f8d31b6a9d59c62f60fc',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (109,9,18,37,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Klasifikasi (Geografi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','d8b061b28006db4ee2b9fa9a9b27e54d37e398e53a252b13c7c179c4d2b5cc3e',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (110,9,18,37,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Klasifikasi (Geografi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','5df16c251c9d43a1ebb03c6267e6bfe51348bcc5f87fe0e25ff6b947708b9a9d',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (111,9,18,37,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Klasifikasi (Geografi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','382039c84b717dbb382e35b71351f94c311898157165c0ca1d650012bbef2e07',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (112,9,18,38,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Indikator (Geografi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','8452360b3481516968de4906aaa1984ffb2827f3a340619251d7bac13c373a34',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (113,9,18,38,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Indikator (Geografi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','567aae059156bb02f2f8ffe4c8009ce47ba1eaa29a8ae091a23b9161a46f3ac5',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (114,9,18,38,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Indikator (Geografi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','3f12fa5a5c68c47f575e999bdf2115821e85ccb0cf3460fed00ae232272f553c',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (115,10,19,39,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Teori (Sosiologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','704fa213d8840169dc7179a11b4a6555ed200e92af1986ac956c94f253b466f4',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (116,10,19,39,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Teori (Sosiologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','d1c39e4984bec42242f90e55248b1f705f06d4401fdc3e697d8197ce6f119afa',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (117,10,19,39,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Teori (Sosiologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','eff388875f96250cae95b0ea73a8c2001d550b74ba0c3d4100be8f75beabb378',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (118,10,19,40,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Faktor Pendorong (Sosiologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','c91e71f87c6d3807224798c5866f6d7d38e877ad95c99027f292c6e0c716d025',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (119,10,19,40,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Faktor Pendorong (Sosiologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','5af07e4b1fadba32cf627a690c8c2c3d98225ab6000c55a380065c1457f18a19',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (120,10,19,40,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Faktor Pendorong (Sosiologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','653bc0fe3b45e0c311047054ca0ebf0982259190723fb23809e4a3bcbe23e197',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (121,10,20,41,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Bentuk (Sosiologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','a78280476b4fc0d8b57fe76b22f2cfd36509d49135c608e94b5d9805389df058',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (122,10,20,41,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Bentuk (Sosiologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','24ecbf85c7946f83e05bca0919cfcc8b06f96d79436953e40598b05eeed39487',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (123,10,20,41,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Bentuk (Sosiologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','196dcb5e66c71e884a4070001f1d6116f95c981e9fbfe15364e5c5f721d8d520',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (124,10,20,42,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Dampak (Sosiologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','27d134ab5bea419f1c75e5ca1adee3954f8497b9f375d41f40faefa2abdf60d6',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (125,10,20,42,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Dampak (Sosiologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','14ceb4f4b4987e4a4298a906c3c3883b597c8be8545ea580ef96b3a1a4067dc3',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (126,10,20,42,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Dampak (Sosiologi): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','1d79d499923ebfb5f6067dcb2ed6a2da6c4de0f74645a13125b3bdd282b3189d',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (127,11,21,43,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Latar Belakang (Sejarah): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','c4446b8ea9c9711193899a600f36ef0100b42b1469d59056a939cf7246e81a55',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (128,11,21,43,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Latar Belakang (Sejarah): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','6ec56b6b3107284d5d0b32c08aab669d28d53540d8fa0daa61a2cfd89b104c74',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (129,11,21,43,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Latar Belakang (Sejarah): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','b4569534145e1840fa353f08cfaaa01221e93e87a56185bb2f87085cffcb6bf0',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (130,11,21,44,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Dinamika (Sejarah): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','bbe4a92b540fd422393291125d04d9890a087a79179fd6ed67d39455356e4c33',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (131,11,21,44,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Dinamika (Sejarah): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','c80bd4ba39ed86631016d052caf042f555eb705ac5432715256432f3d65bb108',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (132,11,21,44,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Dinamika (Sejarah): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','05b55c10d6014f7af59ae04eae6d139065e6c5154dd4b26005a81734bd101d3a',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (133,11,22,45,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Pembangunan (Sejarah): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','bc801f2b7538bbce3cfc1cce118897ec79d3c8bff27f3bbc7db09eb2954ea6b8',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (134,11,22,45,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Pembangunan (Sejarah): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','3425190b9f1d9ccd63e0a0cf82eb950b0d1b4b39d20e44b2654749549343f75f',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (135,11,22,45,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Pembangunan (Sejarah): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','08f9ff3ce2c3b39b3310c34fcbe702775ba4a2661c1e87e0f2942fc25bef1de0',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (136,11,22,46,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Kebijakan (Sejarah): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','55b7bcedac8b9edde1c1af92913f2d82a20e579efe01badbf2dc158b81f542db',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (137,11,22,46,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Kebijakan (Sejarah): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','2712d521012d9d03d857b1bf29e9d692516c993315c569fd8064ad0eac6a2c74',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (138,11,22,46,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Kebijakan (Sejarah): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','b11f6776620c98f3606fc5e895d0b37df23a36507e3d5e821758ded6ba50a2c6',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (139,12,23,47,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Pengertian (Fikih): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','fb14a60213733fc54659b2569633a8a4eb14842ad5068c61ff8e11809e5ace3f',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (140,12,23,47,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Pengertian (Fikih): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','9c540cadab113f12225bd7afe6b569f8f9d132b2523a45fc692e3d5cfeaaef7e',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (141,12,23,47,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Pengertian (Fikih): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','2e2741a918aa88e4cffce009791207935235f3649cb9113c3d4e0b41d2e653b9',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (142,12,23,48,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Ruang Lingkup (Fikih): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','bd8b6a21bbaf22d78ce0233d957a57f9671ecc86d35229fa92b32173b1d72aac',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (143,12,23,48,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Ruang Lingkup (Fikih): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','cb3bfc7ef18dad5ca4570e9203cbede503915a751b48751e2891732f438c36de',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (144,12,23,48,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Ruang Lingkup (Fikih): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','1b980855b65d5b39f4192bbec3ee3667460e8b8b7d4494ef75b05f398fa98aba',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (145,12,24,49,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Studi Kasus (Fikih): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','830aa8dd036e3b234675bd4fa91f8144cf0d5c0654b66072fe0e0171b4561fa0',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (146,12,24,49,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Studi Kasus (Fikih): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','900f4b849f599c365bcd5fa2868f560d70e3415fe1db1e5621a9f9cb7ba133e4',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (147,12,24,49,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Studi Kasus (Fikih): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','1acedd27eede186a3988117b6370bd61eb3c2936f4adde758e781520cb2cb928',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (148,12,24,50,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Evaluasi (Fikih): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','a3c34e860cd03ab3f4d5e356b2c086b13363c3d950628f1f80638f5f4c0728e0',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (149,12,24,50,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Evaluasi (Fikih): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','0c02d9158d8c74c7d47d19e7ee344a1d4f97874412fc63bdf9e024efbe223bb3',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (150,12,24,50,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Evaluasi (Fikih): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','c10a2dfa2e2cf422cdae6fa0415a05a578527cb2b698968275eb4d5e6fc7bf79',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (151,13,25,51,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Pengertian (Sejarah Kebudayaan Islam): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','2691232e6acb47be917a65de35fb891ee3de8b50202b7cca9c7cb09180145d13',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (152,13,25,51,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Pengertian (Sejarah Kebudayaan Islam): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','93f8346c42e0cef642fbbb1b9e6752a03938c333c1d42126978ae3967ea44ec1',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (153,13,25,51,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Pengertian (Sejarah Kebudayaan Islam): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','ace801fc270246c798b53d085deb1fcc7d4e925a73fce0e10b3efc3e369bde20',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (154,13,25,52,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Ruang Lingkup (Sejarah Kebudayaan Islam): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','b997a1e04a9fca91152bf8ea315bada0bb300866a9687edf3b50731b314f64cb',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (155,13,25,52,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Ruang Lingkup (Sejarah Kebudayaan Islam): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','64d9399e60b68c8d99f5842c06f9ea59779fdc2a918f622aba8719325011745b',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (156,13,25,52,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Ruang Lingkup (Sejarah Kebudayaan Islam): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','16ec84a3991a6e1aaef1175009ebcf53bfbfb88eedf6445d0ebb3349bd3e29b5',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (157,13,26,53,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Studi Kasus (Sejarah Kebudayaan Islam): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','41f1fcfa26db42324f8a97649323e110b97961e558a237e3df49c7d2b2412c0e',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (158,13,26,53,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Studi Kasus (Sejarah Kebudayaan Islam): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','e560f4f90e4efad1858d31a7a0318b1be619915a687ba2e729c67085e316f4b3',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (159,13,26,53,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Studi Kasus (Sejarah Kebudayaan Islam): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','8e17841e9c7601021f2f7d9f237dd63d62751b32741e46d27e8cc4be4a0cfe41',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (160,13,26,54,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Evaluasi (Sejarah Kebudayaan Islam): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','a96257f46285db1a2c9cc71f15cb0f70afa9a68c6a6247b206c3d4c3f1a5e2c0',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (161,13,26,54,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Evaluasi (Sejarah Kebudayaan Islam): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','d584ddfbf34bb3874d77598f30fff13877cb8b62aac08c27ce08bef650b31e8f',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (162,13,26,54,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Evaluasi (Sejarah Kebudayaan Islam): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','b1ee2e6686ac3a0d0509881e7476742ce9c33164ca277b9f0c3afb18e4df21c9',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (163,14,27,55,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Pengertian (Akidah Akhlak): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','d64f5fc07ab7ed5afba322484e020417cda683446fd5270500cf45efcff14361',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (164,14,27,55,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Pengertian (Akidah Akhlak): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','0ff017d8e07c5f4c53518e0e689f923ee8e40f9f560cd2346fd9c4fa6351be13',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (165,14,27,55,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Pengertian (Akidah Akhlak): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','6f505d75aed9ebefa44ff8e91fec9590f654c2f09c6406e299fee6ea5500aacb',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (166,14,27,56,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Ruang Lingkup (Akidah Akhlak): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','eaf8217dc864bdb5ff80cc64f14d978b09cc6e4f74ef2a1d8eb5ed4206db49b2',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (167,14,27,56,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Ruang Lingkup (Akidah Akhlak): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','29641cf2f3b8313b9ed7e810fdf85f02e9018e6398a6c3c26c3c1400e896c05d',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (168,14,27,56,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Ruang Lingkup (Akidah Akhlak): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','cedab98a4efd645ae81719202a1c5b4a5970c786a61345078fbce5491d77034f',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (169,14,28,57,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Studi Kasus (Akidah Akhlak): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','fd7885b47c1f1698baac5c103908ce2321126e0ff084febd2b876186b9b42648',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (170,14,28,57,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Studi Kasus (Akidah Akhlak): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','29d4f028efcba2b6cee6ba2d6bddf3701ca23839ef65e097cbe0b9c8690b7eb5',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (171,14,28,57,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Studi Kasus (Akidah Akhlak): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','07528a2561349b56c43d9d04f4c9c40b919dc04e686a4126cc9982e1fa30f39e',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (172,14,28,58,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Evaluasi (Akidah Akhlak): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','2bc86c11c6926d6d8e62a7c874d8b85b840f9ceed8f2fcdecd37dbb9dc8a4ab2',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (173,14,28,58,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Evaluasi (Akidah Akhlak): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','ba2d42a411776f84c53dad831dfe4663b5b1ffc378a46f8ff0b78e2d8cdcaf28',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (174,14,28,58,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Evaluasi (Akidah Akhlak): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','9d4d96fc2f8593c93fd165293b521e65e4eae5a1651ec809e27993cec171e11d',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (175,15,29,59,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Pengertian (Al-Qur\'an Hadis): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','e59e5af3b3e95f0dda401086b852126fd3590f44796e1500f57fc2f3b6276b5d',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (176,15,29,59,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Pengertian (Al-Qur\'an Hadis): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','9f37c8fe40fe564121f8ec5fa512497ddfd22ce62356bc70e48ea297a8440cc6',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (177,15,29,59,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Pengertian (Al-Qur\'an Hadis): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','36b05cda9be9a2d440e7b692f2fbd8bd4d7b72ebbee464360a427d906386fd4b',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (178,15,29,60,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Ruang Lingkup (Al-Qur\'an Hadis): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','9603b25cf59746e7bee745ce8fcab09c5214e6822e8b3dca8c09942a8f631c14',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (179,15,29,60,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Ruang Lingkup (Al-Qur\'an Hadis): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','5847a70d32263c7cee5546aea8c13afebd154b9f9b4c8bf32f12b9346c64ddef',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (180,15,29,60,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Ruang Lingkup (Al-Qur\'an Hadis): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','0b31329da99c2e18705945a161bff1410c89c70a38dfa7f643a82dcc162659f6',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (181,15,30,61,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Studi Kasus (Al-Qur\'an Hadis): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','d369a140716a24ef06c6dc10662f128ba495b0721df19edd084dbc2743bb0c70',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (182,15,30,61,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Studi Kasus (Al-Qur\'an Hadis): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','24e9c2a31c54c5b0bcd7e480e36c94737a74c0acb84b9e19859d58e439681832',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (183,15,30,61,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Studi Kasus (Al-Qur\'an Hadis): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','26b9f9d7e6c03615baba7512dbcd05eccae3bed12125cf2d600df2d365515586',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (184,15,30,62,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Evaluasi (Al-Qur\'an Hadis): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','25e9da1cc688328dbb7d4c5389e630da75aaa8ca5b625f840ceeea379645acf7',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (185,15,30,62,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Evaluasi (Al-Qur\'an Hadis): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','ff536ea7181f7956ff40b0bea127424ca2e2b59551bd423d3e7fde6739d799f6',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (186,15,30,62,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Evaluasi (Al-Qur\'an Hadis): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','e1f5379591b129c8fad681cb416627b49022701b6e476456418a23c5119959cb',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (187,16,31,63,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Pengertian (Bahasa Arab): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','cb8d99e8c2d152b0d78b713991847779ba5072d4b65879d5774eed575c296c02',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (188,16,31,63,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Pengertian (Bahasa Arab): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','afaf4e32fa26dc297422922f9491ab75a2b296ae059b9cc617be288730d1ee4a',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (189,16,31,63,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Pengertian (Bahasa Arab): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','e88bfd46de1407a6fac863b1a6b1d63b3c39842ea1b51ae1bdfdf50def8b395c',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (190,16,31,64,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Ruang Lingkup (Bahasa Arab): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','3971babfb5bfe9e9d9a466bc99cfab4e30ea58120ff66c03717cb2e137edcde9',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (191,16,31,64,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Ruang Lingkup (Bahasa Arab): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','411c9a78f8252c5913539f5d469b7c2f54b68e6f9ef7a60709b41f5954aa9d75',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (192,16,31,64,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Ruang Lingkup (Bahasa Arab): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','9b386387437096db2afaaad905b117a676171b64c656288ea27b7a4377faf3fc',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (193,16,32,65,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Studi Kasus (Bahasa Arab): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','44d4faebef3aad090de4d04bb971140ff8971a9b2cf38975cd130541fde167b8',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (194,16,32,65,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Studi Kasus (Bahasa Arab): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','c0ec56ed2040125aefbb795b15b4a8f484ec93cf29c8a38b4a50e3b6b87deb41',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (195,16,32,65,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Studi Kasus (Bahasa Arab): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','5b0243c529be96a07eb72d18c6556751d4eb5e623ef7e4ac5955e83d8d66a3e8',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (196,16,32,66,1,'XII','multiple_choice','easy','C1',90,'[SAMPLE] Pertanyaan easy tentang Evaluasi (Bahasa Arab): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','3ce01c4fd212071858925dcccae6285ca95246c349f961afb8ec64ac5e777536',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (197,16,32,66,1,'XII','multiple_choice','medium','C3',90,'[SAMPLE] Pertanyaan medium tentang Evaluasi (Bahasa Arab): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','618104c1fa1ee76dd7d990d3df485d35d12e2e1f3c56ef38afeb24802a75ab73',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `questions` VALUES (198,16,32,66,1,'XII','multiple_choice','hard','C4',90,'[SAMPLE] Pertanyaan hard tentang Evaluasi (Bahasa Arab): manakah pernyataan yang paling tepat?','Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.',NULL,'sample','published','4e450f533b9f7c802665b3a90ba197cb2594b553016f7fe03e9f523b9b71d572',NULL,1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `school_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_classes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `grade` varchar(8) NOT NULL DEFAULT 'XII',
  `name` varchar(50) NOT NULL,
  `department` varchar(30) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_classes_school_id_grade_name_unique` (`school_id`,`grade`,`name`),
  CONSTRAINT `school_classes_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `school_classes` WRITE;
/*!40000 ALTER TABLE `school_classes` DISABLE KEYS */;
/*!40000 ALTER TABLE `school_classes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `schools` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `npsn` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `type` enum('SMA','MA','SMK','MAN','other') NOT NULL DEFAULT 'SMA',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schools_name_npsn_unique` (`name`,`npsn`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` VALUES (1,'MA Nurul Huda',NULL,'Jakarta','DKI Jakarta','MA','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `schools` VALUES (2,'SMA Negeri 1 Bandung',NULL,'Bandung','Jawa Barat','SMA','2026-10-02 21:41:19','2026-10-02 21:41:19');
/*!40000 ALTER TABLE `schools` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(120) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(50) NOT NULL DEFAULT 'general',
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'remedial.accuracy_threshold','\"60\"','learning',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `settings` VALUES (2,'remedial.min_attempts','\"5\"','learning',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `settings` VALUES (3,'app.api_version','\"v1\"','app',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `settings` VALUES (4,'app.content_version','\"1\"','app',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `settings` VALUES (5,'app.min_version','\"1.0.0\"','app',1,'2026-10-02 21:41:19','2026-10-02 21:41:19');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `study_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `study_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `topic_id` bigint(20) unsigned DEFAULT NULL,
  `duration_seconds` int(10) unsigned NOT NULL DEFAULT 0,
  `questions_answered` int(10) unsigned NOT NULL DEFAULT 0,
  `questions_correct` int(10) unsigned NOT NULL DEFAULT 0,
  `day` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `study_sessions_subject_id_foreign` (`subject_id`),
  KEY `study_sessions_topic_id_foreign` (`topic_id`),
  KEY `study_sessions_user_id_day_index` (`user_id`,`day`),
  CONSTRAINT `study_sessions_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `study_sessions_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL,
  CONSTRAINT `study_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `study_sessions` WRITE;
/*!40000 ALTER TABLE `study_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `study_sessions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subjects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(20) NOT NULL,
  `icon` varchar(32) NOT NULL DEFAULT 'book',
  `color` varchar(16) NOT NULL DEFAULT '#6366f1',
  `group` enum('umum','peminatan','keagamaan','muatan_lokal') NOT NULL DEFAULT 'umum',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subjects_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,'Matematika','MAT','book','#6366f1','umum',1,0,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `subjects` VALUES (2,'Matematika Tingkat Lanjut','MTL','book','#8b5cf6','peminatan',1,0,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `subjects` VALUES (3,'Bahasa Indonesia','BIND','book','#0ea5e9','umum',1,0,'2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `subjects` VALUES (4,'Bahasa Inggris','BING','book','#14b8a6','umum',1,0,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `subjects` VALUES (5,'Fisika','FIS','book','#f97316','peminatan',1,0,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `subjects` VALUES (6,'Kimia','KIM','book','#ef4444','peminatan',1,0,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `subjects` VALUES (7,'Biologi','BIO','book','#22c55e','peminatan',1,0,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `subjects` VALUES (8,'Ekonomi','EKO','book','#eab308','peminatan',1,0,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `subjects` VALUES (9,'Geografi','GEO','book','#a16207','peminatan',1,0,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `subjects` VALUES (10,'Sosiologi','SOC','book','#64748b','peminatan',1,0,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `subjects` VALUES (11,'Sejarah','SEJ','book','#b45309','peminatan',1,0,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `subjects` VALUES (12,'Fikih','FIQ','book','#0d9488','keagamaan',1,0,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `subjects` VALUES (13,'Sejarah Kebudayaan Islam','SKI','book','#7c3aed','keagamaan',1,0,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `subjects` VALUES (14,'Akidah Akhlak','AQA','book','#059669','keagamaan',1,0,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `subjects` VALUES (15,'Al-Qur\'an Hadis','QRH','book','#2563eb','keagamaan',1,0,'2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `subjects` VALUES (16,'Bahasa Arab','BAR','book','#db2777','keagamaan',1,0,'2026-10-02 21:41:19','2026-10-02 21:41:19');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sync_changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sync_changes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `entity` varchar(60) NOT NULL,
  `entity_id` bigint(20) unsigned NOT NULL,
  `local_id` varchar(100) DEFAULT NULL,
  `operation` enum('create','update','delete') NOT NULL DEFAULT 'create',
  `content_hash` varchar(64) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `changed_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sync_change_uq` (`user_id`,`entity`,`entity_id`,`changed_at`),
  KEY `sync_changes_user_id_changed_at_index` (`user_id`,`changed_at`),
  CONSTRAINT `sync_changes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sync_changes` WRITE;
/*!40000 ALTER TABLE `sync_changes` DISABLE KEYS */;
/*!40000 ALTER TABLE `sync_changes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sync_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sync_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `device_id` varchar(120) DEFAULT NULL,
  `direction` enum('push','pull') NOT NULL DEFAULT 'push',
  `changes_in` int(10) unsigned NOT NULL DEFAULT 0,
  `changes_out` int(10) unsigned NOT NULL DEFAULT 0,
  `conflicts` int(10) unsigned NOT NULL DEFAULT 0,
  `report` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report`)),
  `idempotency_key` varchar(120) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sync_logs_idempotency_key_unique` (`idempotency_key`),
  KEY `sync_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `sync_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sync_logs` WRITE;
/*!40000 ALTER TABLE `sync_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `sync_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `topic_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `topic_progress` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `topic_id` bigint(20) unsigned NOT NULL,
  `total_attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `correct_attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `accuracy` decimal(5,2) NOT NULL DEFAULT 0.00,
  `avg_time_seconds` int(10) unsigned NOT NULL DEFAULT 0,
  `mastery_status` enum('not_started','learning','proficient','mastered','needs_review') NOT NULL DEFAULT 'not_started',
  `last_activity_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `topic_progress_user_id_topic_id_unique` (`user_id`,`topic_id`),
  KEY `topic_progress_topic_id_foreign` (`topic_id`),
  CONSTRAINT `topic_progress_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE,
  CONSTRAINT `topic_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `topic_progress` WRITE;
/*!40000 ALTER TABLE `topic_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `topic_progress` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `topics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `chapter_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topics_chapter_id_foreign` (`chapter_id`),
  CONSTRAINT `topics_chapter_id_foreign` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `topics` WRITE;
/*!40000 ALTER TABLE `topics` DISABLE KEYS */;
INSERT INTO `topics` VALUES (1,1,'Integral Tak Tentu',NULL,1,'published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `topics` VALUES (2,1,'Integral Tertentu',NULL,2,'published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `topics` VALUES (3,1,'Aplikasi Integral',NULL,3,'published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `topics` VALUES (4,2,'Aturan Turunan',NULL,1,'published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `topics` VALUES (5,2,'Aplikasi Turunan',NULL,2,'published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `topics` VALUES (6,3,'Integral Tak Tentu',NULL,1,'published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `topics` VALUES (7,3,'Integral Tertentu',NULL,2,'published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `topics` VALUES (8,3,'Aplikasi Integral',NULL,3,'published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `topics` VALUES (9,4,'Aturan Turunan',NULL,1,'published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `topics` VALUES (10,4,'Aplikasi Turunan',NULL,2,'published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `topics` VALUES (11,5,'Struktur Editorial',NULL,1,'published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `topics` VALUES (12,5,'Kebahasaan',NULL,2,'published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `topics` VALUES (13,6,'Unsur Puisi',NULL,1,'published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `topics` VALUES (14,6,'Apresiasi Puisi',NULL,2,'published','2026-10-02 21:41:18','2026-10-02 21:41:18');
INSERT INTO `topics` VALUES (15,7,'Generic Structure',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (16,7,'Language Features',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (17,8,'Elements',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (18,8,'Comprehension',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (19,9,'Hukum Ohm',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (20,9,'Energi & Daya Listrik',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (21,10,'Fluks',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (22,10,'Hukum Faraday',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (23,11,'Orde Reaksi',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (24,11,'Teori Tumbukan',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (25,12,'Geseran Kesetimbangan',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (26,12,'Hukum Henry',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (27,13,'Faktor Internal',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (28,13,'Faktor Eksternal',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (29,14,'Enzim',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (30,14,'Katabolisme Karbohidrat',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (31,15,'Teori Pembangunan',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (32,15,'Permasalahan',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (33,16,'APBN',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (34,16,'Policy Fiscal',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (35,17,'Konsep',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (36,17,'Indikator',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (37,18,'Klasifikasi',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (38,18,'Indikator',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (39,19,'Teori',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (40,19,'Faktor Pendorong',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (41,20,'Bentuk',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (42,20,'Dampak',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (43,21,'Latar Belakang',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (44,21,'Dinamika',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (45,22,'Pembangunan',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (46,22,'Kebijakan',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (47,23,'Pengertian',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (48,23,'Ruang Lingkup',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (49,24,'Studi Kasus',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (50,24,'Evaluasi',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (51,25,'Pengertian',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (52,25,'Ruang Lingkup',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (53,26,'Studi Kasus',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (54,26,'Evaluasi',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (55,27,'Pengertian',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (56,27,'Ruang Lingkup',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (57,28,'Studi Kasus',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (58,28,'Evaluasi',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (59,29,'Pengertian',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (60,29,'Ruang Lingkup',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (61,30,'Studi Kasus',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (62,30,'Evaluasi',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (63,31,'Pengertian',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (64,31,'Ruang Lingkup',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (65,32,'Studi Kasus',NULL,1,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
INSERT INTO `topics` VALUES (66,32,'Evaluasi',NULL,2,'published','2026-10-02 21:41:19','2026-10-02 21:41:19');
/*!40000 ALTER TABLE `topics` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tryout_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tryout_answers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tryout_session_id` bigint(20) unsigned NOT NULL,
  `question_id` bigint(20) unsigned NOT NULL,
  `selected_option_key` varchar(10) DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `time_spent_seconds` int(10) unsigned NOT NULL DEFAULT 0,
  `flagged` tinyint(1) NOT NULL DEFAULT 0,
  `attempt_number` int(10) unsigned NOT NULL DEFAULT 1,
  `client_request_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ta_session_question_uq` (`tryout_session_id`,`question_id`),
  UNIQUE KEY `tryout_answers_client_request_id_unique` (`client_request_id`),
  KEY `tryout_answers_question_id_foreign` (`question_id`),
  CONSTRAINT `tryout_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tryout_answers_tryout_session_id_foreign` FOREIGN KEY (`tryout_session_id`) REFERENCES `tryout_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tryout_answers` WRITE;
/*!40000 ALTER TABLE `tryout_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `tryout_answers` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tryout_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tryout_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tryout_id` bigint(20) unsigned NOT NULL,
  `question_id` bigint(20) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `points` int(10) unsigned NOT NULL DEFAULT 1,
  `is_compulsory` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tryout_questions_tryout_id_question_id_unique` (`tryout_id`,`question_id`),
  KEY `tryout_questions_question_id_foreign` (`question_id`),
  CONSTRAINT `tryout_questions_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tryout_questions_tryout_id_foreign` FOREIGN KEY (`tryout_id`) REFERENCES `tryouts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tryout_questions` WRITE;
/*!40000 ALTER TABLE `tryout_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `tryout_questions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tryout_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tryout_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tryout_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `uuid` char(36) NOT NULL,
  `status` enum('active','completed','expired','abandoned') NOT NULL DEFAULT 'active',
  `question_order` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`question_order`)),
  `flagged_questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`flagged_questions`)),
  `answered_count` int(10) unsigned NOT NULL DEFAULT 0,
  `started_at` datetime NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `duration_seconds` int(10) unsigned DEFAULT NULL,
  `score` decimal(7,2) DEFAULT NULL,
  `accuracy` decimal(5,2) DEFAULT NULL,
  `statistics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`statistics`)),
  `submit_idempotency_key` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tryout_sessions_uuid_unique` (`uuid`),
  UNIQUE KEY `session_submit_idem_uq` (`user_id`,`tryout_id`,`submit_idempotency_key`),
  KEY `tryout_sessions_tryout_id_foreign` (`tryout_id`),
  KEY `tryout_sessions_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `tryout_sessions_tryout_id_foreign` FOREIGN KEY (`tryout_id`) REFERENCES `tryouts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tryout_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tryout_sessions` WRITE;
/*!40000 ALTER TABLE `tryout_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `tryout_sessions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tryouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tryouts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `question_set_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('umum','mata_pelajaran','tka','simulasi','remedial') NOT NULL DEFAULT 'umum',
  `grade` varchar(8) NOT NULL DEFAULT 'XII',
  `duration_minutes` int(10) unsigned NOT NULL DEFAULT 90,
  `question_count` int(10) unsigned NOT NULL DEFAULT 0,
  `question_order` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`question_order`)),
  `status` enum('draft','scheduled','open','closed','archived') NOT NULL DEFAULT 'draft',
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `auto_publish_result` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tryouts_subject_id_foreign` (`subject_id`),
  KEY `tryouts_question_set_id_foreign` (`question_set_id`),
  KEY `tryouts_created_by_foreign` (`created_by`),
  CONSTRAINT `tryouts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tryouts_question_set_id_foreign` FOREIGN KEY (`question_set_id`) REFERENCES `question_sets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tryouts_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tryouts` WRITE;
/*!40000 ALTER TABLE `tryouts` DISABLE KEYS */;
/*!40000 ALTER TABLE `tryouts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `user_achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_achievements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `achievement_id` bigint(20) unsigned NOT NULL,
  `unlocked_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_achievements_user_id_achievement_id_unique` (`user_id`,`achievement_id`),
  KEY `user_achievements_achievement_id_foreign` (`achievement_id`),
  CONSTRAINT `user_achievements_achievement_id_foreign` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_achievements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `user_achievements` WRITE;
/*!40000 ALTER TABLE `user_achievements` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_achievements` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `profile_picture_path` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `force_password_change` tinyint(1) NOT NULL DEFAULT 1,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `security_questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`security_questions`)),
  `primary_role` varchar(80) DEFAULT NULL,
  `role_slugs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`role_slugs`)),
  `role_assignment_contexts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`role_assignment_contexts`)),
  `department` varchar(80) DEFAULT NULL,
  `assigned_projects` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`assigned_projects`)),
  `employment_status` varchar(40) DEFAULT NULL,
  `contract_start_at` date DEFAULT NULL,
  `contract_end_at` date DEFAULT NULL,
  `auto_suspend_on_expiry` tinyint(1) NOT NULL DEFAULT 1,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `permissions_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions_payload`)),
  `onboarded_by` bigint(20) unsigned DEFAULT NULL,
  `activation_token_hash` varchar(255) DEFAULT NULL,
  `activation_token_expires_at` timestamp NULL DEFAULT NULL,
  `welcome_email_sent_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `approval_status` varchar(20) NOT NULL DEFAULT 'approved',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_onboarded_by_foreign` (`onboarded_by`),
  CONSTRAINT `users_onboarded_by_foreign` FOREIGN KEY (`onboarded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Superuser','Funinsidekids@gmail.com',NULL,NULL,'2026-10-02 21:41:15','$2y$12$wsmJwE71TYr3St9782SiPea0Liv4tCVV1fhTQgRotTpTGc/4WABMO',1,NULL,NULL,'Admin',NULL,NULL,'Operations',NULL,'full-time',NULL,NULL,0,NULL,'[\"*\"]',NULL,NULL,NULL,NULL,NULL,'2026-10-02 21:41:12','2026-10-02 21:41:15','active',NULL,NULL,'funinsidekids','approved',NULL,NULL,NULL);
INSERT INTO `users` VALUES (2,'Makutharama','Steveandriebagus@gmail.com',NULL,NULL,'2026-10-02 21:41:15','$2y$12$jEeO2o/L9IPemuQ8ZIg7eu.vHRhkADTMK..X9sndjDJiVZ.8LsukK',1,NULL,NULL,'Superadmin','[\"superadmin\"]',NULL,'Operations',NULL,'full-time',NULL,NULL,0,NULL,'[\"*\"]',NULL,NULL,NULL,NULL,NULL,'2026-10-02 21:41:12','2026-10-02 21:41:56','active',NULL,NULL,'Makutharama','approved',NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `video_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `video_assets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `context` varchar(255) DEFAULT NULL,
  `source_path` varchar(255) NOT NULL,
  `webm_path` varchar(255) DEFAULT NULL,
  `mp4_path` varchar(255) DEFAULT NULL,
  `thumb_path` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'uploaded',
  `error_message` varchar(255) DEFAULT NULL,
  `stream_path` varchar(255) DEFAULT NULL,
  `cdn_url` varchar(255) DEFAULT NULL,
  `duration_seconds` int(10) unsigned DEFAULT NULL,
  `converted_at` timestamp NULL DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `video_assets_status_created_at_index` (`status`,`created_at`),
  KEY `video_assets_context_created_at_index` (`context`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `video_assets` WRITE;
/*!40000 ALTER TABLE `video_assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `video_assets` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

