<?php

class SNLM_Admin {

	protected $_plugin;

	public function __construct( $plugin ) {
		$this->_plugin = $plugin;
	}

	public function hooks() {
		add_action( 'admin_head', array( $this, 'add_css' ) );
		add_action( 'admin_menu', array( $this, 'add_sub_menu_page' ) );
		// add_action('admin_head', array($this, 'sn_remove_meta_boxes') );
 		add_action('admin_init', array( $this, 'hide_editor' ) );
	}

	/**
	* Add custom css to plugin 
	*/
	public function add_css() {
		echo '<link rel="stylesheet" href="' . SNLM_URL . '/assets/style.css" type="text/css" media="all" />';
	}

	/**
	* Add sub menu page 
	*/
	public function add_sub_menu_page(){
		$templates_page = add_submenu_page(
			'themes.php',
			'SN Lander Manager',
			'SN Lander Manager',
			'manage_options',
			'sn-lander-manager',
			array($this, 'sub_menu_page_render')
		);

		$api_errors_page = add_submenu_page(
			'tools.php',
			'SN API Errors',
			'SN API Errors',
			'manage_options',
			'sn-api-errors',
			array($this, 'display_api_errors')
		);

		$bing_keys_stats = add_submenu_page(
			'tools.php',
			'SN Bing Keys Stats',
			'SN Bing Keys Stats',
			'manage_options',
			'sn-bing-stats',
			array($this, 'bing_stats')
		);

		add_action( "admin_print_styles-{$templates_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}

	/**
	* Include Sub Menu page render
	*/
	public function sub_menu_page_render(){
		if($_POST) {
			$active = !empty($_POST['active']) ? $_POST['active'] : array();

			$newTemplates = array();
			foreach($active as $a) {
				$newTemplates[] = array(
					'name' => $a,
					'path' => SNLM_PATH . 'landers/'.$a
				);
			}

			update_site_option( 'sn_landers', $newTemplates );
		}


		$template_folders = scandir(SNLM_PATH . 'landers/');
		foreach($template_folders as $k => $folder) {
			if($folder{0} === '.') 
				unset($template_folders[$k]);
		}

		$templates = array_map(function($item) {
			return $item['name'];
		}, get_site_option( 'sn_landers', array() ) );

		$pages = get_pages();
		
		include SNLM_PATH . 'views/list.php';
	}

	public function display_api_errors() {
		if(isset($_GET['purge']) && $_GET['purge'] == 1) {
			update_site_option( 'sn_api_errors', array() );
		} else {
			$errors = get_site_option( 'sn_api_errors', array() );
		}
		include SNLM_PATH . 'views/api_errors.php';
	}
	
	public function hide_editor(){
		// Get the Post ID.
		$post_id = !empty($_GET['post']) ? $_GET['post'] : (!empty($_GET['post_ID']) ? $_GET['post_ID'] : null);
		if( !isset( $post_id ) ) 
			return;

		// Hide the editor on a page with a specific page template
		// Get the name of the Page Template file.
		$template_file = get_post_meta($post_id, '_wp_page_template', true);

		if(isset($this->_plugin->_templates->templates[$template_file])){ // the filename of the page template
			remove_post_type_support('page', 'editor');
			remove_post_type_support('page', 'thumbnail');
		}
	}

	public function bing_stats() {
		$used_keys = get_site_option( 'sn_used_bing_keys', array() );
		$count_keys = get_site_option( 'sn_count_bing_keys', array() );
		$disabled_keys = get_site_option( 'sn_disabled_bing_keys', array() );

		include SNLM_PATH . 'views/bing_stats.php';
	}
}