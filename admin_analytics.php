<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

secure_session_start();
require_login();
require_role(['Admin']);

$pageTitle = "Analytics - BankAssist";
require_once 'includes/header.php';

$totalCust = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();

$totalReq = $pdo->query("SELECT COUNT(*) FROM service_requests")->fetchColumn();

$statusCounts = $pdo->query("SELECT status, COUNT(*) as cnt FROM service_requests GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$open = ($statusCounts['Open'] ?? 0) + ($statusCounts['In Progress'] ?? 0);
$completed = $statusCounts['Completed'] ?? 0;

$monthly = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as m, COUNT(*) as cnt 
    FROM customers 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY m 
    ORDER BY m ASC
")->fetchAll(PDO::FETCH_ASSOC);

$months = [];
$counts = [];
foreach ($monthly as $row) {
    $months[] = date('M Y', strtotime($row['m'] . '-01'));
    $counts[] = $row['cnt'];
}
?>

<div class="flex h-screen bg-gray-50 dark:bg-brand-dark overflow-hidden">
  
    <?php include 'includes/sidebar_staff.php'; ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">Platform Analytics</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="glass-panel p-6 flex flex-col">
                    <span class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Customers</span>
                    <span class="text-4xl font-bold text-gray-900 dark:text-white mt-2"><?= $totalCust ?></span>
                </div>
                <div class="glass-panel p-6 flex flex-col">
                     <span class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Requests</span>
                    <span class="text-4xl font-bold text-gray-900 dark:text-white mt-2"><?= $totalReq ?></span>
                </div>
                <div class="glass-panel p-6 flex flex-col border-l-4 border-l-blue-500">
                     <span class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Active Requests</span>
                    <span class="text-4xl font-bold text-gray-900 dark:text-white mt-2"><?= $open ?></span>
                </div>
                 <div class="glass-panel p-6 flex flex-col border-l-4 border-l-green-500">
                     <span class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Completed Requests</span>
                    <span class="text-4xl font-bold text-gray-900 dark:text-white mt-2"><?= $completed ?></span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              
                <div class="glass-panel p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Customer Growth (Last 12 Months)</h3>
                    <div class="h-64">
                         <canvas id="growthChart"></canvas>
                    </div>
                </div>

                <div class="glass-panel p-6">
                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Request Distribution</h3>
                     <div class="h-64 flex justify-center">
                         <canvas id="statusChart"></canvas>
                     </div>
                </div>

            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  
    const ctxGrowth = document.getElementById('growthChart').getContext('2d');
    new Chart(ctxGrowth, {
        type: 'line',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{
                label: 'New Customers',
                data: <?= json_encode($counts) ?>,
                borderColor: '#10B981', 
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#9CA3AF' } },
                x: { grid: { display: false }, ticks: { color: '#9CA3AF' } }
            }
        }
    });

  
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Open/In Progress', 'Completed'],
            datasets: [{
                data: [<?= $open ?>, <?= $completed ?>],
                backgroundColor: ['#3B82F6', '#10B981'],
                borderWidth: 0
            }]
        },
        options: {
             responsive: true,
             maintainAspectRatio: false,
             plugins: {
                 legend: { position: 'bottom', labels: { color: '#FFF' } }
             }
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
