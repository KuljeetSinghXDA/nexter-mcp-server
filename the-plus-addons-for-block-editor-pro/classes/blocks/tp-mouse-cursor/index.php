<?php
/**
 * Block : Mouse Cursor
 * @since 1.3.0
 */
defined( 'ABSPATH' ) || exit;
function tpgb_mouse_cursor_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$cursorEffect = (!empty($attributes['cursorEffect'])) ? $attributes['cursorEffect'] : 'mc-body';
	$cursorType = (!empty($attributes['cursorType'])) ? $attributes['cursorType'] : 'mouse-cursor-icon';
	$curIconType = (!empty($attributes['curIconType'])) ? $attributes['curIconType'] : 'icon-predefine';
	$curPreIcon = (!empty($attributes['curPreIcon'])) ? $attributes['curPreIcon'] : 'crosshair';
	$circleStyle = (!empty($attributes['circleStyle'])) ? $attributes['circleStyle'] : 'mc-cs1';
	$mcPointerIcon = (!empty($attributes['mcPointerIcon']['url'])) ? $attributes['mcPointerIcon'] : '';
	$pointerText = (!empty($attributes['pointerText'])) ? $attributes['pointerText'] : '';
	$firstCircleSize = (!empty($attributes['firstCircleSize'])) ? $attributes['firstCircleSize'] : '';
	$secondCircleSize = (!empty($attributes['secondCircleSize'])) ? $attributes['secondCircleSize'] : '';
	
	$textBlockSize = (!empty($attributes['textBlockSize'])) ? $attributes['textBlockSize'] : '';
	$textBlockColor = (!empty($attributes['textBlockColor'])) ? $attributes['textBlockColor'] : '';
	$textBlockWidth = (!empty($attributes['textBlockWidth'])) ? $attributes['textBlockWidth'] : '';
	
	$listTagHover = (!empty($attributes['listTagHover'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['listTagHover']) : 'a';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	$mouse_cursor_attr = array();
	
	$iconWidth = (array)$attributes['iconMaxWidth'];
	$mouse_cursor_attr['block_id'] = $block_id;
	$mouse_cursor_attr['mc_cursor_adjust_left'] = (!empty($attributes["pointLeftOffset"])) ? $attributes["pointLeftOffset"] : 0;
	$mouse_cursor_attr['mc_cursor_adjust_top'] = (!empty($attributes["pointTopOffset"])) ? $attributes["pointTopOffset"] : 0;
	$mouse_cursor_attr['effect'] = $cursorEffect;
	if ($cursorEffect =='mc-column' || $cursorEffect =='mc-row' || $cursorEffect =='mc-block' || $cursorEffect =='mc-body' ) {
		$mouse_cursor_attr['type'] = $cursorType;
		if($cursorType =='mouse-cursor-icon'){
			$mouse_cursor_attr['icon_type'] = $curIconType;
			if($curIconType =='icon-predefine'){
				$mouse_cursor_attr['mc_cursor_icon'] = $curPreIcon;
			}else if($curIconType =='icon-custom'){
				$dyMcPointerIcon = (isset($attributes['mcPointerIcon']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['mcPointerIcon']) : (!empty($attributes['mcPointerIcon']['url']) ? $attributes['mcPointerIcon']['url'] : '');
				$mouse_cursor_attr['mc_cursor_icon'] = $dyMcPointerIcon;
				if( !empty($attributes['mcClick']) && !empty($attributes['mcClickIcon']['url']) ){
					$mouse_cursor_attr['mc_cursor_see_more'] = 'yes';
					$dymcClickIcon = (isset($attributes['mcClickIcon']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['mcClickIcon']) : (!empty($attributes['mcClickIcon']['url']) ? $attributes['mcClickIcon']['url'] : '');
					$mouse_cursor_attr['mc_cursor_see_icon'] = $dymcClickIcon;
				}
			}
		}else if($cursorType =='mouse-follow-image'){
			$dyMcPointerIcon = (isset($attributes['mcPointerIcon']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['mcPointerIcon']) : (!empty($attributes['mcPointerIcon']['url']) ? $attributes['mcPointerIcon']['url'] : '');
			$mouse_cursor_attr['mc_cursor_icon'] = $dyMcPointerIcon;
			if($cursorEffect =='mc-block'){
				$mouse_cursor_attr['mc_cursor_adjust_width'] = (!empty($iconWidth['md'])) ? $iconWidth['md'].$iconWidth['unit'] : "100px";
			}
			
			if( !empty($attributes['mcClick']) && !empty($attributes['mcClickIcon']['url']) ){
				$mouse_cursor_attr['mc_cursor_see_more'] = 'yes';
				$dymcClickIcon = (isset($attributes['mcClickIcon']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['mcClickIcon']) : (!empty($attributes['mcClickIcon']['url']) ? $attributes['mcClickIcon']['url'] : '');
				$mouse_cursor_attr['mc_cursor_see_icon'] = $dymcClickIcon;
			}
		}else if($cursorType=='mouse-follow-text'){
			$mouse_cursor_attr['mc_cursor_text'] = (!empty($attributes['pointerText'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['pointerText']) : '';
			if($cursorEffect=='mc-block'){
				if(!empty($textBlockSize)){
					$mouse_cursor_attr['mc_cursor_text_size'] = $textBlockSize;
				}
				if(!empty($textBlockColor)){
					$mouse_cursor_attr['mc_cursor_text_color'] = $textBlockColor;
				}
				if(!empty($textBlockWidth)){
					$mouse_cursor_attr['mc_cursor_text_width'] = $textBlockWidth;
				}
			}
			if(!empty($attributes['mcClick']) ){
				$mouse_cursor_attr['mc_cursor_see_more'] = 'yes';
				$mouse_cursor_attr['mc_cursor_see_text'] = (!empty($attributes['mcClickText'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['mcClickText']) : '';
			}	
		}else if($cursorType=='mouse-follow-circle') {	
			$mouse_cursor_attr['circle_type'] = (!empty($attributes['circleCursorType'])) ? $attributes['circleCursorType'] : 'cursor-predefine';
			
			if($attributes['circleCursorType'] == 'cursor-predefine'){
				$mouse_cursor_attr['mc_cursor_adjust_symbol'] = (!empty($attributes["mcCursorSymbol"])) ? $attributes["mcCursorSymbol"] : 'crosshair';
				$mouse_cursor_attr['mc_cursor_adjust_style'] = $circleStyle;
				
				$mouse_cursor_attr['circle_tag_selector'] = $listTagHover;
				if($cursorEffect=='mc-block'){
					$cirMWidth = (array)$attributes['circleMaxWidth'];
					$cirMHeight = (array)$attributes['circleMaxHeight'];
					$mouse_cursor_attr['mc_cursor_adjust_width'] = (!empty($cirMWidth['md']) && !empty($cirMWidth['unit'])) ? $cirMWidth['md'].$cirMWidth['unit'] : "50px";
					$mouse_cursor_attr['mc_cursor_adjust_height'] = (!empty($cirMHeight['md']) && !empty($cirMHeight['unit'])) ? $cirMHeight['md'].$cirMHeight['unit'] : "50px";
					
					$mouse_cursor_attr['mc_circle_transformNml'] = (!empty($attributes["circleTansNmlCss"])) ? $attributes["circleTansNmlCss"] : '';
					$mouse_cursor_attr['mc_circle_transformHvr'] = (!empty($attributes["circleTansHvrCss"])) ? $attributes["circleTansHvrCss"] : '';
					$mouse_cursor_attr['mc_circle_transitionNml'] = (!empty($attributes["circleNmlTranDur"])) ? $attributes["circleNmlTranDur"] : '0.3';
					$mouse_cursor_attr['mc_circle_transitionHvr'] = (!empty($attributes["circleHvrTranDur"])) ? $attributes["circleHvrTranDur"] : '0.3';
					$mouse_cursor_attr['mc_circle_zindex'] = (!empty($attributes["circleZindex"])) ? (int)$attributes["circleZindex"] : 1;
					
			        if($circleStyle == 'mc-cs3'){
						$mouse_cursor_attr['style_two_blend_mode'] = (!empty($attributes["crclMixBMode"])) ? $attributes["crclMixBMode"] : 'difference';
			        }
					$mouse_cursor_attr['style_two_bg'] = (!empty($attributes["circleNmlBG"])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes["circleNmlBG"]) : '';
					$mouse_cursor_attr['style_two_bgh'] = (!empty($attributes["circleHvrBG"])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes["circleHvrBG"]) : '';
				}	       
			}else if($attributes['circleCursorType'] == 'cursor-custom'){
				$dyMcPointerCirIcon = (isset($attributes['mcPointerCirIcon']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['mcPointerCirIcon']) : (!empty($attributes['mcPointerCirIcon']['url']) ? $attributes['mcPointerCirIcon']['url'] : '');
				$mouse_cursor_attr['mc_cursor_icon'] = $dyMcPointerCirIcon;
				if( !empty($attributes['mcClick']) && !empty($attributes['mcClickIcon']['url']) ){
					$mouse_cursor_attr['mc_cursor_see_more'] = 'yes';
					$dyMcClickIcon = (isset($attributes['mcClickIcon']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['mcClickIcon']) : (!empty($attributes['mcClickIcon']['url']) ? $attributes['mcClickIcon']['url'] : '');
					$mouse_cursor_attr['mc_cursor_see_icon'] = $dyMcClickIcon;
				}
			}
		}
	}
	$mouse_cursor_attr = htmlspecialchars(wp_json_encode($mouse_cursor_attr), ENT_QUOTES, 'UTF-8');
	$progressClass = '';
	if($cursorEffect=='mc-body' && $cursorType=='mouse-follow-circle' && $circleStyle=='mc-cs2'){
		$progressClass = 'tpgb-percent-circle';
	}
	$output = '';
    $output .= '<div class="tpgb-mouse-cursor tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'" data-tpgb_mc_settings=\'' .$mouse_cursor_attr. '\'>';
		if($cursorEffect!='mc-block'){
			if($cursorType=='mouse-follow-text'){
				 $output .= '<div class="tpgb-cursor-pointer-follow-text">'.wp_kses_post($pointerText).'</div>';
			}else if( $cursorType=='mouse-follow-image' && !empty($mcPointerIcon['url']) ){
				$altText = (isset($mcPointerIcon['alt']) && !empty($mcPointerIcon['alt'])) ? esc_attr($mcPointerIcon['alt']) : ((!empty($mcPointerIcon['title'])) ? esc_attr($mcPointerIcon['title']) : esc_attr__('Mouse Cursor','tpgbp'));

				$output .= '<img src="'.esc_url($mcPointerIcon['url']).'" class="tpgb-cursor-pointer-follow" alt="'.$altText.'"/>';
			}else if($cursorType=='mouse-follow-circle'){
				$output .= '<div class="tpgb-cursor-follow-circle '.esc_attr($progressClass).'">';
				
				if($cursorEffect=='mc-body' && $circleStyle=='mc-cs2'){
					$output .='<svg class="tpgb-mc-svg-circle" width="200" height="200" viewport="0 0 100 100" xmlns="https://www.w3.org/2000/svg"><circle class="tpgb-mc-circle-st1" cx="100" cy="100" r="'.esc_attr($firstCircleSize).'"></circle><circle class="tpgb-mc-circle-st1 tpgb-mc-circle-progress-bar" cx="100" cy="100" r="'.esc_attr($secondCircleSize).'"></circle></svg>';
				}
				$output .= '</div>';
			}
		}
    $output .= '</div>';
  
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_mouse_cursor() {
	
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_mouse_cursor_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_mouse_cursor' );