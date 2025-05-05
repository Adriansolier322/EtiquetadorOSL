sessionStorage.clear()
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
    
  function deletePdf(pdfPath, buttonElement) {
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
            fetch('delete_pdf.php', {
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