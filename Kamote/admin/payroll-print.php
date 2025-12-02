<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pdo = getDB();
$id = sanitize($_GET['id'] ?? '');

if (!$id) {
    redirect(base_url('admin/payroll.php'));
}

// Get payroll with employee info
$stmt = $pdo->prepare("SELECT p.*, e.first_name, e.last_name, e.middle_name, e.employee_id, e.position, e.department, e.email, e.phone 
                       FROM payrolls p 
                       JOIN employees e ON p.employee_id = e.id 
                       WHERE p.id = ?");
$stmt->execute([$id]);
$payroll = $stmt->fetch();

if (!$payroll) {
    redirect(base_url('admin/payroll.php'));
}

// Calculate hourly rate for display
$hourlyRate = $payroll['base_salary'] / (8 * 22);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Print - Barangay Sto. Angel Payroll</title>
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
            max-width: 800px;
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

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #374151;
            width: 40%;
        }

        .info-value {
            color: #1f2937;
            width: 60%;
            text-align: right;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #159488;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #1fb9aa;
        }

        .payroll-details {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .calculation-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .calculation-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .calculation-table td:first-child {
            font-weight: 500;
            color: #6b7280;
        }

        .calculation-table td:last-child {
            text-align: right;
            font-weight: 600;
            color: #1f2937;
        }

        .calculation-table tr.total-row {
            background: #1fb9aa;
            color: white;
        }

        .calculation-table tr.total-row td {
            border-bottom: none;
            font-size: 18px;
            padding: 15px 10px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }

        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }

        .status-approved {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
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
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="print-actions">
            <button onclick="window.print()" class="btn btn-print"> Print</button>
            <a href="<?php echo base_url('admin/payroll.php'); ?>" class="btn btn-back">← Back to Payroll</a>
        </div>

        <div class="print-header">
            <h1>PAYROLL STATEMENT</h1>
            <p>Barangay Sto. Angel Payroll System</p>
        </div>

        <div class="info-section">
            <div class="section-title">Employee Information</div>
            <div class="payroll-details">
                <div class="info-row">
                    <span class="info-label">Employee Name:</span>
                    <span class="info-value">
                        <?php echo htmlspecialchars($payroll['first_name'] . ' ' . ($payroll['middle_name'] ? $payroll['middle_name'] . ' ' : '') . $payroll['last_name']); ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Employee ID:</span>
                    <span class="info-value"><?php echo htmlspecialchars($payroll['employee_id']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Position:</span>
                    <span class="info-value"><?php echo htmlspecialchars($payroll['position']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Department:</span>
                    <span class="info-value"><?php echo htmlspecialchars($payroll['department']); ?></span>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="section-title">Pay Period</div>
            <div class="payroll-details">
                <div class="info-row">
                    <span class="info-label">Start Date:</span>
                    <span class="info-value"><?php echo date('F d, Y', strtotime($payroll['pay_period_start'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">End Date:</span>
                    <span class="info-value"><?php echo date('F d, Y', strtotime($payroll['pay_period_end'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="status-badge status-<?php echo strtolower($payroll['status']); ?>">
                            <?php echo htmlspecialchars($payroll['status']); ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="section-title">Payroll Breakdown</div>
            <div class="payroll-details">
                <table class="calculation-table">
                    <tr>
                        <td>Base Salary</td>
                        <td><?php echo formatCurrency($payroll['base_salary']); ?></td>
                    </tr>
                    <?php if ($payroll['overtime_hours'] > 0): ?>
                    <tr>
                        <td>Overtime Hours (<?php echo number_format($payroll['overtime_hours'], 2); ?> hrs)</td>
                        <td><?php echo formatCurrency($payroll['overtime_pay']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($payroll['allowances'] > 0): ?>
                    <tr>
                        <td>Allowances</td>
                        <td><?php echo formatCurrency($payroll['allowances']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($payroll['bonuses'] > 0): ?>
                    <tr>
                        <td>Bonuses</td>
                        <td><?php echo formatCurrency($payroll['bonuses']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr style="border-top: 2px solid #1fb9aa;">
                        <td><strong>Gross Pay</strong></td>
                        <td><strong><?php echo formatCurrency($payroll['gross_pay']); ?></strong></td>
                    </tr>
                    <?php if ($payroll['deductions'] > 0): ?>
                    <tr>
                        <td>Deductions</td>
                        <td style="color: #dc2626;">- <?php echo formatCurrency($payroll['deductions']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td><strong>NET PAY</strong></td>
                        <td><strong><?php echo formatCurrency($payroll['net_pay']); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <?php if (!empty($payroll['notes'])): ?>
        <div class="info-section">
            <div class="section-title">Notes</div>
            <div class="payroll-details">
                <p style="color: #374151; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($payroll['notes'])); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p>Generated on <?php echo date('F d, Y \a\t h:i A'); ?></p>
            <p>This is a computer-generated document.</p>
        </div>
    </div>
</body>
</html>

