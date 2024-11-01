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
 * Defines the settings for Disqus comments & ratings plugin
 *
 * @package    Wp_Woo_Dis_Comments_And_Ratings
 * @subpackage Wp_Woo_Dis_Comments_And_Ratings/admin
 * @author     Zachary Martz <zam@zamartz.com>
 */
class Wp_Woo_Disqus_Settings
{

    /**
     * Incorporate the trait functionalities for Zamartz General in this class
     * @see     zamartz/helper/trait-zamartz-general.php
     * 
     * Incorporate the trait functionalities for HTML template in this class
     * @see     zamartz/helper/trait-zamartz-html-template.php
     */
    use Zamartz_General, Zamartz_HTML_Template;

    /**
     * Form settings data
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $form_data    Saves the data for our respective section form (shipping|billing).
     */
    private $form_data;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      object    $core_instance     The instance of Wp_Woo_Dis_Comments_And_Ratings class
     */
    public function __construct($core_instance)
    {

        //Set plugin parameter information
        $this->set_plugin_data($core_instance);

        //Set plugin paid vs free information
        $this->set_plugin_api_data();

        //Set setting ignore list for paid vs free versions
        $this->woo_disqus_set_ignore_list();

        if (class_exists('Wp_Woo_Main_Zamartz_Admin')) {
            require_once $this->plugin_url['admin_path'] . '/class-zamartz-admin-addons.php';
            new Wp_Woo_Disqus_Admin_Settings_Addons($this);
        }

        //Add check if all toggle buttons are disabled
        $disqus_product_detail_toggle = get_option("{$this->plugin_input_prefix}product_detail_toggle");
        $disqus_comments_toggle = get_option("{$this->plugin_input_prefix}comments_toggle");
        $disqus_product_list_toggle = get_option("{$this->plugin_input_prefix}product_list_toggle");

        if ($disqus_product_detail_toggle === 'yes' || $disqus_comments_toggle === 'yes' || $disqus_product_list_toggle === 'yes') {
            //Add filter to define a disqus tab under Product section
            add_filter('woocommerce_get_sections_products', array($this, 'add_product_disqus_comments_tab'));

            //Add filter to define custom settings for our added disqus comments sections
            add_filter('woocommerce_get_settings_products', array($this, 'woo_disqus_comments_settings'), 10, 2);
        }

        //Ajax call to run on saving Disqus comments settings
        add_action('wp_ajax_woo_disqus_form_data_ajax', array($this, 'save_form_data_ajax'));

        if ($this->plugin_api_version !== 'Free') {
            //Logic for meta box
            add_action('add_meta_boxes', array($this, 'woo_disqus_add_meta_boxes'), 40);

            //Save post data - function specific to Product page Post identifier metabox
            add_action('woocommerce_new_product', array($this, 'woo_disqus_post_identifier_save_data'));
            add_action('woocommerce_update_product', array($this, 'woo_disqus_post_identifier_save_data'));
        }

        //Add modal to plugin page
        add_action('admin_footer', array($this, 'get_deactivation_plugin_modal'));

        //Add modal to plugin page
        add_action('wp_ajax_' . $this->plugin_input_prefix . 'deactitvate_plugin', array($this, 'zamartz_deactitvate_plugin'));


        //Content display settings for Network Admin add-ons page
        if ((is_network_admin() || wp_doing_ajax()) && class_exists('Wp_Woo_Main_Zamartz_Admin')) {
            require_once $this->plugin_url['admin_path'] . '/class-zamartz-network-admin-addons.php';
            new Wp_Woo_Disqus_Network_Admin_Settings_Addons($this);
        }
        
        if (class_exists('Wp_Woo_Main_Zamartz_Admin')) {
            require_once $this->plugin_url['admin_path'] . '/class-zamartz-admin-status.php';
            new Wp_Woo_Disqus_Admin_Status($this);
        }
    }

    /**
     * Add a tab in WooCommerce Settings > Products > Disqus comments & ratings Options called Disqus Comments.
     *
     * @since   1.0.0
     * @param   string  $sections   The name of the current WooCommerce section.
     * @return  string
     */
    public function add_product_disqus_comments_tab($sections)
    {
        $sections['disqus_comments_ratings'] = __("Disqus Comments", "wp-disqus-comments-ratings-woo");
        return $sections;
    }

    /**
     * Define the settings that needs to be displayed for shipping and billing section.
     * Shipping section: WooCommerce Settings > Shipping > Shipping field visibility
     * Billing Section: WooCommerce Settings > Billing
     *
     * @since   1.0.0
     * @param   array   $settings           Array of data for form settings to be generated by WooCommerce.
     * @param   string  $current_section    Current WooCommerce section
     * @return  array
     */
    public function woo_disqus_comments_settings($settings, $current_section)
    {
        //Check the current section
        if ($current_section == 'disqus_comments_ratings') {
            $settings_disqus = array();

            $this->section_type = 'disqus_comments';
            $this->woo_disqus_set_form_data();

            // //Display shipping settings html template
            require_once $this->plugin_url['admin_path'] . '/partials/wp-dis-comments-html-form.php';

            return $settings_disqus;

            /**
             * If not, return the standard settings
             **/
        } else {
            return $settings;
        }
    }

    /**
     * Generates the accordion html for "Product detail pages" in WooCommerce > Products > Disqus Comments
     *
     * @since   1.0.0
     */
    public function get_product_detail_page_settings_html()
    {

        $disqus_product_detail_toggle = get_option("{$this->plugin_input_prefix}product_detail_toggle");
        $disqus_comments_toggle = get_option("{$this->plugin_input_prefix}comments_toggle");

        if ($disqus_product_detail_toggle !== 'yes' && $disqus_comments_toggle !== 'yes') {
            return;
        }

        $add_text = '';
        $table_section_detail_array = array();
        $table_section_comment_array = array();

        //Define accordion settings
        $table_params = array(
            'form_data' => $this->form_data,
            'section_type' => $this->section_type,
        );
        $accordion_settings = array(
            'type' => 'form_table',
            'is_delete' => false,
            'accordion_class' => 'zamartz-form-rule-section',
            'title' => __('Product Detail Pages', "wp-disqus-comments-ratings-woo")
        );


        $add_class = '';
        $additional_content = '';
        if ($this->plugin_api_version === 'Free') {
            $add_text = ' (Paid Condition)';
            $add_class = 'zamartz-paid-feature';
            $additional_content = '<div class="additional-content"><span>Disabled (Paid Option)</span></div>';
        }
        if ($disqus_product_detail_toggle === 'yes') {
            $table_section_detail_array = array(
                array(
                    'title' =>  __("Show in Detail Area", "wp-disqus-comments-ratings-woo"),
                    'tooltip_desc' => 'Enables or Disables Disqus from being shown on the Product Detail Pages',
                    'type' => 'toggle_switch',
                    'option_settings' => array(
                        'name' => "{$this->plugin_input_prefix}show_in_detail",
                    ),
                ),
                array(
                    'title' =>  __("Detail Placement", "wp-disqus-comments-ratings-woo"),
                    'type' => 'select',
                    'tooltip_desc' => __("What area of the product detail page should Disqus be shown", "wp-checkout-vis-fields-woo"),
                    'is_multi' => false,
                    'option_settings' => array(
                        'name' => "{$this->plugin_input_prefix}detail_placement",
                        'class' => 'wc-enhanced-select woo-placement-option-selector',
                        'data-params' => array(
                            'input_field' => "{$this->plugin_input_prefix}custom_insert_indentifier_detail_1"
                        )
                    ),
                    'field_options' => array(
                        'show_seperate_tab' => __("Show Disqus in Seperate Tab (default)", "wp-disqus-comments-ratings-woo"),
                        'replace_reviews' => __("Replace Reviews" . $add_text, "wp-disqus-comments-ratings-woo"),
                        'show_under_tabs' => __("Show Under Tabs" . $add_text, "wp-disqus-comments-ratings-woo"),
                        'show_under_summary' => __("Show under Summary" . $add_text, "wp-disqus-comments-ratings-woo"),
                        'custom' => __("Custom", "wp-disqus-comments-ratings-woo"),
                    )
                ),
                array(
                    'title' =>  __("Custom Insert CSS Identifier", "wp-disqus-comments-ratings-woo"),
                    'type' => 'input_text',
                    'tooltip_desc' => __("Configure the CSS targeting in which the Detail placement will be added on the detail page", "wp-checkout-vis-fields-woo"),
                    'option_settings' => array(
                        'name' => "{$this->plugin_input_prefix}custom_insert_indentifier_detail_1",
                    ),
                    'section_class' => 'woo-disqus-custom-css-indentifier'
                ),
            );
        }
        if ($disqus_comments_toggle === 'yes') {
            $table_section_comment_array = array(
                array(
                    'title' =>  __("Show Comment Count", "wp-disqus-comments-ratings-woo"),
                    'tooltip_desc' => 'Enables or Disables the ability to show the comment out elsewhere on the Product Detail Page',
                    'type' => 'toggle_switch',
                    'option_settings' => array(
                        'name' => "{$this->plugin_input_prefix}show_comment_count",
                        'class' => $add_class
                    ),
                    'additional_content' => $additional_content,
                    'section_class' => $disqus_product_detail_toggle === 'yes' ? 'zamartz-bordered' : ''
                ),
                array(
                    'title' =>  __("Comment Count Placement", "wp-disqus-comments-ratings-woo"),
                    'type' => 'select',
                    'tooltip_desc' => __("Configures where on the page the Comment Count will be displayed on the Product Detail Page", "wp-checkout-vis-fields-woo"),
                    'is_multi' => false,
                    'option_settings' => array(
                        'name' => "{$this->plugin_input_prefix}comment_count_placement",
                        'class' => 'wc-enhanced-select woo-placement-option-selector',
                        'data-params' => array(
                            'input_field' => "{$this->plugin_input_prefix}custom_insert_indentifier_detail_2"
                        )
                    ),
                    'field_options' => array(
                        'below_product_title' => __("Below Product Title" . $add_text, "wp-disqus-comments-ratings-woo"),
                        'below_product_sku' => __("Below Product Sku" . $add_text, "wp-disqus-comments-ratings-woo"),
                        'below_price' => __("Below Price" . $add_text, "wp-disqus-comments-ratings-woo"),
                        'custom' => __("Custom" . $add_text, "wp-disqus-comments-ratings-woo"),
                    )
                ),
                array(
                    'title' =>  __("Custom Insert CSS Identifier", "wp-disqus-comments-ratings-woo"),
                    'type' => 'input_text',
                    'tooltip_desc' => __("Configure the CSS targeting in which the Product Detail Page Comment Count placement will be added on the detail page", "wp-checkout-vis-fields-woo"),
                    'option_settings' => array(
                        'name' => "{$this->plugin_input_prefix}custom_insert_indentifier_detail_2",
                    ),
                    'section_class' => 'woo-disqus-custom-css-indentifier'
                )
            );
        }

        $table_section_array = array_merge($table_section_detail_array, $table_section_comment_array);

        $this->generate_accordion_html($accordion_settings, $table_section_array, $table_params);
    }

    /**
     * Generates the accordion html for "Product list pages" in WooCommerce > Products > Disqus Comments
     *
     * @since   1.0.0
     */
    public function get_product_list_page_settings_html()
    {

        $disqus_product_list_toggle = get_option("{$this->plugin_input_prefix}product_list_toggle");
        if ($disqus_product_list_toggle !== 'yes') {
            return;
        }

        $add_text = '';
        $add_class = '';
        $additional_content = '';
        if ($this->plugin_api_version === 'Free') {
            $add_text = ' (Paid Condition)';
            $add_class = 'zamartz-paid-feature';
            $additional_content = '<div class="additional-content"><span>Disabled (Paid Option)</span></div>';
        }

        //Define accordion settings
        $table_params = array(
            'form_data' => $this->form_data,
            'section_type' => $this->section_type,
        );
        $accordion_settings = array(
            'type' => 'form_table',
            'is_delete' => false,
            'accordion_class' => 'zamartz-form-rule-section',
            'title' => __('Product List Pages', "wp-disqus-comments-ratings-woo")
        );
        $table_section_array = array(
            array(
                'title' =>  __("Show in List Area", "wp-disqus-comments-ratings-woo"),
                'tooltip_desc' => 'Enables or Disables Disqus from being shown on the Product List Pages',
                'type' => 'toggle_switch',
                'option_settings' => array(
                    'name' => "{$this->plugin_input_prefix}show_in_list",
                    'class' => $add_class,
                ),
                'additional_content' => $additional_content,
            ),
            array(
                'title' =>  __("List Placement", "wp-disqus-comments-ratings-woo"),
                'type' => 'select',
                'tooltip_desc' => __("What area of the product list page should Disqus comment count be shown", "wp-checkout-vis-fields-woo"),
                'is_multi' => false,
                'option_settings' => array(
                    'name' => "{$this->plugin_input_prefix}list_placement",
                    'class' => 'wc-enhanced-select woo-placement-option-selector',
                    'data-params' => array(
                        'input_field' => "{$this->plugin_input_prefix}custom_insert_indentifier_list"
                    )
                ),
                'field_options' => array(
                    'above_product_title' => __("Above Product Title" . $add_text, "wp-disqus-comments-ratings-woo"),
                    'below_product_title' => __("Below Product Title" . $add_text, "wp-disqus-comments-ratings-woo"),
                    'custom' => __("Custom" . $add_text, "wp-disqus-comments-ratings-woo"),
                )
            ),
            array(
                'title' =>  __("Custom Insert CSS Identifier", "wp-disqus-comments-ratings-woo"),
                'type' => 'input_text',
                'tooltip_desc' => __("Configure the CSS targeting in which the Product List Page Comment Count placement will be added on the List page", "wp-checkout-vis-fields-woo"),
                'option_settings' => array(
                    'name' => "{$this->plugin_input_prefix}custom_insert_indentifier_list",
                ),
                'section_class' => 'woo-disqus-custom-css-indentifier'
            ),
        );

        $this->generate_accordion_html($accordion_settings, $table_section_array, $table_params);
    }

    /**
     * Generates the sidebar to be displayed with the relevant settings accordion
     *
     * @since   1.0.0
     */
    public function woo_disqus_get_sidebar_settings_html()
    {
        $table_section_array =
            array(
                'row_data' => array(
                    array(
                        'data' => array(
                            __("Version", "wp-disqus-comments-ratings-woo"),
                            $this->plugin_api_version
                        ),
                        'tabindex' => 0
                    ),
                    array(
                        'data' => array(
                            __("Authorization", "wp-disqus-comments-ratings-woo"),
                            $this->plugin_api_authorization
                        ),
                        'tabindex' => 0
                    ),
                ),
                'row_footer' => array(
                    'is_link' => array(
                        'link' => admin_url() . 'admin.php?page=zamartz-settings&tab=addons&section=' . $this->get_plugin_section_slug(),
                        'title' => __("Settings", "wp-disqus-comments-ratings-woo"),
                        'class' => ''
                    ),
                    'is_button' => array(
                        'name' => 'save',
                        'type' => 'submit',
                        'action' => 'woo_disqus_form_data_ajax',
                        'class' => 'button button-primary button-large',
                        'value' => __("Save changes", "wp-disqus-comments-ratings-woo"),
                    )
                ),
                'nonce' => wp_nonce_field('zamartz-settings', 'zamartz_settings_nonce', true, false)
            );
        $accordion_settings = array(
            'title' => __("Disqus Comments & Ratings", "wp-disqus-comments-ratings-woo"),
            'type' => 'save_footer',
            'accordion_class' => 'zamartz-accordion-sidebar',
            'form_section_data' => array(
                'toggle' => 'affix',
                'custom-affix-height' => '88'
            ),
        );

        $this->generate_accordion_html($accordion_settings, $table_section_array);
    }

    /**
     * Retrieves the current section type (shipping|billing) and sets the form data.
     *
     * @since   1.0.0
     */
    private function woo_disqus_set_form_data()
    {
        //Product detail page settings
        $this->form_data["{$this->plugin_input_prefix}show_in_detail"] = get_option("{$this->plugin_input_prefix}show_in_detail");

        if ($this->plugin_api_version === 'Paid') {
            //Product detail page settings
            $this->form_data["{$this->plugin_input_prefix}detail_placement"] = get_option("{$this->plugin_input_prefix}detail_placement");
            $this->form_data["{$this->plugin_input_prefix}custom_insert_indentifier_detail_1"] = get_option("{$this->plugin_input_prefix}custom_insert_indentifier_detail_1");
            $this->form_data["{$this->plugin_input_prefix}show_comment_count"] = get_option("{$this->plugin_input_prefix}show_comment_count");
            $this->form_data["{$this->plugin_input_prefix}comment_count_placement"] = get_option("{$this->plugin_input_prefix}comment_count_placement");
            $this->form_data["{$this->plugin_input_prefix}custom_insert_indentifier_detail_2"] = get_option("{$this->plugin_input_prefix}custom_insert_indentifier_detail_2");

            //Product list page settings
            $this->form_data["{$this->plugin_input_prefix}show_in_list"] = get_option("{$this->plugin_input_prefix}show_in_list");
            $this->form_data["{$this->plugin_input_prefix}list_placement"] = get_option("{$this->plugin_input_prefix}list_placement");
            $this->form_data["{$this->plugin_input_prefix}custom_insert_indentifier_list"] = get_option("{$this->plugin_input_prefix}custom_insert_indentifier_list");
        } else {
            $this->form_data["{$this->plugin_input_prefix}detail_placement"] = '';
            $this->form_data["{$this->plugin_input_prefix}custom_insert_indentifier_detail_1"] = '';

            //Product detail page settings
            $this->form_data["{$this->plugin_input_prefix}show_comment_count"] = 'no';
            $this->form_data["{$this->plugin_input_prefix}comment_count_placement"] = '';
            $this->form_data["{$this->plugin_input_prefix}custom_insert_indentifier_detail_2"] = '';

            //Product list page settings
            $this->form_data["{$this->plugin_input_prefix}show_in_list"] = 'no';
            $this->form_data["{$this->plugin_input_prefix}list_placement"] = '';
            $this->form_data["{$this->plugin_input_prefix}custom_insert_indentifier_list"] = '';
        }
    }

    /**
     * Define ignore list to restrict users from updating paid feature settings
     */
    public function woo_disqus_set_ignore_list()
    {
        //Set ignore list for paid features
        if ($this->plugin_api_version === 'Free') {
            $this->ignore_list[] = "{$this->plugin_input_prefix}detail_placement";
            $this->ignore_list[] = "{$this->plugin_input_prefix}custom_insert_indentifier_detail_1";
            $this->ignore_list[] = "{$this->plugin_input_prefix}comment_count_placement";
            $this->ignore_list[] = "{$this->plugin_input_prefix}custom_insert_indentifier_detail_2";
            $this->ignore_list[] = "{$this->plugin_input_prefix}list_placement";
            $this->ignore_list[] = "{$this->plugin_input_prefix}custom_insert_indentifier_list";
        }
    }

    /**
     * Add meta boxes related to WooCommerce
     */
    public function woo_disqus_add_meta_boxes($post_type)
    {
        if ($post_type === 'product') {
            add_meta_box(
                $this->plugin_input_prefix . 'product_meta_box',
                __('Disqus Comments', 'wp-disqus-comments-ratings-woo'),
                array($this, 'render_post_identifier_content'),
                $post_type,
                'side',
                'low'
            );
        }
    }

    /**
     * Render Post identifier meta box content
     */
    public function render_post_identifier_content($post)
    {

        $post_id = $post->ID;

        $disable_disqus_product = get_post_meta($post_id, "{$this->plugin_input_prefix}disable_disqus_product", true);
        $override_identifier_product = get_post_meta($post_id, "{$this->plugin_input_prefix}override_identifier_product", true);
        $disqus_post_identifier_product = get_post_meta($post_id, "{$this->plugin_input_prefix}post_identifier_product", true);
        $manual_post_identifier_product = get_post_meta($post_id, "{$this->plugin_input_prefix}manual_post_identifier_product", true);

        $meta_box_settings = array(
            array(
                'title' => 'Disable disqus on this product',
                'type' => 'toggle_switch',
                'option_settings' => array(
                    'name' => $this->plugin_input_prefix . 'disable_disqus_product',
                ),
                'title_location' => 'right',
                'input_value' => $disable_disqus_product
            ),
            array(
                'title' => 'Override Global Identifier',
                'type' => 'toggle_switch',
                'option_settings' => array(
                    'name' => $this->plugin_input_prefix . 'override_identifier_product',
                ),
                'title_location' => 'right',
                'input_value' => $override_identifier_product
            ),
            array(
                'title' => 'Disqus Post Identifier',
                'type' => 'select',
                'is_multi' => false,
                'option_settings' => array(
                    'name' =>  "{$this->plugin_input_prefix}post_identifier_product",
                    'class' => 'wc-enhanced-select woo-disqus-post-identifier-product',
                    'data-params' => array(
                        'input_field' => "{$this->plugin_input_prefix}manual_post_identifier_product"
                    )
                ),
                'field_options' => array(
                    'wordpress_post_id' => __("Wordpress Post ID (default)", "wp-disqus-comments-ratings-woo"),
                    'product_parent_sku' => __("Product Parent SKU", "wp-disqus-comments-ratings-woo"),
                    'variant_sku' => __("Variant SKU", "wp-disqus-comments-ratings-woo"),
                    'product_wordpress_slug' => __("Product Wordpress Slug", "wp-disqus-comments-ratings-woo"),
                    'manual_entry' => __("Manual Entry", "wp-disqus-comments-ratings-woo"),
                ),
                'title_location' => 'top',
                'class' => 'woo-disqus-meta-select2',
                'input_value' => $disqus_post_identifier_product
            ),
            array(
                'title' => 'Manual Post Identifier',
                'type' => 'input_text',
                'option_settings' => array(
                    'name' => "{$this->plugin_input_prefix}manual_post_identifier_product"
                ),
                'title_location' => 'top',
                'class' => 'woo-disqus-manual-post-identifier',
                'input_value' => $manual_post_identifier_product
            )
        );
        $this->generate_metabox_html($meta_box_settings, $this->plugin_input_prefix);
    }

    /**
     * Save post identifier data on per product page
     * 
     * @param   int     $post_id    Current post id that is being saved
     */
    public function woo_disqus_post_identifier_save_data($post_id)
    {
        $name_list = array(
            "disable_disqus_product",
            "override_identifier_product",
            "post_identifier_product",
            "manual_post_identifier_product"
        );
        foreach ($name_list as $name) {
            $input_name = $this->plugin_input_prefix . $name;
            if (array_key_exists($input_name, $_POST)) {
                $sanitized_value = filter_input(INPUT_POST, $input_name, FILTER_SANITIZE_STRING);
                update_post_meta($post_id, $input_name, $sanitized_value);
            }
        }
    }
}
