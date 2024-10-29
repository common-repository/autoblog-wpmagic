<?php
/*
 * Plugin Name: Autoblog WPMagic
 * Plugin URI:  https://wpmagic.cloud
 * Description: Autoblog WPMagis is a WordPress Plugin for automatically publishing posts from a specific folder.
 * Version:     1.0.0
 * Author:      WPMagic
 * License:     GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * @package autoblog-wpm-free
 * Text Domain: autoblog-wpm-free
 * Domain Path: /languages
 */

namespace autoblogwpm;

/**
 * blocking direct access to your plugin PHP files
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * define pluglin const
 */
require_once( 'settings.php' );

// The class that contains the plugin info.
require_once plugin_dir_path(__FILE__) . 'includes/class-info.php';


/**
 * The code that runs during plugin activation.
 */
function activation() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-activator.php';
    Activator::activate();
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\activation');

/**
 * The code that runs during plugin deactivation.
 */
function deactivation() {
    // clear any previous schedulers
    wp_clear_scheduled_hook(__NAMESPACE__ . '_admin_scheduler');
}
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\deactivation');

/**
 * Run the plugin.
 */
function run() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-plugin.php';
    $plugin = new Plugin();
    $plugin->run();
}
run();



/*
 * make sure the language file(s) are loaded
 */
load_plugin_textdomain(AUTOBLOGWPM_SLUG, false, AUTOBLOGWPM_PATH . '/languages' );
