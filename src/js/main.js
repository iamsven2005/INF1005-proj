document.addEventListener("DOMContentLoaded", function () {
  activateMenu();
});

// not sure what this does.. highlights the nav link?
function activateMenu() {
  const navLinks = document.querySelectorAll('nav a');
  navLinks.forEach(link => {
    if (link.href === location.href) {
      link.classList.add('active');
    }
  })
}
