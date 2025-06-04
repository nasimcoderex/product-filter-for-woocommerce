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
            handle: '.wcfp-filter-name',
            update: function(event, ui) {
                console.log('Filter order updated in UI.');
                update_hidden_filter_data(); // Update on reorder
            }
        });
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

            const newFilterItem = $(
                '<li class="wcfp-filter-item">' +
                '<span class="wcfp-filter-name">' + filterDisplayName + '</span>' +
                '<span class="wcfp-filter-type-indicator">(Type: ' + newFilterType.charAt(0).toUpperCase() + newFilterType.slice(1) + ')</span>' +
                '<span class="wcfp-filter-controls">' +
                '<button type="button" class="button button-small wcfp-edit-filter-button">Edit</button>' +
                '<button type="button" class="button button-small button-link-delete wcfp-remove-filter-button">Remove</button>' +
                '</span>' +
                '</li>'
            );
            // Set data attributes on the new jQuery object before appending
            newFilterItem.attr('data-filter-id', newFilterId);
            newFilterItem.attr('data-filter-type', newFilterType);
            // Storing the display name in data-filter-name for consistency, though it's also the text of .wcfp-filter-name
            newFilterItem.attr('data-filter-name', filterDisplayName);
            // newFilterItem.data('filter-settings', {}); // Initialize with empty settings

            const $noFiltersMessage = $filterList.find('.wcfp-no-filters-message');
            if ($noFiltersMessage.length) {
                $noFiltersMessage.remove();
            }
            $filterList.append(newFilterItem);
            console.log('Added filter: ' + filterName);
            update_hidden_filter_data(); // Update after adding
        });
    }

    // Event listener for Edit buttons
    if ($filterList.length) {
        $filterList.on('click', '.wcfp-edit-filter-button', function() {
            const $filterItem = $(this).closest('.wcfp-filter-item');
            const filterName = $filterItem.find('.wcfp-filter-name').text();
            const filterType = $filterItem.data('filter-type') || 'N/A'; // Read type from data attribute
            const filterId = $filterItem.data('filter-id'); // Read ID from data attribute

            if ($configPanel.length) {
                $configPanel.html(
                    '<h3>Configure Filter: ' + $('<div/>').text(filterName).html() + '</h3>' +
                    '<p><strong>ID:</strong> ' + $('<div/>').text(filterId).html() + '</p>' +
                    '<p><strong>Type:</strong> ' + $('<div/>').text(filterType.charAt(0).toUpperCase() + filterType.slice(1)).html() + '</p>' +
                    '<p>Filter-specific settings for type "' + $('<div/>').text(filterType).html() + '" will go here.</p>' +
                    // Example: '<label>Setting 1: <input type="text" class="wcfp-filter-setting" name="setting1_for_' + filterId + '"></label>' +
                    '<p><button type="button" class="button button-primary" id="wcfp-save-filter-settings">Save Filter</button> ' +
                    '<button type="button" class="button" id="wcfp-close-config-panel">Done</button></p>'
                );
                $configPanel.data('editing-filter-id', filterId); // Store ID for save
                console.log('Editing filter: ' + filterName + ', Type: ' + filterType + ', ID: ' + filterId);
            }
        });
    }

    // Event listener for Remove buttons
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
