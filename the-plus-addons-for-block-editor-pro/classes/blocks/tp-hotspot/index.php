<?php
/* Block : Hotspot
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_hotspot_render_callback( $attributes, $content) {
	$output = '';
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$pinlistRepeater = (!empty($attributes['pinlistRepeater'])) ? $attributes['pinlistRepeater'] : [];
	$hotspotImage = (!empty($attributes['hotspotImage'])) ? $attributes['hotspotImage'] : [] ;
	$imageSize = (!empty($attributes['imageSize'])) ? $attributes['imageSize'] :'';
	$delaytimeout = (!empty($attributes['delaytimeout'])) ? $attributes['delaytimeout'] : 0;

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$i=0;
	$pin_content = '';
	if(!empty($pinlistRepeater)){
		foreach ( $pinlistRepeater as $index => $item ) {

			$i++;
			//Get Attributes of ToolTip
			$itemtooltip = '';
			$uniqid=uniqid("tooltip");
			if(!empty($item['itemTooltip'])){
				$itemtooltip .= ' data-tippy="" ';
				$itemtooltip .= ' data-tippy-interactive="'.(!empty($item['tipInteractive']) ? 'true' : 'false').'" ';
				$itemtooltip .= ' data-tippy-placement="'.(!empty($item['tipPlacement']) ? $item['tipPlacement'] : 'top').'" ';
				$itemtooltip .= ' data-tippy-followCursor="'.(!empty($item['followCursor']) ? 'true' : 'false').'" ';
				$itemtooltip .= ' data-tippy-theme="'.(!empty($item['tipTheme']) ? $item['tipTheme'] : 'material').'"';
				$itemtooltip .= ' data-tippy-arrow="'.(!empty($item['tipArrow']) ? 'true' : 'false').'"';
				
				$itemtooltip .= ' data-tippy-animation="'.(!empty($item['tipAnimation']) ? $item['tipAnimation'] : 'fade').'"';
				$itemtooltip .= ' data-tippy-offset="['.(!empty($item['tipOffset']) ? (int)$item['tipOffset'] : 0).','.(!empty($item['tipDistance']) ? (int)$item['tipDistance'] : 0).']"';
				$itemtooltip .= ' data-tippy-duration="['.(!empty($item['tipDurationIn']) ? (int)$item['tipDurationIn'] : '1').','.(!empty($item['tipDurationOut']) ? (int)$item['tipDurationOut'] : '1').']"';
				$itemtooltip .= ' data-tippy-delay="['.(!empty($item['tipDelayIn']) ? (int)$item['tipDelayIn'] : '1').','.(!empty($item['tipDelayOut']) ? (int)$item['tipDelayOut'] : '1').']"';
			}
			
			//Set Link to Tooltip
			$pincurl_open = '';
			$pinurl_close = '';
			if(!empty($item['pinLink'])){
				$link = (isset($item['pinUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['pinUrl']) : (!empty($item['pinUrl']['url']) ? $item['pinUrl']['url'] : '#');
				$target = ( isset($item['pinUrl']['target']) && !empty($item['pinUrl']['target']) ) ? 'target="_blank"' : '';
				$nofollow = ( isset($item['pinUrl']['nofollow']) && !empty($item['pinUrl']['nofollow']) ) ? 'rel="nofollow"' : '';
				$link_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($item['pinUrl']);
				$ariaLabelT = (!empty($item['ariaLabel'])) ? esc_attr($item['ariaLabel']) : esc_attr__('Pin Point', 'tpgbp');
				$pincurl_open ='<a href="'.esc_url($link).'" '.$target.' '.$nofollow.' '.$link_attr.' aria-label="'.$ariaLabelT.'">';
				$pinurl_close ='</a>';
			}

			$contentItem =[];
			$contentItem['content'] = (!empty($item['tooltipText'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['tooltipText']) : '';
			$contentItem['trigger'] = (!empty($item['tipTriggers'])  ? $item['tipTriggers'] : 'mouseenter');
			$contentItem['MaxWidth'] = (!empty($item['tipMaxWidth']) ? (int)$item['tipMaxWidth'] : 'none');
			$contentItem = htmlspecialchars(wp_json_encode($contentItem), ENT_QUOTES, 'UTF-8');

			//get Pin icon
			$pin_icon = '';
			$pin_icon .= '<div id="'.esc_attr($uniqid).'" class="pin-hotspot tpgb-trans-easeinout tp-repeater-item-'.esc_attr($item['_key']).' " '.$itemtooltip.'  data-tooltip-opt= \'' .$contentItem. '\'  data-hotspot="'.esc_attr($i).'">';
				$pin_icon .= '<div class="pin-hotspot-wrapper amimation-in">';
					$pin_icon .= '<div class="pin-hover '.(!empty($item['contEffect']) ? ( $item['contEffect'] == 'pulse' || $item['contEffect'] == 'floating' || $item['contEffect'] == 'tossing' ? 'tpgb-'.$item['contEffect'] : $item['contEffect']  ) : '' ).'">';
						$pin_icon .= '<div class="pin-content pin-type-'.esc_attr($item['pinType']).' tpgb-trans-easeinout">';
							if($item['pinType'] == 'icon' && $item['pinIconType'] == 'font_awesome'){
								$pin_icon .= '<i class="pin-icon tpgb-trans-easeinout '.esc_attr($item['pinIcon']).'"></i>';
							}
							if($item['pinType'] == 'image'){
								$altText2 = (isset($item['pinImage']['alt']) && !empty($item['pinImage']['alt'])) ? esc_attr($item['pinImage']['alt']) : ((!empty($item['pinImage']['title'])) ? esc_attr($item['pinImage']['title']) : esc_attr__('Pin Image','tpgbp'));

								if(!empty($item['pinImage']) && !empty($item['pinImage']['id'])){
									$icon_image=$item['pinImage']['id'];
									$pinimgsize = (!empty($item['pinimgSize']) ? $item['pinimgSize'] : 'thumbnail' );
									$icon_image = wp_get_attachment_image($icon_image,$pinimgsize, false, ['class' => 'pin-icon tpgb-trans-easeinout', 'alt'=> $altText2]);
								}else if(!empty($item['pinImage']['url'])){
									$icon_image = (isset($item['pinImage']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['pinImage']) : (!empty($item['pinImage']['url']) ? $item['pinImage']['url'] : '');
									$icon_image = '<img class="pin-icon tpgb-trans-easeinout" src="'.esc_url($icon_image).'" alt="'.$altText2.'"/>';
								}else{
									$icon_image='<img class="pin-icon tpgb-trans-easeinout" src="'.esc_url(TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg').'" alt="'.esc_html__('Pin Image','tpgbp').'"/>';
								}
								$pin_icon .= $icon_image;
							}
							if($item['pinType'] == 'text' && !empty($item['pinText'])){
								$pin_icon .= '<div class="pin-icon tpgb-trans-easeinout">';
									$pin_icon .= wp_kses_post($item['pinText']);
								$pin_icon .= '</div>';
							}
						$pin_icon .= '</div>';
					$pin_icon .= '</div>';
				$pin_icon .= '</div>';
			$pin_icon .= '</div>';
			
			$pin_content .= $pincurl_open;
				$pin_content .= $pin_icon;
			$pin_content .= $pinurl_close;
			
		}	
	}

	//Set Image Url
	$altText = (isset($hotspotImage['alt']) && !empty($hotspotImage['alt'])) ? esc_attr($hotspotImage['alt']) : ((!empty($hotspotImage['title'])) ? esc_attr($hotspotImage['title']) : esc_attr__('Hotspot Image','tpgbp'));

	if(!empty($hotspotImage) && !empty($hotspotImage['id'])){
		$imgSrc = wp_get_attachment_image($hotspotImage['id'] , $imageSize, false, ['class' => 'hotspot-image', 'alt'=> $altText]);
	}else if(!empty($hotspotImage['url'])){
		$imgSrc = '<img class="hotspot-image" src="'.esc_url($hotspotImage['url']).'" alt="'.$altText.'"/>';
	}else{
		$imgSrc = '<img class="hotspot-image" src="'.esc_url(TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg').'" alt="'.esc_attr__('Hotspot Image','tpgbp').'" />';
	}

    $output .= '<div class="tpgb-hotspot tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
		$output .= '<div class="tpgb-hotspot-inner tpgb-relative-block overlay-bg-color">';
			$output .= $imgSrc;
			$output .= '<div class="hotspot-overlay tpgb-trans-easeinout">';
				$output .= $pin_content;
			$output .= "</div>";
		$output .= "</div>";
    $output .= "</div>";
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_hotspot() {
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_hotspot_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_hotspot' );