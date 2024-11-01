
var disqus_config = function () {
    this.page.url = woo_disqus_config_settings.url;
    this.page.identifier = woo_disqus_config_settings.identifier;
    this.page.title = woo_disqus_config_settings.title;
    this.callbacks.onNewComment = [function () {
        DISQUSWIDGETS.getCount({ reset: true });
    }];
};
jQuery(document).ready(function ($) {
    if (jQuery('#disqus_thread').length === 0 && woo_disqus_config_settings.custom_detail_placement != false) {
        jQuery(woo_disqus_config_settings.custom_detail_placement).html('<div id="disqus_thread"></div>');
        jQuery(woo_disqus_config_settings.custom_detail_placement).html(jQuery(woo_disqus_config_settings.custom_detail_placement).html() + '<div id="disqus_thread"></div>');
    }

    (function () {
        var d = document, s = d.createElement('script');

        s.src = '//' + woo_disqus_config_settings.shortname + '.disqus.com/embed.js';

        (d.head || d.body).appendChild(s);
    })();
});
