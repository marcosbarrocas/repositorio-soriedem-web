function AjaxClass() {
    this.get = function (url, handleData) {
        return $.ajax({
            url: url,
            dataType: "JSON",
            type: "GET",
            success: function (response) {
                handleData(response);
            },
            error: function (xhr, resp, text) {
                handleData(text);
            }
        });
    }

    this.post = function (url, formData, handleData) {
        $.ajax({
            url: url,
            dataType: "JSON",
            type: "POST",
            data: formData,
            success: function (response) {
                handleData(response);
            },
            error: function (xhr, resp, text) {
                handleData(text);
            }
        });
    }
}