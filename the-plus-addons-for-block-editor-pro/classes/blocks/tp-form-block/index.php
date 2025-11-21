<?php
/* Block : Core Heading
 * @since : 2.0.0
 */
defined( 'ABSPATH' ) || exit;


function nxt_form_block_callback($attr, $content) {
	$pattern = '/\btpgb-wrap-/';
    if (preg_match($pattern, $content)) {
        if( class_exists('Tpgb_Blocks_Global_Options') ){
            $global_blocks = Tpgb_Blocks_Global_Options::get_instance();
            $content = $global_blocks::block_row_conditional_render($attr,$content);
        }
        return $content;
    }
    
    $block_id = (isset($attr['block_id']) && !empty($attr['block_id']) ? $attr['block_id'] : '');
    $layoutType = (isset($attr['layoutType']) && !empty($attr['layoutType']) ? $attr['layoutType'] : '');
    $actionOption = (isset($attr['actionOption']) && !empty($attr['actionOption']) ? $attr['actionOption'] : '');
    $redirect = (isset($attr['redirect']) && !empty($attr['redirect']) ? $attr['redirect'] : '');
    $message2 = (isset($attr['message2']) && !empty($attr['message2']) ? $attr['message2'] : '');
    $subject2 = (isset($attr['subject2']) && !empty($attr['subject2']) ? $attr['subject2'] : '');
    $emailTo2 = (isset($attr['emailTo2']) && !empty($attr['emailTo2']) ? $attr['emailTo2'] : '');
    $subject1 = (isset($attr['subject1']) && !empty($attr['subject1']) ? $attr['subject1'] : '');
    $emailTo1 = (isset($attr['emailTo1']) && !empty($attr['emailTo1']) ? $attr['emailTo1'] : '');
    $cApiKey = (isset($attr['cApiKey']) && !empty($attr['cApiKey']) ? $attr['cApiKey'] : '');
    $cApiUrl = (isset($attr['cApiUrl']) && !empty($attr['cApiUrl']) ? $attr['cApiUrl'] : '');
    $mApiKey = (isset($attr['mApiKey']) && !empty($attr['mApiKey']) ? $attr['mApiKey'] : '');
    $webhookurl = (isset($attr['webhookurl']) && !empty($attr['webhookurl']) ? $attr['webhookurl'] : '');
    $slackTkn = (isset($attr['slackTkn']) && !empty($attr['slackTkn']) ? $attr['slackTkn'] : '');
    $slackChnl = (isset($attr['slackChnl']) && !empty($attr['slackChnl']) ? $attr['slackChnl'] : '');
    $disName = (isset($attr['disName']) && !empty($attr['disName']) ? $attr['disName'] : '');
    $disUrl = (isset($attr['disUrl']) && !empty($attr['disUrl']) ? $attr['disUrl'] : '');
    $mGrpId = (isset($attr['mGrpId']) && !empty($attr['mGrpId']) ? $attr['mGrpId'] : '');
    $autoRespMsg = (isset($attr['autoRespMsg']) && !empty($attr['autoRespMsg']) ? $attr['autoRespMsg'] : '');
    $dApikey = (isset($attr['dApikey']) && !empty($attr['dApikey']) ? $attr['dApikey'] : '');
    $dAccId = (isset($attr['dAccId']) && !empty($attr['dAccId']) ? $attr['dAccId'] : '');
    $selectedLayout = (isset($attr['selectedLayout']) && !empty($attr['selectedLayout']) ? $attr['selectedLayout'] : '');
    $ktApiKey = (isset($attr['ktApiKey']) && !empty($attr['ktApiKey']) ? $attr['ktApiKey'] : '');
    $convertkitformoption = (isset($attr['convertkitformoption']) && !empty($attr['convertkitformoption']) ? $attr['convertkitformoption'] : '');
    $getResApiKey = (isset($attr['getResApiKey']) && !empty($attr['getResApiKey']) ? $attr['getResApiKey'] : '');
    $getRetkn = (isset($attr['getRetkn']) && !empty($attr['getRetkn']) ? $attr['getRetkn'] : '');
    $mailchmpApiki = (isset($attr['mailchmpApiki']) && !empty($attr['mailchmpApiki']) ? $attr['mailchmpApiki'] : '');
    $mailchmpAud = (isset($attr['mailchmpAud']) && !empty($attr['mailchmpAud']) ? $attr['mailchmpAud'] : '');
    $brevoApiKey = (isset($attr['brevoApiKey']) && !empty($attr['brevoApiKey']) ? $attr['brevoApiKey'] : '');
    $options = get_option('tpgb_connection_data');
    $captchaKey = (isset($options['tpgb_site_key_recaptcha']) && !empty($options['tpgb_site_key_recaptcha'])) ? $options['tpgb_site_key_recaptcha'] : '';
    $captchaSecret = (isset($options['tpgb_secret_key_recaptcha']) && !empty($options['tpgb_secret_key_recaptcha'])) ? $options['tpgb_secret_key_recaptcha'] : '';
    $ccEmail1 = (isset($attr['ccEmail1']) && !empty($attr['ccEmail1']) ? $attr['ccEmail1'] : '');
    $bccEmail1 = (isset($attr['bccEmail1']) && !empty($attr['bccEmail1']) ? $attr['bccEmail1'] : '');
    $emailHdg = (isset($attr['emailHdg']) && !empty($attr['emailHdg']) ? $attr['emailHdg'] : '');
    $frmEmail = (isset($attr['frmEmail']) && !empty($attr['frmEmail']) ? $attr['frmEmail'] : '');
    $frmNme = (isset($attr['frmNme']) && !empty($attr['frmNme']) ? $attr['frmNme'] : '');
    $replyTo = (isset($attr['replyTo']) && !empty($attr['replyTo']) ? $attr['replyTo'] : '');
    $metaDataOpt = (isset($attr['metaDataOpt']) && !empty($attr['metaDataOpt']) ? $attr['metaDataOpt'] : '');
    $cloudSecretKey = (isset($attr['cloudSecretKey']) && !empty($attr['cloudSecretKey']) ? $attr['cloudSecretKey'] : '');
    $emailHdg2 = (isset($attr['emailHdg2']) && !empty($attr['emailHdg2']) ? $attr['emailHdg2'] : '');
    $frmEmai2 = (isset($attr['frmEmai2']) && !empty($attr['frmEmai2']) ? $attr['frmEmai2'] : '');
    $metaDataOpt2 = (isset($attr['metaDataOpt2']) && !empty($attr['metaDataOpt2']) ? $attr['metaDataOpt2'] : '');
    $replyTo2 = (isset($attr['replyTo2']) && !empty($attr['replyTo2']) ? $attr['replyTo2'] : '');
    $frmNme2 = (isset($attr['frmNme2']) && !empty($attr['frmNme2']) ? $attr['frmNme2'] : '');
    $bccEmail2 = (isset($attr['bccEmail2']) && !empty($attr['bccEmail2']) ? $attr['bccEmail2'] : '');
    $ccEmail2 = (isset($attr['ccEmail2']) && !empty($attr['ccEmail2']) ? $attr['ccEmail2'] : '');
    $formId = (isset($attr['formId']) && !empty($attr['formId']) ? $attr['formId'] : '');
    $failMsg = (isset($attr['failMsg']) && !empty($attr['failMsg']) ? $attr['failMsg'] : '');
    $valErrMsg = (isset($attr['valErrMsg']) && !empty($attr['valErrMsg']) ? $attr['valErrMsg'] : '');
    $failMsg2 = (isset($attr['failMsg2']) && !empty($attr['failMsg2']) ? $attr['failMsg2'] : '');
    $valErrMsg2 = (isset($attr['valErrMsg2']) && !empty($attr['valErrMsg2']) ? $attr['valErrMsg2'] : '');

    $blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attr );

    $actionOptionString = "";  

    if (is_string($actionOption)) {
        $actionOptionString = $actionOption;  
    } else {
        $actionOptionString = json_encode($actionOption);  
    }
    $formDataAttributes =[
      "emailTo1"=> $emailTo1,
      "subject1"=> $subject1,
      "actionOption"=> $actionOptionString,
      "emailTo2"=> $emailTo2,
      "subject2"=> $subject2,
      "cApiUrl"=> $cApiUrl,
      "cApiKey"=> $cApiKey,
      "mApiKey"=> $mApiKey,
      "webhookurl"=> $webhookurl,
      "slackChnl"=> $slackChnl,
      "slackTkn"=> $slackTkn,
      "disName"=> $disName,
      "disUrl"=> $disUrl,
      "mGrpId"=> $mGrpId,
      "dApikey"=> $dApikey,
      "dAccId"=> $dAccId,
      "ktApiKey"=> $ktApiKey,
      "convertkitformoption"=> $convertkitformoption,
      "getResApiKey"=> $getResApiKey,
      "getRetkn"=> $getRetkn,
      "mailchmpAud"=> $mailchmpAud,
      "mailchmpApiki"=> $mailchmpApiki,
      "brevoApiKey"=> $brevoApiKey,
      "captchaSecret"=>$captchaSecret,
      'ccEmail1'=>$ccEmail1,
      'bccEmail1'=>$bccEmail1,
      'emailHdg'=>$emailHdg,
      'frmEmail'=>$frmEmail,
      'frmNme'=>$frmNme,
      'replyTo'=>$replyTo,
      'metaDataOpt'=>$metaDataOpt,
      'cloudSecretKey'=>$cloudSecretKey,
      'emailHdg2'=>$emailHdg2,
      'ccEmail2'=>$ccEmail2,
      'bccEmail2'=>$bccEmail2,
      'frmEmai2'=>$frmEmai2,
      'frmNme2'=>$frmNme2,
      'replyTo2'=>$replyTo2,
      'metaDataOpt2'=>$metaDataOpt2,
      'formId'=>$formId,
      'failMsg'=>$failMsg,
      'valErrMsg'=>$valErrMsg,
      'failMsg2'=>$failMsg2,
      'valErrMsg2'=>$valErrMsg2
    ];
    $filteredFormDataAttributes = array_filter($formDataAttributes, function ($value) {
        return !empty($value);
    });

    $encryptedFormData = Tp_Blocks_Helper::tpgb_simple_decrypt(json_encode($filteredFormDataAttributes),'ey');
    $dataAction = 'data-actionOption="' . esc_attr($actionOptionString) . '"';
    
    $redirectUrl = is_array($redirect) && isset($redirect['url']) ? $redirect['url'] : '';
    $redirectTarget = is_array($redirect) && isset($redirect['target']) ? $redirect['target'] : '';
    $redirectNofollow = is_array($redirect) && isset($redirect['nofollow']) ? $redirect['nofollow'] : '';
    $dataRedirect = '';
    if (!empty($redirectUrl)) {
        $dataRedirect .= 'data-redirect="' . esc_url($redirectUrl) . '"';
    }
    if ($redirectTarget === 1 ) {
        $dataRedirect .= ' data-link-blank="1"';
    }
    if ($redirectNofollow === 1) {
        $dataRedirect .= ' data-link-nofollow="1"';
    }

    $output = '<div class="tp-form-block ' .esc_attr( $layoutType ) . ' tpgb-relative-block tpgb-block-' .esc_attr( $block_id ).' '.esc_attr($blockClass).'" data-block-id="' . esc_attr($block_id) . '">';
        $output .= '<form id="'.esc_attr($formId).'" class="nxt-form" data-formdata="' . esc_attr($encryptedFormData) . '" data-cap="' . esc_attr($captchaKey) . '" ' . $dataAction . ' '.$dataRedirect.'>';
            $output .= $content;
        $output .=  ' </form>';
        $output.='<span class="nxt-success-message" data-success-message="'.esc_attr($autoRespMsg).'"></span>';
    $output .= '</div>';

    return Tpgb_Blocks_Global_Options::block_Wrap_Render($attr, $output);
}

function nxt_form_block_render() {
    $block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'nxt_form_block_callback');
    $block_data['render_callback'] = 'nxt_form_block_callback';
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'nxt_form_block_render' );