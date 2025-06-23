<?php
// api/delete_branch.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branchId = isset($input['branch_id']) ? (int)sanitizeInput($input['branch_id']) : null;

    if (!$branchId) {
        sendResponse(false, 'Şube ID gerekli.');
    }

    // Yetkilendirme kontrolü (Sadece admin silebilir)
    // if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    //      sendResponse(false, 'Bu işlemi yapmaya yetkiniz yok.');
    // }

    try {
        $branchModel = new Branch();
        $deletedRows = $branchModel->deleteBranch($branchId);

        if ($deletedRows > 0) {
            sendResponse(true, 'Şube başarıyla silindi.');
        } else {
            sendResponse(false, 'Şube bulunamadı veya silinemedi.');
        }
    } catch (PDOException $e) {
        error_log("Şube silme hatası: " . $e->getMessage());
        sendResponse(false, 'Şube silinirken bir hata oluştu: ' . $e->getMessage());
    }
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
