<?php

class SNLM_Templates {

	protected $_plugin;

	public $templates;
	public $DB_templates;

	public function __construct( $plugin ) {
		$this->_plugin = $plugin;

		$this->_init();
	}

	protected function _init() {
		$this->templates = array();
		$this->DB_templates = (!get_site_option( 'sn_landers' )) ? array() : get_site_option( 'sn_landers' );

		foreach ($this->DB_templates as $key => $value) {
			$json = SNLM_PATH . 'landers/'.$value['name'].'/options.json';
			$options = file_get_contents($json);
			$options = json_decode($options, true);
			$this->templates['landers/'.$value['name'].'/index.php'] = $options['name'];
		}
	}

	public function hooks() {
		add_filter( 'theme_page_templates', array( $this, 'register_project_templates' ) );

        // Add a filter to the template include to determine if the page has our 
		// template assigned and return it's path
        add_filter( 'template_include', array( $this, 'view_project_template' ) );

		add_action( 'wp_ajax_snlm_log_error', array( $this, 'logError' ) );
		add_action( 'wp_ajax_nopriv_snlm_log_error', array( $this, 'logError' ) );

		add_action( 'wp_ajax_disable_bing_key', array( $this, 'disableBingKey' ) );
		add_action( 'wp_ajax_nopriv_disable_bing_key', array( $this, 'disableBingKey' ) );

		add_action( 'wp_ajax_count_bing_key', array( $this, 'countBingKey' ) );
		add_action( 'wp_ajax_nopriv_count_bing_key', array( $this, 'countBingKey' ) );
	}

	/**
	* Recording project templates
	*/
	public function register_project_templates( $page_templates ) {
        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge( $page_templates, $this->templates );        

        return $templates;
    } 

    /**
    * Checks if the template is assigned to the page
    */
    public function view_project_template( $template ) {
        global $post;

        if (!isset($this->templates[get_post_meta( 
			$post->ID, '_wp_page_template', true 
		)] ) ) {
			
                return $template;
				
        } 

        $file = SNLM_PATH . get_post_meta( 
			$post->ID, '_wp_page_template', true 
		);

		SN_Lander_Manager::setCurrentTemplate($file);

		// Just to be safe, we check if the file exist first
		if( file_exists( $file ) ) {
			return $file;
		} 

		return $template;
    } 

	protected function _getBrowser() {
		$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

		if(strpos($user_agent, "macintosh") !== false){
			if(strpos($user_agent, 'chrome') !== false)
				return 'chrome_mac';
			if(strpos($user_agent, 'firefox') !== false)
				return 'firefox_mac';
			if(strpos($user_agent, 'safari') !== false)
				return 'safari_mac';
		}else{
			if(strpos($user_agent, 'msie') !== false)
				return 'ie';
			if(strpos($user_agent, 'trident/') !== false)
				return 'ie';
			if(strpos($user_agent, 'edge') !== false)
				return 'edge';
			if(strpos($user_agent, 'firefox') !== false)
				return 'firefox_win';
			if(strpos($user_agent, 'chrome') !== false)
				return 'chrome_win';
			if(strpos($user_agent, 'safari') !== false)
				return 'safari_win';
		}
		return 'other: '.$user_agent;
	}

	protected function _cleanOldErrors($errors) {

		//var_dump($errors);

		$time = (int) current_time('timestamp');

		$twodays_time = 60 * 60 * 24 * 2;

		$new_errors = array();

		foreach($errors as $error) {
			$error_time = (int) $error['time'];
			$diff_time = $time - $error_time;
			if($diff_time < $twodays_time) {
				$new_errors[] = $error;
			}
		}

		return $new_errors;

	}

	public function logError() {
		try {
			$errors = get_site_option( 'sn_api_errors', array() );

			$errors = $this->_cleanOldErrors($errors);

			$errors[] = array(
				'api' => sanitize_text_field($_GET['api']),
				'message' => sanitize_text_field($_GET['message']),
				'referer' => sanitize_text_field($_GET['referer']),
				'browser' => $this->_getBrowser(),
				'time' => current_time('timestamp'),
			);
			update_site_option( 'sn_api_errors', $errors );
			echo 'success';
		} catch(Exception $e) { }
		wp_die();
	}

	public function disableBingKey() {
		$disabled_keys = get_site_option( 'sn_disabled_bing_keys', array() );

		$key = sanitize_text_field($_GET['key']);
		$disabled_keys[$key] = date('Y-m-d H:i:s');

		update_site_option( 'sn_disabled_bing_keys', $disabled_keys );

		wp_die();
	}

	public function countBingKey() {
		$count_keys = get_site_option( 'sn_count_bing_keys', array() );

		$key = sanitize_text_field($_GET['key']);
		$today = date('Y-m-d');

		if(!isset($count_keys[$key])) {
			// create key
			$count_keys[$key] = array(
				$today => 0
			);
		} elseif(!isset($count_keys[$key][$today])) {
			$count_keys[$key][$today] = 0;
		}

		$count_keys[$key][$today] = intval($count_keys[$key][$today]) + 1;

		update_site_option( 'sn_count_bing_keys', $count_keys );

		if(($count_keys[$key][$today] % SN_Lander_Manager::BING_DAILY_USAGE) === 0) {
			$used_keys = get_site_option( 'sn_used_bing_keys', array() );
			$used_keys[$key] = date('Y-m-d H:i:s');
			update_site_option( 'sn_used_bing_keys', $used_keys );
		}

		wp_die();
	}

}