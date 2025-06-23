<?php
// classes/Auth.php
// app_signature_byeyn.1

require_once __DIR__ . '/BaseModel.php';

/**
 * Kimlik Doğrulama Sınıfı
 * Kullanıcı oturumlarını ve yetkilendirmeyi yönetir.
 * Firebase Auth ile entegre çalışır; Firebase UID'sini kullanarak yerel veritabanından rol ve şube bilgisini çeker.
 */
class Auth extends BaseModel {

    public function __construct() {
        parent::__construct('users'); // users tablosu ile çalışır
    }

    /**
     * Firebase UID'ye göre kullanıcı bilgilerini veritabanından çeker.
     * @param string $firebaseUid Firebase'den gelen kullanıcı UID'si
     * @return array|false Kullanıcı verisi veya false
     */
    public function getUserByFirebaseUid($firebaseUid) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE firebase_uid = :firebase_uid");
        $stmt->execute([':firebase_uid' => $firebaseUid]);
        return $stmt->fetch();
    }

    /**
     * Kullanıcı rolüne göre yetki kontrolü yapar.
     * @param string $userRole Kullanıcının mevcut rolü
     * @param array $allowedRoles İzin verilen roller dizisi
     * @return bool Yetki var ise true, yok ise false
     */
    public static function hasPermission($userRole, $allowedRoles) {
        return in_array($userRole, $allowedRoles);
    }
}