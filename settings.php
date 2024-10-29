<?php
/*
 * Define global constants
 */
$plugin_data = get_file_data( plugin_dir_path(__FILE__) . dirname( plugin_basename( __FILE__ ) ) . '.php', array( 'name'=>'Plugin Name', 'version'=>'Version', 'text'=>'Text Domain', 'uri'=>'Plugin URI' ) );

define( 'AUTOBLOGWPM_DIR', dirname( plugin_basename( __FILE__ ) ) );         // Plugin Dir
define( 'AUTOBLOGWPM_BASEFILE', dirname( plugin_basename( __FILE__ ) ) );    // Plugin Folder Name
define( 'AUTOBLOGWPM_URL', plugin_dir_url( __FILE__ ) );                     // Plugin URI
define( 'AUTOBLOGWPM_PATH', plugin_dir_path(__FILE__) );                     // Plugin Path
define( 'AUTOBLOGWPM_SLUG', dirname( plugin_basename( __FILE__ ) ) );        // Plugin slug name
define( 'AUTOBLOGWPM_NAME', $plugin_data['name'] );                          // Plugin Name
define( 'AUTOBLOGWPM_VERSION', $plugin_data['version'] );                    // Plugin Ver.
define( 'AUTOBLOGWPM_TEXT', $plugin_data['text'] );                          // Plugin Dscr.
define( 'AUTOBLOGWPM_WEBSITE', $plugin_data['uri'] );                        // Plugin Webage link
define( 'AUTOBLOGWPM_MENUICON', 'data:image/svg+xml;base64,PHN2ZyBoZWlnaHQ9IjE3OSIgdmlld0JveD0iMCAwIDE3OTIgMTc5MiIgd2lkdGg9IjE3OSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBmaWxsPSIjZmZmZmZmIiBkPSJNODg4IDExODRsMTE2LTExNi0xNTItMTUyLTExNiAxMTZ2NTZoOTZ2OTZoNTZ6bTQ0MC03MjBxLTE2LTE2LTMzIDFsLTM1MCAzNTBxLTE3IDE3LTEgMzN0MzMtMWwzNTAtMzUwcTE3LTE3IDEtMzN6bTgwIDU5NHYxOTBxMCAxMTktODQuNSAyMDMuNXQtMjAzLjUgODQuNWgtODMycS0xMTkgMC0yMDMuNS04NC41dC04NC41LTIwMy41di04MzJxMC0xMTkgODQuNS0yMDMuNXQyMDMuNS04NC41aDgzMnE2MyAwIDExNyAyNSAxNSA3IDE4IDIzIDMgMTctOSAyOWwtNDkgNDlxLTE0IDE0LTMyIDgtMjMtNi00NS02aC04MzJxLTY2IDAtMTEzIDQ3dC00NyAxMTN2ODMycTAgNjYgNDcgMTEzdDExMyA0N2g4MzJxNjYgMCAxMTMtNDd0NDctMTEzdi0xMjZxMC0xMyA5LTIybDY0LTY0cTE1LTE1IDM1LTd0MjAgMjl6bS05Ni03MzhsMjg4IDI4OC02NzIgNjcyaC0yODh2LTI4OHptNDQ0IDEzMmwtOTIgOTItMjg4LTI4OCA5Mi05MnEyOC0yOCA2OC0yOHQ2OCAyOGwxNTIgMTUycTI4IDI4IDI4IDY4dC0yOCA2OHoiLz48L3N2Zz4=' );        // Plugin Icon
define( 'AUTOBLOGWPM_TMPFOLDER', 'wpm_tmp' );                                // Folder name where images are saved
define( 'AUTOBLOGWPM_UPDATES', 'https://www.wpmagic.cloud' );               // Plugin update link
define( 'AUTOBLOGWPM_OPTION_PREFIX', dirname( plugin_basename( __FILE__ ) ) . '_' );   // Plugin option prefix
define( 'AUTOBLOGWPM_ARTICLE_MIN_CHARS', 100 );                              // min. nr. of chars to post an article
define( 'AUTOBLOGWPM_RESET', false );                                        // reset the list of files - set to true and reload admin page
define( 'AUTOBLOGWPM_DEBUG', true  );                                        // if set to true will show some info when article is manualy published
define( 'AUTOBLOGWPM_LICENSE_KEY_VIEW_URL', AUTOBLOGWPM_UPDATES . '/wp-json/licenseapi/v1/view/licensekey=' );  // Plugin license key actions link

/*
 * Define Folder Path & Url where temporary images are saved
 */
$uploadWPDir    = wp_upload_dir();
$uploadDir      = $uploadWPDir['basedir'] . DIRECTORY_SEPARATOR . AUTOBLOGWPM_TMPFOLDER;
$uploadDirUrl   = $uploadWPDir['baseurl'] . DIRECTORY_SEPARATOR . AUTOBLOGWPM_TMPFOLDER;

define( 'AUTOBLOGWPM_IMGTMPFOLDER', $uploadDir );                            // Folder path where images are saved
define( 'AUTOBLOGWPM_IMGTMPFOLDERURL', $uploadDirUrl );                      // Folder Url path where images are saved

/**
 * Define the Articles Folder
 * Now the Slug name = autoblog-wpm-free
 */
define( 'AUTOBLOGWPM_ARTICLES_DIR', $uploadWPDir['basedir'] . DIRECTORY_SEPARATOR . AUTOBLOGWPM_SLUG . DIRECTORY_SEPARATOR);     // Directory where articles are uploaded
define( 'AUTOBLOGWPM_ARTICLES_DIR_URL', $uploadWPDir['baseurl'] . DIRECTORY_SEPARATOR . AUTOBLOGWPM_SLUG . DIRECTORY_SEPARATOR);     // Directory where articles are uploaded
// Create a Folder if not exists already 'autoblog-wpm-free' in Upload Folder and upload Articles with FTP
if (!file_exists( AUTOBLOGWPM_ARTICLES_DIR )) {
    mkdir(AUTOBLOGWPM_ARTICLES_DIR, 0755, true);
}

/**
 * Allowed ext for scaned files
 */
$allowedFilesExt = array('txt', 'doc');

/**
 * Posible status for scaned files
 */
$fileStatus = array(
    'pending'   => 'pending_scan', 
    'scaned'    => 'scaned',
    'not_found' => 'not_found',
    'error'     => 'error',
    'bad'       => 'bad_words',
    'published' => 'published'
);
?>