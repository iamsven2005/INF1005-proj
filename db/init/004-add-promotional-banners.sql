CREATE TABLE IF NOT EXISTS `PromotionalBanners` (
  `bannerID` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `imagePath` varchar(255) DEFAULT NULL,
  `ctaText` varchar(60) DEFAULT NULL,
  `ctaUrl` varchar(255) DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`bannerID`),
  CONSTRAINT `chk_banner_date_range` CHECK ((`startDate` IS NULL OR `endDate` IS NULL OR `startDate` <= `endDate`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
