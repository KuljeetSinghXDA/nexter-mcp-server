<?php
/**
 * Block : Accordion
 * @since 2.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_accordion_render_callback( $attributes, $content) {
	$output = '';
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$pattern = '/\btpgb-block-'.esc_attr($block_id).'/';
   
	if (preg_match($pattern, $content)) {
		if( class_exists('Tpgb_Blocks_Global_Options') ){
            $global_blocks = Tpgb_Blocks_Global_Options::get_instance();
            $content = $global_blocks::block_row_conditional_render($attributes,$content);
        }
		return $content;
	}
	$accordianList = (!empty($attributes['accordianList'])) ? $attributes['accordianList'] : [];
	$onHvrtab = (!empty($attributes['onHvrtab'])) ? $attributes['onHvrtab'] : '';
	$titleAlign = (!empty($attributes['titleAlign'])) ? $attributes['titleAlign'] : 'text-left';
	$toggleIcon = (!empty($attributes['toggleIcon'])) ? $attributes['toggleIcon'] : false;
	$iconFont = (!empty($attributes['iconFont'])) ? $attributes['iconFont'] : 'font_awesome';
	$iconName = (!empty($attributes['iconName'])) ? $attributes['iconName'] : 'fas fa-plus';
	$ActiconName = (!empty($attributes['ActiconName'])) ? $attributes['ActiconName'] : 'fas fa-minus';
	$iconAlign = (!empty($attributes['iconAlign'])) ? $attributes['iconAlign'] : 'end';
	$defaultAct = (!empty($attributes['defaultAct'])) ? $attributes['defaultAct'] : '0';
	$atOneOpen = (!empty($attributes['atOneOpen'])) ? "yes" : "no";
	$titleTag = (!empty($attributes['titleTag'])) ? $attributes['titleTag'] : 'div';
	$markupSch = (!empty($attributes['markupSch'])) ? $attributes['markupSch'] : false;
	$hoverStyle = (!empty($attributes['hoverStyle'])) ? $attributes['hoverStyle'] : '';
	$carouselId = (!empty($attributes['carouselId'])) ? $attributes['carouselId'] : '';
	$descAlign = (!empty($attributes['descAlign'])) ? $attributes['descAlign'] : '';

	$expCollBtn = (!empty($attributes['expCollBtn'])) ? $attributes['expCollBtn'] : false;
	$collBtnPos = (!empty($attributes['collBtnPos'])) ? $attributes['collBtnPos'] : 'before';
	$collapseText = (!empty($attributes['collapseText'])) ? $attributes['collapseText'] : '';
	$expandText = (!empty($attributes['expandText'])) ? $attributes['expandText'] : '';
	
	$accorType = (!empty($attributes['accorType'])) ? $attributes['accorType'] : '';

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$i=0;

	//Get Toogle icon
	$tgicon = '';
	if(!empty($toggleIcon)){	
		$tgicon .= '<div class="accordion-toggle-icon">';
			$tgicon .= '<span class="close-toggle-icon toggle-icon">';
				$iconFont == 'font_awesome' ? $tgicon .= '<i class="'.esc_attr($iconName).'"></i>' : ''; 
			$tgicon .= '</span>';
			$tgicon .= '<span class="open-toggle-icon toggle-icon">';
				$iconFont == 'font_awesome' ? $tgicon .= '<i class="'.esc_attr($ActiconName).'"></i>' : ''; 
			$tgicon .= '</span>';
		$tgicon .= '</div>';
	}
	
	//call Schema Markup
	$mainschema = $schemaAttr = $schemaAttr1 = $schemaAttr2 = $schemaAttr3 = '';
	if(!empty($markupSch)) {
		$mainschema = 'itemscope itemtype="https://schema.org/FAQPage"';
		$schemaAttr = 'itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"';
		$schemaAttr1 = 'itemprop="name"';
		$schemaAttr2 = 'itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer"';
		$schemaAttr3 = 'itemprop="text"';
	}

	$collExpButton = '';
	if(!empty($expCollBtn)){
		$actExCol = ($defaultAct != '0') ? 'active' : '';
		$collExpButton = '<div class="tpgb-aec-button tpgb-relative-block"><a class="tpgb-toggle-aec '.esc_attr($actExCol).'" data-coll="'.esc_attr($collapseText).'" data-exp="'.esc_attr($expandText).'"></a></div>';
	}

	$loop_content = '';
	if(!empty($accordianList)){
		foreach ( $accordianList as $index => $item ) :
			$i++;
			
			//set active class
			$active = $temClass = $cntClass = $onloadClass = '';
			if($i==$defaultAct){
				$active = 'active';
			}

			if( $item['contentType'] == 'template' && !empty($item['blockTemp']) && $item['blockTemp']!='none' && isset($item['ajaxbase']) && !empty($item['ajaxbase']) && $item['ajaxbase'] == 'ajax-base' ){
				$temClass = 'tpgb-load-template-'.esc_attr($onHvrtab).' tpgb-load-'.esc_attr($item['blockTemp']);
				$cntClass = 'tpgb-load-'.esc_attr( $item['blockTemp'] ).'-content';

				if($i==$defaultAct){
					$onloadClass = 'tpgb-load-template-view tpgb-load-'.esc_attr($item['blockTemp']);
				}
			}

			$loop_content .= '<div class="tpgb-accor-item tpgb-relative-block '.esc_attr($active).'" '.$schemaAttr.'>';
				$loop_content .= '<div id="'.(!empty($item['UniqueId']) ? esc_attr($item['UniqueId']) : 'tpag-tab-title-'.esc_attr($block_id).esc_attr($i)).'" class="tpgb-accordion-header tpgb-trans-linear-before '.esc_attr($titleAlign).' '.esc_attr($active).' '.esc_attr($temClass).'  " role="tab" data-tab="'.esc_attr($i).'" aria-controls="'.(!empty($item['UniqueId']) ? esc_attr($item['UniqueId']) : 'tpag-tab-title-'.esc_attr($block_id).esc_attr($i)).'">';
					if($iconAlign == 'start'){
						$loop_content .= $tgicon;
					}
					$loop_content .= '<span class="accordion-title-icon-wrap">';
						if(!empty($item['innerIcon'])){
							$loop_content .= '<span class="accordion-tab-icon">';
								$item['iconFonts'] == 'font_awesome' ?   $loop_content .= '<i class="'.esc_attr($item['innericonName']).'"></i>' : '';
							$loop_content .= '</span>';
						}
						$loop_content .= '<'.Tpgbp_Pro_Blocks_Helper::validate_html_tag($titleTag).' class="accordion-title" '.$schemaAttr1.'> '.wp_kses_post($item['title']).'</'.Tpgbp_Pro_Blocks_Helper::validate_html_tag($titleTag).'>';
					$loop_content .= '</span>';

					if($iconAlign == 'end'){
						$loop_content .= $tgicon;
					}

				$loop_content .= '</div>';

				$loop_content .= '<div id="tpag-tab-content-'.esc_attr($block_id).$i.'" class="tpgb-accordion-content '.esc_attr($active).' '.esc_attr($onloadClass).' " role="tabpanel" data-tab="'.esc_attr($i).'" '.$schemaAttr2.' aria-labelledby="'.(!empty($item['UniqueId']) ? esc_attr($item['UniqueId']) : 'tpag-tab-title-'.esc_attr($block_id).esc_attr($i)).'">';
					$loop_content .= '<div class="tpgb-content-editor '.esc_attr($descAlign).' '.esc_attr($cntClass).' " '.$schemaAttr3.'>';
						if( !empty($item['contentType']) && $item['contentType'] == 'content'){
							$loop_content .= Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['desc']);
						}else if($item['contentType'] == 'template' && !empty($item['blockTemp']) && $item['blockTemp']!='none'){
							ob_start();
								if(!empty($item['blockTemp'])) {
									echo Tpgb_Library()->plus_do_block($item['blockTemp']);
								}
								if( isset($item['ajaxbase']) && !empty($item['ajaxbase']) && $item['ajaxbase'] == 'ajax-base' ){
									$loop_content .= '';
								}else{
									$loop_content .= ob_get_contents();
								}
								
							ob_end_clean();
						}
					$loop_content .= '</div>';
				$loop_content .= '</div>';
			$loop_content .= '</div>';
		endforeach;
	}
	
	$dataAttr = '';
	if(!empty($carouselId)){
		$dataAttr .= ' id="tptab_'.esc_attr($carouselId).'"';
		$dataAttr .= ' data-accordion-id="tptab_'.esc_attr($carouselId).'"';
		$dataAttr .= ' data-connection="tpca-'.esc_attr($carouselId).'"';
	}
	$dataClass = '';
	if(!empty($hoverStyle) && $hoverStyle!='none'){
		$dataClass .= ' hover-'.esc_attr($hoverStyle);
	}
	$output .= '<div class="tpgb-accordion tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'" '.$mainschema.'>';
		$output .= '<div class="tpgb-accor-wrap '.$dataClass.'" data-type="'.($onHvrtab == 'hover' ? 'hover' : 'accordion').'" '.$dataAttr.' data-one-onen="'.esc_attr($atOneOpen).'" role="tablist">';
			if($collBtnPos == 'before'){
				$output .= $collExpButton;
			}
			if( $accorType == 'editor' ){
				$output .= $content;
			}else{
				$output .= $loop_content;
			}
			if($collBtnPos == 'after'){
				$output .= $collExpButton;
			}
		$output .= '</div>';
    $output .= "</div>";
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
	return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_accordion() {
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_accordion_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}


add_action( 'init', 'tpgb_tp_accordion' );