CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text,
  `setting_type` enum('text','textarea','image','number') NOT NULL DEFAULT 'text',
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('hero_title', 'Segar Hari Ini, Dikirim Hari Ini', 'text'),
('hero_description', 'Ayam, ikan, dan seafood premium langsung dari sumber terpercaya, siap melengkapi hidangan lezat keluarga Anda setiap hari.', 'textarea'),
('hero_image', 'assets/images/hero-bg.jpg', 'image'),
('contact_wa', '628123456789', 'number'),
('contact_email', 'lapakbangsawan@gmail.com', 'text'),
('contact_address', 'Jl. Kalitanjung No 52b, Harjamukti, Cirebon', 'textarea'),
('social_instagram', 'https://instagram.com/lapakbangsawan', 'text')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
