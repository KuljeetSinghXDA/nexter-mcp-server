<?php
/* Block : Container(Section)
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_container_render_callback( $attributes, $content) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
    $height = (!empty($attributes['height'])) ? $attributes['height'] : '';
    $shapeTop = (!empty($attributes['shapeTop'])) ? $attributes['shapeTop'] : '';
    $shapeBottom = (!empty($attributes['shapeBottom'])) ? $attributes['shapeBottom'] : '';
    $customClass = (!empty($attributes['customClass'])) ? $attributes['customClass'] : '';
	$customId = (!empty($attributes['customId'])) ? 'id="'.esc_attr($attributes['customId']).'"' : ( isset($attributes['anchor']) && !empty($attributes['anchor']) ? 'id="'.esc_attr($attributes['anchor']).'"'  : '' ) ;
	
	$liveCopy = (!empty($attributes['liveCopy'])) ? 'yes' : 'no';
    $currentID = (!empty($attributes['currentID'])) ? $attributes['currentID'] : get_queried_object_id();

	$deepBgopt = (!empty($attributes['deepBgopt'])) ? $attributes['deepBgopt'] : '';
	$colorList = (!empty($attributes['colorList'])) ? $attributes['colorList'] : [];
	$animdur = (!empty($attributes['animdur'])) ? (int) $attributes['animdur'] : 3;
	$animDelay = (isset($attributes['animDelay'])) ? ($attributes['animDelay']) : 0;
	$imgeffect = (!empty($attributes['imgeffect'])) ? $attributes['imgeffect'] : '';
	$intensity = (!empty($attributes['intensity'])) ? $attributes['intensity'] : '';
	$Scale = (!empty($attributes['Scale'])) ? $attributes['Scale'] : '' ;
	$perspective = (!empty($attributes['perspective'])) ? $attributes['perspective'] : '';
	$invert = (!empty($attributes['inverted'])) ? 'true' : 'false';
	$scrollPara = (!empty($attributes['scrollPara'])) ? $attributes['scrollPara'] : false;
	$craBgeffect = (!empty($attributes['craBgeffect'])) ? $attributes['craBgeffect'] : 'columns_simple_image' ;
	$movedir = (!empty($attributes['movedir'])) ? $attributes['movedir'] : 'left';
	$trasispeed = (!empty($attributes['trasispeed'])) ? $attributes['trasispeed'] : '';
	$kburnseffect = (!empty($attributes['kburnseffect'])) ? $attributes['kburnseffect'] : false;
	$Kbeffctdir = (!empty($attributes['Kbeffctdir'])) ? $attributes['Kbeffctdir'] : '';
	$effctDure = (!empty($attributes['effctDure'])) ? $attributes['effctDure'] : '';
	$videosour = (!empty($attributes['videosour'])) ? $attributes['videosour'] : 'youtube';
	$youtubeId = (!empty($attributes['youtubeId'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['youtubeId']) : 'QrI0jo5JZSs';
	$videoImg = (!empty($attributes['videoImg'])) ? $attributes['videoImg'] : '';
	$vimeoId = (!empty($attributes['vimeoId'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['vimeoId']) : '';
	$rowImgs= (!empty($attributes['rowImgs'])) ? $attributes['rowImgs'] : [] ;
	$scrollchg = (!empty($attributes['scrollchg'])) ? $attributes['scrollchg'] : 'no';
	
	$midOption = (!empty($attributes['midOption'])) ? $attributes['midOption'] : '';
	$topOption = (!empty($attributes['topOption'])) ? $attributes['topOption'] : '';
	
	$wrapLink = (!empty($attributes['wrapLink'])) ? $attributes['wrapLink'] : false;

	$showchild = (!empty($attributes['showchild'])) ? $attributes['showchild'] : false;

	$colDir = (!empty($attributes['colDir'])) ? $attributes['colDir'] : '';
	$contentWidth = (!empty($attributes['contentWidth'])) ? $attributes['contentWidth'] : 'wide';

	$tagName = (!empty($attributes['tagName'])) ? $attributes['tagName'] : '';
	$NormalBg = (!empty($attributes['NormalBg'])) ? (array) $attributes['NormalBg'] : [] ;
	$HoverBg = (!empty($attributes['HoverBg'])) ? (array) $attributes['HoverBg'] : [] ;

	$equalHeightAtt = Tpgbp_Pro_Blocks_Helper::global_equal_height( $attributes );

	$contSticky = (!empty($attributes['contSticky'])) ? $attributes['contSticky'] : [];
	$contOverlays = (!empty($attributes['contOverlays'])) ? $attributes['contOverlays'] : false ;
	$constType = (!empty($attributes['constType'])) ? $attributes['constType'] : '' ;
	$contopoffset = (!empty($attributes['contopoffset'])) ? $attributes['contopoffset'] : '' ;
	$sanimaType = (!empty($attributes['sanimaType'])) ? $attributes['sanimaType'] : '' ;
	$scupSticky = (!empty($attributes['scupSticky'])) ? $attributes['scupSticky'] : false ;
	$stayConta = (!empty($attributes['stayConta'])) ? $attributes['stayConta'] : [];
	$selectedLayout = (!empty($attributes['selectedLayout'])) ? $attributes['selectedLayout'] : '';
    $nxtcontType = (!empty($attributes['nxtcontType'])) ? $attributes['nxtcontType'] : false;
    $contwidFull = (!empty($attributes['contwidFull'])) ? $attributes['contwidFull'] : '';

	$equalHclass = '';
	if(!empty($equalHeightAtt)){
		$equalHclass = ' tpgb-equal-height';
	}

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$sectionClass = '';
	if( !empty( $height ) ){
		$sectionClass .= ' tpgb-section-height-'.$height;
	}
	
	if(!empty($contOverlays)){
		$sectionClass .= ' tpgb-container-overlays';
	}
	
    if( defined('NXT_VERSION') && $contentWidth !== 'full' && !empty($nxtcontType) ){
        $sectionClass .= ' tpgb-nxtcont-type';
    }
	// Toogle Class For wrapper Link

	$linkdata = '';
	if(!empty($wrapLink)){
		$rowUrl = (!empty($attributes['rowUrl'])) ? $attributes['rowUrl'] : '';
		$codyUrl = '';
		$sectionClass .= ' tpgb-row-link';
		
		if( isset($rowUrl['dynamic']) ) {
			$codyUrl = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($rowUrl);
		}else if( isset($rowUrl['url']) && !empty($rowUrl['url']) ){
			$codyUrl =  $rowUrl['url'];
		}
		$linkdata .= 'data-tpgb-row-link="'.esc_url($codyUrl).'" ';

		if(!empty($rowUrl) && isset($rowUrl['target']) && !empty($rowUrl['target'])){
			$linkdata .= 'data-target="_blank" ';
		}else{
			$linkdata .= 'data-target="_self" ';
		}
		$linkdata .= Tpgbp_Pro_Blocks_Helper::add_link_attributes($attributes['rowUrl']);
	}

	$extrDiv = '';
	if(!empty($contSticky) ){
		if($constType == 'sticky'){
			if( (isset( $contSticky['md']) && !empty($contSticky['md'])) || (isset( $contSticky['sm']) && !empty($contSticky['sm'])) || (isset( $contSticky['xs']) && !empty($contSticky['xs']))){
				$sectionClass .= ' tpgb-sticky-only';
				$sectionClass .= ' tpgb-sticky-'.$sanimaType;
				$extrDiv = '<div class="tpgb-stick-header-height"></div>';
			}
			
		}else if( (isset( $contSticky['md']) && !empty($contSticky['md'])) || (isset( $contSticky['sm']) && !empty($contSticky['sm'])) || (isset( $contSticky['xs']) && !empty($contSticky['xs'])) ){
			$sectionClass .= ' tpgb-sticky-enable';
			$sectionClass .= ' tpgb-sticky-'.$sanimaType;
			$extrDiv = '<div class="tpgb-stick-header-height"></div>';
		}

		$sticyArr = [];
		if( isset( $contSticky['md']) && !empty($contSticky['md']) ){
			$sticyArr['md'] = true;
		}

		if( isset( $contSticky['sm']) && !empty($contSticky['sm']) ){
			$sticyArr['sm'] = true;
		}else{
			if( !isset($contSticky['sm']) && isset( $contSticky['md']) && !empty($contSticky['md']) ){
				$sticyArr['sm'] = true;
			}
		}

		if( isset( $contSticky['xs']) && !empty($contSticky['xs']) ){
			$sticyArr['xs'] = true;
		}else{
			if( !isset($contSticky['xs']) && isset( $contSticky['sm']) && !empty($contSticky['sm']) ){
				$sticyArr['xs'] = true;
			}else{
				if( !isset($contSticky['xs']) && isset( $contSticky['md']) && !empty($contSticky['md']) ){
					$sticyArr['xs'] = true;
				}
			}
		}

		if( isset($contopoffset['md']) && !empty($contopoffset['md']) ){
			$sticyArr['topoff']['md'] = $contopoffset['md'];
		}
		if( isset($contopoffset['sm']) && !empty($contopoffset['sm']) ){
			$sticyArr['topoff']['sm'] = $contopoffset['sm'];
		}
		if( isset($contopoffset['xs']) && !empty($contopoffset['xs']) ){
			$sticyArr['topoff']['xs'] = $contopoffset['xs'];
		}
		
		if( isset($scupSticky) && !empty($scupSticky) ){
			$sticyArr['scupSticky'] = $scupSticky;
		}
		if( (isset( $contSticky['md']) && !empty($contSticky['md'])) || (isset( $contSticky['sm']) && !empty($contSticky['sm'])) || (isset( $contSticky['xs']) && !empty($contSticky['xs'])) ){
			$linkdata .= ' data-sticky-opt= \'' .wp_json_encode($sticyArr). '\'';
		}
	}

	$flexChildCss = '';
	
	// Stay In Container css
	if( isset($stayConta['md']) && !empty($stayConta['md']) ){
		$flexChildCss .= '.tpgb-block-'.esc_attr($block_id).'.tpgb-sticky-none.tpgb-desk-sticky{ position: sticky !important; }';
	}
	if( isset($stayConta['sm']) && !empty($stayConta['sm']) ){
		$flexChildCss .= '@media (max-width: 1024px) and (min-width:768px) { .tpgb-block-'.esc_attr($block_id).'.tpgb-sticky-none.tpgb-tab-sticky{ position: sticky !important; } }';
	}
	if( isset($stayConta['xs']) && !empty($stayConta['xs']) ){
		$flexChildCss .= '@media (max-width: 767px){ .tpgb-block-'.esc_attr($block_id).'.tpgb-sticky-none.tpgb-moblie-sticky{ position: sticky !important; } }';
	}

	global $post;
    global $repeater_index;
	if( isset($NormalBg) && !empty($NormalBg) && $NormalBg['openBg'] == 1 ){
		if( class_exists('Tpgbp_Pro_Blocks_Helper') ) {
			$sectionClass .= ' tpgb-container-'.( isset($repeater_index) ? esc_attr($repeater_index) : ( isset($post->ID) ? esc_attr($post->ID) : '' ) ).'';
			if( $NormalBg['bgType']== 'image' && isset($NormalBg['bgImage']['dynamic']) && isset($NormalBg['bgImage']['dynamic']['dynamicUrl']) ){
				$dyImgUrl = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($NormalBg['bgImage']);

				if( !empty($dyImgUrl) ){
					$flexChildCss .= '.tpgb-block-'.esc_attr($block_id).'.tpgb-container-'.( isset($repeater_index) ? esc_attr($repeater_index) : ( isset($post->ID) ? esc_attr($post->ID) : '' ) ).'{ background-image : url('.esc_url($dyImgUrl ).') }';
				}
			}
			if( $NormalBg['bgType']== 'color' && isset($NormalBg['bgDefaultColor']) && preg_match_all( '/<span data-tpgb-dynamic=(.*?)>(.*?)<\/span>/' , $NormalBg['bgDefaultColor'], $matches ) ){
				if( isset($matches[1]) && !empty($matches[1]) ){
					$dyColor = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($NormalBg['bgDefaultColor'],['blockName' => 'tpgb/tp-container']);
					if( !empty($dyColor) ){
						$flexChildCss .= '.tpgb-block-'.esc_attr($block_id).'.tpgb-container-'.( isset($post->ID) ? esc_attr($post->ID) : '' ).'{ background-color : '.esc_url($dyColor).'}';
					}
				}
			}
		}
	}

	// Hover Image
	if(isset($HoverBg) && !empty($HoverBg) && $HoverBg['openBg'] == 1 ){
		if( class_exists('Tpgbp_Pro_Blocks_Helper') ) {
			$sectionClass .= ' tpgb-container-'.( isset($repeater_index) ? esc_attr($repeater_index) : ( isset($post->ID) ? esc_attr($post->ID) : '' ) ).'';
			if(  $HoverBg['bgType']== 'image' && isset($HoverBg['bgImage']['dynamic']) && isset($HoverBg['bgImage']['dynamic']['dynamicUrl']) ){
				$dyhovImgUrl = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($HoverBg['bgImage']);

				if( !empty($dyhovImgUrl) ){
					$flexChildCss .= '.tpgb-block-'.esc_attr($block_id).'.tpgb-container-'.( isset($repeater_index) ? esc_attr($repeater_index) : ( isset($post->ID) ? esc_attr($post->ID) : '' ) ).':hover{ background-image : url('.esc_url($dyhovImgUrl ).') }';
				}
			}
			
			if( $HoverBg['bgType']== 'color' && isset($HoverBg['bgDefaultColor']) && preg_match_all( '/<span data-tpgb-dynamic=(.*?)>(.*?)<\/span>/' , $HoverBg['bgDefaultColor'], $matches ) ){
				if( isset($matches[1]) && !empty($matches[1]) ){
					$dyhvColor = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($HoverBg['bgDefaultColor'],['blockName' => 'tpgb/tp-container']);
					if( !empty($dyColor) ){
						$flexChildCss .= '.tpgb-block-'.esc_attr($block_id).'.tpgb-container-'.( isset($post->ID) ? esc_attr($post->ID) : '' ).':hover{ background-color : '.esc_url($dyhvColor).'}';
					}
				}
			}
			
		}
	}
	
	$shapeContent = '';
	$shapePath = TPGB_PATH . 'assets/images/shape-divider';
	if(!empty($shapeTop)){
		$TInvert = (!empty($attributes['shapeTInvert'])) ? '-invert' : '';
		$shapeUrl = $shapePath . '/'.esc_attr($shapeTop).esc_attr($TInvert).'.svg';
		$bringFront = (!empty($attributes['shapeTFront'])) ? 'bring-front' : '';
		$shapeTFlip = (!empty($attributes['shapeTFlip'])) ? 'shape-flip' : '';
		$shapeContent .= '<div class="tpgb-section-divider section-top-divider shape-'.esc_attr($shapeTop).' '.esc_attr($bringFront).' '.esc_attr($shapeTFlip).'">'.file_get_contents($shapeUrl).'</div>';
	}
	if(!empty($shapeBottom)){
		$BInvert = (!empty($attributes['shapeBInvert'])) ? '-invert' : '';
		$shapeUrl = $shapePath . '/'.esc_attr($shapeBottom).esc_attr($BInvert).'.svg';
		$bringFront = (!empty($attributes['shapeBFront'])) ? 'bring-front' : '';
		$shapeBFlip = (!empty($attributes['shapeBFlip'])) ? 'shape-flip' : '';
		$shapeContent .= '<div class="tpgb-section-divider section-bottom-divider shape-'.esc_attr($shapeBottom).' '.esc_attr($bringFront).' '.esc_attr($shapeBFlip).'">'.file_get_contents($shapeUrl).'</div>';
	}

	//Set Row Backgroung Attr

	$dataAttr=$classname=$rowBgClass=$rowclass=$cssrule=$videoattr=$videoUrl=$midclass=$dataAttr2=$midlayer=$loop =$midclass1 = '';
	$colors = array();
	if(!empty($deepBgopt) && ($deepBgopt == 'bg_color' || $deepBgopt == 'bg_animate_gradient' || $deepBgopt == 'scroll_animate_color' )){
		if(!empty($colorList)){
			$i = 0;
			$scrolltra = (!empty($attributes['scrolltra'])) ? $attributes['scrolltra'] : '0.7';

			foreach($colorList as $index => $item) {
				$actclass = '';
				if($deepBgopt == 'bg_color' || $deepBgopt == 'bg_animate_gradient'){
					$colors[] = $item['aniColor'];
				}else{
					if( empty($item['scrollImg']['url']) ) {
						if( isset($item['aniBgtype']['openBg']) && !empty($item['aniBgtype']['openBg']) ){
							if( isset($item['aniBgtype']['bgType']) && $item['aniBgtype']['bgType'] == 'color' && isset($item['aniBgtype']['bgDefaultColor']) && !empty($item['aniBgtype']['bgDefaultColor']) ){
								$colors[] = $item['aniBgtype']['bgDefaultColor'];
							}else{
								if( isset($item['aniBgtype']['bgType']) && $item['aniBgtype']['bgType'] == 'gradient' && isset($item['aniBgtype']['bgGradient']) && !empty($item['aniBgtype']['bgGradient']) ){
									$colors[] = $item['aniBgtype']['bgGradient'];
								}
							}
						}
						if($i==0){
							$cssrule = 'background:'.esc_attr($item['aniColor']).';';
							$actclass = ( $scrollchg=='no' ) ? 'active' : '';
						}
						if(!empty($scrollchg) && $scrollchg=='no'){
							$loop .='<div class="tp-repeater-item-'.esc_attr($item['_key']).' tpgb-section-bg-scrolling '.esc_attr($actclass).'" style="background:'.esc_attr($item['aniColor']).';transition-duration: '.esc_attr($scrolltra).'s"></div>';
						}
						
					}
					if(!empty($scrollchg) && $scrollchg=='no' && !empty($item['scrollImg']) && !empty($item['scrollImg']['url'])) {
						$colors[] = $item['scrollImg']['url'];
						if($i==0){
							$cssrule='background:url('.esc_url($item['scrollImg']['url']).');';
							$actclass = ( $scrollchg=='no' ) ? 'active' : '';
						}
						if(!empty($scrollchg) && $scrollchg=='no'){
							$loop .= '<div class="tp-repeater-item-'.esc_attr($item['_key']).' tpgb-section-bg-scrolling '.esc_attr($actclass).'" style="background:url('.esc_url($item['scrollImg']['url']).');transition-duration : '.esc_attr($scrolltra).'s"></div>';
						}
					}
					if( isset($item['aniBgtype']) && !empty($item['aniBgtype']) && isset($item['aniBgtype']['openBg']) &&  $item['aniBgtype']['openBg'] == 1 ) {
						$loop .= '<div class="tp-repeater-item-'.esc_attr($item['_key']).' tpgb-section-bg-scrolling '.esc_attr($actclass).'"></div>';
					}
				}
				$i++;
			}
		}
		
		if($deepBgopt == 'bg_color') {
			$dataAttr .= ' data-bg="'.htmlspecialchars(wp_json_encode($colors)).'" data-bg-delay="'.esc_attr($animDelay).'" data-bg-duration="'.esc_attr($animdur).'" ';
			$classname = 'row-animat-bg';
		}else if($deepBgopt == 'bg_animate_gradient'){
			$bgRotation = (!empty($attributes['bgRotation'])) ? $attributes['bgRotation'] : '';
			$bgduration = (!empty($attributes['bgduration'])) ? $attributes['bgduration'] : '';
			$fullBggra = (!empty($attributes['fullBggra'])) ? 'yes' : 'no';
			$bgposition = (!empty($attributes['bgposition'])) ? $attributes['bgposition'] : '';
			
			if($colors!=''){	
				$first = reset($colors);
				$last = end($colors);
				$grad_colors=implode (",", $colors);
			}else{
				$first = '#ff2d60';	$last = '#1deab9';
				$grad_colors= '#ff2d60,#ff9132,#ff61fa,#6caafd,#29ccff,#1deab9';
			}
			$classname = "tpgb-row-bg-gradient";
			$cssrule = 'filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='.esc_attr($first).', endColorstr='.esc_attr($last).');background-image: linear-gradient('.esc_attr($bgRotation).'deg,'.$grad_colors.');animation-duration: '.esc_attr($bgduration).'s;';
			$dataAttr = 'data-full-page="'.esc_attr($fullBggra).'" data-position="'.esc_attr($bgposition).'"';
		}else if($deepBgopt == 'scroll_animate_color'){
			if(!empty($scrollchg) && $scrollchg=='yes'){
				$cssrule .= 'transition-duration:0s';
			}
			$classname = 'tpgb-row-scrollbg';
			$dataAttr = ' data-bgcolors="'.htmlspecialchars(wp_json_encode($colors)).'" data-scrolling-effect='.esc_attr($scrollchg).' ';
		}
	}else if(!empty($deepBgopt) && $deepBgopt == 'bg_image' && $craBgeffect == 'columns_simple_image' && $imgeffect == 'style-1' ){

		$rowBgClass = ' tpgb-img-parallax-mouse';
		$classname = 'tpgb-img-parallax-hover';
		$dataAttr .= ' data-type="tilt" data-amount="'.esc_attr($intensity).'" data-scale="'.esc_attr($Scale).'" data-perspective="'.esc_attr($perspective).'" data-inverted="'.esc_attr($invert).'" data-opacity="1" ';
	}else if(!empty($deepBgopt) && $deepBgopt == 'bg_image' && $craBgeffect == 'columns_simple_image' && $imgeffect == 'style-2' ){
		$rowBgClass = ' tpgb-img-parallax-mouse';
		$classname = 'tpgb-img-parallax-hover';
		$dataAttr .= ' data-type="move" data-amount="'.esc_attr($intensity).'" data-scale="'.esc_attr($Scale).'" data-inverted="'.esc_attr($invert).'" data-opacity="1" ';
	}else if(!empty($deepBgopt) && $deepBgopt == 'bg_image' && $craBgeffect == 'columns_animated_bg'){
		$classname .= $craBgeffect ;
		$classname .= ' row-bg-'.$movedir.'';
		$dataAttr .= ' data-direction="'.esc_attr($movedir).'" data-trasition="'.esc_attr($trasispeed).'" ';
	}else if(!empty($deepBgopt) && $deepBgopt == 'bg_video'){
		$classname = 'tpgb-video-'.esc_attr($videosour);
		if($videosour == 'youtube'){
			$videoattr .= !empty($attributes['videoMute']) ? 'data-muted=1' : 'data-muted=0' ;
			$videoUrl .= !empty($attributes['videoMute']) ? '&amp;mute=1&amp;autoplay=1&amp;playlist='.esc_attr($youtubeId) : '&amp;mute=0&amp;autoplay=1&amp;playlist='.esc_attr($youtubeId) ;
			$videoUrl .= !empty($attributes['videoloop']) ? '&amp;loop=1' : '' ;
		}
		if($videosour == 'vimeo'){
			$videoattr .= !empty($attributes['videoMute']) ? 'data-muted=1' : 'data-muted=0' ;
			$videoUrl .= !empty($attributes['videoloop']) ? '&amp;loop=1' : '' ;
		}
		if($videosour == 'self-hosted'){
			//$videoattr .= (!empty($videoImg) && !empty($videoImg['url']) ? 'poster="'. esc_url($videoImg['url']) .'"' : '' );
			$videoattr .= !empty($attributes['videoMute']) ? ' muted=true' : '' ;
			$videoattr .= !empty($attributes['videoloop']) ? ' loop=true' : '' ;
			if(!empty($attributes['mp4Url'])) {
				$mp4url = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['mp4Url']);
				$videoUrl .= ' src="'.esc_url($mp4url).'"';
			}
			if(!empty($attributes['WebMUrl'])){
				$weburl = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['WebMUrl']);
				$videoUrl .= ' src="'.esc_url($weburl).'"';
			}
			
		}
	}else if(!empty($deepBgopt) && $deepBgopt == 'bg_gallery'){
		$classname = 'row-img-slide';
	}

	if(!empty($midOption) ){
		if($midOption == 'canvas') {
			$canvasSty = (!empty($attributes['canvasSty'])) ? $attributes['canvasSty'] :'style-1';
			$patiColor =  (!empty($attributes['patiColor'])) ? $attributes['patiColor'] :'';
			$canShape =  (!empty($attributes['canShape'])) ? $attributes['canShape'] :'';
			$ctmJson = (!empty($attributes['ctmJson'])) ? $attributes['ctmJson'] : '';

			$midclass = 'canvas-'.$canvasSty.$block_id;
			if( $canvasSty == 'style-1' || $canvasSty == 'style-4' || $canvasSty == 'style-8' ){
				$midclass1 .= ' canvas-'.esc_attr($canvasSty).'';
			}

			$canvasarr = array();
			if($canvasSty == 'style-1'){
				$particleList = (!empty($attributes['particleList'])) ? $attributes['particleList'] : [] ;
				if($particleList != ''){
					foreach($particleList as $item) {
						if(isset($item['bubbleColor']) && $item['bubbleColor'] != '') {			
							$canvasarr[] = $item['bubbleColor'];
						}else{
							$canvasarr =array('#dd3333', '#dd9933', '#eeee22', '#81d742', '#1e73be');
						}
					}
				}
				
				for($ij=1;$ij<=50;$ij++) {	
					$size= wp_rand(1,30).'px';
					$midlayer .='<div class="tpgb-bubble" style="height: '.esc_attr($size).';width: '.esc_attr($size).';animation-delay: -'.($ij*0.2).'s;-webkit-transform:translate3d( '.wp_rand(-2000,2000).'px,  '.wp_rand(-1000,2000).'px, '.wp_rand(-1000,2000).'px);transform: translate3d( '.wp_rand(-2000,2000).'px,  '.wp_rand(-1000,2000).'px, '.wp_rand(-1000,2000).'px);background: '.$canvasarr[array_rand($canvasarr)].';"></div>'; 
				}
			}else if($canvasSty == 'style-2' || $canvasSty == 'style-3' || $canvasSty == 'style-4'){
				$dataAttr2 = 'data-color="'.esc_attr($patiColor).'" ';
			}else if($canvasSty == 'style-5' || $canvasSty == 'style-6' || $canvasSty == 'style-7'){
				$dataAttr2 = 'data-color="'.esc_attr($patiColor).'" data-type="'.esc_attr($canShape).'" ';
			}else if($canvasSty == 'style-8'){
				$dataAttr2 = 'data-color="'.esc_attr($patiColor).'" ';
				$midlayer .= '<canvas class="tpgb-snow-particles"></canvas>';
			}else{
				$dataAttr2 = 'data-patijson="'.esc_attr($ctmJson).'" ';
			}
		}else if( $midOption == 'mordern_image_effect' || $midOption == 'moving_image' ){
			$midimgList = (!empty($attributes['midimgList'])) ? $attributes['midimgList'] : [] ;
			$midclass = ($midOption == 'mordern_image_effect' ? 'tpgb-mordernimg-effect tpgb-mordern-parallax' : ($midOption == 'moving_image' ? 'tpgb-automove-img' : '' ) );
			$animCss = '';
			if(!empty($midimgList)){
				foreach($midimgList as $item) {
					$scene_loop = $scrollAttr = [];
					$imgsrc=$visibility=$imgcss=$magicAttr='';
					if(!empty($item['resVisib'])){
						$visibility .= (!empty($item['desHide']) ? 'desktop-hide ' : '' );							
						$visibility .= (!empty($item['tabHide']) ? 'tablet-hide ' : '' );
						$visibility .= (!empty($item['moHide']) ? 'mobile-hide ' : '' );
					}
					$Effectin = !empty($item['Effectin']) ? (int)$item['Effectin'] : 0;

					if(!empty($item['parallaxImg']) && !empty($item['parallaxImg']['url'] )){
						$imgsrc = $item['parallaxImg']['url'] ;
					}
					if( $midOption == 'moving_image' && !empty($imgsrc) ){
						$imgcss .= 'background-image: url('.esc_url($imgsrc).');background-size:'.(!empty($item['imgSize']) ? esc_attr($item['imgSize']) : '' ).' ';
					}

					if( $midOption == 'mordern_image_effect' && !empty($item['tpgbMagicScroll']) ){
						$effect = [ 'vertical' => 'MSVertical', 'horizontal' => 'MSHorizontal', 'opacity' => 'MSOpacity', 'rotate' => 'MSRotate', 'scale' => 'MSScale' , 'skew' => 'MSSkew' , 'borderR' => 'borderR' ];
						foreach( $effect as $key => $val){
							if( isset($item[$val]) && !empty($item[$val]) && isset($item[$val]['tpgbReset']) && !empty($item[$val]['tpgbReset']) ){
								unset($item[$val]['tpgbReset']);
								$scene_loop[$key] = $item[$val];
							}
						}
						if(isset($item['MSScrollOpt']) && !empty($item['MSScrollOpt']) && isset($item['MSScrollOpt']['tpgbReset']) && !empty($item['MSScrollOpt']['tpgbReset'])){
							$extraOpt = $item['MSScrollOpt'];
							if(isset($extraOpt['trigger']) && !empty($extraOpt['trigger'])){
								$scene_loop['trigger'] = $extraOpt['trigger'];
							}
							if(isset($extraOpt['duration']) && !empty($extraOpt['duration'])){
								$scene_loop['duration'] = $extraOpt['duration'];
							}
							if(isset($extraOpt['offset']) && !empty($extraOpt['offset'])){
								$scene_loop['offset'] = $extraOpt['offset'];
							}
						}else{
							$scene_loop['trigger'] = (object)[ 'md' => 0.5 ];
							$scene_loop['duration'] = (object)[ 'md' => 300, "unit" => 'px', ];
							$scene_loop['offset'] = (object)[ 'md' => '0', "unit" => 'px', ];
						}

						if(isset($item['MsadvOption']) && !empty($item['MsadvOption']) && isset($item['MsadvOption']['tpgbReset']) && !empty($item['MsadvOption']['tpgbReset'])){
							$advOpt = $item['MsadvOption'];

							if(isset($advOpt['repeat']) && !empty($advOpt['repeat'])){
								$scene_loop['repeat'] = $advOpt['repeat'];
							}
							if(isset($advOpt['delay']) && !empty($advOpt['delay'])){
								$scene_loop['delay'] = $advOpt['delay'];
							}
							if(isset($advOpt['timing']) && !empty($advOpt['timing'])){
								$scene_loop['timing'] = $advOpt['timing'];
							}
							if(isset($advOpt['easing']) && !empty($advOpt['easing'])){
								$scene_loop['easing'] = $advOpt['easing'];
							}
							if(isset($advOpt['reverse'])){
								$scene_loop['reverse'] = $advOpt['reverse'];
							}
						}else{
							$scene_loop['repeat'] = (object)[ 'md' => 0 ];
							$scene_loop['delay'] = (object)[ 'md' => 0 ];
							$scene_loop['timing'] = (object)[ 'md' => 1 ];
							$scene_loop['reverse'] = true;
						}

						if(isset($item['MSSticky'])){
							$scene_loop['sticky'] = $item['MSSticky'];
						}
						if(isset($item['MSDevelop'])){
							$scene_loop['develop'] = $item['MSDevelop'];
							if(isset($item['devName']) && !empty($item['devName'])){
								$scene_loop['develop_name'] = $item['devName'];
							}
						}
						if(!empty($scene_loop)){
							$scrollAttr[] = $scene_loop;
						}
						$devices = ['md','sm','xs'];
						
						$magicAttr = 'data-tpgb-ms="'.htmlspecialchars(wp_json_encode($scrollAttr), ENT_QUOTES, 'UTF-8').'" data-tpgb-msview="'.htmlspecialchars(wp_json_encode($devices), ENT_QUOTES, 'UTF-8').'" ';
					}

					//Set Transforn origin
					if( isset( $item['modImgeff'] ) && !empty($item['modImgeff']) && $item['modImgeff'] == 'rotating' && !empty($item["tranOrigin"]) ){
						$animCss .= '-webkit-transform-origin: '.esc_attr($item["tranOrigin"]).';-moz-transform-origin:'.esc_attr($item["tranOrigin"]).';-ms-transform-origin:'.esc_attr($item["tranOrigin"]).';-o-transform-origin:'.esc_attr($item["tranOrigin"]).';transform-origin:'.esc_attr($item["tranOrigin"]).';';
					}
					$midlayer .= '<div class="tpgb-parlximg-wrap tp-repeater-item-'.esc_attr($item['_key']).' '.(($midOption == 'mordern_image_effect' ? 'tpgb-repet-img' : '')).' '.esc_attr($visibility).' '.( $midOption == 'mordern_image_effect' && !empty($item['tpgbMagicScroll']) ? ' tpgb_magic_scroll' : '').'" style="'.esc_attr($imgcss).'" data-direction="'.(!empty($item['imgDire']) ? esc_attr($item['imgDire']) : '' ).'" data-trasition="'.esc_attr($Effectin).'" '.$magicAttr.'>';
						if( $midOption == 'mordern_image_effect' ){
							if(!empty($item['tpgbMagicScroll'])){
								$midlayer .=  '<div>';
							}
							$altText = (isset($item['parallaxImg']['alt']) && !empty($item['parallaxImg']['alt'])) ? esc_attr($item['parallaxImg']['alt']) : ((!empty($item['parallaxImg']['title'])) ? esc_attr($item['parallaxImg']['title']) : esc_attr__('Parallax Image','tpgbp'));

							$midlayer .=  '<img class="tpgb-parlximg '.( isset( $item['modImgeff'] ) && !empty( $item['modImgeff'] ) && $midOption == 'mordern_image_effect' ? 'tpgb-imgeffect tpgb-'.$item['modImgeff'] : '').'" src="'.esc_url($imgsrc).'" alt="'.$altText.'" data-parallax="'.esc_attr($Effectin).'" style="'.$animCss.'" />';
							if(!empty($item['tpgbMagicScroll'])){
								$midlayer .=  '</div>';
							}
						}
					$midlayer .= '</div>';
				}
			}
		}
	}

	if(!empty($deepBgopt) && $deepBgopt == 'bg_image' && !empty($scrollPara)){
		$rowclass = ' tpgb-scroll-parallax';
		$classname .= ' img-scroll-parallax';
	}
	if(!empty($kburnseffect)){
		$cssrule .='-webkit-animation: bg-kenburns-effect '.esc_attr($Kbeffctdir).'s cubic-bezier(0.445, 0.050, 0.550, 0.950) infinite '.esc_attr($Kbeffctdir).' both;animation: bg-kenburns-effect '.esc_attr($effctDure).'s cubic-bezier(0.445, 0.050, 0.550, 0.950) infinite '.esc_attr($Kbeffctdir).' both;';
	}
	
	if( defined('TPGBP_DEVELOPER') && TPGBP_DEVELOPER && $liveCopy == 'yes' ){
		$linkdata .= ' data-tpcp__live="'.esc_attr($liveCopy).'"';
		$linkdata .= ' data-post-id="'.esc_attr($currentID ).'"';
	}

	$output .= $extrDiv;
	$output .= '<'.Tp_Blocks_Helper::validate_html_tag($tagName).' '.$customId.' class="tpgb-container-row tpgb-block-'.esc_attr($block_id).' '.esc_attr($sectionClass).' '.esc_attr($customClass).' '.esc_attr( $rowclass ).' '.esc_attr($blockClass).' '.esc_attr($equalHclass).' '.($colDir == 'c100' || $colDir == 'r100' ? ' tpgb-container-inline' : '').' tpgb-container-'.$contentWidth.' '.($selectedLayout == 'grid' ? ' tpgb-grid' : '').' " data-id="'.esc_attr($block_id).'" '.$linkdata.' '.$equalHeightAtt.'>';

		/* Row Background Start  */
		if(!empty($deepBgopt) || !empty($midOption) || !empty($topOption)) {

			$output .= '<div class="tpgb-row-background '.esc_attr($rowBgClass).' '.(!empty($attributes['parallax']) && $deepBgopt == 'bg_video'  ? ' fixed-bg-video' : '' ).'">';
			
				if(!empty($deepBgopt)){
					
					$deepStyleAttr = '';
					if(!empty($cssrule)){
						$deepStyleAttr = 'style="'.esc_attr($cssrule).'"';
					}
					$output .= '<div id="'.esc_attr($block_id).'" class="tpgb-deep-layer '.esc_attr($block_id).' '.esc_attr($classname).'" '.$dataAttr.' '.$deepStyleAttr.'>';

						if($deepBgopt == 'bg_video'){
							$videoImgUrl = (isset($videoImg['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($videoImg) : (!empty($videoImg['url']) ? $videoImg['url'] : '');

							$vposterCss = '';
							if(!empty($videoImgUrl)){
								$vposterCss = 'style="background-image: url('.esc_url($videoImgUrl).');"';
							}

							$iframeTitle = (!empty($attributes['iframeTitle'])) ? esc_attr($attributes['iframeTitle']) : esc_attr__('My Video','tpgbp');
							$output .= '<div class="video-poster-img video-tpgb-iframe-'.esc_attr($block_id).' '.(!empty($videoImg) && !empty($videoImg['url']) ? 'tp-loading' : '').'" '.( !empty($vposterCss) ? $vposterCss : '' ).'>';
								$output .= '<div class="tpgb-video-wrap">';
									if($videosour == 'youtube'){
										$output .= '<iframe class="tpgb-iframe" id="tpgb-iframe-'.esc_attr($block_id).'" width="100%" height="100%" '.esc_attr($videoattr).' src="https://www.youtube.com/embed/'.esc_attr($youtubeId).'?wmode=opaque&amp;enablejsapi=1&amp;showinfo=0&amp;controls=0&amp;rel=0'.esc_attr($videoUrl).'" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" title="'.$iframeTitle.'"></iframe>';
									}
									if($videosour == 'vimeo'){
										$output .= '<iframe class="tpgb-iframe" id="tpgb-iframe-'.esc_attr($block_id).'" width="100%" height="100%" '.esc_attr($videoattr).' src="//player.vimeo.com/video/'.esc_attr($vimeoId).'?api=1&amp;autoplay=1;portrait=0&amp;rel=0&amp;controls=0'.esc_attr($videoUrl).'" frameborder="0" allowfullscreen title="'.$iframeTitle.'"></iframe>';
									}
									if($videosour == 'self-hosted'){
										$output .= '<video class="self-hosted-video" id="'.esc_attr($block_id).'"  data-autoplay="true" preload="auto" width="100%" height="100%" autoplay="true" playsinline '.esc_attr($videoattr).' data-setup="{}" '.$videoUrl.'></video>';
									}
								$output .= '</div>';
							$output .= '</div>';
						
						}
						
						if($deepBgopt == 'bg_gallery'){
							
							$img_list = [];
							$gallery_opt = [];
							if(!empty($rowImgs)){
								foreach($rowImgs as $img) {
									if(!empty($img)) {
										$img_list[] = array("src" => esc_url($img['url']) );
									}
								}		
							}

							$gallery_opt['transition'] = (!empty($attributes['transieffect'])) ? $attributes['transieffect'] : '';
							$gallery_opt['transduration'] = (!empty($attributes['transdur'])) ? (int) $attributes['transdur'] : 2000;
							$gallery_opt['duration'] = (!empty($attributes['slidetime'])) ? (int) $attributes['slidetime'] : 3000;
							$gallery_opt['animation'] = (!empty($attributes['animation'])) ?  $attributes['animation'] : 'random';
							$gallery_opt['textureoly'] = (!empty($attributes['textureoly']) && $attributes['textureoly'] != 'none' ) ? TPGB_URL.'assets/images/overlays/'.$attributes['textureoly'].'.png'  : '';
							
							$output .= '<div class="row-bg-slide" data-imgdata= \'' .wp_json_encode($img_list). '\' data-galleryopt=\'' .wp_json_encode($gallery_opt). '\'>';
							$output .= '</div>';
						}
						$output .= $loop;
					$output .= '</div>';
				}
				
				if(!empty($midOption)){
				
					$output .= '<div id="'.($midOption == 'canvas' ? esc_attr($midclass) : '').'" class="tpgb-middle-layer '.esc_attr($midclass).esc_attr($midclass1).'" '.$dataAttr2.'>';
						$output .= $midlayer;
					$output .= '</div>';
					
				}
				
				if(!empty($topOption)){
					$output .= '<div class="tpgb-top-layer"></div>';
				}
				
			$output .= "</div>";
			
		}
		/* Row Background Start  */

		$output .= $shapeContent;
		
		if($contentWidth=='wide'){
			$output .= '<div class="tpgb-cont-in">';
		}

			$output .= $content;

		if($contentWidth=='wide'){
			$output .= '</div>';
		}
		
	$output .= "</".Tp_Blocks_Helper::validate_html_tag($tagName).">";
	if(!empty($flexChildCss)){
		$output .= '<style>'.$flexChildCss.'</style>';
	}
	if ( class_exists( 'Tpgb_Blocks_Global_Options' ) ) {
		$global_block = Tpgb_Blocks_Global_Options::get_instance();
		if ( !empty($global_block) && is_callable( array( $global_block, 'block_row_conditional_render' ) ) ) {
			$output = Tpgb_Blocks_Global_Options::block_row_conditional_render($attributes, $output);
		}
	}

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);

    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_container_row() {
	
	$displayRules = [];
	if ( class_exists( 'Tpgb_Display_Conditions_Rules' ) ) {
		$display_Conditions = Tpgb_Display_Conditions_Rules::get_instance();
		if ( !empty($display_Conditions) && is_callable( array( $display_Conditions, 'tpgb_display_option' ) ) ) {
			$displayRules = Tpgb_Display_Conditions_Rules::tpgb_display_option();
		}
	}
	$globalPlusExtrasOption = Tpgb_Blocks_Global_Options::load_plusextras_options();
	$globalEqualHeightOptions = Tpgbp_Plus_Extras_Opt::load_plusEqualHeight_options();
	$attributesOptions = [
			'block_id' => [
                'type' => 'string',
				'default' => '',
			],
			'anchor' => array(
				'type' => 'string',
			),
			'className' => [
				'type' => 'string',
				'default' => '',
			],
			'columns' => [
                'type' => 'number',
				'default' => '',
			],
			'contentWidth' => [
				'type' => 'string',
				'default' => 'wide',
			],
			'contwidFull' => [
				'type' => 'string',
				'default' => '',
			],
			'align' => [
				'type' => 'string',
				'default' => 'wide',
			],
			'containerWide' => [
				'type' => 'object',
				'default' => [ 
					'md' => '',
					"unit" => '%',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in{ --content-width : {{containerWide}};} ',
					],
                    (object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}}.tpgb-nxtcont-type.tpgb-container-wide.alignwide { max-width : {{containerWide}} !important; } ',
					],
				],
			],
			'containerFull' => [
				'type' => 'object',
				'default' => [ 
					'md' => 100,
					"unit" => 'vw',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_CLIENT_ID}}:not(.tpgb-container-row-editor){ max-width : {{containerFull}}  !important;}',
						'backend' => true,
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => 'body {{PLUS_WRAP}}.alignfull.tpgb-container-full.tpgb-container-row{ max-width : {{containerFull}} !important;}',
						'backend' => false,
					],
				],
			],
			'colDir' => [
				'type' => 'string',
				'default' => '',
			],
			'sectionWidth' => [
				'type' => 'string',
				'default' => 'boxed',	
			],
			'height' => [
				'type' => 'string',
				'default' => '',	
			],
			'minHeight' => [
				'type' => 'object',
				'default' => [ 
					'md' => 300,
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'height', 'relation' => '==', 'value' => 'min-height'],(object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in { min-height: {{minHeight}};}',
					],
					(object) [
						'condition' => [ (object) ['key' => 'height', 'relation' => '==', 'value' => 'min-height'],(object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}{ min-height: {{minHeight}};}',
					],
					(object) [
						'condition' => [ (object) ['key' => 'height', 'relation' => '==', 'value' => 'min-height'] , (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => ' {{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ min-height: {{minHeight}};}',
						'backend' => true,
					],
					(object) [
						'condition' => [ (object) ['key' => 'height', 'relation' => '==', 'value' => 'min-height'] , (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}} > .block-editor-block-list__layout { min-height: {{minHeight}}; }',
						'backend' => false,
					],
				],
			],
			'gutterSpace' => [
				'type' => 'object',
				'default' => [ 
					'md' => 15,	
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout > [data-type="tpgb/tp-container-inner"]> .components-resizable-box__container > .tpgb-container-col-editor{ padding: {{gutterSpace}}; }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .tpgb-container-col,{{PLUS_WRAP}} > .tpgb-cont-in > .tpgb-container-col .inner-wrapper-sticky{ padding: {{gutterSpace}}; }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}} > .block-editor-block-list__layout > [data-type="tpgb/tp-container-inner"]> .components-resizable-box__container > .tpgb-container-col-editor{ padding: {{gutterSpace}}; }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-container-col,{{PLUS_WRAP}} > .tpgb-container-col .inner-wrapper-sticky{ padding: {{gutterSpace}}; }',
					],
				],
			],
			'tagName' => [
                'type' => 'string',
				'default' => 'div',
			],
			'overflow' => [
                'type' => 'string',
				'default' => '',
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}{ overflow: {{overflow}}; }',
					],
				],
			],
			'liveCopy' => [
				'type' => 'boolean',
				'default' => false,
			],
			'currentID' => [
				'type' => 'number',
				'default' => '',
			],
			'customClass' => [
				'type' => 'string',
				'default' => '',	
			],
			'customId' => [
				'type' => 'string',
				'default' => '',	
			],
			'customCss' => [
				'type' => 'string',
				'default' => '',
				'style' => [
					(object) [
						'selector' => '',
					],
				],
			],
			
			'shapeTop' => [
                'type' => 'string',
				'default' => '',
				'scopy' => true,
			],
			'shapeTColor' => [
                'type' => 'string',
				'default' => '',
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}} .section-top-divider .shape-fill{ fill: {{shapeTColor}}; }',
					],
				],
				'scopy' => true,
			],
			'shapeTWidth' => [
				'type' => 'object',
				'default' => [ 
					'md' => '',
					"unit" => '',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}} .section-top-divider svg{ width: calc( {{shapeTWidth}}% + 1.2px ); }',
					],
				],
				'scopy' => true,
			],
			'shapeTHeight' => [
				'type' => 'object',
				'default' => [ 
					'md' => '',
					"unit" => '',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}} .section-top-divider svg{ height: {{shapeTHeight}}px; }',
					],
				],
				'scopy' => true,
			],
			'shapeTFlip' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'shapeTInvert' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'shapeTFront' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			
			'shapeBottom' => [
                'type' => 'string',
				'default' => '',
				'scopy' => true,
			],
			'shapeBColor' => [
                'type' => 'string',
				'default' => '',
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}} .section-bottom-divider .shape-fill{ fill: {{shapeBColor}}; }',
					],
				],
				'scopy' => true,
			],
			'shapeBWidth' => [
				'type' => 'object',
				'default' => [ 
					'md' => '',
					"unit" => '',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}} .section-bottom-divider svg{ width: calc( {{shapeBWidth}}% + 1.2px ); }',
					],
				],
				'scopy' => true,
			],
			'shapeBHeight' => [
				'type' => 'object',
				'default' => [ 
					'md' => '',
					"unit" => '',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}} .section-bottom-divider svg{ height: {{shapeBHeight}}px; }',
					],
				],
				'scopy' => true,
			],
			'shapeBFlip' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'shapeBInvert' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'shapeBFront' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			
			'NormalBg' => [
				'type' => 'object',
				'default' => (object) [
					'openBg'=> 0,
					'bgType' => '',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}',
					],
				],
				'scopy' => true,
			],
			'HoverBg' => [
				'type' => 'object',
				'default' => (object) [
					'openBg'=> 0,
					'bgType' => '',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}:hover',
					],
				],
				'scopy' => true,
			],
			'StickyBg' => [
				'type' => 'object',
				'default' => (object) [
					'openBg'=> 0,
					'bgType' => '',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-desk-sticky,{{PLUS_WRAP}}.tpgb-tab-sticky,{{PLUS_WRAP}}.tpgb-moblie-sticky',
					],
				],
				'scopy' => true,
			],
			'NormalBorder' => [
				'type' => 'object',
				'default' => (object) [
					'openBorder' => 0,
					'type' => '',
					'color' => '',
					'width' => (object) [
						'md' => [
							"top" => '',
							'bottom' => '',
							'left' => '',
							'right' => '',
						],
						"unit" => "",
					],
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}',
					],
				],
				'scopy' => true,
			],
			'HoverBorder' => [
				'type' => 'object',
				'default' => (object) [
					'openBorder' => 0,
					'type' => '',
					'color' => '',
					'width' => (object) [
						'md' => [
							"top" => '',
							'bottom' => '',
							'left' => '',
							'right' => '',
						],
						"unit" => "",
					],
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}:hover',
					],
				],
				'scopy' => true,
			],
			'StickyBorder' => [
				'type' => 'object',
				'default' => (object) [
					'openBorder' => 0,
					'type' => '',
					'color' => '',
					'width' => (object) [
						'md' => [
							"top" => '',
							'bottom' => '',
							'left' => '',
							'right' => '',
						],
						"unit" => "",
					],
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-desk-sticky,{{PLUS_WRAP}}.tpgb-tab-sticky,{{PLUS_WRAP}}.tpgb-moblie-sticky',
					],
				],
				'scopy' => true,
			],
			'NormalBradius' => [
				'type' => 'object',
				'default' => (object) [ 
					'md' => [
						"top" => '',
						'bottom' => '',
						'left' => '',
						'right' => '',
					],
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}},{{PLUS_WRAP}} .tpgb-row-background{ border-radius: {{NormalBradius}}; }',
					],
				],
				'scopy' => true,
			],
			'HoverBradius' => [
				'type' => 'object',
				'default' => (object) [ 
					'md' => [
						"top" => '',
						'bottom' => '',
						'left' => '',
						'right' => '',
					],
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}:hover,{{PLUS_WRAP}}:hover .tpgb-row-background{ border-radius: {{HoverBradius}}; }',
					],
				],
				'scopy' => true,
			],
			'StickyBradius' => [
				'type' => 'object',
				'default' => (object) [
					'md' => [
						"top" => '',
						'bottom' => '',
						'left' => '',
						'right' => '',
					],
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-desk-sticky,{{PLUS_WRAP}}.tpgb-tab-sticky,{{PLUS_WRAP}}.tpgb-moblie-sticky{ border-radius: {{StickyBradius}}; }',
					],
				],
				'scopy' => true,
			],
			'NormalBShadow' => [
				'type' => 'object',
				'default' => (object) [
					'openShadow' => 0,
					'inset' => 0,
					'horizontal' => 0,
					'vertical' => 4,
					'blur' => 8,
					'spread' => 0,
					'color' => "rgba(0,0,0,0.40)",
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}',
					],
				],
				'scopy' => true,
			],
			'HoverBShadow' => [
				'type' => 'object',
				'default' => (object) [
					'openShadow' => 0,
					'inset' => 0,
					'horizontal' => 0,
					'vertical' => 4,
					'blur' => 8,
					'spread' => 0,
					'color' => "rgba(0,0,0,0.40)",
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}:hover',
					],
				],
				'scopy' => true,
			],
			'StickyBShadow' => [
				'type' => 'object',
				'default' => (object) [
					'openShadow' => 0,
					'inset' => 0,
					'horizontal' => 0,
					'vertical' => 4,
					'blur' => 8,
					'spread' => 0,
					'color' => "rgba(0,0,0,0.40)",
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-desk-sticky,{{PLUS_WRAP}}.tpgb-tab-sticky,{{PLUS_WRAP}}.tpgb-moblie-sticky',
					],
				],
				'scopy' => true,
			],
			'Margin' => [
				'type' => 'object',
				'default' => (object) [ 
					'md' => [
						"top" => '',
						'bottom' => '',
						'left' => '',
						'right' => '',
					],
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'align', 'relation' => '!=', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}{margin: {{Margin}} !important; }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'align', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}.alignfull{margin: {{Margin}}; }',
					],
				],
				'scopy' => true,
			],
			'Padding' => [
				'type' => 'object',
				'default' => (object) [ 
					'md' => [
						"top" => '',
						'bottom' => '',
						'left' => '',
						'right' => '',
					],
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}}{ padding-left: {{LEFT}}{{Padding}} } {{PLUS_WRAP}}{ padding-right: {{RIGHT}}{{Padding}} } {{PLUS_WRAP}} > .tpgb-cont-in{ padding-top: {{TOP}}{{Padding}} } {{PLUS_WRAP}} > .tpgb-cont-in{ padding-bottom: {{BOTTOM}}{{Padding}} }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}{ padding : {{Padding}} }',
					],
				],
				'scopy' => true,
			],
			'ZIndex' => [
				'type' => 'string',
				'default' => '',
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}}{z-index: {{ZIndex}};}',
					],
				],
				'scopy' => true,
			],
			
			'HideDesktop' => [
				'type' => 'boolean',
				'default' => false,
				'style' => [
					(object) [
						'selector' => '@media (min-width: 1201px){ .edit-post-visual-editor {{PLUS_WRAP}},.editor-styles-wrapper {{PLUS_WRAP}}{display: block;opacity: .5;} }',
						'backend' => true
					],
					(object) [
						'selector' => '@media (min-width: 1201px){ {{PLUS_WRAP}}{ display:none } }',
					],
				],
				'scopy' => true,
			],
			'HideTablet' => [
				'type' => 'boolean',
				'default' => false,
				'style' => [
					(object) [
						'selector' => '@media (min-width: 768px) and (max-width: 1200px){ .edit-post-visual-editor {{PLUS_WRAP}},.editor-styles-wrapper {{PLUS_WRAP}}{display: block;opacity: .5;} }',
						'backend' => true
					],
					(object) [
						'selector' => '@media (min-width: 768px) and (max-width: 1200px){ {{PLUS_WRAP}}{ display:none } }',
					],
				],
				'scopy' => true,
			],
			'HideMobile' => [
				'type' => 'boolean',
				'default' => false,
				'style' => [
					(object) [
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px){ .edit-post-visual-editor {{PLUS_WRAP}},.editor-styles-wrapper {{PLUS_WRAP}}{display: block !important;opacity: .5;} }',
						'backend' => true
					],
					(object) [
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px){ {{PLUS_WRAP}}{ display:none !important; } }',
					],
				],
				'scopy' => true,
			],
			'deepBgopt' => [
				'type' => 'string',
				'default' => '',
				'scopy' => true,
			],
			'DeepBgcolor' => [
				'type' => 'string',
				'default' => '',
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'deepBgopt', 'relation' => '==', 'value' => 'bg_normal_color']],
						'selector' => '{{PLUS_WRAP}} .tpgb-deep-layer { background : {{DeepBgcolor}}; }',
					],
				],
				'scopy' => true,
			],
			'DeepGrecolor' => [
				'type' => 'string',
				'default' => '',
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'deepBgopt', 'relation' => '==', 'value' => 'bg_gradientcolor']],
						'selector' => '{{PLUS_WRAP}} .tpgb-deep-layer{background: {{DeepGrecolor}};}',
					],
				],
				'scopy' => true,
			],
			'colorList' => [
				'type' => 'array',
				'repeaterField' => [
					(object) [
						'aniColor' => [
							'type' => 'string',
							'default' => ''
						],
						'aniBgtype' =>[
							'type' => 'object',
							'default' => (object) [
								'openBg'=> 0,
							],
							'style' => [
								(object) [
									'selector' => '.tpgb-deep-layer .tpgb-section-bg-scrolling{{TP_REPEAT_ID}}',
								],
							],
						],
						'scrollImg' => [
							'type' => 'object',
							'default' => [
								'url' => '',
							],
						],
					]
				],
				'default' => [
					[
						"_key" => '0',
						"aniColor" => '#8072fc',
						"aniBgtype" => '',
					],
					[
						"_key" => '1',
						"aniColor" => '#6fc784',
						"aniBgtype" => '',
					],
					[
						"_key" => '2',
						"aniColor" => '#ff5a6e',
						"aniBgtype" => '',
					],
				],
			],
			'animdur' => [
				'type' => 'string',
				'default' => 3,
				'scopy' => true,
			],
			'animDelay' => [
				'type' => 'string',
				'default' => '',
				'scopy' => true,
			],
			'crativeImg' => [
				'type' => 'object',
				'default' => [
					'url' => '',
					'Id' => '',
				],
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'deepBgopt', 'relation' => '==', 'value' => 'bg_image']],
						'selector' => '{{PLUS_WRAP}} .tpgb-row-background .tpgb-deep-layer{ background-image : {{crativeImg}}; }',
					],
				],
				'scopy' => true,
			],
			'deepimgPosition' => [
				'type' => 'object',
				'default' => [ 'md' => '','sm' => '','xs' => '' ],
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'deepBgopt', 'relation' => '==', 'value' => 'bg_image']],
						'selector' => '{{PLUS_WRAP}} .tpgb-row-background .tpgb-deep-layer { background-position : {{deepimgPosition}}; }',
					],
				],
				'scopy' => true,
			],
			'deepimgAtta' => [
				'type' => 'object',
				'default' => [ 'md' => '','sm' => '','xs' => '' ],
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'deepBgopt', 'relation' => '==', 'value' => 'bg_image']],
						'selector' => '{{PLUS_WRAP}} .tpgb-row-background .tpgb-deep-layer { background-attachment : {{deepimgAtta}}; }',
					],
				],
				'scopy' => true,
			],
			'deepimgRepeat' => [
				'type' => 'object',
				'default' => [ 'md' => '','sm' => '','xs' => '' ],
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'deepBgopt', 'relation' => '==', 'value' => 'bg_image']],
						'selector' => '{{PLUS_WRAP}} .tpgb-row-background .tpgb-deep-layer { background-repeat : {{deepimgRepeat}}; }',
					],
				],
				'scopy' => true,
			],
			'deepimgSize' => [
				'type' => 'object',
				'default' => [ 'md' => '','sm' => '','xs' => '' ],
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'deepBgopt', 'relation' => '==', 'value' => 'bg_image']],
						'selector' => '{{PLUS_WRAP}} .tpgb-row-background .tpgb-deep-layer { background-size : {{deepimgSize}}; }',
					],
				],
				'scopy' => true,
			],
			'craBgeffect' => [
				'type' => 'string',
				'default' => 'columns_simple_image',
				'scopy' => true,
			],
			'imgeffect' => [
				'type' => 'string',
				'default' => '',
				'scopy' => true,
			],
			'intensity' => [
				'type' => 'string',
				'default' => '30',
				'scopy' => true,
			],
			'perspective' => [
				'type' => 'string',
				'default' => '1000',
				'scopy' => true,
			],
			'Scale' => [
				'type' => 'string',
				'default' => '1',
				'scopy' => true,
			],
			'inverted' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'scrollPara' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'kburnseffect' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'Kbeffctdir' => [
				'type' => 'string',
				'default' => 'normal',
				'scopy' => true,
			],
			'effctDure' => [
				'type' => 'string',
				'default' => '14',
				'scopy' => true,
			],
			'respoImg' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'tabImage' => [
				'type' => 'object',
				'default' => (object) [
					'openBg'=> 0,
				],
				'style' => [
					(object) [
						'selector' => '@media (max-width:1024px){ {{PLUS_WRAP}} .tpgb-deep-layer }',
					],
				],
				'scopy' => true,
			],
			'mobileImg' => [
				'type' => 'object',
				'default' => (object) [
					'openBg'=> 0,
				],
				'style' => [
					(object) [
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width:767px){ {{PLUS_WRAP}} .tpgb-deep-layer }',
					],
				],
				'scopy' => true,
			],
			'movedir' => [
				'type' => 'string',
				'default' => 'right',
				'scopy' => true,
			],
			'trasispeed' => [
				'type' => 'string',
				'default' => '30',
				'scopy' => true,
			],
			'videosour' => [
				'type' => 'string',
				'default' => 'youtube',
			],
			'mp4Url' => [
				'type' => 'string',
				'default' => '',
			],
			'WebMUrl' => [
				'type' => 'string',
				'default' => '',
			],
			'youtubeId' => [
				'type' => 'string',
				'default' => '',
			],
			'vimeoId' => [
				'type' => 'string',
				'default' => '',
			],
			'iframeTitle' => [
				'type' => 'string',
				'default' => '',
			],
			'videoloop' => [
				'type' => 'boolean',
				'default' => false,
			],
			'videoMute' => [
				'type' => 'boolean',
				'default' => false,
			],
			'videoImg' => [
				'type' => 'object',
				'default' => [
					'url' => '',
				],
			],
			'parallax' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'rowImgs' => [
				'type' => 'array',
				'default' => [],
			],
			'transieffect' => [
				'type' => 'string',
				'default' => 'fade2',
				'scopy' => true,
			],
			'transdur' => [
				'type' => 'string',
				'default' => 3000,
				'scopy' => true,
			],
			'slidetime' => [
				'type' => 'string',
				'default' => 2000,
				'scopy' => true,
			],
			'textureoly' => [
				'type' => 'string',
				'default' => 'none',
				'scopy' => true,
			],
			'animation' => [
				'type' => 'string',
				'default' => '',
				'scopy' => true,
			],
			'bgduration' => [
				'type' => 'string',
				'default' => '15',
				'scopy' => true,
			],
			'bgRotation' => [
				'type' => 'string',
				'default' => '120',
				'scopy' => true,
			],
			'fullBggra' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'bgposition' => [
				'type' => 'string',
				'default' => 'inherit',
				'scopy' => true,
			],
			'scrollchg' => [
				'type' => 'string',
				'default' => 'no',
				'scopy' => true,
			],
			'scrolltra' => [
				'type' => 'string',
				'default' => '0.7',
				'scopy' => true,
			],
			'midOption' => [
				'type' => 'string',
				'default' => '',
				'scopy' => true,
			],
			'canvasSty' => [
				'type' => 'string',
				'default' => 'style-1',
				'scopy' => true,
			],
			'particleList' => [
				'type'=> 'array',
				'repeaterField' => [
					(object) [
						'bubbleColor' => [
							'type' => 'string',
							'default' => '',
						],
					],
				],
				'default' => [
					[
						"_key" => '0',
						"bubbleColor" => '#8072fc',
					],
					[
						"_key" => '1',
						"bubbleColor" => '#6fc784',
					],
					[
						"_key" => '2',
						"bubbleColor" => '#ff5a6e',
					],
				],

			],
			'patiColor' => [
				'type' => 'string',
				'default' => '',
				'scopy' => true,
			],
			'canShape' => [
				'type' => 'string',
				'default' => 'circle',
				'scopy' => true,
			],
			'ctmJson' => [
				'type' => 'string',
				'default' => '',
			],
			'midimgList' => [
				'type'=> 'array',
				'repeaterField' => [
					(object) [
						'parallaxImg' => [
							'type' => 'object',
							'default' => [
								'url' => TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg',
							],
						],
						'DleftAuto' => [
							'type' => 'boolean',
							'default' => false,
						],
						'leftPos' => [
							'type' => 'string',
							'default' => 20,
							'style' => [
								(object) [
									'condition' => [(object) ['key' => 'DleftAuto', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img {left:{{leftPos}}%;}',
								],
							],
						],
						'DrightAuto' => [
							'type' => 'boolean',
							'default' => false,
						],
						'righttPos' => [
							'type' => 'string',
							'style' => [
								(object) [
									'condition' => [(object) ['key' => 'DrightAuto', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img {right:{{righttPos}}%;}',
								],
							],
						],
						'DtopAuto' => [
							'type' => 'boolean',
							'default' => false,
						],
						'topPos' => [
							'type' => 'string',
							'default' => 25,
							'style' => [
								(object) [
									'condition' => [(object) ['key' => 'DtopAuto', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img {top:{{topPos}}%;}',
								],
							],
						],
						'DbottomAuto' => [
							'type' => 'boolean',
							'default' => false,
						],
						'bottomPos' => [
							'type' => 'string',
							'style' => [
								(object) [
									'condition' => [(object) ['key' => 'DbottomAuto', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img {bottom:{{bottomPos}}%;}',
								],
							],
						],
						'DimgWidth' => [
							'type' => 'string',
							'default' => '150',
							'style' => [
								(object) [
									'selector' => '{{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img { max-width : {{DimgWidth}}px;}',
								],
							],
						],
						'TleftPos' => [
							'style' => [
								(object) [
									'condition' => [
										(object) ['key' => 'TabRespo', 'relation' => '==', 'value' => true],
										['key' => 'TleftAuto', 'relation' => '==', 'value' => true]
									],
									'selector' => '@media (max-width:1024px){ {{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img {left:{{TleftPos}}%;} }',
								],
							],
						],
						'TtopPos' => [
							'style' => [
								(object) [
									'condition' => [
										(object) ['key' => 'TabRespo', 'relation' => '==', 'value' => true],
										['key' => 'TtopAuto', 'relation' => '==', 'value' => true]
									],
									'selector' => '@media (max-width:1024px){ {{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img {top:{{TtopPos}}%;} }',
								],
							],
						],
						'TrightPos' => [
							'style' => [
								(object) [
									'condition' => [
										(object) ['key' => 'TabRespo', 'relation' => '==', 'value' => true],
										['key' => 'TrightAuto', 'relation' => '==', 'value' => true]
									],
									'selector' => '@media (max-width:1024px){ {{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img {right:{{TrightPos}}%;} }',
								],
							],
						],
						'TbottomPos' => [
							'style' => [
								(object) [
									'condition' => [
										(object) ['key' => 'TabRespo', 'relation' => '==', 'value' => true],
										['key' => 'TbottomAuto', 'relation' => '==', 'value' => true]
									],
									'selector' => '@media (max-width:1024px){ {{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img {bottom:{{TbottomPos}}%;} }',
								],
							],
						],
						'TimgWidth' => [
							'type' => 'string',
							'default' => '100',
							'style' => [
								(object) [
									'condition' => [(object) ['key' => 'TabRespo', 'relation' => '==', 'value' => true]],
									'selector' => '@media (max-width:1024px){ {{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img img{ max-width :{{TimgWidth}}px;} }',
								],
							],
						],
						'MleftPos' => [
							'style' => [
								(object) [
									'condition' => [
										(object) ['key' => 'MobRespo', 'relation' => '==', 'value' => true],
										['key' => 'MleftAuto', 'relation' => '==', 'value' => true]
									],
									'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width:767px) { {{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img {left:{{MleftPos}}%;} }',
								],
							],
						],
						'MrightPos' => [
							'style' => [
								(object) [
									'condition' => [
										(object) ['key' => 'MobRespo', 'relation' => '==', 'value' => true],
										['key' => 'MrightAuto', 'relation' => '==', 'value' => true]
									],
									'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width:767px) { {{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img {right:{{MrightPos}}%;} }',
								],
							],
						],
						'MtopPos' => [
							'style' => [
								(object) [
									'condition' => [
										(object) ['key' => 'MobRespo', 'relation' => '==', 'value' => true],
										['key' => 'MtopAuto', 'relation' => '==', 'value' => true]
									],
									'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width:767px) { {{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img {top:{{MtopPos}}%;} }',
								],
							],
						],
						'MbottomPos' => [
							'style' => [
								(object) [
									'condition' => [
										(object) ['key' => 'MobRespo', 'relation' => '==', 'value' => true],
										['key' => 'MbottomAuto', 'relation' => '==', 'value' => true]
									],
									'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width:767px) { {{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img {bottom:{{MbottomPos}}%;} }',
								],
							],
						],
						'MimgWidth' => [
							'type' => 'string',
							'default' => '50',
							'style' => [
								(object) [
									'condition' => [
										(object) ['key' => 'MobRespo', 'relation' => '==', 'value' => true]],
									'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width:767px) { {{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img img{ max-width :{{MimgWidth}}px;} }',
								],
							],
						],
						'tpgbMagicScroll' => [
							'type' => 'boolean',
							'default' => false,
						],
						'MSScrollOpt' => [
							'type' => 'object',
							'default' => [
								'trigger' => (object)[ 'md' => 0.5, ],
								'duration' => (object)[ 'md' => 300, "unit" => 'px', ],
								'offset' => (object)[ 'md' => '0', "unit" => 'px', ],
								'tpgbReset' => 0
							],
						],
						'MSVertical' => [
							'type' => 'object',
							'default' => [
								'speed' => (object)[ 'md' => [0,5] ],
								'reverse' => false,
								'tpgbReset' => 0
							],
							'scopy' => true,
						],
						'MSHorizontal' => [
							'type' => 'object',
							'default' => [
								'speed' => (object)[ 'md' => [0,5] ],
								'reverse' => false,
								'tpgbReset' => 0
							],
							'scopy' => true,
						],
						'MSOpacity' => [
							'type' => 'object',
							'default' => [
								'speed' => (object)[ 'md' => [0,10] ],
								'reverse' => false,
								'tpgbReset' => 0
							],
							'scopy' => true,
						],
						'MSRotate' => [
							'type' => 'object',
							'default' => [
								'position' => 'center center',
								'rotateX' => (object)[ 'md' => [0, 4] ],
								'rotateY' => (object)[ 'md' => [0, 0] ],
								'rotateZ' => (object)[ 'md' => [0, 0] ],
								'reverse' => false,
								'tpgbReset' => 0
							],
						],
						'MSScale' => [
							'type' => 'object',
							'default' => [
								'scaleX' => (object)[ 'md' => [1, 1.5] ],
								'scaleY' => (object)[ 'md' => [1, 1] ],
								'scaleZ' => (object)[ 'md' => [1, 1] ],
								'reverse' => false,
								'tpgbReset' => 0
							],
						],
						'MSSkew' => [
							'skewX' => (object)[ 'md' => [0, 1] ],
							'skewY' => (object)[ 'md' => [0, 0] ],
							'reverse' => false,
							'tpgbReset' => 0
						],
						'borderR' => [
							'type' => 'array',
							'default' => [
								'fromBR' => (object)[ 'md' => [ "top" => '', "bottom" => '', "left" => '', "right" => '' ], "unit" => 'px',	],
								'toBR' => (object)[ 'md' => [ "top" => '', "bottom" => '', "left" => '', "right" => '' ], "unit" => 'px', ],
								'tpgbReset' => 0
							],
						],
						'MsadvOption' => [
							'type' => 'array',
							'default' => [ 
								'repeat' => (object)[ 'md' => '0', ],
								'easing' => '',
								'delay' => (object)[ 'md' => '0', ],
								'timing' => (object)[ 'md' => '1', ],
								'reverse' => true,
								'tpgbReset' => 0
							],
						],
						'MSDevelop' => [
							'type' => 'boolean',
							'default' => false
						],
						'devName' => [
							'type' => 'string',
							'default' => ''
						],
						'MSSticky' => [
							'type' => 'boolean',
							'default' => false
						],

						'Effectin' => [
							'type' => 'string',
							'default' => '',
						],
						'imgOpa' => [
							'type' => 'string',
							'default' => '',
							'style' => [
								(object) [
									'selector' => '{{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img img,{{PLUS_WRAP}} .tpgb-automove-img {{TP_REPEAT_ID}}{ opacity : {{imgOpa}} ;}',
								],
							],
						],
						'ImgZind' => [
							'type' => 'string',
							'default' => '',
							'style' => [
								(object) [
									'selector' => '{{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img img,{{PLUS_WRAP}} .tpgb-middle-layer {{TP_REPEAT_ID}}.tpgb-repet-img { z-index : {{ImgZind}} ;}',
								],
							],
						],
						'resVisib' => [
							'type' => 'boolean',
							'default' => false,	
						],
						'desHide' => [
							'type' => 'boolean',
							'default' => false,	
						],
						'tabHide' => [
							'type' => 'boolean',
							'default' => false,	
						],
						'moHide' => [
							'type' => 'boolean',
							'default' => false,	
						],
						'imgSize' => [
							'type' => 'string',
							'default' => '',
						],
						'imgDire' => [
							'type' => 'string',
							'default' => '',
						],
						'modImgeff' => [
							'type' => 'string',
							'default' => '',
						],
						'imgeffdur' => [
							'type' => 'string',
							'style' => [
								(object) [
									'condition' => [(object) ['key' => 'modImgeff', 'relation' => '!=', 'value' => '']],
									'selector' => '{{PLUS_WRAP}} .tpgb-mordernimg-effect {{TP_REPEAT_ID}} img.tpgb-imgeffect { animation-duration :{{imgeffdur}}s; -webkit-animation-duration: {{imgeffdur}}s; }',
								],
							],
						],
						'tranOrigin' => [
							'type' => 'string',
							'default' => 'center center',
						],
					],
				],
				'default' => [
					[
						"_key" => '0',
						"parallaxImg" => [ 'url' => TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg' ],
						'DleftAuto' => true,
						'leftPos' => 20,
						'topPos' => 25,
						'DtopAuto' => true,
						'DimgWidth' => 150,
						'imgDire' => 'left',
						'modImgeff' => '',
						'tranOrigin' => 'center center',
						'tpgbMagicScroll' => false,
						'MSScrollOpt' => [
							'trigger' => (object)[ 'md' => 0.5 ],
							'duration' => (object)[ 'md' => 300, "unit" => 'px', ],
							'offset' => (object)[ 'md' => '0', "unit" => 'px', ],
							'tpgbReset' => 0
						],
						'MSVertical' => [
							'speed' => (object)[ 'md' => [0,5] ],
							'reverse' => false,
							'tpgbReset' => 0
						],
						'MSHorizontal' => [
							'speed' => (object)[ 'md' => [0,5] ],
							'reverse' => false,
							'tpgbReset' => 0
						],
						'MSOpacity' => [
							'speed' => (object)[ 'md' => [0,10] ],
							'reverse' => false,
							'tpgbReset' => 0
						],
						'MSRotate' => [
							'position' => 'center center',
							'rotateX' => (object)[ 'md' => [0, 4] ],
							'rotateY' => (object)[ 'md' => [0, 0] ],
							'rotateZ' => (object)[ 'md' => [0, 0] ],
							'reverse' => false,
							'tpgbReset' => 0
						],
						'MSScale' => [
							'scaleX' => (object)[ 'md' => [1, 1.5] ],
							'scaleY' => (object)[ 'md' => [1, 1] ],
							'scaleZ' => (object)[ 'md' => [1, 1] ],
							'reverse' => false,
							'tpgbReset' => 0
						],
						'MSSkew' => [
							'skewX' => (object)[ 'md' => [0, 1] ],
							'skewY' => (object)[ 'md' => [0, 0] ],
							'reverse' => false,
							'tpgbReset' => 0
						],
						'borderR' => [
							'fromBR' => (object)[ 'md' => [ "top" => '', "bottom" => '', "left" => '', "right" => '' ], "unit" => 'px',	],
							'toBR' => (object)[ 'md' => [ "top" => '', "bottom" => '', "left" => '', "right" => '' ], "unit" => 'px', ],
							'tpgbReset' => 0
						],
						'MsadvOption' => [
							'repeat' => (object)[ 'md' => '0', ],
							'easing' => '',
							'delay' => (object)[ 'md' => '0', ],
							'timing' => (object)[ 'md' => '1', ],
							'reverse' => true,
							'tpgbReset' => 0
						],
						'MSDevelop' => false,
						'MSSticky' => false,
					],
					[
						"_key" => '1',
						"parallaxImg" => [ 'url' => TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg' ],
						'DleftAuto' => true,
						'leftPos' => 35,
						'topPos' => 25,
						'DtopAuto' => true,
						'DimgWidth' => 150,
						'imgDire' => 'right',
						'modImgeff' => '',
						'tranOrigin' => 'center center',
						'tpgbMagicScroll' => false,
						'MSScrollOpt' => [
							'trigger' => (object)[ 'md' => 0.5 ],
							'duration' => (object)[ 'md' => 300, "unit" => 'px', ],
							'offset' => (object)[ 'md' => '0', "unit" => 'px', ],
							'tpgbReset' => 0
						],
						'MSVertical' => [
							'speed' => (object)[ 'md' => [0,5] ],
							'reverse' => false,
							'tpgbReset' => 0
						],
						'MSHorizontal' => [
							'speed' => (object)[ 'md' => [0,5] ],
							'reverse' => false,
							'tpgbReset' => 0
						],
						'MSOpacity' => [
							'speed' => (object)[ 'md' => [0,10] ],
							'reverse' => false,
							'tpgbReset' => 0
						],
						'MSRotate' => [
							'position' => 'center center',
							'rotateX' => (object)[ 'md' => [0, 4] ],
							'rotateY' => (object)[ 'md' => [0, 0] ],
							'rotateZ' => (object)[ 'md' => [0, 0] ],
							'reverse' => false,
							'tpgbReset' => 0
						],
						'MSScale' => [
							'scaleX' => (object)[ 'md' => [1, 1.5] ],
							'scaleY' => (object)[ 'md' => [1, 1] ],
							'scaleZ' => (object)[ 'md' => [1, 1] ],
							'reverse' => false,
							'tpgbReset' => 0
						],
						'MSSkew' => [
							'skewX' => (object)[ 'md' => [0, 1] ],
							'skewY' => (object)[ 'md' => [0, 0] ],
							'reverse' => false,
							'tpgbReset' => 0
						],
						'borderR' => [
							'fromBR' => (object)[ 'md' => [ "top" => '', "bottom" => '', "left" => '', "right" => '' ], "unit" => 'px',	],
							'toBR' => (object)[ 'md' => [ "top" => '', "bottom" => '', "left" => '', "right" => '' ], "unit" => 'px', ],
							'tpgbReset' => 0
						],
						'MsadvOption' => [
							'repeat' => (object)[ 'md' => '0', ],
							'easing' => '',
							'delay' => (object)[ 'md' => '0', ],
							'timing' => (object)[ 'md' => '1', ],
							'reverse' => true,
							'tpgbReset' => 0
						],
						'MSDevelop' => false,
						'MSSticky' => false,
					],
				],

			],
			'topOption' => [
				'type' => 'string',
				'default' => '',
				'scopy' => true,
			],
			'topBgtype' => [
				'type' => 'object',
				'default' => (object) [
					'openBg'=> 0,
					'bgType' => 'color',
				],
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'topOption', 'relation' => '==', 'value' => 'color' ]],
						'selector' => '{{PLUS_WRAP}} .tpgb-top-layer'
					],
				],
				'scopy' => true,
			],
			'textureImg' => [
				'type' => 'object',
				'default' => [
					'url' => '',
					'id' => '',
				],
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'topOption', 'relation' => '==', 'value' => 'texture-img' ]],
						'selector' => '{{PLUS_WRAP}} .tpgb-row-background .tpgb-top-layer{ background-image: {{textureImg}}; }'
					],
				],
				'scopy' => true,
			],
			'teximgPosition' => [
				'type' => 'object',
				'default' => [ 'md' => '','sm' => '','xs' => '' ],
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'topOption', 'relation' => '==', 'value' => 'texture-img' ]],
						'selector' => '{{PLUS_WRAP}} .tpgb-row-background .tpgb-top-layer { background-position : {{teximgPosition}}; }',
					],
				],
				'scopy' => true,
			],
			'teximgAtta' => [
				'type' => 'object',
				'default' => [ 'md' => '','sm' => '','xs' => '' ],
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'topOption', 'relation' => '==', 'value' => 'texture-img' ]],
						'selector' => '{{PLUS_WRAP}} .tpgb-row-background .tpgb-top-layer { background-attachment : {{teximgAtta}}; }',
					],
				],
				'scopy' => true,
			],
			'teximgRepeat' => [
				'type' => 'object',
				'default' => [ 'md' => '','sm' => '','xs' => '' ],
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'topOption', 'relation' => '==', 'value' => 'texture-img' ]],
						'selector' => '{{PLUS_WRAP}} .tpgb-row-background .tpgb-top-layer { background-repeat : {{teximgRepeat}}; }',
					],
				],
				'scopy' => true,
			],
			'teximgSize' => [
				'type' => 'object',
				'default' => [ 'md' => '','sm' => '','xs' => '' ],
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'topOption', 'relation' => '==', 'value' => 'texture-img' ]],
						'selector' => '{{PLUS_WRAP}} .tpgb-row-background .tpgb-top-layer { background-size : {{teximgSize}}; }',
					],
				],
				'scopy' => true,
			],
			'timgOpacity' => [
				'type' => 'string',
				'default' => '',
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'topOption', 'relation' => '!=', 'value' => '' ]],
						'selector' => '{{PLUS_WRAP}} .tpgb-top-layer{opacity : {{timgOpacity}} }'
					],
				],
				'scopy' => true,
			],
			'wrapLink' => [
				'type' => 'boolean',
				'default' => false,
			],
			'rowUrl' => [
				'type'=> 'object',
				'default'=> [
					'url' => '',
					'target' => '',
					'nofollow' => ''
				],
			],
			
			// Flex Css
			'flexreverse' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'flexRespreverse' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'flexTabreverse' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'flexMobreverse' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
			'flexDirection' => [
				'type' => 'object',
				'default' => [ 'md' => '', 'sm' =>  '', 'xs' =>  'column' ],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'flexreverse', 'relation' => '==', 'value' => false]],
						'selector' => '{{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in { flex-direction: {{flexDirection}} }',
						'media' => 'md',
					],
					(object) [
						'condition' => [ (object) ['key' => 'flexreverse', 'relation' => '==', 'value' => false]],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout, {{PLUS_WRAP}} > .block-editor-block-list__layout{ flex-direction: {{flexDirection}} }',
						'media' => 'md',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'flexreverse', 'relation' => '==', 'value' => true]],
						'selector' => '{{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in { flex-direction: {{flexDirection}}-reverse }',
						'media' => 'md',
					],
					(object) [
						'condition' => [ (object) ['key' => 'flexreverse', 'relation' => '==', 'value' => true]],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout,{{PLUS_WRAP}} > .block-editor-block-list__layout{ flex-direction: {{flexDirection}}-reverse }',
						'media' => 'md',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => false ]],
						'selector' => '@media (max-width: 1024px) { {{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in { flex-direction: {{flexDirection}} } }' ,
						'media' => 'sm',
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => false ]],
						'selector' => '@media (max-width: 1024px) { {{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-direction: {{flexDirection}} } }' ,
						'media' => 'sm',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ],
							(object) ['key' => 'flexTabreverse', 'relation' => '==', 'value' => false] 
						],
						'selector' => '@media (max-width: 1024px) and (min-width:768px) { {{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in { flex-direction: {{flexDirection}} } }' ,
						'media' => 'sm',
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ],
							(object) ['key' => 'flexTabreverse', 'relation' => '==', 'value' => false] 
						],
						'selector' => '@media (max-width: 1024px) and (min-width:768px) { {{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-direction: {{flexDirection}} } }' ,
						'media' => 'sm',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ],
							(object) ['key' => 'flexTabreverse', 'relation' => '==', 'value' => true] 
						],
						'selector' => '@media (max-width: 1024px) and (min-width:768px) { {{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in { flex-direction: {{flexDirection}}-reverse  } }',
						'media' => 'sm',
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ],
							(object) ['key' => 'flexTabreverse', 'relation' => '==', 'value' => true] 
						],
						'selector' => '@media (max-width: 1024px) and (min-width:768px) { {{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-direction: {{flexDirection}}-reverse  } }',
						'media' => 'sm',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => false ]],
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px) { {{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in{ flex-direction: {{flexDirection}} } }',
						'media' => 'xs',
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => false ]],
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px) { {{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-direction: {{flexDirection}} } }',
						'media' => 'xs',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ],
							(object) ['key' => 'flexMobreverse', 'relation' => '==', 'value' => false] 
						],
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px) { {{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in{ flex-direction: {{flexDirection}} } }',
						'media' => 'xs',
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ],
							(object) ['key' => 'flexMobreverse', 'relation' => '==', 'value' => false] 
						],
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px) { {{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-direction: {{flexDirection}} } }',
						'media' => 'xs',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ], 
							(object) ['key' => 'flexMobreverse', 'relation' => '==', 'value' => true] 
						],
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px){ {{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in { flex-direction: {{flexDirection}}-reverse  } }',
						'media' => 'xs',
					],
					(object) [
						'condition' => [ (object) [ 'key' => 'flexRespreverse', 'relation' => '==', 'value' => true ], 
							(object) ['key' => 'flexMobreverse', 'relation' => '==', 'value' => true] 
						],
						'selector' => '@media (max-width: 1024px){.text-center{text-align: center;}}@media (max-width: 767px){ {{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-direction: {{flexDirection}}-reverse  } }',
						'media' => 'xs',
						'backend' => true
					],
				],
				'scopy' => true,
			],
			'flexAlign' => [
				'type' => 'object',
				'default' => [ 'md' => '', 'sm' =>  '', 'xs' =>  '' ],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ align-items : {{flexAlign}} }',
						'backend' => true
					],
					(object) [
						'selector' => '{{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in{ align-items : {{flexAlign}} }',
					],
				],
				'scopy' => true,
			],
			'flexJustify' => [
				'type' => 'object',
				'default' => [ 'md' => '', 'sm' =>  '', 'xs' =>  '' ],
				'style' => [
					(object) [
						'selector' => '{{PLUS_WRAP}} > .block-editor-block-list__layout,{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ justify-content : {{flexJustify}} }',
						'backend' => true
					],
					(object) [
						'selector' => '{{PLUS_WRAP}},{{PLUS_WRAP}} > .tpgb-cont-in{ justify-content : {{flexJustify}} }',
					],
				],
				'scopy' => true,
			],
			'flexGap' => [
				'type' => 'object',
				'default' => [ 
					'md' => '',
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ gap : {{flexGap}} }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in{ gap : {{flexGap}} }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}} > .block-editor-block-list__layout{ gap : {{flexGap}} }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}{ gap : {{flexGap}} }',
					],
				],
				'scopy' => true,
			],
			'flexwrap' => [
				'type' => 'object',
				'default' => [ 'md' => '', 'sm' =>  '', 'xs' =>  'wrap' ],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => false],  ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-wrap : {{flexwrap}} }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => false],  ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in{ flex-wrap : {{flexwrap}} }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => false], ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row-editor > .block-editor-block-list__layout{ flex-wrap : {{flexwrap}} }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => false], ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}{ flex-wrap : {{flexwrap}} }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => true], ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ flex-wrap : {{flexwrap}}-reverse }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => true], ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in{ flex-wrap : {{flexwrap}}-reverse }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => true], ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}} > .block-editor-block-list__layout{ flex-wrap : {{flexwrap}}-reverse }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'reverseWrap', 'relation' => '==', 'value' => true], ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}{ flex-wrap : {{flexwrap}}-reverse }',
					],
				],
				'scopy' => true,
			],
			'alignWrap' => [
				'type' => 'object',
				'default' => [ 'md' => 'flex-end', 'sm' =>  '', 'xs' =>  '' ],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout{ align-content : {{alignWrap}} }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in{ align-content : {{alignWrap}} }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}} > .block-editor-block-list__layout{ align-content : {{alignWrap}} }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}{ align-content : {{alignWrap}} }',
					],
				],
				'scopy' => true,
			],
			'reverseWrap' => [
				'type' => 'boolean',
				'default' => false,
			],
			// child Css
			'flexChild' => [
				'type'=> 'array',
				'repeaterField' => [
					(object) [
						'flexShrink' => [
							'type' => 'object',
							'default' => [ 
								'md' => '',
							],
							'style' => [
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > .tpgb-cont-in > *:nth-child({{TP_INDEX}}){ flex-shrink : {{flexShrink}} }',
								],
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > *:nth-child({{TP_INDEX}}){ flex-shrink : {{flexShrink}} }',
								],
							],
							'scopy' => true,
						],
						'flexGrow' => [
							'type' => 'object',
							'default' => [ 
								'md' => '',
							],
							'style' => [
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > .tpgb-cont-in > *:nth-child({{TP_INDEX}}){ flex-grow : {{flexGrow}} }',
								],
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > *:nth-child({{TP_INDEX}}){ flex-grow : {{flexGrow}} }',
								],
							],
							'scopy' => true,
						],
						'flexBasis' => [
							'type' => 'object',
							'default' => [ 
								'md' => '',
								"unit" => '%',
							],
							'style' => [
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > .tpgb-cont-in > *:nth-child({{TP_INDEX}}){ flex-basis : {{flexBasis}} }{{PLUS_WRAP}} > .tpgb-cont-in > .block-editor-block-list__layout > *:nth-child({{TP_INDEX}}) > *{width:100% !important}',
								],
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > *:nth-child({{TP_INDEX}}){ flex-basis : {{flexBasis}} }{{PLUS_WRAP}} > .block-editor-block-list__layout > *:nth-child({{TP_INDEX}}) > *{width:100% !important}',
								],
							],
							'scopy' => true,
						],
						'flexselfAlign' => [
							'type' => 'object',
							'default' => [ 'md' => 'auto', 'sm' =>  '', 'xs' =>  '' ],
							'style' => [
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > .tpgb-cont-in > *:nth-child({{TP_INDEX}}){ align-self : {{flexselfAlign}} }',
								],
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > *:nth-child({{TP_INDEX}}){ align-self : {{flexselfAlign}} }',
								],
							],
							'scopy' => true,
						],
						'flexOrder' => [
							'type' => 'object',
							'default' => [ 'md' => '', 'sm' =>  '', 'xs' =>  '' ],
							'style' => [
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > .tpgb-cont-in > *:nth-child({{TP_INDEX}}){ order : {{flexOrder}} }',
								],
								(object) [
									'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full'],['key' => 'showchild', 'relation' => '==', 'value' => true]],
									'selector' => '{{PLUS_WRAP}}:not(.tpgb-container-row-editor) > *:nth-child({{TP_INDEX}}){ order : {{flexOrder}} }',
								],
							],
							'scopy' => true,
						],
					],
				],
				'default' => [
					[ '_key'=> 'cvi9', 'flexShrink' => [ 'md' => '' ] , 'flexGrow' => [ 'md' => '' ], 'flexBasis' => [ 'md' => '' ] ,'flexselfAlign' => [ 'md' => '' ] ,'flexOrder' => [ 'md' => '' ] ],
				],
			],
			'showchild' => [
				'type' => 'boolean',
				'default' => false,
			],
			'contSticky' => [
				'type' => 'object',
				'default' => [ 'md' => false ],	
			],
			'contOverlays' => [
				'type' => 'boolean',
				'default' => false,
			],
			'constType' => [
				'type' => 'string',
				'default' => 'normal-sticky',
			],
			'contopoffset' => [
				'type' => 'object',
				'default' => [ 
					'md' => '',
					"unit" => 'px',
				],
			],
			'sanimaType' => [
				'type' => 'string',
				'default' => 'none',
			],
			'stiMargin' => [
				'type' => 'object',
				'default' => (object) [ 
					'md' => [
						"top" => '',
						'bottom' => '',
						'left' => '',
						'right' => '',
					],
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contSticky', 'relation' => '==', 'value' => true]],
						'selector' => '{{PLUS_WRAP}}.tpgb-desk-sticky,{{PLUS_WRAP}}.tpgb-tab-sticky,{{PLUS_WRAP}}.tpgb-moblie-sticky{ margin : {{stiMargin}}  }'
					],
				],
				'scopy' => true,
			],
			'stiPadding' => [
				'type' => 'object',
				'default' => (object) [ 
					'md' => [
						"top" => '',
						'bottom' => '',
						'left' => '',
						'right' => '',
					],
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contSticky', 'relation' => '==', 'value' => true]],
						'selector' => '{{PLUS_WRAP}}.tpgb-desk-sticky,{{PLUS_WRAP}}.tpgb-tab-sticky,{{PLUS_WRAP}}.tpgb-moblie-sticky{ padding : {{stiPadding}}  }'
					],
				],
				'scopy' => true,
			],
            'backFilter' => [
                'type' => 'object',
                'default' => [
                    'openFilter' => false,
                    'isbackdrop' => true,
                ],
                'style' => [
                    (object) [
                        'selector' => '{{PLUS_WRAP}}',
                    ],
                ],
                'scopy' => true,
            ],
			'scupSticky' => [
				'type' => 'boolean',
				'default' => false,
			],
			'stTransdur' => [
				'type' => 'string',
				'default' => '0.3',
				'style' => [
					(object) [
						'condition' => [(object) ['key' => 'sanimaType', 'relation' => '==', 'value' => 'slide']],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row.tpgb-sticky-slide{ transition-duration : {{stTransdur}}s; animation-duration : {{stTransdur}}s;  }',
					],
					(object) [
						'condition' => [(object) ['key' => 'sanimaType', 'relation' => '==', 'value' => 'fade']],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row.tpgb-sticky-fade{ transition-duration : {{stTransdur}}s; animation-duration : {{stTransdur}}s; }',
					],
				],		
			],
			'conPosi' => [
				'type' => 'object',
				'default' => [ 'md' => '','sm' => '','xs' => '' ],
				'style' => [
					(object) [
						'selector' => '{{PLUS_CLIENT_ID}}:not(.tpgb-container-row-editor){ position : {{conPosi}}; }',
						'backend' => true,
					],
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { position : {{conPosi}}; }',
						'backend' => false,
					],
				],
				'scopy' => true,
			],
			'conhorizoOri' => [
				'type' => 'object',
				'default' => [ 'md' => 'left', 'sm' =>  '', 'xs' =>  '' ]
			],
			'conhoriOffset' => [
				'type' => 'object',
				'default' =>[ 
					'md' => '0',
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conhorizoOri', 'relation' => '==', 'value' => 'left' ]
						],
						'selector' => '{{PLUS_CLIENT_ID}}:not(.tpgb-container-row-editor){ left : {{conhoriOffset}};right : auto; }',
						'backend' => true,
					],
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conhorizoOri', 'relation' => '==', 'value' => 'left' ],
						],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { left : {{conhoriOffset}};right : auto; }',
						'backend' => false,
					],
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conhorizoOri', 'relation' => '==', 'value' => 'right' ]
						],
						'selector' => '{{PLUS_CLIENT_ID}}:not(.tpgb-container-row-editor){ right : {{conhoriOffset}};left : auto; }',
						'backend' => true,
					],
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conhorizoOri', 'relation' => '==', 'value' => 'right' ]
						],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { right : {{conhoriOffset}};left : auto; }',
						'backend' => false,
					],
				],
			],
			'conabverticalOri' => [
				'type' => 'object',
				'default' => [ 'md' => 'top', 'sm' =>  '', 'xs' =>  '' ]
			],
			'converticalOffset' => [
				'type' => 'object',
				'default' => [ 
					'md' => '0',
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conabverticalOri', 'relation' => '==', 'value' => 'top' ]
						],
						'selector' => '{{PLUS_CLIENT_ID}}:not(.tpgb-container-row-editor){ top : {{converticalOffset}}; bottom : auto; }',
						'backend' => true,
					],
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conabverticalOri', 'relation' => '==', 'value' => 'top' ]
						],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { top : {{converticalOffset}}; bottom : auto; }',
						'backend' => false,
					],
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conabverticalOri', 'relation' => '==', 'value' => 'bottom' ]
						],
						'selector' => '{{PLUS_CLIENT_ID}}:not(.tpgb-container-row-editor){ bottom : {{converticalOffset}}; top : auto; }',
						'backend' => true,
					],
					(object) [
						'condition' => [
							(object) [ 'key' => 'conPosi', 'relation' => '==', 'value' => ['absolute' , 'fixed'] ],
							(object) [ 'key' => 'conabverticalOri', 'relation' => '==', 'value' => 'bottom' ]
						],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { bottom : {{converticalOffset}}; top : auto; }',
						'backend' => false,
					],
				],
			],
			'stayConta' => [
				'type' => 'object',
				'default' => [ 'md' => false ],	
			],

			// Grid Layouts Attributes
			'columnsRepeater' => [
				"type" => "array",
				"repeaterField" => array(
					array(
						'gridProperty' => [
							'type' => 'object',
							'default' => [
								'md' => 'custom',
							],
						],
						'gridMin' => [
							'type' => 'object',
							'default' => [
								'md' => '10',
								'unit'=>'px'
							],
						],
						'gridMax' => [
							'type' => 'object',
							'default' => [
								'md' => '1',
								'unit'=>'fr'
							],
						],
						'gridWidth' => [
							'type' => 'object',
							'default' => [
								'md' => '1',
								'unit'=>'fr'
							],

						],
					),
				),
				"default" => array(
					array(
						"_key" => "0",
						'gridProperty' => [ 
							'md' =>'custom'
						],
						'gridMin'=>[
							'md' => '10',
							'unit'=>'px'
						],
						'gridMax'=>[
							'md' => '1',
							'unit'=>'fr'
						],
						'gridWidth'=>[
							'md' => '1',
							'unit'=>'fr'
						],

					),
				),
			],
			'rowsRepeater' => [
				"type" => "array",
				"repeaterField" => array(
					array(
						'gridRowProperty' => [
							'type' => 'object',
							'default' => [
								'md' => 'custom',
							],
						],
						'gridRowMin' => [
							'type' => 'object',
							'default' => [
								'md' => '10',
								'unit'=>'px'
							],
						],
						'gridRowMax' => [
							'type' => 'object',
							'default' => [
								'md' => '1',
								'unit'=>'fr'
							],
						],
						'gridHeight' => [
							'type' => 'object',
							'default' => [
								'md' => '1',
								'unit'=>'fr'
							],

						],
					),
				),
				"default" => array(
					array(
						"_key" => "0",
						'gridRowProperty' =>  [ 
							'md' =>'custom'
						],
						'gridRowMin'=>[
							'md' => '10',
							'unit'=>'px'
						],
						'gridRowMax'=>[
							'md' => '1',
							'unit'=>'fr'
						],
						'gridHeight'=>[
							'md' => '1',
							'unit'=>'fr'
						],

					),
				),
			],
			'gridFlow' => [
				'type' => 'string',
				'default'=>'row',				
				'style' => [
					(object) [
						'selector' => '{{PLUS_CLIENT_ID}} .tpgb-grid > .block-editor-block-list__layout, {{PLUS_CLIENT_ID}} .tpgb-container-row-editor.tpgb-grid > .tpgb-cont-in > .block-editor-block-list__layout { grid-auto-flow: {{gridFlow}}; }',
						'backend' => true,
					],
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row.tpgb-grid .tpgb-cont-in, {{PLUS_WRAP}}.tpgb-container-full.tpgb-container-row.tpgb-grid { grid-auto-flow: {{gridFlow}}; }',
						'backend' => false,
					],
				],
			],
			'gridJustify' => [
                'type' => 'object',
                'default'=>[
                    'md' => 0,
                ],
                'style' => [
                    (object) [
                        'selector' => '{{PLUS_CLIENT_ID}} .tpgb-grid > .block-editor-block-list__layout, .tpgb-container-row-editor.tpgb-grid > .tpgb-cont-in > .block-editor-block-list__layout {justify-content: {{gridJustify}}; }',
                        'backend' => true,
                    ],
                    (object) [
                        'selector' => '{{PLUS_WRAP}}.tpgb-container-row.tpgb-grid .tpgb-cont-in,
                        {{PLUS_WRAP}}.tpgb-container-full.tpgb-container-row.tpgb-grid {justify-content: {{gridJustify}}; }',
                        'backend' => false,
                    ],
                ],
            ],
			'gridAlign' => [
				'type' => 'object',
				'default'=>[ 
					'md' => 0,
				],				
				'style' => [
					(object) [
						'selector' => '{{PLUS_CLIENT_ID}} .tpgb-grid > .block-editor-block-list__layout, {{PLUS_CLIENT_ID}} .tpgb-container-row-editor.tpgb-grid > .tpgb-cont-in > .block-editor-block-list__layout {align-items: {{gridAlign}}; }',
						'backend' => true,
					],
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row.tpgb-grid .tpgb-cont-in,
						{{PLUS_WRAP}}.tpgb-container-full.tpgb-container-row.tpgb-grid {align-items: {{gridAlign}}; }',
						'backend' => false,
					],
				],
			],
			'gridAlignCon' => [
                'type' => 'object',
                'default' => [ 'md' => '', 'sm' =>  '', 'xs' =>  '' ],
                'style' => [
                    (object) [
                        'selector' => '{{PLUS_CLIENT_ID}} .tpgb-grid > .block-editor-block-list__layout, .tpgb-container-row-editor.tpgb-grid > .tpgb-cont-in > .block-editor-block-list__layout {align-content : {{gridAlignCon}} }',
                        'backend' => true,
                    ],
                    (object) [
                        'selector' => '{{PLUS_WRAP}}.tpgb-container-row.tpgb-grid .tpgb-cont-in,
                        {{PLUS_WRAP}}.tpgb-container-full.tpgb-container-row.tpgb-grid {align-content : {{gridAlignCon}} }',
                        'backend' => false,
                    ],
                ],
                'scopy' => true,
            ],
            'gridJustItems' => [
                'type' => 'object',
                'default' => [ 'md' => '', 'sm' =>  '', 'xs' =>  '' ],
                'style' => [
                    (object) [
                        'selector' => '{{PLUS_CLIENT_ID}} .tpgb-grid > .block-editor-block-list__layout, .tpgb-container-row-editor.tpgb-grid > .tpgb-cont-in > .block-editor-block-list__layout {justify-items : {{gridJustItems}}; }',
                        'backend' => true,
                    ],
                    (object) [
                        'selector' => '{{PLUS_WRAP}}.tpgb-container-row.tpgb-grid .tpgb-cont-in,
                        {{PLUS_WRAP}}.tpgb-container-full.tpgb-container-row.tpgb-grid { justify-items : {{gridJustItems}}; }',
                        'backend' => false,
                    ],
                ],
                'scopy' => true,
            ],
			'selectedLayout' => [
				'type' => 'string',
				'default' => '',
			],
			'gridStyle' => [
				'type' => 'string',
				'default' => '',
			],

			'isrootContainer' => [
				'type' => 'boolean',
				'default' => false,
			],
			'iscontGrid' => [
				'type' => 'boolean',
				'default' => false,
			],
			'colWidth' => [
				'type' => 'object',
				'default' => [ 
					'md' => '',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_CLIENT_ID}} {grid-column: span {{colWidth}}; }',
						'backend' => true,
					],
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row {grid-column: span {{colWidth}}; }',
						'backend' => false,
					],
				],
				'scopy' => true,
			],
			'rowHeight' => [
				'type' => 'object',
				'default' => [ 
					'md' => '',
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_CLIENT_ID}} {grid-row: span {{rowHeight}}; }',
						'backend' => true,
					],
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row {grid-row: span {{rowHeight}};  }',
						'backend' => false,
					],
				],
				'scopy' => true,
			],
			'colStart' => [
				'type' => 'object',
				'default' => [
					'md' => ''
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_CLIENT_ID}} {grid-column-start: {{colStart}}; }',
						'backend' => true,
					],
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { grid-column-start: {{colStart}}; }',
						'backend' => false,
					],
				],
				'scopy' => true,

			],
			'colEnd' => [
				'type' => 'object',
				'default' => [
					'md' => ''
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_CLIENT_ID}} {grid-column-end: {{colEnd}}; }',
						'backend' => true,
					],
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { grid-column-end: {{colEnd}}; }',
						'backend' => false,
					],
				],
				'scopy' => true,

			],
			'rowStart' => [
				'type' => 'object',
				'default' => [
					'md' => ''
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_CLIENT_ID}} {grid-row-start: {{rowStart}}; }',
						'backend' => true,
					],
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { grid-row-start: {{rowStart}}; }',
						'backend' => false,
					],
				],
				'scopy' => true,

			],
			'rowEnd' => [
				'type' => 'object',
				'default' => [
					'md' => ''
				],
				'style' => [
					(object) [
						'selector' => '{{PLUS_CLIENT_ID}} {grid-row-end: {{rowEnd}}; }',
						'backend' => true,
					],
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { grid-row-end: {{rowEnd}}; }',
						'backend' => false,
					],
				],
				'scopy' => true,

			],
			'gridFlexAlign' => [
				'type' => 'object',
				'default' => [ 'md' => '', 'sm' =>  '', 'xs' =>  '' ],
				'style' => [
					(object) [
						'selector' => '{{PLUS_CLIENT_ID}} { align-items : {{gridFlexAlign}} }',
						'backend' => true
					],
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { align-items : {{gridFlexAlign}} }',
						'backend' => false,
					],
				],
				'scopy' => true,
			],
			'gridFlexJustify' => [
				'type' => 'object',
				'default' => [ 'md' => '', 'sm' =>  '', 'xs' =>  '' ],
				'style' => [
					(object) [
						'selector' => '{{PLUS_CLIENT_ID}} { justify-content : {{gridFlexJustify}} }',
						'backend' => true
					],
					(object) [
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row { justify-content : {{gridFlexJustify}} }',
						'backend' => false,
					],
				],
				'scopy' => true,
			],
			'colGap' => [
				'type' => 'object',
				'default' => [ 
					'md' => '',	
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .tpgb-contain-block-editor.block-editor-block-list__layout {grid-column-gap: {{colGap}}; }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-contain-block-editor.block-editor-block-list__layout {grid-column-gap: {{colGap}}; }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row.tpgb-container-wide.tpgb-grid .tpgb-cont-in {grid-column-gap: {{colGap}}; }',
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row.tpgb-container-full.tpgb-grid {grid-column-gap: {{colGap}}; }',
					],
				],
				'scopy' => true,
			],
			'rowGap' => [
				'type' => 'object',
				'default' => [ 
					'md' => '',	
					"unit" => 'px',
				],
				'style' => [
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-cont-in > .tpgb-contain-block-editor.block-editor-block-list__layout {grid-row-gap: {{rowGap}}; }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}} > .tpgb-contain-block-editor.block-editor-block-list__layout {grid-row-gap: {{rowGap}}; }',
						'backend' => true
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'wide']],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row.tpgb-container-wide.tpgb-grid .tpgb-cont-in {grid-row-gap: {{rowGap}};}',
					],
					(object) [
						'condition' => [ (object) ['key' => 'contentWidth', 'relation' => '==', 'value' => 'full']],
						'selector' => '{{PLUS_WRAP}}.tpgb-container-row.tpgb-container-full.tpgb-grid {grid-row-gap: {{rowGap}}; }',
					],
				],
				'scopy' => true,
			],
            'noofGrid' => [
                'type' => 'string',
                'default' => '',
			],
			'saveGlobalStyle' => [
				'type' => 'string',
				'default' => '',
			],
			'saveGlobalStyleClass' => [
				'type' => 'string',
				'default' => '',
			],
            'nxtcontType' => [
                'type' => 'boolean',
                'default' => false,
            ],
 		];
		
	$attributesOptions = array_merge( $attributesOptions, $displayRules,$globalEqualHeightOptions,$globalPlusExtrasOption );
	
	register_block_type( 'tpgb/tp-container', array(
		'attributes' => $attributesOptions,
		'editor_script' => 'tpgb-block-editor-js',
		'editor_style'  => 'tpgb-block-editor-css',
        'render_callback' => 'tpgb_tp_container_render_callback'
    ) );
}
add_action( 'init', 'tpgb_tp_container_row' );