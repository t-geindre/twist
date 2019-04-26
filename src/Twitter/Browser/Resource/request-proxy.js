var twist = twist || {};

(function(window, undefined) {
    var requests = {};

    window.twist.sendRequest = function(uid, settings) {
        requests[uid] = {status: 'pending', data: null};

        // if (settings.data) {
        //     settings.body = new FormData();
        //     Object.keys(settings.data).forEach(
        //         key => settings.body.append(key, settings.data[key])
        //     );
        // }

        fetch(settings.url, settings)
            .then(response => {
                response.json().then(json => {
                    requests[uid] = {
                        status: response.ok ? 'success' : 'failed',
                        data: json,
                        statusText: response.statusText,
                        code: response.status
                    };
                });
            })
            .catch(() => requests[uid]['status'] = 'failed');
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
