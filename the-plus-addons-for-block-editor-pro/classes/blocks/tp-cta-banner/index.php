<?php
/* Block : CTA Banner
 * @since : 1.2.1
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_cta_banner_render_callback( $attributes, $content) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] :'';
	$styleType = (!empty($attributes['styleType'])) ? $attributes['styleType'] : 'style-1';
	$contentHoverEffect = (!empty($attributes['contentHoverEffect'])) ? $attributes['contentHoverEffect'] : false;
	$selectHoverEffect = (!empty($attributes['selectHoverEffect'])) ? $attributes['selectHoverEffect'] : '';
	$hoverStyle = (!empty($attributes['hoverStyle'])) ? $attributes['hoverStyle'] : 'cta_img_blur';
	$extBtnshow = (!empty($attributes['extBtnshow'])) ? $attributes['extBtnshow'] : false;
	$subTitle = (!empty($attributes['subTitle'])) ? $attributes['subTitle'] : '';
	$Title = (!empty($attributes['Title'])) ? $attributes['Title'] : '';
	$bannerImage  =  (!empty($attributes['bannerImage'])) ? $attributes['bannerImage'] : [] ;
	$imageSize = (!empty($attributes['imageSize'])) ? $attributes['imageSize'] : 'full';
	$desc = (!empty($attributes['desc'])) ? $attributes['desc'] : '';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	// Set Size of banner image 
	$altText = (isset($bannerImage['alt']) && !empty($bannerImage['alt'])) ? esc_attr($bannerImage['alt']) : ((!empty($bannerImage['title'])) ? esc_attr($bannerImage['title']) : esc_attr__('Banner Image','tpgbp'));

	if(!empty($bannerImage) && !empty($bannerImage['id'])){
		$banner_img = $bannerImage['id'];
		$imgRender = wp_get_attachment_image($banner_img , $imageSize,false, ['class' => 'banner-img', 'alt'=> $altText]);
		$imgSrc = wp_get_attachment_image_src($banner_img , $imageSize);
		$imgSrc = ( isset($imgSrc[0]) && !empty($imgSrc[0]) ) ? $imgSrc[0] : TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg';
	}else if(!empty($bannerImage['url'])){
		$imgRender = '<img class="banner-img" src="'.esc_url($bannerImage['url']).'" alt="'.$altText.'"/>';
		$imgSrc = $bannerImage['url'];
	}else{
		$imgRender = '<img class="banner-img" src="'.esc_url(TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg').'" alt="'.$altText.'"/>';
		$imgSrc = TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg';
	}
	
	//Set text wrap class on change style type

	$text_wrap_style = "";	
	if($styleType=='style-1'){ 	
		$text_wrap_style = 'top-left';
	}else if($styleType=='style-2'){ 
		$text_wrap_style = 'center-left';
	}else if($styleType=='style-3'){ 
		$text_wrap_style = 'bottom-left';
	}else if($styleType=='style-4'){ 
		$text_wrap_style = 'top-right text-right';
	}else if($styleType=='style-5'){ 
		$text_wrap_style = 'center-right text-right';
	}else if($styleType=='style-6'){ 
		$text_wrap_style = 'bottom-right text-right';
	}else if($styleType=='style-7'){ 
		$text_wrap_style = 'text-center';
	}else if($styleType=='style-8'){ 
		$text_wrap_style = 'bottom-right';
	}

	// Get Title of Banner
	$getTitle = '';
	if(!empty($Title)){
		$getTitle .= '<h3 class="cta-title tpgb-trans-easeinout">'. wp_kses_post($Title) .'</h3>';
	}

	// Get SubTitle of Banner
	$getSubtitle = '';
	if(!empty($subTitle)){
		$getSubtitle .= '<h4 class="cta-subtitle tpgb-trans-easeinout">'.wp_kses_post($subTitle).'</h4>';
	}
	//Set Description
	$getDesc = '';
	if(!empty($desc)){
		$getDesc .= '<div class="cta-desc tpgb-trans-easeinout">'.wp_kses_post($desc).'</div>';
	}

	//Get Button 
	$getbutton = '';
	$getbutton .= Tpgb_Blocks_Global_Options::load_plusButton_saves($attributes);

    $output .= '<div class="tpgb-cta-banner tpgb-block-'.esc_attr($block_id).' cta-'.esc_attr($styleType).' '.($contentHoverEffect ? 'tpgb_cnt_hvr_effect cnt_hvr_'.esc_attr($selectHoverEffect) : '').' '.esc_attr($blockClass).'">';
		if($styleType !='style-8'){
			$output .= '<div class="cta-block tpgb-relative-block '.esc_attr($hoverStyle).'">';
				$output .= '<div class="cta-block-inner tpgb-relative-block tpgb-trans-easeinout">';
					$output .= '<div class="'.esc_attr($text_wrap_style).'">';
						$output .= '<div class="content-level2">';
							$output .= '<div class="content-level3">';
								if(!empty($extBtnshow)){
									$extBtnUrl = (!empty($attributes['extBtnUrl'])) ? $attributes['extBtnUrl'] : '';
									$output .= $getSubtitle;
										$btntarget = !empty($extBtnUrl['target']) ? 'target="_blank"' : '';
                                        $extUrl = '';
                                        if(class_exists('Tpgbp_Pro_Blocks_Helper')){    
                                            $extUrl = (isset($attributes['extBtnUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['extBtnUrl']) : (!empty($attributes['extBtnUrl']['url']) ? $attributes['extBtnUrl']['url'] : '');
                                        }
										$output .= '<a href="'.(!empty($extUrl) ? $extUrl  : '').'" '.$btntarget.'>';
											$output .= $getTitle;
										$output .= '</a>';
									$output .= $getDesc;
									$output .= $getbutton;
								}else{
									$output .= $getSubtitle;
									$output .= $getTitle;
									$output .= $getDesc;
								}
							$output .= '</div>';
						$output .= '</div>';
					$output .= '</div>';
					$output .= '<div class="cta-block-inner_img">';
						$output .= $imgRender;
					$output .= '</div>';
					$output .= '<div class="entry-thumb">';
						$output .= '<div class="entry-hover tpgb-trans-easeinout-before">';
						$output .= '</div>';
					$output .= '</div>';
				$output .= '</div>';
			$output .= '</div>';
		}else{
			$output .= '<div class="cta-product-box"> ';
				$output .= '<div class="cta-product-box-inner" style="background-image: url('.esc_url($imgSrc).')">';
					$output .= '<div class="cta-img-hide">';
						$output .= $imgRender;
					$output .= "</div>";
					$output .= '<div class="cta-content">';
						$output .= $getTitle;
						$output .= $getSubtitle;
						$output .= $getDesc;
					$output .= "</div>";
				$output .= "</div>";
				$output .= '<div class="cta-btn-block">';
					if(!empty($extBtnshow)){
						$output .= $getbutton;
					}
				$output .= "</div>";
			$output .= "</div>";
		}
		
	$output .= "</div>";
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_cta_banner() {

	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_cta_banner_render_callback', true, false, true);
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_cta_banner' );