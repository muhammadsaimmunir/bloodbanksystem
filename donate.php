<?php
require_once 'includes/db.php';

if(!isset($_GET['id'])) {
    header("Location: donors.php");
    exit();
}

$id = intval($_GET['id']);
$donor = $conn->query("SELECT * FROM donors WHERE id = $id")->fetch_assoc();

if(!$donor) {
    header("Location: donors.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quantity = intval($_POST['quantity']); // bottles
    
    // 1 bottle = 0.5 tokens
    $tokens_earned = $quantity * 0.5;
    
    $conn->begin_transaction();
    try {
        // Log donation
        $stmt1 = $conn->prepare("INSERT INTO donations (donor_id, quantity) VALUES (?, ?)");
        $stmt1->bind_param("ii", $id, $quantity);
        $stmt1->execute();
        
        // Update donor tokens
        $stmt2 = $conn->prepare("UPDATE donors SET tokens = tokens + ? WHERE id = ?");
        $stmt2->bind_param("di", $tokens_earned, $id);
        $stmt2->execute();
        
        // Update inventory
        $bg = $donor['blood_group'];
        $stmt3 = $conn->prepare("UPDATE inventory SET quantity = quantity + ? WHERE blood_group = ?");
        $stmt3->bind_param("is", $quantity, $bg);
        $stmt3->execute();
        
        $conn->commit();
        header("Location: donors.php?msg=donation_added");
        exit();
    } catch(Exception $e) {
        $conn->rollback();
        $error = "Error adding donation.";
    }
}

require_once 'includes/header.php';
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 style="font-size: 1.125rem;">Record Donation for <?= htmlspecialchars($donor['name']) ?></h2>
        <a href="donors.php" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Back</a>
    </div>
    
    <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Blood Group</p>
            <p style="font-weight: 700; font-size: 1.25rem; color: var(--primary);"><?= $donor['blood_group'] ?></p>
        </div>
        <div>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Current Tokens</p>
            <p style="font-weight: 700; font-size: 1.25rem; color: var(--warning); display: flex; align-items: center; gap: 0.25rem;">
                <i class="ph-fill ph-coin"></i> <?= number_format($donor['tokens'], 1) ?>
            </p>
        </div>
    </div>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label>Bottles Donated</label>
            <input type="number" name="quantity" class="form-control" required min="1" value="1">
            <small style="color: var(--text-muted); margin-top: 0.5rem;">1 Bottle = 0.5 Tokens earned</small>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
            <i class="ph ph-check-circle"></i> Complete Donation
        </button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
