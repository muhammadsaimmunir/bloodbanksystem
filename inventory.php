<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Get total stock
$total_blood = $conn->query("SELECT SUM(quantity) as sum FROM inventory")->fetch_assoc()['sum'];
if(!$total_blood) $total_blood = 0;
?>

<div class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card" style="background: var(--primary); color: white; border-color: transparent;">
        <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;">
            <i class="ph-fill ph-drop"></i>
        </div>
        <div class="stat-details">
            <h3 style="color: rgba(255,255,255,0.8);">Total Available Blood</h3>
            <p style="color: white;"><?= $total_blood ?> Bottles</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 style="font-size: 1.125rem;">Blood Inventory Status</h2>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem;">
        <?php
        $inventory = $conn->query("SELECT * FROM inventory");
        while($row = $inventory->fetch_assoc()):
            $bg = $row['blood_group'];
            $qty = $row['quantity'];
            
            $status_color = 'var(--success)';
            $bg_color = '#ecfdf5';
            
            if($qty == 0) {
                $status_color = 'var(--danger)';
                $bg_color = '#fef2f2';
            } elseif($qty < 5) {
                $status_color = 'var(--warning)';
                $bg_color = '#fffbeb';
            }
        ?>
        <div style="background: <?= $bg_color ?>; border: 1px solid <?= $status_color ?>; border-radius: 12px; padding: 1.5rem; text-align: center; transition: transform 0.2s;">
            <div style="font-size: 2rem; font-weight: 700; color: <?= $status_color ?>; margin-bottom: 0.5rem;">
                <?= $bg ?>
            </div>
            <div style="font-size: 1.5rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem;">
                <?= $qty ?> <span style="font-size: 0.875rem; font-weight: 400; color: var(--text-muted);">Bottles</span>
            </div>
            <?php if($qty == 0): ?>
                <span class="badge badge-danger">Out of Stock</span>
            <?php elseif($qty < 5): ?>
                <span class="badge badge-warning">Low Stock</span>
            <?php else: ?>
                <span class="badge badge-success">Available</span>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
