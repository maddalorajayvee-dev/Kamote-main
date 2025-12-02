<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pdo = getDB();
$employee_id = sanitize($_GET['employee_id'] ?? '');
$date_from = sanitize($_GET['date_from'] ?? '');
$date_to = sanitize($_GET['date_to'] ?? '');

// Get all employees for dropdown
$stmt = $pdo->query("SELECT id, employee_id, first_name, last_name, middle_name FROM employees ORDER BY first_name, last_name");
$employees = $stmt->fetchAll();

$employee = null;
$payrolls = [];
$summary = [
    'total_gross' => 0,
    'total_net' => 0,
    'total_deductions' => 0,
    'total_allowances' => 0,
    'total_bonuses' => 0,
    'total_overtime' => 0,
    'count' => 0
];

if ($employee_id) {
    // Get employee info
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch();
    
    if ($employee) {
        // Build query for payrolls
        $query = "SELECT * FROM payrolls WHERE employee_id = ?";
        $params = [$employee_id];
        
        if ($date_from) {
            $query .= " AND pay_period_end >= ?";
            $params[] = $date_from;
        }
        
        if ($date_to) {
            $query .= " AND pay_period_end <= ?";
            $params[] = $date_to;
        }
        
        $query .= " ORDER BY pay_period_end DESC, created_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $payrolls = $stmt->fetchAll();
        
        // Group payrolls by month
        $payrollsByMonth = [];
        foreach ($payrolls as $payroll) {
            $monthKey = date('Y-m', strtotime($payroll['pay_period_end']));
            if (!isset($payrollsByMonth[$monthKey])) {
                $payrollsByMonth[$monthKey] = [
                    'month' => date('F Y', strtotime($payroll['pay_period_end'])),
                    'payrolls' => [],
                    'summary' => [
                        'total_gross' => 0,
                        'total_net' => 0,
                        'total_deductions' => 0,
                        'total_allowances' => 0,
                        'total_bonuses' => 0,
                        'total_overtime' => 0,
                        'total_base' => 0,
                        'count' => 0
                    ]
                ];
            }
            $payrollsByMonth[$monthKey]['payrolls'][] = $payroll;
            $payrollsByMonth[$monthKey]['summary']['total_gross'] += $payroll['gross_pay'];
            $payrollsByMonth[$monthKey]['summary']['total_net'] += $payroll['net_pay'];
            $payrollsByMonth[$monthKey]['summary']['total_deductions'] += $payroll['deductions'];
            $payrollsByMonth[$monthKey]['summary']['total_allowances'] += $payroll['allowances'];
            $payrollsByMonth[$monthKey]['summary']['total_bonuses'] += $payroll['bonuses'];
            $payrollsByMonth[$monthKey]['summary']['total_overtime'] += $payroll['overtime_pay'];
            $payrollsByMonth[$monthKey]['summary']['total_base'] += $payroll['base_salary'];
            $payrollsByMonth[$monthKey]['summary']['count']++;
        }
        
        // Sort months in descending order (newest first)
        krsort($payrollsByMonth);
        
        // Calculate overall summary
        foreach ($payrolls as $payroll) {
            $summary['total_gross'] += $payroll['gross_pay'];
            $summary['total_net'] += $payroll['net_pay'];
            $summary['total_deductions'] += $payroll['deductions'];
            $summary['total_allowances'] += $payroll['allowances'];
            $summary['total_bonuses'] += $payroll['bonuses'];
            $summary['total_overtime'] += $payroll['overtime_pay'];
            $summary['count']++;
        }
    }
}

function getStatusBadge($status) {
    $colors = [
        'Paid' => 'bg-green-100 text-green-800',
        'Approved' => 'bg-blue-100 text-blue-800',
        'Pending' => 'bg-yellow-100 text-yellow-800'
    ];
    return $colors[$status] ?? 'bg-gray-100 text-gray-800';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statement of Account - Barangay Sto. Angel Payroll</title>
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
                <p class="text-sm uppercase tracking-widest text-gray-400">Financial Records</p>
                <h1 class="text-3xl font-bold text-gray-900">Statement of Account</h1>
                <p class="mt-2 text-sm text-gray-600">View complete payroll history and financial summary for employees.</p>
            </div>

            <div class="glass-card tilt-hover p-6">
                <form method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Employee</label>
                            <select name="employee_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="">-- Select Employee --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>" <?php echo $employee_id === $emp['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($emp['employee_id'] . ' - ' . $emp['first_name'] . ' ' . ($emp['middle_name'] ? $emp['middle_name'] . ' ' : '') . $emp['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-6 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded-lg transition">
                            Generate Statement
                        </button>
                        <?php if ($employee_id): ?>
                            <a href="<?php echo base_url('admin/statement-print.php?employee_id=' . $employee_id . '&date_from=' . $date_from . '&date_to=' . $date_to); ?>" target="_blank" class="px-6 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded-lg transition">
                                🖨️ Print Statement
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <?php if ($employee && !empty($payrolls)): ?>
                <div class="glass-card tilt-hover p-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            <?php echo htmlspecialchars($employee['first_name'] . ' ' . ($employee['middle_name'] ? $employee['middle_name'] . ' ' : '') . $employee['last_name']); ?>
                        </h2>
                        <p class="text-gray-600">
                            Employee ID: <span class="font-semibold"><?php echo htmlspecialchars($employee['employee_id']); ?></span> | 
                            Position: <span class="font-semibold"><?php echo htmlspecialchars($employee['position']); ?></span> | 
                            Department: <span class="font-semibold"><?php echo htmlspecialchars($employee['department']); ?></span>
                        </p>
                        <?php if ($date_from || $date_to): ?>
                            <p class="text-sm text-gray-500 mt-1">
                                Period: <?php echo $date_from ? date('M d, Y', strtotime($date_from)) : 'Beginning'; ?> 
                                to <?php echo $date_to ? date('M d, Y', strtotime($date_to)) : 'Present'; ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-6 mt-6">
                        <?php foreach ($payrollsByMonth as $monthData): ?>
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <div class="bg-primary-600 text-white px-6 py-3">
                                    <h3 class="text-lg font-bold"><?php echo htmlspecialchars($monthData['month']); ?></h3>
                                    <p class="text-sm text-primary-100"><?php echo $monthData['summary']['count']; ?> record(s)</p>
                                </div>
                                
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pay Period</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Base Salary</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allowances</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bonuses</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Pay</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($monthData['payrolls'] as $payroll): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?php echo date('M d, Y', strtotime($payroll['pay_period_start'])); ?> - 
                                                        <?php echo date('M d, Y', strtotime($payroll['pay_period_end'])); ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo formatCurrency($payroll['base_salary']); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo formatCurrency($payroll['overtime_pay']); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo formatCurrency($payroll['allowances']); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo formatCurrency($payroll['bonuses']); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600"><?php echo formatCurrency($payroll['deductions']); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900"><?php echo formatCurrency($payroll['gross_pay']); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-primary-700"><?php echo formatCurrency($payroll['net_pay']); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo getStatusBadge($payroll['status']); ?>">
                                                            <?php echo htmlspecialchars($payroll['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                        <a href="<?php echo base_url('admin/payroll-print.php?id=' . $payroll['id']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr class="bg-gray-100 font-bold">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Monthly Total</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo formatCurrency($monthData['summary']['total_base']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo formatCurrency($monthData['summary']['total_overtime']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo formatCurrency($monthData['summary']['total_allowances']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo formatCurrency($monthData['summary']['total_bonuses']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600"><?php echo formatCurrency($monthData['summary']['total_deductions']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo formatCurrency($monthData['summary']['total_gross']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-primary-700"><?php echo formatCurrency($monthData['summary']['total_net']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap"></td>
                                                <td class="px-6 py-4 whitespace-nowrap"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif ($employee_id && empty($payrolls)): ?>
                <div class="glass-card p-6 text-center">
                    <p class="text-gray-500">No payroll records found for the selected employee and date range.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

