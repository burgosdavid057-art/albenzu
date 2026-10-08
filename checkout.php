<?php
require_once __DIR__ . '/includes/functions.php';

if (!carrito()) {
    redirect(base_url('carrito.php'));
}

$tipo   = $_SESSION['tipo_entrega'] ?? 'domicilio';
$tipo   = $tipo === 'recoger' ? 'recoger' : 'domicilio';
$errors = [];
$old    = $_SESSION['checkout_old'] ?? [];
unset($_SESSION['checkout_old']);

// Métodos de pago habilitados desde el panel
$metodos = array_filter([
    'tarjeta'       => ajuste('pago_tarjeta', '1') === '1',
    'efectivo'      => ajuste('pago_efectivo', '1') === '1',
    'transferencia' => ajuste('pago_transferencia', '1') === '1',
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        flash('La sesión expiró. Revisa los datos y envía de nuevo.', 'error');
        redirect(base_url('checkout.php'));
    }

    $d = [
        'tipo'         => ($_POST['tipo'] ?? 'domicilio') === 'recoger' ? 'recoger' : 'domicilio',
        'nombre'       => trim((string)($_POST['nombre'] ?? '')),
        'telefono'     => trim((string)($_POST['telefono'] ?? '')),
        'email'        => trim((string)($_POST['email'] ?? '')),
        'direccion'    => trim((string)($_POST['direccion'] ?? '')),
        'barrio'       => trim((string)($_POST['barrio'] ?? '')),
        'indicaciones' => trim((string)($_POST['indicaciones'] ?? '')),
        'notas'        => trim((string)($_POST['notas'] ?? '')),
        'metodo_pago'  => (string)($_POST['metodo_pago'] ?? 'efectivo'),
        'paga_con'     => (int)preg_replace('/\D+/', '', (string)($_POST['paga_con'] ?? '0')),
    ];
    $tipo = $d['tipo'];
    $_SESSION['tipo_entrega'] = $tipo;

    if (mb_strlen($d['nombre']) < 3)                        $errors['nombre']   = 'Escribe tu nombre completo.';
    if (strlen(preg_replace('/\D+/', '', $d['telefono'])) < 7) $errors['telefono'] = 'Necesitamos un teléfono de contacto válido.';
    if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Ese correo no parece válido.';
    if ($d['tipo'] === 'domicilio') {
        if (mb_strlen($d['direccion']) < 6) $errors['direccion'] = 'Necesitamos la dirección completa con número de apto o casa.';
        if ($d['barrio'] === '')            $errors['barrio']    = 'Dinos el barrio para calcular la ruta.';
    }
    if (!isset($metodos[$d['metodo_pago']])) $errors['metodo_pago'] = 'Elige un método de pago.';

    $res = resumen_carrito($d['tipo']);
    if ($d['metodo_pago'] === 'efectivo' && $d['paga_con'] > 0 && $d['paga_con'] < $res['total']) {
        $errors['paga_con'] = 'Ese monto es menor que el total del pedido.';
    }

    if (!$errors) {
        $pedido = crear_pedido($d);
        carrito_vaciar();
        $_SESSION['ultimo_pedido'] = $pedido['codigo'];

        if ($d['metodo_pago'] === 'tarjeta') {
            redirect(base_url('pasarela.php?codigo=' . urlencode($pedido['codigo'])));
        }
        redirect(base_url('pedido.php?codigo=' . urlencode($pedido['codigo'])));
    }

    $old = $d;
}

$res        = resumen_carrito($tipo);
$page_title = 'Tus datos · ' . SITE_NAME;
$active     = '';
$v          = fn(string $k, string $def = '') => e((string)($old[$k] ?? $def));

include __DIR__ . '/includes/header.php';
?>

<section class="paper">
  <div class="mx-auto max-w-7xl px-5 py-12 lg:py-16">
    <p class="eyebrow mb-4">Paso 2 de 3</p>
    <h1 class="h-display text-4xl sm:text-5xl mb-10">¿A dónde va y quién lo recibe?</h1>

    <?php if ($errors): ?>
      <div class="flash flash-error mb-6">Revisa los campos marcados en rojo para continuar.</div>
    <?php endif; ?>

    <form method="post" class="grid lg:grid-cols-[1fr_380px] gap-8 items-start" x-data="{ tipo: '<?= $tipo ?>', pago: '<?= e($old['metodo_pago'] ?? array_key_first($metodos)) ?>' }">
      <?= csrf_field() ?>

      <div class="space-y-6">

        <!-- Tipo de entrega -->
        <div class="rounded-2xl bg-white border border-carbon/10 p-6">
          <h2 class="font-display font-bold text-xl mb-5">Entrega</h2>
          <div class="grid sm:grid-cols-2 gap-3">
            <label class="pick">
              <input type="radio" name="tipo" value="domicilio" x-model="tipo" <?= $tipo === 'domicilio' ? 'checked' : '' ?>>
              <span class="pick__in flex items-start gap-3">
                <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-6"/></svg>
                <span>
                  <span class="block font-bold">Domicilio</span>
                  <span class="block text-xs text-carbon/55 mt-1">Te lo llevamos en <?= e(ajuste('tiempo_domicilio')) ?> · <?= precio_cop(ajuste_int('domicilio_valor')) ?></span>
                </span>
              </span>
            </label>

            <label class="pick">
              <input type="radio" name="tipo" value="recoger" x-model="tipo" <?= $tipo === 'recoger' ? 'checked' : '' ?>>
              <span class="pick__in flex items-start gap-3">
                <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5h-3V21m9 0V9.349m-15 0V21M2.25 21h19.5M12 2.25 3 8.25v.75h18v-.75L12 2.25Z"/></svg>
                <span>
                  <span class="block font-bold">Paso a recogerlo</span>
                  <span class="block text-xs text-carbon/55 mt-1">Listo en <?= e(ajuste('tiempo_recoger')) ?> · sin costo de envío</span>
                </span>
              </span>
            </label>
          </div>
        </div>

        <!-- Datos de contacto -->
        <div class="rounded-2xl bg-white border border-carbon/10 p-6">
          <h2 class="font-display font-bold text-xl mb-5">Tus datos</h2>
          <div class="grid sm:grid-cols-2 gap-x-4">
            <label class="field">
              <span class="label">Nombre y apellido *</span>
              <input type="text" name="nombre" class="input" value="<?= $v('nombre') ?>" required autocomplete="name" placeholder="María Restrepo">
              <?php if (isset($errors['nombre'])): ?><span class="err"><?= e($errors['nombre']) ?></span><?php endif; ?>
            </label>
            <label class="field">
              <span class="label">Celular *</span>
              <input type="tel" name="telefono" class="input" value="<?= $v('telefono') ?>" required autocomplete="tel" placeholder="300 000 0000">
              <?php if (isset($errors['telefono'])): ?><span class="err"><?= e($errors['telefono']) ?></span><?php endif; ?>
              <span class="help">Te escribimos por aquí cuando salga el pedido.</span>
            </label>
          </div>
          <label class="field">
            <span class="label">Correo (opcional)</span>
            <input type="email" name="email" class="input" value="<?= $v('email') ?>" autocomplete="email" placeholder="tucorreo@ejemplo.com">
            <?php if (isset($errors['email'])): ?><span class="err"><?= e($errors['email']) ?></span><?php endif; ?>
          </label>
        </div>

        <!-- Dirección (sólo domicilio) -->
        <div class="rounded-2xl bg-white border border-carbon/10 p-6" x-show="tipo === 'domicilio'" x-cloak>
          <h2 class="font-display font-bold text-xl mb-5">¿Dónde te lo dejamos?</h2>
          <label class="field">
            <span class="label">Dirección *</span>
            <input type="text" name="direccion" class="input" value="<?= $v('direccion') ?>" autocomplete="street-address" placeholder="Cra 78 # 32 - 14, apto 502">
            <?php if (isset($errors['direccion'])): ?><span class="err"><?= e($errors['direccion']) ?></span><?php endif; ?>
          </label>
          <div class="grid sm:grid-cols-2 gap-x-4">
            <label class="field">
              <span class="label">Barrio *</span>
              <input type="text" name="barrio" class="input" value="<?= $v('barrio') ?>" placeholder="Belén Rosales" list="barrios">
              <datalist id="barrios">
                <?php foreach (['Belén', 'Belén Rosales', 'Laureles', 'La América', 'Nueva Villa de Aburrá', 'Estadio', 'Floresta', 'Calasanz', 'Robledo', 'Santa Mónica'] as $b): ?>
                  <option value="<?= e($b) ?>">
                <?php endforeach; ?>
              </datalist>
              <?php if (isset($errors['barrio'])): ?><span class="err"><?= e($errors['barrio']) ?></span><?php endif; ?>
            </label>
            <label class="field">
              <span class="label">Indicaciones para el domiciliario</span>
              <input type="text" name="indicaciones" class="input" value="<?= $v('indicaciones') ?>" placeholder="Portería, torre 2, timbre 502">
            </label>
          </div>
        </div>

        <!-- Recoger -->
        <div class="rounded-2xl bg-carbon text-crema p-6" x-show="tipo === 'recoger'" x-cloak>
          <h2 class="font-display font-bold text-xl mb-3">Te esperamos en la 80</h2>
          <p class="text-crema/70 text-sm leading-relaxed">
            <?= e(CONTACT_ADDRESS) ?>, <?= e(CONTACT_CITY) ?>.<br>
            <?= e(CONTACT_HOURS) ?>. Te avisamos por WhatsApp apenas esté listo — llega con el código del pedido.
          </p>
          <a href="https://www.google.com/maps/search/?api=1&query=<?= MAPS_QUERY ?>" target="_blank" rel="noopener" class="btn btn-crema mt-5">Ver en el mapa</a>
        </div>

        <!-- Pago -->
        <div class="rounded-2xl bg-white border border-carbon/10 p-6">
          <h2 class="font-display font-bold text-xl mb-5">¿Cómo pagas?</h2>
          <div class="grid sm:grid-cols-3 gap-3">
            <?php
            $iconos = [
                'tarjeta'       => ['Tarjeta', 'Débito o crédito, en línea', 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
                'efectivo'      => ['Efectivo', 'Pagas al recibir', 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                'transferencia' => ['Transferencia', 'Nequi, Daviplata o banco', 'M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z'],
            ];
            foreach ($metodos as $m => $on):
              [$t, $s, $path] = $iconos[$m]; ?>
              <label class="pick">
                <input type="radio" name="metodo_pago" value="<?= $m ?>" x-model="pago" <?= ($old['metodo_pago'] ?? array_key_first($metodos)) === $m ? 'checked' : '' ?>>
                <span class="pick__in">
                  <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $path ?>"/></svg>
                  <span class="block font-bold text-sm"><?= $t ?></span>
                  <span class="block text-xs text-carbon/55 mt-1"><?= $s ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
          <?php if (isset($errors['metodo_pago'])): ?><p class="err"><?= e($errors['metodo_pago']) ?></p><?php endif; ?>

          <div x-show="pago === 'efectivo'" x-cloak class="mt-5">
            <label class="field !mb-0">
              <span class="label">¿Con cuánto pagas? (para llevar la devuelta)</span>
              <input type="text" name="paga_con" class="input" value="<?= $v('paga_con') ?>" inputmode="numeric" placeholder="<?= number_format($res['total'], 0, ',', '.') ?>">
              <?php if (isset($errors['paga_con'])): ?><span class="err"><?= e($errors['paga_con']) ?></span><?php endif; ?>
              <span class="help">Déjalo vacío si vas a pagar con el valor exacto.</span>
            </label>
          </div>

          <div x-show="pago === 'tarjeta'" x-cloak class="mt-5 rounded-xl bg-crema-2 border border-carbon/10 p-4 text-sm text-carbon/70 leading-relaxed">
            Al confirmar te llevamos a la pasarela de pago para completar la transacción con tu tarjeta.
            <strong class="text-carbon">Albenzu nunca guarda los datos de tu tarjeta.</strong>
          </div>

          <div x-show="pago === 'transferencia'" x-cloak class="mt-5 rounded-xl bg-crema-2 border border-carbon/10 p-4 text-sm text-carbon/70 leading-relaxed">
            Al confirmar te enviamos los datos de la cuenta por WhatsApp. El pedido entra a cocina apenas
            recibamos el comprobante.
          </div>
        </div>

        <label class="field">
          <span class="label">Algo más que debamos saber</span>
          <textarea name="notas" class="textarea" placeholder="Ej.: es para una reunión, necesitamos cubiertos extra, timbre dañado…"><?= $v('notas') ?></textarea>
        </label>
      </div>

      <!-- Resumen lateral -->
      <aside class="lg:sticky lg:top-[100px] rounded-2xl bg-carbon text-crema p-6">
        <h3 class="font-display font-bold text-xl mb-5">Tu pedido</h3>
        <ul class="space-y-3 mb-5 max-h-72 overflow-y-auto pr-1">
          <?php foreach (carrito() as $it): ?>
            <li class="flex gap-3 text-sm">
              <span class="w-6 h-6 shrink-0 grid place-items-center rounded-md bg-crema/10 text-[.7rem] font-extrabold tabular"><?= (int)$it['cantidad'] ?></span>
              <span class="flex-1 min-w-0">
                <span class="block leading-tight"><?= e($it['nombre']) ?></span>
                <?php if ($it['adiciones']): ?>
                  <span class="block text-[.72rem] text-crema/45 leading-tight mt-0.5"><?= e(implode(' · ', array_column($it['adiciones'], 'nombre'))) ?></span>
                <?php endif; ?>
              </span>
              <span class="tabular font-semibold shrink-0"><?= precio_cop(linea_total($it)) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>

        <dl class="space-y-2 text-sm border-t border-crema/15 pt-4">
          <div class="flex justify-between"><dt class="text-crema/60">Subtotal</dt><dd class="tabular"><?= precio_cop($res['subtotal']) ?></dd></div>
          <?php if ($res['descuento'] > 0): ?>
            <div class="flex justify-between text-maiz"><dt>Descuento</dt><dd class="tabular">−<?= precio_cop($res['descuento']) ?></dd></div>
          <?php endif; ?>
          <div class="flex justify-between">
            <dt class="text-crema/60">Envío</dt>
            <dd class="tabular" x-text="tipo === 'recoger' ? 'Gratis' : '<?= $res['domicilio'] > 0 ? precio_cop($res['domicilio']) : 'Gratis' ?>'"><?= $res['domicilio'] > 0 ? precio_cop($res['domicilio']) : 'Gratis' ?></dd>
          </div>
        </dl>
        <div class="flex items-end justify-between mt-4 pt-4 border-t border-crema/15">
          <span class="font-display font-bold">Total</span>
          <span class="font-display font-black text-3xl text-maiz tabular"><?= precio_cop($res['total']) ?></span>
        </div>
        <p class="text-[.68rem] text-crema/40 mt-2">El envío se recalcula al confirmar según la opción elegida.</p>

        <button type="submit" class="btn btn-crema btn-lg w-full mt-6" data-cursor>
          <span x-text="pago === 'tarjeta' ? 'Ir a pagar' : 'Confirmar pedido'">Confirmar pedido</span>
        </button>
        <a href="<?= base_url('carrito.php') ?>" class="block text-center text-xs text-crema/45 hover:text-crema mt-4 transition-colors">← Volver al carrito</a>
      </aside>
    </form>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
