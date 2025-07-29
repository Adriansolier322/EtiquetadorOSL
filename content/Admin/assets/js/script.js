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
});

// --- General Functions (can be placed outside DOMContentLoaded if they don't rely on DOM elements immediately) ---

/**
 * Validates a form for required fields and provides basic visual feedback.
 * @param {string} formId The ID of the form to validate.
 * @returns {boolean} True if the form is valid, false otherwise.
 */
function validateForm(formId) {
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
}