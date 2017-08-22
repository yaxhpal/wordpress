<?php

/**
 * @package Lms_Api
 * @version 1.1
 */

/*
  Plugin Name: Lms_Api
  Plugin URI: https://github.com/yaxhpal/wordpress/tree/master/plugins/lms-api
  Description: Plugin to access LMS APIs
  Author: Yashpal Meena <yaxhpal@gmil.com>
  Version: 1.1
  Author URI: http://www.yashpalmeena.com/
*/
 
class Lms_Api {
 
  const LMS_CAPTURE_API_URL = 'http://lms.peerlearning.com/api/v1/users/capture_lead.json';
  const LMS_REFERER = 'avanti.in';
  const LEAD_SQUARED_ORIGIN = 'https://web.mxradon.com';
  

  /**
   * Create student user on LMS
   * @return WP_Error|WP_REST_Request
   */
  public function create_user_via_lead() {

    $headerOrigin = $_SERVER['HTTP_ORIGIN'];
    
    if( $headerOrigin == self::LEAD_SQUARED_ORIGIN) {
      
      $data = array('first_name' => $_POST['FirstName'], 
        'last_name' => $_POST['LastName'],
        'phone' => $_POST['Phone'],
        'email' => $_POST['EmailAddress'],
        'address' => $_POST['mx_City'],
        'grade' => str_replace("Class ", "", $_POST['mx_Class_Content_sales']));
      
      $user_params = http_build_query($data);
      $refer = self::LMS_REFERER;
      $api_url = self::LMS_CAPTURE_API_URL;

      // Send user info to LMS 
      $postRes = $this->postRequest($api_url, $user_params, $refer);
    }
  }
    
  /**
  * Curl send post request, support HTTPS protocol
  * @param string $url The request url
  * @param array $data The post data
  * @param string $refer The request refer
  * @param int $timeout The timeout seconds
  * @param array $header The other request header
  * @return mixed
  */
  public function postRequest($url, $data, $refer = "", $timeout = 10, $header = []) {
      $curlObj = curl_init();
      $ssl = stripos($url,'https://') === 0 ? true : false;
      $options = [
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_POST => 1,
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_FOLLOWLOCATION => 1,
          CURLOPT_AUTOREFERER => 1,
          CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)',
          CURLOPT_TIMEOUT => $timeout,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
          CURLOPT_HTTPHEADER => ['Expect:'],
          CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
          CURLOPT_REFERER => $refer
      ];
      if (!empty($header)) {
          $options[CURLOPT_HTTPHEADER] = $header;
      }
      if ($refer) {
          $options[CURLOPT_REFERER] = $refer;
      }
      if ($ssl) {
          //support https
          $options[CURLOPT_SSL_VERIFYHOST] = false;
          $options[CURLOPT_SSL_VERIFYPEER] = false;
      }
      curl_setopt_array($curlObj, $options);
      $returnData = curl_exec($curlObj);
      if (curl_errno($curlObj)) {
          //error message
          $returnData = curl_error($curlObj);
      }
      curl_close($curlObj);
      return $returnData;
  }
}

// Function to register our new routes from the controller.
function register_hook_for_lms_api() {
    $controller = new Lms_Api();
    $controller->create_user_via_lead();
}

// add_action( 'rest_api_init', 'register_lms_rest_api_routes');
add_action( 'init', 'register_hook_for_lms_api' );