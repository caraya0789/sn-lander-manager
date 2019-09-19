<?php
/**
* Plugin Name: SN Lander Manager
* Plugin URI: http://www.seitznetwork.com/
* Description: Plugin to administrate Seitz Network's Landers
* Version: 1.0
* Author: Seitz Network
* Author URI: http://www.seitznetwork.com/
* License: GPLv2
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define( 'SNLM_VERSION', '2.0.0' );
define( 'SNLM__FILE__', __FILE__ );
define( 'SNLM_PATH', plugin_dir_path(SNLM__FILE__) );
define( 'SNLM_URL', plugin_dir_url(SNLM__FILE__) );

class SN_Lander_Manager {

	/**
	* A reference to an instance of this class.
	*/
	protected static $_instance;
  
  	protected static $_currentTemplate;

    const BING_DAILY_USAGE = 3000;

    public $_admin;
    public $_fields;
    public $_templates;
    public $_offers;

    /**
	* Returns an instance of this class.
    */
	public static function get_instance() {
		if(self::$_instance === null)
			self::$_instance = new SN_Lander_Manager();

		return self::$_instance;
	}

	/**
	* Initializes the plugin by setting filters and administration functions.
	*/
	public function __construct() {
		$this->_includes();
		$this->_init();
	}

	protected function _includes() {
		// Libraries
		require_once SNLM_PATH . 'vendors/Mobile_Detect.php';

		// Classes
		require_once SNLM_PATH . 'classes/Admin.php';
		require_once SNLM_PATH . 'classes/Fields.php';
		require_once SNLM_PATH . 'classes/Templates.php';
		require_once SNLM_PATH . 'classes/Offers.php';

		// Helper Functions
		require_once SNLM_PATH . 'includes/template-functions.php';
	}

	protected function _init() {
		$this->_admin = new SNLM_Admin( $this );
		$this->_fields = new SNLM_Fields( $this );
		$this->_templates = new SNLM_Templates( $this );
		$this->_offers = new SNLM_Offers( $this );
	}

	public function hooks() {
		// Admin
		$this->_admin->hooks();
		$this->_fields->hooks();
		$this->_templates->hooks();
		$this->_offers->hooks();
	}

	public function get_db_templates() {
		return $this->_templates->DB_templates;
	}
  
	public static function setCurrentTemplate($file) {
		self::$_currentTemplate = $file;
	}

	public static function getCurrentTemplate() {
		return self::$_currentTemplate;
	}

	public function getOffers() {
		return $this->_offers->getOffers();
	}
}

function snlm_get_instance() {
	return SN_Lander_Manager::get_instance();
}

add_action( 'plugins_loaded', array( snlm_get_instance(), 'hooks' ) );