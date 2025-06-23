// Funciones generales
document.addEventListener('DOMContentLoaded', function() {

    
    // Cerrar sesión después de inactividad
    let inactivityTime = function() {
        let time;
        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;
        
        function logout() {
            window.location.href = 'logout.php';
        }
        
        function resetTimer() {
            clearTimeout(time);
            time = setTimeout(logout, 1800000); // 30 minutos
        }
    };
    
    inactivityTime();
});

// Funciones específicas para formularios
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'red';
            isValid = false;
        } else {
            input.style.borderColor = '#ddd';
        }
    });
    
    return isValid;
}