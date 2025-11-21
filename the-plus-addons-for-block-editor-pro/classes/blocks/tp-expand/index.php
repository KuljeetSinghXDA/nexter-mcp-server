<?php
/* Block : Expand
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_expand_callback($attributes, $desc) {
    
    $output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
    $pattern = '/\btpgb-block-'.esc_attr($block_id).'/';
   
    if (preg_match($pattern, $desc)) {
       return $desc;
    }
    $title  = !empty($attributes['title']) ? $attributes['title'] : '';
    $loop_content = '';
    
    $iconPosition = !empty($attributes['iconPosition']) ? $attributes['iconPosition'] : 'before';
    
    $expandText = !empty($attributes['expandText']) ? $attributes['expandText'] : '';
    $collapseText = !empty($attributes['collapseText']) ? $attributes['collapseText'] : '';
    $transDuration = !empty($attributes['transDuration']) ? $attributes['transDuration'] : '200';
    $expandContent = !empty($attributes['content']) ? $attributes['content'] : '';
    $titleTag = !empty($attributes['titleTag']) ? $attributes['titleTag'] : '';
    $readMoreIcon = $collapseIcon = $extraButtonIcon = '';
    
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

    if(!empty($attributes["readMoreIcon"])) {
        $readMoreIcon = "<span><i class='".esc_attr($attributes["readMoreIcon"])." toggle-button-icon'></i></span>";
    }
    if(!empty($attributes["collapseIcon"])) {
        $collapseIcon = "<span><i class='".esc_attr($attributes["collapseIcon"])." toggle-button-icon'></i></span>";
    }
    if(!empty($attributes["extraButtonIcon"])) {
        $extraButtonIcon = "<span><i class='".esc_attr($attributes["extraButtonIcon"])." extra-button-icon'></i></span>";
    }

    $contMaxHeightD = !empty($attributes['contentMaxHeight']['md']) ? $attributes['contentMaxHeight']['md'] : "0";
    $contMaxHeightT = !empty($attributes['contentMaxHeight']['sm']) ? $attributes['contentMaxHeight']['sm'] : $contMaxHeightD;
    $contMaxHeightM = !empty($attributes['contentMaxHeight']['xs']) ? $attributes['contentMaxHeight']['xs'] : $contMaxHeightT;

    $ajaxbase = !empty($attributes['ajaxbase']) ? $attributes['ajaxbase'] : '';
    $triclass = $cntClass = '';
    if( !empty($ajaxbase) && $ajaxbase == 'ajax-base' ){
        $triclass = 'tpgb-load-template-view tpgb-load-'.esc_attr( $attributes['templates'] );
        $cntClass = 'tpgb-load-'.esc_attr( $attributes['templates'] ).'-content';
    }

    $content = '';
    if(!empty($attributes['contentSource']) && $attributes['contentSource'] == 'customContent') {
        $content .= '<div class="tpgb-unfold-description" ><div class="tpgb-unfold-description-inner">'.wp_kses_post($expandContent).'</div></div>';
    }
    if((!empty($attributes['contentSource']) && $attributes['contentSource']=='template') && !empty($attributes['templates'])) {
        $content .= '<div class="tpgb-unfold-description '.esc_attr($triclass).'">';
			$content .= '<div class="tpgb-unfold-description-inner '.esc_attr($cntClass).'">';
				ob_start();
					if(!empty($attributes['templates'])) {
						echo Tpgb_Library()->plus_do_block($attributes['templates']);
					}
				$loop_content .= ob_get_contents();
				ob_end_clean();
				if( !empty($ajaxbase) && $ajaxbase == 'ajax-base' ){
                    $content .= '';
                }else{
                    $content .= $loop_content;
                }
			$content .= '</div>';
        $content .= '</div>';
    }
    if(!empty($attributes['contentSource']) && $attributes['contentSource'] == 'editor') {
        $content .= '<div class="tpgb-unfold-description">';
            $content .= '<div class="tpgb-unfold-description-inner">';
                $content .= $desc;
            $content .= '</div>';
        $content .= '</div>';
    }
    
    $toggleAlignmentClass='';
    if(!empty($attributes['toggleAlignment']) && $attributes['toggleAlignment'] == 'center') {
        $toggleAlignmentClass .= 'tpgb-ca-center';
    }
	
    $dataSetting =[];
	$dataSetting['id'] = 'tpgb-block-'.esc_attr($block_id);
	$dataSetting['iconPos'] = esc_attr($iconPosition);
	$dataSetting['readmore'] = (!empty($expandText)) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($expandText) : '';
	$dataSetting['readless'] = (!empty($collapseText)) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($collapseText) : '';
	$dataSetting['readmoreIcon'] = $readMoreIcon;
	$dataSetting['readlessIcon'] = $collapseIcon;
	$dataSetting['duration'] = esc_attr($transDuration);
	$dataSetting['maxHeight'] = esc_attr($contMaxHeightD);
	$dataSetting['maxHeightT'] = esc_attr($contMaxHeightT);
	$dataSetting['maxHeightM'] = esc_attr($contMaxHeightM);
	
	$dataSetting = htmlspecialchars(wp_json_encode($dataSetting), ENT_QUOTES, 'UTF-8');
	
    $output = '<div class="tpgb-block-'.esc_attr($block_id).' tp-expand tpgb-unfold-wrapper '.esc_attr($toggleAlignmentClass).' '.esc_attr($blockClass).' tpgb-rel-flex" data-settings= \'' .$dataSetting. '\'>';
            
    if(!empty($title)){
        $output .= '<'.Tp_Blocks_Helper::validate_html_tag($titleTag).' class="tpgb-unfold-title">'.wp_kses_post($title).'</'.Tp_Blocks_Helper::validate_html_tag($titleTag).'>';
    }

    if(!empty($attributes['contentExpandDir']) && $attributes['contentExpandDir'] == 'above') {
        $output .= $content;
    }

    $contReadmoreIconBefore = $contReadmoreIconAfter = $contExtraIconBefore = $contExtraIconAfter = '';
    if(!empty($iconPosition) && $iconPosition == 'before') {
        $contReadmoreIconBefore = $readMoreIcon;
        $contExtraIconBefore = $extraButtonIcon;
    } else if(!empty($iconPosition) && $iconPosition == 'after') {
        $contReadmoreIconAfter = $readMoreIcon;
        $contExtraIconAfter = $extraButtonIcon;
    }

    $output .= '<div class="tpgb-unfold-last-toggle '.$attributes['toggleAlignment'].'">';
        if(!empty($attributes['toggleAlignment']) && $attributes['toggleAlignment'] != 'right') {
            $ariaLabel = (!empty($attributes['ariaLabel'])) ? esc_attr($attributes['ariaLabel']) : ((!empty($expandText)) ? esc_attr($expandText) : esc_attr__("Button", 'tpgbp'));
            $output .= '<button class="tpgb-unfold-toggle" aria-label="'.$ariaLabel.'">'.$contReadmoreIconBefore.' '.wp_kses_post($expandText).' '.$contReadmoreIconAfter.'</button>';
        }
        if(!empty($attributes['extraButton']) && $attributes['extraButton'] == true) {
            $ebText = !empty($attributes['extraButtonText']) ? $attributes['extraButtonText'] : '';
            $target = $attributes['extraButtonLink']['target'] ? ' target="_blank"' : '';
            $nofollow = $attributes['extraButtonLink']['nofollow'] ? ' rel="nofollow"' : '';
            $ariaLabelEb = (!empty($attributes['ariaLabelEb'])) ? esc_attr($attributes['ariaLabelEb']) : ((!empty($ebText)) ? esc_attr($ebText) : esc_attr__("Extra Button", 'tpgbp'));
			$link_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($attributes['extraButtonLink']);
            $output .= '<a class="tpgb-unfold-toggle-link" href="'.esc_url($attributes['extraButtonLink']['url']).'"' . $target . $nofollow .' '. $link_attr.' aria-label="'.$ariaLabelEb.'">'.$contExtraIconBefore.' '.wp_kses_post($ebText).' '.$contExtraIconAfter.'</a>';
        }
        if(!empty($attributes['toggleAlignment']) && $attributes['toggleAlignment'] == 'right') {
            $ariaLabel = (!empty($attributes['ariaLabel'])) ? esc_attr($attributes['ariaLabel']) : ((!empty($expandText)) ? esc_attr($expandText) : esc_attr__("Button", 'tpgbp'));
            $output .= '<button class="tpgb-unfold-toggle" aria-label="'.$ariaLabel.'">'.$contReadmoreIconBefore.' '.wp_kses_post($expandText).' '.$contReadmoreIconAfter.'</button>';
        }
    $output .= '</div>';

    if(!empty($attributes['contentExpandDir']) && $attributes['contentExpandDir'] == 'below') {
        $output .= $content;
    }
        
    $output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

function tpgb_tp_expand_render() {
    
    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
        $block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_expand_callback');
        register_block_type( $block_data['name'], $block_data );
    }
}
add_action( 'init', 'tpgb_tp_expand_render' );