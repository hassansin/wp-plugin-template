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


/*TODO:
multiple parent page?
localization
*/

defined('ABSPATH') or die("No script kiddies please!");
require_once('lib/class-pluginframework.php');

//if (!isset($_SESSION))
//   session_start();

class Wp_Plugin_Template_Demo extends Plugin_Framework {
	
	protected $name = 'WP Plugin Template Demo'; //Name of plugin to show in Nav panel
	protected $shortcode = array('shortcode1', 'shortcode2'); //array of shortcodes
	protected $parentPage = ''; //leave it empty to creat new menu item
	protected $cronInterval = ''; //specify interval in seconds
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
	
	public function admin_enqueue_scripts() {
		global $hook_suffix; // check this variable first and then use accordingly
		if (in_array($hook_suffix, $this->page_hooks)) {			
			wp_enqueue_script('my-admin-script', $this->pluginurl . 'js/' . 'admin-script.js' , array('jquery'));		
		}
	}
	
	public function public_enqueue_scripts() {
		wp_enqueue_script('jquery');
		//wp_register_script('jquery-ui', 'http://code.jquery.com/ui/1.10.0/jquery-ui.js', 'jquery');
		//wp_enqueue_script('jquery-ui');
		// wp_enqueue_script('my-script', plugins_url('my-script.js', __FILE__), array('jquery'), '1.0', true);
		//wp_enqueue_style($this->slug . '-style', $this->pluginurl . 'css/' . 'style.css');
	}

	
	public function activate_plugin() {		
		if (isset($this->cronInterval))
			wp_schedule_event(time(), $this->slug . '_scheduler', $this->slug . '_cron');
		
		if (($old_option = get_option($this->optionName))) { //if option already exists
			//merge new options and keep the values of old options.
			$merged_option = array_merge($this->defaultOptions, $old_option);
			update_option($this->optionName, $merged_option);
		} else {
			add_option($this->optionName, $this->defaultOptions);
		}
		
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				
		$sql = "CREATE TABLE {$this->table} (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		title VARCHAR(50) DEFAULT '' NOT NULL,
		url VARCHAR(250) DEFAULT '' NOT NULL,
		created TIMESTAMP  DEFAULT CURRENT_TIMESTAMP  NOT NULL,
		UNIQUE KEY id (id)
		);";
		dbDelta($sql);
		
	}
	
	public function deactivate_plugin() {
		delete_option($this->optionName);
		$sql = "DROP TABLE IF EXISTS {$this->table}";
		echo $sql;exit; 
		$this->db->query($sql);
	}
	
	
	public function cron_handler() {
		
	}
	
	public function shortcode_hook($attr, $content = null, $tag, $code) {
		
		//load scripts that is only used by this shortcode
		//wp_enqueue_script('my-script', plugins_url('my-script.js', __FILE__), array('jquery'), '1.0', true);
		//process depending on $tag value
		return $content.  $html;
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
