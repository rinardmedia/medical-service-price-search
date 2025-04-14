<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add Admin Submenu Page for CSV Import
 */
function mpw_add_import_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=pricing_item', // Parent slug (Pricing Items CPT)
        __( 'Import Pricing Items', 'procedure-pricing-widget' ), // Page title
        __( 'Import CSV', 'procedure-pricing-widget' ),       // Menu title
        'manage_options',                  // Capability required
        'mpw-csv-import',                  // Menu slug
        'mpw_render_import_page'           // Function to display the page content
    );
}
add_action( 'admin_menu', 'mpw_add_import_admin_menu' );

/**
 * Render the Admin Page HTML content for CSV Import
 */
function mpw_render_import_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p><?php _e( 'Upload a CSV file to import pricing items. Format: Procedure Name, CPT Code, Estimated Price, Description (one item per row).', 'procedure-pricing-widget' ); ?></p>
        <p><strong><?php _e( 'Important:', 'procedure-pricing-widget' ); ?></strong> <?php _e( 'Ensure ACF Plugin is active. Backup your database before importing. Large files may time out.', 'procedure-pricing-widget' ); ?></p>

        <?php
        // Display feedback messages (success/error)
        if ( isset( $_GET['mpw_import_status'] ) ) {
            $status = sanitize_key( $_GET['mpw_import_status'] );
            $count = isset( $_GET['count'] ) ? intval( $_GET['count'] ) : 0;
            $skipped = isset( $_GET['skipped'] ) ? intval( $_GET['skipped'] ) : 0;
            $errors = isset( $_GET['errors'] ) ? intval( $_GET['errors'] ) : 0;

            if ( $status === 'success' ) {
                echo '<div id="message" class="updated notice is-dismissible"><p>';
                printf(
                    esc_html__( 'Import successful! %d items imported.', 'procedure-pricing-widget' ),
                    $count
                );
                 if ($skipped > 0) printf(' ' . esc_html__('%d items skipped (duplicates or formatting issues).', 'procedure-pricing-widget'), $skipped);
                 if ($errors > 0) printf(' ' . esc_html__('%d errors encountered during insertion.', 'procedure-pricing-widget'), $errors);
                 echo '</p></div>';
            } elseif ( $status === 'error' ) {
                $error_code = isset($_GET['error_code']) ? sanitize_text_field($_GET['error_code']) : 'unknown';
                $error_message = __('An error occurred during import.', 'procedure-pricing-widget');
                if ($error_code === 'no_file') $error_message = __('No file was uploaded or file type is incorrect.', 'procedure-pricing-widget');
                if ($error_code === 'acf_inactive') $error_message = __('ACF Plugin function not found. Is ACF active?', 'procedure-pricing-widget');
                 if ($error_code === 'upload_failed') $error_message = __('File upload failed.', 'procedure-pricing-widget');
                 if ($error_code === 'cannot_read') $error_message = __('Could not read the uploaded file.', 'procedure-pricing-widget');

                echo '<div id="message" class="error notice is-dismissible"><p>' . esc_html( $error_message ) . '</p></div>';
            }
             elseif ( $status === 'nonce_fail' ) {
                 echo '<div id="message" class="error notice is-dismissible"><p>' . esc_html__( 'Security check failed. Please try again.', 'procedure-pricing-widget' ) . '</p></div>';
             }
        }
        ?>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="mpw_handle_csv_import">
            <?php wp_nonce_field( 'mpw_csv_import_nonce', 'mpw_csv_nonce' ); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="mpw_csv_file"><?php _e( 'CSV File', 'procedure-pricing-widget' ); ?></label>
                    </th>
                    <td>
                        <input type="file" id="mpw_csv_file" name="mpw_csv_file" accept=".csv, text/csv" required>
                         <p class="description"><?php _e( 'Must be a .csv file.', 'procedure-pricing-widget' ); ?></p>
                    </td>
                </tr>
                 <tr valign="top">
                     <th scope="row"><?php _e( 'Options', 'procedure-pricing-widget' ); ?></th>
                     <td>
                        <label for="mpw_skip_header">
                            <input name="mpw_skip_header" type="checkbox" id="mpw_skip_header" value="1" checked>
                            <?php _e( 'Skip Header Row', 'procedure-pricing-widget' ); ?>
                        </label>
                     </td>
                 </tr>
            </table>

            <?php submit_button( __( 'Import Items', 'procedure-pricing-widget' ) ); ?>
        </form>
    </div>
    <?php
}

/**
 * Handle the CSV Import form submission via admin-post.php
 */
function mpw_handle_csv_import_action() {
    // 1. Security Checks
    if ( ! isset( $_POST['mpw_csv_nonce'] ) || ! wp_verify_nonce( $_POST['mpw_csv_nonce'], 'mpw_csv_import_nonce' ) ) {
         wp_redirect( admin_url( 'edit.php?post_type=pricing_item&page=mpw-csv-import&mpw_import_status=nonce_fail' ) );
         exit;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
         wp_die( __( 'You do not have sufficient permissions to access this page.', 'procedure-pricing-widget' ) );
    }

     // 2. Check if ACF is active
     if ( ! function_exists('update_field') ) {
         wp_redirect( admin_url( 'edit.php?post_type=pricing_item&page=mpw-csv-import&mpw_import_status=error&error_code=acf_inactive' ) );
         exit;
     }


    // 3. Handle File Upload
    if ( ! isset( $_FILES['mpw_csv_file'] ) || $_FILES['mpw_csv_file']['error'] !== UPLOAD_ERR_OK ) {
        wp_redirect( admin_url( 'edit.php?post_type=pricing_item&page=mpw-csv-import&mpw_import_status=error&error_code=no_file' ) );
        exit;
    }

     // Check file type (basic check)
     $file_info = wp_check_filetype( basename( $_FILES['mpw_csv_file']['name'] ) );
     if ( empty($file_info['ext']) || !in_array(strtolower($file_info['ext']), ['csv']) ) {
        wp_redirect( admin_url( 'edit.php?post_type=pricing_item&page=mpw-csv-import&mpw_import_status=error&error_code=no_file' ) );
        exit;
    }


    // Note: wp_handle_upload is typically for moving to the uploads dir.
    // For temporary processing, using the tmp_name directly is fine, but ensure cleanup.
    $file_path = $_FILES['mpw_csv_file']['tmp_name'];


    // 4. Process CSV File
    $handle = fopen( $file_path, 'r' );
    if ( ! $handle ) {
         wp_redirect( admin_url( 'edit.php?post_type=pricing_item&page=mpw-csv-import&mpw_import_status=error&error_code=cannot_read' ) );
         exit;
    }

    $skip_header = isset( $_POST['mpw_skip_header'] ) && $_POST['mpw_skip_header'] == '1';
    $imported_count = 0;
    $skipped_count = 0;
    $error_count = 0;
    $row_num = 0;

    // Increase execution time limit - may not work on all hosts
    @set_time_limit(300); // 5 minutes

    if ( $skip_header ) {
        fgetcsv( $handle ); // Read and discard header row
        $row_num++;
    }

    while ( ( $row = fgetcsv( $handle ) ) !== false ) {
         $row_num++;

         // Basic check for expected columns
         if ( count( $row ) < 4 ) {
             error_log("Procedure Import: Skipping row #{$row_num}: Incorrect column count.");
             $skipped_count++;
             continue;
         }

        // Assuming CSV columns: 0=Name, 1=CPT, 2=Price, 3=Description
         $procedure_name = sanitize_text_field( trim($row[0]) );
         $cpt_code = sanitize_text_field( trim($row[1]) );
         $estimated_price = sanitize_text_field( trim($row[2]) );
         $description = wp_kses_post( trim($row[3]) ); // Allow basic HTML, sanitize

        // Basic validation
        if ( empty( $procedure_name ) ) {
             error_log("Procedure Import: Skipping row #{$row_num}: Procedure Name empty.");
            $skipped_count++;
            continue;
        }

         // --- Optional: Duplicate Check (Example: by CPT Code) ---
          $existing_post = get_posts(array(
              'post_type' => 'pricing_item',
              'post_status' => 'any',
              'meta_key' => 'mpw_cpt_code',
              'meta_value' => $cpt_code,
              'posts_per_page' => 1,
              'fields' => 'ids'
          ));

          if ( !empty($existing_post) && !empty($cpt_code) ) {
              error_log("Procedure Import: Skipping row #{$row_num}: Duplicate CPT '{$cpt_code}' (Post ID: {$existing_post[0]}).");
              $skipped_count++;
              continue;
          }
         // --- End Duplicate Check ---

         // --- Prepare and Insert Post ---
         $post_data = array(
             'post_title'   => $procedure_name,
             'post_content' => $description,
             'post_type'    => 'pricing_item',
             'post_status'  => 'publish',
             'post_author'  => get_current_user_id(), // Assign to current user
         );

         $post_id = wp_insert_post( $post_data, true ); // Pass true to return WP_Error on failure

         if ( is_wp_error( $post_id ) ) {
             error_log( "Procedure Import Error: Failed to insert post row #{$row_num} ('{$procedure_name}'). Error: " . $post_id->get_error_message() );
             $error_count++;
             continue;
         }

         // --- Update ACF Fields ---
         update_field( 'mpw_cpt_code', $cpt_code, $post_id );
         update_field( 'mpw_estimated_price', $estimated_price, $post_id );

         $imported_count++;

         // Optional: Add a small sleep to prevent hammering the server on shared hosts
         // usleep(50000); // 50 milliseconds

    } // End while loop

    fclose( $handle );

    // 5. Redirect back with status
    $redirect_url = add_query_arg(
        array(
            'mpw_import_status' => 'success',
            'count' => $imported_count,
             'skipped' => $skipped_count,
             'errors' => $error_count
        ),
        admin_url( 'edit.php?post_type=pricing_item&page=mpw-csv-import' )
    );
    wp_redirect( $redirect_url );
    exit;
}
// Hook the handler to admin-post action
add_action( 'admin_post_mpw_handle_csv_import', 'mpw_handle_csv_import_action' );