var twitterContestRequestStates = {};

function twitterContestGetRequestResult(uid) {
    let result = twitterContestRequestStates[uid];

    if (result.status !== 'pending') {
        delete twitterContestRequestStates[uid];
    }

    return result;
}

function twitterContestRequest(uid, settings)
{
    twitterContestRequestStates[uid] = {
        status: 'pending'
    };

    settings['success'] = (function(uid) {
        return function (data, status) {
            twitterContestRequestStates[uid] = {
                status,
                data
            };
        };
    })(uid);

    jQuery.ajax(settings);
}
