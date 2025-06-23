<?php
// api/add_service.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = isset($input['type']) ? sanitizeInput($input['type']) : null;
    $price = isset($input['price']) ? (float)sanitizeInput($input['price']) : null;
    $estimatedTime = isset($input['estimated_time']) ? (int)sanitizeInput($input['estimated_time']) : null;

    if (!$type || !is_numeric($price)) {
        sendResponse(false, 'Hizmet adı ve geçerli fiyat gerekli.');
    }

    // Yetkilendirme kontrolü (Sadece admin ekleyebilir)
    // if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    //      sendResponse(false, 'Bu işlemi yapmaya yetkiniz yok.');
    // }

    try {
        $serviceModel = new Service();
        $newServiceId = $serviceModel->createNewService($type, $price, $estimatedTime);
        sendResponse(true, 'Hizmet başarıyla eklendi.', ['id' => $newServiceId]);
    } catch (PDOException $e) {
        if ($e->getCode() == '23000' && strpos($e->getMessage(), 'Duplicate entry') !== false) {
            sendResponse(false, 'Bu hizmet adı zaten mevcut.');
        } else {
            error_log("Hizmet ekleme hatası: " . $e->getMessage());
            sendResponse(false, 'Hizmet eklenirken bir hata oluştu: ' . $e->getMessage());
        }
    }
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
