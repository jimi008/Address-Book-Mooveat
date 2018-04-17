<?php
function ac_custom_column_settings_e2e1dfda() {


    if ( function_exists( 'ac_register_columns' ) ) {

        ac_register_columns( 'mv_address_book', array(
        array(
            'columns' => array(
                'title' => array(
                    'type' => 'title',
                    'label' => 'Prénom, Nom Contact',
                    'width' => '',
                    'width_unit' => '%',
                    'sort' => 'on',
                    'name' => 'title'
                ),
                '5acf62d6ac60b' => array(
                    'type' => 'column-meta',
                    'label' => 'Intitulé organisation',
                    'width' => '',
                    'width_unit' => '%',
                    'field' => 'contact_intitule_organisation',
                    'field_type' => '',
                    'before' => '',
                    'after' => '',
                    'sort' => 'on',
                    'filter' => 'off',
                    'filter_label' => '',
                    'name' => '5acf62d6ac60b'
                ),
                '5acf62d6b35c0' => array(
                    'type' => 'column-meta',
                    'label' => 'Catégorie principale organisation',
                    'width' => '',
                    'width_unit' => '%',
                    'field' => 'mvcpo_categorie_principale_organisation',
                    'field_type' => '',
                    'before' => '',
                    'after' => '',
                    'sort' => 'on',
                    'filter' => 'off',
                    'filter_label' => '',
                    'name' => '5acf62d6b35c0'
                ),
                '5acf63255edf0' => array(
                    'type' => 'column-meta',
                    'label' => 'Catégorie secondaire organisation',
                    'width' => '',
                    'width_unit' => '%',
                    'field' => 'cso_grp_categorie_secondaire_organisation',
                    'field_type' => '',
                    'before' => '',
                    'after' => '',
                    'sort' => 'on',
                    'filter' => 'off',
                    'filter_label' => 'Any CSO',
                    'name' => '5acf63255edf0'
                ),
                '5acf6761304fa' => array(
                    'type' => 'column-meta',
                    'label' => 'Catégorie(s) produit(s)',
                    'width' => '',
                    'width_unit' => '%',
                    'field' => 'categories_produits_grp_categories_produits',
                    'field_type' => 'array',
                    'before' => '',
                    'after' => '',
                    'sort' => 'on',
                    'name' => '5acf6761304fa'
                ),
                '5acf725676980' => array(
                    'type' => 'column-meta',
                    'label' => 'Label',
                    'width' => '',
                    'width_unit' => '%',
                    'field' => 'label_grp_label',
                    'field_type' => 'array',
                    'before' => '',
                    'after' => '',
                    'sort' => 'on',
                    'name' => '5acf725676980'
                ),
                '5acf7256780b2' => array(
                    'type' => 'column-meta',
                    'label' => 'Email',
                    'width' => '',
                    'width_unit' => '%',
                    'field' => 'social_email',
                    'field_type' => '',
                    'before' => '',
                    'after' => '',
                    'sort' => 'on',
                    'filter' => 'off',
                    'filter_label' => '',
                    'name' => '5acf7256780b2'
                ),
                '5acf72567960b' => array(
                    'type' => 'column-meta',
                    'label' => 'Site internet',
                    'width' => '',
                    'width_unit' => '%',
                    'field' => 'social_site_web',
                    'field_type' => 'link',
                    'link_label' => '',
                    'before' => '',
                    'after' => '',
                    'sort' => 'on',
                    'filter' => 'on',
                    'filter_label' => 'Site internet',
                    'name' => '5acf72567960b'
                ),
                '5acf72567a949' => array(
                    'type' => 'column-meta',
                    'label' => 'To be Contacted',
                    'width' => '7',
                    'width_unit' => '%',
                    'field' => 'suivi_actions_to_be_contacted',
                    'field_type' => 'has_content',
                    'before' => '',
                    'after' => '',
                    'sort' => 'on',
                    'filter' => 'on',
                    'filter_label' => 'To be contacted',
                    'name' => '5acf72567a949'
                )
            ),

        )
    ) );

    }
}
add_action( 'ac/ready', 'ac_custom_column_settings_e2e1dfda' );