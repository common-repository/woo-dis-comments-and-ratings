<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://zamartz.com
 * @since      1.0.0
 *
 * @package    Wp_Woo_Dis_Comments_And_Ratings
 * @subpackage Wp_Woo_Dis_Comments_And_Ratings/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wp_Woo_Dis_Comments_And_Ratings
 * @subpackage Wp_Woo_Dis_Comments_And_Ratings/includes
 * @author     Zachary Martz <zam@zamartz.com>
 */
class Wp_Woo_Dis_Comments_And_Ratings_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wp-dis-comments-and-ratings-woo',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
