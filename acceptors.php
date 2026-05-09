<?php
require_once 'includes/db.php';

$PRICE_PER_BOTTLE = 5000; // Rs. 5000 base price

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_acceptor'])) {
    $name = $_POST['name'];
    $cnic = $_POST['cnic'];
    $phone = $_POST['phone'];
    $bg = $_POST['blood_group'];
    $bottles = intval($_POST['bottles_received']);
    $donor_uid = trim($_POST['donor_uid']);
    
    // Check inventory first
    $inv = $conn->query("SELECT quantity FROM inventory WHERE blood_group = '$bg'")->fetch_assoc();
    if(!$inv || $inv['quantity'] < $bottles) {
        $error = "Not enough blood in inventory. Current stock for $bg is " . ($inv ? $inv['quantity'] : 0);
    } else {
        $donor_id = null;
        $tokens_used = 0;
        $amount_paid = $bottles * $PRICE_PER_BOTTLE;
        
        $conn->begin_transaction();
        try {
            // Check if donor UID or CNIC is provided
            if(!empty($donor_uid)) {
                // If the user entered a CNIC, clean it up and format it
                $possible_cnic = preg_replace('/[^0-9]/', '', $donor_uid);
                if(strlen($possible_cnic) === 13) {
                    $search_term = substr($possible_cnic, 0, 5) . '-' . substr($possible_cnic, 5, 7) . '-' . substr($possible_cnic, 12, 1);
                } else {
                    $search_term = $donor_uid; // Maybe they entered LD-XXXXXX
                }
                
                $stmt = $conn->prepare("SELECT id, tokens FROM donors WHERE unique_id = ? OR cnic = ?");
                $stmt->bind_param("ss", $search_term, $search_term);
                $stmt->execute();
                $d_res = $stmt->get_result();
                if($d_res->num_rows > 0) {
                    $donor = $d_res->fetch_assoc();
                    $donor_id = $donor['id'];
                    $avail_tokens = floatval($donor['tokens']);
                    
                    // Logic: 1 token = 1 free bottle. 0.5 token = 50% off on 1 bottle.
                    // We will use as many tokens as possible to cover the bottles.
                    $tokens_needed = $bottles * 1.0; 
                    
                    if($avail_tokens >= $tokens_needed) {
                        $tokens_used = $tokens_needed;
                        $amount_paid = 0;
                    } else {
                        // Use whatever is available
                        $tokens_used = floor($avail_tokens * 2) / 2; // nearest 0.5
                        
                        $free_bottles = floor($tokens_used);
                        $half_bottles = ($tokens_used - $free_bottles) > 0 ? 1 : 0;
                        
                        $paid_bottles = $bottles - $free_bottles - ($half_bottles * 0.5);
                        $amount_paid = $paid_bottles * $PRICE_PER_BOTTLE;
                    }
                    
                    // Deduct tokens
                    $conn->query("UPDATE donors SET tokens = tokens - $tokens_used WHERE id = $donor_id");
                }
            }
            
            // Insert acceptor
            $stmt = $conn->prepare("INSERT INTO acceptors (donor_id, name, cnic, phone, blood_group, bottles_received, tokens_used, amount_paid) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssidd", $donor_id, $name, $cnic, $phone, $bg, $bottles, $tokens_used, $amount_paid);
            $stmt->execute();
            
            // Deduct inventory
            $conn->query("UPDATE inventory SET quantity = quantity - $bottles WHERE blood_group = '$bg'");
            
            $conn->commit();
            header("Location: acceptors.php?msg=added&paid=$amount_paid&tokens=$tokens_used");
            exit();
            
        } catch(Exception $e) {
            $conn->rollback();
            $error = "Transaction failed.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 style="font-size: 1.125rem;">Register Acceptor (Blood Request)</h2>
    </div>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><i class="ph-fill ph-warning-circle"></i> <?= $error ?></div>
    <?php endif; ?>
    
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'added'): ?>
        <div class="alert alert-success" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 0.5rem;"><i class="ph-fill ph-check-circle"></i> Request Processed Successfully</h3>
            <p><strong>Tokens Used:</strong> <?= htmlspecialchars($_GET['tokens']) ?></p>
            <p style="font-size: 1.25rem; margin-top: 0.5rem;"><strong>Final Amount to Pay:</strong> Rs. <?= number_format($_GET['paid'], 2) ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="add_acceptor" value="1">
        <div class="form-grid">
            <div class="form-group">
                <label>Receiver Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>CNIC</label>
                <input type="text" name="cnic" class="form-control" required placeholder="XXXXX-XXXXXXX-X">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Blood Group Required</label>
                <select name="blood_group" class="form-control" required>
                    <option value="">Select...</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
            </div>
            <div class="form-group">
                <label>Bottles Needed</label>
                <input type="number" name="bottles_received" class="form-control" required min="1" value="1">
                <small style="color: var(--text-muted); margin-top: 0.25rem;">Base Price: Rs. <?= $PRICE_PER_BOTTLE ?> / bottle</small>
            </div>
            <div class="form-group">
                <label style="color: var(--primary);">Donor ID or CNIC (Optional)</label>
                <input type="text" name="donor_uid" class="form-control" placeholder="D-XXXXXX or XXXXX-XXXXXXX-X">
                <small style="color: var(--text-muted); margin-top: 0.25rem;">Enter if receiver is a registered donor to use tokens for discount.</small>
            </div>
        </div>
        <div style="margin-top: 1.5rem; text-align: right;">
            <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Process Request</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 style="font-size: 1.125rem;">Recent Acceptors</h2>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name / CNIC</th>
                    <th>Blood Group</th>
                    <th>Bottles</th>
                    <th>Tokens Used</th>
                    <th>Amount Paid</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $acceptors = $conn->query("SELECT * FROM acceptors ORDER BY created_at DESC");
                while($row = $acceptors->fetch_assoc()):
                ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($row['name']) ?><br>
                        <small style="color: var(--text-muted);"><?= htmlspecialchars($row['cnic']) ?></small>
                    </td>
                    <td><span class="badge badge-danger"><?= htmlspecialchars($row['blood_group']) ?></span></td>
                    <td><?= htmlspecialchars($row['bottles_received']) ?></td>
                    <td><?= number_format($row['tokens_used'], 1) ?></td>
                    <td style="font-weight: 600;">Rs. <?= number_format($row['amount_paid'], 2) ?></td>
                    <td><?= date('d M Y, h:i A', strtotime($row['created_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
