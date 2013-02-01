(function () {
    var location_hash = function (v) {
        var ret = location.href.split("#")[1] || "";
        if (v != undefined) {
            location.hash = v;
        }
        return ret;
    };

    var auth = window.demo_auth = function (api, success) {
        var player = location_hash();
        if (!player) {
            player = window.prompt("Enter your login and password");
        }
        api.request("Auth", {"player": player}, function (rsp) {
            if (rsp['result'] != "success") {
                if (location_hash()) {
                    location_hash("");
                } else {
                    alert("Our server don't like the data you provided. Please try again");
                }
                return auth();
            }
            location_hash(player);
            var house_id = rsp['data']['house_id'];
            return success(house_id);
        });
    };
})();