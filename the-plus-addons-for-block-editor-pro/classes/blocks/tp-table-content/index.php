<?php
/* Block : Table Of Content
 * @since : 2.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_table_content_render_callback( $attr, $content) {
	$output = '';
    $block_id = (!empty($attr['block_id'])) ? $attr['block_id'] : uniqid("title");
    $Style = (!empty($attr['Style'])) ? $attr['Style'] : 'none';
    $ToggleIcon = (!empty($attr['ToggleIcon'])) ? $attr['ToggleIcon'] : false;
    $TableDescText = (!empty($attr['TableDescText'])) ? $attr['TableDescText'] : '';
    $openIcon = (!empty($attr['openIcon'])) ? $attr['openIcon'] : '';
    $closeIcon = (!empty($attr['closeIcon'])) ? $attr['closeIcon'] : '';
    $DefaultToggle = (!empty($attr['DefaultToggle'])) ? $attr['DefaultToggle'] : ['md' => true, 'sm' => true, 'xs' => false];
	$totitleAlign = (!empty($attr['totitleAlign'])) ? $attr['totitleAlign'] : ['md' => '', 'sm' => '', 'xs' => ''];

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attr );

	$selectorHeading ='';
		$selectorHeading .= (!empty($attr['selectorH1'])) ? 'h1' : '';
		$selectorHeading .= (!empty($attr['selectorH2'])) ? ($selectorHeading) ? ',h2' : 'h2' : '';
		$selectorHeading .= (!empty($attr['selectorH3'])) ? ($selectorHeading) ? ',h3' : 'h3' : '';
		$selectorHeading .= (!empty($attr['selectorH4'])) ? ($selectorHeading) ? ',h4' : 'h4' : '';
		$selectorHeading .= (!empty($attr['selectorH5'])) ? ($selectorHeading) ? ',h5' : 'h5' : '';
		$selectorHeading .= (!empty($attr['selectorH6'])) ? ($selectorHeading) ? ',h6' : 'h6' : '';
	$settings = [];
	
	$settings['tocSelector'] = '.tpgb-toc';
	$settings['contentSelector'] = (!empty($attr['contentSelector'])) ? $attr['contentSelector'] : '#content';
	$settings['headingSelector'] = $selectorHeading;
	$settings['isCollapsedClass'] = (!empty($attr['ChildToggle'])) ? ' is-collapsed' : '';
	$settings['headingsOffset'] = (!empty($attr['headingsOffset'])) ? (int)$attr['headingsOffset'] : 1;
	
	$settings['scrollSmooth'] = (!empty($attr['smoothScroll'])) ? true : false;
	$settings['scrollSmoothDuration'] = (!empty($attr['smoothDuration'])) ? (int)$attr['smoothDuration'] : 420;
	$settings['scrollSmoothOffset'] = (!empty($attr['scrollOffset'])) ? (int)$attr['scrollOffset'] : 0;
	
	$settings['orderedList'] = (!empty($attr['typeList']) && $attr['typeList']==='OL') ? true : false;
	$settings['positionFixedSelector'] = (!empty($attr['fixedPosition'])) ? '.tpgb-table-content' : null;
	$settings['fixedSidebarOffset'] = (!empty($attr['fixedPosition']) && !empty($attr['fixedOffset'])) ? (int)$attr['fixedOffset'] : 'auto';
	
	$settings['hasInnerContainers'] = true;
	
	$toggleClass='';
	$toggleAttr ='';
	if(!empty($ToggleIcon)){
		$toggleClass = 'table-toggle-wrap';
		$toggleAttr .= ' data-open="'.esc_attr($openIcon).'"';
		$toggleAttr .= ' data-close="'.esc_attr($closeIcon).'"';
		$toggleAttr .= ' data-default-toggle="'.htmlspecialchars(wp_json_encode($DefaultToggle), ENT_QUOTES, 'UTF-8').'"';
	}

	$toggleActive=' active';
		
    $output .= '<div class="tpgb-table-content tpgb-block-'.esc_attr($block_id).' table-'.esc_attr($Style).' '.esc_attr($blockClass).'" data-settings="'.htmlspecialchars(wp_json_encode($settings), ENT_QUOTES, 'UTF-8').'">';
		$output .= '<div class="tpgb-toc-wrap '.esc_attr($toggleClass).esc_attr($toggleActive).'" '.$toggleAttr.'>';
			if( !empty($attr['showText']) && !empty($attr['contentText']) ) {
				$table_desc='';
				if(!empty($TableDescText)){
					$table_desc= '<div class="tpgb-table-desc tpgb-trans-linear">'.wp_kses_post($TableDescText).'</div>';
				}
				$Icon = (!empty($attr['showIcon']) && !empty($attr['PrefixIcon'])) ? '<i class="'.esc_attr($attr['PrefixIcon']).' table-prefix-icon tpgb-trans-linear"></i>' : '';
				$output .= '<div class="tpgb-toc-heading tpgb-trans-linear"><span>'. $Icon .'<span>'. wp_kses_post($attr['contentText']) .$table_desc.'</span></span>';
				if(!empty($ToggleIcon)){
					$output .= '<span><i class="table-toggle-icon tpgb-trans-linear '.esc_attr($openIcon).'"></i></span>';
				}
				$output .= '</div>';
			}
			$output .= '<div class="tpgb-toc toc"></div>';
		$output .= '</div>';
    $output .= '</div>';

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attr, $output);
	
	return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_table_content() {
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_table_content_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_table_content' );