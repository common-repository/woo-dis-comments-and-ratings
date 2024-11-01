<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://zamartz.com
 * @since             1.0.0
 * @package           Wp_Woo_Dis_Comments_And_Ratings
 *
 * @wordpress-plugin
 * Plugin Name:       Disqus Comments and Ratings for WooCommerce
 * Plugin URI:        https://zamartz.com/product/
 * Description:       Allows the user to choose if they want WooCommerce Disqus Comments to override ratings with the Disqus comments or disable Disqus on product pages.
 * Version:           2.0.5
 * Author:            Zachary Martz
 * Author URI:        https://zamartz.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-dis-comments-and-ratings-woo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 */
define('WP_WOO_DIS_COMMENTS_AND_RATINGS_VERSION', '2.0.5');

/**
 * Current plugin directory slug
 */
define('WP_WOO_DIS_COMMENTS_AND_RATINGS_DIR_SLUG', plugin_basename(dirname(__FILE__)));

/**
 * Current plugin file path with directory slug
 */
define('WP_WOO_DIS_COMMENTS_AND_RATINGS_DIR_FILE_SLUG', plugin_basename(__FILE__));

if (!isset($zamartz_admin_version)){
	$zamartz_admin_version = array();
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-dis-comments-and-ratings-woo-activator.php
 */
function activate_wp_woo_dis_comments_and_ratings()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-wp-dis-comments-and-ratings-woo-activator.php';
	Wp_Woo_Dis_Comments_And_Ratings_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-dis-comments-and-ratings-woo-deactivator.php
 */
function deactivate_wp_woo_dis_comments_and_ratings()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-wp-dis-comments-and-ratings-woo-deactivator.php';
	Wp_Woo_Dis_Comments_And_Ratings_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wp_woo_dis_comments_and_ratings');
register_deactivation_hook(__FILE__, 'deactivate_wp_woo_dis_comments_and_ratings');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-wp-dis-comments-and-ratings-woo.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_wp_woo_dis_comments_and_ratings()
{

	$plugin = new Wp_Woo_Dis_Comments_And_Ratings();
	$plugin->run();
}
run_wp_woo_dis_comments_and_ratings();
