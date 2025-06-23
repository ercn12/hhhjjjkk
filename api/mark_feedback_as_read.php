<?php
// api/mark_feedback_as_read.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedbackId = isset($input['feedback_id']) ? (int)sanitizeInput($input['feedback_id']) : null;

    if (!$feedbackId) {
        sendResponse(false, 'Geri bildirim ID gerekli.');
    }

    // Yetkilendirme kontrolü (Sadece admin/operasyon müdürü/bölge müdürü işaretleyebilir)
    // if (!isset($_SESSION['user_role']) || !Auth::hasPermission($_SESSION['user_role'], ['admin', 'operation_manager', 'regional_manager'])) { ... }

    try {
        $feedbackModel = new Feedback();
        $updatedRows = $feedbackModel->markAsRead($feedbackId);

        if ($updatedRows > 0) {
            sendResponse(true, 'Geri bildirim okundu olarak işaretlendi.');
        } else {
            sendResponse(false, 'Geri bildirim bulunamadı veya güncellenemedi.');
        }
    } catch (PDOException $e) {
        error_log("Geri bildirim okundu olarak işaretleme hatası: " . $e->getMessage());
        sendResponse(false, 'İşlem başarısız: ' . $e->getMessage());
    }
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
?>
