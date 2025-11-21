<?php
/* Block : Circle Menu
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_circle_menu_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$circleMenu = (!empty($attributes['circleMenu'])) ? $attributes['circleMenu'] : [];
	$layoutType = (!empty($attributes['layoutType'])) ? $attributes['layoutType'] : 'circle';
	$cDirection = (!empty($attributes['cDirection'])) ? $attributes['cDirection'] : 'bottom-right';
	$menuStyle = (!empty($attributes['menuStyle'])) ? $attributes['menuStyle'] : 'style-1';
	$sDirection = (!empty($attributes['sDirection'])) ? $attributes['sDirection'] : 'right';
	$tglIcnType = (!empty($attributes['tglIcnType'])) ? $attributes['tglIcnType'] : 'icon';
	$iconStore = (!empty($attributes['iconStore'])) ? $attributes['iconStore'] : '';
	$imageStore = (!empty($attributes['imageStore']['url'])) ? $attributes['imageStore'] : '';
	$imageSize = (!empty($attributes['imageSize'])) ? $attributes['imageSize'] : 'full';
	$tglStyle = (!empty($attributes['tglStyle'])) ? $attributes['tglStyle'] : 'style-1';
	$iconPos = (!empty($attributes['iconPos'])) ? $attributes['iconPos'] : 'absolute';
	
	$leftAuto = (!empty($attributes['leftAuto'])) ? $attributes['leftAuto'] : false;
	$rightAuto = (!empty($attributes['rightAuto'])) ? $attributes['rightAuto'] : false;
	
	$iconGap = (!empty($attributes['iconGap'])) ? $attributes['iconGap'] : 0;
	
	$angleStart = (!empty($attributes['angleStart'])) ? $attributes['angleStart'] : 0;
	$angleEnd = (!empty($attributes['angleEnd'])) ? $attributes['angleEnd'] : 90;
	$circleRadius = (!empty($attributes['circleRadius'])) ? $attributes['circleRadius'] : 150;
	$iconDelay = (!empty($attributes['iconDelay'])) ? $attributes['iconDelay'] : 1000;
	$menuOSpeed = (!empty($attributes['menuOSpeed'])) ? $attributes['menuOSpeed'] : 500;
	$icnStepIn = (!empty($attributes['icnStepIn'])) ? $attributes['icnStepIn'] : -20;
	$icnStepOut = (!empty($attributes['icnStepOut'])) ? $attributes['icnStepOut'] : 20;
	$icnTrans = (!empty($attributes['icnTrans'])) ? $attributes['icnTrans'] : 'ease';
	$icnTrigger = (!empty($attributes['icnTrigger'])) ? $attributes['icnTrigger'] : 'hover';
	
	$scrollToggle = (!empty($attributes['scrollToggle'])) ? $attributes['scrollToggle'] : false;
	$scrollValue = (!empty($attributes['scrollValue'])) ? $attributes['scrollValue'] : '';
	$overlayColorTgl = (!empty($attributes['overlayColorTgl'])) ? $attributes['overlayColorTgl'] : false;

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	$imgSrc = '';
	$altText = (isset($imageStore['alt']) && !empty($imageStore['alt'])) ? esc_attr($imageStore['alt']) : ((!empty($imageStore['title'])) ? esc_attr($imageStore['title']) : esc_attr__('Toggle Button','tpgbp'));

	if(!empty($imageStore) && !empty($imageStore['id'])){
		$imgSrc = wp_get_attachment_image($imageStore['id'] , $imageSize, false, ['class' => 'toggle-icon-wrap', 'alt'=> $altText]);
	}else if(!empty($imageStore['url'])){
		$imgSrc = '<img src="'.esc_url($imageStore['url']).'" class="toggle-icon-wrap" alt="'.$altText.'"/>';
	}else{
		$imgSrc = '<img src="'.esc_url(TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg').'" class="toggle-icon-wrap" alt="'.esc_attr__('Toggle Button','tpgbp').'"/>';
	}
	
	$position_class=$icon_layout_straight_style=$layout_straight_menu_direction='';
	if($iconPos == 'absolute'){
		$position_class = 'circle_menu_position_abs';
	}else if($iconPos == 'fixed'){
		$position_class = 'circle_menu_position_fix';
	}
	
	$loopStyle = '';
	if($layoutType=='straight'){
		$icon_layout_straight_style = 'menu-'.$menuStyle;
		$layout_straight_menu_direction = 'menu-direction-'.$sDirection;
	}
	$p = 1;
	$direction = '';
	
	$ariaLabel = (!empty($attributes['ariaLabel'])) ? esc_attr($attributes['ariaLabel']) : esc_attr__('Toggle Button', 'tpgbp');
	
	/*circle start (fatched)*/
	$angle_start=$angle_end=$circleRadiusDesktop=$circleRadiusTablet=$circleRadiusMobile='';
	if($layoutType=='circle'){
		if($circleRadius!==''){
			$circleRadiusDesktop .= (!empty($circleRadius['md'])) ? $circleRadius['md'] : 150;
			$circleRadiusTablet .= (!empty($circleRadius['sm'])) ? $circleRadius['sm'] : $circleRadiusDesktop;
			$circleRadiusMobile .= (!empty($circleRadius['xs'])) ? $circleRadius['xs'] : $circleRadiusTablet;
		}	
	}
	if($layoutType=='circle'){
		if($cDirection =='none'){
			$angle_start = $angleStart;
			$angle_end = $angleEnd;
		}else{
			$angle_start = 0;
			$angle_end = 0;
		}
	}
	
	// Set Dataattr For Circle Menu
	$cirmenupara = [
		'direction' => $cDirection,
		'anglestart' => $angle_start,
		'angleend' => $angle_end,
		'circle_radius' => $circleRadiusDesktop,
		'circle_radius_tablet' => $circleRadiusTablet,
		'circle_radius_mobile' => $circleRadiusMobile,
		'delay' => $iconDelay,			
		'item_diameter' => 0,
		'speed' => $menuOSpeed,
		'step_in' => $icnStepIn,
		'step_out' => $icnStepOut,
		'transition_function' => $icnTrans,
		'trigger' => $icnTrigger
	];
	$cirmenupara = htmlspecialchars(wp_json_encode($cirmenupara), ENT_QUOTES, 'UTF-8');
	/*circle end*/
	
	//Scroll Offset Value
	$dataScrollValue = $scrollViewClass = '';
	if(!empty($scrollToggle)){
		$scrollViewClass = 'scroll-view';
	}
	if(!empty($scrollToggle) && !empty($scrollValue)){
		$dataScrollValue = 'data-scroll-view="'.esc_attr($scrollValue).'"';
	}
	
	$output = '';
	$output .= '<div id="tpgb-block-'.esc_attr($block_id).'" class="tpgb-circle-menu tpgb-relative-block tpgb-block-'.esc_attr($block_id).' layout-'.esc_attr($layoutType).' '.esc_attr($scrollViewClass).' '.esc_attr($blockClass).'" data-block-id="tpgb-block-'.esc_attr($block_id).'" data-cirmenu-opt= \'' .$cirmenupara. '\' '.$dataScrollValue.'>';
		$output .= '<div class="tpgb-circle-menu-inner-wrapper">';
			if(!empty($overlayColorTgl)){
				$output .='<div id="show-bg-overlay" class="show-bg-overlay"></div>';
			}
			$output .= '<ul class="tpgb-circle-menu-wrap circleMenu-closed '.esc_attr($position_class).' '.esc_attr($layout_straight_menu_direction).' '.esc_attr($icon_layout_straight_style).'">';
				$output .= '<li class="tpgb-circle-main-menu-list tpgb-circle-menu-list '.esc_attr($tglStyle).'">';
					$output .= '<a class="main_menu_icon tpgb-rel-flex" style="cursor:pointer" href="#" aria-label="'.$ariaLabel.'">';
						if($tglIcnType=='icon'){
							$output .= '<span class="toggle-icon-wrap">';
								$output .= '<i class="'.esc_attr($iconStore).'"></i>';
							$output .= '</span>';
						}
						if($tglIcnType=='image' && !empty($imageStore)){
							$output .= $imgSrc;
						}
						if($tglStyle=='style-3'){
							$output .= '<span class="close-toggle-icon"></span>';
						}
					$output .= '</a>';
				$output .= '</li>';
				if(!empty($circleMenu)){
					foreach ( $circleMenu as $index => $network ) {
						$p++;
						$target =$nofollow = $link_attr = '';
						if(!empty($network['linkType']) && $network['linkType']=='email' && !empty($network['emailtxt'])){
							$icon_url='mailto:'.$network['emailtxt'];
						}else if(!empty($network['linkType']) && $network['linkType']=='phone' && !empty($network['phoneNo'])){
							$icon_url='tel:'.$network['phoneNo'];
						}else if(!empty($network['linkType']) && $network['linkType']=='url' && !empty($network['linkUrl']['url'])){
							$target = $network['linkUrl']['target'] ? ' target="_blank"' : '';
							$nofollow = $network['linkUrl']['nofollow'] ? ' rel="nofollow"' : '';
							$icon_url = (isset($network['linkUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($network['linkUrl']) : (!empty($network['linkUrl']['url']) ? $network['linkUrl']['url'] : '');
							
							$link_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($network['linkUrl']);
						}else{
						$target = ' target="_blank"';
							$nofollow = ' rel="nofollow"';
							$icon_url='#';
						}
						if(!empty($network['linkType']) && $network['linkType'] != 'nolink'){
							$nolink='href="'.esc_url($icon_url).'" '.$target.' '.$nofollow;
						}else{
							$nolink='';
						}
						//tooltip
						$itemtooltip ='';
						
						$uniqid=uniqid("tooltip");
						if(($layoutType=='circle' || ($layoutType=='straight' && $menuStyle=='style-1')) && !empty($network['itemTooltip'])){
							$itemtooltip .= ' data-tippy=""';
							$itemtooltip .= ' data-tippy-interactive="'.($attributes['tipInteractive'] ? 'true' : 'false').'"';
							$itemtooltip .= ' data-tippy-placement="'.($attributes['tipPlacement'] ? $attributes['tipPlacement'] : 'top').'"';
							$itemtooltip .= ' data-tippy-theme="'.$attributes['tipTheme'].'"';
							$itemtooltip .= ' data-tippy-arrow="'.($attributes['tipArrow'] ? 'true' : 'false').'"';
							$itemtooltip .= ' data-tippy-animation="'.($attributes['tipAnimation'] ? $attributes['tipAnimation'] : 'fade').'"';
							$itemtooltip .= ' data-tippy-offset="['.(!empty($attributes['tipOffset']) ? (int)$attributes['tipOffset'] : 0).','.(!empty($attributes['tipDistance']) ? (int)$attributes['tipDistance'] : 0).']"';

							$itemtooltip .= ' data-tippy-duration="['.(int)$attributes['tipDurationIn'].','.(int)$attributes['tipDurationOut'].']"';
						}
						
						$contentItem =[];
						if(($layoutType=='circle' || ($layoutType=='straight' && $menuStyle=='style-1')) && !empty($network['itemTooltip'])){
							$contentItem['content'] = (!empty($network['tooltipText']) && preg_match( '/data-tpgb-dynamic=(.*?)\}/', $network['tooltipText'], $route ))  ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($network['tooltipText']) : (!empty($network['tooltipText']) ? $network['tooltipText'] : '');
							$contentItem['trigger'] = (!empty($attributes['tipTriggers'])  ? $attributes['tipTriggers'] : 'mouseenter');
							$contentItem = htmlspecialchars(wp_json_encode($contentItem), ENT_QUOTES, 'UTF-8');
						}
						$ariaLabelT = (!empty($network['ariaLabel'])) ? esc_attr($network['ariaLabel']) : ((!empty($network['title'])) ? esc_attr($network['title']) : esc_attr__("Button", 'tpgbp'));
						$output .= '<li id="'.esc_attr($uniqid).'" class="tpgb-circle-menu-list tp-repeater-item-'.esc_attr($network['_key']).'" '.$itemtooltip.' data-tooltip-opt= \'' .(!empty($contentItem) ? $contentItem : '' ). '\'>';
							$output .= '<a '.$nolink.' class="menu_icon tpgb-rel-flex" aria-label="'.$ariaLabelT.'" '.$link_attr.'>';
							if($layoutType=='circle' || ($layoutType=='straight' && $menuStyle=='style-1')){
								if($network['iconType']=="icon"){
									$output .= '<i class="'.esc_attr($network['iconStore']).'"></i>';
								}
								if($network['iconType']=="image" && !empty($network['imageStore'])){
									$imageSize = (!empty($network['imageSize'])) ? $network['imageSize'] : 'thumbnail';
									$imgISrc = '';
									$altText2 = (isset($network['imageStore']['alt']) && !empty($network['imageStore']['alt'])) ? esc_attr($network['imageStore']['alt']) : ((!empty($network['imageStore']['title'])) ? esc_attr($network['imageStore']['title']) : esc_attr__('Item Icon','tpgbp'));

									if(!empty($network['imageStore']) && !empty($network['imageStore']['dynamic'])){
										$imgISrc = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($network['imageStore']);
										$imgISrc = '<img class="img" src="'.esc_url($imgISrc).'" alt="'.$altText2.'"/>';
									}else if(!empty($network['imageStore']) && !empty($network['imageStore']['id'])){
										$imgISrc = wp_get_attachment_image($network['imageStore']['id'] , $imageSize, false, ['class' => 'img', 'alt'=> $altText2]);
									}else if(!empty($network['imageStore']['url'])){
										$imgISrc = '<img class="img" src="'.esc_url($network['imageStore']['url']).'" alt="'.$altText2.'"/>';
									}
									$output .= $imgISrc;
								}
							}
							if($layoutType=='straight' && $menuStyle=='style-2' && !empty($network['title'])){
								$output .= '<span class="menu-tooltip-title">'.wp_kses_post($network['title']).'</span>';
							}
							$output .= '</a>';
						$output .= '</li>';
					}
				}
						
			$output .= '</ul>';
		$output .= '</div>';
	$output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_circle_menu() {
	
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_circle_menu_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_circle_menu' );