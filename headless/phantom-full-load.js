// This example shows how to render pages that perform AJAX calls
// upon page load.
//
// Instead of waiting a fixed amount of time before doing the render,
// we are keeping track of every resource that is loaded.
//
// Once all resources are loaded, we wait a small amount of time
// (resourceWait) in case these resources load other resources.
//
// The page is rendered after a maximum amount of time (maxRenderTime)
// or if no new resources are loaded.
// from https://gist.github.com/cjoudrey/1341747

var jquery = './vendor/jquery.min.js';
var maxRenderWait = 30000;
var resourceTimeout = 5000;

module.exports = function (phantom, url, onOk, onError) {
    var page = require('webpage').create(), forcedRenderTimeout;

    page.viewportSize = {width: 1280, height: 1024};
    page.settings.resourceTimeout = resourceTimeout;

    var consoleLogs = [];

    function onLoad() {
        clearTimeout(forcedRenderTimeout);

        onOk(page, consoleLogs);

        phantom.exit();
    }

    page.onConsoleMessage = function (msg) {
        consoleLogs.push(JSON.stringify(msg));
    };

    page.onCallback = function (data) {
        onLoad();
    };

    page.onInitialized = function () {
        page.injectJs(jquery) || (console.log("Unable to inject jQuery") && phantom.exit());

        page.evaluate(function () {
            $(function () {
                window.callPhantom();
            });
        });

        page.evaluate(function () {
            var isFunction = function (o) {
                return typeof o == 'function';
            };

            var bind,
                slice = [].slice,
                proto = Function.prototype,
                featureMap;

            featureMap = {
                'function-bind': 'bind'
            };

            function has(feature) {
                var prop = featureMap[feature];
                return isFunction(proto[prop]);
            }

            // check for missing features
            if (!has('function-bind')) {
                // adapted from Mozilla Developer Network example at
                // https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/Function/bind
                bind = function bind(obj) {
                    var args = slice.call(arguments, 1),
                        self = this,
                        nop = function () {
                        },
                        bound = function () {
                            return self.apply(this instanceof nop ? this : (obj || {}), args.concat(slice.call(arguments)));
                        };
                    nop.prototype = this.prototype || {}; // Firefox cries sometimes if prototype is undefined
                    bound.prototype = new nop();
                    return bound;
                };
                proto.bind = bind;
            }
        });
    };

    phantom.onError = page.onError = function (err) {
        consoleLogs.push(err);
    };

    page.open(url, function (status) {
        if (status !== "success") {
            onError(new Error('Unable to load url'));
        } else {
            forcedRenderTimeout = setTimeout(onLoad, maxRenderWait);
        }
    });
};