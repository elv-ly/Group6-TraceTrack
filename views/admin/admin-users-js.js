$(document).ready(function () {

    function ajaxAction(data, callback) {
        data.csrf_token = $("#csrf_token").val();
        $.ajax({
            url: "/controllers/admin/users.php",
            type: "POST", data, dataType: "json",
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

    // ── Deactivate user ──────────────────────────────────────────
    $(".tt-deactivate-user").on("click", function () {
        const id = $(this).data("id"), name = $(this).data("name");
        Swal.fire({
            title: "Deactivate Account?",
            text: `Deactivate "${name}"? They will no longer be able to log in.`,
            icon: "warning", showCancelButton: true,
            confirmButtonColor: "#C62828", confirmButtonText: "Yes, Deactivate"
        }).then(r => {
            if (!r.isConfirmed) return;
            ajaxAction({ action: "deactivate", user_id: id }, successReload);
        });
    });

    // ── Activate user ────────────────────────────────────────────
    $(".tt-activate-user").on("click", function () {
        const id = $(this).data("id"), name = $(this).data("name");
        Swal.fire({
            title: "Activate Account?",
            text: `Re-activate "${name}"?`,
            icon: "question", showCancelButton: true,
            confirmButtonColor: "#1565C0", confirmButtonText: "Yes, Activate"
        }).then(r => {
            if (!r.isConfirmed) return;
            ajaxAction({ action: "activate", user_id: id }, successReload);
        });
    });

});
