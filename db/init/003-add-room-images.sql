-- Create RoomImages table for supporting multiple images per room
CREATE TABLE IF NOT EXISTS `RoomImages` (
  `imageID` int unsigned NOT NULL AUTO_INCREMENT,
  `Rooms_roomID` int unsigned NOT NULL,
  `imagePath` varchar(255) NOT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`imageID`),
  KEY `fk_RoomImages_Rooms_idx` (`Rooms_roomID`),
  CONSTRAINT `fk_RoomImages_Rooms` FOREIGN KEY (`Rooms_roomID`) REFERENCES `Rooms` (`roomID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Migrate existing images from Rooms.imagePath to RoomImages (if imagePath is not the placeholder)
INSERT INTO RoomImages (Rooms_roomID, imagePath, is_featured)
SELECT roomID, imagePath, 1 
FROM Rooms 
WHERE imagePath IS NOT NULL AND imagePath != 'images/placeholder.png';
