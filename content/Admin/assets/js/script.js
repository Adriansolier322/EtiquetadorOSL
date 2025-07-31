document.addEventListener('DOMContentLoaded', () => {
    // --- Theme Toggle Logic ---
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

    // --- Inactivity Logout Logic ---
    let inactivityTimer; // Use a more descriptive name

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

    // --- General Functions (can be placed outside DOMContentLoaded if they don't rely on DOM elements immediately) ---

    /**
     * Validates a form for required fields and provides basic visual feedback.
     * @param {string} formId The ID of the form to validate.
     * @returns {boolean} True if the form is valid, false otherwise.
     */
    // This function is defined inside DOMContentLoaded, making it local to this scope.
    // If you need to call it from an inline HTML event handler (like an `onclick` on a submit button),
    // you might need to move it outside the DOMContentLoaded listener or make it globally accessible.
    // For now, I'll keep it here as the prompt provided it as part of the main script.
    window.validateForm = function(formId) { // Making it global for potential external calls
        const form = document.getElementById(formId);
        if (!form) {
            console.error(`Form with ID '${formId}' not found.`);
            return false;
        }

        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            const errorMessageId = `${input.id}-error`;
            let errorMessageElement = document.getElementById(errorMessageId);

            if (!input.value.trim()) {
                input.classList.add('is-invalid'); // Add a class for invalid state
                if (!errorMessageElement) {
                    // Create and append an error message if it doesn't exist
                    errorMessageElement = document.createElement('div');
                    errorMessageElement.id = errorMessageId;
                    errorMessageElement.classList.add('error-message'); // Add a class for styling
                    errorMessageElement.textContent = 'Este campo es obligatorio.';
                    input.parentNode.insertBefore(errorMessageElement, input.nextSibling);
                }
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
                if (errorMessageElement) {
                    errorMessageElement.remove(); // Remove error message if field is valid
                }
            }
        });

        return isValid;
    };

    // Initialize 'Other' input visibility on page load (from previous context)
    // This part should be called within DOMContentLoaded as it relies on DOM elements.
    function toggleOtherInput(selectId, otherContainerId) {
        const selectElement = document.getElementById(selectId);
        const otherContainer = document.getElementById(otherContainerId);
        const otherInput = otherContainer.querySelector('input[type="text"]');

        if (otherInput) {
            if (selectElement.value === '_OTHER_') {
                otherContainer.style.display = 'block';
                otherInput.setAttribute('required', 'required');
            } else {
                otherContainer.style.display = 'none';
                otherInput.removeAttribute('required');
                otherInput.value = '';
            }
        }
    }

    // Call toggleOtherInput for relevant fields on page load
    toggleOtherInput('cpu_name', 'cpu_name_other_container');
    toggleOtherInput('ram_capacity', 'ram_capacity_other_container');
    toggleOtherInput('disc_capacity', 'disc_capacity_other_container');
    toggleOtherInput('gpu_name', 'gpu_name_other_container');
    // The other fields (board_type, ram_type, disc_type, gpu_type) no longer have 'Other' inputs
    // so their respective toggleOtherInput calls are not strictly necessary but harmless if left.
});
