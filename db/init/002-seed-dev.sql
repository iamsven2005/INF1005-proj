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
-- Dumping data for table `BookingHolding`
--

LOCK TABLES `BookingHolding` WRITE;
/*!40000 ALTER TABLE `BookingHolding` DISABLE KEYS */;
-- INSERT INTO `BookingHolding` VALUES (1,'2025-12-31','20:00:00','2025-11-16 23:59:59',2);
/*!40000 ALTER TABLE `BookingHolding` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `Bookings`
--

LOCK TABLES `Bookings` WRITE;
/*!40000 ALTER TABLE `Bookings` DISABLE KEYS */;
-- INSERT INTO `Bookings` VALUES (1,'2025-12-25','18:00:00',50.00,'Confirmed','2025-11-16 12:00:00',1,1);
/*!40000 ALTER TABLE `Bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `Reviews`
--

LOCK TABLES `Reviews` WRITE;
/*!40000 ALTER TABLE `Reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `Reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `Rooms`
--

LOCK TABLES `Rooms` WRITE;
/*!40000 ALTER TABLE `Rooms` DISABLE KEYS */;
INSERT INTO `Rooms` 
(
    roomID, roomName, roomDescription, roomMax, roomMin, roomDuration, 
    roomDifficulty, roomLocation, roomFearLevel, roomExperienceType, 
    roomGenre, roomPricePeak, roomPriceOffpeak, imagePath
)
VALUES 
(1,'The Cursed Cabin','A cabin in the woods. Nothing can go wrong.',6,2,60,'Hard','Main Street','Very Scary','Live Actor','Horror',50.00,40.00,'images/cursed_cabin.png'),
(2,'Asylum','A creepy asylum.',8,4,90,'Medium','Uptown','Scary','No Live Actor','Thriller',60.00,50.00,'images/asylum.png'),
(3, 'The Game Begins', 'You wake up chained in a dilapidated bathroom. A puppet on a screen tells you the rules. Make the right sacrifices to survive.', 6, 2, 60, 'Very Hard', 'Basement Level', 'Very Scary', 'Live Actor', 'Horror', 60.00, 50.00, 'images/saw.png'),
(4, 'The Cursed Tape', 'You watched the tape 7 days ago. Now the TV is flickering and the phone is ringing. Break the curse before she crawls out.', 5, 2, 60, 'Hard', 'Main Street Branch', 'Very Scary', 'Live Actor', 'Horror', 55.00, 45.00, 'images/cursed_tv.jpg'),
(5, 'Deadly Silence', 'They hunt by sound. You must navigate the farmhouse and solve puzzles without making a noise above 60 decibels. If you drop something, you die.', 4, 2, 45, 'Hard', 'Uptown Branch', 'Scary', 'No Live Actor', 'Thriller', 50.00, 40.00, 'images/quiet_farm.jpg'),
(6, 'The Sewer Entrance', 'A paper boat floated down the drain, and you followed it. Now you are in the sewers of Derry. You’ll float too if you don’t escape.', 7, 3, 75, 'Medium', 'Downtown Branch', 'Very Scary', 'Live Actor', 'Horror', 55.00, 45.00, 'images/red_balloon.jpg'),
(7, 'The Artifact Room', 'Paranormal investigators have locked away a possessed doll. She has escaped her case. You must perform the containment ritual before midnight.', 6, 2, 60, 'Medium', 'Main Street Branch', 'Scary', 'No Live Actor', 'Horror', 45.00, 35.00, 'images/possessed_doll.jpg'),
-- OTHER GENRES (Fantasy, Adventure, Mystery, Thriller) 
(8, 'The Wizard''s Academy', 'Dark forces have infiltrated the school of magic. You must sneak into the Headmaster''s office, find the hidden artifact, and cast the protection spell before the walls fall.', 8, 3, 60, 'Medium', 'Main Street Branch', 'Not Scary', 'No Live Actor', 'Fantasy', 45.00, 35.00, 'images/wizard_office.jpg'),
(9, 'Temple of the Forbidden Eye', 'You have discovered the lost map to an ancient temple. Navigate deadly spike traps and decipher forgotten hieroglyphs to steal the golden idol before the temple collapses.', 6, 2, 75, 'Hard', 'Downtown Branch', 'Mildly Scary', 'No Live Actor', 'Adventure', 50.00, 40.00, 'images/jungle_temple.jpg'),
(10, 'The Missing Detective', 'The world''s greatest detective has vanished. Enter his apartment at 221B Baker Street, connect the clues on his murder board, and solve the case before the culprit escapes London.', 5, 2, 60, 'Medium', 'Uptown Branch', 'Not Scary', 'No Live Actor', 'Mystery', 42.00, 32.00, 'images/baker_street.jpg'),
(11, 'The Casino Heist', 'Your team has 60 minutes to bypass the laser security grid and crack the titanium vault. If you trip a single sensor, the police arrive instantly. Stealth is key.', 7, 3, 60, 'Hard', 'Downtown Branch', 'Not Scary', 'No Live Actor', 'Thriller', 48.00, 38.00, 'images/laser_vault.jpg'),
(12, 'Curse of the Black Pearl', 'You are locked in the brig of a ghost ship. The undead crew rises at moonrise. Escape your cell, find the Aztec gold coin, and break the curse to return to the living world.', 6, 2, 60, 'Easy', 'Main Street Branch', 'Mildly Scary', 'Live Actor', 'Adventure', 45.00, 35.00, 'images/pirate_ship.jpg'),
(13, 'Murder at the Manor', 'The wealthy patriarch was poisoned at his own birthday dinner. The police are baffled. You have 90 minutes to find the true killer hiding among the guests.', 8, 4, 90, 'Very Hard', 'Uptown Branch', 'Mildly Scary', 'No Live Actor', 'Mystery', 55.00, 45.00, 'images/manor_dinner.jpg');
/*!40000 ALTER TABLE `Rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `Users`
--

LOCK TABLES `Users` WRITE;
/*!40000 ALTER TABLE `Users` DISABLE KEYS */;
INSERT INTO `Users` 
VALUES 
(1,'victim_1','test@example.com','some_hashed_password','2025-11-12 16:28:58',0),
(4,'admin_user','admin@myhorror.com','your_secure_hashed_password','2025-11-12 16:56:51',1),
(5,'tester','test@yourhorror.com','another_hashed_password','2025-11-12 17:03:17',0),
(6,'test_user_cas','cascade@example.com','some_hash','2025-11-12 17:04:24',0);
/*!40000 ALTER TABLE `Users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-13  1:14:46
