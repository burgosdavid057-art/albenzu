<?php
require_once __DIR__ . '/includes/functions.php';

$codigo = trim($_GET['codigo'] ?? '');
$pedido = $codigo !== '' ? get_pedido_codigo($codigo) : null;

if (!$pedido) {
    flash('No encontramos ningún pedido con ese código.', 'error');
    redirect(base_url('seguimiento.php'));
}

$items   = get_pedido_items((int)$pedido['id']);
$eventos = get_pedido_eventos((int)$pedido['id']);
$flujo   = flujo_estados($pedido['tipo']);
$idx     = array_search($pedido['estado'], $flujo, true);
$recien  = ($_SESSION['ultimo_pedido'] ?? '') === $pedido['codigo'];
unset($_SESSION['ultimo_pedido']);

$page_title = 'Pedido ' . $pedido['codigo'] . ' · ' . SITE_NAME;
$active     = '';
include __DIR__ . '/includes/header.php';
?>

<section class="paper">
  <div class="mx-auto max-w-4xl px-5 py-12 lg:py-16">

    <?php if ($recien): ?>
      <div class="text-center mb-12 reveal">
        <div class="w-20 h-20 mx-auto grid place-items-center rounded-full bg-rojo text-crema mb-6">
          <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        </div>
        <p class="eyebrow justify-center mb-4">Paso 3 de 3</p>
        <h1 class="h-display text-4xl sm:text-5xl">¡Listo, <?= e(explode(' ', $pedido['nombre'])[0]) ?>!</h1>
        <p class="text-carbon/65 mt-4 max-w-md mx-auto leading-relaxed">
          <?= $pedido['tipo'] === 'recoger'
              ? 'Tu pedido entró a cocina. Te avisamos apenas esté listo para recoger en la 80.'
              : 'Tu pedido entró a cocina. Te escribimos al celular cuando el domiciliario salga.' ?>
        </p>
      </div>
    <?php else: ?>
      <h1 class="h-display text-4xl sm:text-5xl mb-10">Pedido <?= e($pedido['codigo']) ?></h1>
    <?php endif; ?>

    <!-- Tarjeta principal -->
    <div class="rounded-3xl bg-white border border-carbon/10 overflow-hidden shadow-sm">
      <div class="px-6 sm:px-8 py-6 border-b border-carbon/8 flex flex-wrap items-center justify-between gap-4">
        <div>
          <p class="text-[.65rem] uppercase tracking-[.22em] text-carbon/45">Código del pedido</p>
          <p class="font-display font-black text-3xl tracking-tight mt-1"><?= e($pedido['codigo']) ?></p>
        </div>
        <div class="flex flex-col items-start sm:items-end gap-2">
          <span class="state state-<?= e($pedido['estado']) ?>"><?= e(estado_label($pedido['estado'])) ?></span>
          <span class="text-xs text-carbon/50"><?= e(tipo_label($pedido['tipo'])) ?> · <?= e(fecha_humana($pedido['creado'])) ?></span>
        </div>
      </div>

      <!-- Línea de tiempo -->
      <?php if ($pedido['estado'] !== 'cancelado'): ?>
        <div class="px-6 sm:px-8 py-8 border-b border-carbon/8">
          <div class="track">
            <?php foreach ($flujo as $i => $st):
              $done = $idx !== false && $i <= $idx;
              $now  = $idx !== false && $i === $idx; ?>
              <div class="track__step <?= $done ? 'done' : '' ?> <?= $now ? 'now' : '' ?>">
                <span class="block text-[.68rem] sm:text-xs font-bold leading-tight <?= $done ? 'text-carbon' : 'text-carbon/35' ?>">
                  <?= e(estado_label($st)) ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
          <p class="text-center text-sm text-carbon/55 mt-6">
            <?php if ($pedido['estado'] === 'entregado'): ?>
              Pedido entregado. ¡Gracias por comer en casa!
            <?php elseif ($pedido['estado'] === 'camino'): ?>
              Tu domiciliario ya va en camino.
            <?php elseif ($pedido['estado'] === 'listo'): ?>
              Ya puedes pasar a recogerlo en <?= e(CONTACT_ADDRESS) ?>.
            <?php else: ?>
              Tiempo estimado: <strong><?= e($pedido['tipo'] === 'recoger' ? ajuste('tiempo_recoger') : ajuste('tiempo_domicilio')) ?></strong>
            <?php endif; ?>
          </p>
        </div>
      <?php else: ?>
        <div class="px-6 sm:px-8 py-6 border-b border-carbon/8 bg-rojo/5">
          <p class="text-sm font-semibold text-rojo-dark">Este pedido fue cancelado. Si fue un error, escríbenos por WhatsApp.</p>
        </div>
      <?php endif; ?>

      <!-- Detalle -->
      <div class="px-6 sm:px-8 py-6">
        <h2 class="font-display font-bold text-xl mb-4">Lo que pediste</h2>
        <ul class="divide-y divide-carbon/8">
          <?php foreach ($items as $it): ?>
            <li class="py-3 flex gap-4">
              <span class="w-7 h-7 shrink-0 grid place-items-center rounded-md bg-rojo/10 text-rojo text-xs font-extrabold tabular"><?= (int)$it['cantidad'] ?></span>
              <div class="flex-1 min-w-0">
                <p class="font-semibold leading-tight"><?= e($it['nombre']) ?></p>
                <?php foreach (json_col($it['adiciones']) as $a): ?>
                  <p class="text-[.78rem] text-carbon/55">+ <?= e($a['nombre']) ?></p>
                <?php endforeach; ?>
                <?php if ($it['nota'] !== ''): ?>
                  <p class="text-[.78rem] italic text-carbon/50 mt-0.5">“<?= e($it['nota']) ?>”</p>
                <?php endif; ?>
              </div>
              <span class="tabular font-bold shrink-0"><?= precio_cop($it['subtotal']) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>

        <dl class="mt-5 pt-5 border-t border-carbon/10 space-y-2 text-sm">
          <div class="flex justify-between"><dt class="text-carbon/55">Subtotal</dt><dd class="tabular"><?= precio_cop($pedido['subtotal']) ?></dd></div>
          <?php if ((int)$pedido['descuento'] > 0): ?>
            <div class="flex justify-between text-hoja"><dt>Descuento <?= $pedido['cupon_codigo'] ? '(' . e($pedido['cupon_codigo']) . ')' : '' ?></dt><dd class="tabular">−<?= precio_cop($pedido['descuento']) ?></dd></div>
          <?php endif; ?>
          <div class="flex justify-between"><dt class="text-carbon/55">Domicilio</dt><dd class="tabular"><?= (int)$pedido['domicilio'] > 0 ? precio_cop($pedido['domicilio']) : 'Gratis' ?></dd></div>
          <div class="flex items-end justify-between pt-3 mt-3 border-t border-carbon/10">
            <dt class="font-display font-bold text-lg">Total</dt>
            <dd class="font-display font-black text-2xl text-rojo tabular"><?= precio_cop($pedido['total']) ?></dd>
          </div>
        </dl>
      </div>

      <!-- Entrega y pago -->
      <div class="px-6 sm:px-8 py-6 bg-crema-2 grid sm:grid-cols-2 gap-6 border-t border-carbon/8">
        <div>
          <p class="label"><?= $pedido['tipo'] === 'recoger' ? 'Recoges en' : 'Entregamos en' ?></p>
          <?php if ($pedido['tipo'] === 'recoger'): ?>
            <p class="text-sm leading-relaxed"><?= e(CONTACT_ADDRESS) ?><br><?= e(CONTACT_CITY) ?></p>
            <a href="https://www.google.com/maps/search/?api=1&query=<?= MAPS_QUERY ?>" target="_blank" rel="noopener" class="text-sm font-semibold text-rojo hover:underline mt-2 inline-block">Ver en el mapa →</a>
          <?php else: ?>
            <p class="text-sm leading-relaxed">
              <?= e($pedido['direccion']) ?><br>
              <?= e($pedido['barrio']) ?>
              <?php if ($pedido['indicaciones'] !== ''): ?><br><span class="text-carbon/55"><?= e($pedido['indicaciones']) ?></span><?php endif; ?>
            </p>
          <?php endif; ?>
          <p class="text-sm text-carbon/55 mt-3"><?= e($pedido['nombre']) ?> · <?= e(tel_humano($pedido['telefono'])) ?></p>
        </div>

        <div>
          <p class="label">Pago</p>
          <p class="text-sm font-semibold"><?= e(metodo_pago_label($pedido['metodo_pago'])) ?></p>
          <p class="mt-2"><span class="state state-<?= e($pedido['pago_estado']) ?>"><?= $pedido['pago_estado'] === 'pagado' ? 'Pagado' : ($pedido['pago_estado'] === 'fallido' ? 'Pago rechazado' : 'Pendiente de pago') ?></span></p>
          <?php if ($pedido['metodo_pago'] === 'efectivo' && (int)$pedido['paga_con'] > 0): ?>
            <p class="text-sm text-carbon/55 mt-2">Paga con <?= precio_cop($pedido['paga_con']) ?> · devuelta <?= precio_cop((int)$pedido['paga_con'] - (int)$pedido['total']) ?></p>
          <?php endif; ?>
          <?php if ($pedido['pago_estado'] !== 'pagado' && $pedido['metodo_pago'] === 'tarjeta'): ?>
            <a href="<?= base_url('pasarela.php?codigo=' . urlencode($pedido['codigo'])) ?>" class="btn btn-rojo !h-10 !text-[.8rem] mt-3">Reintentar el pago</a>
          <?php endif; ?>
          <?php if ($pedido['notas'] !== ''): ?>
            <p class="label mt-5">Tu nota</p>
            <p class="text-sm italic text-carbon/60">“<?= e($pedido['notas']) ?>”</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Historial -->
    <?php if (count($eventos) > 1): ?>
      <div class="mt-8 rounded-2xl bg-white border border-carbon/10 p-6">
        <h2 class="font-display font-bold text-lg mb-4">Historial</h2>
        <ol class="space-y-3">
          <?php foreach (array_reverse($eventos) as $ev): ?>
            <li class="flex items-start gap-3 text-sm">
              <span class="w-2 h-2 rounded-full bg-rojo mt-1.5 shrink-0"></span>
              <span class="flex-1">
                <strong><?= e(estado_label($ev['estado'])) ?></strong>
                <?php if ($ev['nota'] !== ''): ?><span class="text-carbon/55"> — <?= e($ev['nota']) ?></span><?php endif; ?>
              </span>
              <span class="text-xs text-carbon/40 shrink-0"><?= e(fecha_humana($ev['creado'])) ?></span>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    <?php endif; ?>

    <div class="flex flex-wrap gap-3 justify-center mt-10">
      <a href="<?= e(whatsapp_link('Hola Albenzu, tengo una pregunta sobre mi pedido ' . $pedido['codigo'] . '.')) ?>" target="_blank" rel="noopener" class="btn btn-carbon">Escribir por WhatsApp</a>
      <a href="<?= base_url('carta.php') ?>" class="btn btn-ghost">Pedir otra vez</a>
    </div>

    <p class="text-center text-xs text-carbon/45 mt-6">
      Guarda el código <strong><?= e($pedido['codigo']) ?></strong> para consultar el estado en
      <a href="<?= base_url('seguimiento.php') ?>" class="text-rojo hover:underline">seguimiento</a>.
    </p>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
