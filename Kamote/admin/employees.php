<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

// Handle form actions (new/edit)
if (isset($_GET['action']) && ($_GET['action'] === 'new' || $_GET['action'] === 'edit')) {
    require_once __DIR__ . '/employee-form.php';
    exit;
}

$pdo = getDB();
$search = sanitize($_GET['search'] ?? '');
$stats = [
    'total_employees' => $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn(),
    'active_employees' => $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'Active'")->fetchColumn(),
];

// Handle delete
if (isset($_GET['delete']) && $_GET['delete']) {
    $id = sanitize($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    redirect(base_url('admin/employees.php?deleted=1'));
}

// Get employees
$query = "SELECT * FROM employees";
$params = [];

if ($search) {
    $query .= " WHERE first_name LIKE ? OR last_name LIKE ? OR employee_id LIKE ? OR position LIKE ?";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$employees = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - Barangay Sto. Angel Payroll</title>
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
            <div class="glass-card tilt-hover p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-widest text-gray-400">Human Resources</p>
                    <h1 class="text-3xl font-bold text-gray-900">Employees</h1>
                    <p class="mt-2 text-sm text-gray-600">Manage barangay staff profiles and deployment info.</p>
                </div>
                <div class="data-grid">
                    <div class="data-pill">
                        <span class="pulse-dot inline-block w-3 h-3 rounded-full bg-green-500"></span>
                        Active: <?php echo $stats['active_employees'] ?? '—'; ?>
                    </div>
                    <div class="data-pill">
                        <span class="inline-block w-3 h-3 rounded-full bg-blue-500"></span>
                        Total: <?php echo $stats['total_employees'] ?? '—'; ?>
                    </div>
                </div>
                <a href="<?php echo base_url('admin/employees.php?action=new'); ?>" class="px-6 py-3 bg-sky-500 text-white rounded-full hover:bg-sky-600 transition text-center">
                    + Add Employee
                </a>
            </div>

            <div class="glass-card tilt-hover p-4">
                <form method="GET" class="flex flex-col md:flex-row gap-2">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search employees..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    />
                    <div class="flex gap-2">
                        <button type="submit" class="px-5 py-3 bg-sky-500 hover:bg-sky-600 text-white rounded-lg transition">
                            Search
                        </button>
                        <?php if ($search): ?>
                            <a href="<?php echo base_url('admin/employees.php'); ?>" class="px-5 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                                Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="glass-card overflow-hidden">
                <?php if (empty($employees)): ?>
                    <div class="px-6 py-8 text-center text-gray-500">
                        No employees found
                    </div>
                <?php else: ?>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($employees as $employee): ?>
                            <li class="px-6 py-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($employee['first_name'] . ' ' . ($employee['middle_name'] ? $employee['middle_name'] . ' ' : '') . $employee['last_name']); ?>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            ID: <?php echo htmlspecialchars($employee['employee_id']); ?> | 
                                            <?php echo htmlspecialchars($employee['position']); ?> | 
                                            <?php echo htmlspecialchars($employee['department']); ?>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Salary: <?php echo formatCurrency($employee['base_salary']); ?> | 
                                            Status: <span class="font-medium <?php echo $employee['status'] === 'Active' ? 'text-green-600' : 'text-red-600'; ?>">
                                                <?php echo htmlspecialchars($employee['status']); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="<?php echo base_url('admin/statement-of-account.php?employee_id=' . $employee['id']); ?>" class="group inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-sm font-medium px-2 py-1 rounded hover:bg-blue-50 transition relative">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Statement</span>
                                        </a>
                                        <a href="<?php echo base_url('admin/employees.php?action=edit&id=' . $employee['id']); ?>" class="group inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 text-sm font-medium px-2 py-1 rounded hover:bg-primary-50 transition relative">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Edit</span>
                                        </a>
                                        <a href="<?php echo base_url('admin/employees.php?delete=' . $employee['id']); ?>" 
                                           onclick="return confirm('Are you sure you want to delete this employee?')"
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

