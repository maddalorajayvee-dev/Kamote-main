<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pdo = getDB();

// Get statistics
$stats = [
    'total_employees' => $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn(),
    'active_employees' => $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'Active'")->fetchColumn(),
    'total_payroll' => $pdo->query("SELECT COUNT(*) FROM payrolls")->fetchColumn(),
    'pending_payroll' => $pdo->query("SELECT COUNT(*) FROM payrolls WHERE status = 'Pending'")->fetchColumn(),
];

$admin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Barangay Sto. Angel Payroll</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
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
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Overview of Barangay Sto. Angel Payroll System
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <a href="<?php echo base_url('admin/employees.php'); ?>" class="glass-card tilt-hover overflow-hidden transition">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="bg-sky-100 rounded-md p-3">
                                <span class="text-2xl"><svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="#0ea5e9"><path d="M180-217q60-56 135.9-90.5 75.89-34.5 164-34.5 88.1 0 164.1 34.5T780-217v-563H180v563Zm302-204q58 0 98-40t40-98q0-58-40-98t-98-40q-58 0-98 40t-40 98q0 58 40 98t98 40ZM180-120q-24 0-42-18t-18-42v-600q0-24 18-42t42-18h600q24 0 42 18t18 42v600q0 24-18 42t-42 18H180Zm43-60h513q-62-53-125.5-77.5T480-282q-67 0-130.5 24.5T223-180Zm259-301q-32.5 0-55.25-22.75T404-559q0-32.5 22.75-55.25T482-637q32.5 0 55.25 22.75T560-559q0 32.5-22.75 55.25T482-481Zm-2-18Z"/></svg></span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Employees</dt>
                                    <dd class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_employees']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="<?php echo base_url('admin/employees.php'); ?>" class="glass-card tilt-hover overflow-hidden transition">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="bg-sky-100 rounded-md p-3">
                                <span class="text-2xl"><svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="#0ea5e9"><path d="M702-494 575-622l42-42 85 85 170-170 42 43-212 212Zm-342 13q-66 0-108-42t-42-108q0-66 42-108t108-42q66 0 108 42t42 108q0 66-42 108t-108 42ZM40-160v-94q0-35 17.5-63.5T108-360q75-33 133.34-46.5t118.5-13.5Q420-420 478-406.5T611-360q33 15 51 43t18 63v94H40Zm60-60h520v-34q0-16-9-30.5T587-306q-71-33-120-43.5T360-360q-58 0-107.5 10.5T132-306q-15 7-23.5 21.5T100-254v34Zm260-321q39 0 64.5-25.5T450-631q0-39-25.5-64.5T360-721q-39 0-64.5 25.5T270-631q0 39 25.5 64.5T360-541Zm0 251Zm0-341Z"/></svg></span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Active Employees</dt>
                                    <dd class="text-2xl font-semibold text-gray-900"><?php echo $stats['active_employees']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="<?php echo base_url('admin/payroll.php'); ?>" class="glass-card tilt-hover overflow-hidden transition">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="bg-sky-100 rounded-md p-3">
                                <span class="text-2xl"><svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="#0ea5e9"><path d="M180-233v53-600 547Zm0 113q-24.75 0-42.37-17.63Q120-155.25 120-180v-600q0-24.75 17.63-42.38Q155.25-840 180-840h600q24.75 0 42.38 17.62Q840-804.75 840-780v134h-60v-134H180v600h600v-133h60v133q0 24.75-17.62 42.37Q804.75-120 780-120H180Zm358-173q-30.52 0-52.26-21.44Q464-335.89 464-366v-227q0-30.11 21.74-51.56Q507.48-666 538-666h270q30.53 0 52.26 21.44Q882-623.11 882-593v227q0 30.11-21.74 51.56Q838.53-293 808-293H538Zm284-60v-253H524v253h298Zm-169.76-63q26.76 0 45.26-18.96Q716-453.92 716-481q0-26.25-19-44.63Q678-544 652-544t-45 18.37q-19 18.38-19 44.63 0 27.08 18.74 46.04Q625.47-416 652.24-416Z"/></svg></span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Payroll Records</dt>
                                    <dd class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_payroll']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="<?php echo base_url('admin/payroll.php'); ?>" class="glass-card tilt-hover overflow-hidden transition">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="bg-sky-100 rounded-md p-3">
                                <span class="text-2xl"><svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="#0ea5e9"><path d="M691-80q-78.43 0-133.72-55.28Q502-190.57 502-269t55.28-133.72Q612.57-458 691-458t133.72 55.28Q880-347.43 880-269t-55.28 133.72Q769.43-80 691-80Zm58.24-88L777-196l-75-75v-112h-39v126l86.24 89ZM180-120q-24.75 0-42.37-17.63Q120-155.25 120-180v-600q0-26 17-43t43-17h202q7-35 34.5-57.5T480-920q36 0 63.5 22.5T578-840h202q26 0 43 17t17 43v308q-15-9-29.52-15.48Q795.97-493.96 780-499v-281h-60v90H240v-90h-60v600h280q5 15 12 29.5t17 30.5H180Zm300-660q17 0 28.5-11.5T520-820q0-17-11.5-28.5T480-860q-17 0-28.5 11.5T440-820q0 17 11.5 28.5T480-780Z"/></svg></span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Payroll</dt>
                                    <dd class="text-2xl font-semibold text-gray-900"><?php echo $stats['pending_payroll']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-8">
                <div class="glass-card tilt-hover p-6 border border-sky-100 hover:border-sky-200 transition-colors">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Barangay Payroll Briefing</h2>
                            <p class="text-sm text-gray-500">Snapshot of frontline programs</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-700">
                            Community Fund
                        </span>
                    </div>
                    
                    <div class="space-y-3 text-sm text-gray-600" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <p>
                            Focused on the honoraria and allowances of barangay security teams, health workers,
                            daycare staff, and project-based aides. Each payroll batch is tagged whether it draws from
                            the regular fund, SEF, or special project grants for easy treasury auditing.
                        </p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Automatic hazard pay and night-shift differential for security deployment</li>
                            <li>Consolidated summaries ready for Municipal Accounting submission</li>
                            <li>Tracking for medical missions, cleanliness drives, and similar allowances</li>
                        </ul>
                    </div>
                </div>
                <div class="glass-card tilt-hover p-6 border border-sky-100 hover:border-sky-200 transition-colors">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Disbursement Timeline</h2>
                    <div class="space-y-4">
                        <div class="timeline-step">
                            <div class="timeline-badge">
                                1
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Attendance Validation</p>
                                <p class="text-sm text-gray-600">Digital DTRs and activity logs from security and health workers are uploaded.</p>
                            </div>
                        </div>
                        <div class="timeline-step">
                            <div class="timeline-badge">
                                2
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Treasury Review</p>
                                <p class="text-sm text-gray-600">System-generated reports are forwarded for the Treasurer and Captain’s approval.</p>
                            </div>
                        </div>
                        <div class="timeline-step">
                            
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="glass-card tilt-hover p-6 border border-sky-100 hover:border-sky-200 transition-colors">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="<?php echo base_url('admin/employees.php?action=new'); ?>" class="block w-full bg-sky-500 hover:bg-sky-600 text-white text-center py-2 px-4 rounded-lg transition shadow-md hover:shadow-lg">
                            Add New Employee
                        </a>
                        <a href="<?php echo base_url('admin/payroll.php?action=new'); ?>" class="block w-full bg-sky-400 hover:bg-sky-500 text-white text-center py-2 px-4 rounded-lg transition shadow-md hover:shadow-lg">
                            Create Payroll
                        </a>
                    </div>
                </div>

                <div class="glass-card tilt-hover p-6 border border-sky-100 hover:border-sky-200 transition-colors">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">System Information</h2>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p>Barangay Sto. Angel Payroll Management System</p>
                        <p>Version 1.0.0</p>
                        <p class="pt-2 text-xs text-gray-400">
                            Last updated: <?php echo date('F j, Y'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

