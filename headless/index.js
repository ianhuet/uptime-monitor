var system = require("system");
var url = system.args[1];

require('./phantom-full-load')(phantom, url, function (page, logs) {
    logs.forEach(function (i) {
        console.log('> ' + i);
    });

    result = page.evaluate(function () {
        return $('body *').attr('class');
    });

    console.log(result);
}, function (error) {
    console.log(error);
});