document.addEventListener('DOMContentLoaded', function () {
  const navToggle = document.getElementById('termairNavToggle');
  const navMenu = document.getElementById('termairNavMenu');
  const dropdown = document.getElementById('termairDropdownObras');
  const dropdownToggle = document.getElementById('termairDropdownToggle');

  if (navToggle && navMenu) {
    navToggle.addEventListener('click', function () {
      navMenu.classList.toggle('active');
    });
  }

  if (dropdown && dropdownToggle) {
    dropdownToggle.addEventListener('click', function (e) {
      if (window.innerWidth <= 768) {
        e.preventDefault();
        dropdown.classList.toggle('active');
      }
    });
  }

  window.addEventListener('resize', function () {
    if (window.innerWidth > 768) {
      navMenu.classList.remove('active');
      dropdown.classList.remove('active');
    }
  });
});