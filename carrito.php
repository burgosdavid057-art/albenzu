<?php
require_once __DIR__ . '/includes/componentes.php';

// ── Acciones ─────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        flash('La sesión expiró. Intenta de nuevo.', 'error');
        redirect(base_url('carrito.php'));
    }

    switch ($_POST['accion'] ?? '') {
        case 'add':
            $ok = carrito_add(
                (int)($_POST['producto_id'] ?? 0),
                (int)($_POST['cantidad'] ?? 1),
                array_map('intval', (array)($_POST['adiciones'] ?? [])),
                (string)($_POST['nota'] ?? '')
            );
            flash($ok ? 'Agregado al pedido.' : 'Ese plato no está disponible.', $ok ? 'ok' : 'error');
            // Volver a donde estaba el cliente (sólo rutas internas)
            $volver = (string)($_POST['volver'] ?? $_SERVER['HTTP_REFERER'] ?? '');
            $ruta   = parse_url($volver, PHP_URL_PATH) ?: '';
            $host   = parse_url($volver, PHP_URL_HOST);
            if ($ruta === '' || ($host !== null && $host !== ($_SERVER['HTTP_HOST'] ?? ''))) {
                $ruta = base_url('carta.php');
            } elseif ($qs = parse_url($volver, PHP_URL_QUERY)) {
                $ruta .= '?' . $qs;
            }
            redirect($ruta);

        case 'cantidad':
            carrito_set_cantidad((string)($_POST['key'] ?? ''), (int)($_POST['cantidad'] ?? 1));
            break;

        case 'quitar':
            carrito_quitar((string)($_POST['key'] ?? ''));
            flash('Producto retirado del pedido.');
            break;

        case 'vaciar':
            carrito_vaciar();
            flash('Pedido vaciado.');
            break;

        case 'cupon':
            $cod = trim((string)($_POST['cupon'] ?? ''));
            if ($cod === '') {
                unset($_SESSION['cupon']);
                flash('Cupón retirado.');
            } else {
                $v = validar_cupon($cod, carrito_subtotal());
                if ($v['ok']) { $_SESSION['cupon'] = strtoupper($cod); flash($v['msg']); }
                else          { unset($_SESSION['cupon']); flash($v['msg'], 'error'); }
            }
            break;

        case 'tipo':
            $_SESSION['tipo_entrega'] = ($_POST['tipo'] ?? 'domicilio') === 'recoger' ? 'recoger' : 'domicilio';
            break;
    }
    redirect(base_url('carrito.php'));
}

// ── Vista ────────────────────────────────────────────────────────────────────
$page_title = 'Mi pedido · ' . SITE_NAME;
$active     = '';
$items      = carrito();
$tipo       = $_SESSION['tipo_entrega'] ?? ($_GET['tipo'] ?? 'domicilio');
$tipo       = $tipo === 'recoger' ? 'recoger' : 'domicilio';
$_SESSION['tipo_entrega'] = $tipo;
$res        = resumen_carrito($tipo);
$minimo     = ajuste_int('pedido_minimo', 0);
$sugeridos  = get_productos(['destacado' => 1, 'limit' => 4]);

include __DIR__ . '/includes/header.php';
?>

<section class="paper min-h-[60vh]">
  <div class="mx-auto max-w-7xl px-5 py-12 lg:py-16">

    <div class="flex items-end justify-between gap-6 mb-10">
      <div>
        <p class="eyebrow mb-4">Paso 1 de 3</p>
        <h1 class="h-display text-4xl sm:text-5xl">Tu pedido.</h1>
      </div>
      <?php if ($items): ?>
        <form method="post" onsubmit="return confirm('¿Vaciar todo el pedido?')">
          <?= csrf_field() ?><input type="hidden" name="accion" value="vaciar">
          <button class="text-sm font-semibold text-carbon/45 hover:text-rojo transition-colors">Vaciar pedido</button>
        </form>
      <?php endif; ?>
    </div>

    <?php if (!$items): ?>
      <div class="rounded-3xl bg-white border border-carbon/10 py-20 px-6 text-center">
        <div class="w-20 h-20 mx-auto mb-6 text-rojo/30">
          <svg fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 17.25a.75.75 0 1 1 0 1.5.75.75 0 0 1 0-1.5Zm12 0a.75.75 0 1 1 0 1.5.75.75 0 0 1 0-1.5Z"/></svg>
        </div>
        <h2 class="font-display text-3xl">Todavía no hay nada aquí.</h2>
        <p class="text-carbon/55 mt-3">Dese una vuelta por la carta; el frijol está puesto.</p>
        <a href="<?= base_url('carta.php') ?>" class="btn btn-rojo btn-lg mt-8" data-magnetic>Ver la carta</a>
      </div>

    <?php else: ?>
      <div class="grid lg:grid-cols-[1fr_400px] gap-8 items-start">

        <!-- Líneas del pedido -->
        <div class="space-y-3">
          <?php foreach ($items as $key => $it): ?>
            <div class="rounded-2xl bg-white border border-carbon/10 p-4 flex gap-4">
              <img src="<?= $it['imagen'] ?>" alt="<?= e($it['nombre']) ?>" class="w-24 h-24 sm:w-28 sm:h-28 rounded-xl object-cover shrink-0">

              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                  <h3 class="font-display font-bold text-lg leading-tight"><?= e($it['nombre']) ?></h3>
                  <form method="post" class="shrink-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="quitar">
                    <input type="hidden" name="key" value="<?= e($key) ?>">
                    <button class="w-8 h-8 grid place-items-center rounded-full text-carbon/35 hover:text-rojo hover:bg-rojo/8 transition-colors" aria-label="Quitar">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                  </form>
                </div>

                <?php if ($it['adiciones']): ?>
                  <ul class="mt-1.5 space-y-0.5">
                    <?php foreach ($it['adiciones'] as $a): ?>
                      <li class="text-[.78rem] text-carbon/55">+ <?= e($a['nombre']) ?> <span class="text-rojo font-semibold tabular"><?= precio_cop($a['precio']) ?></span></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>

                <?php if ($it['nota'] !== ''): ?>
                  <p class="text-[.78rem] italic text-carbon/50 mt-1.5">“<?= e($it['nota']) ?>”</p>
                <?php endif; ?>

                <div class="flex items-center justify-between gap-3 mt-3">
                  <form method="post" class="flex items-center rounded-full border border-carbon/15 h-10">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="cantidad">
                    <input type="hidden" name="key" value="<?= e($key) ?>">
                    <button name="cantidad" value="<?= (int)$it['cantidad'] - 1 ?>" class="w-9 h-full grid place-items-center text-lg font-bold text-carbon/55 hover:text-rojo" aria-label="Menos">−</button>
                    <span class="w-8 text-center text-sm font-extrabold tabular"><?= (int)$it['cantidad'] ?></span>
                    <button name="cantidad" value="<?= (int)$it['cantidad'] + 1 ?>" class="w-9 h-full grid place-items-center text-lg font-bold text-carbon/55 hover:text-rojo" aria-label="Más">+</button>
                  </form>
                  <span class="font-display font-black text-lg text-rojo tabular"><?= precio_cop(linea_total($it)) ?></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>

          <!-- Sugeridos -->
          <div class="pt-8">
            <h3 class="font-display font-bold text-xl mb-4">¿Le sumamos algo?</h3>
            <div class="grid sm:grid-cols-2 gap-3">
              <?php foreach (array_slice($sugeridos, 0, 4) as $s): ?>
                <form method="post" class="flex items-center gap-3 rounded-2xl bg-white border border-carbon/10 p-3 hover:border-rojo/40 transition-colors">
                  <?= csrf_field() ?>
                  <input type="hidden" name="accion" value="add">
                  <input type="hidden" name="producto_id" value="<?= (int)$s['id'] ?>">
                  <input type="hidden" name="cantidad" value="1">
                  <input type="hidden" name="volver" value="<?= base_url('carrito.php') ?>">
                  <img src="<?= plato_img($s) ?>" alt="" class="w-14 h-14 rounded-lg object-cover shrink-0">
                  <span class="flex-1 min-w-0">
                    <span class="block text-sm font-bold leading-tight truncate"><?= e($s['nombre']) ?></span>
                    <span class="block text-sm text-rojo font-bold tabular"><?= precio_cop(precio_final($s)) ?></span>
                  </span>
                  <button class="w-9 h-9 grid place-items-center rounded-full bg-rojo/10 text-rojo hover:bg-rojo hover:text-crema transition-colors shrink-0" aria-label="Agregar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                  </button>
                </form>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Resumen -->
        <aside class="lg:sticky lg:top-[100px] space-y-4">
          <!-- Tipo de entrega -->
          <div class="rounded-2xl bg-white border border-carbon/10 p-5">
            <p class="label">¿Cómo lo quieres?</p>
            <div class="grid grid-cols-2 gap-2">
              <?php foreach ([['domicilio', 'Domicilio', ajuste('tiempo_domicilio')], ['recoger', 'Recoger', ajuste('tiempo_recoger')]] as $t): ?>
                <form method="post">
                  <?= csrf_field() ?>
                  <input type="hidden" name="accion" value="tipo">
                  <input type="hidden" name="tipo" value="<?= $t[0] ?>">
                  <button class="w-full rounded-xl border px-3 py-3 text-center transition-all <?= $tipo === $t[0] ? 'border-rojo bg-rojo/5 ring-2 ring-rojo/15' : 'border-carbon/12 hover:border-rojo/40' ?>">
                    <span class="block text-sm font-bold <?= $tipo === $t[0] ? 'text-rojo' : '' ?>"><?= $t[1] ?></span>
                    <span class="block text-[.7rem] text-carbon/50 mt-0.5"><?= e($t[2]) ?></span>
                  </button>
                </form>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Cupón -->
          <div class="rounded-2xl bg-white border border-carbon/10 p-5">
            <p class="label">¿Tienes un cupón?</p>
            <form method="post" class="flex gap-2">
              <?= csrf_field() ?>
              <input type="hidden" name="accion" value="cupon">
              <input type="text" name="cupon" value="<?= e($res['cupon']) ?>" class="input uppercase" placeholder="ALMAPAISA">
              <button class="btn btn-carbon shrink-0">Aplicar</button>
            </form>
            <?php if ($res['cupon'] !== ''): ?>
              <p class="help text-hoja font-semibold mt-2">Cupón <?= e($res['cupon']) ?> aplicado.</p>
            <?php endif; ?>
          </div>

          <!-- Totales -->
          <div class="rounded-2xl bg-carbon text-crema p-6">
            <h3 class="font-display font-bold text-xl mb-5">Resumen</h3>
            <dl class="space-y-2.5 text-sm">
              <div class="flex justify-between"><dt class="text-crema/60">Subtotal</dt><dd class="tabular font-semibold"><?= precio_cop($res['subtotal']) ?></dd></div>
              <?php if ($res['descuento'] > 0): ?>
                <div class="flex justify-between text-maiz"><dt>Descuento (<?= e($res['cupon']) ?>)</dt><dd class="tabular font-semibold">−<?= precio_cop($res['descuento']) ?></dd></div>
              <?php endif; ?>
              <div class="flex justify-between">
                <dt class="text-crema/60"><?= $tipo === 'recoger' ? 'Recoger en tienda' : 'Domicilio' ?></dt>
                <dd class="tabular font-semibold"><?= $res['domicilio'] > 0 ? precio_cop($res['domicilio']) : 'Gratis' ?></dd>
              </div>
            </dl>
            <div class="flex items-end justify-between mt-5 pt-5 border-t border-crema/15">
              <span class="font-display font-bold text-lg">Total</span>
              <span class="font-display font-black text-3xl text-maiz tabular"><?= precio_cop($res['total']) ?></span>
            </div>

            <?php if ($minimo > 0 && $res['subtotal'] < $minimo): ?>
              <p class="mt-5 text-xs text-maiz bg-maiz/12 rounded-xl px-3 py-2.5 leading-relaxed">
                El pedido mínimo es <?= precio_cop($minimo) ?>. Te faltan <?= precio_cop($minimo - $res['subtotal']) ?>.
              </p>
              <button disabled class="btn btn-crema btn-lg w-full mt-4">Continuar</button>
            <?php else: ?>
              <a href="<?= base_url('checkout.php') ?>" class="btn btn-crema btn-lg w-full mt-6" data-cursor>
                Continuar con los datos
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
              </a>
            <?php endif; ?>

            <p class="text-[.7rem] text-crema/40 text-center mt-4">Pagas con tarjeta, transferencia o en efectivo.</p>
          </div>

          <a href="<?= base_url('carta.php') ?>" class="block text-center text-sm font-semibold text-carbon/50 hover:text-rojo transition-colors py-2">← Seguir agregando de la carta</a>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
