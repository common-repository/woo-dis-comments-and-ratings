=== WooCommerce Disqus Comments and Ratings ===

Author: Zachary A. Martz
Contributors: zamartz
Donate link: http://bt.zamartz.com/woodisext
Tags: woocommerce, disqus, comments, ratings, zamartz
Requires at least: 5.0.0
Tested up to: 6.5.5
Requires WooCommerce at least: 5.0.0
Tested WooCommerce up to: 9.0.0
Stable Tag: trunk
Requires PHP: 7.0
License: GPLv3
WooCommerce Disqus Comments and Ratings gives you better control over palcement

== Description ==

Rebuilt from the ground up, the WooCommerce Discus Comments and Ratings is packed with even more features.

The plugin adds administrative functionality to the WooCommerce settings allowing you to target where the comment thread and comment counts can be displayed.

This allows you to show how customers are conversing over your products. It is great for digital downloads and a community forum for self-service support and suggested enhancements.

This adds additional functionality and support to the legacy plugin WooCommerce Disqus. Users of the legacy plugin that have a paid version of this extension will have a one click option to import their previous rules

**Place the Disqus Comment Thread on Product page:**

* Free = Corrects Native Disqus plugin from loading in a way that causes errors across PLP & PDP
* ree = Add a new tab to PDP for Disqus Comment Thread to show
* Paid = Replace Comments tab with Disqus Comment Thread
* Paid = Add Disqus Comment Thread under the product tabs
* Paid = Add Disqus Comment Thread under product summary
* Paid = Custom Target where to insert Disqus Comment Thread on page
* Paid = Import legacy plugin settings for easy activation

**Show Comment count on PLP and PDP:**

* Paid = Add Discus Comment Count on the PLP with default placements
* Paid = Custom target  Discus Comment Count on the PLP
* Paid = Add Discus Comment Count on the PDP with default placements
* Paid = Custom target  Discus Comment Count on the PDP

**Adds Network Support:**

* Paid = Manage all of your sites main activation of core features from the network admin
* Paid = Quick links from the network admin to each sites detail setting pages

**Multiple Ways to Identify your post IDs:**

* Wordpress Post ID (default - free)
* Product Parent SKU (paid)
* Variant SKU (paid)
* Product Wordpress Slug (paid)

This allows you to target how comments are rolled up and displayed from Disqus.

== Installation ==

* Maual Upload to Server - the entire ‘woocommerce-disqus-comments-and-ratings’ folder to the '/wp-content/plugins/' directory 
* Manual Upload throug Worpdress - Install by droping .zip throug Plugins -> Add New -> Upload Plugin -> Select File or
* Auto through Wordpress Plugin Directory

== Activation ==

1. Install and Activate Plugin through the 'Plugins' menu in WordPress
1. Goto Settings in  YourSiteDomain/wp-admin/admin.php?page=wc-settings&tab=products&section=disqus_comments_and_ratings
1. Free - Use Select Option and Save
1. Advanced - Add API Cridentials and Save
1. Advanced - Activate API
1. Advanced - Choose Setting for Both Reviews and Comments and Save

== Frequently Asked Questions ==

= I am not seeing how to activate my API Key in Settings =

Make sure that you have all 3 required fields filled out and that you have save the options.

= How do I find my API Key and/or Disable and/or Create a New Key? =

Login to your purchase at zamartz.com and goto the account settings.

= What if I save over my settings and cannot use the API key? =

Uninstall the plugin and Reinstall it. Then login to your purchase at zamartz.com and goto the account settings to Disable the old API Key and Create a New one.

= I am not seeing the comment tabs on my product page =

Make sure that a "Reviews" tab has been added to your WooCommerce Theme Template and that Comments for the specific product are not turned off. Option for product specific products are in product -> options -> advnaced

== Screenshots ==

1. Bumber screenshot- for WooCommerce Disqus Comments and Ratings `/assets/screenshot-1.png`
2. Admin WooCommerce - Shows all Active Paid & Free features for Product Deatil pages and Product List page comment count displays, includeing comment thread `/assets/screenshot-2.png`
3. Admin ZAMARTZ - When API is active field for Option for Both Disqus Comments and Ratings Shown `/assets/screenshot-3.png`
4. Front End - Comment count custom inserted on the PLP Product List Page `/assets/screenshot-4.png`
5. Front End - Comment count custom inserted on the PDP Product Detail Page `/assets/screenshot-5.png`
6. Product Page - Fix of Standard Ratings when Disqus is Active `/assets/screenshot-6.png`
7. Product Page - Fix of Disqus Ratings when Disqus is Active `/assets/screenshot-7.png`
8. Product Page - Option for both Ratings and Disqus Comments Active `/assets/screenshot-8.png`
9. Product Page - Network Admin view of all your ZAMARTZ extensions to easily manage `/assets/screenshot-9.png`
10. Product Page - Network Admin highlevel areas to mangage from one location `/assets/screenshot-10.png`

== Changelog ==

= 1.0 =

* Original Upload

= 1.0.1 =

* Dubug Erorr with URL query Fixed

= 1.0.2 =

* Upload Error Fix

= 1.0.3 =

* Upload Error Fix

= 1.0.4 =

* Upload Error Fix Working

= 1.0.5 =

* Compatabilty upgrade with new disqus js

= 1.0.6 =

* File Versioning Updates

= 1.0.7 =

* Plugin URL Correction

= 1.1.0 =

* Update to HTTP call to HTTPS when needed

= 1.1.1 =

* Update for free version hide toggel

= 1.1.2 =

* Removed douple call on post pages of discus primary JS

= 1.2.0 =

* updated insertion to stop Disqus native enque and detect comments via WP_Comment

= 2.0.0 =

* Major upgrades the the plugin that will allow it to be stand alone or integrate with maind disqus plugin, including event more paid features like comment counts on the Product List Pages and Product Detail Pages

= 2.0.1 =

* Bugix for set_plugin_api_data() error

= 2.0.2 =

* Design and Opt-in Updates

= 2.0.3 =

* fix, Notice: Undefined property: Woo_Disqus_Comments_Front

* improvement, Hide accordian tab "Product Detail Pages"

= 2.0.4 =

* core ZAMARTZ Update

* fixed bugs with indivdial pdp disablement

* fixed bug with custome placement on pdp

= 2.0.5 =

* core ZAMARTZ Update for API

* Tested for wordpress latest 6.0.x

* Tested for woocommerce lateste 6.x.x

== Upgrade Notice ==

Please upgrade for new features and improved functionality

== Buy Updgrade ==

Purchase the Advanced option to allow both Commenting and Reviews on the Same Product = [WooCommerce Disqus Comments and Ratings](https://zamartz.com/product/woocommerce-disqus-comments-and-ratings)