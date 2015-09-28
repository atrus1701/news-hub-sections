<?php

add_action( 'widgets_init',
     create_function( '', 'return register_widget("NH_TodaysEventsWidget");' )
);

if( !class_exists('NH_TodaysEventsWidget') ):
class NH_TodaysEventsWidget extends WP_Widget
{

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct()
	{
		// widget actual processes
		
		parent::__construct(
			'nh_todays_events_widget', // Base ID
			__("Today's Events", 'text_domain'), // Name
			array( 
				'description' => __( 'Displays events occuring today.', 'text_domain' ), 
			) // Args
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance )
	{
		global $nh_config;

		echo $args['before_widget'];

		if( !empty($instance['title']) ):
			echo $args['before_title'].$instance['title'].$args['after_title'];
		endif;

		$events = nh_event_get_todays_events();
		$event_section = $nh_config->get_section_by_key( 'events' );
		
		//nh_print($events);
					
		if( count($events) == 0 ):
		
			?>
			<div class="no-events">No events today.</div>
			<?php

		else:
			foreach( $events as $event ):

				?>					
				<div class="story event-section none-image clearfix">
					<?php echo nhs_get_anchor( $event['link'], $event['title'] ); ?>

					<div class="details clearfix">
						<h3><?php echo $event['title']; ?></h3>
						<div class="description">
							<div class="datetime"><?php echo $event['description']['datetime']; ?></div>
							<div class="location"><?php echo $event['description']['location']; ?></div>
						</div><!-- .contents -->
					</div><!-- .description -->

					</a>
				</div><!-- .story -->
				<?php

			endforeach;
		endif;

		?>		
		<div class="more">
			<?php echo nhs_get_anchor( $event_section->get_section_link(), 'More Events', null, 'More <em>Events</em> &raquo;' ); ?>
		</div>
		
		<?php
		echo $args['after_widget'];
	}

	/**
	 * Ouputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance )
	{
		// outputs the options form on admin

		//nh_print('options of the widget');
		
		if ( isset($instance[ 'title' ]) )
			$title = $instance[ 'title' ];
		else
			$title = __( "Today's Events", 'text_domain' );
		?>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance )
	{
		// processes widget options to be saved
		
		//nh_print($new_instance);
		//nh_print($old_instance);
		
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;		
	}

}




function nh_event_get_todays_events()
{
	global $nh_config, $wpdb;
	
	$events = array();
	$todays_date = $nh_config->get_todays_datetime()->format('Y-m-d');
	
	$args = array(
		'posts_per_page' => -1,
		'post_type' => 'event',
		'meta_key' => 'datetime',
		'orderby' => 'meta_value',
		'order' => 'ASC',
		'meta_query' => array(
			array(
				'key' => 'datetime',
				'compare' => '>=',
				'value' => "$todays_date 00:00:00",
			),
			array(
				'key' => 'datetime',
				'compare' => '<=',
				'value' => "$todays_date 23:59:59",
			)
		),
		'post_status' => 'publish',
	);
	
	$event_query = new WP_Query( $args );
	
	if( $event_query->have_posts() )
	{
		$event_section = $nh_config->get_section_by_key( 'events' );
		
		while( $event_query->have_posts() )
		{
			$event_query->next_post();
			$events[] = $event_section->get_featured_story( $event_query->post );
		}
	}
	
	wp_reset_postdata();
	
	return $events;
}
endif;


