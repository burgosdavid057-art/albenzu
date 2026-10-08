<?php
require_once __DIR__ . '/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $id = (int)($_POST['id'] ?? 0);

    if (($_POST['accion'] ?? '') === 'guardar') {
        $tel = preg_replace('/\D+/', '', (string)($_POST['telefono'] ?? ''));
        $vals = [
            trim((string)($_POST['nombre'] ?? '')), $tel,
            trim((string)($_POST['email'] ?? '')),
            trim((string)($_POST['direccion'] ?? '')),
            trim((string)($_POST['barrio'] ?? '')),
            trim((string)($_POST['notas'] ?? '')),
        ];
        try {
            if ($id > 0) {
                db()->prepare('UPDATE clientes SET nombre=?, telefono=?, email=?, direccion=?, barrio=?, notas=? WHERE id=?')
                    ->execute([...$vals, $id]);
                flash('Ficha del cliente actualizada.');
            } else {
                db()->prepare('INSERT INTO clientes (nombre, telefono, email, direccion, barrio, notas, creado) VALUES (?,?,?,?,?,?,?)')
                    ->execute([...$vals, date('Y-m-d H:i:s')]);
                $id = (int)db()->lastInsertId();
                flash('Cliente agregado.');
            }
        } catch (PDOException $ex) {
            flash('Ya existe un cliente con ese teléfono.', 'error');
        }
        redirect(base_url('admin/clientes.php?id=' . $id));
    }

    if (($_POST['accion'] ?? '') === 'borrar' && $id > 0) {
        db()->prepare('DELETE FROM clientes WHERE id = ?')->execute([$id]);
        flash('Cliente eliminado. Sus pedidos se conservan sin ficha asociada.');
        redirect(base_url('admin/clientes.php'));
    }
}

// ── Ficha individual ─────────────────────────────────────────────────────────
$verId  = (int)($_GET['id'] ?? 0);
$nuevo  = isset($_GET['nuevo']);
$cliente = null;
if ($verId > 0) {
    $st = db()->prepare('SELECT * FROM clientes WHERE id = ?');
    $st->execute([$verId]);
    $cliente = $st->fetch() ?: null;
}

if ($cliente) {
    $st = db()->prepare('SELECT * FROM pedidos WHERE cliente_id = ? ORDER BY creado DESC');
    $st->execute([$verId]);
    $pedidosCli = $st->fetchAll();

    $validos = array_filter($pedidosCli, fn($p) => $p['estado'] !== 'cancelado');
    $gastado = array_sum(array_column($validos, 'total'));
    $ticket  = $validos ? (int)round($gastado / count($validos)) : 0;

    // Platos favoritos del cliente
    $st = db()->prepare(
        "SELECT i.nombre, SUM(i.cantidad) n FROM pedido_items i
         JOIN pedidos p ON p.id = i.pedido_id
         WHERE p.cliente_id = ? AND p.estado != 'cancelado'
         GROUP BY i.nombre ORDER BY n DESC LIMIT 5"
    );
    $st->execute([$verId]);
    $favoritos = $st->fetchAll();

    admin_header($cliente['nombre'], 'clientes.php', 'Ficha del cliente · ' . tel_humano($cliente['telefono']));
    ?>
    <a href="<?= base_url('admin/clientes.php') ?>" class="btn btn-ghost !h-10 mb-5">← Todos los clientes</a>

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
      <?php
      kpi('Pedidos',         (string)count($validos), 'sin contar cancelados', 'carbon');
      kpi('Total gastado',   precio_cop($gastado),    'histórico', 'rojo');
      kpi('Ticket promedio', precio_cop($ticket),     'por pedido', 'maiz');
      kpi('Cliente desde',   fecha_humana($cliente['creado'], false), '', 'hoja');
      ?>
    </div>

    <div class="grid lg:grid-cols-[1fr_360px] gap-5 items-start">
      <div class="card overflow-hidden">
        <h2 class="font-display font-bold text-xl px-6 py-5 border-b border-carbon/8">Historial de pedidos</h2>
        <?php if (!$pedidosCli): ?>
          <p class="py-12 text-center text-carbon/50">Todavía no ha hecho pedidos.</p>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="tbl">
              <thead><tr><th>Código</th><th>Fecha</th><th>Tipo</th><th>Estado</th><th class="text-right">Total</th></tr></thead>
              <tbody>
                <?php foreach ($pedidosCli as $p): ?>
                  <tr>
                    <td><a href="<?= base_url('admin/pedido.php?id=' . (int)$p['id']) ?>" class="font-mono font-bold text-rojo hover:underline"><?= e($p['codigo']) ?></a></td>
                    <td class="text-sm text-carbon/60"><?= e(fecha_humana($p['creado'])) ?></td>
                    <td><span class="chip <?= $p['tipo'] === 'domicilio' ? 'chip-rojo' : 'chip-maiz' ?>"><?= e(tipo_label($p['tipo'])) ?></span></td>
                    <td><?= badge_estado($p['estado']) ?></td>
                    <td class="text-right font-bold tabular"><?= precio_cop($p['total']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <aside class="space-y-5">
        <?php if ($favoritos): ?>
          <div class="card p-6">
            <h2 class="font-display font-bold text-lg mb-4">Lo que siempre pide</h2>
            <ul class="space-y-2 text-sm">
              <?php foreach ($favoritos as $f): ?>
                <li class="flex justify-between gap-3">
                  <span class="truncate"><?= e($f['nombre']) ?></span>
                  <span class="tabular font-bold text-carbon/50 shrink-0">×<?= (int)$f['n'] ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <div class="card p-6">
          <h2 class="font-display font-bold text-lg mb-5">Datos de contacto</h2>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="accion" value="guardar">
            <input type="hidden" name="id" value="<?= (int)$cliente['id'] ?>">
            <label class="field"><span class="label">Nombre</span><input type="text" name="nombre" class="input" value="<?= e($cliente['nombre']) ?>"></label>
            <label class="field"><span class="label">Teléfono</span><input type="text" name="telefono" class="input" value="<?= e($cliente['telefono']) ?>"></label>
            <label class="field"><span class="label">Correo</span><input type="email" name="email" class="input" value="<?= e($cliente['email']) ?>"></label>
            <label class="field"><span class="label">Dirección</span><input type="text" name="direccion" class="input" value="<?= e($cliente['direccion']) ?>"></label>
            <label class="field"><span class="label">Barrio</span><input type="text" name="barrio" class="input" value="<?= e($cliente['barrio']) ?>"></label>
            <label class="field"><span class="label">Notas internas</span><textarea name="notas" class="textarea !min-h-[5rem]" placeholder="Alergias, preferencias, si es cliente de oficina…"><?= e($cliente['notas']) ?></textarea></label>
            <button class="btn btn-rojo w-full">Guardar ficha</button>
          </form>
          <form method="post" class="mt-3" onsubmit="return confirm('¿Eliminar la ficha de este cliente?')">
            <?= csrf_field() ?>
            <input type="hidden" name="accion" value="borrar">
            <input type="hidden" name="id" value="<?= (int)$cliente['id'] ?>">
            <button class="w-full text-sm font-semibold text-carbon/40 hover:text-rojo transition-colors py-2">Eliminar cliente</button>
          </form>
        </div>
      </aside>
    </div>
    <?php
    admin_footer();
    exit;
}

// ── Listado ──────────────────────────────────────────────────────────────────
$buscar = trim($_GET['q'] ?? '');
$seg    = trim($_GET['seg'] ?? '');

$sql = "SELECT c.*,
               (SELECT COUNT(*) FROM pedidos p WHERE p.cliente_id = c.id AND p.estado != 'cancelado') AS pedidos,
               (SELECT COALESCE(SUM(total),0) FROM pedidos p WHERE p.cliente_id = c.id AND p.estado != 'cancelado') AS gastado,
               (SELECT MAX(creado) FROM pedidos p WHERE p.cliente_id = c.id) AS ultimo
        FROM clientes c WHERE 1=1";
$args = [];
if ($buscar !== '') {
    $sql .= ' AND (c.nombre LIKE ? OR c.telefono LIKE ? OR c.email LIKE ? OR c.barrio LIKE ?)';
    array_push($args, "%$buscar%", "%$buscar%", "%$buscar%", "%$buscar%");
}
$sql .= ' ORDER BY gastado DESC, c.nombre ASC';

$st = db()->prepare($sql);
$st->execute($args);
$clientes = $st->fetchAll();

// Segmentación simple
$hace30 = date('Y-m-d H:i:s', strtotime('-30 days'));
$segmento = function (array $c) use ($hace30): array {
    if ((int)$c['pedidos'] === 0)                    return ['sin pedidos', 'chip-carbon'];
    if ((int)$c['pedidos'] >= 3)                     return ['recurrente', 'chip-rojo'];
    if ($c['ultimo'] && $c['ultimo'] < $hace30)      return ['dormido', 'chip-maiz'];
    return ['nuevo', 'chip-hoja'];
};
if ($seg !== '') {
    $clientes = array_values(array_filter($clientes, fn($c) => $segmento($c)[0] === $seg));
}

$totalGastado = array_sum(array_column($clientes, 'gastado'));

admin_header('Clientes', 'clientes.php', count($clientes) . ' clientes · ' . precio_cop($totalGastado) . ' facturados');
?>

<div class="flex flex-wrap gap-3 items-end mb-5">
  <form method="get" class="flex gap-3 items-end flex-1">
    <label class="block flex-1 max-w-sm">
      <span class="label">Buscar</span>
      <input type="search" name="q" value="<?= e($buscar) ?>" class="input" placeholder="Nombre, teléfono, barrio…">
    </label>
    <button class="btn btn-ghost">Buscar</button>
  </form>
  <a href="<?= base_url('admin/clientes.php?nuevo=1') ?>" class="btn btn-rojo">+ Nuevo cliente</a>
</div>

<div class="flex gap-2 overflow-x-auto pb-1 mb-5 scrollbar-none">
  <?php foreach (['' => 'Todos', 'recurrente' => 'Recurrentes', 'nuevo' => 'Nuevos', 'dormido' => 'Dormidos (+30 días)', 'sin pedidos' => 'Sin pedidos'] as $k => $lbl): ?>
    <a href="<?= base_url('admin/clientes.php' . ($k ? '?seg=' . urlencode($k) : '')) ?>" class="cat-pill <?= $seg === $k ? 'is-active' : '' ?>"><?= e($lbl) ?></a>
  <?php endforeach; ?>
</div>

<?php if ($nuevo): ?>
  <div class="card p-6 mb-6">
    <h2 class="font-display font-bold text-xl mb-5">Nuevo cliente</h2>
    <form method="post" class="grid sm:grid-cols-3 gap-x-4">
      <?= csrf_field() ?>
      <input type="hidden" name="accion" value="guardar">
      <input type="hidden" name="id" value="0">
      <label class="field"><span class="label">Nombre *</span><input type="text" name="nombre" class="input" required></label>
      <label class="field"><span class="label">Teléfono *</span><input type="text" name="telefono" class="input" required></label>
      <label class="field"><span class="label">Correo</span><input type="email" name="email" class="input"></label>
      <label class="field sm:col-span-2"><span class="label">Dirección</span><input type="text" name="direccion" class="input"></label>
      <label class="field"><span class="label">Barrio</span><input type="text" name="barrio" class="input"></label>
      <label class="field sm:col-span-3"><span class="label">Notas internas</span><input type="text" name="notas" class="input"></label>
      <div class="sm:col-span-3 flex gap-2">
        <button class="btn btn-rojo">Guardar cliente</button>
        <a href="<?= base_url('admin/clientes.php') ?>" class="btn btn-ghost">Cancelar</a>
      </div>
    </form>
  </div>
<?php endif; ?>

<div class="card overflow-hidden">
  <?php if (!$clientes): ?>
    <p class="py-16 text-center text-carbon/50">No hay clientes con esos criterios.</p>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead><tr><th>Cliente</th><th>Zona</th><th>Segmento</th><th class="text-center">Pedidos</th><th class="text-right">Gastado</th><th>Último pedido</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($clientes as $c):
            [$segLbl, $segCls] = $segmento($c); ?>
            <tr>
              <td>
                <a href="<?= base_url('admin/clientes.php?id=' . (int)$c['id']) ?>" class="font-semibold hover:text-rojo transition-colors"><?= e($c['nombre']) ?></a>
                <span class="block text-[.72rem] text-carbon/45"><?= e(tel_humano($c['telefono'])) ?><?= $c['email'] ? ' · ' . e($c['email']) : '' ?></span>
              </td>
              <td class="text-sm text-carbon/60"><?= e($c['barrio'] ?: '—') ?></td>
              <td><span class="chip <?= $segCls ?>"><?= e($segLbl) ?></span></td>
              <td class="text-center tabular font-semibold"><?= (int)$c['pedidos'] ?></td>
              <td class="text-right font-bold tabular"><?= precio_cop($c['gastado']) ?></td>
              <td class="text-sm text-carbon/55"><?= $c['ultimo'] ? e(fecha_humana($c['ultimo'], false)) : '—' ?></td>
              <td class="text-right"><a href="<?= base_url('admin/clientes.php?id=' . (int)$c['id']) ?>" class="btn btn-ghost !h-9 !px-3 !text-[.72rem]">Ver ficha</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>
