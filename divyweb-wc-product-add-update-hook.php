<?php
/*
 * Plugin Name: DivyWeb WC Product Add/Update Hook
 * Plugin URI: http://www.divyweb.com
 * Description: Email the site admin when a woocommerce product is added/updated.
 * Version: 1.0
 * Author: Brian Murphy
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This plugin is a proof of concept based on a "Help Me Hour" question
 * at the Arizona WordPress meetup group. (northwest valley)
 *
 * "How do I notify customers when a new product is added to my store?"
 *
 * This demo will show that woocommerce products are handled as custom
 * post types. The site admin will be emailed when a product is added
 * or updated.
 *
 * Autosave is essentially unavoidable and modern wordpress does a lot
 * of revision autosaving that we want to ignore. Deregistering the
 * 'autosave' admin script doesn't solve the problem.
 *
 * A future programmer can replace the admin email action with something
 * more appropriate.
 */

function divyweb_new_wc_product($post_id, $post, $update) {
    /* Check that we are dealing with a published product or exit */
    if(get_post_type($post_id) != "product") {
        return;
    }
    if(get_post_status($post_id) != "publish")
        return;

    /* Autosave is unavoidable so check publish/modify dates for updates */
    if($post->post_modified_gmt == $post->post_date_gmt)
        $update = false;

    /* Ignore all of the automatic processing WP does */
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if(defined('DOING_AJAX') && DOING_AJAX)
        return;
    if(wp_is_post_revision($post_id))
        return;

    /* Get product details from the post ID */
    /* Ref: https://docs.woocommerce.com/wc-apidocs/class-WC_Product.html */
    $product   = wc_get_product($post_id);
    $prod_name = $product->get_title();
    $prod_url  = $product->get_permalink();

    /* Build the email */
    $admin_email = get_option('admin_email');
    if($update) {
        $subject = "Updated Product: $prod_name";
    } else {
        $subject = "New Product: $prod_name";
    }
    $message = "Learn more: $prod_url";

    /* Send the email */
    wp_mail($admin_email, $subject, $message);
}

add_action('save_post', 'divyweb_new_wc_product', 10, 3);
