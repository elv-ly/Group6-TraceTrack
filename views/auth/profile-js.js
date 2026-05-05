$(document).ready(function () {

    // ── Save profile ─────────────────────────────────────────────
    $("#saveProfile").on("click", function () {
        const full_name = $("#profileName").val().trim();
        const contact   = $("#profileContact").val().trim();

        if (!full_name || !contact) {
            Swal.fire({ title: "Required", text: "Name and contact are required.", icon: "warning", confirmButtonColor: "#1565C0" });
            return;
        }

        $.ajax({
            url: "/controllers/auth/profile.php",
            type: "POST",
            data: { action: "update_profile", full_name, contact, csrf_token: $("#csrf_token").val() },
            dataType: "json",
            success: function (res) {
                if (res.status) {
                    Swal.fire({ title: "Saved!", text: res.message, icon: "success", confirmButtonColor: "#1565C0" })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire({ title: "Error!", text: res.message, icon: "error", confirmButtonColor: "#1565C0" });
                }
            }
        });
    });

    // ── Change password ──────────────────────────────────────────
    $("#savePassword").on("click", function () {
        const current = $("#currentPass").val();
        const newPass = $("#newPass").val();
        const confirm = $("#confirmPass").val();

        if (!current || !newPass || !confirm) {
            Swal.fire({ title: "Required", text: "All password fields are required.", icon: "warning", confirmButtonColor: "#1565C0" });
            return;
        }

        if (newPass !== confirm) {
            Swal.fire({ title: "Mismatch", text: "New passwords do not match.", icon: "error", confirmButtonColor: "#1565C0" });
            return;
        }

        $.ajax({
            url: "/controllers/auth/profile.php",
            type: "POST",
            data: {
                action:           "change_password",
                current_password: current,
                new_password:     newPass,
                confirm_password: confirm,
                csrf_token:       $("#csrf_token").val()
            },
            dataType: "json",
            success: function (res) {
                if (res.status) {
                    $("#currentPass, #newPass, #confirmPass").val("");
                    Swal.fire({ title: "Done!", text: res.message, icon: "success", confirmButtonColor: "#1565C0" });
                } else {
                    Swal.fire({ title: "Error!", text: res.message, icon: "error", confirmButtonColor: "#1565C0" });
                }
            }
        });
    });

    // ── Toggle password visibility ───────────────────────────────
    window.togglePass = function (id, btn) {
        const input = document.getElementById(id);
        const icon  = btn.querySelector("i");
        if (input.type === "password") {
            input.type = "text";
            icon.className = "bi bi-eye-slash";
        } else {
            input.type = "password";
            icon.className = "bi bi-eye";
        }
    };

});
