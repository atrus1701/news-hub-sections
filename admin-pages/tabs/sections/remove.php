<?php
/**
 * Controls the admin page "Users" when in edit user mode.
 * 
 * @package    organization-hub
 * @subpackage admin-pages/tabs/users
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('NHS_SectionsRemoveTabAdminPage') ):
class NHS_SectionsRemoveTabAdminPage extends APL_TabAdminPage
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
		$name = 'remove', 
		$tab_title = 'remove', 
		$page_title = 'Remove User' )
	{
		parent::__construct( $parent, $name, $tab_title, $page_title );
		$this->model = NHS_Model::get_instance();
		$this->display_tab = false;
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
	}


	/**
	 * 
	 */
	public function print_section_info( $args )
	{
		echo '<p>Are you sure you want to delete this section?</p>';
		echo '<p>This operation cannot be undone.</p>';
	}


	/**
	 * 
	 */
	public function print_field_name( $args )
	{
		$name = array( NHS_SECTIONS, 'name' );
		?>
		<input type="hidden" name="<?php apl_name_e(NHS_SECTIONS, 'key') ?>" value="<?php echo $this->section['key']; ?>" />
		<input type="text" name="<?php apl_name_e(NHS_SECTIONS, 'name') ?>" value="<?php echo $this->section['name']; ?>" readonly />
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
		// delete existing section.
		$this->model->remove_section( $settings['key'] );
		add_settings_error( '', 'settings_updated', 'The section has been deleted.' );
		
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

