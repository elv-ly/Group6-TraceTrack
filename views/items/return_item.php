<?php
$title = "Return Item";
require_once __DIR__ . '/../../autoload.php';
requireLogin();

$me = sessionUser();
$encrypted_id = urldecode($_GET['item_id'] ?? '');
$item_id = decryptId($encrypted_id);

if (!$item_id || !is_numeric($item_id)) {
    $_SESSION['error'] = "Invalid item ID. Please try again.";
    header("Location: /views/items/browse.php");
    exit;
}

$item = new Item($db);
$record = $item->readOne((int)$item_id);

if (!$record) {
    $_SESSION['error'] = "Item not found.";
    header("Location: /views/items/browse.php");
    exit;
}

if ($record['report_type'] !== 'lost') {
    $_SESSION['error'] = "You can only return lost items.";
    header("Location: /views/items/browse.php");
    exit;
}

if ($record['status'] !== 'active') {
    $_SESSION['error'] = "This item is no longer available for return.";
    header("Location: /views/items/browse.php");
    exit;
}

if ($record['user_id'] == $me['id']) {
    $_SESSION['error'] = "You cannot return your own report.";
    header("Location: /views/items/browse.php");
    exit;
}

if ($item->hasReturnRequest($item_id, $me['id'])) {
    $_SESSION['error'] = "You have already submitted a return request for this item. You can only submit one return request per item.";
    header("Location: /views/items/browse.php");
    exit;
}

ob_start();
?>

<div class="tt-page-header">
    <h1>Return Item</h1>
    <p>You found "<?= htmlspecialchars($record['item_name']) ?>". Please provide details so the owner can confirm.</p>
</div>

<div class="tt-card">
    <div class="tt-card-body">
        <form action="/controllers/items/return_request.php" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="item_id" value="<?= $encrypted_id ?>">

            <div class="tt-form-group">
                <label>Where did you find this item? <span class="tt-required">*</span></label>
                <input type="text" name="found_location" class="tt-input" required>
            </div>

            <div class="tt-form-group">
                <label>Additional description (optional)</label>
                <textarea name="finder_description" class="tt-input" rows="3" placeholder="e.g., I found it near the library entrance..."></textarea>
            </div>

            <div class="tt-form-group">
                <label>Proof photo (optional)</label>
                <div class="tt-file-drop" id="proofDrop">
                    <input type="file" name="proof_photo" id="proofInput" accept="image/jpeg,image/png,image/webp" hidden>
                    <div id="proofPrompt">
                        <i class="bi bi-cloud-arrow-up fs-2"></i>
                        <p>Click to upload a photo</p>
                    </div>
                    <div id="proofPreview" style="display:none;">
                        <img id="proofImg" src="" style="max-height:140px; border-radius:8px;">
                        <p id="proofFileName" class="mt-1"></p>
                    </div>
                </div>
            </div>

            <div class="tt-form-info">
                <i class="bi bi-info-circle"></i>
                The owner will be notified and must confirm this return. Once confirmed, the item will be marked as returned.
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="tt-btn-primary">Submit Return Request</button>
                <a href="/views/items/view.php?id=<?= $item_id ?>" class="tt-btn-outline-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Simple file preview (reuse from create-js.js)
const drop = document.getElementById('proofDrop');
const input = document.getElementById('proofInput');
const prompt = document.getElementById('proofPrompt');
const preview = document.getElementById('proofPreview');
const img = document.getElementById('proofImg');
const fileName = document.getElementById('proofFileName');

if (drop) {
    drop.addEventListener('click', () => input.click());
    input.addEventListener('change', function() {
        if (input.files[0]) handleFile(input.files[0]);
    });
}

function handleFile(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        img.src = e.target.result;
        fileName.textContent = file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)';
        prompt.style.display = 'none';
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
}
</script>

<style>
.tt-file-drop { border:2px dashed var(--border); border-radius:10px; padding:1rem; text-align:center; cursor:pointer; background:var(--input-bg); }
.tt-file-drop:hover { border-color:var(--blue); }
.tt-form-info { background:rgba(21,101,192,.1); border-radius:8px; padding:.75rem 1rem; display:flex; gap:.5rem; margin-top:1rem; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>