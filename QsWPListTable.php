<?php 
/*
 * Plugin Name: QsWPListTable
 * Description: An example of how to use the WP_List_Table class to display data in your WordPress Admin area
 * Author: Q
 * Author URI: Q
 * Version: 0.9
 */
 


add_action("admin_menu", QsFunction_admin_action2);


function QsFunction_admin_action2() {
	
	add_options_page("QsWPListTable", "QsWPListTable", "manage_options", __FILE__, QsFunction_admin2);
}





function QsFunction_admin2()
{ 
	$exampleListTable = new Example_List_Table();
    $exampleListTable->prepare_items();
        ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <form method="post">
                <h2>Example List Table Page</h2>
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                <?php $exampleListTable->display(); ?>
                </form>
            </div>
        <?php
}




// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
 
/**
 * Create a new table class that will extend the WP_List_Table
 */
class Example_List_Table extends WP_List_Table
{	
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
	
	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
            'singular'  => 'wp_post',     //singular name of the listed records
            'plural'    => 'wp_posts',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
		) );

	}
	
	
	

	
	
	
	function process_bulk_action() {
	
			
		//Detect when a bulk action is being triggered...
		if( 'delete'===$this->current_action() ) {
			//echo $this->current_action();
			//echo  "DEO : ".$_GET['wp_posts'];
			
			
			global $wpdb;
			foreach ($_POST[$this->_args["plural"]] as $key => $value) {
				
				$wpdb->query( "DELETE FROM ". $this->_args["plural"]." WHERE ID = $value" );
				//Unset the item from GET[], otherwise the item will still be up for deletion and the parameter list in the url will keep getting longer. 
				unset($_POST[$this->_args["plural"]][$key]);
				remove_query_arg( $key, $query = false );
			}
			
		}
	}
	
	
	function get_bulk_actions() {
	  $actions = array(
	    'delete'    => 'Delete'
	  );
	  return $actions;
	}
	
	function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['plural'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }
	
    public function prepare_items()
    {
        $this->process_bulk_action();
    	$columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
 
        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );
 
        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
 
        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
 
        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
 
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }
 
    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
        	'cb'        => '<input type="checkbox" />',
            'ID'          => 'ID',
            'guid'       => 'guid',
            'description' => 'Description',
            'year'        => 'Year',
            'director'    => 'Director',
            'rating'      => 'Rating'
        );
 
        return $columns;
    }
 
    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }
 
    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('ID' => array('ID', false), 'guid' => array('guid', false));
    }
 
    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
        $data = array();
 		
        // QQQQQQ
        global $wpdb;

		$results = $wpdb->get_results("SELECT ID, post_title, guid FROM $wpdb->posts");
		//var_dump($results);
        // QQQQQQ SLUT
        
		foreach ($results as $result) 
		{
				$data[] = array(
							'ID'          => $result->ID,
							'guid'       => $result->guid
							);
		}
		
		/* $data[] = array(
                    'id'          => 10,
                    'title'       => 'Fight Club',
                    'description' => 'An insomniac office worker looking for a way to change his life crosses paths with a devil-may-care soap maker and they form an underground fight club that evolves into something much, much more...',
                    'year'        => '1999',
                    'director'    => 'David Fincher',
                    'rating'      => '8.8'
                    );   */     
        

 
        return $data;
    }
 
    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'ID':
            case 'guid':
            case 'description':
            case 'year':
            case 'director':
            case 'rating':
                return $item[ $column_name ];
 
            default:
                return print_r( $item, true ) ;
        }
    }
 
    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'title';
        $order = 'asc';
 
        // If orderby is set, use this as the sort column
        if(!empty($_POST['orderby']))
        {
            $orderby = $_POST['orderby'];
        }
 
        // If order is set use this as the order
        if(!empty($_POST['order']))
        {
            $order = $_POST['order'];
        }
 
 
        $result = strnatcmp( $a[$orderby], $b[$orderby] );
 
        if($order === 'asc')
        {
            return $result;
        }
 
        return -$result;
    }
}
?>