CREATE DATABASE IF NOT EXISTS products
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE products;

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `availability_date` date DEFAULT NULL,
  `in_stock` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- initial data
INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `availability_date`, `in_stock`) VALUES
(1, 'Mouse', 'Mouse USB', 50.00, NULL, NULL, 1),
(2, 'Tastatura', 'Tastatura simplaa', 1211.55, NULL, NULL, 0);

ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);