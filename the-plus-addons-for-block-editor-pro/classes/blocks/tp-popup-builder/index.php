<?php
/* Block : Tp Popup Builder
 * @since : 1.2.1
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_popup_builder_callback( $settings, $content) {
	
    $block_id	= isset($settings['block_id']) ? $settings['block_id'] : '';
	$cntType = (!empty($settings['cntType'])) ? $settings['cntType'] :'template';
	$popupCnt = (!empty($settings['popupCnt'])) ? $settings['popupCnt'] :'';
    $shortCodeCnt = (!empty($settings['shortCodeCnt'])) ? $settings['shortCodeCnt'] :'';
    $popupDir = (!empty($settings['popupDir'])) ? $settings['popupDir'] :'';
    $inAnimation = (!empty($settings['inAnimation'])) ? $settings['inAnimation'] :'';
    $outAnimation = (!empty($settings['outAnimation'])) ? $settings['outAnimation'] :'';
	$outcustDur = (!empty($settings['outcustDur'])) ? (int) $settings['outcustDur']*1000 : '';
    $calltoUrlpara = (!empty($settings['calltoUrlpara'])) ? $settings['calltoUrlpara'] :'';
    $callUrl = (!empty($settings['callUrl'])) ? $settings['callUrl'] :'';
    $off_canvas = '';
    $offsetTime = wp_timezone_string();
    $now        = new DateTime('NOW', new DateTimeZone($offsetTime));
    $flag = true;

    if(!empty($settings['showTime']) && $settings['showTime'] == true) {
        $dateStart  = new DateTime($settings['dateStart'], new DateTimeZone($offsetTime));
        $dateEnd    = new DateTime($settings['dateEnd'], new DateTimeZone($offsetTime));
        if(($dateStart <= $now) && ($now <= $dateEnd)) {
            $flag = true;
        } else {
            $flag = false;
        }
    }
    
    if(!empty($settings['onpageviews']) && !empty($settings['pageViews']) && $settings['pageViews'] != '') {
        $flag = false;
        $_SESSION['pageViews'] = (isset($_SESSION['pageViews'])) ? $_SESSION['pageViews'] + 1 : 1;
        if($_SESSION['pageViews'] >= $settings['pageViews']) {
            $flag = true;
        }
    }

    //Based Call to action URL Parameter
    if (!empty($calltoUrlpara) && $calltoUrlpara == true && !empty($callUrl)) {
        $flag = false;

        if (preg_match('/\?(.*?)=/', $callUrl, $matches)) {
            if( isset($matches[1]) && !empty( $matches[1] ) && isset($_GET[$matches[1]]) && $_GET[$matches[1]] === 'true') {
                $flag = true;
            }
        }
    }

    $time = $days = '';
    $days = (!empty($settings['showXDays']) && $settings['showXDays'] != '') ? $settings['showXDays'] : 1;


	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $settings );

    if($flag) {

        $widget_uid = 'canvas-' . $block_id;
        $fixedToggleBtn = ($settings[ "fixedToggleBtn" ] == true) ? 'position-fixed' : '';
        $scrollWindowOffset = ($settings[ "fixedToggleBtn" ] == true && $settings[ 'scrollWindowOffset' ] == true) ? 'scroll-view' : '';
        $scrollTopOffset = ($settings[ "fixedToggleBtn" ] == true && $settings[ 'scrollWindowOffset' ] == true) ? 'data-scroll-view="' . esc_attr($settings[ 'scrollTopOffset' ]) . '"' : '';

        $openStyle = $settings["openStyle"];
        $onbtnClick = !empty($settings["onbtnClick"]) ? 'yes' : 'no';
        $onpageLoad = !empty($settings["onpageLoad"]) ? 'yes' : 'no';
        $loadpodelay = !empty($settings["loadpodelay"]) ? (int) $settings["loadpodelay"] : '';
        $onScroll = !empty($settings["onScroll"]) ? 'yes' : 'no';
        $exitInlet = !empty($settings["exitInlet"]) ? 'yes' : 'no';
        $inactivity = !empty($settings["inactivity"]) ? 'yes' : 'no';
        $onpageviews = !empty($settings["onpageviews"]) ? 'yes' : 'no';
        $prevurl = !empty($settings["prevurl"]) ? 'yes' : 'no';
		$extraclick = !empty($settings["extraclick"]) ? 'yes' : 'no';
        $showRes = !empty($settings["showRestricted"]) ? 'yes' : 'no';
        $noXTimes = (!empty($settings['showXTimes']) && $settings['showXTimes'] != '') ? (int)$settings['showXTimes'] : 1;


        $previousUrl = (!empty($settings["prevurl"]) ) ? $settings["previousUrl"]["url"] : '';
        $extraId = (!empty($settings["extraclick"]) ) ? $settings["extraId"] : '';
        $inactivitySec = ( !empty($settings["inactivity"])) ? $settings["inactivitySec"] : '';
        $openDir = $settings[ "openDir" ];
        $scrlBar = ($settings[ "scrlBar" ] != true) ? 'scroll-bar-disable' : '';
        $closeContent = ($settings[ "closeContent" ] == true) ? 'yes' : 'no';
        $bodyClickClose = ($settings[ "bodyClickClose" ] == true) ? 'yes' : 'no';
        if($openStyle == 'corner-box' ) {
            $openDir = $settings[ "cornerBoxDir" ];
        } elseif($openStyle == 'popup' ) {
            $openDir = "center";
        }
		
		// Set Popup class
        $animClass = $dataAttr = '';
        $animData = [];
        if(!empty($openStyle) && $openStyle == 'popup'){
            $animClass .= 'tpgb-view-animation';
            if(!empty($inAnimation)){
                $animData['anime'] = $inAnimation;
            }
            if(!empty($outAnimation)){
                $animData['animeOut'] = $outAnimation;
            }

            if(!empty($outcustDur)){
                $animData['custoutDur'] = $outcustDur;
            }

            if( !empty($settings['inanimDur']) && $settings['inanimDur'] == 'custom' ){
                $animClass .= ' tpgb-anim-dur-custom';
            }else{
                $animClass .= ' tpgb-anim-dur-'.$settings['inanimDur'];
            }
            
            if( !empty($settings['outanimDur']) && $settings['outanimDur'] == 'custom' ){
                $animClass .= ' tpgb-anim-out-dur-custom';
            }else{
                $animClass .= ' tpgb-anim-out-dur-'.$settings['outanimDur'];
            }
            
            
            $dataAttr =  ' data-animationsetting =\'' . wp_json_encode($animData) . '\' ';
        }

		$scrollHeight = (!empty($settings["onScroll"])) ? $settings['scrollHeight'] : '';
       
        $setData = [
            'content_id' => esc_attr($block_id),
            'transition' => esc_attr($openStyle),
            'direction' => esc_attr($openDir),
            'esc_close' => esc_attr($closeContent),
            'extraclick' => esc_attr($extraclick),
            'body_click_close' => esc_attr($bodyClickClose),
            'trigger' => esc_attr($onbtnClick),
            'onpageLoad' => esc_attr($onpageLoad),
            'onpageloadDelay' => esc_attr($loadpodelay),
            'onScroll' => esc_attr($onScroll),
            'exitInlet' => esc_attr($exitInlet),
            'inactivity' => esc_attr($inactivity),
            'onpageviews' => esc_attr($onpageviews),
            'prevurl' => esc_attr($prevurl),
            'extraId' => esc_attr($extraId),
            'time' => esc_attr($time),
            'days' => esc_attr($days),
            'inactivitySec' => esc_attr($inactivitySec),
            'showuseRes' => esc_attr($showRes),
            'noXTimes' => esc_attr($noXTimes),
            'scrollHeight' => esc_attr($scrollHeight),
            'previousUrl' => esc_attr($previousUrl),
        ];
        
        $data_attr   = ' data-settings =\'' . wp_json_encode($setData) . '\' ';
        $toggle_content    = '';
        
        $full_width_button = ($settings[ "toggleCanvas" ] == 'button' && ! empty ( $settings[ 'btnFullWidth' ] ) && $settings[ 'btnFullWidth' ] == true) ? 'btn_full_width' : '';
        $full_width_button .= ($settings[ "toggleCanvas" ] == 'button' && ! empty ( $settings[ 'btntabFull' ] ) && $settings[ 'btntabFull' ] == true) ? ' btn_full_tab_width' : '';
        $full_width_button .= ($settings[ "toggleCanvas" ] == 'button' && ! empty ( $settings[ 'btnmoFull' ] ) && $settings[ 'btnmoFull' ] == true) ? ' btn_full_mobile_width' : '';

        if( $settings[ "toggleCanvas" ] == 'button') {
            $toggle_content .= '<div class="offcanvas-toggle-btn toggle-button-style tpgb-trans-easeinout ' . esc_attr ( $fixedToggleBtn ) . ' ' . esc_attr ( $full_width_button ) . '">';
            $before_after = $settings['iconPosition'];
            $btnText = $settings['btnText'];

            if($settings["btnIconStyle"] == 'font_awesome') {
                $icons=$settings["btnIcon"];
            } else {
                $icons='';
            }

            $icons_before = $icons_after = '';
            if($before_after=='before' && !empty($icons)){
                $icons_before = '<i class="btn-icon button-before '.esc_attr($icons).'"></i>';
            }
            if($before_after=='after' && !empty($icons)){
            $icons_after = '<i class="btn-icon button-after '.esc_attr($icons).'"></i>';
            }

            $toggle_content .= $icons_before.'<span class="btn-text">'.wp_kses_post($btnText).'</span>'. $icons_after;
            $toggle_content .= '</div>';
        }
        if( $settings[ "toggleCanvas" ] == 'icon' && !empty($settings[ "toggleIconStyle" ])) {
            if( $settings[ "toggleIconStyle" ] == 'style-1' || $settings[ "toggleIconStyle" ] == 'style-2' || $settings[ "toggleIconStyle" ] == 'style-3' ) {
                $toggle_content .= '<div class="offcanvas-toggle-btn humberger-' . esc_attr ( $settings[ "toggleIconStyle" ] ) . ' ' . esc_attr ( $fixedToggleBtn ) . '">';
                $toggle_content .= '<span class="menu_line menu_line--top"></span>';
                $toggle_content .= '<span class="menu_line menu_line--center"></span>';
                $toggle_content .= '<span class="menu_line menu_line--bottom"></span>';
                $toggle_content .= '</div>';
            } else if( $settings[ "toggleIconStyle" ] == 'custom' ) {
                $toggle_content .= '<div class="offcanvas-toggle-btn humberger-' . esc_attr ( $settings[ "toggleIconStyle" ] ) . ' ' . esc_attr ( $fixedToggleBtn ) . '">';
                if(isset($settings[ 'imgSvg' ][ 'url' ])){
                    $altText = (isset($settings[ 'imgSvg' ]['alt']) && !empty($settings[ 'imgSvg' ]['alt'])) ? esc_attr($settings[ 'imgSvg' ]['alt']) : ((!empty($settings[ 'imgSvg' ]['title'])) ? esc_attr($settings[ 'imgSvg' ]['title']) : esc_attr__('Offcanvas Toggle Image','tpgbp'));

                    $toggle_content .= '<img src="' . esc_url($settings[ 'imgSvg' ][ 'url' ]) . '" class="off-can-img-svg" alt="'.$altText.'"/>';
                }
                $toggle_content .= '</div>';
            }
        }
        
        $off_canvas .= '<div class="tpgb-block-'.esc_attr($block_id).' tpgb-offcanvas-wrapper tpgb-relative-block ' . esc_attr ( $widget_uid ) . ' ' . esc_attr ( $scrollWindowOffset ) . ' '.esc_attr($blockClass).'" data-canvas-id="' . esc_attr ( $widget_uid ) . '" ' . $data_attr . ' ' . $scrollTopOffset . '>';

        $off_canvas .= '<div class="offcanvas-toggle-wrap tpgb-relative-block">';
        $off_canvas .= $toggle_content;
        $off_canvas .= '</div>';
        
		$content_classes = '';
		if(isset($settings['globalClasses']) && !empty($settings['globalClasses'])){
			$content_classes = $settings['globalClasses'];
		}
		
        $off_canvas .= '<div class="tpgb-block-'.esc_attr( $block_id ).'-canvas tpgb-canvas-content-wrap tpgb-' . esc_attr ( $openDir ) . ' tpgb-' . esc_attr ( $openStyle ) . ' tpgb-popup-'.esc_attr ($popupDir).'  ' . esc_attr ( $scrlBar ) . ' '.esc_attr($animClass).' '.esc_attr($content_classes).'" '.$dataAttr.'>';
		
        if( ! empty ( $settings[ "contentCloseIcon" ] ) && $settings[ "contentCloseIcon" ] == true ) {
            $sticky_btn       = ( ! empty ( $settings[ "closeIconSticky" ] ) && $settings[ "closeIconSticky" ] == true) ? 'sticky-close-btn' : '';
            $close_icon_class = ( ! empty ( $settings[ "closeIconCustom" ] ) && $settings[ "closeIconCustom" ] == true) ? 'off-close-image' : '';

            $off_canvas .= '<div class="tpgb-canvas-header direction-' . esc_attr ( $settings[ "closeIconAlign" ] ) . ' ' . esc_attr ( $sticky_btn ) . '"><div class="tpgb-offcanvas-close tpgb-offcanvas-close-' . esc_attr($block_id) . ' ' . esc_attr ( $close_icon_class ) . '" role="button">';
            if( ! empty ( $settings[ "closeIconCustom" ] ) && $settings[ "closeIconCustom" ] == true && ! empty ( $settings[ 'closeIconCustomSource' ][ 'url' ] ) ) {
                $altText = (isset($settings[ 'closeIconCustomSource' ]['alt']) && !empty($settings[ 'closeIconCustomSource' ]['alt'])) ? esc_attr($settings[ 'closeIconCustomSource' ]['alt']) : ((!empty($settings[ 'closeIconCustomSource' ]['title'])) ? esc_attr($settings[ 'closeIconCustomSource' ]['title']) : esc_attr__('Popup Close Image','tpgbp'));

                $off_canvas .= '<img src="' . esc_url($settings[ 'closeIconCustomSource' ][ 'url' ]) . '" class="close-custom_img" alt="'.$altText.'"/>';
            }
            $off_canvas .= '</div></div>';
        }
        $off_canvas .= '<div class="tpgb-content-editor">';
            if($cntType == 'template' && ! empty ( $settings[ 'contentSource' ] )) {
                ob_start();
                    if(!empty($settings['contentSource']) && $settings['contentSource'] != 'none') {
                        echo Tpgb_Library()->plus_do_block($settings[ 'contentSource' ]);
                    }
                    $off_canvas .= ob_get_contents();
                ob_end_clean();
            }else if($cntType == 'content' && !empty($popupCnt) ){
                $off_canvas .= '<p>'.wp_kses_post($popupCnt).'</p>';
            }else if($cntType == 'shortcode' && !empty($shortCodeCnt) ){
                $off_canvas .= do_shortcode($shortCodeCnt);
            }else if($cntType == 'editor'){
                $off_canvas .= $content;
            }

        $off_canvas .= '</div>';
		
        $off_canvas .= '</div>';

        $off_canvas .= '</div>';
        
        if( ! empty ( $settings[ "fixedToggleBtn" ] ) && $settings[ "fixedToggleBtn" ] == true ) {
            $off_canvas .= '<style>';
            $rpos       = 'auto';
            $bpos       = 'auto';
            $ypos       = 'auto';
            $xpos       = 'auto';
            if( $settings[ 'leftAutoD' ] == true ) {
                if( ! empty ( $settings[ 'xPosD' ] ) || $settings[ 'xPosD' ] == '0' ) {
                    $xpos = $settings[ 'xPosD' ] . '%';
                }
            }
            if( $settings[ 'topAutoD' ] == true ) {
                if( ! empty ( $settings[ 'yPosD' ] ) || $settings[ 'yPosD' ] == '0' ) {
                    $ypos = $settings[ 'yPosD' ] . '%';
                }
            }
            if( $settings[ 'bottomAutoD' ] == true ) {
                if( ! empty ( $settings[ 'bottomPosD' ] ) || $settings[ 'bottomPosD' ] == '0' ) {
                    $bpos = $settings[ 'bottomPosD' ] . '%';
                }
            }
            if( $settings[ 'rightAutoD' ] == true ) {
                if( ! empty ( $settings[ 'rightPosD' ] ) || $settings[ 'rightPosD' ] == '0' ) {
                    $rpos = $settings[ 'rightPosD' ] . '%';
                }
            }

            $off_canvas .= '.' . esc_attr ( $widget_uid ) . ' .offcanvas-toggle-wrap .offcanvas-toggle-btn.position-fixed{top:' . esc_attr ( $ypos ) . ';bottom:' . esc_attr ( $bpos ) . ';left:' . esc_attr ( $xpos ) . ';right:' . esc_attr ( $rpos ) . ';}';

            if( ! empty ( $settings[ 'responsiveT' ] ) && $settings[ 'responsiveT' ] == true ) {
                $tablet_xpos = 'auto';
                $tablet_ypos = 'auto';
                $tablet_bpos = 'auto';
                $tablet_rpos = 'auto';
                if( $settings[ 'leftAutoT' ] == true ) {
                    if( ! empty ( $settings[ 'xPosT' ] ) || $settings[ 'xPosT' ] == '0' ) {
                        $tablet_xpos = $settings[ 'xPosT' ] . '%';
                    }
                }
                if( $settings[ 'topAutoT' ] == true ) {
                    if( ! empty ( $settings[ 'yPosT' ] ) || $settings[ 'yPosT' ] == '0' ) {
                        $tablet_ypos = $settings[ 'yPosT' ] . '%';
                    }
                }
                if( $settings[ 'bottomAutoT' ] == true ) {
                    if( ! empty ( $settings[ 'bottomPosT' ] ) || $settings[ 'bottomPosT' ] == '0' ) {
                        $tablet_bpos = $settings[ 'bottomPosT' ] . '%';
                    }
                }
                if( $settings[ 'rightAutoT' ] == true ) {
                    if( ! empty ( $settings[ 'rightPosT' ] ) || $settings[ 'rightPosT' ] == '0' ) {
                        $tablet_rpos = $settings[ 'rightPosT' ] . '%';
                    }
                }

                $off_canvas .= '@media (min-width:601px) and (max-width:990px){.' . esc_attr ( $widget_uid ) . ' .offcanvas-toggle-wrap .offcanvas-toggle-btn.position-fixed{top:' . esc_attr ( $tablet_ypos ) . ';bottom:' . esc_attr ( $tablet_bpos ) . ';left:' . esc_attr ( $tablet_xpos ) . ';right:' . esc_attr ( $tablet_rpos ) . ';}';

                $off_canvas .= '}';
            }
            if( ! empty ( $settings[ 'responsiveM' ] ) && $settings[ 'responsiveM' ] == true ) {
                $mobile_xpos = 'auto';
                $mobile_ypos = 'auto';
                $mobile_bpos = 'auto';
                $mobile_rpos = 'auto';
                if( $settings[ 'leftAutoM' ] == true ) {
                    if( ! empty ( $settings[ 'xPosM' ] ) || $settings[ 'xPosM' ] == '0' ) {
                        $mobile_xpos = $settings[ 'xPosM' ] . '%';
                    }
                }
                if( $settings[ 'topAutoM' ] == true ) {
                    if( ! empty ( $settings[ 'yPosM' ] ) || $settings[ 'yPosM' ] == '0' ) {
                        $mobile_ypos = $settings[ 'yPosM' ] . '%';
                    }
                }
                if( $settings[ 'bottomAutoM' ] == true ) {
                    if( ! empty ( $settings[ 'bottomPosM' ]) || $settings[ 'bottomPosM' ] == '0' ) {
                        $mobile_bpos = $settings[ 'bottomPosM' ] . '%';
                    }
                }
                if( $settings[ 'rightAutoM' ] == true ) {
                    if( ! empty ( $settings[ 'rightPosM' ] ) || $settings[ 'rightPosM' ] == '0' ) {
                        $mobile_rpos = $settings[ 'rightPosM' ] . '%';
                    }
                }
                $off_canvas .= '@media (max-width:600px){.' . esc_attr ( $widget_uid ) . ' .offcanvas-toggle-wrap .offcanvas-toggle-btn.position-fixed{top:' . esc_attr ( $mobile_ypos ) . ';bottom:' . esc_attr ( $mobile_bpos ) . ';left:' . esc_attr ( $mobile_xpos ) . ';right:' . esc_attr ( $mobile_rpos ) . ';}';

                $off_canvas .= '}';
            }
            $off_canvas .= '</style>';
        }
		if( $bodyClickClose == 'no'){
            $off_canvas .= '<style>.tpgb-block-'.esc_attr( $block_id ).'-canvas-open .tpgb-offcanvas-container:after { display: none;} </style>';
        }
    }
	
	$off_canvas = Tpgb_Blocks_Global_Options::block_Wrap_Render($settings, $off_canvas);
	
    return $off_canvas;
}

function tpgb_tp_popup_builder_render() {

    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
        $block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_popup_builder_callback');
        register_block_type( $block_data['name'], $block_data );
    }
}
add_action( 'init', 'tpgb_tp_popup_builder_render' );