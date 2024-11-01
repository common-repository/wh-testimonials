<?php
/*
Plugin Name: WH-Testimonials
Plugin URI: http://www.webhostings.in/
Description: This allows you display your testimonials in a random block and/or all on one page.  Widget included.  Optional link in sidebar block to "view all" testimonials on a page.  Requires WordPress 2.7 or higher.
Version: 3.0.0
Author: webhostings.in
Author URI: http://www.webhostings.in/
License: GPL

WH-Testimonials - displays testimonials in WordPress
Version 3.0.0
Copyright (C) 2011 webhostings.in
Released 2012-01-02
Contact Web Hosting at http://www.webhostings.in/

*/

// +---------------------------------------------------------------------------+
// | WP hooks                                                                  |
// +---------------------------------------------------------------------------+

/* WP actions */

register_activation_hook( __FILE__, 'Wh_install' );
register_deactivation_hook( __FILE__, 'Wh_deactivate' );
add_action('admin_menu', 'Wh_addtestimonials');
add_action( 'admin_init', 'register_Wh_options' );
add_action('init', 'Wh_addcss');
add_action('plugins_loaded', 'Wh_Set');
add_shortcode('Wh-testimonials', 'Wh_showall');
add_shortcode('Wh-testimonials_add', 'Wh_newform_front');


function register_Wh_options() { // whitelist options
  register_setting( 'Wh-option-group', 'Wh_showlink' );
  register_setting( 'Wh-option-group', 'Wh_linktext' );
  register_setting( 'Wh-option-group', 'Wh_linkurl' );
  register_setting( 'Wh-option-group', 'Wh_deldata' );
  register_setting( 'Wh-option-group', 'Wh_setlimit' );
  register_setting( 'Wh-option-group', 'Wh_admng' );
  register_setting( 'Wh-option-group', 'Wh_imgalign' );
  register_setting('Wh-option-group','Wh_layout');
  register_setting('Wh-option-group','Wh_copyrights');
  register_setting('Wh-option-group','Wh_form');

}

function unregister_Wh_options() { // unset options
  unregister_setting( 'Wh-option-group', 'Wh_showlink' );
  unregister_setting( 'Wh-option-group', 'Wh_linktext' );
  unregister_setting( 'Wh-option-group', 'Wh_linkurl' );
  unregister_setting( 'Wh-option-group', 'Wh_deldata' );
  unregister_setting( 'Wh-option-group', 'Wh_setlimit' );
  unregister_setting( 'Wh-option-group', 'Wh_admng' );
  unregister_setting( 'Wh-option-group', 'Wh_imgalign' );
  unregister_setting('Wh-option-group','Wh_layout');
  unregister_setting('Wh-option-group','Wh_copyrights');
  unregister_setting('Wh-option-group','Wh_form');

}


function Wh_addcss() { // include style sheet
  	  wp_enqueue_style('Wh_css', '/' . PLUGINDIR . '/wh-testimonials/css/wh-testimonials-style.css' );
  	  wp_enqueue_script('Wh_css', '/' . PLUGINDIR . '/wh-testimonials/css/shadowbox.js' );
  	  wp_enqueue_style('Wh_css', '/' . PLUGINDIR . '/wh-testimonials/css/shadowbox.css' );
}

// +---------------------------------------------------------------------------+
// | Create admin links                                                        |
// +---------------------------------------------------------------------------+

function Wh_addtestimonials() {

	if (get_option('Wh_admng') == '') { $Wh_admng = 'update_plugins'; } else {$Wh_admng = get_option('Wh_admng'); }

// Create top-level menu and appropriate sub-level menus:
	add_menu_page('Wh_Testimonials', 'Testimonials', $Wh_admng, 'Wh_manage', 'Wh_adminpage', plugins_url('/wh-testimonials/Wh_icon.png'));
	add_submenu_page('Wh_manage', 'Settings', 'Settings', $Wh_admng, 'Wh_config', 'Wh_options_page');
}

// +---------------------------------------------------------------------------+
// | Create table on activation                                                |
// +---------------------------------------------------------------------------+

function Wh_install () {

   global $wpdb;

   $table_name = $wpdb->prefix . "wh_testimonials";

//create table

	   $sql = "CREATE TABLE IF NOT EXISTS " . $table_name . "(
		wh_id int( 15 ) NOT NULL AUTO_INCREMENT ,
		wh_text_short text,
		wh_text_full text,
		wh_clientname text,
		wh_company text,
		wh_homepage text,
		wh_sfimgurl text,
		PRIMARY KEY ( `wh_id` )
		) ";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	  add_option("Wh_version", "3.0.0");
	  $txt_short = "Thank you for installing the WH-Testimonials plugin.";
	  $txt_long = "Thank you for installing the WH-Testimonials plugin.  You can manage the testimonials through the admin area under the Testimonials tab.";
      $insert = "INSERT INTO " . $table_name .
            " (wh_text_short,wh_text_full,wh_clientname,wh_company,wh_homepage) " .
            "VALUES ('$txt_short','$txt_long','Web Hosting','webhostings','http://www.webhostings.in/')";
      $results = $wpdb->query( $insert );

	// insert default settings into wp_options
	$toptions = $wpdb->prefix ."options";
	$insert = "INSERT INTO ".$toptions.
		"(option_name, option_value) " .
		"VALUES ('Wh_admng', 'update_plugins'),('Wh_deldata', ''),".
		"('Wh_linktext', 'Read More'),('Wh_linkurl', ''),('Wh_setlimit', '1'),".
		"('Wh_showlink', ''),('Wh_imgalign','right'),('Wh_layout', '1'),('Wh_copyrights', ''),('Wh_form', '1')";
	$solu = $wpdb->query( $insert );
}

// +---------------------------------------------------------------------------+
// | Add Settings Link to the Plugins Page                                         |
// +---------------------------------------------------------------------------+

function add_setting_links($links, $file) {
	static $Wh_plugin;
	if (!$Wh_plugin) $Wh_plugin = plugin_basename(__FILE__);

	if ($file == $Wh_plugin){
		$settings_link = '<a href="admin.php?page=Wh_config">'.__("Settings").'</a>';

		 $links[] = $settings_link;
	}
	return $links;
}

function Wh_Set() {
	if (current_user_can('update_plugins'))
	add_filter('plugin_action_links', 'add_setting_links', 10, 2 );
}

// +---------------------------------------------------------------------------+
// | Add New Testimonial To Site                                               |
// +---------------------------------------------------------------------------+

/* add new testimonial form */
function Wh_newform() {
?>
	<div class="wrap">
	<h2>Add New Testimonial</h2>
	<ul>
	<li>If you want to include this testimonial in the random block, you must have content in the &quot;short text&quot; field.</li>
	<li>You must have content in the &quot;full text&quot; field for this testimonial to show on your Testimonials page.</li>
	</ul>
	<br />
	<div id="Whtest-form">
	<form name="addnew" method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
	<label for="wh_clientname">Client Name:</label><input name="wh_clientname" type="text" size="45"><br/>
	<label for="wh_company">Company:</label><input name="wh_company" type="text" size="45"><br/>
	<label for="website">Website:</label><input name="wh_homepage" type="text" size="45" value="http://" ><br/>
	<label for="wh_text_short">Short text (20-30 words for random block):</label><textarea name="wh_text_short" cols="45" rows="5"></textarea><br/>
	<label for="wh_text_full">Full text:</label><textarea name="wh_text_full" cols="45" rows="15"></textarea><br/>
	<label for="wh_sfimgurl">Image URL:</label><input name="wh_sfimgurl" type="file" size="">  <br/>
	<input type="submit" name="Wh_addnew" value="<?php _e('Add Testimonial', 'Wh_addnew' ) ?>" /><br/>

	</form>
	</div>
	</div>
<?php }

/* insert testimonial into Database */
function Wh_insertnew() {

	global $wpdb;
	$table_name = $wpdb->prefix . "wh_testimonials";
	$txt_short = $wpdb->escape($_POST['wh_text_short']);
	$txt_long = $wpdb->escape($_POST['wh_text_full']);
	$wh_clientname = $wpdb->escape($_POST['wh_clientname']);
	$wh_company = $wpdb->escape($_POST['wh_company']);
	$wh_homepage = $_POST['wh_homepage'];
	 $file = $_POST['wh_sfimgurl'];
	 $max_w="100px";
	$max_h="100px";
	$dest_path= plugins_url('/wh-testimonials/images/');
	require_once(ABSPATH . 'wp-includes/functions.php');



	if($_FILES["wh_sfimgurl"]["name"]){



	 $upload = wp_upload_bits( $_FILES["wh_sfimgurl"]["name"], null, file_get_contents($_FILES["wh_sfimgurl"]["tmp_name"]));
	$url=explode("/",$upload[url]);
	 $count=count($url);
	 $output = array_slice($url, 5, $count);
	 for($i=0;$i<$count;$i++){
	 	$img=$url[$i];
	 }
		$output=implode("/",$output);
	 add_image_size( $img, $width = 100, $height = 100, $crop = FALSE);
	}
	  $insert = "INSERT INTO " . $table_name .
	" (wh_text_short,wh_text_full,wh_clientname,wh_company,wh_homepage,wh_sfimgurl) " .
	"VALUES ('$txt_short','$txt_long','$wh_clientname','$wh_company','$wh_homepage','$output')";

	$results = $wpdb->query( $insert );

}

function Wh_newform_front()
{
	$copyright = get_option('Wh_form');
	if($copyright=="1"){
	Wh_newform();
	if (isset($_POST['Wh_addnew'])) {
			Wh_insertnew();
	}
	}
	else
	{
		echo"Admin has blocked the testimonial form in front-end";
	}

}

// +---------------------------------------------------------------------------+
// | Manage Page - list all and show edit/delete options                       |
// +---------------------------------------------------------------------------+


/* show list of all added testimonials */
function Wh_showlist() {
	global $wpdb;
	$table_name = $wpdb->prefix . "wh_testimonials";
	$tstlist = $wpdb->get_results("SELECT wh_id,wh_clientname,wh_company,wh_homepage FROM $table_name");

	foreach ($tstlist as $tstlist2) {
		echo '<p>';
		echo '<a href="admin.php?page=Wh_manage&amp;mode=Whedit&amp;wh_id='.$tstlist2->wh_id.'">Edit</a>';
		echo '&nbsp;|&nbsp;';
		echo '<a href="admin.php?page=Wh_manage&amp;mode=Whrem&amp;wh_id='.$tstlist2->wh_id.'" onClick="return confirm(\'Delete this testimonial?\')">Delete</a>';
		echo '&nbsp;&nbsp;';
		echo stripslashes($tstlist2->wh_clientname);
			if ($tstlist2->wh_company != '') {
				if ($tstlist2->wh_homepage != '') {
					echo ' ( <a href="'.$tstlist2->wh_homepage.'">'.stripslashes($tstlist2->wh_company).'</a>  )';
				} else {
					echo ' ('.stripslashes($tstlist2->wh_company).')';
				}
			}
		echo '</p>';
	}
}

/* edit testimonial form */

function Wh_edit($wh_id){
	global $wpdb;
	$table_name = $wpdb->prefix . "wh_testimonials";

	$gettst2 = $wpdb->get_row("SELECT wh_id, wh_clientname, wh_company, wh_homepage, wh_text_full, wh_text_short, wh_sfimgurl FROM $table_name WHERE wh_id = $wh_id");

	echo '<h3>Edit Testimonial</h3>';
	echo '<ul>
	<li>If you want to include this testimonial in the random block, you must have content in the &quot;short text&quot; field.</li>
	<li>You must have content in the &quot;full text&quot; field for this testimonial to show on your Testimonials page.</li>
	</ul>';

	echo '<div id="Whtest-form">';
	echo '<form name="edittst" method="post" action="admin.php?page=Wh_manage" enctype="multipart/form-data">';
	echo '<label for="wh_clientname">Client Name:</label>
		  <input name="wh_clientname" type="text" size="45" value="'.stripslashes($gettst2->wh_clientname).'"><br/>
		<label for="wh_company">wh_company:</label>
		  <input name="wh_company" type="text" size="45" value="'.stripslashes($gettst2->wh_company).'"><br/>
		<label for="wh_homepage">Website:</label>
		 <input name="wh_homepage" type="text" size="45" value="'.$gettst2->wh_homepage.'"><br/>
		<label for="wh_text_short">Short text (20-30 words for random block):</label>
		  <textarea name="wh_text_short" cols="45" rows="5">'.stripslashes($gettst2->wh_text_short).'</textarea><br/>
		<label for="wh_text_full">Full text:</label>
		  <textarea name="wh_text_full" cols="45" rows="15">'.stripslashes($gettst2->wh_text_full).'</textarea><br/>
		<label for="wh_sfimgurl">Image URL:</label><input name="wh_sfimgurl" type="file" size="" value="'.$gettst2->wh_sfimgurl.'">  <br/>
		  <input type="hidden" name="wh_id" value="'.$gettst2->wh_id.'">
		  <input name="Whedittest" type="submit" value="Update">';
	echo '</form>';
	echo '</div>';
}

/* update testimonial in Database */
function Wh_edit_test($wh_id){
	global $wpdb;
	$table_name = $wpdb->prefix . "wh_testimonials";



	$wh_id = $wh_id;
	//get the value
	$gettst2 = $wpdb->get_row("SELECT wh_id, wh_clientname, wh_company, wh_homepage, wh_text_full, wh_text_short, wh_sfimgurl FROM $table_name WHERE wh_id = $wh_id");
	$txt_short = $wpdb->escape($_POST['wh_text_short']);
	$txt_long = $wpdb->escape($_POST['wh_text_full']);
	$wh_clientname = $wpdb->escape($_POST['wh_clientname']);
	$wh_company = $wpdb->escape($_POST['wh_company']);
	$wh_homepage = $_POST['wh_homepage'];
	$wh_sfimgurl = $_POST['wh_sfimgurl'];
	$file = $_POST['wh_sfimgurl'];
	//check the value of image
	if( !$_FILES["wh_sfimgurl"]["name"])
	{
	$output =$gettst2->wh_sfimgurl;
	}
	require_once(ABSPATH . 'wp-includes/functions.php');
	wp_handle_upload($file );
	if($_FILES["wh_sfimgurl"]["name"]){



	 $upload = wp_upload_bits( $_FILES["wh_sfimgurl"]["name"], null, file_get_contents($_FILES["wh_sfimgurl"]["tmp_name"]));
	$url=explode("/",$upload[url]);
	 $count=count($url);
	 $output = array_slice($url, 5, $count);
	 for($i=0;$i<$count;$i++){
	 	$img=$url[$i];
	 }
		$output=implode("/",$output);
	 add_image_size( $img, $width = 100, $height = 100, $crop = FALSE);
	}


	$wpdb->query("UPDATE " . $table_name .
	" SET wh_text_short = '$txt_short', ".
	" wh_text_full = '$txt_long', ".
	" wh_clientname = '$wh_clientname', ".
	" wh_company = '$wh_company', ".
	" wh_homepage = '$wh_homepage', ".
	" wh_sfimgurl = '$output' ".
	" WHERE wh_id = '$wh_id'");
}

/* delete testimonials from Database */
function Wh_removetst($wh_id) {
	global $wpdb;
	$table_name = $wpdb->prefix . "wh_testimonials";

	$insert = "DELETE FROM " . $table_name .
	" WHERE wh_id = ".$wh_id ."";

	$results = $wpdb->query( $insert );

}


/* admin page display */
function Wh_adminpage() {
	global $wpdb;
?>
	<div class="wrap">
	<?php
	echo '<h2>Testimonials Management Page</h2>';


		if (isset($_POST['Wh_addnew'])) {
			Wh_insertnew();
			?>
	<div id="message" class="updated fade"><p><strong><?php _e('Testimonial Added'); ?>.</strong></p></div><?php
		}
		if ($_REQUEST['mode']=='Whrem') {
			Wh_removetst($_REQUEST['wh_id']);
			?><div id="message" class="updated fade"><p><strong><?php _e('Testimonial Deleted'); ?>.</strong></p></div><?php
		}
		if ($_REQUEST['mode']=='Whedit') {
			Wh_edit($_REQUEST['wh_id']);
			exit;
		}
		if (isset($_REQUEST['Whedittest'])) {
			Wh_edit_test($_REQUEST['wh_id']);
			?><div id="message" class="updated fade"><p><strong><?php _e('Testimonial Updated'); ?>.</strong></p></div><?php
		}
			Wh_showlist(); // show testimonials
		?>
	</div>
	<div class="wrap"><?php Wh_newform(); // show form to add new testimonial ?>
	</div>
	<div class="wrap">
	<?php
$yearnow = date('Y');

?>
	  <p>WH-Testimonials is &copy; Copyright <?php echo("".date('Y').""); ?>, <a href="http://www.webhostings.in/" target="_blank">web hosting</a> and distributed under the <a href="http://www.fsf.org/licensing/licenses/quick-guide-gplv3.html" target="_blank">GNU General Public License</a>.
	  </p>
	</div>
<?php }

// +---------------------------------------------------------------------------+
// | Sidebar - display random testimonial(s) in sidebar                           |
// +---------------------------------------------------------------------------+

/* show random testimonial(s) in sidebar */
function Wh_widgetrandom() {
	global $wpdb;
	$table_name = $wpdb->prefix . "wh_testimonials";
	if (get_option('Wh_setlimit') == '') {
		$Wh_setlimit = 1;
	} else {
		$Wh_setlimit = get_option('Wh_setlimit');
	}
	$randone = $wpdb->get_results("SELECT wh_id, wh_clientname, wh_company, wh_homepage, wh_text_short FROM $table_name WHERE wh_text_short !='' order by RAND() LIMIT $Wh_setlimit");

	echo '<div id="Whtest-sidebar">';

	foreach ($randone as $randone2) {

			echo '<blockquote>';
			echo '<p>';
			echo nl2br(stripslashes($randone2->wh_text_short));
			echo '</p>';

			echo '<p><cite>';
			if ($randone2->wh_company != '') {
			echo stripslashes($randone2->wh_clientname).'<br/>';
				if ($randone2->wh_homepage != '') {
					echo '<a href="'.$randone2->wh_homepage.'" class="cite-link">'.stripslashes($randone2->company).'</a>';
				} else {
					echo stripslashes($randone2->wh_company);
				}

			} else {
				echo stripslashes($randone2->wh_clientname).'';
			}
			echo '</cite></p>';
			echo '</blockquote>';

		} // end loop
			$Wh_showlink = get_option('Wh_showlink');
			$Wh_linktext = get_option('Wh_linktext');
			$Wh_linkurl = get_option('Wh_linkurl');

				if (($Wh_showlink == 'yes') && ($Wh_linkurl !='')) {
					if ($Wh_linktext == '') { $Wh_linkdisplay = 'Read More'; } else { $Wh_linkdisplay = $Wh_linktext; }
					echo '<div class="Whreadmore"><a href="'.$Wh_linkurl.'">'.$Wh_linkdisplay.'</a></div>';
				}
	echo '</div>';
}

// +---------------------------------------------------------------------------+
// | Widget for testimonial in sidebar                                         |
// +---------------------------------------------------------------------------+
if (version_compare($wp_version, '2.8', '>=')) { // check if this is WP2.8+

	### Class: WH-Testimonials Widget
	 class Wh_widget extends WP_Widget {
		// Constructor
		function Wh_widget() {
			$widget_ops = array('description' => __('Displays one random testimonial in your sidebar', 'WH-Testimonials'));
			$this->WP_Widget('testimonial', __('WH-Testimonials'), $widget_ops);
		}

		// Display Widget
		function widget($args, $instance) {
			extract($args);
			$title = esc_attr($instance['title']);

			echo $before_widget.$before_title.$title.$after_title;

				Wh_widgetrandom();

			echo $after_widget;
		}

		// When Widget Control Form Is Posted
		function update($new_instance, $old_instance) {
			if (!isset($new_instance['submit'])) {
				return false;
			}
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			return $instance;
		}

		// DIsplay Widget Control Form
		function form($instance) {
			global $wpdb;
			$instance = wp_parse_args((array) $instance, array('title' => __('Testimonials', 'WH-Testimonials')));
			$title = esc_attr($instance['title']);
	?>


				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'WH-Testimonials'); ?>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>

	<input type="hidden" id="<?php echo $this->get_field_id('submit'); ?>" name="<?php echo $this->get_field_name('submit'); ?>" value="1" />
	<?php
		}
	}

	### Function: Init WH-Testimonials  Widget
	add_action('widgets_init', 'widget_Wh_init');
	function widget_Wh_init() {
		register_widget('Wh_widget');
	}
} else { // this is an older WP so use old widget structure
	function widget_Whwidget($args) {
		extract($args);
	?>
			<?php echo $before_widget; ?>
				<?php echo $before_title
					. 'Testimonial'
					. $after_title; ?>
			 <?php Wh_widgetrandom(); ?>
			<?php echo $after_widget; ?>
	<?php
	}
	add_action('plugins_loaded', 'Wh_sidebarWidgetInit');
	function Wh_sidebarWidgetInit()
	{
		register_sidebar_widget('Testimonials', 'widget_Whwidget');
	}
}



// +---------------------------------------------------------------------------+
// | Configuration options for testimonials                                    |
// +---------------------------------------------------------------------------+

function Wh_options_page() {
?>
	<div class="wrap">
	<?php if ($_REQUEST['updated']=='true') { ?>
	<div id="message" class="updated fade"><p><strong>Settings Updated</strong></p></div>
	<?php  } ?>

	<h2>Testimonials Settings</h2>

	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>
	<?php settings_fields( 'Wh-option-group' ); ?>

	<table cellpadding="5" cellspacing="5">



	<tr valign="top">
	<td>Show link in sidebar to full page of testimonials</td>
	<td>
	<?php $Wh_showlink = get_option('Wh_showlink');
	if ($Wh_showlink == 'yes') { ?>
	<input type="checkbox" name="Wh_showlink" value="yes" checked />
	<?php } else { ?>
	<input type="checkbox" name="Wh_showlink" value="yes" />
	<?php } ?>
	</td>
	</tr>

	<tr valign="top">
	<td>Text for sidebar link (Read More, View All, etc)</td>
	<td><input type="text" name="Wh_linktext" value="<?php echo get_option('Wh_linktext'); ?>" /></td>
	</tr>
	<tr valign="top">
	<td>Select the Layout</td>
	<td>
		<?php $Wh_layout = get_option('Wh_layout');
	if ($Wh_layout == '1') { ?>
	<input type="radio" name="Wh_layout" value="1" checked /> Layout1
	<input type="radio" name="Wh_layout" value="2" /> Layout2
	<?php } elseif ($Wh_layout == '2') { ?>
	<input type="radio" name="Wh_layout" value="1" /> Layout1
	<input type="radio" name="Wh_layout" value="2" checked/> Layout2
	<?php } else { ?>
	<input type="radio" name="Wh_layout" value="1" /> Layout1
	<input type="radio" name="Wh_layout" value="2" /> Layout2
	<?php } ?>
	</td>
	</tr>
	<tr>
	<td>
	show copyrights
	</td>
	<td><?php
	$Wh_copy = get_option('Wh_copyrights');
	if ($Wh_copy == '1') { ?>
	<input type="checkbox" name="Wh_copyrights" value="1" checked />
	<?php } else { ?>
	<input type="checkbox" name="Wh_copyrights" value="1" />
	<?php } ?>
	</td>
	</tr>
	<tr>
	<td>
	Display form in site<br>
	(use shortcode [Wh-testimonials_add] to display in the front-end)
	</td>
	<td>
	<?php
	$Wh_form = get_option('Wh_form');
	if ($Wh_form == '1') { ?>
	<input type="checkbox" name="Wh_form" value="1" checked />
	<?php } else { ?>
	<input type="checkbox" name="Wh_form" value="1" />
	<?php } ?>
	</td>
	</tr>

	<tr valign="top">
	<td>Number of testimonials to show in sidebar</td>
	<td><input type="text" name="Wh_setlimit" value="<?php echo get_option('Wh_setlimit'); ?>" /></td>
	</tr>

	<tr valign="top">
	<td>Testimonials page for sidebar link<br/> (use shortcode [Wh-testimonials] to display in the front-end)</td>
	<td> <select name="Wh_linkurl">
	 <option value="">
<?php echo attribute_escape(__('Select page')); ?></option>
 <?php
  $pages = get_pages();
  foreach ($pages as $pagg) {
  $pagurl = get_page_link($pagg->ID);
  $sfturl = get_option('Wh_linkurl');
  	if ($pagurl == $sfturl) {
		$option = '<option value="'.get_page_link($pagg->ID).'" selected>';
		$option .= $pagg->post_title;
		$option .= '</option>';
		echo $option;
	} else {
		$option = '<option value="'.get_page_link($pagg->ID).'">';
		$option .= $pagg->post_title;
		$option .= '</option>';
		echo $option;
	}
  }
 ?>	</select></td>
	</tr>



	<tr valign="top">
	<td>Use class alignleft or alignright for testimonial image</td>
	<td>
	<?php $Wh_imgalign = get_option('Wh_imgalign');
	if ($Wh_imgalign == 'alignleft') { ?>
	<input type="radio" name="Wh_imgalign" value="alignleft" checked /> Left
	<input type="radio" name="Wh_imgalign" value="alignright" /> Right
	<?php } elseif ($Wh_imgalign == 'alignright') { ?>
	<input type="radio" name="Wh_imgalign" value="alignleft" /> Left
	<input type="radio" name="Wh_imgalign" value="alignright" checked/> Right
	<?php } else { ?>
	<input type="radio" name="Wh_imgalign" value="alignleft" /> Left
	<input type="radio" name="Wh_imgalign" value="alignright" /> Right
	<?php } ?>
	</td>
	</tr>



	<tr valign="top">
	<td>Remove table when deactivating plugin</td>
	<td>
	<?php $Wh_deldata = get_option('Wh_deldata');
	if ($Wh_deldata == 'yes') { ?>
	<input type="checkbox" name="Wh_deldata" value="yes" checked /> (this will result in all data being deleted!)
	<?php } else { ?>
	<input type="checkbox" name="Wh_deldata" value="yes" /> (this will result in all data being deleted!)
	<?php } ?>
	</td>
	</tr>

	</table>
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="Wh_admng,Wh_showlink,Wh_linktext,Wh_setlimit,Wh_linkurl,Wh_imgalign,Wh_layout,Wh_deldata" />

	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</p>

	</form>

	</div>
<?php
}


// +---------------------------------------------------------------------------+
// | Uninstall plugin                                                          |
// +---------------------------------------------------------------------------+

function Wh_deactivate () {
	global $wpdb;

	$table_name = $wpdb->prefix . "wh_testimonials";

	$Wh_deldata = get_option('Wh_deldata');
	if ($Wh_deldata == 'yes') {
		$wpdb->query("DROP TABLE {$table_name}");
		delete_option("Wh_showlink");
		delete_option("Wh_linktext");
		delete_option("Wh_linkurl");
		delete_option("Wh_deldata");
		delete_option("Wh_setlimit");
		delete_option("Wh_admng");
		delete_option("Wh_imgalign");
		delete_option("Wh_layout");
		delete_option("Wh_copyrights");

 	}
    delete_option("Wh_version");
	unregister_Wh_options();

}

// +---------------------------------------------------------------------------+
// | Show testimonials on page with shortcode [Wh-testimonials]               |
// +---------------------------------------------------------------------------+


/* show page of all testimonials */
function Wh_showall() {
	global $wpdb;
wp_enqueue_script('Wh_css', '/' . PLUGINDIR . '/wh-testimonials/css/wh-testimonials-js.js');
	$sfimgalign = get_option('Wh_imgalign');
	if ($sfimgalign == '') { $Wh_imgalign = 'alignright'; } else { $Wh_imgalign = get_option('Wh_imgalign'); }

 	$table_name = $wpdb->prefix . "wh_testimonials";
	$tstpage = $wpdb->get_results("SELECT wh_id,wh_clientname,wh_company,wh_text_full,wh_homepage,wh_sfimgurl FROM $table_name WHERE wh_text_full !=''");
	$adodis_testimonial = '';
	$adodis_testimonial .= '';
	$adodis_testimonial .= '<div id="Whtest-page">';
	$imglayout = get_option('Wh_layout');
	$copyright = get_option('Wh_copyrights');
/*********************      Layout 1                ***********************/
	if($imglayout=='1'){
		foreach ($tstpage as $tstpage2) {
			if ($tstpage2->wh_text_full != '') { // don't show blank testimonials
				$adodis_testimonial.='<div class="test_top">';
				$adodis_testimonial.='</div>';
				$adodis_testimonial.='<div class="test_mid">';
				$adodis_testimonial .= '<blockquote>';
				$adodis_testimonial .= '<p>';
					if ($tstpage2->wh_sfimgurl != '') { // check for image
						$Wh_imgmax = get_option('Wh_imgmax');
						 $sfiheight = ' width="125px';
						 $Wh_imgalign='align=right';
						$adodis_testimonial .= '<img src="'.$tstpage2->wh_sfimgurl.'"'.$sfiheight.' class="'.$Wh_imgalign.' alt="'.stripslashes($tstpage2->wh_clientname).'">';
					}

				$adodis_testimonial .= nl2br(stripslashes($tstpage2->wh_text_full));
				$adodis_testimonial .= '</p>';

					$adodis_testimonial .= '<p><cite>';
						if ($tstpage2->wh_company != '') {
						$adodis_testimonial .= stripslashes($tstpage2->wh_clientname).'<br/>';
							if ($tstpage2->wh_homepage != '') {
									$adodis_testimonial .= '<a href="'.$tstpage2->wh_homepage.'" class="cite-link">'.stripslashes($tstpage2->wh_company).'</a>';
							} else {
								$adodis_testimonial .= stripslashes($tstpage2->wh_company).'';
							}
						} else {
							$adodis_testimonial .= stripslashes($tstpage2->wh_clientname).'';
						}
			$adodis_testimonial .= '</cite></p>';
			$adodis_testimonial .= '</blockquote>';
			$adodis_testimonial .= '</div>';
			$adodis_testimonial.='<div class="test_bot1">';
			$adodis_testimonial.='<div class="test_bot">';
			$adodis_testimonial.='</div>';
			$adodis_testimonial.='</div>';

			}
	}
	if($copyright=='1'){
	$adodis_testimonial .= '<div style="text-align:center;font-size:9px;">Developed by <a href="http://www.webhostings.in/">web hosting</a></div>';
	}
	$adodis_testimonial .= '</div>';
return $adodis_testimonial;
}
/*********************      Layout 2                ***********************/
else{
	$count=count($tstpage);
	foreach ($tstpage as $tstpage2) {
		if ($tstpage2->wh_text_full != '') { // don't show blank testimonials
			$adodis_testimonial.='<div class="test_top_2">';
			$adodis_testimonial.='</div>';
			$adodis_testimonial.='<div class="test_mid_2">';
			$adodis_testimonial.='<div class="test_bot_2">';
			$adodis_testimonial.='<div class="test_bot1_2">';

			$adodis_testimonial .= '<blockquote>';
			$adodis_testimonial .= '<p>';
			if ($tstpage2->wh_sfimgurl != '') { // check for image
				$Wh_imgmax = get_option('Wh_imgmax');
				 $sfiheight = ' width="125px';
				 $Wh_imgalign='align=right';
				$adodis_testimonial .= '<img src="'.$tstpage2->wh_sfimgurl.'"'.$sfiheight.' class="'.$Wh_imgalign.' alt="'.stripslashes($tstpage2->wh_clientname).'">';
			}

			$adodis_testimonial .= nl2br(stripslashes($tstpage2->wh_text_full));
			$adodis_testimonial .= '</p>';

				$adodis_testimonial .= '<p style="margin-bottom:0px;"><cite>';
				if ($tstpage2->wh_company != '') {
					$adodis_testimonial .='<div class="testimonial_name">';
					$adodis_testimonial .='<span>';
				$adodis_testimonial .= stripslashes($tstpage2->wh_clientname).'</span><br/>';
					if ($tstpage2->wh_homepage != '') {
							$adodis_testimonial .= '<a href="'.$tstpage2->wh_homepage.'" class="cite-link">'.stripslashes($tstpage2->wh_company).'</a>';
					} else {
						$adodis_testimonial .= stripslashes($tstpage2->wh_company).'';
					$adodis_testimonial .='</div>';}
				} else {
					$adodis_testimonial .='<span>';
					$adodis_testimonial .= stripslashes($tstpage2->wh_clientname).'</span>';
				}
				$adodis_testimonial .= '</cite></p>';
		$adodis_testimonial .= '</blockquote>';
		$adodis_testimonial .= '</div>';


		$adodis_testimonial.='</div>';
		$adodis_testimonial.='</div>';

		}
	}

	if($copyright=='1'){
	$adodis_testimonial .= '<div style="text-align:center;font-size:9px;">Developed by <a href="http://www.webhostings.in/">web hosting</a></div>';
	}
	$adodis_testimonial .= '</div>';

return $adodis_testimonial;

}
}
?>
