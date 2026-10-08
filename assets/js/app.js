/* ============================================================
   Albenzu · animaciones e interacción
   GSAP + ScrollTrigger + Lenis (smooth scroll) + cursor propio

   Regla de oro: el contenido NUNCA puede quedar invisible. Las
   apariciones al hacer scroll van con IntersectionObserver (nativo,
   sin dependencias) y hay una red de seguridad que destapa todo si
   algo falla. GSAP se reserva para lo decorativo.
   ============================================================ */
(function () {
  'use strict';

  const reduce  = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const fine    = window.matchMedia('(hover:hover) and (pointer:fine)').matches;
  const hasGsap = typeof window.gsap !== 'undefined' && typeof window.ScrollTrigger !== 'undefined';

  const mostrar = (el) => {
    el.style.opacity   = '1';
    el.style.transform = 'none';
    el.classList.add('is-in');
  };

  /* ── Apariciones al hacer scroll (IntersectionObserver) ──────────────── */
  const reveals = Array.from(document.querySelectorAll('.reveal'));
  const grids   = Array.from(document.querySelectorAll('[data-stagger]'));

  // Los hijos de una grilla escalonada arrancan ocultos sólo si hay JS y IO
  const puedeAnimar = !reduce && 'IntersectionObserver' in window;

  const ocultar = (el, dy, dur, delay) => {
    el.style.opacity    = '0';
    el.style.transform  = 'translateY(' + dy + 'px)';
    el.style.transition = 'opacity ' + dur + 's cubic-bezier(.2,.8,.2,1), transform ' + dur + 's cubic-bezier(.2,.8,.2,1)';
    if (delay) el.style.transitionDelay = delay + 's';
  };

  if (puedeAnimar) {
    grids.forEach((g) => Array.from(g.children).forEach((c) => ocultar(c, 30, .7, 0)));
    reveals.forEach((el) => ocultar(el, 28, .8, parseFloat(el.dataset.delay || 0)));

    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        io.unobserve(el);
        if (el.hasAttribute('data-stagger')) {
          Array.from(el.children).forEach((c, i) => setTimeout(() => mostrar(c), i * 70));
        } else {
          mostrar(el);
        }
      });
    }, { rootMargin: '0px 0px -12% 0px', threshold: 0.04 });

    reveals.forEach((el) => io.observe(el));
    grids.forEach((g) => io.observe(g));
  } else {
    reveals.forEach(mostrar);
    grids.forEach((g) => Array.from(g.children).forEach(mostrar));
  }

  // Red de seguridad: si algo que YA está en pantalla sigue oculto, se destapa.
  // (No toca lo que está más abajo: eso le corresponde al observer.)
  const rescate = () => {
    document.querySelectorAll('.reveal, [data-stagger] > *').forEach((el) => {
      if (getComputedStyle(el).opacity !== '0') return;
      if (el.getBoundingClientRect().top < innerHeight * 0.95) mostrar(el);
    });
  };
  window.addEventListener('load', () => setTimeout(rescate, 2000));
  addEventListener('scroll', () => { clearTimeout(window.__rescate); window.__rescate = setTimeout(rescate, 1200); }, { passive: true });

  /* ── Nav con sombra al bajar ─────────────────────────────────────────── */
  const nav = document.getElementById('nav');
  if (nav) {
    const onScroll = () => nav.classList.toggle('is-stuck', window.scrollY > 20);
    addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ── Cursor personalizado ────────────────────────────────────────────── */
  if (fine && !reduce) {
    const dot  = document.querySelector('.cursor-dot');
    const ring = document.querySelector('.cursor-ring');
    if (dot && ring) {
      let mx = innerWidth / 2, my = innerHeight / 2, rx = mx, ry = my;
      addEventListener('mousemove', (ev) => {
        mx = ev.clientX; my = ev.clientY;
        dot.style.transform = `translate(${mx}px, ${my}px) translate(-50%,-50%)`;
      });
      (function loop() {
        rx += (mx - rx) * 0.16;
        ry += (my - ry) * 0.16;
        ring.style.transform = `translate(${rx}px, ${ry}px) translate(-50%,-50%)`;
        requestAnimationFrame(loop);
      })();
      const hoverables = 'a, button, [data-cursor], .dish, .cat-pill, input, select, textarea';
      document.addEventListener('mouseover', (ev) => {
        if (ev.target.closest(hoverables)) ring.classList.add('is-hover');
      });
      document.addEventListener('mouseout', (ev) => {
        if (ev.target.closest(hoverables)) ring.classList.remove('is-hover');
      });
    }
  }

  if (!hasGsap || reduce) {
    document.querySelectorAll('[data-hero] .split-line > span, [data-lines] .split-line > span, [data-hero-fade]')
      .forEach((el) => { el.style.transform = 'none'; el.style.opacity = '1'; });
    return;
  }

  gsap.registerPlugin(ScrollTrigger);

  /* ── Smooth scroll (receta oficial Lenis + GSAP) ─────────────────────── */
  if (typeof window.Lenis !== 'undefined') {
    const lenis = new window.Lenis({ duration: 1.05, smoothWheel: true, wheelMultiplier: 0.95 });
    lenis.on('scroll', () => ScrollTrigger.update());          // sin argumentos: ST espera un flag
    gsap.ticker.add((t) => lenis.raf(t * 1000));
    gsap.ticker.lagSmoothing(0);

    document.querySelectorAll('a[href^="#"]').forEach((a) => {
      a.addEventListener('click', (ev) => {
        const id = a.getAttribute('href');
        if (id.length < 2) return;
        const t = document.querySelector(id);
        if (!t) return;
        ev.preventDefault();
        lenis.scrollTo(t, { offset: -110 });
      });
    });
  }

  /* ── Entrada del hero: líneas que suben + arte que se destapa ────────── */
  const heroLines = document.querySelectorAll('[data-hero] .split-line > span');
  if (heroLines.length) {
    gsap.from(heroLines, { yPercent: 115, duration: 1.05, ease: 'power4.out', stagger: 0.085, delay: 0.15 });
  }
  if (document.querySelector('[data-hero-fade]')) {
    gsap.from('[data-hero-fade]', { opacity: 0, y: 22, duration: .9, ease: 'power3.out', stagger: .1, delay: .5 });
  }
  const heroArt = document.querySelector('[data-hero-art]');
  if (heroArt) {
    gsap.fromTo(heroArt,
      { clipPath: 'inset(14% 14% 14% 14% round 2rem)', scale: 1.12 },
      { clipPath: 'inset(0% 0% 0% 0% round 1.6rem)', scale: 1, duration: 1.5, ease: 'expo.out', delay: .25 });
  }

  /* ── Parallax suave en las ilustraciones ─────────────────────────────── */
  gsap.utils.toArray('[data-parallax]').forEach((el) => {
    const amount = parseFloat(el.dataset.parallax) || 12;
    gsap.fromTo(el, { yPercent: -amount / 2 }, {
      yPercent: amount / 2, ease: 'none',
      scrollTrigger: { trigger: el.parentElement, start: 'top bottom', end: 'bottom top', scrub: true },
    });
  });

  /* ── Contadores ──────────────────────────────────────────────────────── */
  gsap.utils.toArray('[data-count]').forEach((el) => {
    const end = parseFloat(el.dataset.count);
    const obj = { v: 0 };
    gsap.to(obj, {
      v: end, duration: 1.8, ease: 'power2.out',
      scrollTrigger: { trigger: el, start: 'top 90%', once: true },
      onUpdate: () => { el.textContent = Math.round(obj.v).toLocaleString('es-CO'); },
      onComplete: () => { el.textContent = Math.round(end).toLocaleString('es-CO'); },
    });
  });

  /* ── Titular que se descubre línea a línea ───────────────────────────── */
  gsap.utils.toArray('[data-lines]').forEach((bloque) => {
    const spans = bloque.querySelectorAll('.split-line > span');
    if (!spans.length) return;
    gsap.from(spans, {
      yPercent: 110, duration: .95, ease: 'power4.out', stagger: .09,
      scrollTrigger: { trigger: bloque, start: 'top 85%', once: true },
    });
  });

  /* ── Botones magnéticos ──────────────────────────────────────────────── */
  if (fine) {
    document.querySelectorAll('[data-magnetic]').forEach((btn) => {
      btn.addEventListener('mousemove', (ev) => {
        const r = btn.getBoundingClientRect();
        gsap.to(btn, {
          x: (ev.clientX - r.left - r.width / 2) * .28,
          y: (ev.clientY - r.top - r.height / 2) * .34,
          duration: .5, ease: 'power3.out',
        });
      });
      btn.addEventListener('mouseleave', () => {
        gsap.to(btn, { x: 0, y: 0, duration: .6, ease: 'elastic.out(1,.4)' });
      });
    });
  }

  /* ── Barra de carrito flotante en móvil ──────────────────────────────── */
  const bar = document.querySelector('.cart-bar');
  if (bar) {
    const toggle = () => bar.classList.toggle('is-on', window.scrollY > 260);
    addEventListener('scroll', toggle, { passive: true });
    toggle();
  }

  /* ── Feedback del botón «Agregar» ────────────────────────────────────── */
  document.addEventListener('submit', (ev) => {
    const f = ev.target.closest('form[data-add]');
    if (!f) return;
    const b = f.querySelector('button[type=submit]');
    if (b) { b.disabled = true; b.textContent = 'Agregando…'; }
  });

  window.addEventListener('load', () => ScrollTrigger.refresh());
})();
