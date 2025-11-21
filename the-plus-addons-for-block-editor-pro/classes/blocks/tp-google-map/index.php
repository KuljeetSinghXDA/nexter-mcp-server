<?php
/* Block : Google Map
 * @since : 2.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_google_map_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("map");
	$contentTgl = (!empty($attributes['contentTgl'])) ? $attributes['contentTgl'] : false;
	$title = (!empty($attributes['title'])) ? $attributes['title'] : '';
	$description = (!empty($attributes['description'])) ? $attributes['description'] : '';
	$locationPoint = (!empty($attributes['locationPoint'])) ? $attributes['locationPoint'] : '';
	
	$Zoom = (!empty($attributes['Zoom'])) ? $attributes['Zoom'] : 10;
	$scrollWheel = (!empty($attributes['scrollWheel'])) ? $attributes['scrollWheel'] : false;
	$panCtrl = (!empty($attributes['panCtrl'])) ? $attributes['panCtrl'] : false;
	$Draggable = (!empty($attributes['Draggable'])) ? $attributes['Draggable'] : false;
	$zoomCtrl = (!empty($attributes['zoomCtrl'])) ? $attributes['zoomCtrl'] : false;
	$mapTypeCtrl = (!empty($attributes['mapTypeCtrl'])) ? $attributes['mapTypeCtrl'] : false;
	$scaleCtrl = (!empty($attributes['scaleCtrl'])) ? $attributes['scaleCtrl'] : false;
	$fullScreenCtrl = (!empty($attributes['fullScreenCtrl'])) ? $attributes['fullScreenCtrl'] : false;
	$streetViewCtrl = (!empty($attributes['streetViewCtrl'])) ? $attributes['streetViewCtrl'] : false;
	
	$customStyleTgl = (!empty($attributes['customStyleTgl'])) ? $attributes['customStyleTgl'] : false;
	$customStyle = (!empty($attributes['customStyle'])) ? $attributes['customStyle'] : 'style-1';
	
	$modifyColors = (!empty($attributes['modifyColors'])) ? $attributes['modifyColors'] : false;
	$hue = (!empty($attributes['hue'])) ? $attributes['hue'] : '';
	$saturation = (!empty($attributes['saturation'])) ? $attributes['saturation'] : '';
	$lightness = (!empty($attributes['lightness'])) ? $attributes['lightness'] : '';
	
	$gmapType = (!empty($attributes['gmapType'])) ? $attributes['gmapType'] : 'roadmap';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$json_map  = [];
	$json_map['places']  = [];
	$json_map['options'] = [
		"zoom" => intval($Zoom),
		"scrollwheel"		=> $scrollWheel,
		"draggable"		=> $Draggable,
		"panControl"		=> $panCtrl,
		"zoomControl"		=> $zoomCtrl,
		"scaleControl"		=> $scaleCtrl,
		"mapTypeControl"	=> $mapTypeCtrl,
		"fullscreenControl"	=> $fullScreenCtrl,
		"streetViewControl"	=> $streetViewCtrl,
		"mapTypeId"		=> $gmapType
	];
	
	if(!empty($locationPoint)){
        foreach($locationPoint as $index => $item ) {
            if (isset($item['latOrAddr']) && $item['latOrAddr'] === 'address') {
                // Handle address-based location
                $addr = (!empty($item['addr'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['addr']) : '';
                $address = (!empty($item['address'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['address']) : '';
                $pin_icon='';
                
                if(!empty($item['pinIcon']["id"]) && !empty($item['pinIcon']["url"])){
                    $pinIconSize=(!empty($item['pinIconSize'])) ? $item['pinIconSize'] : 'full';
                    $img = wp_get_attachment_image_src($item['pinIcon']['id'],$pinIconSize);
                    $pin_icon = (!empty($img)) ? esc_url($img[0]) : esc_url($item['pinIcon']["url"]);
                }else if(!empty($item['pinIcon']["url"])){
                    $pin_icon = (isset($item['pinIcon']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['pinIcon']) : ( !empty($item['pinIcon']['url']) ? $item['pinIcon']['url'] : '' );
                }
                
                if(!empty($addr)){
                    $json_map['places'][] = array(
                        "address"   => wp_kses_post($address),
                        "pin_icon"  => esc_url($pin_icon),
                        "latOrAddr" => 'address',
                        "addr"      => $addr
                    );
                }
            } else {
                // Handle latitude/longitude-based location (original functionality)
                $longitude = (!empty($item['longitude'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['longitude']) : '';
                $latitude = (!empty($item['latitude'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['latitude']) : '';
                $address = (!empty($item['address'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['address']) : '';
                $pin_icon='';
                
                if(!empty($item['pinIcon']["id"]) && !empty($item['pinIcon']["url"])){
                    $pinIconSize=(!empty($item['pinIconSize'])) ? $item['pinIconSize'] : 'full';
                    $img = wp_get_attachment_image_src($item['pinIcon']['id'],$pinIconSize);
                    $pin_icon = (!empty($img)) ? esc_url($img[0]) : esc_url($item['pinIcon']["url"]);
                }else if(!empty($item['pinIcon']["url"])){
                    $pin_icon = (isset($item['pinIcon']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['pinIcon']) : ( !empty($item['pinIcon']['url']) ? $item['pinIcon']['url'] : '' );
                }
                
                if(!empty($longitude) || !empty($latitude)){
                    $json_map['places'][] = array(
                        "address"   => wp_kses_post($address),
                        "latitude"  => (float) $latitude,
                        "longitude" => (float) $longitude,
                        "pin_icon"  => esc_url($pin_icon),
                        "latOrAddr" => isset($item['latOrAddr']) ? $item['latOrAddr'] : 'latitude'
                    );
                }
            }
        }
    }
	
	$maps_style='';
	if( !empty($customStyleTgl) ) {
		$maps_style= 'data-map-style="'.esc_attr($customStyle).'"';
	}
	$json_map = str_replace("'", "&apos;", wp_json_encode( $json_map ) );
	
	$output = '';
    $output .= '<div class="tpgb-google-map tpgb-relative-block tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
		
		$output .= '<div id="gmap-'.esc_attr($block_id).'" class="tpgb-adv-map" data-id="gmap-'.esc_attr($block_id).'" data-map-settings="'.htmlentities($json_map, ENT_QUOTES, "UTF-8").'" '.$maps_style.'></div>';
		
		if(!empty($contentTgl)){
			$output .= '<div class="tpgb-overlay-map-content">';
				$output .= '<div class="gmap-title">'.wp_kses_post($title).'</div>';
				$output .= '<div class="gmap-desc">'.wp_kses_post($description).'</div>';
				$output .= '<div class="overlay-list-item">';
					$output .= '<input id="toggle_'.esc_attr($block_id).'" type="checkbox" class="tpgb-overlay-gmap tpgb-overlay-gmap-tgl tpgb-block-'.esc_attr($block_id).'-checked"/>';
					$output .= '<label for="toggle_'.esc_attr($block_id).'" class="tpgb-overlay-gmap-btn tpgb-block-'.esc_attr($block_id).'-label"></label>';
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
function tpgb_google_map() {
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_google_map_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_google_map' );