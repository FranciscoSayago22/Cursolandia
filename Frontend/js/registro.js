document.addEventListener('DOMContentLoaded', () => {
    const tituloInput = document.getElementById('titulo');
    const comprobanteContainer = document.getElementById('comprobanteContainer');
    const comprobanteInput = document.getElementById('comprobanteTitulo');

    tituloInput.addEventListener('input', function() {
       if (this.value.trim().length > 0) {
          comprobanteContainer.classList.remove('d-none');
          comprobanteInput.setAttribute('required', 'required');
        } else {
          comprobanteContainer.classList.add('d-none');
          comprobanteInput.removeAttribute('required');
          comprobanteInput.value = '';
        }
    });
});
