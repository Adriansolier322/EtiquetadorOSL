window.onbeforeunload = null;
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
    
    refresh_iframe()
    });
    function refresh_iframe() {
        const iframe = document.getElementById('preview_iframe');
        const nuevaRuta = 'pdf/generado.pdf';
        
        // Añadir timestamp para evitar caché
        iframe.src = `${nuevaRuta}?t=${Date.now()}`;
      }
      

    function save() {
      Swal.fire({
          title: '¿Estás seguro?',
          text: "Se guardará la última preview generada",
          showCancelButton: true,
          confirmButtonText: 'Aceptar',
          cancelButtonText: 'Cancelar',
          icon: "question",
          customClass: {
              popup: 'my-popup-class',
              confirmButton: 'my-confirm-button',
              cancelButton: 'my-cancel-button'
          }
      }).then((result) => {
          if (result.isConfirmed) {
              const name = document.getElementById('ticket_name').value.trim();
              
              if (!name) {
                  alert('Por favor, ingresa un nombre válido');
                  return;
              }
              
              // Enviar el nombre al servidor para guardar el PDF
              const formData = new FormData();
              formData.append('ticket_name', name);
              
              fetch('save_pdf.php', {
                  method: 'POST',
                  body: formData
              })
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      Swal.fire({
                          title: '¡Guardado exitosamente!',
                          icon: "success",
                          customClass: {
                              popup: 'my-popup-class',
                              confirmButton: 'my-confirm-button'
                          }
                      });
                  } else {
                      Swal.fire({
                          title: 'Error',
                          text: data.message,
                          icon: 'error',
                          customClass: {
                            popup: 'my-popup-class',
                            confirmButton: 'my-confirm-button',
                            cancelButton: 'my-cancel-button'
                          }
                      });
                  }
              });
          }
      });
  }
    
  function load() {
    fetch('get_saved_pdfs.php')
        .then(response => response.json())
        .then(pdfs => {
            const htmlButtons = pdfs.map(pdf => {
                return `
                    <div class="saved-file-item">
                        <button class="popup-btn" onclick="loadPdf('${pdf.path}')">${pdf.name}</button>
                        <button class="popup-delete-btn" onclick="deletePdf('${pdf.path}', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"></path>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                `;
            }).join('');

            Swal.fire({
                title: 'Archivos guardados',
                html: htmlButtons,
                showConfirmButton: false,
                customClass: {
                  popup: 'my-popup-class',
                  confirmButton: 'my-confirm-button',
                  cancelButton: 'my-cancel-button'
                }
            });
        });
}
  
  function loadPdf(pdfPath) {
      // Actualizar el iframe con el PDF seleccionado
      const iframe = document.querySelector('.preview iframe');
      iframe.src = pdfPath + '#toolbar=0#view=Fit';
      
      // Cerrar el popup
      Swal.close();
  }
    
  function deletePdf(pdfPath) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
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
            fetch('delete_save_pdf.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ path: pdfPath })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    
                    
                    Swal.fire({
                      title: "¡Borrado!",
                      text: "El archivo ha sido eliminado.",
                      icon: "success",
                      customClass: {
                        popup: 'my-popup-class',
                        confirmButton: 'my-confirm-button',
                        cancelButton: 'my-cancel-button'
                      }
                    });
                } else {
                    Swal.fire({
                      title: "Error",
                      text: "No se pudo eliminar el archivo",
                      icon: "error",
                      customClass: {
                        popup: 'my-popup-class',
                        confirmButton: 'my-confirm-button',
                        cancelButton: 'my-cancel-button'
                      }
                    });
                }
            });
        }
    });
}



// Función para cargar y mostrar modelos
function loadModel() {
    try {
        // Obtener modelos guardados del localStorage
        let savedModels = JSON.parse(localStorage.getItem('savedModels')) || {};

        // Crear HTML para los botones de modelos
        let htmlButtons = '';
        Object.keys(savedModels).forEach(modelName => {
            htmlButtons += `
                <div class="saved-file-item">
                    <button class="popup-btn" onclick="applyModel('${modelName.replace(/'/g, "\\'")}')">${modelName}</button>
                    <button class="popup-delete-btn" onclick="deleteModel('${modelName.replace(/'/g, "\\'")}', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"></path>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            `;
        });

        // Añadir sección para guardar nuevo modelo
        htmlButtons += `
            <div style="margin-top: 20px; align-items: center;">
                <input type="text" id="newModelName" placeholder="Nombre del nuevo modelo" class="model-input">
                <button class="options-btn-save save-model-btn" onclick="saveCurrentModel()">
                    Guardar modelo actual
                </button>
            </div>
        `;

        // Mostrar el popup con SweetAlert
        Swal.fire({
            title: 'Modelos guardados',
            html: htmlButtons,
            showConfirmButton: false,
            width: '600px',
            customClass: {
                popup: 'my-popup-class',
                confirmButton: 'my-confirm-button',
                cancelButton: 'my-cancel-button'
            },
            didOpen: () => {
                // Asegurar que los eventos se asignan correctamente
                document.querySelectorAll('.popup-btn').forEach(btn => {
                    btn.onclick = function() {
                        const modelName = this.textContent.trim();
                        if (modelName === 'Guardar modelo actual') return;
                        applyModel(modelName);
                    };
                });
            }
        });

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
function applyModel(modelName) {
    try {
        const savedModels = JSON.parse(localStorage.getItem('savedModels')) || {};
        const model = savedModels[modelName];
        
        if (!model) {
            console.error('Modelo no encontrado:', modelName);
            return;
        }

        // Llenar los campos del formulario
        document.getElementById('board_type').value = model.board_type || 'bios';
        document.getElementById('cpu_name').value = model.cpu_name || 'Indefinido';
        document.getElementById('ram_capacity').value = model.ram_capacity || 'Indefinido';
        document.getElementById('ram_type').value = model.ram_type || 'ddr4';
        document.getElementById('disc_capacity').value = model.disc_capacity || 'Indefinido';
        document.getElementById('disc_type').value = model.disc_type || 'hdd';
        document.getElementById('gpu_name').value = model.gpu_name || 'Indefinido';
        document.getElementById('gpu_type').value = model.gpu_type || 'integrada';
        
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
        
        // Otros campos
        document.getElementById('sn_prefix').value = model.sn_prefix || '';
        document.getElementById('num_pag').value = model.num_pag || '1';
        document.getElementById('observaciones').value = model.observaciones || '';
        
        // Actualizar la selección visual de los radio buttons
        updateRadioSelection('wifi');
        updateRadioSelection('bluetooth');
        
        Swal.close();
        
    } catch (error) {
        console.error('Error en applyModel:', error);
    }
}

// Función para guardar el modelo actual
function saveCurrentModel() {
    try {
        const modelNameInput = document.getElementById('newModelName');
        if (!modelNameInput) {
            console.error('No se encontró el campo newModelName');
            return;
        }

        const modelName = modelNameInput.value.trim();
        if (!modelName) {
            Swal.fire({
                title: 'Error',
                text: 'Por favor, ingresa un nombre para el modelo',
                icon: 'warning',
                customClass: {
                    popup: 'my-popup-class',
                    confirmButton: 'my-confirm-button'
                }
            });
            return;
        }

        // Recopilar datos del formulario
        const formData = {
            board_type: document.getElementById('board_type').value,
            cpu_name: document.getElementById('cpu_name').value,
            ram_capacity: document.getElementById('ram_capacity').value,
            ram_type: document.getElementById('ram_type').value,
            disc_capacity: document.getElementById('disc_capacity').value,
            disc_type: document.getElementById('disc_type').value,
            gpu_name: document.getElementById('gpu_name').value,
            gpu_type: document.getElementById('gpu_type').value,
            wifi: document.querySelector('input[name="wifi"]:checked')?.value || 'false',
            bluetooth: document.querySelector('input[name="bluetooth"]:checked')?.value || 'false',
            sn_prefix: document.getElementById('sn_prefix').value,
            num_pag: document.getElementById('num_pag').value,
            observaciones: document.getElementById('observaciones').value
        };

        // Guardar en localStorage
        const savedModels = JSON.parse(localStorage.getItem('savedModels')) || {};
        savedModels[modelName] = formData;
        localStorage.setItem('savedModels', JSON.stringify(savedModels));

        // Cerrar y mostrar mensaje
        Swal.fire({
            title: '¡Modelo guardado!',
            text: `El modelo "${modelName}" ha sido guardado correctamente.`,
            icon: 'success',
            customClass: {
                popup: 'my-popup-class',
                confirmButton: 'my-confirm-button'
            }
        }).then(() => {
            // Recargar la lista de modelos
            loadModel();
        });

    } catch (error) {
        console.error('Error en saveCurrentModel:', error);
        Swal.fire({
            title: 'Error',
            text: 'Ocurrió un error al guardar el modelo',
            icon: 'error',
            customClass: {
                popup: 'my-popup-class',
                confirmButton: 'my-confirm-button'
            }
        });
    }
}

// Función para eliminar un modelo
function deleteModel(modelName, buttonElement) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Quieres eliminar el modelo "${modelName}"?`,
        icon: 'warning',
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
                const savedModels = JSON.parse(localStorage.getItem('savedModels')) || {};
                delete savedModels[modelName];
                localStorage.setItem('savedModels', JSON.stringify(savedModels));
                
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

$(document).ready(function () {
    $('textarea[data-limit-rows=true]').on('keypress', function (event) {
      var textarea = $(this),
          numberOfLines = (textarea.val().match(/\n/g) || []).length + 1,
          maxRows = parseInt(textarea.attr('rows'));
      
      if (event.which === 13 && numberOfLines === maxRows ) {
        return false;
      }
    });
  });