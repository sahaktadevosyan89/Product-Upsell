var stopVideo = function (element) {
    var iframe = element.querySelector('iframe');
    var video = element.querySelector('video');
    if (iframe) {
        var iframeSrc = iframe.src;
        iframe.src = iframeSrc;
    }
    if (video) {
        video.pause();
    }
};

var playVideo = function (element) {
    var iframe = element.querySelector('iframe');
    var video = element.querySelector('video');
    if (iframe) {
        var iframeSrc = iframe.src;
        iframe.src = iframeSrc;
    }
    if (video) {
        video.play();
    }
};

jQuery(document).ready(function ($) {

    $(document).on('click', '.sepu_prod_image_preview', function () {
        //$('.sepu_prod_image_preview').click( sepu_prod_image_preview );
        //function sepu_prod_image_preview(){
        if ($(this).data('video_url')) {

            $(this).parents('.sepu_product_intro_div').find('img.i_sepu_full_image').css({display: 'none'});
            $('.sepu_product_intro_left_div').addClass('video__active');
            console.log('played')
            playVideo(document);
        } else {

            $('.sepu_product_intro_left_div').removeClass('video__active');
            $(this).parents('.sepu_product_intro_div').find('img.i_sepu_full_image').css({display: 'inherit'});
            stopVideo(document);
        }
    });


})

function sendReq(id) {
    console.log('mtav')
    const query = "?upsell_id=" + id
    console.log(sepu_info.ajax_url)
    jQuery.post({
        url: sepu_info.ajax_url,
        method: "post",
        data: {
             action: "set_view",
            upsell_id: id
        },
        success: (data) => {
            // Close modal
            console.log(data);
        }
    })
}

/*
$.post(sepu_info.plugin_url, info).done(function (data) {
    data = JSON.parse(data);
    //console.log( data );
    if( data.status ){
        i_offered_products_sent = true;
        //$(this).find('input[type=submit]').click();
        if( $('.single_add_to_cart_button.loading').length ){
            $('.single_add_to_cart_button').removeClass('loading').click();
        } else {
            add_to_card_form.submit();
        }
    } else {
        alert( data.html );
        $('#i_loading').hide();
    }
});*/
