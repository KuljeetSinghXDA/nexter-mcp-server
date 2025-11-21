<?php
/**
 * TPGB Pro Plugin.
 *
 * @package TPGBP
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Tpgbp_Pro_Blocks_Helper {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;
	
	protected static $load_blocks;
	
	protected static $deactivate_blocks =[];
	
	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * Constructor
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter('tpgb_has_blocks_condition', [ $this, 'tpgb_has_blocks_options'], 10 , 3 );
		//add_filter('tpgb_extra_load_css_js', [ $this, 'tpgb_extra_css_js_loading'], 10 );
		
		add_filter('tpgb_load_blocks', [ $this, 'init_blocks_load'] );
		add_filter('tpgb_load_localize', [ $this, 'localize_data'] );
		add_filter('tpgb_blocks_register_render', [ $this, 'load_blocks_registers_render'] );
		add_filter('tpgb_blocks_register', [ $this, 'load_blocks_registers_css_js'] );
		
		add_action('wp_ajax_tpgb_post_load', [ $this, 'tpgb_post_load'] );
		add_action('wp_ajax_nopriv_tpgb_post_load', [ $this, 'tpgb_post_load'] );
		
		add_filter('tpgb_carousel_options', [ $this, 'tpgb_pro_carousel_options'], 10 );
		add_filter('tpgb_hasWrapper', [ $this, 'tpgb_haswrapper_render'], 10, 2 );
		add_filter('tpgb_globalWrapClass', [ $this, 'tpgb_globalwrapper_class'], 10, 2 );
		add_filter('tpgb_globalAnimOut_filter', [ $this, 'tpgb_globalAnimOut_options'], 10, 2 );

		add_action('tpgb_wrapper_inner_before', [ $this, 'tpgb_wrapper_before_render'] );
		add_action('tpgb_wrapper_inner_after', [ $this, 'tpgb_wrapper_after_render'] );
		
		add_action('wp_ajax_tpgb_reviews_load', array($this, 'tpgb_reviews_load'));
		add_action('wp_ajax_nopriv_tpgb_reviews_load', array($this, 'tpgb_reviews_load'));
		
		/*Get Social Reviews Api Token*/
		add_action('wp_ajax_tpgb_socialreview_Gettoken', array($this, 'tpgb_socialreview_Gettoken'));
		add_action('wp_ajax_nopriv_tpgb_socialreview_Gettoken', array($this, 'tpgb_socialreview_Gettoken'));
		
		add_action('wp_ajax_tpgb_feed_load', array($this, 'tpgb_social_feed_load'));
		add_action('wp_ajax_nopriv_tpgb_feed_load', array($this, 'tpgb_social_feed_load'));
	
		add_filter( 'body_class', array( $this,'tpgb_body_class') );
		add_filter( 'tpgb_event_tracking', array( $this,'event_tracking_attr') );

		// Category Image
		$blockList = get_option('tpgb_normal_blocks_opts');
		if( isset($blockList) && !empty($blockList) && isset($blockList['enable_normal_blocks']) && !empty($blockList['enable_normal_blocks']) && in_array( 'tp-dynamic-category' , $blockList['enable_normal_blocks'] ) ){
			require_once TPGBP_PATH . 'classes/extras/tpgb-category-image.php';
		}
		
		// Ajax For Template Content
		add_action('wp_ajax_tpgb_get_template_content', array($this, 'tpgb_get_template_content'));
		add_action('wp_ajax_nopriv_tpgb_get_template_content', array($this, 'tpgb_get_template_content'));

		add_filter( 'tpgb_generate_inline_css', [$this, 'tpgb_generate_inline_css_action'] , 10, 2 );

        // Form Block AJAX Function
        add_filter( 'nxt_form_pro_action_callback', [$this, 'nxt_form_pro_action_callback'] , 10, 0 );
	}
	
	/*
	 * Check Event Tracking Array
	 * @since 2.0.9
	 * */
	public function event_tracking_attr(){
		$event_tracking_data = get_option( 'tpgb_connection_data' );
		$eventTrackArr = [
			'switch' => (!empty($event_tracking_data) && isset($event_tracking_data['tpgb_event_tracking']) && $event_tracking_data['tpgb_event_tracking']=== 'disable') ? false : true,
			'google_track' => (!empty($event_tracking_data) && !empty($event_tracking_data['event_track_google'])) ? $event_tracking_data['event_track_google'] : '',
			'facebook_track' => (!empty($event_tracking_data) && !empty($event_tracking_data['event_track_facebook'])) ? $event_tracking_data['event_track_facebook'] : ''
		];
		return $eventTrackArr;
	}

	/*
	 * Preloader enable add body class
	 * @since 1.3.0
	 */
	public function tpgb_body_class( $classes ){
		$enable_normal_blocks = $this->tpgb_get_option('tpgb_normal_blocks_opts','enable_normal_blocks');
		
		if( !empty($enable_normal_blocks) && in_array('tp-preloader',$enable_normal_blocks)){
			$classes[]="tpgb-body-preloader";
		}
		return $classes;
	}
	
	/*
	 * Check Wrapper Div 
	 */
	public function tpgb_haswrapper_render( $wrapper = false, $attr = []){
		if( !empty($attr) ){
		
			$filterEffect = false;
			if((!empty($attr['globalCssFilter']) && !empty($attr['globalCssFilter']['openFilter'])) || (!empty($attr['globalHCssFilter']['openFilter']) && !empty($attr['globalHCssFilter']))){
				$filterEffect = true;
			}
			
			$Plus3DTilt = false;
			if(!empty($attr['Plus3DTilt']) && !empty($attr['Plus3DTilt']['tpgbReset'])){
				$Plus3DTilt = true;
			}
			
			$PlusMouseParallax = false;
			if(!empty($attr['PlusMouseParallax']) && !empty($attr['PlusMouseParallax']['tpgbReset'])){
				$PlusMouseParallax = true;
			}
			$contentHoverEffect = false;
			if(!empty($attr['contentHoverEffect']) && !empty($attr['selectHoverEffect']) ){
				$contentHoverEffect = true;
			}
			$continueAnimation = false;
			if(!empty($attr['continueAnimation']) && !empty($attr['continueAniStyle']) ){
				$continueAnimation = true;
			}
			$globalTooltip = false;
			if(!empty($attr['globalTooltip']) ){
				$globalTooltip = true;
			}
			$advBorderRadius = false;
			if(!empty($attr['advBorderRadius']) && !empty($attr['advBorderRadius']['tpgbReset'])){
				$advBorderRadius = true;
			}

			/** Event Tracking */
			$check_event_tracker = get_option( 'tpgb_connection_data' );
			$eventTracker = false;
			if(!empty($check_event_tracker) && isset($check_event_tracker['tpgb_event_tracking']) && ($check_event_tracker['tpgb_event_tracking']==='enable' && (!empty($attr['etFacebook']) || !empty($attr['etGoogle'])))){
				$eventTracker = true;
			}

			if( !empty($filterEffect) || !empty($Plus3DTilt) || !empty($PlusMouseParallax) || !empty($contentHoverEffect) || !empty($continueAnimation) || !empty($globalTooltip) || !empty($advBorderRadius) || !empty($eventTracker)){
				$wrapper = true;
			}

            if( !empty($attr['PlusMagicScroll']) ){
                $wrapper = true;
            }
			
		}
		return $wrapper;
	}
	
	/*
	 * Global Varible Classes
	 */
	public function tpgb_globalwrapper_class($classes = '', $attr = []){
		if(!empty($attr['contentHoverEffect']) && !empty($attr['selectHoverEffect']) ){
			$classes .= ' tpgb_cnt_hvr_effect cnt_hvr_'.esc_attr($attr['selectHoverEffect']);
		}
		if(!empty($attr['continueAnimation']) && !empty($attr['continueAniStyle']) ){
			$contiExClass = '';
			if(!empty($attr['continueHoverAnimation'])){
				$contiExClass = 'tpgb-hover-'.esc_attr($attr['continueAniStyle']);
			}else{
				$contiExClass = 'tpgb-normal-'.esc_attr($attr['continueAniStyle']);
			}
			$classes .= ' tpgb_continue_animation '.esc_attr($contiExClass);
		}

		$check_event_tracker = get_option( 'tpgb_connection_data' );
		if(!empty($check_event_tracker) && isset($check_event_tracker['tpgb_event_tracking']) && ($check_event_tracker['tpgb_event_tracking']==='enable' && (!empty($attr['etFacebook']) || !empty($attr['etGoogle'])))){
			$classes .= ' tpgb-event-tracker';
		}

        if(!empty($attr['PlusMagicScroll']) ){
			$classes .= ' tpgb_magic_scroll';
		}
		return $classes;
	}
    
	/*
	 * Wrapper Before Render
	 */
	public function tpgb_wrapper_before_render( $attr = [] ){
		if( empty($attr) ){
			return  '';
		}
		
		$wrapInnerClass = '';
		
		$Plus3DTilt = false;
		$tiltAttr = '';
		$tiltSetting = [];

		$advBorderRadius = false;
		if(!empty($attr['advBorderRadius']) && !empty($attr['advBorderRadius']['tpgbReset'])){
			$advBorderRadius = true;
		}

		if(!empty($attr['Plus3DTilt']) && !empty($attr['Plus3DTilt']['tpgbReset'])){
			$Plus3DTilt = true;
			$tiltSetting['max'] = (!empty($attr['Plus3DTilt']['tiltMax']) ? $attr['Plus3DTilt']['tiltMax'] : 20);
			$tiltSetting['perspective'] = (!empty($attr['Plus3DTilt']['tiltPerspective']) ? $attr['Plus3DTilt']['tiltPerspective'] : 400);
			$tiltSetting['scale'] = (!empty($attr['Plus3DTilt']['tiltScale']) ? $attr['Plus3DTilt']['tiltScale'] : 1.18);
			$tiltSetting['speed'] = (!empty($attr['Plus3DTilt']['tiltSpeed']) ? $attr['Plus3DTilt']['tiltSpeed'] : 400);
			$tiltSetting['easing'] = (!empty($attr['Plus3DTilt']['tiltEasing']) && $attr['Plus3DTilt']['tiltEasing']!='custom') ? $attr['Plus3DTilt']['tiltEasing'] : (!empty($attr['Plus3DTilt']['tiltEasingCus']) ? $attr['Plus3DTilt']['tiltEasingCus'] : 'cubic-bezier(.03,.98,.52,.99)');
			$tiltSetting['transition'] = true;
			
			$tiltAttr .= 'data-tiltSetting=\'' .htmlspecialchars(wp_json_encode($tiltSetting), ENT_QUOTES, 'UTF-8'). '\'';
			$wrapInnerClass .= ' tpgb-jstilt';
		}

		$gblTooltip ='';$contentItem =[]; $gTooltipAttr = '';$ttId = '';
		$globalTooltip = false;
		if( !empty($attr['globalTooltip'])){
			$wrapInnerClass .= ' tpgb-global-tooltip';
			$globalTooltip = true;
			$uniqid=uniqid("tooltip");
			$gblTooltip .= ' data-tippy=""';
			$gblTooltip .= ' data-tippy-interactive="'.($attr['gbltipInteractive'] ? 'true' : 'false').'"';
			$gblTooltip .= ' data-tippy-placement="'.($attr['gbltipPlacement'] ? $attr['gbltipPlacement'] : 'top').'"';
			$gblTooltip .= ' data-tippy-theme=""';
			$gblTooltip .= ' data-tippy-followCursor="'.($attr['gbltipFlCursor']=='default' ? true : $attr['gbltipFlCursor']).'"';
			$gblTooltip .= ' data-tippy-arrow="'.($attr['gbltipArrow'] ? 'true' : 'false').'"';
			$gblTooltip .= ' data-tippy-animation="'.($attr['gbltipAnimation'] ? $attr['gbltipAnimation'] : 'fade').'"';
			$gblTooltip .= ' data-tippy-offset="['.(!empty($attr['gbltipOffset']) ? (int)$attr['gbltipOffset'] : 0).','.(!empty($attr['gbltipDistance']) ? (int)$attr['gbltipDistance'] : 0).']"';

			$gblTooltip .= ' data-tippy-duration="['.(int)$attr['gbltipDurationIn'].','.(int)$attr['gbltipDurationOut'].']"';
			
			if($attr['gblTooltipType']=='content'){
				$contentItem['content'] = (!empty($attr['gblTooltipText']) && preg_match( '/data-tpgb-dynamic=(.*?)\}/', $attr['gblTooltipText'], $route ))  ? self::tpgb_dynamic_val($attr['gblTooltipText']) : (!empty($attr['gblTooltipText']) ? $attr['gblTooltipText'] : '');
			}else{
				$gblTooltipBlock = '';
				ob_start();
					if(!empty($attr['gblblockTemp'])) {
						echo Tpgb_Library()->plus_do_block($attr['gblblockTemp']);
					}
					$gblTooltipBlock = ob_get_contents();
				ob_end_clean();
				
				$contentItem['content'] = $gblTooltipBlock;
			}
			
			$contentItem['trigger'] = (!empty($attr['gbltipTriggers'])  ? $attr['gbltipTriggers'] : 'mouseenter');


			$gTooltipAttr .= 'data-tooltip-opt=\'' .htmlspecialchars(wp_json_encode($contentItem), ENT_QUOTES, 'UTF-8'). '\'';
			$ttId = 'id="global-'.esc_attr($uniqid).'"';
		}

		$check_event_tracker = get_option( 'tpgb_connection_data' );
		$eventTracker = false;
		$eventTattr = '';
		if(!empty($check_event_tracker) && isset($check_event_tracker['tpgb_event_tracking']) && ($check_event_tracker['tpgb_event_tracking']==='enable' && (!empty($attr['etFacebook']) || !empty($attr['etGoogle'])))){
			$eventTracker = true;
			$wrapInnerClass .= ' tpgb-event-tracker-inner';

			$propertiesAttr =[];
			if(!empty($attr['eventProperties'])){
				foreach ( $attr['eventProperties'] as $index => $item ) :
					if(!empty($item['eProName'])){
						$propertiesAttr[] =[
							$item['eProName'] => $item['eProValue']
						];
					} 
				endforeach;
			}
			
			$eAttr = [
				'facebook' => !empty($attr['etFacebook']) ? true : false,
				'fbEventType' => !empty($attr['fbEventType']) ? $attr['fbEventType'] : 'ViewContent',
				'fbCsmEventName' => !empty($attr['fbCsmEventName']) ? $attr['fbCsmEventName'] : '',
				'google' => !empty($attr['etGoogle']) ? true : false,
				'gglEventType' => !empty($attr['gglEventType']) ? $attr['gglEventType'] : 'recommended',
				'gglSelEvent' => !empty($attr['gglSelEvent']) ? $attr['gglSelEvent'] : 'ad_impression',
				'gCsmEventName' => !empty($attr['gCsmEventName']) ? $attr['gCsmEventName'] : '',
				'eventProperties' => $propertiesAttr,
			];
			$eventTattr .= 'data-event-opt=\'' .htmlspecialchars(wp_json_encode($eAttr), ENT_QUOTES, 'UTF-8'). '\'';
		}
		
		$PlusMouseParallax = false;
		$MouseParallaxAttr = '';
		if(!empty($attr['PlusMouseParallax']) && !empty($attr['PlusMouseParallax']['tpgbReset'])){
			$PlusMouseParallax = true;
			$MouseParallaxAttr .= ' data-speedx="'.(!empty($attr['PlusMouseParallax']['moveX']) ? $attr['PlusMouseParallax']['moveX'] : 30).'"';
			$MouseParallaxAttr .= ' data-speedy="'.(!empty($attr['PlusMouseParallax']['moveY']) ? $attr['PlusMouseParallax']['moveY'] : 30).'"';
			
			$wrapInnerClass .= ' tpgb-parallax-move';
		}
		
		$output = '';
		if(!empty($Plus3DTilt) || !empty($PlusMouseParallax) || !empty($globalTooltip) || !empty($advBorderRadius) || !empty($eventTracker)){
			$output .= '<div class="'.$wrapInnerClass.'" '.$tiltAttr.' '.$MouseParallaxAttr.' '.$gblTooltip.' '.$gTooltipAttr.' '.$ttId.' '.$eventTattr.'>';
		}
		
		if((!empty($attr['globalCssFilter']) && !empty($attr['globalCssFilter']['openFilter'])) || (!empty($attr['globalHCssFilter']['openFilter']) && !empty($attr['globalHCssFilter']))){
			$output .= '<div class="tpgb-cssfilters">';
		}
		
		echo $output;
	}
	
	/*
	 * Wrapper After Render
	 */
	public function tpgb_wrapper_after_render( $attr = [] ){
		if( empty($attr) ){
			return  '';
		}
		
		$Plus3DTilt = false;
		if(!empty($attr['Plus3DTilt']) && !empty($attr['Plus3DTilt']['tpgbReset'])){
			$Plus3DTilt = true;
		}
		$globalTooltip = false;
		if( !empty($attr['globalTooltip'])){
			$globalTooltip = true;
		}
		$advBorderRadius = false;
		if(!empty($attr['advBorderRadius']) && !empty($attr['advBorderRadius']['tpgbReset'])){
			$advBorderRadius = true;
		}

		$check_event_tracker = get_option( 'tpgb_connection_data' );
		$eventTracker = false;
		if(!empty($check_event_tracker) && isset($check_event_tracker['tpgb_event_tracking']) && ($check_event_tracker['tpgb_event_tracking']==='enable' && (!empty($attr['etFacebook']) || !empty($attr['etGoogle'])))){
			$eventTracker = true;
		}
		
		$PlusMouseParallax = false;
		if(!empty($attr['PlusMouseParallax']) && !empty($attr['PlusMouseParallax']['tpgbReset'])){
			$PlusMouseParallax = true;
		}
		
		$output = '';
		if((!empty($attr['globalCssFilter']) && !empty($attr['globalCssFilter']['openFilter'])) || (!empty($attr['globalHCssFilter']['openFilter']) && !empty($attr['globalHCssFilter']))){
			$output .= '</div>';
		}
		if(!empty($Plus3DTilt) || !empty($PlusMouseParallax) || !empty($globalTooltip) || !empty($advBorderRadius) || !empty($eventTracker)){
			$output .= '</div>';
		}
		
		echo $output;
	}
	
	/*
	 * Animation Out Options
	 */
	public static function tpgbAnimationOutDevice($globalAnim='', $AnimDirect='',$device=''){
		$animationVal = '';
		if($globalAnim=='fadeOut'){
			$animationVal .= (($AnimDirect[$device]==='' || $AnimDirect[$device]==='default') ? 'fadeOut' : 'fadeOut'.$AnimDirect[$device]);
		}else if($globalAnim=='slideOut'){
			$animationVal .= (($AnimDirect[$device]==='' || $AnimDirect[$device]==='default') ? 'slideOutDown' : 'slideOut'.$AnimDirect[$device]);
		}else if($globalAnim=='zoomOut'){
			$animationVal .= (($AnimDirect[$device]==='' || $AnimDirect[$device]==='default') ? 'zoomOut' : 'zoomOut'.$AnimDirect[$device]);
		}else if($globalAnim=='rotateOut'){
			$animationVal .= (($AnimDirect[$device]==='' || $AnimDirect[$device]==='default') ? 'rotateOut' : 'rotateOut'.$AnimDirect[$device]);
		}else if($globalAnim=='flipOut'){
			$animationVal .= (($AnimDirect[$device]==='' || $AnimDirect[$device]==='default') ? 'flipOutX' : 'flipOut'.$AnimDirect[$device]);
		}else if($globalAnim=='lightSpeedOut'){
			$animationVal .= (($AnimDirect[$device]==='' || $AnimDirect[$device]==='default') ? 'lightSpeedOutLeft' : 'lightSpeedOut'.$AnimDirect[$device]);
		}else if($globalAnim=='rollOut'){
			$animationVal .= 'rollOut';
		}
		return $animationVal;
	}
	
	/*
	 * Animation Out Options
	 */
	public function tpgb_globalAnimOut_options( $settings = [], $attr = [] ){
		if( !empty($attr) && !empty($attr['globalAnimOut']) ){
			if(!empty($attr['globalAnimOut']['md']) && $attr['globalAnimOut']['md']!='none'){
				$settings['check'] = true;
				if( !empty($attr['globalAnimDirectOut']) ){
					$settings['md'] = self::tpgbAnimationOutDevice($attr['globalAnimOut']['md'], $attr['globalAnimDirectOut'], 'md');
				}
			}
			if(!empty($attr['globalAnimOut']['sm']) && $attr['globalAnimOut']['sm']!='none'){
				$settings['check'] = true;
				if( !empty($attr['globalAnimDirectOut']) ){
					$settings['sm'] = self::tpgbAnimationOutDevice($attr['globalAnimOut']['sm'], $attr['globalAnimDirectOut'], 'sm');
				}
			}
			if(!empty($attr['globalAnimOut']['xs']) && $attr['globalAnimOut']['xs']!='none'){
				$settings['check'] = true;
				if( !empty($attr['globalAnimDirectOut']) ){
					$settings['xs'] = self::tpgbAnimationOutDevice( $attr['globalAnimOut']['xs'], $attr['globalAnimDirectOut'], 'xs');
				}
			}
		}
		return $settings;
	}
	
	/*
	 * Extra Css Js Load in  
	 
	public function tpgb_extra_css_js_loading( $blocks=[] ){
		$extra_js = [];
		if(tpgb_has_lazyload()){
			$extra_js[] = 'tpgb_lazyLoad'; 
		}
		$blocks = array_merge( $extra_js,$blocks );

		return $blocks;
	}*/
	
	/*
	 * Render Block Condition Check 
	 */
	public function tpgb_has_blocks_options( $blocks=[], $options='' , $blockname='' ){
		$pro_blocks = [];
		
		if($blockname=='tpgb/tp-accordion' && !empty($options) && !empty($options['hoverStyle']) && $options['hoverStyle']!='none'){
			$pro_blocks[] = 'tpx-accordion-'.$options['hoverStyle'];
		}

		// TP Audio Player
		if($blockname=='tpgb/tp-audio-player' && !empty($options) ){
			if( !empty($options['Apstyle']) ){
				$pro_blocks[] = 'tpx-audio-player-'.$options['Apstyle'];
			}
		}

		// Antthing Carousel
		if($blockname=='tpgb/tp-anything-carousel' && !empty($options) ){
			if( isset($options['slideautoScroll']) && !empty($options['slideautoScroll']) ){
				$pro_blocks[] = 'carouselautoScroll';
			}
		}

		//Carousel Remote
		if($blockname=='tpgb/tp-carousel-remote' && !empty($options)){
			if( isset($options['showDot']) && !empty($options['showDot']) ){
				$pro_blocks[] = 'tpgx-carousel-dot';

				if( isset($options['dotstyle']) && !empty($options['dotstyle']) && $options['dotstyle'] == 'style-2' ){
					$pro_blocks[] = 'tpgx-carousel-tooltip';
				}

			}
			if( isset($options['showpagi']) && !empty($options['showpagi']) ){
				$pro_blocks[] = 'tpgx-carousel-pagination';
			}
			if( isset($options['carobtn']) && !empty($options['carobtn']) ){
				$pro_blocks[] = 'tpgx-carousel-button';
			}else{
				$pro_blocks[] = 'tpgx-carousel-button';
			}
		}

		if(!empty($options) && !empty($options['globalAnimOut']) && ((!empty($options['globalAnimOut']['md']) && $options['globalAnimOut']['md']!='none') || (!empty($options['globalAnimOut']['sm']) && $options['globalAnimOut']['sm']!='none') || (!empty($options['globalAnimOut']['xs']) && $options['globalAnimOut']['xs']!='none'))){
			$pro_blocks[] = 'tpgb-animation';
			if(isset($options['globalAnimOut']['md']) && $options['globalAnimOut']['md']!='none'){
				$pro_blocks[] = 'tpgb-animation-'.$options['globalAnimOut']['md'];
			}
			if(isset($options['globalAnimOut']['sm']) && $options['globalAnimOut']['sm']!='none'){
				$pro_blocks[] = 'tpgb-animation-'.$options['globalAnimOut']['sm'];
			}
			if(isset($options['globalAnimOut']['xs']) && $options['globalAnimOut']['xs']!='none'){
				$pro_blocks[] = 'tpgb-animation-'.$options['globalAnimOut']['xs'];
			}
		}
		
		if(!empty($options) && !empty($options['contentHoverEffect']) && !empty($options['selectHoverEffect'])){
			$pro_blocks[] = 'content-hover-effect';
		}

		// CTA Banner
		if($blockname=='tpgb/tp-cta-banner' ){
			if( !empty($options) && !empty($options['styleType']) ){
				$pro_blocks[] = 'tpx-cta-'.$options['styleType'];
			}else{
				$pro_blocks[] = 'tpx-cta-style-1';
			}
		}

		if((!empty($options) && !empty($options['continueAnimation'])) || ($blockname=='tpgb/tp-dynamic-device' && !empty($options) && !empty($options['iconConAni']))){
			$pro_blocks[] = 'continue-animation';
		}
		if(!empty($options) && !empty($options['tpgbEqualHeight'])){
			$pro_blocks[] = 'equal-height';
		}
		if(!empty($options) && !empty($options['globalTooltip'])){
			$pro_blocks[] = 'global-tooltip';
		}
        if(!empty($options) && !empty($options['PlusMagicScroll']) ){        //Global Magic Scroll
            $pro_blocks[] = 'tpgb-magic-scroll';
            $pro_blocks[] = 'tpgb-magic-scroll-custom';
        }

		/** TP Advance Heading */
		if($blockname=='tpgb/tp-heading-title'){
			if(!empty($options) && isset( $options['style'] ) && !empty( $options['style'] ) && $options['style'] == 'style-9' ){
				$pro_blocks[] = 'tpx-heading-title-style-9';
			}
		}

		/** Tp Hotspot */
		if($blockname =='tpgb/tp-hotspot'){
			if(!empty($options) && !empty($options['hveOverlay'])){
				$pro_blocks[] = 'tpx-hover-overlay';
			}
			if(!empty($options) && !empty($options['pinlistRepeater']) && is_array($options['pinlistRepeater']) ){
				foreach( $options['pinlistRepeater'] as $key => $val ){
					if(!empty($val) && isset($val['contEffect']) && !empty($val['contEffect'])){
						$pro_blocks[] = 'tpgb-plus-hover-effect';
					}
				}
			}
		}
		/*Tp Row Background*/
		if($blockname=='tpgb/tp-row' || $blockname=='tpgb/tp-container'){
			if(!empty($options) && ( !empty($options['deepBgopt']) && $options['deepBgopt'] == 'bg_color' ) ){	//Animated Color
				$pro_blocks[] = 'tpgb-row-animated-color';   
			}
			
			if( !empty($options) && !empty($options['deepBgopt']) && $options['deepBgopt'] == 'bg_image' && isset($options['imgeffect']) && !isset($options['craBgeffect']) && !empty($options['imgeffect']) && ( $options['imgeffect'] == 'style-1' || $options['imgeffect'] == 'style-2' ) ) {
				$pro_blocks[] = 'tpgb-image-parallax'; 
			}

			if( !empty($options) && ( (!empty($options['deepBgopt']) && $options['deepBgopt'] == 'bg_image' && isset($options['craBgeffect']) && !empty($options['craBgeffect']) && $options['craBgeffect'] = 'columns_animated_bg') || ( !empty($options['midOption']) && $options['midOption'] == 'moving_image' ) ) ){
				$pro_blocks[] = 'tpgb-image-moving';
			}	

			if( !empty($options) && !empty($options['deepBgopt']) && $options['deepBgopt'] == 'bg_image'  && isset($options['scrollPara']) && !empty($options['scrollPara']) ){
				$pro_blocks[] = 'tpgb-scroll-parallax';
			} 

			if( !empty($options) && !empty($options['deepBgopt']) && $options['deepBgopt'] == 'bg_video' && !isset($options['videosour'])  && isset($options['youtubeId']) && !empty($options['youtubeId']) ){
				$pro_blocks[] = 'tpgb-youtube-video';
			}

			if( !empty($options) && !empty($options['deepBgopt']) && $options['deepBgopt'] == 'bg_video' && isset($options['videosour']) && !empty($options['videosour']) && $options['videosour'] == 'vimeo' ){
				$pro_blocks[] = 'tpgb-vimeo-video';
			}

			if( !empty($options) && !empty($options['deepBgopt']) && $options['deepBgopt'] == 'bg_video' && isset($options['videosour']) && !empty($options['videosour']) && $options['videosour'] == 'self-hosted' ){
				$pro_blocks[] = 'tpgb-self-hosted-video';
			}

			if( !empty($options) && !empty($options['deepBgopt']) && $options['deepBgopt'] == 'bg_gallery' ){
				$pro_blocks[] = 'tpgb-bg-gallery';
			}

			if( !empty($options) && !empty($options['midOption']) && ( $options['midOption'] == 'mordern_image_effect' ) ){
				$pro_blocks[] = 'tpgb-magic-scroll';
            	$pro_blocks[] = 'tpgb-magic-scroll-custom';
				$pro_blocks[] = 'tpgb-mordern-parallax';
			}
			
			if(!empty($options) && ( !empty($options['scrollPara']) || ( !empty($options['midOption']) && $options['midOption'] == 'mordern_image_effect') ) ){	//scroll Parallax/Mordern Parallax
				$pro_blocks[] = 'tpgb-magic-scroll';
			}

			if( !empty($options) && !empty($options['midOption']) && $options['midOption'] == 'canvas' && isset($options['canvasSty']) && !empty($options['canvasSty']) ){	//Canvas
				$pro_blocks[] = 'tpgb-canvas-particle';
			}

			if( !empty($options) && !empty($options['midOption']) && $options['midOption'] == 'canvas' && isset($options['canvasSty']) && !empty($options['canvasSty']) && $options['canvasSty'] == 'style-4' ){
				$pro_blocks[] = 'tpgb-canvas-particleground';   
			}

			if(!empty($options) && !empty($options['deepBgopt']) && ($options['deepBgopt'] == 'bg_animate_gradient' || $options['deepBgopt'] == 'scroll_animate_color' ) ){	//scroll bg color
				$pro_blocks[] = 'tpgb-scrollbg-animation';   
			}
			if( !empty($options) && (!empty($options['shapeTop']) || !empty($options['shapeBottom'])) ){
				$pro_blocks[] = 'tpx-tp-shape-divider';
			}

			if( !empty($options['midimgList']) && is_array($options['midimgList']) ){
				foreach( $options['midimgList'] as $key => $val ){
					if(!empty($val) && isset($val['modImgeff']) && !empty($val['modImgeff'])){
						$pro_blocks[] = 'tpgb-plus-hover-effect';
					}
				}
			}

			if( !empty($options) && isset( $options['contSticky'] ) && !empty($options['contSticky']) ){
				$contSticky = $options['contSticky'];
				if( (isset( $contSticky['md']) && !empty($contSticky['md'])) || (isset( $contSticky['sm']) && !empty($contSticky['sm'])) || (isset( $contSticky['xs']) && !empty($contSticky['xs'])) ){
					if( isset($options['constType']) && !empty($options['constType']) && $options['constType'] == 'sticky' ){
						$pro_blocks[] = 'tpx_sticky_only';
					}else{
						$pro_blocks[] = 'tpx_sticky_normal_asset';
					}
				}
			}

			if( !empty($options) && isset( $options['contOverlays'] ) && !empty($options['contOverlays']) ){
				$pro_blocks[] = 'tpgb_overlays_css';
			}
		}
		/*Column*/
		if($blockname=='tpgb/tp-column' || $blockname=='tpgb/tp-container-inner'){
			if( !empty($options) && !empty($options['stickycol']) ){
				$pro_blocks[] = 'tpgb-sticky-col';
			}
		}
		/*Dynamic Category*/
		if($blockname=='tpgb/tp-dynamic-category') {
	
			if(!empty($options) && !empty($options['layout']) && $options['layout']=='carousel'){
				$pro_blocks[] = 'carouselSlider';
			}else if(!empty($options) && !empty($options['layout']) && $options['layout']=='masonry'){
				$pro_blocks[] = 'tpgb-masonary-layout';	
			}

			if(!empty($options) && !empty($options['layout']) && $options['layout']=='metro'){
				$pro_blocks[] = 'tpgb-metro-layout';
				$pro_blocks[] = 'tpx-dy-metro';
			}

			if( !empty($options) && !empty($options['style']) ){
				$pro_blocks[] = 'tpx-dy-cate-'.$options['style'];
			}else{
				$pro_blocks[] = 'tpx-dy-cate-style_1';
			}
		}
		/*Media Listing*/
		if($blockname=='tpgb/tp-media-listing') {
			if(!empty($options) && !empty($options['layout']) && $options['layout']=='carousel'){

				if(!empty($options) && isset($options['slideautoScroll']) && !empty($options['slideautoScroll']) ){
					$pro_blocks[] = 'carouselautoScroll';
				}
				$pro_blocks[] = 'carouselSlider';
			}else{
				$pro_blocks[] = 'tpgb-masonary-layout';
			}

			if(!empty($options) && !empty($options['layout']) && $options['layout']=='metro'){
				$pro_blocks[] = 'tpx-media-metro-style';
				$pro_blocks[] = 'tpgb-metro-layout';
			}

			if( !empty($options) && !empty($options['Category']) ){
				$pro_blocks[] = 'tpgb-category-filter';
			}

			if( !empty($options) && !empty($options['style']) ){
				$pro_blocks[] = 'tpx-media-listing-'.$options['style'];
			}else{
				$pro_blocks[] = 'tpx-media-listing-style-1';
			}
		}
		/*Post Listing*/
		if($blockname=='tpgb/tp-post-listing'){
			if( !empty($options) && ( !empty($options['ShowFilter']) ) ){
				$pro_blocks[] = 'tpgb-category-filter';  
			}

			if(!empty($options) && !empty($options['layout']) && $options['layout']=='carousel'){	//Carousel

				if(!empty($options) && isset($options['slideautoScroll']) && !empty($options['slideautoScroll']) ){
					$pro_blocks[] = 'carouselautoScroll';
				}
				$pro_blocks[] = 'carouselSlider';
			}else if(!empty($options) && !empty($options['layout']) && $options['layout']=='metro'){
				$pro_blocks[] = 'tpgb-metro-layout';
			}else{
				$pro_blocks[] = 'tpgb-masonary-layout';
			}

			if(!empty($options) && !empty($options['style']) && $options['style'] =='style-3' && !empty($options['ShowButton'])){	//Button Group
				$pro_blocks[] = 'tpgb-group-button';   
			}
			if(!empty($options) && !empty($options['postLodop']) && ( $options['postLodop'] == 'pagination' )  ){	//Pagination
				$pro_blocks[] = 'tpgb-pagination';  
			}
			if(!empty($options) && !empty($options['postLodop']) && ( $options['postLodop'] == 'load_more' )  ){	//Post Load More Ajax 
				$pro_blocks[] = 'tpgb-post-load-more-ajax';  
			}
			if(!empty($options) && !empty($options['postLodop']) && ( $options['postLodop'] == 'lazy_load' )  ){	//Post Lazy Load Ajax
				$pro_blocks[] = 'tpgb-post-lazy-load-ajax';  
			}
			if( !empty($options) && isset($options['style']) && !empty($options['style']) ){
				$pro_blocks[] = 'tpx-post-'.$options['style'];
			}else{
				$pro_blocks[] = 'tpx-post-style-1';
			}
			if( !empty($options) && isset($options['layout']) && $options['layout'] == 'metro' ){
				$pro_blocks[] = 'tpx-post-metro';
			}

			
			if( !empty($options) && isset($options['postLodop']) && $options['postLodop'] == 'pagination' && isset($options['pagiOpt']) && $options['pagiOpt'] == 'ajax_based' ){
				$pro_blocks[] = 'tpx-post-pagination-ajax';
			}
		}
		/*Product Listing*/
		if($blockname=='tpgb/tp-product-listing') {

			if( !empty($options) && ( !empty($options['ShowFilter']) ) ){
				$pro_blocks[] = 'tpgb-category-filter';  
			}

			if( !empty($options) && !empty($options['layout']) && $options['layout'] == 'carousel'){ 

				if(!empty($options) && isset($options['slideautoScroll']) && !empty($options['slideautoScroll']) ){
					$pro_blocks[] = 'carouselautoScroll';
				}

				$pro_blocks[] = 'carouselSlider';
			}else{
				$pro_blocks[] = 'tpgb-masonary-layout';
			}

			if(!empty($options) && !empty($options['postLodop']) && ( $options['postLodop'] == 'pagination' )  ){	//Post Load Ajax
				$pro_blocks[] = 'tpgb-pagination';  
			}
			if(!empty($options) && !empty($options['postLodop']) && ( $options['postLodop'] == 'load_more' )  ){	//Post Load Ajax
				$pro_blocks[] = 'tpgb-post-load-more-ajax';  
			}
			if(!empty($options) && !empty($options['postLodop']) && ( $options['postLodop'] == 'lazy_load' )  ){	//Post Load Ajax
				$pro_blocks[] = 'tpgb-post-lazy-load-ajax';  
			}

			if( !empty($options) && isset($options['style']) && !empty($options['style']) ){
				$pro_blocks[] = 'tpx-product-'.$options['style'];
			}else{
				$pro_blocks[] = 'tpx-product-style-1';
			}
			if( !empty($options) && isset($options['layout']) && $options['layout'] == 'metro' ){
				$pro_blocks[] = 'tpgb-metro-layout';
				$pro_blocks[] = 'tpx-product-metro';
			}

			if( !empty($options) && isset($options['postLodop']) && $options['postLodop'] == 'pagination' && isset($options['pagiOpt']) && $options['pagiOpt'] == 'ajax_based' ){
				$pro_blocks[] = 'tpx-post-pagination-ajax';
			}
		}
		
		/*Navigation Menu*/
		if($blockname=='tpgb/tp-navigation-builder' && !empty($options) && !empty($options['resmenuType']) && $options['resmenuType']=='swiper'){
			$pro_blocks[] = 'swiperJs';
		}
		if($blockname=='tpgb/tp-navigation-builder' && !empty($options) && !empty($options['accessWeb']) ){
			$pro_blocks[] = 'tpgb-web-access';
		}
		/*Tabs Tours*/
		if($blockname=='tpgb/tp-tabs-tours' && !empty($options) && !empty($options['swiperEffect'])){
			$pro_blocks[] = 'swiperJs';
		}
		if($blockname=='tpgb/tp-tabs-tours' && !empty($options) && !empty($options['tabLayout']) && $options['tabLayout']=='vertical'){
			$pro_blocks[] = 'tpx-tabs-tours-vertical';
		}
		
		/* Scroll Navigation  */
		if($blockname=='tpgb/tp-scroll-navigation'){
			if(!empty($options) && !empty($options['styletype'])){
				$pro_blocks[] = 'tpx-scroll-navigation-'.$options['styletype'];
			}else{
				$pro_blocks[] = 'tpx-scroll-navigation-style-1';
			}
			if(!empty($options) && isset($options['disCounter']) && !empty($options['disCounter'])){
				$pro_blocks[] = 'tpx-display-counter';
			}
			if(!empty($options) && isset($options['scrolloff']) && !empty($options['scrolloff'])){
				$pro_blocks[] = 'tpx-scroll-offset';
			}
		}

		/*switcher*/
		if($blockname=='tpgb/tp-switcher' && !empty($options) && !empty($options['switchStyle'])){
			$pro_blocks[] = 'tpx-switcher-'.$options['switchStyle'];
		}else if($blockname=='tpgb/tp-switcher'){
			$pro_blocks[] = 'tpx-switcher-style-1';
		}
		
		/*Post Navigation*/
		if($blockname=='tpgb/tp-post-navigation'){
			if( !empty($options) && !empty($options['style']) ){
				$pro_blocks[] = 'tpx-post-navigation-'.$options['style'];
			}else{
				$pro_blocks[] = 'tpx-post-navigation-style-1';
			}
		}
		
		/*Table of Content*/
		if($blockname=='tpgb/tp-table-content'){
			if( !empty($options) && !empty($options['Style']) ){
				$pro_blocks[] = 'tpx-table-content-'.$options['Style'];
			}
		}

		/*Tp Adv Typo*/
		if($blockname=='tpgb/tp-adv-typo'){
			if(!empty($options)){
				if( !empty($options['advUnderline']) && $options['advUnderline']=='overlay' ){
					$pro_blocks[] = 'tpx-adv-typo-normal-overlay';
				}
				if(isset($options['typoListing']) && !empty($options['typoListing'])){
					if( $options['typoListing']=='multiple' ){
						$pro_blocks[] = 'tpx-adv-typo-multiple';
					}
					if( $options['typoListing']=='multiple' || ($options['typoListing']=='normal' && !empty($options['onHoverImg']) && !empty($options['hoverImg'])) ){
						$pro_blocks[] = 'tpx-adv-typo-hover-img-reavel';
					}
				}else if(isset($options['onHoverImg']) && !empty($options['onHoverImg']) && !empty($options['hoverImg'])){
					$pro_blocks[] = 'tpx-adv-typo-hover-img-reavel';
				}
			}
		}
		
		/*Creative Image*/
		if($blockname=='tpgb/tp-creative-image' && !empty($options)){	
			if( !empty($options['ScrollParallax']) ){
				$pro_blocks[] = 'tpgb-magic-scroll';
			}
			if( !empty($options['ScrollImgEffect']) ){
				$pro_blocks[] = 'tpx-tp-image-scroll-effect';
			}
			if( !empty($options['showMaskImg']) ){
				$pro_blocks[] = 'tpx-tp-image-mask-img';
			}
			if( !empty($options['ScrollRevelImg']) ){
				$pro_blocks[] = 'tpx-tp-image-animate';
			}
			if( !empty($options['ScrollParallax']) ){
				$pro_blocks[] = 'tpx-tp-image-parallax';
			}
		}
		/*CountDown*/
		if($blockname=='tpgb/tp-countdown'){
			if( !empty($options) && !empty($options['style'])) {
				$pro_blocks[] = 'countdown-'.$options['style'];
			}else{
				$pro_blocks[] = 'countdown-style-1';
			}

			if( !empty($options) && !empty($options['countdownSelection']) && $options['countdownSelection'] == 'numbers') {
				$pro_blocks[] = 'countdown-fakestyle';
			}
		}
		
		/*Cta Banner*/
		if(!empty($options) && !empty($options['hoverStyle']) && $options['hoverStyle']=='hover-tilt'){	
			$pro_blocks[] = 'hoverTilt';
		}
		/*Google Map*/
		if($blockname=='tpgb/tp-google-map' && !empty($options) && !empty($options['contentTgl'])){	
			$pro_blocks[] = 'tpx-google-map-content';
		}

		/*Team Listing*/
		if($blockname=='tpgb/tp-team-listing'){
			if( !empty($options) && !empty($options['layout']) && $options['layout']=='carousel'){

				if(!empty($options) && isset($options['slideautoScroll']) && !empty($options['slideautoScroll']) ){
					$pro_blocks[] = 'carouselautoScroll';
				}

				$pro_blocks[] = 'carouselSlider';
			}

			if( !empty($options) && !empty($options['Style'])){
				$pro_blocks[] = 'tpx-team-list-'.$options['Style'];
			}else{
				$pro_blocks[] = 'tpx-team-list-style-1';
			}

			if( !empty($options) && !empty($options['CategoryWF']) ){
				$pro_blocks[] = 'tpgb-category-filter';
			}
		}

		/*popup builder*/
		if( $blockname=='tpgb/tp-popup-builder' ){

			if( !empty($options) && isset($options['toggleIconStyle']) && !empty($options['toggleIconStyle']) ){
				$pro_blocks[] = 'tpx-humberger-'.$options['toggleIconStyle'].'';
			}else{
				$pro_blocks[] = 'tpx-humberger-style-1';
			}

			if(!empty($options) && isset($options['fixedToggleBtn']) && !empty($options['fixedToggleBtn'])){
				$pro_blocks[] = 'tpx-fixed-popup-toggle';
			}

			if(!empty($options) && isset($options['openStyle']) && $options['openStyle'] == 'slide' ){
				$pro_blocks[] = 'tpx-slide';
			}else if( !empty($options) && isset($options['openStyle']) && $options['openStyle'] == 'push' ){
				$pro_blocks[] = 'tpx-push-content';
			}else if( !empty($options) && isset($options['openStyle']) && $options['openStyle'] == 'slide-along' ){
				$pro_blocks[] = 'tpx-slide-along';
			}else if( !empty($options) && isset($options['openStyle']) && $options['openStyle'] == 'corner-box' ){
				$pro_blocks[] = 'tpx-corner-box';
			}else{
				$pro_blocks[] = 'tpx-popup-effect';
			}

			if( !empty($options) && !isset($options['openStyle']) ){
				$pro_blocks[] = 'tpgb-popup-animation';
			}
			
			if( !empty($options) && !isset($options['openStyle'])  ){
				
				if( isset($options['inAnimation']) && !empty($options['inAnimation']) ) {
					if( $options['inAnimation'] == 'slideInDown' || $options['inAnimation'] == 'slideInLeft' || $options['inAnimation'] == 'slideInRight' || $options['inAnimation'] == 'slideInUp' ){
						$pro_blocks[] = 'tpgb-animation-slideIn';
					}else if( $options['inAnimation'] == 'zoomIn' || $options['inAnimation'] == 'zoomInDown' || $options['inAnimation'] == 'zoomInLeft' || $options['inAnimation'] == 'zoomInRight' || $options['inAnimation'] == 'zoomInUp' ){
						$pro_blocks[] = 'tpgb-animation-zoomIn';
					}else if( $options['inAnimation'] == 'rotateIn' || $options['inAnimation'] == 'rotateInDownLeft' || $options['inAnimation'] == 'rotateInDownRight' || $options['inAnimation'] == 'rotateInUpLeft' || $options['inAnimation'] == 'rotateInUpRight' ){
						$pro_blocks[] = 'tpgb-animation-rotateIn';
					}else if( $options['inAnimation'] == 'flipInX' || $options['inAnimation'] == 'flipInY' ){
						$pro_blocks[] = 'tpgb-animation-flipIn';
					}else if( $options['inAnimation'] == 'lightSpeedInLeft' || $options['inAnimation'] == 'lightSpeedInRight' ){
						$pro_blocks[] = 'tpgb-animation-lightSpeedIn';
					}else if( $options['inAnimation'] == 'bounce' || $options['inAnimation'] == 'flash' || $options['inAnimation'] == 'pulse' || $options['inAnimation'] == 'rubberBand' || $options['inAnimation'] == 'shakeX' || $options['inAnimation'] == 'shakeY' || $options['inAnimation'] == 'headShake' || $options['inAnimation'] == 'swing' || $options['inAnimation'] == 'tada' || $options['inAnimation'] == 'wobble' || $options['inAnimation'] == 'jello' || $options['inAnimation'] == 'heartBeat'  ){
						$pro_blocks[] = 'tpgb-animation-seekers';
					}else if( $options['inAnimation'] == 'rollIn' ){
						$pro_blocks[] = 'tpgb-animation-rollIn';
					}else{
						$pro_blocks[] = 'tpgb-animation-fadeIn';
					}
				} else{
					$pro_blocks[] = 'tpgb-animation-fadeIn';
				}
				
				if( isset($options['outAnimation']) && !empty($options['outAnimation']) ) {
					if( $options['outAnimation'] == 'slideOutDown' || $options['outAnimation'] == 'slideOutLeft' || $options['outAnimation'] == 'slideOutRight' || $options['outAnimation'] == 'slideOutUp' ){ 
						$pro_blocks[] = 'tpgb-animation-slideOut';
					} else if( $options['outAnimation'] == 'zoomOut' || $options['outAnimation'] == 'zoomOutDown' || $options['outAnimation'] == 'zoomOutLeft' || $options['outAnimation'] == 'zoomOutRight' || $options['outAnimation'] == 'zoomOutUp' ){
						$pro_blocks[] = 'tpgb-animation-zoomOut';
					} else if( $options['outAnimation'] == 'rotateOut' || $options['outAnimation'] == 'rotateOutDownLeft' || $options['outAnimation'] == 'rotateOutDownRight' || $options['outAnimation'] == 'rotateOutUpLeft' || $options['outAnimation'] == 'rotateOutUpRight'  ){
						$pro_blocks[] = 'tpgb-animation-rotateOut';
					} else if( $options['outAnimation'] == 'flipOutX' || $options['outAnimation'] == 'flipOutY'  ){
						$pro_blocks[] = 'tpgb-animation-flipOut';
					} else if( $options['outAnimation'] == 'lightSpeedOutLeft' || $options['outAnimation'] == 'lightSpeedOutRight'  ){
						$pro_blocks[] = 'tpgb-animation-lightSpeedOut';
					} else if( $options['outAnimation'] == 'rollOut' ){
						$pro_blocks[] = 'tpgb-animation-rollOut';
					}else{
						$pro_blocks[] = 'tpgb-animation-fadeOut';
					}
				}else{
					$pro_blocks[] = 'tpgb-animation-fadeOut';
				}
			}
		}

		/*social Review*/
		if($blockname=='tpgb/tp-social-reviews' && !empty($options)){
			if(!empty($options['RType']) && $options['RType']=='beach'){
				if(!empty($options['Bstyle'])){
					$pro_blocks[] = 'tpx-beach-'.$options['Bstyle'];
				}else{
					$pro_blocks[] = 'tpx-beach-style-1';
				}
			}else{
				if(!empty($options['style'])){
					$pro_blocks[] = 'tpx-review-'.$options['style'];
				}else{
					$pro_blocks[] = 'tpx-review-style-1';
				}
			}
			if(!empty($options['layout']) && $options['layout'] == 'carousel'){

				if(!empty($options) && isset($options['slideautoScroll']) && !empty($options['slideautoScroll']) ){
					$pro_blocks[] = 'carouselautoScroll';
				}

				$pro_blocks[] = 'carouselSlider';
			}
			if(!empty($options['postLodop']) && ($options['postLodop'] == 'load_more' || $options['postLodop'] == 'lazy_load')){
				$pro_blocks[] = 'review-feed-load';
			}

            if( !empty($options) && !empty($options['CategoryWF']) ){
                $pro_blocks[] = 'tpgb-category-filter';
            }
		}

		/*social Sharing*/
		if($blockname=='tpgb/tp-social-sharing' && !empty($options)){
			if(!empty($options['sociallayout'])){
				$pro_blocks[] = 'tpx-social-sharing-'.$options['sociallayout'];
			}else{
				$pro_blocks[] = 'tpx-social-sharing-horizontal';
			}
		}

		/*social Feed*/
		if($blockname=='tpgb/tp-social-feed' && !empty($options)){
			if(!empty($options['style']) && ($options['style']=='style-3' || $options['style']=='style-4')){
				$pro_blocks[] = 'tpx-social-feed-'.$options['style'];
			}
			
			if( !empty($options['layout']) && $options['layout'] == 'masonry' ){
				$pro_blocks[] = 'tpgb-masonary-layout';
			}

			if( !empty($options['layout']) && $options['layout'] == 'carousel' ){

				if(!empty($options) && isset($options['slideautoScroll']) && !empty($options['slideautoScroll']) ){
					$pro_blocks[] = 'carouselautoScroll';
				}

				$pro_blocks[] = 'carouselSlider';
			}else{
				$pro_blocks[] = 'tpgb-masonary-layout';
			}

			if(!empty($options['postLodop']) && ($options['postLodop'] == 'load_more' || $options['postLodop'] == 'lazy_load')){
				$pro_blocks[] = 'review-feed-load';
			}
			if( ( !empty($options['layout']) && $options['layout']!='carousel') && ( !empty($options['CategoryWF']) || !empty($options['postLodop'] ) ) ){
				$pro_blocks[] = 'tpgb-category-filter';
			}

            if( !empty($options) && !empty($options['CategoryWF']) ){
                $pro_blocks[] = 'tpgb-category-filter';
            }
		}

		/*Circle Menu*/
		if($blockname=='tpgb/tp-circle-menu' && !empty($options)){
			if(!empty($options['layoutType']) && $options['layoutType']=='straight'){
				$pro_blocks[] = 'tpx-circle-menu-straight';
			}
			if(!empty($options['tglStyle'])){
				$pro_blocks[] = 'tpx-circle-menu-toggle-'.$options['tglStyle'];
			}
			if(!empty($options['overlayColorTgl'])){
				$pro_blocks[] = 'tpx-circle-menu-overlay';
			}
		}

		/* Tp Testimonial  */
		if($blockname=='tpgb/tp-testimonials'){

			if(!empty($options) && isset($options['slideautoScroll']) && !empty($options['slideautoScroll']) ){
				$pro_blocks[] = 'carouselautoScroll';
			}

			if( !empty($options) && !empty($options['telayout']) && $options['telayout']!='carousel' ){
				$pro_blocks[] = 'tpgb-masonary-layout';
			}else{
				$pro_blocks[] = 'carouselSlider';
			}
			
			if( !empty($options) && !empty($options['style']) ){
				$pro_blocks[] = 'tpx-testimonials-'.$options['style'];
			}else{
				$pro_blocks[] = 'tpx-testimonials-style-1';
			}

			if( ( !empty($options) &&  !empty($options['telayout']) && $options['telayout'] == 'grid' ) || ( isset($options['caroByheight']) && !empty( $options['caroByheight'] ) && $options['caroByheight'] == 'height' ) ){
				$pro_blocks[] = 'tpx-testimonials-scroll';
			}
		}

		/* Advanced Buttons */
		if($blockname=='tpgb/tp-advanced-buttons'){
			if( !empty($options) && !empty($options['btnType'])) {
				if($options['btnType']=='download'){
					if(!empty($options['dwnldStyle'])){
						$pro_blocks[] = 'tpx-adv-btn-dwnld-'.$options['dwnldStyle'];
					}else{
						$pro_blocks[] = 'tpx-adv-btn-dwnld-style-1';
					}
				}else{
					if(!empty($options['ctaStyle'])){
						$pro_blocks[] = 'tpx-adv-btn-cta-'.$options['ctaStyle'];
					}else{
						$pro_blocks[] = 'tpx-adv-btn-cta-style-1';
					}
				}
			}else{
				if(!empty($options['ctaStyle'])){	
					$pro_blocks[] = 'tpx-adv-btn-cta-'.$options['ctaStyle'];
				}else{
					$pro_blocks[] = 'tpx-adv-btn-cta-style-1';
				}
			}
		}

		/* Animated Service Boxes */
		if($blockname=='tpgb/tp-animated-service-boxes'){
			if(!empty($options) && !empty($options['mainStyleType'])){	
				$pro_blocks[] = 'tpx-animated-service-boxes-'.$options['mainStyleType'];
			}else{
				$pro_blocks[] = 'tpx-animated-service-boxes-image-accordion';
			}
			if(!empty($options) && !empty($options['disBtn'])) {
				$pro_blocks[] = 'tpgb-group-button';
			}
		}

		/* Mouse Cursor */
		if($blockname=='tpgb/tp-mouse-cursor' && !empty($options) && !empty($options['cursorType'])){
			$pro_blocks[] = 'tpx-'.$options['cursorType'];
		}

		/* Pricing Table */
		if($blockname=='tpgb/tp-pricing-table'){
			/* Layout */
			if( !empty($options) && !empty($options['style'])) {
				$pro_blocks[] = 'tpx-pricing-table-layout-'.$options['style'];
			}else{
				$pro_blocks[] = 'tpx-pricing-table-layout-style-1';
			}

			/* Price */
			if( !empty($options) && !empty($options['priceStyle'])) {
				$pro_blocks[] = 'tpx-pricing-table-price-'.$options['priceStyle'];
			}else{
				$pro_blocks[] = 'tpx-pricing-table-price-style-1';
			}

			/* Content */
			if( !empty($options) && !empty($options['contentStyle'])) {
				$pro_blocks[] = 'tpx-pricing-table-content-'.$options['contentStyle'];
			}else{
				$pro_blocks[] = 'tpx-pricing-table-content-wysiwyg';
			}

			/* Ribbon */
			if( !empty($options) && !empty($options['disRibbon'])) {
				$pro_blocks[] = 'tpx-pricing-table-ribbon';
			}
		}

		/* Process Steps */
		if($blockname=='tpgb/tp-process-steps'){
			/* Style */
			if( !empty($options) && !empty($options['style'])) {
				$pro_blocks[] = 'tpx-process-steps-'.$options['style'];
			}else{
				$pro_blocks[] = 'tpx-process-steps-style-1';
			}

			/* Counter */
			if( !empty($options) && !empty($options['displayCounter'])) {
				$pro_blocks[] = 'tpx-process-steps-counter';
			}

			/* Ring BG */
			if( !empty($options) && !empty($options['specialBG'])) {
				$pro_blocks[] = 'tpx-process-steps-ring-bg';
			}
		}

		/* Tp Heading Animation  */
		if($blockname=='tpgb/tp-heading-animation'){
			if(!empty($options) && !empty($options['style']) && $options['style']=='textAnim'){
				if(!empty($options) && !empty($options['textAnimStyle'])){
					$pro_blocks[] = 'tpx-heading-textAnim-'.$options['textAnimStyle'];
				}else{
					$pro_blocks[] = 'tpx-heading-textAnim-style-1';
				}
			}else{
				$pro_blocks[] = 'tpx-heading-animation-highlights';
			}
		}

		/* Tp MailChimp */
		if($blockname=='tpgb/tp-mailchimp'){
			if(!empty($options) && !empty($options['styleType'])){
				$pro_blocks[] = 'tpx-mailchimp-'.$options['styleType'];
			}else{
				$pro_blocks[] = 'tpx-mailchimp-style-1';
			}
			if(!empty($options) && !empty($options['gdprCompli'])){
				$pro_blocks[] = 'tpx-mailchimp-gdpr';
			}
		}

		/* Login Register */
		if($blockname=='tpgb/tp-login-register'){
			if(!empty($options) && !empty($options['formLayout']) && $options['formLayout'] == 'button' ){
				$pro_blocks[] = 'tpx-form-button';
			}

			if(!empty($options) && !empty($options['formType']) && $options['formType'] == 'login-register' ){
				$pro_blocks[] = 'tpx-form-tab';
			}
		}
		
		/* Mobile Menu */
		if($blockname=='tpgb/tp-mobile-menu'){
			/* Style */
			if( !empty($options) && !empty($options['mmStyle'])) {
				$pro_blocks[] = 'tpx-mobile-menu-'.$options['mmStyle'];
			}else{
				$pro_blocks[] = 'tpx-mobile-menu-style-1';
			}

			/* Toggle */
			if( !empty($options) && !empty($options['extraToggle'])) {
				$pro_blocks[] = 'tpx-mobile-menu-toggle';
			}

			/* Indicator */
			if( !empty($options) && !empty($options['pageIndicator'])) {
				$pro_blocks[] = 'tpx-mobile-menu-indicator';
			}
		}

		/* Dynamic Device */
		if($blockname=='tpgb/tp-dynamic-device'){
			/* Type */
			if( !empty($options) && !empty($options['layoutType']) && $options['layoutType']=='carousel' ) {
				if(!empty($options['cDeviceType'])){
					$pro_blocks[] = 'tpx-dynamic-device-'.$options['cDeviceType'];
				}else{
					$pro_blocks[] = 'tpx-dynamic-device-mobile';
				}
			}else{
				if(!empty($options['deviceType'])){
					$pro_blocks[] = 'tpx-dynamic-device-'.$options['deviceType'];
				}else{
					$pro_blocks[] = 'tpx-dynamic-device-mobile';
				}
			}
		}

        /* Form Builder */
        if($blockname=='tpgb/tp-form-block'){
            if( !empty($options) && !empty($options['layoutType'])) {
                $pro_blocks[] = 'tpx-form-block-layout-'.$options['layoutType'];
            }else{
                $pro_blocks[] = 'tpx-form-block-layout-style-1';
            }
        }

		/* Coupon Code */
		if($blockname=='tpgb/tp-coupon-code'){
			/* Type */
			if( !empty($options) && !empty($options['couponType']) ) {
				$pro_blocks[] = 'tpx-coupon-code-'.$options['couponType'];
			}else{
				if(!empty($options['standardStyle'])){
					$pro_blocks[] = 'tpx-coupon-code-standard-'.$options['standardStyle'];
					if(!empty($options['codecopyIcn'])){
						$pro_blocks[] = 'tpx-coupon-code-standard-hover-icon';
					}
				}else{
					$pro_blocks[] = 'tpx-coupon-code-standard-style-1';
					if(!empty($options['codecopyIcn'])){
						$pro_blocks[] = 'tpx-coupon-code-standard-hover-icon';
					}
				}
				if(!empty($options['actionType']) && $options['actionType']=='popup'){
					$pro_blocks[] = 'tpx-coupon-code-standard-popup';
					if(!empty($options['onScrollBar'])){
						$pro_blocks[] = 'tpx-coupon-code-standard-scrollbar';
					}
				}
			}
		}

        /* Timeline */
        if($blockname=='tpgb/tp-timeline'){
            if( !empty($options) && !empty($options['isAnim']) && $options['isAnim'] == true ){
                $pro_blocks[] = 'nxt-timeline-animation';
            }
        }

		/*Nexter Extras*/
		if(!empty($options) && !empty($options['Plus3DTilt']) && !empty($options['Plus3DTilt']['tpgbReset']) && $options['Plus3DTilt']['tpgbReset']===1){	//tilt
			$pro_blocks[] = 'tpgb-jstilt';
		}
		if(!empty($options) && !empty($options['PlusMouseParallax']) && !empty($options['PlusMouseParallax']['tpgbReset']) && $options['PlusMouseParallax']['tpgbReset']===1){	//mouse parallax
			$pro_blocks[] = 'tpgb-mouse-parallax';
		}
		$blocks = array_merge($blocks, $pro_blocks);

		return $blocks;
	}
	
	public function localize_data($data){
	
		$fontawesome_pro = Tp_Blocks_Helper::get_extra_option('fontawesome_pro_kit');
		$fontAwesomePro = false;
		if(!empty($fontawesome_pro)){
			$fontAwesomePro = $fontawesome_pro;
		}

		$splineJsSrc = '';
		if (defined('TPGBP_URL')) {
			$splineJsSrc = TPGBP_URL.'assets/js/main/spline-3d-viewer/spline-viewer.js';
		}
		
		$eventTracker = apply_filters( 'tpgb_event_tracking', [] );

		$get_whitelabel = get_option( 'tpgb_white_label' );
		$help_link      = isset( $get_whitelabel ) && ! empty( $get_whitelabel['nxt_help_link'] ) ? $get_whitelabel['nxt_help_link'] : '';

		$pro_data = array(
			'menu_lists' => $this->get_menu_lists(),
			'shapeDivider' => $this->getShapeDivider(),
			'fontawesome' => $fontAwesomePro,
			'dynamic_list' => $this->tpgb_get_dynamic_list(),
			'tpgb_page_list' => $this->tpgb_get_page_list(),
			'tpgb_user_role' => $this->tpgbp_get_user_role(),
			'tpgb_current_user' => $this->tpgbp_get_current_user(),
			'splinejsurl' => (!empty($splineJsSrc)) ? esc_url($splineJsSrc) : '',
			'tpgb_developer' => TPGBP_DEVELOPER,
			'tpgb_event_tracking' => $eventTracker['switch'],
    		'event_track_google' => $eventTracker['google_track'],
			'event_track_facebook' => $eventTracker['facebook_track'],
			'nxt_whitelabel_hidehelp_link' => $help_link,
            'mailchimp_group_id' => $this->nxt_mailchimp_interests(),
		);
		
		$pro_data = array_merge($data, $pro_data);
			
		return $pro_data;
	}
	
	
	/**
	 * Init Block Load.
	 *
	 * @since 1.0.0
	 */
	public function init_blocks_load($load_blocks) {
	
		// Return early if this function does not exist.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}
		
		$pro_load_blocks = array(
			'tp-advanced-chart' => TPGBP_CATEGORY.'/tp-advanced-chart',
			'tp-advanced-buttons' => TPGBP_CATEGORY.'/tp-advanced-buttons',
			'tp-adv-typo' => TPGBP_CATEGORY.'/tp-adv-typo',
			'tp-animated-service-boxes' => TPGBP_CATEGORY.'/tp-animated-service-boxes',
			'tp-anything-carousel' => TPGBP_CATEGORY.'/tp-anything-carousel',
			'tp-audio-player' => TPGBP_CATEGORY.'/tp-audio-player',
			'tp-before-after' => TPGBP_CATEGORY.'/tp-before-after',
			'tp-carousel-remote' => TPGBP_CATEGORY.'/tp-carousel-remote',
			'tp-circle-menu' => TPGBP_CATEGORY.'/tp-circle-menu',
			'tp-container' => TPGBP_CATEGORY.'/tp-container',
			'tp-coupon-code' => TPGBP_CATEGORY.'/tp-coupon-code',
			'tp-cta-banner' => TPGBP_CATEGORY.'/tp-cta-banner',
			'tp-design-tool' => TPGBP_CATEGORY.'/tp-design-tool',
			'tp-dynamic-category' => TPGBP_CATEGORY.'/tp-dynamic-category',
			'tp-dynamic-device' => TPGBP_CATEGORY.'/tp-dynamic-device',
			'tp-expand' => TPGBP_CATEGORY.'/tp-expand',
			'tp-heading-animation' => TPGBP_CATEGORY.'/tp-heading-animation',
			'tp-hotspot' => TPGBP_CATEGORY.'/tp-hotspot',
			'tp-login-register' => TPGBP_CATEGORY.'/tp-login-register',
			'tp-lottiefiles' => TPGBP_CATEGORY.'/tp-lottiefiles',
			'tp-mailchimp' => TPGBP_CATEGORY.'/tp-mailchimp',
			'tp-media-listing' => TPGBP_CATEGORY.'/tp-media-listing',
			'tp-mobile-menu' => TPGBP_CATEGORY.'/tp-mobile-menu',
			'tp-mouse-cursor' => TPGBP_CATEGORY.'/tp-mouse-cursor',
			'tp-navigation-builder' => TPGBP_CATEGORY.'/tp-navigation-builder',
			'tp-popup-builder' => TPGBP_CATEGORY.'/tp-popup-builder',
			'tp-post-navigation' => TPGBP_CATEGORY.'/tp-post-navigation',
			'tp-preloader' => TPGBP_CATEGORY.'/tp-preloader',
			'tp-pricing-table' => TPGBP_CATEGORY.'/tp-pricing-table',
			'tp-process-steps' => TPGBP_CATEGORY.'/tp-process-steps',
			'tp-product-listing' => TPGBP_CATEGORY.'/tp-product-listing',
			'tp-scroll-navigation' => TPGBP_CATEGORY.'/tp-scroll-navigation',
			'tp-scroll-sequence' => TPGBP_CATEGORY.'/tp-scroll-sequence',
			'tp-social-feed' => TPGBP_CATEGORY.'/tp-social-feed',
			'tp-social-sharing' => TPGBP_CATEGORY.'/tp-social-sharing',
			'tp-social-reviews' => TPGBP_CATEGORY.'/tp-social-reviews',
			'tp-spline-3d-viewer' => TPGBP_CATEGORY.'/tp-spline-3d-viewer',
			'tp-switcher' => TPGBP_CATEGORY.'/tp-switcher',
			'tp-table-content' => TPGBP_CATEGORY.'/tp-table-content',
			'tp-team-listing' => TPGBP_CATEGORY.'/tp-team-listing',
			'tp-timeline' => TPGBP_CATEGORY.'/tp-timeline',
            'tp-repeater-block' => TPGBP_CATEGORY.'/tp-repeater-block',
		);
		
		$pro_load_blocks = array_merge($pro_load_blocks, $load_blocks);
		
		return $pro_load_blocks;
		
	}
	
	/**
	 * Load Register Blocks Css and Js File
	 *
	 * @since 1.0.0
	 */
	public function load_blocks_registers_css_js($load_blocks_css_js){
		$tpgb_pro = TPGBP_PATH . DIRECTORY_SEPARATOR;
		$tpgb_free = TPGB_PATH . DIRECTORY_SEPARATOR;

		$pro_blocks_register = [
			/* 'tpgb_lazyLoad' => [
				'css' => [
					$tpgb_pro .'assets/css/main/lazy_load/tpgb-lazy_load.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/extra/lazyload.min.js',
					$tpgb_pro . 'assets/js/main/lazy_load/tpgb-lazy_load.js',
				],
			], */
			TPGBP_CATEGORY.'/tp-row' => [
				'css' => [
					$tpgb_pro .'assets/css/main/plus-extras/plus-row-bg.css',
					$tpgb_pro .'classes/blocks/tp-row/style.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/tp-row/tpgb-row.min.js',
				],
			],
			'tpx-tp-shape-divider' => [
				'css' => [
					$tpgb_pro .'assets/css/main/shape-divider/style-shape-divider.css',
				],
			],
			TPGBP_CATEGORY.'/tp-container' => [
				'css' => [
					$tpgb_pro .'assets/css/main/plus-extras/plus-row-bg.css',
					$tpgb_pro .'classes/blocks/tp-container/style.css',
				],
			],
			'tpgb-row-animated-color' => [
				'js' => [
					// $tpgb_pro .'assets/js/extra/effect.min.js',
					$tpgb_pro . 'assets/js/main/row-background/tpgb-row-animate.min.js',
				],
			],
			'tpgb-image-parallax' => [
				'js' => [
					$tpgb_pro . 'assets/js/main/row-background/tpgb-image-parellax.min.js',
				],
			],
			'tpgb-image-moving' => [
				'js' => [
					$tpgb_pro . 'assets/js/main/row-background/tpgb-image-moving.min.js',
				],
			],
			'tpgb-scroll-parallax' => [
				'js' => [
					$tpgb_pro . 'assets/js/main/row-background/tpgb-scroll-parallax.min.js',
				],
			],
			'tpgb-youtube-video' => [
				'js' => [
					$tpgb_pro . 'assets/js/main/row-background/tpgb-video-common.min.js',
					$tpgb_pro . 'assets/js/main/row-background/tpgb-youtube-video.min.js',
				],
			],
			'tpgb-vimeo-video' => [
				'js' => [
					$tpgb_pro . 'assets/js/main/row-background/tpgb-video-common.min.js',
					$tpgb_pro . 'assets/js/main/row-background/tpgb-vimeo-video.min.js',
				],
			],
			'tpgb-self-hosted-video' => [
				'js' => [
					$tpgb_pro . 'assets/js/main/row-background/tpgb-video-common.min.js',
				],
			],
			'tpgb-bg-gallery' => [
				'css' => [
					$tpgb_pro .'assets/css/extra/vegas.min.css',
				],
				'js' => [					
					$tpgb_pro . 'assets/js/extra/vegas.js',
					$tpgb_pro . 'assets/js/main/row-background/tpgb-imge-slide.min.js',

				],
			],
			'tpgb-magic-scroll' => [
				'js' => [
					$tpgb_pro . 'assets/js/extra/tweenmax/gsap.min.js',
					$tpgb_pro . 'assets/js/extra/scrollmagic/scrollmagic.min.js',
					$tpgb_pro . 'assets/js/extra/scrollmagic/animation.gsap.min.js',
					$tpgb_pro . 'assets/js/extra/scrollmagic/addIndicators.min.js',
				],
			],
            'tpgb-magic-scroll-custom' => [
				'js' => [					
					$tpgb_pro . 'assets/js/main/plus-extras/plus-magic-scroll.min.js',
				],
			],
			'tpgb-scrollbg-animation' => [
				'js' => [
					$tpgb_pro .'assets/js/extra/scrollingBackgroundColor.js',
					$tpgb_pro .'assets/js/extra/scrollmonitor.js',		
					$tpgb_pro . 'assets/js/main/row-background/tpgb-bgscroll-animation.min.js',
				],
			],
			'tpgb-canvas-particle' => [
				'js' => [
					$tpgb_pro . 'assets/js/extra/particles.min.js',
					$tpgb_pro . 'assets/js/main/row-background/tpgb-canvas.min.js',
				],
			],
			'tpgb-canvas-particleground' => [
				'js' => [
					$tpgb_pro . 'assets/js/extra/particleground.js',
					$tpgb_pro . 'assets/js/main/row-background/tpgb-canvas-particle-ground.min.js',
				],
			],
			'tpgb-mordern-parallax' => [
				'js' => [
					$tpgb_pro . 'assets/js/main/row-background/tpgb-mordern-parallax.min.js',
				],
			],
			TPGBP_CATEGORY.'/tp-column' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-column/style.css',
				],
			],
			TPGBP_CATEGORY.'/tp-container-inner' => [
				'css' => [		
					$tpgb_pro .'classes/blocks/tp-container-inner/style.css',
				],
			],
			TPGBP_CATEGORY.'/tp-accordion' => [
				'css' => [		
					$tpgb_pro .'classes/blocks/tp-accordion/style.css',
				],
				'js' => [
					$tpgb_free .'assets/js/main/common-created/tpgb-slidetoggle-block.min.js',
					$tpgb_pro .'assets/js/main/accordion/tpgb-accordion.min.js',
				],
			],
			'tpx-accordion-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-accordion/style-1.css',
				],
			],
			'tpx-accordion-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-accordion/style-2.css',
				],
			],
			TPGBP_CATEGORY.'/tp-accordion-inner' => [],
			TPGBP_CATEGORY.'/tp-advanced-buttons' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/style.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/advanced-buttons/tpgb-advanced-buttons.min.js',
				],
			],
			'tpx-adv-btn-cta-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/cta/style-1.css',
				]
			],
			'tpx-adv-btn-cta-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/cta/style-2.css',
				]
			],
			'tpx-adv-btn-cta-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/cta/style-3.css',
				]
			],
			'tpx-adv-btn-cta-style-4' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/cta/style-4.css',
				]
			],
			'tpx-adv-btn-cta-style-5' => [
                'css' => [
                    $tpgb_pro .'classes/blocks/tp-advanced-buttons/cta/style-5.css',
                ],
                'js' => [
                    $tpgb_pro . 'assets/js/main/advanced-buttons/tpgb-advanced-button-style-5.min.js',
                ],
            ],
			'tpx-adv-btn-cta-style-6' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/cta/style-6.css',
				]
			],
			'tpx-adv-btn-cta-style-7' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/cta/style-7.css',
				]
			],
			'tpx-adv-btn-cta-style-8' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/cta/style-8.css',
				]
			],
			'tpx-adv-btn-cta-style-9' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/cta/style-9.css',
				]
			],
			'tpx-adv-btn-cta-style-10' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/cta/style-10.css',
				]
			],
			'tpx-adv-btn-cta-style-11' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/cta/style-11.css',
				]
			],
			'tpx-adv-btn-cta-style-12' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/cta/style-12.css',
				]
			],
			'tpx-adv-btn-cta-style-13' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/cta/style-13.css',
				]
			],
			'tpx-adv-btn-dwnld-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/dwnld/style-1.css',
				]
			],
			'tpx-adv-btn-dwnld-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/dwnld/style-2.css',
				]
			],
			'tpx-adv-btn-dwnld-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/dwnld/style-3.css',
				]
			],
			'tpx-adv-btn-dwnld-style-4' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/dwnld/style-4.css',
				]
			],
			'tpx-adv-btn-dwnld-style-5' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-buttons/dwnld/style-5.css',
				]
			],
			TPGBP_CATEGORY.'/tp-advanced-chart' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-advanced-chart/style.css',
				],
				'js' => [
					$tpgb_free . 'assets/js/extra/waypoints.min.js',
					$tpgb_pro . 'assets/js/extra/chart.js',
					$tpgb_pro . 'assets/js/main/advanced-chart/tpgb-adv-chart.min.js',
				],
			],
			TPGBP_CATEGORY.'/tp-adv-typo' => [
				'css' => [
					$tpgb_pro .'assets/css/extra/imagerevealbase.css',
					$tpgb_pro .'classes/blocks/tp-adv-typo/style.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/extra/circletype.min.js',
					$tpgb_pro . 'assets/js/main/adv-typo/adv-typo.min.js',
				],
			],
			'tpx-adv-typo-hover-img-reavel' => [
				'js' => [
					$tpgb_pro . 'assets/js/extra/charming.min.js',
					$tpgb_pro . 'assets/js/extra/imagesloaded.pkgd.min.js',
					$tpgb_pro . 'assets/js/extra/tweenmax/gsap.min.js',
					$tpgb_pro . 'assets/js/extra/imagerevealdemo.js',
				],
			],
			'tpx-adv-typo-normal-overlay' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-adv-typo/style-normal-overlay.css',
				],
			],
			'tpx-adv-typo-multiple' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-adv-typo/style-multiple.css',
				],
			],
			TPGBP_CATEGORY.'/tp-animated-service-boxes' => [
				'css' => [
					$tpgb_free .'assets/css/extra/bootstrap-grid.min.css',
					$tpgb_pro .'classes/blocks/tp-animated-service-boxes/style.css',
				],
				'js' => [
					$tpgb_free .'assets/js/main/common-created/tpgb-slidetoggle-block.min.js',
					$tpgb_pro . 'assets/js/main/animated-service-boxes/tpgb-animated-service-boxes.min.js',
				],
			],
			'tpx-animated-service-boxes-image-accordion' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-animated-service-boxes/style/image-accordion.css',
				],
			],
			'tpx-animated-service-boxes-sliding-boxes' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-animated-service-boxes/style/sliding-boxes.css',
				],
			],
			'tpx-animated-service-boxes-article-box' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-animated-service-boxes/style/article-box.css',
				],
			],
			'tpx-animated-service-boxes-info-banner' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-animated-service-boxes/style/info-banner.css',
				],
			],
			'tpx-animated-service-boxes-hover-section' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-animated-service-boxes/style/hover-section.css',
				],
			],
			'tpx-animated-service-boxes-fancy-box' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-animated-service-boxes/style/fancy-box.css',
				],
			],
			'tpx-animated-service-boxes-services-element' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-animated-service-boxes/style/services-element.css',
				],
			],
			'tpx-animated-service-boxes-portfolio' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-animated-service-boxes/style/portfolio.css',
				],
			],
			TPGBP_CATEGORY.'/tp-audio-player' => [
				'css' => [
					$tpgb_pro .'assets/css/extra/nouislider.min.css',
					$tpgb_pro .'classes/blocks/tp-audio-player/style.css',
				],
				'js' => [
					$tpgb_pro .'assets/js/extra/nouislider.min.js',
					$tpgb_pro .'assets/js/main/audio-player/tp-audio-player.min.js',
				],
			],
			'tpx-audio-player-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-audio-player/style-2.css',
				],
			],
			'tpx-audio-player-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-audio-player/style-3.css',
				],
			],
			'tpx-audio-player-style-4' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-audio-player/style-4.css',
				],
			],
			'tpx-audio-player-style-5' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-audio-player/style-5.css',
				],
			],
			'tpx-audio-player-style-6' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-audio-player/style-6.css',
				],
			],
			'tpx-audio-player-style-7' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-audio-player/style-7.css',
				],
			],
			'tpx-audio-player-style-8' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-audio-player/style-8.css',
				],
			],
			'tpx-audio-player-style-9' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-audio-player/style-9.css',
				],
			],
			TPGBP_CATEGORY.'/tp-before-after' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-before-after/style.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/before-after/tpgb-before-after.min.js',
				],
			],
			TPGBP_CATEGORY.'/tp-countdown' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-countdown/style.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/countdown/countdown.min.js',
				],
			],
			'countdown-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-countdown/style-1.css',
				],
				'js' => [
					$tpgb_free . 'assets/js/extra/downCount.min.js',
				],
			],
 			'countdown-style-2' => [
				'css' => [
					$tpgb_pro . 'assets/css/extra/flipdown.min.css',
					$tpgb_pro .'classes/blocks/tp-countdown/style-2.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/extra/flipdown.min.js',
				],
			],
			'countdown-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-countdown/style-3.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/extra/progressbar.min.js',
				],
			],
			'countdown-fakestyle' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-countdown/fake-number.css',
				],
			],
			TPGBP_CATEGORY.'/tp-coupon-code' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-coupon-code/style.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/extra/html2canvas.min.js',
					$tpgb_pro . 'assets/js/extra/peeljs.js',
					$tpgb_pro . 'assets/js/main/coupon-code/tpgb-coupon-code.min.js',
				],
			],
			'tpx-coupon-code-standard-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-coupon-code/standard/style.css',
					$tpgb_pro .'classes/blocks/tp-coupon-code/standard/style-1.css',
				],
			],
			'tpx-coupon-code-standard-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-coupon-code/standard/style.css',
					$tpgb_pro .'classes/blocks/tp-coupon-code/standard/style-2.css',
				],
			],
			'tpx-coupon-code-standard-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-coupon-code/standard/style.css',
					$tpgb_pro .'classes/blocks/tp-coupon-code/standard/style-3.css',
				],
			],
			'tpx-coupon-code-standard-style-4' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-coupon-code/standard/style.css',
					$tpgb_pro .'classes/blocks/tp-coupon-code/standard/style-4.css',
				],
			],
			'tpx-coupon-code-standard-style-5' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-coupon-code/standard/style.css',
					$tpgb_pro .'classes/blocks/tp-coupon-code/standard/style-5.css',
				],
			],
			'tpx-coupon-code-standard-popup' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-coupon-code/standard/popup.css',
				],
			],
			'tpx-coupon-code-standard-scrollbar' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-coupon-code/standard/scrollbar.css',
				],
			],
			'tpx-coupon-code-standard-hover-icon' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-coupon-code/standard/hover-copy-icon.css',
				],
			],
			'tpx-coupon-code-scratch' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-coupon-code/peel-scratch-slide/style.css',
					$tpgb_pro .'classes/blocks/tp-coupon-code/peel-scratch-slide/scratch.css',
				],
			],
			'tpx-coupon-code-peel' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-coupon-code/peel-scratch-slide/style.css',
					$tpgb_pro .'classes/blocks/tp-coupon-code/peel-scratch-slide/peel.css',
				],
			],
			'tpx-coupon-code-slideOut' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-coupon-code/peel-scratch-slide/style.css',
					$tpgb_pro .'classes/blocks/tp-coupon-code/peel-scratch-slide/slideout.css',
				],
			],
			TPGBP_CATEGORY.'/tp-circle-menu' => [
				'css' => [
					$tpgb_free .'assets/css/extra/tippy.css',
					$tpgb_pro .'classes/blocks/tp-circle-menu/style.css',
				],
				'js' => [
					$tpgb_free .'assets/js/extra/popper.min.js',
					$tpgb_free .'assets/js/extra/tippy.min.js',
					$tpgb_pro .'assets/js/extra/circle-menu-vanilla.js',
					$tpgb_pro .'assets/js/main/circle-menu/tpgb-circle-menu.min.js',
				],
			],
			'tpx-circle-menu-straight' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-circle-menu/straight.css',
				],
			],
			'tpx-circle-menu-overlay' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-circle-menu/overlay.css',
				],
			],
			'tpx-circle-menu-toggle-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-circle-menu/toggle/style-2.css',
				],
			],
			'tpx-circle-menu-toggle-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-circle-menu/toggle/style-3.css',
				],
			],
			TPGBP_CATEGORY.'/tp-data-table' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-data-table/style.css',
				],
				'js' => [
					$tpgb_free . 'assets/js/extra/datatables.min.js',
					$tpgb_pro .'assets/js/main/data-table/tpgb-data-table.min.js',
				],
			],
			TPGBP_CATEGORY.'/tp-design-tool' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-design-tool/style.css',
				],
			],
			TPGBP_CATEGORY.'/tp-dynamic-category' => [
				'css' => [
					$tpgb_free .'assets/css/extra/bootstrap-grid.min.css',
					$tpgb_pro .'classes/blocks/tp-dynamic-category/style.css',
				],
			],
			'tpx-dy-cate-style_1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-dynamic-category/style-1.css',
				],
			],
			'tpx-dy-cate-style_2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-dynamic-category/style-2.css',
				],
			],
			'tpx-dy-cate-style_3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-dynamic-category/style-3.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/dynamic-category/tpgb-dynamic-category.js',
				],
			],
			'tpx-dy-metro'  => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-dynamic-category/dynamic-metro.css',
				],
			],
			TPGBP_CATEGORY.'/tp-dynamic-device' => [
				'css' => [
					$tpgb_free .'assets/css/extra/fancybox.css',
					$tpgb_pro .'classes/blocks/tp-dynamic-device/style.css',
				],
				'js' => [
					$tpgb_free . 'assets/js/extra/fancybox.umd.js',
					$tpgb_pro . 'assets/js/main/dynamic-devices/tpgb-dynamic-devices.min.js',
				],
			],
			'tpx-dynamic-device-mobile' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-dynamic-device/type/mobile.css',
				],
			],
			'tpx-dynamic-device-tablet' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-dynamic-device/type/tablet.css',
				],
			],
			'tpx-dynamic-device-laptop' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-dynamic-device/type/laptop.css',
				],
			],
			'tpx-dynamic-device-desktop' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-dynamic-device/type/desktop.css',
				],
			],
			'tpx-dynamic-device-custom' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-dynamic-device/type/custom.css',
				],
			],
			TPGBP_CATEGORY.'/tp-expand' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-expand/style.css',
				],
				'js' => [
					$tpgb_free .'assets/js/main/common-created/tpgb-slidetoggle-block.min.js',
					$tpgb_pro . 'assets/js/main/expand/expand.min.js',
				],
			],
			TPGBP_CATEGORY.'/tp-media-listing' => [
				'css' => [
					$tpgb_free .'assets/css/extra/bootstrap-grid.min.css',
					$tpgb_free .'assets/css/extra/fancybox.css',
					$tpgb_pro .'classes/blocks/tp-media-listing/style.css',
				],
				'js' => [
					$tpgb_free . 'assets/js/extra/fancybox.umd.js',
				],
			],
			'tpx-media-listing-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-media-listing/media-style-1.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/media-listing/tpgb-media-listing.min.js',
				],
			],
			'tpx-media-listing-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-media-listing/media-style-2.css',
				],
				'js' => [
					$tpgb_free .'assets/js/main/common-created/tpgb-slidetoggle-block.min.js',
					$tpgb_pro . 'assets/js/main/media-listing/tpgb-media-listing.min.js',
				],
			],
			'tpx-media-listing-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-media-listing/media-style-3.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/media-listing/tpgb-media-listing.min.js',
				],
			],
			'tpx-media-listing-style-4' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-media-listing/media-style-4.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/media-listing/tpgb-media-listing.min.js',
				],		
			],
			'tpx-media-metro-style'  => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-media-listing/metro-style.css',
				],
			],
			TPGBP_CATEGORY.'/tp-post-listing' => [
				'css' => [
					$tpgb_free .'assets/css/extra/bootstrap-grid.min.css',
					$tpgb_pro .'classes/blocks/tp-post-listing/style.css',
				],
				'js' => [
					$tpgb_free . 'assets/js/main/common-created/tpgb-slidetoggle-block.min.js',
					$tpgb_pro . 'assets/js/main/post-listing/post-listing.min.js',
				],
			],
			'tpx-post-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-post-listing/tp-post-style-1.css',
				],
			],
			'tpx-post-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-post-listing/tp-post-style-2.css',
				],
			],
			'tpx-post-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-post-listing/tp-post-style-3.css',
				],
			],
			'tpx-post-style-4' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-post-listing/tp-post-style-4.css',
				],
			],
			'tpx-post-metro' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-post-listing/tp-post-metro.css',
				],
			],
			'tpx-post-pagination-ajax' => [
				'js' => [
					$tpgb_pro . 'assets/js/main/post-listing/post-ajax-pagination.min.js',
				],
			],
			TPGBP_CATEGORY.'/tp-switcher' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-switcher/style.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/switcher/tpgb-switcher.min.js',
				],
			],
			'tpx-switcher-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-switcher/style-1.css',
				],
			],
			'tpx-switcher-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-switcher/style-2.css',
				],
			],
			'tpx-switcher-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-switcher/style-3.css',
				],
			],
			'tpx-switcher-style-4' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-switcher/style-4.css',
				],
			],
			TPGBP_CATEGORY.'/tp-switch-inner' => [],
			TPGBP_CATEGORY.'/tp-testimonials' => [
				'css' => [
					$tpgb_free .'assets/css/extra/bootstrap-grid.min.css',
					// $tpgb_free .'assets/css/extra/splide.min.css',
					// $tpgb_free .'assets/css/main/post-listing/splide-carousel.min.css',
					$tpgb_pro .'classes/blocks/tp-testimonials/style.css',
				],
				'js' => [
					// $tpgb_free . 'assets/js/extra/splide.min.js',
					// $tpgb_pro . 'assets/js/main/post-listing/post-splide.min.js',
					$tpgb_free . 'assets/js/main/testimonial/tpgb-testimonial.min.js',
				],
			],
			'tpx-testimonials-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-testimonials/tp-testimonials-style-1.css',
				],
			],
			'tpx-testimonials-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-testimonials/tp-testimonials-style-2.css',
				],
			],
			'tpx-testimonials-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-testimonials/tp-testimonials-style-3.css',
				],
			],
			'tpx-testimonials-style-4' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-testimonials/tp-testimonials-style-4.css',
				],
			],
			'tpx-testimonials-scroll' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-testimonials/tp-testimonials-scroll.css',
				],
			],
			TPGBP_CATEGORY.'/tp-team-listing' => [
				'css' => [
					$tpgb_free .'assets/css/extra/bootstrap-grid.min.css',
					$tpgb_pro .'classes/blocks/tp-team-listing/style.css',
				],
				'js' => [
					$tpgb_free . 'assets/js/extra/isotope.pkgd.min.js',
					$tpgb_pro . 'assets/js/main/post-listing/post-masonry.min.js',
				],
			],
			'tpx-team-list-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-team-listing/tp-team-list-style-1.css',
				],
			],
			'tpx-team-list-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-team-listing/tp-team-list-style-2.css',
				],
			],
			'tpx-team-list-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-team-listing/tp-team-list-style-3.css',
				],
			],
			'tpx-team-list-style-4' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-team-listing/tp-team-list-style-4.css',
				],
			],
			TPGBP_CATEGORY.'/tp-table-content' => [
				'css' => [
					$tpgb_pro .'assets/css/extra/tocbot.css',
					$tpgb_pro .'classes/blocks/tp-table-content/style.css',
				],
				'js' => [
					$tpgb_pro .'assets/js/extra/tocbot.min.js',
					$tpgb_free . 'assets/js/main/common-created/tpgb-slidetoggle-block.min.js',
					$tpgb_pro .'assets/js/main/table-content/tp-table-content.min.js',
				],
			],
			'tpx-table-content-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-table-content/style-1.css',
				],
			],
			'tpx-table-content-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-table-content/style-2.css',
				],
			],
			'tpx-table-content-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-table-content/style-3.css',
				],
			],
			'tpx-table-content-style-4' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-table-content/style-4.css',
				],
			],
			TPGBP_CATEGORY.'/tp-hotspot' => [
				'css' => [
					$tpgb_free .'assets/css/extra/tippy.css',
					$tpgb_pro .'classes/blocks/tp-hotspot/style.css',
				],
				'js' => [
					$tpgb_free .'assets/js/extra/waypoints.min.js',
					$tpgb_free .'assets/js/extra/popper.min.js',
					$tpgb_free .'assets/js/extra/tippy.min.js',
					$tpgb_pro .'assets/js/main/hotspot/tpgb-hotspot.min.js',
				],
			],
			'tpx-hover-overlay' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-hotspot/hover-overlay.css',
				]
			],
			TPGBP_CATEGORY.'/tp-process-steps' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-process-steps/style.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/process-steps/tpgb-process-steps.min.js',
				],
			],
			'tpx-process-steps-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-process-steps/style-1.css',
				],
			],
			'tpx-process-steps-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-process-steps/style-2.css',
				],
			],
			'tpx-process-steps-counter' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-process-steps/counter.css',
				],
			],
			'tpx-process-steps-ring-bg' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-process-steps/ring-bg.css',
				],
			],
			TPGBP_CATEGORY.'/tp-product-listing' => [
				'css' => [
					$tpgb_free .'assets/css/extra/bootstrap-grid.min.css',
					$tpgb_pro .'classes/blocks/tp-product-listing/style.css',
				],
				'js' => [
					// $tpgb_free . 'assets/js/extra/isotope.pkgd.min.js',
					// $tpgb_pro . 'assets/js/extra/imagesloaded.pkgd.min.js',
					// $tpgb_pro . 'assets/js/main/post-listing/post-masonry.min.js',
					// $tpgb_pro . 'assets/js/main/post-listing/post-listing.min.js',
				],
			],
			'tpx-product-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-product-listing/tp-product-style-1.css',
				],
			],
			'tpx-product-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-product-listing/tp-product-style-2.css',
				],
			],
			'tpx-product-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-product-listing/tp-product-style-3.css',
				],
			],
			'tpx-product-metro' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-product-listing/tp-product-metro.css',
				],
			],
			TPGBP_CATEGORY.'/tp-flipbox' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-flipbox/style.css',
				],
			],
            TPGBP_CATEGORY.'/tp-form-block' => [
                'css' => [
                    $tpgb_pro .'classes/blocks/tp-form-block/style.css',
                ],
                'js' => [
                    $tpgb_pro . 'assets/js/main/form-block/tpgb-form-block.min.js',
                ],
            ],
            'tpx-form-block-layout-style-1' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-form-block/style.css',
				],
			],
            'tpx-form-block-layout-nxt-style-2' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-form-block/contact-form/style2.css',
				],
			],
            'tpx-form-block-layout-nxt-style-3' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-form-block/contact-form/style3.css',
				],
			],
            'tpx-form-block-layout-nxt-style-4' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-form-block/contact-form/style4.css',
				],
			],
            'tpx-form-block-layout-nxt-newsstyle-1' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-form-block/news-form/style2.css',
				],
			],
            'tpx-form-block-layout-nxt-newsstyle-3' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-form-block/news-form/style3.css',
				],
			],
            'tpx-form-block-layout-nxt-newsstyle-4' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-form-block/news-form/style4.css',
				],
			],
			TPGBP_CATEGORY.'/tp-google-map' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-google-map/style.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/google-map/tpgb-google-map.min.js',
				],
			],
			'tpx-google-map-content' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-google-map/style-content-hover.css',
				],
			],
			TPGBP_CATEGORY.'/tp-anything-carousel' => [
				'css' => [
					$tpgb_free .'assets/css/extra/splide.min.css',
					$tpgb_free .'assets/css/main/post-listing/splide-carousel.min.css',
					$tpgb_pro .'classes/blocks/tp-anything-carousel/style.css',
				],
				'js' => [
					$tpgb_free . 'assets/js/extra/splide.min.js',
					$tpgb_pro . 'assets/js/main/post-listing/post-splide.min.js',
				],
			],
			TPGBP_CATEGORY.'/tp-anything-slide' => [],
			TPGBP_CATEGORY.'/tp-carousel-remote' => [
				'css' => [
					$tpgb_free .'assets/css/extra/splide.min.css',
					$tpgb_free .'assets/css/main/post-listing/splide-carousel.min.css',
					$tpgb_pro .'classes/blocks/tp-carousel-remote/style.css',
				],
				'js' => [
					$tpgb_free . 'assets/js/extra/splide.min.js',
					$tpgb_pro . 'assets/js/main/carousel-remote/tpgb-carousel-remote.min.js',
				],
			],
			'tpgx-carousel-button' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-carousel-remote/tp-sliderbutton.css',
				],
			],
			'tpgx-carousel-dot' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-carousel-remote/tp-caro-dot.css',
				],
			],
			'tpgx-carousel-tooltip' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-carousel-remote/tp-caro-tooltip.css',
				],
			],
			'tpgx-carousel-pagination' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-carousel-remote/tp-pagination.css',
				],
			],
			TPGBP_CATEGORY.'/tp-cta-banner' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-cta-banner/style.css',
				],
			],
			'tpx-cta-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-cta-banner/style-1.css',
				],
			],
			'tpx-cta-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-cta-banner/style-2.css',
				],
			],
			'tpx-cta-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-cta-banner/style-3.css',
				],
			],
			'tpx-cta-style-4' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-cta-banner/style-4.css',
				],
			],
			'tpx-cta-style-5' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-cta-banner/style-5.css',
				],
			],
			'tpx-cta-style-6' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-cta-banner/style-6.css',
				],
			],
			'tpx-cta-style-7' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-cta-banner/style-7.css',
				],
			],
			'tpx-cta-style-8' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-cta-banner/style-8.css',
				],
			],
			TPGBP_CATEGORY.'/tp-tabs-tours'  => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-tabs-tours/style.css',
				],
				'js' => [
					$tpgb_pro .'assets/js/main/tabs-tours/plus-tabs-tours.min.js',
				],
			],
			'tpx-tabs-tours-vertical' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-tabs-tours/style-vertical.css',
				],
			],
			TPGBP_CATEGORY.'/tp-tab-item' => [],
			TPGBP_CATEGORY.'/tp-heading-animation' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-heading-animation/style.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/heading-animation/heading-animation.min.js',
				],
			],
			'tpx-heading-animation-highlights' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-heading-animation/style-highlights.css',
				],
			],
			'tpx-heading-textAnim-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-heading-animation/style-textAnim-style-1.css',
				],
			],
			'tpx-heading-textAnim-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-heading-animation/style-textAnim-style-2.css',
				],
			],
			'tpx-heading-textAnim-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-heading-animation/style-textAnim-style-3.css',
				],
			],
			'tpx-heading-textAnim-style-4' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-heading-animation/style-textAnim-style-4.css',
				],
			],
			'tpx-heading-textAnim-style-5' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-heading-animation/style-textAnim-style-5.css',
				],
			],
			'tpx-heading-textAnim-style-6' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-heading-animation/style-textAnim-style-6.css',
				],
			],
			'tpx-heading-textAnim-style-7' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-heading-animation/style-textAnim-style-7.css',
				],
			],
			'tpx-heading-textAnim-style-8' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-heading-animation/style-textAnim-style-8.css',
				],
			],
			TPGBP_CATEGORY.'/tp-mobile-menu' => [
				'css' => [
					$tpgb_pro .'assets/css/extra/swiper.min.css',
					$tpgb_pro .'classes/blocks/tp-mobile-menu/style.css',
				],
				'js' => [
					$tpgb_pro .'assets/js/extra/swiper.min.js',
					$tpgb_pro . 'assets/js/main/mobile-menu/tpgb-mobile-menu.min.js',
				],
			],
			'tpx-mobile-menu-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-mobile-menu/style-1.css',
				],
			],
			'tpx-mobile-menu-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-mobile-menu/style-2.css',
				],
			],
			'tpx-mobile-menu-toggle' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-mobile-menu/toggle.css',
				],
			],
			'tpx-mobile-menu-indicator' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-mobile-menu/indicator.css',
				],
			],
			TPGBP_CATEGORY.'/tp-navigation-builder' => [
                'css' => [
                    $tpgb_pro .'classes/blocks/tp-navigation-builder/style.css',
                ],
                'js' => [
                    $tpgb_free .'assets/js/main/common-created/tpgb-slidetoggle-block.min.js',
                    $tpgb_free .'assets/js/main/common-created/tpgb-fade-in-out.min.js',
                    $tpgb_pro .'assets/js/main/navigation-builder/tpgb-nav.min.js',
                ],
            ],
			'tpgb-web-access' => [
				'js' => [
					$tpgb_pro .'assets/js/main/navigation-builder/tpgb-nav-access.min.js',
				],
			],
			TPGBP_CATEGORY.'/tp-creative-image' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-creative-image/style.css',
				],
				'js' => [
					$tpgb_free .'assets/js/extra/waypoints.min.js',
					$tpgb_pro .'assets/js/main/creative-image/plus-image-factory.min.js',
				],
			],
			'tpx-tp-image-scroll-effect' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-creative-image/style-scroll-effect.css',
				],
			],
			'tpx-tp-image-mask-img' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-creative-image/style-mask-image.css',
				],
			],
			'tpx-tp-image-animate' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-creative-image/style-animate-image.css',
				],
			],
			'tpx-tp-image-parallax' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-creative-image/style-parallax-image.css',
				],
			],
			TPGBP_CATEGORY.'/tp-social-feed' => [
				'css' => [
					$tpgb_free .'assets/css/extra/fancybox.css',
					$tpgb_free .'assets/css/extra/bootstrap-grid.min.css',
					$tpgb_pro .'classes/blocks/tp-social-feed/style.css',
				],
				'js' => [
					$tpgb_free . 'assets/js/extra/fancybox.umd.js',
					$tpgb_pro . 'assets/js/main/social-feed/tp-social-feed.min.js',
				],
			],
			'tpx-social-feed-style-3' => [
				'css' => [
					$tpgb_free .'classes/blocks/tp-social-feed/style-3.css',
				],
			],
			'tpx-social-feed-style-4' => [
				'css' => [
					$tpgb_free .'classes/blocks/tp-social-feed/style-4.css',
				],
			],
			TPGBP_CATEGORY.'/tp-social-sharing' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-social-sharing/style.css',
				],
				'js' => [
					$tpgb_free . 'assets/js/main/common-created/tpgb-slidetoggle-flex.min.js',
					$tpgb_pro . 'assets/js/main/social-sharing/tpgb-social-sharing.min.js',
				],
			],
			'tpx-social-sharing-horizontal' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-social-sharing/horizontal.css',
				],
			],
			'tpx-social-sharing-vertical' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-social-sharing/vertical.css',
				],
			],
			'tpx-social-sharing-toggle' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-social-sharing/toggle.css',
				],
			],
			TPGBP_CATEGORY.'/tp-social-reviews' => [
				'css' => [
					$tpgb_free .'assets/css/extra/bootstrap-grid.min.css',
					$tpgb_pro .'assets/css/main/post-listing/post-category-filter.css',
					$tpgb_pro .'classes/blocks/tp-social-reviews/style.css',
				],
				'js' => [
					$tpgb_free . 'assets/js/extra/isotope.pkgd.min.js',
					$tpgb_pro . 'assets/js/main/post-listing/post-masonry.min.js',
					$tpgb_pro . 'assets/js/main/social-reviews/tp-social-reviews.min.js',
				],
			],
			'tpx-review-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-social-reviews/review/style-1.css',
				],
			],
			'tpx-review-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-social-reviews/review/style-2.css',
				],
			],
			'tpx-review-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-social-reviews/review/style-3.css',
				],
			],
			'tpx-beach-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-social-reviews/beach/beach.css',
					$tpgb_pro .'classes/blocks/tp-social-reviews/beach/style-1.css',
				],
			],
			'tpx-beach-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-social-reviews/beach/beach.css',
					$tpgb_pro .'classes/blocks/tp-social-reviews/beach/style-2.css',
				],
			],
			'tpx-beach-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-social-reviews/beach/beach.css',
					$tpgb_pro .'classes/blocks/tp-social-reviews/beach/style-3.css',
				],
			],
			TPGBP_CATEGORY.'/tp-spline-3d-viewer' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-spline-3d-viewer/style.css',
				],
			],
			TPGBP_CATEGORY.'/tp-timeline' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-timeline/style.css',
				],
				'js' => [
					$tpgb_free . 'assets/js/extra/isotope.pkgd.min.js',
					$tpgb_pro . 'assets/js/main/post-listing/post-masonry.min.js',				
				],
			],
            'nxt-timeline-animation' => [
				'js' => [
                    $tpgb_pro . 'assets/js/main/timeline/nxt-timeline.min.js',				
				],
			],
			TPGBP_CATEGORY.'/tp-timeline-inner' => [],
			TPGBP_CATEGORY.'/tp-popup-builder' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-popup-builder/style.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/popup-builder/plus-popup-builder.min.js',
				],
			],
			'tpgb-popup-animation' => [
				'css' => [
					$tpgb_free .'assets/css/extra/animate.min.css',
				],
			],
			'tpx-humberger-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-popup-builder/hubtn-style-1.css',
				],
			],
			'tpx-humberger-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-popup-builder/hubtn-style-2.css',
				],
			],
			'tpx-humberger-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-popup-builder/hubtn-style-3.css',
				],
			],
			'tpx-corner-box' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-popup-builder/corner-box.css',
				],
			],
			'tpx-popup-effect' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-popup-builder/popup-effect.css',
				],
			],
			'tpx-push-content' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-popup-builder/push-content.css',
				],
			],
			'tpx-slide-along' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-popup-builder/slide-along.css',
				],
			],
			'tpx-slide' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-popup-builder/slide.css',
				],
			],
			'tpx-fixed-popup-toggle' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-popup-builder/fixed-toggle.css',
				],
			],
			TPGBP_CATEGORY.'/tp-post-navigation' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-post-navigation/style.css',
				],
			],
			'tpx-post-navigation-style-1' => [
				'css' => [
					$tpgb_free .'assets/css/extra/bootstrap-grid.min.css',
					$tpgb_pro .'classes/blocks/tp-post-navigation/style-1.css',
				],
			],
			'tpx-post-navigation-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-post-navigation/style-2.css',
				],
			],
			'tpx-post-navigation-style-3' => [
				'css' => [
					$tpgb_free .'assets/css/extra/bootstrap-grid.min.css',
					$tpgb_pro .'classes/blocks/tp-post-navigation/style-3.css',
				],
			],
			'tpx-post-navigation-style-4' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-post-navigation/style-4.css',
				],
			],
			TPGBP_CATEGORY.'/tp-preloader' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-preloader/style.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/extra/lottie-player.js',
					$tpgb_pro . 'assets/js/main/preloader/tpgb-pre-loader-extra-transition.min.js',
					$tpgb_pro . 'assets/js/main/preloader/tpgb-preloader.min.js',
				],
			],
			TPGBP_CATEGORY.'/tp-pricing-table' => [
				'css' => [
					$tpgb_free .'assets/css/extra/tippy.css',					
					$tpgb_pro .'classes/blocks/tp-pricing-table/style.css',
				],
				'js' => [
					$tpgb_free .'assets/js/extra/popper.min.js',
					$tpgb_free .'assets/js/extra/tippy.min.js',
					$tpgb_pro . 'assets/js/main/pricing-table/tp-pricing-table.min.js',
				],
			],
			'tpx-pricing-table-layout-style-1' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-pricing-table/layout/style-1.css',
				],
			],
			'tpx-pricing-table-layout-style-2' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-pricing-table/layout/style-2.css',
				],
			],
			'tpx-pricing-table-layout-style-3' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-pricing-table/layout/style-3.css',
				],
			],
			'tpx-pricing-table-price-style-1' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-pricing-table/price/style-1.css',
				],
			],
			'tpx-pricing-table-price-style-2' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-pricing-table/price/style-2.css',
				],
			],
			'tpx-pricing-table-price-style-3' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-pricing-table/price/style-3.css',
				],
			],
			'tpx-pricing-table-content-wysiwyg' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-pricing-table/content/wysiwyg.css',
				],
			],
			'tpx-pricing-table-content-stylish' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-pricing-table/content/stylish.css',
				],
			],
			'tpx-pricing-table-ribbon' => [
				'css' => [				
					$tpgb_pro .'classes/blocks/tp-pricing-table/ribbon/style.css',
				],
			],
			TPGBP_CATEGORY.'/tp-lottiefiles' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-lottiefiles/style.css',
				],
			],
			TPGBP_CATEGORY.'/tp-mailchimp' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-mailchimp/style.css',
				],
				'js' => [
					$tpgb_pro .'assets/js/main/mailchimp/plus-mailchimp.min.js',
				],
			],
			'tpx-mailchimp-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-mailchimp/style-1.css',
				],
			],
			'tpx-mailchimp-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-mailchimp/style-2.css',
				],
			],
			'tpx-mailchimp-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-mailchimp/style-3.css',
				],
			],
			'tpx-mailchimp-gdpr' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-mailchimp/style-gdpr.css',
				],
			],
			TPGBP_CATEGORY.'/tp-mouse-cursor' => [
				'js' => [
					$tpgb_pro . 'assets/js/main/mouse-cursor/tpgb-mouse-cursor.min.js',
				],
			],
			'tpx-mouse-follow-image' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-mouse-cursor/type/image.css',
				],
			],
			'tpx-mouse-follow-text' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-mouse-cursor/type/text.css',
				],
			],
			'tpx-mouse-follow-circle' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-mouse-cursor/type/circle.css',
				],
			],
			TPGBP_CATEGORY.'/tp-scroll-navigation' => [

				'css' => [
					$tpgb_pro .'classes/blocks/tp-scroll-navigation/style.css',
				],
				'js' => [
					$tpgb_pro .'assets/js/main/scroll-nav/tpgb-scrollnav.min.js',
				],
			],
			'tpx-scroll-navigation-style-1' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-scroll-navigation/tp-scroll-navigation-style-1.css',
				],
			],
			'tpx-scroll-navigation-style-2' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-scroll-navigation/tp-scroll-navigation-style-2.css',
				],
			],
			'tpx-scroll-navigation-style-3' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-scroll-navigation/tp-scroll-navigation-style-3.css',
				],
			],
			'tpx-scroll-navigation-style-4' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-scroll-navigation/tp-scroll-navigation-style-4.css',
				],
			],
			'tpx-scroll-navigation-style-5' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-scroll-navigation/tp-scroll-navigation-style-5.css',
				],
			],
			'tpx-scroll-navigation-style-6' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-scroll-navigation/tp-scroll-navigation-style-6.css',
				],
			],
			'tpx-display-counter' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-scroll-navigation/tp-display-counter.css',
				],
			],
			'tpx-scroll-offset' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-scroll-navigation/tp-scroll-offset.css',
				],
			],
			TPGBP_CATEGORY.'/tp-scroll-sequence' => [

				'css' => [
					$tpgb_pro .'classes/blocks/tp-scroll-sequence/style.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/scroll-sequence/tpgb-scroll-sequence.min.js',
				],
			],
			'review-feed-load' => [

				'css' => [
					$tpgb_pro .'assets/css/main/social-review-feed/tpgb-review-feed-load.css',
				],
			],
			'hoverTilt' => [
				'js' => [
					$tpgb_pro .'assets/js/extra/vanilla-tilt.min.js',
					$tpgb_pro .'assets/js/main/cta-banner/plus-hover-tilt.min.js',
				],
			],
			'swiperJs' => [
				'css' => [
					$tpgb_pro .'assets/css/extra/swiper.min.css',
				],
				'js' => [
					$tpgb_pro .'assets/js/extra/swiper.min.js',
				],
			],
			'content-hover-effect' => [
				'css' => [
					$tpgb_free .'assets/css/main/plus-extras/plus-content-hover-effect.css',
				],
			],
			'continue-animation' => [
				'css' => [
					$tpgb_pro .'assets/css/main/plus-extras/plus-continue-animation.css',
				],
			],
			'global-tooltip' => [
				'css' => [
					$tpgb_free .'assets/css/extra/tippy.css',
				],
				'js' => [
					$tpgb_free .'assets/js/extra/popper.min.js',
					$tpgb_free .'assets/js/extra/tippy.min.js',
					$tpgb_pro .'assets/js/main/plus-extras/plus-global-tooltip.min.js',
				],
			],
			'tpgb-jstilt' => [
				'js' => [
					$tpgb_pro .'assets/js/extra/vanilla-tilt.min.js',
					$tpgb_pro .'assets/js/main/plus-extras/plus-tilt.min.js',
				],
			],
			'tpgb-mouse-parallax' => [
				'js' => [
					$tpgb_pro .'assets/js/extra/tweenmax/gsap.min.js',
					$tpgb_pro .'assets/js/main/plus-extras/plus-mouse-parallax.min.js',
				],
			],
			'tpgb-sticky-col' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-container-inner/sticky.css',
				],
			],
			TPGBP_CATEGORY.'/tp-login-register' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-login-register/style.css',
				],
				'js' => [
					$tpgb_free .'assets/js/main/common-created/tpgb-slidetoggle-block.min.js',
					$tpgb_pro .'assets/js/main/login-register/tpgb-login.min.js',
				],
			],
			'tpx-form-tab' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-login-register/tp-form-tab.css',
				],
			],
			'tpx-form-button' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-login-register/tp-form-button.css',
				],
			],
			'tpx_sticky_normal_asset' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-container/tp-sticky.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/sticky-container/tpgb-normal-sticky.min.js',
				],
			],
			'tpx_sticky_only' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-container/tp-sticky.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/sticky-container/tpgb-sticky.min.js',
				],
			],
			'tpgb_overlays_css' => [
				'css' => [
					$tpgb_pro .'classes/blocks/tp-container/tp-overlays.css',
				],
			],

			// Listing Releated Asste
			'tpgb-masonary-layout' => [
				'js' => [
					$tpgb_free . 'assets/js/extra/isotope.pkgd.min.js',
					$tpgb_pro . 'assets/js/extra/imagesloaded.pkgd.min.js',
					$tpgb_pro . 'assets/js/main/post-listing/post-masonry.min.js',
					$tpgb_pro . 'assets/js/main/post-listing/post-layout.min.js',
				],
			],
			'tpgb-metro-layout' => [
				'js' => [
					$tpgb_free . 'assets/js/extra/isotope.pkgd.min.js',
					$tpgb_pro . 'assets/js/extra/imagesloaded.pkgd.min.js',
					$tpgb_pro . 'assets/js/main/post-listing/post-metro.min.js',
					$tpgb_pro . 'assets/js/main/post-listing/post-layout.min.js',
				],
			],
			'tpgb-category-filter' => [
				'css' => [
					$tpgb_pro .'assets/css/main/post-listing/post-category-filter.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/post-listing/post-filter.min.js',
				],
			],
			'carouselautoScroll' => [
				'js' => [
					$tpgb_free . 'assets/js/extra/splide-extension-auto-scroll.min.js',
				],
			],
			'carouselSlider' => [
				'css' => [
					$tpgb_free .'assets/css/extra/splide.min.css',
					$tpgb_free .'assets/css/main/post-listing/splide-carousel.min.css',
				],
				'js' => [
					$tpgb_free . 'assets/js/extra/splide.min.js',
					$tpgb_pro . 'assets/js/main/post-listing/post-splide.min.js',
				],
			],
			'tpgb-post-load-more-ajax' => [
				'css' => [
					$tpgb_pro .'assets/css/main/post-listing/tpgb-post-load.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/post-listing/post-load-more.min.js',
				],
			],
			'tpgb-post-lazy-load-ajax' => [
				'css' => [
					$tpgb_pro .'assets/css/main/post-listing/tpgb-post-load.css',
				],
				'js' => [
					$tpgb_pro . 'assets/js/main/post-listing/post-load-lazy.min.js',
				],
			],

			//Free Advance Heading 
			'tpx-heading-title-style-9' => [
				'css' => [
					$tpgb_free .'classes/blocks/tp-heading-title/style/style-9.css',
				],
				'js' => [
					$tpgb_free .'assets/js/extra/waypoints.min.js',
					$tpgb_pro .'assets/js/extra/tweenmax/gsap.min.js',
					$tpgb_free .'assets/js/extra/splittext.min.js',
					$tpgb_pro .'assets/js/main/heading-title/tpgb-heading-title.min.js',
				],
			],
            TPGBP_CATEGORY.'/tp-repeater-block' => [
                'css' => [
                        $tpgb_pro .'classes/blocks/tp-repeater-block/child-block/tp-repeater-layout/style.css',
                ]
            ]
		];

		$load_blocks_css_js = array_merge($load_blocks_css_js,$pro_blocks_register);
		
		return $load_blocks_css_js;
	}
	
	/**
	 * Load Register Blocks Css and Js File
	 *
	 * @since 1.0.0
	 */
	public function load_blocks_registers_render($replace){
	
		$pro_blocks_replace = [
			TPGBP_CATEGORY.'/tp-advanced-buttons' => TPGBP_CATEGORY.'/tp-advanced-buttons',
			TPGBP_CATEGORY.'/tp-advanced-chart' => TPGBP_CATEGORY.'/tp-advanced-chart',
			TPGBP_CATEGORY.'/tp-adv-typo' => TPGBP_CATEGORY.'/tp-adv-typo',
			TPGBP_CATEGORY.'/tp-animated-service-boxes' => TPGBP_CATEGORY.'/tp-animated-service-boxes',
			TPGBP_CATEGORY.'/tp-anything-carousel' => TPGBP_CATEGORY.'/tp-anything-carousel',
			TPGBP_CATEGORY.'/tp-anything-slide' => TPGBP_CATEGORY.'/tp-anything-slide',
			TPGBP_CATEGORY.'/tp-audio-player' => TPGBP_CATEGORY.'/tp-audio-player',
			TPGBP_CATEGORY.'/tp-accordion-inner' => TPGBP_CATEGORY.'/tp-accordion-inner',
			TPGBP_CATEGORY.'/tp-before-after' => TPGBP_CATEGORY.'/tp-before-after',
			TPGBP_CATEGORY.'/tp-carousel-remote' => TPGBP_CATEGORY.'/tp-carousel-remote',
			TPGBP_CATEGORY.'/tp-circle-menu' => TPGBP_CATEGORY.'/tp-circle-menu',
			TPGBP_CATEGORY.'/tp-container' => TPGBP_CATEGORY.'/tp-container',
			TPGBP_CATEGORY.'/tp-container-inner' => TPGBP_CATEGORY.'/tp-container-inner',
			TPGBP_CATEGORY.'/tp-coupon-code' => TPGBP_CATEGORY.'/tp-coupon-code',
			TPGBP_CATEGORY.'/tp-cta-banner' => TPGBP_CATEGORY.'/tp-cta-banner',
			TPGBP_CATEGORY.'/tp-design-tool' => TPGBP_CATEGORY.'/tp-design-tool',
			TPGBP_CATEGORY.'/tp-dynamic-category' => TPGBP_CATEGORY.'/tp-dynamic-category',
			TPGBP_CATEGORY.'/tp-dynamic-device' => TPGBP_CATEGORY.'/tp-dynamic-device',
			TPGBP_CATEGORY.'/tp-expand' => TPGBP_CATEGORY.'/tp-expand',
			TPGBP_CATEGORY.'/tp-heading-animation' => TPGBP_CATEGORY.'/tp-heading-animation',
			TPGBP_CATEGORY.'/tp-hotspot' => TPGBP_CATEGORY.'/tp-hotspot',
			TPGBP_CATEGORY.'/tp-lottiefiles' => TPGBP_CATEGORY.'/tp-lottiefiles',
			TPGBP_CATEGORY.'/tp-mailchimp' => TPGBP_CATEGORY.'/tp-mailchimp',
			TPGBP_CATEGORY.'/tp-media-listing' => TPGBP_CATEGORY.'/tp-media-listing',
			TPGBP_CATEGORY.'/tp-mobile-menu' => TPGBP_CATEGORY.'/tp-mobile-menu',
			TPGBP_CATEGORY.'/tp-mouse-cursor' => TPGBP_CATEGORY.'/tp-mouse-cursor',
			TPGBP_CATEGORY.'/tp-navigation-builder' => TPGBP_CATEGORY.'/tp-navigation-builder',
			TPGBP_CATEGORY.'/tp-popup-builder' => TPGBP_CATEGORY.'/tp-popup-builder',
			TPGBP_CATEGORY.'/tp-post-navigation' => TPGBP_CATEGORY.'/tp-post-navigation',
			TPGBP_CATEGORY.'/tp-preloader' => TPGBP_CATEGORY.'/tp-preloader',
			TPGBP_CATEGORY.'/tp-pricing-table' => TPGBP_CATEGORY.'/tp-pricing-table',
			TPGBP_CATEGORY.'/tp-process-steps' => TPGBP_CATEGORY.'/tp-process-steps',
			TPGBP_CATEGORY.'/tp-product-listing' => TPGBP_CATEGORY.'/tp-product-listing',
			TPGBP_CATEGORY.'/tp-scroll-navigation' => TPGBP_CATEGORY.'/tp-scroll-navigation',
			TPGBP_CATEGORY.'/tp-scroll-sequence' => TPGBP_CATEGORY.'/tp-scroll-sequence',
			TPGBP_CATEGORY.'/tp-social-feed' => TPGBP_CATEGORY.'/tp-social-feed',
			TPGBP_CATEGORY.'/tp-social-sharing' => TPGBP_CATEGORY.'/tp-social-sharing',
			TPGBP_CATEGORY.'/tp-social-reviews' => TPGBP_CATEGORY.'/tp-social-reviews',
			TPGBP_CATEGORY.'/tp-spline-3d-viewer' => TPGBP_CATEGORY.'/tp-spline-3d-viewer',
			TPGBP_CATEGORY.'/tp-switcher' => TPGBP_CATEGORY.'/tp-switcher',
			TPGBP_CATEGORY.'/tp-switch-inner' => TPGBP_CATEGORY.'/tp-switch-inner',
			TPGBP_CATEGORY.'/tp-table-content' => TPGBP_CATEGORY.'/tp-table-content',
			TPGBP_CATEGORY.'/tp-team-listing' => TPGBP_CATEGORY.'/tp-team-listing',
			TPGBP_CATEGORY.'/tp-tab-item' => TPGBP_CATEGORY.'/tp-tab-item',
			TPGBP_CATEGORY.'/tp-timeline' => TPGBP_CATEGORY.'/tp-timeline',
			TPGBP_CATEGORY.'/tp-timeline-inner' => TPGBP_CATEGORY.'/tp-timeline-inner',
			TPGBP_CATEGORY.'/tp-login-register' => TPGBP_CATEGORY.'/tp-login-register',
            TPGBP_CATEGORY.'/tp-repeater-block' => TPGBP_CATEGORY.'/tp-repeater-block',
		];
		
		$replace = array_merge($pro_blocks_replace, $replace);

		return $replace;
	}
	
	public function include_block($block){
	
		$filename = sprintf('classes/blocks/'.$block.'/index.php');

		if ( ! file_exists( TPGBP_PATH.$filename ) ) {
			return false;
		}

		require TPGBP_PATH.$filename;

		return true;
	}
	
	/*
	 * Get Load activate Block for tpgb
	 * @Array
	 * @since 1.0.0 
	 */
	public static function get_block_enabled() {
	
		$load_enable_block = self::$load_blocks;
		
		if( !empty( $load_enable_block ) ){
			return $load_enable_block;
		}else{
			return;
		}
	}
	
	/*
	 * Get load deactivate Block for tpgb
	 * @Array
	 * @since 1.0.0
	 */
	public static function get_block_deactivate() {
		$load_disable_block = self::$deactivate_blocks;
		
		if( !empty( $load_disable_block ) ) {
			return $load_disable_block;
		}else{
			return;
		}
	}
	
	
	//Get Manu List
    public function get_menu_lists(){
		$menus = wp_get_nav_menus();
		$menu_items = array();
		$menu_items[] = [' ' , esc_html__( 'Select Menu' , 'tpgbp'  ) ];
		foreach ( $menus as $menu ) {
			$menu_items[] = [ $menu->slug , $menu->name];
		}
	
		return $menu_items;
	}
	
	/*
	 * Get Meta Options
	 * @since 1.0.0
	 */
	public function tpgb_get_option($options,$field){
		
		$tpgb_options=get_option( $options );
		$values='';
		if($tpgb_options){
			if(isset($tpgb_options[$field]) && !empty($tpgb_options[$field])){
				$values=$tpgb_options[$field];
			}
		}
		return $values;
	}
	
	/**
	 * Row Shape Divider
	 */
	public function getShapeDivider() {
		$path = TPGB_PATH . 'assets/images/shape-divider';
		$getShapes = glob($path . '/*.svg');
		$shapeLoop = array();
		if (count($getShapes)) {
			foreach ($getShapes as $shape) {
				$shapeLoop[str_replace(array('.svg', $path . '/'), '', $shape)] = file_get_contents($shape);
			}
		}
		return $shapeLoop;
	}

	/*
	* Dynamic List 
	*
	* @since 1.3.0
	*/
	public static function tpgb_get_dynamic_list(){
		$dynamicList = array();
		$dynamicList['text'][] = [ '' , esc_html__('Select Option','tpgbp') ];
		$dynamicList['text'][] = [ 
			[ 
				'label' => 'Post' , 
				'options' => [ 
					[ 'post-title' , esc_html__('Post Title','tpgbp') ],[ 'post-slug' , esc_html__('Post Slug','tpgbp')], ['post-excerpt' , esc_html__('Post Excerpt','tpgbp')],[ 'post-date' , esc_html__('Post Date','tpgbp')],[ 'post-date-gmt' , esc_html__('Post Date GMT','tpgbp')],[ 'post-modified' ,esc_html__('Post Modified','tpgbp')],[ 'post-modified-gmt' ,esc_html__('Post Modified GMT','tpgbp')],[ 'post-type' ,esc_html__('Post Type','tpgbp')],[ 'post-status' ,esc_html__('Post Status','tpgbp')] 
				] 
			]
		];
		$dynamicList['text'][] = [ 
			[ 
				'label' => 'Archive ' , 
				'options' => [ [ 'archive-title' , esc_html__('Archive Title','tpgbp') ] ,[ 'archive-description' , esc_html__('Archive Description','tpgbp')] ,[ 'archive-url' , esc_html__('Archive URL', 'tpgbp')] ]
			] 
		] ;
		$dynamicList['text'][] = [ 
			[ 
				'label' => 'Author' , 
				'options' => [ [ 'author-name' , esc_html__('Author Name','tpgbp') ] ,[ 'author-id' , esc_html__('Author ID','tpgbp')] ,[ 'author-posts' , esc_html__('Author Posts', 'tpgbp')] ,[ 'author-first-name' , esc_html__('Author First Name','tpgbp')],[ 'author-last-name' , esc_html__('Author Last Name','tpgbp')] ]
			] 
		] ;
		$dynamicList['text'][] = [ 
			[  
				'label' => 'Comment' , 
				'options' => [ [ 'comment-number' , esc_html__('Comment Number','tpgbp') ] , [ 'comment-status' , esc_html__('Comment Status','tpgbp')] ]
			] 
		];
		$dynamicList['text'][] = [ 
			[  
				'label' => 'Site' , 
				'options' => [ [ 'site-title' , esc_html__('Site Title','tpgbp') ] , [ 'site-tagline' , esc_html__('Site Tagline','tpgbp')] ]
			] 
		];
		
		//Woocommerce
		if( class_exists('woocommerce') ){
			$dynamicList['text'][] = [ 
				[  
					'label' => esc_html__('WooCommerce','tpgbp'), 
					'options' => [ [ 'tpgb-product-title-tag' , esc_html__('Product Title','tpgbp')] , [ 'tpgb-product-terms-tag' , esc_html__('Product Terms','tpgbp')],[ 'tpgb-product-price-tag' , esc_html__('Product Price','tpgbp') ] , [ 'tpgb-product-rating-tag' , esc_html__('Product Rating','tpgbp')] , [ 'tpgb-product-sale-tag' , esc_html__('Product Sale','tpgbp')] , [ 'tpgb-product-short-description-tag' , esc_html__('Product Short Description','tpgbp')] , [ 'tpgb-product-sku-tag' , esc_html__('Product SKU','tpgbp')],[ 'tpgb-product-stock-tag' , esc_html__('Product Stock','tpgbp')]]
				] 
			];
		}
		
		$dynamicList['url'][] = [ '' ,esc_html__('Select Option','tpgbp') ];
		$dynamicList['url'][] = [ 
			[ 
				'label' => 'Post' , 
				'options' => [ 
					[ 'post-url' , esc_html__('Post URL','tpgbp') ]
				] 
			]
		];
		$dynamicList['url'][] = [ 
			[ 
				'label' => 'Site' , 
				'options' => [ 
					[ 'site-url' , esc_html__('Site URL','tpgbp') ],
				] 
			]
		];
		$dynamicList['url'][] = [
			[ 
				'label' => 'Author' , 
				'options' => [ 
					[ 'author-post-url' , esc_html__('Author Posts URL','tpgbp') ],
					[ 'author-profile-url' , esc_html__('Author Profile Picture URL','tpgbp') ],
				] 
			]
		];		

		$dynamicList['image'][] = [ '' , esc_html__('Select Option','tpgbp') ];
		$dynamicList['image'][] = [ 
			[ 
				'label' => 'Post' , 
				'options' => [ 
					[ 'post-featured-image' , esc_html__('Featured Image','tpgbp') ]
				] 
			]
		];
		$dynamicList['image'][] = [ 
			[ 
				'label' => 'Site' , 
				'options' => [ 
					[ 'site-logo' , esc_html__('Site Logo','tpgbp') ],
				] 
			]
		];
		$dynamicList['image'][] = [ 
			[ 
				'label' => 'Author' , 
				'options' => [ 
					[ 'author-profile-picture' , esc_html__('Author Profile Picture','tpgbp') ],
					[ 'user-profile-picture' , esc_html__('User Profile Picture','tpgbp') ],
				] 
			]
		];
		
		$dynamicList['color'][] = [ '' , esc_html__('Select Color','tpgbp') ];
		
		/*ACF Plugin*/
		if( class_exists( 'ACF' )){
			if ( function_exists( 'acf_get_field_groups' ) ) {
				$acffield = acf_get_field_groups();
			} else {
				$acffield = apply_filters( 'acf/get_field_groups', [] );
			}
			$keyarr = [];
			foreach ( $acffield as $field_group ) {
				$keyarr[] = $field_group['key'];
			}
			$excluded_field_type = [
				'oembed',
				'gallery',
				'post_object',
				'relationship',
				'google_map',
				'message',
				'accordion',
				'tab',
				'group',
				'flexible_content',
				'clone',
			];
			foreach ( $acffield as $field_group ) {
				$key = $field_group['key'];
				$title = $field_group['title'];

				$tpgbDyfield = [];
				$tpgbDyUrl = [];
				$tpgbDyImg = [];
				$tpgbDyColor = [];
                $tpgbrepField = [];
				
				if ( function_exists( 'acf_get_fields' ) ) {
					if ( isset( $field_group['ID'] ) && ! empty( $field_group['ID'] ) ) {
						$fields = acf_get_fields( $field_group['ID'] );
					} else {
						$fields = acf_get_fields( $field_group );
					}
				} else {
					$fields = apply_filters( 'acf/field_group/get_fields', [], $field_group['id'] );
				}
				
				foreach( $fields as $acf_field ) {
					if ( !empty( $acf_field['name'] ) && ! in_array( $acf_field['type'], $excluded_field_type ) ) {
						$tpgbDyfield[] = [ $acf_field['name'] , $acf_field['label'] ];
					}
					if(!empty( $acf_field['name'] ) && $acf_field['type'] == 'url'){
						$tpgbDyUrl[] = [ $acf_field['name'] , $acf_field['label'] ];
					}
					if(!empty( $acf_field['name'] ) && in_array($acf_field['type'] , ['image','file','gallery'])){
						$tpgbDyImg[] = [ $acf_field['name'] , $acf_field['label'] ];
					}
					if(!empty( $acf_field['name'] ) && $acf_field['type'] == 'color_picker'){
						$tpgbDyColor[] = [ $acf_field['name'] , $acf_field['label'] ];
					}
                    if(!empty( $acf_field['name'] ) && $acf_field['type'] == 'repeater'){
                        $tpgbrepField[] = [ 'acf|'.$acf_field['name'] , $acf_field['label'] ];
                    }
				}
				$dynamicList['text'][] = [
					[  
						'label' => esc_html('ACF('.$title.')'),
						'options' =>  $tpgbDyfield 
					]
				];
				if(!empty($tpgbDyUrl) ) {
					$dynamicList['url'][] = [
						[ 
							'label' => esc_html('ACF('.$title.')'),
							'options' => $tpgbDyUrl
						]
					];
				}
				if(!empty($tpgbDyImg) ) {
					$dynamicList['image'][] = [ 
						[ 
							'label' => esc_html('ACF('.$title.')'),
							'options' => $tpgbDyImg
						]
					];
				}
				if(!empty($tpgbDyColor) ) {
					$dynamicList['color'][] = [
						[ 
							'label' => esc_html('ACF('.$title.')'),
							'options' => $tpgbDyColor
						]
					];
				}
                if(!empty($tpgbrepField) ) {
					$dynamicList['repeater'][] = [
						[ 
							'label' => esc_html('ACF('.$title.')'),
							'options' => $tpgbrepField
						]
					];
				}
			}
		}
		
		/*Metabox Plugin*/
		if( class_exists( 'RWMB_Field' ) ){
			$ex_meta = [ 
				'button',
				'button_group',
				'color',
				'file',
				'file_advanced',
				'image',
				'image_advanced',
				'image_select',
				'image_upload',
				'key_value',
				'oembed',
				'osm',
				'post',
				'single_image',
				'slider',
				'switch',
				'taxonomy',
				'taxonomy_advanced',
				'time',
				'video',
				'password',
				'hidden',
				'range',
			];
			$meta_box_registry = (array) rwmb_get_registry( 'meta_box' )->all();
			foreach($meta_box_registry as $meta_field){
				$tpgbmefield = [];
				$tpgbmeUrl = [];
				$tpgbmeImg = [];
				$tpgbmeColor = [];

				$meta_box = (array) $meta_field;
				$meTitle = $meta_box['meta_box']['title'];
				$meta_field = $meta_box['meta_box']['fields'];
				foreach($meta_field as $field){
					if ( ! empty( $field['name'] ) && ! in_array( $field['type'], $ex_meta ) ) {
						$tpgbmefield[] = [ $field['id'] , $field['name'] ];
					}
					if ( ! empty( $field['name'] ) && in_array( $field['type'], ['single_image','image'] ) ) {
						$tpgbmeImg[] = [ $field['id'] , $field['name'] ];
					}
					if ( ! empty( $field['name'] ) && $field['type'] == 'url' ) {
						$tpgbmeUrl[] = [ $field['id'] , $field['name'] ];
					}
					if ( ! empty( $field['name'] ) && $field['type'] == 'color' ) {
						$tpgbmeColor[] = [ $field['id'] , $field['name'] ];
					}
				}
				if(!empty($tpgbmefield)){
					$dynamicList['text'][] = [ 
						[  
							'label' => 'Meta('.$meTitle.')', 
							'options' =>  $tpgbmefield 
						] 
					];
				}
				if(!empty($tpgbmeUrl) ) {
					$dynamicList['url'][] = [
						[ 
							'label' => 'Meta('.$meTitle.')', 
							'options' => $tpgbmeUrl
						]
					];
				}
				if(!empty($tpgbmeImg) ) {
					$dynamicList['image'][] = [ 
						[ 
							'label' => 'Meta('.$meTitle.')', 
							'options' => $tpgbmeImg
						]
					];
				}
				if(!empty($tpgbmeColor) ) {
					$dynamicList['color'][] = [ 
						[ 
							'label' => 'Meta('.$meTitle.')', 
							'options' => $tpgbmeColor
						]
					];
				}
			}
		}
		
		/*Toolset Plugin*/
		if(is_plugin_active( 'types/wpcf.php' )){
			$ex_tool_meta = [
				'file',
				'image',
				'video',
				'embed',
			];
			$groups = wpcf_admin_fields_get_groups();
			foreach($groups AS $group){
				$tpgbtoolfield = [];
				$tpgbtoolUrl = [];
				$tpgbtoolImg = [];
				$tpgbtoolColor = [];
				
				if($group['slug'] !== 'toolset-woocommerce-fields'){
					$fields =  wpcf_admin_fields_get_fields_by_group($group['id']);
					$tooltitle = $group['name'];
					foreach($fields as $key=>$value){
						if ( ! empty( $value['name'] ) && ! in_array( $value['type'], $ex_tool_meta ) ) {
							$tpgbtoolfield[] = [ 'wpcf-'.$value['id'] , $value['name']];
						}
						if(! empty( $value['name'] ) && $value['type'] == 'image' ){
							$tpgbtoolImg[] = [ 'wpcf-'.$value['id'] , $value['name']];
						}
						if(! empty( $value['name'] ) && $value['type'] == 'url' ){
							$tpgbtoolUrl[] = [ 'wpcf-'.$value['id'] , $value['name']];
						}
						if(! empty( $value['name'] ) && $value['type'] == 'colorpicker' ){
							$tpgbtoolColor[] = [ 'wpcf-'.$value['id'] , $value['name']];
						}
					}
				}
				if(!empty($tpgbtoolfield)){
					$dynamicList['text'][] = [
						[  
							'label' => esc_html('Toolset('.$tooltitle.')'),
							'options' =>  $tpgbtoolfield
						]
					];
				}
				if(!empty($tpgbtoolUrl)){
					$dynamicList['url'][] = [
						[ 
							'label' => esc_html('Toolset('.$tooltitle.')'),
							'options' => $tpgbtoolUrl
						]
					];
				}
				if(!empty($tpgbtoolImg)){
					$dynamicList['image'][] = [ 
						[ 
							'label' => esc_html('Toolset('.$tooltitle.')'),
							'options' => $tpgbtoolImg
						]
					];
				}
				if(!empty($tpgbtoolColor)){
					$dynamicList['color'][] = [ 
						[ 
							'label' => esc_html('Toolset('.$tooltitle.')'),
							'options' => $tpgbtoolColor
						]
					];
				}
			}
		}
		
		/*PODS Plugin*/
		if(class_exists('PodsInit')){
			$all_pods = pods_api()->load_pods( [
				'table_info' => true,
				'fields' => true,
			]);
			$groups = [];
			if (!empty($all_pods)) {
				foreach ( $all_pods as $group ) {
					$tpgbpoText = [];
					$tpgbpoUrl = [];
					$tpgbpoImg = [];
					$tpgbpoColor = [];
					if (!empty($group['fields'])) {
						foreach ( $group['fields'] as $field ) {
							if( !empty( $field['name'] ) && in_array( $field['type'], ['text','phone','email','paragraph','wysiwyg','time','oembed'] )){
								$tpgbpoText[] = [ $group['name'] . ':' . $field['pod_id'] . ':' . $field['name'], $field['label']];
							}
							if( !empty( $field['name'] ) && in_array( $field['type'], ['website','phone','email','file'] )){
								$tpgbpoUrl[] = [ $group['name'] . ':' . $field['pod_id'] . ':' . $field['name'], $field['label']];
							}
							if( !empty( $field['name'] ) &&  $field['type'] == 'file'){
								$tpgbpoImg[] = [ $group['name'] . ':' . $field['pod_id'] . ':' . $field['name'], $field['label']];
							}
							if( !empty( $field['name'] ) &&  $field['type'] == 'color'){
								$tpgbpoColor[] =[ $group['name'] . ':' . $field['pod_id'] . ':' . $field['name'], $field['label']];
							}
						}
					}
					if(!empty($tpgbpoText)){
						$dynamicList['text'][] = [
							[
								'label' => esc_html('Pods('.$group['label'].')'),
								'options' => $tpgbpoText,
							]
						];
					}
					if(!empty($tpgbpoUrl)){
						$dynamicList['url'][] = [
							[
								'label' => esc_html('Pods('.$group['label'].')'),
								'options' => $tpgbpoUrl,
							]
						];
					}
					if(!empty($tpgbpoImg)){
						$dynamicList['image'][] = [
							[
								'label' => esc_html('Pods('.$group['label'].')'),
								'options' => $tpgbpoImg,
							]
						];
					}
					if(!empty($tpgbpoColor)){
						$dynamicList['color'][] = [
							[
								'label' => esc_html('Pods('.$group['label'].')'),
								'options' => $tpgbpoColor,
							]
						];
					}
				}
			}
		}
		
		/* Jet Engine Meta Box */
		if ( class_exists( 'Jet_Engine' ) ) {
			$output = array();

			$tpgb_jet_ex = [
				'iconpicker',
				'html',
				'gallery',
			];

			$tpgb_jet_ex_obj = [
				'tab',
				'accordion',
				'endpoint',
			];

			$tpgb_jet_groups = jet_engine()->meta_boxes->data->raw;
			$uni_groups = jet_engine()->meta_boxes->data->db->query_cache;

			foreach ( $tpgb_jet_groups as $field_group ) {

				$tpgbjetfield = [];
				$tpgbjetUrl = [];
				$tpgbjetImg = [];
				$tpgbjetColor = [];
                $tpgbjetRepeat = [];

				foreach ( $field_group['meta_fields'] as $field ) {
					if ( ! in_array( $field['type'], $tpgb_jet_ex ) && ! in_array( $field['object_type'], $tpgb_jet_ex_obj ) ) {
						if( !empty( $field['name'] ) && in_array( $field['type'], ['text','date','time','datetime-local','textarea','wysiwyg','number' , 'radio' , 'select'] )){
							$tpgbjetfield[] = [  $field['name'], $field['title']];
						}
						if(!empty( $field['name'] ) && in_array($field['type'] , ['media','gallery'])){
							$tpgbjetImg[] = [ $field['name'] , $field['title'] ];
						}
						if(!empty( $field['name'] ) && $field['type'] == 'colorpicker'){
							$tpgbjetColor[] = [ $field['name'] , $field['title'] ];
						}
                        if(!empty( $field['name'] ) && $field['type'] == 'repeater'){
							$tpgbjetRepeat[] = [ "jetengine|".$field['name'], $field['title'] ];
						}
					}
				}

				if(!empty($tpgbjetfield)){
					$dynamicList['text'][] = [
						[
							'label' => esc_html('JetEngine('.$field_group['args']['name'].')'),
							'options' => $tpgbjetfield,
						]
					];
				}
				if(!empty($tpgbjetImg) ) {
					$dynamicList['image'][] = [ 
						[ 
							'label' => esc_html('JetEngine('.$field_group['args']['name'].')'),
							'options' => $tpgbjetImg
						]
					];
				}
				if(!empty($tpgbjetColor) ) {
					$dynamicList['color'][] = [
						[ 
							'label' => esc_html('JetEngine('.$field_group['args']['name'].')'),
							'options' => $tpgbjetColor
						]
					];
				}
                if ( ! empty( $tpgbjetRepeat ) ) {
                    $dynamicList['repeater'][] = [
                        [
                            'label' => esc_html('JetEngine('.$field_group['args']['name'].')'),
                            'options' => $tpgbjetRepeat,
                        ]
                    ];
                }
			}
		}

		$dynamicList = apply_filters( 'tpgb_add_dynamic_option' , $dynamicList);

		return $dynamicList;
	}
	
	// Convert Nested Std Object To array
	public function stdToArray($array){
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				if (is_array($value)) {
						$array[$key] = $this->stdToArray($value);
				}
				if ($value instanceof stdClass) {
						$array[$key] = $this->stdToArray((array)$value);
				}
			}
		}
		if ($array instanceof stdClass) {
			return $this->stdToArray((array)$array);
		}
		return $array;
	}

	/*
	 * Get Post on ajax call Load More & Lazy Load
	 * @since 1.2.1
	 */
	public function tpgb_post_load(){
		global $post;
		ob_start();

		$pages = isset($_POST["pages"]) ? intval(wp_unslash($_POST["pages"])) : 1;

		$postdata1 = isset($_POST["option"]) ? wp_unslash( $_POST["option"] ) : '';
		$postdata2 = isset($_POST["dyOpt"]) ? wp_unslash( $_POST["dyOpt"] ) : '';
		//Encrypt Data
		$postdata1 = self::tpgb_check_decrypt_key($postdata1);
		$postdata1 = (array)json_decode($postdata1);

		$postdata = array_merge( $postdata1,(array) json_decode($postdata2));
		if(empty($postdata) || !is_array($postdata)){
			ob_get_contents();
			exit;
			ob_end_clean();
		}
		//verify nonce	
		$pagiOpt = isset( $postdata["paginationOpt"] ) ? sanitize_text_field( wp_unslash($postdata["paginationOpt"]) ) : '';

		$nonce = isset($postdata['tpgb_nonce']) ? sanitize_text_field(wp_unslash($postdata['tpgb_nonce'])) : '';
		if ( !isset($postdata["tpgb_nonce"]) || !wp_verify_nonce( $nonce, 'theplus-addons-block' ) ){
			die ( 'Security checked!');
		}
		
		$posttype = isset( $postdata["post_type"] ) ? sanitize_text_field( wp_unslash($postdata["post_type"]) ) : '';
		$taxonomySlug = isset( $postdata["texonomy_category"] ) ? sanitize_text_field( wp_unslash($postdata["texonomy_category"]) ) : '';
		$category = isset( $postdata["category"] ) ? wp_unslash($postdata["category"])  : '';
		$layout = isset( $postdata["layout"] ) ? sanitize_text_field( wp_unslash($postdata["layout"]) ) : '';
		$desktop_column = (isset( $postdata["desktop_column"] )  && intval($postdata["desktop_column"]) ) ? wp_unslash($postdata["desktop_column"]) : '';
		$tablet_column = (isset( $postdata["tablet_column"] )  && intval($postdata["tablet_column"]) ) ? wp_unslash($postdata["tablet_column"]) : '';
		$mobile_column = (isset( $postdata["mobile_column"] )  && intval($postdata["mobile_column"]) ) ? wp_unslash($postdata["mobile_column"]) : '';
		$style = isset( $postdata["style"] ) ? sanitize_text_field( wp_unslash($postdata["style"]) ) : '';
		$styleLayout= isset( $postdata["styleLayout"] ) ? sanitize_text_field( wp_unslash($postdata["styleLayout"]) ) : '';
		$style2Alignment = isset( $postdata['style2Alignment'] ) ? sanitize_text_field( wp_unslash($postdata['style2Alignment']) ) : '';
		$style3Alignment = isset( $postdata['style3Alignment'] ) ? sanitize_text_field( wp_unslash($postdata['style3Alignment']) ) : '';
		$ShowFilter = isset( $postdata["filter_category"] ) ? sanitize_text_field( wp_unslash($postdata["filter_category"]) ) : '';
		$orderBy = isset( $postdata["order_by"] ) ? sanitize_text_field( wp_unslash($postdata["order_by"]) ) : '';
		$order = isset( $postdata["post_order"] ) ? sanitize_text_field( wp_unslash($postdata["post_order"]) ) : '';
		$post_load_more = (isset( $postdata["load_more"] ) && intval($postdata["load_more"]) ) ?  wp_unslash($postdata["load_more"]) : '';

		// Changed Ajax Pagination
		$paged = (isset( $postdata["page"] ) && intval($postdata["page"]) ) ?  wp_unslash($postdata["page"]) : $pages;

		$viewPostNtab = !empty($postdata['viewPostNtab']) ? $postdata['viewPostNtab'] : false;
		$display_post = (isset( $postdata["display_post"] ) && intval($postdata["display_post"]) ) ?  wp_unslash($postdata["display_post"]) : '';
		$showPostMeta = isset( $postdata["display_post_meta"] ) ? sanitize_text_field( wp_unslash($postdata["display_post_meta"]) ) : '';
		$postMetaStyle = isset( $postdata["meta_style"] ) ? sanitize_text_field( wp_unslash($postdata["meta_style"]) ) : '';
		$ShowDate = isset( $postdata["showdate"] ) ? sanitize_text_field( wp_unslash($postdata["showdate"]) ) : '';
		$ShowAuthor = isset( $postdata["showauthor"] ) ? sanitize_text_field( wp_unslash($postdata["showauthor"]) ) : '';
		$ShowAuthorImg = isset( $postdata['ShowAuthorImg'] ) ? sanitize_text_field( wp_unslash($postdata['ShowAuthorImg']) ) : '';
		
		$display_thumbnail = isset( $postdata['display_thumbnail'] ) ? sanitize_text_field( wp_unslash($postdata['display_thumbnail']) ) : '';
		$thumbnail = isset( $postdata['thumbnail'] ) ? sanitize_text_field( wp_unslash($postdata['thumbnail']) ) : 'full';
		
		$postCategoryStyle = isset( $postdata["post_category_style"] ) ? sanitize_text_field( wp_unslash($postdata["post_category_style"]) ) : '';
		$showPostCategory = isset( $postdata["display_catagory"] ) ? sanitize_text_field( wp_unslash($postdata["display_catagory"]) ) : '';

		$ShowTitle = isset( $postdata["display_title"] ) ? sanitize_text_field( wp_unslash($postdata["display_title"]) ) : '';
		$titleTag = isset( $postdata['titletag'] ) ? sanitize_text_field( wp_unslash($postdata['titletag']) ) : '';
		$titleLimit = isset( $postdata["display_title_limit"] ) ? wp_unslash($postdata["display_title_limit"]) : '';
		$titleByLimit = isset( $postdata["display_title_by"] ) ? wp_unslash($postdata["display_title_by"]) : '';
		$filterBy = isset( $postdata['filterBy'] ) ? sanitize_text_field( wp_unslash($postdata['filterBy']) ) : '';

		$showExcerpt = isset( $postdata["display_excerpt"] ) ? sanitize_text_field( wp_unslash($postdata["display_excerpt"]) ) : '';
		$excerptByLimit = isset( $postdata["excerptByLimit"] ) ? wp_unslash($postdata["excerptByLimit"]) : '';
		$excerptLimit = isset( $postdata["excerptLimit"] ) ? wp_unslash($postdata["excerptLimit"]) : '';
		
		$ShowButton = isset( $postdata['displaybuttton'] ) ? sanitize_text_field( wp_unslash($postdata['displaybuttton']) ) : '';
		$postbtntext = isset( $postdata['postbtntext'] ) ? sanitize_text_field( wp_unslash($postdata['postbtntext']) ) : '';
		$postBtnsty = isset( $postdata['buttonstyle'] ) ? sanitize_text_field( wp_unslash($postdata['buttonstyle']) ) : '';
		$pobtnIconType = isset( $postdata['pobtnIconType'] ) ? sanitize_text_field( wp_unslash($postdata['pobtnIconType']) ) : '';
		$pobtnIconName = isset( $postdata['pobtnIconName'] ) ? sanitize_text_field( wp_unslash($postdata['pobtnIconName']) ) : '';
		$btnIconPosi = isset( $postdata['btnIconPosi'] ) ? sanitize_text_field( wp_unslash($postdata['btnIconPosi']) ) : '';
		$imageHoverStyle = isset( $postdata['imageHoverStyle'] ) ? sanitize_text_field( wp_unslash($postdata['imageHoverStyle']) ) :'';
		$postTag =  isset( $postdata['postTag'] ) ? wp_unslash($postdata['postTag']) : '';
		$includePosts = (isset( $postdata["includePosts"] )  && intval($postdata["includePosts"]) ) ? wp_unslash($postdata["includePosts"]) : '';
		$excludePosts = (isset( $postdata["excludePosts"] )  && intval($postdata["excludePosts"]) ) ? wp_unslash($postdata["excludePosts"]): '';
		$display_product = isset( $postdata["disproduct"] ) ? wp_unslash($postdata["disproduct"]) : '';
		$dyload = isset( $postdata["dyload"] ) ? wp_unslash($postdata["dyload"]) : '';

		$metrocolumns = isset( $postdata["metro_column"] ) ? (array) wp_unslash($postdata["metro_column"]) : [' md' => '3'] ;
		$metroStyle = isset($postdata['metro_style']) ?  (array) wp_unslash($postdata['metro_style']) : '';
		$metroCustom = isset($postdata['metro_Custom']) ? wp_unslash($postdata['metro_Custom']) : '';
		$authorTxt = isset($postdata['authorTxt']) ? wp_unslash($postdata['authorTxt']) : '';
		$blockTemplate = !empty( $postdata['blockTemplate'] ) ? sanitize_text_field( wp_unslash($postdata['blockTemplate']) ) : '';
		$searchTxt = isset( $postdata["searchTxt"] ) ? wp_unslash($postdata["searchTxt"]) : '';
		$customQueryId = !empty( $postdata['customQueryId'] ) ? $postdata['customQueryId'] : '';
		$showcateTag = !empty( $postdata['showcateTag'] ) ? sanitize_text_field( wp_unslash($postdata['showcateTag']) ) : '';
		$cuscntType = !empty( $postdata['cuscntType'] ) ? sanitize_text_field( wp_unslash($postdata['cuscntType']) ) : '';
		$block_instance = isset( $postdata['blockArr'] ) ? wp_unslash($postdata['blockArr']) : '';
		$block_instance = $this->stdToArray($block_instance);
		$DisBadge = isset( $postdata['badge'] ) ? wp_unslash($postdata['badge']) : '';
        $tax_query = isset( $postdata["tax_query"] ) ? $postdata["tax_query"] : "" ;
        
		$content = '';
		
		$offset = ( $pagiOpt !== '') ? (($pages - 1) * $display_post + (isset($postdata["offset"]) ? intval($postdata["offset"]) : 0)) : ((isset($postdata["offset"]) && intval($postdata["offset"])) ? wp_unslash($postdata["offset"]) : '');

		$column_class = '';
		if($layout!='carousel' && $layout!='metro'){
			$column_class .= " tpgb-col-lg-".esc_attr($desktop_column);
			$column_class .= " tpgb-col-md-".esc_attr($tablet_column);
			$column_class .= " tpgb-col-sm-".esc_attr($mobile_column);
			$column_class .= " tpgb-col-".esc_attr($mobile_column);
		}

		$newTabPostAttr = '';
		if(!empty($viewPostNtab)){
			$newTabPostAttr = 'target="_blank" rel="nofollow"';
		}

		$args = array(
			'post_type'  => $posttype,
			'post_status' => 'publish',
			'posts_per_page' => $pagiOpt ? $display_post : $post_load_more,
			'offset' => $offset,
			'orderby' => $orderBy,
			'order'	=> $order,
			'paged' => $paged,
		);
		
		if(!empty($excludePosts)){
			$args['post__not_in'] =  explode(',', $excludePosts);
		}

		if( !empty($searchTxt)){
			$args['s'] = $searchTxt;
		}

		if(!empty($includePosts)){
			$args['post__in'] = explode(',', $includePosts);
		}

		if($posttype == 'post'){
			$args['cat'] = $category;
		}else if($posttype == 'product'){
			if(!empty($category)){
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'product_cat',
						'field' => 'term_id',
						'terms' => $category,
					),
				);
			}
			if(!empty($postTag)){
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'product_tag',
						'field' => 'term_id',
						'terms' => $postTag,
					),
				);
			}
		}else{
			if (!empty($posttype) && ($posttype !='post' && $posttype !='product')) {
				if ( !empty($taxonomySlug) && $taxonomySlug == 'category' && !empty($category)) {
					$args['tax_query'] = array(
						array(
							'taxonomy' => 'category',
							'field' => 'term_id',
							'terms' => $category,
						),
					);
				}else{
					if(!empty($category)){
						$args['tax_query'] = array(
							array(
								'taxonomy' => $taxonomySlug,
								'field' => 'term_id',
								'terms' => $category,
							),
						);
					}else {
                        if ( ! empty( $tax_query ) && isset( $tax_query[0] ) ) {
                            $first = $tax_query[0];

                            // If it's an object, cast to array
                            if ( is_object( $first ) ) {
                                $first = (array) $first;
                            }

                            $args['tax_query'] = array(
                                array(
                                    'taxonomy' => $first['taxonomy'],
                                    'field'    => $first['field'],
                                    'terms'    => (array) $first['terms'], // ensure array
                                ),
                            );
                        }
                    }
				}
			}else{
				$args[$taxonomySlug] = $category;
			}
		}
		
		if((!empty($posttype) && $posttype =='product')){
			if(!empty($display_product) && $display_product=='featured'){
				$args['tax_query']     = array(
					array(
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => 'featured',
					),
				);
			}
			
			if(!empty($display_product) && $display_product=='on_sale'){
				$args['meta_query']     = array(
					'relation' => 'OR',
					array( // Simple products type
						'key'           => '_sale_price',
						'value'         => 0,
						'compare'       => '>',
						'type'          => 'numeric'
					),
					array( // Variable products type
						'key'           => '_min_variation_sale_price',
						'value'         => 0,
						'compare'       => '>',
						'type'          => 'numeric'
					)
				);
			}
			
			if(!empty($display_product) && $display_product=='top_sales'){
				$args['meta_query']     = array(
					array(
						'key' 		=> 'total_sales',
						'value' 	=> 0,
						'compare' 	=> '>',
						)
				);
			}
			
			if(!empty($display_product) && $display_product=='instock'){
				$args['meta_query']     = array(
					array(
						'key' 		=> '_stock_status',
						'value' 	=> 'instock',												
					)
				);
			}
			
			if(!empty($display_product) && $display_product=='outofstock'){
				$args['meta_query']     = array(
					array(
						'key' 		=> '_stock_status',
						'value' 	=> 'outofstock',												
					)
				);
			}

		}
		
		if ( !empty($postTag) && $posttype=='post') {
			$args['tax_query'] = array(
			'relation' => 'AND',
				array(
					'taxonomy'         => 'post_tag',
					'terms'            => $postTag,
					'field'            => 'term_id',
					'operator'         => 'IN',
					'include_children' => true,
				),
			);
		}
		
		if( isset($postdata['type']) && $postdata['type'] == 'searchList'){

			$taxo =  '';
			$catArr = [];
			$meta_keyArr = [];
			$meta_keyArr1 = [];
			if(!empty($postdata['seapara'])){
				foreach($postdata['seapara'] as $item => $val ) {
					if($posttype == 'post' && $item != 'searTxt' ){
						if($item == 'category' && !empty($val)){
							$args['category__in'] = $val;
						} 
						if($item == 'post_tag' && !empty($val) ){
							$args['tag__in'] = $val;
						}
					}else if($posttype == 'product' && $item != 'searTxt' ) {

						$cusField = acf_get_field($item);

						if($item == 'product_tag' ){
							$tags_args=array(
								'taxonomy'     => $item,
								'field'        => 'id',
								'terms'        => $val
							);
						}else if($item == 'product_cat' ){
							$category_args = array(
								'taxonomy'     => $item,
								'field'        => 'id',
								'terms'        => $val
							);
						}else if($item == 'price' ){
							$meta_keyArr[] = array(
								'key' => '_price',
								'value' => $val[0],
								'compare' => 'BETWEEN',
								'type' => 'NUMERIC' 
							);
							

						}else if($item == 'tpgb-datepicker1'){
							$args['date_query'] = array(
								array(
									'after'     => $val[0],
									'before'    => $val[1],
									'inclusive' => true,
								),
							);
						}else if(!empty($cusField)){
							
							if( !empty($val) && is_array($val) && $cusField['type'] != 'date_picker' && $cusField['type'] != 'range' ){
								$meta_keyArr1 = [];
								foreach( $val as $key => $metadata){
									$meta_keyArr1[] = array(
										'key'		=> $item,
										'value'		=> $metadata,
										'compare'	=> 'LIKE'
									);
								}
							}else if($cusField['type'] == 'date_picker' || $cusField['type'] == 'range'){
								$meta_keyArr1[] = array(
									'key'		=> $item,
									'value'		=> ($cusField['type'] == 'date_picker' ? $val : $val[0]  ),
									'compare'   => 'BETWEEN',
        							'type'      => ($cusField['type'] == 'date_picker' ? 'DATE' : 'NUMERIC' ),
								);
							}else if($cusField['type'] == 'text'){
								$meta_keyArr1[] = array(
									'key'		=> $item,
									'value'		=> $val,
									'compare'	=> 'LIKE'
								);
							}else{
								$meta_keyArr1[] = array(
									'key'		=> $item,
									'value'		=> $val,
									'compare'	=> 'IN'
								);
							}
							$meta_keyArr[] =$meta_keyArr1;
						}else {
							$attr_tax = array(
								'taxonomy'     => $item,
								'field'        => 'id',
								'terms'        => $val
							);
						}
						
						$args['tax_query'] = [ 'relation' => 'AND', $category_args, $tags_args , $attr_tax ];

					}else if($item != 'searTxt'){
						$args['tax_query'] = [['taxonomy' => $item, 'field' => 'id', 'terms' => $val]];
					}
				} 
				$args['meta_query'] = array('relation' => 'AND', $meta_keyArr);
			}
			$args['s'] = $postdata['seapara']['searTxt'];
			$args['orderby'] = 'relevance';
			$args['posts_per_page'] =  -1;
		}
		$count=($post_load_more*$paged)-$post_load_more+(int)$display_post+1;

        /*custom query id*/
		if( !empty($customQueryId) ){
			
			if(has_filter( $customQueryId )) {
				$args = apply_filters( $customQueryId, $args);
			}
		}
		/*custom query id*/
		
		$col=$tabCol=$moCol='';

		$loop = new WP_Query($args);

		if ( $loop->have_posts() ) {
			$count = 1;
			ob_start();
			while ($loop->have_posts()) {
				$loop->the_post();

				if($posttype == 'product' && $postdata['type'] != 'searchList'){
					if($dyload != 'postListing'){
						include TPGBP_PATH ."includes/ajax-load-post/product-style.php";
					}else{
						include TPGBP_PATH ."includes/ajax-load-post/post-listing.php";
					}
				}else{
					include TPGBP_PATH ."includes/ajax-load-post/post-listing.php";
				}
				$count++;
			}
			$content = ob_get_contents();
			ob_end_clean();
		}
		
		wp_reset_postdata();
		echo $content;
		exit();
		ob_end_clean();
	}
	
	/*
	 * Global Carousel Pro Options
	 */
	public function tpgb_pro_carousel_options($options){
		$pro_option = [
			'dotsBorderColor' => [
				'type' => 'string',
				'default' => '',
				'style' => [
						(object) [
						'condition' => [
							(object) [ 'key' => 'dotsStyle', 'relation' => '==', 'value' => ['style-1','style-2','style-3','style-4','style-6'] ],
							(object) [ 'key' => 'showDots', 'relation' => '==', 'value' => true ]
						],
						'selector' => '{{PLUS_WRAP}}.dots-style-1 ul.splide__pagination li button.splide__pagination__page{-webkit-box-shadow:inset 0 0 0 8px {{dotsBorderColor}};-moz-box-shadow: inset 0 0 0 8px {{dotsBorderColor}};box-shadow: inset 0 0 0 8px {{dotsBorderColor}};} {{PLUS_WRAP}}.dots-style-1 ul.splide__pagination li button.splide__pagination__page.is-active{-webkit-box-shadow:inset 0 0 0 1px {{dotsBorderColor}};-moz-box-shadow: inset 0 0 0 1px {{dotsBorderColor}};box-shadow: inset 0 0 0 1px {{dotsBorderColor}};}{{PLUS_WRAP}}.dots-style-2 ul.splide__pagination li button.splide__pagination__page, {{PLUS_WRAP}}.dots-style-6 .splide__pagination button{border: 1px solid {{dotsBorderColor}};}{{PLUS_WRAP}}.dots-style-3 .splide__pagination li button{-webkit-box-shadow: inset 0 0 0 1px {{dotsBorderColor}};-moz-box-shadow: inset 0 0 0 1px {{dotsBorderColor}};box-shadow: inset 0 0 0 1px {{dotsBorderColor}};}{{PLUS_WRAP}}.dots-style-3 .splide__pagination li button.is-active{-webkit-box-shadow: inset 0 0 0 8px {{dotsBorderColor}};-moz-box-shadow: inset 0 0 0 8px {{dotsBorderColor}};box-shadow: inset 0 0 0 8px {{dotsBorderColor}};}{{PLUS_WRAP}}.dots-style-4 .splide__pagination li button::before{-webkit-box-shadow: inset 0 0 0 1px {{dotsBorderColor}};-moz-box-shadow: inset 0 0 0 1px {{dotsBorderColor}};box-shadow: inset 0 0 0 1px {{dotsBorderColor}};}{{PLUS_WRAP}}.dots-style-1 ul.splide__pagination li button.splide__pagination__page{background: transparent;color: {{dotsBorderColor}};}',
					],
				],
				'scopy' => true,
			],
			'dotsBgColor' => [
				'type' => 'string',
				'default' => '',
				'style' => [
						(object) [
						'condition' => [
							(object) [ 'key' => 'dotsStyle', 'relation' => '==', 'value' => ['style-2','style-3','style-4','style-5','style-7'] ],
							(object) [ 'key' => 'showDots', 'relation' => '==', 'value' => true ]
						],
						'selector' => '{{PLUS_WRAP}}.dots-style-2 ul.splide__pagination li button.splide__pagination__page,{{PLUS_WRAP}}.dots-style-2 ul.splide__pagination li button.splide__pagination__page,{{PLUS_WRAP}}.dots-style-3 .splide__pagination li button,{{PLUS_WRAP}}.dots-style-4 .splide__pagination li button::before,{{PLUS_WRAP}}.dots-style-5 .splide__pagination li button,{{PLUS_WRAP}}.dots-style-7 .splide__pagination li button{background:{{dotsBgColor}};}',
					],
				],
				'scopy' => true,
			],
			'dotsActiveBorderColor' => [
				'type' => 'string',
				'default' => '',
				'style' => [
						(object) [
						'condition' => [
							(object) [ 'key' => 'dotsStyle', 'relation' => '==', 'value' => ['style-2','style-4','style-6'] ],
							(object) [ 'key' => 'showDots', 'relation' => '==', 'value' => true ]
						],
						'selector' => '{{PLUS_WRAP}}.dots-style-2 .splide__pagination li button.is-active::after{border-color: {{dotsActiveBorderColor}};}{{PLUS_WRAP}}.dots-style-4 .splide__pagination li button.is-active{-webkit-box-shadow: inset 0 0 0 1px {{dotsActiveBorderColor}};-moz-box-shadow: inset 0 0 0 1px {{dotsActiveBorderColor}};box-shadow: inset 0 0 0 1px {{dotsActiveBorderColor}};}{{PLUS_WRAP}}.dots-style-6 .splide__pagination button::after{color: {{dotsActiveBorderColor}};}',
					],
				],
				'scopy' => true,
			],
			'dotsActiveBgColor' => [
				'type' => 'string',
				'default' => '',
				'style' => [
						(object) [
						'condition' => [ 
							(object) [ 'key' => 'dotsStyle', 'relation' => '==', 'value' => ['style-2','style-4','style-5','style-7'] ],
							(object) [ 'key' => 'showDots', 'relation' => '==', 'value' => true ]
						],
						'selector' => '{{PLUS_WRAP}}.dots-style-2 .splide__pagination li button.is-active::after,{{PLUS_WRAP}}.dots-style-4 .splide__pagination li button.is-active::before,{{PLUS_WRAP}}.dots-style-5 .splide__pagination li:hover button,{{PLUS_WRAP}}.dots-style-5 .splide__pagination li button.is-active,{{PLUS_WRAP}}.dots-style-7 .splide__pagination li button.is-active{background: {{dotsActiveBgColor}};}',
					],
				],
				'scopy' => true,
			],
			
			'arrowsBgColor' => [
				'type' => 'string',
				'default' => '',
				'style' => [
						(object) [
						'condition' => [
							(object) [ 'key' => 'arrowsStyle', 'relation' => '==', 'value' => ['style-1','style-3','style-4','style-6'] ],
							(object) [ 'key' => 'showArrows', 'relation' => '==', 'value' => true ]
						],
						'selector' => '{{PLUS_WRAP}} .splide__arrows.style-1 .splide__arrow.style-1,{{PLUS_WRAP}} .splide__arrows.style-3 .splide__arrow.style-3 .icon-wrap,{{PLUS_WRAP}} .splide__arrows.style-6 .splide__arrow.style-6:before{background:{{arrowsBgColor}};}{{PLUS_WRAP}} .splide__arrows.style-4 .splide__arrow.style-4 .icon-wrap{border-color:{{arrowsBgColor}}}',
					],
				],
				'scopy' => true,
			],
			'arrowsIconColor' => [
				'type' => 'string',
				'default' => '',
				'style' => [
						(object) [
						'condition' => [
							(object) [ 'key' => 'showArrows', 'relation' => '==', 'value' => true ]
						],
						'selector' => '{{PLUS_WRAP}} .splide__arrows.style-1 .splide__arrow.style-1:before,{{PLUS_WRAP}} .splide__arrows.style-3 .splide__arrow.style-3 .icon-wrap,{{PLUS_WRAP}} .splide__arrows.style-4 .splide__arrow.style-4 .icon-wrap,{{PLUS_WRAP}} .splide__arrows.style-6 .splide__arrow.style-6 .icon-wrap svg{color:{{arrowsIconColor}};}{{PLUS_WRAP}} .splide__arrows.style-2 .splide__arrow.style-2 .icon-wrap:before,{{PLUS_WRAP}} .splide__arrows.style-2 .splide__arrow.style-2 .icon-wrap:after,{{PLUS_WRAP}} .splide__arrows.style-5 .splide__arrow.style-5 .icon-wrap:before,{{PLUS_WRAP}} .splide__arrows.style-5 .splide__arrow.style-5 .icon-wrap:after{background:{{arrowsIconColor}};}',
					],
				],
				'scopy' => true,
			],
			'arrowsHoverBgColor' => [
				'type' => 'string',
				'default' => '',
				'style' => [
						(object) [
						'condition' => [
							(object) [ 'key' => 'arrowsStyle', 'relation' => '==', 'value' => ['style-1','style-2','style-3','style-4'] ],
							(object) [ 'key' => 'showArrows', 'relation' => '==', 'value' => true ]
						],
						'selector' => '{{PLUS_WRAP}} .splide__arrows.style-1 .splide__arrow.style-1:hover,{{PLUS_WRAP}} .splide__arrows.style-2 .splide__arrow.style-2:hover:before,{{PLUS_WRAP}} .splide__arrows.style-3 .splide__arrow.style-3:hover .icon-wrap{background:{{arrowsHoverBgColor}};}{{PLUS_WRAP}} .splide__arrows.style-4 .splide__arrow.style-4:hover:before,{{PLUS_WRAP}} .splide__arrows.style-4 .splide__arrow.style-4:hover .icon-wrap{border-color:{{arrowsHoverBgColor}};}',
					],
				],
				'scopy' => true,
			],
			'arrowsHoverIconColor' => [
				'type' => 'string',
				'default' => '',
				'style' => [
						(object) [
						'condition' => [
							(object) [ 'key' => 'showArrows', 'relation' => '==', 'value' => true ]
						],
						'selector' => '{{PLUS_WRAP}} .splide__arrows.style-1 .splide__arrow.style-1:hover:before,{{PLUS_WRAP}} .splide__arrows.style-3 .splide__arrow.style-3:hover .icon-wrap,{{PLUS_WRAP}} .splide__arrows.style-4 .splide__arrow.style-4:hover .icon-wrap,{{PLUS_WRAP}} .splide__arrows.style-6 .splide__arrow.style-6:hover .icon-wrap svg{color:{{arrowsHoverIconColor}};}{{PLUS_WRAP}} .splide__arrows.style-2 .splide__arrow.style-2:hover .icon-wrap:before,{{PLUS_WRAP}} .splide__arrows.style-2 .splide__arrow.style-2:hover .icon-wrap:after,{{PLUS_WRAP}} .splide__arrows.style-5 .splide__arrow.style-5:hover .icon-wrap:before,{{PLUS_WRAP}} .splide__arrows.style-5 .splide__arrow.style-5:hover .icon-wrap:after{background:{{arrowsHoverIconColor}};}',
					],
				],
				'scopy' => true,
			],
			
			'centerPadding' => [
				'type' => 'object',
				'default' => (object)[ 'md' => 0,'sm' => 0,'xs' => 0 ],
				'scopy' => true,
			],
			'centerSlideEffect' => [
				'type' => 'string',
				'default' => 'none',
				'scopy' => true,
			],
			'centerslideScale' => [
				'type' => 'string',
				'default' => 1,
				'style' => [
						(object) [
						'condition' => [
							(object) [ 'key' => 'centerMode', 'relation' => '==', 'value' => true ],
							(object) [ 'key' => 'centerSlideEffect', 'relation' => '==', 'value' => 'scale' ]
						],
						'selector' => '{{PLUS_WRAP}} .splide__list .splide__slide.is-active > div,{{PLUS_WRAP}} .splide__list .splide__slide.is-active > a{-webkit-transform: scale({{centerslideScale}});-moz-transform: scale({{centerslideScale}});-ms-transform: scale({{centerslideScale}});-o-transform: scale({{centerslideScale}});transform: scale({{centerslideScale}});}{{PLUS_WRAP}} .splide__list .splide__slide > div,{{PLUS_WRAP}} .splide__list .splide__slide.is-active > a{transition: .3s all linear;}',
					],
				],
				'scopy' => true,
			],
			'normalslideScale' => [
				'type' => 'string',
				'default' => 1,
				'style' => [
						(object) [
						'condition' => [
							(object) [ 'key' => 'centerMode', 'relation' => '==', 'value' => true ],
							(object) [ 'key' => 'centerSlideEffect', 'relation' => '==', 'value' => 'scale' ]
						],
						'selector' => '{{PLUS_WRAP}} .splide__list .splide__slide  > div,{{PLUS_WRAP}} .splide__list .splide__slide  > a{-webkit-transform: scale({{normalslideScale}});-moz-transform: scale({{normalslideScale}});-ms-transform: scale({{normalslideScale}});-o-transform: scale({{normalslideScale}});transform: scale({{normalslideScale}});}{{PLUS_WRAP}} .splide__list .splide__slide > div,{{PLUS_WRAP}} .splide__list .splide__slide  > a{transition: .3s all linear;}',
					],
				],
				'scopy' => true,
			],
			'slideOpacity' => [
				'type' => 'object',
				'default' => (object)[ 'md' => 1,'sm' => 1,'xs' => 1 ],
				'style' => [
						(object) [
						'condition' => [
							(object) [ 'key' => 'centerMode', 'relation' => '==', 'value' => true ],
						],
						'selector' => '{{PLUS_WRAP}} .splide__list .splide__slide:not(.is-active) > div,{{PLUS_WRAP}} .splide__list .splide__slide:not(.is-active) > a{opacity:{{slideOpacity}};}{{PLUS_WRAP}} .splide__list .splide__slide > div,{{PLUS_WRAP}} .splide__list .splide__slide > a{transition: .3s all linear;}',
					],
				],
				'scopy' => true,
			],
			'slideBoxShadow' => [
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
						'condition' => [
							(object) [ 'key' => 'centerMode', 'relation' => '==', 'value' => true ],
							(object) [ 'key' => 'centerSlideEffect', 'relation' => '==', 'value' => 'shadow' ]
						],
						'selector' => '{{PLUS_WRAP}} .splide__list .splide__slide.is-active > div,{{PLUS_WRAP}} .splide__list .splide__slide.is-active > a',
					],
				],
				'scopy' => true,
			],
			'slideheightRatio' => [
				'type' => 'string',
				'default' => '0.5',
				'scopy' => true,
			],
			'tabslideRatio' => [
				'type' => 'string',
				'default' => '',
				'scopy' => true,
			],
			'mobslideRatio' => [
				'type' => 'string',
				'default' => '',
				'scopy' => true,
			],
			'trimSpace' => [
				'type' => 'boolean',
				'default' => false,
				'scopy' => true,
			],
		];

		return array_merge($options, $pro_option);
	}
	
	/*
	 * Check Html Tag
	 * @since 1.2.1
	 */
	public static function tpgb_html_tag_check(){
		return [ 'div',
			'h1',
			'h2',
			'h3',
			'h4',
			'h5',
			'h6',
			'a',
			'span',
			'p',
			'header',
			'footer',
			'article',
			'aside',
			'main',
			'nav',
			'section',
			'tr',
			'th',
			'td'
		];
	}
	
	/*
	 * Validate Html Tag
	 * @since 1.2.1
	 */
	public static function validate_html_tag( $check_tag ) {
		return in_array( strtolower( $check_tag ), self::tpgb_html_tag_check() ) ? $check_tag : 'div';
	}
	
	/*
	* DECRIPT
	* @since 1.2.1
	*/
	public static function tpgb_check_decrypt_key($key){
		$decrypted = self::tpgb_simple_decrypt( $key, 'dy' );
		return $decrypted;
	}
	
	/*
	* ENCRYPT
	* @since 1.2.1
	*/
	public static function tpgb_simple_decrypt($string, $action = 'dy'){
		// you may change these values to your own
		$tppk=get_option( 'tpgb_activate' );
		$secret_key = ( isset($tppk['tpgb_activate_key']) && !empty($tppk['tpgb_activate_key']) ) ? $tppk['tpgb_activate_key'] : 'PO$_key';
		$secret_iv = 'PO$_iv';

		$output = false;
		$encrypt_method = "AES-128-CBC";
		$key = hash( 'sha256', $secret_key );
		$iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

		if( $action == 'ey' ) {
			$output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
		}
		else if( $action == 'dy' ){
			$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
		}

		return $output;
	}
	
	/*
	 * Dynamic Fields Content Check Value
	 * @since 1.3.0
	 */
    public static function tpgb_dynamic_val($block_content = '', $block=[]){
		
		$data_match = [ '/<span data-tpgb-dynamic=(.*?)>(.*?)<\/span>/' , '/<span data-tpgb-dynamic=(.*?)>([^$]+)?<\/span>/' ];

		foreach($data_match as $datamatch ){
			if(!empty($block_content) && preg_match_all( $datamatch , $block_content, $matches )){
				$dynamicCnt = $matches[0];

                foreach ($dynamicCnt as $dynamic_entry) {
                    // Flexible regex to handle both single and double quotes
                    if (preg_match('/data-tpgb-dynamic=(["\'])([^"\']+)\1/', $dynamic_entry, $route)) {
        
                        // Decode HTML entities first - route[2] now contains the JSON content
                        $jsonString = html_entity_decode($route[2], ENT_QUOTES | ENT_HTML5);

                        // Parse JSON directly (no need for array wrapping)
                        $array = json_decode($jsonString, true);

                        if ($array !== null) {
                            $dynamicpara['field'] = $array;
                            $dynamicpara['id'] = get_the_ID();

                            // Check if this is one of the supported block types
                            $supported_blocks = [
                                'tpgb/tp-heading',
                                'tpgb/tp-button-core', 
                                'tpgb/tp-advanced-buttons',
                                'tpgb/tp-button',
                                'tpgb/tp-blockquote',
                                'tpgb/tp-cta-banner',
                                'tpgb/tp-expand',
                                'tpgb/tp-heading-animation',
                                'tpgb/tp-infobox',
                                'tpgb/tp-pricing-list',
                                'tpgb/tp-pricing-table',
                                'tpgb/tp-messagebox',
                                'tpgb/tp-heading-title',
                                'tpgb/tp-pro-paragraph',
                                'tpgb/tp-container',
                                'tpgb/tp-accordion',
                            ];

                            if (isset($block['blockName']) && in_array($block['blockName'], $supported_blocks, true)) {
                                global $repeater_index;
                                $rep_Index = $repeater_index ? $repeater_index : 0;
                            
                                if ($block['blockName'] !== 'tpgb/tp-infobox') {
                                    $block_content = Tpgbp_Pro_Blocks_Helper::nxt_replace_dynamic_url($block_content, $block);
                                }
                            
                                // Special handling for tp-expand block
                                if ($block['blockName'] === 'tpgb/tp-expand') {
                                    $dynamicField = $array['dynamicField'] ?? '';
                                    $block_content = self::nxt_process_dynamic_repeater_field( $dynamicField, $rep_Index, $dynamic_entry, $block_content );

                                }else if($block['blockName'] === 'tpgb/tp-accordion'){
                                    
                                    $dynamicField = $array['dynamicField'] ?? '';

                                    if ( $rep_Index !== 0 ) {
                                        $block_content = preg_replace_callback(
                                            '/class=(["\'])(.*?)\1/',
                                            function ( $matches ) {
                                                $classes = preg_replace( '/\bactive\b/', '', $matches[2] );
                                                $classes = preg_replace( '/\s+/', ' ', trim( $classes ) );
                                                return 'class=' . $matches[1] . $classes . $matches[1];
                                            },
                                            $block_content
                                        );
                                    }
                                    $block_content = self::nxt_process_dynamic_repeater_field( $dynamicField, $rep_Index, $dynamic_entry, $block_content );

                                } else {
                                    // Flexible regex for span matching - handles both quote types
                                    if (!empty($block_content) && preg_match_all('/<span[^>]*data-tpgb-dynamic=(["\'])(.*?)\1[^>]*><\/span>/', $block_content, $matches, PREG_SET_ORDER)) {
                                        foreach ($matches as $match) {
                                            $json_encoded = $match[2];

                                            // Decode HTML entities
                                            $spanJsonString = html_entity_decode($json_encoded, ENT_QUOTES | ENT_HTML5);
                                            $dataArray = json_decode($spanJsonString, true);

                                            if (json_last_error() === JSON_ERROR_NONE && isset($dataArray['dynamicField']) && !empty($dataArray['dynamicField'])) {
                                                $dynamicField = $dataArray['dynamicField'];

                                                if (strpos($dynamicField, '|') !== false) {
                                                    $dynamicFieldParts = explode('|', $dynamicField);

                                                    if (!empty($dynamicFieldParts) && (count($dynamicFieldParts) === 5 || count($dynamicFieldParts) === 7)) {
                                                        $fieldName = $dynamicFieldParts[1] ?? 'Unknown Field';
                                                        $repFunction = apply_filters('tp_get_repeater_data', $dynamicFieldParts);
                                                        $replacementValue = '';

                                                        if (!is_wp_error($repFunction) && is_array($repFunction) && isset($repFunction['repeater_data'][$rep_Index][$fieldName])) {
                                                            $replacementValue = $repFunction['repeater_data'][$rep_Index][$fieldName];
                                                        }

                                                        // Replace the exact span match with the value  
                                                        $block_content = str_replace($match[0], esc_html($replacementValue), $block_content);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        
                        // Apply dynamic content filter
                            if (has_filter('tpgb_get_dynamic_content')) {
                                $dynamicVal = apply_filters('tpgb_get_dynamic_content', $dynamicpara);
                            
                                if (is_array($dynamicVal)) {
                                    if (!empty($dynamicVal['url']) && isset($dynamicVal['url'])) {
                                        $block_content = str_replace($dynamic_entry, $dynamicVal['url'], $block_content);
                                    }
                                } else {
                                    if (!empty($dynamicpara['field']) && 
                                        isset($dynamicpara['field']['dynamicField']) && 
                                        $dynamicpara['field']['dynamicField'] == 'tpgb-product-price-tag') {
                                        $block_content = $dynamicVal;
                                    } else {
                                        $block_content = str_replace($dynamic_entry, $dynamicVal, $block_content);
                                    }
                                }
                            }
                        }
                    }
                }
			}else if( isset($block['blockName']) && $block['blockName'] === 'tpgb/tp-button-core'){
                $block_content = Tpgbp_Pro_Blocks_Helper::nxt_replace_dynamic_url($block_content, $block);
            }
		}
		if(!empty($block_content) && preg_match_all('/(?:[\'"]http(.*?)[\'"]).*?>/', $block_content, $matche)){
			if( !empty($matche) && isset($matche[1]) ){
				foreach ( $matche[1] as $dyncnt ) {
					if(preg_match_all('/tpgb-dynamicurl=(.*?)\!#/', $dyncnt, $mat)){
						if( !empty($mat) && isset($mat[1]) ){

                            if ( isset($block['attrs'][$mat[1][0]]) && isset($block['attrs'][$mat[1][0]]['dynamic']) && isset($block['attrs'][$mat[1][0]]['dynamic']['dynamicUrl']) && strpos($block['attrs'][$mat[1][0]]['dynamic']['dynamicUrl'], '|') !== false) {
                                $dynamicFieldParts = explode('|', $block['attrs'][$mat[1][0]]['dynamic']['dynamicUrl']);
                            } else {
                                $dynamicFieldParts = explode('|', $mat[1][0]);
                            }
                            global $repeater_index; 
                            
                            if(count($dynamicFieldParts) === 5 || count($dynamicFieldParts) === 7) {
                                $fieldName = $dynamicFieldParts[1] ?? 'Unknown Field';
                                $repFunction = apply_filters('tp_get_repeater_data', $dynamicFieldParts);
                                
                                if (!is_wp_error($repFunction) && is_array($repFunction) && isset($repFunction['repeater_data'][$repeater_index][$fieldName])) {
                                    $value = $repFunction['repeater_data'][$repeater_index][$fieldName];
                                    $block_content = str_replace('http'.$dyncnt, $value, $block_content );
                                }
                            }
                            
							foreach ( $mat[1] as $dynaurl ) {
								$dynamicpara = [];
								if( !empty($block['attrs'][$dynaurl]) && !empty($block['attrs'][$dynaurl]['dynamic'])){
									$dynamicpara['field'] =  $block['attrs'][$dynaurl]['dynamic'];
									$dynamicpara['id'] = get_the_ID();
									$dynamicVal = apply_filters('tpgb_get_dynamic_content', $dynamicpara);
									if(is_array($dynamicVal) && isset($dynamicpara['field']['type']) && $dynamicpara['field']['type'] == 'image' ){
										if(!empty($dynamicVal['url']) && isset( $dynamicVal['url'])){
											$block_content = str_replace( 'http'.$dyncnt, $dynamicVal['url'] , $block_content );
										}
									}else if(!empty($dynamicVal)){
										$block_content = str_replace( 'http'.$dyncnt, $dynamicVal , $block_content );
									}
								}
								
							}
						}
					}
				}
			}
		}

		return $block_content;
	}
	
	/*
	 * Dynamic Fields Repeater Check Value
	 * @since 1.3.0
	 */
	public static function tpgb_dynamic_repeat_url($options = []){
		$value = '';

		if(!empty($options) && !empty($options['dynamic'])){

            if (strpos($options['dynamic']['dynamicUrl'], '|') !== false) {
				global $repeater_index;
				$dynamicFieldParts = explode('|', $options['dynamic']['dynamicUrl']);
			
				if (count($dynamicFieldParts) === 5) {
					$fieldName = $dynamicFieldParts[1] ?? 'Unknown Field';
					$repFunction = apply_filters('tp_get_repeater_data', $dynamicFieldParts);
					
					if (is_wp_error($repFunction)) {
                        // Handle the error as needed, e.g., log or skip
                        $value = '';
                    } elseif (!empty($repFunction['repeater_data'][$repeater_index][$fieldName])) {
						$value = $repFunction['repeater_data'][$repeater_index][$fieldName];
					}
				}
			}

			$dynamicpara = [];
			$dynamicpara['field'] =  $options['dynamic'];
			$dynamicpara['id'] = get_the_ID();
			$dynamicVal = apply_filters('tpgb_get_dynamic_content', $dynamicpara);

			if(is_array($dynamicVal) && isset($dynamicpara['field']['type']) && $dynamicpara['field']['type'] == 'image' ){
				if(!empty($dynamicVal['url']) && isset( $dynamicVal['url'])){
					$value = $dynamicVal['url'];
				}else{
					$value = $dynamicVal;
				}
			}else if(!empty($dynamicVal)){
				$value = $dynamicVal;
			}
			
		}
		return $value;
	}
	
	/*
	 * Social Review Get API
	 * @since 1.3.0
	 */
	public function tpgb_socialreview_Gettoken() {
		$result = [];
		check_ajax_referer('tpgb-addons', 'tpgb_nonce');
		$get_json = wp_remote_get("https://theplusaddons.com/wp-json/template_socialreview_api/v2/socialreviewAPI?time=".time());
		if ( is_wp_error( $get_json ) ) {
			wp_send_json_error( array( 'messages' => 'something wrong in API' ) );
		}else{
			$URL_StatusCode = wp_remote_retrieve_response_code($get_json);
			if($URL_StatusCode == 200){
				$getdata = wp_remote_retrieve_body($get_json);
				$result['SocialReview'] = json_decode($getdata, true);
				$result['success'] = 1;
				wp_send_json($result);
			}
		}
		wp_send_json_error( array( 'messages' => 'something wrong in API' ) );
	}
	
	/*
	 * Social Review Load
	 * @since 1.3.0
	 */
	public function tpgb_reviews_load(){
		ob_start();
		$result = [];
		$load_attr = isset($_POST["loadattr"]) ? wp_unslash( $_POST["loadattr"] ) : '';
		if(empty($load_attr)){
			ob_get_contents();
				exit;
			ob_end_clean();
		}
		$load_attr = self::tpgb_check_decrypt_key($load_attr);
		$load_attr = json_decode($load_attr,true);
		if(!is_array($load_attr)){
			ob_get_contents();
				exit;
			ob_end_clean();
		}
		
		$nonce = (isset($load_attr["tpgb_nonce"])) ? wp_unslash( $load_attr["tpgb_nonce"] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'theplus-addons-block' ) ){
			die ( 'Security checked!');
		}
		
		$UserFooter = (!empty($load_attr['s2Layout']) ? wp_unslash($load_attr['s2Layout']) : 'layout-1' );
		$load_class = isset( $load_attr["load_class"] ) ? sanitize_text_field( wp_unslash($load_attr["load_class"]) ) : '';
		$review_id = isset( $load_attr["review_id"] ) ? sanitize_text_field( wp_unslash($load_attr["review_id"]) ) : '';
		$style = isset( $load_attr["style"] ) ? sanitize_text_field( wp_unslash($load_attr["style"]) ) : '';
		$layout = isset( $load_attr["layout"] ) ? sanitize_text_field( wp_unslash($load_attr["layout"]) ) : '';
		
		$desktop_column = (isset( $load_attr["desktop_column"] )  && intval($load_attr["desktop_column"]) ) ? wp_unslash($load_attr["desktop_column"]) : '';
		$tablet_column = (isset( $load_attr["tablet_column"] )  && intval($load_attr["tablet_column"]) ) ? wp_unslash($load_attr["tablet_column"]) : '';
		$mobile_column = (isset( $load_attr["mobile_column"] )  && intval($load_attr["mobile_column"]) ) ? wp_unslash($load_attr["mobile_column"]) : '';
		$DesktopClass = isset( $load_attr["DesktopClass"] ) ? sanitize_text_field( wp_unslash($load_attr["DesktopClass"]) ) : '';
		$TabletClass = isset( $load_attr["TabletClass"] ) ? sanitize_text_field( wp_unslash($load_attr["TabletClass"]) ) : '';
		$MobileClass = isset( $load_attr["MobileClass"] ) ? sanitize_text_field( wp_unslash($load_attr["MobileClass"]) ) : '';

		$CategoryWF = isset( $load_attr["categorytext"] ) ? sanitize_text_field( wp_unslash($load_attr["categorytext"]) ) : '';
		$FeedId = (!empty($_POST["FeedId"]) && isset( $load_attr["FeedId"] ) ) ? wp_unslash( preg_split("/\,/", $load_attr["FeedId"]) ) : '';
		
		$txtLimt = isset( $load_attr["TextLimit"] ) ? wp_unslash($load_attr["TextLimit"]) : '';
		$TextCount = isset( $load_attr["TextCount"] ) ? wp_unslash($load_attr["TextCount"]) : '';
		$TextType = isset( $load_attr["TextType"] ) ? wp_unslash($load_attr["TextType"]) : '';
		$TextMore = isset( $load_attr["TextMore"] ) ? wp_unslash($load_attr["TextMore"]) : '';
		$TextDots = isset( $load_attr["TextDots"] ) ? wp_unslash($load_attr["TextDots"]) : '';
		
		$postview = (isset( $load_attr["postview"] )  && intval($load_attr["postview"]) ) ? wp_unslash($load_attr["postview"]) : '';
		$display = (isset( $load_attr["display"] )  && intval($load_attr["display"]) ) ? wp_unslash($load_attr["display"]) : '';
		$disSocialIcon = (isset( $load_attr["disSocialIcon"] ) ) ? wp_unslash($load_attr["disSocialIcon"]) : false;
		$disProfileIcon = (isset( $load_attr["disProfileIcon"] ) ) ? wp_unslash($load_attr["disProfileIcon"]) : false;
		
		$view = isset($_POST["view"]) ? intval($_POST["view"]) : '';	
		$loadFview = isset($_POST["loadFview"]) ? intval($_POST["loadFview"]) : '';
			
		$FinalData = get_transient("SR-LoadMore-".$review_id);
		$FinalDataa = array_slice($FinalData, $view, $loadFview);

		$desktop_class=$tablet_class=$mobile_class='';
		if($layout != 'carousel'){
			$desktop_class .= ' tpgb-col-'.esc_attr($mobile_column);
			$desktop_class .= ' tpgb-col-lg-'.esc_attr($desktop_column);
			$tablet_class = ' tpgb-col-md-'.esc_attr($tablet_column);
			$mobile_class = ' tpgb-col-sm-'.esc_attr($mobile_column);
		}

		foreach ($FinalDataa as $F_index => $Review) {
			$RKey = !empty($Review['RKey']) ? $Review['RKey'] : '';
			$RIndex = !empty($Review['Reviews_Index']) ? $Review['Reviews_Index'] : '';
			$PostId = !empty($Review['PostId']) ? $Review['PostId'] : '';
			$Type = !empty($Review['Type']) ? $Review['Type'] : '';
			$Time = !empty($Review['CreatedTime']) ? $Review['CreatedTime'] : '';
			$UName = !empty($Review['UserName']) ? $Review['UserName'] : '';
			$UImage = !empty($Review['UserImage']) ? $Review['UserImage'] : '';
			$ULink = !empty($Review['UserLink']) ? $Review['UserLink'] : '';
			$PageLink = !empty($Review['PageLink']) ? $Review['PageLink'] : '';
			$Massage = !empty($Review['Massage']) ? $Review['Massage'] : '';
			$Icon = !empty($Review['Icon']) ? $Review['Icon'] : 'fas fa-star';
			$Logo = !empty($Review['Logo']) ? $Review['Logo'] : '';
			$rating = !empty($Review['rating']) ? $Review['rating'] : '';
			$CategoryText = !empty($Review['FilterCategory']) ? $Review['FilterCategory'] : '';
			$ReviewClass = !empty($Review['selectType']) ? ' '.$Review['selectType'] : '';
			$ErrClass = !empty($Review['ErrorClass']) ? $Review['ErrorClass'] : '';
			$PlatformName = !empty($Review['selectType']) ? ucwords(str_replace('custom', '', $Review['selectType'])) : '';	

			$category_filter=$loop_category='';
			if( !empty($CategoryWF) && !empty($CategoryText)  && $layout != 'carousel' ){
				$loop_category = explode(',', $CategoryText);
				foreach( $loop_category as $category ) {
					$category = preg_replace('/[^A-Za-z0-9-]+/', '-', $category);
					$category_filter .=' '.esc_attr($category).' ';
				}
			}
			if(!empty($style)){
			  include TPGBP_INCLUDES_URL. 'social-reviews/'.sanitize_file_name('social-review-'.$style.'.php');
			}
		}
		
		$GridData = ob_get_clean();

		$result['success'] = 1;
		$result['TotalReview'] = isset($load_attr['TotalReview']) ? wp_unslash($load_attr['TotalReview']) : '';
		$result['FilterStyle'] = isset($load_attr['FilterStyle']) ? wp_unslash($load_attr['FilterStyle']) : '';
		$result['allposttext'] = isset($load_attr['allposttext']) ? wp_unslash($load_attr['allposttext']) : '';
		$result['HTMLContent'] = $GridData;
		
		return wp_send_json($result);
	}
	
	/* 
	 * Social Feed Load More & Lazy Load
	 * @since 1.3.0
	 */
	public function tpgb_social_feed_load(){
		ob_start();
		$result = [];
		$load_attr = isset($_POST["loadattr"]) ? wp_unslash( $_POST["loadattr"] ) : '';
		
		if(empty($load_attr)){
			ob_get_contents();
			exit;
			ob_end_clean();
		}

		$load_attr = self::tpgb_check_decrypt_key($load_attr);
		$load_attr = json_decode($load_attr, true);
		if(!is_array($load_attr)){
			ob_get_contents();
			exit;
			ob_end_clean();
		}
		
		$nonce = (isset($load_attr["tpgb_nonce"])) ? wp_unslash( $load_attr["tpgb_nonce"] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'theplus-addons-block' ) ){
			die ( 'Security checked!');
		}
		$load_class = isset( $load_attr["load_class"] ) ? sanitize_text_field( wp_unslash($load_attr["load_class"]) ) : '';
		$feed_id = isset( $load_attr["feed_id"] ) ? sanitize_text_field( wp_unslash($load_attr["feed_id"]) ) : '';
		$style = isset( $load_attr["style"] ) ? sanitize_text_field( wp_unslash($load_attr["style"]) ) : 'style-1';
		$layout = isset( $load_attr["layout"] ) ? sanitize_text_field( wp_unslash($load_attr["layout"]) ) : 'grid';

		$desktop_column = (isset( $load_attr["desktop_column"] )  && intval($load_attr["desktop_column"]) ) ? wp_unslash($load_attr["desktop_column"]) : '';
		$tablet_column = (isset( $load_attr["tablet_column"] )  && intval($load_attr["tablet_column"]) ) ? wp_unslash($load_attr["tablet_column"]) : '';
		$mobile_column = (isset( $load_attr["mobile_column"] )  && intval($load_attr["mobile_column"]) ) ? wp_unslash($load_attr["mobile_column"]) : '';

		$postview = (isset( $load_attr["postview"] ) && intval($load_attr["postview"]) ) ? wp_unslash($load_attr["postview"]) : '';
		$display = (isset( $load_attr["display"] ) && intval($load_attr["display"]) ) ? wp_unslash($load_attr["display"]) : '';

		$txtLimt = isset( $load_attr["TextLimit"] ) ? wp_unslash($load_attr["TextLimit"]) : '';
		$TextCount = isset( $load_attr["TextCount"] ) ? wp_unslash($load_attr["TextCount"]) : '';
		$TextType = isset( $load_attr["TextType"] ) ? wp_unslash($load_attr["TextType"]) : '';
		$TextMore = isset( $load_attr["TextMore"] ) ? wp_unslash($load_attr["TextMore"]) : '';
		$TextDots = isset( $load_attr["TextDots"] ) ? wp_unslash($load_attr["TextDots"]) : '';
		$FancyStyle = isset( $load_attr["FancyStyle"] ) ? wp_unslash($load_attr["FancyStyle"]) : 'default';
		$DescripBTM = isset( $load_attr["DescripBTM"] ) ? wp_unslash($load_attr["DescripBTM"]) : '';
		$MediaFilter = isset( $load_attr["MediaFilter"] ) ? wp_unslash($load_attr["MediaFilter"]) : 'default';
		$CategoryWF = isset( $load_attr["categorytext"] ) ? wp_unslash($load_attr["categorytext"]) : '';
		$TotalPost = (isset( $load_attr["TotalPost"] )  && intval($load_attr["TotalPost"]) ) ? wp_unslash($load_attr["TotalPost"]) : '';
		$PopupOption = isset( $load_attr["PopupOption"] ) ? wp_unslash($load_attr["PopupOption"]) : 'Donothing';
		
		$block_id = $load_class;
		
		$FinalData = get_transient("SF-LoadMore-".$feed_id);
		$view = isset($_POST["view"]) ? intval($_POST["view"]) : [];	
		$loadFview = isset($_POST["loadFview"]) ? intval($_POST["loadFview"]) : [];

		$FancyBoxJS='';
		if($PopupOption == 'OnFancyBox'){
			$FancyBoxJS = 'data-fancybox="'.esc_attr($load_class).'"';
		}
		
		$desktop_class='';
		if($layout != 'carousel'){
			$desktop_class .= 'tpgb-col-lg-'.esc_attr($desktop_column);
			$desktop_class .= ' tpgb-col-md-'.esc_attr($tablet_column);
			$desktop_class .= ' tpgb-col-sm-'.esc_attr($mobile_column);
			$desktop_class .= ' tpgb-col-'.esc_attr($mobile_column);
		}	
		$FinalDataa=[];
		if( is_array($FinalData) ){
			$FinalDataa = array_slice($FinalData, $view , $loadFview);
		}
		if(!empty($FinalDataa)){
			foreach ($FinalDataa as $F_index => $loadData) {
				$PopupTarget=$PopupLink='';
				$uniqEach = uniqid();
				$PopupSylNum = "{$block_id}-{$F_index}-{$uniqEach}";
				$RKey = !empty($loadData['RKey']) ? $loadData['RKey'] : '';
				$PostId = !empty($loadData['PostId']) ? $loadData['PostId'] : '';
				$selectFeed = !empty($loadData['selectFeed']) ? $loadData['selectFeed'] : '';
				$Massage = !empty($loadData['Massage']) ? $loadData['Massage'] : '';
				$Description = !empty($loadData['Description']) ? $loadData['Description'] : '';
				$Type = !empty($loadData['Type']) ? $loadData['Type'] : '';
				$PostLink = !empty($loadData['PostLink']) ? $loadData['PostLink'] : '';
				$CreatedTime = !empty($loadData['CreatedTime']) ? $loadData['CreatedTime'] : '';
				$PostImage = !empty($loadData['PostImage']) ? $loadData['PostImage'] : '';
				$UserName = !empty($loadData['UserName']) ? $loadData['UserName'] : '';
				$UserImage = !empty($loadData['UserImage']) ? $loadData['UserImage'] : '';
				$UserLink = !empty($loadData['UserLink']) ? $loadData['UserLink'] : '';
				$socialIcon = !empty($loadData['socialIcon']) ? $loadData['socialIcon'] : '';
				$CategoryText = !empty($loadData['FilterCategory']) ? $loadData['FilterCategory'] : '';
				$ErrorClass = !empty($loadData['ErrorClass']) ? $loadData['ErrorClass'] : '';
				$EmbedURL = !empty($loadData['Embed']) ? $loadData['Embed'] : '';
				$EmbedType = !empty($loadData['EmbedType']) ? $loadData['EmbedType'] : '';
				
				$category_filter = $loop_category = '';
				if( !empty($CategoryText)  && $layout !='carousel' ){
					$loop_category = explode(',', $CategoryText);
					foreach( $loop_category as $category ) {
						$category = preg_replace('/[^A-Za-z0-9-]+/', '-', $category);
						$category_filter .=' '.esc_attr($category).' ';
					}
				}

				if($selectFeed == 'Facebook'){
					$Fblikes = !empty($loadData['FbLikes']) ? $loadData['FbLikes'] : 0;
					$comment = !empty($loadData['comment']) ? $loadData['comment'] : 0;
					$share = !empty($loadData['share']) ? $loadData['share'] : 0;
					$likeImg = TPGB_ASSETS_URL.'assets/images/social-feed/like.png';
					$ReactionImg = TPGB_ASSETS_URL.'assets/images/social-feed/love.png';
				}
				if($selectFeed == 'Twitter'){
					$TwRT = (!empty($loadData['TWRetweet'])) ? $loadData['TWRetweet'] : 0;
					$TWLike = (!empty($loadData['TWLike'])) ? $loadData['TWLike'] : 0;
					
					$TwReplyURL = (!empty($loadData['TwReplyURL'])) ? $loadData['TwReplyURL'] : '';
					$TwRetweetURL = (!empty($loadData['TwRetweetURL'])) ? $loadData['TwRetweetURL'] : '';
					$TwlikeURL = (!empty($loadData['TwlikeURL'])) ? $loadData['TwlikeURL'] : '';
					$TwtweetURL = (!empty($loadData['TwtweetURL'])) ? $loadData['TwtweetURL'] : '';
				}
				if($selectFeed == 'Vimeo'){
					$share = (!empty($loadData['share'])) ? $loadData['share'] : 0;
					$likes = (!empty($loadData['likes'])) ? $loadData['likes'] : 0;
					$comment = (!empty($loadData['comment'])) ? $loadData['comment'] : 0;
				}
				if($selectFeed == 'Youtube'){
					$view = (!empty($loadData['view'])) ? $loadData['view'] : 0;
					$likes = (!empty($loadData['likes'])) ? $loadData['likes'] : 0;
					$comment = (!empty($loadData['comment'])) ? $loadData['comment'] : 0;
					$Dislike = (!empty($loadData['Dislike'])) ? $loadData['Dislike'] : 0;
				}
				if( $Type == 'video' || $Type == 'photo' && $selectFeed != 'Instagram'){
					$videoURL = $PostLink;
					$ImageURL = $PostImage;
				}

				$IGGP_Icon='';
				if($selectFeed == 'Instagram'){
					$IGGP_Type = !empty($loadData['IG_Type']) ? $loadData['IG_Type'] : 'Instagram_Basic';

					if($IGGP_Type == 'Instagram_Graph'){
						$IGGP_Icon = !empty($loadData['IGGP_Icon']) ? $loadData['IGGP_Icon'] : '';
						$likes = !empty($loadData['likes']) ? $loadData['likes']: 0;
						$comment = !empty($loadData['comment']) ? $loadData['comment'] : 0;
						$videoURL = $PostLink;
						$PostLink = !empty($loadData['IGGP_PostLink']) ? $loadData['IGGP_PostLink'] : '';
						$ImageURL = $PostImage;

						$IGGP_CAROUSEL = !empty($loadData['IGGP_CAROUSEL']) ? $loadData['IGGP_CAROUSEL'] : '';
						if( $Type == "CAROUSEL_ALBUM" && $FancyStyle == 'default' ){
							$FancyBoxJS = 'data-fancybox="IGGP-CAROUSEL-'.esc_attr($F_index).'-'.esc_attr($block_id).'-'.esc_attr($uniqEach).'"';
						}else{
							$FancyBoxJS = 'data-fancybox="'.esc_attr($block_id).'"';
						}
					}else if($IGGP_Type == 'Instagram_Basic'){
						$videoURL = $PostLink;
						$ImageURL = $PostImage;
					}
				}
				if(!empty($FbAlbum)){
					$PostLink = !empty($PostLink[0]['link']) ? $PostLink[0]['link'] : 0;
				}
				
				if( ($F_index < $TotalPost) && ( ($MediaFilter == 'default') || ($MediaFilter == 'ompost' && !empty($PostLink) && !empty($PostImage)) || ($MediaFilter == 'hmcontent' &&  empty($PostLink) && empty($PostImage) )) ){
					echo '<div class="grid-item splide__slide '.esc_attr('feed-'.$selectFeed.' '.$desktop_class.' '.$RKey.' '.$category_filter).'" data-index="'.esc_attr($selectFeed.$F_index).'">';				
						if(!empty($style)){
							include TPGBP_INCLUDES_URL. 'social-feed/'.sanitize_file_name('social-feed-'.$style.'.php');
						}
					echo '</div>';
				}
			}
		}
		
		$GridData = ob_get_clean();

		$result['success'] = 1;
		$result['totalFeed'] = isset($load_attr['totalFeed']) ? wp_unslash($load_attr['totalFeed']) : '';
		$result['FilterStyle'] = isset($load_attr['FilterStyle']) ? wp_unslash($load_attr['FilterStyle']) : '';
		$result['allposttext'] = isset($load_attr['allposttext']) ? wp_unslash($load_attr['allposttext']) : '';
		$result['HTMLContent'] = $GridData;
		
		return wp_send_json($result);
		exit();
	}
	
	/*
	 * Get Pages List Login Register block use
	 * @since 1.3.0
	 */
	public function tpgb_get_page_list(){
        $tppage = get_pages();
        $getpageList = [];
        foreach($tppage as $page){
            $pageArr = (array) $page;
            $getpageList[] = [ 'value' => $pageArr['ID'] , 'label' => $pageArr['post_title'] ];
        }
        return $getpageList;
    }
	
	/*
	 * Subscribe Mailchimp Message
	 * @since 1.3.0
	 */
	public static function tpgb_mailchimp_subscriber_message( $email, $status, $list_id, $api_key, $merge_fields = array(),$mc_group_ids = '', $mc_tags_ids = '' ){
		$data = array(
			'apikey'        => $api_key,
			'email_address' => $email,
			'status'        => $status,
		);
		
		if(!empty($merge_fields)){
			$data['merge_fields'] = $merge_fields;
		}
		$mc_group_ids = !empty($mc_group_ids) ? sanitize_text_field( $mc_group_ids ) : '';
		if(!empty($mc_group_ids)){
			$interests = explode( ' | ', trim( $mc_group_ids ) );
			$interests=array_flip($interests);

			foreach($interests as $key => $value){
				$data['interests'][$key] = true;
			}
		}
		$mc_tags_ids = !empty($mc_tags_ids) ? sanitize_text_field( $mc_tags_ids ) : '';
		if(!empty($mc_tags_ids)){
			$data['tags'] = explode( '|', trim($mc_tags_ids) );
		}
	 
		$request_args = array(
			'headers'     => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Basic ' . base64_encode('user:' . $api_key)
			),
			'user-agent'  => 'PHP-MCAPI/2.0',
			'timeout'     => 10,
			'body'        => json_encode($data),
			'method'      => 'PUT'
		);
		
		$response = wp_remote_request(
			'https://' . substr($api_key, strpos($api_key, '-') + 1) . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5(strtolower($data['email_address'])),
			$request_args
		);
		
		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			return "Something went wrong: $error_message";
		} else {
			$result = wp_remote_retrieve_body($response);
			return $result;
		}
	}

	/*
	 * Add Link Custom Attribute
	 * @since 1.3.1
	 */
	public static function add_link_attributes( $fieldname=[], $separator = ',' ) {
		if(!empty($fieldname) && is_array($fieldname) && isset($fieldname['attr']) && !empty($fieldname['attr'])){
			$output = [];
			$custom_attr = $fieldname['attr'];
			
			$attributes = explode( $separator, $custom_attr );
			foreach ( $attributes as $attribute ) {
				$key_val = explode( '|', $attribute );

				$attr_key = mb_strtolower( $key_val[0] );

				// Remove any not allowed characters.
				preg_match( '/[-_a-z0-9]+/', $attr_key, $key_matches );

				if ( empty( $key_matches[0] ) ) {
					continue;
				}

				$attr_key = $key_matches[0];

				// Avoid Javascript events and unescaped href.
				if ( 'on' === substr( $attr_key, 0, 2 ) || 'href' === $attr_key ) {
					continue;
				}

				if ( isset( $key_val[1] ) ) {
					$attr_value = trim( $key_val[1] );
				} else {
					$attr_value = '';
				}

				$output[ $attr_key ] = $attr_value;
			}

			return self::link_render_html_attributes($output);
		}

		return '';
	}

	/*
	 * Html Render Attributes
	 * @since 1.3.1
	 */
	public static function link_render_html_attributes( array $attributes ) {
		$html_attr = [];

		foreach ( $attributes as $key => $values ) {
			if ( is_array( $values ) ) {
				$values = implode( ' ', $values );
			}

			$html_attr[] = sprintf( '%1$s="%2$s"', $key, esc_attr( $values ) );
		}

		return implode( ' ', $html_attr );
	}

	/**
	 * Get User Role List
	 * @since 1.4.0
	 */

	public static function tpgbp_get_user_role(){
		
		global $wp_roles;

		$useroleli = $wp_roles->get_names();

		$tpgbrole = array();
		foreach ( $useroleli as $key => $role_list ) {
			$tpgbrole[] = [ $key , $role_list];
		}
	
		return $tpgbrole;
	}

	/**
	 * Get Class For Metro Layout
	 * @since 1.4.0
	 */

	public static function tpgbp_metro_class($col='1',$metroCol='3',$metrosty='style-1' ,$total=''){
		$i=($col!='') ? $col : 1;
		if(!empty($metroCol)){
			
			//style-3
			if($metroCol=='3' && $metrosty=='style-1'){
				$i=($i<=10) ? $i : ($i%10);			
			}
			if($metroCol=='3' && $metrosty=='style-2'){
				$i=($i<=9) ? $i : ($i%9);			
			}
			if($metroCol=='3' && $metrosty=='style-3'){
				$i=($i<=15) ? $i : ($i%15);			
			}
			if($metroCol=='3' && $metrosty=='style-4'){
				$i=($i<=8) ? $i : ($i%8);			
			}
			if($metroCol=='3' && $metrosty=='custom'){
				$i=($i<=$total) ? $i : ($i%$total);
				if($i == 0) {$i = $total;}
			}
			//style-4
			if($metroCol=='4' && $metrosty=='style-1'){
				$i=($i<=12) ? $i : ($i%12);			
			}
			if($metroCol=='4' && $metrosty=='style-2'){
				$i=($i<=14) ? $i : ($i%14);			
			}
			if($metroCol=='4' && $metrosty=='style-3'){
				$i=($i<=12) ? $i : ($i%12);			
			}
			if($metroCol=='4' && $metrosty=='custom'){
				$i=($i<=$total) ? $i : ($i%$total);
				if($i == 0) {$i = $total;}
			}
			//style-5
			if($metroCol=='5'){
				if($metrosty=='custom'){
					$i=($i<=$total) ? $i : ($i%$total);
					if($i == 0) {$i = $total;}
				}else{
					$i=($i<=18) ? $i : ($i%18);	
				}	
			}
			//style-6
			if($metroCol=='6'){
				if( $metrosty=='custom' ){
					$i=($i<=$total) ? $i : ($i%$total);
					if($i == 0) {$i = $total;}
				}else{
					$i=($i<=16) ? $i : ($i%16);
				}
			}
		}
		return $i;
	}
	
	/**
	 * Equal Height Attribute Function 
	 * @since 1.4.0
	 */
	public static function global_equal_height( $attr ){
		$equalHeight = (!empty($attr['tpgbEqualHeight'])) ? $attr['tpgbEqualHeight'] : false;
		$equalUnqClass = (!empty($attr['equalUnqClass'])) ? $attr['equalUnqClass'] : '';

		$eqlOpt = ''; $equalHeightAttr = '';
		if(!empty($equalHeight)){
			$eqlOpt = esc_attr($equalUnqClass);
			$equalHeightAttr .= ' data-tpgb-equal-height="'.esc_attr($eqlOpt).'"';
		}

		return $equalHeightAttr;
	}

	/**
	 * Get Current User Data For Backend Editor 
	 * @since 2.0.0
	 */
	public static function tpgbp_get_current_user (){
		if( is_user_logged_in()){
			$curtUser = wp_get_current_user();
			$user_info = get_user_meta($curtUser->ID);
			return $user_info;
		}
	}

	/*
	 * Get Carousel Arrow Dot Class 
	 * @since 3.0.4
	 */
	public static function tpgb_carousel_arrowdot_class($attr){
		$showDots = (!empty($attr['showDots'])) ? $attr['showDots'] : [ 'md' => false ];
		$showArrows = (!empty($attr['showArrows'])) ? $attr['showArrows'] : [ 'md' => false ];
		$dotsStyle = (!empty($attr['dotsStyle'])) ? $attr['dotsStyle'] : false;
		$outerArrows = (!empty($attr['outerArrows'])) ? $attr['outerArrows'] : false;
		$slideHoverArrows = (!empty($attr['slideHoverArrows'])) ? $attr['slideHoverArrows'] : false;
		$slideHoverDots = (!empty($attr['slideHoverDots'])) ? $attr['slideHoverDots'] : false;

		$Sliderclass = '';

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

		return $Sliderclass;
	}

	/*
	 * Get Post Content Via Ajax
	 * @since 3.2.10
	 */

	public function tpgb_get_template_content(){
		$nonce = (isset($_POST["tpgb_nonce"])) ? wp_unslash( $_POST["tpgb_nonce"] ) : '';

		if ( !isset($_POST["tpgb_nonce"]) || !wp_verify_nonce( $nonce, 'tpgb-addons' ) ){
			die ( 'Security checked!');
		}

		$post_id =  intval($_POST['postid']);
		if( isset($post_id) && !empty($post_id) ) {
			$content_post = get_post($post_id);
			$content = '';
			if(is_object($content_post)){
				$content = $content_post->post_content;
				$content = apply_filters('the_content', $content);
				$content = str_replace('strokewidth', 'stroke-width', $content);
				$content = str_replace('strokedasharray', 'stroke-dasharray', $content);
				$content = str_replace('stopcolor', 'stop-color', $content);
				$content = str_replace('loading="lazy"', '', $content);
			}
			if ($content) {
				wp_send_json_success($content);
			} else {
				wp_send_json_success('fail');
			}
		}
		wp_die();
	}

	/*
	 * Get Post Content Via Ajax
	 * @since 4.0.9
	 */

	 public function tpgb_generate_inline_css_action($attr=[] , $blockKey = ''){
		$advBdrNCss = $advBdrHCss = ''; 
		$advBdrAllCss = [ 'md' => '' , 'sm' => '' , 'xs' => '' , 'tabCss' => '' , 'noResponsive' => '' ];
		$advBorderRadius = false;
		if(!empty($attr['advBorderRadius']['value']) && !empty($attr['advBorderRadius']['value']['tpgbReset'])){
			
			$selBdrArea = (!empty($attr['advBorderRadius']['value']['selBdrArea'])) ? $attr['advBorderRadius']['value']['selBdrArea'] : 'background';
			$advBdrUniqueClass = (!empty($attr['advBorderRadius']['value']['advBdrUniqueClass'])) ? $attr['advBorderRadius']['value']['advBdrUniqueClass'] : '';
			$abNlayout = (!empty($attr['advBorderRadius']['value']['abNlayout'])) ? $attr['advBorderRadius']['value']['abNlayout'] : '';
			$advBdrNcustom = (!empty($attr['advBorderRadius']['value']['advBdrNcustom'])) ? $attr['advBorderRadius']['value']['advBdrNcustom'] : '';
			$abHlayout = (!empty($attr['advBorderRadius']['value']['abHlayout'])) ? $attr['advBorderRadius']['value']['abHlayout'] : '';
			$advBdrHcustom = (!empty($attr['advBorderRadius']['value']['advBdrHcustom'])) ? $attr['advBorderRadius']['value']['advBdrHcustom'] : '';

			$defLayout1 = '100% 0% 100% 0% / 100% 0% 100% 0%';
			$defLayout2 = '140% 60% 100% 0% / 68% 60% 40% 32%';
			$defLayout3 = '73% 27% 100% 0% / 73% 60% 40% 27%';
			$defLayout4 = '0% 100% 50% 50% / 50% 100% 0% 50%';
			$defLayout5 = '44% 56% 50% 50% / 57% 32% 68% 43%';
			$defLayout6 = '71% 29% 35% 65% / 33% 23% 77% 67%';
			$defLayout7 = '26% 74% 25% 75% / 80% 31% 69% 20%';
			$defLayout8 = '0% 100% 25% 75% / 100% 100% 0% 0%';
			$defLayout9 = '49% 51% 59% 41% / 63% 0% 100% 37%';

			/* Normal Css */
			if($abNlayout=='custom'){
				if(!empty($advBdrNcustom)){
					if($selBdrArea=='background'){
						$advBdrNCss = '.tpgb-wrap-'.esc_attr($attr['block_id']['value']).' .tpgb-block-'.esc_attr($attr['block_id']['value']).' { border-radius: '.esc_attr($advBdrNcustom).'}';
					}else if($selBdrArea=='custom' && !empty($advBdrUniqueClass)){
						$advBdrNCss = '.tpgb-wrap-'.esc_attr($attr['block_id']['value']).' '.esc_attr($advBdrUniqueClass).' { border-radius: '.esc_attr($advBdrNcustom).'}';
					}
				}
			}else{
				$abNlayoutType = '';
				if($abNlayout=='layout-1'){
					$abNlayoutType = $defLayout1;
				}else if($abNlayout=='layout-2'){
					$abNlayoutType = $defLayout2;
				}else if($abNlayout=='layout-3'){
					$abNlayoutType = $defLayout3;
				}else if($abNlayout=='layout-4'){
					$abNlayoutType = $defLayout4;
				}else if($abNlayout=='layout-5'){
					$abNlayoutType = $defLayout5;
				}else if($abNlayout=='layout-6'){
					$abNlayoutType = $defLayout6;
				}else if($abNlayout=='layout-7'){
					$abNlayoutType = $defLayout7;
				}else if($abNlayout=='layout-8'){
					$abNlayoutType = $defLayout8;
				}else if($abNlayout=='layout-9'){
					$abNlayoutType = $defLayout9;
				}
				

				if($selBdrArea=='background'){
					$advBdrNCss = '.tpgb-wrap-'.esc_attr($attr['block_id']['value']).' .tpgb-block-'.esc_attr($attr['block_id']['value']).' { border-radius: '.esc_attr($abNlayoutType).'}';
				}else if($selBdrArea=='custom' && !empty($advBdrUniqueClass)){
					$advBdrNCss = '.tpgb-wrap-'.esc_attr($attr['block_id']['value']).' '.esc_attr($advBdrUniqueClass).'{ border-radius: '.esc_attr($abNlayoutType).'}';
				}
			}

			/* Hover Css */
			if($abHlayout=='custom'){
				if(!empty($advBdrHcustom)){
					if($selBdrArea=='background'){
						$advBdrHCss = '.tpgb-wrap-'.esc_attr($attr['block_id']['value']).' .tpgb-block-'.esc_attr($attr['block_id']['value']).':hover{ border-radius: '.esc_attr($advBdrHcustom).'}';
					}else if($selBdrArea=='custom' && !empty($advBdrUniqueClass)){
						$advBdrHCss = '.tpgb-wrap-'.esc_attr($attr['block_id']['value']).' '.esc_attr($advBdrUniqueClass).':hover{ border-radius: '.esc_attr($advBdrHcustom).'}';
					}
				}
			}else{
				$abHlayoutType = '';
				if($abHlayout=='layout-1'){
					$abHlayoutType = $defLayout1;
				}else if($abHlayout=='layout-2'){
					$abHlayoutType = $defLayout2;
				}else if($abHlayout=='layout-3'){
					$abHlayoutType = $defLayout3;
				}else if($abHlayout=='layout-4'){
					$abHlayoutType = $defLayout4;
				}else if($abHlayout=='layout-5'){
					$abHlayoutType = $defLayout5;
				}else if($abHlayout=='layout-6'){
					$abHlayoutType = $defLayout6;
				}else if($abHlayout=='layout-7'){
					$abHlayoutType = $defLayout7;
				}else if($abHlayout=='layout-8'){
					$abHlayoutType = $defLayout8;
				}else if($abHlayout=='layout-9'){
					$abHlayoutType = $defLayout9;
				}

				if($selBdrArea=='background'){
					$advBdrHCss = '.tpgb-wrap-'.esc_attr($attr['block_id']['value']).' .tpgb-block-'.esc_attr($attr['block_id']['value']).':hover{ border-radius: '.esc_attr($abHlayoutType).'}';
				}else if($selBdrArea=='custom' && !empty($advBdrUniqueClass)){
					$advBdrHCss = '.tpgb-wrap-'.esc_attr($attr['block_id']['value']).' '.esc_attr($advBdrUniqueClass).':hover{ border-radius: '.esc_attr($abHlayoutType).'}';
				}
			}
				
			$advBdrAllCss['noResponsive'] .= $advBdrNCss.' '.$advBdrHCss;
		}

		// Advance Image Pro CSS
		if( $blockKey === 'tpgb/tp-creative-image' ){
			if( isset($attr["ScrollRevelImg"]) && isset($attr["ScrollRevelImg"]['value']) && !empty($attr["ScrollRevelImg"]['value']) && isset($attr["AnimBgColor"]['value']) && !empty( $attr["AnimBgColor"]['value'] ) ){
				$advBdrAllCss['noResponsive'] .= '.tpgb-block-'.esc_attr($attr["block_id"]['value']).' .tpgb-bg-animate-img:after{background:'.esc_attr($attr["AnimBgColor"]['value']).';}';
			}
		}

		// Advanced Typography marqueeStyle CSS 
		if( $blockKey === 'tpgb/tp-adv-typo' ){
			if( isset( $attr["marquee"]['value'] )&& !empty( $attr["marquee"]['value'] ) && isset( $attr["marqueeType"]['value'] ) && !empty($attr["marqueeType"]['value']) &&  $attr["marqueeType"]['value'] == 'on_transition' && isset($attr["marqueeDir"]['value']) &&  !empty($attr["marqueeDir"]['value']) ){
				$marqueeClass = 'tpgb_adv_typo_'.esc_attr($attr["marqueeDir"]['value']);
				if(isset($attr["marqueeAni"]['value']) && !empty($attr["marqueeAni"]['value'])){
					$advBdrAllCss['noResponsive'] .=  '.tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-adv-typo .'.esc_attr($marqueeClass).' { animation: '.esc_attr($marqueeClass).' '.esc_attr($attr["marqueeAni"]['value']).'s linear infinite; }';
				}
			}

			if (isset($attr['textListing']) && is_array($attr['textListing']) && !empty($attr['textListing'])) {
				foreach ($attr['textListing'] as $index => $item) {
					$transMarqueeClass = '';

					if (isset($item['marquee']) && !empty($item['marquee']) && isset($item['marqueeType']) && !empty($attr["marqueeType"]) && $item['marqueeType'] === 'on_transition' && isset($item['marqueeDir']) && !empty($item['marqueeDir'])) {

						$transMarqueeClass = 'tpgb_adv_typo_' . esc_attr($item['marqueeDir']);
						if (isset($item['marqueeAni']) && !empty($item['marqueeAni'])) {
							$advBdrAllCss['noResponsive'] .=  '.tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-adv-typo .tp-repeater-item-' . esc_attr($item['_key']) . ' .' . esc_attr($transMarqueeClass) . ' { animation: ' . esc_attr($transMarqueeClass) . ' ' . esc_attr($item['marqueeAni']) . 's linear infinite; }';
						}
					}
				}
			}
		}

		// Before After Alignment CSS 
		if($blockKey === 'tpgb/tp-before-after'){
			// Alignment css 
			if(( isset($attr["alignment"]['value']['md']) && !empty($attr["alignment"]['value']['md']) && $attr["alignment"]['value']['md'] == 'right' )) {
				$advBdrAllCss['md'] .= '.tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-before-after { margin-left: auto !important; margin-right: 0px !important  } } ';
			}

			if( isset($attr["alignment"]['value']['sm']) && !empty($attr["alignment"]['value']['sm']) && $attr["alignment"]['value']['sm'] == 'right'){
				$advBdrAllCss['tabCss'] .=  '@media (max-width: 1024px) and (min-width:768px) { .tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-before-after{margin-left: auto !important; margin-right: 0px !important } }';
			}

			if( isset($attr["alignment"]['value']['xs']) && !empty($attr["alignment"]['value']['xs']) && $attr["alignment"]['value']['xs'] == 'right'){
				$advBdrAllCss['xs'] .=  '.tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-before-after{margin-left: auto !important; margin-right: 0px !important }';
			}

			// Left
			if(( isset($attr["alignment"]['value']['md']) && !empty($attr["alignment"]['value']['md']) && $attr["alignment"]['value']['md'] == 'left' )) {
				$advBdrAllCss['md'] .= '.tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-before-after {margin-right: auto !important; margin-left: 0px !important; } ';
			}

			if( isset($attr["alignment"]['value']['sm']) && !empty($attr["alignment"]['value']['sm']) && $attr["alignment"]['value']['sm'] == 'left'){
				$advBdrAllCss['tabCss'] .=  '@media (max-width: 1024px) and (min-width:768px) { .tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-before-after{margin-right: auto !important; margin-left: 0px !important; } }';
			}

			if( isset($attr["alignment"]['value']['xs']) && !empty($attr["alignment"]['value']['xs']) && $attr["alignment"]['value']['xs'] == 'left'){
				$advBdrAllCss['xs'] .= '.tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-before-after{margin-right: auto !important; margin-left: 0px !important;}';
			}

			//center
			if(( isset($attr["alignment"]['value']['md']) && !empty($attr["alignment"]['value']['md']) && $attr["alignment"]['value']['md'] == 'center' )){
				$advBdrAllCss['md'] .= '.tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-before-after {margin-left: auto!important;  margin-right: auto !important;} ';
			}

			if( isset($attr["alignment"]['value']['sm']) && !empty($attr["alignment"]['value']['sm']) && $attr["alignment"]['value']['sm'] == 'center' ) {
				$advBdrAllCss['tabCss'] .= '@media (max-width: 1024px) and (min-width:768px) { .tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-before-after {margin-left: auto !important; margin-right: auto !important;} } ';
			}

			if( isset($attr["alignment"]['value']['xs']) && !empty($attr["alignment"]['value']['xs']) && $attr["alignment"]['value']['xs'] == 'center' ) {
				$advBdrAllCss['xs'] .= '.tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-before-after{margin-left: auto !important; margin-right: auto !important;} ';
			}

		}

		// Advanced Button Animation CSS 
		if($blockKey === 'tpgb/tp-advanced-buttons'){
			if(isset($attr["btnType"]['value']) && !empty($attr["btnType"]['value']) && $attr["btnType"]['value'] =='cta'  && $attr["ctaStyle"]['value'] =='style-9' && isset($attr["ctaStyle"]['value']) && !empty($attr["ctaStyle"]['value'])){
				$advBdrAllCss['noResponsive'] .= '.tpgb-block-'.esc_attr($attr["block_id"]['value']).' .tpgb-adv-btn-inner.ab-cta.tpgb-cta-style-9 .adv-btn-parrot{animation: tp-blink-'.esc_attr($attr["block_id"]['value']).' 0.8s infinite;} @keyframes tp-blink-'.esc_attr($attr["block_id"]['value']).' { 25%, 75% { color: transparent; } 40%, 60% { color: '.esc_attr($attr["extraTextColor"]['value']).'; } }';

			}
		}

		// coupon Code
		if( $blockKey === 'tpgb/tp-coupon-code' ){
			if( isset($attr['couponType']['value'] ) && !empty($attr['couponType']['value']) && $attr['couponType']['value'] =='standard' && isset($attr['actionType']['value'] ) && !empty($attr['actionType']['value']) && $attr['actionType']['value'] =='popup' && !empty($attr['ovBackFilt']['value'])){

				$advBdrAllCss['noResponsive'] .= '.tpgb-block-'.esc_attr($attr['block_id']['value']).' .copy-code-wrappar::after{ backdrop-filter: grayscale('.esc_js($attr['backGscale']['value']).')  blur('.esc_js($attr['backBlur']['value']).'px); }';

			}
		}

		if( $blockKey === 'tpgb/tp-design-tool' ){
			$gridOnFront = (!empty($attr['gridOnFront']['value'])) ? $attr['gridOnFront']['value'] : false;
			$designToolOpt = (!empty($attr['designToolOpt']['value'])) ? $attr['designToolOpt']['value'] : 'grid_stystem';
			$gridSystemOpt = (!empty($attr['gridSystemOpt']['value'])) ? $attr['gridSystemOpt']['value'] : 'gs_default';
			$gridDirection = (!empty($attr['gridDirection']['value'])) ? $attr['gridDirection']['value'] : 'ltr';

			if(!empty($gridOnFront) && $designToolOpt=='grid_stystem'){
				$advBdrAllCss['noResponsive'] .= 'html:before{content: "";position:fixed;pointer-events:none;top:0;right:0;bottom:0;left:0;margin-right:auto;margin-left:auto;width: calc(100% - (2 * var(--tp_grid_left_right_offset)));max-width: var(--tp_grid_cont_max_width);min-height: 100vh;background-image: var(--tp_grid_background-col-opt);background-size: var(--tp_grid_background-width-opt) 100%;z-index:999;}';
				if($gridSystemOpt=='gs_default'){
					$advBdrAllCss['noResponsive'] .=':root{--tp_grid_repeate-columns-width: calc(100% / var(--tp_grid_columns));--tp_grid_column-width: calc((100% / var(--tp_grid_columns)) - var(--tp_grid_alley));--tp_grid_background-width-opt: calc(100% + var(--tp_grid_alley));--tp_grid_background-col-opt: repeating-linear-gradient(to right,var(--tp_grid_color), var(--tp_grid_color) var(--tp_grid_column-width), var(--tp_grid_alley_color) var(--tp_grid_column-width), var(--tp_grid_alley_color) var(--tp_grid_repeate-columns-width));} html {--tp_grid_cont_max_width: 1140px;--tp_grid_columns: 12;--tp_grid_color: rgba(128, 114, 252, 0.25);--tp_grid_alley: 30px; --tp_grid_alley_color: transparent;--tp_grid_left_right_offset:0px;} @media (max-width: 1024px){ html {--tp_grid_columns: 6;--tp_grid_alley:15px;}} @media (max-width: 767px){ html {--tp_grid_columns: 4;--tp_grid_alley:10px;}} ';
				}else{
					$advBdrAllCss['noResponsive'] .=':root {--tp_grid_repeate-columns-width: calc(100% / var(--tp_grid_columns));--tp_grid_column-width: calc((100% / var(--tp_grid_columns)) - var(--tp_grid_alley));--tp_grid_background-width-opt: calc(100% + var(--tp_grid_alley)); --tp_grid_background-col-opt: repeating-linear-gradient(';

					$direction ='';
					if($gridDirection=='ltr'){
						$direction ='to right,';
					}else if($gridDirection=='ttb'){
						$direction ='';
					}
					$advBdrAllCss['noResponsive'] .=$direction . 'var(--tp_grid_color), var(--tp_grid_color) var(--tp_grid_column-width), var(--tp_grid_alley_color) var(--tp_grid_column-width), var(--tp_grid_alley_color) var(--tp_grid_repeate-columns-width) );}';
		
				}
			}
		}

		// Circle Menu CSS 
		if($blockKey === 'tpgb/tp-circle-menu'){
			$p = 1;

			if (!empty($attr['circleMenu']) && is_array($attr['circleMenu']) && isset($attr['circleMenu'])) {
				foreach ($attr['circleMenu'] as $index => $network) {
					$direction = '';
					$leftValue = 0;

					$leftValue = ($p - 1) * ((int)$attr["tIcnWidth"]['value']['md'] + $attr["iconGap"]["value"]);

					if( isset($attr["layoutType"]['value']) && !empty($attr["layoutType"]['value']) && $attr["layoutType"]['value']=='straight'){

						$direction = ( $attr["sDirection"]['value'] == 'left' ? 'right' : ($attr["sDirection"]['value'] == 'right' ? 'left' : ( $attr["sDirection"]['value'] == 'top' ? 'bottom' : ( $attr["sDirection"]['value'] == 'bottom' ? 'top' : ''))));

						$advBdrAllCss['noResponsive'] .= '.tpgb-block-'.esc_attr($attr["block_id"]['value']).'.layout-straight .tpgb-circle-menu-wrap.circleMenu-open.menu-direction-'.esc_attr($attr["sDirection"]['value']).' li:nth-child('.esc_attr($p).'){ '.esc_attr($direction).': '.esc_attr($leftValue).'px;}' . "\n";
					}
					$p++;
				}
			}

			$leftAValue = ( isset($attr['leftASize']['value']) && !empty($attr['leftASize']['value']) ) ? (array) $attr['leftASize']['value'] : '';
			$rightAValue = ( isset($attr['rightASize']['value']) && !empty($attr['rightASize']['value']) ) ? (array)$attr['rightASize']['value'] : '';
			$toggleIcnWidth = ( isset($attr['tIcnWidth']['value']) && !empty($attr['tIcnWidth']['value']) ) ? (array)$attr['tIcnWidth']['value'] : '';

			if( isset($attr["leftAuto"]['value']) && !empty($attr["leftAuto"]['value']) && isset($attr["rightAuto"]['value'] ) && !empty($attr["rightAuto"]['value'] ) ){
				$selector = '.tpgb-block-'.esc_attr($attr['block_id']['value']).'.layout-circle .tpgb-circle-menu-inner-wrapper .tpgb-circle-menu-wrap';

				if(isset($leftAValue['md']) && $leftAValue['md']=='0' && $rightAValue['md']=='0') {
					$advBdrAllCss['md'] .= $selector.'{left: '.$toggleIcnWidth["md"].$toggleIcnWidth["unit"].';}';
				}
				if(isset($leftAValue['sm']) && $leftAValue['sm']=='0' && $rightAValue['sm']=='0') {
					$advBdrAllCss['sm'] .= $selector.'{left: '.$toggleIcnWidth["sm"].$toggleIcnWidth["unit"].';}';
				}
				if(isset($leftAValue['xs']) && $leftAValue['xs']=='0' && $rightAValue['xs']=='0') {
					$advBdrAllCss['xs'] .= $selector.'{left: '.$toggleIcnWidth["xs"].$toggleIcnWidth["unit"].';}';
				}
			}
		}

		// Table Content Alignment CSS 
		if($blockKey === 'tpgb/tp-table-content'){
			
			if(( isset($attr["totitleAlign"]['value']['md']) && !empty($attr["totitleAlign"]['value']['md']) && $attr["totitleAlign"]['value'][ 'md'] == 'right' )) {
				$advBdrAllCss['md'] .= ' .tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-table-content{margin-left: auto;margin-right: 0}';
			}
			
			if( isset($attr["totitleAlign"]['value']['sm']) && !empty($attr["totitleAlign"]['value']['sm']) && $attr["totitleAlign"]['value']['sm'] == 'right'){
				$advBdrAllCss['tabCss'] .=  '@media (max-width: 1024px) and (min-width:768px) { .tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-table-content{margin-left: auto;margin-right: 0} }';
			}

			if( isset($attr["totitleAlign"]['value']['xs']) && !empty($attr["totitleAlign"]['value']['xs']) && $attr["totitleAlign"]['value']['xs'] == 'right'){
				$advBdrAllCss['xs'] .= ' .tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-table-content{margin-left: auto;margin-right: 0}';
			}

			if(( isset($attr["totitleAlign"]['value']['md']) && !empty($attr["totitleAlign"]['value']['md']) && $attr["totitleAlign"]['value']['md'] == 'center' )){
				$advBdrAllCss['md'] .= ' .tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-table-content{margin-left: auto; margin-right: auto;} ';
			}

			if( isset($attr["totitleAlign"]['value']['sm']) && !empty($attr["totitleAlign"]['value']['sm']) && $attr["totitleAlign"]['value']['sm'] == 'center' ) {
				$advBdrAllCss['tabCss'] .=  '@media (max-width: 1024px) and (min-width:768px) { .tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-table-content{margin-left: auto;margin-right: auto;} }';
			}
			
			if( isset($attr["totitleAlign"]['value']['xs']) && !empty($attr["totitleAlign"]['value']['xs']) && $attr["totitleAlign"]['value']['xs'] == 'center' ) {
				$advBdrAllCss['xs'] .= '.tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-table-content{margin-left: auto; margin-right: auto;} ';
			}

			if(( isset($attr["totitleAlign"]['value']['md']) && !empty($attr["totitleAlign"]['value']['md']) && $attr["totitleAlign"]['value'][ 'md'] == 'left' )) {
				$advBdrAllCss['md'] .= ' .tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-table-content{margin-right: auto;margin-left: 0}';
			}
			
			if( isset($attr["totitleAlign"]['value']['sm']) && !empty($attr["totitleAlign"]['value']['sm']) && $attr["totitleAlign"]['value']['sm'] == 'left'){
				$advBdrAllCss['tabCss'] .=  '@media (max-width: 1024px) and (min-width:768px) { .tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-table-content{margin-right: auto;margin-left: 0} }';
			}

			if( isset($attr["totitleAlign"]['value']['xs']) && !empty($attr["totitleAlign"]['value']['xs']) && $attr["totitleAlign"]['value']['xs'] == 'left'){
				$advBdrAllCss['xs'] .= ' .tpgb-block-'.esc_attr($attr["block_id"]['value']).'.tpgb-table-content{margin-right: auto;margin-left: 0}';
			}
		}

		if($blockKey === 'tpgb/tp-team-listing'){

			if (isset($attr["Style"]['value']) && !empty($attr["Style"]['value']) && $attr['Style']['value'] == 'style-4') {
				$uid = '.tpgb-block-' . esc_attr($attr["block_id"]['value']);
				
				if ( isset($attr["MaskImg"]['value']) && !empty($attr["MaskImg"]['value']) ) {
					if(isset($attr['MaskImg']['value']['url']) && !empty($attr['MaskImg']['value']['url'])){
						$advBdrAllCss['noResponsive'] .= esc_attr($uid) . '.tpgb-team-member-list.team-style-4 .team-list-content .tpgb-team-profile span.thumb-wrap{mask-image:url(' . esc_url($attr['MaskImg']['value']['url']) . ');-webkit-mask-image:url(' . esc_url($attr['MaskImg']['value']['url']) . ');}';
					}
				}
			
				if (isset($attr["ExLImg"]['value']) && !empty($attr["ExLImg"]['value'])) {
					if( isset($attr['ExLImg']['value']['url']) && !empty($attr['ExLImg']['value']['url']) ){
						$advBdrAllCss['noResponsive'] .= esc_attr($uid) . '.tpgb-team-member-list.team-style-4 .bg-image-layered{background-image:url(' . esc_url($attr['ExLImg']['value']['url']) . ');}';
					}
				}
			}
			
		}

		return $advBdrAllCss;
	}

        /**
     * Form Action Callback
     * @since 4.3.0
     */
    public function nxt_form_pro_action_callback() {
        check_ajax_referer('tpgb-addons', 'nonce');

        $response = array('success' => false, 'data' => '');
        $actions_success = [
            'email' => false,
            'auto_respond'=>false,
            'database'=>false,
            'recaptcha' => false,
            'webhook'=>false,
            'slack'=>false,
            'discord'=>false,
            'activecampaign'=>false,
            'mailerLite'=>false,
            'drip'=>false,
            'convertkit'=>false,
            'getresponse'=>false,
            'mailchimp' => false,
            'brevo' => false,
            'cloudflare'=>false,
        ];
        $errors = '';

        $action_option_raw = isset($_POST['actionOption']) ? $this->tpgb_simple_decrypt($_POST['actionOption'],'dy') : '[]'; 
        $first_decode = json_decode($action_option_raw, true);
        $action_option = json_decode($first_decode['actionOption'], true);
        $actionValues = array_column($action_option, 'value');
        if(empty($actionValues)){
            $response['success'] = false;
            $response['data'] = __('No action values found','tpgbp');
            echo wp_json_encode($response);
            wp_die();
        }

        // CAPTCHA VALIDATION FIRST - Before any actions
        $captcha_required = false;
        $captcha_valid = true;
         $formId = isset($first_decode['formId']) && !empty($first_decode['formId']) 
        ? (is_array($first_decode['formId']) ? implode(', ', array_map('sanitize_text_field', $first_decode['formId'])) :sanitize_text_field($first_decode['formId']))
        : '';

        if (isset($_POST['securityValue']) && $_POST['securityValue'] === "recaptcha") {
            $captcha_required = true;
            if (isset($_POST['g-recaptcha-response']) && isset($_POST['captchaopt'])) {
                $recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response']);
                $captchaopt = sanitize_text_field($_POST['captchaopt']);

                switch ($captchaopt) {
                    case 'v2i': 
                        $recaptcha_secret_v2i = $first_decode['captchaSecret'];
                        $recaptcha_secret = $recaptcha_secret_v2i;
                        break;
                    case 'v2': 
                        $recaptcha_secret_v2 = $first_decode['captchaSecret'];
                        $recaptcha_secret = $recaptcha_secret_v2;
                        break;
                    case 'v3': 
                        $recaptcha_secret_v3 = $first_decode['captchaSecret'];
                        $recaptcha_secret = $recaptcha_secret_v3;
                        break;
                    default:
                        $errors .= 'Invalid captcha option. ';
                        $captcha_valid = false;
                        break;
                }

                if ($captcha_valid) {
                    $recaptcha_response_message = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
                        'body' => array(
                            'secret'   => $recaptcha_secret,
                            'response' => $recaptcha_response,
                            'remoteip' => $_SERVER['REMOTE_ADDR'],
                        ),
                    ));

                    if (is_wp_error($recaptcha_response_message)) {
                        $errors .= __('Failed to validate reCAPTCHA. Error: ' . $recaptcha_response_message->get_error_message() . ' ','tpgbp');
                        $captcha_valid = false;
                    } else {
                        $response_body = wp_remote_retrieve_body($recaptcha_response_message);
                        $result = json_decode($response_body, true);

                        if ($captchaopt === 'v3') {
                            if (isset($result['success']) && $result['success'] && isset($result['score']) && $result['score'] >= 0.5) {
                                $actions_success['recaptcha'] = true;
                            } else {
                                $errors .= __('reCAPTCHA v3 validation failed or the score is too low. ','tpgbp');
                                $actions_success['recaptcha'] = false;
                                $captcha_valid = false;
                            }
                        } else {
                            if (isset($result['success']) && $result['success']) {
                                $actions_success['recaptcha'] = true;
                            } else {
                                $errors .= __('reCAPTCHA validation failed. Please try again. ','tpgbp');
                                $actions_success['recaptcha'] = false;
                                $captcha_valid = false;
                            }
                        }
                    }
                }
            } else {
                $errors .= __('No reCAPTCHA response or captcha option received. ','tpgbp');
                $actions_success['recaptcha'] = false;
                $captcha_valid = false;
            }
        } elseif (isset($_POST['securityValue']) && $_POST['securityValue'] === "cloudflare") {
            $captcha_required = true;
            if (isset($_POST['g-recaptcha-response'])) {
                $cloudflare_response = sanitize_text_field($_POST['g-recaptcha-response']);
                $cloudflare_secret = $first_decode['cloudSecretKey'];
                
                $cloudflare_response_message = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
                    'body' => array(
                        'secret'   => $cloudflare_secret,
                        'response' => $cloudflare_response,
                        'remoteip' => $_SERVER['REMOTE_ADDR'],
                    ),
                ));

                if (is_wp_error($cloudflare_response_message)) {
                    $error_message = $cloudflare_response_message->get_error_message();
                    $errors .= __('Failed to validate Cloudflare Turnstile. Error: ' . $error_message . ' ','tpgbp');
                    $captcha_valid = false;
                } else {
                    $cloudflare_body = wp_remote_retrieve_body($cloudflare_response_message);
                    $cloudflare_result = json_decode($cloudflare_body, true);

                    if (isset($cloudflare_result['success']) && $cloudflare_result['success']) {
                        $actions_success['cloudflare'] = true;
                    } else {
                        $errors .= __('Cloudflare Turnstile validation failed. Please check again. ','tpgbp');
                        $actions_success['cloudflare'] = false;
                        $captcha_valid = false;
                    }
                }
            } else {
                $errors .= __('No Cloudflare Turnstile response received. ','tpgbp');
                $captcha_valid = false;
            }
        } elseif ( isset($_POST['cf-turnstile-response']) ) {
            // If Turnstile response exists, validate it
            $captcha_required = true;
            $captcha_value = sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) );
            $captcha_error = apply_filters('nxt_block_form_content', $captcha_value, $formId, '');
            
            if(!empty($captcha_error)) {
                $errors .= $captcha_error;
                $captcha_valid = false;
            }
        }

        // If captcha is required and validation failed, return error immediately
        if ($captcha_required && !$captcha_valid) {
            $response['success'] = false;
            $response['data'] = $errors;
            echo wp_json_encode($response);
            wp_die();
        }

        // Continue with form field processing only if captcha validation passed
        $name = isset($_POST['text-field-field']) && !empty($_POST['text-field-field'])
            ? (is_array($_POST['text-field-field']) 
                ? implode(', ', array_map('sanitize_text_field', $_POST['text-field-field'])) 
                : sanitize_text_field($_POST['text-field-field']))
            : '';

        $time = isset($_POST['time-field']) && !empty($_POST['time-field'])
            ? (is_array($_POST['time-field']) 
                ? implode(', ', array_map('sanitize_text_field', $_POST['time-field'])) 
                : sanitize_text_field($_POST['time-field']))
            : '';

        $phone = isset($_POST['phone-field']) && !empty($_POST['phone-field'])
            ? (is_array($_POST['phone-field']) 
                ? implode(', ', array_map('sanitize_text_field', $_POST['phone-field'])) 
                : sanitize_text_field($_POST['phone-field']))
            : '';

        $checkbox_values = isset($_POST['checkbox-field']) && !empty($_POST['checkbox-field'])
            ? (is_array($_POST['checkbox-field']) 
                ? implode(', ', array_map('sanitize_text_field', $_POST['checkbox-field'])) 
                : sanitize_text_field($_POST['checkbox-field']))
            : '';

        $date = isset($_POST['date-field']) && !empty($_POST['date-field'])
            ? (is_array($_POST['date-field']) 
                ? implode(', ', array_map('sanitize_text_field', $_POST['date-field'])) 
                : sanitize_text_field($_POST['date-field']))
            : '';

        $email = isset($_POST['email-field']) && !empty($_POST['email-field'])
            ? (is_array($_POST['email-field']) 
                ? implode(', ', array_map('sanitize_email', $_POST['email-field'])) 
                : sanitize_email($_POST['email-field']))
            : '';

        $message_from_form = isset($_POST['message-field']) && !empty($_POST['message-field'])
            ? (is_array($_POST['message-field']) 
                ? implode(', ', array_map('sanitize_text_field', $_POST['message-field'])) 
                : sanitize_text_field($_POST['message-field']))
            : '';

        $number = isset($_POST['number-field']) && !empty($_POST['number-field'])
            ? (is_array($_POST['number-field']) 
                ? implode(', ', array_map('sanitize_text_field', $_POST['number-field'])) 
                : sanitize_text_field($_POST['number-field']))
            : '';

        $select = isset($_POST['select-field']) && !empty($_POST['select-field'])
            ? (is_array($_POST['select-field']) 
                ? implode(', ', array_map('sanitize_text_field', $_POST['select-field'])) 
                : sanitize_text_field($_POST['select-field']))
            : '';

        $radio_group_value = '';
        foreach ($_POST as $key => $value) {
            if (preg_match('/^radio.*-field$/', $key)) {
                if (is_array($value)) {
                    $radio_group_value .= implode(', ', array_map('sanitize_text_field', $value));
                } else {
                    $radio_group_value .= sanitize_text_field($value);
                }
            }
        }

        $url = isset($_POST['url-field']) && !empty($_POST['url-field'])
            ? (is_array($_POST['url-field']) 
                ? implode(', ', array_map('sanitize_text_field', $_POST['url-field'])) 
                : sanitize_text_field($_POST['url-field']))
            : '';

        $formId = isset($first_decode['formId']) && !empty($first_decode['formId']) 
        ? (is_array($first_decode['formId']) ? implode(', ', array_map('sanitize_text_field', $first_decode['formId'])) :sanitize_text_field($first_decode['formId']))
        : '';

        if (in_array('Email', $actionValues)) {
            $email_to = isset($first_decode['emailTo1']) && !empty($first_decode['emailTo1']) ? (strpos($first_decode['emailTo1'], ',') !== false ? array_map('sanitize_email', array_map('trim', explode(',', $first_decode['emailTo1']))) : sanitize_email($first_decode['emailTo1'])) : '';
            $subject = isset($first_decode['subject1']) && !empty($first_decode['subject1']) ? sanitize_text_field($first_decode['subject1']) : ''; 
            if (!empty($email_to) && !empty($subject)) {
                $from_name = isset($first_decode['frmNme']) && !empty($first_decode['frmNme']) ? ($first_decode['frmNme'] === '[nxt_name]' ? get_option('blogname') : sanitize_text_field($first_decode['frmNme'])) : '';
                $from_email = isset($first_decode['frmEmail']) && !empty($first_decode['frmEmail']) ? ($first_decode['frmEmail'] === '[nxt_email]' ? get_option('admin_email') : sanitize_email($first_decode['frmEmail'])) : 'no-reply@example.com';
                $reply_to = isset($first_decode['replyTo']) && !empty($first_decode['replyTo']) ? sanitize_text_field($first_decode['replyTo']) : '';
                $cc = isset($first_decode['ccEmail1']) && !empty($first_decode['ccEmail1']) ? sanitize_text_field($first_decode['ccEmail1']) : ''; 
                $bcc = isset($first_decode['bccEmail1']) && !empty($first_decode['bccEmail1']) ? sanitize_text_field($first_decode['bccEmail1']) : ''; 
                $emailHdg = isset($first_decode['emailHdg']) && !empty($first_decode['emailHdg']) ? sanitize_text_field($first_decode['emailHdg']) : __('You have received a new form submission:','tpgbp');

                                
                $excludedFields = [
                    'actionOption',
                    'nonce',
                    'g-recaptcha-response',
                    'securityValue',
                    'captchaopt',
                    'text-field-field',
                    'label',
                    'phone-field',
                    'time-field',
                    'checkbox-field',
                    'date-field',
                    'email-field',
                    'message-field',
                    'select-field',
                    'number-field',
                    'url-field',
                    'radio-group',
                    '-field',
                    'cf-turnstile-response',
                    'cf-turnstile-response-field'
                ];
                
                $regular_fields = [];
                
                foreach ($_POST as $key => $value) {
                    if (in_array(strtolower($key), array_map('strtolower', $excludedFields), true)) {
                        continue;
                    }
                    if (in_array($key, ['actionOption', 'nonce', 'Captchaopt'], true)) {
                        continue;
                    }

                    $formatted_key = str_replace('_', ' ', $key);

                    //validation for html
                    if (preg_replace('/<[^>]*>/', '', $value) !== $value) {
                        $errors .= __("HTML content not allowed in $formatted_key field. ",'tpgbp');
                        continue;
                    }
                    
                    //validation for email
                    if (!empty($value) && is_string($value) && strpos($value, '@') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $field_label = ucfirst(str_replace(['_', '-'], ' ', $key));
                        $valErrMsg = isset($action_option['valErrMsg']) && !empty($action_option['valErrMsg']) 
                            ? sanitize_text_field($action_option['valErrMsg']) 
                            : __("Invalid email format in " . $field_label . " field. ",'tpgbp');
                        $errors .= $valErrMsg;
                        continue;
                    }
                    
                    // user Email Shortcode 
                    if ($reply_to === '[nxt_user_email]' && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $reply_to = sanitize_email($value);
                    }

                    $patterns = [
                        '/^label\[.*\]$/',                // Label pattern
                        '/^text-field_\d+$/',            // Text field with number
                        '/^url_\d+$/',                   // URL with number
                        '/^time_\d+$/',                  // Time with number
                        '/^date_\d+$/',                  // Date with number
                        '/^number_\d+$/',                // Number with number
                        '/^select_\d+$/',                // Select with number
                        '/^phone_\d+$/',                 // Phone with number
                        '/^email_\d+$/',                 // Email with number
                        '/^message_\d+$/',               // Message with number
                        '/^radio-group.*$/',             // Radio group with any suffix
                        '/^radio\s*\d+$/',               // Radio with number (with optional space)
                        '/^(_\d+|checkbox_\d+)$/',          // Checkbox with number and colon
                        '/^radio\s*\d+:$/'   ,            // Radio with number and colon
                        '/^nxt_.+$/' //any key starting with nxt_
                    ];
                
                    $skip = false;
                    foreach ($patterns as $pattern) {
                        if (preg_match($pattern, $key)) {
                            $skip = true;
                            break;
                        }
                    }
                    
                    if ($skip) {
                        continue;
                    }
                
                    $formatted_key = str_replace('_', ' ', $key);
                    if (is_array($value)) {
                        $regular_fields[$formatted_key] = array_map('sanitize_text_field', $value);
                    } else {
                        $regular_fields[$formatted_key] = sanitize_text_field($value);
                    }
                }

                $full_message = "<h2>$emailHdg</h2>"; 
                $non_empty_fields = array_filter($regular_fields, function ($value, $key) { 
                    if (is_array($value)) { 
                        $value = array_filter($value, function ($item) { 
                            return !empty($item) && strtolower($item) !== 'undefined'; 
                        }); 
                        return !empty($value); 
                    } 
                    return !empty($value) && strtolower($value) !== 'undefined' && strtolower($key) !== 'action'; 
                }, ARRAY_FILTER_USE_BOTH); 
                
                foreach ($non_empty_fields as $key => $value) { 
                    if (is_array($value)) { 
                        $value = implode(', ', $value); 
                    } 
                    $full_message .= "<p>" . ucfirst($key) . ": $value</p>"; 
                }

                $full_message .= "<hr style='border: 1px dashed #ccc; margin: 20px 0;'>";

                if (isset($first_decode['metaDataOpt']) && is_array($first_decode['metaDataOpt'])) {
                    $full_message .= "<p><strong>Meta Data:</strong></p>";
                
                    foreach ($first_decode['metaDataOpt'] as $metaData) {
                        $label = isset($metaData['label']) && !empty($metaData['label']) ? $metaData['label'] : 'Unknown Label';
                        $value = 'Unknown Value';
                
                        if (isset($metaData['value'])) {
                            switch ($metaData['value']) {
                                case 'metaDate':
                                    $value = date('Y-m-d');
                                    break;
                                case 'metaTime':
                                    $value = date('H:i:s');
                                    break;
                                case 'metaRemoteIp':
                                    $value = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
                                    break;
                                case 'metaUserAgent':
                                    $value = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown User Agent';
                                    break;
                                case 'metaPageUrl':
                                    $value = $_SERVER['HTTP_REFERER'] ?? 'Unknown Page URL';
                                    break;
                                default:
                                    $value = 'Unknown Value';
                            }
                        }
                
                        $full_message .= "$label: $value<br>";
                    }
                
                    // $full_message .= "</ul>";
                } else {
                    $full_message .= "<p><em>No metadata options provided.</em></p>";
                }
                
        
                $headers = [
                    "From: $from_name <$from_email>",
                    "Reply-To: $reply_to",
                    "Content-Type: text/html; charset=UTF-8"
                ];
        
                if (!empty($cc)) {
                    $headers[] = "Cc: $cc";
                }
        
                if (!empty($bcc)) {
                    $headers[] = "Bcc: $bcc";
                }
        
                $mail_sent = wp_mail($email_to, $subject, $full_message, $headers);
        
                $actions_success['email'] = $mail_sent ? true : false;
                $errors .= !$mail_sent ? __('Failed to send email. ','tpgbp') : '';
            } else {
                $errors .= 'Email address and Subject is required. '; 
                $actions_success['email'] = false;
            }
        }
        
        if (in_array('Auto Respond Email', $actionValues)) {
            $email_to2 = isset($first_decode['emailTo2']) && !empty($first_decode['emailTo2']) ? ($first_decode['emailTo2'] === '[nxt_user_email]' ? '[nxt_user_email]' : sanitize_email($first_decode['emailTo2'])) : get_option('admin_email');

            if (!empty($email_to2)) {
                $subject2 = isset($first_decode['subject2']) && !empty($first_decode['subject2']) ? sanitize_text_field($first_decode['subject2']) : '';
                $message2 = isset($first_decode['message2']) && !empty($first_decode['message2']) ? sanitize_textarea_field($first_decode['message2']) : '';
                $from_name2 = isset($action_option['frmNme2']) && !empty($action_option['frmNme2']) ? ($action_option['frmNme2'] === '[nxt_name]' ? get_option('blogname') : sanitize_text_field($action_option['frmNme2'])) : '';
                $from_email2 = isset($action_option['frmEmail2']) && !empty($action_option['frmEmail2']) ? ($action_option['frmEmail2'] === '[nxt_email]' ? get_option('admin_email') : sanitize_email($action_option['frmEmail2'])) : 'no-reply@example.com';
                $reply_to2 = isset($first_decode['replyTo2']) && !empty($first_decode['replyTo2']) ? sanitize_email($first_decode['replyTo2']) : '';
                $cc2 = isset($first_decode['ccEmail2']) && !empty($first_decode['ccEmail2']) ? sanitize_text_field($first_decode['ccEmail2']) : '';
                $bcc2 = isset($first_decode['bccEmail2']) && !empty($first_decode['bccEmail2']) ? sanitize_text_field($first_decode['bccEmail2']) : '';
                $emailHdg2 = isset($first_decode['emailHdg2']) && !empty($first_decode['emailHdg2']) ? sanitize_text_field($first_decode['emailHdg2']) : __('You have received a new form submission:','tpgbp');
                $regular_fields = [];
                $excludedFields = [
                    'actionOption',
                    'nonce',
                    'g-recaptcha-response',
                    'securityValue',
                    'captchaopt',
                    'text-field-field',
                    'label',
                    'phone-field',
                    'time-field',
                    'checkbox-field',
                    'date-field',
                    'email-field',
                    'message-field',
                    'select-field',
                    'number-field',
                    'url-field',
                    'radio-group',
                    '-field',
                    'cf-turnstile-response',
                    'cf-turnstile-response-field'
                ];
                
                $regular_fields = [];
                
                foreach ($_POST as $key => $value) {
                    if (in_array(strtolower($key), array_map('strtolower', $excludedFields), true)) {
                        continue;
                    }
                
                    $patterns = [
                        '/^label\[.*\]$/',                // Label pattern
                        '/^text-field_\d+$/',            // Text field with number
                        '/^url_\d+$/',                   // URL with number
                        '/^time_\d+$/',                  // Time with number
                        '/^date_\d+$/',                  // Date with number
                        '/^number_\d+$/',                // Number with number
                        '/^select_\d+$/',                // Select with number
                        '/^phone_\d+$/',                 // Phone with number
                        '/^email_\d+$/',                 // Email with number
                        '/^message_\d+$/',               // Message with number
                        '/^radio-group.*$/',             // Radio group with any suffix
                        '/^(_\d+|checkbox_\d+)$/',          // Checkbox with number and colon
                        '/^radio\s*\d+$/',               // Radio with number (with optional space)
                        '/^\d+$/',                       // Just numbers (like "0", "1", "2")
                        '/^checkbox\s*\d+:$/',           // Checkbox with number and colon
                        '/^radio\s*\d+:$/',               // Radio with number and colon
                        '/^nxt_.+$/' //any key starting with nxt_
                    ];
                
                    $skip = false;
                    foreach ($patterns as $pattern) {
                        if (preg_match($pattern, $key)) {
                            $skip = true;
                            break;
                        }
                    }
                    
                    if ($skip) {
                        continue;
                    }
                
                    $formatted_key = str_replace('_', ' ', $key);
                    if (is_array($value)) {
                        $regular_fields[$formatted_key] = array_map('sanitize_text_field', $value);
                    } else {
                        $regular_fields[$formatted_key] = sanitize_text_field($value);
                    }
                    if ($email_to2 === '[nxt_user_email]' && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $email_to2 = sanitize_email($value);
                    }
                }
                
        
                $full_message2 = "<h2>$emailHdg2</h2>";
                    $non_empty_fields = array_filter($regular_fields, function ($value, $key) {
                        if (is_array($value)) {
                            $value = array_filter($value, function ($item) {
                                return !empty($item) && strtolower($item) !== 'undefined';
                            });
                            return !empty($value);
                        }
                        return !empty($value) && strtolower($value) !== 'undefined' && strtolower($key) !== 'action';
                    }, ARRAY_FILTER_USE_BOTH);
                    
                    foreach ($non_empty_fields as $key => $value) {
                        if (is_array($value)) {
                            $value = implode(', ', $value);
                        }
                        $full_message2 .= "<p>" . ucfirst($key) . ": $value</p>";
                    }


                $full_message2 .= "<hr style='border: 1px dashed #ccc; margin: 20px 0;'>";

                if (isset($first_decode['metaDataOpt2']) && is_array($first_decode['metaDataOpt2'])) {
                    $full_message2 .= "<p>Meta Data:</p>";
                
                    foreach ($first_decode['metaDataOpt2'] as $metaData) {
                        $label = isset($metaData['label']) ? $metaData['label'] : 'Unknown Label';
                        $value = 'Unknown Value';
                
                        if (isset($metaData['value'])) {
                            switch ($metaData['value']) {
                                case 'metaDate2':
                                    $value = date('Y-m-d');
                                    break;
                                case 'metaTime2':
                                    $value = date('H:i:s');
                                    break;
                                case 'metaRemoteIp2':
                                    $value = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
                                    break;
                                case 'metaUserAgent2':
                                    $value = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown User Agent';
                                    break;
                                case 'metaPageUrl2':
                                    $value = $_SERVER['HTTP_REFERER'] ?? 'Unknown Page URL';
                                    break;
                                default:
                                    $value = 'Unknown Value';
                            }
                        }
                
                        $full_message2 .= "$label: $value\n";
                    }
                
                    // $full_message2 .= "</ul>";
                } else {
                    $full_message2 .= "<p><em>No metadata options provided.</em></p>";
                }
                
        
                $headers = [
                    "From: $from_name2 <$from_email2>",
                    "Reply-To: $reply_to2",
                    "Content-Type: text/html; charset=UTF-8"
                ];
        
                if (!empty($cc2)) {
                    $headers[] = "Cc: $cc2";
                }
        
                if (!empty($bcc2)) {
                    $headers[] = "Bcc: $bcc2";
                }
        
                $mail_sent = wp_mail($email_to2, $subject2, $full_message2, $headers);
        
                $actions_success['auto_respond'] = $mail_sent ? true : false;
                $errors .= !$mail_sent ? 'Failed to send email. ' : '';
            } else {
                $errors .= 'Email address is required. '; 
                $actions_success['auto_respond'] = false;
            }
        }

        if (in_array('Database Entry', $actionValues)) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            $prefix = 'nxt_';
            $table_name = $wpdb->prefix . $prefix . 'form_submissions';

            // Create table if it doesn't exist
            // Check if table exists first
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
            
            if(!$table_exists) {
                $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                    id BIGINT(20) NOT NULL AUTO_INCREMENT,
                    form_id varchar(255) DEFAULT '' NOT NULL,
                    name varchar(255) DEFAULT '' NOT NULL, 
                    email varchar(100) DEFAULT '' NOT NULL,
                    checkbox varchar(100) DEFAULT '' NOT NULL,
                    date varchar(100) DEFAULT '' NOT NULL,
                    number varchar(100) DEFAULT '' NOT NULL,
                    select_option varchar(100) DEFAULT '' NOT NULL,
                    radio varchar(100) DEFAULT '' NOT NULL,
                    phone varchar(20) DEFAULT '' NOT NULL,
                    time varchar(20) DEFAULT '' NOT NULL,
                    message text DEFAULT '' NOT NULL,
                    url varchar(255) DEFAULT '' NOT NULL,
                    submitted_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    PRIMARY KEY (id)
                ) $charset_collate;";
            
                dbDelta($sql);
            }

            // Add url column if it doesn't exist
            if ($wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE 'url'") != 'url') {
                $wpdb->query("ALTER TABLE $table_name ADD COLUMN url varchar(255) DEFAULT '' NOT NULL");
            }

            // Insert form data
            $insert_result = $wpdb->insert(
                $table_name,
                array(
                    'form_id' => $formId,
                    'name' => $name,
                    'time' => $time,
                    'phone' => $phone,
                    'checkbox' => $checkbox_values,
                    'date' => $date,
                    'email' => $email,
                    'message' => $message_from_form,
                    'number' => $number,
                    'select_option' => $select,
                    'radio' => $radio_group_value,
                    'url' => $url
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );

            if ($insert_result === false) {
                /* translators: %s: Database error message */
                $errors .= sprintf( __( 'Failed to insert data into the database. Error: %s', 'tpgbp' ),$wpdb->last_error);
                $actions_success['database'] = false;
            } else {
                $actions_success['database'] = true;
            }
        }

        if (in_array('WebHook', $actionValues)) {

            $actions_success['webhook'] = false;
            $webhook_url = isset($first_decode['webhookurl']) && !empty($first_decode['webhookurl']) ? sanitize_text_field($first_decode['webhookurl']) : '';

            // Log the received webhook URL

            if (!filter_var($webhook_url, FILTER_VALIDATE_URL)) {
                $errors .= __('A valid URL was not provided. ', 'tpgbp'); 
                error_log('[Webhook] ERROR: Invalid URL provided.');
            } else {
                $data_to_send = [];

                // Filter only fields starting with nxt_
                $nxt_fields = array_filter($_POST, function($key) {
                    return strpos($key, 'nxt_') === 0; 
                }, ARRAY_FILTER_USE_KEY);

                if (!empty($nxt_fields)) {
                    foreach ($nxt_fields as $key => $value) {
                        $clean_key = preg_replace('/^nxt_/', '', $key); 
                        $data_to_send[$clean_key] = $value;
                    }
                } else {
                    // Fallback static fields
                    $data_to_send = [
                        'name'     => $name,
                        'email'    => $email,
                        'phone'    => $phone,
                        'message'  => $message_from_form,
                        'radio'    => $radio_group_value,
                        'checkbox' => $checkbox_values,
                    ];
                }

                // Send request
                $webhook_response = wp_remote_post($webhook_url, array(
                    'method'    => 'POST',
                    'body'      => json_encode($data_to_send),
                    'headers'   => array('Content-Type' => 'application/json'),
                ));

                if (is_wp_error($webhook_response)) {
                    /* translators: %s: Webhook response error message */
                    $response_message = sprintf(__('Webhook request failed: %s', 'tpgbp'), esc_html($webhook_response->get_error_message()));
                    $errors .= $response_message; 
                } else {
                    $status_code = wp_remote_retrieve_response_code($webhook_response);
                    $body = wp_remote_retrieve_body($webhook_response);


                    $actions_success['webhook'] = $status_code === 200 ? true : false;
                    if ($status_code === 200) {
                        $response['data'] .= __('Webhook request successful. ', 'tpgbp');
                    } else {
                        /* translators: %s: Webhook response error message */
                        $response_message = sprintf(__('Webhook request failed with status code: %s. Response: %s', 'tpgbp'), $status_code, $body);
                        $errors .= $response_message;
                    }
                }
            }
        }

        
        if (in_array('Slack', $actionValues)) {		
        
            $slack_url = "https://slack.com/api/chat.postMessage";
            $slack_token = sanitize_textarea_field($first_decode['slackTkn'] ?? '');
            $slack_channel = sanitize_text_field($first_decode['slackChnl'] ?? '');
        
            if (empty($slack_token)) {
                $errors .=__('Slack token is required. ','tbgbp');
            }
            elseif(empty($slack_channel)) {
                $errors .=__('Slack channel is required. ','tpgbp');			
            } 
            else {
                $form_data = array(
                    'Name' => $name,
                    'Email' => $email,
                    'Phone' => $phone,
                    'Message' => $message_from_form,
                    'Radio Group' => $radio_group_value,
                    'Checkbox' => $checkbox_values,
                );
        
                $formatted_message = "*New Form Submission:*\n";
                foreach ($form_data as $key => $value) {
                    $formatted_message .= "*{$key}:* " . ($value ? $value : 'N/A') . "\n";
                }
        
                $slack_message = array(
                    'channel' => $slack_channel,
                    'text' => $formatted_message,
                );
        
                $slack_headers = array(
                    'Authorization' => 'Bearer ' . $slack_token,
                    'Content-Type' => 'application/json',
                );
        
                $response_slack = wp_remote_post($slack_url, array(
                    'method' => 'POST',
                    'body' => json_encode($slack_message),
                    'headers' => $slack_headers,
                ));
        
                if (is_wp_error($response_slack)) {
                    $response_message = 'Slack request failed: ' . $response_slack->get_error_message();
                    $errors .= $response_message;
                } else {
                    $body = wp_remote_retrieve_body($response_slack);
                    $decoded_body = json_decode($body, true);
        
                    $actions_success['slack'] = (isset($decoded_body['ok']) && $decoded_body['ok']) 
                    ? true 
                    : $errors .= $response_message;
                }
            }			
        }
        
        if (in_array('Discord', $actionValues)) {
        
            $discord_webhook_url = sanitize_textarea_field($first_decode['disUrl'] ?? '');
            $discord_username = sanitize_text_field($first_decode['disName'] ?? '');

            $discord_message = "Form Submission:\n";
            $discord_message .= "Name: $name\n";
            $discord_message .= "Email: $email\n";
            $discord_message .= "Phone: $phone\n";
            $discord_message .= "Message: $message_from_form\n";
            $discord_message .= "Radio Group: $radio_group_value\n";
            $discord_message .= "Checkbox Values: $checkbox_values\n";
        
            if (!filter_var($discord_webhook_url, FILTER_VALIDATE_URL)) {
                /* translators: %s: Discord webhook URL */
                $errors .= __('A valid Discord webhook URL was not provided. ','tpgbp');
            } else {
                $data_to_send = array(
                    'content' => $discord_message,
                    'username' => $discord_username
                );
        
                $response_discord = wp_remote_post($discord_webhook_url, array(
                    'method'    => 'POST',
                    'body'      => json_encode($data_to_send),
                    'headers'   => array('Content-Type' => 'application/json; charset=utf-8')
                ));
        
                if (is_wp_error($response_discord)) {
                    /* translators: %s: Discord webhook request failed message */
                    $response_message = sprintf(__( 'Discord webhook request failed: %s', 'tpgbp' ), $response_discord->get_error_message() );
                    $errors .= $response_message;
                } else {
                    $response_code = wp_remote_retrieve_response_code($response_discord);
                    $response_message = wp_remote_retrieve_body($response_discord);
                    
                    /* translators: %s: Discord webhook request failed message */
                    $actions_success['discord'] = ($response_code === 204) 
                    ? ($response['data'] .= __('Message sent successfully to Discord.','tpgbp')) 
                    : ($errors .= $response_message = sprintf(__('Failed to send message to Discord. Response: %s', 'tpgbp'), $response_message));

                }
            }
        }
        
        
        if (in_array('ActiveCampaign', $actionValues)) {
                    
            $actions_success['activecampaign'] = false;
        
            $activecampaign_file_path = plugin_dir_path(__FILE__) . '../includes/nxt-activecampaign.php';
            include $activecampaign_file_path;		
            $c_api_key = isset($first_decode['cApiKey']) && !empty($first_decode['cApiKey']) ? sanitize_text_field($first_decode['cApiKey']) : '';
            $c_api_url = isset($first_decode['cApiUrl']) && !empty($first_decode['cApiUrl']) ? esc_url_raw($first_decode['cApiUrl']) : '';

            $active_campaign = new nxt_activecampaign_action_callback($c_api_key, $c_api_url);
        
            $subscriber_data = [
                'contact' => [
                    'email' => $email ?? '',
                    'firstName' => $name ?? '',
                    'phone' => $phone ?? '',
                ],
            ];			
        
            $create_response = $active_campaign->create_subscriber($subscriber_data);
        
            if (is_wp_error($create_response)) {
                $error_message = $create_response->get_error_message();
                /* translators: %s: ActiveCampaign API error message */
                $errors .= sprintf( __('Failed to connect to ActiveCampaign API. Error: %s', 'tpgbp'), $error_message );
            } else {
                $actions_success['activecampaign'] = isset($create_response['contact']['id']) 
                ? ($response['data'] .= __('Contact added to ActiveCampaign successfully.','tpgbp')) 
                : ($response['data'] .= __('Failed to add contact to ActiveCampaign. Check response details.','tpgbp'));

            }
        }

        if (in_array('MailerLite', $actionValues)) {
        
            $mailerlite_file_path = plugin_dir_path(__FILE__) . '../includes/nxt-mailerlite.php';
            include $mailerlite_file_path;
        
        
            if (isset($first_decode['mApiKey'], $first_decode['mGrpId'])) {
                $api_key = sanitize_text_field($first_decode['mApiKey']);
                $group_id = sanitize_text_field($first_decode['mGrpId']);
        
                $mailerLite = new Nexter_MailerLiteIntegration($api_key);
        
                $subscriber_data = [
                    'email' => $email,
                    'name' => $name,
                    'groups' => [$group_id] 
                ];
        
                $result = $mailerLite->addSubscriber($subscriber_data);
        
                if (is_wp_error($result)) {
                    $errors .= $result->get_error_message() . "\n"; 
                } else {
                    if (isset($result['id'])) { 
                        $response['success'] = true;
                        $response['data'] = __('Subscriber added successfully.','tpgbp');
                        $actions_success['mailerLite'] = true; 
                    } else {
                        $response['data'] = __('Failed to add subscriber. Check response details.','tpgbp');
                    }
                }
            } else {
                $errors .= __('API key, email, name, and group ID are required fields.','tpgbp'); 
            }

            $response['data'] = !empty($errors) ? $errors : $response['data'];

        }

        if (in_array('Drip', $actionValues)) { 

            $drip_file_path = plugin_dir_path(__FILE__) . '../includes/nxt-drip.php';
            include $drip_file_path;
                
            if (isset($first_decode['dAccId'], $first_decode['dApikey'])) {
                $account_id = sanitize_text_field($first_decode['dAccId']);
                $api_key = sanitize_text_field($first_decode['dApikey']);
        
                $drip = new Nexter_DripIntegration($account_id, $api_key);
                $subscriber_data = [
                    'subscribers' => [
                        [
                            'email' => $email,
                            'first_name' => $name,
                            'phone' => $phone,
                        ]
                    ]	
                ];
        
                $result = $drip->addSubscriber($subscriber_data);
        
                if (is_wp_error($result)) {
                    $errors .= $result->get_error_message() . "\n"; 
                } else {
                    if (isset($result['subscribers'][0]['id'])) {
                        $response['success'] = true;
                        $response['data'] = __('Subscriber added successfully.','tpgbp');
                        $actions_success['drip'] = true; 
                    } else {
                        $errors .= __('Failed to add subscriber. Check response details.','tpgbp'); 
                    }
                }
            } else {
                $errors .= __('Account ID, API key, email, and name are required fields.','tpgbp'); 
            }
        
            !empty($errors) ?  $response['data'] = $errors : null;

        }
        
        if (in_array('ConvertKit', $actionValues)) {
        
            $ktapi_key = sanitize_text_field($first_decode['ktApiKey'] ?? '');
            $selected_form_id = sanitize_text_field($first_decode['convertkitformoption'] ?? '');
        
            $url = "https://api.convertkit.com/v3/forms/{$selected_form_id}/subscribe?api_key={$ktapi_key}";
                
            $headers = [
                'Content-Type' => 'application/json',
            ];
        
            $payload = json_encode([
                'email' => $email,
                'first_name' => $name,
                'phone' => $phone,
            ]);
                
            $args = [
                'method'    => 'POST',
                'body'      => $payload,
                'headers'   => $headers,
                'timeout'   => 45,
            ];
                
            $response = wp_remote_post($url, $args);
        
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                /* translators: %s: ConvertKit API error message */
                $errors = sprintf( __( 'Failed to connect to ConvertKit API. Error: %s', 'tpgbp' ), $error_message );
            } else {
                $status_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);
        
                $response_data = json_decode($response_body, true);
        
                if ($status_code === 200) {
                    $response_data['success'] = true;
                    $response_data['data'] = __('Subscriber added successfully to ConvertKit.','tpgbp');
                    $actions_success['convertkit'] = true;
                } else {
                    $errors .= __('Failed to submit form to ConvertKit. Please check your input and try again.','tpgbp');
                }
            }
        
            if (!empty($errors)) {
                $response_data['data'] = $errors;
            }
        }
        
        
        if (in_array('GetResponse', $actionValues)) {

            $getResApiKey = sanitize_text_field($first_decode['getResApiKey'] ?? '');
            $getRetkn = sanitize_text_field($first_decode['getRetkn'] ?? '');
        
            $url = "https://api.getresponse.com/v3/contacts";
        
            $headers = [
                'X-Auth-Token' => 'api-key ' . $getResApiKey,
                'Content-Type' => 'application/json',
            ];
        
            $payload = json_encode([
                'email' => $email,
                'campaign' => [
                    'campaignId' => $getRetkn,
                ],
                'name' => $name,
            ]);
        
            $args = [
                'method' => 'POST',
                'headers' => $headers,
                'body' => $payload,
            ];
        
            $response = wp_remote_post($url, $args);
                
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                /* translators: %s: GetResponse API error message */
                $errors = sprintf( __('Failed to connect to GetResponse API. Error: %s', 'tpgbp'), $error_message );
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);
        
                if ($response_code === 202) {
                    $response_data['success'] = true;
                    $response_data['data'] = __('Form submitted successfully to GetResponse.','tpgbp');
                    $actions_success['getresponse'] = true; 
                } else {
                    $errors = __('Failed to submit form to GetResponse. Please check your input and try again.','tpgbp');
                }
            }
        
            !empty($errors) ? $response_data['data'] = $errors : null;

        
        }
                
        if (in_array('Mailchimp', $actionValues)) {
            $mc_api_key = sanitize_text_field($first_decode['mailchmpApiki'] ?? '');
            $list_id = sanitize_text_field($first_decode['mailchmpAud'] ?? '');
        
            if (strpos($mc_api_key, '-') === false) {
                $errors .= __('Invalid Mailchimp API key format.','tpgbp');
                return;
            }
        
            $data_center = substr($mc_api_key, strpos($mc_api_key, '-') + 1);
            if (empty($data_center)) {
                $errors .= __('Mailchimp API key is missing the data center.','tpgbp');
                return;
            }
        
            $url = "https://{$data_center}.api.mailchimp.com/3.0/lists/{$list_id}/members";
        
            $headers = [
                'Authorization' => 'apikey ' . $mc_api_key,
                'Content-Type' => 'application/json',
            ];
        
            $payload = json_encode([
                'email_address' => $email,
                'status' => 'subscribed',
                'merge_fields' => [
                    'FNAME' => $name,
                ],
            ]);
        
            $args = [
                'method' => 'POST',
                'headers' => $headers,
                'body' => $payload,
            ];
        
            $response = wp_remote_post($url, $args);
        
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                /* translators: %s: Mailchimp API error message */
                $errors .= sprintf( __('Failed to connect to Mailchimp API. Error: %s', 'tpgbp'), $error_message );
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);
                $response_data = json_decode($response_body, true);
        
                if ($response_code >= 200 && $response_code < 300) {
                    $response_data['success'] = true;
                    $response_data['data'] = __('Subscriber added successfully to Mailchimp.','tpgbp');
                    $actions_success['mailchimp'] = true;
                } else {
                    $errors .= __("Mailchimp API error: {$response_data['detail']}",'tpgbp');
                }
            }
        
            if (!empty($errors)) {
                $response_data['data'] = $errors;
            }
        }
        
        
        if (in_array('Brevo', $actionValues)) {

            $brevoApiKey = isset($first_decode['brevoApiKey']) && !empty($first_decode['brevoApiKey']) ? sanitize_text_field($first_decode['brevoApiKey']) : '';		
            $contactData = [
                'email' => $email, 
                'attributes' => [
                    'FIRSTNAME' => $name, 
                ],
            ];
            
            $url = 'https://api.brevo.com/v3/contacts';
            
            $headers = [
                'Content-Type' => 'application/json',
                'api-key' => $brevoApiKey,
            ];
            
            $payload = json_encode($contactData);
            
            $args = [
                'method' => 'POST',
                'headers' => $headers,
                'body' => $payload,
            ];
            
            $response = wp_remote_post($url, $args);            
        
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $errors .= 'Request failed: ' . $error_message; 
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);
        
                if ($response_code !== 201) {
                    $errors .= __('Failed to add contact. Please try again later.','tpgbp');
                } else {
                    $response_data['data'] = __('Contact added successfully','tpgbp');
                    $actions_success['brevo'] = true;
                }
            }
        }
        
        $response['success'] = empty($errors);
        $response['data'] = empty($errors) ? 'Success' : $errors;
        echo wp_json_encode($response);
        wp_die();
    }

    /**
     * Replace Dynamic URL
     * @since 4.3.2
     */
    public static function nxt_replace_dynamic_url($block_content, $block) {
		global $repeater_index; 

		if (preg_match('/tpgb-dynamicurl=(.*?)\!#/', $block_content, $matches)) {

			if (!isset($block['attrs'][$matches[1]]['dynamic']['dynamicUrl'])) {
				return $block_content;
			}
			$oldHref = $block['attrs'][$matches[1]]['dynamic']['dynamicUrl'];

			if (strpos($oldHref, '|') !== false) {
				$dynamicFieldParts = explode('|', $oldHref);

				if (!empty($dynamicFieldParts) && count($dynamicFieldParts) === 5 || count($dynamicFieldParts) === 7) {
					$fieldName = $dynamicFieldParts[1] ?? 'Unknown Field';
					$repFunction = apply_filters('tp_get_repeater_data', $dynamicFieldParts);

					if (is_wp_error($repFunction)) {
                        // Handle the error as needed, e.g., skip or log
                    } elseif (isset($repFunction['repeater_data'][$repeater_index][$fieldName])) {
						$newHref = $repFunction['repeater_data'][$repeater_index][$fieldName];
						$block_content = preg_replace('/<a\s+href="([^"]+)"/', '<a href="' . esc_url($newHref) . '"', $block_content);
					} 
				} 
			} 
		} 
	
		return $block_content;
	}

    /**
     * Process Dynamic Repeater Field
     * @since 4.4.1
     */

    public static function nxt_process_dynamic_repeater_field( $dynamicField, $rep_Index, $dynamic_entry, $block_content ) {
        if ( strpos( $dynamicField, '|' ) !== false ) {
            $dynamicFieldParts = explode( '|', $dynamicField );

            if ( count( $dynamicFieldParts ) === 5 || count( $dynamicFieldParts ) === 7 ) {
                $fieldName    = $dynamicFieldParts[1] ?? 'Unknown Field';
				$repFunction  = apply_filters( 'tp_get_repeater_data', $dynamicFieldParts );
                $replacementValue = '';
				if (!is_wp_error($repFunction) && is_array($repFunction) && isset($repFunction['repeater_data'][$rep_Index][$fieldName])) {
                    $replacementValue = $repFunction['repeater_data'][$rep_Index][$fieldName];
                }
                // Replace in block content
                return str_replace( $dynamic_entry, $replacementValue, $block_content );
            }
        }

        // Return unchanged if conditions don't match
        return $block_content;
    }

    /**
     * Mailchimp Interests
     * @since 4.5.0
     */
    public function nxt_mailchimp_interests() {
        $connection_data = get_option('tpgb_connection_data', []); 
        $mail_chimp_api_key = isset($connection_data['mailchimp_api']) ? $connection_data['mailchimp_api'] : ''; 
        $mail_chimp_api_id = isset($connection_data['mailchimp_id']) ? $connection_data['mailchimp_id'] : ''; 
        $interests_array = [];  

        if(!empty($mail_chimp_api_key) && !empty($mail_chimp_api_id)) { 
            $dc_parts = explode('-', $mail_chimp_api_key); 
            if(isset($dc_parts[1])) { 
                $dc = $dc_parts[1]; 
                $mailchimp_api_url = 'https://'. $dc .'.api.mailchimp.com/3.0/lists/'.$mail_chimp_api_id.'/interest-categories';  

                $response = wp_remote_get($mailchimp_api_url, [ 
                    'headers' => [ 
                        'Authorization' => 'Basic ' . base64_encode('user:' . $mail_chimp_api_key) 
                    ], 
                    'timeout' => 15 
                ]);  

                if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) { 
                    $body = wp_remote_retrieve_body($response); 
                    $lists = json_decode($body); 
                    
                    if (!empty($lists) && !empty($lists->categories)) { 
                        foreach ($lists->categories as $category) { 
                            if(!empty($category->id)) { 
                                $interests_url = 'https://'. $dc .'.api.mailchimp.com/3.0/lists/'.$mail_chimp_api_id.'/interest-categories/'.$category->id.'/interests'; 
                                $interests_response = wp_remote_get($interests_url, [ 
                                    'headers' => [ 
                                        'Authorization' => 'Basic ' . base64_encode('user:' . $mail_chimp_api_key) 
                                    ], 
                                    'timeout' => 15 
                                ]);  

                                if (!is_wp_error($interests_response) && wp_remote_retrieve_response_code($interests_response) === 200) { 
                                    $interests_body = wp_remote_retrieve_body($interests_response); 
                                    $interests = json_decode($interests_body);  

                                    if (!empty($interests) && !empty($interests->interests)) { 
                                        foreach ($interests->interests as $interest) { 
                                            if(!empty($interest->id) && !empty($interest->name)) { 
                                                $interests_array[] = [ 
                                                    'value' => $interest->id, 
                                                    'label' => $interest->name 
                                                ]; 
                                            } 
                                        } 
                                    } 
                                } else { 
                                    $error_message = is_wp_error($interests_response) ? $interests_response->get_error_message() : 'Invalid response code: ' . wp_remote_retrieve_response_code($interests_response); 
                                    error_log('MailChimp Interests API Error: ' . $error_message); 
                                } 
                            } 
                        } 
                    } 
                } else { 
                    $error_message = is_wp_error($response) ? $response->get_error_message() : 'Invalid response code: ' . wp_remote_retrieve_response_code($response); 
                    error_log('MailChimp Categories API Error: ' . $error_message); 
                } 
            } else { 
                error_log('MailChimp API Key format is invalid'); 
            } 
        }

        return $interests_array;
    }
}

Tpgbp_Pro_Blocks_Helper::get_instance();