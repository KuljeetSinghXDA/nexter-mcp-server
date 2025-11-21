<?php
/**
 * TPGB Pro Library
 *
 * @package tpgbp
 * @since 1.0.0
 */
if ( !class_exists( 'Tpgb_Pro_Library' ) ) {

	class Tpgb_Pro_Library {

		static $status = null;

		private static $_instance = null;
		
		static $licence_status = 'tpgbp_license_status',
		    $licence_nonce = 'tpgb-dash-ajax-nonce' ,
		    $valid_url = 'https://store.posimyth.com',
			$item_name = 'Nexter Blocks',
			$item_id = 99119,
		    $license_page = 'nexter_welcome_page#/activate_PRO';

		const tpgb_activate = 'tpgb_activate';

		public static function instance() {
			return self::$status;
		}
		
		/**
		 * Initiator
		 * @since 1.0.2
		 */
		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
		
		function __construct() {

			self::$status = get_option( self::$licence_status );
			if(is_admin()){
				$status = $this->tpgb_activate_status();
				if(empty($status) || $status!='valid' || $status=='expiring_in_week' || $status=='expiring_in_month'){
					add_action( 'admin_notices', array( $this, 'tpgb_pro_licence_notice' ) );
				}
			}
			
			add_action( 'wp_ajax_tpgb_license_deactivate', array( $this,'tpgb_licence_deactivate_license') );
			add_action( 'wp_ajax_tpgb_license_activate', array( $this,'tpgb_licence_activate_license') );
		}
		
        public function tpgb_pro_licence_notice() {
            $status = $this->tpgb_activate_status();
            // Unique notice IDs
            $notice_id = '';
            $notice_type = 'info';
            $heading = '';
            $description = '';
            $button_html = '';
            $nxlogosvg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"><rect width="24" height="24" fill="#1717CC" rx="5"/><path fill="#fff" d="M12.605 17.374c.026 0 .038.013.039.038.102 0 .192.014.27.04.025 0 .05.012.076.037.128.077.23.167.307.27v.038c0 .026.013.051.039.077v.038a.63.63 0 0 1 .038.193l-.038 1.882h-2.652v-2.613h1.921Zm.308-13.414c.128 0 .23.038.308.115a.259.259 0 0 1 .115.23V15.26c.025.153-.052.295-.23.423a.872.872 0 0 1-.578.192h-1.844V3.96h2.23Z"/></svg>';
        
            // License page link
            $license_page_url = admin_url('admin.php?page=' . self::$license_page);
        
            if ( empty( $status ) ) {
                $notice_id = 'nexter_license_activate';
                if ( get_option($notice_id . '_dismissed') ) return;
    
                $heading = esc_html__('Activate Nexter Blocks License to Receive Active Plugin Updates', "tpgbp");
                $description = esc_html__('Your Pro version is installed, now activate your license for active updates and support access.', "tpgbp");
                
                $nxt_onboarding = get_option('nxt_onboarding_done', false);

                if ($nxt_onboarding === '1') {
                    $dashboard_url = admin_url('admin.php?page=nexter_welcome_page#/activate_pro');
                } else {
                    $dashboard_url = admin_url('admin.php?page=nexter_welcome_page#/activate_pro?skip_onboarding=1');
                }
                $button_html = '<a href="' . esc_url($dashboard_url) . '" class="nxt-nobtn-primary" target="_blank" rel="noopener noreferrer">' . esc_html__('Activate License', 'tpgbp') . '</a>';
        
            } elseif ( $status === 'expired' ) {
                $notice_id = 'nexter_license_expired';
                if ( get_option($notice_id . '_dismissed') ) return;
        
                $heading = esc_html__('Your Nexter Blocks License Has Expired', "tpgbp");
                $description  = esc_html__('Please renew your license to continue using Pro features and receive updates, security patches, and access to new features.', "tpgbp");
                $button_html = '<a href="' . ( isset(get_option(self::tpgb_activate)['tpgb_activate_key']) ? esc_url('https://store.posimyth.com/checkout/?edd_license_key='.get_option(self::tpgb_activate)['tpgb_activate_key'].'&download_id=141309') : '#' ) . '" target="_blank" rel="noopener noreferrer" class="nxt-nobtn-primary">' . esc_html__('Renew License', "tpgbp") . '</a>';
            } elseif ( $status === 'expiring_in_week' ) {
                $notice_id = 'nexter_license_expiring_in_week';
                if ( get_option($notice_id . '_dismissed') ) return;
        
                $heading = esc_html__('Final Reminder – Nexter Blocks License Expires in 7 Days', "tpgbp");
                $description = esc_html__('Your Nexter Blocks Pro license will expire in 7 days. Renewing ensures continued access to updates and Pro features without interruption.', "tpgbp");
                $button_html = '<a href="' . ( isset(get_option(self::tpgb_activate)['tpgb_activate_key']) ? esc_url('https://store.posimyth.com/checkout/?edd_license_key='.get_option(self::tpgb_activate)['tpgb_activate_key'].'&download_id=141309') : '#' ) . '" target="_blank" rel="noopener noreferrer" class="nxt-nobtn-primary">' . esc_html__('Renew Now', "tpgbp") . '</a>';
            }elseif ( $status === 'expiring_in_month' ) {
                $notice_id = 'nexter_license_expiring_in_month';
                if ( get_option($notice_id . '_dismissed') ) return;
        
                $heading = esc_html__('Heads-Up: Your Nexter Blocks Pro License Will Expire in 1 Month', "tpgbp");
                $description = esc_html__('Your Pro license will expire in 30 days renew in advance to avoid any break in functionality or receiving active updates.', "tpgbp");
                $button_html = '<a href="' . ( isset(get_option(self::tpgb_activate)['tpgb_activate_key']) ? esc_url('https://store.posimyth.com/checkout/?edd_license_key='.get_option(self::tpgb_activate)['tpgb_activate_key'].'&download_id=141309') : '#' ) . '" target="_blank" rel="noopener noreferrer" class="nxt-nobtn-primary">' . esc_html__('Renew Early', "tpgbp") . '</a>';
            }
        
            if ( $notice_id ) {
                echo '<div class="notice notice-' . esc_attr($notice_type) . ' is-dismissible nxt-notice-wrap" data-notice-id="' . esc_attr($notice_id) . '">';
                    echo '<div class="nexter-license-activate">';
                        echo '<div class="nexter-license-icon">' . $nxlogosvg . '</div>';
                        echo '<div class="nexter-license-content">';
                            echo '<h2>' . $heading . '</h2>';
                            echo '<p>' . $description . '</p>';
                            echo $button_html;
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
            }
        }

		public static function tpgb_licence_activate_license() {

			// listen for our activate button to be clicked
			$submitKey = (isset($_POST["submit-key"]) && !empty($_POST["submit-key"])) ? sanitize_text_field(wp_unslash($_POST['submit-key'])) : '';
			if ( isset($submitKey) && !empty($submitKey) && $submitKey=='Activate' ) {

				// run a quick security check
				if ( ! check_ajax_referer( self::$licence_nonce, 'tpgb_activte_nonce' ) ) {
					return;
				}
				
				// retrieve the license from the database
				if( !isset($_POST['tpgb_activate_key']) || empty($_POST['tpgb_activate_key']) ) {
					wp_redirect( admin_url( 'admin.php?page=' . self::$license_page ) );
					exit;
				}
				
				$license = isset($_POST['tpgb_activate_key']) ? sanitize_key(wp_unslash($_POST['tpgb_activate_key'])) : '';
				
				$license_data = array();
				// data to send in our API request
				$api_params = array(
					'edd_action' => 'activate_license',
					'license' => $license,
					//'item_name' => self::$item_name,
					'item_id' => self::$item_id,
					'url' => home_url()
				);
				
				$response = wp_remote_get( self::$valid_url, array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'	  => $api_params
				) );
				
				$message = '';

				// make sure the response came back okay
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

					if ( is_wp_error( $response ) ) {
						$message = $response->get_error_message();
					} else {
						$message = __( 'An Error Occurred, Please Try Again Later.', "tpgbp" );
					}

				} else {

					$license_data = json_decode( wp_remote_retrieve_body( $response ), true );

					if ( is_array($license_data) && array_key_exists( 'success', $license_data ) && empty(  $license_data['success'] ) ) {

						switch( $license_data['error'] ) {

							case 'expired' :
								/* translators: Your license key expired. %1$s Manage Licence URL */
								$message = sprintf(
									__( 'Your license key expired.', "tpgbp" )
								);
								break;

							case 'revoked' :
								/* translators: %1$s is a placeholder for the support URL */
								$message = __( 'Your license key has been disabled.', "tpgbp" );
								break;

							case 'missing' :
								/* translators: Invalid license. %s */
								$message = __( 'Invalid license.', "tpgbp" );
								break;

							case 'invalid' :
							case 'site_inactive' :
								/* translators: Your license is not active for this URL. %s */
								$message = __( 'Your license is not active for this URL.', "tpgbp" );
								break;

							case 'item_name_mismatch' :
								/* translators: %s: item name */
								$message = sprintf( __( 'This appears to be an invalid license key for %s.', "tpgbp" ), self::$item_name );
								break;

							case 'no_activations_left':

								$message = __( 'Your license key has reached its activation limit.', "tpgbp" );
								break;

							default :

								$message = __( 'An Error Occurred, Please Try Again Later.', "tpgbp" );
								break;
						}

					}else if( !empty($license_data) && $license_data['success'] == true && $license_data['success'] == 'valid' ) {
						$message = __( 'Your License is active.', "tpgbp" );
					}
					
				}
				
				$update_value = [ 'tpgb_activate_key' => $license  ];
				update_option( self::tpgb_activate , $update_value );
				$active_plan = false;

				if ( is_array($license_data) && array_key_exists( 'activations_left', $license_data ) && !empty(  $license_data['activations_left'] ) && $license_data['activations_left']  == 'unlimited' ) {
					$active_plan = true;
				}

				$status = [ 'status' => $license_data['license'], 'expired' => isset($license_data['expires']) ? $license_data['expires'] : '', 'message' => $message , 'active_plan' =>  $active_plan , 'customer_email' => isset($license_data['customer_email']) ? $license_data['customer_email'] : '' , 'customer_name' =>  isset($license_data['customer_name']) ? $license_data['customer_name'] : '' ];
				
				update_option( self::$licence_status, $status );
				
				wp_send_json_success();
				exit();
				
			}else{
				wp_send_json_success();
				exit;
			}
		}

		public static function tpgb_licence_deactivate_license() {

			// listen for our activate button to be clicked
			$submitKey = (isset($_POST["submit-key"]) && !empty($_POST["submit-key"])) ? sanitize_text_field(wp_unslash($_POST["submit-key"])) : '';
			if ( isset($submitKey) && !empty($submitKey) && $submitKey=='Deactivate' ) {

				// run a quick security check
				if ( ! check_ajax_referer( self::$licence_nonce, 'nonce' ) ) {
					return;
				} // get out if we didn't click the Activate button

				// retrieve the license from the database
				$license = get_option( self::tpgb_activate );

				if ( !empty( $license ) ) {
					delete_option( self::tpgb_activate );
					delete_option( self::$licence_status );
					delete_transient( 'tpgb_activate_transient' );
					delete_transient('tpgbp_rollback_version_' . TPGBP_VERSION);
				}

				wp_send_json_success();
				exit();
			}
		}
		
		public static function tpgb_get_activate_plan() {
		
			$check_status = get_option( self::$licence_status );
			if( !empty($check_status) && $check_status['status'] == 'valid' ) {

				// New User
				if( !empty($check_status) && isset($check_status['active_plan']) && $check_status['active_plan'] === true ){
					return true;
				}else if( !empty($check_status) && isset($check_status['active_plan']) && $check_status['active_plan'] === false  ){
					return false;
				}

				// exisitng User 
				if( !empty($check_status) && !isset($check_status['active_plan']) ){
					return true;
				}
			}
			return false;
		}

		public function tpgb_activate_status() {
		
			$check_status = get_option( self::$licence_status );
			if( !empty($check_status) && $check_status['status'] == 'valid' ) {
				if( !empty($check_status) && !empty($check_status['expired']) && $check_status['expired'] != 'lifetime' ){
					$expired= strtotime($check_status['expired']);
					$today_date = strtotime("today midnight");

                    $diff_days = floor( ( $expired - $today_date ) / ( 60 * 60 * 24 ) );

					if($today_date >= $expired ){
						$status = [ 'status' => 'expired', 'message' => esc_html__('Your license key expired.', "tpgbp") ];
						update_option( self::$licence_status, array_merge($check_status, $status) );
						delete_transient( 'tpgb_activate_transient' );
						delete_transient('tpgbp_rollback_version_' . TPGBP_VERSION);
						return 'expired';
					}

                    // Check for soon-to-expire status
                    if ( $diff_days <= 7 ) {
                        return 'expiring_in_week'; // Expiring within a week
                    } elseif ( $diff_days <= 30 ) {
                        return 'expiring_in_month'; // Expiring within a month
                    }

				}
				return 'valid';
			}else if( !empty($check_status) && $check_status['status'] == 'expired' ){
				return 'expired';
			}else{
				return '';
			}
		}
		
		public static function tpgb_pro_activate_msg(){
			$check_status = get_option( self::$licence_status );
			$value = (!empty($check_status['status']) && isset($check_status['status'])) ? $check_status['status'] : '';
			$message = (!empty($check_status['message']) && isset($check_status['message'])) ? $check_status['message'] : '';

			$redsvg = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_7_19099)"><path d="M8.45434 1.87551C8.67741 1.65244 8.93451 1.5012 9.22943 1.41802C9.50922 1.33862 10.0083 1.36509 10.2919 1.47474C10.5376 1.56548 10.8477 1.78477 11.0065 1.9776C11.1312 2.12884 19.0107 15.1844 19.1695 15.502C19.6232 16.4094 19.196 17.5021 18.2356 17.8916L18.0163 17.9785H9.67936H1.3424L1.1231 17.8916C0.174087 17.5059 -0.264501 16.417 0.177868 15.5209C0.230801 15.415 2.06834 12.3487 4.26506 8.7001C7.40324 3.49754 8.3031 2.03053 8.45434 1.87551Z" fill="#FF0000"/><path d="M9.33918 6.60182C9.13879 6.6661 9.03292 6.7455 8.92706 6.9232C8.85522 7.04041 8.84766 7.11225 8.84766 7.6378C8.84766 7.95918 8.87412 8.81745 8.90437 9.54717C8.9384 10.2731 8.97621 11.1805 8.99133 11.5586C9.02158 12.311 9.02914 12.3375 9.28246 12.4698C9.46017 12.5644 9.88741 12.553 10.084 12.4471C10.3298 12.3186 10.3487 12.2581 10.3789 11.3809C10.3903 10.9537 10.4281 10.0878 10.4583 9.45265C10.5226 8.04614 10.5264 7.09713 10.4697 6.9837C10.2806 6.61695 9.80045 6.45437 9.33918 6.60182Z" fill="white"/><path d="M9.47885 13.3886C8.93061 13.5209 8.65082 14.1448 8.90793 14.6628C9.11588 15.0862 9.6074 15.2677 10.0195 15.0824C10.3825 14.9199 10.564 14.6401 10.564 14.2544C10.564 13.9822 10.4808 13.7818 10.2955 13.6041C10.0989 13.415 9.74729 13.3243 9.47885 13.3886Z" fill="white"/></g><defs><clipPath id="clip0_7_19099"><rect width="19.3584" height="19.3584" fill="white"/></clipPath></defs></svg>';

			switch( $value ) {

				case 'expired' :
					$message = '<div style="display: flex;align-items: center;column-gap: 5px;">'. $redsvg.'<h4 class="tpgb-notice-head">'. __( 'Your Licence Key is Expired !!!', "tpgbp" ).'</h4> </div>';
					/* translators: %1$s is the URL for POSIMYTH Store, %2$s is the link text for POSIMYTH Store, %3$s is the URL for Help Desk, and %4$s is the link text for Help Desk. */
					$message .= sprintf( __( '<p>Seems Like Your Licence Key for Nexter WP is Expired. Visit <a href="%1$s" target="_blank">POSIMYTH Store</a> to Pay Invoices / Change Payment Methods / Manage Your Subscriptions. Please Don’t Hesitate to Reach Us at <a href="%2$s" target="_blank">Nexter Support</a> if You Have an Issue Regarding Our Products.</p>', "tpgbp" ), esc_url('https://store.posimyth.com/'), esc_url('https://store.posimyth.com/helpdesk') );
					
					break;

				case 'valid' :
					$message = '<div style="display: flex;color: #14C38E;font-size: 14px;align-items: center;column-gap: 5px;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 20 20"><path fill="#14C38E" d="M9.247.02C7.797.156 6.694.465 5.49 1.07c-1.587.794-3.058 2.143-4.024 3.69C.765 5.878.257 7.32.05 8.766c-.067.461-.067 2.001 0 2.462.164 1.153.47 2.162.946 3.127.536 1.094 1.064 1.829 1.932 2.7.864.86 1.611 1.396 2.663 1.912.735.359 1.208.535 1.955.723a9.918 9.918 0 0 0 5.827-.278c.587-.207 1.595-.715 2.112-1.06a10.649 10.649 0 0 0 3.007-3.079c.281-.441.735-1.38.919-1.9.919-2.586.758-5.361-.45-7.788a9.571 9.571 0 0 0-1.791-2.533 9.545 9.545 0 0 0-2.78-2.024A9.571 9.571 0 0 0 10.87.03C10.471-.004 9.568-.012 9.247.02Z"/><path fill="#fff" d="M13.826 6.732c-.053.022-1.38 1.327-2.948 2.903L8.02 12.498 6.956 11.43c-1.15-1.15-1.157-1.153-1.489-1.089-.185.034-.41.261-.445.447-.064.34-.087.313 1.369 1.773 1.455 1.46 1.425 1.437 1.768 1.372.128-.022.498-.378 3.284-3.168 2.281-2.288 3.148-3.18 3.179-3.274a.618.618 0 0 0-.238-.696c-.117-.087-.418-.121-.558-.064Z"/></svg>'.esc_html__('Congratulation! License Successfully Activated.', "tpgbp").'</div></div>';
					break;
					
				case 'revoked' :
					$message = '<div style="display: flex;align-items: center;column-gap: 5px;">'. $redsvg.'<h4 class="tpgb-notice-head">'. __( 'We Have Revoked Your Licence for Nexter', "tpgbp" ).'</h4> </div>';
					/* translators: %s: support tpgb */
					$message .= sprintf( __( '<p>Your Licence Key for Nexter WP is Revoked for Some Reason. Visit <a href="%1$s" target="_blank">POSIMYTH Store</a> to Update Your Licence Key / Manage Payments / Pay Invoices. Reach Out to Us at <a href="%2$s" target="_blank">Nexter Support</a> for Queries Regarding Our Products.</p>', "tpgbp" ), esc_url('https://store.posimyth.com/'), esc_url('https://store.posimyth.com/helpdesk') );
					break;

				case 'missing' :
					$message = '<div style="display: flex;align-items: center;column-gap: 5px;">'. $redsvg.'<h4 class="tpgb-notice-head">'. __( "It's Time to Enter Licence Key", "tpgbp" ).'</h4> </div>';
					/* translators: %s: store tpgb */
					$message .= sprintf( __( '<p>You’re Just One Step Away From Having Fun While Crafting Websites. Paste Your Licence Key for Nexter WP Here and Get Inspired With Other People Who Build With Us. Visit <a href="%s" target="_blank">POSIMYTH Store</a> to Update Your Licence Key / Manage Payments / Pay Invoices.</p>', "tpgbp" ), esc_url('https://store.posimyth.com/') );
					break;

				case 'invalid' :
				case 'site_inactive' :
					$message = '<div style="display: flex;align-items: center;column-gap: 5px;">'. $redsvg.'<h4 class="tpgb-notice-head">'. __( 'Typo in Licence Key is Unacceptable !', "tpgbp" ).'</h4> </div>';
					/* translators: %s: store url */
					$message .= sprintf( __( '<p>We Can’t Find Licence Key You Just Entered in Any of Our Lists. Make Sure You Are Not Adding Any White Spaces With It. If You\'re Having This Issue Repeatedly, Visit  <a href="%s" target="_blank">POSIMYTH Store</a> to Confirm Your Licence Key.</p>', "tpgbp" ), esc_url('https://store.posimyth.com/') );
					break;

				case 'item_name_mismatch' :
					$message = '<div style="display: flex;align-items: center;column-gap: 5px;">'. $redsvg.'<h4 class="tpgb-notice-head">'. __( "This License Key Belongs to Some Other Product", "tpgbp" ).'</h4> </div>';
					/* translators: %s: store url */
					$message .= sprintf( __( '<p>It Appears That Licence Key You Entered Belongs to Some Other Product from Our Product Collection. In Layman Terms, You Dialed a Wrong Number. Visit <a href="%s" target="_blank">POSIMYTH Store</a> and Verify Your Licence Key for Nexter WP.</p>', "tpgbp" ), esc_url('https://store.posimyth.com/') );
					break;

				case 'no_activations_left':
					$message = '<div style="display: flex;align-items: center;column-gap: 5px;">'. $redsvg.'<h4 class="tpgb-notice-head">'. __( "You Should’ve Ordered More !!!", "tpgbp" ).'</h4> </div>';
					/* translators: %s: store url */
					$message .= sprintf( __( '<p>Unfortunately, Your Activation Quota for Active / Running Websites Built With Nexter WP  is Over. Like What You’re Using ? Visit <a href="%s" target="_blank">POSIMYTH Store</a> to Upgrade Your Existing Plan and Allow Your Creativity to Bloom.</p>', "tpgbp" ), esc_url('https://store.posimyth.com/') );
					break;

				default :
					$message = '';
					
					break;
			}
			if(!is_array($check_status)){
				$check_status = [];
			}
			$check_status['message'] = $message;
			return $check_status;
		}
		
	}
}