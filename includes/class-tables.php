<?php

namespace autoblogwpm;

/*  Copyright 2015  Matthew Van Andel  (email : matt@mattvanandel.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



/* == NOTICE ===================================================================
 * Please do not alter this file. Instead: make a copy of the entire plugin, 
 * rename it, and work inside the copy. If you modify this plugin directly and 
 * an update is released, your changes will be lost!
 * ========================================================================== */



/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary. In this tutorial, we are
 * going to use the WP_List_Table class directly from WordPress core.
 *
 * IMPORTANT:
 * Please note that the WP_List_Table class technically isn't an official API,
 * and it could change at some point in the distant future. Should that happen,
 * I will update this plugin with the most current techniques for your reference
 * immediately.
 *
 * If you are really worried about future compatibility, you can make a copy of
 * the WP_List_Table class (file path is shown just below) to use and distribute
 * with your plugins. If you do that, just remember to change the name of the
 * class to avoid conflicts with core.
 *
 * Since I will be keeping this tutorial up-to-date for the foreseeable future,
 * I am going to work with the copy of the class provided in WordPress core.
 */

// if(!class_exists('WP_List_Table')){
//     require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
// }
// class WP_List_Table is includet in plugin and don't need to be loaded from core



/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be movies.
 * Add \ before any global class, function and constant (stdClass).
 */
class Files_List_Table extends WP_List_Table {

    private $fileStatus;
    
    /** ************************************************************************
     * Normally we would be querying data from a database and manipulating that
     * for use in your list table. For this example, we're going to simplify it
     * slightly and create a pre-built array. Think of this as the data that might
     * be returned by $wpdb->query()
     * 
     * In a real-world scenario, you would make your own custom query inside
     * this class' prepare_items() method.
     * 
     * @var array 
     **************************************************************************/
    var $example_data = array(
            array(
                'ID'        => 1,
                'title'     => '300',
                'rating'    => 'R',
                'director'  => 'Zach Snyder'
            ),
            array(
                'ID'        => 2,
                'title'     => 'Eyes Wide Shut',
                'rating'    => 'R',
                'director'  => 'Stanley Kubrick'
            ),
            array(
                'ID'        => 3,
                'title'     => 'Moulin Rouge!',
                'rating'    => 'PG-13',
                'director'  => 'Baz Luhrman'
            ),
            array(
                'ID'        => 4,
                'title'     => 'Snow White',
                'rating'    => 'G',
                'director'  => 'Walt Disney'
            ),
            array(
                'ID'        => 5,
                'title'     => 'Super 8',
                'rating'    => 'PG-13',
                'director'  => 'JJ Abrams'
            ),
            array(
                'ID'        => 6,
                'title'     => 'The Fountain',
                'rating'    => 'PG-13',
                'director'  => 'Darren Aronofsky'
            ),
            array(
                'ID'        => 7,
                'title'     => 'Watchmen',
                'rating'    => 'R',
                'director'  => 'Zach Snyder'
            ),
            array(
                'ID'        => 8,
                'title'     => '2001',
                'rating'    => 'G',
                'director'  => 'Stanley Kubrick'
            ),
        );

    /**
     * call Info & Admin class
     */
    public $info;
    public $admin;

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct($fileStatus){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'file',     //singular name of the listed records
            'plural'    => 'files',    //plural name of the listed records
            'ajax'      => false       //does this table support ajax?
        ) );
        
        $this->fileStatus = $fileStatus;

        $this->info     = new Info();
        $plugin_slug    = ''; if ( defined('AUTOBLOGWPM_SLUG') )      $plugin_slug    = AUTOBLOGWPM_SLUG;
        $version        = ''; if ( defined('AUTOBLOGWPM_VERSION') )   $version        = AUTOBLOGWPM_VERSION;
        $option_name    = 'data';
        global $allowedFilesExt;
        $this->admin    = new Admin($plugin_slug, $version, $option_name, $allowedFilesExt, $fileStatus);
    }


    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'size':
            case 'plagiarism':
                return $item[$column_name];
            case 'status':
                //return print_r($item,true); //Show the whole array for troubleshooting purposes 
                if($item[$column_name] == $this->fileStatus['pending']) {
                    return '<span class="dot" title="'.$this->info->get_plugin_text($this->fileStatus['pending']).'"></span>';
                } else if($item[$column_name] == $this->fileStatus['error']) {
                    return '<span class="red dot" title="'.$this->info->get_plugin_text($this->fileStatus['error']).'"></span>';
                } else if($item[$column_name] == $this->fileStatus['bad']) {
                    return '<span class="darkgrey dot" title="'.$this->info->get_plugin_text($this->fileStatus['bad']).'"></span>';
                } else if($item[$column_name] == $this->fileStatus['published']) {
                    return '<span class="green dot" title="'.$this->info->get_plugin_text($this->fileStatus['published']).'"></span>';
                } else return $item[$column_name];
            default:
                return $item[$column_name];
        }
    }

    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_filename($item){

        // get the post ID from info
        $postID = 1;
        if( isset($item['info']) ) {
            $rowInfo = explode("=", $item['info']);
            if(isset($rowInfo[1])) $postID = str_replace( array(" ", ")"), "", $rowInfo[1]);
        }

        // get page if any
        $paged = ''; if( isset($_REQUEST['paged']) ) $paged = filter_input( INPUT_GET, 'paged', FILTER_VALIDATE_INT );
        $filestatus = ''; if( isset($_REQUEST['filestatus']) ) $filestatus = sanitize_text_field( $_REQUEST['filestatus'] );
        
        //Build row actions
        $actions = array(
            'publish'   => ($item['status'] != 'published') ? sprintf('<a href="?page=%s&action=%s&fileid=%s&paged=%d&filestatus=%s">Publish</a>',sanitize_text_field( $_REQUEST['page'] ),'publish',$item['ID'],$paged, $filestatus) : sprintf('<a href="%s">View on site</a>',get_permalink($postID)),    
            'view'      => sprintf('<a href="?page=%s&action=%s&fileid=%s" target="_blank">View</a>',sanitize_text_field( $_REQUEST['page'] ),'view',$item['ID']),
            'delete'    => sprintf('<a href="#" onclick=confirmDelete("?page=%s&action=%s&fileid=%s")>Delete</a>',sanitize_text_field( $_REQUEST['page'] ),'delete',$item['ID'],$this->info->get_plugin_text("info_2")),
        );
        
        //Return the title contents
        // return sprintf('%1$s <span style="color:silver">('.$this->info->get_plugin_text('file_id').': %2$s)</span>%3$s',
        return sprintf('%1$s %3$s',
            /*$1%s*/ $item['filename'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }


    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'            => '<input type="checkbox" />', //Render a checkbox instead of text
            'filename'      => 'Filename',
            'size'          => 'Size',
            // 'plagiarism'    => 'Plagiarism',
            'status'        => 'Status',
            'info'          => 'Info'
        );
        return $columns;
    }


    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'filename'      => array('filename',false),     //true means it's already sorted
            'size'          => array('size',false),
            // 'plagiarism'    => array('plagiarism',false),
            'status'        => array('status',false),
            'info'          => array('info',false),
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {

        $data = get_option(AUTOBLOGWPM_OPTION_PREFIX . 'data');

        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            // wp_die('Items deleted (or they would be if we had items to delete)!');
            
            /**
             * delete single file
             */
            if( isset($_REQUEST['fileid']) && $_REQUEST['fileid'] > 0 ){
                $fileID = sanitize_text_field($_REQUEST['fileid']);
                /**
                 * get the file name
                 */
                foreach($data as $key => $file){
                    if( $file['ID'] == $fileID ) {
                        $fileName = $file['filename'];
                        $dataKey = $key;
                    }
                }
                if( isset($fileName) && $fileName != '') $this->permanentlyDeleteFile(array($fileName), array($dataKey));
                // redirect
                $pluginSlug = $this->admin->autoblog_wpm_get_PluginData("TextDomain");
                $this->admin->autoblog_wpm_redirectUrl("?page=" . $pluginSlug, 'meta');
            }

            /**
             * delete multiple files
             */
            if( isset($_REQUEST['file']) && (is_array($_REQUEST['file']) & sizeof($_REQUEST['file']) > 0) ){
                $fileIDs = $_REQUEST['file'];
                if( isset($_REQUEST['_wpnonce']) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'] ) !== false ) {
                    /**
                     * get the files name
                     */
                    $fileNames = array(); $dataKeys = array();
                    foreach($data as $key => $file) {
                        foreach( $fileIDs as $searchedFiles ){
                            if( $file['ID'] == $searchedFiles ) {
                                $fileNames[] = $file['filename'];
                                $dataKeys[] = $key;
                            }
                        }
                    }
                    $this->permanentlyDeleteFile($fileNames, $dataKeys);
                }
            }
        }

        /**
         * manualy publish the file
         */
        if( 'publish'===$this->current_action() ) {
            $fileId = ''; if( isset($_GET['fileid']) )  $fileId = filter_input( INPUT_GET, 'fileid', FILTER_VALIDATE_INT );
            $paged  = ''; if( isset($_GET['paged']) )   $paged  = filter_input( INPUT_GET, 'paged', FILTER_VALIDATE_INT );
            $filestatus = ( isset($_GET['filestatus']) ) ? sanitize_text_field($_GET['filestatus']) : 'all';
            
            // redirect to publish page
            $this->admin->autoblog_wpm_redirectUrl( '?page=autoblog-wpm-free-logs&action=publish&fileid=' . esc_attr($fileId) .'&paged=' . esc_attr($paged) .'&filestatus=' . esc_attr($filestatus), 'js' );
        }

        /**
         * view the file
         */
        if( 'view'===$this->current_action() ) {
            $fileId = ''; if( isset($_GET['fileid']) ) $fileId = filter_input( INPUT_GET, 'fileid', FILTER_VALIDATE_INT );
            $fileName = $this->viewFile( $fileId );
            if( defined('AUTOBLOGWPM_ARTICLES_DIR_URL') ) {
                $filePath = AUTOBLOGWPM_ARTICLES_DIR_URL . $fileName;
                // redirect to file in new tab
                $this->admin->autoblog_wpm_redirectUrl( $filePath, 'js' );
            }
        }
        
    }


    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 5;
        $default_sort = 'filename';

        /**
         * check scren_option from user and modify $per_page
         */
        // get the current user ID
        $user = get_current_user_id();
        // get the current admin screen
        $screen = get_current_screen();
        // retrieve the "per_page" option
        $screen_option = $screen->get_option('per_page', 'option'); 
        // retrieve the value of the option stored for the current user
        $screen_option_items_per_page = get_user_meta($user, $screen_option, true);
        if(isset($screen_option_items_per_page) && $screen_option_items_per_page > 0) $per_page = get_user_meta($user, $screen_option, true);
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);


        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
        
        
        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example 
         * package slightly different than one you might build on your own. In 
         * this example, we'll be using array manipulation to sort and paginate 
         * our data. In a real-world implementation, you will probably want to 
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        // $data = $this->example_data;
        $data = array();

        /**
         * get data array from data_option 
         * (prepared when WP Cron run in class-admin.php -> admin_scheduler_hook())
         */
        $data_option = get_option(AUTOBLOGWPM_OPTION_PREFIX . 'data');
        if( is_array($data_option) && sizeof($data_option) !== 0 ) {
            $data = $data_option;

            /**
             * check if search active and filter the array
             * unset the $data values not containing the searched val.
             */
            if( isset($_GET['s']) && $_GET['s'] != '' ) {
                $search = sanitize_text_field( $_GET['s'] );
                
                foreach($data as $key => $file) {
                    if( stristr(str_replace( array( '\'', '"',',' , ';', '<', '>', '_' ), ' ', $file['filename']), $search) === false ) unset($data[$key]);
                }
            }
            
            /**
             * filter display items in different status
             */
            $fileStateFilter = sanitize_text_field( isset($_REQUEST['filestatus'] ) ? $_REQUEST['filestatus'] : 'all');
            if ( $fileStateFilter != 'all' ) {
                foreach($data as $key => $file) {
                    if( $file['status'] != $fileStateFilter ) unset($data[$key]);
                }
            }

            /**
             * check if navigate to page requested when displaying filtered results
             * add table head links filter to pages
             */
            if ( isset($_REQUEST['_wp_http_referer']) ) $this->add_page_on_top_links_wp_http_referrer();
        }

        
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        // function usort_reorder($a,$b){
        //     $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
        //     $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
        //     $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            
        //     return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        // }
        // usort($data, 'usort_reorder');
        
        /**
         * Sort Multi-dimensional Array by Value
         */
        function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) { 
            $sort_col = array();
            foreach ($arr as $key=> $row) {
                $sort_col[$key] = mb_strtolower($row[$col]);
            }
            
            array_multisort($sort_col, $dir, $arr);
        }
        
        $orderby    = (!empty($_REQUEST['orderby'])) ? sanitize_text_field($_REQUEST['orderby']) : $default_sort; //If no sort, default to title
        $order      = (!empty($_REQUEST['order']) && $_REQUEST['order'] == 'desc') ? SORT_DESC : SORT_ASC; //If no order, default to asc
        array_sort_by_column($data, $orderby, $order);
        
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         * 
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/
        
                
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }

    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav( $which ) {
        if ( $which == "top" ){
            //The code that goes before the table is here
            // echo "Hello, I'm before the table";
        }
        if ( $which == "bottom" ){
            //The code that goes after the table is there
            // echo "Hi, I'm after the table";
        }
    }

    /**
     * create links at the top of WP_List_table
     */
    function get_views() {
        $views = array();
        $current = ( ! empty($_REQUEST['filestatus']) ? sanitize_text_field($_REQUEST['filestatus']) : 'all');
     
        //All link
        $class = ($current == 'all' ? ' class="current"' :'');
        $all_url = remove_query_arg('filestatus');
        $views['all'] = "<a href='{$all_url }' {$class} >All</a>";
     
        //Published link
        $published_url = add_query_arg('filestatus','published','?page=autoblog-wpm-free');
        $class = ($current == 'published' ? ' class="current"' :'');
        $views['published'] = "<a href='{$published_url}' {$class} >Published</a>";
     
        //Pending link
        $pending_scan_url = add_query_arg('filestatus','pending_scan','?page=autoblog-wpm-free');
        $class = ($current == 'pending_scan' ? ' class="current"' :'');
        $views['pending_scan'] = "<a href='{$pending_scan_url}' {$class} >Pending</a>";

        // Bad words link
        $bad_scan_url = add_query_arg('filestatus','bad_words','?page=autoblog-wpm-free');
        $class = ($current == 'bad_words' ? ' class="current"' :'');
        $views['bad_words'] = "<a href='{$bad_scan_url}' {$class} >Forbidden</a>";

        //Files with error link
        // $errors_scan_url = add_query_arg('filestatus','error');
        // $class = ($current == 'error' ? ' class="current"' :'');
        // $views['errors'] = "<a href='{$errors_scan_url}' {$class} >Error</a>";
     
        return $views;
     }

     /**
      * check if view is a filtered view and redirect
      */
      function add_page_on_top_links_wp_http_referrer() {
        $current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        /**
         * check if _wp_http_referer query var exists and extract vars from it 
         * to create a new url for sorting and pagination
         */
        if (strpos($current_url, '_wp_http_referer') !== false) {
            /**
             * check if filestatus exist in the query vars
             */
            $url_wp_http_referer = esc_attr( $_GET['_wp_http_referer'] );
            preg_match("/&?filestatus=([^&]+)/", $url_wp_http_referer, $matches);
            if( isset($matches[1]) ) $filestatus_wp_http_referer = $matches[1];
            
            if ( isset($filestatus_wp_http_referer) && $filestatus_wp_http_referer != '' ) {
                /**
                 * create the new url to redirect
                 */
                $new_url_1 = remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes($current_url));
                $new_url   = add_query_arg( array('filestatus' => esc_attr($filestatus_wp_http_referer)), stripslashes($new_url_1));
                echo "<script>location.href = '". esc_url($new_url) ."';</script>";
                exit();
            }
        }
    }

    /**
     * delete files
     */
    function permanentlyDeleteFile($fileNames, $dataKeys){

        $data = get_option(AUTOBLOGWPM_OPTION_PREFIX . 'data');

        /**
         * permanently delete the file
         */
        if( defined('AUTOBLOGWPM_ARTICLES_DIR') ) {
            if( is_array($fileNames) && sizeof($fileNames) > 0 ){
                foreach( $fileNames as $fileName ){
                    $filePath = AUTOBLOGWPM_ARTICLES_DIR . $fileName;
                    if( file_exists($filePath) ) {
                        unlink( $filePath );
                        if( is_array($dataKeys) && sizeof($dataKeys) > 0 ) foreach( $dataKeys as $dataKey ){
                            if (isset( $dataKey )) {
                                /**
                                 * update the files data
                                 */
                                unset( $data[$dataKey] );
                            }
                        }
                    }
                }
                /**
                 * remove prev. saved data
                 */
                delete_option(AUTOBLOGWPM_OPTION_PREFIX . 'data');
                /**
                 * save data[] in data_option (update)
                 * use data_option in class-table.php in order to show files in table
                 */
                update_option(AUTOBLOGWPM_OPTION_PREFIX . 'data', $data);
                // display an alert
                echo "<div class='error notice alert'>"; _e('The file(s) have been deleted.', 'autoblog-wpm-free'); echo "</div>";
            }
        }
    }

    /**
     * vie the file 
     */
    function viewFile( $fileId ){

        $fileName = '';
        $data = get_option(AUTOBLOGWPM_OPTION_PREFIX . 'data');

        /**
         * get the data id for the file
         */
        if( isset($fileId) && $fileId > 0 ){
            /**
             * get the file name
             */
            foreach($data as $key => $file){
                if( $file['ID'] == $fileId ) {
                    $fileName = $file['filename'];
                    $dataKey = $key;
                }
            }
        }

        return $fileName;

    }

}








