<?php
/* Block : Carousel Remote
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_carousel_remote_render_callback( $attributes, $content) {
	$output = '';
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$remType = (!empty($attributes['remType'])) ? $attributes['remType'] : '';
	$rbtnAlign = (!empty($attributes['rbtnAlign'])) ? $attributes['rbtnAlign'] : '';
	$btntxt1 = (!empty($attributes['btntxt1'])) ? $attributes['btntxt1'] : '';
	$btntxt2 = (!empty($attributes['btntxt2'])) ? $attributes['btntxt2'] : '';
	$Ctmicon1 = (!empty($attributes['Ctmicon1'])) ? $attributes['Ctmicon1'] : '';
	$Ctmicon2 = (!empty($attributes['Ctmicon2'])) ? $attributes['Ctmicon2'] : '';
	$imgSize = (!empty($attributes['imgSize'])) ? $attributes['imgSize'] : '';
	$carouselId = (!empty($attributes['carouselId'])) ? $attributes['carouselId'] : '';
	$dotList = (!empty($attributes['dotList'])) ? $attributes['dotList'] : [];
	$showDot = (!empty($attributes['showDot'])) ? $attributes['showDot'] : false;
	$dotLayout = (!empty($attributes['dotLayout'])) ? $attributes['dotLayout'] : '';
	$dotstyle = (!empty($attributes['dotstyle'])) ? $attributes['dotstyle'] : '';
	$tooltiparrow = (!empty($attributes['tooltiparrow'])) ? $attributes['tooltiparrow'] : false;
	$AborderColor = (!empty($attributes['AborderColor'])) ? $attributes['AborderColor'] : '';
	$showpagi = (!empty($attributes['showpagi'])) ? $attributes['showpagi'] : false;
	$carobtn = (!empty($attributes['carobtn'])) ? $attributes['carobtn'] : false;
	$tooltipDir = (!empty($attributes['tooltipDir'])) ? $attributes['tooltipDir'] : '';
	$vtooltipDir = (!empty($attributes['vtooltipDir'])) ? $attributes['vtooltipDir'] : '';
	$BiconFont = (!empty($attributes['BiconFont'])) ? $attributes['BiconFont'] : '';
	$btnIcon1 = (!empty($attributes['btnIcon1'])) ? $attributes['btnIcon1'] : '';
	$btnIcon2 = (!empty($attributes['btnIcon2'])) ? $attributes['btnIcon2'] : '';
	$sliderInd = (!empty($attributes['sliderInd'])) ? $attributes['sliderInd'] : '';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	//Set Id Connection 
	$dataAttr='';
	if(!empty($carouselId)){
		$dataAttr .= ' id="tptab_'.esc_attr($carouselId).'" ';
		$dataAttr .= ' data-tab-id="tptab_'.esc_attr($carouselId).'" ' ;
		$dataAttr .= ' data-connection="tpca-'.esc_attr($carouselId).'" ';
		$dataAttr .= ' data-extra-conn="tpex-'.esc_attr($carouselId).'"';
	}
	
	// Set Icon For Button
	$nav_next =$nav_prev = $nav_prev_icon = $nav_next_icon = '';
	$nav_next_text=$nav_prev_text ='';
	if($btntxt1!=''){
		$nav_prev_text ='<span>'.wp_kses_post($btntxt1).'</span>';
	}
	if($btntxt2!=''){
		$nav_next_text ='<span>'.wp_kses_post($btntxt2).'</span>';
	}

	//Svg For Animation 
	$Asvg = '';
	$Asvg .= '<svg height="32" data-v-d3e9c2e8="" width="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" svg-inline="" role="presentation" focusable="false" tabindex="-1" class="active-border">';
		$Asvg .= '<path data-v-d3e9c2e8="" d="M14.7974701,0 C16.6202545,0 19.3544312,0 23,0 C26.8659932,0 30,3.13400675 30,7 L30,23 C30,26.8659932 26.8659932,30 23,30 L7,30 C3.13400675,30 0,26.8659932 0,23 L0,7 C0,3.13400675 3.13400675,0 7,0 L14.7602345,0" transform="translate(1.000000, 1.000000)" fill="none" stroke="'.esc_attr($AborderColor).'" stroke-width="2" class="border"></path>';
	$Asvg .= '</svg>';


	
	if($BiconFont == 'font_awesome'){
		$nav_prev = '<span class="nav-icon"><i class="'.($btnIcon1 != '' ? esc_attr($btnIcon1) : '').'" aria-hidden="true"></i></span>'.wp_kses_post($btntxt1);
		$nav_next = wp_kses_post($btntxt2).'<span class="nav-icon"><i class="'.($btnIcon2 != '' ? esc_attr($btnIcon2) : '').'" aria-hidden="true"></i></span>';
	}else if($BiconFont == 'img'){
		if(!empty($Ctmicon1['id']) && !empty($Ctmicon1) ) {
			$altText = carRemAltText($Ctmicon1);
			$nav_prev_icon = wp_get_attachment_image($Ctmicon1['id'],$imgSize, false, ['alt'=> $altText]);
		}else if( !empty($Ctmicon1) && !empty($Ctmicon1['url'])){
			$altText = carRemAltText($Ctmicon1);
			$nav_prev_icon = '<img src="'.esc_url($Ctmicon1['url']).'" alt="'.$altText.'"/>';
		}
		if(!empty($Ctmicon2['id']) && !empty($Ctmicon2) ) {
			$altText = carRemAltText($Ctmicon2);
			$nav_next_icon = wp_get_attachment_image($Ctmicon2['id'],$imgSize, false, ['alt'=> $altText]);
		}else if( !empty($Ctmicon2) && !empty($Ctmicon2['url'])){
			$altText = carRemAltText($Ctmicon2);
			$nav_next_icon = '<img src="'.esc_url($Ctmicon2['url']).'" alt="'.$altText.'"/>';
		}
		
		$nav_prev = '<span class="nav-icon">'.$nav_prev_icon.'</span>'.$nav_prev_text;
		$nav_next = $nav_next_text.'<span class="nav-icon">'.$nav_next_icon.'</span>';
	}
	else {
		$nav_prev = wp_kses_post($btntxt1);
		$nav_next = wp_kses_post($btntxt2);
	}
	
	
	$output .= '<div class="carousel-remote-wrap '.esc_attr($rbtnAlign).'">';
		$output .= '<div class="tpgb-carousel-remote tpex-'.(!empty($carouselId) ? esc_attr($carouselId)  : '' ).' tpgb-block-'.esc_attr($block_id).' remote-'.esc_attr($remType).' '.esc_attr($blockClass).'" data-remote="'.esc_attr($remType).'" '.$dataAttr.'>';
			if(!empty($carobtn)){
				$pAriaLabel = (!empty($attributes['pAriaLabel'])) ? esc_attr($attributes['pAriaLabel']) : esc_attr__('Prev','tpgbp');
				$nAriaLabel = (!empty($attributes['nAriaLabel'])) ? esc_attr($attributes['nAriaLabel']) : esc_attr__('Next','tpgbp');

				$output .= '<div class="slider-btn-wrap">';
					$output .= '<a href="#" class="slider-btn tpgb-trans-easeinout tpgb-prev-btn '.(($remType == 'switcher') ? 'active' : '' ).'" data-id="tpca-'.esc_attr($carouselId).'" data-nav="'.esc_attr("prev","tpgbp").'" aria-label="'.$pAriaLabel.'">';
						$output .= $nav_prev;
					$output .= "</a>";
					$output .= '<a href="#" class="slider-btn tpgb-trans-easeinout tpgb-next-btn" data-id="tpca-'.esc_attr($carouselId).'" data-nav="'.esc_attr("next","tpgbp").'" aria-label="'.$nAriaLabel.'">';
						$output .= $nav_next;
					$output .= "</a>";
				$output .= "</div>";
			}
			if(!empty($showDot)){
				$output .= '<div class="tpgb-carousel-dots dot-'.esc_attr($dotLayout).'">';
					if(!empty($dotList)){
						foreach ( $dotList as $index => $item ) :
							$output .= '<div class="tpgb-carodots-item tpgb-rel-flex tp-repeater-item-'.esc_attr($item['_key']).' '.esc_attr($dotstyle).'" data-tab="'.esc_attr( $index).'">';
								$output .= '<div class="tpgb-dots tpgb-rel-flex tooltip-'.($dotLayout == 'horizontal' ? esc_attr($tooltipDir) : esc_attr($vtooltipDir) ).'">';
									if($item['iconFonts'] == 'font_awesome'){
										$output .= '<i class="dot-icon '.($item['iconName'] != '' ? esc_attr($item['iconName']) : 'fas fa-home' ).'"></i>';
									}else if($item['iconFonts'] == 'image' && !empty($item['iconImage']) && !empty($item['iconImage']['id']) ){
										$iconImgSize = (!empty($item['iconimageSize']) ? $item['iconimageSize'] : 'full' );
										$output .= wp_get_attachment_image($item['iconImage']['id'],$iconImgSize);
									}
									$output .= '<span class="tooltip-txt '.(!empty($tooltiparrow) ? 'tooltip-arrow' : '').'">'.wp_kses_post($item['label']).'</span>';
									$output .= $Asvg;
								$output .= "</div>";
							$output .= "</div>";
						endforeach;
					}
				$output .= "</div>";
			}
			if(!empty($showpagi)){
				$output .= '<div class="carousel-pagination">';
					$output .= '<div class="pagination-list">';
						$output .= '<div class="active">01</div>';
					$output .= '</div>';
					$output .= '<span class="tpgb-caropagi-line">&#47;</span> ';
					$output .= '<span class="totalpage">'.($sliderInd <= 9 ? '0'.esc_html($sliderInd) : esc_html($sliderInd) ).'</span>';
				$output .= "</div>";
			}
		$output .= "</div>";
	$output .= "</div>";
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

function carRemAltText($imageStore = ''){
	$altText = '';
    if(!empty($imageStore)){
        $altText = (isset($imageStore['alt']) && !empty($imageStore['alt'])) ? esc_attr($imageStore['alt']) : ((!empty($imageStore['title'])) ? esc_attr($imageStore['title']) : esc_attr__('Carousel Remote','tpgbp'));
    }else{
        $altText = esc_attr__('Carousel Remote','tpgbp');
    }
    return $altText;
}

/**
	* Render for the server-side
 */
function tpgb_tp_carousel_remote() {
	
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_carousel_remote_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_carousel_remote' );