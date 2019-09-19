<?php

function snlm_geolocate() {
	$data = array(
		'u' => 'fbcf2571-175d-447d-ac40-aed85e272569',
		'json' => 'true'
	);

	if($_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
		$data['ip'] = $_SERVER['REMOTE_ADDR'];
	}

	$url = 'http://usa.cloud.netacuity.com/webservice/query';
	$params = http_build_query($data);

	$curl = curl_init($url . '?' . $params);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	$result = json_decode(curl_exec($curl), true);

	if(empty($result['response']))
		return false;

	$location = array(
		'location' => array(
			'latitude' => !empty($result['response']['pulse-latitude']) ? $result['response']['pulse-latitude'] : '',
			'longitude' => !empty($result['response']['pulse-longitude']) ? $result['response']['pulse-longitude'] : ''
		)
	);

	if(isset($result['response']['edge-city'])) {
		$location['city'] = array(
			'names' =>  array(
				'en' => ucwords($result['response']['edge-city'])
			)
		);
	} else if(isset($result['response']['pulse-city'])) {
		$location['city'] = array(
			'names' =>  array(
				'en' => ucwords($result['response']['pulse-city'])
			)
		);
	} else if(isset($result['response']['city'])) {
		$location['city'] = array(
			'names' =>  array(
				'en' => ucwords($result['response']['city'])
			)
		);
	}

	if(isset($result['response']['edge-two-letter-country'])) {
		$location['country'] = array(
			'iso_code' => strtoupper($result['response']['edge-two-letter-country'])
		);
	} else if(isset($result['response']['pulse-two-letter-country'])) {
		$location['country'] = array(
			'iso_code' => strtoupper($result['response']['pulse-two-letter-country'])
		);
	} else if(isset($result['response']['two-letter-country'])) {
		$location['country'] = array(
			'iso_code' => strtoupper($result['response']['two-letter-country'])
		);
	}

	return $location;
}

function get_custom_field($id, $type = "default"){
	$option_name = basename(dirname(SN_Lander_Manager::getCurrentTemplate())).'_'.$id;
	$field = get_post_meta(get_the_ID(), $option_name, true);

	if($type == "textarea"){
		$field = preg_replace("/\r\n|\r|\n/",'<br/>', $field);
	}
	return $field;
}

function get_custom_repeatable_field($id){
	return basename(dirname(SN_Lander_Manager::getCurrentTemplate())).'_'.$id;
}

function get_browser_name(){
	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	$browser = "";

	if(get_custom_field('mc_use_browser_labels')) {

		if(strpos($user_agent, 'msie') !== false)
			$browser = (get_custom_field('mc_browser_label_ie')) ? get_custom_field('mc_browser_label_ie') : "Internet Explorer";
		elseif(strpos($user_agent, 'firefox') !== false)
			$browser = (get_custom_field('mc_browser_label_ff')) ? get_custom_field('mc_browser_label_ff') : "Firefox";
		elseif(strpos($user_agent, 'chrome') !== false)
			$browser = (get_custom_field('mc_browser_label_ch')) ? get_custom_field('mc_browser_label_ch') : "Google Chrome";
		elseif(strpos($user_agent, 'trident/') !== false)
			$browser = (get_custom_field('mc_browser_label_ie')) ? get_custom_field('mc_browser_label_ie') : "Internet Explorer";
		elseif(strpos($user_agent, 'safari') !== false)
			$browser = (get_custom_field('mc_browser_label_sf')) ? get_custom_field('mc_browser_label_sf') : "Safari";

	} else {

		if(strpos($user_agent, 'msie') !== false)
			$browser = "Internet Explorer";
		elseif(strpos($user_agent, 'firefox') !== false)
			$browser = "Firefox";
		elseif(strpos($user_agent, 'chrome') !== false)
			$browser = "Google Chrome";
		elseif(strpos($user_agent, 'trident/') !== false)
			$browser = "Internet Explorer";
		elseif(strpos($user_agent, 'safari') !== false)
			$browser = "Safari";

	}

	return $browser;
}

function urlBYWeight($data, $number = 1){
	$result = array();

	if (is_array($data) === true) {
		$data = array_map('abs', $data);
		$number = min(max(1, abs($number)), count($data));

		while ($number-- > 0) {
			$chance = 0;
			$probability = mt_rand(1, array_sum($data));

			foreach ($data as $key => $value){
				$chance += $value;

				if ($chance >= $probability){
					$result[] = $key; unset($data[$key]); break;
				}
			}
		}
	}

	return $result[0];
}

function get_offer_url(){
	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

	// var_dump($_SERVER['blockscript_blocked']);

	if(!get_site_option('sn_lander_offers_inported', false)) {
		$offers = get_site_option('sn_lander_offers', array('sn_lander_offers' => array()));
	} else {
		$offers = get_option('sn_lander_offers', array('sn_lander_offers' => array()));
	}

	$offer_option = (get_custom_field('bs_add_block_script') === 'on' && $_SERVER['blockscript_blocked'] == "YES") ? get_custom_field('blocked_po_offer') : get_custom_field('po_offer');

	$offer = false;

	foreach($offers['sn_lander_offers'] as $off) {
		if($off['name'] == $offer_option)
			$offer = $off;
	}

	if($offer === false)
		return '';

	$url = $offer['default'];

	if(strpos($user_agent, "macintosh") !== false){
		if(strpos($user_agent, 'chrome') !== false) {
			$chrome49 = get_custom_field( 'po_offer_ch48' );
			if( $chrome49 && !empty($chrome49) ) {
				$matches = []; preg_match('/Chrome\/(\d*)/i', $user_agent, $matches);
				if(count($matches) > 1) {
					$version = (int) $matches[1];
					$url = ($version <= 49) ? $chrome49 : $offer['chrome_mac'];
				} else {
					$url = $offer['chrome_mac'];
				}
			} else {
				$url = $offer['chrome_mac'];
			}
		}
		elseif(strpos($user_agent, 'firefox') !== false)
			$url = $offer['firefox_mac'];
		elseif(strpos($user_agent, 'safari') !== false) {
			if((get_custom_field('bs_add_block_script') === 'on' && $_SERVER['blockscript_blocked'] != "YES")) {
				$links = array(
					"http://appfocus.go2cloud.org/aff_c?offer_id=1115&aff_id=84&url_id=61&aff_sub=8ball-US-Safari-Mock" => 50,
					"http://a.peogr.com/?a=49588&c=1650782&m=33&s1=8ball-US-Safari&s2=" => 50
				);
				$url = urlBYWeight($links);
			} else {
				$url = $offer['safari_mac'];
			}
		}
	}else{
		if(strpos($user_agent, 'msie') !== false)
			$url = $offer['ie'];
		elseif(strpos($user_agent, 'trident/') !== false)
			$url = $offer['ie'];
		elseif(strpos($user_agent, 'edge') !== false)
			$url = $offer['edge'];
		elseif(strpos($user_agent, 'firefox') !== false)
			$url = $offer['firefox_win'];
		elseif(strpos($user_agent, 'chrome') !== false) {
			$chrome49 = get_custom_field( 'po_offer_ch48' );
			if( $chrome49 && !empty($chrome49) ) {
				$matches = []; preg_match('/Chrome\/(\d*)/i', $user_agent, $matches);
				if(count($matches) > 1) {
					$version = (int) $matches[1];
					$url = ($version <= 49) ? $chrome49 : $offer['chrome_win'];
				} else {
					$url = $offer['chrome_win'];
				}
			} else {
				$url = $offer['chrome_win'];
			}
		}
		elseif(strpos($user_agent, 'safari') !== false)
			$url = $offer['safari_win'];
	}

	return $url;
}

function get_ts_offer_url(){
	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

	if(!get_site_option('sn_lander_offers_inported', false)) {
		$offers = get_site_option('sn_lander_offers', array('sn_lander_offers' => array()));
	} else {
		$offers = get_option('sn_lander_offers', array('sn_lander_offers' => array()));
	}

	$offer_option = (get_custom_field('bs_add_block_script') === 'on' && $_SERVER['blockscript_blocked'] == "YES") ? get_custom_field('blocked_ts_offer') : get_custom_field('ts_offer');

	$offer = false;

	foreach($offers['sn_lander_offers'] as $off) {
		if($off['name'] == $offer_option)
			$offer = $off;
	}

	if($offer === false)
		return '';

	$url = $offer['default'];

	if(strpos($user_agent, "macintosh") !== false){
		if(strpos($user_agent, 'chrome') !== false)
			$url = $offer['chrome_mac'];
		elseif(strpos($user_agent, 'firefox') !== false)
			$url = $offer['firefox_mac'];
		elseif(strpos($user_agent, 'safari') !== false)
			$url = $offer['safari_mac'];
	}else{
		if(strpos($user_agent, 'msie') !== false)
			$url = $offer['ie'];
		elseif(strpos($user_agent, 'trident/') !== false)
			$url = $offer['ie'];
		elseif(strpos($user_agent, 'edge') !== false)
			$url = $offer['edge'];
		elseif(strpos($user_agent, 'firefox') !== false)
			$url = $offer['firefox_win'];
		elseif(strpos($user_agent, 'chrome') !== false)
			$url = $offer['chrome_win'];
		elseif(strpos($user_agent, 'safari') !== false)
			$url = $offer['safari_win'];
	}

	return $url;
}

function get_blocked_offer_url() {
	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

	if(!get_site_option('sn_lander_offers_inported', false)) {
		$offers = get_site_option('sn_lander_offers', array('sn_lander_offers' => array()));
	} else {
		$offers = get_option('sn_lander_offers', array('sn_lander_offers' => array()));
	}

	$offer_option = get_custom_field('blocked_po_offer');

	$offer = false;

	foreach($offers['sn_lander_offers'] as $off) {
		if($off['name'] == $offer_option)
			$offer = $off;
	}

	if($offer === false)
		return '';

	$url = $offer['default'];

	if(strpos($user_agent, "macintosh") !== false){
		if(strpos($user_agent, 'chrome') !== false)
			$url = $offer['chrome_mac'];
		elseif(strpos($user_agent, 'firefox') !== false)
			$url = $offer['firefox_mac'];
		elseif(strpos($user_agent, 'safari') !== false)
			$url = $offer['safari_mac'];
	}else{
		if(strpos($user_agent, 'msie') !== false)
			$url = $offer['ie'];
		elseif(strpos($user_agent, 'trident/') !== false)
			$url = $offer['ie'];
		elseif(strpos($user_agent, 'edge') !== false)
			$url = $offer['edge'];
		elseif(strpos($user_agent, 'firefox') !== false)
			$url = $offer['firefox_win'];
		elseif(strpos($user_agent, 'chrome') !== false)
			$url = $offer['chrome_win'];
		elseif(strpos($user_agent, 'safari') !== false)
			$url = $offer['safari_win'];
	}

	return $url;
}

function get_blocked_ts_offer_url() {
	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

	if(!get_site_option('sn_lander_offers_inported', false)) {
		$offers = get_site_option('sn_lander_offers', array('sn_lander_offers' => array()));
	} else {
		$offers = get_option('sn_lander_offers', array('sn_lander_offers' => array()));
	}

	$offer_option = get_custom_field('blocked_ts_offer');

	$offer = false;

	foreach($offers['sn_lander_offers'] as $off) {
		if($off['name'] == $offer_option)
			$offer = $off;
	}

	if($offer === false)
		return '';

	$url = $offer['default'];

	if(strpos($user_agent, "macintosh") !== false){
		if(strpos($user_agent, 'chrome') !== false)
			$url = $offer['chrome_mac'];
		elseif(strpos($user_agent, 'firefox') !== false)
			$url = $offer['firefox_mac'];
		elseif(strpos($user_agent, 'safari') !== false)
			$url = $offer['safari_mac'];
	}else{
		if(strpos($user_agent, 'msie') !== false)
			$url = $offer['ie'];
		elseif(strpos($user_agent, 'trident/') !== false)
			$url = $offer['ie'];
		elseif(strpos($user_agent, 'edge') !== false)
			$url = $offer['edge'];
		elseif(strpos($user_agent, 'firefox') !== false)
			$url = $offer['firefox_win'];
		elseif(strpos($user_agent, 'chrome') !== false)
			$url = $offer['chrome_win'];
		elseif(strpos($user_agent, 'safari') !== false)
			$url = $offer['safari_win'];
	}

	return $url;
}

function is_chrome_windows() {
	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	return (strpos($user_agent, 'chrome') !== false) && (strpos($user_agent, 'macintosh') === false);
}

function get_install_pixel() {
	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	switch(true) {
		case ( (strpos($user_agent, 'chrome') !== false) && (strpos($user_agent, 'edge') === false) ):
			return get_custom_field( 'cs_install_pix_ch' );
		break;
	}
	return '';
}

function get_conversion_script(){
	$detect = new Mobile_Detect();
	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

	$script = get_custom_field('cs_script');
	$config = get_custom_field('cs_config');

	if($config !== ""){
		foreach ($config as $key => $value) {
			switch ($value) {
				case 'tablet':
					if($detect->isTablet())
						$script = false;
					break;
				case 'mobile':
					if($detect->isMobile() && !$detect->isTablet())
						$script = false;
					break;
				case 'ie':
					if(strpos($user_agent, 'msie') !== false || strpos($user_agent, 'trident/') !== false)
						$script = false;
					break;
				case 'edge':
					if(strpos($user_agent, 'edge') !== false)
						$script = false;
					break;
				case 'safari':
					if((strpos($user_agent, 'safari') !== false) && (strpos($user_agent, 'macintosh') === false))
						$script = false;
					break;
				case 'chrome':
					if((strpos($user_agent, 'chrome') !== false) && (strpos($user_agent, 'macintosh') === false))
						$script = false;
					break;
				case 'firefox':
					if((strpos($user_agent, 'firefox') !== false) && (strpos($user_agent, 'macintosh') === false))
						$script = false;
					break;
				case 'safari_mac':
					if((strpos($user_agent, 'chrome') === false) && (strpos($user_agent, 'safari') !== false) && (strpos($user_agent, 'macintosh') !== false))
						$script = false;
					break;
				case 'chrome_mac':
					if((strpos($user_agent, 'chrome') !== false) && (strpos($user_agent, 'macintosh') !== false))
						$script = false;
					break;
				case 'firefox_mac':
					if((strpos($user_agent, 'firefox') !== false) && (strpos($user_agent, 'macintosh') !== false))
						$script = false;
					break;
			}
		}
	}

	if(isset($_SERVER['HTTP_REFERER']))
		return str_replace('[REFERER]', base64_encode($_SERVER['HTTP_REFERER']), $script);

	return str_replace('[REFERER]', '', $script);
}

function get_adsense_script($id, $size){
    $adsenseId = get_custom_field($id);
    $clientId = get_custom_field('ai_client_id');
    $dimensions = explode("x", $size);

    if($adsenseId == "0000000000" || $adsenseId == ""){
	    $src = basename(dirname(SN_Lander_Manager::getCurrentTemplate()))."/template/images/ads/".$size.".jpg";
	    $pluginUrl = plugins_url('', __FILE__);
	    $image = "<img src='" . SNLM_URL . "landers/" . $src . "' />";
		return $image;
	}else{
		$script = <<<HTML
    		<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	        <ins class="adsbygoogle"
	             style="display:inline-block;width:$dimensions[0]px;height:$dimensions[1]px"
	             data-ad-client="$clientId"
	             data-ad-slot="$adsenseId"></ins>
	        <script>
	        (adsbygoogle = window.adsbygoogle || []).push({});
	        </script>
HTML;
		return $script;
	}
}

function get_bing_api_key() {
	// $path = dirname(__FILE__);
	// $file = $path . '/disabled_bing_keys.json';
	$used_keys = get_site_option( 'sn_used_bing_keys', array() );
	$disabled_keys = get_site_option( 'sn_disabled_bing_keys', array() );

	//var_dump($disabled_keys); die;

	$_keys = explode("\n", get_custom_field('ac_bing_key'));
	$keys = array();
	foreach($_keys as $k) {
		$k = sanitize_text_field($k);
		if(empty($disabled_keys[$k]))
			$keys[] = $k;
	}

	if(count($keys) === 0)
		return false;

	$key = $keys[0];

	if(count($used_keys) == 0)
	 	return trim($key);

	foreach($keys as $k) {
		$kk = trim($k);
		if(empty($used_keys[$kk])) {
			$key = $k;
			return trim($key);
		}
	}

	// If we hit this point all keys have been used, we should start over
	update_site_option( 'sn_used_bing_keys', array() );
	// Return the first key
	return trim($key);
}

function snlm_allow_php( $value, $field_args, $field ) {
	return $value;
}