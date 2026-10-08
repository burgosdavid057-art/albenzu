<?php
require_once __DIR__ . '/includes/componentes.php';
require_once __DIR__ . '/includes/arte.php';

$page_title  = SITE_NAME . ' · ' . SITE_TAGLINE . ' · Domicilios en Medellín';
$active      = 'index.php';
$destacados  = get_productos(['destacado' => 1, 'limit' => 8]);
$categorias  = get_categorias();
$adiciones   = get_adiciones();
$totalPlatos = (int)db()->query('SELECT COUNT(*) c FROM productos WHERE activo = 1')->fetch()['c'];
$anios       = (int)date('Y') - SITE_FUNDADO;

include __DIR__ . '/includes/header.php';
?>

<!-- ══════════════════════════ HERO ══════════════════════════ -->
<section class="paper relative overflow-hidden">
  <div class="absolute -top-24 -left-24 w-[26rem] h-[26rem] rounded-full bg-rojo/5 blur-3xl" aria-hidden="true"></div>
  <div class="absolute top-1/3 -right-32 w-[30rem] h-[30rem] rounded-full bg-maiz/10 blur-3xl" aria-hidden="true"></div>

  <div class="mx-auto max-w-7xl px-5 pt-14 pb-20 lg:pt-20 lg:pb-28 grid lg:grid-cols-[1.05fr_1fr] gap-12 lg:gap-16 items-center relative">
    <div data-hero>
      <p class="eyebrow mb-6" data-hero-fade>Medellín · Avenida 80 · desde <?= SITE_FUNDADO ?></p>

      <h1 class="h-display text-[clamp(3rem,8.5vw,5.6rem)] text-carbon">
        <span class="split-line"><span>Frijoles</span></span>
        <span class="split-line"><span>con <em class="h-script text-rojo not-italic"><i class="italic">alma</i></em></span></span>
        <span class="split-line"><span>paisa.</span></span>
      </h1>

      <p class="mt-7 text-lg text-carbon/70 max-w-md leading-relaxed" data-hero-fade>
        <?= $anios ?> años cocinando la bandeja que Medellín reconoce de memoria.
        Pide en línea y recíbela en casa, o pásala a recoger por la 80.
      </p>

      <div class="mt-9 flex flex-wrap items-center gap-3" data-hero-fade>
        <a href="<?= base_url('carta.php') ?>" class="btn btn-rojo btn-lg" data-magnetic data-cursor>
          Ver la carta y pedir
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </a>
        <a href="#como-pedir" class="btn btn-ghost btn-lg" data-cursor>¿Cómo funciona?</a>
      </div>

      <div class="mt-10 flex flex-wrap items-center gap-x-8 gap-y-4 text-sm" data-hero-fade>
        <div class="flex items-center gap-2.5">
          <span class="w-9 h-9 grid place-items-center rounded-full bg-rojo/10 text-rojo">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="width:1.1rem;height:1.1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-6"/></svg>
          </span>
          <span><strong class="font-bold"><?= e(ajuste('tiempo_domicilio', '35 – 50 min')) ?></strong><br><span class="text-carbon/50 text-xs">a domicilio</span></span>
        </div>
        <div class="flex items-center gap-2.5">
          <span class="w-9 h-9 grid place-items-center rounded-full bg-maiz/15 text-[#8A6210]">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="width:1.1rem;height:1.1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
          </span>
          <span><strong class="font-bold">Tarjeta o efectivo</strong><br><span class="text-carbon/50 text-xs">como prefieras</span></span>
        </div>
      </div>
    </div>

    <div class="relative">
      <div data-hero-art class="relative rounded-[1.6rem] overflow-hidden shadow-[0_40px_80px_-40px_rgba(36,26,22,.6)]">
        <?= arte_bandeja('w-full h-auto block') ?>
      </div>

      <!-- Tarjetita flotante de reseñas -->
      <div class="absolute -bottom-6 -left-4 sm:left-6 bg-white rounded-2xl shadow-xl px-5 py-4 border border-carbon/8" data-hero-fade>
        <div class="flex items-center gap-1 text-maiz">
          <?php for ($i = 0; $i < 5; $i++): ?>
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="m12 2 2.9 6.26 6.85.72-5.1 4.6 1.44 6.72L12 16.9l-6.09 3.4 1.44-6.72-5.1-4.6 6.85-.72Z"/></svg>
          <?php endfor; ?>
        </div>
        <p class="text-2xl font-display font-black mt-1 leading-none">4,5<span class="text-sm text-carbon/40 font-sans font-bold"> / 5</span></p>
        <p class="text-[.7rem] text-carbon/50 mt-1">434 reseñas de comensales</p>
      </div>

      <!-- Sello circular -->
      <div class="hidden sm:grid absolute -top-5 -right-3 w-28 h-28 place-items-center rounded-full bg-rojo text-crema shadow-xl rotate-[8deg]" data-hero-fade>
        <div class="text-center leading-tight">
          <span class="block font-display italic font-black text-3xl"><?= SITE_FUNDADO ?></span>
          <span class="block text-[.55rem] tracking-[.2em] uppercase opacity-80">tradición</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════ CÓMO PEDIR ══════════════════════════ -->
<section id="como-pedir" class="bg-white border-y border-carbon/8">
  <div class="mx-auto max-w-7xl px-5 py-16 lg:py-20">
    <div class="max-w-2xl reveal">
      <p class="eyebrow mb-4">Pedir es simple</p>
      <h2 class="h-display text-4xl sm:text-5xl">Tres pasos y el almuerzo está en camino.</h2>
    </div>

    <div class="grid md:grid-cols-3 gap-6 mt-12" data-stagger>
      <?php
      $pasos = [
          ['01', 'Arma tu pedido', 'Elige de la carta completa, agrégale chicharrón o aguacate y déjale la nota a la cocina.'],
          ['02', 'Domicilio o recoger', 'Te lo llevamos en ' . ajuste('tiempo_domicilio', '35 – 50 min') . ' o lo tienes listo en ' . ajuste('tiempo_recoger', '20 – 25 min') . ' en la 80.'],
          ['03', 'Paga como quieras', 'Con tarjeta desde la web, por transferencia, o en efectivo cuando lo recibas.'],
      ];
      foreach ($pasos as $p): ?>
        <div class="relative rounded-2xl border border-carbon/10 bg-crema p-7 overflow-hidden group hover:border-rojo/40 transition-colors">
          <span class="absolute -top-5 -right-2 font-display font-black text-[6rem] text-rojo/8 leading-none select-none group-hover:text-rojo/15 transition-colors"><?= $p[0] ?></span>
          <h3 class="font-display font-bold text-xl relative"><?= e($p[1]) ?></h3>
          <p class="text-sm text-carbon/60 mt-3 leading-relaxed relative"><?= e($p[2]) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══════════════════════════ DESTACADOS ══════════════════════════ -->
<section class="paper">
  <div class="mx-auto max-w-7xl px-5 py-18 lg:py-24">
    <div class="flex flex-wrap items-end justify-between gap-6 reveal">
      <div class="max-w-xl">
        <p class="eyebrow mb-4">Lo que más sale de la cocina</p>
        <h2 class="h-display text-4xl sm:text-5xl">Los que nunca fallan.</h2>
      </div>
      <a href="<?= base_url('carta.php') ?>" class="btn btn-ghost" data-cursor>
        Ver los <?= $totalPlatos ?> platos
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
      </a>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-12" data-stagger>
      <?php foreach ($destacados as $p) dish_card($p, false); ?>
    </div>
  </div>
</section>

<!-- ══════════════════════════ HISTORIA ══════════════════════════ -->
<section class="paper-dark text-crema relative overflow-hidden">
  <div class="mx-auto max-w-7xl px-5 py-20 lg:py-28 grid lg:grid-cols-2 gap-14 items-center">
    <div class="relative">
      <div data-parallax="10"><?= arte_fachada('w-full h-auto rounded-3xl shadow-2xl') ?></div>
    </div>

    <div data-lines>
      <p class="eyebrow mb-6 text-maiz">Nuestra casa</p>
      <h2 class="h-display text-4xl sm:text-5xl lg:text-[3.6rem] leading-[.95]">
        <span class="split-line"><span data-i="0">Alicia,</span></span>
        <span class="split-line"><span data-i="1">Benjamín</span></span>
        <span class="split-line"><span data-i="2">y <em class="h-script text-maiz">Zuluaga</em>.</span></span>
      </h2>
      <p class="mt-7 text-crema/70 leading-relaxed max-w-lg">
        De esos tres nombres salió <strong class="text-crema">AL-BEN-ZU</strong>. En 1982 abrieron
        una casa en la Avenida 80 con una olla de frijoles y la idea de que la comida paisa se
        sirve con el corazón. <?= $anios ?> años después la olla sigue puesta, con la misma
        receta y el mismo fogón.
      </p>

      <div class="grid grid-cols-3 gap-6 mt-12 pt-8 border-t border-crema/15">
        <?php
        $stats = [
            [$anios, 'años de fogón'],
            [$totalPlatos, 'platos en carta'],
            [434, 'reseñas'],
        ];
        foreach ($stats as $s): ?>
          <div>
            <p class="font-display font-black text-4xl text-maiz tabular"><span data-count="<?= $s[0] ?>">0</span></p>
            <p class="text-[.7rem] uppercase tracking-[.18em] text-crema/50 mt-2"><?= e($s[1]) ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <a href="<?= base_url('nosotros.php') ?>" class="btn btn-crema mt-10" data-cursor>Conoce la historia completa</a>
    </div>
  </div>
</section>

<!-- ══════════════════════════ CATEGORÍAS ══════════════════════════ -->
<section class="bg-white">
  <div class="mx-auto max-w-7xl px-5 py-18 lg:py-24">
    <div class="max-w-xl reveal">
      <p class="eyebrow mb-4">La carta</p>
      <h2 class="h-display text-4xl sm:text-5xl">Todo lo que hay en la casa.</h2>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-12" data-stagger>
      <?php foreach ($categorias as $c): ?>
        <a href="<?= base_url('carta.php?cat=' . e($c['slug'])) ?>" data-cursor
           class="group flex items-center gap-4 rounded-2xl border border-carbon/10 bg-crema px-5 py-5 hover:border-rojo hover:bg-rojo hover:text-crema transition-all duration-300">
          <span class="text-3xl shrink-0"><?= $c['emoji'] ?></span>
          <span class="min-w-0 flex-1">
            <span class="block font-display font-bold text-lg leading-tight"><?= e($c['nombre']) ?></span>
            <span class="block text-[.78rem] opacity-60 mt-1 leading-snug line-clamp-2"><?= e($c['descripcion']) ?></span>
          </span>
          <svg class="w-5 h-5 shrink-0 opacity-30 group-hover:opacity-100 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══════════════════════════ RESEÑAS ══════════════════════════ -->
<section class="paper">
  <div class="mx-auto max-w-7xl px-5 py-18 lg:py-24">
    <div class="max-w-xl reveal">
      <p class="eyebrow mb-4">Lo dicen ellos</p>
      <h2 class="h-display text-4xl sm:text-5xl">De la 80 para todo Medellín.</h2>
    </div>

    <div class="grid md:grid-cols-3 gap-5 mt-12" data-stagger>
      <?php
      $resenas = [
          ['El frijol es otra cosa. Uno prueba y entiende por qué llevan cuarenta años llenos.', 'Marcela R.', 'Belén'],
          ['Pedí la Súper Antioqueña para la oficina y llegó calientica en media hora. Repetimos cada viernes.', 'Andrés G.', 'Nueva Villa de Aburrá'],
          ['Los llevé a comer y quedaron impresionados. Porciones honestas y sazón de casa, no de restaurante.', 'Diana C.', 'La América'],
      ];
      foreach ($resenas as $r): ?>
        <figure class="rounded-2xl bg-white border border-carbon/10 p-7 flex flex-col">
          <div class="flex gap-1 text-maiz mb-4">
            <?php for ($i = 0; $i < 5; $i++): ?>
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="m12 2 2.9 6.26 6.85.72-5.1 4.6 1.44 6.72L12 16.9l-6.09 3.4 1.44-6.72-5.1-4.6 6.85-.72Z"/></svg>
            <?php endfor; ?>
          </div>
          <blockquote class="font-display text-lg leading-snug flex-1">“<?= e($r[0]) ?>”</blockquote>
          <figcaption class="mt-6 pt-4 border-t border-carbon/8 text-sm">
            <span class="font-bold"><?= e($r[1]) ?></span>
            <span class="text-carbon/45"> · <?= e($r[2]) ?></span>
          </figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══════════════════════════ UBICACIÓN + CTA ══════════════════════════ -->
<section class="bg-white border-t border-carbon/8">
  <div class="mx-auto max-w-7xl px-5 py-18 lg:py-24 grid lg:grid-cols-2 gap-12 items-center">
    <div class="reveal">
      <p class="eyebrow mb-4">Dónde estamos</p>
      <h2 class="h-display text-4xl sm:text-5xl leading-tight">Av. 80 # 32 - 98<br>Nueva Villa de Aburrá.</h2>
      <p class="mt-6 text-carbon/65 leading-relaxed max-w-md">
        <?= e(ajuste('aviso', '')) ?> Si quedas por fuera del área, escríbenos y miramos cómo te lo hacemos llegar.
      </p>

      <dl class="mt-8 space-y-4 text-sm">
        <div class="flex gap-4">
          <dt class="w-28 shrink-0 text-carbon/45 font-semibold uppercase text-[.7rem] tracking-[.14em] pt-0.5">Horario</dt>
          <dd class="font-semibold"><?= e(CONTACT_HOURS) ?></dd>
        </div>
        <div class="flex gap-4">
          <dt class="w-28 shrink-0 text-carbon/45 font-semibold uppercase text-[.7rem] tracking-[.14em] pt-0.5">Teléfono</dt>
          <dd><a href="tel:<?= e(CONTACT_PHONE_TEL) ?>" class="font-semibold hover:text-rojo transition-colors"><?= e(CONTACT_PHONE) ?></a></dd>
        </div>
        <div class="flex gap-4">
          <dt class="w-28 shrink-0 text-carbon/45 font-semibold uppercase text-[.7rem] tracking-[.14em] pt-0.5">Domicilio</dt>
          <dd class="font-semibold"><?= precio_cop(ajuste_int('domicilio_valor')) ?> · gratis desde <?= precio_cop(ajuste_int('domicilio_gratis')) ?></dd>
        </div>
      </dl>

      <div class="flex flex-wrap gap-3 mt-9">
        <a href="<?= base_url('carta.php') ?>" class="btn btn-rojo btn-lg" data-magnetic data-cursor>Hacer mi pedido</a>
        <a href="https://www.google.com/maps/search/?api=1&query=<?= MAPS_QUERY ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-lg" data-cursor>Cómo llegar</a>
      </div>
    </div>

    <div class="reveal rounded-3xl overflow-hidden border border-carbon/10 shadow-lg aspect-[4/3]">
      <iframe
        src="https://www.google.com/maps?q=<?= MAPS_QUERY ?>&output=embed"
        class="w-full h-full" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
        title="Ubicación de <?= e(SITE_NAME) ?>"></iframe>
    </div>
  </div>
</section>

<?php
modal_plato($adiciones);
barra_carrito();
include __DIR__ . '/includes/footer.php';
