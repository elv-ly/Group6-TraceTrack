<?php
// Load required dependencies
require_once __DIR__ . '/../../autoload.php';

// Ensure user is logged in
requireLogin();

// Get report type from URL (lost or found), default to 'lost'
$report_type = $_GET['type'] ?? 'lost';
if (!in_array($report_type, ['lost', 'found'])) $report_type = 'lost';

// Set page variables based on report type
$is_lost  = $report_type === 'lost';
$title    = $is_lost ? 'Report Lost Item' : 'Report Found Item';
$icon     = $is_lost ? 'bi-search' : 'bi-box-seam';
$color    = $is_lost ? '#EF9A9A' : '#A5D6A7';
$today    = date('Y-m-d'); // Max date for date picker

// Start output buffering
ob_start();
?>

<!-- Page Header -->
<div class="tt-page-header">
    <div>
        <h1><i class="bi <?= $icon ?>" style="color:<?= $color ?>"></i> <?= $title ?></h1>
        <p><?= $is_lost
            ? 'Fill in the details of the item you lost. Once submitted, it will be reviewed by the admin before going public.'
            : 'Fill in the details of the item you found. Once submitted, it will be reviewed by the admin before going public.' ?>
        </p>
    </div>
</div>

<!-- Type Toggle (Switch between Lost/Found forms) -->
<div class="tt-type-toggle mb-4">
    <a href="/views/items/create.php?type=lost"
       class="tt-type-btn <?= $is_lost ? 'active' : '' ?>">
        <i class="bi bi-search"></i> Report Lost Item
    </a>
    <a href="/views/items/create.php?type=found"
       class="tt-type-btn <?= !$is_lost ? 'active' : '' ?>">
        <i class="bi bi-box-seam"></i> Report Found Item
    </a>
</div>

<!-- Main Form Card -->
<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-clipboard-plus"></i>
            <?= $is_lost ? 'Lost Item Details' : 'Found Item Details' ?>
        </h5>
    </div>
    <div class="tt-card-body">
        <!-- Form submits to create.php controller -->
        <form action="/controllers/items/create.php" method="POST" enctype="multipart/form-data" id="itemForm">
            <?= csrf_field() ?> <!-- CSRF protection -->
            <input type="hidden" name="report_type" value="<?= $report_type ?>">

            <!-- Row 1: Item Name & Category -->
            <div class="tt-form-row">
                <div class="tt-form-group">
                    <label>Item Name <span class="tt-required">*</span></label>
                    <input type="text" name="item_name" class="tt-input"
                           placeholder="e.g. Black Wallet, iPhone 13, School ID" required>
                </div>
                <div class="tt-form-group">
                    <label>Category <span class="tt-required">*</span></label>
                    <select name="category" class="tt-input" required>
                        <option value="" disabled selected>Select a category</option>
                        <option value="electronics">Electronics</option>
                        <option value="clothing">Clothing</option>
                        <option value="documents">Documents</option>
                        <option value="accessories">Accessories</option>
                        <option value="keys">Keys</option>
                        <option value="others">Others</option>
                    </select>
                </div>
            </div>

            <!-- Description Field -->
            <div class="tt-form-group">
                <label>Description <span class="tt-required">*</span></label>
                <textarea name="description" class="tt-input tt-textarea" rows="4"
                    placeholder="<?= $is_lost
                        ? 'Describe the item in detail — color, brand, distinguishing marks, contents, etc.'
                        : 'Describe the item in detail — condition, color, brand, any visible marks, etc.' ?>"
                    required></textarea>
            </div>

            <!-- Row 2: Location & Date -->
            <div class="tt-form-row">
                <div class="tt-form-group">
                    <label><?= $is_lost ? 'Last Known Location' : 'Location Found' ?> <span class="tt-required">*</span></label>
                    <input type="text" name="location" class="tt-input"
                           placeholder="e.g. Room 201, Library, Canteen Area" required>
                </div>
                <div class="tt-form-group">
                    <label><?= $is_lost ? 'Date Lost' : 'Date Found' ?> <span class="tt-required">*</span></label>
                    <input type="date" name="date_occured" class="tt-input" max="<?= $today ?>" required>
                </div>
            </div>

            <!-- Photo Upload with Preview -->
            <div class="tt-form-group">
                <label>Photo <span class="tt-muted-label">(Optional — JPG, PNG, WEBP, max 5MB)</span></label>
                <div class="tt-file-drop" id="fileDrop">
                    <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/webp" hidden>
                    <div id="filePrompt">
                        <i class="bi bi-cloud-arrow-up fs-2" style="color:var(--text-muted)"></i>
                        <p>Click to upload or drag & drop a photo</p>
                    </div>
                    <div id="filePreview" style="display:none;">
                        <img id="previewImg" src="" alt="Preview" style="max-height:160px; border-radius:8px;">
                        <p id="fileName" class="mt-2" style="font-size:.82rem; color:var(--text-muted)"></p>
                    </div>
                </div>
            </div>

            <!-- Info Notice -->
            <div class="tt-form-info">
                <i class="bi bi-info-circle"></i>
                Your report will be submitted for <strong>admin review</strong> before it appears publicly.
                You will be notified once it is approved or rejected.
            </div>

            <!-- Form Actions -->
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="tt-btn-primary" style="max-width:200px;">
                    <i class="bi bi-send"></i> Submit Report
                </button>
                <a href="/views/items/my_reports.php" class="tt-btn-outline-sm" style="padding:.85rem 1.25rem;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Inline Styles -->
<style>
.tt-type-toggle { display:flex; gap:.75rem; }

.tt-type-btn {
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.6rem 1.25rem;
    border-radius:8px;
    border:1px solid var(--border);
    color:var(--text-muted);
    font-size:.9rem; font-weight:500;
    transition:all .2s;
    text-decoration:none;
}

.tt-type-btn:hover { border-color:var(--blue); color:var(--blue-glow); }
.tt-type-btn.active { background:rgba(21,101,192,.15); border-color:var(--blue); color:var(--blue-glow); font-weight:600; }

.tt-textarea {
    resize:vertical;
    min-height:100px;
    font-family:'DM Sans',sans-serif;
}

.tt-required { color:#EF9A9A; }
.tt-muted-label { color:var(--text-muted); font-size:.78rem; font-weight:400; text-transform:none; letter-spacing:0; }

.tt-file-drop {
    border:2px dashed var(--border);
    border-radius:10px;
    padding:1.5rem;
    text-align:center;
    cursor:pointer;
    transition:border-color .2s;
    background:var(--input-bg);
}

.tt-file-drop:hover { border-color:var(--blue); }
.tt-file-drop p { color:var(--text-muted); font-size:.88rem; margin:.4rem 0 0; }

.tt-form-info {
    background:rgba(21,101,192,.1);
    border:1px solid rgba(21,101,192,.25);
    border-radius:8px;
    padding:.75rem 1rem;
    font-size:.86rem;
    color:var(--text-muted);
    display:flex;
    align-items:flex-start;
    gap:.5rem;
    margin-top:1rem;
}

.tt-form-info i { color:var(--blue-glow); margin-top:.1rem; flex-shrink:0; }
</style>

<!-- External JavaScript for file upload handling -->
<script src="/views/items/create-js.js"></script>

<?php
// Capture buffered content and include layout template
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
