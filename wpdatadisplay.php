<?php
/*  Copyright 2014  Alan Tygel  (email : alantygel@riseup.net)

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

/**
 * Plugin Name: WPDataDisplay
 * Plugin URI: https://github.com/coletivoEITA/wp_datadisplay
 * Description: This plugin creates a special post type that displays data of custom database tables.
 * Version: 0.0
 * Author: alantygel
 * Author URI: https://github.com/alantygel
 * License: GPL3
 */

include('wpdd_config.php');

/* --- NEW POST TYPE DATA DISPLAY --- */

add_action( 'init', 'create_posttype' );

/* --- REGISTER OPTIONS --- */

add_action( 'admin_init', 'register_datadisplay_settings' );

function register_datadisplay_settings() {
	register_setting( 'datadisplay_options', 'datadisplay_options', 'datadisplay_options_validate' );
	add_settings_section('datadisplay_main', 'Template Settings', 'datadisplay_section_text', 'datadisplay');
	add_settings_field('datadisplay_slug', 'URL Slug', 'datadisplay_setting_slug', 'datadisplay', 'datadisplay_main');
	add_settings_field('datadisplay_template', 'Template', 'datadisplay_setting_template', 'datadisplay', 'datadisplay_main');
}
 
function datadisplay_section_text() {
	echo '<p>' . _('Build your template here. <br>
					<b>Title:</b> #column,_,#column - Use column names (from main table) preceded by # to use their values, and strings, separated by comma. This will also be used to build the slug, so you must guarantee its unique.<br>
					<b>Template:</b> [wpdatadisplay id=#id column=col_name table=table_name type={normal,exists}] . Use HTML and shortcodes to display data in from your tables. #id will be replaced by the id in your row') . '</p>';
}

function datadisplay_setting_template() {
	$options = get_option('datadisplay_options');
	echo "<textarea id='datadisplay_template' name='datadisplay_options[template]' rows='20' cols='100'>{$options['template']}</textarea>";
}

function datadisplay_setting_slug() {
	$options = get_option('datadisplay_options');
	echo "<input id='datadisplay_slug' name='datadisplay_options[slug]' value='{$options['slug']}' />";
}

function datadisplay_options_validate($input) {
	$newinput['template'] = trim($input['template']);
	$newinput['slug'] = trim($input['slug']);
	//if(!preg_match('/^[a-z0-9]{32}$/i', $newinput['template'])) {
	//	$newinput['template'] = '';
	//}
	return $newinput;
}


function create_posttype() {
	register_post_type( 'wpdatadisplay',
		array(
			'labels' => array(
				'name' => __( 'Data Display' ),
				'singular_name' => __( 'Data Display' )
			),
			'public' => true,
			'has_archive' => true,
			'supports' => array(
				'title',
				'editor',
				'author',
				'thumbnail', 
				'excerpt',
				'trackbacks',
				'custom-fields',
				'comments',
				'revisions',
				'page-attributes',
				'post-formats'
			)
//			'rewrite' => array('slug' => 'wpdatadisplay'),
		)
	);
}

/* --- ADMIN PANEL --- */

add_action( 'admin_menu', 'wpdatadisplay_menu' );

function wpdatadisplay_menu() {
	add_options_page( 'WPDataDisplay Options', 'WPDataDisplay', 'manage_options', 'wpdatadisplay', 'wpdatadisplay_options' );
}

function wpdatadisplay_options() {
	global $wpdb;

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

		
	if (isset($_GET['action'])){
		$action = $_GET['action'];
		switch ($action){
			case 'generate_posts': 
				clear_posts();
				generate_posts();
				echo _('Posts generated');
				break;
			case 'clear_posts': clear_posts();
				echo _('Posts cleared');
				break;
		}
	}

	echo '<h1>WordPress Data Display</h1>';
	echo '<p>Welcome to WPDataDisplay plugin. To search post, create a new page with the [wpdatadisplay-search] shortcode.</p>';

	echo '<hr><h2>Generate and Clear Data Display Posts</h2>';
	echo 'Click the button to generate the post refering to each table row. Soon here you will configure your template and load new data.</p>';
	echo '<div style="display: inline-block;"><div style="float:left"><form name="generate_posts" action="./options-general.php" method="GET">';
	echo '<input type="hidden" name="page" value="wpdatadisplay">';
	echo '<input type="hidden" name="action" value="generate_posts">';
	echo '<input type="submit" value="' . _('Generate Posts') . '">';
	echo '</form></div>';

	echo '<div style="float:left"><form name="clear_posts" action="./options-general.php" method="GET">';
	echo '<input type="hidden" name="page" value="wpdatadisplay">';
	echo '<input type="hidden" name="action" value="clear_posts">';
	echo '<input type="submit" value="' . _('Clear Posts') . '">';
	echo '</form></div></div>';
	
	
	echo '<hr><h2>Data Tables</h2>';
	echo '<h3>' . _('Available tables and fields:') . '</h3>';

	$tables = get_tables();
	echo '<div style="display: inline-block;">';
	foreach ( $tables as $table ) {
		echo '<div style="float:left; margin-right: 20px; padding: 0 5px; border:1px solid black;"><p><b>' . substr($table[0], strlen($wpdb->prefix . WP_DATADISPLAY_PREFIX)) . '</b></p>';
		$fields = get_data_fields($table[0]);
		echo '<ul>';
		foreach ( $fields as $field){
			echo '<li>' . $field[0] . '</li>';
		}
		echo '</ul></div>';
	}
	echo '</div>';

/*		
	echo '<h3>' . _('Upload tables:') . '</h3>';
	echo '<form name="upload_table" action="./options-general.php" method="GET">';
	echo '<input type="hidden" name="page" value="wpdatadisplay">';
	echo '<input type="hidden" name="action" value="upload_data">';
	echo '<input type="file" name="file">';
	echo '<input type="submit" value="' . _('Upload Data') . '">';
	echo '</form>';
	
*/
	echo '<hr><h2>Edit Template</h2>';
	echo '<form action="options.php" method="post">';
	settings_fields('datadisplay_options');
	do_settings_sections('datadisplay');
 	echo '<input name="Submit" type="submit" value="';
 	esc_attr_e('Save Changes');
 	echo '" /></form>';

}


function generate_posts(){
	global $wpdb;
	$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}" . WP_DATADISPLAY_PREFIX . WP_DATADISPLAY_MAIN_TABLE);

	$category = 1;	//TODO MAKE CONFIGURABLE
	
	$options = get_option('datadisplay_options');
	$i = 0;
	foreach( $results as $row){
		$title = "";
		$title_tmp = explode(",",$options['slug']);
		foreach($title_tmp as $title_part){
			if($title_part[0] == "#"){
				$title .= $row->{ltrim($title_part,"#")};
			}else{
				$title .= $title_part;
			} 
		}

		$slug = sanitize_title_with_dashes($title);
		$exerpt = $title; //TODO MAKE CONFIGURABLE

		//get template and replace id by the actual id number		
		$post_content = str_replace ( '#id' , $row->ID , $options['template'] );
		$post_content = str_replace ( '#slug' , $slug , $post_content );		
				
		$post = array(
			'post_content'   => $post_content, // The full text of the post.
			'post_name'      => $slug, // The name (slug) for your post 
			'post_title'     => $title, // The title of your post.
			'post_status'    => 'publish', // Default 'draft'.
			'post_type'      => 'wpdatadisplay', // Default 'post'.
			'post_excerpt'   => $exerpt, // For all your post excerpt needs.
			'comment_status' => 'open', // Default is the option 'default_comment_status', or 'closed'.
			'post_category'  => array($category) // Default empty.
			//'page_template'	 => 'full-width.php'
			//'tags_input'     => [ '<tag>, <tag>, ...' | array ] // Default empty.
		);
		$pid = wp_insert_post($post);
	}
#	wp_redirect( admin_url( 'options-general.php?page=wpdatadisplay' ) );
}

function clear_posts(){
	global $wpdb;
	$results = $wpdb->get_results("DELETE FROM {$wpdb->prefix}posts WHERE post_type = 'wpdatadisplay'");
}

/* --- SHORTCODE AND TEMPLATE --- */

function get_data_column( $atts ) {

	// Attributes
	extract( shortcode_atts(
		array(
			'id' => '1',
			'column' => '2',
			'table' => '3',
			'type' => '4'
		), $atts )
	);

	// Code
	return get_data($atts);
}

add_shortcode( 'wpdatadisplay', 'get_data_column' );

function get_data($atts){
	global $wpdb;

	$results = $wpdb->get_results("SELECT `" . $atts['column'] . "` FROM `{$wpdb->prefix}" . WP_DATADISPLAY_PREFIX . $atts['table'] . "` WHERE `id` = ". $atts['id']);

	if ($atts['type'] == "exists"){
		return $results ? str_replace(",", ".", $results[0]->$atts['column']) : "Não";
	}
	if (count($results) == 1){
//		echo $results[0]->$atts['column'];
		return $results ? str_replace(",", ".", $results[0]->$atts['column']) : 0;
	}
	elseif (count($results) == 0){
		return 0;
	}else{	
		$str_return = NULL;
		foreach ($results as $res){
			$str_return .= $res->$atts['column'] . "<br>";
		}
		return $str_return;
	}
	
}

function get_data_fields($table){
	global $wpdb;
	$results = $wpdb->get_results("SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_NAME`='$table';",'ARRAY_N');
	return $results;
}

function get_tables(){
	global $wpdb;
	$results = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}" . WP_DATADISPLAY_PREFIX . "%'", 'ARRAY_N');
	return $results;
}

function search_data() {

?>
	<link rel="stylesheet" type="text/css" href="./wp-content/plugins/wpdatadisplay/style.css" media="screen" />

	<h2> <?= WP_DATADISPLAY_SEARCH_DESC ?></h2>
	<form id=wpdatadisplay-search method=POST>
		<input type="text" onblur="if (this.value=='') this.value=this.defaultValue" onclick="style.color='white'; if (this.defaultValue==this.value) this.value=''" value="<?= WP_DATADISPLAY_SEARCH_VALUE ?>" name="search_string" style="width: 400px; height: 30px; color: grey;font-size: 18px; padding-left: 10px;">
		<input type="submit" value="<?= WP_DATADISPLAY_SEARCH ?>">
	</form>
<?php
	if($_POST){		
		$args = array(
			'post_type' => 'wpdatadisplay',
			's' => $_POST['search_string']
		);
		$query = new WP_Query( $args );
	        
                if ( $query->have_posts() ){
			echo "<h3>" . WP_DATADISPLAY_SEARCH_RESULTS . "</h3>";
			echo "<ul style='font-size:18px'>";
		while ( $query->have_posts() ){
			$query->the_post();
			echo "<li><a href=" . get_permalink() . ">" . get_the_title() . "</a></li>";
		}
			echo "</ul>";
		}
		else{
			echo WP_DATADISPLAY_SEARCH_NOTFOUND;
		}
		
		wp_reset_postdata();	
		
	}

	return NULL;
}

add_shortcode( 'wpdatadisplay-search', 'search_data' );

?>
