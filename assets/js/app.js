const sidebar = document.querySelector('[data-sidebar]');
const backdrop = document.querySelector('[data-sidebar-backdrop]');
const toggle = document.querySelector('[data-sidebar-toggle]');

function closeSidebar() {
  document.body.classList.remove('sidebar-open');
}

if (toggle) {
  toggle.addEventListener('click', () => {
    document.body.classList.toggle('sidebar-open');
  });
}

if (backdrop) {
  backdrop.addEventListener('click', closeSidebar);
}

document.querySelectorAll('[data-confirm]').forEach((element) => {
  element.addEventListener('click', (event) => {
    const message = element.getAttribute('data-confirm') || 'Yakin mau lanjut?';
    if (!confirm(message)) {
      event.preventDefault();
    }
  });
});

document.querySelectorAll('[data-preview-input]').forEach((input) => {
  input.addEventListener('change', () => {
    const target = document.querySelector(input.dataset.previewInput);
    const file = input.files && input.files[0];

    if (!target || !file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
      target.src = event.target.result;
      target.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
  });
});
