<?php
/* Block : Creative Image
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_creative_image_callback( $settings, $content) {
	
	$block_id	= !empty($settings['block_id']) ? $settings['block_id'] : '';
	$showCaption	= !empty($settings['showCaption']) ? $settings['showCaption'] : false;
	$ImgCaption	= !empty($settings['ImgCaption']) ? $settings['ImgCaption'] : '';
	$fancyBox = (!empty($settings['fancyBox'])) ? $settings['fancyBox'] : false;
	$floatAlign = !empty($settings['floatAlign']) ? $settings['floatAlign'] : '';

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $settings );
	$AnimDirection = $settings['AnimDirection'];

	// Float Align Class
	if(!empty($floatAlign) && $floatAlign!='none'){
		$blockClass .= 'tpgb-image-'.esc_attr($floatAlign);
	}

	$contentImage = $imgID ='';
	if ( isset( $settings['SelectImg']['id'] ) && !empty($settings['SelectImg']['id'])) {
		$imgID = $settings['SelectImg']['id'];
	}
	
	$altText = (isset($settings['SelectImg']['alt']) && !empty($settings['SelectImg']['alt'])) ? esc_attr($settings['SelectImg']['alt']) : ((!empty($settings['SelectImg']['title'])) ? esc_attr($settings['SelectImg']['title']) : esc_attr__('Creative Image', "tpgbp"));


	if ( ! empty( $settings['SelectImg']['url'] ) && ! empty( $settings['SelectImg']['id'] ) ) {
		$attr = array( 'class' => "hover__img info_img", 'alt'=> $altText );
		$contentImage = wp_get_attachment_image($imgID, $settings['ImgSize'],"",$attr);				
	}else if ( ! empty( $settings['SelectImg']['url'] ) ) {
		$contentImage .= '<img src="'.esc_url($settings['SelectImg']['url']).'" class="hover__img info_img" alt="'.$altText.'"/>';
	} else {
		$contentImage .= tpgb_loading_image_grid(get_the_ID());
	}
	
	if(isset($settings['SelectImg']['dynamic'])){
		$imgUrl = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($settings['SelectImg']);
        if(isset($imgUrl['url']) && !empty($imgUrl['url'])){
            $contentImage = '<img src="'.esc_url($imgUrl['url']).'" class="hover__img info_img" alt="'.$altText.'"/>';
        }
	}

	$scrollImage='';
	if(!empty($settings["ScrollImgEffect"])) {
		$fancyImg = (isset($settings['SelectImg']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($settings['SelectImg']) : (!empty($settings['SelectImg']['url']) ? $settings['SelectImg']['url'] : '');
		$contentImage = '<div class="creative-scroll-image" style="background-image: url('.esc_url($fancyImg).')"></div>';
		$scrollImage = 'scroll-image-wrap';
	}
	
	$href = $target = $rel = '';
	$href  = (isset($settings['link']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($settings['link']) : (!empty($settings['link']['url']) ? $settings['link']['url'] : ''); 
	$target  = (!empty($settings['link']['target'])) ? 'target="_blank"' : ''; 
	$rel = (!empty($settings['link']['rel'])) ? 'rel="nofollow"' : '';
	

	$maskImage='';
	if(!empty($settings["showMaskImg"])){
		$maskImage=' tpgb-creative-mask-media';
	}
	$wrapperClass='tpgb-creative-img-wrap '.esc_attr($maskImage).' '.esc_attr($scrollImage);

	$dataImage = '';
	$fancyImg = TPGB_ASSETS_URL. 'assets/images/tpgb-placeholder.jpg';
	if( !empty($settings["ScrollRevelImg"]) || !empty($fancyBox) ){
		if(!empty($settings['SelectImg']['id'])) {
			$fullImage = wp_get_attachment_image_src( $imgID, 'full' );
			$fancyImg= isset($fullImage[0]) ? $fullImage[0] : '';
			$dataImage = (!empty($fullImage)) ? 'background: url('.esc_url($fullImage[0]).');' : '';
		}else if(!empty($settings['SelectImg']['url'])){
			$fancyImg = (isset($settings['SelectImg']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($settings['SelectImg']) : (!empty($settings['SelectImg']['url']) ? $settings['SelectImg']['url'] : '');
			$fullImage = '<img src="'.esc_url($fancyImg).'" alt="'.$altText.'"/>';
			$dataImage = (!empty($fancyImg)) ? 'background: url('.esc_url($fancyImg).');' : '';
		} else {
			$dataImage = tpgb_loading_image_grid('','background');
		}
	}
	$data_settings = '';
	if(!empty($fancyBox)){
		$FancyData = (!empty($settings['FancyOption'])) ? json_decode($settings['FancyOption']) : [];

		$button = array();
		if (is_array($FancyData) || is_object($FancyData)) {
			foreach ($FancyData as $value) {
				$buttonOpt = (($value->value == 'zoom') ? 'iterateZoom' : (($value->value == 'fullScreen') ? 'fullscreen' : $value->value));
				if($value->value != 'share'){
					$button[] = $buttonOpt;
				}
			}
		}
		$fancybox = array();
		$fancybox['button'] = $button;
		$data_settings .= ' data-fancy-option=\''.wp_json_encode($fancybox).'\'';
		$data_settings .= ' data-id="'.esc_attr($block_id).'"';
	}
	
	if ( !empty( $href ) ) {
		$link_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($settings['link']);
		$ariaLabelT = (!empty($settings['ariaLabel'])) ? esc_attr($settings['ariaLabel']) : esc_attr__('Creative Image', "tpgbp");
		if(!empty($settings["ScrollRevelImg"])) {			
			$html = '<a href="'.esc_url($href).'" '.$target.' '.$rel.' class="'.esc_attr($wrapperClass).' tpgb-bg-animate-img '.esc_attr($AnimDirection).'" style="'.$dataImage.'" '.$link_attr.' aria-label="'.$ariaLabelT.'">' .$contentImage. '</a>';
		} else {
			$html = '<a href="'.esc_url($href).'" '.$target.' '.$rel.' class="'.esc_attr($wrapperClass).'" '.$link_attr.' aria-label="'.$ariaLabelT.'">' .$contentImage. '</a>';
		}
	} else {
		$tag = !empty($fancyBox) && empty($settings['ScrollParallax']) ? 'a' : 'div';
		$fancyAttr =  !empty($fancyBox) ? 'href="'.esc_url($fancyImg).'" data-fancybox="fancyImg-'.esc_attr($block_id).'"' : '';
		
		if(!empty($settings["ScrollRevelImg"])) {			
			$html = '<'.Tpgbp_Pro_Blocks_Helper::validate_html_tag($tag).' class="' . esc_attr($wrapperClass) . ' tpgb-bg-animate-img '.esc_attr($AnimDirection).'" style="'.$dataImage.'" '.$fancyAttr.'>' .$contentImage. '</'.Tpgbp_Pro_Blocks_Helper::validate_html_tag($tag).'>';
		} else {
			$html = '<'.Tpgbp_Pro_Blocks_Helper::validate_html_tag($tag).' class="' . esc_attr($wrapperClass) . '" '.$fancyAttr.'>' .$contentImage. '</'.Tpgbp_Pro_Blocks_Helper::validate_html_tag($tag).'>';
		}
	}

	$uid= $block_id;
	$animatedClass='';

	if(!empty($settings["ScrollRevelImg"])) {
		$bgAnimated    = ' tpgb-bg-img-anim ';
		$bgAnim        = ' tpgb-bg-img-animated ';
		$animatedClass = ' animate-general';
	} else {
		$bgAnimated = $bgAnim = '';
	}
	if(!empty($settings["showMaskImg"]) && !empty($settings['MaskImg']['url'])) {
		$maskImg = (isset($settings['MaskImg']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($settings['MaskImg']) : (!empty($settings['MaskImg']['url']) ? $settings['MaskImg']['url'] : '');
	}
	$cssClass = '';
	$cssClass = ' text-' . $settings["Alignment"]['md'] . ' '.esc_attr($animatedClass);
	$cssClass .= (!empty($settings["Alignment"]['sm'])) ? ' text-tablet-' . esc_attr($settings["Alignment"]['sm']) : '';
	$cssClass .= (!empty($settings["Alignment"]['xs'])) ? ' text-mobile-' . esc_attr($settings["Alignment"]['xs']) : '';

	$parallaxImageScroll = '';
	if(!empty($settings['ScrollParallax'])) {
		$parallaxImageScroll = 'section-parallax-img';
		$html .='<figure class="tpgb-creative-img-parallax" data-scroll-parallax-x="'.esc_attr($settings["ScrollMoveX"]).'" data-scroll-parallax-y="'.esc_attr($settings["ScrollMoveY"]).'"><figure class="tpgb-parallax-img-parent"><div class="tpgb-parallax-img-container">';
		$imageUrl = (!empty($imgID)) ? wp_get_attachment_image( $imgID, 'full', false, ['class' => 'tpgb-simple-parallax-img']) : '';
		if(!empty($imageUrl)){
			$imageUrl = $imageUrl;
		}else{
			$imageUrl = '<img class="tpgb-simple-parallax-img" src="'.esc_url($settings['SelectImg']['url']).'" title="">';
		}
		$html .= $imageUrl;
		$html .='</div></figure></figure>';
	}

	$ImageCaption ='';
	if(!empty($showCaption) && !empty($ImgCaption)){
		$ImageCaption .= '<figcaption class="tpgb-img-caption">'.wp_kses_post($ImgCaption).'</figcaption>';
	}
	
	$uidWidget = $block_id;
	$output = '<div id="'.esc_attr($uidWidget).'" class="tpgb-creative-image tpgb-relative-block tpgb-block-'.esc_attr($block_id).' '.esc_attr( $blockClass ).'">';
		$output .= '<div class="tpgb-anim-img-parallax tpgb-relative-block">';
			$output .= '<div class="tpgb-animate-image bg-image'.esc_attr($uid).' ' .  trim( $cssClass ) . ' '.esc_attr($bgAnim).' '.(!empty($fancyBox) ? 'tpgb-fancy-add' : '').'" '.$data_settings.'>';
				$output .= '<figure class="'.esc_attr($parallaxImageScroll).' '.esc_attr($bgAnimated).'">';
					$output .= $html;
				$output .= '</figure>';
				$output .= $ImageCaption;
			$output .= '</div>';
		$output .= '</div>';

	$output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($settings, $output);
	
	return $output;
}

function tpgb_tp_creative_image_render() {
	
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_creative_image_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_creative_image_render' );

function tpgb_loading_image_grid($postid = '', $type = '') {
	global $post;
	$contentImage = '';
	if($type!='background'){		
		$imageUrl = TPGB_ASSETS_URL. 'assets/images/tpgb-placeholder.jpg';
		$contentImage = '<img src="'.esc_url($imageUrl).'" alt="'.esc_attr(get_the_title()).'"/>';
		return $contentImage;
	} elseif($type == 'background') {
		$imageUrl = TPGB_ASSETS_URL. 'assets/images/tpgb-placeholder.jpg';
		$dataSrc = "background:url(".esc_url($imageUrl).") #f7f7f7;";
		return $dataSrc;
	}
}