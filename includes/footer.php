</main>

<!-- Cinta de marca -->
<div class="marquee bg-rojo text-crema py-3 border-y border-rojo-deep/40">
  <div class="marquee__track font-display italic font-black text-xl sm:text-2xl">
    <?php for ($i = 0; $i < 4; $i++): ?>
      <span>Frijoles con alma paisa</span><span class="marquee__dot">•</span>
      <span>Desde <?= SITE_FUNDADO ?></span><span class="marquee__dot">•</span>
      <span>Avenida 80</span><span class="marquee__dot">•</span>
      <span>Medellín</span><span class="marquee__dot">•</span>
    <?php endfor; ?>
  </div>
</div>

<footer class="bg-carbon text-crema/70">
  <div class="mx-auto max-w-7xl px-5 py-16 grid gap-12 md:grid-cols-[1.4fr_1fr_1fr_1.1fr]">
    <div>
      <div class="w-20 h-20 mb-5"><?= logo_svg(80) ?></div>
      <p class="text-sm leading-relaxed max-w-xs"><?= e(SITE_HISTORIA) ?></p>
      <div class="flex gap-3 mt-6">
        <a href="<?= e(SOCIAL_INSTAGRAM) ?>" target="_blank" rel="noopener" class="social" aria-label="Instagram">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16Zm0 5.68a4.16 4.16 0 1 0 0 8.32 4.16 4.16 0 0 0 0-8.32Zm0 6.86a2.7 2.7 0 1 1 0-5.4 2.7 2.7 0 0 1 0 5.4Zm5.3-7.03a.97.97 0 1 1-1.94 0 .97.97 0 0 1 1.94 0Z"/></svg>
        </a>
        <a href="<?= e(SOCIAL_FACEBOOK) ?>" target="_blank" rel="noopener" class="social" aria-label="Facebook">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.25-1.5 1.55-1.5h1.65V3.63c-.29-.04-1.27-.13-2.42-.13-2.4 0-4.03 1.46-4.03 4.15V9.9H7.5V13h2.75v8h3.25Z"/></svg>
        </a>
        <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="social" aria-label="WhatsApp">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.5 0-9.96 4.46-9.96 9.96 0 1.76.46 3.48 1.34 5L2 22l5.2-1.36a9.9 9.9 0 0 0 4.84 1.24h.01c5.5 0 9.96-4.46 9.96-9.96 0-2.66-1.04-5.16-2.92-7.04A9.88 9.88 0 0 0 12.04 2Zm5.8 14.06c-.24.68-1.42 1.32-1.96 1.36-.5.06-1.14.08-1.84-.12-.42-.12-.96-.3-1.66-.6-2.92-1.26-4.82-4.2-4.96-4.4-.14-.2-1.18-1.58-1.18-3s.74-2.12 1-2.4c.26-.28.58-.36.78-.36h.56c.18 0 .42-.06.66.5.24.58.82 2 .9 2.14.06.14.1.32 0 .5-.1.2-.16.32-.3.48l-.44.52c-.14.14-.3.3-.12.6.16.28.74 1.24 1.6 2 1.1.98 2.02 1.28 2.3 1.42.28.14.44.12.6-.08.18-.2.7-.82.88-1.1.18-.28.36-.24.6-.14.24.08 1.56.74 1.84.86.26.14.44.2.5.32.08.1.08.62-.16 1.3Z"/></svg>
        </a>
      </div>
    </div>

    <div>
      <h4 class="footer-h">La casa</h4>
      <ul class="space-y-2.5 text-sm">
        <li><a href="<?= base_url('carta.php') ?>" class="footer-a">La carta completa</a></li>
        <li><a href="<?= base_url('nosotros.php') ?>" class="footer-a">Nuestra historia</a></li>
        <li><a href="<?= base_url('contacto.php') ?>" class="footer-a">Cómo llegar</a></li>
        <li><a href="<?= base_url('seguimiento.php') ?>" class="footer-a">Seguir mi pedido</a></li>
      </ul>
    </div>

    <div>
      <h4 class="footer-h">Pedidos</h4>
      <ul class="space-y-2.5 text-sm">
        <li><a href="<?= base_url('carta.php?tipo=domicilio') ?>" class="footer-a">Domicilio</a></li>
        <li><a href="<?= base_url('carta.php?tipo=recoger') ?>" class="footer-a">Para recoger</a></li>
        <li><span class="text-crema/45">Pago con tarjeta o efectivo</span></li>
        <li><a href="<?= base_url('admin/') ?>" class="footer-a">Panel del restaurante</a></li>
      </ul>
    </div>

    <div>
      <h4 class="footer-h">Dónde estamos</h4>
      <p class="text-sm leading-relaxed">
        <?= e(CONTACT_ADDRESS) ?><br>
        <?= e(CONTACT_CITY) ?>
      </p>
      <p class="text-sm mt-4">
        <a href="tel:<?= e(CONTACT_PHONE_TEL) ?>" class="footer-a"><?= e(CONTACT_PHONE) ?></a><br>
        <a href="mailto:<?= e(CONTACT_EMAIL) ?>" class="footer-a"><?= e(CONTACT_EMAIL) ?></a>
      </p>
      <p class="text-xs mt-4 text-crema/45"><?= e(CONTACT_HOURS) ?></p>
    </div>
  </div>

  <div class="border-t border-crema/10">
    <div class="mx-auto max-w-7xl px-5 py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-crema/40">
      <span>© <?= date('Y') ?> <?= e(SITE_LEGAL) ?>. Todos los derechos reservados.</span>
      <span>Maqueta de demostración · desarrollo web</span>
    </div>
  </div>
</footer>

<!-- Botón flotante de WhatsApp -->
<a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="wa-fab" aria-label="Escríbenos por WhatsApp" data-cursor>
  <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.5 0-9.96 4.46-9.96 9.96 0 1.76.46 3.48 1.34 5L2 22l5.2-1.36a9.9 9.9 0 0 0 4.84 1.24h.01c5.5 0 9.96-4.46 9.96-9.96 0-2.66-1.04-5.16-2.92-7.04A9.88 9.88 0 0 0 12.04 2Zm5.8 14.06c-.24.68-1.42 1.32-1.96 1.36-.5.06-1.14.08-1.84-.12-.42-.12-.96-.3-1.66-.6-2.92-1.26-4.82-4.2-4.96-4.4-.14-.2-1.18-1.58-1.18-3s.74-2.12 1-2.4c.26-.28.58-.36.78-.36h.56c.18 0 .42-.06.66.5.24.58.82 2 .9 2.14.06.14.1.32 0 .5-.1.2-.16.32-.3.48l-.44.52c-.14.14-.3.3-.12.6.16.28.74 1.24 1.6 2 1.1.98 2.02 1.28 2.3 1.42.28.14.44.12.6-.08.18-.2.7-.82.88-1.1.18-.28.36-.24.6-.14.24.08 1.56.74 1.84.86.26.14.44.2.5.32.08.1.08.62-.16 1.3Z"/></svg>
</a>

<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lenis@1.1.13/dist/lenis.min.js"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
