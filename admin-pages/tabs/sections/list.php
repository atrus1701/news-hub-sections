<?php

if( !class_exists('NHS_SectionsListTable') )
	require_once( NHS_PLUGIN_PATH.'/admin-pages/tables/sections.php' );


/**
 * Controls the tab admin page "Sections > List".
 * 
 * @package    unc-charlotte-news-hub-theme
 * @subpackage admin-pages/tabs/users
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('NHS_SectionsListTabAdminPage') ):
class NHS_SectionsListTabAdminPage extends APL_TabAdminPage
{
	/**
	 * The main model for the UNC Charlotte News Hub theme.
	 * @var  NHS_Model
	 */	
	private $model = null;	

	/**
	 * The Sections admin table.
	 * @var  NHS_SectionsListTable
	 */
	private $list_table = null;
	
	
	/**
	 * Constructor.
	 */
	public function __construct(
		$parent,
		$name = 'list', 
		$tab_title = 'List', 
		$page_title = 'Section List' )
	{
		parent::__construct( $parent, $name, $tab_title, $page_title );
		$this->model = NHS_Model::get_instance();
	}

	
	/**
	 * Initialize the admin page by setting up the filters and list table.
	 */
	public function init()
	{
		$this->list_table = new NHS_SectionsListTable( $this );
	}
	

	/**
	 * Loads the list table's items.
	 */
	public function load()
	{
		$this->list_table->load();
	}
	

	/**
	 * Add screen options.
	 */
	public function add_screen_options()
	{
		$this->add_per_page_screen_option( 'unccnh_sections_per_page', 'Sections', 100 );
		$this->add_selectable_columns( $this->list_table->get_selectable_columns() );
	}
	
	
	/**
	 * Process any action present in the $_REQUEST data.
	 */
	public function process()
	{
		if( $this->list_table->process_batch_action() ) return;

		if( empty($_REQUEST['action']) ) return;
		
		switch( $_REQUEST['action'] )
		{
		// 	case 'Process All Users':
		// 	case 'process-all-users':
		// 		$this->process_users();
		// 		break;

		// 	case 'clear':
		// 		$this->model->user->clear_tables();
		// 		$this->handler->force_redirect_url = $this->get_page_url();
		// 		break;
			
		// 	case 'export':
		// 		require_once( ORGANIZATION_HUB_PLUGIN_PATH . '/libraries/csv-handler/csv-handler.php' );
		// 		$this->model->user->csv_export( $this->filters, $this->search, $this->show_errors, $this->orderby );
		// 		exit;
		// 		break;
		}
	}
	

	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		$this->list_table->prepare_items();

		$this->form_start( 'sections-table' );
		
		$this->list_table->display();
		
		$this->form_end();
	}

} // class NHS_UsersListTabAdminPage extends APL_TabAdminPage
endif; // if( !class_exists('NHS_UsersListTabAdminPage') )

