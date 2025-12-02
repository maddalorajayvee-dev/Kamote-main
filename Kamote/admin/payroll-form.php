<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pdo = getDB();
$action = $_GET['action'] ?? 'new';
$id = $_GET['id'] ?? null;
$payroll = null;
$error = '';
$success = '';

// Get employees for dropdown
$stmt = $pdo->query("SELECT id, employee_id, first_name, last_name, base_salary FROM employees WHERE base_salary > 0 ORDER BY first_name");
$employees = $stmt->fetchAll();

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM payrolls WHERE id = ?");
    $stmt->execute([$id]);
    $payroll = $stmt->fetch();
    
    if (!$payroll) {
        redirect(base_url('admin/payroll.php'));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'employee_id' => sanitize($_POST['employee_id'] ?? ''),
        'pay_period_start' => sanitize($_POST['pay_period_start'] ?? ''),
        'pay_period_end' => sanitize($_POST['pay_period_end'] ?? ''),
        'base_salary' => floatval($_POST['base_salary'] ?? 0),
        'overtime_hours' => floatval($_POST['overtime_hours'] ?? 0),
        'allowances' => floatval($_POST['allowances'] ?? 0),
        'bonuses' => floatval($_POST['bonuses'] ?? 0),
        'deductions' => floatval($_POST['deductions'] ?? 0),
        'notes' => sanitize($_POST['notes'] ?? ''),
        'status' => sanitize($_POST['status'] ?? 'Pending'),
    ];
    
    // Calculate overtime pay (fixed multiplier 1.25)
    $hourlyRate = $data['base_salary'] / (8 * 22);
    $overtimeRate = 1.25;
    $overtimePay = $hourlyRate * $data['overtime_hours'] * $overtimeRate;
    
    // Calculate totals
    $grossPay = $data['base_salary'] + $overtimePay + $data['allowances'] + $data['bonuses'];
    $netPay = $grossPay - $data['deductions'];
    
    try {
        if ($action === 'edit' && $id) {
            $stmt = $pdo->prepare("UPDATE payrolls SET employee_id = ?, pay_period_start = ?, pay_period_end = ?, base_salary = ?, overtime_hours = ?, overtime_pay = ?, allowances = ?, bonuses = ?, deductions = ?, gross_pay = ?, net_pay = ?, status = ?, notes = ? WHERE id = ?");
            $stmt->execute([
                $data['employee_id'], $data['pay_period_start'], $data['pay_period_end'],
                $data['base_salary'], $data['overtime_hours'], $overtimePay,
                $data['allowances'], $data['bonuses'], $data['deductions'],
                $grossPay, $netPay, $data['status'], $data['notes'], $id
            ]);
            $success = 'Payroll updated successfully!';
        } else {
            $newId = uniqid('pay_', true);
            $stmt = $pdo->prepare("INSERT INTO payrolls (id, employee_id, pay_period_start, pay_period_end, base_salary, overtime_hours, overtime_pay, allowances, bonuses, deductions, gross_pay, net_pay, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $newId, $data['employee_id'], $data['pay_period_start'], $data['pay_period_end'],
                $data['base_salary'], $data['overtime_hours'], $overtimePay,
                $data['allowances'], $data['bonuses'], $data['deductions'],
                $grossPay, $netPay, $data['status'], $data['notes']
            ]);
            redirect(base_url('admin/payroll.php?created=1'));
        }
    } catch (PDOException $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// If editing, populate form with payroll data
if ($payroll) {
    $formData = $payroll;
} else {
    $formData = [
        'employee_id' => '',
        'pay_period_start' => date('Y-m-d'),
        'pay_period_end' => date('Y-m-d'),
        'base_salary' => '',
        'overtime_hours' => '0',
        'allowances' => '0',
        'bonuses' => '0',
        'deductions' => '0',
        'notes' => '',
        'status' => 'Pending',
    ];
}

// Calculate totals for display
$baseSalary = floatval($formData['base_salary'] ?? 0);
$overtimeHours = floatval($formData['overtime_hours'] ?? 0);
$overtimeRate = 1.25;
$hourlyRate = $baseSalary > 0 ? $baseSalary / (8 * 22) : 0;
$overtimePay = $hourlyRate * $overtimeHours * $overtimeRate;
$allowances = floatval($formData['allowances'] ?? 0);
$bonuses = floatval($formData['bonuses'] ?? 0);
$deductions = floatval($formData['deductions'] ?? 0);
$grossPay = $baseSalary + $overtimePay + $allowances + $bonuses;
$netPay = $grossPay - $deductions;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action === 'edit' ? 'Edit' : 'Create'; ?> Payroll - Barangay Sto. Angel Payroll</title>
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
    <script>
        function calculateTotals() {
            const baseSalary = parseFloat(document.querySelector('[name="base_salary"]').value) || 0;
            const overtimeHours = parseFloat(document.querySelector('[name="overtime_hours"]').value) || 0;
            const overtimeRate = 1.25;
            const allowances = parseFloat(document.querySelector('[name="allowances"]').value) || 0;
            const bonuses = parseFloat(document.querySelector('[name="bonuses"]').value) || 0;
            const deductionInput = document.querySelector('[name="deductions"]');
            const deductions = deductionInput ? parseFloat(deductionInput.value) || 0 : 0;
            
            const hourlyRate = baseSalary / (8 * 22);
            const overtimePay = hourlyRate * overtimeHours * overtimeRate;
            const grossPay = baseSalary + overtimePay + allowances + bonuses;
            const netPay = grossPay - deductions;
            
            document.getElementById('overtime-pay-display').textContent = '₱' + overtimePay.toFixed(2);
            document.getElementById('gross-pay').textContent = '₱' + grossPay.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('net-pay').textContent = '₱' + netPay.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = ['base_salary', 'overtime_hours', 'allowances', 'bonuses'];
            inputs.forEach(name => {
                const input = document.querySelector(`[name="${name}"]`);
                if (input) {
                    input.addEventListener('input', calculateTotals);
                }
            });
            
            // Auto-fill base salary when employee is selected
            const employeeSelect = document.querySelector('[name="employee_id"]');
            if (employeeSelect) {
                employeeSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const baseSalary = selectedOption.getAttribute('data-salary');
                    if (baseSalary) {
                        document.querySelector('[name="base_salary"]').value = baseSalary;
                        calculateTotals();
                    }
                });
            }
            
            calculateTotals();
        });
    </script>
</head>
<body class="min-h-screen page-gradient">
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6 flex items-center justify-between">
                <a href="<?php echo base_url('admin/payroll.php'); ?>" class="text-sky-500 hover:text-sky-700 text-sm font-medium mb-4 inline-flex items-center gap-2">
                    ← Back to Payroll
                </a>
                <span class="text-xs uppercase tracking-[0.3em] text-gray-400">TREASURY MODULE</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo $action === 'edit' ? 'Edit' : 'Create'; ?> Payroll</h1>

            <div class="glass-card tilt-hover p-6">
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Employee *</label>
                            <select name="employee_id" required onchange="calculateTotals()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="">Select an employee</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>" 
                                            data-salary="<?php echo $emp['base_salary']; ?>"
                                            <?php echo (isset($formData['employee_id']) && $formData['employee_id'] === $emp['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($emp['employee_id'] . ' - ' . $emp['first_name'] . ' ' . $emp['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                            <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="Pending" <?php echo (isset($formData['status']) && $formData['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="Approved" <?php echo (isset($formData['status']) && $formData['status'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="Paid" <?php echo (isset($formData['status']) && $formData['status'] === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pay Period Start *</label>
                            <input type="date" name="pay_period_start" value="<?php echo htmlspecialchars($formData['pay_period_start'] ?? date('Y-m-d')); ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pay Period End *</label>
                            <input type="date" name="pay_period_end" value="<?php echo htmlspecialchars($formData['pay_period_end'] ?? date('Y-m-d')); ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Base Salary *</label>
                            <input type="number" name="base_salary" value="<?php echo htmlspecialchars($formData['base_salary'] ?? ''); ?>" required min="0" step="0.01"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                            <input type="hidden" name="deductions" value="<?php echo htmlspecialchars($formData['deductions'] ?? '0'); ?>" />
                            <input type="hidden" name="bonuses" value="<?php echo htmlspecialchars($formData['bonuses'] ?? '0'); ?>" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Overtime Hours</label>
                            <input type="number" name="overtime_hours" value="<?php echo htmlspecialchars($formData['overtime_hours'] ?? '0'); ?>" min="0" step="0.5"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Overtime Pay (calculated)</label>
                            <input type="text" id="overtime-pay-display" value="₱0.00" disabled
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Allowances</label>
                            <input type="number" name="allowances" value="<?php echo htmlspecialchars($formData['allowances'] ?? '0'); ?>" min="0" step="0.01"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>

                        
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"><?php echo htmlspecialchars($formData['notes'] ?? ''); ?></textarea>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Gross Pay:</p>
                                <p class="text-2xl font-bold text-gray-900" id="gross-pay">₱0.00</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Net Pay:</p>
                                <p class="text-2xl font-bold text-primary-600" id="net-pay">₱0.00</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="<?php echo base_url('admin/payroll.php'); ?>" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded-lg transition">
                            <?php echo $action === 'edit' ? 'Save Changes' : 'Create Payroll'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>

