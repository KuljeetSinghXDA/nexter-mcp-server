<?php
/* Block : Dynamic Device
 * @since : 1.4.2
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_dynamic_device_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$layoutType = (!empty($attributes['layoutType'])) ? $attributes['layoutType'] : 'normal';
	$deviceType = (!empty($attributes['deviceType'])) ? $attributes['deviceType'] : 'mobile';
	$mobileDevice = (!empty($attributes['mobileDevice'])) ? $attributes['mobileDevice'] : 'iphone-white-flat';
	$tabletDevice = (!empty($attributes['tabletDevice'])) ? $attributes['tabletDevice'] : 'ipad-vertical-white';
	$laptopDevice = (!empty($attributes['laptopDevice'])) ? $attributes['laptopDevice'] : 'laptop-macbook-black';
	$desktopDevice = (!empty($attributes['desktopDevice'])) ? $attributes['desktopDevice'] : 'desktop-imac-minimal';
	$customMedia = (!empty($attributes['customMedia'])) ? $attributes['customMedia'] : [];

	$cDeviceType = (!empty($attributes['cDeviceType'])) ? $attributes['cDeviceType'] : 'mobile';
	$cMobileDevice = (!empty($attributes['cMobileDevice'])) ? $attributes['cMobileDevice'] : 'iphone-white-flat';
	$cLaptopDevice = (!empty($attributes['cLaptopDevice'])) ? $attributes['cLaptopDevice'] : 'laptop-macbook-black';
	$cDesktopDevice = (!empty($attributes['cDesktopDevice'])) ? $attributes['cDesktopDevice'] : 'desktop-imac-minimal';
	$cCustomMedia = (!empty($attributes['cCustomMedia'])) ? $attributes['cCustomMedia'] : [];

	$contentType = (!empty($attributes['contentType'])) ? $attributes['contentType'] : 'image';
	$conImage = (!empty($attributes['conImage'])) ? $attributes['conImage'] : [];
	$onClickEfct = (!empty($attributes['onClickEfct'])) ? $attributes['onClickEfct'] : 'nothing';
	$onClickLink = (!empty($attributes['onClickLink']['url'])) ? $attributes['onClickLink']['url'] : '';
	$target = (!empty($attributes['onClickLink']['target'])) ? 'target="_blank"' : '';
	$nofollow = (!empty($attributes['onClickLink']['nofollow'])) ? 'rel="nofollow"' : '';

	$blockTemp = (!empty($attributes['blockTemp'])) ? $attributes['blockTemp'] : 'none';
	$conIframe = (!empty($attributes['conIframe'])) ? $attributes['conIframe'] : [];
	$showIcon = (!empty($attributes['showIcon'])) ? $attributes['showIcon'] : false;
	$iconSrc = (!empty($attributes['iconSrc'])) ? $attributes['iconSrc'] : [];
	$cConImg = (!empty($attributes['cConImg'])) ? $attributes['cConImg'] : [];

	$scrollDimage = (!empty($attributes['scrollDimage'])) ? $attributes['scrollDimage'] : false;
	$scrollManual = (!empty($attributes['scrollManual'])) ? $attributes['scrollManual'] : false;
	$dyDevConId = (!empty($attributes['dyDevConId'])) ? $attributes['dyDevConId'] : '';

	$rebHoverScroll = (!empty($attributes['rebHoverScroll'])) ? $attributes['rebHoverScroll'] : false;
	$dyDevRebConId = (!empty($attributes['dyDevRebConId'])) ? $attributes['dyDevRebConId'] : '';

	$iconConAni = (!empty($attributes['iconConAni'])) ? $attributes['iconConAni'] : false;
	$iconConHoverAnimation = (!empty($attributes['iconConHoverAnimation'])) ? $attributes['iconConHoverAnimation'] : false;
	$iconConAniStyle = (!empty($attributes['iconConAniStyle'])) ? $attributes['iconConAniStyle'] : 'pulse';

	$columnSlide = (!empty($attributes['columnSlide'])) ? $attributes['columnSlide'] : 'single';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$ddImages = TPGB_ASSETS_URL.'assets/images/dynamic-devices/';
	$shapeImage = $deviceTclass = $deviceNclass = $devUrlStart = $devUrlEnd =  $fancybox_settings = $FancyBoxJS= $continuesAniClass = '';
	$shapeAlt = '';
	if($layoutType=='normal'){
		if($deviceType=='mobile'){
			$shapeImage = $ddImages.$mobileDevice.'.png';
			$deviceNclass = $mobileDevice;
			$shapeAlt = 'Mobile Device';
		}else if($deviceType=='tablet'){
			$shapeImage = $ddImages.$tabletDevice.'.png';
			$deviceNclass = $tabletDevice;
			$shapeAlt = 'Tablet Device';
		}else if($deviceType=='laptop'){
			$shapeImage = $ddImages.$laptopDevice.'.png';
			$deviceNclass = $laptopDevice;
			$shapeAlt = 'Laptop Device';
		}else if($deviceType=='desktop'){
			$shapeImage = $ddImages.$desktopDevice.'.png';
			$deviceNclass = $desktopDevice;
			$shapeAlt = 'Desktop Device';
		}else if($deviceType=='custom' && !empty($customMedia) && !empty($customMedia['url'])){
			$altCust = (isset($customMedia['alt']) && !empty($customMedia['alt'])) ? esc_attr($customMedia['alt']) : ((!empty($customMedia['title'])) ? esc_attr($customMedia['title']) : esc_attr__('Custom Device','tpgbp'));
			$shapeImage = $customMedia['url'];
			$deviceNclass = "custom-device-mockup";
			$shapeAlt = $altCust;
		}
		$deviceTclass = 'device-type-'.$deviceType;

		if(!empty($iconConAni)){
			if(!empty($iconConHoverAnimation)){
				$continuesAniClass = 'tpgb-hover-'.$iconConAniStyle;
			}else{
				$continuesAniClass = 'tpgb-normal-'.$iconConAniStyle;
			}
		}
		
		if(($onClickEfct=='link' || $onClickEfct=='popup') && !empty($onClickLink)){
			if($onClickEfct=='popup'){

				$button = $fancybox = array();
				$button[] = 'close';
				$fancybox['button'] = $button;
				$fancybox_settings = wp_json_encode($fancybox);
				
				$FancyBoxJS = 'data-fancybox="'.esc_attr($block_id).'" data-type="iframe"';
			}
			$devUrlStart = '<a class="tpgb-media-link" href="'.esc_url($onClickLink).'" '.$target.' '.$nofollow.' '.$FancyBoxJS.'>';
			$devUrlEnd = '</a>';
		}
	}

	if($layoutType=='carousel'){
		if($cDeviceType=='mobile'){
			$shapeImage = $ddImages.$cMobileDevice.'.png';
			$shapeAlt = 'Mobile Device';
		}else if($cDeviceType=='laptop'){
			$shapeImage = $ddImages.$cLaptopDevice.'.png';
			$shapeAlt = 'Laptop Device';
		}else if($cDeviceType=='desktop'){
			$shapeImage = $ddImages.$cDesktopDevice.'.png';
			$shapeAlt = 'Desktop Device';
		}else if($cDeviceType=='custom' && !empty($cCustomMedia) && !empty($cCustomMedia['url'])){
			$shapeImage = $cCustomMedia['url'];
			$altCust = (isset($cCustomMedia['alt']) && !empty($cCustomMedia['alt'])) ? esc_attr($cCustomMedia['alt']) : ((!empty($cCustomMedia['title'])) ? esc_attr($cCustomMedia['title']) : esc_attr__('Custom Device','tpgbp'));
			$shapeAlt = $altCust;
		}
	}

	$ajaxbase = !empty($attributes['ajaxbase']) ? $attributes['ajaxbase'] : '';
    $triclass = $cntClass = '';
    if( !empty($ajaxbase) && $ajaxbase == 'ajax-base' ){
        $triclass = 'tpgb-load-template-view tpgb-load-'.esc_attr( $blockTemp );
        $cntClass = 'tpgb-load-'.esc_attr( $blockTemp ).'-content';
    }
   
    if( isset( $conImage['dynamic'] ) && !empty( $conImage['dynamic'] ) ){
        $conImage['url'] = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($conImage);
    }

	$getDDmedia = $mulConnClass = $ddattr = $rebTempScroll = $rebMulScrollClass = $rebMulScrollAttr = $scroll_class = '';
	$getDDmedia .= '<div class="tpgb-media-inner tpgb-dd-trans-cb">';
		$getDDmedia .= '<div class="tpgb-media-screen tpgb-dd-trans-cb">';
			$getDDmedia .= '<div class="tpgb-media-screen-inner '.esc_attr($triclass).'">';
				if($contentType=='image' && !empty($conImage) && !empty($conImage['url'])){
					$altText = (isset($conImage['alt']) && !empty($conImage['alt'])) ? esc_attr($conImage['alt']) : ((!empty($conImage['title'])) ? esc_attr($conImage['title']) : esc_attr__('Dynamic Device','tpgbp'));

					if($layoutType=='normal' && !empty($scrollDimage)){
						if(!empty($scrollManual)){
							$getDDmedia .= '<div class="creative-scroll-image tpgb-relative-block manual"><img src="'.esc_url($conImage['url']).'" alt="'.$altText.'"/></div>';
						}else{
							if(!empty($dyDevConId)){
								$mulConnClass = "tpgb-dd-multi-connect ".$dyDevConId;
								$ddattr = ' data-connectdd="'.esc_attr($dyDevConId).'"';
							}
							$getDDmedia .= '<div class="creative-scroll-image tpgb-relative-block" style="background-image: url('.esc_url($conImage['url']).')"></div>';
						}
						$scroll_class = ' tpgb-img-scrl-enable';
					}else{
						$getDDmedia .= '<img class="tpgb-media-image" src="'.esc_url($conImage['url']).'" alt="'.$altText.'"></img>';
					}
				}
				if($contentType=='iframe' && !empty($conIframe) && !empty($conIframe['url'])){
					$iframeTitle = (!empty($attributes['iframeTitle'])) ? esc_attr($attributes['iframeTitle']) : esc_attr__('Content Frame','tpgbp');
					$getDDmedia .= '<iframe width="100%" height="100%" frameborder="0" src="'.esc_url($conIframe['url']).'" title="'.$iframeTitle.'"></iframe>';
				}
				if($contentType=='reusableBlock' && !empty($blockTemp) && $blockTemp!='none'){
					if(!empty($rebHoverScroll)){
						if(!empty($dyDevRebConId)){
							$rebMulScrollClass = 'tpgb-mul-reb-connect '.$dyDevRebConId;
							$rebMulScrollAttr = ' data-connectdd="'.esc_attr($dyDevRebConId).'"';
						}else{
							$rebTempScroll = 'reusable-block-hover-scroll';
						}
					}
					ob_start();
						if(!empty($blockTemp)) {
							echo Tpgb_Library()->plus_do_block($blockTemp);
						}
						if( !empty($ajaxbase) && $ajaxbase == 'ajax-base' ){
							$getDDmedia .= '<div class="'.esc_attr($cntClass).'"></div>';
						}else{
							$getDDmedia .= ob_get_contents();
						}
					ob_end_clean();
				}
				$getDDmedia .= $devUrlStart;
				if($contentType!='iframe' && !empty($showIcon) && !empty($iconSrc) && !empty($iconSrc['url'])){
					$altText2 = (isset($iconSrc['alt']) && !empty($iconSrc['alt'])) ? esc_attr($iconSrc['alt']) : ((!empty($iconSrc['title'])) ? esc_attr($iconSrc['title']) : esc_attr__('Dynamic Device Icon','tpgbp'));

					$getDDmedia .= '<div class="tpgb-device-icon">';
						$getDDmedia .= '<div class="tpgb-device-icon-inner '.esc_attr($continuesAniClass).'">';
							$getDDmedia .= '<img src="'.esc_url($iconSrc['url']).'" alt="'.$altText2.'"></img>';
						$getDDmedia .= '</div>';
					$getDDmedia .= '</div>';
				}
				$getDDmedia .= $devUrlEnd;
			$getDDmedia .= '</div>';
		$getDDmedia .= '</div>';
	$getDDmedia .= '</div>';

	//Carousel Options
	$count = $carouselClass = $carousel_settings = $columnClass = $Sliderclass = '';
	if($layoutType=='carousel'){
		$columnClass = 'column-'.$columnSlide;
		$carouselClass = 'tpgb-carousel splide';

		$cenpadding = isset( $attributes['centerPadding'] ) ? (array) $attributes['centerPadding'] : '';

		$carousel_settings = [
			'updateOnMove' => true,
			'autoplay' => isset( $attributes['slideAutoplay'] ) ? $attributes['slideAutoplay'] : false,
			'speed' => isset( $attributes['slideSpeed'] ) ? (int)$attributes['slideSpeed'] : 1500,
			'interval' => isset( $attributes['slideAutoplaySpeed'] ) ? (int)$attributes['slideAutoplaySpeed'] : '',
			'drag' => true ,
			'focus' => 'center' ,
			'type' => !empty( $attributes['slideInfinite'] ) ? 'loop' : 'slide',
			'pauseOnHover' => isset( $attributes['slideHoverPause'] ) ? $attributes['slideHoverPause'] : false,
			'pagination' => isset( $attributes['showDots']['md'] ) ? $attributes['showDots']['md'] : false ,
			'padding' =>  isset( $cenpadding['md'] ) ? (int) $cenpadding['md'] : '',
			
			'breakpoints' => [
				'1024' => [
					'pagination' => ( !isset($attributes['showDots']['sm']) ) ? $attributes['showDots']['md'] : ( isset($attributes['showDots']['sm'])  ? $attributes['showDots']['sm'] : false ) ,
					'padding' => ( !isset( $cenpadding['sm']) ) ? (isset( $cenpadding['md'] ) ? (int) $cenpadding['md'] : '') : ( isset($cenpadding['sm'])  ? $cenpadding['sm'] : '' ),
					'drag' => true,
					'focus' => 'center' ,
				],
				'767' => [
					'pagination' => ( !isset($attributes['showDots']['xs']) ) ? ( (!isset($attributes['showDots']['sm'])) ? $attributes['showDots']['md'] : $attributes['showDots']['sm'] ) : (isset($attributes['showDots']['xs']) ? $attributes['showDots']['xs'] : false),
					'padding' =>  ( !isset($cenpadding['xs']) ) ? ( (!isset($cenpadding['sm'])) ? (isset( $cenpadding['md'] ) ? (int) $cenpadding['md'] : '') : $cenpadding['sm'] ) : (isset($cenpadding['xs']) ? $cenpadding['xs'] : ''),
					'drag' => true ,
					'focus' => 'center' ,
				]
			],
		];
		$carousel_settings = 'data-splide=\'' . wp_json_encode($carousel_settings) . '\'';
		$Sliderclass = Tpgbp_Pro_Blocks_Helper::tpgb_carousel_arrowdot_class($attributes);
	}
	
	
	

	$output = $scrolljsclass = '';
    $output .= '<div class="tpgb-dynamic-device tpgb-relative-block tpgb-block-'.esc_attr($block_id).' '.esc_attr($deviceNclass).' '.esc_attr($deviceTclass).' '.esc_attr($blockClass).' '.esc_attr($mulConnClass).' '.esc_attr($rebTempScroll).' '.esc_attr($rebMulScrollClass).'" '.$ddattr.' '.$rebMulScrollAttr.' data-id="'.esc_attr($block_id).'" data-fancy-option=\''.$fancybox_settings.'\'>';
		$output .= '<div class="tpgb-device-inner tpgb-dd-trans-cb">';
			if($layoutType=='normal'){
				if(!empty($scrollDimage)){
					$scrolljsclass = 'tpgb-scroll-img-js';
				}
				$output .= '<div class="tpgb-device-content tpgb-dd-trans-cb '.esc_attr($scrolljsclass).'">';
					$output .= '<div class="tpgb-device-shape tpgb-dd-trans-cb">';
					if(!empty($shapeImage)){
						$output .= '<img class="tpgb-device-image tpgb-dd-trans-cb" src="'.esc_url($shapeImage).'" alt="'.$shapeAlt.'"/>';
					}
					$output .= '</div>';
					$output .= '<div class="tpgb-device-media tpgb-dd-trans-cb '.esc_attr($scroll_class).'">';
						$output .= $getDDmedia;
					$output .= '</div>';
				$output .= '</div>';
			}else if($layoutType=='carousel'){
				$output .= '<div class="tpgb-carousel-device-mokeup tpgb-dd-trans-cb">';
					$output .= '<div class="tpgb-device-content tpgb-dd-trans-cb">';
					if(!empty($shapeImage)){
						$output .= '<img class="tpgb-device-image tpgb-dd-trans-cb" src="'.esc_url($shapeImage).'" alt="'.$shapeAlt.'"/>';
					}
					$output .= '</div>';
				$output .= '</div>';

				$output .= '<div class="tpgb-device-carousel '.esc_attr($columnClass).' '.esc_attr($carouselClass).' '.esc_attr($Sliderclass).'" '.$carousel_settings.'>';
					
					$output .= '<div class="splide__track">';
						$output .= '<div class="splide__list">';
							if(!empty($cConImg)){
								foreach ( $cConImg as $index => $item ) {
									$count++;
									$output .= '<div class="splide__slide tpgb-device-slide tpgb-dd-trans-cb" data-index="'.esc_attr($count).'">';
										$altText3 = (isset($item['alt']) && !empty($item['alt'])) ? esc_attr($item['alt']) : ((!empty($item['title'])) ? esc_attr($item['title']) : esc_attr__('Image Content','tpgbp'));

										$output .= '<img src="'.esc_url($item['url']).'" alt="'.$altText3.'"/>';
									$output .= '</div>';
								}
							}
						$output .= '</div>';
					$output .= '</div>';
				$output .= '</div>';
			}

		$output .= '</div>';
	
    $output .= '</div>';

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
	
    return $output;
}

function tpgb_dy_device_fancybox($attr){
	$button = array();
	if (is_array($FancyData) || is_object($FancyData)) {
		foreach ($FancyData as $value) {
			// $button[] = $value->value;
			$buttonOpt = (($value->value == 'zoom') ? 'iterateZoom' : (($value->value == 'fullScreen') ? 'fullscreen' : $value->value));
			if($value->value != 'share'){
				$button[] = $buttonOpt;
			}
		}
	}

	$fancybox = array();
	$fancybox['button'] = $button;
	
	return $fancybox;
}

/**
 * Render for the server-side
 */
function tpgb_dynamic_device() {

	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_dynamic_device_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_dynamic_device' );