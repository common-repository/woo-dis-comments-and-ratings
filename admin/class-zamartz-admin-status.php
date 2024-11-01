<?php

/**
 * The class is responsible for adding sections inside the WooCommerce settings page.
 *
 * @link       https://zamartz.com
 * @since      1.0.0
 *
 * @package    Wp_Woo_Dis_Comments_And_Ratings
 * @subpackage Wp_Woo_Dis_Comments_And_Ratings/admin
 */

/**
 * WooCommerce settings specific functionality of the plugin.
 *
 * Defines the settings for Status submenu
 *
 * @package    Wp_Woo_Dis_Comments_And_Ratings
 * @subpackage Wp_Woo_Dis_Comments_And_Ratings/admin
 * @author     Zachary Martz <zam@zamartz.com>
 */
class Wp_Woo_Disqus_Admin_Status
{
    /**
     * Incorporate the trait functionalities for Zamartz General in this class
     * @see     zamartz/helper/trait-zamartz-general.php
     * 
     * Incorporate the trait functionalities for API methods in this class
     * @see     zamartz/helper/trait-zamartz-api-methods.php
     */
    use Zamartz_General, Zamartz_API_Methods;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct($woo_checkout_settings_instance)
    {
        //Define plugin paramters
        $this->set_plugin_data($woo_checkout_settings_instance);

        //Content display settings for add-ons page
        add_filter('zamartz_plugin_status', array($this, 'get_status_settings'), 10, 1);
    }


    /**
     * Status settings for zamartz admin
     * 
     * @since   1.0.0
     */
    public function get_status_settings($status_settings_array)
    {

        $plugin_version = WP_WOO_DIS_COMMENTS_AND_RATINGS_VERSION;

        $disqus_comments_toggle = get_option("{$this->plugin_input_prefix}comments_toggle");
        $disqus_product_detail_toggle = get_option("{$this->plugin_input_prefix}product_detail_toggle");
        $disqus_product_list_toggle = get_option("{$this->plugin_input_prefix}product_list_toggle");
        $disqus_shortcode_value = get_option("{$this->plugin_input_prefix}shortcode_value");

        //Define table data
        $table_section_array = array(
            'row_head' => array(
                'title' =>  __($this->plugin_display_name . " Status", "wp-checkout-vis-fields-woo"),
                'colspan' => 2
            ),
            'row_data' => array(
                array(
                    'column_data' => array(
                        __("Plugin Version", "wp-checkout-vis-fields-woo"),
                        $plugin_version
                    ),
                    'tabindex' => 0
                ),
                array(
                    'column_data' => array(
                        __("Disqus Comment toggle - Product Detail", "wp-checkout-vis-fields-woo"),
                        $disqus_comments_toggle
                    ),
                    'tabindex' => 0
                ),
                array(
                    'column_data' => array(
                        __("Disqus Comment count toggle - Product Detail", "wp-checkout-vis-fields-woo"),
                        $disqus_product_detail_toggle
                    ),
                    'tabindex' => 0
                ),
                array(
                    'column_data' => array(
                        __("Disqus Comment count toggle - Product Listing", "wp-checkout-vis-fields-woo"),
                        $disqus_product_list_toggle
                    ),
                    'tabindex' => 0
                ),
                array(
                    'column_data' => array(
                        __("Disqus Shortcode", "wp-checkout-vis-fields-woo"),
                        $disqus_shortcode_value
                    ),
                    'tabindex' => 0
                ),
                array(
                    'column_data' => array(
                        __("API Version", "wp-checkout-vis-fields-woo"),
                        $this->plugin_api_version
                    ),
                    'tabindex' => 0
                ),
                array(
                    'column_data' => array(
                        __("API Authorization", "wp-checkout-vis-fields-woo"),
                        $this->plugin_api_authorization
                    ),
                    'tabindex' => 0
                )
            )
        );

        $api_get_response = get_option("{$this->plugin_input_prefix}api_get_response");
        $cron_schedule_details = $this->get_cron_schedule_details($api_get_response);
        if (!empty($cron_schedule_details)) {
            $table_section_array['row_data'][]  = array(
                'column_data' => array(
                    __("Cron current run", "wp-checkout-vis-fields-woo"),
                    $cron_schedule_details['cron_previous_run']
                ),
                'tabindex' => 0
            );
            $table_section_array['row_data'][]  = array(
                'column_data' => array(
                    __("Cron next run", "wp-checkout-vis-fields-woo"),
                    $cron_schedule_details['cron_next_run']
                ),
                'tabindex' => 0
            );
        }

        $table_params = array(
            'class' => 'zamartz-simple-table widefat'
        );

        $status_settings_array['zamartz_table'] = array(
            'table_params' => $table_params,
            'table_section_array' => $table_section_array
        );
        return $status_settings_array;
    }
}
