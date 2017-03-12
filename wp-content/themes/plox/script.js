var $ = jQuery.noConflict();

var Project = {

	init: function()
	{
		$('html').removeClass('no-js');
		Project.isMobile();
		Project.equalColumns();
		Project.slickSlider();
		Project.googleMaps();
		Project.mobileNav();
		Project.productVariations();
		Project.reviewScript();
		Project.customForms();
		Project.infiniteScroll();
		Project.mobileScripts();
		Project.checkoutShipping();
		Project.socialPopup();
		Project.woocommerceCheckout();
	},
	isMobile: function() {
		if( $('.menu-trigger').is(':visible') ) {
			return true;
		} else {
			return false;
		}
	},
	equalColumns : function() {
		if ($('.half-boxes').length) {
			$('.half-boxes .half-box-single').evenColumns (
				{
					columns: 2
				}

			)
		}
		if ($('.promo-products-wrapper').length) {
			$('.promo-products-wrapper .single-item').evenColumns (
				{
					columns: 4
				}

			)
		}
		if ($('.products-list-wrapper').length) {
			$('.products-list-wrapper .product').evenColumns (
				{
					columns: 4
				},
				{
					maxScreenWidth: 980,
					columns: 3
				},
				{
					maxScreenWidth: 750,
					columns: 2
				}

			)
		}
	},
	slickSlider : function() {

		$('.product-slider').slick({

			slidesToShow: 4,
			slidesToScroll: 3

		});

	},
	googleMaps : function() {
		/*
		*  new_map
		*
		*  This function will render a Google Map onto the selected jQuery element
		*
		*  @type	function
		*  @date	8/11/2013
		*  @since	4.3.0
		*
		*  @param	$el (jQuery element)
		*  @return	n/a
		*/

		function new_map( $el ) {

			// var
			var $markers = $el.find('.marker');


			// vars
			var args = {
				zoom		: 15,
				center		: new google.maps.LatLng(0, 0),
				mapTypeId	: google.maps.MapTypeId.ROADMAP
			};


			// create map
			var map = new google.maps.Map( $el[0], args);


			// add a markers reference
			map.markers = [];


			// add markers
			$markers.each(function(){

		    	add_marker( $(this), map );

			});


			// center map
			center_map( map );


			// return
			return map;

		}

		/*
		*  add_marker
		*
		*  This function will add a marker to the selected Google Map
		*
		*  @type	function
		*  @date	8/11/2013
		*  @since	4.3.0
		*
		*  @param	$marker (jQuery element)
		*  @param	map (Google Map object)
		*  @return	n/a
		*/

		function add_marker( $marker, map ) {

			// var
			var latlng = new google.maps.LatLng( $marker.attr('data-lat'), $marker.attr('data-lng') );

			// create marker
			var marker = new google.maps.Marker({
				position	: latlng,
				map			: map
			});

			// add to array
			map.markers.push( marker );

			// if marker contains HTML, add it to an infoWindow
			if( $marker.html() )
			{
				// create info window
				var infowindow = new google.maps.InfoWindow({
					content		: $marker.html()
				});

				// show info window when marker is clicked
				google.maps.event.addListener(marker, 'click', function() {

					infowindow.open( map, marker );

				});
			}

		}

		/*
		*  center_map
		*
		*  This function will center the map, showing all markers attached to this map
		*
		*  @type	function
		*  @date	8/11/2013
		*  @since	4.3.0
		*
		*  @param	map (Google Map object)
		*  @return	n/a
		*/

		function center_map( map ) {

			// vars
			var bounds = new google.maps.LatLngBounds();

			// loop through all markers and create bounds
			$.each( map.markers, function( i, marker ){

				var latlng = new google.maps.LatLng( marker.position.lat(), marker.position.lng() );

				bounds.extend( latlng );

			});

			// only 1 marker?
			if( map.markers.length == 1 )
			{
				// set center of map
			    map.setCenter( bounds.getCenter() );
			    map.setZoom( 15 );
			}
			else
			{
				// fit to bounds
				map.fitBounds( bounds );
			}

		}

		/*
		*  document ready
		*
		*  This function will render each map when the document is ready (page has loaded)
		*
		*  @type	function
		*  @date	8/11/2013
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/
		// global var
		var map = null;



		$('.acf-map').each(function(){

			// create map
			map = new_map( $(this) );

		});


	},
	mobileNav : function() {

		$('.menu a[href="#"]').on('click', function(e){
			e.preventDefault();
		});
		$('.menu-trigger').click(function(){
			$(this).toggleClass('open');
		});
		$('.mobile-nav .menu-item-has-children').each(function(){
			$(this).append('<a href="#" class="submenu-trigger"></a>')
		});
		$('.submenu-trigger').on('click', function(e){
			e.preventDefault();
			if (!$(this).hasClass('active')){
				if($('.sub-menu.active').length > 0) {
					$('.sub-menu.active').removeClass('active');
					$('.submenu-trigger.active').removeClass('active');
				}
				$(this).addClass('active');
				$(this).prev().addClass('active');
			} else{
				$(this).removeClass('active');
				$(this).prev().removeClass('active');
			}
		});
	},
	productVariations : function() {

		if ($('.single-product').length !== 0 ){
			var currColor = $('.variation_button.selected').attr('title'),
				sku = $('span.sku').html();

			setTimeout(function(){
			if ($('.product_dimensions').length !== 0 ){
				var dim = $('.product_dimensions').html();
				dims = dim.replace(/x/g,"mm x");

				if(dim.indexOf('mm') !== -1){
					$('.product_dimensions').html(dims);
				}
			}

			if ($('.product_weight').length !== 0 ){

				var wei = $('.product_weight').html();
				weig = wei.replace(/g/g,"grams");

				if(wei.indexOf('g') !== -1){
					$('.product_weight').html(weig);
				}
			}

			 }, 200);

			if ($('.variation_button').length !== 0 ){
				$('.woocommerce-breadcrumb').append(' <span class="choosen-color">' + currColor + '</span>');
			}

			if ($('span.sku').length !== 0 ){
				$('.woocommerce-breadcrumb').append(' <span class="breadcrumbs-sku">' + sku + '</span>');
			}

			$('.variation_buttons').on('click', 'a', function(){
				var color = $(this).attr('title');

				setTimeout(function(){

					var newSku = $('span.sku').html();
					$('.choosen-color').remove();
					$('.breadcrumbs-sku').remove();

					$('.woocommerce-breadcrumb').append(' <span class="choosen-color">' + color + '</span>');


					$('.woocommerce-breadcrumb').append(' <span class="breadcrumbs-sku">' + newSku + '</span>');
					$('.product_sku').html(newSku);

					if($('.variation_button.selected').length === 0){
						$('.choosen-color').remove();
					}

					var dim = $('.product_dimensions').html();
					dims = dim.replace(/x/g,"mm x");

					if(dim.indexOf('mm') !== -1 && dim.indexOf('mm x') === -1){
						$('.product_dimensions').html(dims);
					}

					var wei = $('.product_weight').html();
					weig = wei.replace(/g/g,"grams");

					if(wei.indexOf('g') !== -1 && wei.indexOf('grams') === -1){
						$('.product_weight').html(weig);
					}

				 }, 200);
			});
		}

	},
	reviewScript : function() {
		var form = $('#review_form_wrapper'),
			btn = $('.write-btn');

		btn.on('click', function(){
			if(!$(this).hasClass('active')){
				$(this).addClass('active');
				$(form).addClass('open');
			} else{
				$(this).removeClass('active');
				$(form).removeClass('open');
			}
		});
	},
	customForms : function() {

		var mySelect = $('.product-list-page select');
		$(mySelect).fancySelect({includeBlank: true});
		mySelect.fancySelect().on('change.fs', function() {
		    $(this).trigger('change.$');
		});


		$('.fancy-select .trigger').each(function(){
			if($(this).parents('.berocket_aapf_widget').prev('.widget-title').length !== 0){
				var title = $(this).parents('.berocket_aapf_widget').prev('.widget-title').find('span').html();
				$(this).html(title);
			}
		});



	},
	infiniteScroll : function() {
	  $('.products').infinitescroll({

	    navSelector  : ".woocommerce-pagination",
	                   // selector for the paged navigation (it will be hidden)
	    nextSelector : ".woocommerce-pagination a.next",
	                   // selector for the NEXT link (to page 2)
	    itemSelector : ".products .product",
	    extraScrollPx: 50
	  });
	},
	mobileScripts : function() {
		var summary = $('.entry-summary'),
			info = $('.main-product-info')
			spec = $('.specification-wrapper');

		if ($('.single-product').length !== 0){
			if(!$('.short-description').is(':visible')){
				$('form.cart').after(info);
			} else{
				summary.after(info);
			}
		}
	},
	checkoutShipping : function() {
		$('.above-address').on('click', function(){
			if($('.shipping_address').is(':visible')){
				$('.shipping_address').slideUp();
			}
		});
	},
	socialPopup : function() {
		$('.close-social').on('click', function(e){
			e.preventDefault();
			$('.social-popup').hide();
		});
		$('.share-trigger').on('click', function(e){
			e.preventDefault();
			$('.social-popup').show();
		});
		$(document).mouseup(function (e)
		{
			if($('.social-popup').is(':visible')){
			    var container = $(".popup-content");

			    if (!container.is(e.target) // if the target of the click isn't the container...
			        && container.has(e.target).length === 0) // ... nor a descendant of the container
			    {
			        $('.social-popup').hide();
			    }
			}

		});
	},
	woocommerceCheckout : function() {
	    $('.woocommerce-checkout .woocommerce-billing-fields input').on('change', function(){
	    	var name = $(this).attr('name');
	    	var name = name.replace('billing', 'shipping');
	    	var val = $(this).val();

	    	var same = $('.shipping_address [name="' + name + '"]');

	    	if(same.length > 0){
		    
		    	same.val(val);
		    
	    	}

	    });
	    $('.woocommerce-checkout .woocommerce-billing-fields select').on('change', function(){
			var name = $(this).attr('name');
			var name = name.replace('billing', 'shipping');  	
			var val = $(this).val();

			var same = $('.shipping_address [name="' + name + '"]');
	    	if(same.length > 0){
		    
		    	same.val(val).trigger("change");;
		    
	    	}
	    });    
	}

}

$(document).ready(function() {
	Project.init();
    $(window).resize(function() {
        Project.mobileScripts();
    });
});
