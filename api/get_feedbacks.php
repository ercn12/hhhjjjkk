<?php
// api/get_feedbacks.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece GET isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== 'all' ? (int)sanitizeInput($_GET['branch_id']) : null;

    // Yetkilendirme kontrolü
    // Bu API'ye erişen kullanıcının rolüne ve şubesine göre filtreleme yapılmalıdır.
    // Örneğin, şube müdürü sadece kendi şubesinin geri bildirimlerini görmeli.
    // if ($currentUserRole === 'branch_manager' && $branchId === null) {
    //     $branchId = $currentUserBranchId;
    // }

    $feedbackModel = new Feedback();
    $feedbacks = $feedbackModel->getAllFeedbacksWithBranchName($branchId);

    sendResponse(true, 'Geri bildirimler başarıyla çekildi.', $feedbacks);
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
?>
