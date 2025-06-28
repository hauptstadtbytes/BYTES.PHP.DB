-- The migration script for upgrading the database schema from version 1 to 2
-- ---------------------------------------------------------------------------

-- Create the 'Properties' table
CREATE TABLE IF NOT EXISTS `Properties` (
  `key` char(100) NOT NULL,
  `value` char(100) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;