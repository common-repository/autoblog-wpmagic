<?php

namespace autoblogwpm;


/************************** CREATE ATTACH IMAGE CLASS *****************************
 *******************************************************************************
 * 
 */
class Image {

    /**
    * Downloads an image from the specified URL and attaches it to a post as a post thumbnail.
    *
    * @param string $file    The URL of the image to download.
    * @param int    $post_id The post ID the post thumbnail is to be associated with.
    * @param string $desc    Optional. Description of the image.
    * @return string|WP_Error Attachment ID, WP_Error object otherwise.
    */
    public function generate_Featured_Image( $remoteFile, $post_id, $descr ){
        /**
         * loadthe WP files when running from wp-cron.php
         */
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        /* 
        * Set variables for storage, fix file filename for query strings.
        * fix filename for query strings
        */
        preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png|JPG|JPEG|webp)/i', $remoteFile, $matches );
        
        /* 
        * Set variable $returnTest to check for errors and display them into help page
        * set image file name & get file type
        */
        $returnTest = array();
        $image_file_name = ''; if(isset($matches[0])) $image_file_name = basename( $matches[0] );
        $filetype = wp_check_filetype($image_file_name, null );
        
        /* 
        * check if file have extension of defined type
        * don't download if not
        */
        if(isset($filetype['ext']) && !empty($filetype['ext'])) {
            // Download file to temp dir
            $timeout_seconds = 5;
            $temp_file = download_url( $remoteFile, $timeout_seconds );

            if ( !is_wp_error( $temp_file ) ) {
                // Array based on $_FILE as seen in PHP file uploads
                $file = array(
                    'name'     => $image_file_name, // ex: wp-header-logo.png
                    'type'     => $filetype,
                    'tmp_name' => $temp_file,
                    'error'    => 0,
                    'size'     => filesize($temp_file),
                );

                $overrides = array(
                    // Tells WordPress to not look for the POST form
                    // fields that would normally be present as
                    // we downloaded the file from a remote server, so there
                    // will be no form fields
                    // Default is true
                    'test_form' => false,

                    // Setting this to false lets WordPress allow empty files, not recommended
                    // Default is true
                    'test_size' => true,
                );

                // Move the temporary file into the uploads directory
                $attachmentID = media_handle_sideload( $file, $post_id, $descr, $overrides );
                
                $returnTest = array(
                    'postID'        => $post_id,
                    'name'          => $image_file_name,
                    'file'          => $file,
                    'url'           => $remoteFile,
                );
                
                if ( is_wp_error( $attachmentID ) ) {
                    // If error storing permanently, unlink.
                    @unlink( $temp_file );
                    // Update result to view it
                    $returnTest = array_merge($returnTest, array('attachmentID' => $attachmentID->get_error_message()));
                } else {
                    // Sets the post thumbnail (featured image) for the given post.
                    $attacheImg = set_post_thumbnail( $post_id, $attachmentID );
                    if ( ! is_wp_error( $attacheImg ) ) {
                        // Update result to view it
                        array_push($returnTest, esc_attr__(Info::get_plugin_text("imgattached_succes"), 'autoblog-wpm-free') );
                    } else {
                        // Errors publishing the image
                        array_push($returnTest, $attacheImg->get_error_message());
                    }
                }
            } else {
                // Check for download errors. If error, unlink.
                @unlink( $temp_file );
                // Update result to view it
                array_push($returnTest, $remoteFile . ' - ' . $temp_file->get_error_message());
            }
        } else {
            /* 
            * no extension was found
            * create a function to proces images from Url's that don't have an image type but still publish a image
            */
            $getRemoteImg = $this -> get_remote_img( $remoteFile, $post_id );
            
            // test if file is an jpeg image
            preg_match( '/[^\?]+\.(jpeg)/i', $getRemoteImg, $matches );
            if(isset($matches[0])){
                $downloadedImageNameAndExt = basename( $matches[0] );
                $imgtype = wp_check_filetype($downloadedImageNameAndExt, null );
                if( isset($imgtype['ext']) && !empty($imgtype['ext']) ) {
                    // Set the Url path for image in order to get a file ext from the Url & recall the function recursively
                    $uploadWPDir = wp_upload_dir();
                    $newRemoteImg = AUTOBLOGWPM_IMGTMPFOLDERURL . DIRECTORY_SEPARATOR . $downloadedImageNameAndExt;
                    // call the function once again recursively & link the image to post - publish the post eventualy
                    $returnTest[] = $this -> generate_Featured_Image( $newRemoteImg, $post_id );
                    // delete the tmp image
                    wp_delete_file( AUTOBLOGWPM_IMGTMPFOLDER . DIRECTORY_SEPARATOR . $downloadedImageNameAndExt );
                    // make a Info for debugging
                    array_push($returnTest);
                }
            } else { 
                // If no image once again was not founded, just make an Info for debugging
                array_push($returnTest, esc_attr__($remoteFile . ' - ' . Info::get_plugin_text("imgattached_notfound"), 'autoblog-wpm-free') );
            }
        }
        
        return $returnTest;
    }

    /**
    * get the content body from Url
    * store the remote HTML in a transient if need it
    */
    private function get_remote_html( $url, $storeit = false, $transientName = 'foo_remote_html', $transientTime = 24) {
    
        // Check for transient, if none, grab remote HTML file
        if ( false === ( $html = get_transient( 'foo_remote_html' ) ) ) {
            
            // Get remote HTML file
            $response = wp_remote_get( $url, array(
                            'method'      => 'GET',
                            'timeout'     => 5
                        ) );
            
            // Check for error
            if ( is_wp_error( $response ) ) {
                return $response->get_error_message();
            }

            // Parse remote HTML file in order to store it in transient
            $data = wp_remote_retrieve_body( $response );

            // Check for error
            if ( is_wp_error( $data ) ) {
                return $data->get_error_message();
            }

            // Store remote HTML file in transient, expire after x hours
            if( $storeit === true ) set_transient( $transientName, $data, $transientTime * HOUR_IN_SECONDS );
            else $html = $data;
        }

        return $html;
    }

    /**
    * get the content body from Url
    * check if is an image (jpeg)
    * create and save image file into a TMP folder in order to use it later
    */
    private function get_remote_img( $url, $post_id, $imgType = 'image/jpeg' ) {

        $fileExt = $this -> getExtension ($imgType);

        // Get remote HTML file
        $response = wp_remote_get( $url, array(
            'method'      => 'GET',
            'timeout'     => 5,
            'headers'     => array('Accept' => $imgType)
        ) );

        // Check for error
        if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        }

        // Parse remote HTML file & look for images (jpeg)
        $reqStatus = wp_remote_retrieve_response_code( $response );
        if( $reqStatus == 200 ) {
            $reqHeaders = wp_remote_retrieve_headers( $response );
            $reqHeadersArray = $reqHeaders->getAll();
            if( is_array($reqHeadersArray) && sizeof($reqHeadersArray) > 0 ) {
                if( $reqHeadersArray['content-type'] == 'image/jpeg' ) {
                    // Get the image from Body
                    $reqBody = wp_remote_retrieve_body( $response );
                    // Stop if any errors found
                    if ( is_wp_error( $reqBody ) ) return $reqBody->get_error_message();
                    // If no errors create Image file and save it into a tmp folder in WP Upload Dir
                    $im = imagecreatefromstring($reqBody);
                    if ( $im !== false & defined('AUTOBLOGWPM_IMGTMPFOLDER') ) {
                        $uploadDir = AUTOBLOGWPM_IMGTMPFOLDER;
                        $createUploadDir = wp_mkdir_p($uploadDir);
                        if($createUploadDir === true ) {
                            $newImage = $uploadDir . DIRECTORY_SEPARATOR . $post_id . "." . $fileExt;
                            imagejpeg($im, $newImage);
                            imagedestroy($im);

                            return $newImage;
                        }
                    }
                }
            }
        }
    }

    /**
    * set the extension of a file based on the mime_type
    * usefull for images 'image/jpeg' => 'jpeg'
    * can be extended if more mime_type are used
    */
    private function getExtension ($mime_type){
        $extensions = array('image/jpeg' => 'jpeg',
                            'text/xml' => 'xml'
                            );

        // Add as many other Mime Types / File Extensions as you like

        return $extensions[$mime_type];
    }

    /**
     * update image Meta
     */
    public function updateImageMeta( $attchId, $postParent, $caption = ''){

        $postarr = array(
            'ID' => $attchId,
            'post_excerpt' => $caption,
            'post_parent'  => $postParent
        );
        wp_update_post($postarr);
        // $attacementExcerpt = get_post_field('post_excerpt', $attchId);
        // echo '<pre>' . $attchId; print_r($attacementExcerpt); echo '</pre>';
    }

}








