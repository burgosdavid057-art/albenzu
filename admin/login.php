<?php
require_once __DIR__ . '/auth.php';

if (is_admin()) redirect(base_url('admin/index.php'));

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim((string)($_POST['usuario'] ?? ''));
    $p = (string)($_POST['clave'] ?? '');
    if (check_admin_login($u, $p)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = $u;
        redirect(base_url('admin/index.php'));
    }
    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Entrar · Panel <?= e(SITE_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,900;1,9..144,900&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config={theme:{extend:{colors:{rojo:{DEFAULT:'#B7202A',dark:'#8E1A22'},crema:{DEFAULT:'#F6F0E2',2:'#EFE6D2'},carbon:'#241A16',maiz:'#E0A526'},fontFamily:{display:['Fraunces','serif'],sans:['Manrope','sans-serif']}}}}
</script>
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="font-sans bg-carbon text-crema min-h-screen grid place-items-center p-5">
  <div class="w-full max-w-sm">
    <div class="text-center mb-8">
      <div class="w-20 h-20 mx-auto mb-5"><?= logo_svg(80) ?></div>
      <h1 class="font-display font-black text-2xl">Panel del restaurante</h1>
      <p class="text-crema/50 text-sm mt-1.5">Pedidos, carta y clientes</p>
    </div>

    <form method="post" class="bg-crema text-carbon rounded-2xl p-7 shadow-2xl">
      <?php if ($error): ?>
        <div class="flash flash-error mb-5"><?= e($error) ?></div>
      <?php endif; ?>

      <label class="field">
        <span class="label">Usuario</span>
        <input type="text" name="usuario" class="input" autofocus autocomplete="username" required>
      </label>
      <label class="field">
        <span class="label">Contraseña</span>
        <input type="password" name="clave" class="input" autocomplete="current-password" required>
      </label>
      <button class="btn btn-rojo btn-lg w-full mt-2">Entrar</button>
    </form>

    <p class="text-center text-xs text-crema/35 mt-6">
      Demo · usuario <strong class="text-crema/60"><?= e(ADMIN_USER) ?></strong> · clave <strong class="text-crema/60"><?= e(ADMIN_PASS_PLAIN) ?></strong>
    </p>
    <p class="text-center text-xs text-crema/25 mt-2">
      <a href="<?= base_url('index.php') ?>" class="hover:text-crema/60 transition-colors">← Volver al sitio</a>
    </p>
  </div>
</body>
</html>
