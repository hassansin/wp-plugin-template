<?php
defined('ABSPATH') or die("No script kiddies please!");

class List_Movie extends Wp_Plugin_Template_Demo {
  
  public $menuoptions = array(
    'order' => 1, 
    'page_title' => 'List', 
    'menu_title' => 'List', 
    'capability' => 'manage_options', //plugin capability , optionanl    
    // add links to plugin option page,optional
    'pluginActionLink' => array('action' => '', //use action name or leave empty for index
    'title' => 'Movie List'));
  
  public function index() {
    $data = array(
      "title" => 'Movie List'
    );
    $data['movies'] = $this->db->get_results("SELECT  * FROM {$this->table}");
    $this->render('options', $data);
  }
  
  
}

?>
