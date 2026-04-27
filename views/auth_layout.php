<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'TraceTrack' ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- SweetAlert for notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="tt-auth-body">

<!-- Display success message if exists -->
<?php if (isset($_SESSION['success'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({ 
            title: "Success!", 
            text: '<?= addslashes($_SESSION['success']) ?>', 
            icon: "success", 
            confirmButtonColor: "#1565C0" 
        });
    });
</script>
<?php unset($_SESSION['success']); endif; ?>

<!-- Display error message if exists -->
<?php if (isset($_SESSION['error'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({ 
            title: "Error!", 
            text: '<?= addslashes($_SESSION['error']) ?>', 
            icon: "error", 
            confirmButtonColor: "#1565C0" 
        });
    });
</script>
<?php unset($_SESSION['error']); endif; ?>

<!-- Page content injected here -->
<?= $content ?>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
