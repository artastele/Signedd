-- Stage 3: Interactive Micro-Learning, FSL Vocabulary, Smart Remediation & SF9 Progress Hub
-- File: config/v59_stage3_learning_fsl_remedial_sf9.sql

-- 1. Create FSL Vocabulary Library Table
CREATE TABLE IF NOT EXISTS `fsl_vocabulary` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `word` VARCHAR(100) NOT NULL,
    `category` VARCHAR(100) NOT NULL DEFAULT 'General',
    `video_path` VARCHAR(255) NULL,
    `gif_path` VARCHAR(255) NULL,
    `thumbnail_path` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`word`),
    INDEX (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Seed Common FSL Vocabulary Terms for SPED / DHH Learning
INSERT INTO `fsl_vocabulary` (`word`, `category`, `gif_path`, `description`) VALUES
('enrollment', 'School Terms', 'images/fsl/enrollment.svg', 'Registration or entering into a school program'),
('teacher', 'School Terms', 'images/fsl/teacher.svg', 'Guro o magtutudlo sa eskwelahan'),
('student', 'School Terms', 'images/fsl/student.svg', 'Tinun-an o mag-aaral'),
('school', 'School Terms', 'images/fsl/school.svg', 'Tunghaan o eskwelahan'),
('family', 'Daily Living', 'images/fsl/family.svg', 'Pamilya o panimalay'),
('help', 'Daily Living', 'images/fsl/help.svg', 'Tabang o pagtabang'),
('friend', 'Socio-Emotional', 'images/fsl/friend.svg', 'Higala o barkada'),
('read', 'Learning', 'images/fsl/read.svg', 'Pagbasa sa libro o teksto'),
('write', 'Learning', 'images/fsl/write.svg', 'Pagsulat gamit ang lapis o papel'),
('thank you', 'Social Courtesy', 'images/fsl/thank_you.svg', 'Salamat o pagpasalamat')
ON DUPLICATE KEY UPDATE `word` = VALUES(`word`);
