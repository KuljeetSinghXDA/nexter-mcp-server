<?php
/* Block : TP Accordion Inner
 * @since : 2.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_Slide_inner_render_callback( $attributes, $content) {
	$pattern = '/\btpgb-slide-content/';
    
	if (preg_match($pattern, $content)) {
		if( class_exists('Tpgb_Blocks_Global_Options') ){
            $global_blocks = Tpgb_Blocks_Global_Options::get_instance();
            $content = $global_blocks::block_row_conditional_render($attributes,$content);
        }
		return $content;
	}
	$output = '';
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$output .= '<div class="splide__slide tpgb-slide-content">';  //'.(!empty($OverflowHid) ? 'slide-overflow-hidden' :'' ).'
		$output .= $content; 
	$output .= '</div>';

	return $output;
	
}

/**
 * Render for the server-side
 */
function tpgb_tp_slide_inner() {
	
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_Slide_inner_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_slide_inner' );