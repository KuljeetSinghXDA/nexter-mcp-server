<?php
/* Block : Timeline
 * @since : 1.2.1
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_timeline_render_callback( $attributes, $content) {


	$Timeline = '';
	$block_id = ( !empty($attributes['block_id']) ) ? $attributes['block_id'] : uniqid("title");
	$pattern = '/\btpgb-block-'.esc_attr($block_id).'/';
   
	if (preg_match($pattern, $content)) {
		if( class_exists('Tpgb_Blocks_Global_Options') ){
            $global_blocks = Tpgb_Blocks_Global_Options::get_instance();
            $content = $global_blocks::block_row_conditional_render($attributes,$content);
        }
		return $content;
	}
	$style = ( !empty($attributes['style']) ) ? $attributes['style'] : 'style-1';
	$MLayout = ( !empty($attributes['MLayout']) ) ? $attributes['MLayout'] : false;
	$RContent = ( !empty($attributes['RContent']) ) ? $attributes['RContent'] : [];
	$Alignment = ( !empty($attributes['Alignment']) ) ? $attributes['Alignment'] : 'center';
	$PinStyle = ( !empty($attributes['PinStyle']) ) ? $attributes['PinStyle'] : 'style-1';
	$StartPin = ( !empty($attributes['StartPin']) ) ? $attributes['StartPin'] : 'none';
	$StartText = ( !empty($attributes['StartText']) ) ? $attributes['StartText'] : '';
	$EndPin = ( !empty($attributes['EndPin']) ) ? $attributes['EndPin'] : 'none';
	$EndImage = ( !empty($attributes['EndImage']) && !empty($attributes['EndImage']['url']) ) ? $attributes['EndImage']['url'] : '';
	$EndText = ( !empty($attributes['EndText']) ) ? $attributes['EndText'] : '';
	$ArrowStyle = ( ($style == 'style-2') ? 'arrow-'.$attributes['ArrowStyle'] : '');
	$Rowclass = ( !empty($attributes['MLayout']) ) ? 'tpgb-row' : '';
	$titledivider = ( !empty($attributes['titledivider']) ) ? $attributes['titledivider'] : false;
	$timeediType = ( !empty($attributes['timeediType']) ) ? $attributes['timeediType'] : '';
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
     $isAnim = ( !empty($attributes['isAnim']) ) ? $attributes['isAnim'] : '';
    $animColor = ( !empty($attributes['animColor']) ) ? $attributes['animColor'] : '';
    $dataattr='';

	$layout=$Mtype='';
	if( $attributes['MLayout'] ){
		$layout = 'tpgb-isotope';
		$Mtype = 'masonry';
	}

	if ($isAnim===true) {
        $dataattr = 'data-anim-color="'.esc_attr($animColor).'"';
    }
	$Timeline .= '<div id="tpgb_timeline" class="tpgb-block-'.esc_attr($block_id).' tpgb-relative-block tpgb-timeline-list tpgb-layout '.esc_attr($layout).' timeline-'.esc_attr($Alignment).'-align timeline-'.esc_attr($style).' '.esc_attr($blockClass).'" data-id="'.esc_attr($block_id).'" data-masonry-type="'.esc_attr($Mtype).'"'.$dataattr.'>';
		$Timeline .= '<div class="'.esc_attr($Rowclass).' post-loop-inner tpgb-relative-block '.esc_attr($ArrowStyle).'">';

			$Timeline .= '<div class="timeline-track"></div>';
			$Timeline .= '<div class="timeline-track timeline-track-draw"></div>';

			$StartIcon = '';
			if( $StartPin == 'icon' ){
				$StartIcon = '<i class="'.esc_attr($attributes['StartIcon']).' startImg"></i>';
			}else if( $StartPin == 'image' ){
				$StartImage ='';
				if( !empty($attributes['StartImage']) && !empty($attributes['StartImage']['id']) ){
					$StartImgSize = (!empty($attributes['StartImgSize']) ? $attributes['StartImgSize'] : 'full');
					$StartIcon = wp_get_attachment_image( $attributes['StartImage']['id'], $StartImgSize, false, ['class' => 'startImg']);
				}
			}
			if( $StartPin != 'none' ){
				$Timeline .= '<div class="timeline--icon">';
					if( !empty($StartIcon) ){
						$Timeline .= '<div class="tpgb-beginning-icon">'.wp_kses_post($StartIcon).'</div>';								
					}
					if( $StartPin == 'text' && !empty($StartText) ){
						$Timeline .= '<div class="tpgb-timeline-text tpgb-text-start">';
							$Timeline .= '<div class="beginning-text">'.wp_kses_post($StartText).'</div>';
						$Timeline .= '</div>';
					}
				$Timeline .= '</div>';
			}

			if($timeediType == 'editor'){
				$Timeline .= $content;
			}else{
				foreach ( $RContent as $index => $Content ) {
					$PinTitle = (!empty($Content['RTitle'])) ? $Content['RTitle'] : '';
                    $contentImg = (!empty($Content['contentImg']) && !empty($Content['contentImg']['url'])) ? $Content['contentImg']['url'] : '';
                    $RPosition = (!empty($Content['RPosition'])) ? $Content['RPosition'] : 'right';
					$RContentType = (!empty($Content['RContentType'])) ? $Content['RContentType'] : 'text';
					$RButton = (!empty($Content['RButton'])) ? $Content['RButton'] : false;
					$RBtnText = (!empty($Content['RBtnText'])) ? $Content['RBtnText'] : 'Read More';
					$RcType =  (!empty($Content['RcType'])) ? $Content['RcType'] : 'image';
					$Rnone =  (!empty($Content['Rnone'])) ? $Content['Rnone'] : 'icon';
					$Rimg = (!empty($Content['Rimg']) && !empty($Content['Rimg']['url'])) ? $Content['Rimg']['url'] : '';
					$ImageSize = (!empty($Content['RimgSize']) ? $Content['RimgSize'] : 'full');
					$Rfimage = (!empty($Content['Rfimage']) && !empty($Content['Rfimage']['url'])) ? $Content['Rfimage']['url'] : '';
					$ImgSize = (!empty($Content['RfImgSize']) ? $Content['RfImgSize'] : 'full');
					$CustomURL = (!empty($Content['RUrl']) && !empty($Content['RUrl']['url'])) ? $Content['RUrl']['url'] : '';
					$Target = (!empty($Content['RUrl']) && !empty($Content['RUrl']['target'])) ? 'target=_blank' : "";
					$Nofollow = (!empty($Content['RUrl']) && !empty($Content['RUrl']['nofollow'])) ? 'rel=nofollow' : "";
					$RcAlign = (!empty($Content['RcAlign'])) ? $Content['RcAlign'] : 'text-right';
					$BTNName = (!empty($Content['RBtnText'])) ? $Content['RBtnText'] : '';
					$RcTitle = (!empty($Content['RcTitle'])) ? $Content['RcTitle'] : '';

					// AJAX Base Template Load Class
					$temClass = $cntClass = '';
					if( $RcType == 'template' && !empty($Content['Rtemplet']) && isset($Content['ajaxbase']) && !empty($Content['ajaxbase']) && $Content['ajaxbase'] == 'ajax-base' ){
						$temClass = 'tpgb-load-template-view tpgb-load-'.esc_attr($Content['Rtemplet']);
						$cntClass = 'tpgb-load-'.esc_attr( $Content['Rtemplet'] ).'-content';
					}

					$Timeline .= '<div class="grid-item timeline-item-wrap tp-repeater-item-'.esc_attr($Content['_key']).' timeline-'.esc_attr($Content['RSAlign']).'-content text-pin-position-'. esc_attr($Content['RPosition']) .'">';
						$Timeline .= '<div class="timeline-inner-block timeline-transition">';
							
							$Timeline .= '<div class="timeline-item '.esc_attr($RcAlign).'">';
								$Timeline .= '<div class="timeline-item-content timeline-transition '.esc_attr($RcAlign).'">';
									$Timeline .= '<div class="timeline-tl-before timeline-transition"></div>';
										$link_attr = Tpgbp_Pro_Blocks_Helper::add_link_attributes($Content['RUrl']);
										if(!empty($RcTitle) && !empty($CustomURL)){
											$Timeline .= '<a class="timeline-item-heading timeline-transition" href="'.esc_url($CustomURL).'" '.esc_attr($Target).' '.esc_attr($Nofollow).' '.$link_attr.'>'. esc_html($RcTitle) .'</a>';
										}else if(!empty($RcTitle)){
											$Timeline .= '<h3 class="timeline-item-heading timeline-transition">'.esc_html($RcTitle).'</h3>';
										}
										if( $style == 'style-2' && !empty($titledivider) ){
											$Timeline .= '<div class="border-bottom '.esc_attr($RcAlign).'"><hr/></div>';
										}
										$Timeline .= '<div class="timeline-content-image '.esc_attr($temClass).'">';
											if( $RcType == 'image' && !empty($Rfimage) ){
												$RImageid = $index;
												if( !empty($Content['Rfimage']['id']) ){
													$RImageid = $Content['Rfimage']['id'];
													$AttImg = wp_get_attachment_image($RImageid,$ImgSize, false, ['class' => 'hover__img']);
													$Timeline .= $AttImg;
												}
											}else if( $RcType == 'iframe' && !empty($Content['RcHTML']) ){
												$Timeline .= $Content['RcHTML'];	
											}else if( $RcType == 'template' && !empty($Content['Rtemplet']) ){
												ob_start();
													if(!empty($Content['Rtemplet'])) {
														echo Tpgb_Library()->plus_do_block($Content['Rtemplet']);
													}
												if(isset($Content['ajaxbase']) && !empty($Content['ajaxbase']) && $Content['ajaxbase'] == 'ajax-base'){
													$Timeline .= '<div class="'.esc_attr($cntClass).'"></div>';
												}else{
													$Timeline .= ob_get_contents();
												}
												ob_end_clean();
											}
										$Timeline .= '</div>';
										if( !empty($Content['Rdes']) ){
											$Timeline .= '<div class="timeline-item-description timeline-transition">'.wp_kses_post($Content['Rdes']).'</div>';
										}
										if( !empty($Content['RButton']) && !empty($BTNName) ){
											$Timeline .= '<div class="button-style-8 btn'.esc_attr($block_id).'" >';
												$Timeline .= '<a href="'.esc_url($CustomURL).'"  class="button-link-wrap tpgb-trans-linear" role="button" '.esc_attr($Target).' '.esc_attr($Nofollow).' '.$link_attr.'>'.esc_html($BTNName).'</a>';
											$Timeline .= '</div>';
										}
								$Timeline .= '</div>';
							$Timeline .= '</div>';

							$Timeline .= '<div class="point-icon '.esc_attr($PinStyle).'">';
								$Timeline .= '<div class="timeline-tooltip-wrap">';
									$Timeline .= '<div class="timeline-point-icon">';
										if( $Rnone == 'icon' && !empty($Content['Ricon']) ){
											$Timeline .= '<i class="'.esc_attr($Content['Ricon']).' point-icon-inner"></i>';
										}elseif( $Rnone == 'image' && !empty($Rimg) ){
											$IconImgId = $index;												
											if( !empty($Content['Rimg']) && !empty($Content['Rimg']['id']) ){
												$IconImgId = $Content['Rimg']['id'];
												$AttImg = wp_get_attachment_image($IconImgId,$ImageSize, false, ['class' => 'point-icon-inner']);
												$Timeline .= $AttImg;
											}
										}
									$Timeline .= '</div>';
								$Timeline .= '</div>';


                                if( !empty($PinTitle) || (!empty($contentImg) && !empty($Content['contentImg']['url'])) ){
                                    $Timeline .= '<div class="timeline-text-tooltip position-'.esc_attr($Content['RPosition']).' timeline-transition">';
                                    if( $RContentType === "image" ){
                                        if( !empty($contentImg) && !empty($Content['contentImg']['url']) ){
                                            $Timeline .= '<img src="'.esc_url($Content['contentImg']['url']).'" alt="" />';
                                        }
                                    } else {
                                        $Timeline .= esc_html($PinTitle);
                                    }
                                    $Timeline .= '<div class="tpgb-tooltip-arrow timeline-transition"></div>';
                                    $Timeline .= '</div>';
                                }
							$Timeline .= '</div>';

						$Timeline .= '</div>';
					$Timeline .= '</div>';
				}
			}
				


			$EndIcon = '';
			if( $EndPin == 'icon' ){
				$EndIcon = '<i class="'.esc_attr($attributes['EndIcon']).' EndImg"></i>';
			}else if( $EndPin == 'image' ){
				if( !empty($attributes['EndImage']) && !empty($attributes['EndImage']['id']) ){
					$EtartImgSize = !empty($attributes['EndImgSize']) ? $attributes['EndImgSize'] : 'full';
					$AttImg = wp_get_attachment_image( $attributes['EndImage']['id'],$EtartImgSize, false, ['class' => 'EndImg'] );
					$EndIcon = $AttImg;
				}
			}
			if( $EndPin != 'none' ){
				$Timeline .= '<div class="timeline--icon">';
					if(!empty($EndIcon) ){
						$Timeline .= '<div class="timeline-end-icon">'.wp_kses_post($EndIcon).'</div>';
					}
					if( $EndPin == 'text' && !empty($EndText) ){
						$Timeline .= '<div class="tpgb-timeline-text tpgb-text-end">';
							$Timeline .= '<div class="end-text">'.esc_html($EndText).'</div>';
						$Timeline .= '</div>';
					}
				$Timeline .= '</div>';
			}

		$Timeline .= '</div>';
	$Timeline .= '</div>';
	
	$Timeline = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $Timeline);
	
    return $Timeline;
}

function tpgb_tp_timeline() {
	
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_timeline_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_timeline' );