function getAjaxById(url, modalName, callback) {
    $.ajax({
        type: "GET",
        url: url,
        success: function (response) {
            window.FlowbiteInstances.getInstance("Modal", modalName)?.show();
            callback(response);
        },
        error: function (res) {
            alert("error");
        },
    });
}
