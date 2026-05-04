function toggleAccountMenu() {
  const menu    = document.getElementById('accountMenu');
  const chevron = document.getElementById('accountChevron');
  menu.classList.toggle('open');
  chevron.classList.toggle('open');
}

document.addEventListener('click', function (e) {
  const dropdown = document.querySelector('.account-dropdown');
  if (dropdown && !dropdown.contains(e.target)) {
    document.getElementById('accountMenu')?.classList.remove('open');
    document.getElementById('accountChevron')?.classList.remove('open');
  }
});


window.addEventListener('scroll', function () {
  const navbar = document.querySelector('.navbar-playzone');
  if (navbar) {
    navbar.classList.toggle('scrolled', window.scrollY > 10);
  }
});