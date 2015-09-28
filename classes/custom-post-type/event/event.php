<?php
/**
 * 
 */

//========================================================================================
//============================================================ Event Post Type files =====

require_once( UNHT_THEME_PATH.'/classes/custom-post-type/event/functions.php' );
require_once( UNHT_THEME_PATH.'/classes/custom-post-type/event/todays-events-widget.php' );


//========================================================================================
//============================================== Event Post Type filters and actions =====

add_action( 'init', array('UNHT_CustomEventPostType', 'create_custom_post') );
add_filter( 'post_updated_messages', array('UNHT_CustomEventPostType', 'update_messages') );
add_action( 'add_meta_boxes', array('UNHT_CustomEventPostType', 'info_box') );
add_action( 'save_post', array('UNHT_CustomEventPostType', 'info_box_save') );

add_filter( 'pre_get_posts', array('UNHT_CustomEventPostType', 'alter_event_query') );
add_filter( 'posts_where', array('UNHT_CustomEventPostType', 'alter_event_where'), 9999, 2 );
add_filter( 'get_post_time', array('UNHT_CustomEventPostType', 'update_event_publication_date'), 9999, 3 );

add_filter( 'manage_edit-event_columns', array( 'UNHT_CustomEventPostType', 'all_columns_key' ) );
add_filter( 'manage_edit-event_sortable_columns', array( 'UNHT_CustomEventPostType', 'all_sortable_columns_key' ) );
add_action( 'manage_event_posts_custom_column', array( 'UNHT_CustomEventPostType', 'all_columns_value' ), 10, 2 );

//========================================================================================
//======================================================= Event Post Type definition =====

class UNHT_CustomEventPostType
{
	/**
	 * Constructor.
	 * Private.  Class only has static members.
	 * TODO: look up PHP abstract class implementation.
	 */
	private function __construct() { }


	/**
	 * Creates the custom Event post type.
	 */	
	public static function create_custom_post()
	{
		$labels = array(
			'name'               => 'Events',
			'singular_name'      => 'Event',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Event',
			'edit_item'          => 'Edit Event',
			'new_item'           => 'New Event',
			'all_items'          => 'All Events',
			'view_item'          => 'View Event',
			'search_items'       => 'Search Events',
			'not_found'          => 'No events found',
			'not_found_in_trash' => 'No events found in the Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Events'
		);
		
		$args = array(
			'labels'        => $labels,
			'description'   => 'Holds our events and event specific data',
			'public'        => true,
			'menu_position' => 5,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'taxonomies'	=> array(),
			'has_archive'   => true,
		);
		
		register_post_type( 'event', $args );

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Event Categories',
			'singular_name'     => 'Event Category',
			'search_items'      => "Search Event Categories",
			'all_items'         => "All Event Categories",
			'parent_item'       => "Parent Event Category",
			'parent_item_colon' => "Parent Event Category:",
			'edit_item'         => "Edit Event Category",
			'update_item'       => "Update Event Category",
			'add_new_item'      => "Add New Event Category",
			'new_item_name'     => "New Event Category Name",
			'menu_name'         => 'Event Categories',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'event-category' ),
		);

		register_taxonomy( 'event-category', 'event', $args );		
		
		flush_rewrite_rules();
	}
	
	
	/**
	 * Updates the messages displayed by the custom Event post type.
	 */
	public static function update_messages( $messages )
	{
		global $post, $post_ID;
		$messages['event'] = array(
			0 => '', 
			1 => sprintf( __('Event updated. <a href="%s">View event</a>'), esc_url( get_permalink($post_ID) ) ),
			2 => __('Custom field updated.'),
			3 => __('Custom field deleted.'),
			4 => __('Event updated.'),
			5 => isset($_GET['revision']) ? sprintf( __('Event restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __('Event published. <a href="%s">View event</a>'), esc_url( get_permalink($post_ID) ) ),
			7 => __('Event saved.'),
			8 => sprintf( __('Event submitted. <a target="_blank" href="%s">Preview event</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __('Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __('Event draft updated. <a target="_blank" href="%s">Preview event</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);
		return $messages;
	}
	
	
	/**
	 * Sets up the custom meta box with special Event meta data tags.
	 */
	public static function info_box()
	{
		add_meta_box( 
			'event_info_box',
			'Event Info',
			array( 'UNHT_CustomEventPostType', 'info_box_content' ),
			'event',
			'side',
			'high'
		);
	}
	
	
	/**
	 * Writes the HTML code used to create the contents of the Event meta box.
	 * @param WP_Post The current post being displayed.
	 */
	public static function info_box_content( $post )
	{
		wp_nonce_field( plugin_basename( __FILE__ ), 'nh-custom-event-post' );

		$datetime = get_post_meta( $post->ID, 'datetime', true );
		if( !empty($datetime) )
		{
			$datetime = DateTime::createFromFormat( 'Y-m-d H:i:s', $datetime );
			$date = $datetime->format('Y-m-d');
			$time = $datetime->format('h:i A');
		}
		
		$location = get_post_meta( $post->ID, 'location', true );

		?>
		<label for="nh-event-date">Date</label><br/>
		<input type="text" id="nh-event-date" name="nh-event-date" value="<?php echo esc_attr($date); ?>" size="32" /><br/>
		<label for="nh-event-time">Time</label><br/>
		<input type="text" id="nh-event-time" name="nh-event-time" value="<?php echo esc_attr($time); ?>" size="32" /><br/>
		<label for="nh-event-location">Location</label><br/>
		<input type="text" id="nh-event-location" name="nh-event-location" value="<?php echo esc_attr($location); ?>" size="32" /><br/>
		<?php
	}
	
	
	/**
	 * Saves the Event's custom meta data.
	 * @param int The current post's id.
	 */
	public static function info_box_save( $post_id )
	{
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;
		
		if ( !isset($_POST) || !isset($_POST['nh-custom-event-post']) )
		return;
		
		if ( !wp_verify_nonce( $_POST['nh-custom-event-post'], plugin_basename( __FILE__ ) ) )
		return;
		
		if ( !current_user_can( 'edit_page', $post_id ) )
		return;

		$datetime = DateTime::createFromFormat( 'Y-m-d h:i A', $_POST['nh-event-date'].' '.$_POST['nh-event-time'] );
		if( $datetime )
			update_post_meta( $post_id, 'datetime', $datetime->format('Y-m-d H:i:s') );
		else
			update_post_meta( $post_id, 'datetime', $datetime->format('Y-m-d H:i:s') );

		$location = $_POST['nh-event-location'];
		update_post_meta( $post_id, 'location', $location );
	}
	
	
	
	public static function get_datetime( $post_id )
	{
		$datetime = nh_event_get_datetime( $post_id );
		if( $datetime === null )
		{
			$date = 'No date provided.';
			$time = '';
			$datetime = null;
 		}
		else
		{
			$date = $datetime->format('F d, Y');
			$time = $datetime->format('g:i A');
		}
		
		return array( 'datetime' => $datetime, 'date' => $date, 'time' => $time );
	}

	public static function get_location( $post_id )
	{
		return get_post_meta( $post_id, 'location', true );
	}
	
	public static function get_events_datetime()
	{
		$todays_date = vtt_get_current_datetime();

		$event_date = ( !empty($_GET['event-date']) ? $_GET['event-date'] : 0 );
		$start_datetime = NULL; $end_datetime = NULL;
		$month = 0; $year = 0;
		
		if( $event_date != 0 )
		{
			$matches = NULL;
			$num_matches = preg_match("/(([0-9]{1,2})-)?([0-9]{4})/", $event_date, $matches);

			if( $num_matches != FALSE )
			{
				$month = ( $matches[2] ? $matches[2] : 0 );
				$year = $matches[3];
				
				$month = ( ($month > 0) && ($month < 13) ? intval($month) : 0 );
				$year = ( ($year >= 1900) && ($year <= 9999) ? intval($year) : 0 );
				
				if( $year == 0 ) $year = $todays_date->format('Y');

				if( $month == 0 ) 
				{
					$start_datetime = new DateTime("$year-01-01");
					$end_datetime = new DateTime("$year-01-01");
					$end_datetime->add( new DateInterval('P1Y') );
				}
				else
				{
					$start_datetime = new DateTime("$year-$month-01");
					$end_datetime = new DateTime("$year-$month-01");
					$end_datetime->add( new DateInterval('P1M') );
				}
			}
		}
		
		if( !$start_datetime )
		{
			$start_datetime = new DateTime( $todays_date->format('Y-m-d') );
			$end_datetime = new DateTime( $todays_date->format('Y-m-d') );
			$end_datetime->add( new DateInterval('P1M') );
		}
		
		return array(
			'month' => $month,
			'year' => $year,
			'start' => $start_datetime,
			'end' => $end_datetime,
		);
	}
	
	public static function alter_event_where( $where , $wp_query )
	{
		if( is_admin() ) return $where;
		if( !$wp_query->is_main_query() ) return $where;
		if( $wp_query->is_single() ) return $where;

		$section = unccnh_get_section( $wp_query );
		if( $section->key !== 'events' ) return $where;
		
		list( $month, $year, $start_datetime, $end_datetime ) = array_values( UNHT_CustomEventPostType::get_events_datetime() );
		
		if( !empty($where) )
			$where .= " AND ";
		$where .= " meta_value >= '" . $start_datetime->format('Y-m-d') . " 00:00:00'";
		
		if( !is_feed() )
			$where .= " AND meta_value < '" . $end_datetime->format('Y-m-d') . " 00:00:00'";
		
		return $where;
	}
	
	
	public static function alter_event_query( $wp_query )
	{
		if( $wp_query->is_single() ) return;
	
		$section = unccnh_get_section( $wp_query );
		if( $section->key !== 'events' ) return;
	
		$todays_date = vtt_get_current_datetime();

		if( is_admin() )
 		{
 			if( !$wp_query->is_main_query() ) return;

 			$screen = get_current_screen();
 			if( $screen->base != 'edit' ) return;

			$wp_query->set( 'posts_per_page', get_user_option('edit_event_per_page') );

 			if( ($wp_query->get('orderby')) && ($wp_query->get('orderby') != 'datetime') ) return;
 			
 			$wp_query->set( 'meta_key', 'datetime' );
			$wp_query->set( 'orderby', 'meta_value' );
 			return;
 		}

		$wp_query->set( 'meta_key', 'datetime' );
		$wp_query->set( 'orderby', 'meta_value' );
		$wp_query->set( 'order', 'ASC' );

		if( $wp_query->is_main_query() ) return;
				
		$datetime_meta_found = false;
		$current_meta_query = $wp_query->get( 'meta_query' );
		if( is_array($current_meta_query) )
		{
			foreach( $current_meta_query as $mq )
			{
				if( isset($mq['key']) && $mq['key'] == 'datetime' )
					$datetime_meta_found = true;
			}
		}
		if( $datetime_meta_found ) return;

		$start_datetime = new DateTime( $todays_date->format('Y-m-d') );
		$end_datetime = new DateTime( $todays_date->format('Y-m-d') );
		$end_datetime->add( new DateInterval('P1M') );

		$wp_query->set( 'meta_query', array(
				array(
					'key'     => 'datetime',
					'value'   => $start_datetime->format('Y-m-d') . " 00:00:00",
					'compare' => '>=',
				),
			)
		);
	}
	
	
	public static function update_event_publication_date( $time, $d, $gmt )
	{
		global $post;

		if( is_feed() && $post->post_type === 'event' )
		{
			$datetime = get_post_meta( $post->ID, 'datetime', true );
		
			if( $datetime != '' ) $time = $datetime;
		}
	
		return $time;
	}
	
	
		/**
	 * 
	 */
	public static function all_columns_key( $columns )
	{
		unset($columns['categories']);
		unset($columns['tags']);
		unset($columns['date']);

		$columns['location'] = 'Location';
		$columns['datetime'] = 'Date and Time';

		return $columns;
	}
	
	
	public static function all_sortable_columns_key( $columns )
	{
		$columns['datetime'] = array( 'datetime', false );
		
		unset($columns['categories']);
		unset($columns['tags']);
		unset($columns['date']);
		
		return $columns;
	}
	

	/**
	 * 
	 */
	public static function all_columns_value( $column_name, $post_id )
	{
		switch( $column_name )
		{
			case 'location':
				$location = get_post_meta( $post_id, 'location', true );
				if( $location )
					echo $location;
				else
					echo 'No location specified.';
				break;
				
			case 'datetime':
				$datetime = get_post_meta( $post_id, 'datetime', true );
				if( $datetime )
				{
					$dt = new DateTime( $datetime );
					echo $dt->format( 'F d, Y h:m A' );
				}
				else
				{
					echo 'No datetime specified.';
				}
				break;
				
			default:
				echo '';
				break;
		}
	}
	
}


