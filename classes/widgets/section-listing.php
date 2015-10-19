<?php

require_once( __DIR__.'/widget-shortcode-control.php' );
require_once( NHS_PLUGIN_PATH.'/classes/model.php' );


/**
 * The NHS_WidgetSectionListingControl class for the "UNC Charlotte News Hub" theme.
 * 
 * Shortcode Example:
 * [section_listing name="section_name" items="2"]
 * 
 * @package    unc-charlotte-news-hub-theme
 * @subpackage classes/widgets
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('NHS_WidgetSectionListingControl') ):
class NHS_WidgetSectionListingControl extends WidgetShortcodeControl
{
	/**
	 * The minimum number of Section listing items.
	 * @var  int
	 */	
	private static $MIN_ITEMS = 1;

	/**
	 * The maximum number of Section listing items.
	 * @var  int
	 */
	private static $MAX_ITEMS = 10;

	/**
	 * The main model for the UNC Charlotte News Hub theme.
	 * @var  NHS_Model
	 */	
	private $model = null;	
	

	/**
	 * Constructor.
	 * Setup the properties and actions.
	 */
	public function __construct()
	{
		$widget_ops = array(
			'description'	=> 'Add the listing for a section.',
		);
		
		parent::__construct( 'section_listing', 'Section Listing', $widget_ops );
		$this->model = NHS_Model::get_instance();
	}
	
	
	/**
	 * Enqueues the scripts or styles needed for the control in the site admin.
	 */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_script( 'section-listing', NHS_PLUGIN_URL.'/classes/widgets/section-listing.js' );
	}
	

	/**
	 * Update a particular instance.
	 * Override function from WP_Widget parent class.
	 * @param  array  $new_options  New options set in the widget form by the user.
	 * @param  array  $old_options  Old options from the database.
	 * @return  array|bool  The settings to save, or false to cancel saving.
	 */
	public function update( $new_options, $old_options )
	{
		$new_options['title'] = $new_options['section'];
		return $new_options;
	}


	/**
	 * Output the widget form in the admin.
	 * Use this function instead of form.
	 * @param   array   $options  The current settings for the widget.
	 */
	public function print_widget_form( $options )
	{
		$options = $this->merge_options( $options );
		extract( $options );

		$sections = $this->model->get_sections();
	 	$section_posts = array();
		if( array_key_exists($section, $sections) )
		{
			$s = new NHS_Section( $sections[$section] );
			$section_posts = $s->get_post_list(0, 20);
		}

		for( $i = count($posts); $i < $items; $i++ )
		{
			$posts[] = -1;
		}

		?>

		<input type="hidden" class="section-data" value="<?php echo $section.','.$items; ?>" />
		
		<p>
		<label for="<?php echo $this->get_field_id( 'section' ); ?>"><?php _e( 'Section:' ); ?></label> 
		<br/>
		<select name="<?php echo $this->get_field_name( 'section' ); ?>">
		<?php foreach( $sections as $s ): ?>
			<option value="<?php echo $s['key']; ?>"
				<?php selected( $section, $s['key'] ); ?>>
			<?php echo $s['name']; ?>
			</option>
		<?php endforeach; ?>
		</select>
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'items' ); ?>"><?php _e( 'Number of items:' ); ?></label> 
		<select id="<?php echo $this->get_field_id( 'items' ); ?>" name="<?php echo $this->get_field_name( 'items' ); ?>">
			<?php for( $i = self::$MIN_ITEMS; $i < self::$MAX_ITEMS+1; $i++ ): ?>
			
				<option value="<?php echo $i; ?>" <?php selected($i, $items); ?>><?php echo $i; ?></option>
			
			<?php endfor; ?>
		</select>
		</p>

		<p><button>Update</button></p>

		<div class="post-list">

		<?php for( $i = 0; $i < $items; $i++ ): ?>
		
			<p>
			<select id="<?php echo $this->get_field_id( 'post-'.($i+1) ); ?>" name="<?php echo $this->get_field_name( 'posts' ).'['.$i.']'; ?>">
				<option value="-1" <?php selected(-1, $posts[$i]); ?>>-- Latest Post --</option>
				<?php foreach( $section_posts as $p ): ?>
				
					<option value="<?php echo $p->ID; ?>" <?php selected($posts[$i], $p->ID); ?>><?php echo $p->post_title; ?></option>
				
				<?php endforeach; ?>
			</select>
			</p>

		<?php endfor; ?>

		</div>

		<?php
	}
	
	
	/**
	 * Get the default settings for the widget or shortcode.
	 * @return  array  The default settings.
	 */
	public function get_default_options()
	{
		$defaults = array();

		$section = $this->model->get_sections( 0, 1 );
		$keys = array_keys($section);

		$defaults['section'] = ( count($section) > 0 ? $section[$keys[0]]['key'] : '' );
		$defaults['title'] = $defaults['section'];
		$defaults['items'] = 2;
		$defaults['posts'] = array();

		return $defaults;
	}
	
	
	/**
	 * Process options from the database or shortcode.
	 * Designed to convert options from strings or sanitize output.
	 * @param   array   $options  The current settings for the widget or shortcode.
	 * @return  array   The processed settings.
	 */
	public function process_options( $options )
	{
		$options['title'] = $options['section'];
		return $options;
	}
	

	/**
	 * Echo the widget or shortcode contents.
	 * @param   array  $options  The current settings for the control.
	 * @param   array  $args     The display arguments.
	 */
	public function print_control( $options, $args = null )
	{
		global $nhs_section, $nhs_stories;

		$options = $this->merge_options( $options );
		if( !$args ) $args = $this->get_args();
		
		extract( $options );

		$section = $this->model->get_section_by_key( $section );
		if( empty($section) ) return;

		$section = new NHS_Section( $section );
		
		echo $args['before_widget'];
		echo '<div id="section-listing-control-'.self::$index.'" class="wscontrol section-listing-control">';
		
		$most_recent_posts = $section->get_post_list( 0, $items, $posts );
		$story_posts = array_fill( 0, $items, NULL );
		
		$j = 0;
		for( $i = 0; $i < $items; $i++ )
		{
			if( $posts[$i] !== -1 )
			{
				$story_posts[$i] = get_post( $posts[$i] );
			}

			if( $story_posts[$i] === NULL && count($most_recent_posts) > $j )
			{
				$story_posts[$i] = $most_recent_posts[$j];
				$j++;
			}
		}

		array_filter( $story_posts );
		$section->process_stories( $story_posts, 'featured' );

		$template_section_list = apply_filters( 'nhs-template-section-list', NHS_PLUGIN_PATH.'/template-section-list.php', $section );

		$nhs_section = $section;
		$nhs_stories = $story_posts;
		include( $template_section_list );
	
		echo '</div>';
		echo $args['after_widget'];		
	}
}
endif;

