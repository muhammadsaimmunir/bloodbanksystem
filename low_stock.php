<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Get low stock inventory (less than 5 bottles)
$low_stock = $conn->query("SELECT * FROM inventory WHERE quantity < 5 ORDER BY quantity ASC");
?>

<div class="card" style="border-top: 4px solid var(--danger);">
    <div class="card-header">
        <h2 style="font-size: 1.125rem; color: var(--danger); display: flex; align-items: center; gap: 0.5rem;">
            <i class="ph-fill ph-warning-circle"></i> Low Stock Action Required
        </h2>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem;">
            The following blood groups are critically low. Please contact the registered donors below.
        </p>
    </div>
    
    <?php if($low_stock->num_rows == 0): ?>
        <div style="text-align: center; padding: 3rem; color: var(--success);">
            <i class="ph-fill ph-check-circle" style="font-size: 4rem; margin-bottom: 1rem;"></i>
            <h3>All Good!</h3>
            <p>No blood groups are currently low on stock.</p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <?php while($row = $low_stock->fetch_assoc()): 
                $bg = $row['blood_group'];
                $qty = $row['quantity'];
                
                // Fetch donors for this blood group
                $stmt = $conn->prepare("SELECT name, phone, location FROM donors WHERE blood_group = ?");
                $stmt->bind_param("s", $bg);
                $stmt->execute();
                $donors = $stmt->get_result();
            ?>
                <div style="border: 1px solid var(--border); border-radius: 12px; overflow: hidden;">
                    <div style="background: #fef2f2; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #fecaca;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--danger); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.25rem;">
                                <?= $bg ?>
                            </div>
                            <div>
                                <h3 style="color: var(--danger); margin-bottom: 0.25rem;">Needs Attention</h3>
                                <p style="font-size: 0.875rem; color: var(--text-muted);">Current Stock: <strong><?= $qty ?> Bottles</strong></p>
                            </div>
                        </div>
                    </div>
                    
                    <div style="padding: 1.5rem;">
                        <h4 style="margin-bottom: 1rem; font-size: 1rem;">Available Donors to Contact</h4>
                        <?php if($donors->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Donor Name</th>
                                            <th>Phone Number</th>
                                            <th>Location</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($donor = $donors->fetch_assoc()): ?>
                                        <tr>
                                            <td style="font-weight: 500;"><?= htmlspecialchars($donor['name']) ?></td>
                                            <td><?= htmlspecialchars($donor['phone']) ?></td>
                                            <td><?= htmlspecialchars($donor['location']) ?></td>
                                            <td>
                                                <a href="tel:<?= htmlspecialchars($donor['phone']) ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.75rem;">
                                                    <i class="ph-fill ph-phone-call"></i> Call
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning" style="background: #fffbeb; color: var(--warning); border: 1px solid #fde68a;">
                                <i class="ph-fill ph-warning"></i> No registered donors found for blood group <?= $bg ?>.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
