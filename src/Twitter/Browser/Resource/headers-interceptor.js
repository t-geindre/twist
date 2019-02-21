var twist = twist || {};

(function(window) {
    var
        proxied = {},
        headersLog = false,
        twitterApiUrl = 'https://api.twitter.com',
        intercepted = false,
        xhrPrototype = window.XMLHttpRequest.prototype
    ;

    ['open', 'setRequestHeader', 'send'].forEach(
        method => proxied[method] = xhrPrototype[method]
    );

    xhrPrototype.open = function(method, url) {
        if (!intercepted && url.substr(0, twitterApiUrl.length) === twitterApiUrl) {
            headersLog = {};
        }

        return proxied.open.apply(this, arguments);
    };

    xhrPrototype.setRequestHeader = function(header, value) {
        if (!intercepted && headersLog) {
            headersLog[header] = value;
        }

        return proxied.setRequestHeader.apply(this, arguments);
    };

    xhrPrototype.send = function() {
        if (!intercepted && headersLog) {
            Object.keys(proxied).forEach(method => xhrPrototype[method] = proxied[method]);
            intercepted = true;
        }

        return proxied.send.apply(this, arguments);
    };

    twist.getInterceptedHeaders = () => {
        if (!intercepted) {
            return false;
        }

        return headersLog;
    }
})(window);
