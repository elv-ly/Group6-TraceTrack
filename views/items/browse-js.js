$(document).ready(function () {

    let searchTimer = null;
    let currentUserId = null;

    // ── Initial load ─────────────────────────────────────────────
    loadItems();

    // ── Real-time search ─────────────────────────────────────────
    $("#searchInput").on("input", function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadItems, 400);
    });

    $("#typeFilter, #categoryFilter").on("change", function () {
        loadItems();
    });

    $("#clearFilters").on("click", function () {
        $("#searchInput").val("");
        $("#typeFilter").val("");
        $("#categoryFilter").val("");
        loadItems();
    });

    // ── Load items via AJAX ──────────────────────────────────────
    function loadItems() {
        const search   = $("#searchInput").val();
        const type     = $("#typeFilter").val();
        const category = $("#categoryFilter").val();

        $("#resultsArea").html(`
            <div class="text-center py-5">
                <div class="spinner-border" style="color:var(--blue-glow)" role="status"></div>
                <p class="mt-2" style="color:var(--text-muted)">Searching...</p>
            </div>
        `);

        $.ajax({
            url: "/controllers/items/browse.php",
            type: "GET",
            data: { search, type, category },
            dataType: "json",

            success: function (res) {
                // Store current user ID for ownership checks
                if (res.current_user_id) {
                    currentUserId = res.current_user_id;
                }
                
                if (!res.status || res.items.length === 0) {
                    $("#resultsArea").html(`
                        <div class="tt-card">
                            <div class="tt-card-body text-center py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2" style="color:var(--text-muted)"></i>
                                <p style="color:var(--text-muted)">No items found. Try adjusting your search.</p>
                            </div>
                        </div>
                    `);
                    return;
                }

                let html = `<div class="row g-3">`;
                res.items.forEach(function (item) {
                    const typeBadge = item.report_type === 'lost'
                        ? `<span class="tt-badge tt-badge-red"><i class="bi bi-search"></i> Lost</span>`
                        : `<span class="tt-badge tt-badge-green"><i class="bi bi-box-seam"></i> Found</span>`;

                    const photoUrl = item.photo 
                        ? `${window.location.protocol}//${window.location.host}${item.photo}`
                        : null;
                    const thumb = photoUrl
                        ? `<img src="${photoUrl}" alt="${item.item_name}">`
                        : `<div class="tt-item-thumb-placeholder">
                                <i class="bi bi-image fs-1" style="color:var(--text-muted)"></i>
                                <small style="color:var(--text-muted)">No photo</small>
                           </div>`;


                    // Build buttons
                    const claimBtn = item.report_type === 'found'
                        ? `<button class="tt-btn-primary-sm tt-claim-btn"
                                    data-id="${item.item_id}"
                                    data-name="${item.item_name}">
                                    <i class="bi bi-hand-index"></i> Claim
                        </button>`
                        : '';

                   const returnBtn = (item.report_type === 'lost' && item.status === 'active' && item.user_id !== currentUserId && !item.has_return_request)
                        ? `<a href="/views/items/return_item.php?item_id=${encodeURIComponent(item.encrypted_id)}" 
                            class="tt-btn-primary-sm" style="background:#2E7D32;">
                            <i class="bi bi-arrow-return-left"></i> Return
                        </a>`
                        : (item.report_type === 'lost' && item.status === 'active' && item.user_id !== currentUserId && item.has_return_request)
                        ? `<button class="tt-btn-outline-sm" disabled style="cursor:not-allowed;">
                            <i class="bi bi-arrow-return-left"></i> Return Submitted
                        </button>`
                        : '';
                        
                    html += `
                        <div class="col-md-4 col-sm-6">
                            <div class="tt-item-card">
                                <div class="tt-item-thumb">${thumb}</div>
                                <div class="tt-item-body">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div class="tt-item-title">${item.item_name}</div>
                                        ${typeBadge}
                                    </div>
                                    <div class="tt-item-meta"><i class="bi bi-tag"></i> ${capitalize(item.category)}</div>
                                    <div class="tt-item-meta"><i class="bi bi-geo-alt"></i> ${item.location}</div>
                                    <div class="tt-item-meta"><i class="bi bi-calendar3"></i> ${formatDate(item.date_occured)}</div>
                                    <div class="tt-item-meta"><i class="bi bi-person"></i> ${item.full_name}</div>
                                    <div class="tt-item-actions">
                                        <a href="/views/items/view.php?id=${item.item_id}" class="tt-btn-outline-sm">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        ${claimBtn}
                                        ${returnBtn}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += `</div>`;
                html += `<p class="mt-3" style="color:var(--text-muted); font-size:.82rem;">${res.items.length} item(s) found</p>`;
                $("#resultsArea").html(html);

                // Bind claim buttons
                $(".tt-claim-btn").on("click", function () {
                    const id   = $(this).data("id");
                    const name = $(this).data("name");
                    $("#claimItemId").val(id);
                    $("#claimItemName").text(name);
                    // Reset form
                    $("[name='description']").val("");
                    $("[name='additional_info']").val("");
                    $("#proofPrompt").show();
                    $("#proofPreview").hide();
                    new bootstrap.Modal(document.getElementById("claimModal")).show();
                });
            },

            error: function () {
                $("#resultsArea").html(`
                    <div class="alert alert-danger">Failed to load items. Please refresh.</div>
                `);
            }
        });
    }

    // ── Proof photo preview ──────────────────────────────────────
    const proofDrop    = document.getElementById("proofDrop");
    const proofInput   = document.getElementById("proofInput");
    const proofPrompt  = document.getElementById("proofPrompt");
    const proofPreview = document.getElementById("proofPreview");
    const proofImg     = document.getElementById("proofImg");
    const proofName    = document.getElementById("proofFileName");

    if (proofDrop) {
        proofDrop.addEventListener("click", () => proofInput.click());
        proofInput.addEventListener("change", function () {
            if (proofInput.files[0]) handleProof(proofInput.files[0]);
        });
    }

    function handleProof(file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            proofImg.src = e.target.result;
            proofName.textContent = file.name + " (" + (file.size / 1024).toFixed(1) + " KB)";
            proofPrompt.style.display  = "none";
            proofPreview.style.display = "block";
        };
        reader.readAsDataURL(file);
    }

    // ── Helpers ──────────────────────────────────────────────────
    function capitalize(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

});
