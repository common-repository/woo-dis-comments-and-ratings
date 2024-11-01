<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://zamartz.com
 * @since      1.0.0
 *
 * @package    Wp_Woo_Dis_Comments_And_Ratings
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

$zamartz_admin_event_tracker = get_option('wp_zamartz_admin_event_tracker');
$plugin_input_prefix = 'woo_disqus_';	//Initialize input prefix

$option_list = array(
	//Api credentials
	'api_license_key',
	'api_password',
	'api_product_id',
	'api_purchase_emails',
	'api_get_response',
	'zamartz_api_admin_notice_data',
	//Product detail comments
	'show_in_detail',
	'detail_placement',
	'custom_insert_indentifier_detail_1',
	//Product detail comment count
	'show_comment_count',
	'comment_count_placement',
	'custom_insert_indentifier_detail_2',
	//Product list comment count
	'show_in_list',
	'list_placement',
	'custom_insert_indentifier_list',
	//Add on settings
	'comments_toggle',
	'product_detail_toggle',
	'product_list_toggle',
	'auto_assign_shortcode_toggle',
	'shortcode_value',
	'post_identifier_global',
);

if (!is_multisite()) {
	//Clear all options
	foreach ($option_list as $option_name) {
		delete_option($plugin_input_prefix . $option_name);
	}
} else {
	// get database of multisites
	global $wpdb;
	// get blog id list
	$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
	// store original id list
	$original_blog_id = get_current_blog_id();
	// cycle through blog ids
	foreach ($blog_ids as $blog_id) {
		switch_to_blog($blog_id);
		//cycle through options
		foreach ($option_list as $option_name) {
			delete_option($plugin_input_prefix . $option_name);
		}
	}
	// Set Back to Current Blog
	restore_current_blog($original_blog_id);
}

if ($zamartz_admin_event_tracker === 'yes') {
	$time_string = time();
	$tracker_url = 'https://zamartz.com/?api-secure-refrence&nocache=' . $time_string;

	$site_url = get_site_url();
	$site_hash_url = hash('sha256', $site_url);

	$tracker_data = array(
		'v'    => '1',
		'cid' => $site_hash_url,
		't' => 'event',
		'ec' => 'wp-dis-comments-and-ratings-woo',
		'ea' => 'delete',
		'el' => 'plugin options deleted',
		'ev' => '1',
	);

	wp_remote_request(
		$tracker_url,
		array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
				'Content-Type' => 'application/json'
			),
			'body'        => wp_json_encode($tracker_data),
			'cookies'     => array(),
		)
	);
}
