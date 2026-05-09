<?php
require_once 'includes/db.php';

// Get totals
$total_donors = $conn->query("SELECT COUNT(*) as count FROM donors")->fetch_assoc()['count'];
$total_blood = $conn->query("SELECT SUM(quantity) as sum FROM inventory")->fetch_assoc()['sum'];
if(!$total_blood) $total_blood = 0;

$monthly_donations = $conn->query("SELECT SUM(quantity) as sum FROM donations WHERE MONTH(donation_date) = MONTH(CURRENT_DATE()) AND YEAR(donation_date) = YEAR(CURRENT_DATE())")->fetch_assoc()['sum'];
if(!$monthly_donations) $monthly_donations = 0;

// Get low stock alerts
$low_stock = $conn->query("SELECT blood_group, quantity FROM inventory WHERE quantity < 5 ORDER BY quantity ASC");

require_once 'includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="ph ph-users"></i>
        </div>
        <div class="stat-details">
            <h3>Total Donors</h3>
            <p><?= $total_donors ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="ph ph-drop"></i>
        </div>
        <div class="stat-details">
            <h3>Total Blood (Bottles)</h3>
            <p><?= $total_blood ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="ph ph-calendar-plus"></i>
        </div>
        <div class="stat-details">
            <h3>Donations This Month</h3>
            <p><?= $monthly_donations ?></p>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
    <!-- Recent Donations -->
    <div class="card">
        <div class="card-header">
            <h2 style="font-size: 1.125rem;">Recent Donations</h2>
            <a href="donors.php" class="btn btn-secondary" style="padding: 0.5rem 1rem;">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Donor Name</th>
                        <th>Blood Group</th>
                        <th>Quantity</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recent = $conn->query("SELECT d.name, d.blood_group, dn.quantity, dn.donation_date FROM donations dn JOIN donors d ON dn.donor_id = d.id ORDER BY dn.donation_date DESC LIMIT 5");
                    while($row = $recent->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><span class="badge badge-success"><?= htmlspecialchars($row['blood_group']) ?></span></td>
                        <td><?= htmlspecialchars($row['quantity']) ?> Bottles</td>
                        <td><?= date('d M Y', strtotime($row['donation_date'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($recent->num_rows == 0): ?>
                    <tr><td colspan="4" style="text-align: center;">No recent donations</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="card" style="background: #fff5f5; border: 1px solid #fecaca;">
        <div class="card-header" style="border-bottom-color: #fecaca;">
            <h2 style="font-size: 1.125rem; color: var(--danger); display: flex; align-items: center; gap: 0.5rem;">
                <i class="ph-fill ph-warning-circle"></i> Low Stock Alerts
            </h2>
        </div>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php if($low_stock->num_rows > 0): ?>
                <?php while($row = $low_stock->fetch_assoc()): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: white; border-radius: 8px; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #fef2f2; color: var(--danger); display: flex; align-items: center; justify-content: center; font-weight: bold;">
                            <?= $row['blood_group'] ?>
                        </div>
                        <div>
                            <p style="font-size: 0.875rem; color: var(--text-muted);">Current Stock</p>
                            <p style="font-weight: 700; color: var(--danger);"><?= $row['quantity'] ?> Bottles</p>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; color: var(--success); padding: 1rem;">
                    <i class="ph-fill ph-check-circle" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                    <p>All blood groups are well stocked!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
