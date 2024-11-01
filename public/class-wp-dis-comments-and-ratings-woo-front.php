<?php

/**
 * The class is responsible for adding sections inside the WooCommerce product page on the front-end.
 *
 * @link       https://zamartz.com
 * @since      1.0.0
 *
 * @package    Wp_Woo_Dis_Comments_And_Ratings
 * @subpackage Wp_Woo_Dis_Comments_And_Ratings/admin
 */

/**
 * Functionality for front-end checkout page to hide/display fields based on defined
 * admin ruleset settings
 *
 *
 * @package    Wp_Woo_Dis_Comments_And_Ratings
 * @subpackage Wp_Woo_Dis_Comments_And_Ratings/admin
 * @author     Zachary Martz <zam@zamartz.com>
 */
class Woo_Disqus_Comments_Front
{
    /**
     * Incorporate the trait functionalities for Zamartz General in this class
     * @see     zamartz/helper/trait-zamartz-general.php
     */
    use Zamartz_General;

    /**
     * Disqus shortname that uniquely identifies the website
     *
     * @since   1.0.0
     * @access  public
     * @var     string  $disqus_shortcode_value     Disqus assigned shortcode
     */
    public $disqus_shortcode_value;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct($core_instance)
    {

        //Set plugin parameter information
        $this->set_plugin_data($core_instance);

        //Set plugin paid vs free information
        $this->set_plugin_api_data();

        //Get disqus global settings enable/disabled
        $this->disqus_shortcode_value = get_option("{$this->plugin_input_prefix}shortcode_value");

        if (empty($this->disqus_shortcode_value)) {
            return;
        }
        add_action('wp', array($this, 'woo_disqus_init'));
    }

    /**
     * Initialize Disqus comments logic on frontend on 'wp' hook
     */
    public function woo_disqus_init()
    {
        if (!has_filter('comments_template')) {
            return;
        }

        if (is_product() === true) {
            $this->woo_disqus_product_detail_init();
        } elseif ($this->plugin_api_version === 'Paid' && (is_shop() === true || is_product_category() === true)) {
            $this->woo_disqus_product_list_init();
        } else {
            return;
        }
    }

    /**
     * Initialize logic for product detail page
     */
    public function woo_disqus_product_detail_init()
    {
        $product_id = get_the_ID();
        //Disable 'Disqus comments system' script load
        $disable_disqus_product = get_post_meta($product_id, "{$this->plugin_input_prefix}disable_disqus_product", true);
        if ($disable_disqus_product === 'yes') {
            //Remove disqus enqueue and disable disqus altogether on the selected product
            add_action('wp_enqueue_scripts', array($this, 'woo_disqus_dequeue_scripts'), 100);
            return;
        }

        //Get enabled/disabled comments on product detail page
        $product_detail_toggle = get_option("{$this->plugin_input_prefix}product_detail_toggle");
        $show_in_detail = get_option("{$this->plugin_input_prefix}show_in_detail");

        //Check if disqus comments is enabled
        if ($product_detail_toggle === 'yes' && $show_in_detail === 'yes' && get_option('disqus_active') !== '0' && comments_open()) {
            $this->detail_comments_placement();
            add_action('wp_enqueue_scripts', array($this, 'woo_disqus_enqueue_scripts'));
            add_action('wp_enqueue_scripts', array($this, 'woo_disqus_dequeue_scripts'), 100);
        }

        //Get enabled/disabled comment count on product detail page
        $show_comment_count = get_option("{$this->plugin_input_prefix}show_comment_count");
        $comments_toggle = get_option("{$this->plugin_input_prefix}comments_toggle");

        //Check if disqus comment count is enabled on product detail page
        if ($this->plugin_api_version === 'Paid' && $comments_toggle === 'yes' && $show_comment_count === 'yes' && get_option('disqus_active') !== '0') {
            $this->comment_count_placement_detail();
        }
    }

    /**
     * Initialize logic for product lisitng page
     */
    public function woo_disqus_product_list_init()
    {
        //Get enabled/disabled comment count on product detail page
        $product_list_toggle = get_option("{$this->plugin_input_prefix}product_list_toggle");
        $show_in_list = get_option("{$this->plugin_input_prefix}show_in_list");

        //Check if disqus comment count is enabled on product listing page
        if ($product_list_toggle === 'yes' && $show_in_list === 'yes' && get_option('disqus_active') !== '0') {
            $this->list_placement_data = [];
            add_action('woocommerce_before_shop_loop_item', array($this, 'comment_count_placement_list_before'));
            add_action('woocommerce_after_shop_loop_item', array($this, 'comment_count_placement_list_after'));
        }
    }

    /**
     * Display Disqus comments on product detail page based on defined Woo Disqus admin settings.
     */
    public function detail_comments_placement()
    {
        add_action('the_post', array($this, 'remove_dsq_comments_template'));
        remove_action('pre_comment_on_post', 'dsq_pre_commment_on_post');

        //Define if custom location for placing comments is false
        $this->custom_detail_placement = false;

        //Get placement value
        $detail_placement = get_option("{$this->plugin_input_prefix}detail_placement");
        if ($this->plugin_api_version === 'Free') {
            $detail_placement = '';
        }
        switch ($detail_placement) {
            case 'replace_reviews':
                add_filter('woocommerce_product_tabs', array($this, 'woo_disqus_replace_review_tab'));
                break;
            case 'show_under_tabs':
                add_filter('woocommerce_product_after_tabs', array($this, 'woo_disqus_render_disqus_comments'));
                break;
            case 'show_under_summary':
                //Set priority to 9 to define prior to WooCommerce product tabs
                add_filter('woocommerce_after_single_product_summary', array($this, 'woo_disqus_render_disqus_comments'), 9);
                break;
            case 'custom':
                $this->custom_detail_placement = get_option("{$this->plugin_input_prefix}custom_insert_indentifier_detail_1");
                break;
            default:
                add_filter('woocommerce_product_tabs', array($this, 'woo_disqus_add_comment_tab'));
                break;
        }
    }

    /**
     * Display Disqus comment count on product detail page based on defined Woo Disqus admin settings.
     */
    public function comment_count_placement_detail()
    {
        $html = '';
        $is_replace = false;

        //Define if custom location for placing comments is false
        $custom_comment_count_placement = false;

        //Get placement value
        $comment_count_placement = get_option("{$this->plugin_input_prefix}comment_count_placement");
        switch ($comment_count_placement) {
            case 'below_product_title':
                add_action('woocommerce_single_product_summary', array($this, 'render_disqus_comment_count'), 9);
                break;
            case 'below_product_sku':
                $custom_comment_count_placement = '.product .sku_wrapper';
                break;
            case 'below_price':
                $custom_comment_count_placement = '.product .price';
                break;
            case 'custom':
                $custom_comment_count_placement = get_option("{$this->plugin_input_prefix}custom_insert_indentifier_detail_2");
                break;
            default:
                add_action('woocommerce_single_product_summary', array($this, 'render_disqus_comment_count'), 9);
                break;
        }

        if ($custom_comment_count_placement !== false) {
            ob_start();
            $this->render_disqus_comment_count();
            $html = ob_get_clean();
        }
        wp_enqueue_script('zamartz-disqus-comment-count-js', plugin_dir_url(__FILE__) . 'js/zamartz-disqus-count.js', array(), $this->version, true);
        wp_localize_script('zamartz-disqus-comment-count-js', 'woo_disqus_count_config_settings', array(
            'shortname' => $this->disqus_shortcode_value,
            'is_woo_list_page' => false,
            'is_replace' => $is_replace,
            'comment_placement' => $custom_comment_count_placement,
            'html' => $html
        ));
    }

    /**
     * Display Disqus comment count on product listing (shop) page based on defined Woo Disqus admin settings.
     */
    public function comment_count_placement_list_before()
    {
        global $product;
        $product_id = $product->get_id();

        $html = '';
        $is_replace = false;
        //Define if custom location for placing comments is false
        $custom_comment_count_placement = false;

        //Get placement value
        $list_placement = get_option("{$this->plugin_input_prefix}list_placement");
        switch ($list_placement) {
            case 'above_product_title':
                add_action('woocommerce_shop_loop_item_title', array($this, 'render_disqus_comment_count'), 9);
                break;
            case 'below_product_title':
                add_action('woocommerce_after_shop_loop_item_title', array($this, 'render_disqus_comment_count'), 9);
                break;
            case 'custom':
                $custom_comment_count_placement = '.post-' . $product_id . ' ' . get_option("{$this->plugin_input_prefix}custom_insert_indentifier_list");
                break;
            default:
                add_action('woocommerce_shop_loop_item_title', array($this, 'render_disqus_comment_count'), 9);
                break;
        }
        if ($custom_comment_count_placement !== false) {
            ob_start();
            $this->render_disqus_comment_count();
            $html = ob_get_clean();
        }
        $push_data['html'] = $html;
        $push_data['comment_placement'] = $custom_comment_count_placement;
        $push_data['is_replace'] = $is_replace;
        array_push($this->list_placement_data, $push_data);
    }

    public function comment_count_placement_list_after()
    {
        wp_enqueue_script('zamartz-disqus-comment-count-js', plugin_dir_url(__FILE__) . 'js/zamartz-disqus-count.js', array(), $this->version, true);
        wp_localize_script('zamartz-disqus-comment-count-js', 'woo_disqus_count_config_settings', array(
            'shortname' => $this->disqus_shortcode_value,
            'is_woo_list_page' => true,
            'list_placement_data' => $this->list_placement_data
        ));
    }

    /**
     * Block Disqus and use Reviews
     */
    public function remove_dsq_comments_template()
    {
        if (get_post_type() == 'product' && has_filter('comments_template', 'dsq_comments_template')) {
            remove_filter('comments_template', 'dsq_comments_template');
        }
    }

    /**
     * Replace review tab with Disqus comments on product detail page
     */
    public function woo_disqus_replace_review_tab($tabs)
    {
        unset($tabs['reviews']);

        // Adds the new tab
        $tabs['reviews'] = array(
            'title'     => __('Reviews' . '<span id="woo-disqus-review-comments"></span>', 'woocommerce'),
            'priority'  => 50,
            'callback'  => array($this, 'woo_disqus_render_disqus_comments')
        );
        return $tabs;
    }

    /**
     * Add New Tab For Disqus comments on product detail page
     */
    public function woo_disqus_add_comment_tab($tabs)
    {
        // Adds the new tab
        $tabs['comments'] = array(
            'title'     => __('Comments', 'woocommerce'),
            'priority'  => 50,
            'callback'  => array($this, 'woo_disqus_render_disqus_comments')
        );
        return $tabs;
    }

    /**
     * Render Disqus comment content
     */
    public function woo_disqus_render_disqus_comments()
    {
        echo '<div id="disqus_thread"></div>';
    }

    /**
     * Render Disqus comment count content
     */
    public function render_disqus_comment_count()
    {
        global $product;
        if (is_string($product)) {
            global $post;
            $product_id = $post->ID;
        } else {
            $product_id = $product->get_id();
        }

        $identifier = $this->disqus_shortcode_value . '-' . $this->woo_disqus_post_identifier($product_id);
        echo '<p class="disqus-comment-count" data-disqus-identifier="' . $identifier . '"></p>';
    }

    /**
     * Enqueue localize object
     */
    public function woo_disqus_enqueue_scripts()
    {

        global $post;

        $identifier = $this->disqus_shortcode_value . '-' . $this->woo_disqus_post_identifier($post->ID);
        wp_enqueue_script(
            'woo-disqus-front-js',
            plugin_dir_url(__FILE__) . 'js/zamartz-disqus-embed.js',
            array('jquery'),
            '1.0.0',
            false
        );
        wp_localize_script(
            'woo-disqus-front-js',
            'woo_disqus_config_settings',
            array(
                'post_id' => $post->ID,
                'title' => get_the_title($post->ID),
                'url' => get_permalink($post->ID),
                'shortname' => $this->disqus_shortcode_value,
                'identifier' => $identifier,
                'custom_detail_placement' => $this->custom_detail_placement
            )
        );
    }

    /**
     * Get the post identifier
     */
    public function woo_disqus_post_identifier($post_id)
    {
        $product = wc_get_product($post_id);
        $parent_product_id = $product->get_parent_id();
        if ($parent_product_id > 0) {
            $override_identifier_product = get_post_meta($parent_product_id, "{$this->plugin_input_prefix}override_identifier_product", true);
        } else {
            $override_identifier_product = get_post_meta($post_id, "{$this->plugin_input_prefix}override_identifier_product", true);
        }

        if ($this->plugin_api_version !== 'Free' && $override_identifier_product === 'yes' && $parent_product_id > 0) {
            $identifier_type = get_post_meta($parent_product_id, "{$this->plugin_input_prefix}post_identifier_product", true);
        } elseif ($this->plugin_api_version !== 'Free' && $override_identifier_product === 'yes' && $parent_product_id == 0) {
            $identifier_type = get_post_meta($post_id, "{$this->plugin_input_prefix}post_identifier_product", true);
        } else {
            $identifier_type = get_option("{$this->plugin_input_prefix}post_identifier_global");
        }

        if ($this->plugin_api_version === 'Free') {
            $identifier_type = '';
        }

        switch ($identifier_type) {
            case 'product_parent_sku':
                if ($parent_product_id > 0) {
                    $parent_product = wc_get_product($parent_product_id);
                    $value = $parent_product->get_sku();
                } else {
                    $value = $product->get_sku();
                }
                break;
            case 'variant_sku':
                if ($product->is_type('variable') && is_product()) {
                    $attributes = $product->get_variation_attributes();
                    $attribute_array = [];
                    $i = 0;
                    foreach ($attributes as $attribute_name => $options) {
                        $selected_key = 'attribute_' . sanitize_title($attribute_name);
                        $input_get_value = filter_input(INPUT_GET, $selected_key, FILTER_SANITIZE_STRING);
                        $attribute_array[$i]['key'] = $selected_key;
                        $attribute_array[$i]['value'] = $input_get_value;
                        $i++;
                    }
                    $args = array(
                        'post_type'  => 'product_variation',
                        'post_parent'  => $post_id,
                        'meta_query' => array(
                            'relation' => 'AND',
                            $attribute_array
                        ),
                    );
                    $variation_query = new WP_Query($args);
                    $variation_data = $variation_query->posts;
                    if (empty($variation_data)) {
                        $value = false;
                        break;
                    }
                    $product_variation_id = $variation_data[0]->ID;
                    if ($product_variation_id > 0) {
                        $variant_product = wc_get_product($product_variation_id);
                        $value = $variant_product->get_sku();
                    } else {
                        $value = false;
                    }
                } else {
                    if ($parent_product_id > 0) {
                        $variant_product = wc_get_product($post_id);
                        $value = $variant_product->get_sku();
                    } else {
                        $value = $post_id;
                    }
                }
                break;
            case 'product_wordpress_slug':
                $value = $product->get_slug();
                break;
            case 'manual_entry':
                $value = get_post_meta($post_id, "{$this->plugin_input_prefix}manual_post_identifier_product", true);
                break;
            default:
                $value = $post_id;
                break;
        }
        if ($value === false || $value == '') {
            return $post_id;
        }
        return $value;
    }

    /**
     * Dequeue all relevant scripts
     */
    public function woo_disqus_dequeue_scripts()
    {
        //Remove disqus enqueue
        wp_dequeue_script('disqus_count');
        wp_dequeue_script('disqus_embed');
    }
}
