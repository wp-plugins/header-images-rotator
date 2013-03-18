<?php
/*
Plugin Name: ntp-header-images
Plugin URI: http://nord-tramper.ru
Description: Image rotator for site header
Version: 1.1
Author: Nikita Kiselev
Author URI: http://nord-tramper.ru
License: GPL2
============================================
Copyright 2013  Nikita Kiselev  (email : admin@lifelongjourney.ru)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//=================================================================================
function init_textdomain() {
    if (function_exists('load_plugin_textdomain')) {
        load_plugin_textdomain('ntp-header-images', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/translate');
    }
}

// Add transaltion
add_action('init', 'init_textdomain');

function init_css(){
	// –егистрируем стили дл€ плагина:
	wp_register_style( 'ntp-header-images-style', plugins_url( '/style.css', __FILE__ ), array(), '20130109', 'all' );
	// —тавим стили в очередь как дл€ плагина, так и дл€ темы:
	wp_enqueue_style( 'ntp-header-images-style' );
}
add_action( 'wp_enqueue_scripts', 'init_css' );

/*
	Function return UNIQUE number between 0 and $maxCount-1 
	$selected_images - array with imges already added into header. 
	$maxCount - total imgae count
*/
function getNextImage(&$selected_images,$maxCount){
	$arr_len = count($selected_images);
	do{
		$nextImage = mt_rand(0, $maxCount-1);
	}while ($arr_len != 0 && in_array($nextImage,$selected_images));
	$selected_images[$arr_len] = $nextImage;
	return $nextImage;
}

function getImageLinksArray($count,$mode){
 global $wpdb;
 if ($mode == 'frontend'){
	return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ntp_header_images order by rand() limit 0,$count",'ARRAY_A');
 } else {//backend
	return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."ntp_header_images order by id",'ARRAY_A');
 }
}

function get_header_imageset2(){
	global $wpdb;
	$out = "\n".'<div id="header-image">'."\n";
	$id = '';
	$parameters = ntpLoadOptions();
	
	//get 4 random pictures from db
	//Yes it is worst way to get 4 random rows, but it is. When I will have more rows in the table I use another way like first one
	$image_links = getImageLinksArray($parameters["imageCount"],'frontend');

	//make 4 pictures
	for ($i = 0; $i < $parameters["imageCount"]; $i++){
		if ($i % $parameters["imageCount"] == 0) $id = "id = \"first_pict\""; else $id = "";
		$out .= "<a href=\"".$image_links[$i]['link']."\"><img $id src=\"".$image_links[$i]['url']."\" width=\"".$parameters["imageWidth"]."\" height=\"".$parameters["imageHeight"]."\" title=\"".$image_links[$i]['title']."\"></a>\n";
	}
	echo $out.'</div>';
}
//----------------Admin panel start---------------------------
//Add new menu item into Parameters
function ntpCreateSettingsPage(){
	if (function_exists('add_options_page')){//GPP :) juxt check that our WP has this function
		add_options_page(__('Plugin settings','ntp-header-images'), 'ntpHeaderImages settings', 'manage_options', 'ntp-header-images', 'ntpOptionsPage');
	};
}

//Draw settings pages
function ntpOptionsPage(){
	//===================
	$out = "\n".'<script type="text/javascript"><!--
				function ntpDoSubmit(id){
					//set data
					if (id){
						document.getElementById("id").value = id;
						document.getElementById("image").value = document.getElementsByName("image_url_"+id)[0].value;
						document.getElementById("link").value = document.getElementsByName("image_link_"+id)[0].value;
						document.getElementById("title").value = document.getElementsByName("image_title_"+id)[0].value;
					}
					document.forms["data"].submit();
				}
				
				function ntpDoImport(){
					document.getElementById("action").value = "import";
					document.forms["data"].submit();
				}
			--></script>
			<form id="data" name="data" method="post" action="">
			    <input type="hidden" name="action" id="action" value="edit">
				<input type="hidden" name="id" id="id"  value="">
				<input type="hidden" name="link" id="link" value="">
				<input type="hidden" name="image" id="image" value="">
				<input type="hidden" name="title" id="title" value="">
			</form>
			';
	$out .= '<div class="wrap"><div id="icon-options-general" class="icon32"></div>';//tag with Settings icon
	$out .= '<h2>'.__('Image rotator settings','ntp-header-images').'</h2>';//.$_REQUEST['action'].'-'.$_REQUEST['save'].'-'.$_REQUEST['delete'];
	//look at request and check what we should do
	if ($_REQUEST['action'] == 'edit') {
		$out .= '<h3>'.__('Edit image','ntp-header-images').'</h3>';
		//add image itself
		$out .= '<form name="image_edit" id="image_edit" method="post">';
		$out .= '<img src="'.$_REQUEST['image'].'"><br>';
		$out .= '<table>';
		$out .= '<tr><td>'.__('Link to image','ntp-header-images').':</td><td><input type="text" size="100" name="image_url"   id="image_url" value="'.$_REQUEST['image'].'"></td></tr>';
		$out .= '<tr><td>'.__('Link to post','ntp-header-images').':</td><td><input type="text" size="100"  name="image_link"  id="image_link" value="'.$_REQUEST['link'].'"></td></tr>';
		$out .= '<tr><td>'.__('Image title','ntp-header-images').':</td><td><input type="text" size="100"   name="image_title" id="image_title" value="'.$_REQUEST['title'].'"></td></tr>';
		$out .= '</table>';
		$out .= '<input type="hidden" name="id" name="id" value="'.$_REQUEST['id'].'">';
		$out .= '<input type="hidden" name="action" name="action" value="save">';
		$out .= '<input type="submit" name="save" value="'.__('Save','ntp-header-images').'">';
		if($_REQUEST['id']){$out .= '<input type="submit" name="delete" value="'.__('Delete','ntp-header-images').'">';}
		$out .= '</form>';	
		//.$_REQUEST['id']."-".$_REQUEST['link']."-".$_REQUEST['image'];
	}
	elseif($_REQUEST['action'] == 'save' or $_REQUEST['action'] == 'import' or $_REQUEST['action'] == null){
		//check if we need make some save actions
		if ($_REQUEST['action'] == 'save'){//save parameters for image with id = $_REQUEST['id']
			if ($_REQUEST['save']){
				ntpDoSave($_REQUEST['id'],'save',$_REQUEST['image_url'],$_REQUEST['image_link'],$_REQUEST['image_title']);
			}
			if ($_REQUEST['delete']){//drop image with id = $_REQUEST['id']
				ntpDoSave($_REQUEST['id'],'delete');
			}
			if ($_REQUEST['saveSettings']){//save plugin settings
				ntpDoSaveOptions($_REQUEST['imageWidth'],$_REQUEST['imageHeight'],$_REQUEST['imageCount']);
			}
		} 
		elseif ($_REQUEST['action'] == 'import'){
			//import all posts with preview
			ntpDoImport();
		}
	
		//Load plugin options
		$parameters = ntpLoadOptions();

		//Draw main settings page
		$out .= '<div style="width:100%;">';
		$out .= '<div style="width:200px; float:left">';
		$out .= '<form name="dispalySettings" id="displaySettings" method="post">';
		$out .= '<h3>'.__('Display settings','ntp-header-images').'</h3>';

		$out .= '<table>';
		$out .= '<tr><td>'.__('Images width','ntp-header-images').':</td><td><input size="5" type="text" name="imageWidth" id="imageWidth" value="'.$parameters["imageWidth"].'"></td></tr>';
		$out .= '<tr><td>'.__('Images height','ntp-header-images').':</td><td><input size="5" type="text" name="imageHeight" id="imageHeight" value="'.$parameters["imageHeight"].'"></td></tr>';
		$out .= '<tr><td>'.__('Images count','ntp-header-images').':</td><td><input size="5" type="text" name="imageCount" id="imageCount" value="'.$parameters["imageCount"].'"></td></tr>';		
		$out .= '</table>';

		$out .= '<input type="hidden" name="action" id="action" value="save">';	
		$out .= '<input type="submit" name="saveSettings" id="saveSettings" value="'.__('Save','ntp-header-images').'">';
		$out .= '</form>';
		$out .= '</div>';
        
		$out .= ntpShowDonation();
		$out .= '</div>';
		$out .= '<h3>'.__('Images','ntp-header-images').'</h3>';
		$out .= '<input type="button" name="add" id="add" value="'.__('Add new','ntp-header-images').'" onclick="ntpDoSubmit()">';
		$out .= '<input type="button" name="import" id="import" value="'.__('Import from posts','ntp-header-images').'" onclick="ntpDoImport()"><br>';
		$out .= '<h4>'.__('Images in roatator','ntp-header-images').'</h4>';
		$out .= '<p><i>'.__('Click to any picture to edit or delete','ntp-header-images').'</i></p>';
		//show all images and links
		$out .= ntpGetAllImages($parameters);
	}
	else{//default action
		$out .= '<h3>FAIL!</h3>';
	}
	$out .= '</div>';//the last tag
	
	echo $out;
}

function ntpGetAllImages($parameters){
	$out = "";
	$image_links = getImageLinksArray(null,'backend');

	//make all pictures
	for ($i = 0; $i < count($image_links); $i++){
		$id = $image_links[$i]['id'];
		$out .= "<img src=\"".$image_links[$i]['url']."\" title=\"".$image_links[$i]['title']."\" onclick=\"ntpDoSubmit(".$id.")\" width=\"".$parameters["imageWidth"]."\" height=\"".$parameters["imageHeight"]."\"><input name=\"image_link_".$id ."\"  type=\"hidden\" value=\"".$image_links[$i]['link']."\"><input name=\"image_title_".$id ."\"  type=\"hidden\" value=\"".$image_links[$i]['title']."\"><input name=\"image_url_".$id ."\"  type=\"hidden\" value=\"".$image_links[$i]['url']."\">\n";
	}//*/	<a href=\"".$image_links[$i]['link']."\">
	return $out;
}

function ntpClearInput($str){
	$out = $str;
	$out = strip_tags($out);
	$out = htmlspecialchars($out);
	$out = preg_replace('/\s+/', ' ', $out);
	return $out;
}

function ntpDoSave($id,$action,$img_url,$img_link,$img_title){
	global $wpdb;
	$sql = "";
	if($id){
		if  ($action == 'delete'){//delete image
			$sql = "DELETE FROM ".$wpdb->prefix."ntp_header_images where id = ".$id;
		}
		elseif ($action == 'save'){//save new parameters for image
			$sql = "UPDATE ".$wpdb->prefix."ntp_header_images SET url='".ntpClearInput($img_url)."', link = '".ntpClearInput($img_link)."', title = '".ntpClearInput($img_title)."' WHERE id = ".$id;
		}
	}
	else{//add new image
		$sql = "INSERT INTO ".$wpdb->prefix."ntp_header_images (url,link,title) VALUES ('".ntpClearInput($img_url)."','".ntpClearInput($img_link)."','".ntpClearInput($img_title)."')";
	}
	//execute query
	$wpdb->query($sql);
}

//Import images from posts
function ntpDoImport(){
	global $wpdb;
	$sql = "insert into ".$wpdb->prefix."ntp_header_images (url,link,title)
			SELECT *
			FROM (
				SELECT p2.guid url, p1.guid link,p1.post_title
				FROM ".$wpdb->prefix."postmeta pm,
					 ".$wpdb->prefix."posts p1,
					 ".$wpdb->prefix."posts p2
				where pm.meta_key = '_thumbnail_id'
				 and pm.post_id = p1.id
				 and pm.meta_value = p2.id
			) new_
			WHERE (new_.url, new_.link) NOT IN ( SELECT url, link FROM wpmain_ntp_header_images	)
	";
	$wpdb->query($sql);	
}

//Load options from option table
function ntpLoadOptions(){
 	$out["imageWidth"]  = get_option("ntpHeaderImagesWidth");
	if (!$out["imageWidth"]){add_option("ntpHeaderImagesWidth", "275");}
	$out["imageHeight"]  = get_option("ntpHeaderImagesHeight");
	if (!$out["imageHeight"]){add_option("ntpHeaderImagesHeight", "182");}
	$out["imageCount"]  = get_option("ntpHeaderImagesCount");
	if (!$out["imageCount"]){add_option("ntpHeaderImagesCount", "4");}
	
	return $out;
}

//Save new options or create it with default values
function ntpDoSaveOptions($width, $height, $cnt){
	global $wpdb;
	update_option('ntpHeaderImagesWidth',$width);
	update_option('ntpHeaderImagesHeight',$height);
	update_option('ntpHeaderImagesCount',$cnt);
	//echo "settings was saved!!!!!".$height;
}

//Draw Donation button
function ntpShowDonation(){
	$out  = '<div style="height:140px; text-align:right">';
	$out .= 'You can help me to make this plugin better:';
	$out .= '
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_donations">
				<input type="hidden" name="business" value="nord.tramper@gmail.com">
				<input type="hidden" name="lc" value="US">
				<input type="hidden" name="item_name" value="nord-tramper.ru">
				<input type="hidden" name="no_note" value="0">
				<input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>
	';
	$out .= '</div>';
	return $out;
}

//Admin panel end

//Create table for plugin
function ntpCreateTable(){
	global $wpdb;
	$bTableExists;
	$bAltNotExists;
	//check if table already exists
	$bTableExists = ($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."ntp_header_images'") == $wpdb->prefix."ntp_header_images");
	$bAltNotExists = $wpdb->get_results("select title from ".$wpdb->prefix."ntp_header_images where 0");
	if (!$bTableExists){
		//create new table
		$sql = '
				CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'ntp_header_images (
				id tinyint(3) NOT NULL AUTO_INCREMENT,
				url varchar(255) DEFAULT NULL,
				link varchar(255) DEFAULT NULL,
				title varchar(255) DEFAULT NULL,
				PRIMARY KEY (`id`)
				) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		';
	}
	
	if (!$bAltNotExists && $bTableExists){
		//update existing if needed
		$sql = '
			alter table '.$wpdb->prefix.'ntp_header_images add title varchar(255);
		';
	}
	
	//execute query if it is not empty
	if ($sql) {
		$wpdb->query($sql); 
	};
	
}
register_activation_hook(__FILE__,'ntpCreateTable');//install plugin

//Uninstall function
function ntpHMdeinstall() {
	global $wpdb;
	delete_option('ntpHeaderImagesWidth',$width);
	delete_option('ntpHeaderImagesHeight',$height);
	delete_option('ntpHeaderImagesCount',$cnt);
	$wbdb->query('drop table '.$wpdb->prefix.'ntp_header_images');
}

if (function_exists('register_uninstall_hook')) {
  register_uninstall_hook(__FILE__, 'ntpHMdeinstall');
}
 
add_action('admin_menu', 'ntpCreateSettingsPage');//Add new menu item into Prameters menu

?>