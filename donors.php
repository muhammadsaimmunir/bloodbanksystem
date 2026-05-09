<?php
require_once 'includes/db.php';

$error = '';
// Handle Add Donor
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_donor'])) {
    // Clean CNIC: keep only numbers
    $raw_cnic = preg_replace('/[^0-9]/', '', $_POST['cnic']);
    
    if(strlen($raw_cnic) !== 13) {
        $error = "Invalid CNIC format. It must be exactly 13 digits.";
    } else {
        // Format as XXXXX-XXXXXXX-X to store consistently
        $cnic = substr($raw_cnic, 0, 5) . '-' . substr($raw_cnic, 5, 7) . '-' . substr($raw_cnic, 12, 1);
        
        // Check if CNIC already exists
        $check = $conn->prepare("SELECT id FROM donors WHERE cnic = ?");
        $check->bind_param("s", $cnic);
        $check->execute();
        
        if($check->get_result()->num_rows > 0) {
            $error = "A donor with this CNIC is already registered.";
        } else {
            $name = $_POST['name'];
            $phone = $_POST['phone'];
            $location = $_POST['location'];
            $bg = $_POST['blood_group'];
            
            // Generate unique ID
            $unique_id = 'D-' . strtoupper(substr(uniqid(), -6));
            
            $stmt = $conn->prepare("INSERT INTO donors (unique_id, cnic, name, phone, location, blood_group) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $unique_id, $cnic, $name, $phone, $location, $bg);
            $stmt->execute();
            
            header("Location: donors.php?msg=added");
            exit();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 style="font-size: 1.125rem;">Register New Donor</h2>
    </div>
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger" style="margin: 1rem 1rem 0 1rem;"><i class="ph-fill ph-warning-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="hidden" name="add_donor" value="1">
        <div class="form-grid">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>CNIC</label>
                <input type="text" name="cnic" class="form-control" required placeholder="XXXXX-XXXXXXX-X" pattern="^[\d\-]{13,15}$" title="Enter valid 13 digit CNIC">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Blood Group</label>
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
            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Location / Address</label>
                <input type="text" name="location" class="form-control" required>
            </div>
        </div>
        <div style="margin-top: 1.5rem; text-align: right;">
            <button type="submit" class="btn btn-primary"><i class="ph ph-plus"></i> Register Donor</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <h2 style="font-size: 1.125rem;">Registered Donors</h2>
        <form method="GET" action="" style="display: flex; gap: 0.5rem;">
            <input type="text" name="search" class="form-control" placeholder="Search by CNIC, Name or ID" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" style="min-width: 250px;">
            <button type="submit" class="btn btn-primary"><i class="ph ph-magnifying-glass"></i> Search</button>
            <?php if(isset($_GET['search'])): ?>
                <a href="donors.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'added'): ?>
        <div class="alert alert-success" style="margin-bottom: 1rem;">Donor registered successfully!</div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Unique ID</th>
                    <th>Name / CNIC</th>
                    <th>Blood Group</th>
                    <th>Location</th>
                    <th>Tokens</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $search_query = "";
                if(isset($_GET['search']) && !empty(trim($_GET['search']))) {
                    $search = $conn->real_escape_string(trim($_GET['search']));
                    $possible_cnic = preg_replace('/[^0-9]/', '', $search);
                    if(strlen($possible_cnic) === 13) {
                        $search = substr($possible_cnic, 0, 5) . '-' . substr($possible_cnic, 5, 7) . '-' . substr($possible_cnic, 12, 1);
                    }
                    $search_query = " WHERE cnic = '$search' OR unique_id = '$search' OR name LIKE '%$search%' ";
                }
                
                $donors = $conn->query("SELECT * FROM donors $search_query ORDER BY created_at DESC");
                if($donors->num_rows > 0):
                    while($row = $donors->fetch_assoc()):
                ?>
                <tr>
                    <td><strong style="color: var(--primary);"><?= htmlspecialchars($row['unique_id']) ?></strong></td>
                    <td>
                        <?= htmlspecialchars($row['name']) ?><br>
                        <small style="color: var(--text-muted);"><?= htmlspecialchars($row['cnic']) ?></small>
                    </td>
                    <td><span class="badge badge-success"><?= htmlspecialchars($row['blood_group']) ?></span></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.25rem; font-weight: 600;">
                            <i class="ph-fill ph-coin" style="color: var(--warning);"></i>
                            <?= number_format($row['tokens'], 1) ?>
                        </div>
                    </td>
                    <td>
                        <a href="donate.php?id=<?= $row['id'] ?>" class="btn btn-secondary" style="padding: 0.5rem; font-size: 0.75rem;">
                            <i class="ph ph-drop"></i> Add Donation
                        </a>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else: 
                ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">No donors found matching your search.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
