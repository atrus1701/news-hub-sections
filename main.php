<?php
/*
Plugin Name: News Hub Sections
Plugin URI: https://github.com/clas-web/news-hub-sections
Description: 
Version: 1.1.2
Author: Crystal Barton
Author URI: https://www.linkedin.com/in/crystalbarton
*/


if( !defined('NEWS_HUB_SECTIONS') ):

/**
 * The full title of the Connections Hub plugin.
 * @var  string
 */
define( 'NEWS_HUB_SECTIONS', 'News Hub Sections' );

/**
 * True if debug is active, otherwise False.
 * @var  bool
 */
define( 'NHS_DEBUG', false );

/**
 * The path to the plugin.
 * @var  string
 */
define( 'NHS_PLUGIN_PATH', __DIR__ );

/**
 * The url to the plugin.
 * @var  string
 */
define( 'NHS_PLUGIN_URL', plugins_url('', __FILE__) );

/**
 * The version of the plugin.
 * @var  string
 */
define( 'NHS_VERSION', '2.5.0' );

/**
 * The database version of the plugin.
 * @var  string
 */
define( 'NHS_DB_VERSION', '1.0' );

/**
 * The database options key for the Connections Hub version.
 * @var  string
 */
define( 'NHS_VERSION_OPTION', 'nhs-version' );

/**
 * The database options key for the Connections Hub database version.
 * @var  string
 */
define( 'NHS_DB_VERSION_OPTION', 'nhs-db-version' );

/**
 * The database options key for the sections list.
 * @var  string
 */
define( 'NHS_SECTIONS', 'nhs-sections' );

/**
 * The full path to the log file used to log a synch.
 * @var  string
 */
define( 'NHS_LOG_FILE', __DIR__.'log.txt' );

endif;


require_once( NHS_PLUGIN_PATH.'/classes/model.php' );
$nhs_model = NHS_Model::get_instance();
$nhs_sections = $nhs_model->get_sections_objects();


require_once( NHS_PLUGIN_PATH.'/classes/widgets/section-listing.php' );
NHS_WidgetSectionListingControl::register_widget();
NHS_WidgetSectionListingControl::register_shortcode();


if( is_admin() ):
	add_action( 'wp_loaded', 'nhs_load' );
endif;


add_filter( 'pre_get_posts', 'nhs_alter_main_query' );
add_filter( 'vtt-post-type', 'nhs_get_post_type', 10, 2 );

add_action( 'wp_ajax_news-hub-sections', 'nhs_perform_ajax_request' );


/**
 * Setup the admin pages.
 */
if( !function_exists('nhs_load') ):
function nhs_load()
{
	require_once( __DIR__.'/admin-pages/require.php' );
	
	$pages = new APL_Handler( false );

	$pages->add_page( new NHS_SectionsAdminPage(NHS_SECTIONS) );
	$pages->setup();
	
	if( $pages->controller )
	{
		add_action( 'admin_enqueue_scripts', 'nhs_enqueue_scripts' );
		add_action( 'admin_menu', 'nhs_update', 5 );
	}
}
endif;


/**
 * Enqueue the admin page's CSS styles.
 */
if( !function_exists('nhs_enqueue_scripts') ):
function nhs_enqueue_scripts()
{
	wp_enqueue_style( 'nhs-style', NHS_PLUGIN_URL.'/admin-pages/styles/style.css' );
}
endif;


/**
 * Update the database if a version change.
 */
if( !function_exists('nhs_update') ):
function nhs_update()
{
	$version = get_theme_mod( NHS_DB_VERSION_OPTION, '1.0.0' );
	if( $version !== NHS_DB_VERSION )
	{
		$model = NHS_Model::get_instance();
//		$model->create_tables(); // NO tables...
	}
	
	set_theme_mod( NHS_DB_VERSION_OPTION, NHS_DB_VERSION );
	set_theme_mod( NHS_VERSION_OPTION, NHS_VERSION );
}
endif;


/**
 * 
 */
if( !function_exists('nhs_get_wpquery_section') ):
function nhs_get_wpquery_section( $wpquery = null )
{
	global $wp_query;

	if( $wpquery === null ) $wpquery = $wp_query;
	if( $wpquery->get('section') ) return $wpquery->get('section');
	
	$qo = $wpquery->get_queried_object();
	
	if( $wpquery->is_archive() )
	{
		if( $wpquery->is_tax() || $wpquery->is_tag() || $wpquery->is_category() )
		{
			$post_types = null;
			
			$taxonomy = get_taxonomy( $qo->taxonomy );
			if( $taxonomy ) $post_types = $taxonomy->object_type;
			
			$section = nhs_get_section( $post_types, array( $qo->taxonomy => $qo->slug ), FALSE );
			$wpquery->set( 'section', $section );
			
			return $section;
		}
		elseif( $wpquery->is_post_type_archive() )
		{
			$section = nhs_get_section( $qo->name, null, FALSE );
			$wpquery->set( 'section', $section );
			return $section;
		}

		return nhs_get_default_section();
	}
	
	if( $wpquery->is_single() )
	{
		if( $qo === null )
		{
			$post_id = $wp_query->get( 'p' );

			if( !$post_id )
			{
				global $wpdb;
				
				$post_type = $wp_query->get( 'post_type', FALSE );
				if( !$post_type ) $post_type = 'post';
				
				$post_slug = $wp_query->get( 'name', FALSE );
				
				if( $post_slug !== FALSE )
					$post_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_type = '$post_type' AND post_name = '$post_slug'" );
			}
		}
		else
		{
			$post_id = $qo->ID;
		}
		
		if( $post_id )
		{
			$post_type = get_post_type( $post_id );
			$taxonomies = nhs_get_taxonomies( $post_id );
			$section = nhs_get_section( $post_type, $taxonomies, false );
		}
		else
		{
			$section = nhs_get_default_section();
		}
		
		$wpquery->set( 'section', $section );
		return $section;
	}
	
	return nhs_get_default_section();
}
endif;


/**
 * 
 */
if( !function_exists('nhs_get_section') ):
function nhs_get_section( $post_types, $taxonomies = array(), $return_null = FALSE )
{
	global $nhs_sections;

	$type_match = null;
	$partial_match = null;
	$exact_match = null;
	$best_count = 0;
	
	// 
	if( empty($post_types) ) $post_types = array( 'post' );
	if( !is_array($post_types) ) $post_types = array( $post_types );
	if( empty($taxonomies) ) $taxonomies = array();
	
	// cycle through each section looking for exact taxonomy and post type match
	foreach( $nhs_sections as $key => $section )
	{
		if( $section->priority == -1 ) continue;
		if( !$section->is_post_type($post_types) ) continue;
		
		if( !$section->has_taxonomies() )
		{
			if( $type_match === NULL || $section->priority < $type_match->priority )
				$type_match = $section;
			continue;
		}
		
		$section_count = $section->get_taxonomy_count();
		$taxonomy_count = 0;
		$match_count = 0;
		foreach( $taxonomies as $taxname => $terms )
		{
			if( is_array($terms) )
			{
				foreach( $terms as $term )
				{
					if( $section->has_term($taxname, $term) )
					{
						$match_count++;
					}
					$taxonomy_count++;
				}
			}
			else
			{
				if( $section->has_term($taxname, $terms) )
				{
					$match_count++;
				}
				$taxonomy_count++;
			}
		}
		
		if( ($taxonomy_count == $match_count) && ($taxonomy_count == $section_count) )
		{
			if( $exact_match === NULL || $section->priority < $exact_match->priority )
				$exact_match = $section;
			continue;
		}

		if( $match_count === 0 ) continue;
		
		if( $match_count > $best_count )
		{
			$partial_match = $section;
			$best_count = $match_count;
		}
		elseif( $match_count == $best_count )
		{
			if( $partial_match === NULL || $section->priority < $partial_match->priority )
				$partial_match = $section;
		}
	}
	
	// 
	if( $exact_match !== null ) return $exact_match;
	if( $partial_match !== null ) return $partial_match;
	if( $type_match !== null ) return $type_match;
	
	
	// Done.
	if( $return_null ) return null;
	return nhs_get_default_section();
}
endif;


/**
 * 
 */
if( !function_exists('nhs_get_default_section') ):
function nhs_get_default_section()
{
	return new NHS_Section( array('key' => 'none') );
}
endif;


/**
 * 
 */
if( !function_exists('nhs_get_taxonomies') ):
function nhs_get_taxonomies( $post_id = -1 )
{
	global $post;
	
	if( $post_id == -1 )
		$post_id = $post->ID;
		
	$all_taxonomies = get_taxonomies( '', 'names' );
	
	$taxonomies = array();
	foreach( $all_taxonomies as $taxname )
	{
		$terms = wp_get_post_terms( $post_id, $taxname, array('fields' => 'slugs') );
		if( count($terms) > 0 )
			$taxonomies[$taxname] = $terms;
	}
	
	return $taxonomies;
}
endif;


/**
 * Alters the default query made when querying the News section.
 */
if( !function_exists('nhs_alter_main_query') ):
function nhs_alter_main_query( $wp_query )
{
	if( is_admin() || !$wp_query->is_main_query() ) return;

	$section = nhs_get_wpquery_section();
	if( $section->key == 'none' || $section->key == '' ) return;

	if( is_archive() )
	{
		if( is_feed() )
			$wp_query->set( 'posts_per_page', $section->rss_feed_stories );
		else
			$wp_query->set( 'posts_per_page', $section->archive_page_stories );
	}
}
endif;


/**
 * AJAX request.
 */
if( !function_exists('nhs_perform_ajax_request') ):
function nhs_perform_ajax_request()
{
	global $nhs_sections;

	$output = array(
		'status' => true,
		'message' => '',
	);

	switch( $_GET['ajax-action'] )
	{
		case 'get-post-list':

			$section = $_GET['section'];
			if( !$section )
			{
				$output['status'] = false;
				$output['message'] = 'No section specififed.';
				break;
			}

			if( array_key_exists($section, $nhs_sections) )
			{
				$s = $nhs_sections[$section];
			}
			else
			{
				$output['status'] = false;
				$output['message'] = 'Invalid section: '.$section;
				break;
			}

			$output['posts'] = $s->get_post_list(0, 20);
			break;
	}

	echo json_encode( $output );
	exit();
}
endif;


if( !function_exists('nhs_get_post_type') ):
function nhs_get_post_type( $post_type, $post )
{
	$section = nhs_get_section(
		array( $post_type ), 
		nhs_get_taxonomies( $post->ID ),
		TRUE );

	if( $section === NULL ) return $post_type;

	return $section->key;
}
endif;

