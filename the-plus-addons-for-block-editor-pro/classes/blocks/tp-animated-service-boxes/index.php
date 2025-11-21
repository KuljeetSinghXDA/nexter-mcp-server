<?php
/* Block : Animated Service Boxes
 * @since : 1.4.3
 */
defined( 'ABSPATH' ) || exit;

function tpgb_animated_service_boxes_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$mainStyleType = (!empty($attributes['mainStyleType'])) ? $attributes['mainStyleType'] : 'image-accordion';
	$imgAcrdnStyle = (!empty($attributes['imgAcrdnStyle'])) ? $attributes['imgAcrdnStyle'] : 'accordion-style-1';
	$imgOrientation = (!empty($attributes['imgOrientation'])) ? $attributes['imgOrientation'] : 'accordion-vertical';
	$slideStyle = (!empty($attributes['slideStyle'])) ? $attributes['slideStyle'] : 'sliding-style-1';
	$articleStyle = (!empty($attributes['articleStyle'])) ? $attributes['articleStyle'] : 'article-box-style-1';
	$activeSlide = (!empty($attributes['activeSlide'])) ? $attributes['activeSlide'] : '';
	$imgFlexGrow = (!empty($attributes['imgFlexGrow'])) ? $attributes['imgFlexGrow'] : '7.5';
	$bannerStyle = (!empty($attributes['bannerStyle'])) ? $attributes['bannerStyle'] : 'info-banner-style-1';
	$bannerOrientation = (!empty($attributes['bannerOrientation'])) ? $attributes['bannerOrientation'] : 'info-banner-left';
	$sectionStyle = (!empty($attributes['sectionStyle'])) ? $attributes['sectionStyle'] : 'hover-section-style-1';
	$sectionImgPreload = (!empty($attributes['sectionImgPreload'])) ? $attributes['sectionImgPreload'] : false;
	$fancyBStyle = (!empty($attributes['fancyBStyle'])) ? $attributes['fancyBStyle'] : 'fancy-box-style-1';
	$serviceEStyle = (!empty($attributes['serviceEStyle'])) ? $attributes['serviceEStyle'] : 'services-element-style-1';
	$portfolioStyle = (!empty($attributes['portfolioStyle'])) ? $attributes['portfolioStyle'] : 'portfolio-style-1';
	$serviceBox = (!empty($attributes['serviceBox'])) ? $attributes['serviceBox'] : [];
	$columns = (!empty($attributes['columns'])) ? $attributes['columns'] : 'md';
	$sbTabColumn = (!empty($attributes['sbTabColumn'])) ? $attributes['sbTabColumn'] : 'sb_t_2';
	$sbMobColumn = (!empty($attributes['sbMobColumn'])) ? $attributes['sbMobColumn'] : 'sb_m_1';
	
	$disIcnImg = (!empty($attributes['disIcnImg'])) ? $attributes['disIcnImg'] : false;
	$disBtn = (!empty($attributes['disBtn'])) ? $attributes['disBtn'] : false;
	$btnStyle = (!empty($attributes['btnStyle'])) ? $attributes['btnStyle'] : 'style-7';
	$btnIconType = (!empty($attributes['btnIconType'])) ? $attributes['btnIconType'] : 'none';
	$btnIconPosition = (!empty($attributes['btnIconPosition'])) ? $attributes['btnIconPosition'] : 'after';
	
	$titleOnClick = (!empty($attributes['titleOnClick'])) ? $attributes['titleOnClick'] : '';
	$titleLinkColor = (!empty($attributes['titleLinkColor'])) ? $attributes['titleLinkColor'] : '';
	
	$titleTagType = (!empty($attributes['titleTagType'])) ? $attributes['titleTagType'] : 'h6';
	$sTitleTagType = (!empty($attributes['sTitleTagType'])) ? $attributes['sTitleTagType'] : 'h6';
	
	$iconStyle = (!empty($attributes['iconStyle'])) ? $attributes['iconStyle'] : 'icon-square';
	$hoverBGOverlay = (!empty($attributes['hoverBGOverlay'])) ? $attributes['hoverBGOverlay'] : 'rgba(0,0,0,0.3)';
	$btnIconStore = (!empty($attributes['btnIconStore'])) ? $attributes['btnIconStore'] : '';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$equalHeightAtt = Tpgbp_Pro_Blocks_Helper::global_equal_height( $attributes );
	$equalHclass = '';
	if(!empty($equalHeightAtt)){
		$equalHclass = ' tpgb-equal-height';
	}
	
	//Style Class
	$style = $hoverSectionExtra = $orientClass = $sbTabClass = $sbMobClass = '';
	if($mainStyleType=='info-banner' && $bannerStyle=='info-banner-style-1'){
		$orientClass = $bannerOrientation;
	}
	if($mainStyleType=='image-accordion'){
		$style .= $imgAcrdnStyle." ".$imgOrientation;
	}else if($mainStyleType=='sliding-boxes'){
		$style .= $slideStyle;
		$sbTabClass .= " ".$sbTabColumn;
		$sbMobClass .= " ".$sbMobColumn;
	}else if($mainStyleType=='article-box'){
		$style .= $articleStyle;
	}else if($mainStyleType=='info-banner'){
		$style .= $bannerStyle." ".$orientClass;
	}else if($mainStyleType=='hover-section'){
		$style .= $sectionStyle;
		$hoverSectionExtra .= 'hover-section-extra';
	}else if($mainStyleType=='fancy-box'){
		$style .= $fancyBStyle;
	}else if($mainStyleType=='services-element'){
		$style .= $serviceEStyle;
	}else if($mainStyleType=='portfolio'){
		$style .= $portfolioStyle;
	}
	
	$tnslin = 'tpgb-trans-linear'; $tnsease = 'tpgb-trans-ease'; $tnseaseout = 'tpgb-trans-easeinout';
	$relposw = 'tpgb-relative-block'; $relpos = 'tpgb-relative-block';
	$relfposw = 'tpgb-rel-flex'; $absfposw = 'tpgb-abs-flex';
	
	//Column Class
	$list_column = '';
	if( $mainStyleType!='image-accordion' && $mainStyleType!='portfolio' && $mainStyleType!='sliding-boxes'){
		$list_column .= 'tpgb-col-'.esc_attr($columns['xs']);
		$list_column .= ' tpgb-col-lg-'.esc_attr($columns['md']);
		$list_column .= ' tpgb-col-md-'.esc_attr($columns['sm']);
		$list_column .= ' tpgb-col-sm-'.esc_attr($columns['xs']);
	}
	
	$port_hover_color=$port_click_text='';
	if($mainStyleType=='portfolio'){
		$port_hover_color='data-phcolor="'.esc_attr($titleLinkColor).'"';
		$port_click_text='data-clicktext="'.esc_attr($titleOnClick).'"';
	}
	
	$output = '';
	$featureImgSrc =$featureImgRender= '';
	$i=1;
	$loopItem = '';
	if(!empty($serviceBox)){
		foreach ( $serviceBox as $index => $item ) :
			
			$btnUrl = (isset($item['btnUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['btnUrl']) : (!empty($item['btnUrl']['url']) ? $item['btnUrl']['url'] : '');
			$link_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($item['btnUrl']);
			
			$getItemTitle = '';
			if(!empty($item['title'])){
				if((!empty($disBtn) || $mainStyleType=='portfolio') && !empty($btnUrl)){
					$target = (!empty($item['btnUrl']['target'])) ? 'target="_blank"' : '';
					$nofollow = (!empty($item['btnUrl']['nofollow'])) ? 'rel="nofollow"' : '';
					$ariaLabelT = (!empty($item['ariaLabel'])) ? $item['ariaLabel'] : $item['title'];
					$getItemTitle .= '<a class="asb-title-link" style="cursor: pointer" href="'.esc_url($btnUrl).'" '.$target.' '.$nofollow.' '.$link_attr.' aria-label="'.esc_attr($ariaLabelT).'">';
				}
					$getItemTitle .='<'.Tp_Blocks_Helper::validate_html_tag($titleTagType).' class="asb-title '.esc_attr($tnseaseout).'">';
						$getItemTitle .= wp_kses_post($item['title']);
					$getItemTitle .='</'.Tp_Blocks_Helper::validate_html_tag($titleTagType).'>';
				if((!empty($disBtn) || $mainStyleType=='portfolio') && !empty($btnUrl)){
					$getItemTitle .='</a>';
				}
			}
		
			$getItemSubTitle = '';
			if(!empty($item['subTitle'])){
				$getItemSubTitle .='<'.Tp_Blocks_Helper::validate_html_tag($sTitleTagType).' class="asb-sub-title '.esc_attr($tnseaseout).'">';
					$getItemSubTitle .= wp_kses_post($item['subTitle']);
				$getItemSubTitle .='</'.Tp_Blocks_Helper::validate_html_tag($sTitleTagType).'>';
			}
		
			$getItemDesc = '';
			if(!empty($item['description'])){
				$getItemDesc .= '<div class="asb-desc '.esc_attr($tnseaseout).'">'.wp_kses_post($item['description']).'</div>';
			}
			
			$loop_content_list = $item['contentList'];
			$se_listing='';
			if(!empty($loop_content_list) ){
				$loop_content_list = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($loop_content_list);
				$array=explode("|",$loop_content_list);
				if(!empty($array[1])){
					$se_listing .='<div class="se-liting-ul">';
					foreach($array as $value){							
						$se_listing .='<div class="se-listing" >'.wp_kses_post($value).'</div>';							
					}
					$se_listing .='</div>';
				}else{
					$se_listing ='<div class="se-liting-ul"><div class="se-listing">'.wp_kses_post($loop_content_list).'</div></div>';
				}
			}
		
			$getIcon = $iconSty= ''; 
			if($mainStyleType!='services-element'){
				$iconSty = $iconStyle;
			}
			$getIcon .= '<span class="asb-icon-image asb-icon '.esc_attr($iconSty).' '.esc_attr($tnseaseout).'">';
				$getIcon .= '<i class="'.esc_attr($item['iconStore']).'"></i>';
			$getIcon .= '</span>';
			
			$getImg = '';
			if(!empty($item['iconType']) && $item['iconType']=='image' && !empty($item['imgStore']['url'])){
				$imageSize = (!empty($item['imageSize'])) ? $item['imageSize'] : 'full';
				$altText = (isset($item['imgStore']['alt']) && !empty($item['imgStore']['alt'])) ? esc_attr($item['imgStore']['alt']) : ((!empty($item['imgStore']['title'])) ? esc_attr($item['imgStore']['title']) : esc_attr__('Service Box','tpgbp'));

				if(!empty($item['imgStore']['id'])){
					$imgSrc = wp_get_attachment_image($item['imgStore']['id'] , $imageSize,false, ['class' => 'asb-icon-image asb-image '.esc_attr($tnseaseout).' '.esc_attr($iconSty), 'alt'=> $altText ]);
				}else if( !empty($item['imgStore']['url']) ){
					$imgUrl = (isset($item['imgStore']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['imgStore']) : $item['imgStore']['url'];
					$imgSrc = '<img class="asb-icon-image asb-image '.esc_attr($iconSty).' '.esc_attr($tnseaseout).'" src="'.esc_url($imgUrl).'" alt="'.$altText.'"/>';
				}
				$getImg .= $imgSrc;
			}
			$getbutton = '';
			$target = (!empty($item['btnUrl']['target'])) ? 'target="_blank"' : '';
			$nofollow = (!empty($item['btnUrl']['nofollow'])) ? 'rel="nofollow"' : '';
			$ariaLabelT = (!empty($item['ariaLabel'])) ? esc_attr($item['ariaLabel']) : ((!empty($item['btnText'])) ? esc_attr($item['btnText']) : esc_attr__("Button", 'tpgbp'));
			$getbutton .= '<div class="tpgb-adv-button button-'.esc_attr($btnStyle).'">';
				$getbutton .= '<a href="'.esc_url($btnUrl).'" class="button-link-wrap" role="button" '.$target.' '.$nofollow.' '.$link_attr.' aria-label="'.$ariaLabelT.'">';
				if($btnStyle == 'style-8'){
					if($btnIconPosition == 'before'){
						if($btnIconType == 'icon'){
							$getbutton .= '<span class="btn-icon button-'.esc_attr($btnIconPosition).'">';
								$getbutton .= '<i class="'.esc_attr($btnIconStore).'"></i>';
							$getbutton .= '</span>';
						}
						$getbutton .= wp_kses_post($item['btnText']);
					} 
					if($btnIconPosition == 'after'){
						$getbutton .= wp_kses_post($item['btnText']);
						if($btnIconType == 'icon'){
							$getbutton .= '<span class="btn-icon button-'.esc_attr($btnIconPosition).'">';
								$getbutton .= '<i class="'.esc_attr($btnIconStore).'"></i>';
							$getbutton .= '</span>';
						}
					}
				}
				if($btnStyle == 'style-7' || $btnStyle == 'style-9' ){
					$getbutton .= wp_kses_post($item['btnText']);
					$getbutton .= '<span class="button-arrow">';
					if($btnStyle == 'style-7'){
						$getbutton .= '<span class="btn-right-arrow"><i class="fas fa-chevron-right"></i></span>';
					}
					if($btnStyle == 'style-9'){
						$getbutton .= '<i class="btn-show fas fa-chevron-right"></i>';
						$getbutton .= '<i class="btn-hide fas fa-chevron-right"></i>';
					}
					$getbutton .= '</span>';
				}
				$getbutton .= '</a>';
			$getbutton .= '</div>';
			
			$altText2 = (isset($item['featureImg']['alt']) && !empty($item['featureImg']['alt'])) ? esc_attr($item['featureImg']['alt']) : ((!empty($item['featureImg']['title'])) ? esc_attr($item['featureImg']['title']) : esc_attr__('Service Box','tpgbp'));

			if($mainStyleType=='image-accordion'){
				$classes = [ 'class' => 'theplus-image-accordion__image-instance loaded', 'alt'=> $altText2];
			}else if($mainStyleType=='sliding-boxes'){
				$classes = [ 'class' => esc_attr($tnslin), 'alt'=> $altText2];
			}else{
				$classes =['alt'=> $altText2];
			}
			$fimageSize = (!empty($attributes['imageSize'])) ? $attributes['imageSize'] : 'full';

			if(!empty($item['featureImg']) && !empty($item['featureImg']['id'])){
				$featureImgRender = wp_get_attachment_image($item['featureImg']['id'] , $fimageSize, false, $classes);
				$featureImgSrc = wp_get_attachment_image_src($item['featureImg']['id'] , $fimageSize);
				$featureImgSrc = isset($featureImgSrc[0]) ? $featureImgSrc[0] : '';
			}else if(!empty($item['featureImg']['url'])){
				$featureImg = (isset($item['featureImg']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['featureImg']) : $item['featureImg']['url'];
				if($mainStyleType=='image-accordion'){
					$featureImgRender = '<img class="theplus-image-accordion__image-instance loaded" src="'.esc_url($featureImg).'" alt="'.$altText2.'"/>';
				}else if($mainStyleType=='sliding-boxes'){
					$featureImgRender = '<img src="'.esc_url($featureImg).'" class="'.esc_attr($tnslin).'" alt="'.$altText2.'"/>';
				}else{
					$featureImgRender = '<img src="'.esc_url($featureImg).'" alt="'.$altText2.'"/>';
				}
				$featureImgSrc = $featureImg;
			}else{
				$featureImgRender = '';
				$featureImgSrc = '';
			}
			
			$infobannerBack = '';
			if($mainStyleType=='info-banner' && $bannerStyle=='info-banner-style-1' && !empty($featureImgSrc)){
				$infobannerBack = 'background:url('.esc_url($featureImgSrc).') center/cover';
			}
			
			$active_class='';
			if($mainStyleType=='image-accordion'){
				if($i == $activeSlide){
					$active_class='active_accrodian';
				}
			}
			
			if($mainStyleType=='sliding-boxes' && $i== $activeSlide){
				$active_class="active-slide";
			}else if($mainStyleType=='hover-section' && $i== 1){
				$active_class="active-hover";
			}else if($mainStyleType=='portfolio' && $i== 1){
                $active_class="active-port";
            }
			
			$hover_sec_ovly='';
			if($mainStyleType=='hover-section'){
				$hover_sec_ovly='data-hsboc="'.esc_attr($hoverBGOverlay).'"';
			}
			
			$image_url=$click_url='';
			if($mainStyleType=='portfolio' && ($portfolioStyle == 'portfolio-style-1' || $portfolioStyle == 'portfolio-style-2')){
				$image_url='data-url="'.esc_url($featureImgSrc).'"';
				$click_url='data-clickurl="'.esc_url($btnUrl).'"';
			}
			$reldb = ($mainStyleType=='article-box' ? $relposw : '');
			$loopItem .='<div class="service-item-loop tp-repeater-item-'.esc_attr($item['_key']).' '.esc_attr($active_class).' '.$list_column.''.esc_attr($sbTabClass).''.esc_attr($sbMobClass).' '.esc_attr($reldb).'" '.$hover_sec_ovly.' '.$image_url.' '.$click_url.' '.$port_click_text.' '.$port_hover_color.'>';
				if($mainStyleType=='image-accordion'){
					if(!empty($item['featureImg']) && !empty($item['featureImg']['url'])){
						$loopItem .= $featureImgRender;
					}
					$loopItem .='<div class="asb-content">';
						$loopItem .= $getItemTitle;
						$loopItem .= $getItemSubTitle;
						$loopItem .= $getItemDesc;
						if(!empty($disBtn)) {
							$loopItem .= $getbutton;
						}
					$loopItem .='</div>';
				}
				if($mainStyleType=='sliding-boxes'){
					$loopItem .='<div class="tp-sb-image">';
						if(!empty($item['featureImg']) && !empty($item['featureImg']['url'])){
							$loopItem .= $featureImgRender;
						}
					$loopItem .='</div>';
					$loopItem .='<div class="asb-content '.esc_attr($absfposw).'">';
						$loopItem .= $getItemTitle;
						$loopItem .= $getItemSubTitle;
						$loopItem .= $getItemDesc;
						if(!empty($disBtn)) {
							$loopItem .= $getbutton;
						}
					$loopItem .='</div>';
				}
				if($mainStyleType=='article-box' && $articleStyle=='article-box-style-1'){
					$loopItem .='<div class="article-box-inner-content '.esc_attr($tnseaseout).' '.esc_attr($relposw).'">';
						if($item['featureImg'] && $item['featureImg']['url']){
							$loopItem .='<div class="article-box-img">';
								if(!empty($item['featureImg']) && !empty($item['featureImg']['url'])){
									$loopItem .= $featureImgRender;
								}
							$loopItem .='</div>';
						}
						$loopItem .='<div class="article-overlay">';
							$loopItem .='<div class="article-box-content">';
								$loopItem .= $getItemTitle;
								$loopItem .='<div class="article-hover-content">';	
									$loopItem .= $getItemSubTitle;
									$loopItem .= $getItemDesc;
									if(!empty($disBtn)){
										$loopItem .= $getbutton;
									}
								$loopItem .= '</div>';
							$loopItem .= '</div>';
						$loopItem .= '</div>';
					$loopItem .= '</div>';
				}
				if($mainStyleType=='article-box' && $articleStyle=='article-box-style-2'){
					$loopItem .= '<div class="article-box-main '.esc_attr($relfposw).'">';
						$loopItem .= '<div class="article-box-main-wrapper '.esc_attr($relpos).'" style="background:url('.esc_url($featureImgSrc).') center/cover">';
							$loopItem .= '<div class="article-box-front-wrapper '.esc_attr($relfposw).'">';
								if(!empty($disIcnImg) && $item['iconType']=='icon'){
									$loopItem .= $getIcon;
								}
								if(!empty($disIcnImg) && $item['iconType']=='image'){
									$loopItem .= $getImg;
								}
								$loopItem .= $getItemTitle;
								$loopItem .= $getItemSubTitle;
							$loopItem .= '</div>';
							$loopItem .= '<div class="article-box-hover-wrapper">';
								$loopItem .= $getItemDesc;
								if(!empty($disBtn)){
									$loopItem .= $getbutton;
								}
							$loopItem .= '</div>';
						$loopItem .= '</div>';	
					$loopItem .= '</div>';
				}
				if($mainStyleType=='info-banner' && $bannerStyle=='info-banner-style-1'){
					$loopItem .= '<div class="info-banner-content-wrapper '.esc_attr($relposw).'">';
						$loopItem .= '<div class="info-banner-front-content '.esc_attr($tnseaseout).'">';
							if(!empty($disIcnImg) && $item['iconType']=='icon'){
								$loopItem .= $getIcon;
							}
							if(!empty($disIcnImg) && $item['iconType']=='image'){
								$loopItem .= $getImg;
							}
							$loopItem .= $getItemTitle;
							$loopItem .= $getItemSubTitle;
						$loopItem .= '</div>';
						$loopItem .= '<div class="info-banner-back-content '.esc_attr($tnseaseout).'" style="'.$infobannerBack.'">';
							$loopItem .= '<div class="info-banner-back-content-inner">';
								$loopItem .= $getItemDesc;
								if(!empty($disBtn)){
									$loopItem .= $getbutton;
								}
							$loopItem .= '</div>';
						$loopItem .= '</div>';
					$loopItem .= '</div>';
				}
				if($mainStyleType=='info-banner' && $bannerStyle=='info-banner-style-2'){
					$loopItem .= '<div class="info-banner-content-wrapper '.esc_attr($relfposw).'">';
						$loopItem .= '<div class="info-front-content '.esc_attr($relfposw).'">';
							if(!empty($disIcnImg) && $item['iconType']=='icon'){
								$loopItem .= $getIcon;
							}
							if(!empty($disIcnImg) && $item['iconType']=='image'){
								$loopItem .= $getImg;
							}
							$loopItem .= $getItemTitle;
							$loopItem .= $getItemSubTitle;
							$loopItem .= $getItemDesc;
							if(!empty($disBtn)){
								$loopItem .= $getbutton;
							}
						$loopItem .= '</div>';
					$loopItem .= '</div>';
				}
				if($mainStyleType=='hover-section'){
					$loopItem .= '<div class="hover-section-content-wrapper" data-image="'.esc_url($featureImgSrc).'">';
						if(!empty($disIcnImg) && $item['iconType']=='icon'){
							$loopItem .= $getIcon;
						}
						if(!empty($disIcnImg) && $item['iconType']=='image'){
							$loopItem .= $getImg;
						}
						$loopItem .= $getItemTitle;
						$loopItem .= '<div class="hover-content-inner-hover '.esc_attr($tnslin).'">';
							$loopItem .= $getItemSubTitle;
							$loopItem .= $getItemDesc;
							if(!empty($disBtn)){
								$loopItem .= $getbutton;
							}
						$loopItem .= '</div>';
					$loopItem .= '</div>';
				}
				if($mainStyleType=='fancy-box'){
					$loopItem .= '<div class="fancybox-inner-wrapper '.esc_attr($relposw).'">';
						$loopItem .= '<div class="fancybox-image-background '.esc_attr($relposw).'" style="background-image:url('.esc_url($featureImgSrc).')">';
						$loopItem .= '</div>';
						$loopItem .= '<div class="fancybox-inner-content '.esc_attr($relposw).'">';
							$loopItem .= '<div class="fb-content '.esc_attr($relpos).'">';
								$loopItem .= $getItemTitle;
								$loopItem .= $getItemSubTitle;
								$loopItem .= $getItemDesc;
							$loopItem .= '</div>';
							$loopItem .= '<div class="fb-button '.esc_attr($relpos).'">';
								if(!empty($disBtn)){
									$loopItem .= $getbutton;
								}
							$loopItem .= '</div>';
						$loopItem .= '</div>';
					$loopItem .= '</div>';
				}
				if($mainStyleType=='services-element' && $serviceEStyle=='services-element-style-1'){
					$loopItem .= '<div class="se-wrapper '.esc_attr($relposw).' '.esc_attr($tnseaseout).'">';
						$loopItem .= '<div class="se-first-section">';
							$loopItem .= '<div class="se-icon">';
								if(!empty($disIcnImg) && $item['iconType']=='icon'){
									$loopItem .= $getIcon;
								}
								if(!empty($disIcnImg) && $item['iconType']=='image'){
									$loopItem .= $getImg;
								}
								$loopItem .= '<div class="se-title-desc">';
									$loopItem .= $getItemTitle;
									$loopItem .= $getItemSubTitle;
									$loopItem .= $getItemDesc;
									if(!empty($disBtn)){
										$loopItem .= $getbutton;
									}
								$loopItem .= '</div>';
							$loopItem .= '</div>';
						$loopItem .= '</div>';
						$loopItem .= '<div class="se-listing-section">';		
							$loopItem .= $se_listing;
						$loopItem .= '</div>';
					$loopItem .= '</div>';
				}
				if($mainStyleType=='services-element' && $serviceEStyle=='services-element-style-2'){
					$loopItem .= '<div class="se-wrapper-main">';
						$loopItem .= '<div class="se-wrapper '.esc_attr($tnseaseout).'">';
							$loopItem .= '<div class="se-wrapper-inner">';
								$loopItem .= '<div class="se-icon">';
									if(!empty($disIcnImg) && $item['iconType']=='icon'){
										$loopItem .= $getIcon;
									}
									if(!empty($disIcnImg) && $item['iconType']=='image'){
										$loopItem .= $getImg;
									}
								$loopItem .= '</div>';
								$loopItem .= '<div class="se-content">';
									$loopItem .= $getItemTitle;
									$loopItem .= $getItemSubTitle;
									$loopItem .= $getItemDesc;
									if(!empty($disBtn)){
										$loopItem .= $getbutton;
									}
									$loopItem .= $se_listing;
								$loopItem .= '</div>';
							$loopItem .= '</div>';
						$loopItem .= '</div>';
					$loopItem .= '</div>';
				}
				if($mainStyleType=='portfolio'){
					$loopItem .= $getItemTitle;
				}
			$loopItem .= '</div>';
			if($i==1){
				$first_port_img=$featureImgSrc;
			}
			$i++;
			endforeach;
	}
	
	$data_attr = '';
	if($activeSlide==0 && $mainStyleType=='image-accordion'){
		$data_attr .= 'data-accordion-hover="yes"';
	}
	
    $output .= '<div class="tpgb-animated-service-boxes tpgb-relative-block tpgb-block-'.esc_attr($block_id).' '.esc_attr($mainStyleType).' '.esc_attr($style).' '.esc_attr($blockClass).' '.esc_attr($equalHclass).'" '.$data_attr.' '.$equalHeightAtt.'>';
		$output .= '<div class="asb_wrap_list tpgb-row '.esc_attr($hoverSectionExtra).'">';
			if($mainStyleType!='portfolio'){
				$output .= $loopItem;
			}
			if($mainStyleType=='portfolio' && $portfolioStyle=='portfolio-style-1'){
				$output .= '<div class="portfolio-content-wrapper tpgb-col-md-6 tpgb-col-lg-6 tpgb-col-sm-12 tpgb-col-12">';
					$output .= $loopItem;
				$output .= '</div>';
				$output .= '<div class="portfolio-hover-wrapper tpgb-col-md-6 tpgb-col-lg-6 tpgb-col-sm-12 tpgb-col-12 '.esc_attr($relfposw).'">';
					$output .= '<div class="portfolio-hover-image '.esc_attr($relposw).'" style="background:url('.esc_url($first_port_img).')">';
					$output .= '</div>';
				$output .= '</div>';
			}
			if($mainStyleType=='portfolio' && $portfolioStyle=='portfolio-style-2'){
				$output .= '<div class="portfolio-wrapper tpgb-col-md-12 '.esc_attr($relfposw).'" style="background:url('.esc_url($first_port_img).')">';
					$output .= $loopItem;
				$output .= '</div>';
			}
		$output .= '</div>';
    $output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

function tpgb_animated_service_boxes() {
    
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_animated_service_boxes_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_animated_service_boxes' );