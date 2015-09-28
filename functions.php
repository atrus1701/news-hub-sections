<?php
/**
 * The functions for the UNC Charlotte News Hub Theme
 * 
 * @package    unc-charlotte-news-hub-theme
 * @author     Crystal Barton <atrus1701@gmail.com>
 * @version    1.0
 */


if( !defined('UNCCNH') ):

/**
 * The full title of the UNC Charlotte News Hub theme.
 * @var  string
 */
define( 'UNC_CHARLOTTE_NEWS_HUB_THEME', 'UNC Charlotte News Hub Theme' );

/**
 * True if debug is active, otherwise False.
 * @var  bool
 */
define( 'UNHT_DEBUG', false );

/**
 * The path to the plugin.
 * @var  string
 */
define( 'UNHT_THEME_PATH', __DIR__ );

/**
 * The url to the plugin.
 * @var  string
 */
define( 'UNHT_THEME_URL', get_stylesheet_directory_uri() );

/**
 * The version of the plugin.
 * @var  string
 */
define( 'UNHT_VERSION', '1.0.0' );

/**
 * The database version of the plugin.
 * @var  string
 */
define( 'UNHT_DB_VERSION', '1.0.0' );

/**
 * The database options key for the version.
 * @var  string
 */
define( 'UNHT_VERSION_OPTION', 'theme-version' );

/**
 * The database options key for the database version.
 * @var  string
 */
define( 'UNHT_DB_VERSION_OPTION', 'db-version' );

/**
 * The full path to the log file used for debugging.
 * @var  string
 */
define( 'UNHT_LOG_FILE', __DIR__.'/log.txt' );

/**
 * The sections option key.
 * @var  string
 */
define( 'UNHT_SECTIONS', 'unccnh-sections' );

endif;


require_once( UNHT_THEME_PATH.'/classes/section.php' );
require_once( UNHT_THEME_PATH.'/classes/widgets/section-listing.php' );
UNHT_WidgetSectionListingControl::register_widget();
UNHT_WidgetSectionListingControl::register_shortcode();

require_once( UNHT_THEME_PATH.'/classes/custom-post-type/event/event.php' );
require_once( UNHT_THEME_PATH.'/classes/custom-post-type/in-the-news/in-the-news.php' );


if( is_admin() ):
	add_action( 'wp_loaded', 'unht_load' );
endif;


add_filter( 'vtt-valid-variations', 'unht_valid_variations' );
add_filter( 'vtt-queried-object-type', 'unht_queried_object_type' );
add_filter( 'vtt-widget-areas', 'unht_modify_widgets' );
add_filter( 'vtt-main-template-parts', 'unht_main_template_parts', 11 );
add_filter( 'vtt-config', 'unht_config' );



if( !function_exists('unht_main_template_parts') ):
function unht_main_template_parts( $parts )
{
	return array(
		'header',
		'banner',
		'subheader',
		'main',
		'footer',
	);
}
endif;


if( !function_exists('unht_config') ):
function unht_config( $parts )
{
	$s = array_merge(
		array( 'id' => 0, 'list' => array() ),
		get_option( UNHT_SECTIONS, array() )
	);

	$sections = array();
	foreach( $s['list'] as $item )
	{
		$sections[ $item['name'] ] = new NH_Section( $item['name'], $item );
	}

	return array( 'sections' => $sections );
}
endif;


/**
 * 
 */
if( !function_exists('uncc_theme_setup') ):
function uncc_theme_setup()
{
	global $_wp_theme_features;

	add_theme_support( 'custom-header',
		array( 
			'random-default' 			=> false,
			'admin-head-callback' 		=> 'vtt_admin_head_callback',
			'admin-preview-callback' 	=> 'vtt_admin_preview_callback',
			'header-text'				=> false,
		)
	);
}
endif;


/**
 * Get a list of valid variations for this theme.
 * @param  Array  $valid_variations  Current valid variations.
 * @return  Array  A list of valid variations for the theme.
 */
if( !function_exists('unht_valid_variations') ):
function unht_valid_variations( $valid_variations )
{
	return array(
		'uncc',
		'uncc-light',
	);
}
endif;


/**
 * Modify a the object type to be the section the object type is a part of.
 * @param  string  $object_type  The type of queried object.
 * @return  string  The modified queried object type.
 */
if( !function_exists('unht_queried_object_type') ):
function unht_queried_object_type( $object_type )
{
	// switch( $object_type )
	// {
	// 	case 'connection-group':
	// 	case 'connection-link':
	// 		$object_type = 'connection';
	// 		break;
	// }
	
	return $object_type;
}
endif;


/**
 * Setup the site admin pages.
 */
if( !function_exists('unht_load') ):
function unht_load()
{
	require_once( __DIR__.'/admin-pages/require.php' );
	
	$pages = new APL_Handler( false );

	$pages->add_page( new UNHT_SectionsAdminPage('unccnh-sections') );
	$pages->setup();
	
	if( $pages->controller )
	{
		add_action( 'admin_enqueue_scripts', 'unht_enqueue_scripts' );
		add_action( 'admin_menu', 'unht_update', 5 );
	}
}
endif;


/**
 * Enqueue the admin page's CSS styles.
 */
if( !function_exists('unht_enqueue_scripts') ):
function unht_enqueue_scripts()
{
	wp_enqueue_style( 'uncchb-style', UNHT_THEME_URL.'/admin-pages/styles/style.css' );
}
endif;


/**
 * Update the database if a version change.
 */
if( !function_exists('unht_update') ):
function unht_update()
{
	$version = get_theme_mod( UNHT_DB_VERSION_OPTION, '1.0.0' );
	if( $version !== UNHT_DB_VERSION )
	{
//		$model = OrgHub_Model::get_instance();
//		$model->create_tables();
	}
	
	set_theme_mod( UNHT_DB_VERSION_OPTION, UNHT_DB_VERSION );
	set_theme_mod( UNHT_VERSION_OPTION, UNHT_VERSION );
}
endif;


/**
 * 
 */
if( !function_exists('unht_modify_widgets') ):
function unht_modify_widgets( $widgets )
{
	$widgets = array(
		array(
			'id'   => 'unccnh-front-page-1',
			'name' => 'Front Page - Column 1',
		),
		array(
			'id'   => 'unccnh-front-page-2',
			'name' => 'Front Page - Column 2',
		),
		array(
			'id'   => 'unccnh-sidebar',
			'name' => 'Sidebar',
		),
		array(
			'id'   => 'unccnh-mobile-menu-1',
			'name' => 'Mobile Menu - Tab 1',
		),
		array(
			'id'   => 'unccnh-mobile-menu-2',
			'name' => 'Mobile Menu - Tab 2',
		),
		array(
			'id'   => 'unccnh-mobile-menu-3',
			'name' => 'Mobile Menu - Tab 3',
		),
	);
	
	return $widgets;
}
endif;



if( !function_exists('unht_get_wpquery_section') ):
function unht_get_wpquery_section( $wpquery = null )
{
	global $wp_query, $vtt_config;

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
			
			$section = unht_get_section( $post_types, array( $qo->taxonomy => $qo->slug ), false );
			$wpquery->set( 'section', $section );
			
			return $section;
		}
		elseif( $wpquery->is_post_type_archive() )
		{
			$section = unht_get_section( $qo->name, null, false );
			$wpquery->set( 'section', $section );
			return $section;
		}

		return unht_get_default_section();
	}
	
	if( $wpquery->is_single() )
	{
		if( $qo === null )
		{
			$post_id = $wp_query->get( 'p' );

			if( !$post_id )
			{
				global $wpdb;
				
				$post_type = $wp_query->get( 'post_type', false );
				if( !$post_type ) $post_type = 'post';
				
				$post_slug = $wp_query->get( 'name', false );
				
				if( $post_slug !== false )
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
			$taxonomies = nh_get_taxonomies( $post_id );
			$section = unht_get_section( $post_type, $taxonomies, false, array('news') );
		}
		else
		{
			$section = unht_get_default_section();
		}
		
		$wpquery->set( 'section', $section );
		return $section;
	}
	
	return unht_get_default_section();
}
endif;


if( !function_exists('unht_get_section') ):
function unht_get_section( $post_types, $taxonomies = array(), $return_null = false, $exclude_sections = array() )
{
	global $vtt_config;

		$type_match = null;
		$partial_match = null;
		$exact_match = null;
		$best_count = 0;
		
		
		// 
		// 
		// 
		if( empty($post_types) ) $post_types = array( 'post' );
		if( !is_array($post_types) ) $post_types = array( $post_types );
		if( empty($taxonomies) ) $taxonomies = array();
		

		// 
		// 
		// 
		// cycle through each section looking for exact taxonomy and post type match
		foreach( $vtt_config->get_value('sections') as $key => $section )
		{
			if( in_array($key, $exclude_sections) ) continue;
			if( !$section->is_post_type($post_types) ) continue;
			
			if( !$section->has_taxonomies() )
			{
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
				$exact_match = $section;
				break;
			}
			
			if( $match_count > $best_count )
			{
				$partial_match = $section;
				$best_count = $match_count;
			}
		}
		
		
		if( $exact_match !== null ) return $exact_match;
		if( $partial_match !== null ) return $partial_match;
		if( $type_match !== null ) return $type_match;
		
		
		// 
		// Done.
		// 
		if( $return_null ) return null;
		return unht_get_default_section();
}
endif;


if( !function_exists('unht_get_default_section') ):
function unht_get_default_section()
{
	return new NH_Section( 'none', array( 'name' => 'None' ) );
}
endif;
	
	
if( !function_exists('unht_get_empty_section') ):
function unht_get_empty_section()
{
	return new NH_Section( '', array( 'name' => '' ) );
}
endif;

//----------------------------------------------------------------------------------------
// 
//----------------------------------------------------------------------------------------
if( !function_exists('nh_get_taxonomies') ):
function nh_get_taxonomies( $post_id = -1 )
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


if( !function_exists('nhs_get_anchor') ):
function nhs_get_anchor( $url, $title, $class = null, $contents = null )
{
	if( $url === null ) return $contents;
	
	$anchor = '<a href="'.$url.'" title="'.htmlentities($title).'"';
	if( strpos( $url, 'uncc.edu' ) === false ) $anchor .= ' target="_blank"';
	if( $class ) $anchor .= ' class="'.$class.'"';
	$anchor .= '>';

	if( $contents !== null )
		$anchor .= $contents.'</a>';

	return $anchor;
}
endif;
