<?php
require_once __DIR__ . '/../includes/functions.php';

function is_admin(): bool {
    return !empty($_SESSION['admin']);
}

function require_admin(): void {
    if (!is_admin()) {
        redirect(base_url('admin/login.php'));
    }
}

function check_admin_login(string $user, string $pass): bool {
    return hash_equals(ADMIN_USER, $user) && hash_equals(ADMIN_PASS_PLAIN, $pass);
}

/** Menú lateral del panel */
function admin_nav(): array {
    return [
        'index.php'      => ['Resumen',    'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25Zm9.75-9.75A2.25 2.25 0 0 1 15.75 3.75H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'],
        'pedidos.php'    => ['Pedidos',    'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
        'productos.php'  => ['Carta',      'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
        'categorias.php' => ['Categorías', 'M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-2.25Z'],
        'clientes.php'   => ['Clientes',   'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
        'cupones.php'    => ['Cupones',    'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z'],
        'ajustes.php'    => ['Ajustes',    'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456c.516-.194 1.096.026 1.37.52l1.296 2.247c.275.494.163 1.115-.26 1.481l-.976.843c-.284.245-.428.613-.42.988a6.759 6.759 0 0 1 0 .255c-.008.375.136.743.42.988l.976.843c.423.366.535.987.26 1.481l-1.297 2.247a1.125 1.125 0 0 1-1.369.52l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.52l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l.977-.843c.284-.245.428-.613.42-.988a6.932 6.932 0 0 1 0-.255c.008-.375-.136-.743-.42-.988l-.977-.843a1.125 1.125 0 0 1-.26-1.431l1.297-2.247a1.125 1.125 0 0 1 1.37-.52l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.281Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
    ];
}

/** Pedidos que requieren atención (badge del menú) */
function pedidos_abiertos(): int {
    return (int)db()->query("SELECT COUNT(*) c FROM pedidos WHERE estado NOT IN ('entregado','cancelado')")->fetch()['c'];
}

function admin_header(string $title, string $activo = '', string $subtitulo = ''): void {
    $nav      = admin_nav();
    $abiertos = pedidos_abiertos();
    ?>
<!DOCTYPE html>
<html lang="es" class="no-js">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · Panel <?= e(SITE_NAME) ?></title>
<link rel="icon" href="data:image/svg+xml,<?= rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="22" fill="#241A16"/><text x="50" y="72" font-size="64" font-family="Georgia,serif" font-style="italic" font-weight="700" fill="#E0A526" text-anchor="middle">A</text></svg>') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,900&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config={theme:{extend:{colors:{
  rojo:{DEFAULT:'#B7202A',dark:'#8E1A22',deep:'#5E1216'},
  crema:{DEFAULT:'#F6F0E2',2:'#EFE6D2',3:'#E4D7BC'},
  carbon:{DEFAULT:'#241A16',soft:'#3A2A23'},
  maiz:'#E0A526', hoja:'#3E5C3A'
},fontFamily:{display:['Fraunces','Georgia','serif'],sans:['Manrope','system-ui','sans-serif']}}}}
</script>
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
<script>document.documentElement.classList.remove('no-js');</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans bg-crema-2 text-carbon min-h-screen">
<div class="flex min-h-screen">

  <aside class="hidden lg:flex flex-col w-60 shrink-0 bg-carbon text-crema sticky top-0 h-screen no-print">
    <div class="p-6 pb-5 border-b border-crema/10">
      <a href="<?= base_url('admin/index.php') ?>" class="block">
        <span class="block font-display italic font-black text-2xl text-crema leading-none">Albenzu</span>
        <span class="block text-[.55rem] tracking-[.3em] uppercase text-crema/40 mt-1.5">Panel del restaurante</span>
      </a>
    </div>

    <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
      <?php foreach ($nav as $file => $n): ?>
        <a href="<?= base_url('admin/' . $file) ?>"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors <?= $activo === $file ? 'bg-rojo text-crema font-bold' : 'text-crema/60 hover:text-crema hover:bg-crema/5' ?>">
          <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $n[1] ?>"/></svg>
          <span class="flex-1"><?= e($n[0]) ?></span>
          <?php if ($file === 'pedidos.php' && $abiertos > 0): ?>
            <span class="min-w-[1.3rem] h-5 px-1 grid place-items-center rounded-full bg-maiz text-carbon text-[.65rem] font-extrabold"><?= $abiertos ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="p-3 border-t border-crema/10 space-y-1">
      <a href="<?= base_url('index.php') ?>" target="_blank" class="block px-3.5 py-2 text-sm text-crema/50 hover:text-crema transition-colors">Ver el sitio ↗</a>
      <a href="<?= base_url('admin/logout.php') ?>" class="block px-3.5 py-2 text-sm text-crema/50 hover:text-crema transition-colors">Cerrar sesión</a>
    </div>
  </aside>

  <div class="flex-1 min-w-0">
    <!-- Barra móvil -->
    <div class="lg:hidden bg-carbon text-crema no-print">
      <div class="flex items-center justify-between px-5 h-14">
        <span class="font-display italic font-black text-xl">Albenzu</span>
        <a href="<?= base_url('admin/logout.php') ?>" class="text-xs text-crema/60">Salir</a>
      </div>
      <nav class="flex gap-1 overflow-x-auto px-3 pb-3 scrollbar-none">
        <?php foreach ($nav as $file => $n): ?>
          <a href="<?= base_url('admin/' . $file) ?>" class="px-3 py-1.5 rounded-lg text-xs whitespace-nowrap <?= $activo === $file ? 'bg-rojo text-crema font-bold' : 'text-crema/60' ?>"><?= e($n[0]) ?></a>
        <?php endforeach; ?>
      </nav>
    </div>

    <header class="bg-white border-b border-carbon/8 px-5 sm:px-8 py-5 no-print">
      <h1 class="font-display font-black text-2xl sm:text-3xl leading-tight"><?= e($title) ?></h1>
      <?php if ($subtitulo !== ''): ?><p class="text-sm text-carbon/55 mt-1"><?= e($subtitulo) ?></p><?php endif; ?>
    </header>

    <main class="p-5 sm:p-8 max-w-[1400px]">
      <?php if ($f = flash()): ?>
        <div class="flash flash-<?= e($f['tipo']) ?> mb-6 no-print"><?= e($f['msg']) ?></div>
      <?php endif; ?>
    <?php
}

function admin_footer(): void {
    ?>
    </main>
  </div>
</div>
</body>
</html>
    <?php
}

/** Píldora de estado */
function badge_estado(string $estado): string {
    return '<span class="state state-' . e($estado) . '">' . e(estado_label($estado)) . '</span>';
}

function badge_pago(string $estado): string {
    $txt = match ($estado) { 'pagado' => 'Pagado', 'fallido' => 'Rechazado', default => 'Pendiente' };
    return '<span class="state state-' . e($estado) . '">' . $txt . '</span>';
}

/** Tarjeta de métrica del dashboard */
function kpi(string $label, string $valor, string $extra = '', string $tono = 'rojo'): void {
    $colores = ['rojo' => 'text-rojo', 'maiz' => 'text-[#8A6210]', 'hoja' => 'text-hoja', 'carbon' => 'text-carbon'];
    ?>
    <div class="card p-5">
      <p class="text-[.65rem] uppercase tracking-[.18em] text-carbon/45 font-bold"><?= e($label) ?></p>
      <p class="font-display font-black text-3xl mt-2 tabular <?= $colores[$tono] ?? 'text-rojo' ?>"><?= e($valor) ?></p>
      <?php if ($extra !== ''): ?><p class="text-xs text-carbon/50 mt-1"><?= e($extra) ?></p><?php endif; ?>
    </div>
    <?php
}
