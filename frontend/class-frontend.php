<?php

namespace autoblogwpm;

/**
 * The code used on the frontend.
 */
class Frontend
{
    private $plugin_slug;
    private $version;
    private $option_name;
    private $settings;

    public function __construct($plugin_slug, $version, $option_name) {
        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
        $this->option_name = $option_name;
        $this->settings = get_option($this->option_name);
    }

    public function assets() {
        wp_enqueue_style($this->plugin_slug, plugin_dir_url(__FILE__).'css/autoblog-wpm-free-frontend.css', [], $this->version);
        wp_enqueue_script($this->plugin_slug, plugin_dir_url(__FILE__).'js/autoblog-wpm-free-frontend.js', ['jquery'], $this->version, true);
    }

    /**
     * Render the view using MVC pattern.
     */
    public function render() {

        // Model
        $settings = $this->settings;

        // Controller
        // Declare vars like so:
        // $var = $settings['slug'] ?? '';

        // View
        if (locate_template('partials/' . $this->plugin_slug . '.php')) {
            require_once(locate_template('partials/' . $this->plugin_slug . '.php'));
        } else {
            require_once plugin_dir_path(dirname(__FILE__)).'frontend/partials/view.php';
        }
    }

    /**
     * Example of using plugin to insert text in some page
     */
    function displayTextInSamplePage( $content ){
        if ( is_page( 'sample-page' ) ) {
            $content .= "<h3>plugin content</h3>";
            $content .= "more plugin content";
        }

        return $content;
    }
}
