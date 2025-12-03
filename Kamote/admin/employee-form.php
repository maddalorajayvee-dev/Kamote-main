<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pdo = getDB();
$action = $_GET['action'] ?? 'new';
$id = $_GET['id'] ?? null;
$employee = null;
$error = '';
$success = '';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        redirect(base_url('admin/employees.php'));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'employee_id' => sanitize($_POST['employee_id'] ?? ''),
        'first_name' => sanitize($_POST['first_name'] ?? ''),
        'last_name' => sanitize($_POST['last_name'] ?? ''),
        'middle_name' => sanitize($_POST['middle_name'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'phone' => sanitize($_POST['phone'] ?? ''),
        'address' => sanitize($_POST['address'] ?? ''),
        'position' => sanitize($_POST['position'] ?? ''),
        'department' => sanitize($_POST['department'] ?? 'Barangay Services'),
        'employment_type' => sanitize($_POST['employment_type'] ?? 'Full-time'),
        'base_salary' => floatval($_POST['base_salary'] ?? 0),
        'date_hired' => sanitize($_POST['date_hired'] ?? ''),
        'status' => sanitize($_POST['status'] ?? 'Active'),
    ];
    
    try {
        if ($action === 'edit' && $id) {
            $stmt = $pdo->prepare("UPDATE employees SET employee_id = ?, first_name = ?, last_name = ?, middle_name = ?, email = ?, phone = ?, address = ?, position = ?, department = ?, employment_type = ?, base_salary = ?, date_hired = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $data['employee_id'], $data['first_name'], $data['last_name'], $data['middle_name'],
                $data['email'], $data['phone'], $data['address'], $data['position'],
                $data['department'], $data['employment_type'], $data['base_salary'],
                $data['date_hired'], $data['status'], $id
            ]);
            $success = 'Employee updated successfully!';
        } else {
            $newId = uniqid('emp_', true);
            $stmt = $pdo->prepare("INSERT INTO employees (id, employee_id, first_name, last_name, middle_name, email, phone, address, position, department, employment_type, base_salary, date_hired, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $newId, $data['employee_id'], $data['first_name'], $data['last_name'], $data['middle_name'],
                $data['email'], $data['phone'], $data['address'], $data['position'],
                $data['department'], $data['employment_type'], $data['base_salary'],
                $data['date_hired'], $data['status']
            ]);
            $success = 'Employee created successfully!';
            redirect(base_url('admin/employees.php?created=1'));
        }
    } catch (PDOException $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// If editing, populate form with employee data
if ($employee) {
    $formData = $employee;
} else {
    $formData = [
        'employee_id' => '',
        'first_name' => '',
        'last_name' => '',
        'middle_name' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
        'position' => '',
        'department' => 'Barangay Services',
        'employment_type' => 'Full-time',
        'base_salary' => '',
        'date_hired' => date('Y-m-d'),
        'status' => 'Active',
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action === 'edit' ? 'Edit' : 'Add'; ?> Employee - Barangay Sto. Angel Payroll</title>
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
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6 flex items-center justify-between">
                <a href="<?php echo base_url('admin/employees.php'); ?>" class="text-sky-500 hover:text-sky-700 text-sm font-medium mb-4 inline-flex items-center gap-2">
                    ← Back to Employees
                </a>
                <span class="text-xs uppercase tracking-[0.3em] text-gray-400"></span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo $action === 'edit' ? 'Edit' : 'Add New'; ?> Employee</h1>

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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Employee ID *</label>
                            <input type="text" name="employee_id" value="<?php echo htmlspecialchars($formData['employee_id']); ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                            <input type="hidden" name="department" value="<?php echo htmlspecialchars($formData['department']); ?>" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                            <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="Active" <?php echo $formData['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo $formData['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="On Leave" <?php echo $formData['status'] === 'On Leave' ? 'selected' : ''; ?>>On Leave</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($formData['first_name']); ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($formData['last_name']); ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                            <input type="text" name="middle_name" value="<?php echo htmlspecialchars($formData['middle_name']); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($formData['phone']); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Position *</label>
                            <input type="text" name="position" value="<?php echo htmlspecialchars($formData['position']); ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Employment Type *</label>
                            <select name="employment_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="Full-time" <?php echo $formData['employment_type'] === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                                <option value="Part-time" <?php echo $formData['employment_type'] === 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                                <option value="Contract" <?php echo $formData['employment_type'] === 'Contract' ? 'selected' : ''; ?>>Contract</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Base Salary *</label>
                            <input type="number" name="base_salary" value="<?php echo htmlspecialchars($formData['base_salary']); ?>" required min="0" step="0.01"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Hired *</label>
                            <input type="date" name="date_hired" value="<?php echo htmlspecialchars($formData['date_hired']); ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($formData['address']); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="<?php echo base_url('admin/employees.php'); ?>" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded-lg transition">
                            <?php echo $action === 'edit' ? 'Save Changes' : 'Create Employee'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>

