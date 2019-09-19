<?php

class SNLM_Offers {

	protected $_plugin;

	public function __construct( $plugin ) {
		$this->_plugin = $plugin;
	}

	public function hooks() {
		add_action('cmb2_admin_init', array( $this, 'add_offers_form' ) );
		//var_dump(get_site_option( 'sn_lander_offers_inported', 0 ));
		add_action( 'admin_menu', array( $this, 'add_offers_page' ) );
		if(!get_site_option('sn_lander_offers_inported', false)) {
			// Override CMB's getter
			add_filter( 'cmb2_override_option_get_' . 'sn_lander_offers', array( $this, '_get_override' ), 10, 2 );
			// Override CMB's setter
			add_filter( 'cmb2_override_option_save_' . 'sn_lander_offers', array( $this, '_update_override' ), 10, 2 );
		}
	}

	public function add_offers_page() {
		$offers_page = add_submenu_page(
			'tools.php',
			'SN Site Offers',
			'SN Site Offers',
			'manage_options',
			'sn-site-offers',
			array($this, 'display_offers')
		);

		if(!get_site_option('sn_lander_offers_inported', false)) {

			$offers_page = add_submenu_page(
				'tools.php',
				'SN Inport Old Offers',
				'SN Inport Old Offers',
				'manage_options',
				'sn-inport-offers',
				array($this, 'inport_offers')
			);

		}
	}

	public function display_offers() {
		include SNLM_PATH . 'views/offers.php';
	}

	public function inport_offers() {
		if(!empty($_POST['action'])) {

			$offers = get_site_option( 'sn_lander_offers', array() );

			update_option( 'sn_lander_offers', $offers );

			update_site_option( 'sn_lander_offers_inported', 1 );

			?>
			<div class="notice notice-success">
				<p>Offers Inported correctly</p>
			</div>
			<?php

		} ?>
		<div class="wrap">
			<h2>Inport old offers</h2>
			<form method="post">
				<p><input type="submit" name="action" class="button button-primary" value="Inport" /></p>
			</form>
		</div>
		<?php
	}

	public function add_offers_form() {
		add_action( "cmb2_save_options-page_fields_sn_lander_offers_form", array( $this, 'settings_notices' ), 10, 2 );

		$cmb = new_cmb2_box( array(
			'id'         => 'sn_lander_offers_form',
			'hookup'     => false,
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( 'sn-site-offers' )
			),
		) );

		$offers_field_id = $cmb->add_field(array(
			'id'          => 'sn_lander_offers',
		    'type'        => 'group',
		    'description' => '',
		    'repeatable'  => true,
		    'closed' 	  => true,
		    'options'     => array(
		        'group_title'   => 'Offer {#}', // since version 1.1.4, {#} gets replaced by row number
		        'add_button'    => 'Add Another Offer',
		        'remove_button' => 'Remove Offer'
		    )
		));

		$cmb->add_group_field( $offers_field_id, array(
		    'name' => 'Offer Name',
		    'id'   => 'name',
		    'type' => 'text',
		) );

		$cmb->add_group_field( $offers_field_id, array(
		    'name' => 'Browser Default',
		    'id'   => 'default',
		    'type' => 'text_url',
		) );

		$cmb->add_group_field( $offers_field_id, array(
		    'name' => 'Internet Explorer',
		    'id'   => 'ie',
		    'type' => 'text_url',
		) );

		$cmb->add_group_field( $offers_field_id, array(
		    'name' => 'Edge',
		    'id'   => 'edge',
		    'type' => 'text_url',
		) );

		$cmb->add_group_field( $offers_field_id, array(
		    'name' => 'Chrome - Windows',
		    'id'   => 'chrome_win',
		    'type' => 'text_url',
		) );

		$cmb->add_group_field( $offers_field_id, array(
		    'name' => 'Firefox - Windows',
		    'id'   => 'firefox_win',
		    'type' => 'text_url',
		) );

		$cmb->add_group_field( $offers_field_id, array(
		    'name' => 'Safari - Windows',
		    'id'   => 'safari_win',
		    'type' => 'text_url',
		) );

		$cmb->add_group_field( $offers_field_id, array(
		    'name' => 'Chrome - Mac',
		    'id'   => 'chrome_mac',
		    'type' => 'text_url',
		) );

		$cmb->add_group_field( $offers_field_id, array(
		    'name' => 'Firefox - Mac',
		    'id'   => 'firefox_mac',
		    'type' => 'text_url',
		) );

		$cmb->add_group_field( $offers_field_id, array(
		    'name' => 'Safari - Mac',
		    'id'   => 'safari_mac',
		    'type' => 'text_url',
		) );
	}

	public function settings_notices( $object_id, $updated ) {
		if ( $object_id !== 'sn_lander_offers' || empty( $updated ) ) {
			return;
		}
		add_settings_error( 'sn_lander_offers' . '-notices', '', __( 'Offers updated.', 'myprefix' ), 'updated' );
		settings_errors( 'sn_lander_offers' . '-notices' );
	}

	public function _get_override($test, $default) {
		return get_site_option( 'sn_lander_offers', $default );
	}

	public function _update_override( $test, $option_value ) {
		return update_site_option( 'sn_lander_offers', $option_value );
	}

	public function getOffers() {
		if(!get_site_option('sn_lander_offers_inported', false)) {
			$offers = get_site_option('sn_lander_offers');
		} else {
			$offers = get_option('sn_lander_offers');
		}
		$options = array();

		if($offers == null)
			return $options;
		
		foreach($offers['sn_lander_offers'] as $offer) {
			$options[$offer['name']] = $offer['name'];
		}		
		return $options;
	}

}