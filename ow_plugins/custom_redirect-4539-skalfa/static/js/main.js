function isAuthenticated(url) {

    $('input[name=search]').click(function (e) {
        e.preventDefault();
        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                var data = JSON.parse(response);

                if (data) {
                    window.location.href = "" + data + "";
                }
            }
        });
    });

    $('span.ow_qs_label a').click(function (e) {
        e.preventDefault();
        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                var data = JSON.parse(response);

                if (data) {
                    window.location.href = "" + data + "";
                }
            }
        });
    });
}