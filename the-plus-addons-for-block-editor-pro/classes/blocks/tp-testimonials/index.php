<?php
/* Block : Testimonials
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_testimonials_render_callback( $attributes, $content) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
    $style = (!empty($attributes['style'])) ? $attributes['style'] : 'style-1';
    $styleLayout = (!empty($attributes['styleLayout'])) ? $attributes['styleLayout'] : 'style-1';
    $style4Alignment = (!empty($attributes['style4Alignment'])) ? $attributes['style4Alignment'] : 'left';
    
	$ItemRepeater = (!empty($attributes['ItemRepeater'])) ? $attributes['ItemRepeater'] : [];
    $carouselId = (!empty($attributes['carouselId'])) ? $attributes['carouselId'] : '';
	
	$showArrows = (!empty($attributes['showArrows'])) ? $attributes['showArrows'] : [ 'md' => false ];
	$arrowsStyle = (!empty($attributes['arrowsStyle'])) ? $attributes['arrowsStyle'] : 'style-1';
	$arrowsPosition = (!empty($attributes['arrowsPosition'])) ? $attributes['arrowsPosition'] : 'top-right';
	$rating = (!empty($attributes['rating'])) ? $attributes['rating'] : false;
	$telayout = (!empty($attributes['telayout'])) ? $attributes['telayout'] : '';

	$descByLimit	= !empty($attributes['descByLimit']) ? $attributes['descByLimit'] : '';
	$descLimit = !empty($attributes['descLimit']) ? $attributes['descLimit'] : '' ;
	$cntscrollOn = (!empty($attributes['cntscrollOn'])) ? $attributes['cntscrollOn'] : '';
	$caroByheight = (!empty($attributes['caroByheight'])) ? $attributes['caroByheight'] : '';

	$titleByLimit = !empty($attributes['titleByLimit']) ? $attributes['titleByLimit'] : '';
	$titleLimit = !empty($attributes['titleLimit']) ? $attributes['titleLimit'] : '' ;

	$redmorTxt = (!empty($attributes['redmorTxt'])) ? $attributes['redmorTxt'] : '';
	$redlesTxt = (!empty($attributes['redlesTxt'])) ? $attributes['redlesTxt'] : '';

	$starIcon = (!empty($attributes['starIcon'])) ? $attributes['starIcon'] : '';
	$sIcon = (!empty($attributes['sIcon'])) ? $attributes['sIcon'] : '';

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	//Carousel Options
	$carousel_settings = Tp_Blocks_Helper::carousel_settings( $attributes );

	$equalHeightAtt = Tpgbp_Pro_Blocks_Helper::global_equal_height( $attributes );
	$equalHclass = '';
	if(!empty($equalHeightAtt)){
		$equalHclass = ' tpgb-equal-height';
	}
	
	$Style3Layout ='';
	if($style=='style-3' && !empty($styleLayout)){
		$Style3Layout ='layout-'.$styleLayout;
	}
	$style4Class = '';
	if($style=="style-4" && $style4Alignment){
		$style4Class = ' content-'.$style4Alignment;
	}
		
	$Sliderclass = $dataAttr = '';
	if($telayout == 'carousel'){
		$dataAttr .= 'data-splide=\'' . wp_json_encode($carousel_settings) . '\' ';
		$Sliderclass = Tpgbp_Pro_Blocks_Helper::tpgb_carousel_arrowdot_class($attributes);
		if(!empty($carouselId)){
			$dataAttr .=' id="tpca-'.esc_attr($carouselId).'"';
			$dataAttr .=' data-id="tpca-'.esc_attr($carouselId).'"';
			$dataAttr .=' data-connection="tptab_'.esc_attr($carouselId).'"';
		}
	}

	$readAttr = [];
	$attr = '';
	if($telayout == 'masonry' || ( $telayout == 'carousel' && $caroByheight == 'text-limit' )){
		
		$readAttr['readMore'] = $redmorTxt;
		$readAttr['readLess'] = $redlesTxt;
		
		$readAttr = htmlspecialchars(wp_json_encode($readAttr), ENT_QUOTES, 'UTF-8');

		$attr = 'data-readData = \'' .$readAttr. '\'';
	}

	$list_layout = '';
	if($telayout=='grid' || $telayout=='masonry'){
		$list_layout = 'tpgb-isotope';
	}else if($telayout=='carousel'){
		$list_layout = 'tpgb-carousel splide';
	}

	$column_class = ' tpgb-col';
	if( $telayout!='carousel' && !empty($attributes['columns']) && is_array($attributes['columns'])){
		$column_class .= isset($attributes['columns']['md']) ? " tpgb-col-lg-".$attributes['columns']['md'] : ' tpgb-col-lg-3';
		$column_class .= isset($attributes['columns']['sm']) ? " tpgb-col-md-".$attributes['columns']['sm'] : ' tpgb-col-md-4';
		$column_class .= isset($attributes['columns']['xs']) ? " tpgb-col-sm-".$attributes['columns']['xs'] : ' tpgb-col-sm-6';
		$column_class .= isset($attributes['columns']['xs']) ? " tpgb-col-".$attributes['columns']['xs'] : ' tpgb-col-6';
	}

    $output .= '<div class="tpgb-testimonials tpgb-relative-block testimonial-'.esc_attr($style).' '.esc_attr($Style3Layout).' '.esc_attr($Sliderclass).' tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).' '.esc_attr($equalHclass).' '.esc_attr($list_layout).' " '.$dataAttr.' '.$equalHeightAtt.' data-layout="'.esc_attr($telayout).'" data-id="'.esc_attr($block_id).'">';
		if( $telayout == 'carousel' && ( isset($showArrows['md']) && !empty($showArrows['md']) ) || ( isset($showArrows['sm']) && !empty($showArrows['sm']) ) || ( isset($showArrows['xs']) && !empty($showArrows['xs']) ) ){
			$output .= Tp_Blocks_Helper::tpgb_carousel_arrow($arrowsStyle,$arrowsPosition);
		}
		$output .= '<div class="'.($telayout == 'carousel' ? 'splide__track' : 'tpgb-row').' post-loop-inner '.esc_attr($style4Class).'">';
			if($telayout == 'carousel'){
				$output .= '<div class="splide__list">';
			}
				if( !empty( $ItemRepeater ) ){
					foreach ( $ItemRepeater as $index => $item ) :
						if(is_array($item)){

							$itemContent = '';
							if( !empty($item['content']) ){
								if($descByLimit == 'default'){
									$itemContent .= '<div class="entry-content scroll-'.esc_attr($cntscrollOn).'">';
										$itemContent .= wp_kses_post($item['content']);
									$itemContent .= '</div>';
								}else{
									if( $telayout == 'masonry' || ( $telayout == 'carousel' && $caroByheight == 'text-limit' ) ){
										if( $descByLimit === 'words' ){
											$total = explode(' ', $item['content']);
											$limit_words = explode(' ', $item['content'], $descLimit);
											$ltn = count($limit_words);
											$remaining_words = implode(" " , array_slice($total, $descLimit-1));
											if (count($limit_words)>=$descLimit) {
												array_pop($limit_words);
												$excerpt = implode(" ",$limit_words).' <span class="testi-more-text" style="display: none">'.wp_kses_post($remaining_words).'</span><a '.$attr.' class="testi-readbtn">'.esc_attr($redmorTxt).'</a>';
											} else {
												$excerpt = implode(" ",$limit_words);
											}
											
										}else if( $descByLimit === 'letters' ){
											$ltn = strlen($item['content']);
											$limit_words = substr($item['content'],0,$descLimit); 
											$remaining_words = substr($item['content'], $descLimit, $ltn);
											if(strlen($item['content'])>$descLimit){
												$excerpt = $limit_words.'<span class="testi-more-text" style="display:none">'.wp_kses_post($remaining_words).'</span><a '.$attr.' class="testi-readbtn">'.esc_attr($redmorTxt).'</a>';
											}else{
												$excerpt = $limit_words;
											}
										}
										$itemContent .= '<div class="entry-content">'.$excerpt.'</div>';
									}else{
										$itemContent .= '<div class="entry-content scroll-'.esc_attr($cntscrollOn).'">';
											$itemContent .= wp_kses_post($item['content']);
										$itemContent .= '</div>';
									}
								}
							}
							
							$itemAuthorTitle = $title = '';
							if( !empty($item['authorTitle']) ){

								if($telayout != 'carousel'){
									$itemAuthorTitle .= '<h3 class="testi-author-title title-scroll-'.esc_attr($cntscrollOn).'">'.wp_kses_post($item['authorTitle']).'</h3>';
								}else{
									if( $titleByLimit === 'words' ){
										$titotal = explode(' ', $item['authorTitle']);
										$tilimit_words = explode(' ', $item['authorTitle'], $titleLimit);
										$tiltn = count($tilimit_words);
										$tiremaining_words = implode(" " , array_slice($titotal, $titleLimit-1));
										if (count($tilimit_words)>=$titleLimit) {
											array_pop($tilimit_words);
											$title = implode(" ",$tilimit_words).' <span class="testi-more-text" style="display: none">'.wp_kses_post($tiremaining_words).'</span><a '.$attr.' class="testi-readbtn">'.esc_attr($redmorTxt).'</a>';
										} else {
											$title = implode(" ",$tilimit_words);
										}
										
									}else if( $titleByLimit === 'letters' ){
										$tiltn = strlen($item['authorTitle']);
										$tilimit_words = substr($item['authorTitle'],0,$titleLimit); 
										$tiremaining_words = substr($item['authorTitle'], $titleLimit, $tiltn);
										if(strlen($item['authorTitle'])>$titleLimit){
											$title = $tilimit_words.'<span class="testi-more-text" style="display:none">'.wp_kses_post($tiremaining_words).'</span><a '.$attr.' class="testi-readbtn">'.esc_attr($redmorTxt).'</a>';
										}else{
											$title = $tilimit_words;
										}
									}else{
										$title = $item['authorTitle'];
									}

									$itemAuthorTitle .= '<h3 class="testi-author-title">'.$title.'</h3>';
								}

								
							}
							
							$itemTitle ='';
							if(!empty($item['testiTitle'])){
								$itemTitle .= '<div class="testi-post-title">'.wp_kses_post($item['testiTitle']).'</div>';
							}
							
							$itemDesignation ='';
							if(!empty($item['designation'])){
								$itemDesignation .= '<div class="testi-post-designation">'.wp_kses_post($item['designation']).'</div>';
							}
							
							//Star Rating
							$itemStarAct = '';
							if(!empty($item['starRating'])){
								$nuMatch = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['starRating']);
								$racAct = (int) $nuMatch;
								for($i=0;$i<$racAct;$i++){
									$itemStarAct .= '<span class="tpgb-testi-star checked '.($starIcon == 'custom' ? esc_attr($sIcon) : 'fa fa-star' ).'"></span>';
								}
							}

							$itemStarnor = '';
							if(!empty($item['starRating'])){
								$renuMatch = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['starRating']);
								$ratDis =  5 - (int) $renuMatch;
								for($i=0;$i<$ratDis;$i++){
									$itemStarnor .= '<span class="tpgb-testi-star '.($starIcon == 'custom' ? esc_attr($sIcon) : 'fa fa-star' ).'"></span>';
								}
							}
							
							$imgUrl ='';
							$altText = (isset($item['avatar']['alt']) && !empty($item['avatar']['alt'])) ? esc_attr($item['avatar']['alt']) : ((!empty($item['avatar']['title'])) ? esc_attr($item['avatar']['title']) : esc_attr__('Author Avatar','tpgbp'));

							if(!empty($item['avatar']) && !empty($item['avatar']['id'])){
								$imgUrl = wp_get_attachment_image($item['avatar']['id'],'medium', false, ['alt'=> $altText]);
							}else if(!empty($item['avatar']) && !empty($item['avatar']['url'])){
								$urlImg = (isset($item['avatar']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['avatar']) : (!empty($item['avatar']['url']) ? $item['avatar']['url'] : '');
								$imgUrl = '<img src="'.esc_url($urlImg).'" alt="'.$altText.'"/>';
							}else{
								$imgUrl ='<img src="'.esc_url(TPGB_URL.'assets/images/tpgb-placeholder-grid.jpg').'" alt="'.esc_html__('Author Avatar','tpgbp').'"/>';
							}
							//'.$telayout=='carousel' ? 'splide__slide' : $column_class .'
							$output .= '<div class="grid-item tp-repeater-item-'. ( isset($item['_key']) ? esc_attr($item['_key']) : '' ) . ' '.($telayout=='carousel' ? 'splide__slide' : $column_class).'">';
								$output .= '<div class="testimonial-list-content'.( $style=='style-4' ? ' tpgb-align-items-center tpgb-d-flex tpgb-flex-row tpgb-flex-wrap' : '').'">';
									
									if($style!='style-4'){
										$output .= '<div class="testimonial-content-text">';
											if($style=="style-1" || $style=="style-2"){
												if($style=="style-2" && !empty($rating)){
													$output .= '<div class="tpgb-testim-rating">';
														$output .= $itemStarAct;
														$output .= $itemStarnor;
													$output .= '</div>';
												}
												$output .= $itemContent;
												$output .= $itemAuthorTitle;
											}
											if($style=="style-3"){
												$output .= $itemAuthorTitle;
												$output .= $itemContent;
											}
										$output .= '</div>';
									}
									
									$output .= '<div class="post-content-image'.($style=='style-4' ? ' tpgb-flex-column tpgb-flex-wrap' : '').'">';
										$output .= '<div class="author-thumb">';
											$output .= $imgUrl;
										$output .= '</div>';
										if($style=="style-1" || $style=="style-2"){
											$output .= $itemTitle;
											$output .= $itemDesignation;
											if($style=="style-1" && !empty($rating)){
												$output .= '<div class="tpgb-testim-rating">';
													$output .= $itemStarAct;
													$output .= $itemStarnor;
												$output .= '</div>';
											}
										}
										if($style=="style-3"){
											$output .= '<div class="author-left-text">';
												$output .= $itemTitle;
												$output .= $itemDesignation;
											$output .= '</div>';
											if(!empty($rating)){
												$output .= '<div class="tpgb-testim-rating">';
													$output .= $itemStarAct;
													$output .= $itemStarnor;
												$output .= '</div>';
											}
										}
									$output .= '</div>';
									
									if($style=='style-4'){
										$output .= '<div class="testimonial-content-text tpgb-flex-column tpgb-flex-wrap">';
											if(!empty($rating)){
												$output .= '<div class="tpgb-testim-rating">';
													$output .= $itemStarAct;
													$output .= $itemStarnor;
												$output .= '</div>';
											}
											$output .= $itemAuthorTitle;
											$output .= $itemContent;
											$output .= '<div class="author-left-text">';
												$output .= $itemTitle;
												$output .= $itemDesignation;
											$output .= '</div>';
										$output .= '</div>';
									}
									
								$output .= "</div>";
							$output .= "</div>";
						}
					endforeach;
				}
			if($telayout == 'carousel'){
				$output .= '</div>';
			}
		$output .= "</div>";
    $output .= "</div>";
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	$arrowCss = Tp_Blocks_Helper::tpgb_carousel_arrow_css( $showArrows , $block_id );
	if( !empty($arrowCss) ){
		$output .= $arrowCss;
	}
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_testimonials() {
	
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_testimonials_render_callback', true, true);
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_testimonials' );