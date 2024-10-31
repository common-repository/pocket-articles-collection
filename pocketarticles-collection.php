<?php
/*
Plugin Name: Pocket Articles Collection
Plugin URI: http://www.thestrongtype.com/resources/pocket-articles-collection-wordpress-plugin/
Description: Pocket Articles Collection plugin helps you collect and display your favourite articles saved to your Pocket account on your WordPress blog.
Version: 1.0.0
title: Martin H
title URI: http://www.thestrongtype.com
License: GPL2
*/

/*  Copyright 2013 - Martin Hallonqvist (email : martin@thestrongtype.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



/*	The 'Next pocketarticle »' link text
	By default, this is 'Next Pocket article »' (or the corresponding translation).
	You can change it if you wish */
$pocketarticlescollection_next_pocketarticle = "";

/*  Refer http://codex.wordpress.org/Roles_and_Capabilities */
$pocketarticlescollection_admin_userlevel = 'edit_posts'; 

$pocketarticlescollection_version = '1.0.0';
$pocketarticlescollection_db_version = '1.0'; 

require_once('pocketarticles-collection-admin.php');
require_once('pocketarticles-collection-shortcodes.php');
require_once('pocketarticles-collection-settings.php');

function pocketarticlescollection_get_randompocketarticle($exclude = 0)
{
	if($exclude) $condition = "pocketarticle_id <> ".$exclude;
	else $condition = "";
	return pocketarticlescollection_get_pocketarticle($condition);
}

function pocketarticlescollection_get_pocketarticles($condition = "")
{
	global $wpdb;
	$sql = "SELECT pocketarticle_id, pocketarticle, title, sourceurl, tags, public, UNIX_TIMESTAMP(time_added) time_added
		FROM " . $wpdb->prefix . "pocketarticlescollection"
		. $condition;
	if($pocketarticles = $wpdb->get_results($sql, ARRAY_A))
		return $pocketarticles;	
	else
		return array();
}

function pocketarticlescollection_get_pocketarticle($condition = '', $random = 1, $current = 0)
{
	global $wpdb;
	$sql = "SELECT pocketarticle_id, pocketarticle, title, sourceurl
		FROM " . $wpdb->prefix . "pocketarticlescollection";
	if ($condition)
		$sql .= $condition;
	if(!$random) {
		if($current)
			$sql .= " AND pocketarticle_id < {$current}";
		$sql .= " ORDER BY pocketarticle_id DESC";
	}
	else
		$sql .= " ORDER BY RAND(UNIX_TIMESTAMP(NOW()))";
	$sql .= " LIMIT 1";
	$random_pocketarticle = $wpdb->get_row($sql, ARRAY_A);
	if ( empty($random_pocketarticle) ) {
		if(!$random && $current)
			return pocketarticlescollection_get_pocketarticle($condition, 0, 0);
		else
			return 0;
	}
	else
		return $random_pocketarticle;
}


function pocketarticlescollection_count($condition = "")
{
	global $wpdb;
	$sql = "SELECT COUNT(*) FROM " . $wpdb->prefix . "pocketarticlescollection ".$condition;
	$count = $wpdb->get_var($sql);
	return $count;
}

function pocketarticlescollection_pagenav($total, $current = 1, $format = 0, $paged = 'paged', $url = "")
{
	if($total == 1 && $current == 1) return "";
	
	if(!$url) {
		$url = 'http';
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$url .= "s";}
		$url .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
		} else {
			$url .= $_SERVER["SERVER_NAME"];
		}

		if ( get_option('permalink_structure') != '' ) {
			if($_SERVER['REQUEST_URI']) {
				$request_uri = explode('?', $_SERVER['REQUEST_URI']);
				$url .= $request_uri[0];
			}
			else $url .= "/";
		}
		else {
			$url .= $_SERVER["PHP_SELF"];
		}
		
		if($query_string = $_SERVER['QUERY_STRING']) {
			$parms = explode('&', $query_string);
			$y = '';
			foreach($parms as $parm) {
				$x = explode('=', $parm);
				if($x[0] == $paged) {
					$query_string = str_replace($y.$parm, '', $query_string);
				}
				else $y = '&';
			}
			if($query_string) {
				$url .= '?'.$query_string;
				$a = '&';
			}
			else $a = '?';	
		}
		else $a = '?';
	}
	else {
		$a = '?';
		if(strpos($url, '?')) $a = '&';	
	}
	
	if(!$format || $format > 2 || $format < 0 || !is_numeric($format)) {	
		if($total <= 8) $format = 1;
		else $format = 2;
	}
	
	
	if($current > $total) $current = $total;
		$pagenav = "";

	if($format == 2) {
		$first_disabled = $prev_disabled = $next_disabled = $last_disabled = '';
		if($current == 1)
			$first_disabled = $prev_disabled = ' disabled';
		if($current == $total)
			$next_disabled = $last_disabled = ' disabled';

		$pagenav .= "<a class=\"first-page{$first_disabled}\" title=\"".__('Go to the first page', 'pocketarticles-collection')."\" href=\"{$url}\">&laquo;</a>&nbsp;&nbsp;";

		$pagenav .= "<a class=\"prev-page{$prev_disabled}\" title=\"".__('Go to the previous page', 'pocketarticles-collection')."\" href=\"{$url}{$a}{$paged}=".($current - 1)."\">&#139;</a>&nbsp;&nbsp;";

		$pagenav .= '<span class="paging-input">'.$current.' of <span class="total-pages">'.$total.'</span></span>';

		$pagenav .= "&nbsp;&nbsp;<a class=\"next-page{$next_disabled}\" title=\"".__('Go to the next page', 'pocketarticles-collection')."\" href=\"{$url}{$a}{$paged}=".($current + 1)."\">&#155;</a>";

		$pagenav .= "&nbsp;&nbsp;<a class=\"last-page{$last_disabled}\" title=\"".__('Go to the last page', 'pocketarticles-collection')."\" href=\"{$url}{$a}{$paged}={$total}\">&raquo;</a>";
	
	}
	else {
		$pagenav = __("Goto page:", 'pocketarticles-collection');
		for( $i = 1; $i <= $total; $i++ ) {
			if($i == $current)
				$pagenav .= "&nbsp;<strong>{$i}</strong>";
			else if($i == 1)
				$pagenav .= "&nbsp;<a href=\"{$url}\">{$i}</a>";
			else 
				$pagenav .= "&nbsp;<a href=\"{$url}{$a}{$paged}={$i}\">{$i}</a>";
		}
	}
	return $pagenav;
}

function pocketarticlescollection_txtfmt($pocketarticledata = array())
{
	if(!$pocketarticledata)
		return;

	foreach($pocketarticledata as $key => $value){
		$value = make_clickable($value); 
		$value = wptexturize(str_replace(array("\r\n", "\r", "\n"), '', nl2br(trim($value))));
		$pocketarticledata[$key] = $value;
	}
	
	return $pocketarticledata;	
}

function pocketarticlescollection_output_format( $pocketarticle_data, $options = array('show_title' => 1, 'show_sourceurl' => 1, 'show_source' => 0, 'show_date' => 0) )
{
	$learn_more_label = __('Learn more', 'pocket_articles');
	$display = "";

	$display .= '<div class="col">';
	$display .= '<h4><a target="_blank" href="' . $pocketarticle_data['sourceurl'] .'">'. $pocketarticle_data['title'] .'</a></h4>';
	$display .= '<p>'.$pocketarticle_data['pocketarticle'];
	$display .= '<br/>';
	$extra_info='';
	if ($options['show_date'] == 'true') {
		$extra_info .= date_i18n(get_option('date_format'), $pocketarticle_data['time_added']);
	}
	if ($options['show_source'] == 'true') {
		$extra_info .= ( ($extra_info=='')?'':' | ') . '<a target="_blank" href="' . parse_url($pocketarticle_data['sourceurl'],PHP_URL_SCHEME).'://'.parse_url($pocketarticle_data['sourceurl'],PHP_URL_HOST) .'">'.parse_url($pocketarticle_data['sourceurl'],PHP_URL_HOST).'</a>';
	}
	if ($extra_info != '') {
		$display .= '<span style="font-style:italic">' . $extra_info . '</span>';
	}
	$display .= '<span style="float:right"><a target="_blank" href="' . $pocketarticle_data['sourceurl'] .'">'.$learn_more_label.'</a></span>';
	$display .= '</p>';
	$display .= '</div>';
	return $display;
}


function pocketarticlescollection_display_randompocketarticle($show_title = 1, $show_sourceurl = 1, $random_pocketarticle = array()) 
{
	$args = "show_title={$show_title}&show_sourceurl={$show_sourceurl}&char_limit={$char_limit}&echo=1";
	return pocketarticlescollection_pocketarticle($args);
}


function pocketarticlescollection_pocketarticle($args = '') 
{
	global $pocketarticlescollection_instances, $pocketarticlescollection_next_pocketarticle;
	if(!$pocketarticlescollection_next_pocketarticle) $pocketarticlescollection_next_pocketarticle = __('Next Pocket article', 'pocketarticles-collection')."&nbsp;&raquo;";
	if(!($instance = $pocketarticlescollection_instances))
		$instance = $pocketarticlescollection_instances = 0;
	
		$key_value = explode('&', $args);
	$options = array();
	foreach($key_value as $value) {
		$x = explode('=', $value);
		$options[$x[0]] = $x[1]; // $options['key'] = 'value';
	}
	
	$options_default = array(
		'show_title' => 1,
		'show_sourceurl' => 1,
		'auto_refresh' => 0,
		'tags' => '',
		'char_limit' => 500,
		'echo' => 1,
		'random' => 1,
		'exclude' => '',
		'current' => 0
	);
	
	$options = array_merge($options_default, $options);
	
	$condition = " WHERE public = 'yes'";
	
	if($options['random'])
		$current = 0;
	else $current = $options['current'];
	
	if($options['char_limit'] && is_numeric($options['char_limit']))
		$condition .= " AND CHAR_LENGTH(pocketarticle) <= ".$options['char_limit'];
	
	else $options['char_limit'] = 0;
	
	if($options['exclude'])
		$condition .=" AND pocketarticle_id <> ".$options['exclude'];
		
	if($options['tags']) {
		$taglist = explode(',', $options['tags']);
		$tag_condition = "";
		foreach($taglist as $tag) {
			$tag = mysql_real_escape_string(strip_tags(trim($tag)));
			if($tag_condition) $tag_condition .= " OR ";
			$tag_condition .= "tags = '{$tag}' OR tags LIKE '{$tag},%' OR tags LIKE '%,{$tag},%' OR tags LIKE '%,{$tag}'";
		}
		$condition .= " AND ({$tag_condition})";
	}
	$random_pocketarticle = pocketarticlescollection_get_pocketarticle($condition, $options['random'], $current);

	if(!$random_pocketarticle)
		return;
	
	$random_pocketarticle  = pocketarticlescollection_txtfmt($random_pocketarticle);
				
	$display = pocketarticlescollection_output_format($random_pocketarticle, $options);
	
	// We don't want to display the 'next pocketarticle' link if there is no more than 1 pocketarticle
	$pocketarticles_count = pocketarticlescollection_count($condition); 
	
	$display = "<div id=\"pocketarticlescollection_randompocketarticle-".$instance."\" class=\"pocketarticlescollection_randompocketarticle\">{$display}</div>";
	$pocketarticlescollection_instances++;
	if($options['echo'])
		echo $display;
	else
		return $display;
}



function pocketarticlescollection_install()
{
	global $wpdb;
	$table_name = $wpdb->prefix . "pocketarticlescollection";

	if(!defined('DB_CHARSET') || !($db_charset = DB_CHARSET))
		$db_charset = 'utf8';
	$db_charset = "CHARACTER SET ".$db_charset;
	if(defined('DB_COLLATE') && $db_collate = DB_COLLATE) 
		$db_collate = "COLLATE ".$db_collate;


	// if table name already exists
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
   		$wpdb->query("ALTER TABLE `{$table_name}` {$db_charset} {$db_collate}");

   		$wpdb->query("ALTER TABLE `{$table_name}` MODIFY pocketarticle TEXT {$db_charset} {$db_collate}");

   		$wpdb->query("ALTER TABLE `{$table_name}` MODIFY title VARCHAR(255) {$db_charset} {$db_collate}");

   		$wpdb->query("ALTER TABLE `{$table_name}` MODIFY sourceurl VARCHAR(255) {$db_charset} {$db_collate}");

   		if(!($wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'tags'"))) {
   			$wpdb->query("ALTER TABLE `{$table_name}` ADD `tags` VARCHAR(255) {$db_charset} {$db_collate} AFTER `sourceurl`");
		}
   		if(!($wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'public'"))) {
   			$wpdb->query("ALTER TABLE `{$table_name}` CHANGE `visible` `public` enum('yes', 'no') DEFAULT 'yes' NOT NULL");
		}
	}
	else {
		//Creating the table ... fresh!
		$sql = "CREATE TABLE " . $table_name . " (
			pocketarticle_id mediumint(9) NOT NULL AUTO_INCREMENT,
			pocketarticle TEXT NOT NULL,
			title VARCHAR(255),
			sourceurl VARCHAR(255),
			tags VARCHAR(255),
			public enum('yes', 'no') DEFAULT 'yes' NOT NULL,
			time_added datetime NOT NULL,
			time_updated datetime,
			PRIMARY KEY  (pocketarticle_id)
		) {$db_charset} {$db_collate};";
		$results = $wpdb->query( $sql );
	}
	
	global $pocketarticlescollection_db_version;
	$options = get_option('pocketarticlescollection');
	$options['db_version'] = $pocketarticlescollection_db_version;
	update_option('pocketarticlescollection', $options);
	update_option('pocketarticlescollection_run_messages', array());

}


function pocketarticlescollection_css_head()
{
	global $pocketarticlescollection_version;
	if ( !is_admin() ) {
		wp_register_style( 'pocketarticlescollection-style', plugins_url('pocketarticles-collection.css', __FILE__), false, $pocketarticlescollection_version );
		wp_enqueue_style( 'pocketarticlescollection-style' );
	}
}
add_action( 'wp_enqueue_scripts', 'pocketarticlescollection_css_head' );
register_activation_hook( __FILE__, 'pocketarticlescollection_install' );

function custom_plugin_setup() {
    load_plugin_textdomain('pocketarticles-collection', false, dirname(plugin_basename(__FILE__)) . '/languages/');
} // end custom_theme_setup
add_action('after_setup_theme', 'custom_plugin_setup');

/* Handle recurring task */
	register_activation_hook(__FILE__,'pocketarticlescollection_recurringtask_activation');
	/* The deactivation hook is executed when the plugin is deactivated */
	register_deactivation_hook(__FILE__,'pocketarticlescollection_recurringtask_deactivation');
	/* This function is executed when the user activates the plugin */
	function pocketarticlescollection_recurringtask_activation(){  wp_schedule_event(time()+3*60, 'hourly', 'pocketarticlescollection_recurringtask_hook');}
	/* This function is executed when the user deactivates the plugin */
	function pocketarticlescollection_recurringtask_deactivation(){  wp_clear_scheduled_hook('pocketarticlescollection_recurringtask_hook');}
	/* We add a function of our own to the my_hook  */
	action.add_action('pocketarticlescollection_recurringtask_hook','pocketarticlescollection_recurringtask_function');
	/* This is the function that is executed by the hourly recurring action my_hook */
	function pocketarticlescollection_recurringtask_function(){
		$options = get_option( 'pocketarticlescollection_settings' );
		$access_token = $options["access_token"];
		$consumer_key = $options["consumer_key"];

		$args = array(
		'method' => 'POST',
		'httpversion' => '1.1',
		'headers' => array( 
			'Authorization' => 'Basic ' . $credentials,
			'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
			),
			'body' => array( 'consumer_key' => $consumer_key, 'access_token' => $access_token, 'detailType' => 'complete' )
		);
		
		if ($options['favoritesonly_checkbox'] == "1")
		{
			$args["body"]["favorite"] = 1;
		}
		//Add this line if you are having problems with ssl certificates
		//add_filter('https_ssl_verify', '__return_false');
		$response = wp_remote_post( 'https://getpocket.com/v3/get', $args );

		$run_messages = get_option( 'pocketarticlescollection_run_messages' );
		if (is_wp_error($response) || $response["response"]["code"] != 200)
		{
			if (array_unshift($run_messages, date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ) . ":" . __('ERROR: Could not connect to Pocket to retrieve articles') ) > 10)
			{
				array_pop($run_messages);
			}
			update_option('pocketarticlescollection_run_messages', $run_messages);
		}
		else
		{
			$json_raw = $response["body"];
			$json_objects = json_decode($json_raw);
			$num_new_articles = 0;
			foreach ($json_objects->list as $id => $json_object)
			{
				if (!pocketarticlescollection_does_pocketarticle_exist($json_object->resolved_url))
				{
					$tag_list = "";
					if (isset($json_object->tags) ) {
						
						foreach ($json_object->tags as $tag)
						{
							if ($tag_list == "")
								$tag_list = $tag->tag;
							else
								$tag_list = $tag_list . "," . $tag->tag;
						}
					}
					if (pocketarticlescollection_addpocketarticle($json_object->excerpt, $json_object->resolved_title, $json_object->resolved_url, $tag_list, 'yes') == __('pocketarticle added', 'pocketarticles-collection'))
					{
						$num_new_articles++;
					}
				}
			}
			if (array_unshift($run_messages, date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ) . ":" . __('Pocket articles updated. Number of new articles: ') . $num_new_articles ) > 10)
			{
				array_pop($run_messages);
			}
			update_option('pocketarticlescollection_run_messages', $run_messages);
		}
	}
?>
