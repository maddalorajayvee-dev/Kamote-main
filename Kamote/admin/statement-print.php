<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pdo = getDB();
$employee_id = sanitize($_GET['employee_id'] ?? '');
$date_from = sanitize($_GET['date_from'] ?? '');
$date_to = sanitize($_GET['date_to'] ?? '');

if (!$employee_id) {
    redirect(base_url('admin/statement-of-account.php'));
}

// Get employee info
$stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$employee_id]);
$employee = $stmt->fetch();

if (!$employee) {
    redirect(base_url('admin/statement-of-account.php'));
}

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

// Calculate summary
$summary = [
    'total_gross' => 0,
    'total_net' => 0,
    'total_deductions' => 0,
    'total_allowances' => 0,
    'total_bonuses' => 0,
    'total_overtime' => 0,
    'total_base' => 0,
    'count' => 0
];

foreach ($payrolls as $payroll) {
    $summary['total_gross'] += $payroll['gross_pay'];
    $summary['total_net'] += $payroll['net_pay'];
    $summary['total_deductions'] += $payroll['deductions'];
    $summary['total_allowances'] += $payroll['allowances'];
    $summary['total_bonuses'] += $payroll['bonuses'];
    $summary['total_overtime'] += $payroll['overtime_pay'];
    $summary['total_base'] += $payroll['base_salary'];
    $summary['count']++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statement of Account - Barangay Sto. Angel Payroll</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .print-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .print-header {
            text-align: center;
            border-bottom: 3px solid #1fb9aa;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .print-header h1 {
            color: #0ea5e9; /* Sky blue color */
            color: #159488;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .print-header p {
            color: #666;
            font-size: 14px;
        }

        .print-actions {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 14px;
        }

        .btn-print {
            background: #1fb9aa;
            color: white;
        }

        .btn-print:hover {
            background: #159488;
        }

        .btn-back {
            background: #6b7280;
            color: white;
        }

        .btn-back:hover {
            background: #4b5563;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-box {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #1fb9aa;
        }

        .info-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #159488;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #1fb9aa;
        }

        .statement-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 12px;
        }

        .statement-table thead {
            background: #1fb9aa;
            color: white;
        }

        .statement-table th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }

        .statement-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .statement-table tbody tr:hover {
            background: #f9fafb;
        }

        .statement-table tbody tr.total-row {
            background: #1fb9aa;
            color: white;
            font-weight: 700;
        }

        .statement-table tbody tr.total-row td {
            border-bottom: none;
            padding: 15px 8px;
            font-size: 13px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }

        .summary-box {
            background: #f0fdf4;
            border: 2px solid #1fb9aa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .summary-item {
            text-align: center;
        }

        .summary-item-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .summary-item-value {
            font-size: 20px;
            font-weight: 700;
            color: #159488;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .print-actions {
                display: none;
            }

            .print-container {
                box-shadow: none;
                padding: 20px;
            }

            .btn {
                display: none;
            }

            @page {
                margin: 1cm;
            }

            .statement-table {
                font-size: 10px;
            }

            .statement-table th,
            .statement-table td {
                padding: 6px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="print-actions">
            <button onclick="window.print()" class="btn btn-print">🖨️ Print</button>
            <a href="<?php echo base_url('admin/statement-of-account.php?employee_id=' . $employee_id . '&date_from=' . $date_from . '&date_to=' . $date_to); ?>" class="btn btn-back">← Back</a>
        </div>

        <div class="print-header">
            <h1>STATEMENT OF ACCOUNT</h1>
            <p>Barangay Sto. Angel Payroll System</p>
        </div>

        <div class="info-section">
            <div class="info-grid">
                <div class="info-box">
                    <div class="info-label">Employee Name</div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($employee['first_name'] . ' ' . ($employee['middle_name'] ? $employee['middle_name'] . ' ' : '') . $employee['last_name']); ?>
                    </div>
                </div>
                <div class="info-box">
                    <div class="info-label">Employee ID</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['employee_id']); ?></div>
                </div>
                <div class="info-box">
                    <div class="info-label">Position</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['position']); ?></div>
                </div>
                <div class="info-box">
                    <div class="info-label">Department</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['department']); ?></div>
                </div>
            </div>
            <?php if ($date_from || $date_to): ?>
                <div class="info-box" style="margin-top: 10px;">
                    <div class="info-label">Period Covered</div>
                    <div class="info-value">
                        <?php echo $date_from ? date('F d, Y', strtotime($date_from)) : 'Beginning'; ?> 
                        to <?php echo $date_to ? date('F d, Y', strtotime($date_to)) : 'Present'; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($payrolls)): ?>
            <div class="summary-box">
                <div class="section-title" style="border-bottom-color: #0ea5e9; color: #0ea5e9;">Summary</div>
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-item-label">Total Records</div>
                        <div class="summary-item-value"><?php echo $summary['count']; ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-item-label">Total Gross Pay</div>
                        <div class="summary-item-value"><?php echo formatCurrency($summary['total_gross']); ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-item-label">Total Net Pay</div>
                        <div class="summary-item-value"><?php echo formatCurrency($summary['total_net']); ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-item-label">Total Deductions</div>
                        <div class="summary-item-value"><?php echo formatCurrency($summary['total_deductions']); ?></div>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <div class="section-title">Payroll History</div>
                <table class="statement-table">
                    <thead>
                        <tr>
                            <th>Pay Period</th>
                            <th class="text-right">Base Salary</th>
                            <th class="text-right">Overtime</th>
                            <th class="text-right">Allowances</th>
                            <th class="text-right">Bonuses</th>
                            <th class="text-right">Deductions</th>
                            <th class="text-right">Gross Pay</th>
                            <th class="text-right">Net Pay</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payrolls as $payroll): ?>
                            <tr>
                                <td>
                                    <?php echo date('M d, Y', strtotime($payroll['pay_period_start'])); ?><br>
                                    <small style="color: #6b7280;">to <?php echo date('M d, Y', strtotime($payroll['pay_period_end'])); ?></small>
                                </td>
                                <td class="text-right"><?php echo formatCurrency($payroll['base_salary']); ?></td>
                                <td class="text-right"><?php echo formatCurrency($payroll['overtime_pay']); ?></td>
                                <td class="text-right"><?php echo formatCurrency($payroll['allowances']); ?></td>
                                <td class="text-right"><?php echo formatCurrency($payroll['bonuses']); ?></td>
                                <td class="text-right" style="color: #dc2626;"><?php echo formatCurrency($payroll['deductions']); ?></td>
                                <td class="text-right"><strong><?php echo formatCurrency($payroll['gross_pay']); ?></strong></td>
                                <td class="text-right"><strong style="color: #159488;"><?php echo formatCurrency($payroll['net_pay']); ?></strong></td>
                                <td class="text-center"><?php echo htmlspecialchars($payroll['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td><strong>TOTAL</strong></td>
                            <td class="text-right"><strong><?php echo formatCurrency($summary['total_base']); ?></strong></td>
                            <td class="text-right"><strong><?php echo formatCurrency($summary['total_overtime']); ?></strong></td>
                            <td class="text-right"><strong><?php echo formatCurrency($summary['total_allowances']); ?></strong></td>
                            <td class="text-right"><strong><?php echo formatCurrency($summary['total_bonuses']); ?></strong></td>
                            <td class="text-right"><strong><?php echo formatCurrency($summary['total_deductions']); ?></strong></td>
                            <td class="text-right"><strong><?php echo formatCurrency($summary['total_gross']); ?></strong></td>
                            <td class="text-right"><strong><?php echo formatCurrency($summary['total_net']); ?></strong></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="info-section">
                <p style="text-align: center; color: #6b7280; padding: 40px;">No payroll records found for the selected period.</p>
            </div>
        <?php endif; ?>

        <div class="footer">
            <p>Generated on <?php echo date('F d, Y \a\t h:i A'); ?></p>
            <p>This is a computer-generated statement of account.</p>
        </div>
    </div>
</body>
</html>

