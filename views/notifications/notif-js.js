$(document).ready(function () {

    // ── Click notification → mark read → redirect ────────────────
    $(".tt-notif-item").on("click", function () {
        const id     = $(this).data("id");
        const isRead = $(this).data("read");
        const link   = $(this).data("link");
        const self   = $(this);

        if (!isRead) {
            $.ajax({
                url: "/controllers/notifications/mark.php",
                type: "POST",
                data: {
                    action:          "mark_one",
                    notification_id: id,
                    csrf_token:      $("#csrf_token").val()
                },
                dataType: "json",
                success: function () {
                    self.removeClass("tt-notif-unread");
                    self.find(".tt-notif-dot").remove();
                    self.data("read", 1);
                    if (link) window.location.href = link;
                }
            });
        } else {
            if (link) window.location.href = link;
        }
    });

    // ── Mark all as read ─────────────────────────────────────────
    $("#markAllRead").on("click", function () {
        $.ajax({
            url: "/controllers/notifications/mark.php",
            type: "POST",
            data: {
                action:     "mark_all",
                csrf_token: $("#csrf_token").val()
            },
            dataType: "json",
            success: function (res) {
                if (res.status) {
                    $(".tt-notif-unread").removeClass("tt-notif-unread");
                    $(".tt-notif-dot").remove();
                    $("#markAllRead").hide();
                    $(".tt-badge-count").text("0 unread");
                }
            }
        });
    });

});
