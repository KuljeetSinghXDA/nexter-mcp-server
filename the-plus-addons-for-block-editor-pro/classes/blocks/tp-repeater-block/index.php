<?php
/* Block : TP Button
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_repeater_render_callback( $attributes, $content ) {
	return  $content;
}

/**
 * Render for the server-side
 */
function tpgb_tp_repeater() {
	
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_repeater_render_callback');
	register_block_type( __DIR__.'/block.json', $block_data, );
}
add_action( 'init', 'tpgb_tp_repeater' );