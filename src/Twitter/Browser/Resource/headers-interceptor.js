var twitterContestHeadersLogs = null;

(function() {
    var
        proxiedXhrOpen = XMLHttpRequest.prototype.open,
        proxiedXhrSetRequestHeader = XMLHttpRequest.prototype.setRequestHeader,
        proxiedXhrSend = XMLHttpRequest.prototype.send,
        headersLog = false,
        twitterApiUrl = 'https://api.twitter.com'
    ;

    XMLHttpRequest.prototype.open = function(method, url) {
        if (url.substr(0, twitterApiUrl.length) === twitterApiUrl) {
            headersLog = {};
        }

        return proxiedXhrOpen.apply(this, arguments);
    };

    XMLHttpRequest.prototype.setRequestHeader = function(header, value) {
        if (headersLog) {
            headersLog[header] = value;
        }

        return proxiedXhrSetRequestHeader.apply(this, arguments);
    };

    XMLHttpRequest.prototype.send = function() {
        if (headersLog) {
            XMLHttpRequest.prototype.open = proxiedXhrOpen;
            XMLHttpRequest.prototype.send = proxiedXhrSend;
            XMLHttpRequest.prototype.setRequestHeader = proxiedXhrSetRequestHeader;
            twitterContestHeadersLogs = headersLog;
            headersLog = false;
        }

        return proxiedXhrSend.apply(this, arguments);
    };
})();
