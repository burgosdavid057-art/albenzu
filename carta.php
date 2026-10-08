<?php
require_once __DIR__ . '/includes/componentes.php';

$page_title = 'La carta · ' . SITE_NAME;
$page_desc  = 'Bandejas, cazuelas, arepas Re-Paisas, asados y desayunos. Pide a domicilio o para recoger en la Avenida 80.';
$active     = 'carta.php';

$catSlug  = trim($_GET['cat'] ?? '');
$buscar   = trim($_GET['q'] ?? '');
$categorias = get_categorias();
$adiciones  = get_adiciones();

$catActiva = null;
foreach ($categorias as $c) {
    if ($c['slug'] === $catSlug) { $catActiva = $c; break; }
}

$filtros = ['orden' => 'default'];
if ($catActiva) $filtros['categoria_id'] = (int)$catActiva['id'];
if ($buscar !== '') $filtros['buscar'] = $buscar;
$productos = get_productos($filtros);

// Agrupar por categoría para el listado editorial
$porCategoria = [];
foreach ($productos as $p) {
    $porCategoria[$p['categoria']][] = $p;
}

include __DIR__ . '/includes/header.php';
?>

<section class="paper border-b border-carbon/8">
  <div class="mx-auto max-w-7xl px-5 pt-12 pb-8 lg:pt-16">
    <div class="grid lg:grid-cols-[1.2fr_1fr] gap-8 items-end">
      <div>
        <p class="eyebrow mb-4">Carta completa</p>
        <h1 class="h-display text-[clamp(2.6rem,6vw,4.2rem)] leading-[.95]">
          Sírvase<br><em class="h-script text-rojo">a gusto.</em>
        </h1>
        <p class="mt-5 text-carbon/65 max-w-md leading-relaxed">
          Precios reales de la casa. Agrega adiciones y notas para la cocina en cada plato;
          al final eliges si lo quieres a domicilio o para recoger.
        </p>
      </div>

      <form method="get" class="lg:justify-self-end w-full lg:max-w-sm">
        <?php if ($catSlug !== ''): ?><input type="hidden" name="cat" value="<?= e($catSlug) ?>"><?php endif; ?>
        <label class="label">Buscar en la carta</label>
        <div class="relative">
          <input type="search" name="q" value="<?= e($buscar) ?>" class="input pl-11" placeholder="chicharrón, arepa, jugo…">
          <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-carbon/35" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.2-5.2m2.2-5.3a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"/></svg>
        </div>
      </form>
    </div>
  </div>

  <!-- Filtro pegajoso de categorías -->
  <div class="sticky top-[74px] z-30 bg-crema/92 backdrop-blur-md border-t border-carbon/8">
    <div class="mx-auto max-w-7xl px-5 py-3 flex gap-2 overflow-x-auto scrollbar-none">
      <a href="<?= base_url('carta.php') ?>" class="cat-pill <?= $catActiva ? '' : 'is-active' ?>">Todo</a>
      <?php foreach ($categorias as $c): ?>
        <a href="<?= base_url('carta.php?cat=' . e($c['slug'])) ?>"
           class="cat-pill <?= $catActiva && $catActiva['id'] == $c['id'] ? 'is-active' : '' ?>">
          <span><?= $c['emoji'] ?></span><?= e($c['nombre']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="paper pb-24">
  <div class="mx-auto max-w-7xl px-5">

    <?php if (!$productos): ?>
      <div class="py-24 text-center">
        <p class="font-display text-3xl">Nada por aquí.</p>
        <p class="text-carbon/55 mt-3">No encontramos platos con «<?= e($buscar) ?>». Prueba con otra palabra.</p>
        <a href="<?= base_url('carta.php') ?>" class="btn btn-rojo mt-7">Ver la carta completa</a>
      </div>
    <?php endif; ?>

    <?php foreach ($porCategoria as $nombreCat => $items):
      $cat = null;
      foreach ($categorias as $c) if ($c['nombre'] === $nombreCat) $cat = $c;
    ?>
      <div class="pt-14" id="<?= e($cat['slug'] ?? slugify($nombreCat)) ?>">
        <div class="flex items-baseline gap-4 pb-6 border-b border-carbon/10 reveal">
          <span class="text-3xl"><?= $cat['emoji'] ?? '' ?></span>
          <div>
            <h2 class="h-display text-3xl sm:text-4xl"><?= e($nombreCat) ?></h2>
            <p class="text-sm text-carbon/50 mt-1"><?= e($cat['descripcion'] ?? '') ?></p>
          </div>
          <span class="ml-auto text-xs font-bold text-carbon/35 tabular"><?= count($items) ?> platos</span>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-8" data-stagger>
          <?php foreach ($items as $p) dish_card($p, false); ?>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Adiciones -->
    <?php if ($adiciones && !$catActiva && $buscar === ''): ?>
      <div class="pt-16">
        <div class="rounded-3xl bg-carbon text-crema p-8 sm:p-10 reveal">
          <p class="eyebrow text-maiz mb-4">Para completar</p>
          <h2 class="h-display text-3xl sm:text-4xl">Adiciones</h2>
          <p class="text-crema/60 text-sm mt-2 max-w-lg">Súmalas a cualquier plato desde el botón <strong class="text-crema">＋</strong> de la tarjeta.</p>
          <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-1 mt-8">
            <?php foreach ($adiciones as $a): ?>
              <div class="flex items-baseline gap-3 py-2.5 border-b border-crema/10">
                <span class="text-sm"><?= e($a['nombre']) ?></span>
                <span class="flex-1 border-b border-dotted border-crema/25 translate-y-[-.2rem]"></span>
                <span class="text-sm font-bold text-maiz tabular"><?= precio_cop($a['precio']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php
modal_plato($adiciones);
barra_carrito();
include __DIR__ . '/includes/footer.php';
