<?php
require_once __DIR__ . '/auth.php';
require_admin();

// Cambio rápido de estado desde la lista
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $id     = (int)($_POST['id'] ?? 0);
    $estado = (string)($_POST['estado'] ?? '');
    if ($id > 0 && isset(estados_pedido()[$estado])) {
        cambiar_estado_pedido($id, $estado, 'Cambio rápido desde la lista');
        flash('Pedido actualizado a «' . estado_label($estado) . '».');
    }
    redirect(base_url('admin/pedidos.php?' . $_SERVER['QUERY_STRING']));
}

// ── Filtros ──────────────────────────────────────────────────────────────────
$fEstado = trim($_GET['estado'] ?? '');
$fTipo   = trim($_GET['tipo'] ?? '');
$fPago   = trim($_GET['pago'] ?? '');
$fDesde  = trim($_GET['desde'] ?? '');
$fHasta  = trim($_GET['hasta'] ?? '');
$buscar  = trim($_GET['q'] ?? '');

$sql  = 'SELECT * FROM pedidos WHERE 1=1';
$args = [];
if ($fEstado !== '') { $sql .= ' AND estado = ?';        $args[] = $fEstado; }
if ($fTipo   !== '') { $sql .= ' AND tipo = ?';          $args[] = $fTipo; }
if ($fPago   !== '') { $sql .= ' AND pago_estado = ?';   $args[] = $fPago; }
if ($fDesde  !== '') { $sql .= ' AND date(creado) >= ?'; $args[] = $fDesde; }
if ($fHasta  !== '') { $sql .= ' AND date(creado) <= ?'; $args[] = $fHasta; }
if ($buscar  !== '') {
    $sql .= ' AND (codigo LIKE ? OR nombre LIKE ? OR telefono LIKE ?)';
    array_push($args, "%$buscar%", "%$buscar%", "%$buscar%");
}
$sql .= ' ORDER BY creado DESC LIMIT 200';

$st = db()->prepare($sql);
$st->execute($args);
$pedidos = $st->fetchAll();

$sumaFiltro = array_sum(array_map(fn($p) => $p['estado'] === 'cancelado' ? 0 : (int)$p['total'], $pedidos));

// ── Exportación a CSV (para contabilidad) ────────────────────────────────────
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="pedidos-albenzu-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");  // BOM para que Excel respete las tildes
    fputcsv($out, ['Código','Fecha','Cliente','Teléfono','Tipo','Barrio','Dirección','Estado','Método de pago','Estado del pago','Subtotal','Descuento','Domicilio','Total'], ';');
    foreach ($pedidos as $p) {
        fputcsv($out, [
            $p['codigo'], $p['creado'], $p['nombre'], $p['telefono'],
            tipo_label($p['tipo']), $p['barrio'], $p['direccion'],
            estado_label($p['estado']), metodo_pago_label($p['metodo_pago']), $p['pago_estado'],
            $p['subtotal'], $p['descuento'], $p['domicilio'], $p['total'],
        ], ';');
    }
    fclose($out);
    exit;
}

// Siguiente estado sugerido según el flujo del pedido
function siguiente_estado(array $p): ?string {
    $flujo = flujo_estados($p['tipo']);
    $i = array_search($p['estado'], $flujo, true);
    return ($i !== false && isset($flujo[$i + 1])) ? $flujo[$i + 1] : null;
}

admin_header('Pedidos', 'pedidos.php', count($pedidos) . ' pedidos · ' . precio_cop($sumaFiltro) . ' facturados en la selección');
?>

<!-- Filtros -->
<form method="get" class="card p-5 mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-6 items-end">
  <label class="block lg:col-span-2">
    <span class="label">Buscar</span>
    <input type="search" name="q" value="<?= e($buscar) ?>" class="input" placeholder="Código, nombre o teléfono">
  </label>
  <label class="block">
    <span class="label">Estado</span>
    <select name="estado" class="select">
      <option value="">Todos</option>
      <?php foreach (estados_pedido() as $k => $v): ?>
        <option value="<?= $k ?>" <?= $fEstado === $k ? 'selected' : '' ?>><?= e($v) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label class="block">
    <span class="label">Tipo</span>
    <select name="tipo" class="select">
      <option value="">Todos</option>
      <option value="domicilio" <?= $fTipo === 'domicilio' ? 'selected' : '' ?>>Domicilio</option>
      <option value="recoger"   <?= $fTipo === 'recoger'   ? 'selected' : '' ?>>Recoger</option>
    </select>
  </label>
  <label class="block">
    <span class="label">Pago</span>
    <select name="pago" class="select">
      <option value="">Todos</option>
      <option value="pendiente" <?= $fPago === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
      <option value="pagado"    <?= $fPago === 'pagado'    ? 'selected' : '' ?>>Pagado</option>
      <option value="fallido"   <?= $fPago === 'fallido'   ? 'selected' : '' ?>>Rechazado</option>
    </select>
  </label>
  <div class="grid grid-cols-2 gap-2">
    <label class="block">
      <span class="label">Desde</span>
      <input type="date" name="desde" value="<?= e($fDesde) ?>" class="input !px-2">
    </label>
    <label class="block">
      <span class="label">Hasta</span>
      <input type="date" name="hasta" value="<?= e($fHasta) ?>" class="input !px-2">
    </label>
  </div>
  <div class="flex gap-2 lg:col-span-6">
    <button class="btn btn-rojo">Filtrar</button>
    <a href="<?= base_url('admin/pedidos.php') ?>" class="btn btn-ghost">Limpiar</a>
    <a href="<?= base_url('admin/pedidos.php?' . http_build_query(array_merge($_GET, ['export' => 'csv']))) ?>" class="btn btn-ghost ml-auto">Exportar CSV</a>
  </div>
</form>

<!-- Accesos rápidos -->
<div class="flex gap-2 overflow-x-auto pb-1 mb-5 scrollbar-none">
  <?php
  $rapidos = [
      ''            => 'Todos',
      'recibido'    => 'Recibidos',
      'confirmado'  => 'Confirmados',
      'preparacion' => 'En cocina',
      'camino'      => 'En camino',
      'listo'       => 'Listos',
      'entregado'   => 'Entregados',
  ];
  foreach ($rapidos as $k => $lbl): ?>
    <a href="<?= base_url('admin/pedidos.php' . ($k ? '?estado=' . $k : '')) ?>"
       class="cat-pill <?= $fEstado === $k && !$buscar ? 'is-active' : '' ?>"><?= e($lbl) ?></a>
  <?php endforeach; ?>
</div>

<div class="card overflow-hidden">
  <?php if (!$pedidos): ?>
    <p class="py-16 text-center text-carbon/50">No hay pedidos con esos filtros.</p>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead>
          <tr><th>Código</th><th>Cliente</th><th>Entrega</th><th>Estado</th><th>Pago</th><th class="text-right">Total</th><th class="text-right">Acciones</th></tr>
        </thead>
        <tbody>
          <?php foreach ($pedidos as $p):
            $sig = siguiente_estado($p); ?>
            <tr>
              <td>
                <a href="<?= base_url('admin/pedido.php?id=' . (int)$p['id']) ?>" class="font-mono font-bold text-rojo hover:underline"><?= e($p['codigo']) ?></a>
                <span class="block text-[.7rem] text-carbon/40"><?= e(fecha_humana($p['creado'])) ?></span>
              </td>
              <td>
                <span class="font-semibold"><?= e($p['nombre']) ?></span>
                <span class="block text-[.7rem] text-carbon/45"><?= e(tel_humano($p['telefono'])) ?></span>
              </td>
              <td>
                <span class="chip <?= $p['tipo'] === 'domicilio' ? 'chip-rojo' : 'chip-maiz' ?>"><?= e(tipo_label($p['tipo'])) ?></span>
                <?php if ($p['tipo'] === 'domicilio'): ?>
                  <span class="block text-[.7rem] text-carbon/45 mt-1 max-w-[16rem] truncate"><?= e($p['barrio'] . ' · ' . $p['direccion']) ?></span>
                <?php endif; ?>
              </td>
              <td><?= badge_estado($p['estado']) ?></td>
              <td>
                <?= badge_pago($p['pago_estado']) ?>
                <span class="block text-[.7rem] text-carbon/45 mt-1"><?= e(metodo_pago_label($p['metodo_pago'])) ?></span>
              </td>
              <td class="text-right font-bold tabular"><?= precio_cop($p['total']) ?></td>
              <td class="text-right whitespace-nowrap">
                <?php if ($sig): ?>
                  <form method="post" class="inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <input type="hidden" name="estado" value="<?= $sig ?>">
                    <button class="btn btn-rojo !h-9 !px-3 !text-[.72rem]">→ <?= e(estado_label($sig)) ?></button>
                  </form>
                <?php endif; ?>
                <a href="<?= base_url('admin/pedido.php?id=' . (int)$p['id']) ?>" class="btn btn-ghost !h-9 !px-3 !text-[.72rem]">Ver</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>
