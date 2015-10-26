<?php

/**
 * 
 * 
 * @package    unc-charlotte-news-hub-theme
 * @subpackage classes
 * @author     Crystal Barton <atrus1701@gmail.com>
 * @version    1.0
 */
class NHS_Section
{
	
	/**
	 * 
	 * @var  string
	 */
	public $key;
	
	/**
	 * 
	 * @var  string
	 */
	public $name;
	
	/**
	 * 
	 * @var  string
	 */
	public $title;
	
	/**
	 * 
	 * @var  array
	 */
	public $post_types;
	
	/**
	 * 
	 * @var  array
	 */
	public $taxonomies;
	
	/**
	 * 
	 * @var  string
	 */
	public $featured_image;
	
	/**
	 * 
	 * @var  string
	 */
	public $thumbnail_image;

	/**
	 * 
	 * @var  int
	 */
	public $archive_page_stories;

	/**
	 * 
	 * @var  int
	 */
	public $rss_feed_stories;
	

	/**
	 * 
	 * @var  int
	 */
	public $priority;

	
	/**
	 * Default Constructor.
	 * Setup the section's data.
	 */
	public function __construct( $section )
	{
		foreach( $section as $key => $value )
		{
			$this->$key = $value;
		}

		if( isset($section['post_types']) )
		{
			if( is_array($section['post_types']) )
				$this->post_types = $section['post_types'];
			else
				$this->post_types = array_unique( array_filter( explode(',',$section['post_types']) ) );
			if( count($this->post_types) == 0 ) $this->post_types = array( 'post' );
		}
		else
		{
			$this->post_types = array( 'post' );
		}
		
		$this->taxonomies = array();
		if( isset($section['taxonomies']) )
		{
			if( !is_array($section['taxonomies']) )
			{
				$section['taxonomies'] = array_filter( explode(',', $section['taxonomies']) );
			}
			
			foreach( $section['taxonomies'] as $taxname )
			{
				if( isset($section[$taxname]) )
				{
					if( !is_array($section[$taxname]) )
					{
						$section[$taxname] = array_unique( 
							array_filter(
								explode(',', $section[$taxname])
							)
						);
					}
					$this->taxonomies[$taxname] = $section[$taxname];
				}
				else
				{
					$this->taxonomies[$taxname] = array();
				}
			}
		}
		
		foreach( $this->taxonomies as $taxname => $terms )
		{
			if( count($terms) == 0 ) unset($this->taxonomies[$taxname]);
		}

		$this->archive_page_stories = $section['archive_page_stories'];
		$this->rss_feed_stories = $section['rss_feed_stories'];

		$this->priority = ( isset($section['priority']) ? intval($section['priority']) : 5 );
	}
	
	/**
	 * Get a single post.
	 * @param  $offset  int  The offset of the post.
	 * @param  $omit_ids  array  A list of ids to include omit.
	 * @return  WP_Post|null  The requested post or null if not found.
	 */
	public function get_post( $offset = 0, $omit_ids = array() )
	{
		$posts = $this->get_post_list( $offset, $omit_ids );
		if( count($posts) > 0 ) return $posts[0];
		return null;
	}
	
	
	/**
	 * Get a list of posts.
	 * @param	$offset		int				The offset of the post.
	 * @param	$limit		int				The number of posts to retrieve.
	 * @param	$omit_ids	array			A list of ids to include omit.
	 * @return				array			A array of WP_Post objects.
	 */
	public function get_post_list( $offset = 0, $limit = 10, $omit_ids = array() )
	{
		$posts = array();
		
		$args = array(
			'posts_per_page' => $limit,
			'post_type' => $this->post_types,
			'post_status' => 'publish',
			'offset' => $offset,
			'post__not_in' => $omit_ids,
			'tax_query' => $this->create_tax_query(),
			'section' => $this
		);

		$query = new WP_Query( $args );
		
		if( $query->have_posts() )
			$posts = $query->get_posts();
		
		wp_reset_postdata();
		
		return $posts;
	}
	
	
	/**
	 * 
	 */
	private function create_tax_query()
	{
		$count = 0;
		$tax_query = array();
		foreach( $this->taxonomies as $taxname => $terms )
		{
			if( count($terms) > 0 )
			{
				$count++;
				array_push(
					$tax_query,
					array(
						'taxonomy' => $taxname,
						'field' => 'slug',
						'terms' => $terms,
						'operator' => 'IN',
					)
				);
			}
		}
		if( $count > 1 )
		{
			$tax_query['relation'] = 'OR';
		}
		
		return $tax_query;
	}
	
	
	/**
	 * 
	 */
	public function get_stories( $num_of_stories, $type = 'featured', $post_process = TRUE )
	{
		$story_posts = $this->get_post_list( 0, $num_of_stories );
		
		if( $post_process ) $this->process_stories( $story_posts );
		
		return $story_posts;
	}


	public function process_stories( &$story_posts, $type = 'featured' )
	{
		switch( $type )
		{
			case 'featured':
			case 'front-page':
			case 'sidebar':
				foreach( $story_posts as &$sp )
				{
					$sp = $this->get_featured_story( $sp );
				}
				break;

			case 'listing':
				foreach( $story_posts as &$sp )
				{
					$sp = $this->get_listing_story( $sp );
				}
				break;

			case 'rss-feed':
				foreach( $story_posts as &$sp )
				{
					$sp = $this->get_rss_story( $sp );
				}
				break;
		}
	}


	/**
	 * 
	 */
	 public function get_section_link()
	 {
	 	$link = null;
		
		if( count($this->post_types) == 1 )
		{
			switch( count($this->taxonomies) )
			{
				case 0:
					// post page
					if( $this->post_types[0] != 'post' )
						$link = get_site_url().'/'.$this->post_types[0];
					break;
					
				case 1:
					// taxonomy page?
					foreach( $this->taxonomies as $tn => $tax )
					{
						$taxname = $tn;
						break;
					}
					if( count($this->taxonomies[$taxname]) == 1 )
					{
						$link = get_term_link( $this->taxonomies[$taxname][0], $taxname );
						if( is_wp_error($link) ) $link = '';
					}
					break;
			}
		}
		
		return apply_filters( 'section-link', $link, $this );
	}
	

	/**
	 * 
	 */
	private function apply_filters( $name, $story, $post )
	{
		$story = apply_filters( 'nhs-'.$name, $story, $post );
		$story = apply_filters( 'nhs-'.$this->key.'-'.$name, $story, $post );
		
		if( is_a($post, 'WP_Post') )
		$story = apply_filters( 'nhs-'.$post->post_type.'-'.$name, $story, $post );
		
		return $story;
	}
	
	
	/**
	 * 
	 */
	public function get_featured_story( $post )
	{
		if( empty($post) ) return null;
		
		$story = array();
		$story['title'] = $this->get_title( $post );
		$story['link'] = $this->get_link( $post );
		$story['target'] = $this->get_link_target( $post, $story['link'] );
		$story['byline'] = $this->get_byline( $post );
		
		if( $this->thumbnail_image == 'embed' )
			$story['embed'] = $this->get_embed_code( $post->post_content );
		else
			$story['image'] = $this->get_image( $post->ID, 'medium' );
		
		$story['description'] = array();
		$story['description']['excerpt'] = $this->get_excerpt( $post );
		
		$post->nhs_data = $this->apply_filters( 'featured-story', $story, $post );
		return $post;
	}
	
	
	/**
	 * 
	 */
	public function get_listing_story( $post )
	{
		if( empty($post) ) return null;
	
		$story = array();
		$story['title'] = $this->get_title( $post );
		$story['link'] = $this->get_link( $post );
		$story['target'] = $this->get_link_target( $post, $story['link'] );
		$story['byline'] = $this->get_byline( $post );
		
		if( $this->thumbnail_image == 'embed' )
			$story['embed'] = $this->get_embed_code( $post->post_content );
		else
			$story['image'] = $this->get_image( $post->ID, 'medium' );

		$story['description'] = array();
		$story['description']['excerpt'] = $this->get_excerpt( $post );

		$post->nhs_data = $this->apply_filters( 'listing-story', $story, $post );
		return $post;
	}
	
	
	/**
	 * 
	 */
	public function get_rss_story( $post )
	{
		$story = $post;
		
		$post->nhs_data = $this->apply_filters( 'rss-story', $story, $post );
		return $post;
	}
	
	
	/**
	 * 
	 */
	public function get_single_story( $post )
	{
		$story = array();
		$story['title'] = $this->get_title( $post );
		$story['image'] = $this->get_image( $post->ID, 'full' );
		$story['byline'] = $this->get_byline( $post );
		$story['description'] = array();
		$story['description']['text'] = $this->get_content( $post );

		$post->nhs_data = $this->apply_filters( 'single-story', $story, $post );
		return $post;
	}
	
	
	/**
	 * 
	 */
	public function get_title( $post )
	{
		$title = $post->post_title;
		return $this->apply_filters( 'story-title', $title, $post );
	}
	
	
	/**
	 * 
	 */
	public function get_link( $post )
	{
		$link = get_permalink( $post->ID );
		return $this->apply_filters( 'story-link', $link, $post );
	}
	
	
	/**
	 * 
	 */
	public function get_link_target( $post, $link )
	{
		$target = '';
		return $this->apply_filters( 'story-link-target', $target, $link );
		return $target;
	}
	

	/**
	 * 
	 */
	public function get_byline( $post )
	{
		$date = date( 'F d, Y', strtotime($post->post_modified) );
		
		$author = get_the_author_meta( 'display_name', $post->post_author );
		$url = get_author_posts_url( $post->post_author );
		
		return $date.' by <a href="'.$url.'" title="Posts by '.$author.'">'.$author.'</a>';
	}
	

	/**
	 * 
	 */
	public function get_excerpt( $post )
	{
		if( !empty($post->post_excerpt) )
		{
			$excerpt = $post->post_excerpt;
		}
		else
		{
			$excerpt = $this->get_content( $post );
			$excerpt = strip_tags($excerpt);
			if( strlen($excerpt) > 140 )
			{
				$excerpt = substr($excerpt, 0, 140);
				$excerpt = substr($excerpt, 0, strripos($excerpt, " "));
				$excerpt .= ' [&hellip;]';
			}
		}
		
		return $this->apply_filters( 'story-excerpt', $excerpt, $post );
	}
	
	
	/**
	 * 
	 */
	public function get_content( $post )
	{
		$content = $post->post_content;

		$matches = null;
		$num_matches = preg_match_all( "/(\[embed\].+?)+(\[\/embed\])/i", $content, $matches, PREG_SET_ORDER );
		
		if( ($num_matches !== FALSE) && ($num_matches > 0) )
		{
			for( $i = 0; $i < $num_matches; $i++ )
			{
				$content = str_replace( $matches[$i][0], '<div class="embed">'.$matches[$i][0].'</div>', $content );
			}
		}
		
		$content = apply_filters( 'the_content', $content );
		
		return $this->apply_filters( 'story-content', $content, $post );
	}
	
	

	/**
	 * 
	 */
	public function get_search_results( $search_text )
	{
		$stories = array();
		
		$args = array(
			's' => $search_text,
			'posts_per_page' => 10,
			'post_type' => $this->post_types,
			'post_status' => 'publish',
			'tax_query' => $this->create_tax_query(),
			'section' => $this
		);
		
		$query = new WP_Query( $args );
		
		while( $query->have_posts() )
		{
			$query->the_post();
			array_push(
				$stories,
				array(
					'id' => get_the_ID(),
					'title' => get_the_title(),
				)
			);
		}
		
		wp_reset_postdata();
		
		return $stories;
	}
	
	
	/**
	 * 
	 */
	public function get_image( $post_id, $type )
	{
		$image = wp_get_attachment_image_src(
			get_post_thumbnail_id( $post_id ), $type
		);
		
		if( $image ) 
			return $image[0];
		
		return null;
	}


	/**
	 * 
	 */
	function get_embed_code( $content )
	{
		$embed = '';
		
		$matches = null;
		$num_matches = preg_match_all( "/(\[embed\].+?)+(\[\/embed\])/i", $content, $matches, PREG_SET_ORDER );
		
		if( ($num_matches !== FALSE) && ($num_matches > 0) )
		{
			global $wp_embed;
			return $wp_embed->run_shortcode( $matches[0][0] );
		}
		
		return $embed;
	}
	
	
	/**
	 * 
	 */
	public function is_post_type( $post_type )
	{
		if( is_array($post_type) )
		{
			foreach( $post_type as $type )
			{
				if( in_array($type, $this->post_types) ) return true;
			}
			return false;
		}
		return ( in_array($post_type, $this->post_types) );
	}
	
	
	/**
	 * 
	 */
	public function has_taxonomies()
	{
		return( count($this->taxonomies) > 0 );
	}
	
	
	/**
	 * 
	 */
	public function has_term( $taxonomy, $term )
	{
		if( array_key_exists($taxonomy, $this->taxonomies) )
			return in_array( $term, $this->taxonomies[$taxonomy] );
		return false;
	}
	
	
	/**
	 * 
	 */
	public function get_taxonomy_count()
	{
		$count = 0;
		
		foreach( $this->taxonomies as $taxname => $terms )
		{
			$count += count($this->taxonomies[$taxname]);
		}
		
		return $count;
	}
}

