<?php

namespace autoblogwpm;

/**
 * The code used in the admin.
 */
class Admin
{
    private $plugin_slug;
    private $version;
    private $option_name;
    private $settings;
    private $settings_group;
    private $allowedFilesExt;
    private $fileStatus;
    
    /**
     * call Info class
     */
    public $info;
    
    public function __construct($plugin_slug, $version, $option_name, $allowedFilesExt, $fileStatus) {
        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
        $this->option_name = $option_name;
        $this->settings = get_option($this->option_name);
        $this->settings_group = $this->option_name.'_group';
        $this->allowedFilesExt = $allowedFilesExt;
        $this->fileStatus = $fileStatus;
        $this->filesInfo = $this->getOptValues('filesstatus');

        $this->info = new Info();
    }

    /**
     * Generate settings fields by passing an array of data (see the render method).
     *
     * @param array $field_args The array that helps build the settings fields
     * @param array $settings   The settings array from the options table
     *
     * @return string The settings fields' HTML to be output in the view
     */
    private function custom_settings_fields($field_args, $settings) {
        $output = '';

        if(isset($field_args))
        foreach ($field_args as $field) {
            $slug = $field['slug'];
            $setting = $this->option_name.'['.$slug.']';
            $label = esc_attr__($field['label'], 'autoblog-wpm-free');
            $output .= '<h3><label for="'.$setting.'">'.$label.'</label></h3>';

            if ($field['type'] === 'text') {
                $output .= '<p><input type="text" id="'.$setting.'" name="'.$setting.'" value="'.$settings[$slug].'"></p>';
            } elseif ($field['type'] === 'textarea') {
                $output .= '<p><textarea id="'.$setting.'" name="'.$setting.'" rows="10">'.$settings[$slug].'</textarea></p>';
            }
        }

        return $output;
    }

    public function assets() {
        wp_enqueue_style($this->plugin_slug, plugin_dir_url(__FILE__).'css/autoblog-wpm-free-admin.min.css', [], $this->version);
        wp_enqueue_style( 'fontawesome',  plugin_dir_url(__FILE__).'css/fontawesome/all.min.css' );
        
        wp_enqueue_script($this->plugin_slug, plugin_dir_url(__FILE__).'js/autoblog-wpm-free-admin.min.js', ['jquery'], $this->version, true);
    }

    public function register_settings() {
        register_setting($this->settings_group, $this->option_name);
    }

    public function add_menus() {
        $plugin_name = $this->info->get_plugin_title();
        add_submenu_page(
            'options-general.php',
            $plugin_name,
            $plugin_name,
            'manage_options',
            $this->plugin_slug,
            [$this, 'render']
        );
    }

    /**
     * Render the view using MVC pattern.
     */
    public function render() {

        // Generate the settings fields
        $field_args = [
            [
                'label' => 'Text Label',
                'slug'  => 'text-slug',
                'type'  => 'text'
            ],
            [
                'label' => 'Textarea Label',
                'slug'  => 'textarea-slug',
                'type'  => 'textarea'
            ]
        ];

        // Model
        // $settings = $this->settings;
        
        // Controller
        // $heading = $this->info->get_plugin_title();
        // $settings_group = $this->settings_group;
        // $fields = $this->custom_settings_fields($field_args, $settings);
        // $submit_text = esc_attr__('Submit', 'autoblog-wpm-free');
        // $defaultopt = $this->option_name;
        
        // View
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/view.php';
    }

    /**
     * add plugin menu direct in admin menu 
     * (not as submenu of settings menu)
     */
    public function add_admin_menus() {
        $plugin_name = $this->info->get_plugin_title();
        // add settings page
        $plugin_settingspage = add_menu_page(
            $plugin_name,
            $plugin_name,
            'manage_options',
            $this->plugin_slug,
            [$this, 'autoblogwpmview'],
            AUTOBLOGWPM_MENUICON
        );

        // Add submenu page with same slug as parent to ensure no duplicates
        $plugin_submenu = add_submenu_page(
            null,
            $plugin_name,
            esc_attr__('autoblog-wpm-free-logs', 'autoblog-wpm-free'),
            'manage_options',
            esc_attr__('autoblog-wpm-free-logs', 'autoblog-wpm-free'),
            [$this, 'autoblogwpmlogs'],
            2
        );

        // Add submenu page with same slug as parent to ensure no duplicates
        $plugin_submenu = add_submenu_page(
            $this->plugin_slug,
            $plugin_name,
            esc_attr__('Settings', 'autoblog-wpm-free'),
            'manage_options',
            esc_attr__('autoblog-wpm-free-settings', 'autoblog-wpm-free'),
            [$this, 'autoblogwpmsettings'],
            3
        );

        // Add submenu page with same slug as parent to ensure no duplicates
        $plugin_submenu = add_submenu_page(
            $this->plugin_slug,
            $plugin_name,
            esc_attr__('Help', 'autoblog-wpm-free'),
            'manage_options',
            esc_attr__('autoblog-wpm-free-help', 'autoblog-wpm-free'),
            [$this, 'autoblogwpmhelp'],
            4
        );

        // Add submenu page with same slug as parent to ensure no duplicates
        $plugin_submenu = add_submenu_page(
            $this->plugin_slug,
            $plugin_name,
            esc_attr__('Contact Us', 'autoblog-wpm-free'),
            'manage_options',
            esc_attr__('autoblog-wpm-free-contact', 'autoblog-wpm-free'),
            [$this, 'autoblogwpmcontactus'],
            6
        );

        /**
         * add screen_options to plugin page
         */
        add_action("load-$plugin_settingspage", [$this, 'sample_screen_options']);
    }

    /**
     * add Screen Options to plugin page
     */
    public function sample_screen_options() { 
        $screen = get_current_screen();
        
        // get out of here if we are not on our settings page
        if(!is_object($screen))
            return;
    
        $args = array(
            'label' => esc_attr__('Items per page', 'autoblog-wpm-free'),
            'default' => 5,
            'option' => 'items_per_page'
        );
        add_screen_option( 'per_page', $args );
    }

    /**
     * Save plugin 'per_page' option
     */
    public function set_screen_option($status, $option, $value) {
        if ( 'items_per_page' == $option ) return $value;
        return $status;
    }

    /**
     * Post from folder plugin Admin View.
     */
    public function autoblogwpmview() {

        $heading = $this->info->get_plugin_title();

        /**
         * reset the list with the files on request
         */
        if( AUTOBLOGWPM_RESET ) {
            delete_option(AUTOBLOGWPM_OPTION_PREFIX . 'data');
            delete_option(AUTOBLOGWPM_OPTION_PREFIX . 'filesstatus');
        }

        /** *************************** RENDER TABLE TEST PAGE ********************************
         *******************************************************************************
        * This function renders the admin page and the files list table. Although it's
        * possible to call prepare_items() and display() from the constructor, there
        * are often times where you may need to include logic here between those steps,
        * so we've instead called those methods explicitly. It keeps things flexible, and
        * it's the way the list tables are used in the WordPress core.
        */

        //Create an instance of our package class...
        $this->filesListTable = new Files_List_Table($this->fileStatus);
        
        //Fetch, prepare, sort, and filter our data...
        $this->filesListTable->prepare_items();
        ?>

        <div class="wrap filestable">

            <h2>
                <span class="dashicons dashicons-media-text iconv"></span>
                <?php echo esc_attr__('Files available for publishing as blog posts', 'autoblog-wpm-free'); ?>
                <?php // echo '(from ' . AUTOBLOGWPM_ARTICLES_DIR . ')'; ?>
            </h2>
            
            <!-- <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
                <p>This page demonstrates the use of the <tt><a href="http://codex.wordpress.org/Class_Reference/WP_List_Table" target="_blank" style="text-decoration:none;">WP_List_Table</a></tt> class in plugins.</p> 
                <p>For a detailed explanation of using the <tt><a href="http://codex.wordpress.org/Class_Reference/WP_List_Table" target="_blank" style="text-decoration:none;">WP_List_Table</a></tt>
                class in your own plugins, you can view this file <a href="<?php echo admin_url( 'plugin-editor.php?plugin='.plugin_basename(__FILE__) ); ?>" style="text-decoration:none;">in the Plugin Editor</a> or simply open <tt style="color:gray;"><?php echo __FILE__ ?></tt> in the PHP editor of your choice.</p>
                <p>Additional class details are available on the <a href="http://codex.wordpress.org/Class_Reference/WP_List_Table" target="_blank" style="text-decoration:none;">WordPress Codex</a>.</p>
            </div> -->
            
            <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
            <form id="files-filter" method="get">

                <!-- Now we can render the completed list table -->
                <?php $this->filesListTable->views() ?>

                <!-- display the search box -->
                <input type="hidden" name="page" value="<?php if( defined('AUTOBLOGWPM_SLUG') ) echo AUTOBLOGWPM_SLUG; ?>" />
                <?php $this->filesListTable->search_box('Search file', 'search_id'); ?>
                
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
                
                <!-- Now we can render the completed list table -->
                <?php $this->filesListTable->display() ?>
            </form>
            
        </div>
        <?php
    }

    /**
     * this function is called Twice Daily
     * scan folder for new files * add them to file option
     * show all files finded in table in autoblogwpmview()
     */
    public function admin_scheduler_hook() {
        /**
         * might need 
         * define('ALTERNATE_WP_CRON', true);
         * in order to work on localhost
         */
        
        /**
         * update files array
         */
        $this->updateFilesTable();

        /**
         * this code gets in admin_scheduler_hook
         * publish the next article with status = 'pending_scan' from the list
         */
        $this->publishNextArticle();  
    }

    /**
     * Formats filesize in human readable way.
     *
     * @param file $file
     * @return string Formatted Filesize, e.g. "113.24 MB".
     */
    function calcSize($size,$accuracy=2) {
        $output = '';
        $units = array('b','Kb','Mb','Gb');
        foreach($units as $n=>$u) {
            $div = pow(1024,$n);
            if($size > $div) $output = number_format($size/$div,$accuracy). ' ' . $u;
        }

        return $output;
    }

    /**
     * scan dir for files (articles)
     */
    public function dirScan(){
        $articles = preg_grep('~\.(txt|doc)$~', $this->myscandir(AUTOBLOGWPM_ARTICLES_DIR, $desc=0));
        $k = 1;
        /**
         * prepare data[] array for show in table autoblogwpmview()
         * check if 'status' for the file have already been saved into option_filesstatus
         * option_filesstatus contains an array('filename','status') with the file name & status of file
         * option_filesstatus is updated every time when a file is used (scaned)
         */
        $data = array();
        if( isset($articles) && is_array($articles) ) foreach($articles as $file){
            $addData = true; if( stristr($file, '._') !== false ) $addData = false;
            // get previous info about the file if any
            $savedFileInfo = $this->getOptValues( 'filesstatus' );
            // prepare file array to save in data option
            if( $addData ) {
                $data[] = array(
                    'ID'            => $k,
                    'filename'      => $file,
                    'size'          => $this->calcSize( filesize(AUTOBLOGWPM_ARTICLES_DIR . $file) ),
                    // 'plagiarism'    => (isset($savedFileInfo[$file][1]) && ($savedFileInfo[$file][1] != '' & $savedFileInfo[$file][1] != '-')) ? $savedFileInfo[$file][1] : 0,
                    'status'        => (isset($savedFileInfo[$file][2]) && $savedFileInfo[$file][2] != '') ? $savedFileInfo[$file][2] : $this->fileStatus['pending'],
                    'info'          => (isset($savedFileInfo[$file][3]) && $savedFileInfo[$file][3] != '') ? $savedFileInfo[$file][3] : '',  
                );
                
                $k++;
            }
        }

        return $data;
    }

    /**
     * scan folder
     */
    function myscandir($dir, $desc=0) {
        return(scandir($dir,$desc));
    }

    /**
     * get values saved in options
     */
    function getOptValues( $whichOpt = 'data') {
        $option = get_option(AUTOBLOGWPM_OPTION_PREFIX . $whichOpt);

        return $option;
    }

    /**
     * save info about scaned files into an option
     * update this info into file table on cron run
     */
    private function updateFileInfoOption( $fileInfoToPush = array() ) {
        //get saved data
        $savedOptFileInfo = $this->filesInfo;
        /**
         * update $savedOptFileInfo 
         * array(filename, plagiarism, status, info)
         */
        $savedOptFileInfo[$fileInfoToPush[0]] = array($fileInfoToPush[0], $fileInfoToPush[1], $fileInfoToPush[2], $fileInfoToPush[3]);
        
        // update option with new array
        update_option(AUTOBLOGWPM_OPTION_PREFIX . 'filesstatus', $savedOptFileInfo);
    }

    /**************************************************************************
     * publish next article with a status = 'pending_scan' from the option_data
     **************************************************************************/
    public function publishNextArticle( $fileId = '', $redirect = false ) {
        
        // check time passed from last publishing
        $lasPublished = false;
        if( ! $redirect ){ // not manual published
            $lasPublished = $this->autoblog_wpm_isPublishingIntervalAllowed();
            if( $lasPublished === false ) return false; // time interval for publishing not allowed - exit
        }
        
        // check if files exists
        $data_option = $this->getOptValues();// list of files
        
        /**
         * check if some file left to publish
         */
        $goOnSignal = false;
        if(is_array($data_option)) foreach($data_option as $key => $checkFileStatus){
            if($checkFileStatus['status'] == $this->fileStatus['pending']) {
                $goOnSignal = true;
                // will leave the foreach loop and also the if statement
                break;
            }
        }

        /**
         * get the data files array id for a particular file
         */
        $pendingFilesIds = array();
        if( is_array($data_option) ) foreach($data_option as $key => $filesToScan){
            // loop only pending files
            if( $filesToScan['status'] == $this->fileStatus['pending'] ){
                // publish a requested article (manual publishing)
                if( isset($fileId) && $fileId >= 0 ) {
                    if( $fileId == $filesToScan['ID'] ) {
                        $fileId = $key;
                        // article was selected - leave the loop
                        break;
                    }
                }
                // if no specific article selected, create an array with pending articles IDs to select a random article (automatic publishing)
                $pendingFilesIds[] = $filesToScan['ID'];
            }
        }
        
        // select a random file to publish
        if( is_array($pendingFilesIds) && ! empty($pendingFilesIds) && $fileId == '' ) {
            $randomFieleNr = rand(0, (count($pendingFilesIds) - 1));
            $fileId = $pendingFilesIds[$randomFieleNr];
        }
        
        if( $goOnSignal && is_array($data_option) ){
            // 1. Get the content of the scanded file in an array. Select random file if fileId == ''.
            $fileContent = array(); $scanedFileID = ''; $getfile = array();
            // publish one particular article
            $getfile = $this->getArticle( $fileId );
            if(isset($getfile['fileContent']))  $fileContent = $getfile['fileContent'];
            if(isset($getfile['scanedFileID'])) $scanedFileID = $getfile['scanedFileID'];
            
            // 2. publish article
            if((isset($fileContent) & sizeof($fileContent) > 0) & isset($scanedFileID)){
                $result = $this->publishArticle($fileContent, $scanedFileID);
                /**
                 * put a flag published in file table
                 */
                $this->updateFileInfoOption(
                    array(
                        $data_option[$scanedFileID]['filename'], 
                        '-', 
                        $this->fileStatus['published'], 
                        esc_attr__("Published (Post ID = " . $result . ")", 'autoblog-wpm-free')
                    )
                );
                // update the files table to reflect the changes in table view
                $this->updateFilesTable();

                // update the published time in plugin_options
                $plugin_options = get_option(AUTOBLOGWPM_OPTION_PREFIX . 'plugin_options');
                if( is_array($plugin_options) ){
                    $plugin_options['last_post_published_time'] = time();
                    update_option(AUTOBLOGWPM_OPTION_PREFIX . 'plugin_options', $plugin_options);
                }

                // redirect if requested - on manual publish
                if( $redirect ){
                    $urlr = '?page=autoblog-wpm-free&paged=' . filter_input( INPUT_GET, 'paged', FILTER_VALIDATE_INT);
                    if( isset($_GET['filestatus']) && $_GET['filestatus'] != '' ) $urlr .= '&filestatus=' . sanitize_text_field($_GET['filestatus']);
                    $this->autoblog_wpm_redirectUrl($urlr, 'meta', 5);
                }
            }

            return true;
        }

        /**
         * no files left to publish
         * add to logs
         */
        $this->addToLogs('Info', 'no files left to publish...');

        return false;
    }

    /**
     * get the article content
     */
    private function getArticle( $fileId = '' ){
        // 1. get the list of files
        $data_option = $this->getOptValues();
        $scanedFileID = 0;

        // 2. Select a random file with status='pending_scan' 
        $fileToScan = '';
        if( is_array($data_option) && sizeof($data_option) > 0 ){
            
            // save the file ID for later
            if( isset($fileId) && ($fileId >= 0 & $fileId <= count($data_option)) ) $scanedFileID = $fileId;
            else if( isset($fileId) && $fileId == 'rnd') {$scanedFileID = rand(0,count($data_option));}
            
            if( isset($data_option[$scanedFileID]) ) {
                // select a random file from the list or the file with specific ID
                $file = $data_option[$scanedFileID];
                // check if file not used already
                if( $file['status'] == $this->fileStatus['pending'] ) {
                    // check if file extension is allowed
                    if($this->isFileExtensionAllowed($file['status'])){
                        $fileToScan = $file['filename'];
                    } else {
                        // save to filesstatus option - use $this->filesInfo to get saved info from option
                        $this->updateFileInfoOption(
                            array(
                                $file['filename'], 
                                // $file['plagiarism'], 
                                $file['status'], 
                                esc_attr__('File extension not allowed. You can delete this file.', 'autoblog-wpm-free')
                            )
                        );
                        // add to logs
                        $this->addToLogs($this->info->get_plugin_text("extnotallowed"), $file['filename']);
                        exit;
                    }
                } else { 
                    // file already used
                    $this->addToLogs($this->info->get_plugin_text("info_3"), $file['filename']);
                }
            }
        }
        
        /**
         * 3. get the content of the file as array
         * echo 'file_get_contents : ', ini_get('allow_url_fopen') ? 'Enabled' : 'Disabled';
         */
        $fileContent = array();
        // check if file extension is allowed
        if($fileToScan != '' && $this->isFileExtensionAllowed($fileToScan)) {
            // url = AUTOBLOGWPM_URL . AUTOBLOGWPM_ARTICLES_DIR . $fileToScan; 
            $fileToScanOnDisk = AUTOBLOGWPM_ARTICLES_DIR . $fileToScan;
            $this->addToLogs('Info', 'File: ' . $fileToScanOnDisk);
            // check if file exist on Disk
            if( file_exists($fileToScanOnDisk) ) {
                $opendFile = $this->utf8_fopen_read($fileToScanOnDisk, "r");
                // Read Single Line - fgets()
                // Output one line until end-of-file
                while(!feof($opendFile)) {
                    $fileContent[] = trim(fgets($opendFile)); // . "<br />\n"
                }
                fclose($opendFile);
            } else { 
                // file don't exist save the status in info column and exit
                // save to filesstatus option
                $this->updateFileInfoOption(
                    array(
                        $fileToScan, 
                        '-', 
                        $this->fileStatus['not_found'], 
                        'File don\'t exists.'
                    )
                );
                // add to logs
                $this->addToLogs('Info', 'File don\'t exists. ' . $fileToScan);
                exit;
            }
        }
        
        if( isset($scanedFileID) ) {
            // return the article
            return array('fileContent' => array_values(array_filter($fileContent)), 'scanedFileID' => $scanedFileID); // get rid of empty lines
        }
    }

    /**
     * While opening a file with multibyte data (Ex: données multi-octets), faced some issues with the encoding. 
     * Got to know that it uses  windows-1250. 
     * Used iconv to convert it to UTF-8 and it resolved the issue.
     * ref. https://www.php.net/manual/en/function.fopen.php
     */
    private function utf8_fopen_read($fileName) {
        // $fc = iconv('windows-1250', 'UTF-8//IGNORE', utf8_encode(file_get_contents($fileName)));
        $fc = utf8_encode(file_get_contents($fileName));
        $handle=fopen("php://memory", "rw");
        fwrite($handle, $fc);
        fseek($handle, 0);

        return $handle;
    }

    /**
     * check if extension allowed
     */
    private function isFileExtensionAllowed( $fileName = '' ) {
        if(array_search(substr($fileName, -3), $this->allowedFilesExt) >= 0) return true;
        else return false;
    }

    /****************************************************************
     * Publish the article
     * 1. check if text start with 'Title:' (first element from array)
     * 2. get Title, Summary, Keywords, Article Body
     ***************************************************************/
    private function publishArticle( $txtArray = '', $scanedFileID = '' ){ 
        
        // 1. get Title, Summary, Keywords, Article Body & search replace some chars in text
        $articlePartsArray = $this->articlePartsArray($txtArray, $scanedFileID);
        
        // 2. check if $articlePartsArray['Body'] exists
        if( is_array($articlePartsArray) & ! empty($articlePartsArray) & (isset($articlePartsArray['Body']) && !empty($articlePartsArray['Body'])) & (isset($articlePartsArray['Title']) && $articlePartsArray['Title'] != '') ){
            
            $noGoSignal = false;
            /*
            * search article title among existing Post to be sure there are no duplicates articles posted
            * ››› even if we check the fetched articles with the chached ones the post sometimes are repeated during the day
            * √ need to check within the last 100 posted articles the same title
            * √ if the title not found » create the article
            */
            
            $getLatestPostsObj = $this -> getLatestArticlesObj();
            
            // check the article title between the last 100 posts on the site
            foreach($getLatestPostsObj as $latestPost) {
                if($latestPost->post_title == $articlePartsArray['Title']) $noGoSignal = true;
            }
            
            if( ! $noGoSignal ) { // no duplicate found
                // get the default category for posts
                // $default_category = get_option('default_category');
                // get list of files
                $data_option = $this->getOptValues();

                /**
                 * get the $articlePartsArray['Body'] into a string
                 * error flag, put message in info
                 * get the body string
                */
                $body = ''; $excerpt = '';
                $tags = array();
                if( isset($articlePartsArray['Keywords']) ) {
                    $articleKeywords = esc_attr($articlePartsArray['Keywords']);
                    if(stristr($articlePartsArray['Keywords'], ",") !== false ) $tags = explode(',', $articleKeywords);
                    else if($articlePartsArray['Keywords'] != 'Article Body:')  $tags = array($articleKeywords);
                    $tags = array_map(function($val) { return str_replace(" ", "-", trim($val)); }, $tags);
                }
                // if no tags founded than add default category as a tag
                if( empty($tags) ) $tags = array($this->getCategory());
                
                // get the $articlePartsArray['Body'] into a string
                $body = $this->getArticleBody($articlePartsArray['Body'], $scanedFileID);
                
                // check if body received and post the content
                if( !is_array($body) && mb_strlen($body, "UTF-8") > AUTOBLOGWPM_ARTICLE_MIN_CHARS ) {

                    if( isset($articlePartsArray['Summary']) ) {
                        $excerpt = $articlePartsArray['Summary'];
                    }

                    // Set the Article Title
                    $articleTitle = $articlePartsArray['Title'];
                    
                
                    // prepare the post attributes
                    $postarr = array (
                        'post_title'    => $articleTitle,
                        'post_content'  => $body,
                        'post_status'   => 'publish',
                        'post_type'     => 'post',
                        'tags_input'    => $tags,
                        'post_excerpt'  => $excerpt
                    );
                    
                    // create post
                    $result = $this->create_custom_post( $postarr );
                    if ( ! is_wp_error($result) ){
                        if($result > 0) {
                            // ********* post created **********
                            /**
                             * update post meta with custom fields
                             */
                            if( isset($articlePartsArray['Custom-Fields']) ) $this->addPostMetaFields($result, $articlePartsArray['Custom-Fields']);
                        }
                    } else {
                        // error creating the post
                        $this->updateFileInfoOption(
                            array(
                                $data_option[$scanedFileID]['filename'], 
                                '-', 
                                $this->fileStatus['error'], 
                                esc_attr__('Error creating the post', 'autoblog-wpm-free')
                            )
                        );
                        // update the files table to reflect the changes in table view
                        $this->updateFilesTable();
                        // add to logs
                        $this->addToLogs('Info', $this->info->get_plugin_text("posterror"));
                        // publish next article
                        // $this->publishNextArticle();
                        exit;
                    }
                }
            } else {
                // duplicate post found
                // add to logs
                $this->addToLogs('Info', $this->info->get_plugin_text("duplicate"));
                // return 'duplicate post found';
                exit;
            }

            // 3. check if empty array & get another article
            if( empty($articlePartsArray) ) {
                // sleep(5); // wait 5s
                // $this->publishNextArticle();
            }
            
            // add to logs
            if( isset($tags) ) $this->addToLogs('Tags:', json_encode($tags));
            if( isset($articlePartsArray['Title']) ) $this->addToLogs('Title:', $articlePartsArray['Title']);
            if( isset($body) ) $this->addToLogs('Body:', strip_tags($body));
            
            if( isset($result) && $result > 0 ) return $result;
        }

        return false;
    }

    /**
     * split the raw article array into parts
     * (tile, body, ..)
     */
    private function articlePartsArray( $article, $scanedFileID ){
        $aTitle = '';
        $aWCount = '';
        $aSummary = '';
        $aKeywords = '';
        $aBody = array();
        
        /**
         * only take in consideration files in format:
         * Title: 
         * Word Count:
         * Summary:
         * Keywords:
         * Article Body:
         */
        
        $result = array(); $customFields = array();
        if( is_array($article) ) for($counter = 0; $counter <= count($article) - 1; ++$counter) {
            if ($article[$counter] == 'Title:')         $result['Title']    = $article[$counter+1]; // get the next line from array after Title:
            if ($article[$counter] == 'Word Count:')    $result['WCount']   = $article[$counter+1]; // get the next line from array after Word Count:
            if ($article[$counter] == 'Summary:')       $result['Summary']  = $article[$counter+1]; // get the next line from array after Summary:
            if ($article[$counter] == 'Keywords:')      $result['Keywords'] = $article[$counter+1]; // get the next line from array after Keywords:
            if ($article[$counter] == 'Custom Fields:') {
                $get_custom_fields  = array_map('trim', explode(',', $article[$counter+1]));         // get the next line from array after Custom Fields:
                if( is_array($get_custom_fields) )  foreach($get_custom_fields as $cfield) $customFieldsArray[] = array_map('trim', explode('=', $cfield));  
                if( is_array($customFieldsArray) ){
                    for( $i = 0; $i <= count($customFieldsArray) -1; $i++){
                        $customFields[$customFieldsArray[$i][0]] = $customFieldsArray[$i][1];
                    }
                }
                $result['Custom-Fields'] = $customFields;
            }
            if ($article[$counter] == 'Article Body:') {
                $b = $counter+1;
                if (isset($b)) for($i = $b; $i <= count($article); $i++) {
                    if(!empty($article[$i])) $result['Body'][] = $article[$i]; // get the next lines from array after Article Body: till the end of array
                }
            }
        }
        
        /**
         * check if file is not in the desired format
         * if not place a info
         */
        if($article[0] != 'Title:'){
            // save to filesstatus option
            $data_option = $this->getOptValues();
            $this->updateFileInfoOption(
                array(
                    $data_option[$scanedFileID]['filename'], 
                    '-', 
                    $this->fileStatus['error'], 
                    esc_attr__('File is not in desired format.', 'autoblog-wpm-free')
                )
            );
            // update the files table to reflect the changes in table view
            $this->updateFilesTable();
            // add to logs
            $this->addToLogs($this->info->get_plugin_text("fileformat_error"), $data_option[$scanedFileID]['filename']);
            $filestatus = ( isset($_GET['filestatus']) ) ? sanitize_text_field($_GET['filestatus']) : '';
            $this->autoblog_wpm_redirectUrl('?page=autoblog-wpm-free&filestatus=' . $filestatus. 'js', 1 );
            exit;
            return '';
        }
        
        return $result;
    }
        
    
    /**
     * Create a custom post
     */
    function create_custom_post($postarr = array()) {
        $post = wp_insert_post($postarr);

        return $post;
    }

    /**
     * get article body
     */
    private function getArticleBody( $rawBody = array(), $fileID ){
        $bodyString = ''; $body = '';
        
        // try replacing some bad chars in text
        $toReplace      = array("/^\?\s/s", "/Mediterranean/"); // regex match any line starting with ? one space space until chars  (https://regex101.com) | https://www.functions-online.com/preg_replace.html
        $replaceWith    = array("&bull;&nbsp;", "<b>Mediterranean</b>");

        // join array rows into a string
        if( is_array($rawBody)) foreach($rawBody as $row){
            $nrow = preg_replace($toReplace, $replaceWith, $row);
            if( mb_strlen($nrow, "UTF-8") < 30 ) $bodyString .= '<b>'.$nrow.'</b>'; // check if text header
            else $bodyString .= '<p>'.$nrow.'</p>';
        }

        $data_option = $this->getOptValues();
        
        // check if article contains some bad words (Forbidden words) and don't publish
        // preformat $bodyString var.
        $bad_text_string = '';
        $plugin_options = get_option(AUTOBLOGWPM_OPTION_PREFIX . 'plugin_options');
        if( is_array($plugin_options) && array_key_exists('bad_words', $plugin_options) ){
            $sep = '';
            if( is_array($plugin_options['bad_words']) ) foreach( $plugin_options['bad_words'] as $key => $bad_word ){
                if ($key !== array_key_first($plugin_options['bad_words'])) $sep = '|';
                $bad_text_string = $bad_text_string . $sep . $bad_word;
            }
        }
        
        if( $bad_text_string != '' & preg_match('/'.$bad_text_string.'/', $bodyString) === 1) {
            // delete the text
            $body = '';
            // place a flag into status & update the info
            $this->updateFileInfoOption(
                array(
                    $data_option[$fileID]['filename'], 
                    '-', 
                    $this->fileStatus['bad'], 
                    esc_attr__('Forbidden words', 'autoblog-wpm-free')
                )
            );
            // update the files table to reflect the changes in table view
            $this->updateFilesTable();
            // add to logs
            $this->addToLogs($this->info->get_plugin_text("bad_words"), $data_option[$fileID]['filename']);
            exit;
            // redirect to file list
            // $this->autoblog_wpm_redirectUrl('', 'meta', 5);
            // // bat words encountered
            // return false;
        }

        if( mb_strlen($bodyString, "UTF-8") > AUTOBLOGWPM_ARTICLE_MIN_CHARS ){
            // original version of the article
            $body = $bodyString;

            return $body;
        }

        // AUTOBLOGWPM_ARTICLE_MIN_CHARS condition not meet
        return false;
    }

    

    /**
    * Get the latest 100 published articles
    */
    function getLatestArticlesObj( $nrOfArticlesToGet = 100 ) {
        $args = array(
            'numberposts'      => $nrOfArticlesToGet,
            'orderby'          => 'date',
            'order'            => 'DESC',
            'include'          => array(),
            'exclude'          => array(),
            'meta_key'         => '',
            'meta_value'       => '',
            'post_type'        => 'post',
            'suppress_filters' => true,
        );

        $getLatestPostsObj = get_posts($args);

        return $getLatestPostsObj;
    }

    /**
     * alert
     */
    private function show_alert($alertHeader = 'Info', $alertBody = ''){
        
        $alertSpinner = ''; 
        if($alertHeader == 'Info') $alertSpinner = ' <img src="/wp-admin/images/loading.gif" id="hideMe" />';
        
        $alert = '
        <div aria-live="polite" aria-atomic="true" class="infoalert">
            <div class="toast">
                <div class="toast-header">
                    <span class="dashicons dashicons-bell"></span>
                    <strong class="mr-auto">'.$alertHeader.'</strong>
                    <span>'.$alertSpinner.'</span>
                    <!-- <small>1 mins ago</small>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> //-->
                </div>
                <div class="toast-body">
                    '.$alertBody.'
                </div>
            </div>
        </div>
        ';

        return $alert;
    }

    /**
     * Add to log.
     */
    public function addToLogs($headerInfo = '', $textInfo = '') {
        if( AUTOBLOGWPM_DEBUG ) {
            switch ($headerInfo) {
                case ($headerInfo != 'InfoArray'):
                    echo $this->show_alert(esc_attr(strip_tags($headerInfo)), esc_attr(strip_tags($textInfo)));
                    break;
                case ($headerInfo == 'InfoArray'):
                    echo '<pre>'; print_r(esc_attr(strip_tags($textInfo))); echo '</pre>';
                    break;
                default:
                    // break;
            }
        } else {
            // In order to log you need to set AUTOBLOGWPM_DEBUG = true in settings.php
        }
    }

    /**
     * get the most used category in blog
     */
    private function getCategory(){
        // get default category from settings -> write
        $default_category = get_option('default_category');
        // get blog categories
        $args = array(
            'hide_empty'      => false,
        );
        $categories = get_categories($args);
        // get the slug of the defaul category
        foreach( $categories as $cats ){
            if( $cats->term_id == $default_category ) $catSlug = $cats->slug;
        }
        // return the slug
        if(isset($catSlug)) return $catSlug;
    }

    /**
     * Post from folder plugin Admin Logs.
     */
    public function autoblogwpmlogs() {
        // publish article & display logs
        $fileId = ''; if( isset($_GET['fileid']) ) $fileId = filter_input( INPUT_GET, 'fileid', FILTER_VALIDATE_INT );
        if( isset($_GET['action']) && $_GET['action'] == 'publish' ) {
            $this->publishNextArticle($fileId, true);
        }
    }

    /**
     * create settings page
     */
    function autoblogwpmsettings(){
        ?>
        <div class="plugin_options">
            <?php _e( '<h2>Autoblog WPMagic settings</h2>', 'autoblog-wpm-free' ); ?>
            <div class="grid">
                <section data-name="general">
                    <h3><?php _e('General settings:', 'autoblog-wpm-free'); ?></h3>
                    <?php $this->autoblog_wpm_General_settings(); ?>
                </section>
            </div>
        </div>
        <?php
    }

    /**
     * update files data
     */
    function updateFilesTable(){
        // scan articles folder
        $data = $this->dirScan();

        /**
         * remove prev. saved data
         */
        delete_option(AUTOBLOGWPM_OPTION_PREFIX . 'data');
        
        /**
         * save data[] in data_option (update)
         * use data_option in class-table.php in order to show files in table
         */
        update_option(AUTOBLOGWPM_OPTION_PREFIX . 'data', $data);
    }

    /**
     * Add a Link to Your Plugin Settings Page in The WordPress Plugin List
     */
    function autoblog_wpm_add_plugin_page_settings_link( $links ) { 
        $links[] = '<a href="' .
            admin_url( 'admin.php?page=autoblog-wpm-free' ) .
            '">' . __('Files list') . '</a>';
        $links[] = '<a href="' .
            admin_url( 'admin.php?page=autoblog-wpm-free-settings' ) .
            '">' . __('Settings') . '</a>';

        return $links;
    }

    /**
     * Page refresh / redirect
     */
    function autoblog_wpm_redirectUrl( $urlRedirect = '', $method = 'meta', $delay = 0 ){
        
        if( $urlRedirect == '' ) $urlRedirect = $this->autoblog_wpm_get_current_admin_url();
        switch( $method ){
          case 'php':
            break;
          case 'js':?>
            <script>window.location.href = '<?php echo esc_url_raw($urlRedirect); ?>'</script>
            <?php
            break;
          case 'meta':?>
            <meta http-equiv="refresh" content="<?php echo absint($delay); ?>;url=<?php echo esc_url_raw($urlRedirect); ?>" />
            <?php
            break;
        }
    }

    /**
     * get Plugin Data
     */
    public function autoblog_wpm_get_PluginData( $whatToGet = "Name", $display_data = false ) {
        $plugin_data_item = '';
        $plugin_file_path = plugin_dir_path(dirname( __FILE__ , 1 )) . basename( plugin_dir_path(  dirname( __FILE__ , 1 ) )) . ".php";
        $plugin_data = get_plugin_data($plugin_file_path);

        if( isset($plugin_data[$whatToGet]) ) $plugin_data_item = $plugin_data[$whatToGet];
        
        /**
         * display plugin data on request
         */
        if( $display_data ) {
            echo '<pre>'; print_r($plugin_data); echo '</pre>';
        }
        /**
         * return plugin requested data
         */
        return $plugin_data_item;
    }


    /**
     * display info about the settings tab
     */
    function autoblog_wpm_settings_tab_info( $info ){
        // display infos about the feature
        switch( $info ){
            case 'general':
                _e( $this->autoblog_wpm_makeAlert( '<p>Select the time interval for publishing the posts from the list. One post will be automatically published at the chosen time intervals. Please note that if some errors or forbidden words (ex. sex, viagra, naughty, etc.) are encountered at the time of publishing, no posts will be published on site.</p>', 'info' ), 'wpm-licenses' );
                break;
        }
    }

    /**
     * display alerts
     */
    function autoblog_wpm_makeAlert( $message = '', $alertType = 'error' ){
        /**
         * error, warning, success, info
         */
        ?>
        <div class='notice-<?php echo esc_attr($alertType); ?> notice alert is-dismissible'>
            <?php _e($message, 'autoblog-wpm-free'); ?>
        </div>
        <?php
    }

    

    /**
     * display general settings
     */
    function autoblog_wpm_General_settings(){
        /**
         * get & update plugin_options
         */
        $plugin_options = get_option(AUTOBLOGWPM_OPTION_PREFIX . 'plugin_options');
        
        // check if form submited
        if( ! empty($_POST) && isset($_POST["general_options"])) {
            
            // check nonce
            check_admin_referer( 'general_options' );
            // nonce verified - save form data

            // get $_POST data
            if(isset($_POST["publish_interval"]))   $plugin_options['publish_interval'] = sanitize_text_field($_POST["publish_interval"]);
            if(isset($_POST["bad_words"]))          $plugin_options['bad_words']        = explode("\r\n", sanitize_textarea_field($_POST["bad_words"]));
            
            // save options
            update_option(AUTOBLOGWPM_OPTION_PREFIX . 'plugin_options', $plugin_options);
            _e( $this->autoblog_wpm_makeAlert( 'Options saved.', 'success' ), 'wpm-licenses' );
            $this->autoblog_wpm_redirectUrl('', 'meta', 3);
        } 

        // define select field & default value for select
        $select_field = array(
            "1 week"    => "One per week",
            "5 days"    => "Every 5 days",
            "3 days"    => "Every 3 days",
            "1 day"     => "One per day",
            "12 hours"  => "Twice a day",
            "6 hours"   => "Every 6 hours",
            "3 hours"   => "Every 3 hours",
            "1 hour"    => "Every 1 hour"
        );

        /**
         * check if table sync requested
         */
        if( ! empty($_GET) && isset($_GET['table']) ){
            if( $_GET['table'] == 'sync'){
                // check nonce
                check_admin_referer( 'synchronize' );
                // nonce verified - save form data
                /**
                 * update files array
                 */
                $this->updateFilesTable();
                _e( $this->autoblog_wpm_makeAlert( 'The file list has been synchronized with the folder content. Please check.', 'success' ), 'wpm-licenses' );
            }
        }
        ?>

        <form id="autoblog_wpm_general_options" class="settings" method="post">
            <div class="column">
                <div class="row">
                    <label for="publish_interval">Time interval to publish the posts:</label>
                    <select name="publish_interval">
                        <?php
                        if(is_array($plugin_options) && array_key_exists('publish_interval', $plugin_options)) {
                            $selected_time_interval = $plugin_options['publish_interval'];
                        }
                        if( is_array($select_field) ) 
                        foreach($select_field as $key => $time_opt) 
                        echo "<option value='".esc_attr($key)."' ". ((isset($selected_time_interval) && $selected_time_interval == $key) ? 'selected' : '') .">".esc_attr($time_opt)."</option>";
                        ?>
                    </select>
                </div>
                <div class="row">
                    <label for="bad_words">Define the forbidden words to check when the post is published (one per line):</label>
                    <textarea name="bad_words"><?php if(is_array($plugin_options) && array_key_exists('bad_words', $plugin_options) && is_array($plugin_options['bad_words'])) foreach($plugin_options['bad_words'] as $bad_word) echo $bad_word . "\n"; ?></textarea>
                </div>
                <div class="row">
                    <?php wp_nonce_field( 'general_options' ); ?>
                    <input type="hidden" name="general_options" />
                    <input type="submit" id="submit" value="Save" />
                    <span class="spinner"></span>
                </div>
            </div>
        </form>
        <div class="info">
            <?php $this->autoblog_wpm_settings_tab_info( 'general' ); ?>
        </div>
        <a href="<?php echo wp_nonce_url($this->autoblog_wpm_get_current_admin_url() . '&table=sync', 'synchronize'); ?>" class="l-button btn btn-primary tooltip a-right">
            <?php _e('Synchronize files', 'autoblog-wpm-free');?>
            <span class="dashicons dashicons-info-outline"></span>
            <div class="top">
                <h3><?php _e('Synchronize the plugin file\'s table.', 'autoblog-wpm-free');?></h3>
                <p><?php _e('When you add new files to the plugin\'s folder, they will be automatically added to the files list in the plugin file\'s table. If you don\'t want to wait, you can manually synchronize the folder content with the files list table.', 'autoblog-wpm-free');?></p>
                <i></i>
            </div>
        </a>
        <?php
    }

    

    /**
     * create help page
     */
    function autoblogwpmhelp(){
        ?>
        <section class="tabs-container">
     
            <ul id="tabs-titles">
                <li class="current"> <!-- default (on page load), first one is currently displayed -->
                    Getting started
                </li>
                <li>
                    Requirements
                </li>
                <li>
                    Plugin settings
                </li>
                <li>
                    Frequently asked question
                </li>
            </ul>
            <ul id="tabs-contents">
                <li>
                    <div class="content">
                        <h3>Installation and setup</h3>
                        <h4>Follow the steps to install and get starting with the <?php echo $this->autoblog_wpm_get_PluginData("Name"); ?>:</h4>
                        <p>1. Download and install the plugin from Plugin Menu -> Add new.</p>
                        <p>2. Upload your .txt files to the folder /wp-content/uploads/autoblog-wpm-free/.</p>
                        <p>3. Check a <a href="https://wpmagic.cloud/wp-content/uploads/autoblog-wpm-free/3_Must_Do_Steps_To_A_Happier_Healthier_Day.txt">sample file</a> that shows you how the .txt files should be formatted.</p>
                        <p>4. Wait for the file's table to populate or manually populate the table with files in the "Settings".</p>
                        <p>5. Choose a default category for the published posts in "Default Post Category" in the "Settings" -> "Writing" in the admin menu.</p>
                        <p>The plugin will publish periodically content from the files as blog posts on your website.</p>
                    </div>
                </li>
                <li>
                    <div class="content">
                        <h3>Requirements</h3>
                        <p>PHP > 7.0</p>
                    </div>
                </li>
                <li>
                    <div class="content">
                        <h3>Settings</h3>
                        <p>
                            <strong>Time interval to publish the posts</strong> 
                            <br />This is the time interval for publishing the posts from the list. 
                            One post will be automatically published at the chosen time intervals.
                            To do that <a href="https://developer.wordpress.org/plugins/cron/" target="_blank">WP-Cron</a> needs to be activated and running on your site.
                        </p>
                        <p>
                            <strong>Forbidden words</strong>
                            <br />These are the words to check in the text before publishing. 
                            If any of these words are found, the post will not be published, and info "Forbidden words" will be displayed in the "Info" column on the files list.
                        </p>
                        <p>
                            <strong>Synchronize files</strong>
                            <br />When you add new files to the plugin's folder, they will be automatically added to the files list in the plugin file's table. 
                            If you don't want to wait for the files to appear on the list, you can manually synchronize the folder content with the files list table.
                        </p>
                    </div>
                </li>
                <li>
                    <div class="content">
                        <h3>FAQ</h3>
                        <p><strong>Will the plugin work with my installed WordPress Theme?</strong></p>
                        <p>Yes. The <?php echo $this->autoblog_wpm_get_PluginData("Name"); ?> Plugin works with any WordPress Theme. The Plugin will publish posts automatically on your blog. How they show on your site depends on your chosen Theme.</p>
                        <p><strong>Is it possible to integrate it with my favorite rewriting tool?</strong></p>
                        <p>
                            Just send us a request on the contact page. 
                            If your favorite tool for rewriting the articles has a REST API feature, we will gladly consider integrating it.
                        </p>
                        <p>&nbsp;</p>
                        <p class="align-right">Don't find your question here?<br />Leave us a message on the <a href="?page=autoblog-wpm-free-contact">contact page</a>.</p>
                    </div>
                </li>
            </ul>

        </section>
        <?php
    }

    
    /**
     * Get the base URL of the current admin page, with query params.
     *
     * @return string
     */
    function autoblog_wpm_get_current_admin_url(): string{

        return admin_url(sprintf(basename($_SERVER['REQUEST_URI'])));

    }

    /**
     * check publishing interval
     */
    function autoblog_wpm_isPublishingIntervalAllowed(){

        /**
         * get & update plugin_options
         */
        $plugin_options = get_option(AUTOBLOGWPM_OPTION_PREFIX . 'plugin_options');
        if( is_array($plugin_options) ){
            if( array_key_exists('publish_interval', $plugin_options) & array_key_exists('last_post_published_time', $plugin_options) ){
                $last_post_published_time   = $plugin_options['last_post_published_time'];
                $publish_interval           = $plugin_options['publish_interval'];
                // compare time passed since last publishing
                if( (time() - $last_post_published_time) < (time() - strtotime( "-" . $publish_interval )) ){
                    // article can't be published
                    return false;
                }
            }

        }

        return true;
    }

    /**
     * create contact page
     */
    function autoblogwpmcontactus(){
        $conatct_url = AUTOBLOGWPM_UPDATES . '/support/?mtm_campaign=plugin&mtm_source='.$this->plugin_slug;
        _e( $this->autoblog_wpm_makeAlert( 'You will be automatically redirected to the contact page. If not click <a href="'.$conatct_url.'" target="_blank">here</a> to go to the contact page.', 'info' ), 'wpm-licenses' );
        if( defined("AUTOBLOGWPM_UPDATES") ) $this->autoblog_wpm_redirectUrl( $conatct_url, 'meta', 3);
    }

    /**
     * add the post meta to post ID
     */
    function addPostMetaFields( $post_id, $metaArray ){
        if( is_array($metaArray) ) foreach( $metaArray as $key => $metaValue ){
            add_post_meta( $post_id, $key, $metaValue );
        }
    }

}
