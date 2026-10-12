document.addEventListener('DOMContentLoaded', () => {
  const tituloInput = document.getElementById('titulo');
  const comprobanteContainer = document.getElementById('comprobanteContainer');
  const comprobanteInput = document.getElementById('comprobanteTitulo');

  // Solo ejecutamos la lógica si estamos en la pantalla que tiene estos elementos
  if (tituloInput && comprobanteContainer && comprobanteInput) {
    
    const toggleComprobante = () => {
      if (tituloInput.value.trim().length > 0) {
        comprobanteContainer.classList.remove('d-none');
      } else {
        comprobanteContainer.classList.add('d-none');
        comprobanteInput.value = ''; // Limpiamos el archivo si borra el título
      }
    };

    // Revisamos al cargar la página
    toggleComprobante();

    // Revisamos cada vez que el usuario escribe
    tituloInput.addEventListener('input', toggleComprobante);
  }
});


