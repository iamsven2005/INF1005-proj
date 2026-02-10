-- MySQL dump 10.13  Distrib 8.0.44, for macos15 (arm64)
--
-- Host: 127.0.0.1    Database: mydb
-- ------------------------------------------------------
-- Server version	8.0.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `BookingHolding`
--

DROP TABLE IF EXISTS `BookingHolding`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `BookingHolding` (
  `holdID` int NOT NULL AUTO_INCREMENT,
  `holdDate` date NOT NULL,
  `holdTimeslot` time NOT NULL,
  `expires_at` timestamp NOT NULL,
  `Rooms_roomID` int unsigned NOT NULL,
  `Users_userID` int unsigned NOT NULL,
  PRIMARY KEY (`holdID`),
  UNIQUE KEY `UK_slot_lock` (`Rooms_roomID`,`holdDate`,`holdTimeslot`),
  KEY `fk_BookingHolding_Rooms1_idx` (`Rooms_roomID`),
  KEY `fk_BookingHolding_Users1_idx` (`Users_userID`),
  CONSTRAINT `fk_BookingHolding_Rooms1` FOREIGN KEY (`Rooms_roomID`) REFERENCES `Rooms` (`roomID`) ON DELETE CASCADE,
  CONSTRAINT `fk_BookingHolding_Users1` FOREIGN KEY (`Users_userID`) REFERENCES `Users` (`userID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Bookings`
--

DROP TABLE IF EXISTS `Bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Bookings` (
  `bookingID` int unsigned NOT NULL AUTO_INCREMENT,
  `bookingRef` varchar(20) UNIQUE NOT NULL,
  `bookingDate` date NOT NULL,
  `bookingTimeslot` time NOT NULL,
  `numPlayers` int unsigned NOT NULL,
  `totalPrice` decimal(10,2) NOT NULL,
  `bookingStatus` enum('Confirmed','Cancelled') DEFAULT 'Confirmed',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cancel_token` varchar(64) NULL,
  `stripe_payment_id` varchar(255) NULL,
  `refund_id` varchar(255) NULL,
  `refund_amt` decimal(10,2) NULL,
  `cancelled_at` DATETIME NULL,
  `Rooms_roomID` int unsigned NOT NULL,
  `Users_userID` int unsigned NOT NULL,
  PRIMARY KEY (`bookingID`),
  UNIQUE KEY `bookingID_UNIQUE` (`bookingID`),
  KEY `fk_Bookings_Rooms_idx` (`Rooms_roomID`),
  KEY `fk_Bookings_Users1_idx` (`Users_userID`),
  CONSTRAINT `fk_Bookings_Rooms` FOREIGN KEY (`Rooms_roomID`) REFERENCES `Rooms` (`roomID`) ON DELETE CASCADE,
  CONSTRAINT `fk_Bookings_Users1` FOREIGN KEY (`Users_userID`) REFERENCES `Users` (`userID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Reviews`
--

DROP TABLE IF EXISTS `Reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Reviews` (
  `reviewID` int unsigned NOT NULL AUTO_INCREMENT,
  `rating` tinyint unsigned NOT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Users_userID` int unsigned NOT NULL,
  `Rooms_roomID` int unsigned NOT NULL,
  PRIMARY KEY (`reviewID`),
  UNIQUE KEY `reviewID_UNIQUE` (`reviewID`),
  KEY `fk_Reviews_Users1_idx` (`Users_userID`),
  KEY `fk_Reviews_Rooms1_idx` (`Rooms_roomID`),
  CONSTRAINT `fk_Reviews_Rooms1` FOREIGN KEY (`Rooms_roomID`) REFERENCES `Rooms` (`roomID`) ON DELETE CASCADE,
  CONSTRAINT `fk_Reviews_Users1` FOREIGN KEY (`Users_userID`) REFERENCES `Users` (`userID`) ON DELETE CASCADE,
  CONSTRAINT `chk_rating_range` CHECK (`rating` >= 1 AND `rating` <= 5)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Rooms`
--

DROP TABLE IF EXISTS `Rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Rooms` (
  `roomID` int unsigned NOT NULL AUTO_INCREMENT,
  `roomName` varchar(100) NOT NULL,
  `roomDescription` text NOT NULL,
  `roomMax` tinyint unsigned NOT NULL,
  `roomMin` tinyint unsigned NOT NULL,
  `roomDuration` tinyint unsigned NOT NULL,
  `roomDifficulty` enum('Easy','Medium','Hard','Very Hard') NOT NULL,
  `roomLocation` varchar(45) NOT NULL,
  `roomFearLevel` enum('Not Scary','Mildly Scary','Scary','Very Scary') NOT NULL,
  `roomExperienceType` enum('No Live Actor','Live Actor') NOT NULL,
  `roomGenre` enum('Horror','Thriller','Fantasy','Adventure','Mystery') NOT NULL,
  `roomPricePeak` decimal(10,2) NOT NULL,
  `roomPriceOffPeak` decimal(10,2) NOT NULL,
  `imagePath` varchar(255) NULL DEFAULT 'images/placeholder.png',
  PRIMARY KEY (`roomID`),
  UNIQUE KEY `roomID_UNIQUE` (`roomID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Users` (
  `userID` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `email` varchar(100) NOT NULL,
  `passwordhash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_admin` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`userID`),
  UNIQUE KEY `userID_UNIQUE` (`userID`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-13  1:14:08
