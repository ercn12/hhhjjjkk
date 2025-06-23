<?php
// api/get_services.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece GET isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $serviceModel = new Service();
    $services = $serviceModel->getAll('type', 'ASC'); // Hizmetleri türe göre sırala

    sendResponse(true, 'Hizmetler başarıyla çekildi.', $services);
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
?>
