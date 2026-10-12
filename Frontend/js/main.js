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