<!-- FOOTER -->
  <footer class="bg-dark text-light py-4 mt-auto border-top border-secondary">
    <div class="container text-center small">
      <p class="mb-1 fw-semibold">Cursolandia.com &copy; 2026 - Plataforma de Aprendizaje</p>
      <p class="text-muted mb-0">Tecnicatura Universitaria en Web | Ingeniería de Software & Programación III</p>
    </div>
  </footer>

  <!-- Bootstrap 5 JS Bundle CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- SCRIPT PARA EL MODO CLARO/OSCURO -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const toggleBtn = document.getElementById('themeToggle');
      const htmlElement = document.documentElement;
      const icon = toggleBtn.querySelector('i');

      toggleBtn.addEventListener('click', () => {
        const currentTheme = htmlElement.getAttribute('data-bs-theme');
        
        if (currentTheme === 'light') {
          htmlElement.setAttribute('data-bs-theme', 'dark');
          icon.classList.replace('bi-moon-fill', 'bi-sun-fill');
          toggleBtn.classList.replace('btn-outline-secondary', 'btn-outline-warning');
        } else {
          htmlElement.setAttribute('data-bs-theme', 'light');
          icon.classList.replace('bi-sun-fill', 'bi-moon-fill');
          toggleBtn.classList.replace('btn-outline-warning', 'btn-outline-secondary');
        }
      });
    });
  </script>
</body>
</html>