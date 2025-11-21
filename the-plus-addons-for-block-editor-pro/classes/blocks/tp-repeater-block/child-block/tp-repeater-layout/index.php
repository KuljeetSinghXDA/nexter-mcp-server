<?php

defined( 'ABSPATH' ) || exit;

function tpgb_repeater_layout_render_callback($attributes, $content, $blocks) {
    global $repeater_index;
    $repeater_index = 0;
    $rep_field_raw = $blocks->context['tpgb/dynamicRepField'] ?? '';
    
    if (strpos($attributes['postOpt']['value'], '|') !== false) {
        $postOptParts = explode('|', $attributes['postOpt']['value']);
        $attributes['postOpt']['value'] = isset($postOptParts[2]) ? $postOptParts[2] : $attributes['postOpt']['value'];
    }
    $post_id = ($attributes['postOpt']['value'] === 'nxt-repeater-all') ? get_the_ID() : $attributes['postOpt']['value'];
    
    // Check if the field has a source prefix
    $field_parts = explode('|', $rep_field_raw);
    $source = count($field_parts) > 1 ? $field_parts[0] : '';
    $rep_field = count($field_parts) > 1 ? $field_parts[1] : $rep_field_raw;
    
    $output = '';
    $repData = [];
    
    // Process based on the source prefix
    if (strtolower($source) === 'acf' && function_exists('get_field')) {
        // ACF handling
        $repData = get_field($rep_field, $post_id);
        
        if (is_array($repData) && !empty($repData)) {
            foreach ($repData as $data_item) {
                $block_instance = $blocks->parsed_block;
                
                $block_content = (
                    new WP_Block(
                        $block_instance,
                        array(
                            'tpgb/dynamicRowIndex' => $repeater_index,
                            'tpgb/dynamicRepField' => $rep_field,
                            'tpgb/dynamicPost' => $post_id
                        )
                    )
                )->render(array('dynamic' => false));
                
                $output .= $block_content;
                $repeater_index++;
            }
        }
    } elseif (strtolower($source) === 'jetengine') {
        // JetEngine handling
        $specific_meta = get_post_meta($post_id, $rep_field, false);
        
        if (!empty($specific_meta) && isset($specific_meta[0])) {
            $repData = maybe_unserialize($specific_meta[0]);
        } else {
            // If direct access failed, try getting all meta
            $all_meta = get_post_meta($post_id);
            
            if (isset($all_meta[$rep_field]) && !empty($all_meta[$rep_field][0])) {
                $repData = maybe_unserialize($all_meta[$rep_field][0]);
            }
        }
        
        if (is_array($repData) && !empty($repData)) {
            foreach ($repData as $data_item) {
                $block_instance = $blocks->parsed_block;
                
                $block_content = (
                    new WP_Block(
                        $block_instance,
                        array(
                            'tpgb/dynamicRowIndex' => "jetengine|$repeater_index",
                            'tpgb/dynamicRepField' => "jetengine|$rep_field",
                            'tpgb/dynamicPost' => "jetengine|$post_id"
                        )
                    )
                )->render(array('dynamic' => false));
                
                $output .= $block_content;
                $repeater_index++;
            }
        }
    }
    
    return $output;
}

function tpgb_tp_repeater_layout() {
	
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_repeater_layout_render_callback');
    $block_data['render_callback'] = 'tpgb_repeater_layout_render_callback';
	register_block_type( __DIR__.'/block.json', $block_data, );
}
add_action( 'init', 'tpgb_tp_repeater_layout' );