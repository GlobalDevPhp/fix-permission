<?php

/**
 * Fix Permission fix-permission.php
 *
 * @package fix-permission
 */
/*
  Plugin Name: Fix Permission
  Plugin URI: https://itnotes.org.ua/fix-permission
  Description: Fix Permission allows you to change permission for files, that you can't change with your ssh user. Work for linux and Windows servers, on behalf of Apache user www-data.
  Version: 1.0.0
  Author: Evgeny
  Author URI: https://itnotes.org.ua
  Text Domain: fix-permission
 */

/**
 * Fix Permission version
 *
 * @since 1.0.0
 */
define('FIX_PERMISSION_VERSION', '1.0.0');
require_once __DIR__ . '/lib/FileChanger.php';

class FixPermission {

    /**
     * Static instance
     *
     * @since 1.0.0
     *
     * @access private
     * @var FixPermission $instance
     */
    private static $instance;

    /**
     * Object of path list handler class FilesLoopAbstract and his child's.
     * 
     * @access public
     * 
     * @var object $loop_obj
     */
    public $loop_obj;

    /**
     * Constructor method
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function __construct() {
        // Add Plugin Hooks.
        add_action('plugins_loaded', array($this, 'add_hooks'));

        // Load Translation.
        load_plugin_textdomain('wp-sweep');

        // Plugin Activation/Deactivation.
        register_activation_hook(__FILE__, array($this, 'plugin_activation'));
        register_deactivation_hook(__FILE__, array($this, 'plugin_deactivation'));
    }

    /**
     * Initializes the plugin object and returns its instance
     *
     * @since 1.0.0
     *
     * @access public
     * @return object The plugin object instance
     */
    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Init this plugin
     *
     * @since 1.0.0
     *
     * @access public
     * @return void
     */
    public function init() {

    }

    /**
     * Adds all the plugin hooks
     *
     * @since 1.0.0
     *
     * @access public
     * @return void
     */
    public function add_hooks() {
        // Actions.
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'admin_menu'));

        //add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        //add_action( 'wp_ajax_sweep_details', array( $this, 'ajax_sweep_details' ) );
        //add_action( 'wp_ajax_sweep', array( $this, 'ajax_sweep' ) );
    }

    /**
     * Admin menu
     *
     * @since 1.0.3
     *
     * @access public
     * @return void
     */
    public function admin_menu() {
        /* TODO: _x( 'Fix Permission', 'Page title', 'fix-permission' ), _x( 'Permission', 'Menu title', 'fix-permission' ) */
        add_management_page('Fix Permission', 'Fix Permission', 'activate_plugins', 'fix-permission', array('FixPermission', 'view'));
    }

    /**
     * What to do when the plugin is being deactivated
     *
     * @since 1.0.0
     *
     * @access public
     * @param boolean $network_wide Is network wide.
     * @return void
     */
    public function plugin_activation($network_wide) {

    }

    /**
     * Perform plugin activation tasks
     *
     * @since 1.0.0
     *
     * @access private
     * @return void
     */
    private function plugin_activated() {
        
    }

    /**
     * What to do when the plugin is being activated
     *
     * @since 1.0.0
     *
     * @access public
     * @param boolean $network_wide Is network wide.
     * @return void
     */
    public function plugin_deactivation($network_wide) {

    }

    /**
     * Perform plugin deactivation tasks
     *
     * @since 1.0.0
     *
     * @access private
     * @return void
     */
    private function plugin_deactivated() {
        
    }

    /**
     * Including plugin view files. If user send some form data - call run_action();
     *
     * @since 1.0.0
     *
     * @access public
     * @return void
     */
    
    public static function view() {

        if (!empty($_POST)) {
            self::run_action();
        }

        include_once( FP_ABSPATH . '/views/admin.php' );
    }
    
    /**
     * Check user sent data, prepare and create loop action class object
     *
     * @since 1.0.0
     *
     * @access public
     * @return void
     */
    public static function run_action() {

        if (!isset($_POST['generated_nonce']) || !wp_verify_nonce($_POST['generated_nonce'], 'fix-permission-nonce')) {
            return;
        }
        
        if ($_POST['action_type'] == 'deletion') {
            self::$instance->loop_obj = new DeleteLoop();
        } elseif ($_POST['action_type'] == 'permission') {
            self::$instance->loop_obj = new PermissionLoop();
            self::$instance->loop_obj->flag_str = $_POST['permission_flag'];
        }
        if (!empty($_POST['recursion_on']))
            self::$instance->loop_obj->recursion = ($_POST['recursion_on'] == 'yes') ? true : false;
        self::$instance->loop_obj->test_mode = ($_POST['test_mode'] == 'true') ? true : false;
        self::$instance->loop_obj->setPaths(explode(PHP_EOL, $_POST['fperm_options_paths']));
        self::$instance->loop_obj->runLoop();

    }

}

/**
 * Init FixPermission
 */
$FP = FixPermission::get_instance();

if (!defined('FP_ABSPATH')) {
    define('FP_ABSPATH', plugin_dir_path(__FILE__));
}