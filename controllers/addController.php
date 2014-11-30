<?php
defined('ABSPATH') or die("No script kiddies please!");

class Add_Controller extends Wp_Plugin_Template_Demo {
  
  public $menuoptions = array(
    'order' => 0, 
    'page_title' => 'Add New', 
    'menu_title' => 'Add New', 
    'capability' => 'manage_options', //plugin capability , optionanl    
    // add links to plugin option page, optional
    'pluginActionLink' => array('action' => '', //use action name or leave empty for index
    'title' => 'Add New'));
  
  public function index() {
    $data = array(
      "title" => 'Add New'
    );
    
    $this->render('addnew', $data);
  }
  public function add(){
    $this->db->insert($this->table,array(
      'title' => $_POST['mov_title'],
      'url' => $_POST['mov_url']
      ));    
    wp_redirect($this->get_url().'&added=1');
    exit;
  }
  
  
}

?>
