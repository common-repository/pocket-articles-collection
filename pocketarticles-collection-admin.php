<?php

function pocketarticlescollection_admin_menu() 
{
	global $pocketarticlescollection_admin_userlevel;
	add_object_page('Pocket Articles', 'Pocket', $pocketarticlescollection_admin_userlevel, 'pocketarticles-collection', 'pocketarticlescollection_pocketarticles_management');
}
add_action('admin_menu', 'pocketarticlescollection_admin_menu');



function pocketarticlescollection_addpocketarticle($pocketarticle, $title = "", $sourceurl = "", $tags = "", $public = 'yes')
{
	if(!$pocketarticle) return __('Nothing added to the database.', 'pocketarticles-collection');
	global $wpdb;
	$table_name = $wpdb->prefix . "pocketarticlescollection";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 
		return __('Database table not found', 'pocketarticles-collection');
	else //Add the pocket article data to the database
	{
		global $allowedposttags;
		$pocketarticle = wp_kses( stripslashes($pocketarticle), $allowedposttags );
		$title = wp_kses( stripslashes($title), array( 'a' => array( 'href' => array(),'title' => array() ) ) ) ;	
		$sourceurl = wp_kses( stripslashes($sourceurl), array( 'a' => array( 'href' => array(),'title' => array() ) ) ) ;	
		$tags = strip_tags( stripslashes($tags) );
		
		$pocketarticle = "'".$wpdb->escape($pocketarticle)."'";
		$title = $title?"'".$wpdb->escape($title)."'":"NULL";
		$sourceurl = $sourceurl?"'".$wpdb->escape($sourceurl)."'":"NULL";
		$tags = explode(',', $tags);
		foreach ($tags as $key => $tag)
			$tags[$key] = trim($tag);
		$tags = implode(',', $tags);
		$tags = $tags?"'".$wpdb->escape($tags)."'":"NULL";
		if(!$public) $public = "'no'";
		else $public = "'yes'";
		$insert = "INSERT INTO " . $table_name .
			"(pocketarticle, title, sourceurl, tags, public, time_added)" .
			"VALUES ({$pocketarticle}, {$title}, {$sourceurl}, {$tags}, {$public}, NOW())";
		$results = $wpdb->query( $insert );
		if(FALSE === $results)
			return __('There was an error in the MySQL query', 'pocketarticles-collection');
		else
			return __('pocketarticle added', 'pocketarticles-collection');
   }
}

function pocketarticlescollection_editpocketarticle($pocketarticle_id, $pocketarticle, $title = "", $sourceurl = "", $tags = "", $public = 'yes')
{
	if(!$pocketarticle) return __('Pocket Article not updated.', 'pocketarticles-collection');
	if(!$pocketarticle_id) return srgq_addpocketarticle($pocketarticle, $title, $sourceurl, $public);
	global $wpdb;
	$table_name = $wpdb->prefix . "pocketarticlescollection";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 
		return __('Database table not found', 'pocketarticles-collection');
	else //Update database
	{
		global $allowedposttags;
		$pocketarticle = wp_kses( stripslashes($pocketarticle), $allowedposttags );
		$title = wp_kses( stripslashes($title), array( 'a' => array( 'href' => array(),'title' => array() ) ) ) ;	
		$sourceurl = wp_kses( stripslashes($sourceurl), array( 'a' => array( 'href' => array(),'title' => array() ) ) ) ;	
		$tags = strip_tags( stripslashes($tags) );

	  	$pocketarticle = "'".$wpdb->escape($pocketarticle)."'";
		$title = $title?"'".$wpdb->escape($title)."'":"NULL";
		$sourceurl = $sourceurl?"'".$wpdb->escape($sourceurl)."'":"NULL";
		$tags = explode(',', $tags);
		foreach ($tags as $key => $tag)
			$tags[$key] = trim($tag);
		$tags = implode(',', $tags);
		$tags = $tags?"'".$wpdb->escape($tags)."'":"NULL";
		if(!$public) $public = "'no'";
		else $public = "'yes'";
		$update = "UPDATE " . $table_name . "
			SET pocketarticle = {$pocketarticle},
				title = {$title},
				sourceurl = {$sourceurl}, 
				tags = {$tags},
				public = {$public}, 
				time_updated = NOW()
			WHERE pocketarticle_id = $pocketarticle_id";
		$results = $wpdb->query( $update );
		if(FALSE === $results)
			return __('There was an error in the MySQL query', 'pocketarticles-collection');		
		else
			return __('Changes saved', 'pocketarticles-collection');
   }
}


function pocketarticlescollection_deletepocketarticle($pocketarticle_id)
{
	if($pocketarticle_id) {
		global $wpdb;
		$sql = "DELETE from " . $wpdb->prefix ."pocketarticlescollection" .
			" WHERE pocketarticle_id = " . $pocketarticle_id;
		if(FALSE === $wpdb->query($sql))
			return __('There was an error in the MySQL query', 'pocketarticles-collection');		
		else
			return __('Pocket article deleted', 'pocketarticles-collection');
	}
	else return __('The Pocket article cannot be deleted', 'pocketarticles-collection');
}

function pocketarticlescollection_getpocketarticledata($pocketarticle_id)
{
	global $wpdb;
	$sql = "SELECT pocketarticle_id, pocketarticle, title, sourceurl, tags, public
		FROM " . $wpdb->prefix . "pocketarticlescollection 
		WHERE pocketarticle_id = {$pocketarticle_id}";
	$pocketarticle_data = $wpdb->get_row($sql, ARRAY_A);	
	return $pocketarticle_data;
}

function pocketarticlescollection_does_pocketarticle_exist($source_url)
{
	global $wpdb;
	$sql = "SELECT COUNT(*) FROM " . $wpdb->prefix . "pocketarticlescollection WHERE sourceurl = '" . $wpdb->escape(wp_kses( stripslashes($source_url), array( 'a' => array( 'href' => array(),'title' => array() ) ) ) ) . "'";
	$count = $wpdb->get_var($sql);

	if($count == 0){ return false; }else{ return true; }	
}

function pocketarticlescollection_editform($pocketarticle_id = 0)
{
	$public_selected = " checked=\"checked\"";
	$submit_value = __('Add Pocket article', 'pocketarticles-collection');
	$form_name = "addpocketarticle";
	$action_url = get_bloginfo('wpurl')."/wp-admin/admin.php?page=pocketarticles-collection#addnew";
	$pocketarticle = $title = $sourceurl = $tags = $hidden_input = $back = "";

	if($pocketarticle_id) {
		$form_name = "editpocketarticle";
		$pocketarticle_data = pocketarticlescollection_getpocketarticledata($pocketarticle_id);
		foreach($pocketarticle_data as $key => $value)
			$pocketarticle_data[$key] = $pocketarticle_data[$key];
		extract($pocketarticle_data);
		$pocketarticle = htmlspecialchars($pocketarticle);
		$title = htmlspecialchars($title);
		$sourceurl = htmlspecialchars($sourceurl);
		$tags = implode(', ', explode(',', $tags));
		$hidden_input = "<input type=\"hidden\" name=\"pocketarticle_id\" value=\"{$pocketarticle_id}\" />";
		if($public == 'no') $public_selected = "";
		$submit_value = __('Save changes', 'pocketarticles-collection');
		$back = "<input type=\"submit\" name=\"submit\" value=\"".__('Back', 'pocketarticles-collection')."\" />&nbsp;";
		$action_url = get_bloginfo('wpurl')."/wp-admin/admin.php?page=pocketarticles-collection";
	}

	$pocketarticle_label = __('The Pocket article', 'pocketarticles-collection');
	$title_label = __('Title', 'pocketarticles-collection');
	$sourceurl_label = __('Source url', 'pocketarticles-collection');
	$tags_label = __('Tags', 'pocketarticles-collection');
	$public_label = __('Public?', 'pocketarticles-collection');
	$optional_text = __('optional', 'pocketarticles-collection');
	$comma_separated_text = __('comma separated', 'pocketarticles-collection');
	

	$display =<<< EDITFORM
<form name="{$form_name}" method="post" action="{$action_url}">
	{$hidden_input}
	<table class="form-table" cellpadding="5" cellspacing="2" width="100%">
		<tbody><tr class="form-field form-required">
			<th style="text-align:left;" scope="row" valign="top"><label for="pocketarticlescollection_pocketarticle">{$pocketarticle_label}</label></th>
			<td><textarea id="pocketarticlescollection_pocketarticle" name="pocketarticle" rows="5" cols="50" style="width: 97%;">{$pocketarticle}</textarea></td>
		</tr>
		<tr class="form-field">
			<th style="text-align:left;" scope="row" valign="top"><label for="pocketarticlescollection_title">{$title_label}</label></th>
			<td><input type="text" id="pocketarticlescollection_title" name="title" size="40" value="{$title}" /><br />{$optional_text}</td>
		</tr>
		<tr class="form-field">
			<th style="text-align:left;" scope="row" valign="top"><label for="pocketarticlescollection_sourceurl">{$sourceurl_label}</label></th>
			<td><input type="text" id="pocketarticlescollection_sourceurl" name="sourceurl" size="40" value="{$sourceurl}" /><br />{$optional_text}</td>
		</tr>
		<tr class="form-field">
			<th style="text-align:left;" scope="row" valign="top"><label for="pocketarticlescollection_tags">{$tags_label}</label></th>
			<td><input type="text" id="pocketarticlescollection_tags" name="tags" size="40" value="{$tags}" /><br />{$optional_text}, {$comma_separated_text}</small></td>
		</tr>
		<tr>
			<th style="text-align:left;" scope="row" valign="top"><label for="pocketarticlescollection_public">{$public_label}</label></th>
			<td><input type="checkbox" id="pocketarticlescollection_public" name="public"{$public_selected} />
		</tr></tbody>
	</table>
	<p class="submit">{$back}<input name="submit" value="{$submit_value}" type="submit" class="button button-primary" /></p>
</form>
EDITFORM;
	return $display;
}

function pocketarticlescollection_changevisibility($pocketarticle_ids, $public = 'yes')
{
	if(!$pocketarticle_ids)
		return __('Nothing done!', 'pocketarticles-collection');
	global $wpdb;
	$sql = "UPDATE ".$wpdb->prefix."pocketarticlescollection 
		SET public = '".$public."',
			time_updated = NOW()
		WHERE pocketarticle_id IN (".implode(', ', $pocketarticle_ids).")";
	$wpdb->query($sql);
	if($public == 'yes')
		return __("Selected Pocket articles made public", 'pocketarticles-collection');
	else
		return __("Selected Pocket articles made private", 'pocketarticles-collection');
}

function pocketarticlescollection_bulkdelete($pocketarticle_ids)
{
	if(!$pocketarticle_ids)
		return __('Nothing done!', 'pocketarticles-collection');
	global $wpdb;
	$sql = "DELETE FROM ".$wpdb->prefix."pocketarticlescollection 
		WHERE pocketarticle_id IN (".implode(', ', $pocketarticle_ids).")";
	$wpdb->query($sql);
	return __('pocketarticle(s) deleted', 'pocketarticles-collection');
}



function pocketarticlescollection_pocketarticles_management()
{	
	global $pocketarticlescollection_db_version;
	$options = get_option('pocketarticlescollection');
	$display = $msg = $pocketarticles_list = $alternate = "";
	
	if($options['db_version'] != $pocketarticlescollection_db_version )
		pocketarticlescollection_install();
		
	if(isset($_REQUEST['submit'])) {
		if($_REQUEST['submit'] == __('Add Pocket article', 'pocketarticles-collection')) {
			extract($_REQUEST);
			$msg = pocketarticlescollection_addpocketarticle($pocketarticle, $title, $sourceurl, $tags, $public);
		}
		else if($_REQUEST['submit'] == __('Save changes', 'pocketarticles-collection')) {
			extract($_REQUEST);
			$msg = pocketarticlescollection_editpocketarticle($pocketarticle_id, $pocketarticle, $title, $sourceurl, $tags, $public);
		}
	}
	else if(isset($_REQUEST['action'])) {
		if($_REQUEST['action'] == 'editpocketarticle') {
			$display .= "<div class=\"wrap\">\n<h2>Pocket Articles Collection &raquo; ".__('Edit Pocket article', 'pocketarticles-collection')."</h2>";
			$display .=  pocketarticlescollection_editform($_REQUEST['id']);
			$display .= "</div>";
			echo $display;
			return;
		}
		else if($_REQUEST['action'] == 'delpocketarticle') {
			$msg = pocketarticlescollection_deletepocketarticle($_REQUEST['id']);
		}
	}
	else if(isset($_REQUEST['bulkactionsubmit']))  {
		if($_REQUEST['bulkaction'] == 'delete') 
			$msg = pocketarticlescollection_bulkdelete($_REQUEST['bulkcheck']);
		if($_REQUEST['bulkaction'] == 'make_public') {
			$msg = pocketarticlescollection_changevisibility($_REQUEST['bulkcheck'], 'yes');
		}
		if($_REQUEST['bulkaction'] == 'keep_private') {
			$msg = pocketarticlescollection_changevisibility($_REQUEST['bulkcheck'], 'no');
		}
	}
	
	
	$display .= "<div class=\"wrap\">";
	
	if($msg)
		$display .= "<div id=\"message\" class=\"updated fade\"><p>{$msg}</p></div>";

	$display .= "<h2>Pocket Articles Collection <a href=\"#addnew\" class=\"add-new-h2\">".__('Add new Pocket article', 'pocketarticles-collection')."</a></h2>";

	$num_pocketarticles = pocketarticlescollection_count();
	
	if(!$num_pocketarticles) {
		$display .= "<p>".__('No Pocket articles in the database', 'pocketarticles-collection')."</p>";

		$display .= "</div>";
	
		$display .= "<div id=\"addnew\" class=\"wrap\">\n<h2>".__('Add new Pocket article', 'pocketarticles-collection')."</h2>";
		$display .= pocketarticlescollection_editform();
		$display .= "</div>";

		echo $display;
		return;
	}

	global $wpdb;

	$sql = "SELECT pocketarticle_id, pocketarticle, title, sourceurl, tags, public
		FROM " . $wpdb->prefix . "pocketarticlescollection";
		
	$option_selected = array (
		'pocketarticle_id' => '',
		'pocketarticle' => '',
		'title' => '',
		'sourceurl' => '',
		'time_added' => '',
		'time_updated' => '',
		'public' => '',
		'ASC' => '',
		'DESC' => '',
	);
	if(isset($_REQUEST['orderby'])) {
		$sql .= " ORDER BY " . $_REQUEST['orderby'] . " " . $_REQUEST['order'];
		$option_selected[$_REQUEST['orderby']] = " selected=\"selected\"";
		$option_selected[$_REQUEST['order']] = " selected=\"selected\"";
	}
	else {
		$sql .= " ORDER BY pocketarticle_id DESC";
		$option_selected['pocketarticle_id'] = " selected=\"selected\"";
		$option_selected['DESC'] = " selected=\"selected\"";
	}
	
	if(isset($_REQUEST['paged']) && $_REQUEST['paged'] && is_numeric($_REQUEST['paged']))
		$paged = $_REQUEST['paged'];
	else
		$paged = 1;

	$limit_per_page = 20;
		
	
	
	$total_pages = ceil($num_pocketarticles / $limit_per_page);
	
	
	if($paged > $total_pages) $paged = $total_pages;

	$admin_url = get_bloginfo('wpurl'). "/wp-admin/admin.php?page=pocketarticles-collection";
	if(isset($_REQUEST['orderby']))
		$admin_url .= "&orderby=".$_REQUEST['orderby']."&order=".$_REQUEST['order'];
	
	$page_nav = pocketarticlescollection_pagenav($total_pages, $paged, 2, 'paged', $admin_url);
	
	$start = ($paged - 1) * $limit_per_page;
		
	$sql .= " LIMIT {$start}, {$limit_per_page}"; 

	// Get all the pocketarticles from the database
	$pocketarticles = $wpdb->get_results($sql);
	
	foreach($pocketarticles as $pocketarticle_data) {
		if($alternate) $alternate = "";
		else $alternate = " class=\"alternate\"";
		$pocketarticles_list .= "<tr{$alternate}>";
		$pocketarticles_list .= "<th scope=\"row\" class=\"check-column\"><input type=\"checkbox\" name=\"bulkcheck[]\" value=\"".$pocketarticle_data->pocketarticle_id."\" /></th>";
		$pocketarticles_list .= "<td>" . $pocketarticle_data->pocketarticle_id . "</td>";
		$pocketarticles_list .= "<td>";
		$pocketarticles_list .= wptexturize(nl2br(make_clickable($pocketarticle_data->pocketarticle)));
    	$pocketarticles_list .= "<div class=\"row-actions\"><span class=\"edit\"><a href=\"{$admin_url}&action=editpocketarticle&amp;id=".$pocketarticle_data->pocketarticle_id."\" class=\"edit\">".__('Edit', 'pocketarticles-collection')."</a></span> | <span class=\"trash\"><a href=\"{$admin_url}&action=delpocketarticle&amp;id=".$pocketarticle_data->pocketarticle_id."\" onclick=\"return confirm( '".__('Are you sure you want to delete this Pocket article?', 'pocketarticles-collection')."');\" class=\"delete\">".__('Delete', 'pocketarticles-collection')."</a></span></div>";
		$pocketarticles_list .= "</td>";
		$pocketarticles_list .= "<td>" . make_clickable($pocketarticle_data->title);
		if($pocketarticle_data->title && $pocketarticle_data->sourceurl)
			$pocketarticles_list .= " / ";
		$pocketarticles_list .= make_clickable($pocketarticle_data->sourceurl) ."</td>";
		$pocketarticles_list .= "<td>" . implode(', ', explode(',', $pocketarticle_data->tags)) . "</td>";
		if($pocketarticle_data->public == 'no') $public = __('No', 'pocketarticles-collection');
		else $public = __('Yes', 'pocketarticles-collection');
		$pocketarticles_list .= "<td>" . $public  ."</td>";
		$pocketarticles_list .= "</tr>";
	}
	
	if($pocketarticles_list) {
		$pocketarticles_count = pocketarticlescollection_count();

		$display .= "<form id=\"pocketarticlescollection\" method=\"post\" action=\"".get_bloginfo('wpurl')."/wp-admin/admin.php?page=pocketarticles-collection\">";
		$display .= "<div class=\"tablenav\">";
		$display .= "<div class=\"alignleft actions\">";
		$display .= "<select name=\"bulkaction\">";
		$display .= 	"<option value=\"0\">".__('Bulk Actions')."</option>";
		$display .= 	"<option value=\"delete\">".__('Delete', 'pocketarticles-collection')."</option>";
		$display .= 	"<option value=\"make_public\">".__('Make public', 'pocketarticles-collection')."</option>";
		$display .= 	"<option value=\"keep_private\">".__('Keep private', 'pocketarticles-collection')."</option>";
		$display .= "</select>";	
		$display .= "<input type=\"submit\" name=\"bulkactionsubmit\" value=\"".__('Apply', 'pocketarticles-collection')."\" class=\"button-secondary\" />";
		$display .= "&nbsp;&nbsp;&nbsp;";
		$display .= __('Sort by: ', 'pocketarticles-collection');
		$display .= "<select name=\"orderby\">";
		$display .= "<option value=\"pocketarticle_id\"{$option_selected['pocketarticle_id']}>".__('Pocket article', 'pocketarticles-collection')." ID</option>";
		$display .= "<option value=\"pocketarticle\"{$option_selected['pocketarticle']}>".__('Pocket article', 'pocketarticles-collection')."</option>";
		$display .= "<option value=\"title\"{$option_selected['title']}>".__('title', 'pocketarticles-collection')."</option>";
		$display .= "<option value=\"sourceurl\"{$option_selected['sourceurl']}>".__('sourceurl', 'pocketarticles-collection')."</option>";
		$display .= "<option value=\"time_added\"{$option_selected['time_added']}>".__('Date added', 'pocketarticles-collection')."</option>";
		$display .= "<option value=\"time_updated\"{$option_selected['time_updated']}>".__('Date updated', 'pocketarticles-collection')."</option>";
		$display .= "<option value=\"public\"{$option_selected['public']}>".__('Visibility', 'pocketarticles-collection')."</option>";
		$display .= "</select>";
		$display .= "<select name=\"order\"><option{$option_selected['ASC']}>ASC</option><option{$option_selected['DESC']}>DESC</option></select>";
		$display .= "<input type=\"submit\" name=\"orderbysubmit\" value=\"".__('Go', 'pocketarticles-collection')."\" class=\"button-secondary\" />";
		$display .= "</div>";
		$display .= '<div class="tablenav-pages"><span class="displaying-num">'.sprintf(_n('%d pocketarticle', '%d pocketarticles', $pocketarticles_count, 'pocketarticles-collection'), $pocketarticles_count).'</span><span class="pagination-links">'. $page_nav. "</span></div>";
		$display .= "<div class=\"clear\"></div>";	
		$display .= "</div>";
		

		
		$display .= "<table class=\"widefat\">";
		$display .= "<thead><tr>
			<th class=\"check-column\"><input type=\"checkbox\" onclick=\"pocketarticlescollection_checkAll(document.getElementById('pocketarticlescollection'));\" /></th>
			<th>ID</th><th>".__('The pocketarticle', 'pocketarticles-collection')."</th>
			<th>
				".__('title', 'pocketarticles-collection')." / ".__('sourceurl', 'pocketarticles-collection')."
			</th>
			<th>".__('Tags', 'pocketarticles-collection')."</th>
			<th>".__('Public?', 'pocketarticles-collection')."</th>
		</tr></thead>";
		$display .= "<tbody id=\"the-list\">{$pocketarticles_list}</tbody>";
		$display .= "</table>";

		$display .= "<div class=\"tablenav\">";
		$display .= '<div class="tablenav-pages"><span class="displaying-num">'.sprintf(_n('%d pocketarticle', '%d pocketarticles', $pocketarticles_count, 'pocketarticles-collection'), $pocketarticles_count).'</span><span class="pagination-links">'. $page_nav. "</span></div>";
		$display .= "<div class=\"clear\"></div>";	
		$display .= "</div>";

		$display .= "</form>";
		$display .= "<br style=\"clear:both;\" />";

	}
	else
		$display .= "<p>".__('No Pocket articles in the database', 'pocketarticles-collection')."</p>";



	$display .= "</div>";
	
	$display .= "<div id=\"addnew\" class=\"wrap\">\n<h2>".__('Add new Pocket article', 'pocketarticles-collection')."</h2>";
	$display .= pocketarticlescollection_editform();
	$display .= "</div>";
	

	echo $display;

}


function pocketarticlescollection_admin_footer()
{
	?>
<script type="text/javascript">
function pocketarticlescollection_checkAll(form) {
	for (i = 0, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox" && !(form.elements[i].hasAttribute('onclick'))) {
				if(form.elements[i].checked == true)
					form.elements[i].checked = false;
				else
					form.elements[i].checked = true;
		}
	}
}
</script>

	<?php
}

add_action('admin_footer', 'pocketarticlescollection_admin_footer');

?>
