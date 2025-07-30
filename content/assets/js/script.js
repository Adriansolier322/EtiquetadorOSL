function showRegisterForm(event) {
    event.preventDefault(); // Prevent default link behavior (page reload)
    document.getElementById('login-section').style.display = 'none';
    document.getElementById('register-section').style.display = 'block';
    document.getElementById('register-username').focus(); // Focus on username field
}

function showLoginForm(event) {
    event.preventDefault(); // Prevent default link behavior (page reload)
    document.getElementById('register-section').style.display = 'none';
    document.getElementById('login-section').style.display = 'block';
    document.getElementById('login-username').focus(); // Focus on username field
}