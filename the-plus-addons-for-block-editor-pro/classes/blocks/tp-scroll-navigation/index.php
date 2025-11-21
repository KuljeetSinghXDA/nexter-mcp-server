<?php
/**
 * Block : Scroll Navigation
 * @since : 1.2.1
 */
defined( 'ABSPATH' ) || exit;

function tpgb_scroll_navigation_render_callback( $attributes, $content) {
	$output = '';
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$menuList = (!empty($attributes['menuList'])) ? $attributes['menuList'] : [];
	$styletype = (!empty($attributes['styletype'])) ? $attributes['styletype'] : '';
	$navdire = (!empty($attributes['navdire'])) ? $attributes['navdire'] : '';
	$navposi = (!empty($attributes['navposi'])) ? $attributes['navposi'] : '';
	$disCounter = (!empty($attributes['disCounter'])) ? $attributes['disCounter'] : false;
   	$countersty = (!empty($attributes['countersty'])) ? $attributes['countersty'] : '';
	$tooltipsty = (!empty($attributes['tooltipsty'])) ? $attributes['tooltipsty'] : '';
	$totipAlign = (!empty($attributes['totipAlign'])) ? $attributes['totipAlign'] : '';
	$tooltiparrow = (!empty($attributes['tooltiparrow'])) ? $attributes['tooltiparrow'] : false;
	$scrolloff = (!empty($attributes['scrolloff'])) ? $attributes['scrolloff'] : false;
	$sTopoffset = (!empty($attributes['sTopoffset'])) ? $attributes['sTopoffset'] : '';
	$carouselId = (!empty($attributes['carouselId'])) ? $attributes['carouselId'] : '';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$i = 0;
	//Set Id Connection 
	$dataAttr='';
	if(!empty($carouselId)){
		$dataAttr .= ' id="tptab_'.esc_attr($carouselId).'"';
		$dataAttr .= ' data-tab-id="tptab_'.esc_attr($carouselId).'"';
		$dataAttr .= ' data-connection="tpca-'.esc_attr($carouselId).'" ';
	}

    //Get Navigation
	if(!empty($menuList)){ 
		$nav = '';

		foreach ( $menuList as $index => $item ) : 
			$i++;
			//Get Icon
			$icons = '';
			$txtIcons = '';

			// Style 6 Text Icon
			$txtIcons .= '<div class="tpgb-iotxt">';
				if(!empty($item['icon']) && isset($item['iconName']) && $item['iconName'] != '' ){
					$txtIcons .= '<i class="'.esc_attr($item['iconName']).' tooltip-icon"></i>';
				}
				if( isset($item['navTxt']) && !empty($item['navTxt'])){
					$txtIcons .= '<span>'.wp_kses_post($item['navTxt']).'</span>';
				}
			$txtIcons .= '</div>';

			if(!empty($item['icon']) && isset($item['iconName']) && $item['iconName'] != '' ){
				$icons .= '<i class="'.esc_attr($item['iconName']).' tooltip-icon"></i>';
			}else{
				$icons .= '<i class="tooltip-icon fas fa-home"></i>';
			}
			$ariaLabelT = (!empty($item['ariaLabel'])) ? esc_attr($item['ariaLabel']) : esc_attr__('Navigation Button', 'tpgbp');
			$nav .= '<a id="scroll121" href="#'.esc_attr($item['secId']).'" class="tpgb-scroll-nav-item" data-tab="'.esc_attr($index).'" aria-label="'.$ariaLabelT.'">';
				$nav .= '<div class="tpgb-scroll-nav-dots '.(!empty($disCounter) && $countersty != '' ? esc_attr($countersty)  :'' ).'">';
					if($styletype == 'style-5'){
						$nav .= $icons;
					}
					if($styletype == 'style-6'){
						$nav .= $txtIcons;
					}
					if(!empty($item['tooltip']) && isset($item['tooltiptxt']) && $item['tooltiptxt'] != ''){
						$nav .= '<span class="tooltip-title nav-'.esc_attr($navdire).' '.esc_attr($tooltipsty).' '.esc_attr($totipAlign).' '.(!empty($tooltiparrow) ? 'tooltip-arrow' : '') .'">';
							if($styletype != 'style-5' && !empty($item['icon'])){
								$nav .= $icons;
							}
							$nav .= wp_kses_post($item['tooltiptxt']);
						$nav .= '</span>';
					}
				$nav .= "</div>";
			$nav .= '</a>';
		endforeach;
	}

	$output .= '<div class="tpgb-scroll-nav tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).' '.esc_attr($styletype).' nav-'.esc_attr($navdire).' '.($navdire == 'top' || $navdire == 'bottom' ? esc_attr($navposi)  : ''  ).' '.(!empty($scrolloff) ? 'scroll-view' :'').'" data-scroll-view="'.((!empty($scrolloff) && $sTopoffset != '') ? esc_attr( $sTopoffset ) : '' ).'">';
		$output .= '<div class="tpgb-scroll-nav-inner" '.$dataAttr.'>';
			$output .= $nav;
		$output .= "</div>";
    $output .= "</div>";
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_scroll_navigation() {
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_scroll_navigation_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_scroll_navigation' );