<?php
require_once __DIR__ . '/auth.php';
require_admin();

$id     = (int)($_GET['id'] ?? 0);
$pedido = get_pedido($id);
if (!$pedido) {
    flash('Ese pedido no existe.', 'error');
    redirect(base_url('admin/pedidos.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    switch ($_POST['accion'] ?? '') {
        case 'estado':
            $nuevo = (string)($_POST['estado'] ?? '');
            if (isset(estados_pedido()[$nuevo])) {
                cambiar_estado_pedido($id, $nuevo, trim((string)($_POST['nota'] ?? '')));
                flash('Estado actualizado a «' . estado_label($nuevo) . '».');
            }
            break;

        case 'pago':
            $pe = (string)($_POST['pago_estado'] ?? 'pendiente');
            if (in_array($pe, ['pendiente', 'pagado', 'fallido'], true)) {
                marcar_pago($id, $pe, trim((string)($_POST['pago_ref'] ?? '')));
                flash('Pago marcado como «' . $pe . '».');
            }
            break;

        case 'datos':
            db()->prepare(
                'UPDATE pedidos SET metodo_pago = ?, domicilio = ?, total = subtotal - descuento + ?,
                                    direccion = ?, barrio = ?, indicaciones = ?, notas = ?, actualizado = ?
                 WHERE id = ?'
            )->execute([
                (string)($_POST['metodo_pago'] ?? $pedido['metodo_pago']),
                (int)($_POST['domicilio'] ?? 0), (int)($_POST['domicilio'] ?? 0),
                trim((string)($_POST['direccion'] ?? '')), trim((string)($_POST['barrio'] ?? '')),
                trim((string)($_POST['indicaciones'] ?? '')), trim((string)($_POST['notas'] ?? '')),
                date('Y-m-d H:i:s'), $id,
            ]);
            flash('Datos del pedido actualizados.');
            break;
    }
    redirect(base_url('admin/pedido.php?id=' . $id));
}

$items   = get_pedido_items($id);
$eventos = get_pedido_eventos($id);
$flujo   = flujo_estados($pedido['tipo']);
$idx     = array_search($pedido['estado'], $flujo, true);
$sig     = ($idx !== false && isset($flujo[$idx + 1])) ? $flujo[$idx + 1] : null;

// Historial del cliente (contexto CRM)
$hist = [];
if ($pedido['cliente_id']) {
    $st = db()->prepare("SELECT COUNT(*) pedidos, COALESCE(SUM(total),0) gastado
                         FROM pedidos WHERE cliente_id = ? AND estado != 'cancelado'");
    $st->execute([$pedido['cliente_id']]);
    $hist = $st->fetch();
}

$wa = 'Hola ' . explode(' ', $pedido['nombre'])[0] . ', te escribimos de Albenzu por tu pedido ' . $pedido['codigo'] . '.';

admin_header('Pedido ' . $pedido['codigo'], 'pedidos.php', fecha_humana($pedido['creado']) . ' · ' . tipo_label($pedido['tipo']));
?>

<div class="flex flex-wrap items-center gap-3 mb-6 no-print">
  <a href="<?= base_url('admin/pedidos.php') ?>" class="btn btn-ghost !h-10">← Volver</a>
  <?= badge_estado($pedido['estado']) ?>
  <?= badge_pago($pedido['pago_estado']) ?>
  <div class="ml-auto flex gap-2">
    <button onclick="window.print()" class="btn btn-ghost !h-10">Imprimir comanda</button>
    <?php if (WHATSAPP_NUMBER !== ''): ?>
      <a href="https://wa.me/<?= e($pedido['telefono']) ?>?text=<?= rawurlencode($wa) ?>" target="_blank" rel="noopener" class="btn btn-carbon !h-10">WhatsApp al cliente</a>
    <?php else: ?>
      <a href="tel:<?= e($pedido['telefono']) ?>" class="btn btn-carbon !h-10">Llamar al cliente</a>
    <?php endif; ?>
    <?php if ($sig): ?>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="estado">
        <input type="hidden" name="estado" value="<?= $sig ?>">
        <button class="btn btn-rojo !h-10">Pasar a «<?= e(estado_label($sig)) ?>»</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="grid lg:grid-cols-[1fr_360px] gap-5 items-start">

  <!-- Comanda -->
  <div class="space-y-5">
    <div class="card p-6">
      <div class="flex items-start justify-between gap-4 pb-5 border-b border-carbon/8">
        <div>
          <p class="font-display font-black text-3xl"><?= e($pedido['codigo']) ?></p>
          <p class="text-sm text-carbon/55 mt-1"><?= e(fecha_humana($pedido['creado'])) ?></p>
        </div>
        <div class="text-right">
          <p class="text-[.65rem] uppercase tracking-[.18em] text-carbon/45 font-bold">Total</p>
          <p class="font-display font-black text-3xl text-rojo tabular"><?= precio_cop($pedido['total']) ?></p>
        </div>
      </div>

      <h2 class="font-display font-bold text-lg mt-5 mb-3">Comanda</h2>
      <ul class="divide-y divide-carbon/8">
        <?php foreach ($items as $it): ?>
          <li class="py-3 flex gap-4">
            <span class="w-8 h-8 shrink-0 grid place-items-center rounded-lg bg-rojo text-crema text-sm font-extrabold tabular"><?= (int)$it['cantidad'] ?></span>
            <div class="flex-1 min-w-0">
              <p class="font-semibold"><?= e($it['nombre']) ?></p>
              <?php foreach (json_col($it['adiciones']) as $a): ?>
                <p class="text-sm text-carbon/60">+ <?= e($a['nombre']) ?> <span class="tabular"><?= precio_cop($a['precio']) ?></span></p>
              <?php endforeach; ?>
              <?php if ($it['nota'] !== ''): ?>
                <p class="text-sm font-semibold text-rojo mt-1">⚠ <?= e($it['nota']) ?></p>
              <?php endif; ?>
            </div>
            <span class="tabular font-bold shrink-0"><?= precio_cop($it['subtotal']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>

      <dl class="mt-5 pt-4 border-t border-carbon/10 space-y-1.5 text-sm max-w-xs ml-auto">
        <div class="flex justify-between"><dt class="text-carbon/55">Subtotal</dt><dd class="tabular"><?= precio_cop($pedido['subtotal']) ?></dd></div>
        <?php if ((int)$pedido['descuento'] > 0): ?>
          <div class="flex justify-between text-hoja"><dt>Descuento <?= $pedido['cupon_codigo'] ? '(' . e($pedido['cupon_codigo']) . ')' : '' ?></dt><dd class="tabular">−<?= precio_cop($pedido['descuento']) ?></dd></div>
        <?php endif; ?>
        <div class="flex justify-between"><dt class="text-carbon/55">Domicilio</dt><dd class="tabular"><?= precio_cop($pedido['domicilio']) ?></dd></div>
        <div class="flex justify-between pt-2 mt-2 border-t border-carbon/10 font-bold text-base"><dt>Total</dt><dd class="tabular text-rojo"><?= precio_cop($pedido['total']) ?></dd></div>
      </dl>

      <?php if ($pedido['notas'] !== ''): ?>
        <div class="mt-5 rounded-xl bg-maiz/12 border border-maiz/30 p-4">
          <p class="text-[.65rem] uppercase tracking-[.18em] font-bold text-[#8A6210]">Nota del cliente</p>
          <p class="text-sm mt-1.5">“<?= e($pedido['notas']) ?>”</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Editar datos operativos -->
    <div class="card p-6 no-print">
      <h2 class="font-display font-bold text-lg mb-5">Ajustar el pedido</h2>
      <form method="post" class="grid sm:grid-cols-2 gap-x-4">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="datos">
        <label class="field">
          <span class="label">Método de pago</span>
          <select name="metodo_pago" class="select">
            <?php foreach (['efectivo', 'tarjeta', 'transferencia'] as $m): ?>
              <option value="<?= $m ?>" <?= $pedido['metodo_pago'] === $m ? 'selected' : '' ?>><?= e(metodo_pago_label($m)) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="field">
          <span class="label">Valor del domicilio</span>
          <input type="number" name="domicilio" class="input" value="<?= (int)$pedido['domicilio'] ?>" min="0" step="500">
          <span class="help">Al guardar se recalcula el total.</span>
        </label>
        <label class="field sm:col-span-2">
          <span class="label">Dirección</span>
          <input type="text" name="direccion" class="input" value="<?= e($pedido['direccion']) ?>">
        </label>
        <label class="field">
          <span class="label">Barrio</span>
          <input type="text" name="barrio" class="input" value="<?= e($pedido['barrio']) ?>">
        </label>
        <label class="field">
          <span class="label">Indicaciones</span>
          <input type="text" name="indicaciones" class="input" value="<?= e($pedido['indicaciones']) ?>">
        </label>
        <label class="field sm:col-span-2">
          <span class="label">Notas</span>
          <textarea name="notas" class="textarea !min-h-[4.5rem]"><?= e($pedido['notas']) ?></textarea>
        </label>
        <div class="sm:col-span-2"><button class="btn btn-rojo">Guardar cambios</button></div>
      </form>
    </div>

    <!-- Historial -->
    <div class="card p-6 no-print">
      <h2 class="font-display font-bold text-lg mb-4">Trazabilidad</h2>
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
  </div>

  <!-- Lateral -->
  <aside class="space-y-5">
    <div class="card p-6">
      <h2 class="font-display font-bold text-lg mb-4">Cliente</h2>
      <p class="font-bold text-lg"><?= e($pedido['nombre']) ?></p>
      <p class="text-sm text-carbon/60"><?= e(tel_humano($pedido['telefono'])) ?></p>
      <?php if ($pedido['email'] !== ''): ?><p class="text-sm text-carbon/60"><?= e($pedido['email']) ?></p><?php endif; ?>

      <?php if ($hist): ?>
        <div class="grid grid-cols-2 gap-3 mt-5 pt-5 border-t border-carbon/8">
          <div>
            <p class="text-[.62rem] uppercase tracking-[.16em] text-carbon/45 font-bold">Pedidos</p>
            <p class="font-display font-black text-2xl tabular"><?= (int)$hist['pedidos'] ?></p>
          </div>
          <div>
            <p class="text-[.62rem] uppercase tracking-[.16em] text-carbon/45 font-bold">Histórico</p>
            <p class="font-display font-black text-2xl tabular text-rojo"><?= precio_cop($hist['gastado']) ?></p>
          </div>
        </div>
        <a href="<?= base_url('admin/clientes.php?id=' . (int)$pedido['cliente_id']) ?>" class="text-sm font-semibold text-rojo hover:underline mt-4 inline-block no-print">Ver ficha completa →</a>
      <?php endif; ?>
    </div>

    <div class="card p-6">
      <h2 class="font-display font-bold text-lg mb-3"><?= $pedido['tipo'] === 'recoger' ? 'Recoge en tienda' : 'Entrega' ?></h2>
      <?php if ($pedido['tipo'] === 'recoger'): ?>
        <p class="text-sm text-carbon/65 leading-relaxed">El cliente pasa por el pedido a <?= e(CONTACT_ADDRESS) ?>.</p>
      <?php else: ?>
        <p class="text-sm leading-relaxed"><?= e($pedido['direccion']) ?><br><span class="text-carbon/60"><?= e($pedido['barrio']) ?></span></p>
        <?php if ($pedido['indicaciones'] !== ''): ?>
          <p class="text-sm text-carbon/55 mt-2 italic"><?= e($pedido['indicaciones']) ?></p>
        <?php endif; ?>
        <a href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode($pedido['direccion'] . ', ' . $pedido['barrio'] . ', Medellín') ?>"
           target="_blank" rel="noopener" class="text-sm font-semibold text-rojo hover:underline mt-3 inline-block no-print">Abrir en Maps →</a>
      <?php endif; ?>
    </div>

    <!-- Cambiar estado -->
    <div class="card p-6 no-print">
      <h2 class="font-display font-bold text-lg mb-4">Estado del pedido</h2>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="estado">
        <label class="field">
          <span class="label">Nuevo estado</span>
          <select name="estado" class="select">
            <?php foreach (estados_pedido() as $k => $v): ?>
              <option value="<?= $k ?>" <?= $pedido['estado'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="field">
          <span class="label">Nota interna (opcional)</span>
          <input type="text" name="nota" class="input" placeholder="Ej.: salió con Camilo en la moto">
        </label>
        <button class="btn btn-rojo w-full">Actualizar estado</button>
      </form>
    </div>

    <!-- Pago -->
    <div class="card p-6 no-print">
      <h2 class="font-display font-bold text-lg mb-4">Pago</h2>
      <p class="text-sm text-carbon/60 mb-4"><?= e(metodo_pago_label($pedido['metodo_pago'])) ?>
        <?php if ((int)$pedido['paga_con'] > 0): ?>
          <br>Paga con <?= precio_cop($pedido['paga_con']) ?> · devuelta <strong><?= precio_cop((int)$pedido['paga_con'] - (int)$pedido['total']) ?></strong>
        <?php endif; ?>
      </p>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="pago">
        <label class="field">
          <span class="label">Estado del pago</span>
          <select name="pago_estado" class="select">
            <option value="pendiente" <?= $pedido['pago_estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
            <option value="pagado"    <?= $pedido['pago_estado'] === 'pagado'    ? 'selected' : '' ?>>Pagado</option>
            <option value="fallido"   <?= $pedido['pago_estado'] === 'fallido'   ? 'selected' : '' ?>>Rechazado</option>
          </select>
        </label>
        <label class="field">
          <span class="label">Referencia</span>
          <input type="text" name="pago_ref" class="input" value="<?= e($pedido['pago_ref']) ?>" placeholder="Voucher, Nequi, transacción…">
        </label>
        <button class="btn btn-carbon w-full">Guardar pago</button>
      </form>
    </div>
  </aside>
</div>

<?php admin_footer(); ?>
