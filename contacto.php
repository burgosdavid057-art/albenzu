<?php
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Contacto · ' . SITE_NAME;
$page_desc  = 'Av. 80 # 32 - 98, Nueva Villa de Aburrá, Medellín. Domicilios y reservas.';
$active     = 'contacto.php';

include __DIR__ . '/includes/header.php';
?>

<section class="paper">
  <div class="mx-auto max-w-7xl px-5 py-14 lg:py-20 grid lg:grid-cols-2 gap-12 items-start">
    <div>
      <p class="eyebrow mb-5">Contacto</p>
      <h1 class="h-display text-[clamp(2.6rem,6vw,4.2rem)] leading-[.95]">
        Nos encuentras<br>en la <em class="h-script text-rojo">80.</em>
      </h1>

      <div class="mt-10 space-y-5">
        <?php
        $datos = [
            ['Dirección', CONTACT_ADDRESS . '<br>' . CONTACT_CITY, 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z'],
            ['Horario', CONTACT_HOURS, 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ['Teléfono', '<a href="tel:' . CONTACT_PHONE_TEL . '" class="hover:text-rojo transition-colors">' . CONTACT_PHONE . '</a>', 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z'],
            ['Correo', '<a href="mailto:' . CONTACT_EMAIL . '" class="hover:text-rojo transition-colors">' . CONTACT_EMAIL . '</a>', 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75'],
        ];
        foreach ($datos as $d): ?>
          <div class="flex gap-4 reveal">
            <span class="w-11 h-11 shrink-0 grid place-items-center rounded-xl bg-rojo/10 text-rojo">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $d[2] ?>"/></svg>
            </span>
            <div>
              <p class="text-[.68rem] uppercase tracking-[.2em] text-carbon/45 font-bold"><?= e($d[0]) ?></p>
              <p class="mt-1 leading-relaxed"><?= $d[1] ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="mt-10 rounded-2xl bg-carbon text-crema p-6">
        <h2 class="font-display font-bold text-xl">Zona de domicilios</h2>
        <p class="text-crema/70 text-sm mt-2 leading-relaxed"><?= e(ajuste('aviso')) ?></p>
        <p class="text-crema/70 text-sm mt-3">
          Domicilio <strong class="text-maiz"><?= precio_cop(ajuste_int('domicilio_valor')) ?></strong> ·
          gratis desde <strong class="text-maiz"><?= precio_cop(ajuste_int('domicilio_gratis')) ?></strong> ·
          pedido mínimo <?= precio_cop(ajuste_int('pedido_minimo')) ?>.
        </p>
      </div>

      <div class="flex flex-wrap gap-3 mt-8">
        <a href="<?= base_url('carta.php') ?>" class="btn btn-rojo btn-lg" data-magnetic>Hacer un pedido</a>
        <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-lg">WhatsApp</a>
      </div>
    </div>

    <div class="lg:sticky lg:top-[100px] space-y-4">
      <div class="rounded-3xl overflow-hidden border border-carbon/10 shadow-lg aspect-square">
        <iframe src="https://www.google.com/maps?q=<?= MAPS_QUERY ?>&output=embed"
                class="w-full h-full" style="border:0" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade" title="Mapa de <?= e(SITE_NAME) ?>"></iframe>
      </div>
      <a href="https://www.google.com/maps/search/?api=1&query=<?= MAPS_QUERY ?>" target="_blank" rel="noopener"
         class="btn btn-carbon w-full">Abrir en Google Maps</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
