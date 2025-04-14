<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register ACF Field Group and Fields programmatically.
 */
function mpw_register_acf_fields() {

	// Check if ACF function exists
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
        'key' => 'group_mpw_pricing_details',
        'title' => 'Pricing Item Details',
        'fields' => array(
            array(
                'key' => 'field_mpw_cpt_code', // Unique key
                'label' => 'CPT Code',
                'name' => 'mpw_cpt_code', // Field name used in get_field()
                'type' => 'text',
                'instructions' => 'Enter the CPT code for this procedure/service.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => 'e.g., 99395',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_mpw_estimated_price', // Unique key
                'label' => 'Estimated Price',
                'name' => 'mpw_estimated_price', // Field name used in get_field()
                'type' => 'text', // Using text for flexibility with currency symbols/formatting
                'instructions' => 'Enter the estimated average price. Include currency symbol if desired (e.g., $150.00).',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => 'e.g., $150.00',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            // Note: The 'Description' field uses the standard WordPress editor,
            // enabled via 'supports' => array('title', 'editor') in the CPT registration.
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'pricing_item', // Show these fields only for the 'pricing_item' CPT
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'acf_after_title', // Position below the title field
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '', // Add options here if you want to hide default WP boxes e.g. 'the_content' if you don't use the editor
        'active' => true,
        'description' => 'Custom fields for Procedure Pricing Items.',
    ));
}
add_action( 'acf/init', 'mpw_register_acf_fields' );
