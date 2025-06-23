<?php
// config/database.php
// app_signature_byeyn.1

// Veritabanı bağlantı ayarları
// Lütfen bu değerleri kendi MySQL sunucunuzun bilgileriyle değiştirin!
define('DB_HOST', 'localhost'); // Hostinger'da genellikle 'localhost' olur
define('DB_NAME', 'u494199722_eyn_oto_yikama'); // Hostinger'dan aldığınız doğru DB adıyla değiştirildi
define('DB_USER', 'u494199722_admin'); // Hostinger'dan aldığınız doğru kullanıcı adıyla değiştirildi
define('DB_PASS', 'Ercnymn.1'); // Kullanıcı şifrenizle değiştirildi

/**
 * Veritabanı bağlantısını kurar ve PDO nesnesini döndürür.
 * Hata durumunda bir PDOException fırlatır.
 *
 * @return PDO PDO veritabanı bağlantı nesnesi.
 * @throws PDOException Eğer veritabanı bağlantısı kurulamazsa.
 */
function connectDB() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Hataları exception olarak fırlat
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Varsayılan fetch modunu assosiyatif array yap
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Prepared statement'ları emüle etme
    ];
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Veritabanı bağlantı hatası: " . $e->getMessage());
        // Hata durumunda direkt die() yerine bir PDOException fırlatıyoruz.
        // Bu, API dosyalarında veya modeli çağıran yerde yakalanabilir.
        throw new PDOException("Veritabanına bağlanılamadı: " . $e->getMessage(), (int)$e->getCode());
    }
}