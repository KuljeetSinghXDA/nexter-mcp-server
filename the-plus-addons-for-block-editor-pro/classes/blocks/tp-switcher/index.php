<?php
/* Block : Switcher
 * @since : 2.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_switcher_render_callback( $attributes, $content) {
	$output = '';
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$pattern = '/\btpgb-block-'.esc_attr($block_id).'/';
   
	if (preg_match($pattern, $content)) {
		if( class_exists('Tpgb_Blocks_Global_Options') ){
            $global_blocks = Tpgb_Blocks_Global_Options::get_instance();
            $content = $global_blocks::block_row_conditional_render($attributes,$content);
        }
		return $content;
	}
	$switchStyle = (!empty($attributes['switchStyle'])) ? $attributes['switchStyle'] : 'style-1' ;
	$switchalign = (!empty($attributes['switchalign'])) ? $attributes['switchalign'] : 'text-left';
	$title1 = (!empty($attributes['title1'])) ? $attributes['title1'] : '';
	$title2 = (!empty($attributes['title2'])) ? $attributes['title2'] : '';
	$showBtn = (!empty($attributes['showBtn'])) ? $attributes['showBtn'] : false;
	$desc1 = (!empty($attributes['desc1'])) ? $attributes['desc1'] : '';
	$desc2 = (!empty($attributes['desc2'])) ? $attributes['desc2'] : '';
	$source1 = (!empty($attributes['source1'])) ? $attributes['source1'] : '';
	$source2 = (!empty($attributes['source2'])) ? $attributes['source2'] : '';
	$blockTemp1 = (!empty($attributes['blockTemp1'])) ? $attributes['blockTemp1'] : '';
	$blockTemp2 = (!empty($attributes['blockTemp2'])) ? $attributes['blockTemp2'] : '';
	$carouselId = (!empty($attributes['carouselId'])) ? $attributes['carouselId'] : '';
	
	$lblIcon = (!empty($attributes['lblIcon'])) ? $attributes['lblIcon'] : false;
	$switch1Icn = (!empty($attributes['switch1Icn'])) ? $attributes['switch1Icn'] : '';
	$switch2Icn = (!empty($attributes['switch2Icn'])) ? $attributes['switch2Icn'] : '';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$ajaxbaseTem1 = (!empty($attributes['ajaxbaseTem1'])) ? $attributes['ajaxbaseTem1'] : '';
	$ajaxbaseTem2 = (!empty($attributes['ajaxbaseTem2'])) ? $attributes['ajaxbaseTem2'] : '';
	
	$sotriclass = $sttriclass = $temp1class = $temp2class = $viewclass = '';
	if( !empty($ajaxbaseTem1) && $ajaxbaseTem1 == 'ajax-base' && $source1 == 'template' && $blockTemp1 != '' && $blockTemp1!='none'){
		$sotriclass = 'tpgb-load-template-click tpgb-load-'.esc_attr( $blockTemp1 );
		$temp1class = 'tpgb-load-'.esc_attr( $blockTemp1 ).'-content';
		$viewclass = 'tpgb-load-template-view tpgb-load-'.esc_attr($blockTemp1);
	}
	if(!empty($ajaxbaseTem2) && $ajaxbaseTem2 == 'ajax-base' &&  $source2 == 'template' && $blockTemp2 != '' && $blockTemp2!='none'){
		$sttriclass = 'tpgb-load-template-click tpgb-load-'.esc_attr( $blockTemp2 );
		$temp2class = 'tpgb-load-'.esc_attr( $blockTemp2 ).'-content';
	}

    $output .= '<div class="tpgb-switcher tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
		$output .= '<div id="tpca-'.(!empty($carouselId) ? esc_attr($carouselId) : '').'" class="tpgb-switch-wrap" data-id="'.esc_attr($block_id).'">';
			$output .= '<div class="switch-toggle-wrap switch-'.esc_attr($switchStyle).' '.esc_attr($switchalign). ' inactive">';
				$output .= '<div class="switch-1 '.esc_attr($sotriclass).'">';
					$output .= '<div class="switch-label">';
						if(!empty($lblIcon)){
							$output .= '<i class="tpgb-swt-icon '.esc_attr($switch1Icn).'"></i>';
						}
						$output .= wp_kses_post($title1);
					$output .= '</div>';
				$output .= '</div>';
				if(!empty($showBtn)){
					$output .= '<div class="switcher-button">';
						$output .= '<label class="switch-btn-label">';
							$output .= '<input type="checkbox" class="switch-toggle '.esc_attr($switchStyle).'" />';
							$output .= '<span class="switch-slider switch-round '.esc_attr($switchStyle).'"></span>';
						$output .= '</label>';
					$output .= '</div>';
				}
				$output .= '<div class="switch-2 '.esc_attr($sttriclass).'">';
					$output .= '<div class="switch-label">';
						if(!empty($lblIcon)){
							$output .= '<i class="tpgb-swt-icon '.esc_attr($switch2Icn).'"></i>';
						}
						$output .= wp_kses_post($title2);
					$output .= '</div>';
				$output .= '</div>';
				if($switchStyle == 'style-3'){
					$output .= '<div class="underline"></div>';
				}
			$output .= '</div>';
			$output .= '<div class="switch-toggle-content '.esc_attr($viewclass).'">';
				if($source1 == 'editor' || $source2 == 'editor'){
					$output .= $content;
				}else{
					$output .= '<div class="switch-content-1 '.esc_attr($temp1class).'">';
						if(!empty($source1) && $source1 == 'content'){
							$output .= wp_kses_post($desc1);
						}else if($source1 == 'template' && $blockTemp1 != '' && $blockTemp1!='none'){
							ob_start();
								echo Tpgb_Library()->plus_do_block($attributes['blockTemp1']);
							if( !empty($ajaxbaseTem1) && $ajaxbaseTem1 == 'ajax-base'  ){
								$output .= '';
							}else{
								$output .= ob_get_contents();
							}
							ob_end_clean();
						}
					$output .= '</div>';
					$output .= '<div class="switch-content-2 '.esc_attr($temp2class).'">';
						if(!empty($source2) && $source2 == 'content'){
							$output .= wp_kses_post($desc2);
						}else if($source2 == 'template' && $blockTemp2 != '' && $blockTemp2!='none'){
							ob_start();
								echo Tpgb_Library()->plus_do_block($attributes['blockTemp2']);
								if( !empty($ajaxbaseTem2) && $ajaxbaseTem2 == 'ajax-base' ){
									$output .= '';
								}else{
									$output .= ob_get_contents();
								}
							ob_end_clean();
						}
					$output .= '</div>';
				}
			$output .= "</div>";
		$output .= "</div>";
    $output .= "</div>";

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_switcher() {
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_switcher_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_switcher' );