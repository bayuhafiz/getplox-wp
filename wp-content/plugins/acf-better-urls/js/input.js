(function($){


	function initialize_field( $el ) {

		/**
		 *  Initialize select2 on all plugins select fields
		 */

		$el.find( 'select' ).select2();



		/**
		 *  Handle changing the type of URL using radio buttons
		 */

		$el.find( '.betterurls-radios input[type="radio"]' ).change(function() {
			var choice = $(this).val();

			if( 'media' == choice ) {
				$el.find('.betterurls-value').removeClass('active');
				$el.find('.betterurls-hash').addClass('hide');

				$el.find('.betterurls-media-wrapper').addClass('active');
			} else {
				$el.find('.betterurls-value').removeClass('active');
				$el.find('.betterurls-media-wrapper').removeClass('active');
				$el.find('.betterurls-hash').removeClass('hide');

				$el.find('.betterurls-' + $(this).val()).addClass('active');
			}
		});



		/**
		 *  Wordpress media uploader
		 */

		$el.find('.betterurls-media-button').click(function(e) {
			e.preventDefault();

			var file_frame, image_data;


			/**
			 *  If media uploader frame already exists then instead of creating
			 *  a new one just open the existing.
			 */

			if( undefined !== file_frame ) {
				file_frame.open();
				return;
			}


			/**
			 *  If media uploader frame doesn't exists - create one.
			 */

			file_frame = wp.media({
				frame: 'post',
				state: 'insert',
				multiple: false
			});


			/**
			 *  Handle insert event.
			 *  User selects an image and tries to insert it into the post.
			 */

			file_frame.on('insert', function() {
				var file = file_frame.state().get('selection').first().toJSON();

				$el.find('.betterurls-media-input').val(file.id);
				$el.find('.betterurls-media-url').text(file.url);
			});


			/**
			 *  Open / display the media uploader frame
			 */

			file_frame.open();
		});

	}


	if( typeof acf.add_action !== 'undefined' ) {

		/*
		*  These are 2 events which are fired during the page load
		*  ready = on page load similar to $(document).ready()
		*  append = on new DOM elements appended via repeater field
		*/

		acf.add_action('ready append', function( $el ){

			// search $el for fields of type 'betterurls'
			acf.get_fields({ type : 'betterurls'}, $el).each(function(){

				initialize_field( $(this) );

			});

		});


	} else {


		/*
		*  This event is triggered when ACF adds any new elements to the DOM.
		*/

		$(document).on('acf/setup_fields', function(e, postbox){

			$(postbox).find('.field[data-field_type="betterurls"]').each(function(){

				initialize_field( $(this) );

			});

		});


	}


})(jQuery);
