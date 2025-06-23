<?php
// api/_boilerplate.php
// app_signature_byeyn.1

// Oturum başlatma (PHP ile oturum yönetimi için gereklidir)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// CORS (Cross-Origin Resource Sharing) başlıkları
// Geliştirme ortamında tüm kaynaklardan erişime izin verir, üretimde kısıtlanmalıdır.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// OPTIONS isteğini yanıtla (CORS preflight için)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Hata raporlamayı aç (geliştirme için)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Otomatik sınıf yükleyici
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/../classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Gelen JSON verisini ayrıştır
$input = json_decode(file_get_contents('php://input'), true);

// Yanıt yapısı için yardımcı fonksiyon
function sendResponse($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit();
}

// Güvenli veri temizleme fonksiyonu
function sanitizeInput($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeInput($value);
        }
    } elseif (is_string($data)) {
        $data = htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    return $data;
}

// Bu dosya diğer API dosyalarında 'require_once' ile kullanılacaktır.
// Kendisi doğrudan çalıştırılmayacaktır.
