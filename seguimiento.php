<?php
require_once __DIR__ . '/includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cod = trim((string)($_POST['codigo'] ?? ''));
    if ($cod === '') {
        $error = 'Escribe el código que te dimos al confirmar el pedido.';
    } elseif (!get_pedido_codigo($cod)) {
        $error = 'No encontramos ningún pedido con ese código. Revísalo e intenta de nuevo.';
    } else {
        redirect(base_url('pedido.php?codigo=' . urlencode($cod)));
    }
}

$page_title = 'Seguir mi pedido · ' . SITE_NAME;
$active     = '';
include __DIR__ . '/includes/header.php';
?>

<section class="paper min-h-[70vh] grid place-items-center py-16">
  <div class="w-full max-w-md px-5">
    <div class="text-center mb-8">
      <p class="eyebrow justify-center mb-4">Seguimiento</p>
      <h1 class="h-display text-4xl sm:text-5xl">¿Por dónde va<br>tu pedido?</h1>
      <p class="text-carbon/60 mt-4">Escribe el código que te dimos al confirmar.</p>
    </div>

    <form method="post" class="rounded-3xl bg-white border border-carbon/10 p-7 shadow-sm">
      <?= csrf_field() ?>
      <label class="field !mb-0">
        <span class="label">Código del pedido</span>
        <input type="text" name="codigo" class="input text-center font-mono text-lg uppercase tracking-wider"
               placeholder="ALB-XXXX" autofocus value="<?= e($_POST['codigo'] ?? '') ?>">
        <?php if ($error !== ''): ?><span class="err"><?= e($error) ?></span><?php endif; ?>
      </label>
      <button class="btn btn-rojo btn-lg w-full mt-5">Buscar mi pedido</button>
    </form>

    <p class="text-center text-sm text-carbon/50 mt-6">
      ¿Perdiste el código? <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="text-rojo font-semibold hover:underline">Escríbenos por WhatsApp</a>
      o llama al <a href="tel:<?= e(CONTACT_PHONE_TEL) ?>" class="text-rojo font-semibold hover:underline"><?= e(CONTACT_PHONE) ?></a>.
    </p>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
