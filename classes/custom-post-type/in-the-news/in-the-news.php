<?php
/**
 * 
 */

add_action( 'init', array('NH_InTheNewsCustomPostType', 'create_custom_post') );
add_filter( 'post_updated_messages', array('NH_InTheNewsCustomPostType', 'update_messages') );
add_action( 'add_meta_boxes', array('NH_InTheNewsCustomPostType', 'info_box') );
add_action( 'save_post', array('NH_InTheNewsCustomPostType', 'info_box_save') );

add_filter( 'nh-in-the-news-featured-story', array('NH_InTheNewsCustomPostType', 'get_featured_story'), 99, 2 );
add_filter( 'nh-in-the-news-listing-story', array('NH_InTheNewsCustomPostType', 'get_listing_story'), 99, 2 );
add_filter( 'nh-in-the-news-story-title', array('NH_InTheNewsCustomPostType', 'get_title'), 99, 2 );
add_filter( 'nh-in-the-news-story-link', array('NH_InTheNewsCustomPostType', 'get_link'), 99, 2 );

add_filter( 'pre_get_posts', array('NH_InTheNewsCustomPostType', 'alter_in_the_news_query') );
add_filter( 'get_post_time', array('NH_InTheNewsCustomPostType', 'update_in_the_news_publication_date'), 9999, 3 );
add_filter( 'posts_groupby', array('NH_InTheNewsCustomPostType', 'alter_in_the_news_groupby'), 9999, 2 );
add_filter( 'posts_join', array('NH_InTheNewsCustomPostType', 'alter_in_the_news_join'), 9999, 2 );
add_filter( 'posts_orderby', array('NH_InTheNewsCustomPostType', 'alter_in_the_news_orderby'), 9999, 2 );
add_filter( 'posts_fields', array('NH_InTheNewsCustomPostType', 'alter_in_the_news_fields'), 9999, 2 );

	
class NH_InTheNewsCustomPostType
{
	/**
	 * Constructor.
	 * Private.  Class only has static members.
	 * TODO: look up PHP abstract class implementation.
	 */
	private function __construct() { }


	/**
	 * Creates the custom "In the News" post type.
	 */	
	public static function create_custom_post()
	{
		$labels = array(
			'name'               => 'In the News',
			'singular_name'      => 'In the News',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New "In the News" story',
			'edit_item'          => 'Edit "In the News" story',
			'new_item'           => 'New "In the News" story',
			'all_items'          => 'All "In the News"',
			'view_item'          => 'View "In the News" story',
			'search_items'       => 'Search "In the News" stories',
			'not_found'          => 'No "In the News" stories found',
			'not_found_in_trash' => 'No "In the News" stories found in the Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'In the News'
		);
		
		$args = array(
			'labels'        => $labels,
			'description'   => 'Holds our "In the News" stories data',
			'public'        => true,
			'menu_position' => 5,
			'supports'      => array( 'title', 'editor' ),
			'has_archive'   => true,
		);
		
		register_post_type( 'in-the-news', $args );
		
		flush_rewrite_rules();
	}
	
	
	/**
	 * Updates the messages displayed by the custom Event post type.
	 */
	public static function update_messages( $messages )
	{
		global $post, $post_ID;
		$messages['in-the-news'] = array(
			0 => '', 
			1 => sprintf( __('"In the News" story updated. <a href="%s">View "In the News" story</a>'), esc_url( get_permalink($post_ID) ) ),
			2 => __('Custom field updated.'),
			3 => __('Custom field deleted.'),
			4 => __('"In the News" story updated.'),
			5 => isset($_GET['revision']) ? sprintf( __('"In the News" story restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __('"In the News" story published. <a href="%s">View "In the News" story</a>'), esc_url( get_permalink($post_ID) ) ),
			7 => __('"In the News" story saved.'),
			8 => sprintf( __('"In the News" story submitted. <a target="_blank" href="%s">Preview "In the News" story</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __('"In the News" story scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview "In the News" story</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __('"In the News" story draft updated. <a target="_blank" href="%s">Preview "In the News" story</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);
		return $messages;
	}
	
	
	/**
	 * Sets up the custom meta box with special Event meta data tags.
	 */
	public static function info_box()
	{
		add_meta_box( 
			'in_the_news_info_box',
			'Story Info',
			array( 'NH_InTheNewsCustomPostType', 'info_box_content' ),
			'in-the-news',
			'normal',
			'high'
		);
	}
	
	
	/**
	 * Writes the HTML code used to create the contents of the Event meta box.
	 * @param WP_Post The current post being displayed.
	 */
	public static function info_box_content( $post )
	{
		wp_nonce_field( plugin_basename( __FILE__ ), 'exchange-custom-in-the-news-post' );

		$name = get_post_meta( $post->ID, 'name', true );
		$relationship = get_post_meta( $post->ID, 'relationship', true );
		$department = get_post_meta( $post->ID, 'department', true );
		$publication_date = get_post_meta( $post->ID, 'publication-date', true );
		$news_outlet = get_post_meta( $post->ID, 'news-outlet', true );
		$remote_url = get_post_meta( $post->ID, 'remote-url', true );

		?>
		<label for="clas-event-name">Name</label><br/>
		<input type="text" id="exchange-in-the-news-name" name="exchange-in-the-news-name" value="<?php echo esc_attr($name); ?>" size="60" /><br/>
		<label for="clas-event-relationship">Relationship</label><br/>
		<input type="text" id="exchange-in-the-news-relationship" name="exchange-in-the-news-relationship" value="<?php echo esc_attr($relationship); ?>" size="60" /><br/>
		<label for="clas-event-department">Department</label><br/>
		<input type="text" id="exchange-in-the-news-department" name="exchange-in-the-news-department" value="<?php echo esc_attr($department); ?>" size="60" /><br/>
		<div style="height:1px;background-color:#aaa;margin:5px 0px;"></div>
		<label for="clas-event-publication-date">Publication Date</label><br/>
		<input type="text" id="exchange-in-the-news-publication-date" name="exchange-in-the-news-publication-date" value="<?php echo esc_attr($publication_date); ?>" size="32" /><br/>
		<label for="clas-event-news-outlet">News Outlet</label><br/>
		<input type="text" id="exchange-in-the-news-news-outlet" name="exchange-in-the-news-news-outlet" value="<?php echo esc_attr($news_outlet); ?>" size="60" /><br/>
		<div style="height:1px;background-color:#aaa;margin:5px 0px;"></div>
		<label for="clas-event-remote-url">Remote URL</label><br/>
		<input type="text" id="exchange-in-the-news-remote-url" name="exchange-in-the-news-remote-url" value="<?php echo esc_attr($remote_url); ?>" size="100" />
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
		
		if ( !isset($_POST) || !isset($_POST['exchange-custom-in-the-news-post']) )
		return;
		
		if ( !wp_verify_nonce( $_POST['exchange-custom-in-the-news-post'], plugin_basename( __FILE__ ) ) )
		return;
		
		if ( !current_user_can( 'edit_page', $post_id ) )
		return;

		$name = $_POST['exchange-in-the-news-name'];
		$relationship = $_POST['exchange-in-the-news-relationship'];
		$department = $_POST['exchange-in-the-news-department'];
		$publication_date = $_POST['exchange-in-the-news-publication-date'];
		$news_outlet = $_POST['exchange-in-the-news-news-outlet'];
		$remote_url = $_POST['exchange-in-the-news-remote-url'];

		update_post_meta( $post_id, 'name', $name );
		update_post_meta( $post_id, 'relationship', $relationship );
		update_post_meta( $post_id, 'department', $department );
		update_post_meta( $post_id, 'publication-date', $publication_date );
		update_post_meta( $post_id, 'news-outlet', $news_outlet );
		update_post_meta( $post_id, 'remote-url', $remote_url );
		
		// Update post content.

		$pub_date = DateTime::createFromFormat( 'Y-m-d', $publication_date );
		$publication_date = $pub_date->format('F d, Y');
		$news_title = get_the_title($post_id);
		$contents = "$name, $relationship. $department. $publication_date. <em>$news_outlet</em>. \"$news_title\".";
		
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare( 
				"
				UPDATE $wpdb->posts
				SET post_content = %s
				WHERE ID = %d
				",
				$contents,
				$post_id
			)
		);

	}
	
	public static function get_featured_story( $story, $post )
	{
		unset($story['description']['excerpt']);
		return $story;
	}

	public static function get_listing_story( $story, $post )
	{
		unset($story['description']['excerpt']);
		return $story;
	}
	
	public static function get_title( $title, $post )
	{
		return $post->post_content;
	}

	public static function get_link( $link, $post )
	{
		return get_post_meta( $post->ID, 'remote-url', true );
	}


	/**
	 * Alters the default query made when querying In The News items.
	 */
	public static function alter_in_the_news_query( $wp_query )
	{
		if( !isset($wp_query->query['post_type']) ) return;
		if( is_string($wp_query->query['post_type']) && $wp_query->query['post_type'] !== 'in-the-news' ) return;
		if( is_array($wp_query->query['post_type']) && $wp_query->query['post_type'] !== array('in-the-news') ) return;
		
		if( is_category('news') && !isset($wp_query->query_vars['section']) )
		{
			$wp_query->query_vars['posts_per_page'] = 20;
		}		
	}

	/**
	 * 
	 */
	public static function update_in_the_news_publication_date( $time, $d, $gmt )
	{
		global $post;

		if( is_feed() && $post->post_type === 'in-the-news' )
		{
			$datetime = get_post_meta( $post->ID, 'publication-date', true );
		
			if( $datetime != '' ) $time = $datetime;
		}
	
		return $time;
	}

	/**
	 * 
	 */
	public static function alter_in_the_news_groupby( $groupby, $wp_query )
	{
		if( !isset($wp_query->query['post_type']) ) return $groupby;
		if( $wp_query->query['post_type'] !== 'in-the-news' && $wp_query->query['post_type'] !== array('in-the-news') ) return $groupby;

		global $wpdb;
		$groupby = "$wpdb->posts.ID";
		return $groupby;
	}

	/**
	 * 
	 */
	public static function alter_in_the_news_join( $join, $wp_query )
	{
		if( !isset($wp_query->query['post_type']) ) return $join;
		if( $wp_query->query['post_type'] !== 'in-the-news' && $wp_query->query['post_type'] !== array('in-the-news') ) return $join;

		global $wpdb;
		$join = "LEFT JOIN (SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = 'publication-date') ttable ON ($wpdb->posts.ID = ttable.post_id)";
		return $join;
	}

	/**
	 * 
	 */
	public static function alter_in_the_news_orderby( $orderby, $wp_query )
	{
		if( !isset($wp_query->query['post_type']) ) return $orderby;
		if( $wp_query->query['post_type'] !== 'in-the-news' && $wp_query->query['post_type'] !== array('in-the-news') ) return $orderby;

		global $wpdb;
		$orderby = "ISNULL(publication_date) ASC, publication_date DESC, post_date DESC";
		return $orderby;
	}

	/**
	 * 
	 */
	public static function alter_in_the_news_fields( $fields, $wp_query )
	{
		if( !isset($wp_query->query['post_type']) ) return $fields;
		if( $wp_query->query['post_type'] !== 'in-the-news' && $wp_query->query['post_type'] !== array('in-the-news') ) return $fields;

		global $wpdb;
		$fields = "$wpdb->posts.*, ttable.meta_value AS publication_date";
		return $fields;
	}

}

