<?php
/* Block : Audio player
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_audio_player_callback( $attributes, $content) {
	$audio_player = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
    $Apstyle = (!empty($attributes['Apstyle'])) ? $attributes['Apstyle'] : 'style-1';
    $SplitText = (!empty($attributes['SplitText'])) ? $attributes['SplitText'] : '';
    $Aprepeater = (!empty($attributes['Aprepeater'])) ? $attributes['Aprepeater'] : [];
    $DefaultVolume = (!empty($attributes['DefaultVolume'])) ? $attributes['DefaultVolume'] : '80';
    $ImageSize = (!empty($attributes['ImageSize'])) ? $attributes['ImageSize'] : 'thumbnail';
   
	$Default_Img = TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
   
    $ap_playlisticon ='<div class="playlistIcon"><i class="fa fa-list" aria-hidden="true"></i></div>';
    $ap_track_txt='<span class="splitTxt">'.wp_kses_post($SplitText).'</span>';		
    $ap_play_pause ='<div class="tpgb-ap-pp"> <button class="play"><i class="fa fa-play" aria-hidden="true"></i></button><div class="pause"><i class="fa fa-pause" aria-hidden="true"></i></div></div>';
    $ap_rew='<div class="rew"><i class="fa fa-backward" aria-hidden="true"></i></div>';
    $ap_fwd='<div class="fwd"><i class="fa fa-forward" aria-hidden="true"></i></div>';
    $ap_endtime ='<div class="durationtime"></div>';
    $ap_currenttime ='<div class="currenttime">00.00</div>';
    $ap_tracker='<div class="tracker"><div class="tracker-fill"></div></div>'; 
    $ap_volume='<div class="volumeIcon"><i class="fa fa-volume-up vol-icon-toggle" aria-hidden="true"></i><div class="tpgb-volume-bg"><div class="volume ui-slider ui-slider-vertical ui-widget ui-widget-content ui-corner-all"><div class="ui-slider-range ui-widget-header ui-corner-all ui-slider-range-max"></div><span class="ui-slider-handle tpgb-trans-easeinout ui-state-default ui-corner-all" tabindex="0"></span></div></div></div>';
	
	$contorls = '<div class="controls">'.wp_kses_post($ap_rew). wp_kses_post($ap_play_pause). wp_kses_post($ap_fwd).'</div>';
	
    $i=0;
    $ap_trackdetails_title=$ap_trackdetails_artist=$ap_img=$ap_img_rnd='';
    $ap_playlist='<div class="playlist" id="playlist">';

        foreach ( $Aprepeater as $item ) {
            $audiourl=$thumb_img='';
            $AudSource = ( !empty($item['AudSource']) ) ? $item['AudSource'] : 'url';
            $SourceFile = (isset($item['sorself']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['sorself']) : (!empty($item['sorself']['url']) ? $item['sorself']['url'] : '');
            $SourceURL = (isset($item['sorurl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['sorurl']) : (!empty($item['sorurl']['url']) ? $item['sorurl']['url'] : '');
            
            if( $AudSource == 'file' ){
                $audiourl = $SourceFile;
            }else if( $AudSource == 'url' ){
                $audiourl = $SourceURL;
            }
            
            if(isset($item['Imagesource']['dynamic'])){
                $img_url = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['Imagesource']);
                $altText = altTextGet($item['Imagesource']);
                $thumb_img_Render = '<img src="'.esc_url($img_url).'" alt="'.$altText.'"/>';
                $thumb_img = (!empty($img_url)) ? $img_url : $Default_Img;
            }else if(!empty($item['Imagesource']['id'])){
                $image_id = $item['Imagesource']['id'];
                $altText = altTextGet($item['Imagesource']);
                $thumb_img = wp_get_attachment_image_src($image_id, $ImageSize);
                $thumb_img_Render = wp_get_attachment_image($image_id, $ImageSize, false, ['alt'=> $altText]);
                $thumb_img = !empty($thumb_img[0]) ? $thumb_img[0] : $Default_Img;
            }else if(!empty($attributes['Audioimage']['id'])){
                $image_id = $attributes['Audioimage']['id'];
                $altText = altTextGet($attributes['Audioimage']);
                $thumb_img = wp_get_attachment_image_src($image_id, $ImageSize);
                $thumb_img_Render = wp_get_attachment_image($image_id, $ImageSize, false, ['alt'=> $altText]);
                $thumb_img = !empty($thumb_img[0]) ? $thumb_img[0] : $Default_Img;
            }else if(!empty($attributes['Audioimage']['url'])){
                $altText = altTextGet($attributes['Audioimage']);
                $thumb_img_Render = '<img src="'.esc_url($attributes['Audioimage']['url']).'" alt="'.$altText.'"/>';
                $thumb_img = $attributes['Audioimage']['url'];
            }else{
                $altText = altTextGet();
                $thumb_img_Render = '<img src="'.esc_url($Default_Img).'" alt="'.$altText.'"/>';
                $thumb_img = $Default_Img;
            }
        
            if($i==0){ 
                $ap_trackdetails_title='<span class="title">'.wp_kses_post($item['title']).'</span>';
                $ap_trackdetails_artist='<span class="artist">'.wp_kses_post($item['author']).'</span>';
                
                if(isset($item['Imagesource']['dynamic'])){
                    $img_url = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['Imagesource']);
                    $altText = altTextGet($item['Imagesource']);
                    $ap_img_rnd = '<img src="'.esc_url($img_url).'" alt="'.$altText.'"/>';
                    $ap_img = (!empty($img_url)) ? $img_url : $Default_Img;
                }else if(!empty($item['Imagesource']['id'])){
                    $image_id = $item['Imagesource']['id'];
                    $ap_img = wp_get_attachment_image_src($image_id, $ImageSize);
                    $ap_img = !empty($ap_img[0]) ? $ap_img[0] : $Default_Img;
                    $altText = altTextGet($item['Imagesource']);
                    $ap_img_rnd = '<img src="'.esc_url($ap_img).'"/>';
                }elseif(!empty($attributes['Audioimage']['id'])) {	
                    $image_id = $attributes['Audioimage']['id'];
                    $altText = altTextGet($attributes['Audioimage']);
                    $ap_img = wp_get_attachment_image_src($image_id, $ImageSize);					
                    $ap_img = !empty($ap_img[0]) ? $ap_img[0] : $Default_Img;
                    $ap_img_rnd = '<img src="'.esc_url($ap_img).'" alt="'.$altText.'"/>';
                }else{
                    $ap_img = $Default_Img;
                    $ap_img_rnd = $thumb_img_Render;
                }
            }

            $ap_playlist .= '<div class="tpgb-playlist" audioURL="'.esc_url($audiourl).'" artist="'.wp_kses_post($item['author']).'" data-thumb="'.esc_url($thumb_img).'">'.wp_kses_post($item['title']).'</div>';
            $i++;
        }
    $ap_playlist.='</div>';
	
	$trackDetails = '<div class="trackDetails text-center">'.wp_kses_post($ap_trackdetails_title).wp_kses_post($ap_track_txt).wp_kses_post($ap_trackdetails_artist).'</div>';
	
	$audio_player ='<div class="tpgb-audio-player tpgb-block-'.esc_attr($block_id).' '.esc_attr($Apstyle).' '.esc_attr($blockClass).'" data-id="tpgb-block-'.esc_attr($block_id).'" data-style="'.esc_attr($Apstyle).'" data-apvolume="'.esc_attr($DefaultVolume).'">';

        $audio_player .='<div class="tpgb-audioplay-wrap tpgb-relative-block '.esc_attr($Apstyle).'">';
		
            if($Apstyle == 'style-1'){
                $audio_player .= '<div class="tpgb-player tpgb-relative-block text-center">';
                    $audio_player .= $ap_playlisticon;
                    $audio_player .= $trackDetails;
                    $audio_player .= $contorls;
                    $audio_player .= $ap_volume;					
                    $audio_player .= $ap_tracker;
                $audio_player .= '</div>';
                $audio_player .= $ap_playlist;
            }else if($Apstyle == 'style-2'){
                $audio_player .='<div class="tpgb-player tpgb-relative-block text-center">';
                    $audio_player .='<div class="main-wrapper-style">';
                        $audio_player .='<div class="controls">';
                            $audio_player .= $ap_play_pause;
                        $audio_player .='</div>';
                        $audio_player .= $ap_tracker;
                        $audio_player .= $ap_volume;
                    $audio_player .='</div>';
                $audio_player .='</div>';
                $audio_player .= $ap_playlist;
			}else if($Apstyle == 'style-3'){
               $audio_player .= '<div class="tpgb-player tpgb-relative-block text-center">';
					$audio_player .= $ap_playlisticon;
					$audio_player .= '<div class="trackimage">'.$ap_img_rnd.'</div>';
					$audio_player .= $trackDetails;
					$audio_player .=  $contorls;
					$audio_player .= '<div class="ap-time-seek-vol">'.$ap_volume;	
					$audio_player .= '<div class="ap-time">'.$ap_currenttime;
					$audio_player .= $ap_endtime.'</div>';
					$audio_player .= $ap_tracker;
				$audio_player .= '</div></div>';
				$audio_player .= $ap_playlist;
			}else if($Apstyle == 'style-4'){
				$audio_player .= '<div class="tpgb-player tpgb-relative-block text-center">';
						$audio_player .= $ap_playlisticon;
						$audio_player .= '<div class="ap-title-art">';
						$audio_player .= wp_kses_post($ap_trackdetails_title) .wp_kses_post($ap_trackdetails_artist);
						$audio_player .= '</div>';
						$audio_player .= '<div class="main-wrapper-style">';
							$audio_player .= '<div class="controls">';
								$audio_player .= $ap_play_pause;
							$audio_player .= '</div>';
							$audio_player .= $ap_tracker;
							$audio_player .= $ap_volume;
						$audio_player .= '</div>';
				$audio_player .= '</div>';
				$audio_player .= $ap_playlist;
			}else if($Apstyle == 'style-5'){
				$audio_player .= '<div class="tpgb-player tpgb-relative-block text-center">';
						$audio_player .= $ap_playlisticon;						
						$audio_player .= '<div class="ap-st5-img"></div>';						
						$audio_player .= '<div class="ap-st5-content">';
								$audio_player .= '<div class="ap-controls-track">';
                                    $audio_player .= '<div class="controls">'.$ap_play_pause;
                                        $audio_player .= '<div class="ap-nextprev">'.wp_kses_post($ap_rew).wp_kses_post($ap_fwd).'</div>';
                                    $audio_player .= '</div>';
								$audio_player .= $ap_tracker.'</div>';
								$audio_player .= $trackDetails;
						$audio_player .= '</div>';
						$audio_player .= $ap_volume;
				$audio_player .= '</div>';
				$audio_player .= $ap_playlist;
			}else if($Apstyle == 'style-6'){
				$audio_player .= '<div class="tpgb-player tpgb-relative-block text-center">';
						$audio_player .= $ap_playlisticon;
							$audio_player .= '<div class="ap-st5-img">';
                                $audio_player .= '<div class="controls">'.$ap_play_pause; 
                                    $audio_player .= '<div class="ap-nextprev">'.wp_kses_post($ap_rew).wp_kses_post($ap_fwd).'</div>';
                                $audio_player .= '</div>';
                            $audio_player .= '</div>';
						    $audio_player .= '<div class="ap-st5-content">';								
								$audio_player .= $trackDetails;						
							    $audio_player .= $ap_tracker;								
						    $audio_player .= '</div>';
						$audio_player .= $ap_volume;
				$audio_player .= '</div>';
				$audio_player .= $ap_playlist;
			}else if($Apstyle == 'style-7'){
                $audio_player .= '<div class="tpgb-player tpgb-relative-block text-center">';
                    $audio_player .= $contorls;
                    $audio_player .= $ap_tracker;
                    $audio_player .= '<div class="ap-time-title">'.wp_kses_post($ap_currenttime).wp_kses_post($ap_trackdetails_title).wp_kses_post($ap_endtime).'</div>';
                $audio_player .= '</div>';
                $audio_player .= $ap_playlist;
			}else if($Apstyle == 'style-8'){
				$audio_player .= '<div class="tpgb-player tpgb-relative-block text-center">';
					$audio_player .= '<div class="tpgb-player-bg-img"></div>';					
					    $audio_player .= '<div class="trackimage">'.$ap_img_rnd.'</div>';
					    $audio_player .= '<div class="trackDetails text-center">'.wp_kses_post($ap_trackdetails_title).wp_kses_post($ap_trackdetails_artist).'</div>';
					    $audio_player .= $contorls;
					    $audio_player .= $ap_tracker;
					$audio_player .= '<div class="ap-time-seek-vol">';
					    $audio_player .= '<div class="ap-time">'.$ap_currenttime;
					$audio_player .= $ap_endtime.'</div>';
				$audio_player .= '</div></div>';
				$audio_player .= $ap_playlist;
			}else if($Apstyle=='style-9'){
				$audio_player .= '<div class="tpgb-player tpgb-relative-block text-center">';
					$audio_player .= '<div class="tpgb-player-hover">';
					$audio_player .= '<div class="tpgb-player-bg-img">';
                        $audio_player .= '<div class="trackDetails text-center">'.wp_kses_post($ap_trackdetails_title).wp_kses_post($ap_trackdetails_artist).'</div>';
                    $audio_player .= '</div>';
					$audio_player .= $contorls;
				$audio_player .= '</div>';  
				$audio_player .= '</div>';
				$audio_player .= $ap_playlist;
			}
        $audio_player .='</div>';

    $audio_player .='</div>';
	
	$audio_player = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $audio_player);
	
    return $audio_player;
}

function altTextGet($imageStore = ''){
    $altText = '';
    if(!empty($imageStore)){
        $altText = (isset($imageStore['alt']) && !empty($imageStore['alt'])) ? esc_attr($imageStore['alt']) : ((!empty($imageStore['title'])) ? esc_attr($imageStore['title']) : esc_attr__('Audio Player','tpgbp'));
    }else{
        $altText = esc_attr__('Audio Player','tpgbp');
    }
    return $altText;
}

function tpgb_tp_audio_player_render() {
    
    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
        $block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_audio_player_callback');
	    register_block_type( $block_data['name'], $block_data );
    }
}
add_action( 'init', 'tpgb_tp_audio_player_render' );