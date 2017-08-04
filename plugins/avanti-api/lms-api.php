<?php

/**
 * @package Lms_Api
 * @version 1.0
 */

/*
  Plugin Name: Lms_Api
  Plugin URI: https://github.com/yaxhpal/wordpress/plugins/lms-api
  Description: Plugin to access LMS APIs
  Author: Yashpal Meena <yaxhpal@gmil.com>
  Version: 1.0
  Author URI: http://www.yashpalmeena.com/
*/
 
class Lms_Api {
 
  // Here initialize our namespace and resource name.
  public function __construct() {
        $version = '1';
        $this->namespace  = 'lmsapi/v' . $version;
        $this->base = 'user';
  }
 
  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {
    register_rest_route( $this->namespace, '/' . $this->base, array(
        'methods'         => WP_REST_Server::CREATABLE,
        'callback'        => array( $this, 'create_user' )
    ));
  }
  
  /**
   * Create one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function create_user( $request ) {
    $user_params = $request["student"];
    $refer = "avanti.in";
    $api_url = "http://lms.staging.peerlearning.com/api/v1/users/capture_lead.json";
    $postRes = $this->postRequest($api_url, $user_params, $refer);
    return new WP_REST_Response( $postRes, 200 );
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
function register_lms_rest_api_routes() {
    $controller = new Lms_Api();
    $controller->register_routes();
}

add_action( 'rest_api_init', 'register_lms_rest_api_routes');





