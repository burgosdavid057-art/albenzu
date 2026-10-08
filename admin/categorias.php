<?php
require_once __DIR__ . '/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $accion = $_POST['accion'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    // ── Categorías ───────────────────────────────────────────────────────────
    if ($accion === 'cat_guardar') {
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        if ($nombre !== '') {
            $slug = slugify($nombre);
            $chk = db()->prepare('SELECT id FROM categorias WHERE slug = ? AND id != ?');
            $chk->execute([$slug, $id]);
            if ($chk->fetch()) $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);

            $vals = [
                $nombre, $slug,
                trim((string)($_POST['descripcion'] ?? '')),
                trim((string)($_POST['emoji'] ?? '')),
                (int)($_POST['orden'] ?? 0),
                isset($_POST['activo']) ? 1 : 0,
            ];
            if ($id > 0) {
                db()->prepare('UPDATE categorias SET nombre=?, slug=?, descripcion=?, emoji=?, orden=?, activo=? WHERE id=?')
                    ->execute([...$vals, $id]);
                flash('Categoría actualizada.');
            } else {
                db()->prepare('INSERT INTO categorias (nombre, slug, descripcion, emoji, orden, activo) VALUES (?,?,?,?,?,?)')
                    ->execute($vals);
                flash('Categoría creada.');
            }
        }
    }

    if ($accion === 'cat_borrar' && $id > 0) {
        $n = db()->prepare('SELECT COUNT(*) c FROM productos WHERE categoria_id = ?');
        $n->execute([$id]);
        if ((int)$n->fetch()['c'] > 0) {
            flash('No se puede borrar: esa categoría todavía tiene platos. Muévelos primero.', 'error');
        } else {
            db()->prepare('DELETE FROM categorias WHERE id = ?')->execute([$id]);
            flash('Categoría eliminada.');
        }
    }

    // ── Adiciones ────────────────────────────────────────────────────────────
    if ($accion === 'ad_guardar') {
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        $precio = (int)preg_replace('/\D+/', '', (string)($_POST['precio'] ?? '0'));
        if ($nombre !== '') {
            $vals = [$nombre, $precio, (int)($_POST['orden'] ?? 0), isset($_POST['activo']) ? 1 : 0];
            if ($id > 0) {
                db()->prepare('UPDATE adiciones SET nombre=?, precio=?, orden=?, activo=? WHERE id=?')->execute([...$vals, $id]);
                flash('Adición actualizada.');
            } else {
                db()->prepare('INSERT INTO adiciones (nombre, precio, orden, activo) VALUES (?,?,?,?)')->execute($vals);
                flash('Adición creada.');
            }
        }
    }

    if ($accion === 'ad_borrar' && $id > 0) {
        db()->prepare('DELETE FROM adiciones WHERE id = ?')->execute([$id]);
        flash('Adición eliminada.');
    }

    redirect(base_url('admin/categorias.php'));
}

$categorias = get_categorias(false);
$adiciones  = get_adiciones(false);
$editCat    = null;
$editAd     = null;
if (($i = (int)($_GET['cat'] ?? 0)) > 0) $editCat = get_categoria($i);
if (($i = (int)($_GET['ad'] ?? 0)) > 0) {
    $st = db()->prepare('SELECT * FROM adiciones WHERE id = ?'); $st->execute([$i]); $editAd = $st->fetch() ?: null;
}

// Conteo de platos por categoría
$conteo = [];
foreach (db()->query('SELECT categoria_id, COUNT(*) c FROM productos GROUP BY categoria_id')->fetchAll() as $r) {
    $conteo[(int)$r['categoria_id']] = (int)$r['c'];
}

admin_header('Categorías y adiciones', 'categorias.php', 'La estructura de la carta');
?>

<div class="grid lg:grid-cols-2 gap-6 items-start">

  <!-- ── Categorías ─────────────────────────────────────────────────────── -->
  <section class="space-y-5">
    <div class="card p-6">
      <h2 class="font-display font-bold text-xl mb-5"><?= $editCat ? 'Editar categoría' : 'Nueva categoría' ?></h2>
      <form method="post" class="grid sm:grid-cols-2 gap-x-4">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="cat_guardar">
        <input type="hidden" name="id" value="<?= (int)($editCat['id'] ?? 0) ?>">

        <label class="field">
          <span class="label">Nombre *</span>
          <input type="text" name="nombre" class="input" required value="<?= e($editCat['nombre'] ?? '') ?>" placeholder="Platos típicos">
        </label>
        <label class="field">
          <span class="label">Emoji</span>
          <input type="text" name="emoji" class="input" maxlength="4" value="<?= e($editCat['emoji'] ?? '') ?>" placeholder="🫘">
        </label>
        <label class="field sm:col-span-2">
          <span class="label">Descripción</span>
          <input type="text" name="descripcion" class="input" value="<?= e($editCat['descripcion'] ?? '') ?>" placeholder="Se muestra bajo el título en la carta">
        </label>
        <label class="field">
          <span class="label">Orden</span>
          <input type="number" name="orden" class="input tabular" value="<?= (int)($editCat['orden'] ?? count($categorias) + 1) ?>">
        </label>
        <label class="flex items-center gap-2 text-sm font-semibold pt-7">
          <input type="checkbox" name="activo" class="w-4 h-4 accent-[#B7202A]" <?= ($editCat['activo'] ?? 1) ? 'checked' : '' ?>> Visible
        </label>
        <div class="sm:col-span-2 flex gap-2 mt-2">
          <button class="btn btn-rojo"><?= $editCat ? 'Guardar' : 'Crear categoría' ?></button>
          <?php if ($editCat): ?><a href="<?= base_url('admin/categorias.php') ?>" class="btn btn-ghost">Cancelar</a><?php endif; ?>
        </div>
      </form>
    </div>

    <div class="card overflow-hidden">
      <table class="tbl">
        <thead><tr><th>Categoría</th><th class="text-center">Platos</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($categorias as $c): ?>
            <tr class="<?= $c['activo'] ? '' : 'opacity-50' ?>">
              <td>
                <span class="text-lg mr-1.5"><?= $c['emoji'] ?></span>
                <span class="font-semibold"><?= e($c['nombre']) ?></span>
                <?php if (!$c['activo']): ?><span class="chip chip-carbon ml-2">oculta</span><?php endif; ?>
                <span class="block text-[.72rem] text-carbon/45 mt-0.5"><?= e($c['descripcion']) ?></span>
              </td>
              <td class="text-center tabular font-semibold"><?= $conteo[(int)$c['id']] ?? 0 ?></td>
              <td class="text-right whitespace-nowrap">
                <a href="<?= base_url('admin/categorias.php?cat=' . (int)$c['id']) ?>" class="btn btn-ghost !h-8 !px-3 !text-[.7rem]">Editar</a>
                <form method="post" class="inline" onsubmit="return confirm('¿Eliminar la categoría «<?= e($c['nombre']) ?>»?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="accion" value="cat_borrar">
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <button class="btn btn-ghost !h-8 !px-3 !text-[.7rem] !text-rojo !border-rojo/30">Borrar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- ── Adiciones ──────────────────────────────────────────────────────── -->
  <section class="space-y-5">
    <div class="card p-6">
      <h2 class="font-display font-bold text-xl mb-2"><?= $editAd ? 'Editar adición' : 'Nueva adición' ?></h2>
      <p class="text-sm text-carbon/55 mb-5">Se ofrecen en el modal de cualquier plato al agregarlo al pedido.</p>
      <form method="post" class="grid sm:grid-cols-2 gap-x-4">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="ad_guardar">
        <input type="hidden" name="id" value="<?= (int)($editAd['id'] ?? 0) ?>">

        <label class="field">
          <span class="label">Nombre *</span>
          <input type="text" name="nombre" class="input" required value="<?= e($editAd['nombre'] ?? '') ?>" placeholder="Porción de chicharrón paisa">
        </label>
        <label class="field">
          <span class="label">Precio *</span>
          <input type="text" name="precio" class="input tabular" required inputmode="numeric" value="<?= (int)($editAd['precio'] ?? 0) ?>" placeholder="15900">
        </label>
        <label class="field">
          <span class="label">Orden</span>
          <input type="number" name="orden" class="input tabular" value="<?= (int)($editAd['orden'] ?? count($adiciones) + 1) ?>">
        </label>
        <label class="flex items-center gap-2 text-sm font-semibold pt-7">
          <input type="checkbox" name="activo" class="w-4 h-4 accent-[#B7202A]" <?= ($editAd['activo'] ?? 1) ? 'checked' : '' ?>> Activa
        </label>
        <div class="sm:col-span-2 flex gap-2 mt-2">
          <button class="btn btn-rojo"><?= $editAd ? 'Guardar' : 'Crear adición' ?></button>
          <?php if ($editAd): ?><a href="<?= base_url('admin/categorias.php') ?>" class="btn btn-ghost">Cancelar</a><?php endif; ?>
        </div>
      </form>
    </div>

    <div class="card overflow-hidden">
      <table class="tbl">
        <thead><tr><th>Adición</th><th class="text-right">Precio</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($adiciones as $a): ?>
            <tr class="<?= $a['activo'] ? '' : 'opacity-50' ?>">
              <td class="font-semibold"><?= e($a['nombre']) ?>
                <?php if (!$a['activo']): ?><span class="chip chip-carbon ml-2">inactiva</span><?php endif; ?>
              </td>
              <td class="text-right font-bold tabular"><?= precio_cop($a['precio']) ?></td>
              <td class="text-right whitespace-nowrap">
                <a href="<?= base_url('admin/categorias.php?ad=' . (int)$a['id']) ?>" class="btn btn-ghost !h-8 !px-3 !text-[.7rem]">Editar</a>
                <form method="post" class="inline" onsubmit="return confirm('¿Eliminar la adición «<?= e($a['nombre']) ?>»?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="accion" value="ad_borrar">
                  <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                  <button class="btn btn-ghost !h-8 !px-3 !text-[.7rem] !text-rojo !border-rojo/30">Borrar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php admin_footer(); ?>
