<?php
// config/app_config.php
// app_signature_byeyn.1

// Site Genel Ayarları
define('SITE_NAME', 'Eyn Oto Yıkama Takip Sistemi');

// Uygulamada kullanılabilecek şube bilgileri (Pro Versiyon için örnek)
// Gerçek uygulamada bu veriler veritabanından çekilecektir.
// Bu kısım JavaScript tarafında fetchBranches() fonksiyonu ile doldurulacaktır.
// PHP tarafında sadece placeholder olarak tutulabilir.
$branches = [
    // ['id' => 'branch_merkez', 'name' => 'Merkez Şube', 'address' => 'Merkez Mah. No:1', 'phone' => '0212 111 2233'],
    // ['id' => 'branch_bati', 'name' => 'Batı Şube', 'address' => 'Batı Mah. No:2', 'phone' => '0212 444 5566'],
];

// Uygulamada kullanılabilecek roller
// Bu roller, Admin panelinde kullanıcı eklerken veya düzenlerken seçilebilir.
$roles = [
    'admin' => 'Yönetici (Süper Admin)',
    'operation_manager' => 'Operasyon Müdürü',
    'regional_manager' => 'Bölge Müdürü',
    'branch_manager' => 'Şube Müdürü',
    'employee' => 'Çalışan',
];

// PHP tarafında ek bir Firebase konfigürasyonu gerekiyorsa buraya eklenebilir.
// Ancak mevcut JS tabanlı Firebase entegrasyonu için doğrudan gerekli değildir.