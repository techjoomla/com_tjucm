$(function() {
    if (!window.navigator.userAgent.toLowerCase().match(/iphone|ipad|ipod|opera/)) {
        return
    }
    $('a').bind('click', function(evt) {
        var tjucm_href = $(evt.target).closest('a').attr('href');
        var tjucm_response = '';
        var tjucm_msg = '';
        if (tjucm_href !== undefined && !(tjucm_href.match(/^#/) || tjucm_href.trim() == '')) {
            tjucm_response = $(window).triggerHandler('beforeunload', tjucm_response);
            if (tjucm_response && tjucm_response != "") {
                tjucm_msg = tjucm_response + "\n\n" + "Press OK to leave this page or Cancel to stay.";
                if (!confirm(tjucm_msg)) {
                    return !1
                }
            }
            window.location.tjucm_href = tjucm_href;
            return !1
        }
        return !0
    })
})
