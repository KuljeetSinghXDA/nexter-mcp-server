<?php
/* Block : TP Design Tool
 * @since : 1.3.2
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_design_tool_render_callback( $attributes ) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$designToolOpt = (!empty($attributes['designToolOpt'])) ? $attributes['designToolOpt'] : 'grid_stystem';
	$gridSystemOpt = (!empty($attributes['gridSystemOpt'])) ? $attributes['gridSystemOpt'] : 'gs_default';
	$gridDirection = (!empty($attributes['gridDirection'])) ? $attributes['gridDirection'] : 'ltr';
	$gridOnFront = (!empty($attributes['gridOnFront'])) ? $attributes['gridOnFront'] : false;
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

    $output .= '<div class="tpgb-design-tool tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'"></div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_design_tool() {
	
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_design_tool_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_design_tool' );