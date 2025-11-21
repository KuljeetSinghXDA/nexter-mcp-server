<?php
/**
 * Nexter Blocks Pro Loader.
 * @since 1.0.0
 * @package TPGBP_Gutenberg_Pro_Loader
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( !class_exists( 'TPGBP_Gutenberg_Pro_Loader' ) ) {
    
    /**
     * Class TPGBP_Gutenberg_Pro_Loader.
     */
    final class TPGBP_Gutenberg_Pro_Loader {
        
        /**
         * Constructor
         */
        public function __construct() {
		
			$this->load_textdomain();
			
            if(is_admin()){
                require TPGBP_PATH . 'classes/tpgb-pro-admin.php';
            }

            $this->loader_helper();
			
        }
        
        /**
         * Loads Helper/Other files.
         *
         * @since 1.0.0
         *
         * @return void
         */
        public function loader_helper() {

            if(is_admin()){
                require TPGBP_PATH . 'includes/rollback.php';
                require TPGBP_PATH . 'includes/plus-settings-options.php';
            }
			
			//Load Conditions Rules
			require_once TPGBP_PATH . 'classes/extras/tpgb-conditions-rules.php';
            
            //Load Nexter Extras Opt
            require_once TPGBP_PATH . 'classes/extras/tpgb-plus-extras.php';

			//Load Magic Scroll
			require_once TPGBP_PATH . 'classes/extras/tpgb-magic-scroll.php';
            
			//Load Init Blocks Files
			require_once TPGBP_PATH . 'classes/tp-block-helper.php';
			
			//Load Plugin Wp Enqueue Scripts and Styles
            require_once TPGBP_PATH . 'classes/tp-core-init-blocks.php';
			
            //Login Register Custom ajax
            require_once TPGBP_PATH . 'classes/tp-custom-ajax.php';
        }
        
        /**
         * Load Nexter Blocks Pro Text Domain.
         * Text Domain : tpgbp
         * @since  1.0.0
         * @return void
         */
        public function load_textdomain() {
            load_plugin_textdomain( 'tpgbp', false, TPGBP_BDNAME . '/languages/' );
        }
    }
    
    new TPGBP_Gutenberg_Pro_Loader();
}