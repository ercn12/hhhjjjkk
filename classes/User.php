<?php
// classes/User.php
// app_signature_byeyn.1

require_once __DIR__ . '/BaseModel.php';

/**
 * Kullanıcı Yönetimi Sınıfı
 * 'users' tablosu üzerindeki CRUD işlemlerini yönetir.
 */
class User extends BaseModel {
    public function __construct() {
        parent::__construct('users');
    }

    /**
     * Yeni bir kullanıcı oluşturur (Firebase UID'si ile birlikte).
     * @param string $firebaseUid Kullanıcının Firebase UID'si
     * @param string $email Kullanıcı e-postası
     * @param string $name Kullanıcı adı
     * @param string $role Kullanıcı rolü
     * @param int|null $branchId Şube ID'si
     * @param string $createdByUserId Kullanıcıyı oluşturan adminin Firebase UID'si
     * @return int Eklenen kullanıcının ID'si
     */
    public function createNewUser($firebaseUid, $email, $name, $role, $branchId = null, $createdByUserId = null) {
        $data = [
            'firebase_uid' => $firebaseUid,
            'email' => $email,
            'name' => $name,
            'role' => $role,
            'created_at' => time(), // Mevcut Unix timestamp
            'created_by_user_id' => $createdByUserId
        ];
        if ($branchId) {
            $data['branch_id'] = $branchId;
        }
        return $this->create($data);
    }

    /**
     * Tüm kullanıcıları getirir, şube bilgisiyle birlikte.
     * @return array Kullanıcılar dizisi
     */
    public function getAllUsersWithBranchName() {
        $stmt = $this->pdo->prepare("SELECT u.*, b.name AS branch_name
                                     FROM users u
                                     LEFT JOIN branches b ON u.branch_id = b.id
                                     ORDER BY u.created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Firebase UID'ye göre kullanıcı bilgilerini veritabanından çeker.
     * Bu fonksiyonu Auth sınıfı da kullanıyor, ancak User sınıfında da bulunması faydalı olabilir.
     * @param string $firebaseUid Firebase'den gelen kullanıcı UID'si
     * @return array|false Kullanıcı verisi veya false
     */
    public function getUserByFirebaseUid($firebaseUid) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE firebase_uid = :firebase_uid");
        $stmt->execute([':firebase_uid' => $firebaseUid]);
        return $stmt->fetch();
    }

    /**
     * Bir kullanıcının rolünü günceller.
     * @param int $userId Kullanıcı ID'si
     * @param string $newRole Yeni rol
     * @return int Etkilenen satır sayısı
     */
    public function updateRole($userId, $newRole) {
        return $this->update($userId, ['role' => $newRole]);
    }

    /**
     * Bir kullanıcının aktiflik durumunu günceller.
     * @param int $userId Kullanıcı ID'si
     * @param int $isActive Yeni aktiflik durumu (0 veya 1)
     * @return int Etkilenen satır sayısı
     */
    public function updateUserStatus($userId, $isActive) {
        return $this->update($userId, ['is_active' => $isActive]);
    }

    /**
     * Bir kullanıcıyı Firebase UID'sine göre siler.
     * @param string $firebaseUid Kullanıcının Firebase UID'si
     * @return int Etkilenen satır sayısı
     */
    public function deleteUserByFirebaseUid($firebaseUid) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE firebase_uid = :firebase_uid");
        $stmt->execute([':firebase_uid' => $firebaseUid]);
        return $stmt->rowCount();
    }
}
