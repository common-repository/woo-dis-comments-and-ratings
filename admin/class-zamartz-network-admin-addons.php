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
 * Defines the settings for billing and shipping
 *
 * @package    Wp_Woo_Dis_Comments_And_Ratings
 * @subpackage Wp_Woo_Dis_Comments_And_Ratings/admin
 * @author     Zachary Martz <zam@zamartz.com>
 */
class Wp_Woo_Disqus_Network_Admin_Settings_Addons
{

    /**
     * Incorporate the trait functionalities for Zamartz General in this class
     * @see     zamartz/helper/trait-zamartz-general.php
     * 
     * Incorporate the trait functionalities for HTML template in this class
     * @see     zamartz/helper/trait-zamartz-html-template.php
     * 
     * Incorporate the trait functionalities for API methods in this plugin
     * @see     zamartz/helper/trait-zamartz-api-methods.php
     */
    use Zamartz_General, Zamartz_HTML_Template, Zamartz_API_Methods;

    /**
     * The purchase URL of the current plugin on Zamartz marketplace
     *
     * @since    1.0.0
     * @access   public
     * @var      string    $zamartz_plugin_url    Stores the Zamartz marketplace plugin URL
     */
    public $zamartz_plugin_url;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct($settings_instance)
    {

        //Define plugin paramters

        $this->zamartz_plugin_url = $settings_instance->zamartz_plugin_url;

        $this->set_plugin_data($settings_instance);

        //Set plugin paid vs free information
        $this->set_plugin_api_data();


        //Add filter to display information for network dashboard
        add_filter('zamartz_network_dashboard_accordion_information', array($this, 'get_network_dashboard_information'), 10, 1);

        //Add filter to display dashboard accordion for network dashboard
        add_filter('zamartz_network_dashboard_accordion_settings', array($this, 'get_network_dashboard_settings'), 10, 1);

        //Add filter to display add-on site list
        add_filter('zamartz_network_dashboard_active_addons_site_list', array($this, 'set_active_addons_site_list'), 10, 1);

        //Content display settings for Network Admin add-ons page
        add_action('zamartz_network_addon_settings', array($this, 'get_network_addon_settings'), 10, 1);

        //Content display information for Network Admin add-ons page
        add_action('zamartz_network_addon_information', array($this, 'get_network_addon_information'), 10, 1);

        //Get network api status
        add_action('wp_ajax_woo_disqus_get_network_api_status_ajax', array($this, 'get_network_api_status_ajax'));

        //Add ajax action to save form data
        add_action('wp_ajax_woo_disqus_network_addon_form_data_ajax', array($this, 'save_form_data_ajax'));

        if ($this->plugin_api_version === 'Paid') {
            add_filter('zamartz_network_is_remove_ads', function () {
                return true;
            });
        }
    }

    /**
     * Content to display on network admin dashboard
     * 
     * @since   1.0.0
     */
    public function get_network_dashboard_information($dashboard_information)
    {
        if (empty($dashboard_information)) {
            $dashboard_information = array(
                'title' => __('Dashboard', "wp-dis-comments-and-ratings-woo"),
                'description' => __("This dashboard will show all of the most recent update and activity for the ZAMARTZ family of Wordpress extensions.", "wp-dis-comments-and-ratings-woo")
            );
        }
        return $dashboard_information;
    }

    /**
     * Add-on settings for zamartz admin
     * 
     * @since   1.0.0
     */
    public function get_network_dashboard_settings($table_section_array)
    {
        $plugin_info = $this->get_plugin_info();
        $addon_settings_link = network_admin_url() . 'admin.php?page=zamartz-network-settings&tab=addons';
        $image_url = '<a href="' . $addon_settings_link . '">
                <img title="' . $this->plugin_display_name . '" alt="Thumbnail for ' . $this->plugin_display_name . ', click for settings" src="' . $this->plugin_url['image_url'] . '/dashboard-default.png">
            </a>';
        $feed_title = '<a alt="Title for ' . $this->plugin_display_name . ', click for settings" href="' . $addon_settings_link . '">' . $this->plugin_display_name . '</a>';
        $table_section_array['row_data'][] = array(
            'data' => array(
                $image_url,
                '<p class="feed-item-title">' . $feed_title . '</p>
                 <p tabindex="0">' . $plugin_info['Description'] . '</p>',
            ),
            'row_class' => 'feed-row-content',
        );
        return $table_section_array;
    }

    /**
     * Add-on settings for zamartz admin
     * 
     * @since   1.0.0
     */
    public function get_network_addon_information($addon_information)
    {
        if ($this->plugin_api_version === 'Paid' && empty($addon_information)) {

            $addon_information = array(
                'title' => $this->plugin_display_name,
                'description' => __("This dashboard will allow you to quickly manage high-level features of this add-on and see global use of API keys", "wp-dis-comments-and-ratings-woo")
            );
        }
        return $addon_information;
    }

    /**
     * Add-on settings for zamartz admin
     * 
     * @since   1.0.0
     */
    public function get_network_addon_settings($addon_settings)
    {
        $plugin_section_slug = $this->get_plugin_section_slug();

        if ($this->plugin_api_version !== 'Paid') {
            $addon_settings[$plugin_section_slug][] = array(
                'row_field_settings' => $this->get_paid_addons_notice_box_settings(),
                'page_structure' => array(
                    'desktop_span' => '50',
                    'mobile_span' => '100',
                ),
            );
            $addon_settings[$plugin_section_slug][] = array(
                'row_field_settings' => $this->get_zamartz_review_notice_box_settings(),
                'page_structure' => array(
                    'desktop_span' => '50',
                    'mobile_span' => '100',
                ),
            );

            return $addon_settings;
        }

        //Get get_functionality settings
        $content_array['column_array'] = $this->get_domain_settings();

        //Define page structure
        $content_array['page_structure'] = array(
            'desktop_span' => '75',
            'mobile_span' => '100',
        );

        $addon_settings[$plugin_section_slug][] = $content_array;

        //Get sidebar settings
        $addon_settings[$plugin_section_slug]['sidebar-settings'] = $this->get_network_admin_sidebar_settings();

        return $addon_settings;
    }

    /**
     * Functionality settings inside the add-on tab
     */
    public function get_domain_settings()
    {
        $domain_list = get_sites();
        $column_array = [];

        foreach ($domain_list as $domain) {


            $domain_url = $domain->domain . $domain->path;
            $blog_id = $domain->blog_id;
            $blog_details = get_blog_details(array('blog_id' => $blog_id));

            //Add logic to check if plugin is active for this blog
            if ($blog_details->deleted || !$this->is_plugin_active_zamartz($blog_id)) {
                continue;
            }

            $title = $blog_details->blogname;

            //Define accordion settings
            $accordion_settings = array(
                'type' => 'form_table',
                'is_delete' => false,
                'accordion_class' => 'woo-disqus-addon-settings',
                'accordion_loop' => $blog_id,
                'form_section_data' => array(
                    'linked_class' => 'woo-disqus-addon-settings'
                ),
                'title' => __($title . ' (' . $domain_url . ')', "wp-dis-comments-and-ratings-woo")
            );

            $cron_log = get_blog_option($blog_id, $this->plugin_input_prefix . 'cron_log');
            $disqus_comments_toggle = get_blog_option($blog_id, "{$this->plugin_input_prefix}comments_toggle");
            $disqus_product_detail_toggle = get_blog_option($blog_id, "{$this->plugin_input_prefix}product_detail_toggle");
            $disqus_product_list_toggle = get_blog_option($blog_id, "{$this->plugin_input_prefix}product_list_toggle");

            //Define table data
            $admin_url = get_admin_url($blog_id);   //Get admin url based on domain
            $table_section_array = array(
                array(
                    'title' =>  __("Add-on activation status", "wp-dis-comments-and-ratings-woo"),
                    'type' => '',
                    'additional_content' => '
                    <div class="additional-content">
                        <a href="' . $admin_url . 'admin.php?page=zamartz-settings&tab=addons&section=' . $this->get_plugin_section_slug() . '">
                        ' . __("[Network activated]", "wp-dis-comments-and-ratings-woo") . '
                        </a>
                    </div>'
                ),
                array(
                    'title' =>  __("WooCommerce Status", "wp-dis-comments-and-ratings-woo"),
                    'type' => '',
                    'additional_content' => '
                    <div class="additional-content">
                        <a href="' . $admin_url . 'admin.php?page=wc-status">
                        ' . __("[Site activated]", "wp-dis-comments-and-ratings-woo") . '
                        </a>
                    </div>'
                ),
                array(
                    'title' =>  __("API Key Status", "wp-dis-comments-and-ratings-woo"),
                    'type' => '',
                    'additional_content' => '
                    <div class="additional-content">
                        <a href="' . $admin_url . 'admin.php?page=zamartz-settings&tab=addons&accordion=license&section=' . $this->get_plugin_section_slug() . '">
                        ' . __("[Site activated]", "wp-dis-comments-and-ratings-woo") . '
                        </a>
                    </div>'
                ),
                array(
                    'title' =>  __("Disqus Comments", "wp-dis-comments-and-ratings-woo"),
                    'tooltip_desc' =>  __("Enable or Disable Disqus Comments for Wordpress", "wp-dis-comments-and-ratings-woo"),
                    'type' => 'toggle_switch',
                    'option_settings' => array(
                        'name' => "{$this->plugin_input_prefix}comments_toggle",
                    ),
                    'input_value' => $disqus_comments_toggle,
                    'additional_content' => '
                        <div class="additional-content">
                            <a href="' . $admin_url . 'admin.php?page=wc-settings&tab=products&section=disqus_comments_ratings' . '">
                            ' . __("Configure Disqus Comments", "wp-dis-comments-and-ratings-woo") . '
                            </a>
                        </div>'
                ),
                array(
                    'title' =>  __("Disqus Product Detail", "wp-dis-comments-and-ratings-woo"),
                    'tooltip_desc' =>  __("Enables or Disables Disqus Comments functionality for Product Detail Pages", "wp-dis-comments-and-ratings-woo"),
                    'type' => 'toggle_switch',
                    'option_settings' => array(
                        'name' => "{$this->plugin_input_prefix}product_detail_toggle",
                    ),
                    'input_value' => $disqus_product_detail_toggle,
                    'additional_content' => '
                        <div class="additional-content">
                            <a href="' . $admin_url . 'admin.php?page=wc-settings&tab=products&section=disqus_comments_ratings' . '">
                            ' . __("Configure Product Detail", "wp-dis-comments-and-ratings-woo") . '
                            </a>
                        </div>'
                ),
                array(
                    'title' =>  __("Disqus Product List", "wp-dis-comments-and-ratings-woo"),
                    'tooltip_desc' =>  __("Enables or Disables Disqus Comments functionality for Product List Pages", "wp-dis-comments-and-ratings-woo"),
                    'type' => 'toggle_switch',
                    'option_settings' => array(
                        'name' => "{$this->plugin_input_prefix}product_list_toggle",
                    ),
                    'input_value' => $disqus_product_list_toggle,
                    'additional_content' => '
                        <div class="additional-content">
                            <a href="' . $admin_url . 'admin.php?page=wc-settings&tab=products&section=disqus_comments_ratings' . '">
                            ' . __("Configure Product List", "wp-dis-comments-and-ratings-woo") . '
                            </a>
                        </div>'
                ),
                array(
                    'title' =>  __("CRON Log", "wp-dis-comments-and-ratings-woo"),
                    'tooltip_desc' => __("Enabling this will create a debug log for each time the activation status cron job is run. Turning off will delete the log.", "wp-dis-comments-and-ratings-woo"),
                    'type' => 'toggle_switch',
                    'option_settings' => array(
                        'name' => $this->plugin_input_prefix . "cron_log",
                    ),
                    'input_value' => $cron_log,
                    'additional_content' => '
                    <div class="additional-content">
                        ' . __("Created per site", "wp-dis-comments-and-ratings-woo") . '
                    </div>'
                ),
            );

            //Define table parameters
            $table_params = array(
                'form_data' => [],
                'section_type' => 'zamartz_network_domain_settings',
                'key' => $blog_id
            );
            $column_array[] = array(
                'accordion_settings' => $accordion_settings,
                'table_section_array' => $table_section_array,
                'table_params' => $table_params,
            );
        }
        return $column_array;
    }

    /**
     * Save the form data of settings page
     * 
     * @since    1.0.0
     */
    public function save_form_data_ajax()
    {
        $form_data = filter_input(INPUT_POST, 'form_data', FILTER_SANITIZE_STRING);
        parse_str($form_data, $postArray);

        if (!wp_verify_nonce(wp_unslash($postArray['zamartz_settings_nonce']), 'zamartz-settings')) {
            echo json_encode(array('status' => false, 'message' => __('Nonce could not be verified!')));
        }
        global $wpdb;

        $error = false;
        $message = 'Your settings have been saved.';
        $class = 'updated inline';

        foreach ($postArray as $option_name => $data) {
            if (empty($option_name) || strpos($option_name, "woo_disqus") === false || $option_name == 'zamartz_settings_nonce') {
                continue;
            }

            foreach ($data as $blog_id => $value) {
                if (!current_user_can_for_blog($blog_id, 'manage_options')) {
                    continue;
                }
                update_blog_option($blog_id, $option_name, $value);
                if (!empty($wpdb->last_error)) {
                    $error = true;
                    $message = 'There was a problem while updating the option data';
                    $class = 'error inline';
                    break 2;
                }
            }
        }

        echo json_encode(
            array(
                'status' => !$error,
                'message' => '<p><strong>' . $message . '</strong></p>',
                'class' => $class
            )
        );
        die();
    }
}
