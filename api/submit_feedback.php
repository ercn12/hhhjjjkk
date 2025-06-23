<?php
// api/submit_feedback.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plate = isset($input['plate']) ? sanitizeInput($input['plate']) : null;
    $rating = isset($input['rating']) ? (int)sanitizeInput($input['rating']) : null;
    $comment = isset($input['comment']) ? sanitizeInput($input['comment']) : null;
    $customerName = isset($input['customer_name']) ? sanitizeInput($input['customer_name']) : 'Anonim';
    $branchId = isset($input['branch_id']) && $input['branch_id'] !== '' ? (int)sanitizeInput($input['branch_id']) : null; // Varsayılan veya tespit edilebilir

    if (!$plate || !is_numeric($rating) || $rating < 1 || $rating > 5) {
        sendResponse(false, 'Plaka ve 1-5 arası geçerli bir puan gerekli.');
    }

    try {
        $feedbackModel = new Feedback();
        $newFeedbackId = $feedbackModel->createNewFeedback($plate, $rating, $comment, $customerName, $branchId);
        sendResponse(true, 'Geri bildiriminiz başarıyla gönderildi.', ['id' => $newFeedbackId]);
    } catch (PDOException $e) {
        error_log("Geri bildirim gönderme hatası: " . $e->getMessage());
        sendResponse(false, 'Geri bildirim gönderilirken bir hata oluştu: ' . $e->getMessage());
    }
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
?>
