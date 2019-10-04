'use strict';
/** global: com_tjucm */
com_tjucm.Services.Base = Class.extend({
    /**
     * @param   string  url       API Request URL
     * @param   config  object    Configuration object
     * @param   cb      function  Callback function
     */
    get: function (url, config, cb) {
        config = config || {};
        config.headers = config.headers || {};
        if (typeof cb !== 'function') {
            throw "base expects callback to be function";
        }

        return jQuery.ajax({
            type: "GET",
            url: url,
            headers: config.headers,
            beforeSend: function () {
            },
            success: function (res) {
                cb(null, res);
            },
            error: function (err) {
                cb(err, null);
            }
        });
    },
    /**
     * @param   string  url       API Request URL
     * @param   data    object    Data to post
     * @param   config  object    Configuration object which have headers
     * @param   cb      function  Callback function
     */
    post: function (url, data, config, cb) {
        data = data || {};
        config = config || {};
        config.headers = config.headers || {};

        if (typeof cb !== 'function') {
            throw "base expects callback to be function";
        }

        config.contentType = typeof config.contentType != "undefined" ? config.contentType : 'application/x-www-form-urlencoded; charset=UTF-8';
        config.processData = typeof config.processData != "undefined" ? config.processData : true;

        return jQuery.ajax({
            type: "POST",
            url: url,
            data: data,
            contentType: config.contentType,
            processData: config.processData,
            headers: config.headers,
            beforeSend: function () {
            },
            success: function (res) {
                cb(null, res);
            },
            error: function (err) {
                cb(err, null);
            }
        });
    },
    /**
     * @param   string  url       API Request URL
     * @param   config  object    Configuration object which have headers
     * @param   cb      function  Callback function
     */
    patch: function (url, data, config, cb) {
        data = data || {};
        config = config || {};
        config.headers = config.headers || {};

        if (typeof cb !== 'function') {
            throw "base expects callback to be function";
        }

        if (typeof data === 'object') {
            data = JSON.stringify(data);
        }
        return jQuery.ajax({
            type: "PATCH",
            url: url,
            data: data,
            headers: config.headers,
            beforeSend: function () {
            },
            success: function (res) {
                cb(null, res);
            },
            error: function (xhr) {
                cb(xhr, null);
            }
        });
    }
});