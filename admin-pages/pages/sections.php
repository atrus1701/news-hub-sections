<?php
/**
 * Controls the admin page "Sections".
 * 
 * @package    unc-charlotte-news-hub-theme
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <atrus1701@gmail.com>
 */

if( !class_exists('NHS_SectionsAdminPage') ):
class NHS_SectionsAdminPage extends APL_AdminPage
{
	
	/**
	 * The main model for the Organization Hub.
	 * @var  NHS_Model
	 */	
	private $model = null;	
	

	/**
	 * Constructor.
	 */
	public function __construct(
		$name = 'sections',
		$menu_title = 'Sections',
		$page_title = 'Sections',
		$capability = 'administrator' )
	{
		parent::__construct( $name, $menu_title, $page_title, $capability );
		
		$this->add_tab( new NHS_SectionsListTabAdminPage($this) );
		$this->add_tab( new NHS_SectionsAddTabAdminPage($this) );
		$this->add_tab( new NHS_SectionsEditTabAdminPage($this) );
		$this->add_tab( new NHS_SectionsRemoveTabAdminPage($this) );

		$this->display_page_tab_list = FALSE;
	}
	
} // class NHS_SectionsAdminPage extends APL_AdminPage
endif; // if( !class_exists('NHS_SectionsAdminPage') )

