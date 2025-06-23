-- eyn_oto_yikama.sql
-- app_signature_byeyn.1

-- Veritabanını oluştur (eğer yoksa)
CREATE DATABASE IF NOT EXISTS `eyn_oto_yikama` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Veritabanını kullan
USE `eyn_oto_yikama`;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firebase_uid` VARCHAR(255) UNIQUE NOT NULL, -- Firebase UID'si
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'operation_manager', 'regional_manager', 'branch_manager', 'employee') NOT NULL DEFAULT 'employee',
    `branch_id` INT NULL, -- Hangi şubeye ait olduğu (NULL ise genel/merkez)
    `created_at` INT NOT NULL, -- Unix timestamp
    `created_by_user_id` VARCHAR(255) NULL, -- Firebase UID'si, kimin oluşturduğu
    `is_active` TINYINT(1) NOT NULL DEFAULT 1, -- Kullanıcının aktif olup olmadığı
    INDEX (`branch_id`),
    INDEX (`role`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Şubeler tablosu
CREATE TABLE IF NOT EXISTS `branches` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) UNIQUE NOT NULL,
    `address` TEXT NULL,
    `phone` VARCHAR(50) NULL,
    `created_at` INT NOT NULL, -- Unix timestamp
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 -- Şubenin aktif olup olmadığı
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hizmetler tablosu
CREATE TABLE IF NOT EXISTS `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(255) UNIQUE NOT NULL, -- Hizmet adı (örn: İç Dış Yıkama, Cilalı Yıkama)
    `price` DECIMAL(10, 2) NOT NULL,
    `estimated_time` INT NULL, -- Dakika cinsinden tahmini süre
    `created_at` INT NOT NULL, -- Unix timestamp
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 -- Hizmetin aktif olup olmadığı
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Araçlar tablosu
CREATE TABLE IF NOT EXISTS `cars` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `plate` VARCHAR(50) NOT NULL,
    `customer_name` VARCHAR(255) NULL,
    `customer_phone` VARCHAR(50) NULL,
    `service_type` VARCHAR(255) NOT NULL, -- Hangi hizmetin alındığı (Hizmetler tablosuna FK eklenebilir, şimdilik string)
    `estimated_time` INT NULL, -- Tahmini bitiş süresi (dakika)
    `price` DECIMAL(10, 2) NULL,
    `status` ENUM('waiting', 'washing', 'drying', 'ready', 'completed') NOT NULL DEFAULT 'waiting',
    `created_at` INT NOT NULL, -- Kayıt tarihi (Unix timestamp)
    `created_by_user_id` VARCHAR(255) NULL, -- Firebase UID'si, kimin kaydettiği
    `updated_at` INT NULL, -- Son güncelleme tarihi
    `updated_by_user_id` VARCHAR(255) NULL, -- Son kimin güncellediği
    `branch_id` INT NOT NULL, -- Hangi şubede yıkandığı
    INDEX (`plate`),
    INDEX (`status`),
    INDEX (`branch_id`),
    FOREIGN KEY (`created_by_user_id`) REFERENCES `users`(`firebase_uid`) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (`updated_by_user_id`) REFERENCES `users`(`firebase_uid`) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Geri Bildirimler tablosu
CREATE TABLE IF NOT EXISTS `feedbacks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `plate` VARCHAR(50) NOT NULL,
    `rating` INT NOT NULL, -- 1-5 arası puan
    `comment` TEXT NULL,
    `customer_name` VARCHAR(255) NULL,
    `created_at` INT NOT NULL, -- Unix timestamp
    `is_read` TINYINT(1) NOT NULL DEFAULT 0, -- Okundu bilgisi
    `branch_id` INT NULL, -- Hangi şubeye verildiği (eğer müşteri sorgulama ekranında şube bilgisi alınabilirse)
    INDEX (`plate`),
    INDEX (`branch_id`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Eğer gerekli ise örnek bir admin kullanıcısı ekle (Şifreyi kendiniz belirleyin, şifreleme ile kaydetmek güvenlik için önemlidir.)
-- Bu örnek sadece Firebase Auth'a ekledikten sonra manuel olarak veritabanına eklemek içindir.
-- INSERT INTO `users` (`firebase_uid`, `email`, `name`, `role`, `created_at`, `is_active`) VALUES
-- ('FIREBASE_ADMIN_UID_BURAYA', 'admin@example.com', 'Admin Kullanıcısı', 'admin', UNIX_TIMESTAMP(), 1);

-- Eğer gerekli ise örnek şube ekle
-- INSERT INTO `branches` (`name`, `address`, `phone`, `created_at`, `is_active`) VALUES
-- ('Merkez Şube', 'Örnek Cad. No:1, İstanbul', '02121234567', UNIX_TIMESTAMP(), 1);

-- Eğer gerekli ise örnek hizmet ekle
-- INSERT INTO `services` (`type`, `price`, `estimated_time`, `created_at`, `is_active`) VALUES
-- ('Dış Yıkama', 100.00, 30, UNIX_TIMESTAMP(), 1),
-- ('İç Dış Yıkama', 150.00, 45, UNIX_TIMESTAMP(), 1),
-- ('Detaylı Temizlik', 500.00, 180, UNIX_TIMESTAMP(), 1);
