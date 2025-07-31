document.addEventListener('DOMContentLoaded', () => {
    // --- Theme Toggle Logic (keep if you have it) ---
    const themeToggle = document.getElementById('theme-toggle');

    // 1. Check user's previously saved preference
    const userPref = localStorage.getItem('theme');

    // 2. Apply theme based on preference or system settings
    if (userPref === 'dark') {
        document.body.classList.add('dark');
    } else if (userPref === 'light-mode') {
        document.body.classList.remove('dark');
    } else {
        // If no preference, check system prefers-color-scheme
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.body.classList.add('dark');
        }
    }

    // 3. Add event listener for the toggle button
    if (themeToggle) { // Ensure the button exists before adding listener
        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark');
            const currentTheme = document.body.classList.contains('dark') ? 'dark' : 'light-mode';
            localStorage.setItem('theme', currentTheme);
        });
    }

    // --- Inactivity Logout Logic (keep if you have it) ---
    let inactivityTimer;

    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        // Set timeout for 30 minutes (1800000 milliseconds)
        inactivityTimer = setTimeout(() => {
            // Redirect to logout page
            window.location.href = 'logout.php';
        }, 1800000);
    }

    // Attach event listeners for user activity
    ['load', 'mousemove', 'keypress', 'scroll', 'click'].forEach(eventType => {
        window.addEventListener(eventType, resetInactivityTimer);
    });

    // Initialize the timer
    resetInactivityTimer();

    // --- Select2 Initialization for Searchable Dropdowns ---
    // IMPORTANT: Ensure jQuery and Select2 are loaded BEFORE this script executes.
    // In edit_pc.php, these are loaded just before script.js.
    const select2Selectors = ['#cpu_name', '#ram_capacity', '#disc_capacity', '#gpu_name'];

    select2Selectors.forEach(selector => {
        const selectElement = $(selector); // Use jQuery to select the element
        if (selectElement.length) { // Check if the element exists on the page
            selectElement.select2({
                tags: true, // Allow users to add new options by typing
                placeholder: 'Seleccione o escriba un valor', // Placeholder text
                allowClear: true, // Allow clearing the selection
                width: '100%', // Make it take full width
                // The 'createTag' function is crucial for handling custom inputs.
                // It ensures that when a new value is typed, Select2 creates a new option
                // with its value set to the typed text, and this value will be sent to the server.
                createTag: function (params) {
                    const term = $.trim(params.term);
                    if (term === '') {
                        return null;
                    }
                    return {
                        id: term, // Use the typed text as the ID for a new tag
                        text: term, // Use the typed text as the display text
                        newTag: true // Add a flag to identify new tags if needed later
                    };
                },
                // Customize how new tags are displayed in the results list
                // (Optional, for better UX)
                templateResult: function(data) {
                    var $result = $('<span></span>');
                    $result.text(data.text);
                    if (data.newTag) {
                        $result.append(' <em>(nuevo)</em>');
                    }
                    return $result;
                },
                // Customize how selected tags are displayed
                // (Optional, for better UX)
                templateSelection: function(data) {
                    return data.text;
                }
            });
        }
    });

    // No explicit JS loop here for pre-filling is typically needed if PHP renders options correctly.
    // Select2 will pick up the 'selected' attribute from the HTML options.
});