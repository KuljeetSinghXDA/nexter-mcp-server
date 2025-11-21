<?php
/* Block : Advanced Typography
 * @since : 2.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_adv_typo_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$typoListing = (!empty($attributes['typoListing'])) ? $attributes['typoListing'] : 'normal';
	$typoText = (!empty($attributes['typoText'])) ? $attributes['typoText'] : '';
	$tTextLink = (!empty($attributes['tTextLink']['url'])) ? $attributes['tTextLink']['url'] : '';
	$tTarget = (!empty($attributes['tTextLink']['target'])) ? ' target="_blank" ' : '';
	$tNofollow = (!empty($attributes['tTextLink']['nofollow'])) ? ' rel="nofollow" ' : '';
	$textListing = (!empty($attributes['textListing'])) ? $attributes['textListing'] : [];

	$strokeFill = (!empty($attributes['strokeFill'])) ? $attributes['strokeFill'] : false;
	$knockoutText = (!empty($attributes['knockoutText'])) ? $attributes['knockoutText'] : false;

	$cirTextEn = (!empty($attributes['cirTextEn'])) ? $attributes['cirTextEn'] : false;
	$customRadius = (!empty($attributes['customRadius'])) ? $attributes['customRadius'] : '';
	$revDirection = (!empty($attributes['revDirection'])) ? $attributes['revDirection'] : false;

	$blendMode = (!empty($attributes['blendMode'])) ? $attributes['blendMode'] : false;

	$marquee = (!empty($attributes['marquee'])) ? $attributes['marquee'] : false;
	$marqueeType = (!empty($attributes['marqueeType'])) ? $attributes['marqueeType'] : 'default';
	$marqueeDir = (!empty($attributes['marqueeDir'])) ? $attributes['marqueeDir'] : 'left';
	$marqueeBeh = (!empty($attributes['marqueeBeh'])) ? $attributes['marqueeBeh'] : 'initial';
	$marqueeLoop = (!empty($attributes['marqueeLoop'])) ? $attributes['marqueeLoop'] : '';
	$marqueeScroll = (!empty($attributes['marqueeScroll'])) ? $attributes['marqueeScroll'] : '';
	$marqueeAni = (!empty($attributes['marqueeAni'])) ? $attributes['marqueeAni'] : '';

	$onHoverImg = (!empty($attributes['onHoverImg'])) ? $attributes['onHoverImg'] : false;
	$hoverImg = (!empty($attributes['hoverImg'])) ? $attributes['hoverImg'] : '';
	$hoverStyle = (!empty($attributes['hoverStyle'])) ? $attributes['hoverStyle'] : '1';

	$advUnderline = (!empty($attributes['advUnderline'])) ? $attributes['advUnderline'] : 'none';
	$overlayStyle = (!empty($attributes['overlayStyle'])) ? $attributes['overlayStyle'] : 'style-1';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$innerClass = $strokeClass = $marqueeClass= $marqueeStyle = $advLineCls=$circular_attr='';
	if($typoListing=='normal'){
		$innerClass = 'tpgb-adv-single-typo tpgb-trans-linear';
		if(!empty($strokeFill)){
			$strokeClass = 'typo_stroke';
		}

		if(!empty($knockoutText)){
			$strokeClass .= ' typo_gif_based_text';
		}
		if(!empty($blendMode)){
			$strokeClass .= ' typo_bg_based_text';
		}

		if($advUnderline=='overlay'){
			$advLineCls = 'under_overlay overlay-'.$overlayStyle;
		}

		if(!empty($cirTextEn)){
			$strokeClass .= ' typo_circular';
			if(!empty($customRadius)){
				$circular_attr .= ' data-custom-radius="' . esc_attr($customRadius) . '" ';
			}
			if(!empty($revDirection)){				
				$circular_attr .= ' data-custom-reversed="yes" ';
			}
		}
	}else{
		$innerClass = 'tpgb-adv-list-typo';
	}

	if(!empty($marquee) && $marqueeType=='on_transition' && !empty($marqueeDir)){
		$marqueeClass = 'tpgb_adv_typo_'.esc_attr($marqueeDir);
	}
	
    // Set Dynamic URL For Title Link
    if(class_exists('Tpgbp_Pro_Blocks_Helper')){
        $tTextLink = (isset($attributes['tTextLink']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['tTextLink']) : (!empty($attributes['tTextLink']['url']) ? $attributes['tTextLink']['url'] : '');
    }

	$output = '';
    $output .= '<div class="tpgb-adv-typo tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
		$output .='<div class="'.esc_attr($innerClass).' '.esc_attr($advLineCls).'">';

		if($typoListing=='normal'){
			if(!empty($typoText)){
				if(!empty($onHoverImg) && !empty($hoverImg)){
					$output .= '<div class="tpgb-block" data-fx="'.esc_attr($hoverStyle).'">';
						$output .= '<p class="block__title" data-img="'.esc_attr($hoverImg['url']).'">';
				}

				if(!empty($marquee) && $marqueeType=='default'){
					$output .= '<marquee id="tpgb-adv-'.esc_attr($block_id).'" class="text-content-block '.esc_attr($strokeClass).'" direction="'.esc_attr($marqueeDir).'" behavior="'.esc_attr($marqueeBeh).'" loop="'.esc_attr($marqueeLoop).'" scrollamount="'.esc_attr($marqueeScroll).'" scrolldelay="'.esc_attr($marqueeAni).'" '.$circular_attr.'>'.wp_kses_post( $typoText ).'</marquee>';
				}

				if(empty($marquee) || (!empty($marquee) && $marqueeType=='on_transition')){
					if(!empty($attributes['tTextLink']['url'])){
						$link_attr = Tp_Blocks_Helper::add_link_attributes($attributes['tTextLink']);
						$output .= '<a id="tpgb-adv-'.esc_attr($block_id).'" href="'.esc_url($tTextLink).'" '.$tNofollow.' '.$tTarget.' '.$link_attr.' class="text-content-block '.esc_attr($strokeClass).' '.esc_attr($marqueeClass).'" '.$circular_attr.' aria-label="'.esc_attr($typoText).'">'.wp_kses_post( $typoText ).'</a>';
					}else{
						$output .= '<span id="tpgb-adv-'.esc_attr($block_id).'" class="text-content-block '.esc_attr($strokeClass).' '.esc_attr($marqueeClass).'" '.$circular_attr.' aria-label="'.esc_attr($typoText).'">'.wp_kses_post( $typoText ).'</span>';
					}
				}
				
				if(!empty($onHoverImg) && !empty($hoverImg)){
					$output .= '</p></div>';
				}
			}
		}else if($typoListing=='multiple'){
			if(!empty($textListing)){
				foreach ( $textListing as $index => $item ) :
					$dataClass = $advLineClass = $transMarqueeStyle = $transMarqueeClass = $text_cont_animation = '';
					if(!empty($item['strokeFill'])){
						$dataClass .= 'list_typo_stroke';
					}
					if(!empty($item['knockoutText'])){
						$dataClass .= ' typo_gif_based_text';
					}

					if(!empty($item['contiAnimation'])){
						$text_animation_class = '';
						if(!empty($item['aniOnHover'])){
							$text_animation_class = 'hover_';
						}else{
							$text_animation_class = 'image-';
						}
						$text_cont_animation = $text_animation_class.$item['aniEffect'];
					}
					
					if(!empty($item['marquee']) && $item['marqueeType']=='on_transition' && !empty($item['marqueeDir'])){
						$transMarqueeClass = 'tpgb_adv_typo_'.esc_attr($item['marqueeDir']);
					}

					if($item['advUnderline']=='overlay'){
						$advLineClass = 'under_overlay overlay-'.esc_attr($item['overlayStyle']);
					}
					$output .= '<div class="tpgb-text-typo tp-repeater-item-'.esc_attr($item['_key']).' '.esc_attr($advLineClass).'">';

						if(!empty($item['onHoverImg']) && !empty($item['hoverImg'])){
							$output .= '<div class="tpgb-block" data-fx="'.$item['hoverStyle'].'">';
								$output .= '<p class="block__title" data-img="'.esc_attr($item['hoverImg']['url']).'">';
						}

						if(!empty($item['marquee']) && $item['marqueeType']=='default'){

							$mDir = ($item['marqueeDir']) ? $item['marqueeDir'] : '';
							$mBeh = ($item['marqueeBeh']) ? $item['marqueeBeh'] : '';
							$mLoop = ($item['marqueeLoop']) ? $item['marqueeLoop'] : '';
							$mScrl = ($item['marqueeScroll']) ? $item['marqueeScroll'] : '';
							$mAni = ($item['marqueeAni']) ? $item['marqueeAni'] : '';

							$output .= '<marquee class="list-typo-text '.esc_attr($dataClass).' '.esc_attr($text_cont_animation).'" direction="'.esc_attr($mDir).'" behavior="'.esc_attr($mBeh).'" loop="'.esc_attr($mLoop).'" scrollamount="'.esc_attr($mScrl).'" scrolldelay="'.esc_attr($mAni).'">'.wp_kses_post($item['lText']).'</marquee>';
						}

						$textLink = (!empty($item['linkUrl']['url'])) ? $item['linkUrl']['url'] : '';
						$target = (!empty($item['linkUrl']['target'])) ? '_blank' : '';
						$nofollow = (!empty($item['linkUrl']['nofollow'])) ? 'nofollow' : '';
						$link_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($item['linkUrl']);
						if(!empty($textLink)){
							if(empty($item['marquee']) || (!empty($item['marquee']) && $item['marqueeType']=='on_transition')){
								$ariaLabelT = (!empty($item['ariaLabel'])) ? $item['ariaLabel'] : $item['lText'];
								$output .= '<a class="list-typo-text '.esc_attr($dataClass).' '.esc_attr($transMarqueeClass).' '.esc_attr($text_cont_animation).'" href="'.esc_url($textLink).'" target="'.esc_attr($target).'" rel="'.esc_attr($nofollow).'" '.$link_attr.' aria-label="'.esc_attr($ariaLabelT).'">'.wp_kses_post($item['lText']).'</a>';
							}
						}else{
							if(empty($item['marquee']) || (!empty($item['marquee']) && $item['marqueeType']=='on_transition')){
								$output .= '<span class="list-typo-text '.esc_attr($dataClass).' '.esc_attr($transMarqueeClass).' '.esc_attr($text_cont_animation).'">'.wp_kses_post($item['lText']).'</span>';
							}
						}

						if(!empty($item['onHoverImg']) && !empty($item['hoverImg'])){
							$output .= '</p></div>';
						}
						
					$output .= '</div>';

				endforeach;
			}
		}
		$output .= '</div>';
		
    $output .= '</div>';

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_adv_typo() {
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_adv_typo_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_adv_typo' );