<?php
require_once __DIR__ . '/functions.php';

$page_title = $page_title ?? SITE_NAME . ' · ' . SITE_TAGLINE;
$page_desc  = $page_desc  ?? SITE_DESC;
$active     = $active     ?? '';
$unidades   = carrito_unidades();
$abierto    = esta_abierto();

$nav = [
    'index.php'  => 'Inicio',
    'carta.php'  => 'La carta',
    'nosotros.php' => 'La casa',
    'contacto.php' => 'Contacto',
];
?>
<!DOCTYPE html>
<html lang="es" class="no-js">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?></title>
<meta name="description" content="<?= e($page_desc) ?>">
<meta property="og:title" content="<?= e($page_title) ?>">
<meta property="og:description" content="<?= e($page_desc) ?>">
<meta property="og:type" content="restaurant">
<link rel="icon" href="data:image/svg+xml,<?= rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="22" fill="#B7202A"/><text x="50" y="72" font-size="64" font-family="Georgia,serif" font-style="italic" font-weight="700" fill="#F6F0E2" text-anchor="middle">A</text></svg>') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;0,9..144,900;1,9..144,600;1,9..144,900&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config={theme:{extend:{colors:{
  rojo:{DEFAULT:'#B7202A',dark:'#8E1A22',deep:'#5E1216',soft:'#D9414A'},
  crema:{DEFAULT:'#F6F0E2',2:'#EFE6D2',3:'#E4D7BC'},
  carbon:{DEFAULT:'#241A16',soft:'#3A2A23'},
  maiz:'#E0A526', hoja:'#3E5C3A', tierra:'#7A3B1E'
},fontFamily:{display:['Fraunces','Georgia','serif'],sans:['Manrope','system-ui','sans-serif']},
 letterSpacing:{ultra:'.34em'}}}}
</script>
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
<script>document.documentElement.classList.remove('no-js');</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans bg-crema text-carbon antialiased selection:bg-rojo selection:text-crema">

<div class="cursor-dot" aria-hidden="true"></div>
<div class="cursor-ring" aria-hidden="true"></div>

<!-- Cinta superior -->
<div class="bg-carbon text-crema/80 text-[.68rem] sm:text-xs tracking-[.14em] uppercase">
  <div class="mx-auto max-w-7xl px-5 h-9 flex items-center justify-between gap-4">
    <span class="hidden sm:inline"><?= e(CONTACT_HOURS) ?></span>
    <span class="flex items-center gap-2">
      <span class="relative flex h-2 w-2">
        <?php if ($abierto): ?><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-maiz opacity-70"></span><?php endif; ?>
        <span class="relative inline-flex rounded-full h-2 w-2 <?= $abierto ? 'bg-maiz' : 'bg-crema/40' ?>"></span>
      </span>
      <?= $abierto ? 'Estamos recibiendo pedidos' : 'Cerrado · deja tu pedido programado' ?>
    </span>
    <a href="tel:<?= e(CONTACT_PHONE_TEL) ?>" class="hidden sm:inline hover:text-maiz transition-colors"><?= e(CONTACT_PHONE) ?></a>
  </div>
</div>

<!-- Navegación -->
<header id="nav" class="sticky top-0 z-40 transition-all duration-300 bg-crema/85 backdrop-blur-md border-b border-carbon/10">
  <div class="mx-auto max-w-7xl px-5 h-[74px] flex items-center gap-6">
    <a href="<?= base_url('index.php') ?>" class="flex items-center gap-3 shrink-0" data-cursor>
      <span class="block w-11 h-11"><?= logo_svg(44) ?></span>
      <span class="leading-none">
        <span class="block font-display italic font-black text-2xl text-rojo tracking-tight">Albenzu</span>
        <span class="block text-[.55rem] tracking-ultra uppercase text-carbon/55 mt-0.5">Tradición paisa</span>
      </span>
    </a>

    <nav class="hidden lg:flex items-center gap-1 ml-4">
      <?php foreach ($nav as $file => $label): ?>
        <a href="<?= base_url($file) ?>" data-cursor
           class="nav-link <?= $active === $file ? 'is-active' : '' ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="ml-auto flex items-center gap-2 sm:gap-3">
      <a href="<?= base_url('seguimiento.php') ?>" class="hidden md:inline-flex text-xs font-semibold text-carbon/60 hover:text-rojo transition-colors px-3 py-2" data-cursor>Seguir mi pedido</a>

      <a href="<?= base_url('carrito.php') ?>" data-cursor
         class="relative inline-flex items-center gap-2 rounded-full border border-carbon/15 bg-white/70 px-4 h-11 hover:border-rojo hover:text-rojo transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 17.25a.75.75 0 1 1 0 1.5.75.75 0 0 1 0-1.5Zm12 0a.75.75 0 1 1 0 1.5.75.75 0 0 1 0-1.5Z"/></svg>
        <span class="hidden sm:inline text-sm font-bold tabular">
          <?= $unidades > 0 ? precio_cop(carrito_subtotal()) : 'Carrito' ?>
        </span>
        <?php if ($unidades > 0): ?>
          <span class="absolute -top-1.5 -right-1.5 min-w-[1.35rem] h-[1.35rem] px-1 grid place-items-center rounded-full bg-rojo text-crema text-[.68rem] font-extrabold"><?= $unidades ?></span>
        <?php endif; ?>
      </a>

      <a href="<?= base_url('carta.php') ?>" class="btn btn-rojo hidden sm:inline-flex" data-cursor data-magnetic>Pedir ahora</a>

      <button type="button" class="lg:hidden w-11 h-11 grid place-items-center rounded-full border border-carbon/15" onclick="document.getElementById('menu-movil').classList.toggle('hidden')" aria-label="Abrir menú">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
      </button>
    </div>
  </div>

  <div id="menu-movil" class="hidden lg:hidden border-t border-carbon/10 bg-crema">
    <nav class="px-5 py-3 flex flex-col">
      <?php foreach ($nav as $file => $label): ?>
        <a href="<?= base_url($file) ?>" class="py-3 border-b border-carbon/5 font-display text-lg <?= $active === $file ? 'text-rojo font-bold' : '' ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
      <a href="<?= base_url('seguimiento.php') ?>" class="py-3 font-display text-lg">Seguir mi pedido</a>
    </nav>
  </div>
</header>

<?php if ($f = flash()): ?>
  <div class="mx-auto max-w-7xl px-5 pt-4">
    <div class="flash flash-<?= e($f['tipo']) ?>"><?= e($f['msg']) ?></div>
  </div>
<?php endif; ?>

<main>
