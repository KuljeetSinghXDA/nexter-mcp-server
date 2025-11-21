<?php 
/**
 * TPGB Pro Settings Options
 * @since 1.0.0
 *
 */
if (!defined('ABSPATH')) {
    exit;
}

class TPgb_Pro_Gutenberg_Settings_Options {
	
	/**
     * Constructor
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'admin_head', [ $this, 'tpgb_plus_icon_logo' ] );
        add_action( 'wp_ajax_tpgb_white_label_save', array( $this,'tpgb_white_label_save_action') );

        add_action( 'wp_ajax_tpgb_white_label_reset', array( $this,'tpgb_white_label_reset_action') );
    

		include_once TPGBP_INCLUDES_URL . 'plus-library/tpgb-library.php';
		Tpgb_Pro_Library::get_instance();
    }
	
	public function tpgb_plus_icon_logo(){
		$tpgb_white_label = get_option( 'tpgb_white_label' );
		if(!empty($tpgb_white_label['tpgb_plus_logo'])){
			?>
			<style>.wp-menu-image.dashicons-before.dashicons-tpgb-plus-settings{background: url(<?php echo esc_url($tpgb_white_label['tpgb_plus_logo']); ?>);background-size: 22px;background-repeat: no-repeat;background-position: center;}</style>
		<?php }
	}
	
	public function tpgb_white_label_save_action(){
		$action_page = 'tpgb_white_label';
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(['Success' => false]);
		}
		if(isset($_POST["submit-key"]) && !empty($_POST["submit-key"]) && $_POST["submit-key"]=='Save'){

			if ( ! isset( $_POST['nonce_tpgb_white_label'] ) || ! wp_verify_nonce( sanitize_key($_POST['nonce_tpgb_white_label']), 'tpgb-dash-ajax-nonce' ) ) {
				wp_send_json(['Success' => false]);
			} else {
				$getArr = $_POST;
				unset($getArr['nonce_tpgb_white_label']);
				unset($getArr['_wp_http_referer']);
				unset($getArr['action']);
				unset($getArr['submit-key']);

				if( isset($getArr['fields']) && !empty($getArr['fields']) ){
					$getArr = json_decode(stripslashes(  $getArr['fields'] ),true);
				}
				if ( FALSE === get_option($action_page) ){
					add_option($action_page,$getArr);
					wp_send_json(['Success' => true]);
				}else{
					update_option( $action_page, $getArr );
					wp_send_json(['Success' => true]);
				}
			}

		}else{
			wp_send_json(['Success' => false]);
		}

	}

	public function tpgb_white_label_reset_action(){
		if(isset($_POST["submit-key"]) && !empty($_POST["submit-key"]) && $_POST["submit-key"]=='reset'){

			if ( ! isset( $_POST['nonce_tpgb_white_label'] ) || ! wp_verify_nonce( sanitize_key($_POST['nonce_tpgb_white_label']), 'tpgb-dash-ajax-nonce' ) ) {
				wp_send_json(['Success' => false]);
			} else {
				delete_option('tpgb_white_label');
				wp_send_json(['Success' => true]);
			}
		}
	}
}
// Get it started
$TPgb_Pro_Gutenberg_Settings_Options = new TPgb_Pro_Gutenberg_Settings_Options();
?>