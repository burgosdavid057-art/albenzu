<?php
/**
 * Pasarela de pago — SIMULADA.
 *
 * Esta pantalla NO pide ni almacena datos de tarjeta: reproduce el paso del
 * checkout en el que el cliente sale a la pasarela y vuelve con un resultado,
 * para poder mostrarle al restaurante el flujo completo (aprobado / rechazado)
 * sin credenciales reales.
 *
 * Al integrar la pasarela real (Wompi o Bold):
 *   1. Sustituir el bloque de botones por el redirect firmado del proveedor.
 *   2. Recibir el webhook / redirect de retorno y llamar a
 *      marcar_pago($pedidoId, 'pagado'|'fallido', $referencia).
 *   3. Poner PAYMENT_ENABLED = true en config/config.php.
 */
require_once __DIR__ . '/includes/functions.php';

$codigo = trim($_GET['codigo'] ?? $_POST['codigo'] ?? '');
$pedido = $codigo !== '' ? get_pedido_codigo($codigo) : null;

if (!$pedido) {
    flash('No encontramos ese pedido.', 'error');
    redirect(base_url('index.php'));
}
if ($pedido['pago_estado'] === 'pagado') {
    redirect(base_url('pedido.php?codigo=' . urlencode($pedido['codigo'])));
}

// Resultado simulado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $r = $_POST['resultado'] ?? '';
    if ($r === 'aprobado') {
        marcar_pago((int)$pedido['id'], 'pagado', 'DEMO-' . strtoupper(bin2hex(random_bytes(4))));
        cambiar_estado_pedido((int)$pedido['id'], 'confirmado', 'Pago aprobado en la pasarela');
        flash('¡Pago aprobado! Ya pasamos tu pedido a cocina.');
        redirect(base_url('pedido.php?codigo=' . urlencode($pedido['codigo'])));
    }
    if ($r === 'rechazado') {
        marcar_pago((int)$pedido['id'], 'fallido', '');
        flash('El pago fue rechazado. Puedes reintentar o cambiar el método de pago.', 'error');
        redirect(base_url('pasarela.php?codigo=' . urlencode($pedido['codigo'])));
    }
    if ($r === 'efectivo') {
        db()->prepare('UPDATE pedidos SET metodo_pago = ?, pago_estado = ?, actualizado = ? WHERE id = ?')
            ->execute(['efectivo', 'pendiente', date('Y-m-d H:i:s'), $pedido['id']]);
        flash('Cambiamos tu pedido a pago en efectivo contra entrega.');
        redirect(base_url('pedido.php?codigo=' . urlencode($pedido['codigo'])));
    }
}

$page_title = 'Pago del pedido ' . $pedido['codigo'];
$active     = '';
include __DIR__ . '/includes/header.php';
?>

<section class="paper min-h-[70vh] grid place-items-center py-16">
  <div class="w-full max-w-lg px-5">

    <div class="rounded-3xl bg-white border border-carbon/10 shadow-xl overflow-hidden">
      <!-- Cabecera del "proveedor" -->
      <div class="bg-carbon text-crema px-7 py-5 flex items-center justify-between">
        <div>
          <p class="text-[.62rem] uppercase tracking-[.28em] text-crema/50">Pasarela de pago</p>
          <p class="font-display font-bold text-lg mt-0.5"><?= ucfirst(PAYMENT_PROVIDER) ?> Checkout</p>
        </div>
        <span class="flex items-center gap-1.5 text-[.65rem] font-bold uppercase tracking-widest bg-maiz/20 text-maiz px-3 py-1.5 rounded-full">
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2 2 20h20L12 2Zm0 6 5.5 10h-11L12 8Z"/></svg>
          Modo demo
        </span>
      </div>

      <div class="p-7">
        <div class="rounded-2xl bg-crema-2 border border-carbon/10 p-5 mb-6">
          <div class="flex items-center justify-between">
            <span class="text-sm text-carbon/60">Pedido</span>
            <span class="font-mono font-bold text-sm"><?= e($pedido['codigo']) ?></span>
          </div>
          <div class="flex items-center justify-between mt-2">
            <span class="text-sm text-carbon/60">Comercio</span>
            <span class="text-sm font-semibold"><?= e(SITE_LEGAL) ?></span>
          </div>
          <div class="flex items-end justify-between mt-4 pt-4 border-t border-carbon/10">
            <span class="font-display font-bold">Total a pagar</span>
            <span class="font-display font-black text-3xl text-rojo tabular"><?= precio_cop($pedido['total']) ?></span>
          </div>
        </div>

        <?php if ($pedido['pago_estado'] === 'fallido'): ?>
          <div class="flash flash-error mb-5">El intento anterior fue rechazado por el emisor.</div>
        <?php endif; ?>

        <div class="rounded-xl bg-crema border border-carbon/10 p-4 text-[.82rem] text-carbon/65 leading-relaxed mb-6">
          <strong class="text-carbon">Esta es una simulación.</strong> En la versión en producción, aquí se abre
          el formulario seguro de <?= ucfirst(PAYMENT_PROVIDER) ?> —los datos de la tarjeta viajan directamente
          al proveedor y nunca pasan por el servidor del restaurante. Usa los botones para ver los dos desenlaces.
        </div>

        <form method="post" class="space-y-3">
          <?= csrf_field() ?>
          <input type="hidden" name="codigo" value="<?= e($pedido['codigo']) ?>">
          <button name="resultado" value="aprobado" class="btn btn-rojo btn-lg w-full">
            Simular pago aprobado
          </button>
          <button name="resultado" value="rechazado" class="btn btn-ghost w-full">
            Simular pago rechazado
          </button>
          <button name="resultado" value="efectivo" class="w-full text-sm font-semibold text-carbon/50 hover:text-rojo transition-colors py-2">
            Mejor pago en efectivo al recibir
          </button>
        </form>
      </div>
    </div>

    <p class="text-center text-xs text-carbon/40 mt-6">
      Transacción protegida · <?= e(SITE_LEGAL) ?> · NIT en trámite
    </p>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
