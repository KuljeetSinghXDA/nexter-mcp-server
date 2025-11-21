<?php
/* Block : Mobile Menu
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_mobile_menu_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$mmStyle  = (!empty($attributes['mmStyle'])) ? $attributes['mmStyle'] : 'style-1';
	$posType  = (!empty($attributes['posType'])) ? $attributes['posType'] : 'absolute';
	$openMenu  = (!empty($attributes['openMenu'])) ? $attributes['openMenu'] : '';
	$extraToggle  = (!empty($attributes['extraToggle'])) ? $attributes['extraToggle'] : false;
	$contentType  = (!empty($attributes['contentType'])) ? $attributes['contentType'] : 'link';
	$contentLink = (!empty($attributes['contentLink']['url'])) ? $attributes['contentLink']['url'] : '';
	$tglIconType  = (!empty($attributes['tglIconType'])) ? $attributes['tglIconType'] : 'icon';
	$iconStore  = (!empty($attributes['iconStore'])) ? $attributes['iconStore'] : '';
	$imageStore  = (!empty($attributes['imageStore'])) ? $attributes['imageStore'] : '';
	$imageSize  = (!empty($attributes['imageSize'])) ? $attributes['imageSize'] : 'thumbnail';
	$tglText  = (!empty($attributes['tglText'])) ? $attributes['tglText'] : '';
	$displayMode  = (!empty($attributes['displayMode'])) ? $attributes['displayMode'] : 'swiper';
	$fixPosType  = (!empty($attributes['fixPosType'])) ? $attributes['fixPosType'] : 'top';
	$menu1Item  = (!empty($attributes['menu1Item'])) ? $attributes['menu1Item'] : [];
	$menu2Item  = (!empty($attributes['menu2Item'])) ? $attributes['menu2Item'] : [];
	$tempList  = (!empty($attributes['tempList'])) ? $attributes['tempList'] : '';
	$pageIndicator  = (!empty($attributes['pageIndicator'])) ? $attributes['pageIndicator'] : false;
	$indiStyle  = (!empty($attributes['indiStyle'])) ? $attributes['indiStyle'] : 'line';
	$indiPos  = (!empty($attributes['indiPos'])) ? $attributes['indiPos'] : 'indi-top';
	
	$oCntntStyle  = (!empty($attributes['oCntntStyle'])) ? $attributes['oCntntStyle'] : 'style-1';
	$cntntWidth  = (!empty($attributes['cntntWidth'])) ? $attributes['cntntWidth'] : 'custom';
	$toggleDirection  = (!empty($attributes['toggleDirection'])) ? $attributes['toggleDirection'] : 'right';
	$cIconPos  = (!empty($attributes['cIconPos'])) ? $attributes['cIconPos'] : 'mm-ci-top-right';
	$tempOverflow  = (!empty($attributes['tempOverflow'])) ? $attributes['tempOverflow'] : 'tpgb-of-h';
	$scrollOffsetTgl  = (!empty($attributes['scrollOffsetTgl'])) ? $attributes['scrollOffsetTgl'] : false;
	$scrollTopValue  = (!empty($attributes['scrollTopValue'])) ? $attributes['scrollTopValue'] : '';
	
	//Responsive Hide
	$globalHideDesktop  = (!empty($attributes['globalHideDesktop'])) ? $attributes['globalHideDesktop'] : false;
	$globalHideTablet  = (!empty($attributes['globalHideTablet'])) ? $attributes['globalHideTablet'] : false;
	$globalHideMobile  = (!empty($attributes['globalHideMobile'])) ? $attributes['globalHideMobile'] : false;
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$etClass = $position_class = $fixPosClass = $wrapper_main_class = $wrapper_class = $inner_class = $main_class = $inner_class_loop = '';
	
	//page indicator
	$indicateClass = '';
	if(!empty($pageIndicator) && $indiStyle=='line'){
		$indicateClass = $indiStyle." ".$indiPos ;
	} else if(!empty($pageIndicator) && $indiStyle=='dot'){
		$indicateClass = $indiStyle;
	}
	if($displayMode=='swiper'){			
		$wrapper_main_class = ' swiper-container swiper-free-mode';
		$wrapper_class = ' swiper-wrapper';
		$inner_class = ' swiper-slide swiper-slide-active';				
	}else if($displayMode=='columns'){
		$inner_class = ' tpgb-row';
		$main_class = ' tpgb-column-base';
		$inner_class_loop = ' grid-item tpgb-mm-eq-col';
	}	
	if(!empty($extraToggle)){
		$etClass = 'tpet-on';
	}
	if($posType == 'absolute'){
		$position_class = 'tpgb-mm-absolute';
	}else if($posType == 'fixed'){
		$position_class = 'tpgb-mm-fix';
		$fixPosClass = $fixPosType;
	}	
	
	$altText = (isset($imageStore['alt']) && !empty($imageStore['alt'])) ? esc_attr($imageStore['alt']) : ((!empty($imageStore['title'])) ? esc_attr($imageStore['title']) : esc_attr__('Mobile Menu Image','tpgbp'));
	if(!empty($imageStore) && !empty($imageStore['id'])){
		$mm_t_img = $imageStore['id'];
		$imgSrc = wp_get_attachment_image($mm_t_img , $imageSize, false, ['class' => 'tpgb-mm-img tpgb-mm-et-img tpgb-trans-easeinout', 'alt'=> $altText]);
	}else if(!empty($imageStore['url'])){
		$imgSrc = '<img class="tpgb-mm-img tpgb-mm-et-img tpgb-trans-easeinout" src="'.esc_url($imageStore['url']).'" alt="'.$altText.'"/>';
	}else{
		$imgSrc = '';
	}
	
	$show_scroll_window_offset = (!empty($scrollOffsetTgl)) ? 'scroll-view' : '';
	$dataArr = [
		"ScrollVal"		=> (isset($scrollTopValue) && !empty($scrollOffsetTgl)) ? $scrollTopValue : '',
		"DeskTopHide"	=> $globalHideDesktop,
		"TabletHide"	=> $globalHideTablet,
		"MobileHide"	=> $globalHideMobile,
		"uid"		=> 'tpgb-block-'.$block_id,
	];
	$dataArr = htmlspecialchars(wp_json_encode($dataArr), ENT_QUOTES, 'UTF-8');
	
	$getmenu1 = $getmenu2 = $toggleLink = '';
	if(!empty($menu1Item)){
		foreach ( $menu1Item as $index => $item ) :
			$getmenu1 .= '<div class="tpgb-mm-li tp-repeater-item-'.esc_attr($item['_key']).' '.esc_attr($inner_class_loop).' '.esc_attr($indicateClass).'">';
				$getmenu1 .= '<div class="tpgb-loop-inner tpgb-rel-flex">';
				$target = (!empty($item['linkUrl']['target'])) ? 'target="_blank"' : '';
				$nofollow = (!empty($item['linkUrl']['nofollow'])) ? 'rel="nofollow"' : '';
				$link_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($item['linkUrl']);
					$getmenu1 .= '<a class="tpgb-menu-link tp-mm-normal tpgb-rel-flex" href="'.esc_url($item['linkUrl']['url']).'" '.$target.' '.$nofollow.' '.$link_attr.'>';
						if($item['iconType']=='icon'){
							$getmenu1 .= '<span class="tpgb-mm-icon">';
								$getmenu1 .= '<i class="'.esc_attr($item['iconStore']).'"></i>';
							$getmenu1 .= '</span>';
						}
						if($item['iconType']=='image' && !empty($item['imgStore'])){
							$imageSize = (!empty($item['imageSize'])) ? $item['imageSize'] : 'thumbnail';
							$altText1 = (isset($item['imgStore']['alt']) && !empty($item['imgStore']['alt'])) ? esc_attr($item['imgStore']['alt']) : ((!empty($item['imgStore']['title'])) ? esc_attr($item['imgStore']['title']) : esc_attr__('Mobile Menu Image','tpgbp'));

							$imgISrc = '';
							if(!empty($item['imgStore']['id'])){
								$imgISrc = wp_get_attachment_image($item['imgStore']['id'] , $imageSize,false, ['class' => 'tpgb-mm-img tpgb-mm-st1-img', 'alt'=> $altText1]);
							}else if(!empty($item['imgStore']['url'])){
								$imgISrc = '<img class="tpgb-mm-img tpgb-mm-st1-img" src="'.esc_url($item['imgStore']['url']).'" alt="'.$altText1.'"/>';
							}
							$getmenu1 .= $imgISrc;
						}
						$getmenu1 .= '<span class="tpgb-mm-st1-title">'.esc_html($item['textVal']).'</span>';
					$getmenu1 .= '</a>';
					if(!empty($item['pinText'])){
						$getmenu1 .= '<span class="tpgb-menu-pintext">'.esc_html($item['pinText']).'</span>';
					}
				$getmenu1 .= '</div>';
			$getmenu1 .= '</div>';
			
			endforeach;
	}
	
	if(!empty($menu2Item)){
		foreach ( $menu2Item as $index => $item ) :
			$getmenu2 .= '<div class="tpgb-mm-li tp-repeater-item-'.esc_attr($item['_key']).' '.esc_attr($inner_class_loop).' '.esc_attr($indicateClass).'">';
				$getmenu2 .= '<div class="tpgb-loop-inner tpgb-rel-flex">';
				$target = (!empty($item['linkUrl']['target'])) ? 'target="_blank"' : '';
				$nofollow = (!empty($item['linkUrl']['nofollow'])) ? 'rel="nofollow"' : '';
					$getmenu2 .= '<a class="tpgb-menu-link tp-mm-normal tpgb-rel-flex" href="'.esc_url($item['linkUrl']['url']).'" '.$target.' '.$nofollow.'>';
						if($item['iconType']=='icon'){
							$getmenu2 .= '<span class="tpgb-mm-icon">';
								$getmenu2 .= '<i class="'.esc_attr($item['iconStore']).'"></i>';
							$getmenu2 .= '</span>';
						}
						if($item['iconType']=='image' && !empty($item['imgStore'])){
							$imageSize = (!empty($item['imageSize'])) ? $item['imageSize'] : 'thumbnail';
							$altText = (isset($item['imgStore']['alt']) && !empty($item['imgStore']['alt'])) ? esc_attr($item['imgStore']['alt']) : ((!empty($item['imgStore']['title'])) ? esc_attr($item['imgStore']['title']) : esc_attr__('Mobile Menu Image','tpgbp'));

							$imgISrc = '';
							if(!empty($item['imgStore']['id'])){
								$imgISrc = wp_get_attachment_image($item['imgStore']['id'] , $imageSize, false, ['class' => 'tpgb-mm-img tpgb-mm-st1-img', 'alt'=> $altText]);
							}else if(!empty($item['imgStore']['url'])){
								$imgISrc = '<img class="tpgb-mm-img tpgb-mm-st1-img" src="'.esc_url($item['imgStore']['url']).'" alt="'.$altText.'"/>';
							}
							$getmenu2 .= $imgISrc;
						}
						$getmenu2 .= '<span class="tpgb-mm-st1-title">'.esc_html($item['textVal']).'</span>';
					$getmenu2 .= '</a>';
					if(!empty($item['pinText'])){
						$getmenu2 .= '<span class="tpgb-menu-pintext">'.esc_html($item['pinText']).'</span>';
					}
				$getmenu2 .= '</div>';
			$getmenu2 .= '</div>';
			
			endforeach;
	}

	$contentALink = '';
	if($contentType=='link' && !empty($contentLink)){
		$contentALink .= 'href="'.esc_url($contentLink).'" ';
		$contentALink .= Tpgbp_Pro_Blocks_Helper::add_link_attributes($attributes['contentLink']);
	}
	
	// Ajax Base Template Load
	$ajaxbase = !empty($attributes['ajaxbase']) ? $attributes['ajaxbase'] : '';
    $triclass = $cntClass = '';
    if( !empty($ajaxbase) && $ajaxbase == 'ajax-base' ){
        $triclass = 'tpgb-load-template-click tpgb-load-'.esc_attr( $tempList );
        $cntClass = 'tpgb-load-'.esc_attr( $tempList ).'-content';
    }

	$toggleLink .= '<a class="tpgb-menu-link tpgb-mm-et-link tpgb-rel-flex '.esc_attr( $triclass ).'" '.$contentALink.'>';
		if($tglIconType=='icon'){
			$toggleLink .= '<span class="tpgb-mm-icon tpgb-trans-easeinout">';
				$toggleLink .= '<i aria-hidden="true" class="'.esc_attr($iconStore).'"></i>';
			$toggleLink .= '</span>';
		}
		if($tglIconType=='image'){
			$toggleLink .= $imgSrc;
		}
		$toggleLink .= '<span class="tpgb-mm-extra-toggle tpgb-trans-easeinout">'.esc_html($tglText).'</span>';
	$toggleLink .= '</a>';
	
	$fullwidthclass = '';
	if($cntntWidth=='fullwidth'){
		$fullwidthclass .='full-width-content';
	}
	$easeinoutC = 'tpgb-trans-easeinout tpgb-trans-easeinout-after tpgb-trans-easeinout-before';
	$toggleTemp = '';
	if(!empty($extraToggle) && $contentType=='template'){
		if($cIconPos=='mm-ci-auto'){
			$toggleTemp .= '<div class="extra-toggle-close-menu-auto '.esc_attr($easeinoutC).'"></div>';
		}
		$toggleTemp .= '<div class="header-extra-toggle-content mm-ett-'.esc_attr($oCntntStyle).' '.esc_attr($fullwidthclass).' '.esc_attr($toggleDirection).' '.esc_attr($tempOverflow).'">';
		if($oCntntStyle=='style-2'){
			$toggleTemp .= '<div class="tpgb-con-open-st2">';
		}
			$toggleTemp .= '<div class="extra-toggle-close-menu '.esc_attr($cIconPos).'  '.esc_attr($easeinoutC).'"></div>';
			if(!empty($tempList) ){
				ob_start();
					if(!empty($tempList)) {
						echo Tpgb_Library()->plus_do_block($tempList);
					}
				if( !empty($ajaxbase) && $ajaxbase == 'ajax-base' ){
					$toggleTemp .= '<div class="'.esc_attr($cntClass).'"></div>';
				}else{
					$toggleTemp .= ob_get_contents();
				}
				ob_end_clean();
			}
		if($oCntntStyle=='style-2'){
			$toggleTemp .= '</div>';
		}
		$toggleTemp .= '</div>';
		$toggleTemp .= '<div class="extra-toggle-content-overlay"></div>';
	}
	$output = '';
    $output .= '<div class="tpgb-mobile-menu tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).' '.esc_attr($mmStyle).' '.esc_attr($etClass).' '.esc_attr($main_class).' '.esc_attr($position_class).' '.esc_attr($fixPosClass).' '.esc_attr($show_scroll_window_offset).'" data-mm-option= \'' .$dataArr. '\'>';
		if($mmStyle=='style-1'){
			$output .= '<div class="tpgb-mm-wrapper'.esc_attr($wrapper_main_class).'">';
				$output .= '<div class="tpgb-mm-wrapper-inner'.esc_attr($wrapper_class).'">';
					$output .= '<div class="tpgb-mm-ul'.esc_attr($inner_class).'">';
						$output .= $getmenu1;
					$output .= '</div>';
				$output .= '</div>';
			$output .= '</div>';
			if(!empty($extraToggle)){
				$output .= '<div class="tpgb-mm-et-wrapper">';
					$output .= '<div class="tpgb-mm-et-ul">';
						$output .= '<div class="tpgb-mm-et-li">';
							$output .= '<div class="tpgb-loop-inner tpgb-rel-flex">';
								$output .= $toggleLink;
								$output .= $toggleTemp;
							$output .= '</div>';
						$output .= '</div>';
					$output .= '</div>';
				$output .= '</div>';
			}
		}
		if($mmStyle=='style-2'){
			$output .= '<div class="tpgb-mm-l-wrapper'.esc_attr($wrapper_main_class).'">';
				$output .= '<div class="tpgb-mm-l-wrapper-inner'.esc_attr($wrapper_class).'">';
					$output .= '<div class="tpgb-mm-l-ul'.esc_attr($inner_class).'">';
						$output .= $getmenu1;
					$output .= '</div>';
				$output .= '</div>';
			$output .= '</div>';	
			
			if(!empty($extraToggle)){
				$output .= '<div class="tpgb-mm-c-wrapper">';
					$output .= '<div class="tpgb-mm-c-et-ul">';
						$output .= '<div class="tpgb-mm-c-et-li">';
							$output .= '<div class="tpgb-loop-inner tpgb-rel-flex">';
								$output .= $toggleLink;
								$output .= $toggleTemp;
							$output .= '</div>';
						$output .= '</div>';
					$output .= '</div>';
				$output .= '</div>';
			}
			$output .= '<div class="tpgb-mm-r-wrapper'.esc_attr($wrapper_main_class).'">';
				$output .= '<div class="tpgb-mm-r-wrapper-inner'.esc_attr($wrapper_class).'">';
					$output .= '<div class="tpgb-mm-r-ul'.esc_attr($inner_class).'">';
						$output .= $getmenu2;
					$output .= '</div>';
				$output .= '</div>';
			$output .= '</div>';
		}
    $output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);

    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_mobile_menu() {
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_mobile_menu_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_mobile_menu' );