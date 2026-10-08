<?php
require_once __DIR__ . '/auth.php';
require_admin();

$categorias = get_categorias(false);

/** Guarda la imagen subida y devuelve el nombre de archivo (o '' si no hubo) */
function guardar_imagen(string $campo, string $slug): string {
    if (empty($_FILES[$campo]['name']) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) return '';
    $tmp  = $_FILES[$campo]['tmp_name'];
    $info = @getimagesize($tmp);
    if (!$info) return '';
    $ext = match ($info[2]) {
        IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif',
        default => '',
    };
    if ($ext === '') return '';
    if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0775, true);
    $nombre = $slug . '-' . substr(bin2hex(random_bytes(3)), 0, 6) . '.' . $ext;
    return move_uploaded_file($tmp, UPLOAD_DIR . '/' . $nombre) ? $nombre : '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $accion = $_POST['accion'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($accion === 'guardar') {
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        if ($nombre === '') {
            flash('El plato necesita un nombre.', 'error');
            redirect(base_url('admin/productos.php'));
        }
        $slug = slugify($nombre);
        // Slug único
        $chk = db()->prepare('SELECT id FROM productos WHERE slug = ? AND id != ?');
        $chk->execute([$slug, $id]);
        if ($chk->fetch()) $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);

        $etiquetas = array_values(array_filter(array_map('trim', explode(',', (string)($_POST['etiquetas'] ?? '')))));
        $campos = [
            'categoria_id'  => (int)($_POST['categoria_id'] ?? 0),
            'nombre'        => $nombre,
            'slug'          => $slug,
            'descripcion'   => trim((string)($_POST['descripcion'] ?? '')),
            'precio'        => (int)preg_replace('/\D+/', '', (string)($_POST['precio'] ?? '0')),
            'precio_oferta' => ($v = (int)preg_replace('/\D+/', '', (string)($_POST['precio_oferta'] ?? '0'))) > 0 ? $v : null,
            'etiquetas'     => json_encode($etiquetas, JSON_UNESCAPED_UNICODE),
            'destacado'     => isset($_POST['destacado']) ? 1 : 0,
            'disponible'    => isset($_POST['disponible']) ? 1 : 0,
            'activo'        => isset($_POST['activo']) ? 1 : 0,
            'orden'         => (int)($_POST['orden'] ?? 0),
        ];

        $img = guardar_imagen('imagen', $slug);

        if ($id > 0) {
            if ($img !== '') $campos['imagen'] = $img;
            $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
            $st  = db()->prepare("UPDATE productos SET $set WHERE id = ?");
            $st->execute([...array_values($campos), $id]);
            flash('Plato actualizado.');
        } else {
            $campos['imagen'] = $img;
            $cols = implode(', ', array_keys($campos));
            $ph   = implode(', ', array_fill(0, count($campos), '?'));
            db()->prepare("INSERT INTO productos ($cols) VALUES ($ph)")->execute(array_values($campos));
            flash('Plato agregado a la carta.');
        }
        redirect(base_url('admin/productos.php'));
    }

    if ($accion === 'borrar' && $id > 0) {
        db()->prepare('DELETE FROM productos WHERE id = ?')->execute([$id]);
        flash('Plato eliminado de la carta.');
        redirect(base_url('admin/productos.php'));
    }

    if ($accion === 'toggle' && $id > 0) {
        $campo = ($_POST['campo'] ?? '') === 'disponible' ? 'disponible' : 'activo';
        db()->prepare("UPDATE productos SET $campo = 1 - $campo WHERE id = ?")->execute([$id]);
        redirect(base_url('admin/productos.php?' . ($_SERVER['QUERY_STRING'] ?? '')));
    }
}

// ── Datos de la vista ────────────────────────────────────────────────────────
$editar = null;
if (($eid = (int)($_GET['editar'] ?? 0)) > 0) $editar = get_producto($eid);

$fCat   = (int)($_GET['cat'] ?? 0);
$buscar = trim($_GET['q'] ?? '');
$productos = get_productos([
    'solo_activos' => false,
    'categoria_id' => $fCat ?: null,
    'buscar'       => $buscar ?: null,
]);

admin_header('Carta', 'productos.php', count($productos) . ' platos · edita precios, fotos y disponibilidad');
?>

<div x-data="{ form: <?= $editar ? 'true' : 'false' ?> }">

  <div class="flex flex-wrap gap-3 items-end mb-5">
    <form method="get" class="flex flex-wrap gap-3 items-end flex-1">
      <label class="block">
        <span class="label">Buscar plato</span>
        <input type="search" name="q" value="<?= e($buscar) ?>" class="input" placeholder="Nombre o descripción">
      </label>
      <label class="block">
        <span class="label">Categoría</span>
        <select name="cat" class="select" onchange="this.form.submit()">
          <option value="0">Todas</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= $fCat === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button class="btn btn-ghost">Filtrar</button>
    </form>
    <button @click="form = true" class="btn btn-rojo">+ Nuevo plato</button>
  </div>

  <!-- Formulario -->
  <div x-show="form" x-cloak class="card p-6 mb-6" id="form">
    <div class="flex items-center justify-between mb-5">
      <h2 class="font-display font-bold text-xl"><?= $editar ? 'Editar «' . e($editar['nombre']) . '»' : 'Nuevo plato' ?></h2>
      <a href="<?= base_url('admin/productos.php') ?>" @click="form = false" class="text-sm text-carbon/45 hover:text-rojo">Cerrar</a>
    </div>

    <form method="post" enctype="multipart/form-data" class="grid lg:grid-cols-3 gap-x-5">
      <?= csrf_field() ?>
      <input type="hidden" name="accion" value="guardar">
      <input type="hidden" name="id" value="<?= (int)($editar['id'] ?? 0) ?>">

      <label class="field lg:col-span-2">
        <span class="label">Nombre *</span>
        <input type="text" name="nombre" class="input" required value="<?= e($editar['nombre'] ?? '') ?>" placeholder="Bandeja Mis Montañas">
      </label>
      <label class="field">
        <span class="label">Categoría</span>
        <select name="categoria_id" class="select">
          <?php foreach ($categorias as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (int)($editar['categoria_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="field lg:col-span-3">
        <span class="label">Descripción</span>
        <textarea name="descripcion" class="textarea !min-h-[5rem]" placeholder="Qué trae el plato, para cuántas personas, cómo se sirve…"><?= e($editar['descripcion'] ?? '') ?></textarea>
      </label>

      <label class="field">
        <span class="label">Precio *</span>
        <input type="text" name="precio" class="input tabular" required inputmode="numeric"
               value="<?= (int)($editar['precio'] ?? 0) ?>" placeholder="32900">
      </label>
      <label class="field">
        <span class="label">Precio en oferta</span>
        <input type="text" name="precio_oferta" class="input tabular" inputmode="numeric"
               value="<?= (int)($editar['precio_oferta'] ?? 0) ?: '' ?>" placeholder="Vacío = sin oferta">
      </label>
      <label class="field">
        <span class="label">Orden dentro de la categoría</span>
        <input type="number" name="orden" class="input tabular" value="<?= (int)($editar['orden'] ?? 0) ?>">
      </label>

      <label class="field lg:col-span-2">
        <span class="label">Etiquetas (separadas por coma)</span>
        <input type="text" name="etiquetas" class="input"
               value="<?= e(implode(', ', json_col($editar['etiquetas'] ?? '[]'))) ?>"
               placeholder="favorito, insignia, vegetariana">
      </label>
      <label class="field">
        <span class="label">Foto del plato</span>
        <input type="file" name="imagen" accept="image/*" class="input !py-2">
        <span class="help">JPG o PNG. Si no subes nada se usa la ilustración de la casa.</span>
      </label>

      <div class="lg:col-span-3 flex flex-wrap gap-5 items-center py-3 border-t border-carbon/8 mt-2">
        <label class="flex items-center gap-2 text-sm font-semibold">
          <input type="checkbox" name="activo" class="w-4 h-4 accent-[#B7202A]" <?= ($editar['activo'] ?? 1) ? 'checked' : '' ?>> Visible en la carta
        </label>
        <label class="flex items-center gap-2 text-sm font-semibold">
          <input type="checkbox" name="disponible" class="w-4 h-4 accent-[#B7202A]" <?= ($editar['disponible'] ?? 1) ? 'checked' : '' ?>> Disponible hoy
        </label>
        <label class="flex items-center gap-2 text-sm font-semibold">
          <input type="checkbox" name="destacado" class="w-4 h-4 accent-[#B7202A]" <?= ($editar['destacado'] ?? 0) ? 'checked' : '' ?>> Destacado en la portada
        </label>
        <button class="btn btn-rojo ml-auto"><?= $editar ? 'Guardar cambios' : 'Agregar a la carta' ?></button>
      </div>
    </form>
  </div>

  <!-- Listado -->
  <div class="card overflow-hidden">
    <?php if (!$productos): ?>
      <p class="py-16 text-center text-carbon/50">No hay platos con esos filtros.</p>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="tbl">
          <thead><tr><th></th><th>Plato</th><th>Categoría</th><th class="text-right">Precio</th><th>Estado</th><th class="text-right">Acciones</th></tr></thead>
          <tbody>
            <?php foreach ($productos as $p): ?>
              <tr class="<?= $p['activo'] ? '' : 'opacity-50' ?>">
                <td class="w-16"><img src="<?= plato_img($p) ?>" alt="" class="w-12 h-12 rounded-lg object-cover"></td>
                <td>
                  <span class="font-semibold"><?= e($p['nombre']) ?></span>
                  <?php if ($p['destacado']): ?><span class="chip chip-rojo ml-1.5">destacado</span><?php endif; ?>
                  <span class="block text-[.72rem] text-carbon/45 max-w-md truncate"><?= e($p['descripcion']) ?></span>
                </td>
                <td class="text-sm text-carbon/60"><?= e($p['categoria']) ?></td>
                <td class="text-right">
                  <span class="font-bold tabular"><?= precio_cop(precio_final($p)) ?></span>
                  <?php if (tiene_oferta($p)): ?>
                    <span class="block text-[.7rem] text-carbon/40 line-through tabular"><?= precio_cop($p['precio']) ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <form method="post" class="inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="toggle">
                    <input type="hidden" name="campo" value="disponible">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button class="state <?= $p['disponible'] ? 'state-entregado' : 'state-cancelado' ?>" title="Cambiar disponibilidad">
                      <?= $p['disponible'] ? 'Disponible' : 'Agotado' ?>
                    </button>
                  </form>
                  <?php if (!$p['activo']): ?><span class="block text-[.7rem] text-carbon/45 mt-1">Oculto</span><?php endif; ?>
                </td>
                <td class="text-right whitespace-nowrap">
                  <a href="<?= base_url('admin/productos.php?editar=' . (int)$p['id']) ?>#form" class="btn btn-ghost !h-9 !px-3 !text-[.72rem]">Editar</a>
                  <form method="post" class="inline" onsubmit="return confirm('¿Eliminar «<?= e($p['nombre']) ?>» de la carta?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="borrar">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button class="btn btn-ghost !h-9 !px-3 !text-[.72rem] !text-rojo !border-rojo/30">Borrar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php admin_footer(); ?>
