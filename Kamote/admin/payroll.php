<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

// Handle form actions (new/edit)
if (isset($_GET['action']) && ($_GET['action'] === 'new' || $_GET['action'] === 'edit')) {
    require_once __DIR__ . '/payroll-form.php';
    exit;
}

$pdo = getDB();
$search = sanitize($_GET['search'] ?? '');
$overview = [
    'total_payroll' => $pdo->query("SELECT COUNT(*) FROM payrolls")->fetchColumn(),
    'pending_payroll' => $pdo->query("SELECT COUNT(*) FROM payrolls WHERE status = 'Pending'")->fetchColumn(),
    'latest_disbursement' => $pdo->query("SELECT COALESCE(MAX(pay_period_end), NULL) FROM payrolls")->fetchColumn(),
];

// Handle delete
if (isset($_GET['delete']) && $_GET['delete']) {
    $id = sanitize($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM payrolls WHERE id = ?");
    $stmt->execute([$id]);
    redirect(base_url('admin/payroll.php?deleted=1'));
}

// Get payrolls with employee info
$query = "SELECT p.*, e.first_name, e.last_name, e.employee_id 
          FROM payrolls p 
          JOIN employees e ON p.employee_id = e.id";
$params = [];

if ($search) {
    $query .= " WHERE e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_id LIKE ?";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

$query .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$payrolls = $stmt->fetchAll();

function getStatusColor($status) {
    switch ($status) {
        case 'Paid':
            return 'bg-green-100 text-green-800';
        case 'Approved':
            return 'bg-blue-100 text-blue-800';
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll - Barangay Sto. Angel Payroll</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            600: '#1fb9aa',
                            700: '#159488',
                        },
                    },
                },
            },
        }
    </script>
</head>
<body class="min-h-screen page-gradient">
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0 space-y-6">
            <div class="glass-card tilt-hover p-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-widest text-gray-400">Treasury Flow</p>
                    <h1 class="text-3xl font-bold text-gray-900">Payroll</h1>
                    <p class="mt-2 text-sm text-gray-600">Monitor disbursement batches and approval status.</p>
                </div>
                <div class="data-grid">
                    <div class="data-pill">
                        <span class="inline-block w-3 h-3 rounded-full bg-emerald-500"></span>
                        Total Batches: <?php echo $overview['total_payroll']; ?>
                    </div>
                    <div class="data-pill">
                        <span class="pulse-dot inline-block w-3 h-3 rounded-full bg-amber-500"></span>
                        Pending: <?php echo $overview['pending_payroll']; ?>
                    </div>
                    <?php if ($overview['latest_disbursement']): ?>
                    <div class="data-pill">
                        <span class="inline-block w-3 h-3 rounded-full bg-blue-500"></span>
                        Last release: <?php echo date('M d, Y', strtotime($overview['latest_disbursement'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="flex gap-2">
                    <a href="<?php echo base_url('admin/payroll-archive.php'); ?>" class="px-6 py-3 bg-sky-500 hover:bg-sky-600 text-white rounded-full transition text-center">
                         View Archive
                    </a>
                    <a href="<?php echo base_url('admin/payroll.php?action=new'); ?>" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-full transition text-center">
                        + Create Payroll
                    </a>
                </div>
            </div>

            <div class="glass-card tilt-hover p-4">
                <form method="GET" class="flex flex-col md:flex-row gap-2">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search payroll records..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    />
                    <div class="flex gap-2">
                        <button type="submit" class="px-5 py-3 bg-sky-500 hover:bg-sky-600 text-white rounded-lg transition">
                            Search
                        </button>
                        <?php if ($search): ?>
                            <a href="<?php echo base_url('admin/payroll.php'); ?>" class="px-5 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                                Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="glass-card tilt-hover p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Generate Batch Payroll</h2>
                <p class="text-sm text-gray-600 mb-4">Generate a batch payroll document for a specific pay period (matching the official format).</p>
                <form method="GET" action="<?php echo base_url('admin/payroll-batch-print.php'); ?>" target="_blank" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pay Period Start</label>
                        <input
                            type="date"
                            name="period_start"
                            required
                            value="<?php echo date('Y-m-01'); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        />
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pay Period End</label>
                        <input
                            type="date"
                            name="period_end"
                            required
                            value="<?php echo date('Y-m-t'); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        />
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-6 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded-lg transition">
                            📄 Generate Batch Payroll
                        </button>
                    </div>
                </form>
            </div>

            <div class="glass-card overflow-hidden">
                <?php if (empty($payrolls)): ?>
                    <div class="px-6 py-8 text-center text-gray-500">
                        No payroll records found
                    </div>
                <?php else: ?>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($payrolls as $payroll): ?>
                            <li class="px-6 py-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($payroll['first_name'] . ' ' . $payroll['last_name']); ?>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            ID: <?php echo htmlspecialchars($payroll['employee_id']); ?> | 
                                            Period: <?php echo date('M d, Y', strtotime($payroll['pay_period_start'])); ?> - 
                                            <?php echo date('M d, Y', strtotime($payroll['pay_period_end'])); ?>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Gross Pay: <?php echo formatCurrency($payroll['gross_pay']); ?> | 
                                            Net Pay: <?php echo formatCurrency($payroll['net_pay']); ?> | 
                                            Status: <span class="font-medium px-2 py-1 rounded <?php echo getStatusColor($payroll['status']); ?>">
                                                <?php echo htmlspecialchars($payroll['status']); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="<?php echo base_url('admin/payroll-print.php?id=' . $payroll['id']); ?>" target="_blank" class="group inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-sm font-medium px-2 py-1 rounded hover:bg-blue-50 transition relative">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Print</span>
                                        </a>
                                        <a href="<?php echo base_url('admin/payroll.php?action=edit&id=' . $payroll['id']); ?>" class="group inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 text-sm font-medium px-2 py-1 rounded hover:bg-primary-50 transition relative">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Edit</span>
                                        </a>
                                        <a href="<?php echo base_url('admin/payroll.php?delete=' . $payroll['id']); ?>" 
                                           onclick="return confirm('Are you sure you want to delete this payroll record?')"
                                           class="group inline-flex items-center gap-1 text-red-600 hover:text-red-800 text-sm font-medium px-2 py-1 rounded hover:bg-red-50 transition relative">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Delete</span>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>

