<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle the AJAX request for searching pricing items.
 *
 * Searches the post title, content, and specified Pods fields.
 */
function mpw_handle_ajax_search() {
    // 1. Verify Nonce for security
    check_ajax_referer( 'mpw_search_nonce', 'nonce' );

    // 2. Get and Sanitize Search Query
    $search_query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';

    // 3. Basic validation
    if ( empty( $search_query ) ) {
        wp_send_json_success( array( 'message' => 'Please enter a search term.' ) );
        wp_die();
    }

    // 4. Caching Check (Transients API)
    $transient_key = 'mpw_search_' . md5( $search_query );
    $cached_results = get_transient( $transient_key );

    // --- Development Note: Temporarily disable cache during testing ---
    // $cached_results = false;
    // ---

    if ( false !== $cached_results ) {
        // Serve results from cache
        wp_send_json_success( $cached_results );
        wp_die();
    }

    // 5. Prepare Pods Query
    $pods = pods( 'pricing_item', array(
        'limit' => 50, // Adjust as needed
        'orderby' => 't.post_title ASC',
        'where' => "t.post_title LIKE '%{$search_query}%' OR t.post_content LIKE '%{$search_query}%' OR cpt_code.meta_value LIKE '%{$search_query}%' OR estimated_price.meta_value LIKE '%{$search_query}%' OR procedure_name.meta_value LIKE '%{$search_query}%' OR description.meta_value LIKE '%{$search_query}%'",
    ) );

    // 6. Format Results
    $results = array();
    if ( $pods->total() > 0 ) {
        while ( $pods->fetch() ) {
            $results[] = array(
                'id'          => $pods->display('id'),
                'title'       => $pods->display('post_title'),
                'cpt_code'    => esc_html( $pods->display('cpt_code') ),
                'price'       => esc_html( $pods->display('estimated_price') ),
                'description' => wp_kses_post( $pods->display('post_content') ),
                'permalink'   => esc_url( get_permalink( $pods->display('id') ) ),
                'procedure_name' => esc_html( $pods->display('procedure_name') ), // Add procedure name
            );
        }
    }

    // 7. Cache the Results
    set_transient( $transient_key, $results, HOUR_IN_SECONDS );

    // 8. Send JSON Response
    error_log( 'AJAX Response Data: ' . print_r( $results, true ) ); // Log the results to the server's error log

    wp_send_json_success( $results );

    // 9. Always die() in AJAX handlers
    wp_die();
}

// Hook the AJAX handler
add_action( 'wp_ajax_mpw_search_procedures', 'mpw_handle_ajax_search' );
add_action( 'wp_ajax_nopriv_mpw_search_procedures', 'mpw_handle_ajax_search' );
