<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'TraceTrack' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
            padding-top: 50px;
            padding-bottom: 65px;
        }
        .tt-auth-body {
            background: linear-gradient(135deg, #0D1B2A 0%, #1A2332 100%);
        }
        .tt-header-top {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background: #12243b;
            color: white;
            padding: 0.7rem 0;
            text-align: center;
            border-bottom: 3px solid #0b2c49;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        .tt-header-top-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .tt-header-icon {
            font-size: 1.3rem;
        }
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tt-footer-sticky {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background: #12243b;
            color: #E0E0E0;
            padding: 0.9rem 0;
            text-align: center;
            border-top: 3px solid #0b2c49;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.2);
        }
        .tt-footer-content {
            letter-spacing: 0.3px;
            font-size: 0.9rem;
        }
        .tt-footer-main {
            color: #B0BEC5;
            font-weight: 400;
        }
    </style>
</head>
<body class="tt-auth-body">

<!-- HEADER -->
<div class="tt-header-top">
    <div class="tt-header-top-content">
        <span class="tt-header-icon">🔍</span>
        <span>TraceTrack — SLSU Main Campus Lost & Found</span>
    </div>
</div>

<main>

<?php if (isset($_SESSION['success'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({ title: "Success!", text: '<?= addslashes($_SESSION['success']) ?>', icon: "success", confirmButtonColor: "#1565C0" });
    });
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({ title: "Error!", text: '<?= addslashes($_SESSION['error']) ?>', icon: "error", confirmButtonColor: "#1565C0" });
    });
</script>
<?php unset($_SESSION['error']); endif; ?>

<?= $content ?>

</main>

<!-- FOOTER -->
<footer class="tt-footer-sticky">
    <div class="tt-footer-content">
        <div class="tt-footer-main">TraceTrack • 2026 • All Rights Reserved</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
