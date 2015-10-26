<?php
/**
 * Controls the admin page "Users" when in edit user mode.
 * 
 * @package    organization-hub
 * @subpackage admin-pages/tabs/users
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('NHS_SectionsEditTabAdminPage') ):
class NHS_SectionsEditTabAdminPage extends APL_TabAdminPage
{
	/**
	 * The main model for the Organization Hub.
	 * @var  NHS_Model
	 */	
	private $model = null;
	
	
	/**
	 * Controller.
	 */
	public function __construct(
		$parent,
		$name = 'edit', 
		$tab_title = 'Edit', 
		$page_title = 'Edit Section' )
	{
		parent::__construct( $parent, $name, $tab_title, $page_title );
		$this->model = NHS_Model::get_instance();
		$this->display_tab = false;
	}


	/**
	 * Enqueues all the scripts or styles needed for the admin page. 
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js' );
		wp_enqueue_script( 'section-edit', NHS_PLUGIN_URL.'/admin-pages/scripts/section-edit.js' );
	}


	/**
	 * Register each individual settings for the Settings API.
	 */
	public function register_settings()
	{
		$this->register_setting( NHS_SECTIONS );
	}


	/**
	 * Add the sections used for the Settings API. 
	 */
	public function add_settings_sections()
	{
		$this->add_section( 'section-info', 'Section Info', 'print_section_info' );
	}
	
	
	/**
	 * Add the settings used for the Settings API. 
	 */
	public function add_settings_fields()
	{
		$this->add_field(
			'section-info',
			'name',
			'Name',
			'print_field_name',
			array()
		);

		$this->add_field(
			'section-info',
			'title',
			'Title',
			'print_field_title',
			array()
		);
		
		$this->add_field(
			'section-info',
			'post-type',
			'Post Types',
			'print_field_post_types',
			array()
		);
		
		$this->add_field(
			'section-info',
			'taxonomies',
			'Taxonomies',
			'print_field_taxonomies',
			array()
		);
		
		$this->add_field(
			'section-info',
			'image-type',
			'Image Type',
			'print_field_image_type',
			array()
		);
		
		$this->add_field(
			'section-info',
			'layout',
			'Layout',
			'print_field_layout',
			array()
		);		

		$this->add_field(
			'section-info',
			'priority',
			'Priority',
			'print_field_priority',
			array()
		);
	}


	/**
	 * 
	 */
	public function print_section_info( $args )
	{
		apl_print( 'print_section_info' );
	}


	/**
	 * 
	 */
	public function print_field_name( $args )
	{
		$name = array( NHS_SECTIONS, 'name' );
		?>
		<input type="hidden" name="<?php apl_name_e(NHS_SECTIONS, 'key') ?>" value="<?php echo $this->section['key']; ?>" />
		<input type="text" name="<?php apl_name_e(NHS_SECTIONS, 'name') ?>" value="<?php echo $this->section['name']; ?>" />
		<?php
	}

	
	/**
	 * 
	 */
	public function print_field_title( $args )
	{
		$name = array( NHS_SECTIONS, 'title' );
		?>
		<input type="text" name="<?php apl_name_e(NHS_SECTIONS, 'title') ?>" value="<?php echo $this->section['title']; ?>" />
		<?php
	}

	
	/**
	 * 
	 */
	public function print_field_post_types( $args )
	{
		$all_post_types = get_post_types( array( 'public' => true ), 'objects' );
		?>

		<div id="post-type-selection">
		
		<?php foreach( $all_post_types as $post_type ): ?>
			<div class="post-type <?php echo $post_type->name; ?>">
			
				<input type="checkbox"
					name="<?php apl_name_e(NHS_SECTIONS, 'post_types', ''); ?>"
					value="<?php echo $post_type->name; ?>"
					<?php checked(true, in_array($post_type->name, $this->section['post_types'])); ?>
				/>
				<input type="hidden" 
				       value="<?php echo implode(',',get_object_taxonomies($post_type->name)); ?>" />
				<label><?php echo $post_type->label; ?></label>
			
			</div>
		<?php endforeach; ?>

		</div>
		<?php
	}

	
	/**
	 * 
	 */
	public function print_field_taxonomies( $args )
	{
		$all_taxonomies = get_taxonomies( array(), 'objects' );
		$section_taxonomies = $this->section['taxonomies'];
		?>
		
		<div id="no-taxonomies">No taxonomies for selected post types.</div>
		
		<div id="taxonomy-selection">
		
		<?php foreach( $all_taxonomies as $taxname => $taxonomy ): ?>
		
			<?php
			$tax = get_taxonomy( $taxname ); $name = $tax->label;
			if( empty($name) ) $name = $taxname;
			?>

			<div class="taxonomy <?php echo $taxname; ?>">
			<input type="checkbox" 
			       name="<?php apl_name_e( NHS_SECTIONS, 'taxonomies', '' ); ?>" 
			       value="<?php echo $taxname; ?>" 
			       <?php checked(true, in_array($taxname, $section_taxonomies)); ?> />
			<label><?php echo $name; ?></label>
			</div>
					
		<?php endforeach; ?>
		
		</div>
		
		<div id="taxonomies-selection">
		
		<?php foreach( $all_taxonomies as $taxname => $taxonomy ): ?>
		
			<div class="taxonomy <?php echo $taxname; ?>">

			<?php
			$tax = get_taxonomy( $taxname ); $name = $tax->label;
			if( empty($name) ) $name = $taxname;
			?>
			
			<div class="taxname"><strong><?php echo $name; ?></strong></div>
			
			<?php 
			$terms = ( array_key_exists($taxname, $this->section) ? $this->section[$taxname] : array() );
			?>
			
			<?php foreach( get_terms( $taxname, array( 'hide_empty' => false ) ) as $term ): ?>
		
			<div>
			<input type="checkbox" 
			       name="<?php apl_name_e( NHS_SECTIONS, $taxname, '' ); ?>" 
			       value="<?php echo $term->slug; ?>" 
			       <?php checked(true, in_array($term->slug, $terms), true); ?> />
			<label><?php echo $term->name; ?></label>
			</div>
			
			<?php endforeach; ?>
			
			</div>
		
		<?php endforeach; ?>
		
		</div>
		
		<?php
	}


	/**
	 * 
	 */
	public function print_field_image_type( $args )
	{
		?>

		<strong>Featured</strong>
		<?php $this->print_image_selection( 'featured_image', $this->section['featured_image'] ); ?>

		<br/>

		<strong>Thumbnail</strong>
		<?php $this->print_image_selection( 'thumbnail_image', $this->section['thumbnail_image'] ); ?>

		<?php
	}
	

	/**
	 * 
	 */
	private function print_image_selection( $name, $current_value )
	{
		?>
		<select name="<?php apl_name_e( NHS_SECTIONS, $name ); ?>">

		<?php foreach( array( 'none', 'normal', 'landscape', 'portrait', 'embed' ) as $image_type ): ?>
			<option value="<?php echo $image_type; ?>" 
			    <?php selected( $current_value, $image_type ); ?>>
			<?php echo $image_type; ?>
			</option>
		<?php endforeach; ?>
		
		</select>
		<?php
	}


	/**
	 * 
	 */
	public function print_field_layout( $args )
	{
		?>
		<strong>Archive Page</strong>
		<?php $this->print_number_selection( 'archive_page_stories', $this->section['archive_page_stories'] ); ?>

		<br/>

		<strong>RSS Feed</strong>
		<?php $this->print_number_selection( 'rss_feed_stories', $this->section['rss_feed_stories'] ); ?>

		<?php
	}


	/**
	 * 
	 */
	public function print_field_priority( $args )
	{
		?>
		<select name="<?php apl_name_e( NHS_SECTIONS, 'priority' ); ?>">

		<?php foreach( range( -1, 10 ) as $i ): ?>
			<option value="<?php echo $i; ?>" 
			    <?php selected( $i, $this->section['priority'] ); ?>>
			<?php echo $i; ?>
			</option>
		<?php endforeach; ?>
		
		</select>
		<?php
	}


	/**
	 * 
	 */
	private function print_number_selection( $name, $current_value )
	{
		?>
		<select name="<?php apl_name_e( NHS_SECTIONS, $name ); ?>">

		<?php for( $i = 10; $i < 101; $i += 10 ): ?>
			<option value="<?php echo $i; ?>" 
			    <?php selected( $current_value, $i ); ?>>
			<?php echo $i; ?>
			</option>
		<?php endfor; ?>
		
		</select>
		<?php		
	}


	/**
	 * Processes the current admin page's Settings API input.
	 * @param  array  $settings  The inputted settings from the Settings API.
	 * @param  string  $option  The option key of the settings input array.
	 * @return  array  The resulted array to store in the db.
	 */
	public function process_settings( $settings, $option )
	{
		// check if section name already exists.
		if( empty($settings['name']) )
		{
			add_settings_error( 'name', 'settings_not_updated', 'Name must be specified.', 'error' );
			return $this->model->get_sections();
		}

		$existing_section = $this->model->get_section_by_name( $settings['name'] );
		if( $existing_section !== NULL && $existing_section['key'] != $settings['key'] )
		{
			add_settings_error( 'name', 'settings_not_updated', 'Name already in use.', 'error' );
			return $this->model->get_sections();
		}

		$key = sanitize_title( $settings['name'] );
		if( $key != $settings['key'] && $this->model->get_section_by_key( $key ) !== NULL )
		{
			add_settings_error( 'name', 'settings_not_updated', 'Name already in use.', 'error' );
			return $this->model->get_sections();
		}
		
		// check if section name already exists.
		if( empty($settings['title']) )
		{
			add_settings_error( 'title', 'settings_not_updated', 'Title must be specified.', 'error' );
			return $this->model->get_sections();
		}
		
		// update existing section.
		$this->model->update_section( $key, $settings );
		return $this->model->get_sections();
	}

		
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		$url = $this->get_page_url( array( 'tab' => 'list' ) );
		?>
		<a href="<?php echo $url; ?>"><< Return to Section List</a>
		<?php
		
		if( !isset($_GET['key']) && !is_numeric($_GET['key']) )
		{
			echo 'The section key is not specified.';
			return;
		}

		$this->section = $this->model->get_section_by_key( $_GET['key'] );

		if( $this->section === NULL )
		{
			echo 'The section key is not valid.';
			return;
		}
		
		$this->section = $this->model->filter_section( $this->section );
		
		$this->print_settings();
	}

} // class NHS_UsersEditTabAdminPage extends APL_TabAdminPage
endif; // if( !class_exists('NHS_UsersEditTabAdminPage') )

