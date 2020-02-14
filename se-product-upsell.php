<?php
/*
Plugin Name:    SE Product Upsell
Description:    Special Offer products before add to card button on single product page, by using Product Upsell list
Author: Sahak Tadevosyan
Version: 1.0.0
*/

/*
 * GX Codes
 */


define('SEPU_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SEPUVersion', '1.0.0');

if (!function_exists('i_print')) {
    function i_print($a)
    {
        echo '<pre>';
        print_r($a);
        echo '</pre>';
    }
}
register_activation_hook(__FILE__, 'create_upsell_statistics_table');
function create_upsell_statistics_table()
{
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'upsell_statistics';

    $sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  upsell_id smallint(5) NOT NULL,
		  views smallint(5) NOT NULL,
		  clicks smallint(5) NOT NULL,
		  date DATE DEFAULT '0000-00-00' NOT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";

    dbDelta($sql);

}


function i_load_resources()
{
    $req = array('jquery');

    wp_enqueue_style('fancybox_css', SEPU_PLUGIN_URL . 'resources/js/fancybox/jquery.fancybox.min.css', array(), null);
    wp_enqueue_script('fancybox_js', SEPU_PLUGIN_URL . 'resources/js/fancybox/jquery.fancybox.min.js', $req, null, true);
    //WOBS_PLUGIN_URL.'resources/style/admin_style.css
    wp_enqueue_style('sepu_style', SEPU_PLUGIN_URL . 'resources/style/front_style.css', array(), SEPUVersion . 'x', 'all');
    wp_enqueue_style('video', SEPU_PLUGIN_URL . 'assets/video.css', array());

    wp_enqueue_script('sepu-front-js', SEPU_PLUGIN_URL . 'resources/js/front_js.js', $req, SEPUVersion . 'x', true);
    wp_enqueue_script('video', SEPU_PLUGIN_URL . 'assets/video.js', $req);

    global $woocommerce;
    $cart_url = '/cart/';
    $checkout_url = '/checkout/';
    if (!empty($woocommerce)) {
        $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : $woocommerce->cart->get_cart_url();
        $checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : $woocommerce->cart->get_checkout_url();
    }

    wp_localize_script('sepu-front-js', 'sepu_info',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'plugin_url' => SEPU_PLUGIN_URL,
            'loadingmessage' => __('Sending info, please wait...'),
            'loading_img' => '<img src="' . SEPU_PLUGIN_URL . 'images/loading.gif" id="i_loading_img">',
            'site_url' => site_url(),
            'cart_url' => $cart_url,
            'checkout_url' => $checkout_url
        )
    );
}

add_action('wp_enqueue_scripts', 'i_load_resources');


add_action('admin_menu', 'register_newpage');

function register_newpage()
{
    add_menu_page('statistic-page', 'upsell_stats', 'administrator', 'upsell_stats', 'get_upsell_by_id');
    remove_menu_page('upsell_stats');
}

function get_upsell_by_id()
{
    global $wpdb;
    $stat_table = $wpdb->prefix . 'upsell_statistics';
    if (isset($_GET['upsell_id'])) {
        $id = $_GET['upsell_id'];
        $now = date("Y-m-d");

        $last_month = [];
        for ($d = 0; $d < 31; $d++) {
            $last_month[] = date('Y-m-d', strtotime('-' . $d . ' days'));
        }
        $db_data = $wpdb->get_results("SELECT * FROM $stat_table WHERE upsell_id=$id AND `date` BETWEEN '$last_month[30]' AND '$last_month[0]'");
        // echo '<div>'.$db_data.'</div>';
        $formated = [];
        foreach ($db_data as $data) {
            $formated[$data->date][] = $data;
        }

        $content = '';
        $content .= '<div class="stat_content">';
        $content .= '<h3>Product Upsell Statistic for last month</h3>';

        $content .= '<div class="tab_header">';
        $i = 0;
        $activeClass = 'active';
        foreach ($last_month as $day) {
            if ($i === 0) {
                $content .= '<button class="tab_button active"  data-index="' . $i . '">' . date('d/m', strtotime($day)) . '</button>';
            } else {
                $content .= '<button class="tab_button"  data-index="' . $i . '">' . date('d/m', strtotime($day)) . '</button>';
            }
            $i++;
        }
        $content .= '</div>';
        $content .= '<div class="tab_body">';
        $j = 0;
        foreach ($last_month as $day) {
            if ($j === 0) {
                $content .= '<div class="body_item active" data-tab="'.$j.'">';
            } else {
                $content .= '<div class="body_item" data-tab="'.$j.'">';
            }


            $content .= '<div><span class="item__text"  data-tab="'.$j.'" >Clicks</span>';
            if (isset($formated[$day])) {
                $content .= $formated[$day][0]->clicks;
            } else {
                $content .= 0;
            }
            $content .= '</div>';
            $content .= '<div><span class="item__text">Views</span>';
            if (isset($formated[$day])) {
                $content .= $formated[$day][0]->views;
            } else {
                $content .= 0;
            }
            $content .= '</div>';
            $content .= '</div>';
            $j++;
        }
        $content .= '</div>';


        $content .= '</div>';
        $content .= '
			<script>
				jQuery(document).ready(function ($) {
					jQuery(".tab_button").on("click",function () {
					    var activeIndex = $(this).data("index");
					    console.log(activeIndex);
                        jQuery(".tab_button").removeClass("active");
                        jQuery(this).addClass("active")
                        jQuery(".body_item").removeClass("active");
                        jQuery(".tab_body").find("[data-tab="+ activeIndex+"]").addClass("active")
                         console.log(activeIndex);
                    })
				});
			</script>';
        $content .= '
			<style>
				.tab_header{
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }
        .tab_button{
        cursor: pointer;
            background: #ffffff;
            border: 1px solid #aca5a5;
            padding: 5px 10px;
            outline: none;
            transition: all ease .3s;
        }
        .tab_button.active{
            background: #1e39ff;
            color: #ffffff;
            transition: all ease .3s;
        }
        .stat_content{
        min-height: 100vh;
        background: #ffffff;
        padding: 30px 10px;
        }
        .body_item{
        display: none;
        }
        .body_item.active{
        display: block;
        }
        .body_item{
        padding: 20px 10px;
        text-align: center;
        }
        .item__text {
    padding-right: 15px;
}
			</style>';
        echo $content;
    } else {
        echo '<div><h1 style="text-align: center">Nothing was found !</h1></div>';
    }

}

//Similiar function like wc_dropdown_variation_attribute_options(), which are return view instead of print
if (!function_exists('return_wc_dropdown_variation_attribute_options')) {
    function return_wc_dropdown_variation_attribute_options($args = array())
    {
        $args = wp_parse_args(apply_filters('woocommerce_dropdown_variation_attribute_options_args', $args), array(
            'options' => false,
            'attribute' => false,
            'product' => false,
            'selected' => false,
            'name' => '',
            'id' => '',
            'class' => '',
            'show_option_none' => pll__('Choose an option', 'woocommerce'),
        ));

        $options = $args['options'];
        $product = $args['product'];
        $attribute = $args['attribute'];
        $name = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title($attribute);
        $id = $args['id'] ? $args['id'] : sanitize_title($attribute);
        $class = $args['class'];
        $show_option_none = $args['show_option_none'] ? true : false;
        $show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : pll__('Choose an option', 'woocommerce'); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

        if (empty($options) && !empty($product) && !empty($attribute)) {
            $attributes = $product->get_variation_attributes();
            $options = $attributes[$attribute];
        }


        $html = '<select id="' . esc_attr($id) . '" class="' . trim(esc_attr($class)) . ' i_product_attribute" name="i_variation_' . esc_attr($name) . '" data-product_id="' . $product->get_id() . '" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '" data-show_option_none="' . ($show_option_none ? 'yes' : 'no') . '">';
        //$html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

        if (!empty($options)) {
            if ($product && taxonomy_exists($attribute)) {
                // Get terms if this is a taxonomy - ordered. We need the names too.
                $terms = wc_get_product_terms($product->get_id(), $attribute, array(
                    'fields' => 'all',
                ));

                foreach ($terms as $term) {
                    if (in_array($term->slug, $options, true)) {
                        $html .= '<option value="' . esc_attr($term->slug) . '" ' . selected(sanitize_title($args['selected']), $term->slug, false) . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $term->name)) . '</option>';
                    }
                }
            } else {
                foreach ($options as $option) {
                    // This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
                    $selected = sanitize_title($args['selected']) === $args['selected'] ? selected($args['selected'], sanitize_title($option), false) : selected($args['selected'], $option, false);
                    $html .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $option)) . '</option>';
                }
            }
        }

        $html .= '</select>';

        return apply_filters('woocommerce_dropdown_variation_attribute_options_html', $html, $args); // WPCS: XSS ok.
    }
}
// START get product price
function get_product_variation_price($variation_id)
{

    global $woocommerce;
    $product = new WC_Product_Variation($variation_id);
    /*$product = wc_get_product($variation_id);
    $price = $product->get_price();*/
    return $product->get_price_html(); // Works. Use this if you want the formatted price

}

// END get product price

//Ajax Requests
function i_special_offer_add_to_cart_multiple()
{
    global $wpdb;
    if (!(isset($_REQUEST['action']) && 'i_special_offer_add_to_cart_multiple' == $_POST['action']))
        return;


    $return = array(
        'status' => false,
        'html' => '<h3> There is no any Product request!!! </h3>'
    );

    $add_to_cart_items_data = $_POST['add_to_cart_items_data'];

    $products = $add_to_cart_items_data['products'];


    $return = array(
        'status' => false,
        'html' => '<h3> There is no any Product request!!! </h3>'
    );

    session_start();

    if ($products) {
        /*
        foreach ( $products as $product_id => $product_data ){
            //$product = wc_get_product( $product_id );
            $variations_vals = $product_data['i_product_attribute'];
            $product = new WC_Product_Variable($product_id);
            $product_qty = ( isset($product_data['qty']) && is_numeric($product_data['qty']))?$product_data['qty']:1;

            $variations = $product->get_available_variations();
            $variation_id = '';
            if ( count($variations) )
                $variation_id = $variations[0]['variation_id'];
            $variation_val = ($variatios_vals)?$variations_vals:'';

            if ( ! WC()->session->has_session() ) {
                WC()->session->set_customer_session_cookie( true );
            }
            //WC()->cart->empty_cart();
            WC()->cart->add_to_cart( $product_id, $product_qty, $variation_id, $variation_val );
        }
        */

        foreach ($products as $product_id => $product_data) {
            //$product = wc_get_product( $product_id );
            $variations_vals = $product_data['i_product_attribute'];
            $product = new WC_Product_Variable($product_id);
            $product_qty = (isset($product_data['i_product_qty']) && is_numeric($product_data['i_product_qty'])) ? $product_data['i_product_qty'] : 1;

            if ($product_qty > 1) {
                setcookie("woocommerce_want_multiple", "yes", time() + DAY_IN_SECONDS, "/", COOKIE_DOMAIN);
            }

            $variations = $product->get_available_variations();
            $variation_id = '';
            if (count($variations)) {
                $attribute_name = array_keys($variations_vals)[0];
                $variation_attributes = array();

                foreach ($variations as $variation) {
                    $variation_attributes[$variation['variation_id']] = $variation['attributes'][$attribute_name];
                }

                $variation_id = array_search($variations_vals[$attribute_name], $variation_attributes);

                //$att_array_name = array_keys($product->get_variation_attributes())[0];
                //$attribute_key = array_search ($variations_vals[$attribute_name], $product->get_variation_attributes()[$att_array_name]);

                //$variation_id = $variations[$attribute_key]['variation_id'];
                //$variation_id = $variations[0]['variation_id'];
            }

            $variation_val = ($variations_vals) ? $variations_vals : '';

            if (!WC()->session->has_session()) {
                WC()->session->set_customer_session_cookie(true);
            }

            WC()->cart->add_to_cart($product_id, $product_qty, $variation_id, $variation_val);

            unset($variation_attributes);
        }


        $ids = array_keys($_REQUEST['add_to_cart_items_data']['products']);

        foreach ($ids as $id) {
            add_to_cache($id, 'click');
        }
        $return = array(
            'status' => true,
            'html' => '<h3> Product added!!! </h3>'
        );
    }

    echo json_encode($return);
    exit;
}

add_action('wp_ajax_i_special_offer_add_to_cart_multiple', 'i_special_offer_add_to_cart_multiple');
add_action('wp_ajax_nopriv_i_special_offer_add_to_cart_multiple', 'i_special_offer_add_to_cart_multiple');

//////////////////////////////////////
//add_action( 'woocommerce_before_add_to_cart_button', 'i_special_offer_display', 5 );
add_action('woocommerce_after_add_to_cart_quantity', 'i_special_offer_display', 5);

global $already_special_offer_displayed;
$already_special_offer_displayed = 0;

function i_special_offer_display()
{
    global $already_special_offer_displayed;
    if ($already_special_offer_displayed)
        return;

    $already_special_offer_displayed = 1;

    global $post, $woocommerce;
    $user_country = $_SERVER["HTTP_CF_IPCOUNTRY"];


    $fields_add = 5;

    for ($i = 1; $i <= $fields_add; $i++) {
        $upsells[$i]['rank'] = get_post_meta($post->ID, '_se_upsell_' . $i . '_rank', true); // Added by Avetis
        $upsells[$i]['ids'] = (array)get_post_meta($post->ID, '_se_upsell_' . $i, true);
        $upsells[$i]['exclude'] = get_post_meta($post->ID, '_se_upsell_' . $i . '_exclude', true);
        $upsells[$i]['countries'] = get_post_meta($post->ID, '_se_upsell_' . $i . '_countries', true);
        $upsells[$i]['countries_array'] = ($upsells[$i]['countries']) ? array_map('trim', explode(',', $upsells[$i]['countries'])) : "";

    }

    //Added by Avetis
    $max = 0;

    for ($i = 1; $i <= $fields_add; $i++) {
        if ($max < $upsells[$i]['rank']) {
            $max = $upsells[$i]['rank'];   // getting max element
        }

    }
    $min = $max;

    for ($i = 1; $i <= $fields_add; $i++) {
        if ($min > $upsells[$i]['rank'] && $upsells[$i]['rank'] !== 0 && $upsells[$i]['rank'] !== '') {
            $min = $upsells[$i]['rank']; // getting min element
        }

    }

    for ($i = 1; $i <= $fields_add; $i++) {
        if ($upsells[$i]['rank'] == 0 || $upsells[$i]['rank'] == '') {
            $upsells[$i]['rank'] = rand($min, $max);    // change element rank from 0 and empty to random between min and max
        }
    }

    asort($upsells);  // sorting array by rank

    $rankArray = [];
    foreach ($upsells as $upsell) {
        array_push($rankArray, [(int)$upsell['ids'][0] => (int)$upsell['rank']]); // takes ranks and ids
    }

    $ranks = [];
    foreach ($rankArray as $key => $value) {
        foreach ($value as $keys => $values) {
            array_push($ranks, [$keys => $values]);
        }
    }

    $upsell_ids = array();
    foreach ($rankArray as $key => $value) {
        foreach ($value as $keys => $values) {
            array_push($upsell_ids, $keys);
        }
    }


    //    $i = 1;
    // foreach ($upsells as $upsell) {
    //     if ($upsell['exclude'] == "no") {
    //         if (is_array($upsell['countries_array'])) {
    //             if (in_array($user_country, $upsell['countries_array'])) {
    //                 $upsell_ids = array_merge($upsell_ids, $upsell['ids']);
    //             }
    //         } else {
    //             $upsell_ids = array_merge($upsell_ids, $upsell['ids']);
    //         }
    //     } else {
    //         if (is_array($upsell['countries_array'])) {
    //             if (!in_array($user_country, $upsell['countries_array'])) {
    //                 $upsell_ids = array_merge($upsell_ids, $upsell['ids']);
    //             }
    //         }
    //     }
    //     $i++;
    // }

    if (count($upsell_ids) == 0) {
        return;
    }

    $return = '';
    $products_variations = array();
    $products_variations_prices = array();

    $col_class = ' i_woo_item';

    if ($upsell_ids) {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => '',
            'post__in' => $upsell_ids,
            'orderby' => 'post__in',
            'order' => 'ASC'
        );
        $products = get_posts($args);
        //i_print($products);
        if (!empty($products)) {
            $return .= '<div class="smartency_woo_offered_items_div">';
            $return .= '<h5 class="upsell_title">' . pll__('Frequently Bought Together') . '</h5>';
            $count = 0;

            foreach ($products as $product) {

                $p_id = $product->ID;
                $p_title = $product->post_title;
                $permalink = get_permalink($p_id);

                $product = wc_get_product($p_id);
                if (trim($p_title) == '')
                    $p_title = $product->get_title();
                $price = $product->get_price_html();

                $p_title = trim(str_replace("(Bundle Special)", "", $p_title));

                $product_featured_image_id = get_post_thumbnail_id($p_id);
                $thumb_image = wp_get_attachment_image_src(get_post_thumbnail_id($p_id), 'shop_thumbnail', true);
                $thumb_url = $thumb_image[0];
                $not_first_item = ($count != 0) ? ' smartency_woo_not_first' : '';

                $return .= '<div class="smartency_woo_offered_item ' . $col_class . $not_first_item . '" id="smartency_woo_offered_item_' . $p_id . '">';

                $return .= '<div class="i_relative">';

                $return .= '<article id="post-' . $p_id . '" class="i_clearfix">';
                $return .= '<div class="smartency_woo_item_inner clearfix"> '; //<a href="'.$permalink.'" class="entry-featured-image-url">
                $return .= '<div class="i_product_checkbox_div i_fleft"> <div class="i_center"><div class="i_center_anchor"> <input type="checkbox" name="i_selected_offered_product" data-product_id="' . $p_id . '" class="i_selected_offered_product"> </div></div></div>';
                $return .= '<div class="i_image_container i_fleft"> <img src="' . $thumb_url . '" alt="" style="border-radius:10%" class="upsell_thumb_' . $p_id . '"></div>';


                $variable_return = '';
                $qty_class = '';
                $variable_return .= '<table class="i_variations" cellspacing="0"><tbody><tr>';

                if ($product->is_type('variable')) {


                    $attribute_keys = array_keys($product->get_attributes()); //i_print($product->get_variation_attributes());
                    if (empty($product->get_available_variations()) && false !== $product->get_available_variations()) {
                        $variable_return .= '<p class="stock out-of-stock">' . pll__('This product is currently out of stock and unavailable.', 'woocommerce') . '</p>';
                    } else {
                        //if( $product->get_id() == '29' ){
                        //$product->get_available_variations();
                        $product_variations = array();
                        $product_avail_vars = array();
                        $product_variations_data = $product->get_available_variations();

                        foreach ($product_variations_data as $product_variation_data) {
                            $product_variations[$product_variation_data['variation_id']] = $product_variation_data['attributes'];
                            $p_attribute_name = array_keys($product_variation_data['attributes'])[0];

                            $variation_images .= '"' . $p_id . "_" . $product_variation_data['attributes'][$p_attribute_name] . '": "' . wp_get_attachment_image_src(get_post_thumbnail_id($product_variation_data['variation_id']), 'shop_thumbnail', true)[0] . '", ';

                            $origin_var_name = str_replace("attribute_", "", $p_attribute_name);
                            if ($product_variation_data['is_in_stock']) {
                                $product_avail_vars[] = $product_variation_data['attributes'][$p_attribute_name];
                            }
                        }
                        //i_print($product_variations);i_print($product->get_variation_prices()['price']); //exit;
                        //}
                        //i_print($product_variations);
                        $products_variations[$p_id] = $product_variations;
                        $products_variations_prices[$p_id] = $product->get_variation_prices()['price'];

                        foreach ($product->get_variation_attributes() as $attribute_name => $options) {
                            $attributes_list = $product->get_variation_attributes();

                            $variable_return .= '<td class="label"><label for="' . sanitize_title($attribute_name) . '">' . wc_attribute_label($attribute_name) . '</label></td>';
                            $variable_return .= '<td class="value" style="padding-right: 5px">';
                            $selected = isset($_REQUEST['attribute_' . sanitize_title($attribute_name)]) ? wc_clean(urldecode($_REQUEST['attribute_' . sanitize_title($attribute_name)])) : $product->get_variation_default_attribute($attribute_name);
                            $variable_return .= return_wc_dropdown_variation_attribute_options(array('options' => $attributes_list[$attribute_name], 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected));
                            $variable_return .= end($attribute_keys) === $attribute_name ? apply_filters('woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . pll__('Clear', 'woocommerce') . '</a>') : '';
                            $variable_return .= '</td>';

                        }

                    }
                } else {
                    $qty_class = ' simple_prod_qty';
                }

                /** Qty HTML **/
                $qty_count_html = '';
                for ($i = 1; $i <= 10; $i++) {
                    $qty_count_html .= '<option value="' . $i . '">' . $i . '</option>';
                }

                $qty_html = '<td class="label sepu_qty_label' . $qty_class . '">
									<label for="sepu_qty" style="margin-right: 2px;">Qty</label>
								</td>
								<td class="value">
									<select id="sepu_qty" class="i_product_qty" name="i_variation_qty" data-product_id="' . $p_id . '" data-attribute_name="i_variation_qty" data-show_option_none="yes" style="min-width:38px;max-width:51px;">
										' . $qty_count_html . '
									</select>
								</td>';

                $variable_return .= $qty_html;
                $variable_return .= '</tr></tbody></table>';
                //i_print($products_variations);
                //file_put_contents('/home/admin/web/woodev.smartecom.io/public_html/debug_email.txt', "HTML: " . $variable_return . "\n", FILE_APPEND);

                $return .= '
				<div class="i_product_title_div i_fleft">
					<div class="i_center">
						<div class="i_center_anchor">
							<div class="i_product_info_badge_div i_fleft" style="float: right;">
								<div class="i_center">
									<a class="i_product_info_badge i_sepu_fancy_btn" href="#sepu_product_intro_' . $p_id . '" onclick="sendReq(' . $p_id . ')">i</a>
								</div>
							</div>
							<div class="i_product_price_div i_fleft" style="float: right;">
								<div class="i_center">
									<span class="i_product_price">' . $price . '</span>
								</div>
							</div>
							<div class="i_product_title_in">' . $p_title . '</div>'
                    . $variable_return . '
						</div>
					</div>
				</div>';

                $return .= '</div></article> ';
                $return .= '</div>';

                $return .= '</div>';

                // sepu_product_intro --
                $gallery_images = $product->get_gallery_image_ids();
                $return .= '<div style="display: none;"><div id="sepu_product_intro_' . $p_id . '" class="sepu_product_intro_div">';

                // upsell video image and url

                $video_thumb = get_post_meta($p_id, 'upsell_video_link_thumbnail', true);
                $video_thumb_url = get_post_meta($p_id, 'upsell_video_link', true);
                $video_id = substr($video_thumb_url, strpos($video_thumb_url, "?v=") + 3);
                $video_class = '';
                if ($video_thumb && $video_thumb_url) {
                    $video_class = 'video__active';
                }
                $return .= '<div class="sepu_product_intro_left_div col-md-6 ' . $video_class . '">';
                if ($video_thumb && $video_thumb_url) {
                    //var_dump($p_img_url);die();

                    $return .= '<div class="video__section"><iframe class="video__frame" width="420" height="315" frameborder="0"
                                  src="https://www.youtube.com/embed/' . $video_id . '?autoplay=0&enablejsapi=1&version=3">
                               </iframe></div>';
                }
                $p_img_url = wp_get_attachment_image_src($product_featured_image_id, 'shop_single')[0];
                $return .= '<div class="i_sepu_full_image_div fn_img_div"><img src="' . $p_img_url . '" class="i_sepu_full_image"></div>';


                if (!empty($gallery_images)) {
                    $return .= '<div class="sepu_product_intro_images_div i_row i_clearfix">';
                    //$p_img_url = wp_get_attachment_image_src( $product_featured_image_id, 'shop_single' )[0];
                    if ($video_thumb && $video_thumb_url) {
                        $return .= '<div class="sepu_prod_image_preview fn_img_div col-md-2" data-video_url="' . $video_id . '"><i class="iconic-woothumbs-icon iconic-woothumbs-icon-play"></i>';
                        $return .= '<img src="' . $video_thumb . '" class=""></div>';
                    }
                    $return .= '<div class="sepu_prod_image_preview fn_img_div col-md-2" data-image_url="' . $p_img_url . '">';

                    $return .= '<img src="' . $p_img_url . '" class=""></div>'; //shop_thumbnail shop_catalog shop_single

                    /*if (get_post_meta($p_id,'upsell_video_link_thumbnail',true)) {
                       $return.= '<div class="sepu_prod_image_preview fn_img_div col-md-2" data-image_url="'.get_post_meta($p_id,'upsell_video_link_thumbnail',true).'">';
                        $return.= get_post_meta($p_id,'upsell_video_link_thumbnail',true).'</div>';
                        }*/
                    foreach ($gallery_images as $gallery_image_id) {

                        $return .= '<div class="sepu_prod_image_preview fn_img_div col-md-2" data-image_url="' . wp_get_attachment_image_src($gallery_image_id, 'shop_single')[0] . '">';
                        $return .= wp_get_attachment_image($gallery_image_id, 'shop_single') . '</div>'; //shop_thumbnail shop_catalog shop_single
                    }

                    $return .= '</div>';
                }

                $return .= '</div>';
                $return .= '<div class="sepu_product_intro_right_div col-md-6">';
                $return .= '<div class="sepu_product_intro_title_div"><span class="preview_title">' . $p_title . '</span></div>';
                $return .= '<p class="sepu_product_intro_desc">' . mb_strimwidth($product->get_short_description(), 0, 110, '...') . '</p>';
                $return .= '<div class="sepu_product_intro_price_div sepu_product_intro_price_div_' . $p_id . '" > <span class="i_product_price">' . $price . '</span> </div>';

                $return .= '<div class="sepu_product_intro_additem_div">';
                $return .= '<button data-add_item="' . $p_id . '" class="sepu_product_intro_additem_btn">' . __('Add Item', 'sepu') . '</button>';
                $return .= '</div>';
                $return .= '</div>';
                //i_print($gallery_images);
                $return .= '</div></div>';
                // -- sepu_product_intro
            }

            $variation_images = rtrim($variation_images, ", ");

            $return .= '
			<script>
				jQuery(document).ready(function ($) {
					var pictureList = { ' . $variation_images . ' }; 
					
					$(".i_product_attribute").change(function () {
						var val = $(this).val();
						var prod_id = $(this).data("product_id");
						var thumbnail_val = prod_id + "_" + val;
						
						console.log(thumbnail_val);
						
						$(".upsell_thumb_" + prod_id).attr("src",pictureList[thumbnail_val]);
					});
					jQuery(".tab_button").on("click",function () {
                        jQuery(".tab_button").removeClass("active");
                        jQuery(this).addClass("active")
                    })
				});
			</script>
			';

            $return .= '</div>';
        }
        $return .= '<script> var offered_products_variations = ' . json_encode($products_variations) . '; ';
        $return .= 'var offered_products_variations_prices = ' . json_encode($products_variations_prices) . '; ';
        $return .= '</script>';

        echo $return;
    }
}

add_shortcode('se_product_upsell', 'se_product_upsell_shortcode');
function se_product_upsell_shortcode($atts)
{
    $options = shortcode_atts(array(
        'id' => '0',
    ), $atts);
}

/**
 * Add a custom product tab.
 */
function custom_product_tabs($original_tabs)
{
    $new_tab['giftcard'] = array(
        'label' => __('Addon Sales', 'woocommerce'),
        'target' => 'addon_sales_options',
        'class' => array('show_if_simple', 'show_if_variable'),
    );
    $insert_at_position = 10; // This can be changed
    $tabs = array_slice($original_tabs, 0, $insert_at_position, true); // First part of original tabs
    $tabs = array_merge($tabs, $new_tab); // Add new
    $tabs = array_merge($tabs, array_slice($original_tabs, $insert_at_position, null, true)); // Glue the second part of original
    return $tabs;
}

add_filter('woocommerce_product_data_tabs', 'custom_product_tabs');
/**
 * Contents of the gift card options product tab.
 */
function giftcard_options_product_tab_content()
{
    global $post;
    global $wpdb;
    $last_7_days = [];
    for ($d = 0; $d < 7; $d++) {
        $last_7_days[] = date('Y-m-d', strtotime('-' . $d . ' days'));
    }
    $stat_table = $wpdb->prefix . 'upsell_statistics';
    $stats = $wpdb->get_results("SELECT * FROM $stat_table WHERE `date` BETWEEN '$last_7_days[6]' AND '$last_7_days[0]'");

    $formated = [];
    foreach ($stats as $stat) {
        $formated[$stat->date][$stat->upsell_id][] = $stat;
    }
    // var_dump($formated);die();
    $fields_add = 5;

    // Note the 'id' attribute needs to match the 'target' parameter set above
    ?>
    <div id='addon_sales_options' class='panel woocommerce_options_panel'>
        <?php

        for ($i = 1; $i <= $fields_add; $i++) {

            ?>
            <div class='options_group'>
                <p class="form-field">
                    <label for="_se_upsell_<?php echo $i; ?>"><?php esc_html_e('Upsell ' . $i, 'woocommerce'); ?></label>
                    <select class="wc-product-search" multiple="multiple" style="width: 50%;"
                            id="_se_upsell_<?php echo $i; ?>" name="_se_upsell_<?php echo $i; ?>[]"
                            data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'woocommerce'); ?>"
                            data-action="woocommerce_json_search_products_and_variations"
                            data-exclude="<?php echo intval($post->ID); ?>">
                        <?php
                        $product_ids = (array)get_post_meta($post->ID, '_se_upsell_' . $i, true);


                        foreach ($product_ids as $product_id) {

                            $product = wc_get_product($product_id);
                            if (is_object($product)) {
                                echo '<option value="' . esc_attr($product_id) . '"' . selected(true, true, false) . '>' . wp_kses_post($product->get_formatted_name()) . '</option>';
                            }
                        }
                        ?>
                    </select> <?php echo wc_help_tip(__('Upsells are products which you recommend instead of the currently viewed product, for example, products that are more profitable or better quality or more expensive.', 'woocommerce')); // WPCS: XSS ok. ?>
                </p>

                <?php

                woocommerce_wp_checkbox(array(
                    'id' => '_se_upsell_' . $i . '_exclude',
                    'value' => get_post_meta($post->ID, '_se_upsell_' . $i . '_exclude', true),
                    'label' => __('Exclude Countries?', 'woocommerce'),
                    'desc_tip' => true,
                    'description' => __('Exclude countries? (If checked, it will exclude or it will include)', 'woocommerce'),
                ));
                woocommerce_wp_text_input(array(
                    'id' => '_se_upsell_' . $i . '_countries',
                    'value' => get_post_meta($post->ID, '_se_upsell_' . $i . '_countries', true),
                    'label' => __('Countries', 'woocommerce'),
                    'desc_tip' => 'true',
                    'description' => __('If empty, applies to all countries', 'woocommerce'),
                    'type' => 'text',
                ));
                // added by Avetis
                woocommerce_wp_text_input(array(
                    'id' => '_se_upsell_' . $i . '_rank',
                    'value' => get_post_meta($post->ID, '_se_upsell_' . $i . '_rank', true),
                    'label' => __('Rank', 'woocommerce'),
                    'desc_tip' => 'true',
                    'description' => __('If empty, add random number', 'woocommerce'),
                    'type' => 'text',
                ));
                ?>
                <!-- *Start* Adding upsell statistics to Admin page  -->
                <div class="form-field">
                    <label>Statistics</label>

                    <div class="tab">
                        <?php
                        foreach ($last_7_days as $day) {
                            echo '<button class="tablinks">' . date('d/m', strtotime($day)) . '</button>';
                        }
                        ?>
                    </div>
                    <?php
                    $block = '';
                    $block .= '<div class="tab__content">';
                    foreach ($last_7_days as $day) {


                        $block .= '<div class="tab__item">';
                        $block .= '<div><span class="item__text">Clicks</span>';
                        if (isset($formated[$day]) && isset($formated[$day][$product_ids[0]])) {
                            $block .= $formated[$day][$product_ids[0]][0]->clicks;
                        } else {
                            $block .= 0;
                        }
                        $block .= '</div>';
                        $block .= '<div><span class="item__text">Views</span>';
                        if (isset($formated[$day]) && isset($formated[$day][$product_ids[0]])) {
                            $block .= $formated[$day][$product_ids[0]][0]->views;
                        } else {
                            $block .= 0;
                        }
                        $block .= '</div>';
                        $block .= '</div>';

                    }

                    $block .= '</div>';
                    $block .= '<a href="' . menu_page_url('upsell_stats', true) . '&upsell_id=' . $product_ids[0] . '" target="_blank">Get last month statistics</a>';
                    echo $block;
                    ?>


                </div>
                <!-- *End* -->
            </div>
            <?php
        }
        ?>

    </div>
    <?php
}

add_action('woocommerce_product_data_panels', 'giftcard_options_product_tab_content');
/**
 * Save the custom fields.
 */
function save_se_upsell_option_fields($post_id)
{
    $fields_add = 5;

    for ($i = 1; $i <= $fields_add; $i++) {
        update_post_meta($post_id, '_se_upsell_' . $i, (array)$_POST['_se_upsell_' . $i]);
        $exclude[$i] = isset($_POST['_se_upsell_' . $i . '_exclude']) ? 'yes' : 'no';
        update_post_meta($post_id, '_se_upsell_' . $i . '_exclude', $exclude[$i]);
        update_post_meta($post_id, '_se_upsell_' . $i . '_countries', $_POST['_se_upsell_' . $i . '_countries']);
        update_post_meta($post_id, '_se_upsell_' . $i . '_rank', $_POST['_se_upsell_' . $i . '_rank']);                     // added by Avetis

    }
}

add_action('woocommerce_process_product_meta_simple', 'save_se_upsell_option_fields');
add_action('woocommerce_process_product_meta_variable', 'save_se_upsell_option_fields');
/**
 * Add a bit of style
 */
function wcpp_custom_style()
{
    ?>
    <style>
        #woocommerce-product-data ul.wc-tabs li.addon_sales_options a:before {
            font-family: WooCommerce;
            content: '\e600';
        }

        .woocommerce_options_panel input[type=text].short {
            width: 50% !important;
        }

        button.tablinks {
            padding: 8px 0;
            display: block;
            width: 100%;
            border: 1px solid #fff;
            background: #dedede91;
            cursor: pointer;
            outline: none;
        }

        div.form-field {
            padding: 5px 0 15px 162px;
        }

        .tab {
            display: flex;
            justify-content: flex-end;
        }

        .tab__content {
            display: flex;
        }

        .tab__item {
            width: 100%;
            padding: 10px;
            text-align: center;
        }

        button.tablinks.active {
            background: #1e39ff;
            color: #ffffff;
        }

    </style>
    <?php
}

add_action('admin_head', 'wcpp_custom_style');
define('UVU_DIR', rtrim(plugin_dir_path(__FILE__), '/'));
function vtfw_load_main_files()
{
    //To add tab at admin
    require_once(UVU_DIR . '/admin/video-tab-admin.php');
}

add_action('plugins_loaded', 'vtfw_load_main_files', 3);

function add_to_cache($id, $type)
{
    $type_2 = '';
    if ($type === 'click') {
        $type_2 = 'view';
    } else {
        $type_2 = 'click';
    }
    $upsellData = ['upsell_id' => $id, $type => 1, $type_2 => 0];
    $savedKeys = wp_cache_get("upsell_data", '', true);

    if ($savedKeys === false) {
        $savedKeys = [];
    }
    array_push($savedKeys, $upsellData);
    wp_cache_set("upsell_data", $savedKeys, '', $expire = 0);
}

function set_view()
{
    if ($_POST['upsell_id']) {

        // Adding upsell data to server cache

        add_to_cache($_POST['upsell_id'], 'view');
        echo 'View is cached !';
        die();

    } else {
        die();
    }


}

add_action('wp_ajax_nopriv_set_view', 'set_view');
add_action('wp_ajax_set_view', 'set_view');


function run_every_five_minutes()
{
    global $wpdb;

    $asd = wp_cache_get("upsell_data", '', true);
    $keys = array_unique(array_column($asd, 'upsell_id'));
    $aa = [];
    foreach ($keys as $key) {
        foreach ($asd as $item => $value) {

            if (in_array($key, $value)) {
                $aa[$key]['click'] ? $aa[$key]['click'] : 0;
                $aa[$key]['view'] ? $aa[$key]['view'] : 0;
                $aa[$key]['click'] = $aa[$key]['click'] + $value['click'];
                $aa[$key]['view'] = $aa[$key]['view'] + $value['view'];
            };
        }
    }
    $stat_table = $wpdb->prefix . 'upsell_statistics';
    $db_data = $wpdb->get_results("SELECT * FROM $stat_table WHERE DATE(date) = CURDATE()");
    // var_dump($db_data);die();
    if (!empty($db_data)) {
        foreach ($keys as $id) {
            $idx = array_search($id, array_column($db_data, 'upsell_id'));
            if (in_array($id, array_column($db_data, 'upsell_id'))) {
                $wpdb->update(
                    $stat_table,
                    array(
                        'clicks' => $aa[$id]['click'],
                        'views' => $aa[$id]['view'],
                        'date' => date("Y-m-d"),
                    ),
                    array('id' => $db_data[$idx]->id)
                );
            } else {

                $wpdb->insert(
                    $stat_table,
                    array(
                        'upsell_id' => $id,
                        'clicks' => $aa[$id]['click'],
                        'views' => $aa[$id]['view'],
                        'date' => date("Y-m-d"),
                    )
                );
            }
        }
    } else {
        foreach ($keys as $id) {
            $upsellData[$id] = ['id' => $id, 'views' => 1, 'clicks' => 1, 'date' => date("Y-m-d")];
            $wpdb->insert(
                $stat_table,
                array(
                    'upsell_id' => $id,
                    'clicks' => $aa[$id]['click'],
                    'views' => $aa[$id]['view'],
                    'date' => date("Y-m-d"),
                )
            );
        }
    }

}

if (!get_transient('every_5_minutes')) {
    set_transient('every_5_minutes', true, MINUTE_IN_SECONDS - 30);
    run_every_five_minutes();


    add_action('init', 'run_every_five_minutes');
}