$(document).ready(function () {

    // ── Open cancel modal ────────────────────────────────────────
    $(".tt-cancel-btn").click(function () {
        const id   = $(this).data("id");
        const name = $(this).data("name");

        $("#cancelClaimId").val(id);
        $("#cancelItemName").text(name);

        new bootstrap.Modal(document.getElementById("cancelModal")).show();
    });

    // ── Submit cancel request ────────────────────────────────────
    $("#confirmCancel").click(function () {
        const id = $("#cancelClaimId").val();

        $.ajax({
            url: "/controllers/claims/cancel.php",
            type: "POST",
            data: {
                claim_id:   id,
                csrf_token: $("#csrf_token").val()
            },
            dataType: "json",

            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById("cancelModal")).hide();

                if (res.status) {
                    let timerInterval;
                    Swal.fire({
                        icon: "success",
                        title: "Submitted!",
                        html: res.message + " Closing in <b></b> ms.",
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
