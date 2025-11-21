<?php
/* Block : Dynamic Heading
 * @since : 4.3.3
 */
defined( 'ABSPATH' ) || exit;

function tpgb_repeater_data_render_callback($attributes, $content, $blocks) {
    $blockId = isset($attributes['block_id']) ? $attributes['block_id'] : '';
    $output = '';
    $textValue = $attributes['textValue'];
    global $repeater_index;

    if (preg_match('/data-tpgb-dynamic=[\'"](.+?)[\'"]/', $textValue, $matches)) {
        // Decode HTML entities
        $jsonString = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);

        // Try to fix malformed JSON: remove extra commas like ,,"key":
        $jsonString = preg_replace('/,\s*,/', ',', $jsonString);

        // Extra: remove a comma before closing braces (optional cleanup)
        $jsonString = preg_replace('/,\s*}/', '}', $jsonString);

        $dataArray = json_decode($jsonString, true);


        if (json_last_error() === JSON_ERROR_NONE && isset($dataArray['dynamicField'])) {
            $dynamicField = $dataArray['dynamicField'];

            if (strpos($dynamicField, '|') !== false) {
                $dynamicFieldParts = explode('|', $dynamicField);
                if (count($dynamicFieldParts) === 5 || count($dynamicFieldParts) === 7) {
                    $fieldName = $dynamicFieldParts[1] ?? 'Unknown Field';
                    $repFunction = apply_filters('tp_get_repeater_data', $dynamicFieldParts);
                    if (is_wp_error($repFunction)) {
                        $output = $repFunction->get_error_message();
                    } else {
                        $output = $repFunction['repeater_data'][$repeater_index][$fieldName] ?? '';
                    }
                }
            }
        } else {
            error_log("JSON Decode Error: " . json_last_error_msg());
        }
    } elseif (is_string($textValue)) {
        $output = $textValue;
    }

    $blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

    $content = '<div class="tpgb-dynamic-heading tpgb-block-' . esc_attr($blockId) . ' ' . esc_attr($blockClass) . '">' . wp_kses_post($output) . '</div>';
    return $content; 
}

function tpgb_tp_repeater_data() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_repeater_data_render_callback', true);
	register_block_type( __DIR__.'/block.json' , $block_data, );
}
add_action( 'init', 'tpgb_tp_repeater_data' );
