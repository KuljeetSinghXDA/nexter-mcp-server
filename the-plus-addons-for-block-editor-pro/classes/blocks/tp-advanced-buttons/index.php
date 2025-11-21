<?php
/* Block : Advanced Buttons
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_advanced_buttons_render_callback( $attributes ) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$btnType = (!empty($attributes['btnType'])) ? $attributes['btnType'] : 'cta';
	$ctaStyle = (!empty($attributes['ctaStyle'])) ? $attributes['ctaStyle'] : 'style-1';
	$dwnldStyle = (!empty($attributes['dwnldStyle'])) ? $attributes['dwnldStyle'] : 'style-1';
	$btnText = (!empty($attributes['btnText'])) ? $attributes['btnText'] : '';
	$extraText = (!empty($attributes['extraText'])) ? $attributes['extraText'] : '';
	$extraText1 = (!empty($attributes['extraText1'])) ? $attributes['extraText1'] : '';
	$btnLink = (!empty($attributes['btnLink']['url'])) ? $attributes['btnLink']['url'] : '';
	$target = (!empty($attributes['btnLink']['target'])) ? 'target="_blank"' : '';
	$nofollow = (!empty($attributes['btnLink']['nofollow'])) ? 'rel="nofollow"' : '';
	$link_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($attributes['btnLink']);
	$dwnldFileName = (!empty($attributes['dwnldFileName'])) ? $attributes['dwnldFileName'] : '';
	$extraTextColor = (!empty($attributes['extraTextColor'])) ? $attributes['extraTextColor'] : '';
	
	$marqueeSpeed = (!empty($attributes['marqueeSpeed'])) ? $attributes['marqueeSpeed'] : '12';
	$marqueeDir = (!empty($attributes['marqueeDir'])) ? $attributes['marqueeDir'] : 'left';
	$ariaLabel = (!empty($attributes['ariaLabel'])) ? $attributes['ariaLabel'] : '';
	
	$tooltipPos = (!empty($attributes['tooltipPos'])) ? $attributes['tooltipPos'] : 'left';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$btnLink = (isset($attributes['btnLink']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['btnLink']) : (!empty($attributes['btnLink']['url']) ? $attributes['btnLink']['url'] : '');

	$dyWidth = (array)$attributes['cta10Width'];
	$dyHeight = (array)$attributes['cta10Height'];
	$ctaMDWidth = $ctaMDHeight = '';
	if($btnType=='cta' && $ctaStyle=='style-10'){
		$ctaMDWidth = (!empty($dyWidth) && !empty($dyWidth['md'])) ? $dyWidth['md'] : '150';
	}
	if($btnType=='cta' && $ctaStyle=='style-10'){
		$ctaMDHeight = (!empty($dyHeight) && !empty($dyHeight['md'])) ? $dyHeight['md'] : '50';
	}
	
	$styleClass ='' ;
	
	if($btnType=='cta'){
		$styleClass .= ' tpgb-cta-'.$ctaStyle;
	} else {
		$styleClass .= ' tpgb-download-'.$dwnldStyle;
	}
	if($btnType=='cta' && $ctaStyle=='style-13' && $tooltipPos=='left'){
		$styleClass .= ' style-13-align-left';
	}
	if($btnType=='cta' && $ctaStyle=='style-13' && $tooltipPos=='right'){
		$styleClass .= ' style-13-align-right';
	}
	
	$data_attr = '';
	
	$download_attr = '';
	if($btnType=='download'){
		$data_attr .= ' data-dfname='.esc_attr($dwnldFileName).'';
		$download_attr .=' download='.esc_attr($dwnldFileName).'';
	}
	$uid_advbutton=uniqid("advbutton");
	$ariaLabelT = (!empty($ariaLabel)) ? esc_attr($ariaLabel) : ((!empty($btnText)) ? esc_attr($btnText) : esc_attr__("Button", 'tpgbp'));
	
    $output .= '<div class="tpgb-advanced-buttons tpgb-relative-block tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
		if($btnType=='download' && $dwnldStyle!='style-3' && $dwnldStyle!='style-5') {
			$output .='<div class="adv_btn_ext_txt">'.wp_kses_post($btnText).'</div>';
		}
		$output .= '<div id="'.esc_attr($uid_advbutton).'" class="tpgb-adv-btn-inner ab-'.esc_attr($btnType).' '.esc_attr($styleClass).'" '.$data_attr.'>';
			if($btnType=='cta' && $ctaStyle=='style-4'){
				$output .= '<div class="pulsing"></div>';
			}
			$output .= '<a href="'.esc_url($btnLink).'" '.$target.' '.$nofollow.' class="adv-button-link-wrap tpgb-trans-ease tpgb-trans-ease-before" role="button" aria-label="'.$ariaLabelT.'" '.$download_attr.' '.$link_attr.'>';
				if($btnType=='cta' && ($ctaStyle!='style-5' && $ctaStyle!='style-6' && $ctaStyle!='style-8' && $ctaStyle!='style-9' && $ctaStyle!='style-13')){
					$output .= '<span class="tpgb-trans-ease">'.wp_kses_post($btnText).'</span>';
				}
				if($btnType=='cta' && $ctaStyle=='style-5'){
					$output .= '<p class="tpgb-cta-style-5-text">'.wp_kses_post($btnText).'</p>';
				}
				if($btnType=='cta' && ($ctaStyle=='style-6' || $ctaStyle=='style-8' || $ctaStyle=='style-9' || $ctaStyle=='style-13')){
					if($ctaStyle!='style-13'){
						$output .= wp_kses_post($btnText);
					}
					if($btnType=='cta' && $ctaStyle=='style-6'){
						$output .= '<marquee scrollamount="'.esc_attr($marqueeSpeed).'" direction="'.esc_attr($marqueeDir).'">';
							$output .= '<span class="tpgb-trans-ease">'.wp_kses_post($extraText1).'</span>';
						$output .= '</marquee>';
					}
					if($btnType=='cta' && $ctaStyle=='style-8'){
						for ($ij = 1; $ij <= 3; $ij++) {
						  $output .= '<div class="adv-btn-emoji"></div>';
						}
					}
					if($btnType=='cta' && $ctaStyle=='style-9'){
						for ($ij = 1; $ij <= 6; $ij++) {
							$output .= '<div class="adv-btn-parrot"></div>';
						}
					}
					if($btnType=='cta' && $ctaStyle=='style-13'){
						$output .= '<span class="tpgb-trans-ease sty13-main-text">'.wp_kses_post($btnText).'</span>';
						$output .= '<span class="tpgb-trans-ease sty13-extra-text">'.wp_kses_post($extraText1).'</span>';
					}
				}
				if($btnType=='cta' && $ctaStyle=='style-7'){
					$output .= '<div class="hands"></div>';
				}
				if($btnType=='cta' && $ctaStyle=='style-10'){
					$output .= '<svg>';
						$output .='<polyline class="tpgb-cpt-btn01" points="0 0, '.esc_attr($ctaMDWidth).' 0, '.esc_attr($ctaMDWidth).' '.esc_attr($ctaMDHeight).', 0 '.esc_attr($ctaMDHeight).', 0 0"></polyline>';
						$output .='<polyline class="tpgb-cpt-btn02" points="0 0, '.esc_attr($ctaMDWidth).' 0, '.esc_attr($ctaMDWidth).' '.esc_attr($ctaMDHeight).', 0 '.esc_attr($ctaMDHeight).', 0 0"></polyline>';
					$output .= '</svg>';
				}
				if($btnType=='download' && $dwnldStyle=='style-1') {
					$output .= '<svg width="22" height="16" viewBox="0 0 22 16"><path d="m2 10 4 3 6.88-8.4A4.2 4.2 0 0 1 18 3.5c1.84.92 3 2.8 3 4.85V10a5 5 0 0 1-5 5H1" id="check"/><path class="svg-out" d="M4.5 8.5 8 11l3.5-2.5M8 1v10"/></svg>';
				}
				if($btnType=='download' && $dwnldStyle=='style-2') {
					$output .= '<svg id="arrow" width="14" height="20" viewBox="17 14 14 20"><path d="M24 15v17M30 27l-6 6-6-6"/></svg><svg id="check" width="21" height="15" viewBox="13 17 21 15"><path d="M32.5 18.5 20 31l-5.5-5.5"/></svg><svg id="border" width="48" height="48" viewBox="0 0 48 48"><path d="M24 1a23 23 0 0 1 0 46 23 23 0 0 1 0-46Z"/></svg>';
				}
				if($btnType=='download' && $dwnldStyle=='style-3') {
					$output .= '<span class="tpgb-trans-ease dw-sty3-extra-text">'.wp_kses_post($extraText).'</span><span class="tpgb-trans-ease dw-sty3-main-text">'.wp_kses_post($btnText).'</span>';
				}
				if($btnType=='download' && $dwnldStyle=='style-4') {
					$output .= '<span class="tpgb-trans-ease adv-btn-icon">';
						$output .= '<i class="fas fa-download cmn-icon btn-icon-start"></i>';
						$output .= '<i class="fas fa-circle-notch cmn-icon btn-icon-load"></i>';
						$output .= '<i class="fas fa-check cmn-icon btn-icon-success"></i>';
					$output .= '</span>';
				}
				if($btnType=='download' && $dwnldStyle=='style-5') {
					$output .= wp_kses_post($btnText);
					 $output .= '<span class="tpgb-trans-ease icon-wrap">';
						$output .= '<i class="icon-download"></i>';
					$output .= '</span>';
				}
			$output .= '</a>';
				if($btnType=='download' && $dwnldStyle=='style-5') {
					$output .= '<div class="tp-meter">';
						$output .= '<span class="tpgb-trans-ease tp-meter-progress"></span>';
					$output .= '</div>';
				}
		$output .= '</div>';
    $output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);

    return $output;
}

function tpgb_tp_advanced_buttons() {
    
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_advanced_buttons_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_advanced_buttons' );