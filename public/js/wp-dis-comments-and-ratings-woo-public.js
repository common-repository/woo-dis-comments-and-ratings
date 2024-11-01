// jQuery(document).ready(function ($) {
// 	var disqusPublicKey = "9H1DVUINOSWttvMlwf8sfJsGYRvo3clyb3Jk34zRTXvoFd9BjSzncBwlEaC4N16D";
// 	var disqusShortname = "http-disqus-loc";
// 	var urlArray = [];

// 	$.ajax({
// 		type: 'GET',
// 		url: "https://disqus.com/api/3.0/threads/details.json", // API endpoint
// 		data: {
// 			api_key: disqusPublicKey,
// 			forum: disqusShortname,
// 			thread: 'link:http://disqus.loc/product/test-product-1/', // get thread by identifier
// 		},
// 		cache: false,
// 		dataType: 'jsonp', // for cross-domain requests
// 		success: function (result) {
// 			console

// 			// let's update the link somewhere on the page
// 			$('.disqus-comments-link')
// 				.attr('href', 'https://disqus.com/home/discussion/%YOUR_FORUM_NAME%/' + slug + '/');
// 		}
// 	});
// });