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
	 * Enqueues the scripts or styles needed for the control in the site frontend.
	 */
	public function enqueue_scripts()
	{

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
		?>
		
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
		$defaults['items'] = 2;
		
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
		$options['widget_name'] = $options['section'];
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
		
		$stories = $section->get_stories( $items, 'featured', TRUE );

		$template_section_list = apply_filters( 'nhs-template-section-list', NHS_PLUGIN_PATH.'/template-section-list.php', $section );

		$nhs_section = $section;
		$nhs_stories = $stories;
		include( $template_section_list );
	
		echo '</div>';
		echo $args['after_widget'];		
	}
}
endif;

