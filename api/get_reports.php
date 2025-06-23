<?php
// api/get_reports.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece GET isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $timeframe = isset($_GET['timeframe']) ? sanitizeInput($_GET['timeframe']) : 'daily';
    $branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== 'all' ? (int)sanitizeInput($_GET['branch_id']) : null;

    // Yetkilendirme kontrolü
    // Raporlara erişen kullanıcının rolüne göre filtreleme veya kısıtlama yapılmalı.

    $carModel = new Car();
    $feedbackModel = new Feedback();

    $startOfDay = strtotime('today midnight'); // Bugünün başlangıcı
    $endOfDay = strtotime('tomorrow midnight') - 1; // Bugünün sonu

    $startOfWeek = strtotime('last monday', $startOfDay); // Haftanın başlangıcı
    $endOfWeek = strtotime('next monday', $startOfDay) - 1;

    $startOfMonth = strtotime('first day of this month', $startOfDay); // Ayın başlangıcı
    $endOfMonth = strtotime('first day of next month', $startOfDay) - 1;

    $startOfYear = strtotime('first day of january this year', $startOfDay); // Yılın başlangıcı
    $endOfYear = strtotime('first day of january next year', $startOfDay) - 1;

    $cars = [];
    $feedbacks = [];

    $reportCars = [];
    $reportFeedbacks = [];

    // Zaman aralığına göre verileri çek
    if ($timeframe === 'all') {
        $reportCars = $carModel->getAllCarsWithDetails($branchId);
        $reportFeedbacks = $feedbackModel->getAllFeedbacksWithBranchName($branchId);
    } else {
        // Cars for reporting
        $sqlCars = "SELECT c.*, b.name AS branch_name, u_created.name AS created_by_name
                    FROM cars c
                    LEFT JOIN branches b ON c.branch_id = b.id
                    LEFT JOIN users u_created ON c.created_by_user_id = u_created.firebase_uid
                    WHERE c.created_at BETWEEN :start_time AND :end_time";
        $paramsCars = [];

        // Feedbacks for reporting
        $sqlFeedbacks = "SELECT f.*, b.name AS branch_name
                         FROM feedbacks f
                         LEFT JOIN branches b ON f.branch_id = b.id
                         WHERE f.created_at BETWEEN :start_time AND :end_time";
        $paramsFeedbacks = [];

        if ($branchId !== null) {
            $sqlCars .= " AND c.branch_id = :branch_id";
            $paramsCars[':branch_id'] = $branchId;
            $sqlFeedbacks .= " AND f.branch_id = :branch_id";
            $paramsFeedbacks[':branch_id'] = $branchId;
        }

        switch ($timeframe) {
            case 'daily':
                $paramsCars[':start_time'] = $startOfDay;
                $paramsCars[':end_time'] = $endOfDay;
                $paramsFeedbacks[':start_time'] = $startOfDay;
                $paramsFeedbacks[':end_time'] = $endOfDay;
                break;
            case 'weekly':
                $paramsCars[':start_time'] = $startOfWeek;
                $paramsCars[':end_time'] = $endOfWeek;
                $paramsFeedbacks[':start_time'] = $startOfWeek;
                $paramsFeedbacks[':end_time'] = $endOfWeek;
                break;
            case 'monthly':
                $paramsCars[':start_time'] = $startOfMonth;
                $paramsCars[':end_time'] = $endOfMonth;
                $paramsFeedbacks[':start_time'] = $startOfMonth;
                $paramsFeedbacks[':end_time'] = $endOfMonth;
                break;
            case 'yearly':
                $paramsCars[':start_time'] = $startOfYear;
                $paramsCars[':end_time'] = $endOfYear;
                $paramsFeedbacks[':start_time'] = $startOfYear;
                $paramsFeedbacks[':end_time'] = $endOfYear;
                break;
        }

        $stmtCars = $carModel->pdo->prepare($sqlCars . " ORDER BY c.created_at DESC");
        $stmtCars->execute($paramsCars);
        $reportCars = $stmtCars->fetchAll();

        $stmtFeedbacks = $feedbackModel->pdo->prepare($sqlFeedbacks . " ORDER BY f.created_at DESC");
        $stmtFeedbacks->execute($paramsFeedbacks);
        $reportFeedbacks = $stmtFeedbacks->fetchAll();
    }

    // Rapor verilerini hesapla
    $totalCars = count($reportCars);
    $activeCars = count(array_filter($reportCars, function($car) {
        return $car['status'] !== 'completed';
    }));
    $completedCars = count(array_filter($reportCars, function($car) {
        return $car['status'] === 'completed';
    }));

    $totalRevenue = array_reduce($reportCars, function($sum, $car) {
        return $sum + ($car['status'] === 'completed' ? (float)$car['price'] : 0);
    }, 0.0);

    $totalRating = array_reduce($reportFeedbacks, function($sum, $feedback) {
        return $sum + (int)$feedback['rating'];
    }, 0);
    $avgRating = $reportFeedbacks ? round($totalRating / count($reportFeedbacks), 1) : 0.0;

    $response = [
        'total_cars' => $totalCars,
        'active_cars' => $activeCars,
        'completed_cars' => $completedCars,
        'total_revenue' => $totalRevenue,
        'avg_rating' => $avgRating,
        'daily_cars' => $reportCars // Detaylı araç listesi
        // Diğer rapor detayları buraya eklenebilir (örn: personel performansı, hizmet karlılığı)
    ];

    sendResponse(true, 'Rapor verileri başarıyla çekildi.', $response);
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
?>
