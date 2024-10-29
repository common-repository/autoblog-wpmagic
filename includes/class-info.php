<?php

namespace autoblogwpm;

/**
 * The class containing informatin about the plugin.
 */
class Info
{
    /**
     * The plugin slug.
     *
     * @var string
     */
    const SLUG = AUTOBLOGWPM_SLUG;

    /**
     * The plugin version.
     *
     * @var string
     */
    const VERSION = AUTOBLOGWPM_VERSION;

    /**
     * The nae for the entry in the options table.
     *
     * @var string
     */
    const OPTION_NAME = AUTOBLOGWPM_OPTION_PREFIX . 'default';

    /**
     * The URL where your update server is located (uses wp-update-server).
     *
     * @var string
     */
    const UPDATE_URL = AUTOBLOGWPM_UPDATES;

    /**
     * Retrieves the plugin title from the main plugin file.
     *
     * @return string The plugin title
     */
    public static function get_plugin_title() {
        $path = plugin_dir_path(dirname(__FILE__)).self::SLUG.'.php';
        return get_plugin_data($path)['Name'];
    }

    /**
     * Retrieves the plugin text.
     *
     * @return string The plugin text
     */
    public static function get_plugin_text( $what = '' ) {
        /*
        * Define Plugin Texts
        */
        $infoTexts = array(
            "alert_1"               => sprintf(  
                                        "<div class='alert alert-info alert-dismissible fade show alerthelp1' role='alert'>
                                            <button type='button' class='close wpminfo1' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                                            <p class='mt-3 ml-2'>" . 
                                                __( "Replace me", "autoblog-wpm-free" ) . 
                                            "</p>
                                        </div>"
                                    ),
            "help_2"                => sprintf(
                                        __( "Debugging Info:", "autoblog-wpm-free" )
                                    ),
            "button_1"              => sprintf( 
                                        __( 'Submit', "autoblog-wpm-free" )
                                    ),
            "table_header"          => sprintf( 
                                        __( 'Files available for publishing as blog posts', "autoblog-wpm-free" )
                                    ),
            "default_pagination"    => sprintf( 
                                        __( 'Items per page', "autoblog-wpm-free" )
                                    ),
            "pending_scan"          => sprintf( 
                                        __( 'Waiting to be published', "autoblog-wpm-free" )
                                    ),
            "error"                 => sprintf( 
                                        __( 'Error', "autoblog-wpm-free" )
                                    ),
            "extnotallowed"         => sprintf( 
                                        __( 'File extension not allowed. You can delete this file.', "autoblog-wpm-free" )
                                    ),
            "rewrite_error"         => sprintf( 
                                        __( 'File extension not allowed or empty text', "autoblog-wpm-free" )
                                    ),
            "fileformat_error"      => sprintf( 
                                        __( 'File is not in desired format.', "autoblog-wpm-free" )
                                    ),
            "bad_words"             => sprintf( 
                                        __( 'Forbidden words', "autoblog-wpm-free" )
                                    ),
            "imgattached_succes"    => sprintf( 
                                        __( 'Image was attached to post.', "autoblog-wpm-free" )
                                    ),
            "imgattached_notfound"  => sprintf( 
                                        __( 'No image found.', "autoblog-wpm-free" )
                                    ),
            "photographer"          => sprintf( 
                                        __( 'Photographer: ', "autoblog-wpm-free" )
                                    ),
            "published"             => sprintf( 
                                        __( 'Published', "autoblog-wpm-free" )
                                    ),
            "posterror"             => sprintf( 
                                        __( 'Error creating the post', "autoblog-wpm-free" )
                                    ),
            "logs"                  => sprintf( 
                                        __( 'autoblog-wpm-free-logs', "autoblog-wpm-free" )
                                    ),
            "settings"              => sprintf( 
                                        __( 'Settings', "autoblog-wpm-free" )
                                    ),
            "account"               => sprintf( 
                                        __( 'Account', "autoblog-wpm-free" )
                                    ),
            "contactus"             => sprintf( 
                                        __( 'Contact Us', "autoblog-wpm-free" )
                                    ),
            "help"                  => sprintf( 
                                        __( 'Help', "autoblog-wpm-free" )
                                    ),
            "label1"                => sprintf( 
                                        __( 'Rewrite articles', "autoblog-wpm-free" )
                                    ),
            "search_file"           => sprintf( 
                                        __( 'Search file', "autoblog-wpm-free" )
                                    ),
            "file_id"               => sprintf( 
                                        __( 'File ID', "autoblog-wpm-free" )
                                    ),
            "info_1"                => sprintf( 
                                        __( 'The file was deleted.', "autoblog-wpm-free" )
                                    ),
            "info_2"                => sprintf( 
                                        __( 'The file(s) have been deleted.', "autoblog-wpm-free" )
                                    ),
            "info_3"                => sprintf( 
                                        __( 'The file is already published.', "autoblog-wpm-free" )
                                    ),
            "duplicate"                => sprintf( 
                                        __( 'Duplicate found.', "autoblog-wpm-free" )
                                    ),
            "settings_header"          => sprintf( 
                                        __( 'Autoblog WPMagic settings', "autoblog-wpm-free" )
                                    ),
        );

        if($what != '') return $infoTexts[$what];
        else return $infoTexts;
    }
}
