<aside class="sidebar">
    <div class="sidebar-header">
        <i class="ph-fill ph-drop" style="font-size: 2rem;"></i>
        <h2 style="font-size: 1.25rem;">Blood Bank System</h2>
    </div>
    <nav class="nav-links">
        <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <i class="ph ph-squares-four"></i> Dashboard
        </a>
        <a href="donors.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'donors.php' ? 'active' : '' ?>">
            <i class="ph ph-users"></i> Donors
        </a>
        <a href="acceptors.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'acceptors.php' ? 'active' : '' ?>">
            <i class="ph ph-hand-heart"></i> Acceptors
        </a>
        <a href="inventory.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : '' ?>">
            <i class="ph ph-stack"></i> Inventory
        </a>
        <a href="low_stock.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'low_stock.php' ? 'active' : '' ?>">
            <i class="ph ph-warning-circle"></i> Low Stock
        </a>
    </nav>
    <div style="margin-top: auto;">
        <a href="logout.php" class="nav-link" style="color: var(--danger);">
            <i class="ph ph-sign-out"></i> Logout
        </a>
    </div>
</aside>
