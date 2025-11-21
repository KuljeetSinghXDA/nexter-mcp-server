<?php
/**
 * Nexter Blocks Loader.
 * @since 1.0.0
 * @package Tpgb_Gutenberg_Loader
 */
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( !class_exists( 'Tpgb_Gutenberg_Loader' ) ) {
    
    /**
     * Class Tpgb_Gutenberg_Loader.
     */
    final class Tpgb_Gutenberg_Loader {
        
        /**
         * Member Variable
         *
         * @var instance
         */
        private static $instance;
        
        public $post_assets_objects = array();

        /**
         *  Initiator
         */
        public static function get_instance() {
            if ( !isset( self::$instance ) ) {
                self::$instance = new self;
            } 
            return self::$instance;
        }
        
        /**
         * Constructor
         */
        public function __construct() {
            
            $this->loader_helper();

            add_action( 'plugins_loaded', array( $this, 'tp_plugin_loaded' ) );

            if( is_admin() ){
                require_once TPGB_PATH . 'classes/tp-admin.php';
            }
        }
        
        /**
         * Loads Helper/Other files.
         *
         * @since 1.0.0
         *
         * @return void
         */
        public function loader_helper() {			
			$option_name='default_tpgb_load_opt';
			$value='1';
			if ( is_admin() && get_option( $option_name ) !== false ) {
			} else if( is_admin() ){
				$default_load=get_option( 'tpgb_normal_blocks_opts' );
				if ( $default_load !== false && $default_load!='') {
					$autoload = 'no';
					add_option( $option_name,$value, '', $autoload );
				}else{
					$tpgb_normal_blocks_opts=get_option( 'tpgb_normal_blocks_opts' );
                    if($tpgb_normal_blocks_opts === false){
                        $tpgb_normal_blocks_opts = [];
                    }
					$tpgb_normal_blocks_opts['enable_normal_blocks']= array("tp-accordion","tp-breadcrumbs","tp-blockquote","tp-button-core","tp-countdown","tp-container","tp-data-table","tp-draw-svg","tp-empty-space","tp-flipbox","tp-google-map","tp-heading","tp-hovercard","tp-icon-box","tp-infobox","tp-image","tp-messagebox","tp-number-counter","tp-post-listing","tp-pricing-list","tp-pricing-table","tp-pro-paragraph","tp-progress-bar","tp-stylist-list","tp-social-icons","tp-tabs-tours","tp-testimonials","tp-video");
                    
                    $tpgb_normal_blocks_opts['tp_extra_option']= ['tp-global-block-style','tp-advanced-border-radius','tp-display-rules','tp-equal-height','tp-event-tracking','tp-magic-scroll','tp-global-tooltip','tp-continuous-animation','tp-content-hover-effect','tp-mouse-parallax','tp-3d-tilt','tp-scoll-animation'];
					
					$autoload = 'no';
					add_option( 'tpgb_normal_blocks_opts',$tpgb_normal_blocks_opts, '', $autoload );
					add_option( $option_name,$value, '', $autoload );
                    $action_delay = 'tpgb_delay_css_js';
                    if ( false === get_option($action_delay) ){
                        add_option( $action_delay, 'true' );
                    }
                    $action_defer = 'tpgb_defer_css_js';
                    if ( false === get_option($action_defer) ){
                        add_option( $action_defer, 'true' );
                    }

                    // Add option For Init Nexter Block Version
                    $nxtData = [
                        "install-version" => TPGB_VERSION , 
                        "install-date" => date('d-m-Y') 
                    ];
                    add_option( 'nexter-installed-data', $nxtData );

				}
			}
			
			//Load Conditions Rules
			require_once TPGB_PATH . 'classes/extras/tpgb-conditions-rules.php';

            // Reusable Short code
            require_once TPGB_PATH . 'classes/extras/tpag-reusable-shortcode.php';

            if( is_admin() ){

                // Rollback
                require TPGB_PATH . 'includes/rollback.php';

                // What's New Notification
                require TPGB_PATH . 'classes/extras/nexter-block-whats-new.php';

                // Dashboard Options
                require TPGB_PATH . 'includes/plus-settings-options.php';
                
                // Plugin Deactive Popup
                require_once TPGB_PATH . 'classes/extras/tpag-deactive.php';
            }

            require_once TPGB_PATH . 'classes/tp-block-helper.php';
        }
        
        /*
         * Files load plugin loaded.
         *
         * @since 1.1.3
         *
         * @return void
         */
        public function tp_plugin_loaded() {
            $this->load_textdomain();
            require_once TPGB_PATH . 'classes/tp-generate-block-css.php';

            require_once TPGB_PATH . 'classes/tp-get-blocks.php';
            require_once TPGB_PATH . 'classes/tp-core-init-blocks.php';
            
            if(defined('AGNI_PLUGIN_URL') || class_exists( 'AgniBuilder' )){
                require_once TPGB_PATH . 'classes/extras/compatibility/class-tpag-cartify.php';
            }

            // wdkit widget api
            require_once TPGB_PATH . 'classes/extras/tpgb-wdk-widgets-api.php';
        }
        
        /**
         * Load Nexter Blocks Text Domain.
         * Text Domain : the-plus-addons-for-block-editor
         * @since  1.0.0
         * @return void
         */
        public function load_textdomain() {
            load_plugin_textdomain( 'the-plus-addons-for-block-editor', false, TPGB_BDNAME . '/languages/' );
        }
        
        /**
         * If Check Gutenberg is installed
         *
         * @since 1.0.0
         *
         * @param string $plugin_url Plugin path.
         * @return boolean true | false
         * @access public
         */
        public function check_gutenberg_installed( $plugin_url ) {
            $get_plugins = get_plugins();
            return isset( $get_plugins[ $plugin_url ] );
        }
    }
    
    Tpgb_Gutenberg_Loader::get_instance();

    function tpgb_load_data() {
        return Tpgb_Gutenberg_Loader::get_instance();
    }
}