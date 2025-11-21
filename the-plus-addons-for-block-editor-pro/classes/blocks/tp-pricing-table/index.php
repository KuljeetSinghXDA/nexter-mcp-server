<?php
/* Block : Pricing Table 
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_pricing_table_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$style = (!empty($attributes['style'])) ? $attributes['style'] : 'style-1';
	$contentStyle = (!empty($attributes['contentStyle'])) ? $attributes['contentStyle'] : 'wysiwyg';
	$conListStyle = (!empty($attributes['conListStyle'])) ? $attributes['conListStyle'] : 'style-1';
	$stylishList = (!empty($attributes['stylishList'])) ? $attributes['stylishList'] : [];
	$wyStyle = (!empty($attributes['wyStyle'])) ? $attributes['wyStyle'] : 'style-1';
	$wyContent = (!empty($attributes['wyContent'])) ? $attributes['wyContent'] : '';
	
	$disRibbon = (!empty($attributes['disRibbon'])) ? $attributes['disRibbon'] : false;
	$ribbonStyle = (!empty($attributes['ribbonStyle'])) ? $attributes['ribbonStyle'] : 'style-1';
	$ribbonText = (!empty($attributes['ribbonText'])) ? $attributes['ribbonText'] : '';
	
	$titleStyle = (!empty($attributes['titleStyle'])) ? $attributes['titleStyle'] : 'style-1';
	$iconType = (!empty($attributes['iconType'])) ? $attributes['iconType'] : 'none';
	$iconStyle = (!empty($attributes['iconStyle'])) ? $attributes['iconStyle'] : 'square';
	$iconStore = (!empty($attributes['iconStore'])) ? $attributes['iconStore'] : 'fas fa-home';
	$imgStore = (!empty($attributes['imgStore'])) ? $attributes['imgStore'] : '';
	$title = (!empty($attributes['title'])) ? $attributes['title'] : '';
	$subTitle = (!empty($attributes['subTitle'])) ? $attributes['subTitle'] : '';
	
	$priceStyle = (!empty($attributes['priceStyle'])) ? $attributes['priceStyle'] : 'style-1';
	$disPrePrice = (!empty($attributes['disPrePrice'])) ? $attributes['disPrePrice'] : false;
	$prevPreText = (!empty($attributes['prevPreText'])) ? $attributes['prevPreText'] : '';
	$prevPriceValue = (!empty($attributes['prevPriceValue'])) ? $attributes['prevPriceValue'] : '';
	$prevPostText = (!empty($attributes['prevPostText'])) ? $attributes['prevPostText'] : '';
	$preText = (!empty($attributes['preText'])) ? $attributes['preText'] : '';
	$priceValue = (isset($attributes['priceValue'])) ? $attributes['priceValue'] : '';
	$postText = (!empty($attributes['postText'])) ? $attributes['postText'] : '';
	
	$readMoreToggle = (!empty($attributes['readMoreToggle'])) ? $attributes['readMoreToggle'] : false;
    $showListToggle = (!empty($attributes['showListToggle'])) ? (int)$attributes['showListToggle'] : 3;
    $readMoreText = (!empty($attributes['readMoreText'])) ? $attributes['readMoreText'] : '';
    $readLessText = (!empty($attributes['readLessText'])) ? $attributes['readLessText'] : '';
	
	$ctaText = (!empty($attributes['ctaText'])) ? $attributes['ctaText'] : '';
	
	$extBtnshow = (!empty($attributes['extBtnshow'])) ? $attributes['extBtnshow'] : false ;
	
	$hoverStyle = (!empty($attributes['hoverStyle'])) ? $attributes['hoverStyle'] : 'hover_normal';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$svgIcon = (!empty($attributes['svgIcon'])) ? $attributes['svgIcon'] : '';
	$svgDraw = (!empty($attributes['svgDraw'])) ? $attributes['svgDraw'] : 'delayed';
	$svgstroColor = (!empty($attributes['svgstroColor'])) ? $attributes['svgstroColor'] : '';
	$svgfillColor = (!empty($attributes['svgfillColor'])) ? $attributes['svgfillColor'] : 'none';
	$svgDura = (!empty($attributes['svgDura'])) ? $attributes['svgDura'] : 90;
	$extbtnPosition = (!empty($attributes['extbtnPosition'])) ? $attributes['extbtnPosition'] : '';
	
	$i = 0;
	// Overlay
	$contentOverlay = '';
	$contentOverlay .= '<div class="content-overlay-bg-color tpgb-trans-easeinout"></div>';
	
	//Get Icon
	$getPriceIcon = '';
	$icon_style = '';
	$trlinr = 'tpgb-trans-linear';
	if($iconType=='icon'){
		$icon_style = $iconStyle ;
	}
	$getPriceIcon .= '<div class="price-table-icon '.($iconType=='svg' ? 'tpgb-draw-svg' : 'pricing-icon '.esc_attr($trlinr) ).' icon-'.esc_attr($icon_style).'" '.($iconType=='svg' ? 'data-id="service-svg-'.esc_attr($block_id).'" data-type="'.esc_attr($svgDraw).'" data-duration="'.esc_attr($svgDura).'" data-stroke="'.esc_attr($svgstroColor).'" data-fillColor="'.esc_attr($svgfillColor).'" data-fillEnable="yes"': '' ).'>';
		if($iconType=='icon'){
			$getPriceIcon .= '<i class="'.esc_attr($iconStore).'"></i>';
		}
		if($iconType=='img' && !empty($imgStore)){
			$altText = (isset($imgStore['alt']) && !empty($imgStore['alt'])) ? esc_attr($imgStore['alt']) : ((!empty($imgStore['title'])) ? esc_attr($imgStore['title']) : esc_attr__('Price Icon','tpgbp'));

			if(!empty($imgStore['id'])){
				$imgSrc = wp_get_attachment_image($imgStore['id'] , 'full', false, ['class' => 'pricing-icon-img', 'alt'=> $altText]);
			}else if(!empty($imgStore['url'])){
				$imgUrl = (isset($imgStore['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($imgStore) : (!empty($imgStore['url']) ? $imgStore['url'] : '');
				
                if( is_array($imgUrl) && isset($imgUrl['url']) && !empty($imgUrl['url']) ){
                    $imgSrc = '<img src="'.esc_url($imgUrl['url']).'" class="pricing-icon-img" alt="'.$altText.'"/>';
                }else{
                    $imgSrc = '<img src="'.esc_url($imgUrl).'" class="pricing-icon-img" alt="'.$altText.'"/>';
                }
			}
			$getPriceIcon .= $imgSrc;
		}
		if($iconType=='svg' && !empty($svgIcon) && !empty($svgIcon['url'])){
			$svgUrl = (isset($svgIcon['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($svgIcon) : (!empty($svgIcon['url']) ? $svgIcon['url'] : '');
			$getPriceIcon .= '<object id="service-svg-'.esc_attr($block_id).'" type="image/svg+xml" data="'.esc_url($svgUrl).'" aria-label="'.esc_attr__('icon','tpgbp').'"></object>';
		}
	$getPriceIcon .= '</div>';
		
	//Get Title
	$getPriceTitle = '';
	if(!empty($title)){
		$getPriceTitle .= '<div class="pricing-title-wrap">';
			$getPriceTitle .= '<div class="pricing-title '.esc_attr($trlinr).'">'.wp_kses_post($title).'</div>';
		$getPriceTitle .= '</div>';
	}
	
	//Get Sub Title
	$getPriceSubTitle = '';
	if(!empty($subTitle)){
		$getPriceSubTitle .= '<div class="pricing-subtitle-wrap">';
			$getPriceSubTitle .= '<div class="pricing-subtitle '.esc_attr($trlinr).'">'.wp_kses_post($subTitle).'</div>';
		$getPriceSubTitle .= '</div>'; 
	}
	
	//Get Ribbon/Pin
	$getRibbon = '';
	if(!empty($disRibbon) && !empty($ribbonText)){
		$getRibbon .= '<div class="pricing-ribbon-pin tpgb-relative-block '.esc_attr($ribbonStyle).'">';
			$getRibbon .= '<div class="ribbon-pin-inner '.esc_attr($trlinr).'">'.wp_kses_post($ribbonText).'</div>';
		$getRibbon .= '</div>';
	}
	
	//Get Title-SubTitle Content
	$getTitleContent = '';
	$getTitleContent .= '<div class="pricing-title-content tpgb-relative-block '.esc_attr($titleStyle).'">';
		if($iconType!='none'){
			$getTitleContent .= $getPriceIcon;
		}
		$getTitleContent .= $getPriceTitle;
		$getTitleContent .= $getPriceSubTitle;
	$getTitleContent .= '</div>';
	
	//Get Price Content
	$getPriceContent = '';
	$getPriceContent .= '<div class="pricing-price-wrap '.esc_attr($priceStyle).'">';
		if(!empty($disPrePrice)){
			$getPriceContent .= '<span class="pricing-previous-price-wrap '.esc_attr($trlinr).'">';
				$getPriceContent .= wp_kses_post($prevPreText);
				$getPriceContent .= wp_kses_post($prevPriceValue);
				$getPriceContent .= wp_kses_post($prevPostText);
			$getPriceContent .='</span>';
		}
		if(!empty($preText)){
			$getPriceContent .= '<span class="price-prefix-text '.esc_attr($trlinr).'">'.wp_kses_post($preText).'</span>';
		}
		if(isset($priceValue) && $priceValue != ''){
			$getPriceContent .= '<span class="pricing-price '.esc_attr($trlinr).'">'.wp_kses_post($priceValue).'</span>'; 
		}
		if(!empty($postText)){
			$getPriceContent .= '<span class="price-postfix-text '.esc_attr($trlinr).'">'.wp_kses_post($postText).'</span>';
		}
	$getPriceContent .= '</div>';
		
	//Get Button & CTA Text
	$getBtnCta = '';
	if(!empty($extBtnshow)){
		$getBtnCta .= '<div class="pricing-table-button">';
			$getBtnCta .= Tpgb_Blocks_Global_Options::load_plusButton_saves($attributes);
		$getBtnCta .= '</div>';
	}
	if(!empty($ctaText)){
		$getBtnCta .= '<div class="pricing-cta-text">'.wp_kses_post($ctaText).'</div>';
	}
	
	//Get Stylish List Content
	$getStylishContent = '';
	if($contentStyle=='stylish'){
		$getStylishContent .= '<div class="pricing-content-wrap listing-content tpgb-relative-block '.esc_attr($conListStyle).'">';
			if(!empty($stylishList)){
				$getStylishContent .= '<div class="tpgb-icon-list-items '.esc_attr($trlinr).'">';
					foreach ( $stylishList as $index => $item ) :
						
						$i++;
						//Tooltip
				
						$itemtooltip = $tooltipdata = '';
						$uniqid=uniqid("tooltip");

						$contentItem =[];
						if(!empty($item['itemTooltip'])){
							$contentItem['content'] = (!empty($item['tooltipText'])  ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['tooltipText']) : '');
							$contentItem['trigger'] = (!empty($attributes['tipTriggers'])  ? $attributes['tipTriggers'] : 'mouseenter');
							$contentItem['MaxWidth'] = (!empty($attributes['tipMaxWidth']) ? (int)$attributes['tipMaxWidth'] : 'none');
							$contentItem = htmlspecialchars(wp_json_encode($contentItem), ENT_QUOTES, 'UTF-8');
							$tooltipdata = ' data-tooltip-opt= \'' .$contentItem. '\' ';
						}
						
						if(!empty($item['itemTooltip'])){
							$itemtooltip .= ' data-tippy=""';
							$itemtooltip .= ' data-tippy-interactive="'.($attributes['tipInteractive'] ? 'true' : 'false').'"';
							$itemtooltip .= ' data-tippy-placement="'.($attributes['tipPlacement'] ? $attributes['tipPlacement'] : 'top').'"';
							$itemtooltip .= ' data-tippy-followCursor="'.(!empty($attributes['followCursor']) ? 'true' : 'false').'" ';
							$itemtooltip .= ' data-tippy-theme="'.$attributes['tipTheme'].'"';
							$itemtooltip .= ' data-tippy-arrow="'.($attributes['tipArrow'] ? 'true' : 'false').'"';
							$itemtooltip .= ' data-tippy-animation="'.($attributes['tipAnimation'] ? $attributes['tipAnimation'] : 'fade').'"';
							$itemtooltip .= ' data-tippy-offset="['.(int)$attributes['tipOffset'].','.(int)$attributes['tipDistance'].']"';
							$itemtooltip .= ' data-tippy-duration="['.(int)$attributes['tipDurationIn'].','.(int)$attributes['tipDurationOut'].']"';
						}
						//Item Content
						$getStylishContent .= '<div id="'.esc_attr($uniqid).'" class="tpgb-icon-list-item '.esc_attr($trlinr).' tp-repeater-item-'.esc_attr($item['_key']).'" '.$itemtooltip.'  '.$tooltipdata.'>';
						
							//Get Item Icon
							$getItemIcon = '';
							$getItemIcon .= '<span class="tpgb-icon-list-icon '.esc_attr($trlinr).'">'; 
								$getItemIcon .='<i class="'.esc_attr($item['iconStore']).'" aria-hidden="true"></i>';
							$getItemIcon .= '</span>';

							//Get Item Extra Icon
							$getItemExIcon = '';
							if(!empty($item['eIcnToggle']) && !empty($item['eIconStore'])){
								$getItemExIcon .= '<span class="tpgb-extra-list-icon '.esc_attr($trlinr).'">'; 
									$getItemExIcon .='<i class="'.esc_attr($item['eIconStore']).'" aria-hidden="true"></i>';
								$getItemExIcon .= '</span>';
							}
							
							//Get Item Description
							$getItemDesc = '';
							if(!empty($item['listDesc'])){
								$getItemDesc .= '<span class="tpgb-icon-list-text '.esc_attr($trlinr).'">'.wp_kses_post($item['listDesc']).'</span>';
							}
							$getStylishContent .= $getItemIcon;
							$getStylishContent .= $getItemDesc;
							$getStylishContent .= $getItemExIcon;
						$getStylishContent .= "</div>";
						
					endforeach;
				$getStylishContent .= "</div>";
				
				if($conListStyle!='style-2' && !empty($readMoreToggle) && $i > $showListToggle){
					$getStylishContent .= '<a href="#" class="read-more-options tpgb-relative-block '.esc_attr($trlinr).' more" data-default-load="'.(int)$showListToggle.'" data-more-text="'.esc_attr($readMoreText).'" data-less-text="'.esc_attr($readLessText).'">'.wp_kses_post($readMoreText).'</a>';
				}
				if($conListStyle=='style-1'){
					$getStylishContent .= $contentOverlay;
				}
			}
		$getStylishContent .= "</div>";
	}
	
	//Get wysiwyg Content
	$getWysiwygContent = '';
	if($contentStyle=='wysiwyg'){
		$getWysiwygContent .= '<div class="pricing-content-wrap content-desc '.esc_attr($wyStyle).'">';
			if($wyStyle=='style-2'){
				$getWysiwygContent .= '<hr class="border-line"/>';
			}
			$getWysiwygContent .= '<div class="pricing-content '.esc_attr($trlinr).'">'.wp_kses_post($wyContent).'</div>';
			$getWysiwygContent .= $contentOverlay;
		$getWysiwygContent .= '</div>';
	}

	$dyStyle = '';
	$titleAlign = (!empty($attributes['titleAlign'])) ? $attributes['titleAlign'] : ['md'=>'', 'sm'=>'', 'xs'=> ''];
	if($iconType!='none' && !empty($titleAlign)){
			$leftStyle = ' .tpgb-block-'.esc_attr($block_id).' .pricing-table-inner .price-table-icon { margin-left: 0}';
			$rightStyle = ' .tpgb-block-'.esc_attr($block_id).' .pricing-table-inner .price-table-icon { margin-right: 0}';
			$centerStyle = ' .tpgb-block-'.esc_attr($block_id).' .pricing-table-inner .price-table-icon { margin-left: auto; margin-right: auto; }';
		if(!empty($titleAlign['md'])){
			if($titleAlign['md']=='left'){
				$dyStyle .= $leftStyle;
			}
			if($titleAlign['md']=='right'){
				$dyStyle .= $rightStyle;
			}
		}
		if(!empty($titleAlign['sm'])){
			if($titleAlign['sm']=='left'){
				$dyStyle .= ' @media (max-width:1024px) and (min-width:768px){ '.$leftStyle.' }';
			}
			if($titleAlign['sm']=='center'){
				$dyStyle .= ' @media (max-width:1024px) and (min-width:768px){ '.$centerStyle.' }';
			}
			if($titleAlign['sm']=='right'){
				$dyStyle .= ' @media (max-width:1024px) and (min-width:768px){ '.$rightStyle.' }';
			}
		}
		if(!empty($titleAlign['xs'])){
			if($titleAlign['xs']=='left'){
				$dyStyle .= ' @media (max-width:767px){ '.$leftStyle.' }';
			}
			if($titleAlign['xs']=='center'){
				$dyStyle .= ' @media (max-width:767px){ '.$centerStyle.' }';
			}
			if($titleAlign['xs']=='right'){
				$dyStyle .= ' @media (max-width:767px){ '.$rightStyle.' }';
			}
		}
	}
		
	$output = '';
    $output .= '<div class="tpgb-pricing-table tpgb-relative-block '.esc_attr($trlinr).' pricing-'.esc_attr($style).' '.esc_attr($hoverStyle).' tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
		$output .= '<div class="pricing-table-inner '.esc_attr($trlinr).'">';
			if($style=='style-1' || $style=='style-2'){
				if($extbtnPosition==='bottom'){
					$output .= $getRibbon;
				$output .= $getTitleContent;
				$output .= $getPriceContent;
				$output .= $getStylishContent;
				$output .= $getWysiwygContent;
				$output .= $getBtnCta;
				$output .= '<div class="pricing-overlay-color tpgb-trans-easeinout"></div>';
				}
				else{
				$output .= $getRibbon;
				$output .= $getTitleContent;
				$output .= $getPriceContent;
				$output .= $getBtnCta;
							
				$output .= $getStylishContent;
				$output .= $getWysiwygContent;
				$output .= '<div class="pricing-overlay-color tpgb-trans-easeinout"></div>';
				}
				
			}
			if($style=='style-3'){
				if($extbtnPosition==='bottom'){
					$output .= '<div class="pricing-top-part '.esc_attr($trlinr).'">';
					$output .= $getRibbon;
					$output .= $getTitleContent;
					$output .= $getPriceContent;
					$output .= '<div class="pricing-overlay-color tpgb-trans-easeinout"></div>';
				$output .= '</div>';        
					$output .= $getStylishContent;
					$output .= $getWysiwygContent;
					$output .= $getBtnCta;
				}
				else{
					$output .= '<div class="pricing-top-part '.esc_attr($trlinr).'">';
					$output .= $getRibbon;
					$output .= $getTitleContent;
					$output .= $getPriceContent;
					$output .= $getBtnCta;
					$output .= '<div class="pricing-overlay-color tpgb-trans-easeinout"></div>';
				$output .= '</div>';        
					$output .= $getStylishContent;
					$output .= $getWysiwygContent;
				}
				
				
			}
		$output .= '</div>';
	$output .= '</div>';

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);

	if(!empty($dyStyle)){
		$output .= '<style>'.$dyStyle.'</style>';
	}
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_pricing_table() {
    
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_pricing_table_render_callback', true, false, true);
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_pricing_table' );