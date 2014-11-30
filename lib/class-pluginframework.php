<?php
defined('ABSPATH') or die("No script kiddies please!");

if (!class_exists('Plugin_Framework')) {
	class Plugin_Framework {
			
		protected $slug;
		protected $optionName;
		protected $options = array();
		protected $basename;
		protected $plugindir;
		protected $pluginurl;
		protected $page_hooks = array();
		protected $menus = array();
		protected static $_inst;
		protected static $loaded;
		
		public function __construct() {			
			global $wpdb;		
			$this->db 				=& $wpdb;
			$this->optionName = $this->slug . '_options'; //plugin options
			$this->options 		= get_option($this->optionName);
			if(self::$loaded){
				self::$loaded =false;
				$this->init();
				$this->init_actions();			
				register_activation_hook($this->FILE, array(
					$this,
					'activate_plugin'
				));
				register_deactivation_hook($this->FILE, array(
					$this,
					'deactivate_plugin'
				));
			}
			
		}

		private function init(){	

			$this->pluginActionLinks = array();
			$dir = opendir($this->plugindir . '/controllers/');
			if ($dir) {						
				while (($entry = readdir($dir)) !== false) {
					if (strrchr($entry, '.') === '.php') {
						$php_classes = get_declared_classes();
						require_once $this->plugindir . '/controllers/' . $entry;
						$class = array_diff(get_declared_classes(), $php_classes);
						if(!count($class))	
							continue;
						$class = array_shift($class);
						//$class                = substr($entry, 0, -4); // remove .php and get class name
						$instance             = new $class();
						$return               = $instance->menuoptions; //
						$this->pages[$class] = $instance;
						//add class name as slug i.e admin.php?page={$class} - for routing purpose
						$return['slug']       = $class;
						$return['capability'] = isset($return['capability']) ? $return['capability'] : 'manage_options';
						//check if plugin action link is set to true
						if (isset($return['pluginActionLink']) && is_array($return['pluginActionLink']))
							$this->pluginActionLinks[] = array_merge($return['pluginActionLink'], array(
								'page' => $class
							));
						$this->menus[] = $return;
					}
				}				
				if(count($this->menus)){
					usort($this->menus, array($this,'sort_menus')); //sort menu options by 'order' key
				}
				closedir($dir);	
			}

		}
		private function init_actions() {			
			add_action('admin_menu', array(&$this,"add_menu"));
			add_action('admin_head', array(&$this,'admin_enqueue_scripts'));
			add_action('wp_enqueue_scripts', array(&$this,'public_enqueue_scripts'));
			add_action('init', array(&$this,'ajax_init'));
			add_action('init', array(&$this,'route'));
			add_action('widgets_init', array(&$this,'register_widget'));
			if ($this->cronInterval) {
				add_filter('cron_schedules', array($this,'create_custom_schedule_event'));
				add_action($this->slug . '_cron', get_class() . '::cron_handler');
			}
			
			if (isset($this->shortcode)) {
				foreach ($this->shortcode as $code)
					add_shortcode($code, array($this,'shortcode_hook')); //register all shortcodes
			}
		}
		public function admin_enqueue_scripts() {
			//placeholder
		}
		
		public function ajax_handler() {
			//placeholder
		}
		
		public function create_custom_schedule_event($schedules) {
			$schedules[$this->slug . '_scheduler'] = array(
				'interval' => $this->cronInterval,
				'display' => __($this->name . ' Scheduler')
			);
			return $schedules;
		}
		
		
		public function public_enqueue_scripts() {
			//placeholder
		}
		


		
		public function cron_handler() {
			//placeholder
		}
		
		public function shortcode_hook($attr, $content = null, $tag, $code) {
			//placeholder
		}
		
		
		//embed ajax object in admin and public pages
		public function ajax_init() {
			
			$jsObject = array(
				'ajaxurl' => admin_url('admin-ajax.php') . '?action=' . $this->slug
			);
			wp_localize_script('jquery', $this->slug, $jsObject);
			// this hook is fired if the current viewer is not logged in
			add_action('wp_ajax_nopriv_' . $this->slug, array(
				&$this,
				'ajax_handler'
			));
			add_action('wp_ajax_' . $this->slug, array(
				&$this,
				'ajax_handler'
			));
		}
		
		
		/*Registering Plugin widgets in /widgets folder */
		public function register_widget() {
			$dir = opendir($this->plugindir . '/widgets/');
			if ($dir) {
				$widgets = array();
				while (($entry = readdir($dir)) !== false) {
					if (strrchr($entry, '.') === '.php') {
						require_once $this->plugindir . '/widgets/' . $entry;
						$class = substr($entry, 0, -4); // remove .php and get class name
						register_widget($class);
					}
				}
			}
		}
		/*Creating Plugin Action links*/
		public function plugin_action_links($links, $file) {			
			if (basename($file) == basename($this->FILE)) {
				foreach ($this->pluginActionLinks as $link)
					$links[] = '<a href="' . $this->get_url($link['page'], $link['action']) . '">' . $link['title'] . '</a>';
			}
			return $links;
		}
		public function add_menu() {
			$parent_slug = $this->parentPage;
			//add separate menu option for all files in controllers folder
			$dir         = opendir($this->plugindir . '/controllers/');			
			if (count($this->menus)) {											
				
				add_action('plugin_action_links', array(&$this,'plugin_action_links'), 10, 2);
				$this->page_hooks = [];				
				if (!$parent_slug) {
					$parent = array_shift($this->menus); //remove first element. It will be used as parent page
					add_menu_page($this->name, $this->name, 'manage_options', $parent['slug']);
					$this->page_hooks[] = add_submenu_page($parent['slug'], $parent['page_title'], $parent['menu_title'], $parent['capability'], $parent['slug'], array(
						&$this,
						'menuCallback'
					));
					$parent_slug = $parent['slug'];
				}
				//add submenu page for the rest
				foreach ($this->menus as $menu)
					$this->page_hooks[] = add_submenu_page($parent_slug, $menu['page_title'], $menu['menu_title'], $menu['capability'], $menu['slug'], array(
						&$this,
						'menuCallback'
					));				
			}
		}
		//process post request or any other processing required to do during initialization
		public function route() {

			if (!isset($_POST[$this->slug . '_controller']) || !isset($_POST[$this->slug . '_method']))
				return;
			$controller = $_POST[$this->slug . '_controller']; //controller name
			$action     = $_POST[$this->slug . '_method'];			
			if (key_exists($controller,$this->pages)) {	
				if (method_exists($this->pages[$controller], $action) === false) {
					die('Action doesn\'t exists');
				}			
				$this->pages[$controller]->$action();
			}
			else {
				die('Controller doesn\'t exists');
			}
		}
		
		//sort menu pages
		public function sort_menus($a, $b) {
			
			return $a['order'] - $b['order'];
		}
		
		//generates admin menu pages
		public function menuCallback($page = '', $action = '') {
			if (!$page)
				$page = $_GET['page']; //controller name
			if (!$action)
				$action = isset($_GET['action']) ? $_GET['action'] : 'index'; //method name

			if (key_exists($page,$this->pages)) {				
				$this->pages[$page]->$action();
			} else {
				echo 'controller not found';
			}
		}
		protected  function getRequestUri() {
	        $http = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'],'off')?'https':'http';
	        if(isset($_SERVER['HTTP_HOST']))
	            $host=$http.'://'.$_SERVER['HTTP_HOST'];

	        if ( isset( $_SERVER['HTTP_X_REWRITE_URL'] ) ) // IIS
	            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
	        elseif ( isset( $_SERVER['REQUEST_URI'] ) ) {
	            $requestUri = $_SERVER['REQUEST_URI'];
	        }
	        else
	            $requestUri=false;

	        return $host.$requestUri;
	    }

	    protected function IsAjaxRequest() {
	        return isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	    }

	    protected function wpShowErrors() {
	        error_reporting( E_ALL );
	        ini_set( 'display_errors', 'On' );
	        global $wpdb;
	        $wpdb->show_errors();
	    }
		
		//template parser  from view folder
		protected function render($template, $data = array(), $echo = true) {
			
			$file = $this->plugindir . '/views/' . $template . '.php';
			if (!file_exists($file)) {
				if ($echo) {
					echo 'View file doesn\'t exists';
					return;
				} else
					return 'View file doesn\'t exists';
			}
			extract($data);
			if ($echo) {
				include $file;
			} else {
				ob_start();
				include $file;
				$content = ob_get_contents();
				ob_end_clean();
				return $content;
			}
		}
		
		//returns url for admin page
		protected function get_url($page = '', $action = '') {
			if($page=='')
				$page = $_GET['page'];
			if (!empty($this->parentPage))
				$url = admin_url() . $this->parentPage;
			else
				$url = admin_url() . 'admin.php';
			
			if ($page)
				$url .= '?page=' . $page;
			if ($action)
				$url .= '&action=' . $action;
			return $url;
		}
		
		//save a single option in options array save('version','1.0.1');
		protected function save($optionName, $optionval) {
			if (key_exists($optionName, $this->options)) {
				$this->options[$optionName] = $optionval;
				update_option($this->optionName, $this->options);
				return true;
			}
			return false;
		}
		
		//get a sigle option value eg get('version');
		protected function get($optionName) {
			if (isset($this->options[$optionName])) {
				return $this->options[$optionName];
			}
			return false;
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
						
			
		}
		
		public function deactivate_plugin() {
			//placeholder
		}
		
		//enable and display errors
	}
}
?>
