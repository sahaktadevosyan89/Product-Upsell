jQuery(document).ready(function ($) {

    $('.i_sepu_fancy_btn').fancybox({
		clickOutside: "close",
		touch: false,
    });

	$('.i_selected_offered_product').change( i_selected_product_changed );
	function i_selected_product_changed(){
		if( $(this).is(':checked') ){
			$(this).parents('.smartency_woo_offered_item').addClass('i_active_product');
		} else {
			$(this).parents('.smartency_woo_offered_item').removeClass('i_active_product');
		}

		//i_calculate_total_price();
	}
           $('.i_selected_offered_product').change( i_selected_product_changed );
            function i_selected_product_changed(){
                if( $(this).is(':checked') ){
                    $(this).parents('.smartency_woo_offered_item').addClass('i_active_product');
                } else {
                    $(this).parents('.smartency_woo_offered_item').removeClass('i_active_product');
                }

                //i_calculate_total_price();
            }
            $('.i_product_attribute').change( i_calculate_total_price );
            var offered_products_total = 0;
		
		/*
            var primary_price_el = $('.summary.entry-summary .price .amount');
			
            if( $('.summary.entry-summary .price ins .amount').length )
                primary_price_el = $('.summary.entry-summary .price ins .amount');

            var price_symbol = $('.woocommerce-Price-currencySymbol').first().text();
			
			//console.log("primary_product_price1 = " + primary_price_el.text());
			//console.log("primary_product_price1.5 = " + primary_price_el.text().replace(price_symbol,'').replace(/,/g,''));
			
            var primary_product_price = Number( primary_price_el.text().replace(price_symbol,'').replace(/,/g,'') );

			//console.log("primary_product_price2 = " + primary_product_price);

            $('.summary .variations_form.cart:first .variations').on('change', 'select', function(){

                setTimeout(function(){
                    if( $('.summary.entry-summary .single_variation_wrap .price .amount').length ){
                        primary_price_el = $('.summary.entry-summary .single_variation_wrap .price .amount');

                        if( $('.summary.entry-summary .single_variation_wrap .price ins .amount').length )
                            primary_price_el = $('.summary.entry-summary .single_variation_wrap .price ins .amount');
                    }

                    primary_product_price = Number( $('.summary.entry-summary .single_variation_wrap .price ins .amount').text().replace(price_symbol,'').replace(/,/g,'') );
                    //console.log("primary_product_price_timeout = "+primary_product_price);
                }, 500 );
            });

            $('.summary .variations_form.cart:first .variations select').first().change();
		*/
            function i_addCommas(nStr) {
                nStr += '';
                var x = nStr.split('.');
                var x1 = x[0];
                var x2 = x.length > 1 ? '.' + x[1] : '';
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                    x1 = x1.replace(rgx, '$1' + ',' + '$2');
                }
                return x1 + x2;
            }


            function i_calculate_total_price(){ //console.log('primary_product_price='+primary_product_price);
                offered_products_total = 0;

                price_symbol = $('.woocommerce-Price-currencySymbol').first().text();
                var c_product_price = 0;

                $('.smartency_woo_offered_item.i_active_product').each( function( index, el ){
                    c_product_price = 0;
                    var c_product_id = $(el).find('.i_selected_offered_product').data('product_id');
                    if( $(el).find('.i_variations').length ){
                        var offered_product_variations = offered_products_variations[c_product_id];

                        var i_variations_el = $(el).find('.i_variations');
                        var variation_found = false;
                        var found_n_max = 0;

                        $.each(offered_product_variations, function(var_index, var_value) {
                            //console.log(var_index);
                            //console.log(var_value);

                            /*if( variation_found )
                                return;*/

                            var found_i = 0;
                            var found_n = 0;
                            $.each(var_value, function(opt_index, opt_value) {
                                //console.log(opt_index);
                                //console.log(opt_value);

                                if( opt_value ){
                                    if ( i_variations_el.find('select[name=i_variation_'+opt_index+']').val() == opt_value ){
                                        found_i++;
                                        found_n ++; //console.log(opt_value+' found');
                                    } else {
                                        found_i--;
                                    }
                                } else {
                                    found_i++; //console.log(opt_value+' found');
                                }
                            });
                            //console.log(found_i);
                            if( found_i == Object.keys(var_value).length ){

                                //console.log('found variation '+var_index);
                                //console.log(var_value);

                                if( found_n >= found_n_max ){
                                    found_n_max = found_n;
                                    variation_found = true;
                                    c_product_price = Number( offered_products_variations_prices[c_product_id][var_index] );
                                }
                            }

                        });

                    } else {
                        if( $('.i_product_price_div_'+c_product_id).find('.i_product_price ins').length ){
                            c_product_price = Number( $('.i_product_price_div_'+c_product_id).find('.i_product_price ins .amount').text().replace(price_symbol,'').replace(/,/g,'') )
                        } else {
                            c_product_price = Number( $('.i_product_price_div_'+c_product_id).find('.i_product_price .amount').text().replace(price_symbol,'').replace(/,/g,'') )
                        }

                    }
                    offered_products_total+= c_product_price;
                    if( $('.i_product_price_div_'+c_product_id).find('.i_product_price ins').length ){
                        $('.i_product_price_div_'+c_product_id).find('.i_product_price ins').html( '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">'+price_symbol+'</span>'+i_addCommas( c_product_price.toFixed(2) )+'</span>');
                    } else {
                        $('.i_product_price_div_'+c_product_id).find('.i_product_price').html( '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">'+price_symbol+'</span>'+i_addCommas( c_product_price.toFixed(2) )+'</span>');
                    }


                });
				/*
                var full_total = primary_product_price + Number( offered_products_total );
                if( $('.summary .variations_form.cart').length ){
                    primary_price_el.html('<span class="woocommerce-Price-currencySymbol">'+price_symbol+'</span>'+ i_addCommas( full_total.toFixed(2) ) );
                } else {
                    primary_price_el.html('<span class="woocommerce-Price-currencySymbol">'+price_symbol+'</span>'+ i_addCommas( full_total.toFixed(2) ) );
                    //$('.summary.entry-summary .price .amount')
                }
				*/
            }
			
            $('.smartency_woo_offered_item').click(function(evt){
				var target = $(evt.target);
				
				if (target[0].nodeName == 'IMG'){
				//if (target.attr('class').is('[class^="upsell_thumb"]')){
					$(this).find('.i_product_info_badge').click();
				}
				
				if( !target.hasClass('i_selected_offered_product') && !target.is( "select" ) && !target.hasClass('i_product_info_badge') && target[0].nodeName != 'IMG' ){
					$(this).find('.i_selected_offered_product').click(); //$(this).find('.i_selected_offered_product').click();
				}
            });
			
			/*
            $('.smartency_woo_offered_item').click(function(evt){
                var target = $(evt.target);
                if( !target.hasClass('i_selected_offered_product') && !target.is( "select" ) && !target.hasClass('i_image_container') ){
                    $(this).find('.i_product_info_badge').click();
				}
				
				if (target.hasClass('i_image_container')){
					$(this).find('.i_selected_offered_product').click();
				}
            });
			*/

            var i_offered_products_sent = false;

            $('.variations_form, .single-product form.cart').submit( check_submit_offered_products );
            function check_submit_offered_products(e){
                if( $(this).find( '.i_selected_offered_product:checked' ).length && !i_offered_products_sent ){
                    var add_to_card_form = $(this);
                    e.preventDefault;
                    var product_id = '';
                    var add_to_cart_items_data = {
                        'products': {}
                    };
                    var i_qty = $(this).find('input[name=quantity]').val();

                    $(this).find( '.i_selected_offered_product:checked' ).each(function(index, el){
                        if ( $(el).is(':checked') ) {
                            product_id = $(el).data('product_id');
                            i_product_attribute = {};
                            $(el).parents('.smartency_woo_offered_item').find('.i_product_attribute').each(function(var_index, var_el){
                                if( $(var_el).val() ){
                                    i_product_attribute[$(var_el).attr('name').replace('i_variation_','')] = $(var_el).val();
                                }
                            });

                            i_product_qty = $(el).parents('.smartency_woo_offered_item').find('.i_product_qty').val();
							
                            if( typeof add_to_cart_items_data['products'][product_id] == "undefined"  ){
                                add_to_cart_items_data['products'][product_id] = {};
                            }
                            add_to_cart_items_data['products'][product_id] = {
                                product_id: product_id,
                                i_product_attribute: i_product_attribute,
								i_product_qty: i_product_qty,
                                qty: i_qty
                            };
                        }
                    });

                    //console.log( add_to_cart_items_data );

                    var info = {};
                    info['action'] = 'i_special_offer_add_to_cart_multiple';
                    info['add_to_cart_items_data'] = add_to_cart_items_data;

                    $('#i_loading').show();
                    //console.log( info );
                    $.post(sepu_info.ajax_url, info).done(function (data) {
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
                    });

                    e.preventDefault();
                    return false;
                }
            }
	
	$(document).on('click', '.sepu_prod_image_preview', function () {
    //$('.sepu_prod_image_preview').click( sepu_prod_image_preview );
    //function sepu_prod_image_preview(){
        var c_src = $(this).data('image_url');
		console.log(c_src);
        $(this).parents('.sepu_product_intro_div').find('img.i_sepu_full_image').attr('src', c_src);
    });
	
    $('.sepu_product_intro_additem_btn').click( sepu_product_intro_additem );
    function sepu_product_intro_additem(){
        var p_id = $(this).data('add_item');
        var sp_el = $('#smartency_woo_offered_item_'+p_id);
        if( !sp_el.hasClass('i_active_product') ){
            sp_el.find('.i_selected_offered_product').click();
        }
        $.fancybox.close();
        return false;
    }
});