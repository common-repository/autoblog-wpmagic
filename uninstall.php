<?php

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options
delete_option('autoblog-wpm-free_default');
delete_option('autoblog-wpm-free_data');
delete_option('autoblog-wpm-free_filesstatus');
delete_option('autoblog-wpm-free_plugin_options');

// Delete options in Multisite
delete_site_option('autoblog-wpm-free_files');
