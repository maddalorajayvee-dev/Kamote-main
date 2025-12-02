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
                            50: '#f0fffc',
                            100: '#cafdf3',
                            200: '#a1f4e5',
                            300: '#78e4d5',
                            400: '#4fc9c0',
                            500: '#34b9b0',
                            600: '#1fb9aa',
                            700: '#159488',
                            800: '#0f6f65',
                            900: '#0b4d47',
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
                            <div class="bg-white-500 rounded-md p-3">
                                <span class="text-2xl"><svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="#000000"><path d="M222-255q63-44 125-67.5T480-346q71 0 133.5 23.5T739-255q44-54 62.5-109T820-480q0-145-97.5-242.5T480-820q-145 0-242.5 97.5T140-480q0 61 19 116t63 109Zm257.81-195q-57.81 0-97.31-39.69-39.5-39.68-39.5-97.5 0-57.81 39.69-97.31 39.68-39.5 97.5-39.5 57.81 0 97.31 39.69 39.5 39.68 39.5 97.5 0 57.81-39.69 97.31-39.68 39.5-97.5 39.5Zm.66 370Q398-80 325-111.5t-127.5-86q-54.5-54.5-86-127.27Q80-397.53 80-480.27 80-563 111.5-635.5q31.5-72.5 86-127t127.27-86q72.76-31.5 155.5-31.5 82.73 0 155.23 31.5 72.5 31.5 127 86t86 127.03q31.5 72.53 31.5 155T848.5-325q-31.5 73-86 127.5t-127.03 86Q562.94-80 480.47-80Zm-.47-60q55 0 107.5-16T691-212q-51-36-104-55t-107-19q-54 0-107 19t-104 55q51 40 103.5 56T480-140Zm0-370q34 0 55.5-21.5T557-587q0-34-21.5-55.5T480-664q-34 0-55.5 21.5T403-587q0 34 21.5 55.5T480-510Zm0-77Zm0 374Z"/></svg></span>
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
                            <div class="bg-white-500 rounded-md p-3">
                                <span class="text-2xl"><svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="#1f1f1f"><path d="m421-298 283-283-46-45-237 237-120-120-45 45 165 166Zm59 218q-82 0-155-31.5t-127.5-86Q143-252 111.5-325T80-480q0-83 31.5-156t86-127Q252-817 325-848.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 82-31.5 155T763-197.5q-54 54.5-127 86T480-80Zm0-60q142 0 241-99.5T820-480q0-142-99-241t-241-99q-141 0-240.5 99T140-480q0 141 99.5 240.5T480-140Zm0-340Z"/></svg></span>
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
                            <div class="bg-white-500 rounded-md p-3">
                                <span class="text-2xl"><svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="#1f1f1f"><path d="M226-44 80-190l146-145 42 42-73 73h569l-72-73 43-42 145 145.5L734-44l-42-42 74-74H195l73 74-42 42Zm253.76-486Q438-530 409-559.24q-29-29.23-29-71Q380-672 409.24-701q29.23-29 71-29Q522-730 551-700.76q29 29.23 29 71Q580-588 550.76-559q-29.23 29-71 29ZM180-380q-24.75 0-42.37-17.63Q120-415.25 120-440v-380q0-24.75 17.63-42.38Q155.25-880 180-880h600q24.75 0 42.38 17.62Q840-844.75 840-820v380q0 24.75-17.62 42.37Q804.75-380 780-380H180Zm100-60h400q0-42 29-71t71-29v-180q-42 0-71-29t-29-71H280q0 42-29 71t-71 29v180q42 0 71 29t29 71Zm-100 0v-380 380Z"/></svg></span>
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
                            <div class="bg-white-500 rounded-md p-3">
                                <span class="text-2xl"><svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="#000000"><path d="M480-522q72 0 122-50t50-122v-126H308v126q0 72 50 122t122 50ZM160-80v-60h88v-127q0-71 40-129t106-84q-66-27-106-85t-40-129v-126h-88v-60h640v60h-88v126q0 71-40 129t-106 85q66 26 106 84t40 129v127h88v60H160Z"/></svg></span>
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
                <div class="glass-card tilt-hover p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Barangay Payroll Briefing</h2>
                            <p class="text-sm text-gray-500">Snapshot of frontline programs</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
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
                <div class="glass-card tilt-hover p-6">
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
                <div class="glass-card tilt-hover p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="<?php echo base_url('admin/employees.php?action=new'); ?>" class="block w-full bg-sky-500 hover:bg-sky-600 text-white text-center py-2 px-4 rounded-lg transition">
                            Add New Employee
                        </a>
                        <a href="<?php echo base_url('admin/payroll.php?action=new'); ?>" class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-lg transition">
                            Create Payroll
                        </a>
                    </div>
                </div>

                <div class="glass-card tilt-hover p-6">
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

