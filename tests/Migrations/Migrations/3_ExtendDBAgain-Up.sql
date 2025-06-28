-- The migration script for upgrading the database schema to version 3
-- -----------------------------------------------------------------

-- Create the 'Items' table
DROP TABLE IF EXISTS `Test`;
CREATE TABLE IF NOT EXISTS `Test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL,
  `content` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add some initial item(s) to the 'Items' table
DELETE FROM `Items`;
INSERT INTO `Items` (`id`, `title`, `content`) VALUES
	(1, 'Hello', 'World!'),
	(2, 'Test Item', 'this is a new test item');