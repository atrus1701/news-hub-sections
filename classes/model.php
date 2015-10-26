<?php

require_once( NHS_PLUGIN_PATH.'/classes/section.php' );

/**
 * The main model for the UNC Charlotte News Hub theme.
 * 
 * @package    unc-charlotte-news-hub-theme
 * @subpackage classes/model
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('NHS_Model') ):
class NHS_Model
{
	/**
	 * The only instance of the current model.
	 * @var  OrgHub_Model
	 */	
	private static $instance = null;
	
	/**
	 * The last error saved by the model.
	 * @var  string
	 */	
	public $last_error = null;
		
	
	/**
	 * Private Constructor.  Needed for a Singleton class.
	 */
	protected function __construct() { }
	
	
	/**
	 * Sets up the "children" models used by this model.
	 */
	protected function setup_models()
	{
	}
	

	/**
	 * Get the only instance of this class.
	 * @return  NHS_Model  A singleton instance of the model class.
	 */
	public static function get_instance()
	{
		if( self::$instance	=== null )
		{
			self::$instance = new NHS_Model();
			self::$instance->setup_models();
		}
		return self::$instance;
	}



//========================================================================================
//========================================================================= Log file =====


	/**
	 * Clear the log.
	 */
	public function clear_log()
	{
		file_put_contents( NHS_LOG_FILE );
	}
	

	/**
	 * Write the username followed by a log line.
	 * @param  string  $username  The user's username.
	 * @param  string  $text  The line of text to insert into the log.
	 * @param  bool  $newline  True if a new line character should be inserted after the line, otherwise False.
	 */
	public function write_to_log( $username = '', $text = '', $newline = true )
	{
		$text = print_r( $text, true );
		if( $newline ) $text .= "\n";
		$text = str_pad( $username, 8, ' ', STR_PAD_RIGHT ).' : '.$text;
		file_put_contents( NHS_LOG_FILE, $text, FILE_APPEND );
	}	



//========================================================================================
//========================================================================= Sections =====
	
	
	/**
	 * Retrieve a complete list of sections from the database.
	 * @param  int  $offset  The offset of the users list.
	 * @param  int  $limit  The amount of users to retrieve.
	 * @return  array  An array of users given the filtering.
	 */
	public function get_sections( $offset = 0, $limit = NULL )
	{
		$sections = get_option( NHS_SECTIONS, array() );
		return array_slice( $sections, $offsets, $limit );
	}


	/**
	 * 
	 */
	public function get_sections_objects()
	{
		$sections = $this->get_sections();
		foreach( $sections as &$s )
		{
			$s = new NHS_Section( $s );
		}
		return $sections;
	}


	/**
	 * The amount of sections from the database.
	 * @return  int  A count of sections.
	 */
	public function get_sections_count()
	{
		$sections = $this->get_sections();
		return count($sections);
	}


	/**
	 * 
	 * @param  string  $name  The name of the section.
	 * @return  array|NULL  The section data, or NULL if not found.
	 */
	public function get_section_by_key( $key )
	{
		$sections = $this->get_sections();
		if( array_key_exists($key, $sections) )
			return $sections[$key];
		
		return NULL;
	}


	/**
	 * 
	 * @param  string  $name  The name of the section.
	 * @return  array|NULL  The section data, or NULL if not found.
	 */
	public function get_section_by_name( $name )
	{
		$sections = $this->get_sections();
		foreach( $sections as $s )
			if( $s['name'] == $name ) return $s;
		
		return NULL;
	}


	/**
	 * 
	 * @return  array  The default section data.
	 */
	public function get_default_section()
	{
		return array(
			'name'					=> '',
			'title'					=> '',
			'post_types'			=> array( 'post' ),
			'taxonomies'			=> array(),
			'featured_image'		=> 'none',
			'thumbnail_image'		=> 'none',
			'archive_page_stories'	=> 10,
			'rss_feed_stories'		=> 10,
			'priority'				=> 5,
		);
	}


	/**
	 * 
	 * @param  array  $section  
	 * @return  array  The filtered section data.
	 */
	public function filter_section( $section )
	{
		if( empty($section['post_types']) )
		{
			$section['post_types'] = array( 'post' );
		}

		if( empty($section['taxonomies']) )
		{
			$section['taxonomies'] = array();
		}

		return array_merge( $this->get_default_section(), $section );
	}


	/**
	 * 
	 */
	public function add_section( $key, $section )
	{
		$sections = $this->get_sections();
		
		$section['key'] = $key;
		$sections[$key] = $this->filter_section( $section );

		$this->update_sections( $sections );
	}


	/**
	 * 
	 */
	public function update_section( $key, $section )
	{
		$sections = $this->get_sections();
		
		if( $key !== $section['key'] )
		{
			unset( $sections[$section['key']] );
			$section['key'] = $key;
		}

		$sections[ $key ] = $this->filter_section( $section );
		$this->update_sections( $sections );
	}


	/**
	 * 
	 */
	public function remove_section( $key )
	{
		$sections = $this->get_sections();
		unset( $sections[ $key ] );

		$this->update_sections( $sections );
	}


	/**
	 * 
	 */
	public function update_sections( $sections )
	{
		global $wp_filter;
		$filters = $wp_filter['sanitize_option_'.NHS_SECTIONS];
		$wp_filter['sanitize_option_'.NHS_SECTIONS] = array();
		
		update_option( NHS_SECTIONS, $sections );

		$wp_filter['sanitize_option_'.NHS_SECTIONS] = $filters;
	}

}
endif;

