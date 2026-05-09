<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Phosphor Icons for premium vector icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="topbar">
                <div class="topbar-title">
                    <button id="sidebar-toggle" class="btn btn-secondary" style="display: none;"><i class="ph ph-list"></i></button>
                    <h1>Dashboard</h1>
                </div>
                <div class="topbar-actions">
                    <div class="user-profile">
                        <i class="ph ph-user-circle" style="font-size: 1.5rem; color: var(--primary);"></i>
                        <span style="font-weight: 500;">Admin</span>
                    </div>
                </div>
            </div>
