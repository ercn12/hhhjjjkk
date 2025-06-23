<?php
// api/delete_user.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($input['user_id']) ? (int)sanitizeInput($input['user_id']) : null;

    if (!$userId) {
        sendResponse(false, 'Kullanıcı ID gerekli.');
    }

    // Yetkilendirme kontrolü (Sadece admin silebilir)
    // if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { ... }

    try {
        $userModel = new User();
        $deletedRows = $userModel->deleteUser($userId);

        if ($deletedRows > 0) {
            sendResponse(true, 'Kullanıcı başarıyla silindi.');
        } else {
            sendResponse(false, 'Kullanıcı bulunamadı veya silinemedi.');
        }
    } catch (PDOException $e) {
        error_log("Kullanıcı silme hatası: " . $e->getMessage());
        sendResponse(false, 'Kullanıcı silinirken bir hata oluştu: ' . $e->getMessage());
    }
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
?>