$(function() {
  if (!window.navigator.userAgent.toLowerCase().match(/iphone|ipad|ipod|opera/)) {
    return;
  }
  $('a').bind('click', function(evt) {
    let href = $(evt.target).closest('a').attr('href');
    let response = '';
    let msg = '';
    if (href !== undefined && !(href.match(/^#/) || href.trim() == '')) {
      response = $(window).triggerHandler('beforeunload', response);
      if (response && response != "") {
        msg = response + "\n\n"
          + "Press OK to leave this page or Cancel to stay.";
        if (!confirm(msg)) {
          return false;
        }
      }
      window.location.href = href;
      return false;
     }
	return true;
  });
});
