<?php

if (!class_exists('Cptts_Shortcodes')):

class Cptts_Shortcodes {

	private $shortcodes = array(
		'custom_button' => 'btn',
		'icon_list' => 'list_wrapper',
		'icon_list_item' => 'list_item',
		'clear_func' => 'clear',
		'tabs_func' => 'tabs',
		'tab_func' => 'tab'
	);

	function custom_button($atts)
	{
		extract(shortcode_atts(array(
			'href' => '#',
			'size' => 'short',
			'label' => __('Anchor', 'plox'),
			'color' => 'default'
		), $atts));

		return "<a href='$href' class='main-btn $size-btn $color-btn'>$label</a>";
	}
	function icon_list($atts, $content = "")
	{
		extract(shortcode_atts(array(
		), $atts));
		$html = '<ul class="custom-icons-list">';
		$html .= do_shortcode($content);
		$html .= '</ul>';
		return $html;
	}
	function icon_list_item($atts, $content = "")
	{
		extract(shortcode_atts(array(
			'text' => '',
			'sup' => ''
		), $atts));

		$html = "<li><span class='list-icon'>$content</span>$text";

		if ($sup !== ''){
			$html .= "<sup>$sup</sup>";
		}

		$html .= "</li>";

		return $html;
	}
	function clear_func($atts, $content = "")
	{
		return "<div class='clearfix'></div>";
	}

	function tabs_func($atts, $content = null)
	{
		extract(shortcode_atts(array('titles' => ''), $atts));

		$titles = explode(",", $titles);
		$html = '<div class="tabs">';

		$html .= '<ul>';
		$i=1;
		foreach($titles as $title):
			$html .= '<li><a href="#tabs-'.$i.'" rel="tabs-'.$i.'">'.trim($title).'</a></li>';
			$i++;
		endforeach;

		$html .= '</ul>';
		$html .= do_shortcode($content);
		$html .= '</div>';

		return $html;
	}

	function tab_func($atts, $content = null)
	{
		extract(shortcode_atts(array(
			'id' => ''), $atts));

		$html = '<div id="tabs-'.$id.'">';

		$html .= do_shortcode($content);

		$html .= '</div>';

		return $html;
	}

	/**
	 * INTERNAL CLASS FUNCTIONALITY
	 */

	/**
	 * Cptts_Shortcodes constructor.
     */
	function __construct()
	{
		add_action( 'init', array( $this, 'create_shorcodes' ) );
	}

	/**
	 * Registers all shortcodes defined in $shortcodes property.
     */
	public function create_shorcodes()
	{
		foreach ($this->shortcodes as $func => $name) {
			add_shortcode($name, array($this, $func));
		}
	}
}

new Cptts_Shortcodes();

endif;
