!function(e) {
    e.fn.areYouSure = function(i) {
        var t = e.extend({
                message: "You have unsaved changes!",
                dirtyClass: "dirty",
                change: null,
                silent: !1,
                addRemoveFieldsMarksDirty: !1,
                fieldEvents: "change keyup propertychange input",
                fieldSelector: ":input:not(input[type=submit]):not(input[type=button])"
            }, i),
            n = function(i) {
                if (i.hasClass("ays-ignore") || i.hasClass("aysIgnore") || i.attr("data-ays-ignore") || void 0 === i.attr("name")) {
                   return null;
                }
                if (i.is(":disabled")) {
                   return "ays-disabled";
                }
                var t, n = i.attr("type");
                switch (i.is("select") && (n = "select"), n) {
                    case "checkbox":
                    case "radio":
                        t = i.is(":checked");
                        break;
                    case "select":
                        t = "", i.find("option").each(function() {
                            var i = e(this);
                            i.is(":selected") && (t += i.val())
                        });
                        break;
                    default:
                        t = i.val()
                }
                return t
            },
            a = function(e) {
                e.data("ays-orig", n(e))
            },
            r = function(i) {
                var a = function(e) {
                        var i = e.data("ays-orig");
                        return void 0 !== i && n(e) != i
                    },
                    r = e(this).is("form") ? e(this) : e(this).parents("form");
                if (a(e(i.target))) {
                    o(r, !0);
                }
                else {
                    var s = r.find(t.fieldSelector);
                    if (t.addRemoveFieldsMarksDirty)
                        if (r.data("ays-orig-field-count") != s.length) {
                             return void o(r, !0);
                        }
                    var d = !1;
                    s.each(function() {
                        var i = e(this);
                        if (a(i)) {
                            return d = !0, !1
                        }
                    }), o(r, d)
                }
            },
            s = function(i) {
                var n = i.find(t.fieldSelector);
                e(n).each(function() {
                    a(e(this))
                }), e(n).unbind(t.fieldEvents, r), e(n).bind(t.fieldEvents, r), i.data("ays-orig-field-count", e(n).length), o(i, !1)
            },
            o = function(e, i) {
                var n = i != e.hasClass(t.dirtyClass);
                e.toggleClass(t.dirtyClass, i), n && (t.change && t.change.call(e, e), i && e.trigger("dirty.areYouSure", [e]), i || e.trigger("clean.areYouSure", [e]), e.trigger("change.areYouSure", [e]))
            },
            d = function() {
                var i = e(this),
                    n = i.find(t.fieldSelector);
                e(n).each(function() {
                    var i = e(this);
                    i.data("ays-orig") || (a(i), i.bind(t.fieldEvents, r))
                }), i.trigger("checkform.areYouSure")
            },
            u = function() {
                s(e(this))
            };
        return t.silent || window.aysUnloadSet || (window.aysUnloadSet = !0, e(window).bind("beforeunload", function() {
            if (0 != e("form").filter("." + t.dirtyClass).length) {
                if (window.navigator.userAgent.toLowerCase().match(/msie|chrome/)) {
                    if (window.aysHasPrompted) {
                        return;
                    }
                    window.aysHasPrompted = !0, window.setTimeout(function() {
                        window.aysHasPrompted = !1
                    }, 900)
                }
                return t.message
            }
        })), this.each(function() {
            if (e(this).is("form")) {
                var i = e(this);
                i.submit(function() {
                    i.removeClass(t.dirtyClass)
                }), i.bind("reset", function() {
                    o(i, !1)
                }), i.bind("rescan.areYouSure", d), i.bind("reinitialize.areYouSure", u), i.bind("checkform.areYouSure", r), s(i)
            }
        })
    }
}(jQuery);
