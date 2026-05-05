$(document).ready(function () {

    function ajaxAction(url, data, callback) {
        data.csrf_token = $("#csrf_token").val();
        $.ajax({
            url, type: "POST", data, dataType: "json",
            success: callback,
            error: function () {
                Swal.fire({ title: "Error!", text: "Something went wrong.", icon: "error", confirmButtonColor: "#1565C0" });
            }
        });
    }

    function successReload(res) {
        if (res.status) {
            let t;
            Swal.fire({
                icon: "success", title: "Done!", html: res.message + " Closing in <b></b>ms.",
                timer: 2000, timerProgressBar: true,
                didOpen: () => { Swal.showLoading(); t = setInterval(() => Swal.getPopup().querySelector("b").textContent = Swal.getTimerLeft(), 100); },
                willClose: () => clearInterval(t)
            }).then(() => window.location.reload());
        } else {
            Swal.fire({ title: "Error!", text: res.message, icon: "error", confirmButtonColor: "#1565C0" });
        }
    }

    // ── Approve claim ────────────────────────────────────────────
    $(".tt-approve-claim").on("click", function () {
        const id = $(this).data("id"), name = $(this).data("name");
        Swal.fire({
            title: "Approve Claim?",
            text: `Approve the claim on "${name}"? The item will be marked as claimed.`,
            icon: "question", showCancelButton: true,
            confirmButtonColor: "#1565C0", confirmButtonText: "Yes, Approve"
        }).then(r => {
            if (!r.isConfirmed) return;
            ajaxAction("/controllers/admin/claims.php", { action: "approve", claim_id: id }, successReload);
        });
    });

    // ── Open reject claim modal ───────────────────────────────────
    $(".tt-reject-claim").on("click", function () {
        $("#rejectClaimId").val($(this).data("id"));
        $("#rejectClaimName").text($(this).data("name"));
        $("#rejectClaimReason").val("");
        new bootstrap.Modal(document.getElementById("rejectClaimModal")).show();
    });

    $("#confirmRejectClaim").on("click", function () {
        const id = $("#rejectClaimId").val(), reason = $("#rejectClaimReason").val().trim();
        if (!reason) { Swal.fire({ title: "Required", text: "Reason is required.", icon: "warning", confirmButtonColor: "#1565C0" }); return; }
        bootstrap.Modal.getInstance(document.getElementById("rejectClaimModal")).hide();
        ajaxAction("/controllers/admin/claims.php", { action: "reject", claim_id: id, reason }, successReload);
    });

    // ── Mark returned ─────────────────────────────────────────────
    $(".tt-mark-returned").on("click", function () {
        const id = $(this).data("id"), name = $(this).data("name");
        Swal.fire({
            title: "Mark as Returned?",
            text: `Confirm that "${name}" has been returned to its owner?`,
            icon: "question", showCancelButton: true,
            confirmButtonColor: "#2E7D32", confirmButtonText: "Yes, Mark Returned"
        }).then(r => {
            if (!r.isConfirmed) return;
            ajaxAction("/controllers/admin/claims.php", { action: "returned", claim_id: id }, successReload);
        });
    });

    // ── Approve cancel ───────────────────────────────────────────
    $(".tt-approve-cancel").on("click", function () {
        const id = $(this).data("id"), name = $(this).data("name");
        Swal.fire({
            title: "Allow Cancellation?",
            text: `Allow the user to cancel their claim on "${name}"?`,
            icon: "question", showCancelButton: true,
            confirmButtonColor: "#1565C0", confirmButtonText: "Yes, Allow"
        }).then(r => {
            if (!r.isConfirmed) return;
            ajaxAction("/controllers/admin/claims.php", { action: "approve_cancel", claim_id: id }, successReload);
        });
    });

    // ── Open deny cancel modal ───────────────────────────────────
    $(".tt-reject-cancel").on("click", function () {
        $("#denyCancelId").val($(this).data("id"));
        $("#denyCancelName").text($(this).data("name"));
        $("#denyCancelReason").val("");
        new bootstrap.Modal(document.getElementById("denyCancelModal")).show();
    });

    $("#confirmDenyCancel").on("click", function () {
        const id = $("#denyCancelId").val(), reason = $("#denyCancelReason").val().trim();
        if (!reason) { Swal.fire({ title: "Required", text: "Reason is required.", icon: "warning", confirmButtonColor: "#1565C0" }); return; }
        bootstrap.Modal.getInstance(document.getElementById("denyCancelModal")).hide();
        ajaxAction("/controllers/admin/claims.php", { action: "reject_cancel", claim_id: id, reason }, successReload);
    });

});
