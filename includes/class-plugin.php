<?php

namespace autoblogwpm;

/**
 * The main plugin class.
 */
class Plugin
{

    private $loader;
    private $plugin_slug;
    private $version;
    private $option_name;

    public function __construct() {
        $this->plugin_slug = Info::SLUG;
        $this->version     = Info::VERSION;
        $this->option_name = Info::OPTION_NAME;
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_frontend_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-loader.php';
        $this->loader = new Loader();
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'frontend/class-frontend.php';
        /**
         * get a copy of the WP List Table from WP Core as described here
         * https://codex.wordpress.org/Class_Reference/WP_List_Table
         * and extend it to use with our plugin in class-tables.php
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-list-table.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tables.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-attachimage.php';
    }

    private function define_admin_hooks() {
        global $allowedFilesExt;
        global $fileStatus;
        $plugin_admin = new Admin($this->plugin_slug, $this->version, $this->option_name, $allowedFilesExt, $fileStatus);
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'assets');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        // $this->loader->add_action('admin_menu', $plugin_admin, 'add_menus');                                 // add plugin menu as submenu of settings in admin menu
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menus');                              // add plugin menu in admin menu
        $this->loader->add_action(__NAMESPACE__ . '_admin_scheduler', $plugin_admin, 'admin_scheduler_hook');   // add scheduler for scaning the folder
        $this->loader->add_filter('set-screen-option', $plugin_admin, 'set_screen_option', 10, 3);              // add screen options to plugin page
        $this->loader->add_action('before_delete_post', $plugin_admin, 'delete_all_attached_media');            // delete all attached media when a post is permanently deleted
        $this->loader->add_filter('plugin_action_links_autoblog-wpm-free/autoblog-wpm-free.php', $plugin_admin, 'autoblog_wpm_add_plugin_page_settings_link'); // add a link into plugins list
    }

    private function define_frontend_hooks() {
        $plugin_frontend = new Frontend($this->plugin_slug, $this->version, $this->option_name);
        $this->loader->add_action('wp_enqueue_scripts', $plugin_frontend, 'assets');
        $this->loader->add_action('wp_footer', $plugin_frontend, 'render');
        $this->loader->add_filter('the_content', $plugin_frontend, 'displayTextInSamplePage');
    }

    public function run() {
        $this->loader->run();
    }
}
