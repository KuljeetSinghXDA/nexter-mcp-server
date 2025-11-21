<?php
/* Block : Dynamic Category
 * @since : 1.4.2
 */
defined( 'ABSPATH' ) || exit;

function tpgb_dy_limit_words($string, $word_limit){
	$words = explode(" ",$string);
	return implode(" ",array_splice($words,0,$word_limit));
}

function tpgb_tp_dynamic_category_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");

	$output = '';
	$queryArgs = tpgb_postCate_query($attributes);
	$style = isset($attributes['style']) ? $attributes['style'] : 'style_1';
	$layout = isset($attributes['layout']) ? $attributes['layout'] : 'grid';
	$hideParentCat = isset($attributes['hideParentCat']) ? $attributes['hideParentCat'] : false;
	$hvrBgImg = isset($attributes['hvrBgImg']) ? $attributes['hvrBgImg'] : false;
	$taxonomySlug	= !empty($attributes['taxonomySlug']) ? $attributes['taxonomySlug'] : '';
	$notFoundText = (!empty($attributes['notFoundText'])) ? $attributes['notFoundText'] : '';
	$showDots = (!empty($attributes['showDots'])) ? $attributes['showDots'] : [ 'md' => false ];
	$dotsStyle = (!empty($attributes['dotsStyle'])) ? $attributes['dotsStyle'] : false;
	$slideHoverDots = (!empty($attributes['slideHoverDots'])) ? $attributes['slideHoverDots'] : false;
	$showArrows = (!empty($attributes['showArrows'])) ? $attributes['showArrows'] : [ 'md' => false ];
	$arrowsStyle = (!empty($attributes['arrowsStyle'])) ? $attributes['arrowsStyle'] : 'style-1';
	$arrowsPosition = (!empty($attributes['arrowsPosition'])) ? $attributes['arrowsPosition'] : 'top-right';
	$outerArrows = (!empty($attributes['outerArrows'])) ? $attributes['outerArrows'] : false;
	$slideHoverArrows = (!empty($attributes['slideHoverArrows'])) ? $attributes['slideHoverArrows'] : false;

	$display_thumbnail = !empty($attributes['DisImgSize']) ? $attributes['DisImgSize'] : false;
    $thumbnail = isset($attributes['ImageSize']) ? $attributes['ImageSize'] : 'full';

	$defaultImg = TPGB_ASSETS_URL.'assets/images/tpgb-placeholder-grid.jpg';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$metrocolumns = isset($attributes['metrocolumns']) ? $attributes['metrocolumns'] : [ 'md' => '3' ] ;
	$metroStyle = isset($attributes['metroStyle']) ? $attributes['metroStyle'] : '';
	$metroCustom = (!empty($attributes['metroCustom'])) ? $attributes['metroCustom'] : '';

	$onhoverbgclass="";
	if(!empty($hvrBgImg) && $hvrBgImg == true){
		$onhoverbgclass=" tpgb-dc-st3-bgimg";
	}
	
	//Columns
	$column_class = '';
	if($layout!='carousel' && !empty($attributes['columns']) && is_array($attributes['columns'])){
		$column_class .= ' tpgb-col';
		$column_class .= isset($attributes['columns']['md']) ? " tpgb-col-lg-".$attributes['columns']['md'] : ' tpgb-col-lg-3';
		$column_class .= isset($attributes['columns']['sm']) ? " tpgb-col-md-".$attributes['columns']['sm'] : ' tpgb-col-md-4';
		$column_class .= isset($attributes['columns']['xs']) ? " tpgb-col-sm-".$attributes['columns']['xs'] : ' tpgb-col-sm-6';
		$column_class .= isset($attributes['columns']['xs']) ? " tpgb-col-".$attributes['columns']['xs'] : ' tpgb-col-6';
	}

	//Classes
	$list_style	=' dynamic-cate-'.esc_attr($style);

	$list_layout	= '';
	if($layout=='grid' || $layout=='masonry'){
		$list_layout = 'tpgb-isotope';
	}else if($layout=='metro'){
		$list_layout = 'tpgb-metro';
	}else if($layout=='carousel'){
		$list_layout = 'tpgb-carousel splide';
	}else{
		$list_layout = 'tpgb-isotope';
	}


	//Carousel Options
	$carousel_settings = '';
	$Sliderclass = '';
	if($layout=='carousel'){
		
		if($slideHoverDots==true && ( ( isset($showDots['md']) && !empty($showDots['md']) ) || ( isset($showDots['sm']) && !empty($showDots['sm']) ) || ( isset($showDots['xs']) && !empty($showDots['xs']) )) ){
			$Sliderclass .= ' hover-slider-dots';
		}
		if($outerArrows==true && ( ( isset($showArrows['md']) && !empty($showArrows['md']) ) || ( isset($showArrows['sm']) && !empty($showArrows['sm']) ) || ( isset($showArrows['xs']) && !empty($showArrows['xs']) ) ) ){
			$Sliderclass .= ' outer-slider-arrow';
		}
		if($slideHoverArrows==true && ( ( isset($showArrows['md']) && !empty($showArrows['md']) ) || ( isset($showArrows['sm']) && !empty($showArrows['sm']) ) || ( isset($showArrows['xs']) && !empty($showArrows['xs']) ) ) ){
			$Sliderclass .= ' hover-slider-arrow';
		}
		if( ( isset($showDots['md']) && !empty($showDots['md']) ) || ( isset($showDots['sm']) && !empty($showDots['sm']) ) || ( isset($showDots['xs']) && !empty($showDots['xs']) ) ){
			$Sliderclass .= ' dots-'.esc_attr($dotsStyle);
		}
		
		$carousel_settings = Tp_Blocks_Helper::carousel_settings( $attributes );
	}

	$classattr = '';
	$classattr .= ' tpgb-block-'.$block_id;
	$classattr .= ' '.$list_style;
	$classattr .= ' '.$list_layout;
	

	// Set Data For Metro Layout
	$metroAttr = [];
	$total = '';
	if( $layout == 'metro' ){
		if( isset($metrocolumns['md']) && !empty($metrocolumns['md']) ){
			$metroAttr['metro_col'] = $metrocolumns['md'];
		}
		
		if( isset($metrocolumns['sm']) && !empty($metrocolumns['sm']) ){
			$metroAttr['tab_metro_col'] = $metrocolumns['sm'];
		}else if( isset($metrocolumns['md']) && !empty($metrocolumns['md']) ){
			$metroAttr['tab_metro_col'] =  $metrocolumns['md'];
		}

		if( isset($metrocolumns['xs']) && !empty($metrocolumns['xs']) ){
			$metroAttr['mobile_metro_col'] = $metrocolumns['xs'];
		}else if( isset($metrocolumns['sm']) && !empty($metrocolumns['sm']) ){
			$metroAttr['mobile_metro_col'] =  $metrocolumns['sm'];
		}else if( isset($metrocolumns['md']) && !empty($metrocolumns['md']) ){
			$metroAttr['mobile_metro_col'] =  $metrocolumns['md'];
		}

		if( isset($metroStyle['md']) && !empty($metroStyle['md']) ){
			$metroAttr['metro_style'] = $metroStyle['md'];
		}

		if( isset($metroStyle['sm']) && !empty($metroStyle['sm']) ){
			$metroAttr['tab_metro_style'] =  $metroStyle['sm'];
		}else if( isset($metroStyle['md']) && !empty($metroStyle['md']) ){
			$metroAttr['tab_metro_style'] = $metroStyle['md'];
		}

		if( isset($metroStyle['xs']) && !empty($metroStyle['xs']) ){
			$metroAttr['mobile_metro_style'] = $metroStyle['xs'];
		}else if( isset($metroStyle['sm']) && !empty($metroStyle['sm']) ){
			$metroAttr['mobile_metro_style'] = $metroStyle['sm'];
		}else if( isset($metroStyle['md']) && !empty($metroStyle['md']) ){
			$metroAttr['mobile_metro_style'] =  $metroStyle['md'];
		}
		if( (isset($metroStyle['md']) && !empty($metroStyle['md']) && $metroStyle['md'] == 'custom') || ( isset($metroStyle['sm']) && !empty($metroStyle['sm']) && $metroStyle['sm'] == 'custom' ) || ( isset($metroStyle['xs']) && !empty($metroStyle['xs']) && $metroStyle['xs'] == 'custom' ) ){
			$mecusCol = [];
			$exString = explode(' | ', $metroCustom );
			
			if(!empty($exString)){
				foreach( $exString as $index => $item ){
					if( isset($item) && !empty($item) ){
						$mecusCol[$index+1] = [ 'layout' => $item ];
					}
					
				}
				$total = count($exString);
			}
			$metroAttr['customLay'] = $mecusCol; 
		}
		$metroAttr = 'data-metroAttr= \'' .wp_json_encode($metroAttr) . '\' ';
	}
	


	$ji=1;$col=$tabCol=$moCol='';
	
	if ( empty($queryArgs) && !empty($taxonomySlug)  ) {
		$output .= '<div id="'.esc_attr($block_id).'" class="tpgb-dy-cat-list '.esc_attr($blockClass).' '.esc_attr($classattr).' '.esc_attr($Sliderclass).' '.esc_attr($onhoverbgclass).' tpgb-relative-block '.esc_attr($list_style).'">';
			$output .='<p class="tpgb-no-posts-found">'.esc_html($notFoundText).'</p>';
		$output .= '</div>';
	}
	else{
		if(!is_object($queryArgs)){
			$output .= '<div id="'.esc_attr($block_id).'" class="tpgb-dy-cat-list '.esc_attr($blockClass).' '.esc_attr($classattr).' '.esc_attr($Sliderclass).' '.esc_attr($onhoverbgclass).' tpgb-relative-block '.esc_attr($list_style).'" data-style="'.esc_attr($style).'" data-layout="'.esc_attr($layout).'" data-splide=\'' . wp_json_encode($carousel_settings). '\' '.( $layout == 'metro' ? $metroAttr : '' ).' data-id="'.esc_attr($block_id).'">';

				if( $layout == 'carousel' && ( ( isset($showArrows['md']) && !empty($showArrows['md']) ) || ( isset($showArrows['sm']) && !empty($showArrows['sm']) ) || ( isset($showArrows['xs']) && !empty($showArrows['xs']) ) ) ){
					$output .= Tp_Blocks_Helper::tpgb_carousel_arrow($arrowsStyle,$arrowsPosition);
				}
				$output .= '<div id="tpgb_list" class="post-loop-inner '.($layout == 'carousel' ? 'splide__track' : 'tpgb-row').'">';
					if($layout == 'carousel'){
						$output .= '<div class="splide__list">';
					}
					foreach( $queryArgs as $prodCat ) :
							
						$featuredImage='';
						if( $prodCat->parent == 0 && isset($hideParentCat) && $hideParentCat==true){
							
						 }else{
							if(!empty($prodCat->term_id )){
						 		$catID = get_term_meta($prodCat->term_id, 'tpgb_category_id', true);
							}
							if(!empty($catID)){
								$catImg = $catID;
								
								if(($layout=='grid' || $layout=='carousel') && !empty($catID)){
									if((!empty($display_thumbnail) && $display_thumbnail==true) && !empty($thumbnail)){
										$catID = wp_get_attachment_image_url( $catID, $thumbnail );
									}else{
										$catID = wp_get_attachment_image_url( $catID, 'tp-image-grid' );
									}
									
								}else if(($layout=='masonry' || $layout=='metro') && !empty($catID)){
									if((!empty($display_thumbnail) && $display_thumbnail==true) && !empty($thumbnail)){
										$catID = wp_get_attachment_image_url( $catID,  $thumbnail );
									}else{
										$catID = wp_get_attachment_image_url( $catID, 'full' );
									}
						 		}
								$featuredImage='<img src="'.esc_url($catID).'" alt="'.esc_attr(get_the_title()).'">';
						 	}else if($taxonomySlug == 'product_cat' || $taxonomySlug == 'product_tag'){

								$catID = get_term_meta( $prodCat->term_id, 'thumbnail_id', true );

								if(($layout=='grid' || $layout=='carousel') && !empty($catID)){
									if((!empty($display_thumbnail) && $display_thumbnail==true) && !empty($thumbnail)){
										$catID = wp_get_attachment_image_url( $catID, $thumbnail );
										
									}else{
										$catID = wp_get_attachment_image_url( $catID, 'tp-image-grid' );
									}
									
								}else if(($layout=='masonry' || $layout=='metro')  && !empty($catID)){
									if((!empty($display_thumbnail) && $display_thumbnail==true) && !empty($thumbnail)){
										$catID = wp_get_attachment_image_url( $catID, $thumbnail );
									}else{
										$catID = wp_get_attachment_image_url( $catID, 'full' );
									}										
								}

								if( !empty($catID) ){
									$featuredImage='<img src="'.esc_url($catID).'" alt="'.esc_attr(get_the_title()).'">';
								}else{
									$catImg = $defaultImg;
									$featuredImage='<img src="'.esc_url($catImg).'" alt="'.esc_attr(get_the_title()).'">';
								}

							}else{
								$catImg = $defaultImg;
								$featuredImage='<img src="'.esc_url($catImg).'" alt="'.esc_attr(get_the_title()).'">';
							}
							$catLink = get_term_link( $prodCat, $taxonomySlug );
							$catName = !empty($prodCat->name) ? $prodCat->name : '';
							
							$dataAttr = '';
							if($style == 'style_3'){
								$dataAttr .= 'data-bgimage="'.esc_url($catID).'"';
							}

							if((!empty($attributes['descTxtLmt']) && $attributes['descTxtLmt']==true) && !empty($attributes['shwDescInput'])){
								if(!empty($attributes['shwDescBy'])){				
									if($attributes['shwDescBy']=='letters'){												
										$cateDesc = substr($prodCat->description,0,$attributes['shwDescInput']);								
									}else if($attributes['shwDescBy']=='words'){
										$cateDesc = tpgb_dy_limit_words($prodCat->description,$attributes['shwDescInput']);					
									}
								}	
								if($attributes['shwDescBy']=='letters'){
									if(strlen($prodCat->description) > $attributes['shwDescInput']){
										if(!empty($attributes['shwDots']) && $attributes['shwDots']==true){
											$cateDesc .='...';
										}
									}
								}
								else if($attributes['shwDescBy']=='words'){
									if(str_word_count($prodCat->description) > $attributes['shwDescInput']){
										if(!empty($attributes['shwDots']) && $attributes['shwDots']==true){
											$cateDesc .='...';
										}
									}
								}
							}
							else{
								$cateDesc = !empty($prodCat->description) ? $prodCat->description : '';
							}

							$category_product_count = isset($prodCat->count) ? $prodCat->count : '0';

							// Metro class Layout
							if( $layout == 'metro' ){
								if( ( isset($metrocolumns['md']) && !empty($metrocolumns['md']) ) && ( isset($metroStyle['md']) && !empty($metroStyle['md']) ) ){
									$col= Tpgbp_Pro_Blocks_Helper::tpgbp_metro_class($ji , $metrocolumns['md'] , $metroStyle['md'] , $total );
								}
								if( ( isset($metrocolumns['sm']) && !empty($metrocolumns['sm']) ) && ( isset($metroStyle['sm']) && !empty($metroStyle['sm']) ) ){
									$tabCol = Tpgbp_Pro_Blocks_Helper::tpgbp_metro_class($ji , $metrocolumns['sm'] , $metroStyle['sm'] , $total );
								}
								if( ( isset($metrocolumns['xs']) && !empty($metrocolumns['xs']) ) && ( isset($metroStyle['xs']) && !empty($metroStyle['xs']) ) ){
									$moCol = Tpgbp_Pro_Blocks_Helper::tpgbp_metro_class($ji , $metrocolumns['xs'] , $metroStyle['xs'] , $total );
								}
							}
							//grid item loop
							$output .= '<div class="grid-item '.( $layout=='carousel' ? 'splide__slide' : ( $layout !='metro' ? esc_attr($column_class) : '')).' '.( $layout=='metro' ? ' tpgb-metro-'.esc_attr($col).' '.( !empty($tabCol) ? ' tpgb-tab-metro-'.esc_attr($tabCol).''  : '' ).' '.( !empty($moCol) ? ' tpgb-mobile-metro-'.esc_attr($moCol).''  : '' ).' ' : '' ).'">';

								$catImg = $catID;

								$cdClass = '';
								if(empty($cateDesc)){
									$cdClass = ' tpgb-cd-empty-dsc';
								}

								$cateHtml = '';
								$cateHtml .='<div class="tpgb-dy-hvr-cnt">';
									$cateHtml .='<div class="tpgb-dy-hvr-cnt-inn">';
                                        $cateHtml .='<div class="tpgb-rel-flex">';
                                            $cateHtml .='<div class="tpgb-dy-hvr-cat-name">'.esc_html($catName).'</div>';
                                            if(!empty($attributes['hideProCnt']) && $attributes['hideProCnt']==true){
                                                $cateHtml .='<div class="tpgb-dy-hvr-cat-count">'.esc_html($category_product_count).'';
                                                    if(!empty($attributes['countExtraText'])){
                                                        $cateHtml .='<span class="tpgb-cnt-extra-txt">'.esc_html($attributes['countExtraText']).'</span>';
                                                    }
                                                $cateHtml .='</div>';
                                            }
                                        $cateHtml .= '</div>';
										if( ( !empty($attributes['shwDesc']) && $attributes['shwDesc']==true ) && !empty($cateDesc) && $style != 'style_3' ){
											$cateHtml .='<div class="tpgb-dy-hvr-cat-desc '.esc_attr($cdClass).'">'.wp_kses_post($cateDesc).'</div>';
										}
									$cateHtml .= '</div>';
								$cateHtml .= '</div>';

								
								$output .='<div class="tpgb-dynamic-wrapper '.esc_attr($style).'" '.$dataAttr.'>';
									$output .='<div class="tpgb-dy-cnt">';
										if($style == 'style_1'){
											if($layout=='metro'){
												$output .= '<a href="'.esc_url($catLink).'">';
													if($attributes['clHvrCntTgl'] == true){
														$output .='<div class="tpgb-extra-wcc-inn">';
													}
												$output .= '<div class="tpgb-dy-cat-bg-img-metro" style="background:url('.$catID.') center/cover"></div>';
												
											}else{
												$output .= '<a href="'.esc_url($catLink).'">';
													if($attributes['clHvrCntTgl'] == true){
														$output .='<div class="tpgb-extra-wcc-inn">';
													}
												$output .=$featuredImage;								
											}
											$output .= $cateHtml;
											if($attributes['clHvrCntTgl'] == true){
												$output .='</div>';	
											}
											$output .='</a>';
										}else if($style == 'style_2'){	
											
											if($layout=='metro'){
												$output .= '<a href="'.esc_url($catLink).'"> <div class="tpgb-dy-cat-bg-img-metro" style="background:url('.esc_url($catID).') center/cover"></div>';
											}else{								
												$output .= '<a href="'.esc_url($catLink).'">'.$featuredImage.'';
											}
											$output .= $cateHtml;
											$output .='</a>';	
													

										}else if($style == 'style_3'){	
											
											if($layout=='metro'){
												$output .= '<a href="'.esc_url($catLink).'"><div class="tpgb-dy-cat-bg-img-metro" style="background:url('.esc_url($catID).') center/cover"></div>';
											}else{				
												$output .= '<a href="'.esc_url($catLink).'">';
											}
											$output .= $cateHtml;
											$output .='</a>';	
													
										}
									$output .= '</div>';
								$output .= '</div>';
								
							$output .= '</div>';
						 	$ji++;
						}
					endforeach;

					if($layout == 'carousel'){
						$output .= '</div>';
					}
				$output .= '</div>';

			$output .= '</div>';
		}else{
			$output .= '<div id="'.esc_attr($block_id).'" class="tpgb-dy-cat-list '.esc_attr($blockClass).' '.esc_attr($classattr).' '.esc_attr($Sliderclass).' '.esc_attr($onhoverbgclass).' tpgb-relative-block '.esc_attr($list_style).'">';
				$output .='<p class="tpgb-no-posts-found">'.esc_html($notFoundText).'</p>';
			$output .= '</div>';
		}
		
	}

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	if( $layout == 'carousel' ){
		$arrowCss = Tp_Blocks_Helper::tpgb_carousel_arrow_css( $showArrows , $block_id );
		if( !empty($arrowCss) ){
			$output .= $arrowCss;
		}
	}
	wp_reset_postdata();
	
    return $output;
}


/**
 * Render for the server-side
 */
function tpgb_dynamic_category() {
	$globalBgOption = Tpgb_Blocks_Global_Options::load_bg_options();
	$globalpositioningOption = Tpgb_Blocks_Global_Options::load_positioning_options();
	$globalPlusExtrasOption = Tpgb_Blocks_Global_Options::load_plusextras_options();
	$carousel_options = Tpgb_Blocks_Global_Options::carousel_options();
	$sliderOpt = [
		'slideColumns' => [
			'type' => 'object',
			'default' => [ 'md' => 2,'sm' => 2,'xs' => 1 ],
		],
	];
	$carousel_options = array_merge($carousel_options,$sliderOpt);
	
	$attributesOptions = array(
		'block_id' => [
			'type' => 'string',
			'default' => '',
		],
		'notFoundText' => [
			'type' => 'string',
			'default' => 'No Taxonomy Found',
		],
		'style' => [
			'type' => 'string',
			'default' => 'style_1',
		],
		'layout' => [
			'type' => 'string',
			'default' => 'grid',
		],
		'taxonomySlug' => [
			'type' => 'string',
			'default' => 'category',
		],
		'alignSt1' => [
			'type' => 'string',
			'default' => 'flex-start',
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'style', 'relation' => '==', 'value' => 'style_1']],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper.style_1 .tpgb-dy-hvr-cnt{ justify-content: {{alignSt1}};}',
				],	
				(object) [
					'condition' => [(object) ['key' => 'style', 'relation' => '==', 'value' => 'style_2']],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper.style_2 .tpgb-dy-hvr-cnt{ align-items: {{alignSt1}}; }',
				],	
			],
			'scopy' => true,
		],
		'alignOffset' => [
			'type' => 'string',
			'default' => 'flex-start',
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'style', 'relation' => '==', 'value' => 'style_1']],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper.style_1 .tpgb-dy-hvr-cnt .tpgb-dy-hvr-cnt-inn{ align-items : {{alignOffset}}; width : 100%; }',
				],
				(object) [
					'condition' => [ (object) ['key' => 'style', 'relation' => '==', 'value' => 'style_2']],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper.style_2 .tpgb-dy-hvr-cnt-inn{ align-items: {{alignOffset}}; }',
				],	
			],
			'scopy' => true,
		],
		'hideEmpty' => [
			'type' => 'boolean',
			'default' => true,	
		],
		'hideSubCat' => [
			'type' => 'boolean',
			'default' => false,	
		],
		'includePosts' => [
			'type' => 'string',
			'default' => '',
		],
		'excludePosts' => [
			'type' => 'string',
			'default' => '',
		],
		'displayPosts' => [
			'type' => 'string',
			'default' => 6,
		],
		'offsetPosts' => [
			'type' => 'string',
			'default' => 0,
		],
		'orderBy' => [
			'type' => 'string',
			'default' => 'title',
		],
		'order' => [
			'type' => 'string',
			'default' => 'asc',
		],
		'hideProCnt' => [
			'type' => 'boolean',
			'default' => true,	
		],
		'customQueryId' => [
			'type' => 'string',
			'default' => '',
		],
		'shwDesc' => [
			'type' => 'boolean',
			'default' => false,	
		],
		'descTxtLmt' => [
			'type' => 'boolean',
			'default' => false,	
		],
		'shwDescBy' => [
			'type' => 'string',
			'default' => 'letters',
		],
		'shwDescInput' => [
			'type' => 'string',
			'default' => 30,
		],
		'shwDots' => [
			'type' => 'boolean',
			'default' => true,	
		],
		'DisImgSize' => [
			'type' => 'boolean',
			'default' => false,
		],
		'ImageSize' => [
			'type' => 'string',
			'default' => 'full',
		],
		'hvrBgImg' => [
			'type' => 'boolean',
			'default' => false,
		],
		'hideParentCat' => [
			'type' => 'boolean',
			'default' => false,
		],
		'columns' => [
			'type' => 'object',
			'default' => [ 'md' => 4,'sm' => 4,'xs' => 6 ],
		],
		'metrocolumns' => [
			'type' => 'object',
			'default' => [ 'md' => 3,'sm' => 3,'xs' => 3 ],
		],
		'metroStyle' => [
			'type' => 'object',
			'default' => [ 'md' => 'style-1','sm' => 'style-1','xs' => 'style-1' ],
		],
		'metroCustom' => [
			'type' => 'string',
			'default' => '',
		],
		'columnSpace' => [
			'type' => 'object',
			'default' => (object) [ 
				'md' => [
					"top" => 15,
					"right" => 15,
					"bottom" => 15,
					"left" => 15,
				],
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'layout', 'relation' => '!=', 'value' => 'carousel']],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .grid-item{padding: {{columnSpace}};}',
				],
			],
		],
		'titleTypo' => [
			'type' => 'object',
			'default' => (object) [
				'openTypography' => 0,
				'size' => [ 'md' => 20, 'unit' => 'px' ],
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-name,{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-name a',
				],
			],
			'scopy' => true,
		],
		'titleNormalColor' => [
			'type' => 'string',
			'default' => '',
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dy-hvr-cat-name,{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dy-hvr-cat-name a{color: {{titleNormalColor}} !important;}',
				],
			],
			'scopy' => true,
		],
		'titleHoverColor' => [
			'type' => 'string',
			'default' => '',
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-name,{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-name a{color: {{titleHoverColor}} !important;}',
				],
			],
			'scopy' => true,
		],
		'titleBoxShadow' => [
			'type' => 'object',
			'default' => (object) [
				'openShadow' => 0,
				'blur' => 8,
				'color' => "rgba(0,0,0,0.40)",
				'horizontal' => 0,
				'inset' => 0,
				'spread' => 0,
				'vertical' => 4
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dy-hvr-cat-name,{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dy-hvr-cat-name a',
				],
			],
			'scopy' => true,
		],
		'titleHoverBoxShadow' => [
			'type' => 'object',
			'default' => (object) [
				'openShadow' => 0,
				'blur' => 8,
				'color' => "rgba(0,0,0,0.40)",
				'horizontal' => 0,
				'inset' => 0,
				'spread' => 0,
				'vertical' => 4
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-name,{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-name a',
				],
			],
			'scopy' => true,
		],
		'titleBgTgl' => [
			'type' => 'boolean',
			'default' => false,
		],
		'titleBgPadding' => [
			'type' => 'object',
			'default' => (object) [ 
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [
						(object) ['key' => 'titleBgTgl', 'relation' => '==', 'value' => true],
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-name{padding: {{titleBgPadding}} !important;}',
				],
			],
			'scopy' => true,
		],
		'titleBg' => [
			'type' => 'object',
			'default' => (object) [
				'openBg'=> 0,
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'titleBgTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-name',
				],
			],
			'scopy' => true,
		],
		'titleBgH' => [
			'type' => 'object',
			'default' => (object) [
				'openBg'=> 0,
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'titleBgTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-name',
				],
			],
			'scopy' => true,
		],
		'titleBdr' => [
			'type' => 'object',
			'default' => (object) [
				'openBorder' => 0,	
			],
			'style' => [
				(object) [
					'condition' => [ ['key' => 'titleBgTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-name',
				],
			],
			'scopy' => true,
		],
		'titleBdrH' => [
			'type' => 'object',
			'default' => (object) [
				'openBorder' => 0,	
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'titleBgTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-name',
				],
			],
			'scopy' => true,
		],
		'titleBdrs' => [
			'type' => 'object',
			'default' => (object) [
				'default' => (object) [ 
					'md' => [
						"top" => '',
						"right" => '',
						"bottom" => '',
						"left" => '',
					],
					"unit" => 'px',
				],		
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'titleBgTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-name{border-radius:{{titleBdrs}} !important;}',
				],
			],
			'scopy' => true,
		],
		'titleBdrsH' => [
			'type' => 'object',
			'default' => (object) [
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],		
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [
						(object) ['key' => 'titleBgTgl', 'relation' => '==', 'value' => true],
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-name{border-radius:{{titleBdrsH}} !important;}',
				],
			],
			'scopy' => true,
		],
		'titleBsw' => [
			'type' => 'object',
			'default' => (object) [
				'openShadow' => 0,
			],
			'style' => [
				(object) [
					'condition' => [
						(object) ['key' => 'titleBgTgl', 'relation' => '==', 'value' => true],
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-name',
				],
			],
			'scopy' => true,
		],
		'titleBswH' => [
			'type' => 'object',
			'default' => (object) [
				'openShadow' => 0,
			],
			'style' => [
				(object) [
					'condition' => [
						(object) ['key' => 'titleBgTgl', 'relation' => '==', 'value' => true],
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-name',
				],
			],
			'scopy' => true,
		],
		'titleUnderline' => [
			'type' => 'boolean',
			'default' => false,
		],
		'underlineTop' => [
            'type' => 'object',
            'default' => ["unit" => 'px'],
            'style' => [
                (object) [
                    'condition' => [
						(object) ['key' => 'titleUnderline', 'relation' => '==', 'value' => true],
					],
                    'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-name:after{margin-top:{{underlineTop}};}',
                ],
            ],
			'scopy' => true,
        ],
		'underlineHeight' => [
			'type' => 'object',
			'default' => ['md' => '', 'unit' => 'px'],
			'style' => [
				(object) [
					'condition' => [
						(object) ['key' => 'titleUnderline', 'relation' => '==', 'value' => true],
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-name:after{height: {{underlineHeight}};}',
				],
			],
			'scopy' => true,
		],
		'underlineSize' => [
			'type' => 'object',
			'default' => ['md' => 30, 'unit' => 'px'],
			'style' => [
				(object) [
					'condition' => [
						(object) ['key' => 'titleUnderline', 'relation' => '==', 'value' => true],
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-name:after{width: {{underlineSize}};}',
				],
			],
			'scopy' => true,
		],
		'underlineColor' => [
			'type' => 'string',
			'default' => '#313131',
			'style' => [
				(object) [
					'condition' => [
						(object) ['key' => 'titleUnderline', 'relation' => '==', 'value' => true],
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-name:after{background-color: {{underlineColor}};}',
				],
			],
			'scopy' => true,
		],
		'underlineSizeH' => [
			'type' => 'object',
			'default' => ['md' => 60, 'unit' => 'px'],
			'style' => [
				(object) [
					'condition' => [
						(object) ['key' => 'titleUnderline', 'relation' => '==', 'value' => true],
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-name:after{width: {{underlineSizeH}};}',
				],
			],
			'scopy' => true,
		],
		'underlineColorH' => [
			'type' => 'string',
			'default' => '',
			'style' => [
				(object) [
					'condition' => [
						(object) ['key' => 'titleUnderline', 'relation' => '==', 'value' => true],
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-name:after{background-color: {{underlineColorH}};}',
				],
			],
			'scopy' => true,
		],
		'countPadding' => [
			'type' => 'object',
			'default' => (object) [ 
				'md' => ['top' => '', 'bottom' => '', 'left' => '', 'right' => ''],
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'style', 'relation' => '==', 'value' => 'style_1'], ],
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-count { padding : {{countPadding}} }',
				]
			],
			'scopy' => true,
		],
		'countExtraText' => [
			'type' => 'string',
			'default' => '',
		],
		'countWH' => [
			'type' => 'object',
			'default' => (object) [ 
				'md' => '',
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [(object) 
					['key' => 'style', 'relation' => '==', 'value' => 'style_1'],
				],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-count{ width: {{countWH}}; height: {{countWH}}; }',
				],
			],
			'scopy' => true,
		],
		'countTB' => [
			'type' => 'object',
			'default' => (object) [ 
				'md' => '',
				"unit" => 'px',
			],
			'style' => [
				(object) ['condition' => [(object)['key' => 'style', 'relation' => '==', 'value' => 'style_1'],
				],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-count{ top: {{countTB}}; }',
				],
			],
			'scopy' => true,
		],
		'countLR' => [
			'type' => 'object',
			'default' => (object) [ 
				'md' => '',
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [(object) 
					['key' => 'style', 'relation' => '==', 'value' => 'style_1'],
				],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-count{ left: {{countLR}}; }',
				],
			],
			'scopy' => true,
		],
		'countTypo' => [
			'type' => 'object',
			'default' => (object) [
				'openTypography' => 0,
				'size' => [ 'md' => 20, 'unit' => 'px' ],
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-count',
				],
			],
			'scopy' => true,
		],
		'countColor' => [
			'type' => 'string',
			'default' => '',
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-count{color: {{countColor}};}',
				],
			],
			'scopy' => true,
		],
		'countColorH' => [
			'type' => 'string',
			'default' => '',
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-count{color: {{countColorH}};}',
				],
			],
			'scopy' => true,
		],
		'countOpacity' => [
			'type' => 'string',
			'default' => [ 
				'md' => '',
				"unit" => 'px',
			],	
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'style', 'relation' => '==', 'value' => 'style_2'],],
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper.style_2 .tpgb-dy-hvr-cnt-inn  .tpgb-dy-hvr-cat-count{opacity:{{countOpacity}};}',
				],
			],
			'scopy' => true,
		],
		'countOpacityH' => [
			'type' => 'string',
			'default' => [ 
				'md' => '',
				"unit" => 'px',
			],	
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'style', 'relation' => '==', 'value' => 'style_2'],],
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper.style_2:hover .tpgb-dy-hvr-cnt-inn  .tpgb-dy-hvr-cat-count{opacity:{{countOpacityH}};}',
				],
			],
			'scopy' => true,
		],
		'countTransform' => [
			'type' => 'string',
			'default' => '',	
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'style', 'relation' => '==', 'value' => 'style_2'],],
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper.style_2 .tpgb-dy-hvr-cnt-inn .tpgb-dy-hvr-cat-count{ transform: {{countTransform}}; transform-style: preserve-3d; transition: all .3s ease-in-out; }',
				],
			],
			'scopy' => true,
		],
		'countTransformH' => [
			'type' => 'string',
			'default' => '',	
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'style', 'relation' => '==', 'value' => 'style_2'],],
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper.style_2:hover .tpgb-dy-hvr-cnt-inn .tpgb-dy-hvr-cat-count{ transform: {{countTransformH}}; transform-style: preserve-3d; transition: all .3s ease-in-out; }',
				],
			],
			'scopy' => true,
		],
		'countBg' => [
			'type' => 'object',
			'default' => (object) [
				'openBg'=> 0,
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-count',
				],
			],
			'scopy' => true,
		],
		'countBgH' => [
			'type' => 'object',
			'default' => (object) [
				'openBg'=> 0,
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-count',
				],
			],
			'scopy' => true,
		],
		'countBdr' => [
			'type' => 'object',
			'default' => (object) [
				'openBorder' => 0,	
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-count',
				],
			],
			'scopy' => true,
		],
		'countBdrH' => [
			'type' => 'object',
			'default' => (object) [
				'openBorder' => 0,	
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-count',
				],
			],
			'scopy' => true,
		],
		'countBdrs' => [
			'type' => 'object',
			'default' => (object) [
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],		
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-count{ border-radius:{{countBdrs}};}',
				],
			],
			'scopy' => true,
		],
		'countBdrsH' => [
			'type' => 'object',
			'default' => (object) [
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],		
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-count{border-radius:{{countBdrsH}};}',
				],
			],
			'scopy' => true,
		],
		'countBsw' => [
			'type' => 'object',
			'default' => (object) [
				'openShadow' => 0,
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-count',
				],
			],
			'scopy' => true,
		],
		'countBswH' => [
			'type' => 'object',
			'default' => (object) [
				'openShadow' => 0,
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-count',
				],
			],
			'scopy' => true,
		],
		'countBdrsCH' => [
			'type' => 'string',
			'default' => '',
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-count{color: {{countBdrsCH}};}',
				],
			],
			'scopy' => true,
		],
		'descMargin' => [
			'type' => 'object',
			'default' => (object) [ 
				'md' => ['top' => '', 'bottom' => '', 'left' => '', 'right' => ''],
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'style', 'relation' => '!=', 'value' => 'style_3' ] , ['key' => 'shwDesc', 'relation' => '==', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-desc { margin : {{descMargin}} }',
				]
			],
			'scopy' => true,
		],
		'DescAlignment' => [
			'type' => 'object',
			'default' => '',
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'style', 'relation' => '!=', 'value' => 'style_3' ] , ['key' => 'shwDesc', 'relation' => '==', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-desc{ text-align: {{DescAlignment}}; }',
				]
			],
			'scopy' => true,
		],
		'descTypo' => [
			'type' => 'object',
			'default' => (object) [
				'openTypography' => 0,
				'size' => [ 'md' => 20, 'unit' => 'px' ],
			],
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'style', 'relation' => '!=', 'value' => 'style_3' ] , ['key' => 'shwDesc', 'relation' => '==', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-desc',
				],
			],
			'scopy' => true,
		],
		'descColor' => [
			'type' => 'string',
			'default' => '',
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-desc{color: {{descColor}};}',
				],
			],
			'scopy' => true,
		],
		'descOpacity' => [
			'type' => 'string',
			'default' => [ 
				'md' => '',
				"unit" => 'px',
			],	
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper .tpgb-dy-hvr-cnt-inn  .tpgb-dy-hvr-cat-desc{opacity:{{descOpacity}};}',
				],
			],
			'scopy' => true,
		],
		'descColorH' => [
			'type' => 'string',
			'default' => '',
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-desc{color: {{descColorH}};}',
				],
			],
			'scopy' => true,
		],
		'descOpacityH' => [
			'type' => 'string',
			'default' => [ 
				'md' => '',
				"unit" => 'px',
			],	
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cnt-inn  .tpgb-dy-hvr-cat-desc{opacity:{{descOpacityH}};}',
				],
			],
			'scopy' => true,
		],
		'descBgTgl' => [
			'type' => 'boolean',
			'default' => false,
		],
		'descPadding' => [
			'type' => 'object',
			'default' => (object) [ 
				'md' => ['top' => '', 'bottom' => '', 'left' => '', 'right' => ''],
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'descBgTgl', 'relation' => '==', 'value' => true] ],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-desc { padding : {{descPadding}} }',
				]
			],
			'scopy' => true,
		],
		'descBg' => [
			'type' => 'object',
			'default' => (object) [
				'openBg'=> 0,
			],
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'descBgTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-desc',
				],
			],
			'scopy' => true,
		],
		'descBgH' => [
			'type' => 'object',
			'default' => (object) [
				'openBg'=> 0,
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'descBgTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-desc',

				],
			],
			'scopy' => true,
		],
		'descBdr' => [
			'type' => 'object',
			'default' => (object) [
				'openBorder' => 0,	
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'descBgTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-desc',
				],
			],
			'scopy' => true,
		],
		'descBdrH' => [
			'type' => 'object',
			'default' => (object) [
				'openBorder' => 0,	
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'descBgTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-desc',
				],
			],
			'scopy' => true,
		],
		'descBdrs' => [
			'type' => 'object',
			'default' => (object) [
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],		
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'descBgTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-desc{border-radius:{{descBdrs}};}',
				],
			],
			'scopy' => true,
		],
		'descBdrsH' => [
			'type' => 'object',
			'default' => (object) [
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],			
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'descBgTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-desc{border-radius:{{descBdrsH}};}',
				],
			],
			'scopy' => true,
		],
		'descBsw' => [
			'type' => 'object',
			'default' => (object) [
				'openShadow' => 0,
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'descBgTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cat-desc',
				],
			],
			'scopy' => true,
		],
		'descBswH' => [
			'type' => 'object',
			'default' => (object) [
				'openShadow' => 0,
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'descBgTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cat-desc',
				],
			],
			'scopy' => true,
		],
		'clSt3Padding' => [
			'type' => 'object',
			'default' => (object) [ 
				'md' => ['top' => '', 'bottom' => '', 'left' => '', 'right' => ''],
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'style', 'relation' => '==', 'value' => 'style_3']],
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper.style_3 .tpgb-dy-cnt a { padding : {{clSt3Padding}} }',
				]
			],
			'scopy' => true,
		],
		'clSt3Bg' => [
			'type' => 'object',
			'default' => (object) [
				'openBg'=> 0,
			],
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'hvrBgImg', 'relation' => '!=', 'value' => true]],
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper.style_3 .tpgb-dy-cnt a',
				],
			],
			'scopy' => true,
		],
		'clSt3BgH' => [
			'type' => 'object',
			'default' => (object) [
				'openBg'=> 0,
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'hvrBgImg', 'relation' => '!=', 'value' => true]],
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper.style_3 .tpgb-dy-cnt a:hover',

				],
			],
			'scopy' => true,
		],
		'clSt3Bdr' => [
			'type' => 'object',
			'default' => (object) [
				'openBorder' => 0,	
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper.style_3 .tpgb-dy-cnt a',
				],
			],
			'scopy' => true,
		],
		'clSt3BdrH' => [
			'type' => 'object',
			'default' => (object) [
				'openBorder' => 0,	
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper.style_3 .tpgb-dy-cnt a:hover',
				],
			],
			'scopy' => true,
		],
		'clSt3Bdrs' => [
			'type' => 'object',
			'default' => (object) [
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],	
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper.style_3 .tpgb-dy-cnt a{border-radius:{{clSt3Bdrs}};}',
				],
			],
			'scopy' => true,
		],
		'clSt3BdrsH' => [
			'type' => 'object',
			'default' => (object) [
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper.style_3 .tpgb-dy-cnt a:hover{border-radius:{{clSt3BdrsH}};}',
				],
			],
			'scopy' => true,
		],
		'clSt3Bsw' => [
			'type' => 'object',
			'default' => (object) [
				'openShadow' => 0,
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper.style_3 .tpgb-dy-cnt a',
				],
			],
			'scopy' => true,
		],
		'clSt3BswH' => [
			'type' => 'object',
			'default' => (object) [
				'openShadow' => 0,
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}} .tpgb-dynamic-wrapper.style_3 .tpgb-dy-cnt a:hover',
				],
			],
			'scopy' => true,
		],
		'clBgolColor' => [
			'type' => 'string',
			'default' => '',
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cnt{background-color: {{clBgolColor}};}',
				],
			],
			'scopy' => true,
		],
		'clBgolColorH' => [
			'type' => 'string',
			'default' => '',
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cnt{background-color: {{clBgolColorH}};}',
				],
			],
			'scopy' => true,
		],
		'clHvrCntTgl' => [
			'type' => 'boolean',
			'default' => false,
			'condition' => [
				(object) ['key' => 'style', 'relation' => '==', 'value' => 'style_1'],
			],
		],
		'clBdr' => [
			'type' => 'object',
			'default' => (object) [
				'openBorder' => 0,	
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'clHvrCntTgl', 'relation' => '!=', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper',
				],
			],
			'scopy' => true,
		],
		'clBdrs' => [
			'type' => 'object',
			'default' => (object) [
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],		
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'clHvrCntTgl', 'relation' => '!=', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper{border-radius:{{clBdrs}};}',
				],
			],
			'scopy' => true,
		],
		'clBdrHc' => [
			'type' => 'object',
			'default' => (object) [
				'openBorder' => 0,	
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'clHvrCntTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-extra-wcc-inn',
				],
			],
			'scopy' => true,
		],
		'clBdrsHc' => [
			'type' => 'object',
			'default' => (object) [
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],	
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'clHvrCntTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-extra-wcc-inn{border-radius:{{clBdrsHc}};}',
				],
			],
			'scopy' => true,
		],
		'clBdrH' => [
			'type' => 'object',
			'default' => (object) [
				'openBorder' => 0,	
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'clHvrCntTgl', 'relation' => '!=', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover',
				],
			],
			'scopy' => true,
		],
		'clBdrsH' => [
			'type' => 'object',
			'default' => (object) [
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],	
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'clHvrCntTgl', 'relation' => '!=', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover{border-radius:{{clBdrsH}};}',
				],
			],
			'scopy' => true,
		],
		'clBdrHcH' => [
			'type' => 'object',
			'default' => (object) [
				'openBorder' => 0,	
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'clHvrCntTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-extra-wcc-inn',
				],
			],
			'scopy' => true,
		],
		'clBdrsHcH' => [
			'type' => 'object',
			'default' => (object) [
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],	
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [ (object) ['key' => 'clHvrCntTgl', 'relation' => '==', 'value' => true]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-extra-wcc-inn{border-radius:{{clBdrsHcH}};}',
				],
			],
			'scopy' => true,
		],
		'clTransform' => [
			'type' => 'string',
			'default' => '',	
			'style' => [
				(object) [ 
					'condition' => [(object) ['key' => 'layout', 'relation' => '!=', 'value' => 'metro'], ],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper img, {{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cnt{ transform: {{clTransform}}; transform-style: preserve-3d; transition: all .3s ease-in-out; }',
				],
				(object) [
					'condition' => [(object) ['key' => 'layout', 'relation' => '==', 'value' => 'metro'], ],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-cat-bg-img-metro{ transform: {{clTransform}}; transform-style: preserve-3d; transition: all .3s ease-in-out; }',
				],
			],
			'scopy' => true,
		],
		'clTransformTgl' => [
			'type' => 'boolean',
			'default' => false,
		],
		'clTransformH' => [
			'type' => 'string',
			'default' => '',	
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'layout', 'relation' => '!=', 'value' => 'metro'],['key' => 'clTransformTgl', 'relation' => '!=', 'value' => true],
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover img{ transform: {{clTransformH}}; transform-style: preserve-3d ; transition: all .3s ease-in-out; }',
				],
				(object) [
					'condition' => [(object) ['key' => 'layout', 'relation' => '==', 'value' => 'metro'],['key' => 'clTransformTgl', 'relation' => '!=', 'value' => true],
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-cat-bg-img-metro{ transform: {{clTransformH}}; transform-style: preserve-3d; transition: all .3s ease-in-out; }',
				]
			],
			'scopy' => true,
		],
		'clTransformHAll' => [
			'type' => 'string',
			'default' => '',	
			'style' => [
				(object) [
					'condition' => [(object)  ['key' => 'layout', 'relation' => '!=', 'value' => 'metro'], ['key' => 'clTransformTgl', 'relation' => '==', 'value' => true],['key' => 'clHvrCntTgl', 'relation' => '!=', 'value' => true],['key' => 'style', 'relation' => '!=', 'value' => 'style_1']
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover img,{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cnt{ transform: {{clTransformHAll}}; transform-style: preserve-3d;transition: all .3s ease-in-out; }',
				],
				(object) [
					'condition' => [(object)  ['key' => 'layout', 'relation' => '!=', 'value' => 'metro'], ['key' => 'clTransformTgl', 'relation' => '==', 'value' => true],['key' => 'style', 'relation' => '==', 'value' => 'style_1'],
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover{ transform: {{clTransformHAll}}; transform-style: preserve-3d;transition: all .3s ease-in-out; }',
				],
				(object) [
					'condition' => [(object) ['key' => 'layout', 'relation' => '!=', 'value' => 'metro'],['key' => 'clTransformTgl', 'relation' => '==', 'value' => true],['key' => 'clHvrCntTgl', 'relation' => '==', 'value' => true],
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-extra-wcc-inn{ transform: {{clTransformHAll}}; transform-style: preserve-3d; transition: all .3s ease-in-out; }',
				],
			],
			'scopy' => true,
		],
		'clTransformHAllM' => [
			'type' => 'string',
			'default' => '',	
			'style' => [
				(object) [
					'condition' => [(object)  ['key' => 'layout', 'relation' => '==', 'value' => 'metro'], ['key' => 'clTransformTgl', 'relation' => '==', 'value' => true],['key' => 'style', 'relation' => '!=', 'value' => 'style_1']
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-cat-bg-img-metro,{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-cat-bg-img-metro .tpgb-dy-hvr-cnt{ transform: {{clTransformHAllM}}; transform-style: preserve-3d; transition: all .3s ease-in-out; }',
				],
				(object) [
					'condition' => [(object)  ['key' => 'layout', 'relation' => '==', 'value' => 'metro'], ['key' => 'clTransformTgl', 'relation' => '==', 'value' => true],['key' => 'style', 'relation' => '==', 'value' => 'style_1']
					],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover{ transform: {{clTransformHAllM}}; transform-style: preserve-3d; transition: all .3s ease-in-out; }',
				],
			],
			'scopy' => true,
		],
		'contentCSSFilters' => [
            'type' => 'object',
            'default' => [
                'openFilter' => false,
		        'blur' => 0,
            ],
            'style' => [
                (object) [
                    'condition' => [(object) ['key' => 'layout', 'relation' => '!=', 'value' => 'metro' ]],
                    'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper img,{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-cat-bg-img-metro',
                ],
				(object) [
                    'condition' => [(object) ['key' => 'layout', 'relation' => '==', 'value' => 'metro' ]],
                    'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-cat-bg-img-metro',
                ],
            ],
			'scopy' => true,
        ],
		'contentBswH' => [
			'type' => 'object',
			'default' => (object) [
				'openShadow' => 0,
			],
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'clHvrCntTgl', 'relation' => '!=', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper',
				],
				(object) [
					'condition' => [(object) ['key' => 'clHvrCntTgl', 'relation' => '==', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-extra-wcc-inn',
				],
			],
			'scopy' => true,
		],
		'contentCSSFiltersH' => [
            'type' => 'object',
            'default' => [
                'openFilter' => false,
		        'blur' => 0,
            ],
            'style' => [
                (object) [
                    'condition' => [(object) ['key' => 'layout', 'relation' => '!=', 'value' => 'metro' ]],
                    'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover img',
                ],
				(object) [
                    'condition' => [(object) ['key' => 'layout', 'relation' => '==', 'value' => 'metro' ]],
                    'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-cat-bg-img-metro',
                ],
            ],
			'scopy' => true,
        ],
		'contentBswHH' => [
			'type' => 'object',
			'default' => (object) [
				'openShadow' => 0,
			],
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'clHvrCntTgl', 'relation' => '!=', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover',
				],
				(object) [
					'condition' => [(object) ['key' => 'clHvrCntTgl', 'relation' => '==', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-extra-wcc-inn',
				]
			],
			'scopy' => true,
		],
		'transDur' => [
			'type' => 'string',
			'default' => '0.5',
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'layout', 'relation' => '!=', 'value' => 'metro' ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper img, {{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cnt{ transition-duration: {{transDur}}s; }',
				],
				(object) [
					'condition' => [(object) ['key' => 'layout', 'relation' => '==', 'value' => 'metro' ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-cat-bg-img-metro { transition-duration: {{transDur}}s; }',
				]
			],
			'scopy' => true,
		],
		'clInnerSwitch' => [
			'type' => 'boolean',
			'default' => false,
		],
		'clOutPadding' => [
			'type' => 'object',
			'default' => (object) [ 
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'clInnerSwitch', 'relation' => '==', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper.style_1 .tpgb-dy-hvr-cnt,{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper.style_2 .tpgb-dy-hvr-cnt {padding: {{clOutPadding}};}',
				],
			],
			'scopy' => true,
		],
		'clInnPadding' => [
			'type' => 'object',
			'default' => (object) [ 
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'clInnerSwitch', 'relation' => '==', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cnt-inn {padding: {{clInnPadding}};}',
				],
			],
			'scopy' => true,
		],
		'clInnBgColor' => [
			'type' => 'string',
			'default' => '',
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'clInnerSwitch', 'relation' => '==', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cnt-inn{background-color: {{clInnBgColor}};}',
				],
			],
			'scopy' => true,
		],
		'clInnBgColorH' => [
			'type' => 'string',
			'default' => '',
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'clInnerSwitch', 'relation' => '==', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cnt-inn{background-color: {{clInnBgColorH}};}',
				],
			],
			'scopy' => true,
		],
		'clInnBdr' => [
			'type' => 'object',
			'default' => (object) [
				'openBorder' => 0,	
			],
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'clInnerSwitch', 'relation' => '==', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cnt-inn',
				],
			],
			'scopy' => true,
		],
		'clInnBdrColorH' => [
			'type' => 'string',
			'default' => '',
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'clInnerSwitch', 'relation' => '==', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper:hover .tpgb-dy-hvr-cnt-inn{ border-color: {{clInnBdrColorH}};}',
				],
			],
			'scopy' => true,
		],
		'clInnBdrs' => [
			'type' => 'object',
			'default' => (object) [
				'md' => [
					"top" => '',
					"right" => '',
					"bottom" => '',
					"left" => '',
				],
				"unit" => 'px',
			],
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'clInnerSwitch', 'relation' => '==', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-dy-hvr-cnt-inn{ border-radius:{{clInnBdrs}};}',
				],
			],
			'scopy' => true,
		],
		'overflowHidden' => [
			'type' => 'string',
			'default' => 'hidden',
			'style' => [
				(object) [
					'condition' => [(object) ['key' => 'style', 'relation' => '!=', 'value' => 'style_3'],['key' => 'clHvrCntTgl', 'relation' => '!=', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper.style_1,{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper.style_2{ overflow: {{overflowHidden}} !important; }',
				],
				(object) [
					'condition' => [(object) ['key' => 'clHvrCntTgl', 'relation' => '==', 'value' => true ]],
					'selector' => '{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper .tpgb-extra-wcc-inn{ overflow: {{overflowHidden}} !important; transition: all .3s ease-in-out; }','{{PLUS_WRAP}}.tpgb-dy-cat-list .tpgb-dynamic-wrapper.style_1{ overflow: visible !important; }',
				]
			],
		],
		'notxtTypo' => [
			'type' => 'object',
			'default' => (object) [
				'openTypography' => 0,
				'size' => [ 'md' => 20, 'unit' => 'px' ],
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}} .tpgb-no-posts-found',
				],
			],
			'scopy' => true,
		],
		'notxtcolor' => [
			'type' => 'string',
			'default' => '',
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}} .tpgb-no-posts-found{ color : {{notxtcolor}} }',
				],
			],
			'scopy' => true,
		],
		'notxtBg' => [
			'type' => 'object',
			'default' => (object) [
				'openBg'=> 0,
			],
			'style' => [
				(object) [
					'selector' => '{{PLUS_WRAP}} .tpgb-no-posts-found',
				],
			],
			'scopy' => true,
		],
	);

	$attributesOptions = array_merge($attributesOptions,$globalBgOption,$carousel_options,$globalpositioningOption, $globalPlusExtrasOption);
	
	register_block_type( 'tpgb/tp-dynamic-category', array(
		'attributes' => $attributesOptions,
		'editor_script' => 'tpgb-block-editor-js',
		'editor_style'  => 'tpgb-block-editor-css',
        'render_callback' => 'tpgb_tp_dynamic_category_render_callback'
    ) );
}
add_action( 'init', 'tpgb_dynamic_category' );

function tpgb_postCate_query($attr){
	$include_posts = ($attr['includePosts']) ? explode(',', $attr['includePosts']) : '';
	$exclude_posts = ($attr['excludePosts']) ? explode(',', $attr['excludePosts']) : '';
	$taxonomySlug = (!empty($attr['taxonomySlug'])) ? ($attr['taxonomySlug']) : '';
	$offsetPosts = (!empty($attr['offsetPosts'])) ? ($attr['offsetPosts']) : '';
	$customQueryId = (!empty($attr['customQueryId'])) ? $attr['customQueryId'] : '';

	$cat_arg = array(
		'post_status'     => 'publish',
		'number'      => ( $attr['displayPosts'] ) ? intval($attr['displayPosts']) : -1,
		'offset' => $offsetPosts,
		'orderby'      =>  ($attr['orderBy']) ? $attr['orderBy'] : 'date',
		'order'      => ($attr['order']) ? $attr['order'] : 'desc',
		'exclude'  => $exclude_posts,
		'include'   => $include_posts,	
		'hide_empty' => ( isset($attr['hideEmpty']) && !empty($attr['hideEmpty']) ? 1 : 0 ),
		'parent' => (($attr['hideSubCat']) && $attr['hideSubCat'] == true) ? 0 : '',
	);

	/*custom query id*/
	if( !empty( $customQueryId ) ){
		if( has_filter( $customQueryId )) {
			$cat_arg = apply_filters( $customQueryId , $cat_arg);
		}
	}

	$queryArgs = get_terms( $taxonomySlug , $cat_arg );
	
	return $queryArgs;
}