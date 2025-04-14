<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Custom Post Type for Pricing Items.
 */
function mpw_register_pricing_item_cpt() {

	$labels = array(
		'name'                  => _x( 'Pricing Items', 'Post Type General Name', 'procedure-pricing-widget' ),
		'singular_name'         => _x( 'Pricing Item', 'Post Type Singular Name', 'procedure-pricing-widget' ),
		'menu_name'             => __( 'Pricing Items', 'procedure-pricing-widget' ),
		'name_admin_bar'        => __( 'Pricing Item', 'procedure-pricing-widget' ),
		'archives'              => __( 'Pricing Item Archives', 'procedure-pricing-widget' ),
		'attributes'            => __( 'Pricing Item Attributes', 'procedure-pricing-widget' ),
		'parent_item_colon'     => __( 'Parent Item:', 'procedure-pricing-widget' ),
		'all_items'             => __( 'All Pricing Items', 'procedure-pricing-widget' ),
		'add_new_item'          => __( 'Add New Pricing Item', 'procedure-pricing-widget' ),
		'add_new'               => __( 'Add New', 'procedure-pricing-widget' ),
		'new_item'              => __( 'New Pricing Item', 'procedure-pricing-widget' ),
		'edit_item'             => __( 'Edit Pricing Item', 'procedure-pricing-widget' ),
		'update_item'           => __( 'Update Pricing Item', 'procedure-pricing-widget' ),
		'view_item'             => __( 'View Pricing Item', 'procedure-pricing-widget' ),
		'view_items'            => __( 'View Pricing Items', 'procedure-pricing-widget' ),
		'search_items'          => __( 'Search Pricing Item', 'procedure-pricing-widget' ),
		'not_found'             => __( 'Not found', 'procedure-pricing-widget' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'procedure-pricing-widget' ),
		'featured_image'        => __( 'Featured Image', 'procedure-pricing-widget' ),
		'set_featured_image'    => __( 'Set featured image', 'procedure-pricing-widget' ),
		'remove_featured_image' => __( 'Remove featured image', 'procedure-pricing-widget' ),
		'use_featured_image'    => __( 'Use as featured image', 'procedure-pricing-widget' ),
		'insert_into_item'      => __( 'Insert into item', 'procedure-pricing-widget' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'procedure-pricing-widget' ),
		'items_list'            => __( 'Pricing Items list', 'procedure-pricing-widget' ),
		'items_list_navigation' => __( 'Pricing Items list navigation', 'procedure-pricing-widget' ),
		'filter_items_list'     => __( 'Filter items list', 'procedure-pricing-widget' ),
	);
	$args = array(
		'label'                 => __( 'Pricing Item', 'procedure-pricing-widget' ),
		'description'           => __( 'Stores medical procedures and their estimated prices.', 'procedure-pricing-widget' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor' ), // Title = Procedure Name, Editor = Description
		'hierarchical'          => false,
		'public'                => true, // Make it public so it can be queried on the frontend
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 20, // Position in admin menu
		'menu_icon'             => 'dashicons-money-alt', // Choose an appropriate icon
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => false, // No archive page needed typically
		'exclude_from_search'   => true, // Exclude from default WordPress site search (we use custom AJAX search)
		'publicly_queryable'    => true, // Allow querying via URL (though we mainly use AJAX)
		'capability_type'       => 'post',
        'show_in_rest'          => true, // Enable REST API access if needed in future
        'rewrite'               => array( 'slug' => 'pricing-item', 'with_front' => false ), // Customize slug if needed
	);
	register_post_type( 'pricing_item', $args );

}
add_action( 'init', 'mpw_register_pricing_item_cpt', 0 );