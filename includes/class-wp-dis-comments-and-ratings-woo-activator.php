<?php

/**
 * Fired during plugin activation
 *
 * @link       https://zamartz.com
 * @since      1.0.0
 *
 * @package    Wp_Woo_Dis_Comments_And_Ratings
 * @subpackage Wp_Woo_Dis_Comments_And_Ratings/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Woo_Dis_Comments_And_Ratings
 * @subpackage Wp_Woo_Dis_Comments_And_Ratings/includes
 * @author     Zachary Martz <zam@zamartz.com>
 */
class Wp_Woo_Dis_Comments_And_Ratings_Activator
{

	/**
	 * Additional functionality to perform during plugin activation
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		$event_tracker = get_option('wp_zamartz_admin_event_tracker');
		if ($event_tracker === 'yes') {
			$ec = WP_WOO_DIS_COMMENTS_AND_RATINGS_DIR_SLUG;
			$time_string = time();
			$tracker_url = 'https://zamartz.com/?api-secure-refrence&nocache=' . $time_string;

			$site_url = get_site_url();
			$site_hash_url = hash('sha256', $site_url);

			$tracker_data = array(
				'v'    => '1',
				'cid' => $site_hash_url,
				't' => 'event',
				'ec' =>  $ec,
				'ea' => 'activate',
				'el' => 'plugin_activated',
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
	}
}
