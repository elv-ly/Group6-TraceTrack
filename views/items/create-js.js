$(document).ready(function () {

    // ── File drop / preview ──────────────────────────────────────
    const drop     = document.getElementById("fileDrop");
    const input    = document.getElementById("photoInput");
    const prompt   = document.getElementById("filePrompt");
    const preview  = document.getElementById("filePreview");
    const img      = document.getElementById("previewImg");
    const fileName = document.getElementById("fileName");

    if (drop) {
        drop.addEventListener("click", () => input.click());

        drop.addEventListener("dragover", function (e) {
            e.preventDefault();
            drop.style.borderColor = "var(--blue)";
        });

        drop.addEventListener("dragleave", function () {
            drop.style.borderColor = "";
        });

        drop.addEventListener("drop", function (e) {
            e.preventDefault();
            drop.style.borderColor = "";
            if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
        });

        input.addEventListener("change", function () {
            if (input.files[0]) handleFile(input.files[0]);
        });
    }

    function handleFile(file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            img.src        = e.target.result;
            fileName.textContent = file.name + " (" + (file.size / 1024).toFixed(1) + " KB)";
            prompt.style.display  = "none";
            preview.style.display = "block";
        };
        reader.readAsDataURL(file);
    }

    // ── Date validation on submit ────────────────────────────────
    $("#itemForm").on("submit", function (e) {
        const dateVal = $("[name='date_occured']").val();
        if (new Date(dateVal) > new Date()) {
            e.preventDefault();
            Swal.fire({
                title: "Error!",
                text: "Date cannot be in the future.",
                icon: "error",
                confirmButtonColor: "#1565C0"
            });
        }
    });

});
