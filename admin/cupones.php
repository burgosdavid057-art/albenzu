<?php
require_once __DIR__ . '/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $id = (int)($_POST['id'] ?? 0);

    if (($_POST['accion'] ?? '') === 'guardar') {
        $codigo = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)($_POST['codigo'] ?? '')));
        if ($codigo === '') {
            flash('El cupón necesita un código.', 'error');
            redirect(base_url('admin/cupones.php'));
        }
        $vals = [
            $codigo,
            ($_POST['tipo'] ?? 'pct') === 'fijo' ? 'fijo' : 'pct',
            (int)preg_replace('/\D+/', '', (string)($_POST['valor'] ?? '0')),
            (int)preg_replace('/\D+/', '', (string)($_POST['min_compra'] ?? '0')),
            (int)($_POST['usos_max'] ?? 0),
            trim((string)($_POST['vence'] ?? '')),
            isset($_POST['activo']) ? 1 : 0,
        ];
        try {
            if ($id > 0) {
                db()->prepare('UPDATE cupones SET codigo=?, tipo=?, valor=?, min_compra=?, usos_max=?, vence=?, activo=? WHERE id=?')
                    ->execute([...$vals, $id]);
                flash('Cupón actualizado.');
            } else {
                db()->prepare('INSERT INTO cupones (codigo, tipo, valor, min_compra, usos_max, vence, activo) VALUES (?,?,?,?,?,?,?)')
                    ->execute($vals);
                flash('Cupón creado.');
            }
        } catch (PDOException $ex) {
            flash('Ya existe un cupón con ese código.', 'error');
        }
    }

    if (($_POST['accion'] ?? '') === 'borrar' && $id > 0) {
        db()->prepare('DELETE FROM cupones WHERE id = ?')->execute([$id]);
        flash('Cupón eliminado.');
    }

    if (($_POST['accion'] ?? '') === 'toggle' && $id > 0) {
        db()->prepare('UPDATE cupones SET activo = 1 - activo WHERE id = ?')->execute([$id]);
    }

    redirect(base_url('admin/cupones.php'));
}

$editar = null;
if (($i = (int)($_GET['editar'] ?? 0)) > 0) {
    $st = db()->prepare('SELECT * FROM cupones WHERE id = ?'); $st->execute([$i]); $editar = $st->fetch() ?: null;
}

$cupones = db()->query('SELECT * FROM cupones ORDER BY activo DESC, id DESC')->fetchAll();

admin_header('Cupones', 'cupones.php', 'Descuentos para campañas y clientes frecuentes');
?>

<div class="grid lg:grid-cols-[380px_1fr] gap-6 items-start">

  <div class="card p-6">
    <h2 class="font-display font-bold text-xl mb-5"><?= $editar ? 'Editar cupón' : 'Nuevo cupón' ?></h2>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="accion" value="guardar">
      <input type="hidden" name="id" value="<?= (int)($editar['id'] ?? 0) ?>">

      <label class="field">
        <span class="label">Código *</span>
        <input type="text" name="codigo" class="input uppercase font-mono" required value="<?= e($editar['codigo'] ?? '') ?>" placeholder="ALMAPAISA">
        <span class="help">Sin espacios ni tildes. El cliente lo escribe en el carrito.</span>
      </label>

      <div class="grid grid-cols-2 gap-x-3">
        <label class="field">
          <span class="label">Tipo</span>
          <select name="tipo" class="select">
            <option value="pct"  <?= ($editar['tipo'] ?? 'pct') === 'pct'  ? 'selected' : '' ?>>Porcentaje</option>
            <option value="fijo" <?= ($editar['tipo'] ?? '')    === 'fijo' ? 'selected' : '' ?>>Valor fijo</option>
          </select>
        </label>
        <label class="field">
          <span class="label">Valor *</span>
          <input type="text" name="valor" class="input tabular" required inputmode="numeric" value="<?= (int)($editar['valor'] ?? 0) ?>" placeholder="10">
          <span class="help">% o pesos según el tipo.</span>
        </label>
      </div>

      <label class="field">
        <span class="label">Compra mínima</span>
        <input type="text" name="min_compra" class="input tabular" inputmode="numeric" value="<?= (int)($editar['min_compra'] ?? 0) ?>" placeholder="60000">
      </label>

      <div class="grid grid-cols-2 gap-x-3">
        <label class="field">
          <span class="label">Usos máximos</span>
          <input type="number" name="usos_max" class="input tabular" value="<?= (int)($editar['usos_max'] ?? 0) ?>" min="0">
          <span class="help">0 = sin límite.</span>
        </label>
        <label class="field">
          <span class="label">Vence</span>
          <input type="date" name="vence" class="input" value="<?= e($editar['vence'] ?? '') ?>">
        </label>
      </div>

      <label class="flex items-center gap-2 text-sm font-semibold mb-5">
        <input type="checkbox" name="activo" class="w-4 h-4 accent-[#B7202A]" <?= ($editar['activo'] ?? 1) ? 'checked' : '' ?>> Cupón activo
      </label>

      <div class="flex gap-2">
        <button class="btn btn-rojo flex-1"><?= $editar ? 'Guardar' : 'Crear cupón' ?></button>
        <?php if ($editar): ?><a href="<?= base_url('admin/cupones.php') ?>" class="btn btn-ghost">Cancelar</a><?php endif; ?>
      </div>
    </form>
  </div>

  <div class="card overflow-hidden">
    <?php if (!$cupones): ?>
      <p class="py-16 text-center text-carbon/50">Aún no hay cupones.</p>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="tbl">
          <thead><tr><th>Código</th><th>Descuento</th><th>Condición</th><th>Usos</th><th>Vence</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($cupones as $c):
              $vencido = $c['vence'] !== '' && $c['vence'] < date('Y-m-d');
              $agotado = (int)$c['usos_max'] > 0 && (int)$c['usos'] >= (int)$c['usos_max']; ?>
              <tr class="<?= $c['activo'] && !$vencido && !$agotado ? '' : 'opacity-55' ?>">
                <td>
                  <span class="font-mono font-bold text-rojo"><?= e($c['codigo']) ?></span>
                  <?php if ($vencido): ?><span class="chip chip-carbon ml-2">vencido</span>
                  <?php elseif ($agotado): ?><span class="chip chip-carbon ml-2">agotado</span>
                  <?php elseif (!$c['activo']): ?><span class="chip chip-carbon ml-2">inactivo</span><?php endif; ?>
                </td>
                <td class="font-bold tabular"><?= $c['tipo'] === 'pct' ? (int)$c['valor'] . '%' : precio_cop($c['valor']) ?></td>
                <td class="text-sm text-carbon/60"><?= (int)$c['min_compra'] > 0 ? 'desde ' . precio_cop($c['min_compra']) : 'sin mínimo' ?></td>
                <td class="text-sm tabular"><?= (int)$c['usos'] ?><?= (int)$c['usos_max'] > 0 ? ' / ' . (int)$c['usos_max'] : '' ?></td>
                <td class="text-sm text-carbon/55"><?= $c['vence'] !== '' ? e(fecha_humana($c['vence'] . ' 00:00:00', false)) : '—' ?></td>
                <td class="text-right whitespace-nowrap">
                  <form method="post" class="inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="toggle">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <button class="btn btn-ghost !h-8 !px-3 !text-[.7rem]"><?= $c['activo'] ? 'Desactivar' : 'Activar' ?></button>
                  </form>
                  <a href="<?= base_url('admin/cupones.php?editar=' . (int)$c['id']) ?>" class="btn btn-ghost !h-8 !px-3 !text-[.7rem]">Editar</a>
                  <form method="post" class="inline" onsubmit="return confirm('¿Eliminar el cupón <?= e($c['codigo']) ?>?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="borrar">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <button class="btn btn-ghost !h-8 !px-3 !text-[.7rem] !text-rojo !border-rojo/30">Borrar</button>
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
