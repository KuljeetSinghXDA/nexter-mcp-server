<?php
/**
 * Block : TP Preloader
 * @since 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_preloader_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
    $preLoader = (!empty($attributes['preLoader'])) ? $attributes['preLoader'] : [];
    $pDefineColor1 = (!empty($attributes['pDefineColor1'])) ? $attributes['pDefineColor1'] : '';
    $pDefineColor2 = (!empty($attributes['pDefineColor2'])) ? $attributes['pDefineColor2'] : '';
    $excludeClass = (!empty($attributes['excludeClass'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['excludeClass']) : '';
    $aniLoadFirst = (!empty($attributes['aniLoadFirst'])) ? $attributes['aniLoadFirst'] : false;
    $alfExclude = (!empty($attributes['alfExclude'])) ? $attributes['alfExclude'] : 'alfheader';
    $customClass = (!empty($attributes['customClass'])) ? $attributes['customClass'] : '';
    $customPos = (!empty($attributes['customPos'])) ? $attributes['customPos'] : 'top';
    $excludeZIndex = (!empty($attributes['excludeZIndex'])) ? $attributes['excludeZIndex'] : '';
    $outTransition = (!empty($attributes['outTransition'])) ? $attributes['outTransition'] : false;
    $loadTime = (!empty($attributes['loadTime'])) ? $attributes['loadTime'] : 'loadtimedefault';
    $loadMinTime = (!empty($attributes['loadMinTime'])) ? $attributes['loadMinTime'] : '';
    $loadMaxTime = (!empty($attributes['loadMaxTime'])) ? $attributes['loadMaxTime'] : '';
	
    $pageLoadTrans = (!empty($attributes['pageLoadTrans'])) ? $attributes['pageLoadTrans'] : 'pageloadfadein';
	$pageLoadSlideDir = (!empty($attributes['pageLoadSlideDir'])) ? $attributes['pageLoadSlideDir'] : 'left';
    $pageLoad4InDir = (!empty($attributes['pageLoad4InDir'])) ? $attributes['pageLoad4InDir'] : 'left';
	
	$postLoadTrans = (!empty($attributes['postLoadTrans'])) ? $attributes['postLoadTrans'] : 'pageloadfadein';
	$postLoadSlideDir = (!empty($attributes['postLoadSlideDir'])) ? $attributes['postLoadSlideDir'] : 'left';
    $postLoad4InDir = (!empty($attributes['postLoad4InDir'])) ? $attributes['postLoad4InDir'] : 'left';
	
    $tpgb4color1 = (!empty($attributes['tpgb4color1'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['tpgb4color1']) : '#ff5a6e';
    $tpgb4color2 = (!empty($attributes['tpgb4color2'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['tpgb4color2']) : '#8072fc';
    $tpgb4color3 = (!empty($attributes['tpgb4color3'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['tpgb4color3']) : '#6f14f1';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$dyCss='';
	$slideinclass=$slideoutclass=$slideinclasseclass=$loadtimedt=$loadminmax='';
	$data_attr = [];
	$data_attr = [
		"post_load_exclude_class"	=> $excludeClass,
		"post_load_opt"	=> (!empty($outTransition)) ? 'enablepostload' : 'disablepostload',
		"loadtime"	=> $loadTime,
		"loadmintime"	=> $loadMinTime,
		"loadmaxtime"	=> $loadMaxTime,
	];
	if($pageLoadTrans=='pageloadslidein' && !empty($pageLoadSlideDir)){
		$slideinclass .= 'tpgb-duo-move-'.$pageLoadSlideDir;
	}
	if($postLoadTrans=='postloadslideout' && !empty($postLoadTrans)){
		$slideoutclass .= 'tpgb-out-duo-move-'.$postLoadSlideDir;
	}
	if($pageLoadTrans=='pageloadtriplesw' || $pageLoadTrans=='pageloadsimple' || $pageLoadTrans=='pageloadduomove' || $postLoadTrans=='postloadtriplesw' || $postLoadTrans=='postloadsimple' || $postLoadTrans=='postloadsduomove'){
		$slideinclasseclass .= 'tpgb-preload-transion4';
	}	
	if($pageLoadTrans=='pageloadtriplesw' && !empty($pageLoad4InDir)){
		$slideinclass .= 'tpgb-tripleswoosh tpgb-4-preload-'.$pageLoad4InDir;
	}
	if($postLoadTrans=='postloadtriplesw' && !empty($postLoad4InDir)){
		$slideoutclass .= 'tpgb-tripleswoosh tpgb-4-postload-'.$postLoad4InDir;
	}
	if($pageLoadTrans=='pageloadsimple' && !empty($pageLoad4InDir)){
		$slideinclass .= 'tpgb-simple tpgb-4-preload-'.$pageLoad4InDir;
	}
	if($postLoadTrans=='postloadsimple' && !empty($postLoad4InDir)){
		$slideoutclass .= 'tpgb-simple tpgb-4-postload-'.$postLoad4InDir;
	}
	if($pageLoadTrans=='pageloadduomove' && !empty($pageLoad4InDir)){
		$slideinclass .= 'tpgb-duomove2 tpgb-4-preload-'.$pageLoad4InDir;
	}
	if($postLoadTrans=='postloadduomove' && !empty($postLoad4InDir)){
		$slideoutclass .= 'tpgb-duomove2 tpgb-4-postload-'.$postLoad4InDir;
	}
	
	
	$data_attr = htmlspecialchars(wp_json_encode($data_attr), ENT_QUOTES, 'UTF-8');
	
	if(!empty($aniLoadFirst) && $alfExclude=='alfcustom' && !empty($customClass) && !empty($excludeZIndex) && !empty($customPos)){
		$topbottom = '';
		if($customPos == 'top'){
			$topbottom = 'top:0';
		}else if($customPos == 'bottom'){
			$topbottom = 'bottom:0';
		}
		$dyCss .='body:not(.tpgb-loaded):not(.tpgb-out-loaded) '.esc_attr($customClass).'{z-index : '.esc_attr($excludeZIndex).';width:100%;position:fixed;'.esc_attr($topbottom).'}';				
	}else if(!empty($aniLoadFirst)&& $alfExclude=='alfheader' && !empty($excludeZIndex)){
		$dyCss .='body:not(.tpgb-loaded):not(.tpgb-out-loaded) header{z-index : '.esc_attr($excludeZIndex).' !important;width:100%;position:fixed;}';
	}
	
	$output=$progressColor='';
    $output .='<div class="tpgb-preloader tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).' '.esc_attr($slideinclass).' '.esc_attr($slideoutclass).' '.esc_attr($slideinclasseclass).'" data-plec=\'' .$data_attr. '\' id="tpgb-preloader">';
	
		if(!empty($preLoader)){
			foreach ( $preLoader as $index => $item1 ) :
				$reProg = '';
				if($item1["preLoadType"]=='progress') {
					if($item1['preLoadType']=='progress' && empty($item1['gradientToggle']) && ($item1['progressType']=='layout-2' || $item1['progressType']=='layout-5')){
						$pb1 = ($item1['pBarColor1'] ? $item1['pBarColor1'] : '#6f14f1');
						$pb2 = ($item1['pBarColor2'] ? $item1['pBarColor2'] : '#7013f2d1');
						$reProg = ".tpgb-block-".esc_attr($block_id)." .percentagelayout.tp-repeater-item-".esc_attr($item1['_key']).", .tpgb-block-".esc_attr($block_id)." .tpgb-progress-loader5.layout-5.tp-repeater-item-".esc_attr($item1['_key'])." .tpgb-pre-5 { background: repeating-linear-gradient(45deg, ".esc_attr($pb1).", ".esc_attr($pb1)." 10px, ".esc_attr($pb2)." 10px, ".esc_attr($pb2)." 20px) }";
					}
					$progressColor .= $reProg;
					
					if(($item1['progressType']=='layout-2')){					
						$plcposclass="";	
						if(!empty($item1['lay02pos'])){
							if($item1['lay02pos']=='top'){
								$plcposclass = 'tpgb-perc-top';							
							}else if($item1['lay02pos']=='bottom'){
								$plcposclass = 'tpgb-perc-bottom';
							} 
						}					
						$output .='<span class="percentagelayout '.esc_attr($plcposclass).' tp-repeater-item-'.esc_attr($item1['_key']).'"></span>';
					}else if($item1['progressType']=='layout-5'){
						$output .='<div class="tpgb-progress-loader5 '.esc_attr($item1['progressType']).'  tp-repeater-item-'.esc_attr($item1['_key']).'"><div class="tpgb-pre-5 tpgb-pre-5-in1"></div><div class="tpgb-pre-5 tpgb-pre-5-in2"></div><div class="tpgb-pre-5 tpgb-pre-5-in3"></div><div class="tpgb-pre-5 tpgb-pre-5-in4"></div></div>';
					}
				}
			$progressColor .= $reProg;
			
			endforeach;
		}

		$output .= '<div class="tpgb-loader-wrap">';
		if(!empty($preLoader)){
			foreach ( $preLoader as $index => $item ) :
			$reProg = '';
			if($item['preLoadType']=='image' && !empty($item['imagestore']) && !empty($item['imagestore']['url'])) {
				$imagestore = (isset($item['imagestore']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['imagestore']) : (!empty($item['imagestore']['url']) ? $item['imagestore']['url'] : '');
				$altText = (isset($item['imagestore']['alt']) && !empty($item['imagestore']['alt'])) ? esc_attr($item['imagestore']['alt']) : ((!empty($item['imagestore']['title'])) ? esc_attr($item['imagestore']['title']) : esc_attr__('Preloader Image','tpgbp'));

				if(!empty($item['loaderOnImg'])){
					$output .="<div class='tpgb-img-loader'><div class='tpgb-img-loader-wrap'><span style='background-image: url(".esc_url($imagestore).");' data-no-lazy='1' class='tpgb-img-loader-wrap-in skip-lazy'></span></div>";
						$output .="<img data-no-lazy='1' class='tpgb-preloader-logo-l-img skip-lazy' alt='".$altText."' src='".esc_url($imagestore)."'>";
					$output .="</div>";
				} else {
					$output .= '<div class="tpgb-preloader-logo-img">';
						$output .= '<img class="tpgb-preloader-image" src="'.esc_url($imagestore).'" alt="'.$altText.'"/>';
					$output .= '</div>';
				}
			}else if($item['preLoadType']=='icon' && !empty($item['iconStore'])) {
				$output .= '<div class="tpgb-preloader-logo-icon">';
					$output .= '<span class="tpgb-preloader-icon">';
						$output .= '<i class="'.esc_attr($item['iconStore']).'"></i>';
					$output .= '</span>';
				$output .= '</div>';
			}else if($item['preLoadType']=='text' && !empty($item['textCntnt'])) {
				if(!empty($item['loaderOnText'])){
					$output .='<div class="tpgb-text-loader tp-repeater-item-'.esc_attr($item['_key']).'">'.Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['textCntnt']).'<div class="tpgb-text-loader-inner"><span class="tpgb-inner-load-text">'.Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['textCntnt']).'</span></div></div>';
				}else{
					$output .= '<div class="tpgb-preloader-animated-text tpgb-relative-block tp-repeater-item-'.esc_attr($item['_key']).'">';
						$output .= '<span>'.wp_kses_post($item['textCntnt']).'</span>';
					$output .= '</div>';
				}
			}else if($item['preLoadType']=='preDefine') {
				$prD1 = ($item['pDefineColor1'] ? $item['pDefineColor1'] : '#6f14f1');
				$prD2 = ($item['pDefineColor2'] ? $item['pDefineColor2'] : '#ff844a');
				if($item['aniType']=='1'){
					$output .= '<div class="tpgb-ball-grid-pulse tp-repeater-item-'.esc_attr($item['_key']).'"><div></div><div></div><div></div><div></div><div></div> <div></div><div></div><div></div><div></div></div>';
				}else if($item['aniType']=='2'){
					$output .= '<div class="tpgb-ball-triangle-path tp-repeater-item-'.esc_attr($item['_key']).'"><div></div><div></div><div></div></div>';
				}else if($item['aniType']=='3'){
					$output .= '<div class="tpgb-ball-scale-ripple-multiple tpgb-relative-block tp-repeater-item-'.esc_attr($item['_key']).'"><div></div><div></div><div></div></div>';
				}else if($item['aniType']=='4'){
					$output .= '<div class="tpgb-triangle-skew-spin tpgb-relative-block tp-repeater-item-'.esc_attr($item['_key']).'"><div></div></div>';
				}else if($item['aniType']=='5'){
					$output .= '<div class="tpgb-rounded-triangle tp-repeater-item-'.esc_attr($item['_key']).'"></div>';
				}else if($item['aniType']=='6'){
					$reProg = "@keyframes tpgb_preloader_1{0%{ height: 5px; transform: translateY(0px); background: ".esc_attr($prD1).";} 25%{ height: 30px; transform: translateY(15px); background: ".esc_attr($prD2).";} 50%{ height: 5px; transform: translateY(0px); background: ".esc_attr($prD1).";} 100%{ height: 5px; transform: translateY(0px); background: ".esc_attr($prD1).";}}";
					$progressColor .= $reProg;
					$output .= '<div class="tpgb_preloader_audio_wave tp-repeater-item-'.esc_attr($item['_key']).'"><span></span><span></span><span></span><span></span><span></span></div>';
				}else if($item['aniType']=='7'){
					if(!empty($item['pDefineColor1'])){
						$reProg = "@keyframes tpgb_typing_loader{0%{background-color: ".esc_attr($item['pDefineColor1']).";box-shadow: 12px 0 0 0 ".esc_attr($item['pDefineColor1'])."33,24px 0 0 0 ".esc_attr($item['pDefineColor1'])."33;}25% {background-color: ".esc_attr($item['pDefineColor1'])."66;box-shadow: 12px 0 0 0 ".esc_attr($item['pDefineColor1'])."33,24px 0 0 0 ".esc_attr($item['pDefineColor1'])."33;}75% {background-color: ".esc_attr($item['pDefineColor1'])."66;box-shadow: 12px 0 0 0 ".esc_attr($item['pDefineColor1'])."33,24px 0 0 0 ".esc_attr($item['pDefineColor1']).";}}";
						$progressColor .= $reProg;
					}
					$output .= '<div class="tpgb_typing_loader tp-repeater-item-'.esc_attr($item['_key']).'"></div>';
				}else if($item['aniType']=='8'){
					$output .= '<div class="tpgb-preloader-help tp-repeater-item-'.esc_attr($item['_key']).'"></div>';
				}else if($item['aniType']=='9'){
					$output .= '<div class="tpgb-preloader-cord tp-repeater-item-'.esc_attr($item['_key']).'">';
						$output .= '<div class="tpgb-cord tpgb-leftMove"><div class="tpgb-ball"></div></div>';
						$output .= '<div class="tpgb-cord"><div class="tpgb-ball"></div></div>';
						$output .= '<div class="tpgb-cord"><div class="tpgb-ball"></div></div>';
						$output .= '<div class="tpgb-cord"><div class="tpgb-ball"></div></div>';
						$output .= '<div class="tpgb-cord"><div class="tpgb-ball"></div></div>';
						$output .= '<div class="tpgb-cord"><div class="tpgb-ball"></div></div>';
						$output .= '<div class="tpgb-cord tpgb-rightMove"><div class="tpgb-ball"></div></div>';
						$output .= '<div class="tpgb-shadows">';
							$output .= '<div class="tpgb-leftShadow"></div>';
							$output .= '<div></div><div></div><div></div><div></div><div></div>';
							$output .= '<div class="tpgb-rightShadow"></div>';
						$output .= '</div>';
					$output .= '</div>';
				}else if($item['aniType']=='10'){
					$output .= '<div class="tpgb-preloader-dot tp-repeater-item-'.esc_attr($item['_key']).'">';
						$output .= '<span class="tpgb-preloader-dots"></span><span class="tpgb-preloader-dots"></span>';
						$output .= '<span class="tpgb-preloader-dots"></span><span class="tpgb-preloader-dots"></span>';
						$output .= '<span class="tpgb-preloader-dots"></span><span class="tpgb-preloader-dots"></span>';
						$output .= '<span class="tpgb-preloader-dots"></span><span class="tpgb-preloader-dots"></span>';
						$output .= '<span class="tpgb-preloader-dots"></span><span class="tpgb-preloader-dots"></span>';
					$output .= '</div>';
				}else if($item['aniType']=='11'){
					$output .= '<div class="tpgb-preloader-11-main tp-repeater-item-'.esc_attr($item['_key']).'">';
						$output .= '<span class="tpgb-preloader-11 tpgb_dot_1"></span>';
						$output .= '<span class="tpgb-preloader-11 tpgb_dot_2"></span>';
						$output .= '<span class="tpgb-preloader-11 tpgb_dot_3"></span>';
						$output .= '<span class="tpgb-preloader-11 tpgb_dot_4"></span>';
					$output .= '</div>';
				}else if($item['aniType']=='12'){
					if(!empty($item['pDefineColor2'])){
						$reProg ="@keyframes tpgb_preloader_4 { 0% {opacity: 0.3; transform:translateY(0px);-webkit-box-shadow:0px 0px 3px rgba(0, 0, 0, 0.1);-moz-box-shadow:0px 0px 3px rgba(0, 0, 0, 0.1);	box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.1);} 50% {opacity: 1; transform: translateY(-10px); background: ".esc_attr($item['pDefineColor2']).";	-webkit-box-shadow:0px 20px 3px rgba(0, 0, 0, 0.05); -moz-box-shadow:0px 20px 3px rgba(0, 0, 0, 0.05);box-shadow: 0px 20px 3px rgba(0, 0, 0, 0.05);} 100%  {opacity: 0.3; transform:translateY(0px);-webkit-box-shadow:0px 0px 3px rgba(0, 0, 0, 0.1);-moz-box-shadow:0px 0px 3px rgba(0, 0, 0, 0.1); box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.1);}}";
						$progressColor .= $reProg;
					}
					$output .= '<div class="tpgb_preloader_the_shake tp-repeater-item-'.esc_attr($item['_key']).'">';
						$output .= '<span></span><span></span><span></span><span></span><span></span>';
					$output .= '</div>';
				}else if($item['aniType']=='13'){
					$reProg ='@keyframes tpgb_preloader_5 { 0% {transform: rotate(0deg);} 50% {transform: rotate(180deg);background: '.esc_attr($prD2).';} 100% {transform: rotate(360deg);}} @keyframes tpgb_preloader_5_after { 0% {border-top:10px solid '.esc_attr($prD2).';border-bottom:10px solid '.esc_attr($prD2).';} 50% {border-top:10px solid '.esc_attr($prD1).';border-bottom:10px solid '.esc_attr($prD1).';} 100% {border-top:10px solid '.esc_attr($prD2).';border-bottom:10px solid '.esc_attr($prD2).';}}';
					$progressColor .= $reProg;
					$output .= '<div class="tpgb_preloader_spinning_disc_block tp-repeater-item-'.esc_attr($item['_key']).'">';
						$output .= '<div class="tpgb_preloader_spinning_disc"></div>';
					$output .= '</div>';
				}
			} else if($item['preLoadType']=='lottie' && !empty($item['lottieUrl']['url'])) {
				$lottieUrl = (isset($item['lottieUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['lottieUrl']) : (!empty($item['lottieUrl']['url']) ? $item['lottieUrl']['url'] : '');
				$ext = pathinfo($lottieUrl, PATHINFO_EXTENSION);
				if($ext!='json'){
					return '<h3 class="tpgb-posts-not-found">'.esc_html__("Opps! Wrong file format. Only JSON files accepted.",'tpgbp').'</h3>';
				}else{
					$lottieWidth = isset($item['lottieWidth']) ? $item['lottieWidth'] : 300;
					$lottieHeight = isset($item['lottieHeight']) ? $item['lottieHeight'] : 300;
					$lottieSpeed = isset($item['lottieSpeed']) ? $item['lottieSpeed'] : 300;
					$lottieLoopValue='';
					if(!empty($item['lottieLoop'])){
						$lottieLoopValue='loop'; 
					}
					$output .='<lottie-player src="'.esc_url($lottieUrl).'" style="width: '.esc_attr($lottieWidth).'px; height: '.esc_attr($lottieHeight).'px;" '.esc_attr($lottieLoopValue).' speed="'.esc_attr($lottieSpeed).'" autoplay></lottie-player>';
				}	
			} else if($item['preLoadType']=='progress') {
				if($item['preLoadType']=='progress' && empty($item['gradientToggle']) && ($item['progressType']=='layout-1' || $item['progressType']=='layout-3' || $item['progressType']=='layout-4')){
					$pb1 = ($item['pBarColor1']) ? $item['pBarColor1'] : '#6f14f1';
					$pb2 = ($item['pBarColor2']) ? $item['pBarColor2'] : '#7013f2d1';
					$reProg = ".tpgb-block-".esc_attr($block_id)." .tp-repeater-item-".esc_attr($item['_key'])." .tpgb-loadbar, .tpgb-block-".esc_attr($block_id)." .tpgb-progress-loader4.layout-4.tp-repeater-item-".esc_attr($item['_key'])." .tpgb-progress-loader4-in { background: repeating-linear-gradient(45deg, ".esc_attr($pb1).", ".esc_attr($pb1)." 10px, ".esc_attr($pb2)." 10px, ".esc_attr($pb2)." 20px) }";
				}
				$progressColor .= $reProg;
				if($item['progressType']=='layout-1'){
					$output .='<div class="tpgb-progress-loader tp-repeater-item-'.esc_attr($item['_key']).'"><div class="tpgb-percentage tpgb-precent"></div><div class="tpgb-progress-load"><div class="p-trackbar"><div class="tpgb-loadbar"></div></div><div class="tpgb-glow"></div></div></div>';
				}else if($item['progressType']=='layout-3'){
					$percpre=$percpost='';
					if(!empty($item['layo3Prefix'])){
						$percpre='<span class="tpgb-perc-prepostfix tpgb-perc-pre">'.Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['layo3Prefix']).'</span>';
					}
					if(!empty($item['layo3Postfix'])){
						$percpost='<span class="tpgb-perc-prepostfix tpgb-perc-post">'.Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['layo3Postfix']).'</span>';
					}
					$output .='<div class="tpgb-progress-loader '.esc_attr($item['progressType']).' tp-repeater-item-'.esc_attr($item['_key']).'">'.$percpre.'<div class="tpgb-percentage tpgb-precent3">0%</div>'.$percpost.'</div>';
				}else if($item['progressType']=='layout-4'){
					$output .='<div class="tpgb-progress-loader4 tpgb-relative-block '.esc_attr($item['progressType']).' tp-repeater-item-'.esc_attr($item['_key']).'"><div class="tpgb-progress-loader4-in tpgb-precent4">0%</div></div>';
				}else if($item['progressType']=='layout-6'){
					$pcEmptyColor = (!empty($item['pcEmptyColor'])) ? $item['pcEmptyColor'] : '#6f14f136';
					$pcFillColor = (!empty($item['pcFillColor'])) ? $item['pcFillColor'] : '#6f14f1';
					$pcStrockSize = (!empty($item['pcStrockSize'])) ? $item['pcStrockSize'] : 4;
					
					$output .='<div class="tpgb-progress-loader6 '.esc_attr($item['progressType']).' tp-repeater-item-'.esc_attr($item['_key']).'">';
						$output .='<svg class="progress-ring" width="120" height="120">';
							$output .='<circle class="progress-ring__circle progress-ring1 tpgb-precent6" style="stroke-dasharray: 326.726, 326.726;stroke-dashoffset: 326.726;" stroke="'.esc_attr($pcFillColor).'" stroke-width="'.esc_attr($pcStrockSize).'" fill="transparent" r="52" cx="60" cy="60"/>';
						$output .='</svg>';
						$output .='<svg class="progress-ring progress-ring2" width="120" height="120">';
							$output .='<circle class="progress-ring__circle" style="stroke-dasharray: 326.726, 326.726;stroke-dashoffset:0;" stroke="'.esc_attr($pcEmptyColor).'" stroke-width="'.esc_attr($pcStrockSize).'" fill="transparent" r="52" cx="60" cy="60"/>';
						$output .='</svg>';
						$output .='<div class="tpgb-percentage tpgb-precent3"></div>';
					$output .='</div>';
				}
			}else if($item['preLoadType']=='custom' && !empty($item['customCode'])){
				$allowed_html = array(
					'div' => array(
							'class' => array(),
					),
					'style' => array(),
				);
				$output .='<div class="tpgb-preloader-custom">'.wp_kses($item['customCode'], $allowed_html).'</div>';
			}else if($item['preLoadType']=='shortcode' && !empty($item['preShortCode'])){
				$output .='<div class="tpgb-preloader-custom-shortcode">'.do_shortcode( shortcode_unautop( $item['preShortCode'] ) ).'</div>';
			}
			endforeach;
		}
		$output .= '<style>'.$dyCss.$progressColor.'</style>';
		$output .= '</div>';
		
		if(!empty($pageLoadTrans) && $pageLoadTrans=='pageloadtriplesw'){
			$output .='<div class="tpgb-preload-reveal-layer-box">';
				$output .='<div style="background:'.esc_attr($tpgb4color1).'" class="tpgb-preload-reveal-layer"></div>';
				$output .='<div style="background:'.esc_attr($tpgb4color2).'" class="tpgb-preload-reveal-layer"></div>';
				$output .='<div style="background:'.esc_attr($tpgb4color3).'" class="tpgb-preload-reveal-layer"></div>';
			$output .='</div>';
		}else if(!empty($pageLoadTrans) && $pageLoadTrans=='pageloadsimple'){
			$output .='<div class="tpgb-preload-reveal-layer-box"><div style="background:'.esc_attr($tpgb4color1).'" class="tpgb-preload-reveal-layer"></div></div>';
		}else if(($pageLoadTrans && $pageLoadTrans=='pageloadduomove')){
			$output .='<div class="tpgb-preload-reveal-layer-box"><div style="background:'.esc_attr($tpgb4color1).'" class="tpgb-preload-reveal-layer"></div>';
			$output .='<div style="background:'.esc_attr($tpgb4color2).'" class="tpgb-preload-reveal-layer"></div></div>';
		}
    $output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
  
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_preloader() {
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_preloader_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_preloader' );