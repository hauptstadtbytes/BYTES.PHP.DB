CREATE TABLE IF NOT EXISTS `Test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL,
  `content` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `Test` (`id`, `title`, `content`) VALUES
    (2, 'Test Item', 'this is a new test item'),
	(1, 'Hello', 'wold!');