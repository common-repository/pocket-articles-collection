<?php

function pocketarticlescollection_shortcode_output_format($pocketarticles, $show_source=0, $show_date=0)
{
	$display = "";

	foreach($pocketarticles as $pocketarticle_data) {
		$display .= "<div class=\"pocketarticlescollection\" id=\"pocketarticle-".$pocketarticle_data['pocketarticle_id']."\">";
		$display .= pocketarticlescollection_output_format( $pocketarticle_data, array('show_source' => $show_source, 'show_date' => $show_date) );
		$display .= "</div>\n";
	}
	return apply_filters( 'pocketarticlescollection_shortcode_output_format', $display );
}


function pocketarticlescollection_shortcodes($atts = array())
{
	extract( shortcode_atts( array(
		'limit' => 0,
		'id' => 0,
		'title' => '',
		'sourceurl' => '',
		'tags' => '',
		'orderby' => 'pocketarticle_id',
		'order' => 'ASC',
		'paging' => false,
		'limit_per_page' => 10,
		'page' => -1,
		'show_paging' => true,
		'show_source' => false,
		'show_date' => false
	), $atts ) );
	
	
	$condition = " WHERE public = 'yes'";
	
	if(isset($pocketarticle_id) && is_numeric($pocketarticle_id)) $id = $pocketarticle_id;
	
	if($id && is_numeric($id)) {
		$condition .= " AND pocketarticle_id = ".$id;
		
		if ($pocketarticle = pocketarticlescollection_get_pocketarticles($condition))
			return pocketarticlescollection_shortcode_output_format($pocketarticle, $show_source, $show_date);
		else
			return "";
	}
	
	if($title)
		$condition .= " AND title = '".$title."'";
	if($sourceurl) 
		$condition .= " AND sourceurl = '".$sourceurl."'";
	if ($tags) {
		$tags = html_entity_decode($tags);
		if(!$tags)
			break;
		$taglist = explode(',', $tags);
		$tags_condition = "";
		foreach($taglist as $tag) {
			$tag = trim($tag);
			if($tags_condition) $tags_condition .= " OR ";
			$tags_condition .= "tags = '{$tag}' OR tags LIKE '{$tag},%' OR tags LIKE '%,{$tag},%' OR tags LIKE '%,{$tag}'";
		}
		if($tags_condition) $condition .= " AND ".$tags_condition;
	}



	if($orderby == 'id' || !$orderby) $orderby = 'pocketarticle_id';
	else if ($orderby == 'date_added') $orderby = 'time_added';
	else if($orderby == 'random' || $orderby == 'rand') {
		$orderby = 'RAND(UNIX_TIMESTAMP(NOW()))';
		$order = '';
		$paging = false;
	};
	$order = strtoupper($order);
	
	if($order && $order != 'DESC')	
		$order = 'ASC';
	
	$condition .= " ORDER BY {$orderby} {$order}";
	
	if($paging == true || $paging == 1) {
	
		$num_pocketarticles = pocketarticlescollection_count($condition);
		
		$total_pages = ceil($num_pocketarticles / $limit_per_page);
		
		if(!isset($_GET['pocketarticles_page']) || !$_GET['pocketarticles_page'] || !is_numeric($_GET['pocketarticles_page']))
			$page = ($page != -1)?$page : 1;
		else
			$page = $_GET['pocketarticles_page'];
		
		if($page > $total_pages) $page = $total_pages;

		$show_paging_bool = ($show_paging == "true");
		if ($show_paging_bool)
		{
			if($page_nav = pocketarticlescollection_pagenav($total_pages, $page, 0, 'pocketarticles_page'))
				$page_nav = '<div class="pocketarticlescollection_pagenav">'.$page_nav.'</div>';
		}	
		$start = ($page - 1) * $limit_per_page;
		
		$condition .= " LIMIT {$start}, {$limit_per_page}"; 

//		return $condition;
		
		if($pocketarticles = pocketarticlescollection_get_pocketarticles($condition))
			return $page_nav.pocketarticlescollection_shortcode_output_format($pocketarticles, $show_source, $show_date).$page_nav;
		else
			return "";
		
	}
	
	else if($limit && is_numeric($limit))
		$condition .= " LIMIT ".$limit;
	
//	return $condition;

	if($pocketarticles = pocketarticlescollection_get_pocketarticles($condition))
		return pocketarticlescollection_shortcode_output_format($pocketarticles, $show_source, $show_date);
	else
		return "";
}

add_shortcode('pocketarticlescollection', 'pocketarticlescollection_shortcodes');
add_shortcode('pocketarticlecoll', 'pocketarticlescollection_shortcodes'); // just in case, somebody misspells the shortcode




/* Backward compatibility for [pocketarticle] */



function pocketarticlescollection_displaypocketarticle($matches)
{
	if(!isset($matches[1]) || (isset($matches[1]) && !$matches[1]) || $matches[0] == "[pocketarticle|random]")
		$atts = array( 'orderby' => 'random', 'limit' => 1 );
	else
		$atts = array (	'id' => $matches[1] );
	
	return pocketarticlescollection_shortcodes($atts);
}


function pocketarticlescollection_displaypocketarticles_title($matches)
{
	return pocketarticlescollection_shortcodes(array('title'=>$matches[1]));
}


function pocketarticlescollection_displaypocketarticles_sourceurl($matches)
{
	return pocketarticlescollection_shortcodes(array('sourceurl'=>$matches[1]));
}

function pocketarticlescollection_displaypocketarticles_tags($matches)
{
	return pocketarticlescollection_shortcodes(array('tags'=>$matches[1]));
}

function pocketarticlescollection_inpost( $text )
{
  $start = strpos($text,"[pocketarticle|id=");
  if ($start !== FALSE) {
    $text = preg_replace_callback( "/\[pocketarticle\|id=(\d+)\]/i", "pocketarticlescollection_displaypocketarticle", $text );
  }
  $start = strpos($text,"[pocketarticle|random]");
  if ($start !== FALSE) {
    $text = preg_replace_callback( "/\[pocketarticle\|random\]/i", "pocketarticlescollection_displaypocketarticle", $text );
  }
  $start = strpos($text,"[pocketarticle|all]");
  if ($start !== FALSE) {
    $text = preg_replace_callback( "/\[pocketarticle\|all\]/i", "pocketarticlescollection_shortcodes", $text );
  }
	$start = strpos($text,"[pocketarticle|title=");
	if($start !== FALSE) {
		$text = preg_replace_callback("/\[pocketarticle\|title=(.{1,})?\]/i", "pocketarticlescollection_displaypocketarticles_title", $text);
	}
	$start = strpos($text,"[pocketarticle|sourceurl=");
	if($start !== FALSE) {
		$text = preg_replace_callback("/\[pocketarticle\|sourceurl=(.{1,})?\]/i", "pocketarticlescollection_displaypocketarticles_sourceurl", $text);
	}
	$start = strpos($text,"[pocketarticle|tags=");
	if($start !== FALSE) {
		$text = preg_replace_callback("/\[pocketarticle\|tags=(.{1,})?\]/i", "pocketarticlescollection_displaypocketarticles_tags", $text);
	}	return $text;
}
add_filter('the_content', 'pocketarticlescollection_inpost', 7);
add_filter('the_excerpt', 'pocketarticlescollection_inpost', 7);

?>
