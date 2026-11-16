document.addEventListener('DOMContentLoaded', () => {
  const fechaInicio = document.getElementById('fecha_inicio');
  const fechaFin = document.getElementById('fecha_fin');

  if (fechaInicio && fechaFin) {
    fechaInicio.addEventListener('change', () => {
      if (fechaInicio.value) {
        // Establece como mínimo permitido el mismo día de inicio
        fechaFin.min = fechaInicio.value;

        // Si ya había una fecha de fin elegida y quedó anterior a la de inicio, la ajusta
        if (fechaFin.value && fechaFin.value < fechaInicio.value) {
          fechaFin.value = fechaInicio.value;
        }
      }
    });
  }
});