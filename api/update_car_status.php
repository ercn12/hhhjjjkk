<?php
// api/update_car_status.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carId = isset($input['car_id']) ? (int)sanitizeInput($input['car_id']) : null;
    $newStatus = isset($input['status']) ? sanitizeInput($input['status']) : null;
    $updatedByUserId = isset($input['updated_by_user_id']) ? sanitizeInput($input['updated_by_user_id']) : null;

    if (!$carId || !$newStatus || !$updatedByUserId) {
        sendResponse(false, 'Araç ID, yeni durum ve güncelleyen kullanıcı bilgisi gerekli.');
    }

    // Geçerli durum kontrolü
    $allowedStatuses = ['waiting', 'washing', 'drying', 'ready', 'completed'];
    if (!in_array($newStatus, $allowedStatuses)) {
        sendResponse(false, 'Geçersiz araç durumu.');
    }

    // Yetkilendirme kontrolü (örnek: sadece çalışanlar ve yöneticiler güncelleyebilir)
    // if (!isset($_SESSION['user_role']) || !Auth::hasPermission($_SESSION['user_role'], ['employee', 'branch_manager', 'operation_manager', 'admin'])) { ... }

    try {
        $carModel = new Car();
        $updatedRows = $carModel->updateCarStatus($carId, $newStatus, $updatedByUserId);

        if ($updatedRows > 0) {
            sendResponse(true, 'Araç durumu başarıyla güncellendi.');
        } else {
            sendResponse(false, 'Araç bulunamadı veya durum güncellenemedi.');
        }
    } catch (PDOException $e) {
        error_log("Araç durumu güncelleme hatası: " . $e->getMessage());
        sendResponse(false, 'Araç durumu güncellenirken bir hata oluştu: ' . $e->getMessage());
    }
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
?>
