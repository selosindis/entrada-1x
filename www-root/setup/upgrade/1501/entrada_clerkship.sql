INSERT INTO `categories` (`category_id`, `category_parent`, `category_code`, `category_type`, `category_name`, `category_desc`, `category_min`, `category_max`, `category_buffer`, `category_start`, `category_finish`, `subcategory_strict`, `category_expiry`, `category_status`, `category_order`, `rotation_id`)
VALUES
(1, 0, NULL, 12, 'School of Medicine', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 'published', 0, 0),
(2, 0, NULL, 13, 'All Students', NULL, NULL, NULL, NULL, 0, 1924927200, 0, 0, 'published', 0, 0),
(3, 2, NULL, 17, 'Example Stream', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 0, 0),
(4, 3, NULL, 32, 'Pediatrics', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 4, 4),
(5, 3, NULL, 32, 'Obstetrics & Gynecology', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 3, 3),
(6, 3, NULL, 32, 'Perioperative', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 5, 5),
(7, 3, NULL, 32, 'Surgery - Urology', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 7, 7),
(8, 3, NULL, 32, 'Surgery - Orthopedic', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 8, 8),
(9, 3, NULL, 32, 'Family Medicine', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 1, 1),
(10, 3, NULL, 32, 'Psychiatry', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 6, 6),
(11, 3, NULL, 32, 'Medicine', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 2, 2),
(12, 3, NULL, 32, 'Integrated', NULL, 2, 2, NULL, 0, 1924927200, 0, 0, 'published', 9, 9);