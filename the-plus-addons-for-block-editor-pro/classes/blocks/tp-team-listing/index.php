<?php
/* Block : Team Member Listing
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_team_member_listing_render_callback( $attributes, $content) {
	$TeamMember = '';
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$style = (!empty($attributes['Style'])) ? $attributes['Style'] : 'style-1';
	$layout = (!empty($attributes['layout'])) ? $attributes['layout'] : 'grid';
	$DisbleLink = (!empty($attributes['DisLink'])) ? $attributes['DisLink'] : false;
	$TeamMemberR = (!empty($attributes['TeamMemberR'])) ? $attributes['TeamMemberR'] : [];
	$columns = (!empty($attributes['columns'])) ? $attributes['columns'] : 'md';
	$TitleTag = (!empty($attributes['TitleTag'])) ? $attributes['TitleTag'] : 'h3';
	$Designation = (!empty($attributes['DesignDis'])) ? $attributes['DesignDis'] : false;
	$DisableIcon = (!empty($attributes['SocialIcon'])) ? $attributes['SocialIcon'] : false;
	$DisableISize = (!empty($attributes['DImgS'])) ? $attributes['DImgS'] : false;
	$FImageTp = (!empty($attributes['FImageTp'])) ? $attributes['FImageTp'] : 'full';
	$ImageSize = (!empty($attributes['ImgSize'])) ? $attributes['ImgSize'] : 'full';
	$CategoryWF = (!empty($attributes['CategoryWF'])) ? $attributes['CategoryWF'] : '';
	$Categoryclass = (!empty($CategoryWF)) ? 'tpgb-category-filter' : '';
	$MaskImg = (isset($attributes['MaskImg']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['MaskImg']) : (!empty($attributes['MaskImg']['url']) ? $attributes['MaskImg']['url'] : '');
	$ExLImg = (isset($attributes['ExLImg']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['ExLImg']) : (!empty($attributes['ExLImg']['url']) ? $attributes['ExLImg']['url'] : '');
	$AnimationIMG = (!empty($attributes['AExlImg'])) ? $attributes['AExlImg'] : 'none';
	$AniToggle = (!empty($attributes['HAnimation'])) ? $attributes['HAnimation'] : false;

	$showArrows = (!empty($attributes['showArrows'])) ? $attributes['showArrows'] : [ 'md' => false ];
	$arrowsStyle = (!empty($attributes['arrowsStyle'])) ? $attributes['arrowsStyle'] : 'style-1';
	$arrowsPosition = (!empty($attributes['arrowsPosition'])) ? $attributes['arrowsPosition'] : 'top-right';
	
	$Default_Img = TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg';

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$list_layout='';
	if( $layout=='grid' || $layout=='masonry' ){
		$list_layout = 'tpgb-isotope';
	}else if( $layout =='metro' ){
		$list_layout = 'tpgb-metro';
	}else if( $layout =='carousel' ){
		$list_layout = 'tpgb-carousel splide';	
	}else{
		$list_layout = 'tpgb-isotope';
	}

	$desktop_class = '';
	if( $layout !='carousel' && $layout !='metro' && $columns ){
		$desktop_class .= 'tpgb-col-'.esc_attr($columns['xs']);
		$desktop_class .= ' tpgb-col-lg-'.esc_attr($columns['md']);
		$desktop_class .= ' tpgb-col-md-'.esc_attr($columns['sm']);
		$desktop_class .= ' tpgb-col-sm-'.esc_attr($columns['xs']);
	}

	$carousel_settings = $Sliderclass = '';
	if($layout=='carousel'){
		$carousel_settings = Tp_Blocks_Helper::carousel_settings( $attributes );
		$Sliderclass = Tpgbp_Pro_Blocks_Helper::tpgb_carousel_arrowdot_class($attributes);
	}
	
	$TeamMember .= '<div id="'.esc_attr($block_id).'" class="tpgb-block-'.esc_attr($block_id).' tpgb-relative-block  tpgb-team-member-list team-'.esc_attr($style).' '.esc_attr($list_layout).' '.esc_attr($Categoryclass).' '.esc_attr($Sliderclass).' '.esc_attr($blockClass).' " data-style="'.esc_attr($style).'" data-layout="'.esc_attr($layout).'" data-id="'.esc_attr($block_id).'" data-splide=\''.wp_json_encode($carousel_settings).'\'>';
		if(!empty($CategoryWF) && $layout != 'carousel'){
			$TeamMember .= TMCategoryFilter($attributes);
		}
		if( $layout == 'carousel' && ( ( isset($showArrows['md']) && !empty($showArrows['md']) ) || ( isset($showArrows['sm']) && !empty($showArrows['sm']) ) || ( isset($showArrows['xs']) && !empty($showArrows['xs']) ) ) ){
			$TeamMember .= Tp_Blocks_Helper::tpgb_carousel_arrow($arrowsStyle,$arrowsPosition);
		}
		$TeamMember .= '<div class="post-loop-inner '.($layout=='carousel' ? 'splide__track' : ' tpgb-row').'">';
			if($layout=='carousel'){
				$TeamMember .= '<div class="splide__list">';
			}
			if( !empty($TeamMemberR) ){
				foreach ( $TeamMemberR as $index => $TeamItem ) {
					$TeamName = ( !empty($TeamItem['TName']) ) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($TeamItem['TName']) : '';
					$TeamDesignation = ( !empty($TeamItem['TDesig']) ) ? $TeamItem['TDesig'] : '';
					$ImgId = ( !empty($TeamItem['TImage']) ) ? $TeamItem['TImage'] : [];
					$TeamCUrl = (isset($TeamItem['CusUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($TeamItem['CusUrl']) : (!empty($TeamItem['CusUrl']['url']) ? $TeamItem['CusUrl']['url'] : '');
					$TeamWsUrl = (isset($TeamItem['WsUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($TeamItem['WsUrl']) : (!empty($TeamItem['WsUrl']['url']) ? $TeamItem['WsUrl']['url'] : '');
					$TeamFbUrl = (isset($TeamItem['FbUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($TeamItem['FbUrl']) : (!empty($TeamItem['FbUrl']['url']) ? $TeamItem['FbUrl']['url'] : '');
					$TeamMailUrl = (isset($TeamItem['MailUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($TeamItem['MailUrl']) : (!empty($TeamItem['MailUrl']['url']) ? $TeamItem['MailUrl']['url'] : '');
					$TeamIGUrl = (isset($TeamItem['IGUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($TeamItem['IGUrl']) : (!empty($TeamItem['IGUrl']['url']) ? $TeamItem['IGUrl']['url'] : '');
					$TeamTwUrl = (isset($TeamItem['TwUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($TeamItem['TwUrl']) : (!empty($TeamItem['TwUrl']['url']) ? $TeamItem['TwUrl']['url'] : '');
					$TeamldUrl = (isset($TeamItem['ldUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($TeamItem['ldUrl']) : (!empty($TeamItem['ldUrl']['url']) ? $TeamItem['ldUrl']['url'] : '');
					$TeamCategory = ( !empty($TeamItem['TCateg']) ) ? $TeamItem['TCateg'] : '';
					$CustomTarget = ( !empty($TeamItem['CusUrl']) && !empty($TeamItem['CusUrl']['target']) ) ? 'target="_blank"' : '';
					$CustomRel = ( !empty($TeamItem['CusUrl']) && !empty($TeamItem['CusUrl']['nofollow']) ) ? 'rel="nofollow"' : '';
					$Telephone = ( !empty($TeamItem['TelNum']) ) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($TeamItem['TelNum']) : '';

					$category_filter=$loop_category='';						
					if( !empty($CategoryWF) && !empty($TeamCategory)  && $layout != 'carousel' ){
						$loop_category = explode(',', $TeamCategory);
						foreach( $loop_category as $category ) {
							$category = TM_Media_createSlug($category);
							$category_filter .= ' '.$category;
						}
					}

					// Set Default Image Url
					if(empty($ImgId)){
						$ImgId['url'] = $Default_Img;
					}

					$TeamMember .= '<div class="grid-item '.($layout =='carousel' ? 'splide__slide' : esc_attr($desktop_class)).' '.esc_attr($category_filter).'">';							
						$TeamMember .= '<div class="team-list-content tpgb-trans-linear">';
						
								$ImageHTML = $TeamImage = $AttImg = '';
								if(!empty($TeamCUrl) || !empty($ImgId)){
								
									if(!empty($ImgId)){
										$linkImage = '';
										$altText = (isset($ImgId['alt']) && !empty($ImgId['alt'])) ? esc_attr($ImgId['alt']) : ((!empty($ImgId['title'])) ? esc_attr($ImgId['title']) : esc_attr__('Profile Image','tpgbp'));
										if( $layout !='carousel' && !empty($DisableISize) ){
											if(!empty($ImgId['id'])){
												$AttImg .= wp_get_attachment_image($ImgId['id'] , $ImageSize, false, ['alt'=> $altText]);
											}else if(!empty($ImgId['url'])){
												$imgUrl = (isset($ImgId['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($ImgId) : (!empty($ImgId['url']) ? $ImgId['url'] : '');
												$AttImg .= '<img src="'.esc_url($imgUrl).'" alt="'.$altText.'"/>';
											}else{
												$AttImg .= '<img src="'.esc_url($Default_Img).'" alt="'.$altText.'"/>';
											}
											$TeamImage .= $AttImg;
										}else{
											if( $FImageTp != 'custom' ){
												$ImageSize = $FImageTp;
											}
											if(!empty($ImgId['id'])){
												
												$AttImg .= wp_get_attachment_image($ImgId['id'] , 'full' , false, ['alt'=> $altText]);
											}else if(!empty($ImgId['url'])){
												$imgUrl = (isset($ImgId['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($ImgId) : (!empty($ImgId['url']) ? $ImgId['url'] : '');
												$AttImg .= '<img src="'.esc_url($imgUrl).'" alt="'.$altText.'"/>';
											}else{
												$AttImg .= '<img src="'.esc_url($Default_Img).'" alt="'.$altText.'"/>';
											}
											$TeamImage .= $AttImg;
										}

										$linkImage .= '<div class="tpgb-team-profile">';
											$linkImage .= '<span class="thumb-wrap">'.$TeamImage.'</span>';
										$linkImage .= '</div>';

										if(!empty($DisbleLink)){
											$ImageHTML .= $linkImage;
										}else{
											$link_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['CusUrl']);
											$ImageHTML .= '<a href="'.esc_url($TeamCUrl).'" '.$CustomTarget.' '.$CustomRel.' '.$link_attr.' aria-label="'.esc_attr($TeamName).'">'.$linkImage.'</a>';
										}
									}
								}

								$IconHTML = '';
								if( !empty($DisableIcon) ){
									$Nofollow=$Target="";

									$IconHTML .= '<div class="tpgb-team-social-content">';
										$IconHTML .= '<div class="tpgb-team-social-list">';
											if( !empty($TeamWsUrl) ){
												$wb_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['WsUrl']);
												$Target = ( !empty($TeamItem['WsUrl']) && !empty($TeamItem['WsUrl']['target']) ) ? 'target="_blank"' : "";
												$Nofollow = ( !empty($TeamItem['WsUrl']) && !empty($TeamItem['WsUrl']['nofollow']) ) ? 'rel="nofollow"' : "";
												$IconHTML .= '<div class="tpgb-team-profile-link">';
													$IconHTML .= '<a href="'.esc_url($TeamWsUrl).'" '.$Target.' '.$Nofollow.' '.$wb_attr.'  aria-label="'.esc_attr__('Site URL','tpgbp').'"><i class="fas fa-globe" aria-hidden="true"></i></a>';
												$IconHTML .= '</div>';
											}
											if( !empty($TeamFbUrl) ){
												$fb_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['FbUrl']);
												$Target = ( !empty($TeamItem['FbUrl']) && !empty($TeamItem['FbUrl']['target']) ) ? 'target="_blank"' : "";
												$Nofollow = ( !empty($TeamItem['FbUrl']) && !empty($TeamItem['FbUrl']['nofollow']) ) ? 'rel="nofollow"' : "";
												$IconHTML .= '<div class="fb-link">';
													$IconHTML .= '<a href="'.esc_url($TeamFbUrl).'" '.$Target.' '.$Nofollow.' '.$fb_attr.' aria-label="'.esc_attr__('Facebook','tpgbp').'"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>';
												$IconHTML .= '</div>';
											}
											if( !empty($TeamTwUrl) ){
												$tw_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['TwUrl']);
												$Target = ( !empty($TeamItem['TwUrl']) && !empty($TeamItem['TwUrl']['target']) ) ? 'target="_blank"' : "";
												$Nofollow = ( !empty($TeamItem['TwUrl']) && !empty($TeamItem['TwUrl']['nofollow']) ) ? 'rel="nofollow"' : "";
												$IconHTML .= '<div class="twitter-link">';
													$IconHTML .= '<a href="'.esc_url($TeamTwUrl).'" '.$Target.' '.$Nofollow.' '.$tw_attr.' aria-label="'.esc_attr__('Twitter','tpgbp').'"><i class="fab fa-twitter" aria-hidden="true"></i></a>';
												$IconHTML .= '</div>';
											}
											if( !empty($TeamIGUrl) ){
												$ig_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['IGUrl']);
												$Target = ( !empty($TeamItem['IGUrl']) && !empty($TeamItem['IGUrl']['target']) ) ? 'target="_blank"' : "";
												$Nofollow = ( !empty($TeamItem['IGUrl']) && !empty($TeamItem['IGUrl']['nofollow']) ) ? 'rel="nofollow"' : "";
												$IconHTML .= '<div class="instagram-link">';
													$IconHTML .= '<a href="'.esc_url($TeamIGUrl).'" '.$Target.' '.$Nofollow.' '.$ig_attr.' aria-label="'.esc_attr__('Instagram','tpgbp').'"><i class="fab fa-instagram" aria-hidden="true"></i></a>';
												$IconHTML .= '</div>';
											}
											if( !empty($TeamMailUrl) ){
												$ml_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['MailUrl']);
												$Target = ( !empty($TeamItem['MailUrl']) && !empty($TeamItem['MailUrl']['target']) ) ? 'target="_blank"' : "";
												$Nofollow = ( !empty($TeamItem['MailUrl']) && !empty($TeamItem['MailUrl']['nofollow']) ) ? 'rel="nofollow"' : "";
												$IconHTML .= '<div class="mail-link">';
													$IconHTML .= '<a href="'.esc_url($TeamMailUrl).'" '.$Target.' '.$Nofollow.' '.$ml_attr.' aria-label="'.esc_attr__('Mail','tpgbp').'"><i class="fas fa-envelope-square"></i></a>';
												$IconHTML .= '</div>';
											}
											if( !empty($TeamldUrl) ){
												$ld_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['ldUrl']);
												$Target = ( !empty($TeamItem['ldUrl']) && !empty($TeamItem['ldUrl']['target']) ) ? 'target="_blank"' : "";
												$Nofollow = ( !empty($TeamItem['ldUrl']) && !empty($TeamItem['ldUrl']['nofollow']) ) ? 'rel="nofollow"' : "";
												$IconHTML .= '<div class="linkedin-link">';
													$IconHTML .= '<a href="'.esc_url($TeamldUrl).'" '.$Target.' '.$Nofollow.' '.$ld_attr.' aria-label="'.esc_attr__('LinkedIn','tpgbp').'"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>';
												$IconHTML .= '</div>';
											}
											if( !empty($Telephone) ){
												$IconHTML .= '<div class="Telephone-link">';
													$IconHTML .= '<a href="'.esc_url('tel:'.$Telephone).'" aria-label="'.esc_attr__('Phone No','tpgbp').'"><i class="fas fa-phone" aria-hidden="true"></i></a>';
												$IconHTML .= '</div>';
											}
										$IconHTML .= '</div>';
									$IconHTML .= '</div>';	
								}

							$TitleHTML = '';
							if(!empty($TeamName)){
								$TitleHTML .= '<'.Tpgbp_Pro_Blocks_Helper::validate_html_tag($TitleTag).' class="tpgb-post-title">';
									if( !empty($DisbleLink) ){
										$TitleHTML .= wp_kses_post($TeamName);
									}else{
										$link_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['CusUrl']);
										$TitleHTML .= '<a href="'.esc_attr($TeamCUrl).'" '.$CustomTarget.' '.$CustomRel.' '.$link_attr.'>'.wp_kses_post($TeamName).'</a>';
									}
								$TitleHTML .= '</'.Tpgbp_Pro_Blocks_Helper::validate_html_tag($TitleTag).'>';
							}

							$DesigHTML = '';
							if( !empty($TeamDesignation) && !empty($Designation) ){
								$DesigHTML .= '<div class="tpgb-member-designation">'.wp_kses_post($TeamDesignation).'</div>';
							}					

							$FinalHTML = '';
							if( $style == 'style-1' ){
								$FinalHTML .= '<div class="post-content-image">';
									$FinalHTML .= $ImageHTML;
									$FinalHTML .= $IconHTML;
								$FinalHTML .= '</div>';
								$FinalHTML .= '<div class="post-content-bottom">';
									$FinalHTML .= $TitleHTML;
									$FinalHTML .= $DesigHTML;
								$FinalHTML .= '</div>';
							}else if( $style == 'style-2' ){
								$FinalHTML .= '<div class="post-content-image">'.$ImageHTML.'</div>';
								$FinalHTML .= '<div class="post-content-bottom">';
									$FinalHTML .= $TitleHTML;
									$FinalHTML .= $DesigHTML;
									$FinalHTML .= $IconHTML;
								$FinalHTML .= '</div>';
							}else if( $style == 'style-3' ){
								$FinalHTML .= '<div class="post-content-image">'.$ImageHTML.'</div>';
								$FinalHTML .= '<div class="post-content-bottom">';
									$FinalHTML .= '<div class="content-table">';
										$FinalHTML .= '<div class="table-cell">';
											$FinalHTML .= $TitleHTML;
										$FinalHTML .= '</div>';
										$FinalHTML .= '<div class="table-cell">';
											$FinalHTML .= $IconHTML;
										$FinalHTML .= '</div>';
									$FinalHTML .= '</div>';
									$FinalHTML .= $DesigHTML;
								$FinalHTML .= '</div>';
							}else if( $style == 'style-4' ){
								$AnimClass = '';
								if($AnimationIMG == 'pulse'){
									$AnimClass = 'image-plus';
									if($AniToggle){
										$AnimClass = 'hover_pulse';
									}
								}else if($AnimationIMG == 'floating'){
									$AnimClass = 'image-floating';
									if($AniToggle){
										$AnimClass = 'hover_floating';
									}
								}else if($AnimationIMG == 'tossing'){
									$AnimClass = 'image-tossing';
									if($AniToggle){
										$AnimClass = 'hover_tossing';
									}
								}else if($AnimationIMG == 'rotating'){
									$AnimClass = 'image-rotating';
									if($AniToggle){
										$AnimClass = 'hover_rotating';
									}
								}
								$FinalHTML .= '<div class="post-content-image">';
									$FinalHTML .= '<div class="bg-image-layered '.esc_attr($AnimClass).'"></div>';
									$FinalHTML .= $ImageHTML;
								$FinalHTML .= '</div>';
								$FinalHTML .= '<div class="post-content-bottom">';
									$FinalHTML .= $TitleHTML;
									$FinalHTML .= $DesigHTML;
									$FinalHTML .= $IconHTML;
								$FinalHTML .= '</div>';
							}

							$TeamMember .= $FinalHTML;
						$TeamMember .= '</div>';
					$TeamMember .= '</div>';
				}
			}
			if($layout=='carousel'){
				$TeamMember .= '</div>';
			}
		$TeamMember .= "</div>";
	$TeamMember .= "</div>";
	
	$TeamMember = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $TeamMember);
	if($layout =='carousel'){
		$arrowCss = Tp_Blocks_Helper::tpgb_carousel_arrow_css( $showArrows , $block_id );
		if( !empty($arrowCss) ){
			$TeamMember .= $arrowCss;
		}
	}
    return $TeamMember;
}

function tpgb_tp_team_member_listing() {

	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_team_member_listing_render_callback', true, true);
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_team_member_listing' );

function TMCategoryFilter($attributes){
	$category_filter = '';
	$TeamMemberR = (!empty($attributes['TeamMemberR'])) ? $attributes['TeamMemberR'] : [];

	$filter_style = $attributes['CatFilterS'];
	$filter_hover_style = $attributes["FilterHs"];
	$all_filter_category = (!empty($attributes["TextCat"])) ? $attributes["TextCat"] : esc_html__('All','tpgbp');
	$loop_category = array();
	$count_loop = 0;
	
	foreach ( $TeamMemberR as $TMFilter ) {
		$TMCategory = !empty($TMFilter['TCateg']) ? $TMFilter['TCateg'] : '';
			if(!empty($TMCategory)){
				$loop_category[] = explode(',', $TMCategory);
			}
		$count_loop++;
	}
	$loop_category = TM_Split_Array_Category($loop_category);
	$count_category = array_count_values($loop_category);
	
	$all_category=$category_post_count='';
	if($filter_style=='style-1'){
		$all_category='<span class="tpgb-category-count">'.esc_html($count_loop).'</span>';
	}
	if($filter_style=='style-2' || $filter_style=='style-3'){
		$category_post_count='<span class="tpgb-category-count">'.esc_html($count_loop).'</span>';
	}

	$category_filter .='<div class="tpgb-filter-data '.esc_attr($filter_style).'">';
		if($filter_style=='style-4'){
			$category_filter .= '<span class="tpgb-filters-link">'.esc_html__('Filters','tpgbp').'<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><line x1="0" y1="32" x2="63" y2="32"></line></g><polyline points="50.7,44.6 63.3,32 50.7,19.4 "></polyline><circle cx="32" cy="32" r="31"></circle></svg></span>';
		}
			$category_filter .='<div class="tpgb-categories '.esc_attr($filter_style).' hover-'.esc_attr($filter_hover_style).'">';			
				$category_filter .= '<div class="tpgb-filter-list"><a href="#" class="tpgb-category-list active all" data-filter="*" >'.$category_post_count.'<span data-hover="'.esc_attr($all_filter_category).'">'.esc_html($all_filter_category).'</span>'.$all_category.'</a></div>';
			
					foreach ( $count_category as $key => $value ) {
						$slug = TM_Media_createSlug($key);								
						$category_post_count = '';
						if($filter_style=='style-2' || $filter_style=='style-3'){
							$category_post_count='<span class="tpgb-category-count">'.esc_html($value).'</span>';
						}
						$category_filter .= '<div class="tpgb-filter-list"><a href="#" class="tpgb-category-list" data-filter=".'.esc_attr($slug).'">'.$category_post_count.'<span data-hover="'.esc_attr($key).'">'.esc_html($key).'</span></a></div>';
					}

			$category_filter .= '</div>';
	$category_filter .= '</div>';
	
	return $category_filter;

}

function TM_Split_Array_Category($array){
	if (!is_array($array)) { 
	  return FALSE; 
	} 
	$result = array(); 
	foreach ($array as $key => $value) { 
	  if (is_array($value)) { 
		$result = array_merge($result, TM_Split_Array_Category($value)); 
	  } 
	  else { 
		$result[$key] = $value; 
	  }
	}
	
	return $result; 
}

function TM_Media_createSlug($str, $delimiter = '-'){
	$slug = preg_replace('/[^A-Za-z0-9-]+/', $delimiter, $str);
	return $slug;
}