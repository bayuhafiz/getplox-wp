<?php
/**
 * The search form template.
 *
 *
 * @package WordPress
 * @subpackage plox
 * @since plox 1.0
 */
?>
<form method="get" action="<?php echo bloginfo('url'); ?>" >
	<input type="text" name="s" id="s"/>
	<input type="submit" value="search" />
</form>