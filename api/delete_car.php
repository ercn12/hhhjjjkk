<?php
// api/delete_car.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carId = isset($input['car_id']) ? (int)sanitizeInput($input['car_id']) : null;

    if (!$carId) {
        sendResponse(false, 'Araç ID gerekli.');
    }

    // Yetkilendirme kontrolü (Sadece admin silebilir)
    // if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    //      sendResponse(false, 'Bu işlemi yapmaya yetkiniz yok.');
    // }

    try {
        $carModel = new Car();
        $deletedRows = $carModel->delete($carId);

        if ($deletedRows > 0) {
            sendResponse(true, 'Araç başarıyla silindi.');
        } else {
            sendResponse(false, 'Araç bulunamadı veya silinemedi.');
        }
    } catch (PDOException $e) {
        error_log("Araç silme hatası: " . $e->getMessage());
        sendResponse(false, 'Araç silinirken bir hata oluştu: ' . $e->getMessage());
    }
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
