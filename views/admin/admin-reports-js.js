$(document).ready(function () {

    // ── Approve report ───────────────────────────────────────────
    $(".tt-approve-report").on("click", function () {
        const id   = $(this).data("id");
        const name = $(this).data("name");

        Swal.fire({
            title: "Approve Report?",
            text: `Approve "${name}"? It will become publicly visible.`,
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#1565C0",
            confirmButtonText: "Yes, Approve"
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: "/controllers/admin/items.php",
                type: "POST",
                data: { action: "approve", item_id: id, csrf_token: $("#csrf_token").val() },
                dataType: "json",
                success: function (res) {
                    if (res.status) {
                        let t;
                        Swal.fire({
                            icon: "success", title: "Approved!", html: res.message + " Closing in <b></b>ms.",
                            timer: 2000, timerProgressBar: true,
                            didOpen: () => { Swal.showLoading(); t = setInterval(() => Swal.getPopup().querySelector("b").textContent = Swal.getTimerLeft(), 100); },
                            willClose: () => clearInterval(t)
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({ title: "Error!", text: res.message, icon: "error", confirmButtonColor: "#1565C0" });
                    }
                }
            });
        });
    });

    // ── Open reject modal ────────────────────────────────────────
    $(".tt-reject-report").on("click", function () {
        $("#rejectReportId").val($(this).data("id"));
        $("#rejectReportName").text($(this).data("name"));
        $("#rejectReportReason").val("");
        new bootstrap.Modal(document.getElementById("rejectReportModal")).show();
    });

    // ── Confirm reject ───────────────────────────────────────────
    $("#confirmRejectReport").on("click", function () {
        const id     = $("#rejectReportId").val();
        const reason = $("#rejectReportReason").val().trim();
        if (!reason) {
            Swal.fire({ title: "Required", text: "Please state a reason.", icon: "warning", confirmButtonColor: "#1565C0" });
            return;
        }
        $.ajax({
            url: "/controllers/admin/items.php",
            type: "POST",
            data: { action: "reject", item_id: id, reason, csrf_token: $("#csrf_token").val() },
            dataType: "json",
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById("rejectReportModal")).hide();
                if (res.status) {
                    let t;
                    Swal.fire({
                        icon: "success", title: "Rejected!", html: res.message + " Closing in <b></b>ms.",
                        timer: 2000, timerProgressBar: true,
                        didOpen: () => { Swal.showLoading(); t = setInterval(() => Swal.getPopup().querySelector("b").textContent = Swal.getTimerLeft(), 100); },
                        willClose: () => clearInterval(t)
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire({ title: "Error!", text: res.message, icon: "error", confirmButtonColor: "#1565C0" });
                }
            }
        });
    });

});
