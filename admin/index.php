<?php
require_once __DIR__ . '/auth.php';
require_admin();

$hoy = date('Y-m-d');

/** Atajo: primera fila de una consulta */
function fila(string $sql, array $args = []): array {
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st->fetch() ?: [];
}
$q = 'fila';

$ventasHoy   = (int)$q("SELECT COALESCE(SUM(total),0) v FROM pedidos WHERE date(creado) = ? AND estado != 'cancelado'", [$hoy])['v'];
$pedidosHoy  = (int)$q("SELECT COUNT(*) c FROM pedidos WHERE date(creado) = ?", [$hoy])['c'];
$abiertos    = pedidos_abiertos();
$ticket      = (int)$q("SELECT COALESCE(AVG(total),0) t FROM pedidos WHERE estado != 'cancelado'")['t'];
$ventasMes   = (int)$q("SELECT COALESCE(SUM(total),0) v FROM pedidos WHERE strftime('%Y-%m', creado) = ? AND estado != 'cancelado'", [date('Y-m')])['v'];
$porCobrar   = (int)$q("SELECT COALESCE(SUM(total),0) v FROM pedidos WHERE pago_estado = 'pendiente' AND estado != 'cancelado'")['v'];
$totalCli    = (int)$q("SELECT COUNT(*) c FROM clientes")['c'];

// Serie de los últimos 7 días
$serie = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $serie[] = [
        'dia'   => ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'][(int)date('w', strtotime($d))],
        'fecha' => $d,
        'valor' => (int)$q("SELECT COALESCE(SUM(total),0) v FROM pedidos WHERE date(creado) = ? AND estado != 'cancelado'", [$d])['v'],
    ];
}
$maxSerie = max(1, max(array_column($serie, 'valor')));

// Pedidos que necesitan atención
$stPend = db()->query("SELECT * FROM pedidos WHERE estado NOT IN ('entregado','cancelado') ORDER BY creado DESC LIMIT 8");
$pendientes = $stPend->fetchAll();

// Platos más vendidos
$top = db()->query(
    "SELECT i.nombre, SUM(i.cantidad) unidades, SUM(i.subtotal) ingreso
     FROM pedido_items i JOIN pedidos p ON p.id = i.pedido_id
     WHERE p.estado != 'cancelado'
     GROUP BY i.nombre ORDER BY unidades DESC LIMIT 6"
)->fetchAll();

// Reparto domicilio / recoger
$reparto = db()->query("SELECT tipo, COUNT(*) c FROM pedidos WHERE estado != 'cancelado' GROUP BY tipo")->fetchAll();
$totalRep = max(1, array_sum(array_column($reparto, 'c')));

admin_header('Resumen', 'index.php', 'Cómo va el restaurante hoy · ' . fecha_humana(date('Y-m-d H:i:s'), false));
?>

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
  <?php
  kpi('Ventas de hoy',    precio_cop($ventasHoy),  $pedidosHoy . ' ' . ($pedidosHoy === 1 ? 'pedido' : 'pedidos'), 'rojo');
  kpi('En curso',         (string)$abiertos,       'pedidos sin entregar', 'maiz');
  kpi('Ticket promedio',  precio_cop($ticket),     'sobre todos los pedidos', 'carbon');
  kpi('Ventas del mes',   precio_cop($ventasMes),  date('F Y'), 'hoja');
  ?>
</div>

<div class="grid lg:grid-cols-[1.5fr_1fr] gap-5 mt-5">

  <!-- Ventas de la semana -->
  <div class="card p-6">
    <div class="flex items-baseline justify-between mb-6">
      <h2 class="font-display font-bold text-xl">Últimos 7 días</h2>
      <span class="text-sm text-carbon/50 tabular"><?= precio_cop(array_sum(array_column($serie, 'valor'))) ?> en total</span>
    </div>
    <div class="flex items-end gap-2 sm:gap-3 h-52">
      <?php foreach ($serie as $s):
        $h = max(3, (int)round($s['valor'] / $maxSerie * 100)); ?>
        <div class="flex-1 flex flex-col items-center gap-2 group">
          <span class="text-[.65rem] font-bold text-carbon/50 tabular opacity-0 group-hover:opacity-100 transition-opacity"><?= $s['valor'] > 0 ? precio_cop($s['valor']) : '—' ?></span>
          <div class="w-full rounded-t-lg bg-gradient-to-t from-rojo-deep to-rojo transition-all duration-300 group-hover:from-rojo group-hover:to-maiz"
               style="height:<?= $h ?>%" title="<?= e($s['fecha']) ?>: <?= precio_cop($s['valor']) ?>"></div>
          <span class="text-[.7rem] font-semibold text-carbon/45"><?= $s['dia'] ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Estado del negocio -->
  <div class="space-y-5">
    <div class="card p-6">
      <h2 class="font-display font-bold text-xl mb-5">Cómo piden</h2>
      <?php foreach ($reparto as $r):
        $pct = round((int)$r['c'] / $totalRep * 100); ?>
        <div class="mb-4 last:mb-0">
          <div class="flex justify-between text-sm mb-1.5">
            <span class="font-semibold"><?= e(tipo_label($r['tipo'])) ?></span>
            <span class="text-carbon/50 tabular"><?= $pct ?>% · <?= (int)$r['c'] ?></span>
          </div>
          <div class="h-2.5 rounded-full bg-carbon/8 overflow-hidden">
            <div class="h-full rounded-full <?= $r['tipo'] === 'domicilio' ? 'bg-rojo' : 'bg-maiz' ?>" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="card p-6">
      <h2 class="font-display font-bold text-xl mb-4">Pendiente de cobro</h2>
      <p class="font-display font-black text-3xl text-rojo tabular"><?= precio_cop($porCobrar) ?></p>
      <p class="text-sm text-carbon/50 mt-1">en pedidos sin pago registrado</p>
      <div class="flex items-center gap-2 mt-5 pt-5 border-t border-carbon/8 text-sm">
        <span class="text-carbon/50">Clientes en la base</span>
        <span class="ml-auto font-display font-black text-xl tabular"><?= $totalCli ?></span>
      </div>
    </div>
  </div>
</div>

<!-- Pedidos en curso -->
<div class="card mt-5 overflow-hidden">
  <div class="flex items-center justify-between px-6 py-5 border-b border-carbon/8">
    <h2 class="font-display font-bold text-xl">Pedidos en curso</h2>
    <a href="<?= base_url('admin/pedidos.php') ?>" class="text-sm font-semibold text-rojo hover:underline">Ver todos →</a>
  </div>

  <?php if (!$pendientes): ?>
    <p class="px-6 py-12 text-center text-carbon/50">Nada en cola. Cocina al día.</p>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="tbl">
        <thead><tr><th>Código</th><th>Cliente</th><th>Tipo</th><th>Estado</th><th>Pago</th><th class="text-right">Total</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($pendientes as $p): ?>
            <tr>
              <td><a href="<?= base_url('admin/pedido.php?id=' . (int)$p['id']) ?>" class="font-mono font-bold text-rojo hover:underline"><?= e($p['codigo']) ?></a>
                  <span class="block text-[.7rem] text-carbon/40"><?= e(fecha_humana($p['creado'])) ?></span></td>
              <td><span class="font-semibold"><?= e($p['nombre']) ?></span>
                  <span class="block text-[.7rem] text-carbon/45"><?= e(tel_humano($p['telefono'])) ?></span></td>
              <td><span class="chip <?= $p['tipo'] === 'domicilio' ? 'chip-rojo' : 'chip-maiz' ?>"><?= e(tipo_label($p['tipo'])) ?></span></td>
              <td><?= badge_estado($p['estado']) ?></td>
              <td><?= badge_pago($p['pago_estado']) ?></td>
              <td class="text-right font-bold tabular"><?= precio_cop($p['total']) ?></td>
              <td class="text-right"><a href="<?= base_url('admin/pedido.php?id=' . (int)$p['id']) ?>" class="btn btn-ghost !h-9 !px-4 !text-xs">Gestionar</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Top platos -->
<div class="card mt-5 p-6">
  <h2 class="font-display font-bold text-xl mb-5">Lo que más se vende</h2>
  <?php if (!$top): ?>
    <p class="text-carbon/50 text-sm">Todavía no hay ventas registradas.</p>
  <?php else:
    $maxTop = max(1, (int)$top[0]['unidades']); ?>
    <div class="space-y-3.5">
      <?php foreach ($top as $i => $t): ?>
        <div class="flex items-center gap-4">
          <span class="w-6 text-center font-display font-black text-carbon/25"><?= $i + 1 ?></span>
          <div class="flex-1 min-w-0">
            <div class="flex justify-between text-sm mb-1">
              <span class="font-semibold truncate"><?= e($t['nombre']) ?></span>
              <span class="text-carbon/50 tabular shrink-0 ml-3"><?= (int)$t['unidades'] ?> uds · <?= precio_cop($t['ingreso']) ?></span>
            </div>
            <div class="h-2 rounded-full bg-carbon/8 overflow-hidden">
              <div class="h-full rounded-full bg-rojo" style="width:<?= round((int)$t['unidades'] / $maxTop * 100) ?>%"></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>
