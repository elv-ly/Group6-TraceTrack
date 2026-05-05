<?php
$title = "Browse Items";
require_once __DIR__ . '/../../autoload.php';
requireLogin();

$me = sessionUser();
ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1>Browse Items</h1>
        <p>Search and filter active lost and found reports on campus.</p>
    </div>
</div>

<?= csrf_field() ?>

<!-- Filters -->
<div class="tt-card mb-4">
    <div class="tt-card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="tt-filter-label">Search</label>
                <input type="text" id="searchInput" class="tt-input"
                       placeholder="Search by item name, description, or location...">
            </div>
            <div class="col-md-3">
                <label class="tt-filter-label">Type</label>
                <select id="typeFilter" class="tt-input">
                    <option value="">All Types</option>
                    <option value="lost">Lost Items</option>
                    <option value="found">Found Items</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="tt-filter-label">Category</label>
                <select id="categoryFilter" class="tt-input">
                    <option value="">All Categories</option>
                    <option value="electronics">Electronics</option>
                    <option value="clothing">Clothing</option>
                    <option value="documents">Documents</option>
                    <option value="accessories">Accessories</option>
                    <option value="keys">Keys</option>
                    <option value="others">Others</option>
                </select>
            </div>
            <div class="col-md-1">
                <button id="clearFilters" class="tt-btn-outline-sm w-100" style="padding:.72rem;">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Results -->
<div id="resultsArea">
    <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2" style="color:var(--text-muted)">Loading items...</p>
    </div>
</div>

<!-- Claim Modal -->
<div class="modal fade" id="claimModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content tt-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-hand-index"></i> File a Claim</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/controllers/claims/create.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="item_id" id="claimItemId">
                <div class="modal-body">
                    <div class="tt-form-info mb-3">
                        <i class="bi bi-info-circle"></i>
                        You are claiming: <strong id="claimItemName"></strong>.
                        Provide thorough proof of ownership — the admin will review everything before approving.
                    </div>

                    <div class="tt-form-group">
                        <label>Describe Distinguishing Features <span class="tt-required">*</span></label>
                        <textarea name="description" class="tt-input" rows="4"
                            placeholder="Describe specific features that only the owner would know — scratches, stickers, contents inside, engravings, color details, etc."
                            required></textarea>
                    </div>

                    <div class="tt-form-group">
                        <label>Proof Photo <span class="tt-muted-label">(Receipt, photo with item, ID alongside item, etc.)</span></label>
                        <div class="tt-file-drop" id="proofDrop">
                            <input type="file" name="proof_photo" id="proofInput" accept="image/jpeg,image/png,image/webp" hidden>
                            <div id="proofPrompt">
                                <i class="bi bi-cloud-arrow-up fs-2" style="color:var(--text-muted)"></i>
                                <p>Click to upload proof photo</p>
                            </div>
                            <div id="proofPreview" style="display:none;">
                                <img id="proofImg" src="" style="max-height:140px; border-radius:8px;">
                                <p id="proofFileName" class="mt-1" style="font-size:.8rem; color:var(--text-muted)"></p>
                            </div>
                        </div>
                    </div>

                    <div class="tt-form-group">
                        <label>Additional Information <span class="tt-muted-label">(Serial number, brand, model, purchase date, etc.)</span></label>
                        <textarea name="additional_info" class="tt-input" rows="3"
                            placeholder="Any other identifying details that support your claim..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="tt-btn-outline-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="tt-btn-primary-sm">
                        <i class="bi bi-send"></i> Submit Claim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.tt-filter-label { display:block; font-size:.78rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-bottom:.4rem; }
.tt-item-card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; overflow:hidden; transition:border-color .2s, transform .2s; height:100%; display:flex; flex-direction:column; }
.tt-item-card:hover { border-color:var(--blue); transform:translateY(-2px); }
.tt-item-thumb { width:100%; height:160px; object-fit:cover; background:var(--input-bg); display:flex; align-items:center; justify-content:center; }
.tt-item-thumb img { width:100%; height:160px; object-fit:cover; }
.tt-item-thumb-placeholder { height:160px; background:var(--input-bg); display:flex; align-items:center; justify-content:center; flex-direction:column; gap:.3rem; }
.tt-item-body { padding:1rem; flex:1; display:flex; flex-direction:column; }
.tt-item-title { font-family:'Sora',sans-serif; font-weight:600; font-size:.95rem; margin-bottom:.4rem; }
.tt-item-meta { font-size:.8rem; color:var(--text-muted); margin-bottom:.3rem; }
.tt-item-actions { margin-top:auto; padding-top:.75rem; display:flex; gap:.5rem; }
.tt-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.22rem .65rem; border-radius:99px; font-size:.72rem; font-weight:600; }
.tt-badge-red    { background:rgba(198,40,40,.18);   color:#EF9A9A; }
.tt-badge-green  { background:rgba(46,125,50,.18);   color:#A5D6A7; }
.tt-required { color:#EF9A9A; }
.tt-muted-label { color:var(--text-muted); font-size:.78rem; font-weight:400; text-transform:none; letter-spacing:0; }
.tt-file-drop { border:2px dashed var(--border); border-radius:10px; padding:1.25rem; text-align:center; cursor:pointer; background:var(--input-bg); transition:border-color .2s; }
.tt-file-drop:hover { border-color:var(--blue); }
.tt-file-drop p { color:var(--text-muted); font-size:.85rem; margin:.3rem 0 0; }
.tt-modal { background:var(--card-bg); color:var(--text); border:1px solid var(--border); }
.tt-modal .modal-header,.tt-modal .modal-footer { border-color:var(--border); }
.tt-form-info { background:rgba(21,101,192,.1); border:1px solid rgba(21,101,192,.25); border-radius:8px; padding:.75rem 1rem; font-size:.86rem; color:var(--text-muted); display:flex; align-items:flex-start; gap:.5rem; }
.tt-form-info i { color:var(--blue-glow); margin-top:.1rem; flex-shrink:0; }
</style>

<script src="/views/items/browse-js.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
