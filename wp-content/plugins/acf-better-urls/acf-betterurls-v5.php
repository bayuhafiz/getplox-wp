<?php

/**
 *   Bandaid for the function that was removed in ACF 5.7.
 */

if( !function_exists('acf_force_type_array') ) {
	function acf_force_type_array( $var ) {
	
		// is array?
		if( is_array($var) ) {
		
			return $var;
		
		}
		
		
		// bail early if empty
		if( empty($var) && !is_numeric($var) ) {
			
			return array();
			
		}
		
		
		// string 
		if( is_string($var) ) {
			
			return explode(',', $var);
			
		}
		
		
		// place in array
		return array( $var );
	} 
}


class acf_field_betterurls extends acf_field {


	/*
	*  This function will setup the field type data
	*/

	function __construct()
	{
		$this->name = 'betterurls';
		$this->label = __('Better URLs', 'acf');
		$this->category = 'relational';
		$this->defaults = array(
			'def_type' => 'internal',
			'post_type' => array()
		);


		// do not delete!
    	parent::__construct();
	}


	/*
	*  Create extra settings for your field. These are visible when editing a field
	*/

	function render_field_settings( $field ) {

		acf_render_field_setting( $field, array(
			'label'			=> __( 'Default', 'acf' ),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'def_type',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'internal'	=> __( 'Existing Object', 'acf' ),
				'external'	=> __( 'Custom URL', 'acf' ),
				'media'     => __( 'Media', 'acf' )
			)
		));


		acf_render_field_setting( $field, array(
			'label'			=> __('Filter by Post Type','acf'),
			'instructions'	=> 'Applies only to internal URLs',
			'type'			=> 'select',
			'name'			=> 'post_type',
			'choices'		=> acf_get_pretty_post_types(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All post types",'acf'),
		));


		acf_render_field_setting( $field, array(
			'label'			=> __( 'Return as', 'acf' ),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'return_as',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'url'	    => __( 'URL', 'acf' ),
				'array'	    => __( 'Array', 'acf' )
			)
		));

	}



	/*
	*  Create the HTML interface for your field
	*/

	function render_field( $field )
	{
		/**
		 *  Prepare values for field rendering.
		 */


		/**
		 *  Make sure the values are being saved as an array
		 */

		$field['name'] .= '[]';


		/**
		 *  Add class to ul making the choices list horizontal
		 */

		$field['class'] .= ' acf-radio-list acf-hl';


		/**
		 *  Prepare HTML attributes for internal (existing object) radio
		 */

		$atts_int = array(
			'type' => 'radio',
			'id' => $field['id'] . '_int',
			'name' => $field['name'],
			'value' => 'internal'
		);

		if( 'internal' == $field['value'][0] )
			$atts_int['checked'] = 'checked';

		if ($field['value'][0] != 'internal' && $field['value'][0] != 'external' && $field['value'][0] != 'media' && $field['def_type'] == 'internal')
			$atts_int['checked'] = 'checked';


		/**
		 *  Prepare HTML attributes for external (custom) URL radio
		 */

		$atts_ext = array(
			'type' => 'radio',
			'id' => $field['id'] . '_ext',
			'name' => $field['name'],
			'value' => 'external'
		);

		if( 'external' == $field['value'][0] )
			$atts_ext['checked'] = 'checked';

		if ($field['value'][0] != 'internal' && $field['value'][0] != 'external' && $field['value'][0] != 'media' && $field['def_type'] == 'external')
			$atts_ext['checked'] = 'checked';


		/**
		 *  Prepare HTML attributes for media URL radio
		 */

		$atts_media_r = array(
			'type' => 'radio',
			'id' => $field['id'] . '_media_r',
			'name' => $field['name'],
			'value' => 'media'
		);

		if( 'media' == $field['value'][0] )
			$atts_media_r['checked'] = 'checked';

		if( $field['value'][0] != 'internal' && $field['value'][0] != 'external' && $field['value'][0] != 'media' && $field['def_type'] == 'media' )
			$atts_media_r['checked'] = 'checked';


		/**
		 *  Prepare HTML attributes for internal (existing object) input
		 */

		$atts_internal = array(
			'id' => $field['id'] . '_int_value',
			'name' => $field['name']
		);


		/**
		 *  Prepare HTML attributes for external (custom) input
		 */

		$atts_external = array(
			'type' => 'text',
			'id' => $field['id'] . '_ext_value',
			'name' => $field['name'],
			'value' => $field['value'][1]
		);


		/**
		 *  Prepare HTML attributes for hash value input
		 */

		$atts_hash = array(
			'type' => 'text',
			'id' => $field['id'] . '_hash',
			'name' => $field['name'],
			'placeholder' => __('Enter hash here, e.g. #hash', 'acf'),
			'value' => $field['value'][3]
		);


		/**
		 *  Prepare HTML attributes for media hidden input
		 */

		$atts_media = array(
			'type' => 'hidden',
			'id' => $field['id'] . '_media',
			'class' => 'betterurls-media-input',
			'name' => $field['name'],
			'value' => $field['value'][4]
		);

		?>


		<?php
		/**
		 *  Print radios with choices
		 */
		?>

		<div class="betterurls-radios">
			<ul <?php echo acf_esc_attr( array( 'class' => $field['class'] ) ); ?>>
				<?php

				/**
				 *  External URL markup
				 */

				$external = '';
				$external .= '<li>';
				$external .= '<input ' . acf_esc_attr( $atts_ext ) . '>';
				$external .= '<label for="' . esc_attr($field['id']) . '_ext' . '">' . __( 'Custom URL', 'acf' ) . '</label>';
				$external .= '</li>';


				/**
				 *  Internal URL markup
				 */

				$internal = '';
				$internal .= '<li>';
				$internal .= '<input ' . acf_esc_attr( $atts_int ) . '>';
				$internal .= '<label for="' . esc_attr($field['id']) . '_int' . '">' . __( 'Existing Object', 'acf' ) . '</label>';
				$internal .= '</li>';


				/**
				 *  Media URL markup
				 */

				$media = '';
				$media .= '<li>';
				$media .= '<input ' . acf_esc_attr($atts_media_r) . '>';
				$media .= '<label for="' . esc_attr($field['id']) . '_media_r' . '">' . __('Media', 'acf') . '</label>';
				$media .= '</li>';


				/**
				 *  Figure out the default URL type
				 *  This will be printed as the first radio button
				 */

				$radios = array($internal, $external, $media);

				if( 'internal' == $field['def_type'] )
					$radio_id = 0;

				if( 'external' == $field['def_type'] )
					$radio_id = 1;

				if( 'media' == $field['def_type'] )
					$radio_id = 2;


				/**
				 *  Print radio buttons, default first
				 */

				echo $radios[$radio_id];

				for( $i = 0; $i < count($radios); ++$i ) {
					if( $i != $radio_id )
						echo $radios[$i];
				}

				?>
			</ul>
		</div>


		<div class="betterurls-withhash-wrapper">

			<?php

			/**
			 *  External (custom) URL input
			 */

			$active = '';
			if ($field['value'][0] == 'external')
				$active = 'active';

			if ($field['value'][0] != 'internal' && $field['value'][0] != 'external' && $field['value'][0] != 'media' && $field['def_type'] == 'external')
				$active = 'active';

			?>
			<div class="betterurls-external betterurls-value betterurls-withhash acf-input-wrap acf-url <?php echo $active; ?>">
				<i class="acf-sprite-url"></i>
				<input <?php echo acf_esc_attr( $atts_external ); ?>>
			</div>



			<?php

			/**
			 *  Internal (existing object) URL input
			 */

			$active = '';
			if ($field['value'][0] == 'internal')
				$active = 'active';

			if ($field['value'][0] != 'internal' && $field['value'][0] != 'external' && $field['value'][0] != 'media' && $field['def_type'] == 'internal')
				$active = 'active';

			?>
			<div class="betterurls-internal betterurls-value betterurls-withhash <?php echo $active; ?>">
				<select <?php echo acf_esc_attr( $atts_internal ); ?>>

					<?php

					$post_types = get_post_types( array( 'public' => true ), 'names' );
					if (!empty( $field['post_type'] ) && is_array( $field['post_type'] ))
						$post_types = $field['post_type'];

					foreach( $post_types as $post_type ):

						$pt_object = get_post_type_object( $post_type );
						$posts = get_posts(array(
							'posts_per_page' => -1,
							'post_type' => $pt_object->name,
							'orderby' => 'title',
							'order' => 'ASC'
						));

						if (!empty( $posts )):

					?>
						<optgroup label="<?php echo $pt_object->labels->name; ?>">

							<?php foreach( $posts as $p ): ?>
								<?php

								if ($p->ID == $field['value'][2])
									$selected = 'selected="selected"';
								else
									$selected = '';

								?>
								<option value="<?php echo $p->ID; ?>" <?php echo $selected; ?>><?php echo $p->post_title; ?></option>
							<?php endforeach; ?>

						</optgroup>
					<?php

						endif;
					endforeach;

					?>
				</select>
			</div>


			<?php

			/**
			 *  Input for hash value
			 *  Displayed only for internal (existing object) and external (custom) URLs
			 */

			$hide = '';
			if( 'media' == $field['value'][0] )
				$hide = 'hide';

			if( $field['value'][0] != 'internal' && $field['value'][0] != 'external' && $field['value'][0] != 'media' && 'media' == $field['def_type'] )
				$hide = 'hide';

			?>
			<div class="betterurls-hash <?php echo $hide; ?>">
				<input <?php echo acf_esc_attr( $atts_hash ); ?>>
			</div>

		</div> <!-- .betterurls-withhash-wrapper -->


		<?php

		/**
		 *  Media interface
		 */

		$active = '';
		if( 'media' == $field['value'][0] )
			$active = 'active';

		if( $field['value'][0] != 'internal' && $field['value'][0] != 'external' && $field['value'][0] != 'media' && 'media' == $field['def_type'] )
			$active = 'active';


		$media_url = __('No media selected.', 'acf');
		if( isset($field['value'][4]) && ! empty($field['value'][4]) ) {
			$media_url = wp_get_attachment_url(intval($field['value'][4]));
		}

		?>
			<div class="betterurls-media-wrapper <?php echo $active; ?>">
				<input <?php echo acf_esc_attr($atts_media); ?>>
				<p class="betterurls-media-url"><?php echo $media_url; ?></p>
				<button class="button betterurls-media-button"><?php _e('Pick media', 'acf'); ?></button>
			</div>
		<?php
	}



	/*
	*  This filter is appied to the $value before it is updated in the db
	*/

	function update_value( $value, $post_id, $field )
	{
		if( empty($value) ) {
			return $value;
		}

		if( is_array($value) ) {
			$value = array_map('strval', $value);
		}

		return $value;
	}



	/*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*/

	function format_value( $value, $post_id, $field )
	{
		if( empty($value) ) {
			return $value;
		}

		$value = acf_force_type_array( $value );
		$return = '';
		$url = '';
		$full_url = '';


		/**
		 *  Generate url
		 */

		if( 'internal' == $value[0] )
			$url = get_permalink($value[2]);

		elseif( 'external' == $value[0] )
			$url = $value[1];

		elseif( 'media' == $value[0] )
			$url = wp_get_attachment_url($value[4]);

		$full_url = $url;

		if( 'internal' == $value[0] || 'external' == $value[0] )
			if( isset($value[3]) && ! empty($value[3]) )
				$full_url = $url . $value[3];


		/**
		 *  Return values as URL
		 */

		if( 'url' == $field['return_as'] ) {
			$return = $full_url;
		}


		/**
		 *  Return values as array
		 */

		else {
			$return = array();

			$return['type'] = $value[0];
			$return['url'] = $full_url;
			$return['base_url'] = $url;
			$return['hash'] = $value[3];

			if( '#' == $value[3][0] )
				$return['hash_strip'] = substr($value[3], 1);
			else
				$return['hash_strip'] = $value[3];

			if( 'internal' == $value[0] )
				$return['object_id'] = $value[2];

			if( 'media' == $value[0] )
				$return['object_id'] = $value[4];
		}


		return $return;
	}



	/*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your render_field() action.
	*/

	function input_admin_enqueue_scripts()
	{
		$dir = plugin_dir_url( __FILE__ );

		wp_enqueue_media();

		wp_register_script( 'acf-input-betterurls-select2', $dir . 'js/select2.js' );
		wp_enqueue_script('acf-input-betterurls-select2');

		wp_register_script( 'acf-input-betterurls', $dir . 'js/input.js' );
		wp_enqueue_script('acf-input-betterurls');

		wp_register_style( 'acf-input-betterurls-select2', $dir . 'css/select2.css' );
		wp_enqueue_style('acf-input-betterurls-select2');

		wp_register_style( 'acf-input-betterurls', $dir . 'css/input.css' );
		wp_enqueue_style('acf-input-betterurls');
	}


}


// create field
new acf_field_betterurls();

?>
