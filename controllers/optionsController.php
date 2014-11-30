<?php
defined('ABSPATH') or die("No script kiddies please!");

class Options_Controller extends Wp_Plugin_Template_Demo {
  
  public $menuoptions = array(
    'order' => 1, 
    'page_title' => 'Options', 
    'menu_title' => 'Options', 
    'capability' => 'manage_options', //plugin capability , optionanl    
    // add links to plugin option page,optional
    'pluginActionLink' => array('action' => '', //use action name or leave empty for index
    'title' => 'Options'));
  
  public function index() {
    $data = array(
      "title" => 'Options'
    );
    $data['movies'] = $this->db->get_results("SELECT  * FROM {$this->table}");
    $this->render('options', $data);
  }
  
  
}

?>
