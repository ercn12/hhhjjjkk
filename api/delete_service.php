<?php
// api/delete_service.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = isset($input['service_id']) ? (int)sanitizeInput($input['service_id']) : null;

    if (!$serviceId) {
        sendResponse(false, 'Hizmet ID gerekli.');
    }

    // Yetkilendirme kontrolü (Sadece admin silebilir)
    // if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    //      sendResponse(false, 'Bu işlemi yapmaya yetkiniz yok.');
    // }

    try {
        $serviceModel = new Service();
        $deletedRows = $serviceModel->deleteService($serviceId);

        if ($deletedRows > 0) {
            sendResponse(true, 'Hizmet başarıyla silindi.');
        } else {
            sendResponse(false, 'Hizmet bulunamadı veya silinemedi.');
        }
    } catch (PDOException $e) {
        error_log("Hizmet silme hatası: " . $e->getMessage());
        sendResponse(false, 'Hizmet silinirken bir hata oluştu: ' . $e->getMessage());
    }
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
