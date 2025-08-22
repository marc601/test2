/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.11-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: peibo_test2
-- ------------------------------------------------------
-- Server version	10.11.11-MariaDB-0+deb12u1

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

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `cookie_token` varchar(255) DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES
(28,1,'8025d45388635f0b4679209f',NULL,'2025-08-22 08:47:41'),
(29,1,'cb4df0e594cf578983935a9b',NULL,'2025-08-22 09:21:56'),
(30,1,'c2fedc339b19c54f8870b976',NULL,'2025-08-22 10:13:34'),
(31,1,'f4f682d278da1a183883d262',NULL,'2025-08-22 11:24:24'),
(32,1,'e6a1fcf4edcd3f66082beda3',NULL,'2025-08-22 12:25:50'),
(33,1,'b9e3dbec7ead75745f2fc948',NULL,'2025-08-22 20:32:34');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `due_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES
(21,1,'test2','algo',2,'2025-08-22 12:37:11','2025-08-22 06:37:43','2025-08-19'),
(24,1,'New Task Title','Description of the new task.',1,'2025-08-22 12:45:32','2025-08-22 12:45:32','2025-08-22'),
(25,1,'New Task Title','Description of the new task.',1,'2025-08-22 12:45:51','2025-08-22 12:45:51','2025-08-22');
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Marco Castro','cosme.fulanito3@example.com','$2y$12$6HThIoiY.p3xW3iW5kNKp.ECbfNTe.M4k/dmqU4/LeIFwP6fJAWM2','2025-08-21 15:16:34','2025-08-22 04:54:11'),
(9,'Cosme Fulanito3','cosme.fulanito4@example.com','$2y$12$D5dxPgu2ct9Y7DDzIJUNfe7kGXxVrfpE7hhAU99c9NX4ETRsC.Tl.','2025-08-22 09:04:48','2025-08-22 09:04:48'),
(10,'Cosme Fulanito3','cosme.fulanito5@example.com','$2y$12$Mwz1iI8GclYmhQHByub4B.Ld8/XjePUgUANgoFZW7DnMYuIIz8a1q','2025-08-22 09:06:21','2025-08-22 09:06:21'),
(11,'Cosme Fulanito3','cosme.fulanito6@example.com','$2y$12$pK3jDUr447nPwjFdXW9atOWqf2m7trCpULaTkv.xoS6S32JxTl.XK','2025-08-22 09:09:02','2025-08-22 09:09:02'),
(12,'Cosme Fulanito3','cosme.fulanito7@example.com','$2y$12$wCdmSsidcH/mGJxdhNfl4eNd5L5dUKcloYKPVFL1Cr8OVqgM8S8p2','2025-08-22 09:10:03','2025-08-22 09:10:03'),
(13,'Cosme Fulanito3','cosme.fulanito8@example.com','$2y$12$tFwe0uj0tqiUn8P8wMptAeIkdyCPuDBT4eigIeDGeaHJgpK5O5Bim','2025-08-22 09:10:22','2025-08-22 09:10:22'),
(15,'Cosme Fulanito3','cosme.fulanito9@example.com','$2y$12$9MsaZaQ/ZXNARSQc4RUsBu/uAps5.cvsECu6MzqNdB/Zl4I8wSzeS','2025-08-22 12:39:23','2025-08-22 12:39:23');
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

-- Dump completed on 2025-08-22  0:47:16
