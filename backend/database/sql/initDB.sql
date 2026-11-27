
-- Dumping structure for table ans.osoba
CREATE TABLE IF NOT EXISTS `person` (
    `person_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `first_name` varchar(100) NOT NULL,
    `last_name` varchar(100) NOT NULL,
    `title_prefix` varchar(50) DEFAULT NULL,
    `title_suffix` varchar(50) DEFAULT NULL,
    `email` varchar(254) NOT NULL,
    `organization` varchar(200) DEFAULT NULL,
    `department` varchar(200) DEFAULT NULL,
    `country` varchar(100) DEFAULT NULL,
    PRIMARY KEY (`person_id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping structure for table ans.konferencia
CREATE TABLE IF NOT EXISTS `conference` (
    `conference_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(200) NOT NULL,
    `description` text DEFAULT NULL,
    PRIMARY KEY (`conference_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping structure for table ans.rocnik
CREATE TABLE IF NOT EXISTS `edition` (
    `edition_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `conference_id` int(10) unsigned NOT NULL,
    `year` smallint(5) unsigned NOT NULL,
    `location` varchar(150) DEFAULT NULL,
    `start_date` date NOT NULL,
    `end_date` date NOT NULL,
    PRIMARY KEY (`edition_id`),
    KEY `conference_id` (`conference_id`),
    CONSTRAINT `fk_parent_1` FOREIGN KEY (`conference_id`) REFERENCES `conference` (`conference_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping structure for table ans.kategoria_registracie
CREATE TABLE IF NOT EXISTS `registration_categories` (
    `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `edition_id` int(10) unsigned NOT NULL,
    `name` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    PRIMARY KEY (`category_id`),
    UNIQUE KEY `category_id` (`category_id`,`edition_id`),
    KEY `edition_id` (`edition_id`),
    CONSTRAINT `fk_parent_1` FOREIGN KEY (`edition_id`) REFERENCES `edition` (`edition_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping structure for table ans.registracia
CREATE TABLE IF NOT EXISTS `registration` (
    `registration_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `person_id` int(10) unsigned NOT NULL,
    `edition_id` int(10) unsigned NOT NULL,
    `category_id` int(10) unsigned NOT NULL,
    `registration_date` datetime NOT NULL DEFAULT current_timestamp(),
    `status` varchar(30) NOT NULL DEFAULT 'submitted',
    PRIMARY KEY (`registration_id`),
    UNIQUE KEY `person_id` (`person_id`,`edition_id`),
    KEY `edition_id` (`edition_id`),
    KEY `category_id` (`category_id`,`edition_id`),
    CONSTRAINT `fk_parent_1` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`),
    CONSTRAINT `fk_parent_2` FOREIGN KEY (`edition_id`) REFERENCES `edition` (`edition_id`),
    CONSTRAINT `fk_parent_3` FOREIGN KEY (`category_id`, `edition_id`) REFERENCES `registration_categories` (`category_id`, `edition_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping structure for table ans.prispevok
CREATE TABLE IF NOT EXISTS `submission` (
    `submission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `registration_id` int(10) unsigned NOT NULL,
    `name` varchar(250) NOT NULL,
    `abstract` text NOT NULL,
    `type` varchar(50) NOT NULL,
    `status` varchar(30) NOT NULL DEFAULT 'odoslany',
    `submission_date` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`submission_id`),
    KEY `registration_id` (`registration_id`),
    CONSTRAINT `fk_parent_1` FOREIGN KEY (`registration_id`) REFERENCES `registration` (`registration_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping structure for table ans.autor_prispevku
CREATE TABLE IF NOT EXISTS `submission_authors` (
  `submission_id` int(10) unsigned NOT NULL,
  `person_id` int(10) unsigned NOT NULL,
  `author_order` smallint(5) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`submission_id`,`person_id`),
  KEY `person_id` (`person_id`),
  CONSTRAINT `fk_parent_1` FOREIGN KEY (`submission_id`) REFERENCES `submission` (`submission_id`),
  CONSTRAINT `fk_parent_2` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping data for table ans.autor_prispevku: ~0 rows (approximately)
DELETE FROM `submission_authors`;

-- Dumping structure for table ans.bod_programu
CREATE TABLE IF NOT EXISTS `program_items` (
  `program_item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `edition_id` int(10) unsigned NOT NULL,
  `submission_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(250) NOT NULL,
  `type` varchar(50) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  PRIMARY KEY (`program_item_id`),
  KEY `edition_id` (`edition_id`),
  KEY `submission_id` (`submission_id`),
  CONSTRAINT `fk_parent_1` FOREIGN KEY (`edition_id`) REFERENCES `edition` (`edition_id`),
  CONSTRAINT `fk_parent_2` FOREIGN KEY (`submission_id`) REFERENCES `submission` (`submission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping structure for table ans.pouzivatelsky_ucet
CREATE TABLE IF NOT EXISTS `user_accounts` (
    `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `person_id` int(10) unsigned NOT NULL,
    `password_hash` varchar(255) NOT NULL,
    `account_status` varchar(30) NOT NULL DEFAULT 'active',
    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `person_id` (`person_id`),
    CONSTRAINT `fk_parent_1` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping structure for table ans.prednasajuci_programu
CREATE TABLE IF NOT EXISTS `program_presenters` (
    `program_item_id` int(10) unsigned NOT NULL,
    `person_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`program_item_id`,`person_id`),
    KEY `person_id` (`person_id`),
    CONSTRAINT `fk_parent_1` FOREIGN KEY (`program_item_id`) REFERENCES `program_items` (`program_item_id`),
    CONSTRAINT `fk_parent_2` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping structure for table ans.recenzia_prispevku
CREATE TABLE IF NOT EXISTS `submission_reviews` (
    `review_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `submission_id` int(10) unsigned NOT NULL,
    `reviewer_id` int(10) unsigned NOT NULL,
    `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
    `completed_at` datetime DEFAULT NULL,
    `status` varchar(30) NOT NULL DEFAULT 'assigned',
    `written_assessment` text DEFAULT NULL,
    `comments` text DEFAULT NULL,
    `recommendation` varchar(30) DEFAULT NULL,
    PRIMARY KEY (`review_id`),
    KEY `submission_id` (`submission_id`),
    KEY `reviewer_id` (`reviewer_id`),
    CONSTRAINT `fk_parent_1` FOREIGN KEY (`submission_id`) REFERENCES `submission` (`submission_id`),
    CONSTRAINT `fk_parent_2` FOREIGN KEY (`reviewer_id`) REFERENCES `person` (`person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping structure for table ans.rola
CREATE TABLE IF NOT EXISTS `role` (
    `role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `description` text DEFAULT NULL,
    PRIMARY KEY (`role_id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping structure for table ans.rola_pouzivatela
CREATE TABLE IF NOT EXISTS `user_role` (
    `user_id` int(10) unsigned NOT NULL,
    `role_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`user_id`,`role_id`),
    KEY `role_id` (`role_id`),
    CONSTRAINT `fk_parent_1` FOREIGN KEY (`user_id`) REFERENCES `user_accounts` (`user_id`),
    CONSTRAINT `fk_parent_2` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping structure for table ans.rozhodnutie
CREATE TABLE IF NOT EXISTS `decision` (
    `decision_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `submission_id` int(10) unsigned NOT NULL,
    `user_id` int(10) unsigned NOT NULL,
    `decision_type` varchar(30) NOT NULL,
    `justification` text DEFAULT NULL,
    `decision_date` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`decision_id`),
    KEY `submission_id` (`submission_id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `fk_parent_1` FOREIGN KEY (`submission_id`) REFERENCES `submission` (`submission_id`),
    CONSTRAINT `fk_parent_2` FOREIGN KEY (`user_id`) REFERENCES `user_accounts` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping structure for table ans.termin
CREATE TABLE IF NOT EXISTS `deadline` (
    `deadline_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `edition_id` int(10) unsigned NOT NULL,
    `name` varchar(150) NOT NULL,
    `description` text DEFAULT NULL,
    `start_datetime` datetime NOT NULL,
    `end_datetime` datetime NOT NULL,
    PRIMARY KEY (`deadline_id`),
    KEY `edition_id` (`edition_id`),
    CONSTRAINT `fk_parent_1` FOREIGN KEY (`edition_id`) REFERENCES `edition` (`edition_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping structure for table ans.verzia_prispevku
CREATE TABLE IF NOT EXISTS `submission_versions` (
    `version_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `submission_id` int(10) unsigned NOT NULL,
    `version_number` smallint(5) unsigned NOT NULL,
    `file_name` varchar(255) NOT NULL,
    `file_path` varchar(500) NOT NULL,
    `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
    `document_type` varchar(50) NOT NULL,
    PRIMARY KEY (`version_id`),
    UNIQUE KEY `submission_id` (`submission_id`,`version_number`),
    CONSTRAINT `fk_parent_1` FOREIGN KEY (`submission_id`) REFERENCES `submission` (`submission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;


-- Dumping data for table ans.bod_programu: ~0 rows (approximately)
DELETE FROM `program_items`;

-- Dumping data for table ans.konferencia: ~0 rows (approximately)
DELETE FROM `conference`;
INSERT INTO `conference` (`conference_id`, `name`, `description`) VALUES
    (1, 'ANS 2027 – Applied Informatics', 'Test conference focused on applied informatics, information systems, and new technologies.');

-- Dumping data for table ans.osoba: ~12 rows (approximately)
DELETE FROM `person`;
INSERT INTO `person` (`person_id`, `first_name`, `last_name`, `title_prefix`, `title_suffix`, `email`, `organization`, `department`, `country`) VALUES
    (1, 'Jana', 'Nováková', 'Ing.', 'PhD.', 'jana.novakova@example.com', 'UCM v Trnave', 'Faculty of Natural Sciences', 'Slovakia'),
    (2, 'Martin', 'Kováč', 'Mgr.', 'PhD.', 'martin.kovac@example.com', 'UCM v Trnave', 'Faculty of Natural Sciences', 'Slovakia'),
    (3, 'Petra', 'Horváthová', 'doc. RNDr.', 'PhD.', 'petra.horvathova@example.com', 'UCM v Trnave', 'Faculty of Natural Sciences', 'Slovakia'),
    (4, 'Tomáš', 'Bielik', 'Ing.', NULL, 'tomas.bielik@example.com', 'STU v Bratislave', 'FIIT', 'Slovakia'),
    (5, 'Lucia', 'Šimková', 'Mgr.', NULL, 'lucia.simkova@example.com', 'Comenius University', 'FMFI', 'Slovakia'),
    (6, 'Peter', 'Urban', 'Ing.', 'PhD.', 'peter.urban@example.com', 'Žilinská univerzita', 'Faculty of Management Science and Informatics', 'Slovakia'),
    (7, 'Eva', 'Poláková', NULL, NULL, 'eva.polakova@example.com', 'UCM v Trnave', 'Faculty of Natural Sciences', 'Slovakia'),
    (8, 'Michal', 'Tóth', 'Bc.', NULL, 'michal.toth@example.com', 'UCM v Trnave', 'Faculty of Natural Sciences', 'Slovakia'),
    (9, 'Andrej', 'Král', 'Ing.', NULL, 'andrej.kral@example.com', 'VUT v Brne', 'Faculty of Information Technology', 'Czechia'),
    (10, 'Zuzana', 'Malá', 'Mgr.', 'PhD.', 'zuzana.mala@example.com', 'UCM v Trnave', 'Faculty of Natural Sciences', 'Slovakia'),
    (11, 'Filip', 'Černý', NULL, NULL, 'filip.cerny@example.com', 'Masarykova univerzita', 'FI', 'Czechia'),
    (12, 'Katarína', 'Vargová', 'Bc.', NULL, 'katarina.vargova@example.com', 'UCM v Trnave', 'Faculty of Natural Sciences', 'Slovakia');


-- Dumping data for table ans.rocnik: ~0 rows (approximately)
DELETE FROM `edition`;
INSERT INTO `edition` (`edition_id`, `conference_id`, `year`, `location`, `start_date`, `end_date`) VALUES
    (1, 1, 2027, 'Trnava, Slovakia', '2027-05-20', '2027-05-21');

-- Dumping data for table ans.kategoria_registracie: ~3 rows (approximately)
DELETE FROM `registration_categories`;
INSERT INTO `registration_categories` (`category_id`, `edition_id`, `name`, `description`) VALUES
	(1, 1, 'Registered speaker', 'Participant giving an oral presentation.'),
	(2, 1, 'Poster author', 'Participant presenting a poster.'),
	(3, 1, 'Passive participant', 'Participant without a submitted presentation.');

-- Dumping data for table ans.pouzivatelsky_ucet: ~12 rows (approximately)
DELETE FROM `user_accounts`;
INSERT INTO `user_accounts` (`user_id`, `person_id`, `password_hash`, `account_status`, `created_at`) VALUES
	(1, 1, 'MOCK_HASH_USER_1', 'active', '2026-11-01 09:00:00'),
	(2, 2, 'MOCK_HASH_USER_2', 'active', '2026-11-01 09:10:00'),
	(3, 3, 'MOCK_HASH_USER_3', 'active', '2026-11-01 09:20:00'),
	(4, 4, 'MOCK_HASH_USER_4', 'active', '2026-11-02 10:00:00'),
	(5, 5, 'MOCK_HASH_USER_5', 'active', '2026-11-02 10:10:00'),
	(6, 6, 'MOCK_HASH_USER_6', 'active', '2026-11-02 10:20:00'),
	(7, 7, 'MOCK_HASH_USER_7', 'active', '2026-11-03 08:00:00'),
	(8, 8, 'MOCK_HASH_USER_8', 'active', '2026-11-03 08:10:00'),
	(9, 9, 'MOCK_HASH_USER_9', 'active', '2026-11-03 08:20:00'),
	(10, 10, 'MOCK_HASH_USER_10', 'active', '2026-11-04 11:00:00'),
	(11, 11, 'MOCK_HASH_USER_11', 'active', '2026-11-04 11:10:00'),
	(12, 12, 'MOCK_HASH_USER_12', 'active', '2026-11-04 11:20:00');


-- Dumping data for table ans.prednasajuci_programu: ~0 rows (approximately)
DELETE FROM `program_presenters`;



-- Dumping data for table ans.prispevok: ~0 rows (approximately)
DELETE FROM `submission`;


-- Dumping data for table ans.recenzia_prispevku: ~0 rows (approximately)
DELETE FROM `submission_reviews`;


-- Dumping data for table ans.registracia: ~8 rows (approximately)
DELETE FROM `registration`;
INSERT INTO `registration` (`registration_id`, `person_id`, `edition_id`, `category_id`, `registration_date`, `status`) VALUES
	(1, 7, 1, 1, '2026-11-05 09:15:00', 'confirmed'),
	(2, 8, 1, 1, '2026-11-05 10:20:00', 'confirmed'),
	(3, 9, 1, 2, '2026-11-06 08:30:00', 'confirmed'),
	(4, 10, 1, 2, '2026-11-06 11:45:00', 'pending'),
	(5, 11, 1, 1, '2026-11-07 13:00:00', 'confirmed'),
	(6, 12, 1, 3, '2026-11-08 09:00:00', 'confirmed'),
	(7, 4, 1, 3, '2026-11-08 10:10:00', 'confirmed'),
	(8, 5, 1, 3, '2026-11-09 14:25:00', 'pending');



-- Dumping data for table ans.rola: ~5 rows (approximately)
DELETE FROM `role`;
INSERT INTO `role` (`role_id`, `name`, `description`) VALUES
	(1, 'Superadmin', 'Management of the entire application and user permissions.'),
	(2, 'Organizer', 'Management of the conference edition, registrations, and program.'),
	(3, 'Program Committee', 'Making submission acceptance decisions and preparing the program.'),
	(4, 'Reviewer', 'Reviewing assigned submissions.'),
	(5, 'Participant', 'User with a participant account.');


-- Dumping data for table ans.rola_pouzivatela: ~12 rows (approximately)
DELETE FROM `user_role`;
INSERT INTO `user_role` (`user_id`, `role_id`) VALUES
	(1, 1),
	(2, 2),
	(3, 3),
	(4, 4),
	(5, 4),
	(6, 4),
	(7, 5),
	(8, 5),
	(9, 5),
	(10, 5),
	(11, 5),
	(12, 5);

-- Dumping data for table ans.rozhodnutie: ~0 rows (approximately)
DELETE FROM `decision`;


-- Dumping data for table ans.termin: ~0 rows (approximately)
DELETE FROM `deadline`;

-- Dumping data for table ans.verzia_prispevku: ~0 rows (approximately)
DELETE FROM `submission_versions`;

