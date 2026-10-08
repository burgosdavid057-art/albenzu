<?php
require_once __DIR__ . '/auth.php';
require_admin();

$campos = [
    'domicilio_valor'    => ['Valor del domicilio', 'num',  'Lo que se cobra por llevar el pedido.'],
    'domicilio_gratis'   => ['Domicilio gratis desde', 'num', 'Pedidos por encima de este valor no pagan envío. 0 lo desactiva.'],
    'pedido_minimo'      => ['Pedido mínimo', 'num', 'Por debajo de este valor no se puede confirmar. 0 lo desactiva.'],
    'tiempo_domicilio'   => ['Tiempo estimado a domicilio', 'txt', 'Se muestra en la web. Ej.: 35 – 50 min'],
    'tiempo_recoger'     => ['Tiempo estimado para recoger', 'txt', 'Ej.: 20 – 25 min'],
    'hora_apertura'      => ['Hora de apertura', 'hora', ''],
    'hora_cierre'        => ['Hora de cierre', 'hora', ''],
    'aviso'              => ['Aviso de cobertura', 'txt', 'Aparece en la portada y en contacto.'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    foreach ($campos as $k => $c) {
        $v = trim((string)($_POST[$k] ?? ''));
        if ($c[1] === 'num') $v = (string)(int)preg_replace('/\D+/', '', $v);
        set_ajuste($k, $v);
    }
    foreach (['abierto', 'pago_tarjeta', 'pago_efectivo', 'pago_transferencia'] as $k) {
        set_ajuste($k, isset($_POST[$k]) ? '1' : '0');
    }
    flash('Ajustes guardados.');
    redirect(base_url('admin/ajustes.php'));
}

admin_header('Ajustes', 'ajustes.php', 'Domicilios, horarios y métodos de pago');
?>

<form method="post" class="grid lg:grid-cols-2 gap-6 items-start">
  <?= csrf_field() ?>

  <div class="space-y-6">
    <div class="card p-6">
      <h2 class="font-display font-bold text-xl mb-5">Domicilios</h2>
      <?php foreach (['domicilio_valor', 'domicilio_gratis', 'pedido_minimo'] as $k):
        [$lbl, , $help] = $campos[$k]; ?>
        <label class="field">
          <span class="label"><?= e($lbl) ?></span>
          <input type="text" name="<?= $k ?>" class="input tabular" inputmode="numeric" value="<?= (int)ajuste_int($k) ?>">
          <span class="help"><?= e($help) ?></span>
        </label>
      <?php endforeach; ?>
      <label class="field !mb-0">
        <span class="label">Aviso de cobertura</span>
        <textarea name="aviso" class="textarea !min-h-[4.5rem]"><?= e(ajuste('aviso')) ?></textarea>
        <span class="help"><?= e($campos['aviso'][2]) ?></span>
      </label>
    </div>

    <div class="card p-6">
      <h2 class="font-display font-bold text-xl mb-5">Tiempos de entrega</h2>
      <div class="grid sm:grid-cols-2 gap-x-4">
        <?php foreach (['tiempo_domicilio', 'tiempo_recoger'] as $k):
          [$lbl, , $help] = $campos[$k]; ?>
          <label class="field">
            <span class="label"><?= e($lbl) ?></span>
            <input type="text" name="<?= $k ?>" class="input" value="<?= e(ajuste($k)) ?>">
            <span class="help"><?= e($help) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="space-y-6">
    <div class="card p-6">
      <h2 class="font-display font-bold text-xl mb-5">Horario y estado</h2>
      <div class="grid sm:grid-cols-2 gap-x-4">
        <label class="field">
          <span class="label">Hora de apertura</span>
          <input type="time" name="hora_apertura" class="input" value="<?= e(ajuste('hora_apertura', '11:00')) ?>">
        </label>
        <label class="field">
          <span class="label">Hora de cierre</span>
          <input type="time" name="hora_cierre" class="input" value="<?= e(ajuste('hora_cierre', '22:00')) ?>">
        </label>
      </div>
      <label class="flex items-start gap-3 rounded-xl border border-carbon/12 p-4 cursor-pointer has-[:checked]:border-rojo has-[:checked]:bg-rojo/5 transition-colors">
        <input type="checkbox" name="abierto" class="w-4 h-4 accent-[#B7202A] mt-0.5" <?= ajuste('abierto', '1') === '1' ? 'checked' : '' ?>>
        <span>
          <span class="block font-bold text-sm">Recibiendo pedidos</span>
          <span class="block text-xs text-carbon/55 mt-1">Desmárcalo para cerrar el canal en línea (por inventario, festivo o corte). La carta sigue visible.</span>
        </span>
      </label>
      <p class="mt-4 text-sm">
        Estado actual:
        <?php if (esta_abierto()): ?>
          <span class="state state-entregado">Abierto y recibiendo</span>
        <?php else: ?>
          <span class="state state-cancelado">Cerrado</span>
        <?php endif; ?>
      </p>
    </div>

    <div class="card p-6">
      <h2 class="font-display font-bold text-xl mb-2">Métodos de pago</h2>
      <p class="text-sm text-carbon/55 mb-5">Lo que el cliente puede elegir al confirmar el pedido.</p>
      <div class="space-y-2">
        <?php foreach ([
            'pago_tarjeta'       => ['Tarjeta en línea', 'Sale a la pasarela de pago. En esta demo la pasarela es simulada.'],
            'pago_efectivo'      => ['Efectivo', 'Se paga al recibir o al recoger.'],
            'pago_transferencia' => ['Transferencia / Nequi', 'Se coordina por WhatsApp con el comprobante.'],
        ] as $k => $c): ?>
          <label class="flex items-start gap-3 rounded-xl border border-carbon/12 p-4 cursor-pointer has-[:checked]:border-rojo has-[:checked]:bg-rojo/5 transition-colors">
            <input type="checkbox" name="<?= $k ?>" class="w-4 h-4 accent-[#B7202A] mt-0.5" <?= ajuste($k, '1') === '1' ? 'checked' : '' ?>>
            <span>
              <span class="block font-bold text-sm"><?= e($c[0]) ?></span>
              <span class="block text-xs text-carbon/55 mt-1"><?= e($c[1]) ?></span>
            </span>
          </label>
        <?php endforeach; ?>
      </div>

      <?php if (!PAYMENT_ENABLED): ?>
        <div class="flash flash-info mt-5">
          La pasarela real (<?= e(ucfirst(PAYMENT_PROVIDER)) ?>) aún no está conectada. El flujo de tarjeta
          funciona de punta a punta contra una pasarela simulada; al recibir las llaves del comercio se
          activa en <code>config/config.php</code>.
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="lg:col-span-2">
    <button class="btn btn-rojo btn-lg">Guardar ajustes</button>
  </div>
</form>

<?php admin_footer(); ?>
