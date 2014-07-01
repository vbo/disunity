(function ($) {
    var API = window.API = function (impl_url) {
        this.url = impl_url;
    };

    API.prototype.request = function (action, data, callback) {
        data['action'] = action;
        $.ajax({
            url: this.url,
            data: data,
            dataType: "json",
            success: callback,
            error: function (e_data) {
                console.log('api request error', action, data, e_data);
            }
        });
    }
})(jQuery);