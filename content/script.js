document.addEventListener('DOMContentLoaded', () => {

    const toggle = document.getElementById('theme-toggle');

    const userPref = localStorage.getItem('theme');
    if (userPref) {
        document.body.classList.add(userPref);
    } else {
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.body.classList.add('dark');
        } else {
        document.body.classList.add('light');
        }
    }

    toggle.addEventListener('click', () => {
        document.body.classList.toggle('dark');
        document.body.classList.toggle('light');
        const currentTheme = document.body.classList.contains('dark') ? 'dark' : 'light';
        localStorage.setItem('theme', currentTheme);
    
    });
    

    });

function disableSaveAsk(){
    window.onbeforeunload = null;
    refresh_iframe()
}

function refresh_iframe() {
    const iframe = document.getElementById('iframe_preview');
    
    fetch('pdf/generado.pdf', { method: 'HEAD' })
        .then(response => {
            if (response.ok) {
                return nuevaRuta = 'pdf/generado.pdf';
            } else {
                return nuevaRuta = 'pdf/etiqueta_preview.pdf';
            }
        })
        .then(data => {
            // Añadir timestamp para evitar caché
            iframe.src = `${data}?t=${Date.now()}`;
        })

}
      


// Función para cargar y mostrar modelos
function loadModel() {
    try {
    fetch('get_saved_models.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta');
        }
        return response.json();
    })
    .then(data => {
        let htmlButtons = '';
        data.forEach(model => {
            htmlButtons += `
                <div class="saved-file-item">
                    <button class="popup-btn" onclick="applyModel('${model.model}')">${model.name}</button>
                    <button class="popup-delete-btn popup-edit-btn" onclick="editModelName('${model.name}', '${model.id}', this)">
                        <svg width="20" height="20" viewBox="0 0 24 24" id="_24x24_On_Light_Edit" data-name="24x24/On Light/Edit" xmlns="http://www.w3.org/2000/svg">
                        <rect id="view-box" width="16" height="16" fill="none"/>
                        <path id="Shape" d="M.75,17.5A.751.751,0,0,1,0,16.75V12.569a.755.755,0,0,1,.22-.53L11.461.8a2.72,2.72,0,0,1,3.848,0L16.7,2.191a2.72,2.72,0,0,1,0,3.848L5.462,17.28a.747.747,0,0,1-.531.22ZM1.5,12.879V16h3.12l7.91-7.91L9.41,4.97ZM13.591,7.03l2.051-2.051a1.223,1.223,0,0,0,0-1.727L14.249,1.858a1.222,1.222,0,0,0-1.727,0L10.47,3.91Z" transform="translate(3.25 3.25)" fill="currentColor"/>
                        </svg>
                    </button>
                    <button class="popup-delete-btn" style="padding: 12px;" onclick="deleteModel('${model.name}', '${model.id}', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"></path>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>

                </div>
                <hr>
            `;
        })
        return htmlButtons;
    })
    .then(data => {
        // Mostrar el popup con SweetAlert
        Swal.fire({
            title: 'Modelos guardados',
            html: data,
            showConfirmButton: false,
            width: '600px',
            customClass: {
                popup: 'my-popup-class',
                confirmButton: 'my-confirm-button',
                cancelButton: 'my-cancel-button'
            }
        })
    })
    } catch (error) {
        console.error('Error en loadModel:', error);
        Swal.fire({
            title: 'Error',
            text: 'Ocurrió un error al cargar los modelos',
            icon: 'error',
            customClass: {
                popup: 'my-popup-class',
                confirmButton: 'my-confirm-button'
            }
        });
    }
}

// Función para aplicar un modelo al formulario
function applyModel(modelId) {
    try {
        // Hacer una petición al archivo PHP
        fetch(`get_model.php?modelId=${encodeURIComponent(modelId)}`)
        .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta');
        }
        return response.json();
        })
        .then(data => {
            data.forEach(model =>{
                // Aquí tienes acceso a los datos
                console.log(model)
                document.getElementById('board_type').value = model.board_type || 'bios';
                document.getElementById('cpu_name').value = model.cpu_name || 'Indefinido';
                document.getElementById('ram_capacity').value = model.ram_capacity || 'Indefinido';
                document.getElementById('ram_type').value = model.ram_type || 'ddr2';
                document.getElementById('disc_capacity').value = model.disc_capacity || 'Indefinido';
                document.getElementById('disc_type').value = model.disc_type || 'hdd';
                document.getElementById('gpu_name').value = model.gpu_name || 'Indefinido';
                document.getElementById('gpu_type').value = model.gpu_type || 'integrada';
                document.getElementById('observaciones').value = model.obser || '';
                // Radio buttons
                if (model.wifi === 'true') {
                    document.getElementById('wifi_si').checked = true;
                } else {
                    document.getElementById('wifi_no').checked = true;
                }
                
                if (model.bluetooth === 'true') {
                    document.getElementById('bluetooth_si').checked = true;
                } else {
                    document.getElementById('bluetooth_no').checked = true;
                }
                // Actualizar la selección visual de los radio buttons
                updateRadioSelection('wifi');
                updateRadioSelection('bluetooth');
            })
        })
        Swal.close();
        
    } catch (error) {
        console.error('Error en applyModel:', error);
    }
}

// Función para eliminar un modelo
function deleteModel(modelName, modelId, buttonElement) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Quieres eliminar el modelo "${modelName}"?`,
        showCancelButton: true,
        confirmButtonText: 'Sí, borrarlo',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'my-popup-class',
            confirmButton: 'my-confirm-button',
            cancelButton: 'my-cancel-button'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            try {
                fetch(`delete_save_model.php?modelId=${encodeURIComponent(modelId)}`)
            
                // Eliminar el elemento del DOM
                if (buttonElement && buttonElement.closest('.saved-file-item')) {
                    buttonElement.closest('.saved-file-item').remove();
                }
                
                Swal.fire({
                    title: '¡Eliminado!',
                    text: `El modelo "${modelName}" ha sido eliminado.`,
                    icon: 'success',
                    customClass: {
                        popup: 'my-popup-class',
                        confirmButton: 'my-confirm-button'
                    }
                });
            } catch (error) {
                console.error('Error en deleteModel:', error);
            }
        }
    });
}

// Función auxiliar para actualizar radio buttons
function updateRadioSelection(name) {
    const radioInput = document.querySelector(`input[name="${name}"]:checked`)?.closest('.radio-input');
    if (radioInput) {
        const selection = radioInput.querySelector('.selection');
        if (selection) {
            const isTrue = document.querySelector(`input[name="${name}"]:checked`).value === 'true';
            selection.style.transform = isTrue ? 'translateX(0%)' : 'translateX(100%)';
            selection.style.backgroundColor = isTrue ? getComputedStyle(document.documentElement).getPropertyValue('--radio-green') : 
                                                     getComputedStyle(document.documentElement).getPropertyValue('--radio-red');
        }
    }
}

function editModelName(modelName, modelId) {
    Swal.close()
    const form = `<div class="saved-file-item">
                    <input type="text" placeholder="Nombre nuevo" id="nameChange" autofocus maxlength=20>
                </div>`;
    


    Swal.fire({
        title: `Editar nombre del modelo "${modelName}"`,
        html: form,
        showCancelButton: true,
        confirmButtonText: 'Cambiar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'my-popup-class',
            confirmButton: 'my-confirm-button',
            cancelButton: 'my-cancel-button'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            try {
                const newName = document.getElementById("nameChange").value
                fetch(`edit_model_name.php?modelId=${encodeURIComponent(modelId)}&modelName=${encodeURIComponent(newName)}`)
                
                Swal.fire({
                    title: '¡Nombre cambiado con exito',
                    text: `Nombre cambiado a "${newName}".`,
                    icon: 'success',
                    customClass: {
                        popup: 'my-popup-class',
                        confirmButton: 'my-confirm-button'
                    }
                }).then((result) => {
                    if (result.isConfirmed){
                        loadModel()
                    }
                })
            } catch (error) {
                console.error('Error en editmodel:', error);
            }
        }
    });
}