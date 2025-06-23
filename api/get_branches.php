<?php
// api/get_branches.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece GET isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $branchModel = new Branch();
    $branches = $branchModel->getAll('name', 'ASC'); // Şubeleri isme göre sırala

    sendResponse(true, 'Şubeler başarıyla çekildi.', $branches);
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
?>
