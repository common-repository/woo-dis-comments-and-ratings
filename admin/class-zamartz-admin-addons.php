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
 * Defines the settings for Zamartz admin settings, add-on tab
 *
 * @package    Wp_Woo_Dis_Comments_And_Ratings
 * @subpackage Wp_Woo_Dis_Comments_And_Ratings/admin
 * @author     Zachary Martz <zam@zamartz.com>
 */
class Wp_Woo_Disqus_Admin_Settings_Addons
{

    /**
     * Incorporate the trait functionalities for Zamartz General in this class
     * @see     zamartz/helper/trait-zamartz-general.php
     * 
     * Incorporate the trait functionalities for HTML template in this class
     * @see     zamartz/helper/trait-zamartz-html-template.php
     * 
     * Incorporate the trait functionalities for API methods in this class
     * @see     zamartz/helper/trait-zamartz-api-methods.php
     */
    use Zamartz_General, Zamartz_HTML_Template, Zamartz_API_Methods;

    /**
     * Loop order defining which accordion should be given priority with open/close state
     * 
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $loop_order    The loop number of each section.
     */
    protected $loop_order;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct($settings_instance)
    {

        //Set plugin parameter information
        $this->set_plugin_data($settings_instance);

        if (is_multisite()) {
            $blog_id = get_current_blog_id();
            $this->is_cron_log = get_blog_option($blog_id,  $settings_instance->plugin_input_prefix . 'cron_log');
        } else {
            $this->is_cron_log = get_option($settings_instance->plugin_input_prefix . 'cron_log');
        }
        $this->api_license_key = get_option($settings_instance->plugin_input_prefix . 'api_license_key');

        //Set setting ignore list for paid vs free versions
        $this->woo_disqus_addon_set_ignore_list();

        //Set accordion loop number
        $this->set_accordion_loop_order();

        //Set valid product IDs for API integration
        $product_id_array = array(8261, 8262, 8263, 8264);
        $this->set_valid_product_id($product_id_array);

        //Add filter to add/remove sub-navigation for each tab
        add_filter('zamartz_dashboard_accordion_information', array($this, 'get_dashboard_information'), 10, 1);

        //Add filter to add/remove sub-navigation for each tab
        add_filter('zamartz_dashboard_accordion_settings', array($this, 'get_dashboard_settings'), 10, 1);

        //Add filter to add/remove sub-navigation for each tab
        add_filter('zamartz_settings_subnav', array($this, 'get_section_tab_settings'), 10, 1);

        //Content display settings for add-ons page - Zamartz Admin
        add_action('zamartz_admin_addon_information', array($this, 'get_addon_information'), 10, 1);

        //Content display settings for add-ons page
        add_action('zamartz_admin_addon_settings', array($this, 'get_addon_settings'), 10, 1);

        //Add ajax action to save form data - Zamartz Admin
        add_action('wp_ajax_' . $this->plugin_input_prefix . 'form_data_ajax', array($this, 'save_form_data_ajax'));

        //Add ajax action to activate/deactivate plugin - Zamartz Admin
        add_action('wp_ajax_' . $this->plugin_input_prefix . 'activate_ajax', array($this, 'set_api_license_key_ajax'));

        //Add ajax action to activate/deactivate plugin - Zamartz Admin
        add_action('wp_ajax_' . $this->plugin_input_prefix . 'clear_api_credentials_ajax', array($this, 'clear_api_credentials_ajax'));

        //Add ajax to get plugin status - Zamartz Admin
        add_action('wp_ajax_' . $this->plugin_input_prefix . 'get_api_status_ajax', array($this, 'get_api_status_ajax'));

        //Create twice monthly cron schedule - Zamartz Admin
        add_filter('cron_schedules', array($this, 'zamartz_interval_twice_monthly'));

        //Run the API cron scheduler handler twice a month to check for API handshake - Zamartz Admin
        add_action('zamartz_api_cron_schedule_twice_monthly', array($this, 'zamartz_api_cron_schedule_handler'));

        //Create weekly cron schedule - Zamartz Admin
        add_filter('cron_schedules', array($this, 'zamartz_interval_weekly'));

        //Run the API cron scheduler handler weekly for disabling API paid features (if needed) - Zamartz Admin
        add_action('zamartz_api_cron_schedule_admin_notice', array($this, 'zamartz_disable_paid_features'));

        //Add admin notice if any - Zamartz Admin
        add_action('admin_notices', array($this, 'zamartz_api_admin_notice'));

        //Add ajax action to activate/deactivate plugin
        add_action('wp_ajax_' . $this->plugin_input_prefix . 'import_settings_ajax', array($this, 'set_import_settings_ajax'));

        //Add ajax action to auto assign disqus shortcode
        add_action('wp_ajax_' . $this->plugin_input_prefix . 'get_shortcode_ajax', array($this, 'get_shortcode_ajax'));

        //Add ajax action to fire google anayltics event on review now button click
        add_action('wp_ajax_zamartz_review_now_ajax', array($this, 'zamartz_review_now_ajax'));
    }

    /**
     * Get zamartz dasboard add-on accordion settings
     * 
     * @since    1.0.0
     */
    public function get_dashboard_information($dashboard_information)
    {
        if (!empty($dashboard_information) && $dashboard_information != null) {
            return $dashboard_information;
        }
        $dashboard_information = array(
            'title' => __('Dashboard', "wp-dis-comments-and-ratings-woo"),
            'description' => __("This dashboard will show all of the most recent update and activity for the ZAMARTZ family of Wordpress extensions.", "wp-dis-comments-and-ratings-woo")
        );
        return $dashboard_information;
    }

    /**
     * Get zamartz dasboard add-on accordion settings
     * 
     * @since    1.0.0
     */
    public function get_dashboard_settings($table_row_data)
    {
        $plugin_info = $this->get_plugin_info();
        $addon_settings_link = admin_url() . 'admin.php?page=zamartz-settings&tab=addons&section=' . $this->get_plugin_section_slug();
        $image_url = '<a href="' . $addon_settings_link . '">
                        <img title="' . $this->plugin_display_name . '" alt="Thumbnail for ' . $this->plugin_display_name . ', click for settings" src="' . $this->plugin_url['image_url'] . '/dashboard-default.png">
                        </a>';
        $feed_title = '<a alt="Title for ' . $this->plugin_display_name . ', click for settings" href="' . $addon_settings_link . '">' . $this->plugin_display_name . '</a>';
        $table_row_data[] = array(
            'data' => array(
                $image_url,
                '<p class="feed-item-title">' . $feed_title . '</p>
                 <p tabindex="0">' . $plugin_info['Description'] . '</p>',
            ),
            'row_class' => 'feed-row-content',
        );
        return $table_row_data;
    }

    /**
     * Add-on information for zamartz admin
     * 
     * @since   1.0.0
     */
    public function get_addon_information($addon_information)
    {
        $addon_information[$this->get_plugin_section_slug()] = array(
            'title' => $this->plugin_display_name,
            'description' => __("These Add-Ons provide functionality to existing Wordpress functionality or other extensions and plugins", "wp-dis-comments-and-ratings-woo"),
            'wrapper_class' => ($this->plugin_api_version !== 'Free' ? '' : ' plugin-free-version'),
            'input_prefix' => $this->plugin_input_prefix
        );
        return $addon_information;
    }

    /**
     * Add-on settings for zamartz admin
     * 
     * @since   1.0.0
     */
    public function get_addon_settings($addon_settings)
    {
        //Get get_functionality settings
        $content_array['column_array'][] = $this->get_functionality_settings();

        //Get license settings
        $content_array['column_array'][] = $this->get_license_settings();

        //Check if woo checkout plugin is paid
        if ($this->plugin_api_version !== 'Free') {
            //Get advanced settings
            $content_array['column_array'][] = $this->get_advanced_settings();
        }

        //Define page structure
        $content_array['page_structure'] = array(
            'desktop_span' => '75',
            'mobile_span' => '100',
        );

        $plugin_section_slug = $this->get_plugin_section_slug();
        $addon_settings[$plugin_section_slug][] = $content_array;

        //Get sidebar settings
        $addon_settings[$plugin_section_slug]['sidebar-settings'] = $this->get_sidebar_settings();

        return $addon_settings;
    }

    /**
     * Functionality settings inside the add-on tab
     */
    public function get_functionality_settings()
    {
        //Define accordion settings
        $accordion_settings = array(
            'type' => 'form_table',
            'is_delete' => false,
            'accordion_class' => 'zamartz-addon-settings',
            'accordion_loop' => $this->loop_order['zamartz_functionality_settings'],
            'form_section_data' => array(
                'linked_class' => 'zamartz-addon-settings'
            ),
            'title' => __("Functionality", "wp-dis-comments-and-ratings-woo")
        );

        $disqus_comments_toggle = get_option("{$this->plugin_input_prefix}comments_toggle");
        $disqus_product_detail_toggle = get_option("{$this->plugin_input_prefix}product_detail_toggle");
        $disqus_product_list_toggle = get_option("{$this->plugin_input_prefix}product_list_toggle");
        $disqus_auto_assign_shortcode_toggle = get_option("{$this->plugin_input_prefix}auto_assign_shortcode_toggle");
        $disqus_shortcode_value = get_option("{$this->plugin_input_prefix}shortcode_value");
        $disqus_post_identifier_global = get_option("{$this->plugin_input_prefix}post_identifier_global");

        $add_text = '';
        if ($this->plugin_api_version === 'Free') {
            $add_text = ' (Paid Condition)';
            $disqus_auto_assign_shortcode_toggle = 'no';
            $disqus_post_identifier_global = 'wordpress_post_id';
        }

        if ($disqus_shortcode_value != '') {
            $disqus_auto_assign_shortcode_toggle = 'yes';
        }

        //Define table data
        $table_section_array = array(
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
                        <a href="' . admin_url() . 'admin.php?page=wc-settings&tab=products&section=disqus_comments_ratings' . '">
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
                        <a href="' . admin_url() . 'admin.php?page=wc-settings&tab=products&section=disqus_comments_ratings' . '">
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
                        <a href="' . admin_url() . 'admin.php?page=wc-settings&tab=products&section=disqus_comments_ratings' . '">
                        ' . __("Configure Product List", "wp-dis-comments-and-ratings-woo") . '
                        </a>
                    </div>'
            ),
            array(
                'title' =>  __("Auto Assign Shortcode", "wp-dis-comments-and-ratings-woo"),
                'tooltip_desc' =>  __("Uses the short code that is already setup by the main Disqus Plugin", "wp-dis-comments-and-ratings-woo"),
                'type' => 'toggle_switch',
                'option_settings' => array(
                    'name' => "{$this->plugin_input_prefix}auto_assign_shortcode_toggle",
                    'class' => 'woo-disqus-auto-assign'
                ),
                'input_value' => $disqus_auto_assign_shortcode_toggle,
                'additional_content' => '
                <span class="dashicons dashicons-update spin woo-disqus-auto-assign-dashicon" style="display: none;"></span>
                <span class="zamartz-message"></span>
                '
            ),
            array(
                'title' =>  __("Manual Shortcode", "wp-dis-comments-and-ratings-woo"),
                'tooltip_desc' =>  __("Manually Enter your Disqus shortcode", "wp-dis-comments-and-ratings-woo"),
                'type' => 'input_text',
                'option_settings' => array(
                    'name' => "{$this->plugin_input_prefix}shortcode_value",
                ),
                'input_value' => $disqus_shortcode_value,
            ),
            array(
                'title' =>  __("Disqus Post Identifier", "wp-dis-comments-and-ratings-woo"),
                'tooltip_desc' =>  __("Configure what identifier links your product to the correct Disqus comments", "wp-dis-comments-and-ratings-woo"),
                'type' => 'select',
                'is_multi' => false,
                'option_settings' => array(
                    'name' => "{$this->plugin_input_prefix}post_identifier_global",
                    'class' => 'wc-enhanced-select disqus-post-identifier'
                ),
                'field_options' => array(
                    'wordpress_post_id' => __("Wordpress Post ID (default)", "wp-disqus-comments-ratings-woo"),
                    'product_parent_sku' => __("Product Parent SKU" . $add_text, "wp-disqus-comments-ratings-woo"),
                    'variant_sku' => __("Variant SKU" . $add_text, "wp-disqus-comments-ratings-woo"),
                    'product_wordpress_slug' => __("Product Wordpress Slug" . $add_text, "wp-disqus-comments-ratings-woo"),
                ),
                'input_value' => $disqus_post_identifier_global,
            ),
        );

        //Define table parameters
        $table_params = array(
            'form_data' => [],
            'section_type' => 'zamartz_functionality_settings',
        );

        return array(
            'accordion_settings' => $accordion_settings,
            'table_section_array' => $table_section_array,
            'table_params' => $table_params,
        );
    }

    /**
     * Define ignore list to restrict users from updating paid feature settings
     */
    public function woo_disqus_addon_set_ignore_list()
    {
        //Set ignore list for paid features
        if ($this->plugin_api_version === 'Free') {
            $this->ignore_list[] = "{$this->plugin_input_prefix}auto_assign_shortcode_toggle";
            $this->ignore_list[] = "{$this->plugin_input_prefix}post_identifier_global";
        }
    }

    /**
     * Auto assign shortcode from Disqus plugin
     */
    public function get_shortcode_ajax()
    {
        $settings_nonce = filter_input(INPUT_POST, 'settings_nonce', FILTER_SANITIZE_STRING);

        if (!wp_verify_nonce(wp_unslash($settings_nonce), 'zamartz-settings')) {
            echo json_encode(array('status' => false, 'message' => __('Nonce could not be verified!')));
            die();
        }

        if ($this->plugin_api_version === 'Free') {
            echo json_encode(array('status' => false, 'message' => __('API version not free.')));
            die();
        }

        $value = filter_input(INPUT_POST, 'value', FILTER_SANITIZE_STRING);
        if ($value === 'no') {
            update_option("{$this->plugin_input_prefix}shortcode_value", '');
            echo json_encode(array(
                'status' => true,
                'message' => __('Shortcode cleared.'),
                'shortcode' => ''
            ));
            die();
        }
        $shortcode = get_option('disqus_forum_url');

        if (!$shortcode) {
            echo json_encode(array('status' => false, 'message' => __('Disqus shortcode not found.')));
            die();
        }

        update_option("{$this->plugin_input_prefix}shortcode_value", $shortcode);
        echo json_encode(array(
            'status' => true,
            'message' => __('Shortcode assigned.'),
            'shortcode' => $shortcode
        ));
        die();
    }

    /**
     * Import settings from legacy plugin to new plugin
     */
    public function set_import_settings_ajax()
    {
        //Verify nonce
        $settings_nonce = filter_input(INPUT_POST, 'settings_nonce', FILTER_SANITIZE_STRING);
        if (!wp_verify_nonce(wp_unslash($settings_nonce), 'zamartz-settings')) {
            echo json_encode(array('status' => false, 'message' => __('Nonce could not be verified!')));
            die();
        }

        //Check if data exists in database for old and new plugin
        $is_legacy_exists = get_option("woocommerce_disqus_comments_and_ratings");

        //Check if legacy plugin data exists
        if ($is_legacy_exists === false) {
            echo json_encode(
                array(
                    'status' => false,
                    'message' => __('There is no Legacy Import data to bring into the new extension.', "wp-dis-comments-and-ratings-woo")
                )
            );
            die();
        }

        switch ($is_legacy_exists) {
            case 'show_disqus':
                $option = 'show_seperate_tab';
                break;
            case 'show_ratings':
                $option = 'replace_reviews';
                break;
            default:
                $option = 'show_seperate_tab';
                break;
        }
        update_option("{$this->plugin_input_prefix}detail_placement", $option);

        echo json_encode(
            array(
                'status' => true,
                'message' => __('Data has been imported.', "wp-dis-comments-and-ratings-woo")
            )
        );
        die();
    }
}
