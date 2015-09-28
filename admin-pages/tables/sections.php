<?php

if( !class_exists('WP_List_Table') )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

if( !class_exists('NHS_Model') )
	require_once( NHS_PLUGIN_PATH . '/classes/model.php' );


/**
 * The WP_List_Table class for the Sections table.
 * 
 * @package    unc-charlotte-news-hub-theme
 * @subpackage classes
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('NHS_SectionsListTable') ):
class NHS_SectionsListTable extends WP_List_Table
{
	/**
	 * Parent admin page.
	 * @var  APL_AdminPage
	 */
	private $parent;

	/**
	 * The main UNC Charlotte News Hub theme model.
	 * @var  NHS_Model
	 */
	private $model;
	
	
	/**
	 * Constructor.
	 */
	public function __construct( $parent )
	{
		$this->parent = $parent;
		$this->model = NHS_Model::get_instance();
	}
	
	
	/**
	 * Loads the list table.
	 */
	public function load()
	{
		parent::__construct(
            array(
                'singular' => 'unccnh-section',
                'plural'   => 'unccnh-sections',
                'ajax'     => false,
            )
        );

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
	}
	
	
	/**
	 * Prepare the table's items.
	 */
	public function prepare_items()
	{
		$sections_count = $this->model->get_sections_count();
	
		$current_page = $this->get_pagenum();
		$per_page = $this->parent->get_screen_option( 'unccnh_sections_per_page' );

		$this->set_pagination_args( array(
    		'total_items' => $sections_count,
    		'per_page'    => $per_page
  		) );
  		
  		$this->items = $this->model->get_sections( ($current_page-1)*$per_page, $per_page );
	}


	/**
	 * Get the columns for the table.
	 * @return  array  An array of columns for the table.
	 */
	public function get_columns()
	{
		return array(
			'cb'                     => '<input type="checkbox" />',
			'section_name'           => 'Name / Title',
			'section_post_type'      => 'Post Types',
			'section_taxonomies'     => 'Taxonomy Terms',
			'section_type'           => 'Image Type',
		);
	}
	
	
	/**
	 * Get the column that are hidden.
	 * @return  array  An array of hidden columns.
	 */
	public function get_hidden_columns()
	{
		$screen = get_current_screen();
		$hidden = get_user_option( 'manage'.$screen->id.'columnshidden' );
		
		if( $hidden === false )
		{
			$hidden = array();
		}
		
		return $hidden;
	}

	
	/**
	 * Get the sortable columns.
	 * @return  array  An array of sortable columns.
	 */
	public function get_sortable_columns()
	{
		return array(
			'section_name' => array( 'name', false ),
		);
	}
	
	
	/**
	 * Get the selectable (throught Screen Options) columns.
	 * @return  array  An array of selectable columns.
	 */
	public function get_selectable_columns()
	{
		return array();
	}
	
	
	/**
	 * Get the bulk action for the sections table.
	 * @return  array  An array of bulk actions.
	 */
	public function get_bulk_actions()
	{
		$actions = array();
  		return $actions;
	}


	/**
	 * Echos html to display to the right of bulk actions.
	 * @param  string  $which  Which tablenav is being displayed (top / bottom).
	 */
	public function extra_tablenav( $which )
	{
		$url = $this->parent->get_page_url(
			array(
				'tab' => 'add'
			)
		);
		?>
		<a href="<?php echo $url; ?>" class="add-section" >Add Section</a>
		<?php				
	}


	/**
	 * Displays the text to display when no sections are found.
	 */
	public function no_items()
	{
  		_e( 'No sections found.' );
	}
	
	
	/**
	 * Generates the html for a column.
	 * @param  array  $item  The item for the current row.
	 * @param  string  $column_name  The name of the current column.
	 * @return  string  The heml for the current column.
	 */
	public function column_default( $item, $column_name )
	{
		$html = '';

		switch( $column_name )
		{
			case 'cb':
				$html = sprintf( '<input type="checkbox" name="site[]" value="%s" />', $item['blog_id'] );
				break;

			case 'section_name':
				$actions = array(
    		        'edit' => sprintf( 
    		        	'<a href="%s">Edit</a>', 
    		        	$this->parent->get_page_url( 
    		        		array( 
    		        			'tab' => 'edit',
    		        			'key'  => $item['key']
    		        		)
    		        	)
    		        ),
        		    'delete' => sprintf(
        		    	'<a href="%s">Delete</a>',
        		    	$this->parent->get_page_url(
        		    		array(
        		    			'tab' => 'remove',
        		    			'key'  => $item['key']
        		    		)
        		    	)
        		    ),
		        );
        
				$html = sprintf( '%1$s<br/>%2$s<br/>%3$s', $item['name'], $item['title'],  $this->row_actions($actions) );
				break;
				
			case 'section_post_type':
				$html = print_r($item['post_types'],true);
				if( empty($item['post_types']) )
				{
					$html = 'post';
				}
				else
				{
					$html = implode( ', ', $item['post_types'] );
				}
				break;

			case 'section_taxonomies':
				if( empty($item['taxonomies']) )
				{
					$html = '<i>No taxonomies specified.</i>';
				}
				else
				{
					foreach( $item['taxonomies'] as $taxonomy )
					{
						$html .= '<strong>'.$taxonomy.':</strong> ';
						if( array_key_exists($taxonomy, $item) )
						{
							$html .= implode( ', ', $item[$taxonomy] );
						}
						$html .= '<br/>';
					}
				}
				break;
			
			case 'section_type':
				$html = '<strong>Featured:</strong> '.$item['featured_image'].'<br/><strong>Thumbnail:</strong> '.$item['thumbnail_image'];
				break;
			
			default:
				$html = '<strong>ERROR:</strong><br/>'.$column_name;
		}
		
		return $html;
	}
	
} // class NHS_SectionsListTable extends WP_List_Table
endif; // if( !class_exists('NHS_SectionsListTable') ):

