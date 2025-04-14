jQuery(document).ready(function ($) {
    'use strict';

    // --- Configuration ---
    // UPDATED SELECTOR: Target the input *inside* the element with your ID
    const searchInputSelector = '#mpw-procedure-search-input .x-search-input';
    const searchFormSelector = '#mpw-procedure-search-input'; // Selector for the form itself
    const resultsContainerSelector = '#mpw-procedure-search-results'; // This ID seems correct based on your HTML
    const loadingIndicatorSelector = '#mpw-loading-indicator'; // Optional: Update ID for a loading spinner element
    const debounceDelay = 350; // Milliseconds delay after typing stops before sending AJAX request
    const minQueryLength = 3; // Minimum characters needed to trigger search

    // --- DOM Elements ---
    const $searchInput = $(searchInputSelector); // Now targets the actual input field
    const $searchForm = $(searchFormSelector);   // Target the form element
    const $resultsContainer = $(resultsContainerSelector);
    const $loadingIndicator = $(loadingIndicatorSelector); // Optional loading indicator

    // --- Debounce Function ---
    // (Keep the debounce function as it was)
    function debounce(func, wait, immediate) {
        var timeout;
        return function () {
            var context = this, args = arguments;
            var later = function () {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }


    // --- Search Handler (triggered by typing) ---
    const handleSearch = debounce(function () {
        // Now $searchInput correctly refers to the input field
        const query = $searchInput.val().trim();

        // Clear results and hide loading if query is too short or empty
        if (query.length < minQueryLength) {
            $resultsContainer.empty().hide(); // Clear and hide
             if ($loadingIndicator.length) $loadingIndicator.hide();
            return;
        }

        // Show loading indicator (optional)
         if ($loadingIndicator.length) $loadingIndicator.show();
         $resultsContainer.empty().hide(); // Clear previous results while loading


        // --- AJAX Request ---
        $.ajax({
            url: mpw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mpw_search_procedures',
                nonce: mpw_ajax.nonce,
                query: query
            },
            dataType: 'json',
            success: function (response) {
                 if ($loadingIndicator.length) $loadingIndicator.hide();

                if (response.success) {
                    displayResults(response.data);
                } else {
                    displayError(mpw_ajax.error_message);
                    console.error("Search failed:", response);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                 if ($loadingIndicator.length) $loadingIndicator.hide();
                displayError(mpw_ajax.error_message);
                console.error("AJAX Error:", textStatus, errorThrown, jqXHR);
            }
        });

    }, debounceDelay);

    // --- Display Results ---
    // (Keep the displayResults function as it was)
    function displayResults(results) {
        $resultsContainer.empty();

        if (results && results.length > 0) {
            const resultsHtml = results.map(item => `
                <div class="mpw-result-item">
                    <h4 class="mpw-result-title">${escapeHtml(item.title)}</h4>
                    ${item.cpt_code ? `<p class="mpw-result-cpt"><strong>CPT Code:</strong> ${escapeHtml(item.cpt_code)}</p>` : ''}
                    ${item.price ? `<p class="mpw-result-price"><strong>Estimated Price:</strong> ${escapeHtml(item.price)}</p>` : ''}
                    ${item.description ? `<div class="mpw-result-description">${item.description}</div>` : ''}
                </div>
            `).join('');

            $resultsContainer.html(resultsHtml).show();
        } else {
            $resultsContainer.html(`<p class="mpw-no-results">${mpw_ajax.no_results_message}</p>`).show();
        }
    }


    // --- Display Error ---
    // (Keep the displayError function as it was)
     function displayError(message) {
        $resultsContainer.html(`<p class="mpw-error">${escapeHtml(message)}</p>`).show();
    }

    // --- HTML Escaping Helper ---
    // (Keep the escapeHtml function as it was)
     function escapeHtml(unsafe) {
        if (unsafe === null || typeof unsafe === 'undefined') return '';
        return String(unsafe)
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
     }

    // --- Event Listeners ---
    if ($searchInput.length) {
        // Listen for typing in the input field
        $searchInput.on('input', handleSearch);
    } else {
        console.warn('Procedure Pricing Widget: Search input field (' + searchInputSelector + ') not found.');
    }

    if ($searchForm.length) {
         // Listen for the form submission event (e.g., pressing Enter)
         $searchForm.on('submit', function(event) {
            event.preventDefault(); // Prevent the default page reload
            handleSearch(); // Optionally trigger the search immediately on Enter
            // Or do nothing extra if the 'input' event already covers it.
         });
    } else {
        console.warn('Procedure Pricing Widget: Search form (' + searchFormSelector + ') not found.');
    }


    // Check results container exists
    if (!$resultsContainer.length) {
         console.warn('Procedure Pricing Widget: Results container (' + resultsContainerSelector + ') not found.');
    }
     // Check loading indicator exists (optional)
     if (!$loadingIndicator.length) {
          console.info('Procedure Pricing Widget: Loading indicator (' + loadingIndicatorSelector + ') not found (optional).');
     }

});
