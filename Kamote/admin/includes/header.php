<?php
$current_page = basename($_SERVER['PHP_SELF']);
$admin = getCurrentAdmin();
?>
<nav class="backdrop-blur shadow-sm border-b border-sky-500" style="background: linear-gradient(135deg, #87CEEB 0%, #6BB6FF 50%, #4A9EFF 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <img src="<?php echo base_url('img/logo.jpeg.jpg'); ?>" alt="Barangay Seal" class="h-10 w-10 mr-3" style="height: 60px; width: 60px; object-fit: contain; border-radius: 50%;">
                    <h1 class="text-xl font-bold text-gray-800">
                        Barangay Sto. Angel Payroll
                    </h1>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <a href="<?php echo base_url('admin/dashboard.php'); ?>" class="<?php echo $current_page === 'dashboard.php' ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <span class="mr-2"> </span> Dashboard
                    </a>
                    <a href="<?php echo base_url('admin/employees.php'); ?>" class="<?php echo $current_page === 'employees.php' ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <span class="mr-2"> </span> Employees
                    </a>
                    <a href="<?php echo base_url('admin/payroll.php'); ?>" class="<?php echo $current_page === 'payroll.php' ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <span class="mr-2"> </span> Payroll
                    </a>
                    <a href="<?php echo base_url('admin/statement-of-account.php'); ?>" class="<?php echo $current_page === 'statement-of-account.php' || $current_page === 'statement-print.php' ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <span class="mr-2"> </span> SOA
                    </a>
                    <a href="<?php echo base_url('admin/payroll-archive.php'); ?>" class="<?php echo $current_page === 'payroll-archive.php' ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <span class="mr-2"> </span> Archive
                    </a>
                </div>
            </div>
            <div class="flex items-center">
                <span class="text-sm text-gray-700 mr-4">
                    Welcome, <?php echo htmlspecialchars($admin['name'] ?? $admin['username']); ?>
                </span>
                <a href="<?php echo base_url('logout.php'); ?>" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Logout
                </a>
            </div>
        </div>
    </div>
</nav>

