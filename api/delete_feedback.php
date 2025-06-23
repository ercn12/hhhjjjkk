<?php
// api/delete_feedback.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedbackId = isset($input['feedback_id']) ? (int)sanitizeInput($input['feedback_id']) : null;

    if (!$feedbackId) {
        sendResponse(false, 'Geri bildirim ID gerekli.');
    }

    // Yetkilendirme kontrolü (Sadece admin silebilir)
    // if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    //      sendResponse(false, 'Bu işlemi yapmaya yetkiniz yok.');
    // }

    try {
        $feedbackModel = new Feedback();
        $deletedRows = $feedbackModel->deleteFeedback($feedbackId);

        if ($deletedRows > 0) {
            sendResponse(true, 'Geri bildirim başarıyla silindi.');
        } else {
            sendResponse(false, 'Geri bildirim bulunamadı veya silinemedi.');
        }
    } catch (PDOException $e) {
        error_log("Geri bildirim silme hatası: " . $e->getMessage());
        sendResponse(false, 'Silme işlemi başarısız: ' . $e->getMessage());
    }
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
