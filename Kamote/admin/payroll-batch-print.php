<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pdo = getDB();
$period_start = sanitize($_GET['period_start'] ?? '');
$period_end = sanitize($_GET['period_end'] ?? '');

if (!$period_start || !$period_end) {
    redirect(base_url('admin/payroll.php'));
}

// Get all payrolls for the period
$stmt = $pdo->prepare("SELECT p.*, e.first_name, e.last_name, e.middle_name, e.position 
                       FROM payrolls p 
                       JOIN employees e ON p.employee_id = e.id 
                       WHERE p.pay_period_start = ? AND p.pay_period_end = ?
                       ORDER BY e.position, e.last_name, e.first_name");
$stmt->execute([$period_start, $period_end]);
$payrolls = $stmt->fetchAll();

if (empty($payrolls)) {
    redirect(base_url('admin/payroll.php?error=no_payrolls'));
}

// Calculate totals
$total_salaries = 0;
$total_honoraria = 0;
$total_compensation = 0;
$total_deductions = 0;
$total_net = 0;

foreach ($payrolls as $payroll) {
    $salaries = $payroll['base_salary'];
    $honoraria = $payroll['allowances'] + $payroll['bonuses'];
    $compensation = $salaries + $honoraria;
    $deductions = $payroll['deductions'];
    $net = $payroll['net_pay'];
    
    $total_salaries += $salaries;
    $total_honoraria += $honoraria;
    $total_compensation += $compensation;
    $total_deductions += $deductions;
    $total_net += $net;
}

// Get barangay treasurer (assuming it's an employee with position "Ingat-Yaman" or "Barangay Treasurer")
$stmt = $pdo->prepare("SELECT first_name, middle_name, last_name FROM employees WHERE position LIKE '%Treasurer%' OR position LIKE '%Ingat-Yaman%' LIMIT 1");
$stmt->execute();
$treasurer = $stmt->fetch();

// Get Punong Barangay
$stmt = $pdo->prepare("SELECT first_name, middle_name, last_name FROM employees WHERE position LIKE '%Punong Barangay%' OR position LIKE '%Barangay Captain%' LIMIT 1");
$stmt->execute();
$punong_barangay = $stmt->fetch();

// Get Chairman, Com.on Appropriation
$stmt = $pdo->prepare("SELECT first_name, middle_name, last_name FROM employees WHERE position LIKE '%Chairman%' OR position LIKE '%Appropriation%' LIMIT 1");
$stmt->execute();
$chairman = $stmt->fetch();

// Format dates - match the format in the image: "November 01-30, 2025"
$start_day = date('d', strtotime($period_start));
$end_day = date('d', strtotime($period_end));
$month = date('F', strtotime($period_start));
$year = date('Y', strtotime($period_end));
$period_display = $month . ' ' . $start_day . '-' . $end_day . ', ' . $year;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll</title>
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
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

        .payroll-header {
            text-align: center;
            margin-bottom: 15px;
            margin-top: 0;
        }

        .payroll-title {
            font-size: 40px;
            font-weight: bold;
            margin-bottom: 10px;
            margin-top: 0;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        .payroll-info {
            margin-bottom: 5px;
            line-height: 1.6;
        }

        .payroll-info p {
            margin: 3px 0;
            font-size: 15px;
            font-weight: 500;
        }

        .payroll-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            font-size: 12px;
            table-layout: fixed;
        }

        .payroll-table th,
        .payroll-table td {
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: center;
            vertical-align: middle;
        }

        .payroll-table th {
            background-color: #e8e8e8;
            font-weight: bold;
            font-size: 11px;
        }

        .payroll-table td {
            text-align: center;
            font-size: 11px;
        }

        .payroll-table td.text-left {
            text-align: left;
            padding-left: 10px;
        }

        .payroll-table td.text-right {
            text-align: right;
            padding-right: 10px;
        }

        .col-no { width: 3%; }
        .col-name { width: 18%; }
        .col-position { width: 13%; }
        .col-salaries { width: 9%; }
        .col-honoraria { width: 9%; }
        .col-total-comp { width: 9%; }
        .col-bir { width: 9%; }
        .col-total-ded { width: 9%; }
        .col-net { width: 11%; }
        .col-signature { width: 9%; }

        .certification-section {
            margin-top: 40px;
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            width: 100%;
        }

        .cert-item {
            font-size: 11px;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            min-height: 200px;
        }

        .cert-label {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .cert-text {
            margin-bottom: 15px;
            line-height: 1.5;
            padding-left: 0;
            font-size: 10px;
            min-height: 40px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: auto;
            padding-top: 5px;
            width: 100%;
            max-width: 200px;
            display: block;
        }

        .signature-box {
            margin-top: 10px;
            width: 100%;
        }

        .signature-box p {
            margin: 5px 0;
            font-size: 10px;
            line-height: 1.4;
        }

        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
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
                padding: 10px 20px;
            }
            
            .payroll-header {
                margin-bottom: 10px;
            }
            
            .payroll-title {
                margin-bottom: 5px;
            }
            
            .payroll-info {
                margin-bottom: 3px;
            }
            
            .payroll-info p {
                margin: 2px 0;
            }

            .btn {
                display: none;
            }

            @page {
                margin: 1.5cm;
                size: A4 landscape;
            }

            .payroll-table {
                font-size: 11px;
            }

            .payroll-table th,
            .payroll-table td {
                padding: 6px 4px;
            }
            
            .payroll-title {
                font-size: 38px;
            }
            
            .payroll-info p {
                font-size: 14px;
            }
            
            .certification-section {
                gap: 10px;
                margin-top: 30px;
            }
            
            .cert-item {
                font-size: 10px;
                min-height: 180px;
            }
            
            .cert-label {
                font-size: 11px;
            }
            
            .cert-text {
                font-size: 9px;
                min-height: 35px;
            }
            
            .signature-box p {
                font-size: 9px;
            }
            
            .signature-line {
                max-width: 180px;
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

        <div class="payroll-header">
            <h1 class="payroll-title">PAYROLL</h1>
            <div class="payroll-info">
                <p><strong>Period Covered:</strong> <?php echo $period_display; ?></p>
                <p><strong>Barangay:</strong> STO. ANGEL, SAN PABLO CITY</p>
                <p><strong>Province:</strong> LAGUNA</p>
                <?php if ($treasurer): ?>
                <p><strong>Barangay Treasurer:</strong> <?php echo strtoupper($treasurer['first_name'] . ' ' . ($treasurer['middle_name'] ? substr($treasurer['middle_name'], 0, 1) . '. ' : '') . $treasurer['last_name']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <table class="payroll-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-no">No.</th>
                    <th rowspan="2" class="col-name">Name</th>
                    <th rowspan="2" class="col-position">Position</th>
                    <th colspan="3">Compensation</th>
                    <th colspan="2">Deduction</th>
                    <th rowspan="2" class="col-net">Net Amount Due</th>
                    <th rowspan="2" class="col-signature">Signature of Recipient</th>
                </tr>
                <tr>
                    <th class="col-salaries">Salaries and Wages</th>
                    <th class="col-honoraria">Honoraria</th>
                    <th class="col-total-comp">Total</th>
                    <th class="col-bir">BIR W/ holding Tax</th>
                    <th class="col-total-ded">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                foreach ($payrolls as $payroll): 
                    $salaries = $payroll['base_salary'];
                    $honoraria = $payroll['allowances'] + $payroll['bonuses'];
                    $total_comp = $salaries + $honoraria;
                    $bir_tax = $payroll['deductions'];
                    $total_ded = $bir_tax;
                    $net_amount = $payroll['net_pay'];
                    
                    $full_name = $payroll['first_name'] . ' ' . ($payroll['middle_name'] ? substr($payroll['middle_name'], 0, 1) . '. ' : '') . $payroll['last_name'];
                ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td class="text-left"><?php echo strtoupper($full_name); ?></td>
                    <td class="text-left"><?php echo htmlspecialchars($payroll['position']); ?></td>
                    <td class="text-right"><?php echo number_format($salaries, 2, '.', ','); ?></td>
                    <td class="text-right"><?php echo number_format($honoraria, 2, '.', ','); ?></td>
                    <td class="text-right"><?php echo number_format($total_comp, 2, '.', ','); ?></td>
                    <td class="text-right"><?php echo number_format($bir_tax, 2, '.', ','); ?></td>
                    <td class="text-right"><?php echo number_format($total_ded, 2, '.', ','); ?></td>
                    <td class="text-right"><?php echo number_format($net_amount, 2, '.', ','); ?></td>
                    <td></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="3" class="text-left"><strong>TOTAL</strong></td>
                    <td class="text-right"><strong><?php echo number_format($total_salaries, 2, '.', ','); ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($total_honoraria, 2, '.', ','); ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($total_compensation, 2, '.', ','); ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($total_deductions, 2, '.', ','); ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($total_deductions, 2, '.', ','); ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($total_net, 2, '.', ','); ?></strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="certification-section">
            <div class="cert-item">
                <div class="cert-label">A. Certified:</div>
                <div class="cert-text">for obligation for the amount of</div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p><strong>Printed Name:</strong><br><?php echo $chairman ? strtoupper($chairman['first_name'] . ' ' . ($chairman['middle_name'] ? substr($chairman['middle_name'], 0, 1) . '. ' : '') . $chairman['last_name']) : 'RICKY C. CAPISTRANO'; ?></p>
                    <p><strong>Position:</strong><br>Chairman, Com.on Appropriation</p>
                    <p><strong>Date:</strong> _______________</p>
                </div>
            </div>

            <div class="cert-item">
                <div class="cert-label">B. Certified:</div>
                <div class="cert-text">As supporting documents valid and complete</div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p><strong>Printed Name:</strong><br><?php echo $treasurer ? strtoupper($treasurer['first_name'] . ' ' . ($treasurer['middle_name'] ? substr($treasurer['middle_name'], 0, 1) . '. ' : '') . $treasurer['last_name']) : 'FROILAN M. AQUINO'; ?></p>
                    <p><strong>Position:</strong><br>Barangay Treasurer</p>
                    <p><strong>Date:</strong> _______________</p>
                </div>
            </div>

            <div class="cert-item">
                <div class="cert-label">C. Certified:</div>
                <div class="cert-text">As to validity, property and legally of claim</div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p><strong>Printed Name:</strong><br><?php echo $punong_barangay ? strtoupper($punong_barangay['first_name'] . ' ' . ($punong_barangay['middle_name'] ? substr($punong_barangay['middle_name'], 0, 1) . '. ' : '') . $punong_barangay['last_name']) : 'JOVITO C. MAGHIRANG'; ?></p>
                    <p><strong>Position:</strong><br>Punong Barangay</p>
                    <p><strong>Date:</strong> _______________</p>
                </div>
            </div>

            <div class="cert-item">
                <div class="cert-label">D. Certified:</div>
                <div class="cert-text">Each official/employee whose name appears on the above roll has been paid the amount stated opposite his name</div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p><strong>Printed Name:</strong><br><?php echo $treasurer ? strtoupper($treasurer['first_name'] . ' ' . ($treasurer['middle_name'] ? substr($treasurer['middle_name'], 0, 1) . '. ' : '') . $treasurer['last_name']) : 'FROILAN M. AQUINO'; ?></p>
                    <p><strong>Position:</strong><br>Barangay Treasurer</p>
                    <p><strong>Date:</strong> _______________</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

