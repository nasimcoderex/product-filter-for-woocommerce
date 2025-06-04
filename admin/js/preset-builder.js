jQuery(document).ready(function($) {
    const $filterList = $('#wcfp-filter-list');
    const $addFilterButton = $('#wcfp-add-filter-button');
    const $configPanel = $('#wcfp-filter-config-panel');
    const $settingsArea = $('#wcfp-settings-area');
    const $hiddenDataInput = $('#wcfp_filters_data_input');

    let filterCounter = 0; // Used for new items if no ID is present (e.g. for truly new items)

    function update_hidden_filter_data() {
        if (!$hiddenDataInput.length) return;

        const filtersArray = [];
        $filterList.find('li.wcfp-filter-item').each(function() {
            const $item = $(this);
            const filterId = $item.data('filter-id') || Date.now() + '_' + Math.random().toString(36).substr(2, 9); // Fallback ID
            const filterType = $item.data('filter-type') || 'category'; // Fallback type
            const filterName = $item.find('.wcfp-filter-name').text(); // Name might be from type or custom

            // For now, settings are empty. Later, this would be fetched from the config panel or data attributes.
            const filterSettings = $item.data('filter-settings') || {};

            filtersArray.push({
                id: filterId,
                type: filterType,
                name: filterName, // Storing the display name for now, might be derived from type
                settings: filterSettings
            });
        });
        $hiddenDataInput.val(JSON.stringify(filtersArray));
        console.log('Updated hidden input:', $hiddenDataInput.val());
    }

    // Initialize Sortable
    if ($filterList.length) {
        $filterList.sortable({
            axis: 'y',
            placeholder: 'wcfp-sortable-placeholder',
            handle: '.wcfp-filter-name', // Using the name as the handle
            update: function(event, ui) {
                console.log('Filter order updated in UI.');
                update_hidden_filter_data(); // Update on reorder
                $('#wcfp-drag-announcer').text('Filter order changed.'); // Announce change
            }
        });
    }

    // Helper function to find the last focused "Edit" button
    let lastFocusedEditButton = null;
    $filterList.on('focus', '.wcfp-edit-filter-button', function() {
        lastFocusedEditButton = this;
    });
    $filterList.on('blur', '.wcfp-edit-filter-button', function() {
        // Optional: clear if focus moves outside of an expected flow
        // lastFocusedEditButton = null;
    });


    // Initial state for config panel & hidden input
    if ($configPanel.length) {
        $configPanel.html('<p class="wcfp-panel-placeholder">' + 'Select a filter from the list to configure its specific settings here. Each filter type will have different options.' + '</p>');
    }

    // Initial state for config panel & hidden input
    if ($configPanel.length) {
        $configPanel.html('<p class="wcfp-panel-placeholder">' + 'Select a filter from the list to configure its specific settings here. Each filter type will have different options.' + '</p>');
    }
    // Initial call to populate hidden field if items are loaded by PHP
    // This will be more effective once PHP renders existing items with data attributes
    update_hidden_filter_data();


    if ($addFilterButton.length) {
        $addFilterButton.on('click', function() {
            filterCounter++;
            // For a truly new filter, generate a unique ID
            const newFilterId = 'new_' + Date.now();
            // Default type for new filters, this could come from a dropdown later
            const newFilterType = 'category'; // Example default type
            const filterDisplayName = 'New ' + newFilterType.charAt(0).toUpperCase() + newFilterType.slice(1) + ' Filter ' + filterCounter;

            const $editButton = $('<button type="button" class="button button-small wcfp-edit-filter-button">Edit</button>')
                .attr('aria-label', 'Edit Filter ' + filterDisplayName);
            const $removeButton = $('<button type="button" class="button button-small button-link-delete wcfp-remove-filter-button">Remove</button>')
                .attr('aria-label', 'Remove Filter ' + filterDisplayName);

            const newFilterItem = $(
                '<li class="wcfp-filter-item">' +
                '<span class="wcfp-filter-name">' + filterDisplayName + '</span>' +
                '<span class="wcfp-filter-type-indicator">(Type: ' + newFilterType.charAt(0).toUpperCase() + newFilterType.slice(1) + ')</span>' +
                '</li>'
            );
            const $controls = $('<span class="wcfp-filter-controls"></span>').append($editButton).append($removeButton);
            newFilterItem.append($controls);

            newFilterItem.attr('data-filter-id', newFilterId);
            newFilterItem.attr('data-filter-type', newFilterType);
            newFilterItem.attr('data-filter-name', filterDisplayName);

            const $noFiltersMessage = $filterList.find('.wcfp-no-filters-message');
            if ($noFiltersMessage.length) {
                $noFiltersMessage.remove();
            }
            $filterList.append(newFilterItem);
            update_hidden_filter_data();
            console.log('Added filter: ' + filterDisplayName);
            $editButton.focus(); // Move focus to the new filter's Edit button
        });
    }

    // Event listener for Edit buttons
    if ($filterList.length) {
        $filterList.on('click', '.wcfp-edit-filter-button', function(e) {
            e.preventDefault(); // Good practice for button clicks handled by JS
            lastFocusedEditButton = this; // Store this button

            const $filterItem = $(this).closest('.wcfp-filter-item');
            const filterName = $filterItem.find('.wcfp-filter-name').text();
            const filterType = $filterItem.data('filter-type') || 'N/A';
            const filterId = $filterItem.data('filter-id');

            if ($configPanel.length) {
                $configPanel.html(
                    '<h3>Configure Filter: ' + $('<div/>').text(filterName).html() + '</h3>' +
                    '<p><strong>ID:</strong> ' + $('<div/>').text(filterId).html() + '</p>' +
                    '<p><strong>Type:</strong> ' + $('<div/>').text(filterType.charAt(0).toUpperCase() + filterType.slice(1)).html() + '</p>' +
                    '<p>Filter-specific settings for type "' + $('<div/>').text(filterType).html() + '" will go here.</p>' +
                    '<p><label for="filter-setting-sample-' + filterId + '">Sample Setting: </label><input type="text" id="filter-setting-sample-' + filterId + '" class="wcfp-filter-setting-input"></p>' + // Example focusable element
                    '<p><button type="button" class="button button-primary" id="wcfp-save-filter-settings">Save Filter</button> ' +
                    '<button type="button" class="button" id="wcfp-close-config-panel">Done</button></p>'
                );
                $configPanel.data('editing-filter-id', filterId);
                console.log('Editing filter: ' + filterName + ', Type: ' + filterType + ', ID: ' + filterId);

                // Move focus to the first focusable element in the panel
                // This could be the first input, button, or the h3.
                $configPanel.find('h3, input, select, button').first().focus();
            }
        });
    }

    // Event listener for Remove buttons (already includes aria-label update from Add Filter)
    if ($filterList.length) {
        $filterList.on('click', '.wcfp-remove-filter-button', function() {
            const $filterItem = $(this).closest('.wcfp-filter-item');
            const filterName = $filterItem.find('.wcfp-filter-name').text();

            $filterItem.remove();
            console.log('Removed filter: ' + filterName);

            if ($filterList.children().length === 0) {
                $filterList.append('<li class="wcfp-no-filters-message">' + 'No filters added yet.' + '</li>');
            }

            const panelTitle = $configPanel.find('h3').text();
            if (panelTitle.includes(filterName)) { // If the removed filter was being edited
                 $configPanel.html('<p class="wcfp-panel-placeholder">' + 'Select a filter from the list to configure its specific settings here. Each filter type will have different options.' + '</p>');
            }
            update_hidden_filter_data(); // Update after removing
        });
    }

    // Event listener for closing/Done button on the config panel
    if ($settingsArea.length) {
        $settingsArea.on('click', '#wcfp-close-config-panel', function() {
            if ($configPanel.length) {
                $configPanel.html('<p class="wcfp-panel-placeholder">' + 'Select a filter to configure its settings.' + '</p>');
                console.log('Configuration panel closed.');
            }
        });

        // Placeholder for saving individual filter settings
        $settingsArea.on('click', '#wcfp-save-filter-settings', function() {
            // const $editedFilterItem = $filterList.find('.wcfp-filter-item[data-filter-id="' + $configPanel.data('editing-filter-id') + '"]');
            // Logic to extract settings from the panel and update $editedFilterItem.data('filter-settings', collectedSettings);
            console.log('Save Filter Settings clicked. Implement actual saving logic for individual filter settings.');
            // After saving settings to the filter item's data, update the hidden field:
            // update_hidden_filter_data();
            $('#wcfp-close-config-panel').click();
        });
    }
});
