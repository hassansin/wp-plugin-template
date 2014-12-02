<?php

/**
 * Plugin Name: WP Plugin Template Demo
 * Plugin URI: http://plugin-url.com
 * Description: WP Plugin Template Demo
 * Version: 0.0.1
 * Author: Name of the plugin author
 * Author URI: http://URI_Of_The_Plugin_Author
 * License: GPL2
 */

/*
* TODO:
*  Multiple parent page
*  Localization
*/

defined('ABSPATH') or die("No script kiddies please!");
require_once('lib/class-pluginframework.php');

//if (!isset($_SESSION))
//   session_start();

class Wp_Plugin_Template_Demo extends Plugin_Framework {
	
	/*
	* Name of plugin to show in Nav panel
	*/
	protected $name = 'WP Plugin Template Demo'; 

	/*
	* Array of shortcodes to be used.
	*/
	protected $shortcode = array();
	
	/*
	*options-general.php, tools.php etc or leave it empty to creat new menu item
	*/
	protected $parentPage = ''; 

	/*
	* Specify interval in seconds, empty to disable
	*/
	protected $cronInterval = '';

	/*
	* Store plugin options & meta data here.
	*/
	protected $defaultOptions = array(
		'version' => '0.0.1'
		);
	
	public function __construct() {			
		$this->plugindir  = realpath(dirname(__FILE__));
		$this->pluginurl  = plugin_dir_url(__FILE__);		
		$this->slug       = __CLASS__;
		$this->FILE 	  = __FILE__;		
		parent::__construct();
		
		//add your custom initialization & actions here
		$this->table = $this->db->prefix . 'plugin_template';
	}

	/*
	* Enqueue Admin styles & scripts here
	*/
	public function admin_enqueue_scripts() {
		global $hook_suffix;

		//load these scripts only in plugin pages
		if (in_array($hook_suffix, $this->page_hooks)) {			
			wp_enqueue_script('my-admin-script', $this->pluginurl . 'js/' . 'admin-script.js' , array('jquery'));		
		}
	}
	
	/*
	* Enqueue frontend styles & scripts here
	*/
	public function public_enqueue_scripts() {
		wp_enqueue_script('jquery');		
	}
	
	public function activate_plugin() {						
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		
		//table schema		
		$sql = "CREATE TABLE {$this->table} (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		title VARCHAR(50) DEFAULT '' NOT NULL,
		url VARCHAR(250) DEFAULT '' NOT NULL,
		created TIMESTAMP  DEFAULT CURRENT_TIMESTAMP  NOT NULL,
		UNIQUE KEY id (id)
		);";

		//dummy data
		dbDelta($sql);		
		$this->db->insert($this->table,array(
	      'title' => 'The Godfather',
	      'url' => 'http://www.imdb.com/title/tt0068646/?ref_=chttp_tt_2'
	      ));
	    $this->db->insert($this->table,array(
	      'title' => 'The Dark Knight',
	      'url' => 'http://www.imdb.com/title/tt0468569/?ref_=chttp_tt_4'
	      ));   
		
	}
	
	public function deactivate_plugin() {
		delete_option($this->optionName);
		$sql = "DROP TABLE IF EXISTS {$this->table}";		
		$this->db->query($sql);
	}

	/*
	* Hook for cron if enabled
	*/	
	public function cron_handler() {
		
	}
	
	/*
	* Hook for all the shortcodes. 
	* Use $code argument to differentiate shortcodes
	*/
	public function shortcode_hook($attr, $content = null, $code) {
		$html = "";
		//load scripts that is only used by this shortcode
		//wp_enqueue_script('my-script', plugins_url('my-script.js', __FILE__), array('jquery'), '1.0', true);
		
		return $content.$html;
	}
	static function instance() {
			
		if (!isset(self::$_inst)) {			
			$className   = __CLASS__;
			self::$loaded = true;
			self::$_inst = new $className;			
		}		
		return self::$_inst;
	}
	
}
Wp_Plugin_Template_Demo::instance();

?>
