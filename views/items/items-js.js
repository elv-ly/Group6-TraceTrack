$(document).ready(function () {

    // ── Open deletion request modal ──────────────────────────────
    $(".tt-delete-btn").click(function () {
        const id   = $(this).data("id");
        const name = $(this).data("name");

        $("#deletionItemId").val(id);
        $("#deletionItemName").text(name);
        $("#deletionReason").val("");

        new bootstrap.Modal(document.getElementById("deletionModal")).show();
    });

    // ── Submit deletion request ──────────────────────────────────
    $("#submitDeletion").click(function () {
        const id     = $("#deletionItemId").val();
        const reason = $("#deletionReason").val().trim();

        if (!reason) {
            Swal.fire({
                title: "Required",
                text: "Please provide a reason for deletion.",
                icon: "warning",
                confirmButtonColor: "#1565C0"
            });
            return;
        }

        $.ajax({
            url: "/controllers/items/request_deletion.php",
            type: "POST",
            data: {
                item_id:    id,
                reason:     reason,
                csrf_token: $("#csrf_token").val()
            },
            dataType: "json",

            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById("deletionModal")).hide();

                if (res.status) {
                    let timerInterval;
                    Swal.fire({
                        icon: "success",
                        title: "Submitted!",
                        html: res.message + " This will close in <b></b> ms.",
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: () => {
                            Swal.showLoading();
                            const timer = Swal.getPopup().querySelector("b");
                            timerInterval = setInterval(() => {
                                timer.textContent = `${Swal.getTimerLeft()}`;
                            }, 100);
                        },
                        willClose: () => clearInterval(timerInterval)
                    }).then(() => window.location.reload());

                } else {
                    Swal.fire({
                        title: "Error!",
                        text: res.message,
                        icon: "error",
                        confirmButtonColor: "#1565C0"
                    });
                }
            },

            error: function (xhr) {
                Swal.fire({
                    title: "Error!",
                    text: xhr.responseText || "Something went wrong.",
                    icon: "error",
                    confirmButtonColor: "#1565C0"
                });
            }
        });
    });

});
