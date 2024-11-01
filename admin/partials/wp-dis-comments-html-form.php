<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://zamartz.com
 * @since      1.0.0
 *
 * @package    Wp_Woo_Dis_Comments_And_Ratings
 * @subpackage Wp_Woo_Dis_Comments_And_Ratings/admin/partials
 */
?>
<style>
    .woocommerce #mainform .submit {
        display: none;
    }
</style>
<?php
$add_class = '';
if ($this->plugin_api_version === 'Free') {
    $add_class = ' plugin-free-version';
}
?>
<div class="zamartz-wrapper<?php echo $add_class; ?>" data-section_type="<?php echo $this->section_type; ?>" data-input_prefix="<?php echo $this->plugin_input_prefix  ?>">
    <div id="zamartz-message"></div>
    <?php
    ob_start();
    $this->get_product_detail_page_settings_html();
    $this->get_product_list_page_settings_html();
    $accordion_html = ob_get_clean();
    ob_start();
    $this->woo_disqus_get_sidebar_settings_html();
    $sidebar_accordion_html = ob_get_clean();
    $page_structure = array(
        array(
            'desktop_span' => '75',
            'mobile_span' => '100',
            'content' => $accordion_html
        ),
        array(
            'desktop_span' => '25',
            'mobile_span' => '100',
            'content' => $sidebar_accordion_html
        )
    );
    $page_content = array(
        'title' => 'Disqus Comments',
        'description' => __(
            "When options are configured, they will control how Disqus comments are shown on WooCommerce Pages",
            "wp-disqus-comments-ratings-woo"
        )
    );
    $this->generate_column_html($page_structure, $page_content);
    ?>
</div>