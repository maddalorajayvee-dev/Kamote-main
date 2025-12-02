<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pdo = getDB();

// Get filter parameters
$search = sanitize($_GET['search'] ?? '');
$status_filter = sanitize($_GET['status'] ?? '');
$employee_filter = sanitize($_GET['employee_id'] ?? '');
$date_from = sanitize($_GET['date_from'] ?? '');
$date_to = sanitize($_GET['date_to'] ?? '');

// Get all employees for filter dropdown
$stmt = $pdo->query("SELECT id, employee_id, first_name, last_name, middle_name FROM employees ORDER BY first_name, last_name");
$all_employees = $stmt->fetchAll();

// Build query with filters
$query = "SELECT p.*, e.first_name, e.last_name, e.middle_name, e.employee_id, e.position, e.department 
          FROM payrolls p 
          JOIN employees e ON p.employee_id = e.id 
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_id LIKE ? OR e.position LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

if ($status_filter) {
    $query .= " AND p.status = ?";
    $params[] = $status_filter;
}

if ($employee_filter) {
    $query .= " AND p.employee_id = ?";
    $params[] = $employee_filter;
}

if ($date_from) {
    $query .= " AND p.pay_period_end >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $query .= " AND p.pay_period_end <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY p.pay_period_end DESC, p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$payrolls = $stmt->fetchAll();

// Calculate summary statistics
$summary = [
    'total_records' => count($payrolls),
    'total_gross' => 0,
    'total_net' => 0,
    'total_deductions' => 0,
    'total_allowances' => 0,
    'total_bonuses' => 0,
    'total_overtime' => 0,
    'by_status' => [
        'Paid' => 0,
        'Approved' => 0,
        'Pending' => 0
    ]
];

foreach ($payrolls as $payroll) {
    $summary['total_gross'] += $payroll['gross_pay'];
    $summary['total_net'] += $payroll['net_pay'];
    $summary['total_deductions'] += $payroll['deductions'];
    $summary['total_allowances'] += $payroll['allowances'];
    $summary['total_bonuses'] += $payroll['bonuses'];
    $summary['total_overtime'] += $payroll['overtime_pay'];
    if (isset($summary['by_status'][$payroll['status']])) {
        $summary['by_status'][$payroll['status']]++;
    }
}

// Get overall statistics
$overall_stats = [
    'total_payrolls' => $pdo->query("SELECT COUNT(*) FROM payrolls")->fetchColumn(),
    'total_employees' => $pdo->query("SELECT COUNT(DISTINCT employee_id) FROM payrolls")->fetchColumn(),
    'oldest_record' => $pdo->query("SELECT MIN(pay_period_start) FROM payrolls")->fetchColumn(),
    'newest_record' => $pdo->query("SELECT MAX(pay_period_end) FROM payrolls")->fetchColumn(),
];

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
    <title>Payroll Archive - Barangay Sto. Angel Payroll</title>
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
            <div class="glass-card tilt-hover p-6">
                <p class="text-sm uppercase tracking-widest text-gray-400">Historical Records</p>
                <h1 class="text-3xl font-bold text-gray-900">Payroll Archive</h1>
                <p class="mt-2 text-sm text-gray-600">View and search all payroll records across all employees.</p>
            </div>

            <!-- Overall Statistics -->
            <div class="glass-card tilt-hover p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Archive Overview</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Total Records</p>
                        <p class="text-2xl font-bold text-blue-700"><?php echo number_format($overall_stats['total_payrolls']); ?></p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Employees with Records</p>
                        <p class="text-2xl font-bold text-green-700"><?php echo number_format($overall_stats['total_employees']); ?></p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Earliest Record</p>
                        <p class="text-lg font-bold text-purple-700">
                            <?php echo $overall_stats['oldest_record'] ? date('M Y', strtotime($overall_stats['oldest_record'])) : 'N/A'; ?>
                        </p>
                    </div>
                    <div class="bg-orange-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Latest Record</p>
                        <p class="text-lg font-bold text-orange-700">
                            <?php echo $overall_stats['newest_record'] ? date('M Y', strtotime($overall_stats['newest_record'])) : 'N/A'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="glass-card tilt-hover p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Filters</h2>
                <form method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input
                                type="text"
                                name="search"
                                placeholder="Employee name, ID, or position..."
                                value="<?php echo htmlspecialchars($search); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                            <select name="employee_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="">All Employees</option>
                                <?php foreach ($all_employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>" <?php echo $employee_filter === $emp['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($emp['employee_id'] . ' - ' . $emp['first_name'] . ' ' . ($emp['middle_name'] ? $emp['middle_name'] . ' ' : '') . $emp['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="">All Status</option>
                                <option value="Paid" <?php echo $status_filter === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                            <input
                                type="date"
                                name="date_from"
                                value="<?php echo htmlspecialchars($date_from); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                            <input
                                type="date"
                                name="date_to"
                                value="<?php echo htmlspecialchars($date_to); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            />
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-6 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded-lg transition">
                            Apply Filters
                        </button>
                        <a href="<?php echo base_url('admin/payroll-archive.php'); ?>" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                            Clear All
                        </a>
                    </div>
                </form>
            </div>

            <!-- Summary for Filtered Results -->
            <?php if (!empty($payrolls) || $search || $status_filter || $employee_filter || $date_from || $date_to): ?>
                <div class="glass-card tilt-hover p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtered Results Summary</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Records Found</p>
                            <p class="text-2xl font-bold text-blue-700"><?php echo $summary['total_records']; ?></p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Gross Pay</p>
                            <p class="text-xl font-bold text-green-700"><?php echo formatCurrency($summary['total_gross']); ?></p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Net Pay</p>
                            <p class="text-xl font-bold text-purple-700"><?php echo formatCurrency($summary['total_net']); ?></p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Deductions</p>
                            <p class="text-xl font-bold text-red-700"><?php echo formatCurrency($summary['total_deductions']); ?></p>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                            Paid: <?php echo $summary['by_status']['Paid']; ?>
                        </span>
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                            Approved: <?php echo $summary['by_status']['Approved']; ?>
                        </span>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                            Pending: <?php echo $summary['by_status']['Pending']; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Payroll Records Table -->
            <div class="glass-card overflow-hidden">
                <?php if (empty($payrolls)): ?>
                    <div class="px-6 py-8 text-center text-gray-500">
                        No payroll records found matching your filters.
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pay Period</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Base Salary</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Allowances</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Bonuses</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Pay</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($payrolls as $payroll): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($payroll['first_name'] . ' ' . ($payroll['middle_name'] ? $payroll['middle_name'] . ' ' : '') . $payroll['last_name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($payroll['employee_id']); ?> | <?php echo htmlspecialchars($payroll['position']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo date('M d, Y', strtotime($payroll['pay_period_start'])); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                to <?php echo date('M d, Y', strtotime($payroll['pay_period_end'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            <?php echo formatCurrency($payroll['base_salary']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            <?php echo formatCurrency($payroll['overtime_pay']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            <?php echo formatCurrency($payroll['allowances']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            <?php echo formatCurrency($payroll['bonuses']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 text-right">
                                            <?php echo formatCurrency($payroll['deductions']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                            <?php echo formatCurrency($payroll['gross_pay']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-primary-700 text-right">
                                            <?php echo formatCurrency($payroll['net_pay']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo getStatusColor($payroll['status']); ?>">
                                                <?php echo htmlspecialchars($payroll['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                            <div class="flex justify-center gap-2">
                                                <a href="<?php echo base_url('admin/payroll-print.php?id=' . $payroll['id']); ?>" target="_blank" class="group inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-sm font-medium px-2 py-1 rounded hover:bg-blue-50 transition relative" title="Print">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                    </svg>
                                                    <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Print</span>
                                                </a>
                                                <a href="<?php echo base_url('admin/payroll.php?action=edit&id=' . $payroll['id']); ?>" class="group inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 text-sm font-medium px-2 py-1 rounded hover:bg-primary-50 transition relative" title="Edit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Edit</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (count($payrolls) > 5): ?>
                                    <tr class="bg-gray-100 font-bold">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" colspan="2">TOTAL</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            <?php echo formatCurrency(array_sum(array_column($payrolls, 'base_salary'))); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            <?php echo formatCurrency($summary['total_overtime']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            <?php echo formatCurrency($summary['total_allowances']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            <?php echo formatCurrency($summary['total_bonuses']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 text-right">
                                            <?php echo formatCurrency($summary['total_deductions']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            <?php echo formatCurrency($summary['total_gross']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-primary-700 text-right">
                                            <?php echo formatCurrency($summary['total_net']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap"></td>
                                        <td class="px-6 py-4 whitespace-nowrap"></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>

