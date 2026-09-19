document.addEventListener("DOMContentLoaded", function () {
  if (window.lucide) {
    lucide.createIcons();
  }

  // Force the hero background video to autoplay muted/inline on mobile.
  // Some mobile browsers ignore the autoplay attribute (or briefly show the
  // native player) unless playback is also triggered via JS, with `muted`
  // set as a property rather than just an attribute.
  var heroVideo = document.getElementById("hero-video");
  if (heroVideo) {
    heroVideo.muted = true;
    heroVideo.setAttribute("muted", "");

    var tryPlay = function () {
      var playPromise = heroVideo.play();
      if (playPromise && typeof playPromise.catch === "function") {
        playPromise.catch(function () {
          // Autoplay blocked until a user gesture; retry on first touch/click.
          var resume = function () {
            heroVideo.play().catch(function () {});
            document.removeEventListener("touchstart", resume);
            document.removeEventListener("click", resume);
          };
          document.addEventListener("touchstart", resume, { once: true, passive: true });
          document.addEventListener("click", resume, { once: true });
        });
      }
    };

    if (heroVideo.readyState >= 2) {
      tryPlay();
    } else {
      heroVideo.addEventListener("loadeddata", tryPlay, { once: true });
    }
  }

  var menuBtn = document.getElementById("menu-btn");
  var mobileMenu = document.getElementById("mobile-menu");

  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener("click", function () {
      var isOpen = !mobileMenu.classList.contains("hidden");
      mobileMenu.classList.toggle("hidden");
      menuBtn.innerHTML = isOpen
        ? '<i data-lucide="menu" class="h-5 w-5"></i>'
        : '<i data-lucide="x" class="h-5 w-5"></i>';
      if (window.lucide) lucide.createIcons();
    });

    document.querySelectorAll(".mobile-link").forEach(function (link) {
      link.addEventListener("click", function () {
        mobileMenu.classList.add("hidden");
        menuBtn.innerHTML = '<i data-lucide="menu" class="h-5 w-5"></i>';
        if (window.lucide) lucide.createIcons();
      });
    });
  }

  var whatsappNumber = "5511965937180";
  var form = document.getElementById("contact-form");

  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      var nome = form.nome.value.trim();
      var empresa = form.empresa.value.trim();
      var whatsapp = form.whatsapp.value.trim();
      var email = form.email.value.trim();
      var mensagem = form.mensagem.value.trim();

      var message = [
        "Olá! Gostaria de solicitar um orçamento.",
        "",
        "Nome: " + nome,
        "Empresa: " + empresa,
        "WhatsApp: " + whatsapp,
        "E-mail: " + email,
        "Mensagem: " + mensagem,
      ].join("\n");

      var url =
        "https://wa.me/" + whatsappNumber + "?text=" + encodeURIComponent(message);

      window.open(url, "_blank", "noopener,noreferrer");
      form.reset();
    });
  }

  // Scroll-reveal: fade + slide up as elements enter the viewport,
  // staggering siblings that reveal together (grid rows, stacked text blocks).
  var revealEls = Array.prototype.slice.call(document.querySelectorAll(".reveal"));

  if (revealEls.length) {
    var groups = new Map();
    revealEls.forEach(function (el) {
      var parent = el.parentElement;
      if (!groups.has(parent)) groups.set(parent, []);
      groups.get(parent).push(el);
    });
    groups.forEach(function (list) {
      var cap = list.length > 6 ? 4 : list.length;
      list.forEach(function (el, idx) {
        el.style.transitionDelay = (idx % cap) * 90 + "ms";
      });
    });

    if ("IntersectionObserver" in window) {
      var revealObserver = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              entry.target.classList.add("is-visible");
              revealObserver.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.15, rootMargin: "0px 0px -40px 0px" }
      );
      revealEls.forEach(function (el) {
        revealObserver.observe(el);
      });
    } else {
      revealEls.forEach(function (el) {
        el.classList.add("is-visible");
      });
    }
  }

  // Count-up animation for the stats numbers, triggered once when in view.
  var numbersGrid = document.getElementById("numbers-grid");
  if (numbersGrid) {
    var countEls = numbersGrid.querySelectorAll(".count-up");

    function animateCount(el) {
      var target = parseFloat(el.getAttribute("data-target"), 10);
      var prefix = el.getAttribute("data-prefix") || "";
      var suffix = el.getAttribute("data-suffix") || "";
      var duration = 1200;
      var start = null;

      function step(timestamp) {
        if (start === null) start = timestamp;
        var progress = Math.min((timestamp - start) / duration, 1);
        var eased = 1 - Math.pow(1 - progress, 3);
        var value = Math.round(eased * target);
        el.textContent = prefix + value + suffix;
        if (progress < 1) {
          window.requestAnimationFrame(step);
        }
      }

      window.requestAnimationFrame(step);
    }

    if ("IntersectionObserver" in window && countEls.length) {
      var countObserver = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              countEls.forEach(function (el) {
                animateCount(el);
              });
              countObserver.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.4 }
      );
      countObserver.observe(numbersGrid);
    }
  }
});
