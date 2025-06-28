-- The migration script for creating the initial database schema (1)
-- -----------------------------------------------------------------

-- Create the 'Items' table
DROP TABLE IF EXISTS `Items`;
CREATE TABLE IF NOT EXISTS `Items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL,
  `content` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add some initial item(s) to the 'Items' table
DELETE FROM `Items`;
INSERT INTO `Items` (`id`, `title`, `content`) VALUES
	(1, 'Hello', 'Hello World!'),
	(2, 'Description', 'This is a mockup item in a mockup database');