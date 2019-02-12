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
    console.log(settings);

    twitterContestRequestStates[uid] = {
        status: 'pending'
    };

    settings['success'] = (function(uid) {
        return function (data, status) {
            console.log('REQUEST DONE');
            console.log(uid);
            twitterContestRequestStates[uid] = {
                status,
                data
            };
        };
    })(uid);

    jQuery.ajax(settings);

    console.log('SENT');
}
