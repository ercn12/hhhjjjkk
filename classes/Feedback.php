<?php
// classes/Feedback.php
// app_signature_byeyn.1

require_once __DIR__ . '/BaseModel.php';

/**
 * Geri Bildirim Yönetimi Sınıfı
 * 'feedbacks' tablosu üzerindeki CRUD işlemlerini yönetir.
 */
class Feedback extends BaseModel {
    public function __construct() {
        parent::__construct('feedbacks');
    }

    /**
     * Yeni bir geri bildirim ekler.
     * @param string $plate Geri bildirimin yapıldığı araç plakası
     * @param int $rating Verilen puan (1-5)
     * @param string|null $comment Yorum metni
     * @param string|null $customerName Müşteri adı
     * @param int|null $branchId Şube ID'si
     * @return int Eklenen geri bildirimin ID'si
     */
    public function createNewFeedback($plate, $rating, $comment = null, $customerName = null, $branchId = null) {
        $data = [
            'plate' => $plate,
            'rating' => $rating,
            'comment' => $comment,
            'customer_name' => $customerName,
            'created_at' => time(), // Mevcut Unix timestamp
            'is_read' => 0, // Varsayılan olarak okunmadı
            'branch_id' => $branchId
        ];
        return $this->create($data);
    }

    /**
     * Bir geri bildirimi okundu olarak işaretler.
     * @param int $feedbackId Geri bildirim ID'si
     * @return int Etkilenen satır sayısı
     */
    public function markAsRead($feedbackId) {
        return $this->update($feedbackId, ['is_read' => 1]);
    }

    /**
     * Tüm geri bildirimleri getirir, şube adı ile birlikte.
     * @param int|null $branchId Şube ID'si (opsiyonel)
     * @return array Geri bildirimler dizisi
     */
    public function getAllFeedbacksWithBranchName($branchId = null) {
        $sql = "SELECT f.*, b.name AS branch_name
                FROM feedbacks f
                LEFT JOIN branches b ON f.branch_id = b.id";
        
        $params = [];
        if ($branchId !== null && $branchId !== 'all') {
            $sql .= " WHERE f.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        $sql .= " ORDER BY f.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Bir geri bildirimi siler.
     * @param int $feedbackId Geri bildirim ID'si
     * @return int Etkilenen satır sayısı
     */
    public function deleteFeedback($feedbackId) {
        return $this->delete($feedbackId);
    }

    /**
     * Ortalama geri bildirim puanını hesaplar.
     * @param int|null $branchId Şube ID'si (opsiyonel)
     * @param int $startTime Zaman aralığı başlangıcı (Unix timestamp)
     * @return float Ortalama puan
     */
    public function getAverageRating($branchId = null, $startTime = 0) {
        $sql = "SELECT AVG(rating) as avg_rating FROM feedbacks WHERE created_at >= :start_time";
        $params = [':start_time' => $startTime];

        if ($branchId !== null && $branchId !== 'all') {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (float)$result['avg_rating'];
    }
}
