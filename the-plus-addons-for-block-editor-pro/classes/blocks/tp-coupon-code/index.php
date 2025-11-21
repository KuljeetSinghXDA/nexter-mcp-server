<?php
/**
 * Block : Coupon Code
 * @since 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_coupon_code_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$className = (!empty($attributes['className'])) ? $attributes['className'] :'';
	$align = (!empty($attributes['align'])) ? $attributes['align'] :'';
	$couponType = (!empty($attributes['couponType'])) ? $attributes['couponType'] :'standard';
	$standardStyle = (!empty($attributes['standardStyle'])) ? $attributes['standardStyle'] :'style-1';
	$directionHint = (!empty($attributes['directionHint'])) ? $attributes['directionHint'] :false;
	$couponText = (!empty($attributes['couponText'])) ? $attributes['couponText'] :'';
	$couponCode = (!empty($attributes['couponCode'])) ? $attributes['couponCode'] :'';
	$codeArrow = (!empty($attributes['codeArrow'])) ? $attributes['codeArrow'] :false;
	$codecopyIcn = (!empty($attributes['codecopyIcn'])) ? $attributes['codecopyIcn'] :false;
	$copyBtnText = (!empty($attributes['copyBtnText'])) ? $attributes['copyBtnText'] :'';
	$afterCopyText = (!empty($attributes['afterCopyText'])) ? $attributes['afterCopyText'] :'';
	$visitBtnText = (!empty($attributes['visitBtnText'])) ? $attributes['visitBtnText'] :'';
	$popupTitle = (!empty($attributes['popupTitle'])) ? $attributes['popupTitle'] :'';
	$popupDesc = (!empty($attributes['popupDesc'])) ? $attributes['popupDesc'] :'';
	$actionType = (!empty($attributes['actionType'])) ? $attributes['actionType'] :'click';
	
	$tabReverse = (!empty($attributes['tabReverse'])) ? $attributes['tabReverse'] :false;
	$saveCookie = (!empty($attributes['saveCookie'])) ? $attributes['saveCookie'] :false;
	$hideLink = (!empty($attributes['hideLink'])) ? $attributes['hideLink'] :false;
	$linkMaskText = (!empty($attributes['linkMaskText'])) ? $attributes['linkMaskText'] :'';
	$maskLinkList = (!empty($attributes['maskLinkList'])) ? $attributes['maskLinkList'] :[];
	
	$fillPercent = (!empty($attributes['fillPercent'])) ? $attributes['fillPercent'] :'70';
	$slideDirection = (!empty($attributes['slideDirection'])) ? $attributes['slideDirection'] :'left';
	
	$frontContentType = (!empty($attributes['frontContentType'])) ? $attributes['frontContentType'] :'default';
	$frontContent = (!empty($attributes['frontContent'])) ? $attributes['frontContent'] :'';
	$frontTemp = (!empty($attributes['frontTemp'])) ? $attributes['frontTemp'] : '';
	$backContentType = (!empty($attributes['backContentType'])) ? $attributes['backContentType'] :'default';
	$backTitle = (!empty($attributes['backTitle'])) ? $attributes['backTitle'] : '';
	$backDesc = (!empty($attributes['backDesc'])) ? $attributes['backDesc'] : '';
	$backTemp = (!empty($attributes['backTemp'])) ? $attributes['backTemp'] : '';
	
	$onScrollBar = (!empty($attributes['onScrollBar'])) ? $attributes['onScrollBar'] : false;
	$ovBackFilt = (!empty($attributes['ovBackFilt'])) ? $attributes['ovBackFilt'] : false;
	$backBlur = (!empty($attributes['backBlur'])) ? $attributes['backBlur'] : '1';
	$backGscale = (!empty($attributes['backGscale'])) ? $attributes['backGscale'] : '0';
	
	$redirectLink = (!empty($attributes['redirectLink']['url'])) ? $attributes['redirectLink']['url'] : '';
	$target = (!empty($attributes['redirectLink']['target'])) ? '_blank' : '';
	$nofollow = (!empty($attributes['redirectLink']['nofollow'])) ? 'nofollow' : '';
	$link_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($attributes['redirectLink']);
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$iduu=get_queried_object_id();
	if(!empty($tabReverse)){				
		$uid_ccd = 'ccd'.esc_attr($block_id).esc_attr($iduu);
	}else{
		$uid_ccd = 'ccd'.esc_attr($block_id);
	}
	$clickAction = $visitLink = $codeArrowclass = $tabReverseClass = '';
	$tnsease = 'tpgb-trans-ease';
	$tnseasea = 'tpgb-trans-ease';
	if($couponType=='standard' && ($standardStyle=='style-4' || $standardStyle=='style-5')){
		$tnseasea = 'tpgb-trans-linear';
	}
	
	$hovercopyiconc = '';
	if($actionType=='click') {
		if(!empty($codecopyIcn)){
			$hovercopyiconc = 'code-click-icon';
		}
		$clickAction .= 'href="'.esc_url($redirectLink).'"';
		$clickAction .= ' target="'.esc_attr($target).'"';
		$clickAction .= ' nofollow="'.esc_attr($nofollow).'"';
	} else if($actionType=='popup') {
		if(!empty($tabReverse)){
			$tabReverseClass =" tpgb-tab-cop-rev";
			$clickAction .= 'href="#tpgb-block-'.esc_attr($uid_ccd).'"';
		}else{
			$clickAction .= 'href="'.esc_url($redirectLink).'"';
		}
		$clickAction .= ' target="'.esc_attr($target).'"';
		$clickAction .= ' nofollow="'.esc_attr($nofollow).'"';
		$visitLink .= 'href="'.esc_url($redirectLink).'"';
		$visitLink .= ' target="'.esc_attr($target).'"';
		$visitLink .= ' nofollow="'.esc_attr($nofollow).'"';
	}
	$cpnTextCss =$scrollClass= '';
	$coupon_code_attr = [];
	$coupon_code_attr['id'] = $uid_ccd;
	$coupon_code_attr['couponType'] = $couponType;
	if($couponType=='standard') {
		$coupon_code_attr['actionType'] = $actionType;
		$coupon_code_attr['coupon_code'] = $couponCode;
		$coupon_code_attr['copy_btn_text'] = $copyBtnText;
		$coupon_code_attr['after_copy_text'] = $afterCopyText;
		if($codeArrow){
			$codeArrowclass = 'code-arrow';
			$coupon_code_attr['code_arrow'] = 'code-arrow';
		}else {
			$coupon_code_attr['code_arrow'] = '';
		}
		if($actionType=='popup' && !empty($tabReverse)) {
			$coupon_code_attr['extlink'] = esc_url($redirectLink);
		}

		if($standardStyle=='style-1' || $standardStyle=='style-2' || $standardStyle=='style-3'){
			$cpnTextCss = 'tpgb-abs-flex';
		}

		if(!empty($onScrollBar)){
			$scrollClass = 'tpgb-code-scroll';
		}
	}else if($couponType=='scratch') {
		$coupon_code_attr['fillPercent'] = $fillPercent;
	}
	$slide_out_class = '';
	if($couponType=='slideOut') {
		$coupon_code_attr['slideDirection'] = $slideDirection;
		$slide_out_class = ' slide-out-'.esc_attr($slideDirection);
	}

	$bfcss = $afcss = '';
	if($couponType=='standard'){
		if($standardStyle=='style-1'){
			$bfcss = 'tpgb-trans-ease-before';
			$afcss = 'tpgb-trans-ease-after';
		}else if($standardStyle=='style-2'){
			$afcss = 'tpgb-trans-ease-after';
		}else if($standardStyle=='style-5'){
			$bfcss = 'tpgb-trans-easeinout-before';
			$afcss = 'tpgb-trans-easeinout-after';
		}
	}
	
	$coupon_code_attr = htmlspecialchars(wp_json_encode($coupon_code_attr), ENT_QUOTES, 'UTF-8');

	$output = '';
    $output .= '<div id="tpgb-block-'.esc_attr($uid_ccd).'" class="tpgb-coupon-code tpgb-relative-block action-'.esc_attr($actionType).' coupon-code-'.esc_attr($couponType).''.esc_attr($slide_out_class).' tpgb-block-'.esc_attr($block_id).' tpgb-block-'.esc_attr($uid_ccd).' '.esc_attr($blockClass).''.esc_attr($tabReverseClass).' '.esc_attr($hovercopyiconc).'" data-tpgb_cc_settings=\'' .$coupon_code_attr. '\' data-save-cookies="'.esc_attr($saveCookie).'">';
		if($couponType=='standard') {
			 $output .= '<div class="coupon-code-inner '.esc_attr($standardStyle).' '.esc_attr($bfcss).' '.esc_attr($afcss).'">';
				$data = [];
				if($actionType=='click' && !empty($hideLink) && !empty($linkMaskText)){
					foreach($maskLinkList as $item) {
						$hideLinks = !empty($item["linkUrl"]["url"]) ? $item["linkUrl"]["url"] : '';
						$data[]= $hideLinks;
					}
					if(!empty($redirectLink)){
						$data[]= $redirectLink;		
					}
					$data = wp_json_encode($data);
					$output .= '<a class="coupon-btn-link tpgb-hl-links '.esc_attr($tnseasea).' '.esc_attr($bfcss).' '.esc_attr($afcss).'" href="'.esc_attr($linkMaskText).'" data-hlset=\''.$data .'\' '.$link_attr.'>';
				}else{
					$output .= '<a class="coupon-btn-link '.esc_attr($tnsease).' '.esc_attr($bfcss).' '.esc_attr($afcss).'" '.$clickAction.' '.$link_attr.'>';
				}
					if($standardStyle=='style-4' || $standardStyle=='style-5') {
						$output .= '<span class="coupon-icon '.esc_attr($tnsease).'">';
							$output .= '<svg class="tpgb-scissors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path class="'.esc_attr($tnsease).'" d="M396.8 51.2C425.1 22.92 470.9 22.92 499.2 51.2C506.3 58.27 506.3 69.73 499.2 76.8L216.5 359.5C221.3 372.1 224 385.7 224 400C224 461.9 173.9 512 112 512C50.14 512 0 461.9 0 400C0 338.1 50.14 287.1 112 287.1C126.3 287.1 139.9 290.7 152.5 295.5L191.1 255.1L152.5 216.5C139.9 221.3 126.3 224 112 224C50.14 224 0 173.9 0 112C0 50.14 50.14 0 112 0C173.9 0 224 50.14 224 112C224 126.3 221.3 139.9 216.5 152.5L255.1 191.1L396.8 51.2zM160 111.1C160 85.49 138.5 63.1 112 63.1C85.49 63.1 64 85.49 64 111.1C64 138.5 85.49 159.1 112 159.1C138.5 159.1 160 138.5 160 111.1zM112 448C138.5 448 160 426.5 160 400C160 373.5 138.5 352 112 352C85.49 352 64 373.5 64 400C64 426.5 85.49 448 112 448zM278.6 342.6L342.6 278.6L499.2 435.2C506.3 442.3 506.3 453.7 499.2 460.8C470.9 489.1 425.1 489.1 396.8 460.8L278.6 342.6z"/></svg>';
						$output .= '</span>';
					}
					$output .= '<div class="coupon-text '.esc_attr($cpnTextCss).' '.esc_attr($tnsease).'">'.wp_kses_post($couponText).'</div>';
					if($standardStyle!='style-4' && $standardStyle!='style-5') {
						$output .= '<div class="coupon-code">'.wp_kses_post($couponCode).'</div>';
					}
				$output .= '</a>';
				if($actionType=='popup') {
					$output .= '<div class="copy-code-wrappar" role="dialog"></div>';
					$output .= '<div class="ccd-main-modal '.esc_attr($scrollClass).'" role="alert">';
						$output .= '<button class="tpgb-ccd-closebtn '.esc_attr($tnsease).'" role="button"><i class="fas fa-times"></i></button>';
						$output .= '<div class="popup-code-modal">';
							$output .= '<div class="popup-content">';
								$output .= '<div class="content-title">'.wp_kses_post($popupTitle).'</div>';
								$output .= '<div class="content-desc">'.wp_kses_post($popupDesc).'</div>';
							$output .= '</div>';
							
							$output .= '<div class="coupon-code-outer">';
								$output .= '<span class="full-code-text '.esc_attr($codeArrowclass).' '.esc_attr($tnsease).'">'.wp_kses_post($couponCode).'</span>';
								$output .= '<button class="copy-code-btn '.esc_attr($tnsease).'">'.wp_kses_post($copyBtnText).'</button>';
							$output .= '</div>';
							if(!empty($visitBtnText)){
								$output .= '<div class="coupon-store-visit">';
									$output .= '<a class="store-visit-link '.esc_attr($tnsease).'" '.$visitLink.' '.$link_attr.'>'.wp_kses_post($visitBtnText).'</a>';
								$output .= '</div>';
							}
						$output .= '</div>';
					$output .= '</div>';
				}
			 $output .= '</div>';
		}else if($couponType!='standard') {

			//AJAX base Template Load class
			$frotriclass = $froncntclass = '';
			if( isset($attributes['froajaxbase']) && !empty( $attributes['froajaxbase'] ) && $attributes['froajaxbase'] == 'ajax-base' ){
				
				$frotriclass = 'tpgb-load-template-view tpgb-load-'.esc_attr( $frontTemp );
				$froncntclass = 'tpgb-load-'.esc_attr( $frontTemp ).'-content';
			}
			
			$output .= '<div class="coupon-front-side" id="front-side-'.esc_attr($uid_ccd).'">';
				$output .= '<div class="coupon-front-inner '.esc_attr($frotriclass).'">';
					if($couponType=='slideOut' && !empty($directionHint)){
						$output .= '<div class="tpgb-anim-pos-cont '.esc_attr($slide_out_class).'"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="67.000000pt" height="34.000000pt" viewBox="0 0 67.000000 34.000000" preserveAspectRatio="xMidYMid meet"><g transform="translate(0.000000,34.000000) scale(0.100000,-0.100000)" fill="currentcolor" stroke="none"><path d="M300 250 c0 -64 -9 -86 -25 -60 -3 6 -13 10 -21 10 -29 0 -25 -30 9 -72 19 -24 38 -53 41 -66 7 -20 14 -22 69 -22 l62 0 14 53 c23 88 24 112 7 126 -12 10 -16 11 -16 1 0 -10 -3 -10 -15 0 -8 6 -19 9 -24 6 -5 -4 -13 -2 -16 4 -4 6 -15 8 -26 5 -17 -6 -19 -1 -19 39 0 39 -3 46 -20 46 -18 0 -20 -7 -20 -70z"/><path d="M91 156 l-32 -24 37 -23 c26 -16 39 -19 42 -11 2 7 17 12 33 12 24 0 29 4 29 25 0 21 -5 25 -30 25 -16 0 -30 5 -30 10 0 16 -14 12 -49 -14z"/><path d="M590 170 c0 -5 -16 -10 -35 -10 -31 0 -35 -3 -35 -25 0 -22 4 -25 34 -25 19 0 36 -5 38 -12 3 -8 15 -4 37 11 l33 24 -29 23 c-31 25 -43 29 -43 14z"/></g></svg></div>';
					}
					if($frontContentType=='default' && !empty($frontContent)) {
						$output .= '<div class="coupon-inner-content">';
							$output .= '<h3 class="coupon-front-content">'.wp_kses_post($frontContent).'</h3>';
						$output .= '</div>';
					} else if($frontContentType=='template' && !empty($frontTemp) && $frontTemp!='none' ){
						ob_start();
							if(!empty($frontTemp)) {
								echo Tpgb_Library()->plus_do_block($frontTemp);
							}
						if( isset($attributes['froajaxbase']) && !empty( $attributes['froajaxbase'] ) && $attributes['froajaxbase'] == 'ajax-base'  ){
							$output .= '<div class="'.esc_attr($froncntclass).'"></div>';
						}else{
							$output .= ob_get_contents();
						}
						ob_end_clean();
					}
				$output .= '</div>';
				$output .= '<div class="coupon-code-overlay"></div>';
			$output .= '</div>';
			
			//AJAX base Template Load class
			$backtriclass = $backcntclass = '';
			if( isset($attributes['backajaxbase']) && !empty( $attributes['backajaxbase'] ) && $attributes['backajaxbase'] == 'ajax-base' ){
				
				$backtriclass = 'tpgb-load-template-view tpgb-load-'.esc_attr( $backTemp );
				$backcntclass = 'tpgb-load-'.esc_attr( $backTemp ).'-content';
			}

			$output .= '<div class="coupon-back-side">';
				$output .= '<div class="coupon-back-inner '.esc_attr($backtriclass).'">';
					if($backContentType=='default') {
						$output .= '<div class="coupon-back-content">';
						if(!empty($backTitle)) {
							$output .= '<h3 class="coupon-back-title">'.wp_kses_post($backTitle).'</h3>';
						}
						if(!empty($backDesc)) {
							$output .= '<p class="coupon-back-description">'.wp_kses_post($backDesc).'</p>';
						}
						$output .= '</div>';
					} else if($backContentType=='template' && !empty($backTemp) && $backTemp!='none' ){
						ob_start();
							if(!empty($backTemp)) {
								echo Tpgb_Library()->plus_do_block($backTemp);
							}
						if( isset($attributes['backajaxbase']) && !empty( $attributes['backajaxbase'] ) && $attributes['backajaxbase'] == 'ajax-base'  ){
							$output .= '<div class="'.esc_attr($backcntclass).'"></div>';
						}else{
							$output .= ob_get_contents();
						}
						ob_end_clean();
					}
				$output .= '</div>';
				$output .= '<div class="coupon-code-overlay"></div>';
			$output .= '</div>';
		}
    $output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_coupon_code() {

	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_coupon_code_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_coupon_code' );