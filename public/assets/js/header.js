document.addEventListener("DOMContentLoaded", function () {

  /* ============================
     BURGER MENU
  ============================ */
  const burger = document.getElementById("burger");
  const drawer = document.getElementById("mobile-drawer");
  const backdrop = document.getElementById("backdrop");

  if (burger && drawer && backdrop) {
    burger.addEventListener("click", () => {
      const isOpen = burger.classList.toggle("open");
      drawer.classList.toggle("open", isOpen);
      backdrop.classList.toggle("active", isOpen);
    });

    backdrop.addEventListener("click", () => {
      burger.classList.remove("open");
      drawer.classList.remove("open");
      backdrop.classList.remove("active");
    });
  }

  /* ============================
     MOBILE MENU ACCORDION – MAIN (À propos, Cours…)
  ============================ */
  const menuItems = document.querySelectorAll("#mobile-drawer .menu-item");

  menuItems.forEach(item => {
    const label = item.querySelector(".menu-label");
    if (!label) return;

    label.addEventListener("click", function () {
      const isOpen = item.classList.contains("open");

      // Ferme tous les autres
      menuItems.forEach(i => i.classList.remove("open"));

      // Ouvre celui cliqué (ou referme si déjà ouvert)
      if (!isOpen) {
        item.classList.add("open");
      }
    });
  });

  /* ============================
     SUB ACCORDION – Nos centres, Niveaux
  ============================ */
  const subGroups = document.querySelectorAll("#mobile-drawer .submenu-group");

  subGroups.forEach(group => {
    const title = group.querySelector(".submenu-title");
    if (!title) return;

    title.addEventListener("click", function (e) {
      e.stopPropagation(); // empêche ouverture du parent
      group.classList.toggle("open");
    });
  });

  /* ============================
     STICKY HEADER
  ============================ */
  const header = document.querySelector(".site-header");
  if (header) {
    const offset = header.offsetTop;
    window.addEventListener("scroll", () => {
      header.classList.toggle("is-fixed", window.scrollY > offset);
    });
  }

  /* ============================
     LANGUAGE SWITCHER
  ============================ */
  document.querySelectorAll(".nav-lang-btn").forEach(btn => {
    btn.addEventListener("click", function () {
      document.querySelectorAll(".nav-lang-btn").forEach(b => {
        b.classList.remove("active-lang");
      });

      this.classList.add("active-lang");
      // redirection automatique via href
    });
  });

});
