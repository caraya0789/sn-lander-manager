<?php

class SNLM_Fields {

	protected $_plugin;

	public function __construct( $plugin ) {
		$this->_plugin = $plugin;
	}

	public function hooks() {
		add_action('cmb2_init',  array( $this, 'custom_fields' ) );
	}

	/**
	* CMB2 plugin init
	*/
	public function custom_fields(){
		foreach ($this->_plugin->get_db_templates() as $key => $template) {
			$json =  SNLM_PATH . 'landers/'.$template['name'].'/options.json';
			$json = str_replace('//', '/', $json);
			$options = file_get_contents($json);
			$options = json_decode($options, true);
			
			$this->register_group_fields($options['custom_fields'], $template);
		} 
	}

	/**
	* Recording group fields for specific Template
	*/
	public function register_group_fields($custom_fields, $template){
		foreach ($custom_fields as $key => $group_field) {
			$rex = new_cmb2_box(array(
				'title'	=> $group_field['title'],
				'id'	=> $template['name'].'_'.$group_field['id'],
				'object_types'	=> array( 'page' ),
				'show_on'	=> array( 'key' => 'page-template', 'value' => 'landers/'.$template['name'].'/index.php' ),
			));

			$this->register_custom_fields($group_field['fields'], $template, $rex);
		}
	}

	/**
	* Recording fields for specific Group Fields
	*/
	public function register_custom_fields($fields, $template, $rex){
		foreach ($fields as $key => $field) {
			if($field['type'] == 'offer') {
				$field['type'] = 'select';
				$field['show_option_none'] = true;
				$field['options'] = $this->_plugin->getOffers();
			}
			//Rewriting ID to be unique
			$field['id'] = $template['name'].'_'.$field['id'];

			if($field['type'] == 'group'){
				$repeatable_field_id = $rex->add_field($field);
				$this->register_repeatable_fields($field['repeatable_fields'], $template, $rex, $repeatable_field_id);
			}else{
				/*if(isset($field['default']) && function_exists('pll_register_string') && $field['type'] == 'text') {
					pll_register_string('sn-lander-manager', $field['default']);
					$field['default'] = pll__($field['default']);
				}*/
				$rex->add_field($field);
			}
		}
	}

	/**
	* Recording Group Fields
	*/
	public function register_repeatable_fields($repeatable_fields, $template, $rex, $repeatable_field_id){
		foreach ($repeatable_fields as $key => $field) {
			$field['id'] = $template['name'].'_'.$field['id'];
			if($field['type'] == 'select_page') {
				$field['type'] = 'select';
				$field['show_option_none'] = true;
				$field['options'] = $this->getPages();
			}

			$rex->add_group_field($repeatable_field_id, $field);
		}
	}

	public function getPages() {
		$allPages = get_pages(array('hierarchical' => 0));
		$pages = array();
		foreach($allPages as $page) {
			$pages[$page->ID] = $page->post_title;
		}
		return $pages;
	}

}