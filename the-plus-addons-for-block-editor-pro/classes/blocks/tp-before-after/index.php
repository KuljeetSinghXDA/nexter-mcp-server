<?php
/* Block : Before After
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_before_after_render_callback( $attr, $content) {
	$output = '';
	$block_id = (!empty($attr['block_id'])) ? $attr['block_id'] : uniqid("title");
	$style = (!empty($attr['style'])) ? $attr['style'] : 'horizontal';
	$beforeImg = (!empty($attr['beforeImg'])) ? $attr['beforeImg'] : '';
	$beforeLabel = (!empty($attr['beforeLabel'])) ? $attr['beforeLabel'] : '';
	$afterImg = (!empty($attr['afterImg'])) ? $attr['afterImg'] : '';
	$afterLabel = (!empty($attr['afterLabel'])) ? $attr['afterLabel'] : '';
	$imageSize = (!empty($attr['imageSize'])) ? $attr['imageSize'] : 'full';
	$fullWidth = (!empty($attr['fullWidth']) ? 'yes' : 'no');
	$onmouseHvr = (!empty($attr['onmouseHvr']) ? 'yes' : 'no');
	$sepLine = (!empty($attr['sepLine']) ? 'yes' : 'no');
	$sepStyle = (!empty($attr['sepStyle'])) ? $attr['sepStyle'] : '';
	$sepPosi = (!empty($attr['sepPosi'])) ? $attr['sepPosi'] : '25';
	$sepWidth = (!empty($attr['sepWidth'])) ? $attr['sepWidth'] : '15';
	$sepIcon = (!empty($attr['sepIcon'])) ? $attr['sepIcon'] : '';
	$alignment = (!empty($attr['alignment'])) ? $attr['alignment'] : [ 'md' => 'center', 'sm' =>  '', 'xs' =>  '' ];
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attr );
	
	$datattr=$mid_sep=$bottom_sep='';
	//Set Before Image Tag
	if(!empty($beforeImg)){
		$before_img = '';
		if(!empty($beforeImg['url']) && !empty($imageSize) && !empty($beforeImg['id'])){
			$before_image=$beforeImg['id'];
			$img = wp_get_attachment_image_src($before_image,$imageSize);
			$before_imgSrc = ( isset($img[0]) && !empty( $img[0]) ) ? $img[0] :  TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg';
		}else{
			$before_imgSrc = $beforeImg['url'];
		}
		$altText = (isset($beforeImg['alt']) && !empty($beforeImg['alt'])) ? esc_attr($beforeImg['alt']) : ((!empty($beforeImg['title'])) ? esc_attr($beforeImg['title']) : esc_attr__('Before Image','tpgbp'));

		$before_img='<img class="tpgb-beforeimg-wrap" src="'.esc_url($before_imgSrc).'" alt="'.$altText.'">';
	}
	if(!empty($afterImg)){
		$after_img = '';
		if(!empty($afterImg['url']) && !empty($imageSize) && !empty($afterImg['id'])){
			$after_image=$afterImg['id'];
			$img = wp_get_attachment_image_src($after_image,$imageSize);
			$after_imgSrc = ( isset($img[0]) && !empty( $img[0]) ) ? $img[0] :  TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg';
		}else{
			$after_imgSrc = $afterImg['url'];
		}
		$altText = (isset($afterImg['alt']) && !empty($afterImg['alt'])) ? esc_attr($afterImg['alt']) : ((!empty($afterImg['title'])) ? esc_attr($afterImg['title']) : esc_attr__('After Image','tpgbp'));
		$after_img='<img class="tpgb-afterimg-wrap" src="'.esc_url($after_imgSrc).'" alt="'.$altText.'">';
	}

	//Set Separator 
	if(!empty($style) && ($style=='horizontal' || $style=='vertical')){
		if($sepStyle=='middle'){
			$mid_sep='<div class="tpgb-beforeafter-sep"></div>';
		}else{
			$mid_sep='<div class="tpgb-beforeafter-sep"></div>';
			$bottom_sep='<div class="tpgb-bottom-sep"></div>';
		}
	} 
	//Set Separator Image
	$image_sep = '';
	if(!empty($sepIcon['url'])){
		$imgSrc = $sepIcon['url'];
		$altText = (isset($sepIcon['alt']) && !empty($sepIcon['alt'])) ? esc_attr($sepIcon['alt']) : ((!empty($sepIcon['title'])) ? esc_attr($sepIcon['title']) : esc_attr__('Seprator Icon','tpgbp'));
		$image_sep= '<div class="tpgb-before-sepicon"><img src="'.esc_url($imgSrc).'" alt="'.$altText.'"></div>';
	}
	//Set Data Attr
	$datattr .=' data-type="'.esc_attr($style).'" ';
	$datattr .=' data-id="tpgb-block-'.esc_attr($block_id).'" ';
	$datattr .=' data-click_hover_move="'.esc_attr($onmouseHvr).'" ';
	$datattr .=' data-separate_position="'.esc_attr($sepPosi).'" ';
	$datattr .=' data-full_width="'.esc_attr($fullWidth).'" ';
	$datattr .=' data-separator_style="'.esc_attr($sepStyle).'" ';
	$datattr .=' data-separate_width="'.esc_attr($sepWidth).'" ';
	$datattr .=' data-responsive="yes" ';
	$datattr .=' data-width="0" ';
	$datattr .=' data-max-width="0" ';
	$datattr .=' data-separate_switch="'.esc_attr($sepLine).'" ';
	$datattr .=' data-show="1" ';
	if(!empty($sepIcon['url'])){
		$datattr .=' data-separate_image="2" ';
	}else{
		$datattr .=' data-separate_image="1" ';
	}
	if( !empty($beforeImg['url']) && !empty($afterImg['url']) ){
		$output .= '<div  class="tpgb-before-after tpgb-relative-block tpgb-block-'.esc_attr($block_id).' '.esc_attr( $blockClass ).'" '.$datattr.'>';
			$output .= '<div class="tpgb-beforeafter-inner">';
				
					$output .= '<div class="tpgb-beforeafter-img tpgb-before-img">';
						$output .= $before_img;
						if(!empty($beforeLabel)){
							$output .= '<div class="tpgb-beforeafter-label before-label '.esc_attr($style).'">'.wp_kses_post($beforeLabel).'</div>';
						}
					$output .= '</div>';
					$output .= '<div class="tpgb-beforeafter-img tpgb-after-img">';
						$output .= $after_img;
						if(!empty($afterLabel)){
							$output .= '<div class="tpgb-beforeafter-label after-label">'.wp_kses_post($afterLabel).'</div>';
						}
					$output .= '</div>';
					$output .= $mid_sep;
					$output .= $image_sep;
				
			$output .= '</div>';
			$output .= $bottom_sep;
		$output .= '</div>';
	}else{
		$output .= '<h3 class="tpgb-posts-not-found">'.esc_html__('Please select a Before images & After Image','tpgbp').'</h3>';
	}

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attr, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_before_after_content() {
	
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_before_after_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_before_after_content' );