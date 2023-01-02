<?php
/**
 * Twenty Twenty-Two functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Two
 * @since Twenty Twenty-Two 1.0
 */


if ( ! function_exists( 'twentytwentytwo_support' ) ) :

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since Twenty Twenty-Two 1.0
	 *
	 * @return void
	 */
	function twentytwentytwo_support() {

		// Add support for block styles.
		add_theme_support( 'wp-block-styles' );

		// Enqueue editor styles.
		add_editor_style( 'style.css' );

	}

endif;

add_action( 'after_setup_theme', 'twentytwentytwo_support' );

if ( ! function_exists( 'twentytwentytwo_styles' ) ) :

	/**
	 * Enqueue styles.
	 *
	 * @since Twenty Twenty-Two 1.0
	 *
	 * @return void
	 */
	function twentytwentytwo_styles() {
		// Register theme stylesheet.
		$theme_version = wp_get_theme()->get( 'Version' );

		$version_string = is_string( $theme_version ) ? $theme_version : false;
		wp_register_style(
			'twentytwentytwo-style',
			get_template_directory_uri() . '/style.css',
			array(),
			$version_string
		);

		// Enqueue theme stylesheet.
		wp_enqueue_style( 'twentytwentytwo-style' );
		wp_enqueue_script('custom-js',get_template_directory_uri().'/assets/custom.js', array(), true,true);


	}

endif;

add_action( 'wp_enqueue_scripts', 'twentytwentytwo_styles' );

// Add block patterns
require get_template_directory() . '/inc/block-patterns.php';


/*function to create coor swatch starts*/
function woo_custom_color_swatch($types){
	$types['color_type'] ='Color';
	return $types;
}
add_filter('product_attributes_type_selector','woo_custom_color_swatch');

function woo_swatch_edit_fields($term, $taxonomy){
	global $wpdb;

	$attr_type = $wpdb->get_var(
		$wpdb->prepare("SELECT attribute_type FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = '%s'", substr($taxonomy, 3 ))
	);
	if('color_type' !== $attr_type){
		return;
	}
	$color = get_term_meta($term->term_id, 'color_type', true);
	?>

	<div class="frm-fields">
		<div><label for="term_color_type">Color</label></div>
		<input type="text" name="color_type" id="term_color_type" value="<?php echo esc_attr($color); ?>">
	</div>
<?php

}
add_action('pa_color_edit_form_fields','woo_swatch_edit_fields',10,20);


function woo_save_color_swatch($term_id){
	$color_type = !empty($_POST['color_type']) ? $_POST['color_type'] : '';
	update_term_meta($term_id, 'color_type', sanitize_hex_color($color_type));
}
add_action('edited_pa_color','woo_save_color_swatch');


function woo_swatch_select_attr($attribute_taxonomy, $i, $attribute){
	if('color_type' !== $attribute_taxonomy->attribute_type){
		return;
	}

	$options = $attribute->get_options();
	$options = !empty($options) ? $options : array();

	?>
	<select multiple="multiple" data-placeholder="Select Color" class="attrinute_values" name="attribut_value[<?php echo $i; ?>][]">
		<?php 
			$colors = get_terms('pa_color', array('hide_empty'=>0));
			if($colors){
				foreach($colors as $color){
					echo '<option value="'.$color->term_id.'"'.wc_selected($color->term_id, $options).'>'.$color->name.'</option>';
				}
			}
		?>	
	</select>
	<button class="button plus select_all_attributes">Select All</button>
	<button class="button minus select_all_attributes">Select None</button>
<?php
}
add_action('woocommerce_product_option_terms','woo_swatch_select_attr', 10, 3);

function woo_color_swatch_html($html, $args){
	global $wpdb;

	$taxonomy = $args['attribute'];
	$product = $args['product'];


	$attribute_type = $wpdb->get_var(
		$wpdb->prepare("SELECT attribute_type FROM ".$wpdb->prefix."woocommerce_attribute_taxonomies WHERE attribute_name = '%s'",substr($taxonomy, 3))
	);

	if('color_type' !== $attribute_type){
		return $html;
	}

	$html = '<div style="display:none">'. $html.'</div>';
	$colors = wc_get_product_terms($product->get_id(), $taxonomy);
	//print_r($args);
	foreach ($colors as $color) {
		if(in_array($color->slug, $args['options'])){
			$hex_color = get_term_meta($color->term_id, 'color_type', true);

			$selected = $args['selected'] === $color->slug ? 'selected' : '';

			$html.= sprintf('<span class="swatch %s" style="background-color:%s;" title="%s" data-value="%s"></span>',
				$selected,$hex_color, $color->name, $color->slug);
		}
	}
	return $html;
}
add_action('woocommerce_dropdown_variation_attribute_options_html','woo_color_swatch_html', 20,2);
/*function to create coor swatch ends*/