<?php

/**
 * POST /image-cop/v1/options
 *
 */
function get_image_cop_options() {

    $response = new WP_REST_Response( ImageCop::getOptions() );
     
    // Add a custom status code
    $response->set_status( 200 );
     
    // Add a custom header
    $response->header( 'Location', site_url() );

    return $response;

  }

/**
 * POST /image-cop/v1/options
 *
 * @return void
 */
function post_image_cop_options( $data ) {

    $data = $_REQUEST;
    error_log(json_encode($_REQUEST));
    update_option('image_cop_bucket', $data['bucket']);
    update_option('image_cop_upload_folder', $data['upload_folder']);
    update_option('image_cop_compressed_folder', $data['compressed_folder']);
    update_option('image_cop_keep_local_files', $data['keep_local_files']);

    $response = new WP_REST_Response(ImageCop::getOptions());
        
    // Add a custom status code
    $response->set_status( 200 );
        
    // Add a custom header
    $response->header( 'Location', site_url() );

    return $response;

}

add_action( 'rest_api_init', function () {

    register_rest_route( 'image-cop/v1', '/options', array(
      'methods' => 'GET',
      'callback' => 'get_image_cop_options',
    ) );

    register_rest_route( 'image-cop/v1', '/options', array(
        'methods' => 'POST',
        'callback' => 'post_image_cop_options',
      ) );

  } );