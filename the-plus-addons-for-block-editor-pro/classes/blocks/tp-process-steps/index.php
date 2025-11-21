<?php
/* Block : Process Steps
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_process_steps( $attributes, $content) {
	$block_id = isset($attributes['block_id']) ? $attributes['block_id'] : '';
	$style = (!empty($attributes['style'])) ? $attributes['style'] : 'style-1';
	$dfltActive = (!empty($attributes['dfltActive'])) ? $attributes['dfltActive'] :'none';
	$nmlLoutMobile = (!empty($attributes['nmlLoutMobile'])) ? $attributes['nmlLoutMobile'] : false;
	$vertOnTablet = (!empty($attributes['vertOnTablet'])) ? $attributes['vertOnTablet'] : false;
	$imgSt2Align = (!empty($attributes['imgSt2Align'])) ? $attributes['imgSt2Align'] : '';
	$cntntSt2Align = (!empty($attributes['cntntSt2Align'])) ? $attributes['cntntSt2Align'] : '';
	$processSteps = (!empty($attributes['processSteps'])) ? $attributes['processSteps'] : [];
	$carouselToggle = (!empty($attributes['carouselToggle'])) ? $attributes['carouselToggle'] : false;
	$carouselID = (!empty($attributes['carouselID'])) ? $attributes['carouselID'] : '';
	$carouselEffect = (!empty($attributes['carouselEffect'])) ? $attributes['carouselEffect'] : 'con_pro_hover';
	$specialBG = (!empty($attributes['specialBG'])) ? $attributes['specialBG'] : false;
	$displayCounter = (!empty($attributes['displayCounter'])) ? $attributes['displayCounter'] : false;
	$counterStyle = (!empty($attributes['counterStyle'])) ? $attributes['counterStyle'] : 'number_normal';
	$bType = (!empty($attributes['bType'])) ? $attributes['bType'] : 'solid';
	$customImg = (!empty($attributes['customImg'])) ? $attributes['customImg'] : '';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$equalHeightAtt = Tpgbp_Pro_Blocks_Helper::global_equal_height( $attributes );
	$equalHclass = '';
	if(!empty($equalHeightAtt)){
		$equalHclass = ' tpgb-equal-height';
	}

	$connect_carousel = $connection_hover_click = $connect_id = '';
	if(!empty($carouselToggle) && !empty($carouselID)){
		$connect_carousel = 'tpca-'.$carouselID ;
		$connect_id = 'tptab_'.$carouselID ;
		$connection_hover_click = $carouselEffect ;
	}
		
	$j=0;
	
	$specbg='';
	if(!empty($specialBG)){
		$specbg='tp-ps-special-bg';
	}
	$custom_sep='';
	if($bType=='custom'){
		$custom_sep = 'tp_ps_sep_img';
	}
	$verti_tablet_class = $mobile_class='';
	if(!empty($nmlLoutMobile)){
		$mobile_class = 'mobile';
	}
	if(!empty($vertOnTablet)){
		$verti_tablet_class = 'verticle-tablet';
	}
	$flexCss = ''; 
	if($style=='style-2'){
		$flexCss = 'tpgb-rel-flex';
	}
	$output = '';
    	$output .= '<div class="tpgb-process-steps '.esc_attr($flexCss).' '.esc_attr($style).' '.esc_attr($custom_sep).' '.esc_attr($mobile_class).' '.esc_attr($verti_tablet_class).' '.esc_attr($imgSt2Align).' '.esc_attr($cntntSt2Align).' tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).' '.esc_attr($equalHclass).'" id="'.esc_attr($connect_id).'" data-connection="'.esc_attr($connect_carousel).'" data-eventtype="'.esc_attr($connection_hover_click).'" '.$equalHeightAtt.'>';
			if(!empty($processSteps)){
				foreach ( $processSteps as $index => $item ) { 
					$j++;
					
					//Set Active class 
					$active_class = '';
					if($j == $dfltActive){
						$active_class = 'active';
					}
					
					$output .='<div class="tp-repeater-item-'.esc_attr($item['_key']).' tpgb-p-s-wrap tpgb-trans-easeinout '.esc_attr($active_class).'" data-index="'.esc_attr($j).'">';
						if(!empty($item['selectIcon']) && $item['selectIcon']!='none'){
							$output .='<div class="tp-ps-left-imt '.esc_attr($specbg).'">';
							if($bType=='custom'){
								$altText = (isset($customImg['alt']) && !empty($customImg['alt'])) ? esc_attr($customImg['alt']) : ((!empty($customImg['title'])) ? esc_attr($customImg['title']) : esc_attr__('Process Step Image','tpgbp'));

								if(isset($customImg['dynamic'])){
									$imgUrl = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($customImg);
									$customImgRender = '<img class="tp-sep-custom-img-inner" src="'.esc_url($imgUrl).'" alt="'.$altText.'"/>';
								}else if(!empty($customImg) && !empty($customImg['id'])){
									$customImgRender = wp_get_attachment_image($customImg['id'] , 'full', false, ['class' => 'tp-sep-custom-img-inner', 'alt'=> $altText]);
								}else if(!empty($customImg['url'])){
									$customImgRender = '<img class="tp-sep-custom-img-inner" src="'.esc_url($customImg['url']).'" alt="'.$altText.'"/>';
								}else{
									$customImgRender = '<img class="tp-sep-custom-img-inner" src="'.esc_url(TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg').'" alt="'.esc_attr__('Process Step Image','tpgbp').'"/>';
								}
								$output .='<span class="separator_custom_img">';
									$output .= $customImgRender;
								$output .='</span>';
							}
								
							if(!empty($item['selectIcon']) && $item['selectIcon']=='icon' && !empty($item['fontAwesomeIcon'])){
								$output .='<span class="tp-ps-icon-img tpgb-rel-flex tpgb-trans-easeinout '.esc_attr($specbg).'">';
									$output .='<i aria-hidden="true" class="'.esc_attr($item['fontAwesomeIcon']).'"></i>';
								$output .='</span>';
							}
							if(!empty($item['selectIcon']) && $item['selectIcon']=='image'){
								$output .='<div class="tp-ps-icon-img tpgb-rel-flex tpgb-trans-easeinout tp-pro-step-icon-img">';
								if(!empty($item['stepImage']['url'])){
									$imgSrc = '';
									$imageSize = (!empty($item['imageSize'])) ? $item['imageSize'] : 'thumbnail';
									$altText = (isset($item['stepImage']['alt']) && !empty($item['stepImage']['alt'])) ? esc_attr($item['stepImage']['alt']) : ((!empty($item['stepImage']['title'])) ? esc_attr($item['stepImage']['title']) : esc_attr__('Process Step Image','tpgbp'));

									if( !empty($item['stepImage']) && !empty($item['stepImage']['id']) ){
										$imgSrc = wp_get_attachment_image($item['stepImage']['id'] , $imageSize, false, ['class' => 'tp-icon-img tpgb-trans-easeinout', 'alt'=> $altText]);
									}else if(!empty($item['stepImage']['url'])){
										$imgUrl = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['stepImage']);
										$imgSrc = '<img class="tp-icon-img tpgb-trans-easeinout" src="'.esc_url($imgUrl).'" alt="'.$altText.'"/>';
									}
									$output .= $imgSrc;
								}
								$output .='</div>';
							}
							if(!empty($item['selectIcon']) && $item['selectIcon']=='text'){
								$output .='<div class="tp-ps-icon-img tpgb-rel-flex tpgb-trans-easeinout tp-pro-step-icon-img">';
									$output .='<span class="tp-ps-text tpgb-trans-easeinout">'.wp_kses_post($item['stepText']).'</span>';
								$output .='</div>';
							}
							
							if(!empty($displayCounter)){
								$output .='<div class="tp-ps-dc tpgb-trans-easeinout-after '.esc_attr($counterStyle).'">';
									if($counterStyle=='dc_custom_text'){
										$output .='<span class="ds_custom_text_label">'.wp_kses_post($item['customText']).'</span>';
									}
								$output .='</div>';
							}
							$output .='</div>';
						}
						$output .='<div class="tp-ps-right-content tpgb-trans-easeinout">';
							$output .='<span class="tp-ps-content">';
								if(!empty($item['linkUrl']) && !empty($item['linkUrl']['url'])){
									$linkUrl = (isset($item['linkUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['linkUrl']) : (!empty($item['linkUrl']['url']) ? $item['linkUrl']['url'] : '');
									$target = (!empty($item['linkUrl']['target'])) ? 'target="_blank"' : '';
									$nofollow = (!empty($item['linkUrl']['nofollow'])) ? 'rel="nofollow"' : '';
									$link_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($item['linkUrl']);
									$output .='<a href="'.esc_url($linkUrl).'" '.$target.' '.$nofollow.'" '.$link_attr.'>';
								}
								if(!empty($item['title'])){
									$output .='<h6 class="tp-pro-step-title">'.wp_kses_post($item['title']).'</h6>';
								}
								if(!empty($item['linkUrl']) && !empty($item['linkUrl']['url'])){
									$output .='</a>';
								}
								if(!empty($item['desc'])){
									$output .='<div class="tp-pro-step-desc">'.wp_kses_post($item['desc']).'</div>';
								}
							$output .='</span>';
						$output .='</div>';
					$output .='</div>';
				}
			}
		$output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
  	return $output;
}
/**
 * Render for the server-side
 */
function tpgb_tp_process_steps() {
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_process_steps');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_process_steps' );