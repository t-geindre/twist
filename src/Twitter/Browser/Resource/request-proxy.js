var twist = twist || {};

(function(window, undefined) {
    var requests = {};

    window.twist.sendRequest = function(uid, settings) {
        requests[uid] = {status: 'pending', data: null};

        window.jQuery.ajax(settings).then(
            (data, status) => {
                requests[uid] = {data, status};
            },
            (jqXHR, textStatus) => {
                requests[uid] = {
                    status: 'failed',
                    error: textStatus,
                    data: jqXHR.responseText,
                    code: jqXHR.status
                }
            }
        );
    };

    window.twist.getRequestResult = function(uid) {
        let result = requests[uid];

        if (result !== undefined) {
            if (result.status !== 'pending') {
                delete requests[uid];
            }

            return result;
        }

        throw 'Unknown request uid '.uid;
    };
})(window);
