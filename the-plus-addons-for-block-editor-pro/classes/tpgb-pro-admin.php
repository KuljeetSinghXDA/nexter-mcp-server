<?php
/**
 * Tpgb Admin.
 *
 * @package Tpgb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'Tpgb_Pro_Admin' ) ) {

	/**
	 * Class UAGB_Admin.
	 */
	final class Tpgb_Pro_Admin {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 *  Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
            $whitedata = get_option('tpgb_white_label');
            
            if( ( empty($whitedata) || ( !empty($whitedata['nxt_help_link']) && $whitedata['nxt_help_link'] !== 'on') ) ) {
				add_filter( 'plugin_action_links_' . TPGBP_BASENAME, array( $this, 'tpgbp_add_settings_link' ) );
				add_filter( 'plugin_row_meta', array( $this, 'tpgbp_extra_links_plugin_row_meta' ), 10, 2 );
            }

            add_action('wp_ajax_Tp_delete_transient', array($this, 'Tp_delete_transient'));
			add_action('wp_ajax_nopriv_Tp_delete_transient', array($this, 'Tp_delete_transient'));

            /* Custom Link url attachment Media */
			add_filter( 'attachment_fields_to_edit', [$this, 'tpgb_attachment_field_media'],  10, 2  );
			add_filter( 'attachment_fields_to_save', [$this, 'tpgb_attachment_field_save'] , 10, 2 );

            add_action( 'admin_enqueue_scripts', array($this, 'tpgbp_admin_enqueue_scripts') );

            add_filter( 'all_plugins', array($this,'tpgb_free_white_label_plugin') );
            add_filter( 'all_plugins', array($this,'tpgb_pro_white_label_plugin') );
        }

        /**
		 * Adds Links to the plugins page.
		 * @since 2.0.0
		 */
		public function tpgbp_add_settings_link( $links ) {
            $nxtProlink = [];
            if ( current_user_can( 'manage_options' ) ) {
                $nxtProlink[] = sprintf( '<a href="%s" rel="noopener noreferrer">%s</a>', admin_url( 'admin.php?page=nexter_welcome_page') , __( 'Settings', 'tpgbp' ) );
            }
			// Need Help link.
            $nxtProlink[] = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url('https://store.posimyth.com/get-support-nexterwp/?utm_source=wpbackend&utm_medium=admin&utm_campaign=pluginpage'), __( 'Need Help?', 'tpgbp' ) );

            // Licence Activate
            $nxtProlink[] = sprintf( '<a href="%s" style="color:green;font-weight:600;">%s</a>',  admin_url( 'admin.php?page=nexter_welcome_page#/activate_PRO' ), __( 'License', 'tpgbp' ) );
           

			return array_merge( $nxtProlink,$links );
		}

        /**
		 * Adds Extra Links to the plugins row meta.
		 * @since 1.1.0
		 */
		public function tpgbp_extra_links_plugin_row_meta( $plugin_meta, $plugin_file ) {
			if ( strpos( $plugin_file, TPGBP_BASENAME ) !== false && current_user_can( 'manage_options' ) ) {
				$new_links = array(
						'docs' => '<a href="'.esc_url('https://nexterwp.com/docs/?utm_source=wpbackend&utm_medium=admin&utm_campaign=pluginpage').'" target="_blank" rel="noopener noreferrer" style="color:green;">'.esc_html__( 'Docs', 'tpgbp' ).'</a>',
                        'video-tutorials' => '<a href="'.esc_url('https://www.youtube.com/c/POSIMYTHInnovations/?sub_confirmation=1').'" target="_blank" rel="noopener noreferrer">'.esc_html__( 'Video Tutorials', 'tpgbp' ).'</a>',
						'join-community' => '<a href="'.esc_url('https://www.facebook.com/groups/nexterwpcommunity/').'" target="_blank" rel="noopener noreferrer">'.esc_html__( 'Join Community', 'tpgbp' ).'</a>',
						'whats-new' => '<a href="'.esc_url('https://roadmap.nexterwp.com/updates?filter=Nexter+Blocks+-+PRO').'" target="_blank" rel="noopener noreferrer" style="color: orange;">'.esc_html__( 'What\'s New?', 'tpgbp' ).'</a>',
						'req-feature' => '<a href="'.esc_url('https://roadmap.nexterwp.com/boards/feature-requests/').'" target="_blank" rel="noopener noreferrer">'.esc_html__( 'Request Feature', 'tpgbp' ).'</a>',
						'rate-theme' => '<a href="'.esc_url('https://wordpress.org/support/plugin/the-plus-addons-for-block-editor/reviews/?filter=5').'" target="_blank" rel="noopener noreferrer">'.esc_html__( 'Rate 5 Stars', 'tpgbp' ).'</a>'
						);
				 
				$plugin_meta = array_merge( $plugin_meta, $new_links );
			}
			 
			return $plugin_meta;
		}

        /*
        * Remove Cache Transient Data
        * @since 1.3.0
        */
        public function Tp_delete_transient() {
            $result = [];
            check_ajax_referer('tpgb-addons', 'tpgb_nonce');
            if ( ! current_user_can( 'edit_posts' ) ) {
                wp_die( 'You can not Permission.' );
            }
            global $wpdb;
                $table_name = $wpdb->prefix . "options";
                $DataBash = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %s", $table_name ) );
                $blockName = !empty($_POST['blockName']) ? sanitize_text_field(wp_unslash($_POST['blockName'])) : '';
                
                if($blockName == 'SocialFeed'){
                    $transient = array(
                        // facebook
                            'Fb-Url-','Fb-Time-','Data-Fb-',
                        // vimeo
                            'Vm-Url-', 'Vm-Time-', 'Data-Vm-',
                        // Instagram basic
                            'IG-Url-', 'IG-Profile-', 'IG-Time-', 'Data-IG-',	
                        // Instagram Graph
                            'IG-GP-Url-', 'IG-GP-Time-', 'IG-GP-Data-', 'IG-GP-UserFeed-Url-', 'IG-GP-UserFeed-Data-', 'IG-GP-Hashtag-Url-', 'IG-GP-HashtagID-data-', 'IG-GP-HashtagData-Url-', 'IG-GP-Hashtag-Data-', 'IG-GP-story-Url-', 'IG-GP-story-Data-', 'IG-GP-Tag-Url-', 'IG-GP-Tag-Data-',
                        // Tweeter
                            'Tw-BaseUrl-', 'Tw-Url-', 'Tw-Time-', 'Data-tw-',
                        // Youtube
                            'Yt-user-', 'Yt-user-Time-', 'Data-Yt-user-', 'Yt-Url-', 'Yt-Time-', 'Data-Yt-', 'Yt-C-Url-', 'Yt-c-Time-', 'Data-c-Yt-',
                        // loadmore
                            'SF-Loadmore-',
                        // Performance
                            'SF-Performance-'
                    );
                }else if($blockName == 'SocialReview'){
                    $transient = array(
                        // Facebook
                            'Fb-R-Url-', 'Fb-R-Time-', 'Fb-R-Data-',
                        // Google
                            'G-R-Url-', 'G-R-Time-', 'G-R-Data-',
                        // loadmore
                            'SR-LoadMore-',
                        // Performance
                            'SR-Performance-',
                        // Beach
                            'Beach-Url-', 'Beach-Time-', 'Beach-Data-',
                    );
                }
                foreach ($DataBash as $First) {
                    foreach ($transient as $second) {
                        $Find_Transient = !empty($First->option_name) ? strpos( $First->option_name, $second ) : '';
                        if(!empty($Find_Transient)){
                            $wpdb->delete( $table_name, array( 'option_name' => $First->option_name ) );
                        }
                    }
                }
                
            $result['success'] = 1;
            $result['blockName'] = $blockName;
            echo wp_send_json($result);
        }

        /*
        * Attachment Media Image Url Field
        * @since 1.3.0
        */
        public function tpgb_attachment_field_media( $form_fields, $post ) {
            $form_fields['tpgb-gallery-url'] = array(
                'label' => esc_html__('Custom URL','tpgbp'),
                'input' => 'url',
                'value' => get_post_meta( $post->ID, 'tpgb_gallery_url', true ),
                'helps' => esc_html__('Gallery Listing Widget Used Custom Url Media','tpgbp'),
            );
            return $form_fields;
        }
        
        /*
        * Attachment Media Custom Url Field Save
        * @since 1.3.0
        */
        public function tpgb_attachment_field_save( $post, $attachment ) {
            if( isset( $attachment['tpgb-gallery-url'] ) )
                update_post_meta( $post['ID'], 'tpgb_gallery_url', esc_url( $attachment['tpgb-gallery-url'] ) ); 
            
            return $post;
        }

        /*
        * Admin Enqueue Scripts
        *
        * @since 1.0.0
        */
        public function tpgbp_admin_enqueue_scripts( $hook ){
            if ( 'edit-comments.php' === $hook ) {
                return;
            }
            wp_enqueue_script( 'tpgb-pro-admin', TPGBP_URL . 'assets/js/admin/tpgb-pro-admin.min.js',array() , TPGBP_VERSION, true );
        }

        public function get_white_label_option($field){
            $label_options = get_option( 'tpgb_white_label' );	
                $values='';
                if(isset($label_options[$field]) && !empty($label_options[$field])){
                    $values=$label_options[$field];
                }	
            return $values;
        }

        /* TPGB Free Update white label
        * @since 1.0.0
        */
        public function tpgb_free_white_label_plugin( $all_plugins ){
            $plugin_name = $this->get_white_label_option('tpgb_free_plugin_name');
            $tpgb_plugin_desc = $this->get_white_label_option('tpgb_free_plugin_desc');
            $tpgb_author_name = $this->get_white_label_option('tpgb_free_author_name');
            $tpgb_author_uri = $this->get_white_label_option('tpgb_free_author_uri');
            
            if(!empty($all_plugins[TPGB_BASENAME]) && is_array($all_plugins[TPGB_BASENAME])){
                $all_plugins[TPGB_BASENAME]['Name']           = ! empty( $plugin_name )     ? $plugin_name      : $all_plugins[TPGB_BASENAME]['Name'];
                $all_plugins[TPGB_BASENAME]['PluginURI']      = ! empty( $tpgb_author_uri )      ? $tpgb_author_uri       : $all_plugins[TPGB_BASENAME]['PluginURI'];
                $all_plugins[TPGB_BASENAME]['Description']    = ! empty( $tpgb_plugin_desc )     ? $tpgb_plugin_desc      : $all_plugins[TPGB_BASENAME]['Description'];
                $all_plugins[TPGB_BASENAME]['Author']         = ! empty( $tpgb_author_name )   ? $tpgb_author_name    : $all_plugins[TPGB_BASENAME]['Author'];
                $all_plugins[TPGB_BASENAME]['AuthorURI']      = ! empty( $tpgb_author_uri )      ? $tpgb_author_uri       : $all_plugins[TPGB_BASENAME]['AuthorURI'];
                $all_plugins[TPGB_BASENAME]['Title']          = ! empty( $plugin_name )     ? $plugin_name      : $all_plugins[TPGB_BASENAME]['Title'];
                $all_plugins[TPGB_BASENAME]['AuthorName']     = ! empty( $tpgb_author_name )   ? $tpgb_author_name    : $all_plugins[TPGB_BASENAME]['AuthorName'];

                return $all_plugins;
            }
        }
        
        /* TPGB Pro Update white label
        * @since 1.0.0
        */
        public function tpgb_pro_white_label_plugin( $all_plugins ){
            $plugin_name = $this->get_white_label_option('tpgb_plugin_name');
            $tpgb_plugin_desc = $this->get_white_label_option('tpgb_plugin_desc');
            $tpgb_author_name = $this->get_white_label_option('tpgb_author_name');
            $tpgb_author_uri = $this->get_white_label_option('tpgb_author_uri');
            
            if(!empty($all_plugins[TPGBP_BASENAME]) && is_array($all_plugins[TPGBP_BASENAME])){
                $all_plugins[TPGBP_BASENAME]['Name']           = ! empty( $plugin_name )     ? $plugin_name      : $all_plugins[TPGBP_BASENAME]['Name'];
                $all_plugins[TPGBP_BASENAME]['PluginURI']      = ! empty( $tpgb_author_uri )      ? $tpgb_author_uri       : $all_plugins[TPGBP_BASENAME]['PluginURI'];
                $all_plugins[TPGBP_BASENAME]['Description']    = ! empty( $tpgb_plugin_desc )     ? $tpgb_plugin_desc      : $all_plugins[TPGBP_BASENAME]['Description'];
                $all_plugins[TPGBP_BASENAME]['Author']         = ! empty( $tpgb_author_name )   ? $tpgb_author_name    : $all_plugins[TPGBP_BASENAME]['Author'];
                $all_plugins[TPGBP_BASENAME]['AuthorURI']      = ! empty( $tpgb_author_uri )      ? $tpgb_author_uri       : $all_plugins[TPGBP_BASENAME]['AuthorURI'];
                $all_plugins[TPGBP_BASENAME]['Title']          = ! empty( $plugin_name )     ? $plugin_name      : $all_plugins[TPGBP_BASENAME]['Title'];
                $all_plugins[TPGBP_BASENAME]['AuthorName']     = ! empty( $tpgb_author_name )   ? $tpgb_author_name    : $all_plugins[TPGBP_BASENAME]['AuthorName'];

                return $all_plugins;
            }
        }
    }

    Tpgb_Pro_Admin::get_instance();

}