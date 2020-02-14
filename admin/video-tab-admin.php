<?php

// Register the video tab by hooking into the 'woocommerce_product_data_tabs' filter
add_filter('woocommerce_product_data_tabs', 'vtfw_add_product_video_tab');

if (!function_exists('vtfw_add_product_video_tab')) {

    function vtfw_add_product_video_tab($product_data_tabs)
    {

        $product_data_tabs['vtfw-video-tab'] = array(
            'label' => __('Product Video', 'video-tab-for-woocommerce'),
            'target' => 'vtfw_custom_video_tab_data',
        );

        return $product_data_tabs;
    }

}

// Call outputs like text boxes, select boxes, etc.
add_action('woocommerce_product_data_panels', 'vtfw_custom_video_tab_data_fields');

function vtfw_custom_video_tab_data_fields()
{
    $id = $_GET['post'];
    $meta = get_post_meta($id);
    ?>
    <div id='vtfw_custom_video_tab_data' class='panel woocommerce_options_panel'>
    <div class='options_group'>
        <?php


        woocommerce_wp_textarea_input(
            array(
                'id' => 'upsell_video_link',
                'label' => __('Video Source', 'video-tab-for-woocommerce'),
                'placeholder' => __('Enter video embed code or youtube/vimeo video url', 'video-tab-for-woocommerce'),
                'description' => __('', 'video-tab-for-woocommerce'),
                'style' => 'height:100px;',
            )
        );
        ?>
        <div class="full__with">
            <?php
            woocommerce_wp_text_input(
            array(
            'id' => 'upsell_video_link_thumbnail',
            'label' => __( 'Add image', 'video-tab-for-woocommerce' ),
            'placeholder' => __( 'Enter Url or select image', 'video-tab-for-woocommerce' ),
            'desc_tip' => 'true',
            'description' => __( '', 'video-tab-for-woocommerce' )
            )
            );

            ?>
            <!-- <input id="background_image" type="text" name="background_image" value="<?php /*echo $meta->upsell_video_link_thumbnail; */?>" />-->
            <input id="upload_image_button" type="button" class="button-primary" value="Insert Image"/>
        </div>

        <div class="video_image_bg" style="background: url('<?php echo $meta['upsell_video_link_thumbnail'][0]; ?>') no-repeat;    background-size: cover;"></div>
    </div>
    </div><?php

}

// Hook callback function to save custom fields information
add_action('woocommerce_process_product_meta', 'vtfw_custom_video_tab_save_data');


function vtfw_custom_video_tab_save_data($post_id)
{
    $allowed_tags = array(
        'em' => array(),
        'strong' => array(),
        'iframe' => array(
            'src' => array(),
            'height' => array(),
            'width' => array(),
            'frameborder' => array(),
            'allowfullscreen' => array(),
            'allow' => array(),
        ),
        'a' => array(
            'class' => array(),
            'href' => array(),
            'rel' => array(),
            'title' => array(),
        ),
        'p' => array(
            'class' => array(),
        ),
        'span' => array(
            'class' => array(),
        ),
        'h1' => array(
            'class' => array(),
        ),
        'h2' => array(
            'class' => array(),
        ),
        'h3' => array(
            'class' => array(),
        ),
        'h4' => array(
            'class' => array(),
        ),
        'h5' => array(
            'class' => array(),
        ),
        'h6' => array(
            'class' => array(),
        ),
    );

    // Save Textarea
    $upsell_video_link = $_POST['upsell_video_link'];
    $upsell_video_link_thumbnail = $_POST['upsell_video_link_thumbnail'];
    update_post_meta($post_id, 'upsell_video_link', wp_kses($upsell_video_link, $allowed_tags));
    update_post_meta($post_id, 'upsell_video_link_thumbnail', wp_kses($upsell_video_link_thumbnail, $allowed_tags));
}


//Add style to tab added for product video
add_action('admin_head', 'vtfw_custom_video_tab_style');

function vtfw_custom_video_tab_style()
{
    ?>
    <style>
        #woocommerce-product-data ul.wc-tabs li.vtfw-video-tab_tab a:before {
            font-family: Dashicons;
            content: "\f236";
        }

        .btn-primary {
            padding: 15px 30px;
            background: #1e39ff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
        }

        textarea#upsell_video_link {
            width: 73%;
        }

        .woocommerce_options_panel .add_product_images {
            text-align: right;
            margin-top: 20px;
        }

        .btn-primary:hover {
            color: #fff;
        }
        .full__with{
            display: flex;
            align-items: center;
        }
        .woocommerce_options_panel fieldset.form-field, .woocommerce_options_panel p.form-field {
            width: 100%;
        }
        .woocommerce_options_panel .short, .woocommerce_options_panel input[type=email].short, .woocommerce_options_panel input[type=number].short, .woocommerce_options_panel input[type=password].short, .woocommerce_options_panel input[type=text].short {
            width: 100%;
        }
        #vtfw_custom_video_tab_data  .options_group{
            padding: 30px;
        }
        .video_image_bg{
            width: 100px;
            height: 100px;
            background-size: cover;
        }
    </style>
    <script>
        jQuery(document).ready(function ($) {
            var mediaUploader;
            $('#upload_image_button').click(function (e) {
                e.preventDefault();
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                mediaUploader = wp.media.frames.file_frame = wp.media({
                    title: 'Choose Image',
                    button: {
                        text: 'Choose Image'
                    }, multiple: false
                });
                mediaUploader.on('select', function () {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#upsell_video_link_thumbnail').val(attachment.url);
                    $('.video_image_bg').css({background: "url('" + attachment.url +"') no-repeat",backgroundSize: 'cover'});
                });
                mediaUploader.open();
            });

            $('.tablinks').on('click',function (e) {
                e.preventDefault();
                $('.tablinks').removeClass('active');
                $(this).addClass('active');
            })
        });
    </script>
    <?php


}