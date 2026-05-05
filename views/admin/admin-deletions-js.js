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

    // ── Approve deletion ─────────────────────────────────────────
    $(".tt-approve-deletion").on("click", function () {
        const id = $(this).data("id"), name = $(this).data("name");
        Swal.fire({
            title: "Approve Deletion?",
            text: `Delete "${name}" permanently? This cannot be undone.`,
            icon: "warning", showCancelButton: true,
            confirmButtonColor: "#C62828", confirmButtonText: "Yes, Delete"
        }).then(r => {
            if (!r.isConfirmed) return;
            ajaxAction("/controllers/admin/deletions.php", { action: "approve", deletion_id: id }, successReload);
        });
    });

    // ── Open deny modal ──────────────────────────────────────────
    $(".tt-reject-deletion").on("click", function () {
        $("#denyDeletionId").val($(this).data("id"));
        $("#denyDeletionName").text($(this).data("name"));
        $("#denyDeletionReason").val("");
        new bootstrap.Modal(document.getElementById("denyDeletionModal")).show();
    });

    $("#confirmDenyDeletion").on("click", function () {
        const id = $("#denyDeletionId").val(), reason = $("#denyDeletionReason").val().trim();
        if (!reason) {
            Swal.fire({ title: "Required", text: "Reason is required.", icon: "warning", confirmButtonColor: "#1565C0" });
            return;
        }
        bootstrap.Modal.getInstance(document.getElementById("denyDeletionModal")).hide();
        ajaxAction("/controllers/admin/deletions.php", { action: "reject", deletion_id: id, reason }, successReload);
    });

});
