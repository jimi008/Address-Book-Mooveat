<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Follow_Up_Table extends WP_List_Table {

    /** Class constructor */
    public function __construct() {

        parent::__construct( [
            'singular' => __( 'Organization', 'mv' ), //singular name of the listed records
            'plural'   => __( 'Organizations', 'mv' ), //plural name of the listed records
            'ajax'     => true //does this table support ajax?
        ] );

        if( !current_user_can('administrator') ) {
        // Only admins allowed
           wp_die( __('You are not allowed to access this part of the site') );
       } 

   }


//Find newly created organizations
public function get_newly_created_terms(){

    $terms = get_terms( array(
        'taxonomy' => 'nomenclature_beta',
        'order'     => 'DESC',
        'orderby'   => 'id',
        'hide_empty' => false,
        'meta_query'        => array(
            'relation'      => 'AND',
            array(
                'key'           => 'is_new_org',
                'value'         => true,
                'compare'       => '='
            )
        ),
    ));

    return $terms;

}



public function prepare_data() {

$data = '';
$orgs = $this->get_newly_created_terms();

foreach ($orgs as $org ) {

    $org_id = $org->term_id;
    $org_name = $org->name;
    $contact_count = $org->count;
    $creation_date = get_field('creation_date', 'term_'. $org_id);
    $creater_username = get_field('creater_username', 'term_'. $org_id);

    $data[] = 
        array(
            "id"            => $org_id,
            "org_name"      => $org_name,
            "creater_name"  => $creater_username,
            "date"          => $creation_date,
            "status"        => "Status",
            "count"         => $contact_count,
            "cat1"            => "Catégorie niveau 1",
            "cat2"            => "Catégorie niveau 2",
            "cat3"            => "Catégorie niveau 3",
            "cat4"            => "Catégorie niveau 4",
            "cat5"            => "Catégorie niveau 5",
            "action"        => "Action",
        
    );
}

    return $data;


}

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            "id"            => "ID",
            "org_name"      => "Nom de l'organisation",
            "creater_name"  => "Créateur",
            "date"          => "Date de création",
            "status"        => "Statut",
            "count"         => "Nombre de contacts associés",
            "cat1"            => "Catégorie niveau 1",
            "cat2"            => "Catégorie niveau 2",
            "cat3"            => "Catégorie niveau 3",
            "cat4"            => "Catégorie niveau 4",
            "cat5"            => "Catégorie niveau 5",
            "action"        => "Action",
            );

        return $columns;
    }


function no_items() {
    _e( 'No Organization found.' );
  }


    /**
     * Render a column when no column specific method exist.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'id':
            case 'org_name':
            case 'creater_name':
            case 'date':
            case 'status':
            case 'count':
            case 'cat1':
            case 'cat2':
            case 'cat3':
            case 'cat4':
            case 'cat5':
            case 'action':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }


        function column_status($item){
        
        $org_id = $item['id'];
        $org_status = get_field('status', 'term_'. $org_id);

        $options = array (
            'pending'    => 'Pending',
            'valid' => 'Valid',
            'refused' => 'Refused',
        );

        $select = '<span class="org-status"><select data-org-id="'.$org_id.'" name="orgstatus" id="org-status" class="select" style="width:80px;" placeholder="Pending">';

        
        foreach ($options as $key => $label) {
            # code...
            $selected = ($org_status['value'] == $key) ? 'selected' : '';
            $select .= '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';
        }

        $select .= "</select></span>";

        return $select;         
  
    }

    //load all parent terms for respective org
    function select_load_category($field, $org_id, $cat_id)
    {   
        //$field['choices'] = array();
        //$selected_field = array();

        $acf_cat_orga_fields = array(
            'categorie_principale_organisation',
            'categorie_secondaire_organisation',
            'categorie_tertiaire_organisation',
            'cat_pro_level_4',
            'cat_pro_level_5',
            //'cat_pro_level_6'
        );        

        $get_terms_param = array(
            'taxonomy' => 'nomenclature_beta',
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        );

        $ancestors = get_ancestors( $org_id, 'nomenclature_beta' );
        array_pop($ancestors);
        $ancestors_rev = array_reverse($ancestors);


            if($field=='categorie_principale_organisation'){
                $selected_field = 8502;
                $get_terms_param['parent'] = $selected_field;
                 

            } else {
                
                $previous_field = '';

                $idp = 0;
                
                foreach ($acf_cat_orga_fields as $acf_field){

                    if( $field==$acf_field ){

                        $previous_field = $ancestors_rev[$idp-1];
                        // print_r($previous_field);
  
                        if ( $previous_field ) {
                            $selected_field = $previous_field;
                            $get_terms_param['parent'] = $selected_field;

                        }
                    }

                    $idp++;
                    
                    // $previous_field = $acf_field;
                }
            }

            if ( !empty( $selected_field ) ) {


                $terms = get_terms($get_terms_param);

                $result .= '<div class="'.$cat_id.'">';
                $result .= '<select data-org-id="'.$org_id.'" name="'.$field.'" id="'.$cat_id.'" class="select cat-lvl" style="width:160px;">
                <option>- Choisir -</option>';


                if (!empty($terms)) {

                    $idx = array_search($field, $acf_cat_orga_fields); 
                    foreach ($terms as $term) {

                        $term_id = $term->term_id;
                        $selected = selected( $ancestors_rev[$idx], $term_id, false );
                        $result .= '<option value="'.$term_id.'" '.$selected.'>'.$term->name.'</option>';
                    }
                }

                $result .= '</select></div>';
            }
        

        // Return values
        return $result;

    }


    function column_cat1($item){

        $org_id = $item['id'];
        $select = $this->select_load_category('categorie_principale_organisation', $org_id, 'cat1');
 
        return $select; 

    }

    function column_cat2($item){

        $org_id = $item['id'];
        $select = $this->select_load_category('categorie_secondaire_organisation', $org_id, 'cat2');

        return $select; 

    }

    function column_cat3($item){

        $org_id = $item['id'];
        $select = $this->select_load_category('categorie_tertiaire_organisation', $org_id, 'cat3');

        return $select; 

    }

    function column_cat4($item){

        $org_id = $item['id'];
        $select = $this->select_load_category('cat_pro_level_4', $org_id, 'cat4');

        return $select; 

    }

    function column_cat5($item){

        $org_id = $item['id'];
        $select = $this->select_load_category('cat_pro_level_5', $org_id, 'cat5');

        return $select; 

    }

    function column_action($item){

        $org_id = $item['id'];

        $action = "<input id='rename-org-".$org_id."' name='rename-org' class='rename-org button-primary' type='submit' value='Renommer'>";
        
        $action .= "<span class='orginput-wrap'><input id='orgname-input-".$org_id."' class='orgname-input' name='orgname-input' type='text'>";
        
        $action .= "<input id='orgname-submit-".$org_id."' name='orgname-submit' class='orgname-submit button-secondary' type='submit' data-org-id='".$org_id."' value='Save'></span>";
        
        $action .= "<input disabled id='status-action-".$org_id."' name='status-action' class='status-action button-secondary' type='submit' data-org-id='".$org_id."' value='Statut à définir'>";

        $action .= "<input disabled id='cat-action-".$org_id."' name='cat-action' class='cat-action button-primary' type='submit' data-org-id='".$org_id."' value='Valider le changement de classification'>";


        return $action;
        
    }


    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {

        $this->items = $this->prepare_data();
        $columns = $this->get_columns();
        // $this->_column_headers = array($columns, $hidden, $sortable);
        $this->_column_headers = $this->get_column_info();
    }


} //end Class Followup
