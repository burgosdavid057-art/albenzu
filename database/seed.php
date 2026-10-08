<?php
/**
 * Carga inicial de la base de datos.
 *
 * Ejecutar:  php database/seed.php
 * (borra y recrea todas las tablas)
 *
 * La carta y los precios son los REALES publicados por el restaurante en su
 * canal de domicilios. Las descripciones son redacción propia para la demo:
 * el cliente las ajusta desde el panel.
 */
// Sólo por consola: nunca por HTTP (borraría la base en producción).
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script sólo se ejecuta por consola.');
}

require_once __DIR__ . '/../includes/functions.php';

$pdo = db();
$pdo->exec(file_get_contents(__DIR__ . '/schema.sql'));

$ahora = date('Y-m-d H:i:s');

// ── Categorías ───────────────────────────────────────────────────────────────
$categorias = [
    ['Platos típicos',      'Lo que nos hizo famosos: bandejas, cazuelas y frijoles con alma.', '🫘', 1],
    ['Asados a la parrilla','Carnes al carbón con el punto que usted pida.',                    '🔥', 2],
    ['Arepas Re-Paisas',    'Nuestra arepa de maíz, cargada como sólo aquí la cargan.',         '🌽', 3],
    ['Combos para compartir','Para la mesa completa, sin pelear por el último chicharrón.',     '🍽️', 4],
    ['Pa\' picar',          'Mientras llega el plato fuerte.',                                  '🥟', 5],
    ['Antojitos',           'Hamburguesas, sánduches y salchipapa con sello paisa.',            '🍔', 6],
    ['Delicias de la casa', 'Los caprichos del chef que ya tienen clientela fija.',             '⭐', 7],
    ['Desayunos',           'Desde las 11, porque aquí el calenta\'o no tiene hora.',           '🍳', 8],
    ['Menú infantil',       'Porciones para los chiquitos de la casa.',                         '🧒', 9],
    ['Bebidas',             'Jugos naturales, gaseosas y aguas.',                               '🥤', 10],
];
$stCat = $pdo->prepare('INSERT INTO categorias (nombre, slug, descripcion, emoji, orden) VALUES (?,?,?,?,?)');
$catId = [];
foreach ($categorias as $c) {
    $stCat->execute([$c[0], slugify($c[0]), $c[1], $c[2], $c[3]]);
    $catId[$c[0]] = (int)$pdo->lastInsertId();
}

// ── Productos [categoría, nombre, precio, descripción, etiquetas, destacado] ──
$productos = [
    // Platos típicos
    ['Platos típicos', 'Bandeja Mis Montañas', 65900, 'La bandeja grande de la casa: frijoles, arroz, chicharrón, chorizo, morcilla, carne en polvo, huevo, tajadas, aguacate y arepa. Para el hambre en serio.', ['insignia'], 1],
    ['Platos típicos', 'La Creída', 62900, 'Bandeja montada con corte de res a la parrilla sobre el clásico paisa. Se llama así por algo.', [], 1],
    ['Platos típicos', 'La Chismosa', 52900, 'Frijoles, chicharrón, chorizo y todo lo que se cuenta en la mesa. Plato para dos que se pide para uno.', ['favorito'], 1],
    ['Platos típicos', 'Bandeja Paisano', 48100, 'La bandeja paisa de toda la vida, sin adornos y sin recortes.', ['clásico'], 1],
    ['Platos típicos', 'Fiambre La Mula 3 Carnes', 46000, 'Servido en hoja de plátano con tres carnes, papa, yuca y hoga\'o. Como lo mandaban al monte.', ['tradición'], 0],
    ['Platos típicos', 'Bandeja Montañitas', 36000, 'Media porción de la bandeja grande. Todo el sabor, menos el arrepentimiento.', [], 0],
    ['Platos típicos', 'La Huerta Encebollada', 33900, 'Carne encebollada con arroz, tajadas, ensalada y arepa.', [], 0],
    ['Platos típicos', 'Cazuela Botero', 32900, 'Cazuela de frijoles con chicharrón, chorizo y aguacate. Nuestro frijol, en su mejor formato.', ['favorito'], 1],
    ['Platos típicos', 'Medio Típico', 32500, 'Frijoles, arroz, chicharrón, huevo, tajada y arepa. El almuerzo de cajón.', [], 0],
    ['Platos típicos', 'Sopa de la abuela', 28900, 'Sancocho de la casa. Sólo fines de semana y festivos.', ['fin de semana'], 0],

    // Asados a la parrilla
    ['Asados a la parrilla', 'Arca', 54500, 'Mixta de res, cerdo y pollo a la parrilla con papa, ensalada y arepa. Para el que no se decide.', ['para compartir'], 1],
    ['Asados a la parrilla', 'Punta Cielo 200 gr', 44900, 'Punta de anca de 200 gr al carbón, con papa criolla y ensalada.', [], 0],
    ['Asados a la parrilla', 'Paso Fino 200 gr', 43900, 'Lomo de res a la parrilla, término a su gusto, con acompañamientos.', [], 0],
    ['Asados a la parrilla', 'Mi Tierrita Cerdo', 39900, 'Chuleta de cerdo a la parrilla con chimichurri de la casa.', [], 0],
    ['Asados a la parrilla', 'Chanchito', 36900, 'Cerdo asado con hoga\'o, papa y ensalada.', [], 0],
    ['Asados a la parrilla', 'Ave María', 35000, 'Pechuga de pollo a la parrilla con guarnición. Nombre paisa, porción paisa.', [], 0],

    // Arepas Re-Paisas
    ['Arepas Re-Paisas', 'Súper Antioqueña Re-Paisa', 40900, 'Arepa de maíz con frijoles, chicharrón, chorizo, salchicha, tocineta, aguacate y queso mozzarella. La más pedida del Instagram.', ['insignia','favorito'], 1],
    ['Arepas Re-Paisas', 'Despeinada Re-Paisa', 34900, 'Arepa desbordada de carnes, queso fundido y hoga\'o. No se come con las manos.', [], 1],
    ['Arepas Re-Paisas', 'Sanjuanera Re-Paisa', 33900, 'Arepa con carne desmechada, queso, maduro y hoga\'o.', [], 0],
    ['Arepas Re-Paisas', 'Arepa La Linda', 23900, 'Arepa con pollo, champiñones y queso.', [], 0],
    ['Arepas Re-Paisas', 'Arepa Jardinera', 23500, 'Arepa con verduras salteadas y queso. La opción liviana de la casa.', ['vegetariana'], 0],
    ['Arepas Re-Paisas', 'Arepa Pocineta', 20900, 'Arepa con tocineta crocante y queso mozzarella.', [], 0],

    // Combos
    ['Combos para compartir', 'Combo 2 Medio Típico', 65000, 'Dos medios típicos completos. El plan de dos por menos.', ['ahorro'], 1],

    // Pa' picar
    ['Pa\' picar', 'Picada Capricho', 35900, 'Chicharrón, chorizo, morcilla, carne, papa criolla y maduro. Para picar de a varios.', ['para compartir'], 1],
    ['Pa\' picar', 'Empanadas de Iglesia', 16900, 'Seis empanadas de carne y papa con ají de la casa.', [], 0],
    ['Pa\' picar', 'Pataconcitos', 12900, 'Patacones con hoga\'o y queso rallado.', [], 0],

    // Antojitos
    ['Antojitos', 'Hamburguesa Artesana', 27900, 'Carne artesanal de 180 gr, queso, tocineta y salsas de la casa con papas.', [], 0],
    ['Antojitos', 'Sánduche Sanjuanero', 27900, 'Pan artesanal con carne desmechada, queso fundido y hoga\'o.', [], 0],
    ['Antojitos', 'Hamburguesa Enchicharronada', 25900, 'Hamburguesa coronada con chicharrón crocante. Bien paisa.', ['favorito'], 1],
    ['Antojitos', 'Salchipapa', 23900, 'Papa a la francesa con salchicha, queso y salsas.', [], 0],

    // Delicias de la casa
    ['Delicias de la casa', 'Maderita Churrasco de Res', 48000, 'Churrasco servido en tabla con papa criolla, chimichurri y ensalada.', [], 1],
    ['Delicias de la casa', 'Papaisas Chicharronadas', 32000, 'Papas de la casa cubiertas de chicharrón, queso y hoga\'o.', ['favorito'], 1],
    ['Delicias de la casa', 'Artesana', 22900, 'Nuestra receta artesanal del día, servida como en casa.', [], 0],

    // Desayunos
    ['Desayunos', 'Calenta\'o Paisa', 30000, 'Arroz con frijol calentado, huevo, chicharrón, arepa y chocolate. El desayuno que levanta muertos.', ['clásico'], 1],
    ['Desayunos', 'Huevos Revuelticos', 16000, 'Huevos revueltos con hoga\'o, arepa y bebida caliente.', [], 0],
    ['Desayunos', 'Arepa de chócolo', 13900, 'Arepa de chócolo con quesito antioqueño.', ['favorito'], 0],
    ['Desayunos', 'Arepa con acompañante', 8500, 'Arepa asada con quesito o hoga\'o.', [], 0],

    // Menú infantil
    ['Menú infantil', 'Paisita Infantil', 25900, 'Porción pequeña de bandeja paisa para los chiquitos.', [], 0],
    ['Menú infantil', 'Deditos', 23900, 'Deditos de pollo apanados con papas a la francesa.', [], 0],

    // Bebidas
    ['Bebidas', 'Jugo natural en leche 16 oz', 10900, 'Mora, maracuyá, guanábana o mango.', [], 0],
    ['Bebidas', 'Jugo natural en agua 16 oz', 9900, 'Mora, maracuyá, guanábana o mango.', [], 0],
    ['Bebidas', 'Soda Hatsu', 8000, 'Soda artesanal saborizada.', [], 0],
    ['Bebidas', 'Agua Brisa saborizada 600 ml', 6500, 'Agua con sabor, bien fría.', [], 0],
    ['Bebidas', 'Mr. Tea 500 ml', 6500, 'Té frío de limón o durazno.', [], 0],
    ['Bebidas', 'Bretaña 300 ml', 6500, 'Gaseosa Bretaña en botella.', [], 0],
];

$stProd = $pdo->prepare(
    'INSERT INTO productos (categoria_id, nombre, slug, descripcion, precio, etiquetas, destacado, orden)
     VALUES (?,?,?,?,?,?,?,?)'
);
$orden = [];
foreach ($productos as $p) {
    [$cat, $nombre, $precio, $desc, $tags, $dest] = $p;
    $orden[$cat] = ($orden[$cat] ?? 0) + 1;
    $stProd->execute([
        $catId[$cat], $nombre, slugify($nombre), $desc, $precio,
        json_encode($tags, JSON_UNESCAPED_UNICODE), $dest, $orden[$cat],
    ]);
}

// ── Adiciones (precios reales de la carta) ───────────────────────────────────
$adiciones = [
    ['Porción de chicharrón paisa', 15900],
    ['Porción de frijol tradicional', 11900],
    ['Unidad de chorizo', 7900],
    ['Unidad de morcilla', 5500],
    ['Adición de aguacate', 4900],
    ['Porción de arroz', 4900],
    ['Porción de ensalada', 3500],
    ['Adición de hoga\'o', 3500],
];
$stAd = $pdo->prepare('INSERT INTO adiciones (nombre, precio, orden) VALUES (?,?,?)');
foreach ($adiciones as $i => $a) {
    $stAd->execute([$a[0], $a[1], $i + 1]);
}

// ── Cupones de ejemplo ───────────────────────────────────────────────────────
$stCup = $pdo->prepare('INSERT INTO cupones (codigo, tipo, valor, min_compra, usos_max, vence) VALUES (?,?,?,?,?,?)');
$stCup->execute(['ALMAPAISA', 'pct',  10, 60000, 0,  '']);
$stCup->execute(['DESDE1982', 'fijo', 8000, 45000, 100, date('Y-m-d', strtotime('+3 months'))]);
$stCup->execute(['DOMIGRATIS','fijo', 6000, 80000, 50, date('Y-m-d', strtotime('+1 month'))]);

// ── Ajustes operativos ───────────────────────────────────────────────────────
$ajustes = [
    'domicilio_valor'   => '6000',
    'domicilio_gratis'  => '90000',   // pedido desde el cual el domicilio va gratis
    'pedido_minimo'     => '25000',
    'tiempo_domicilio'  => '35 – 50 min',
    'tiempo_recoger'    => '20 – 25 min',
    'abierto'           => '1',
    'hora_apertura'     => '11:00',
    'hora_cierre'       => '22:00',
    'aviso'             => 'Domicilios en Belén, Laureles, La América y alrededores de la 80.',
    'pago_tarjeta'      => '1',
    'pago_efectivo'     => '1',
    'pago_transferencia'=> '1',
];
$stAj = $pdo->prepare('INSERT INTO ajustes (clave, valor) VALUES (?,?)');
foreach ($ajustes as $k => $v) {
    $stAj->execute([$k, $v]);
}

// ── Pedidos de ejemplo (para que el CRM no arranque vacío) ───────────────────
$clientesDemo = [
    ['Marcela Restrepo',  '3104458821', 'marce.restrepo@gmail.com', 'Cra 78 # 32 - 14, apto 502', 'Belén Rosales'],
    ['Julián Ossa',       '3012234110', 'julianossa@outlook.com',   'Cll 33 # 76 - 40',           'Laureles'],
    ['Diana Cardona',     '3155590087', '',                          'Cra 81 # 30 A - 22',         'La América'],
    ['Andrés Gil',        '3214478890', 'agil@empresa.com',          'Av 80 # 34 - 10, of. 203',   'Nueva Villa de Aburrá'],
];
$stCli = $pdo->prepare('INSERT INTO clientes (nombre, telefono, email, direccion, barrio, creado) VALUES (?,?,?,?,?,?)');
$cliIds = [];
foreach ($clientesDemo as $i => $c) {
    $stCli->execute([$c[0], $c[1], $c[2], $c[3], $c[4], date('Y-m-d H:i:s', strtotime("-" . (30 - $i * 5) . " days"))]);
    $cliIds[] = (int)$pdo->lastInsertId();
}

$prods = $pdo->query('SELECT id, nombre, precio FROM productos WHERE activo = 1')->fetchAll();
$dom   = 6000;

$pedidosDemo = [
    // [clienteIdx, tipo, metodo, pago_estado, estado, díasAtrás, hora, itemsIdx]
    [0, 'domicilio', 'tarjeta',  'pagado',    'entregado',   6, '13:12'],
    [1, 'recoger',   'efectivo', 'pendiente', 'entregado',   5, '19:40'],
    [2, 'domicilio', 'efectivo', 'pagado',    'entregado',   3, '12:55'],
    [3, 'domicilio', 'tarjeta',  'pagado',    'camino',      0, '12:30'],
    [0, 'recoger',   'tarjeta',  'pagado',    'preparacion', 0, '13:05'],
    [1, 'domicilio', 'efectivo', 'pendiente', 'recibido',    0, '13:20'],
];

$stPed = $pdo->prepare(
    'INSERT INTO pedidos (codigo, cliente_id, tipo, estado, metodo_pago, pago_estado, nombre, telefono, email,
                          direccion, barrio, notas, subtotal, descuento, domicilio, total, creado, actualizado)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
);
$stItem = $pdo->prepare(
    'INSERT INTO pedido_items (pedido_id, producto_id, nombre, precio, cantidad, adiciones, nota, subtotal)
     VALUES (?,?,?,?,?,?,?,?)'
);
$stEv = $pdo->prepare('INSERT INTO pedido_eventos (pedido_id, estado, nota, creado) VALUES (?,?,?,?)');

foreach ($pedidosDemo as $n => $d) {
    [$ci, $tipo, $metodo, $pagoEstado, $estado, $dias, $hora] = $d;
    $cli   = $clientesDemo[$ci];
    $fecha = date('Y-m-d', strtotime("-$dias days")) . ' ' . $hora . ':00';

    // 1 a 3 platos por pedido, tomados de la carta real
    $items = [];
    $sub   = 0;
    $picks = array_slice($prods, ($n * 7) % max(1, count($prods) - 3), 2 + ($n % 2));
    foreach ($picks as $p) {
        $cant = 1 + ($n + $p['id']) % 2;
        $lineTotal = (int)$p['precio'] * $cant;
        $items[] = [$p['id'], $p['nombre'], (int)$p['precio'], $cant, $lineTotal];
        $sub += $lineTotal;
    }
    $envio = $tipo === 'domicilio' ? $dom : 0;
    $total = $sub + $envio;

    $stPed->execute([
        'ALB-' . str_pad((string)(1000 + $n), 4, '0', STR_PAD_LEFT),
        $cliIds[$ci], $tipo, $estado, $metodo, $pagoEstado,
        $cli[0], $cli[1], $cli[2],
        $tipo === 'domicilio' ? $cli[3] : '', $tipo === 'domicilio' ? $cli[4] : '',
        $n === 2 ? 'Sin cebolla, por favor.' : '',
        $sub, 0, $envio, $total, $fecha, $fecha,
    ]);
    $pid = (int)$pdo->lastInsertId();
    foreach ($items as $it) {
        $stItem->execute([$pid, $it[0], $it[1], $it[2], $it[3], '[]', '', $it[4]]);
    }
    $stEv->execute([$pid, 'recibido', 'Pedido creado desde la web', $fecha]);
    if ($estado !== 'recibido') {
        $stEv->execute([$pid, $estado, 'Actualizado por el equipo', $fecha]);
    }
}

echo "Base de datos creada en " . DB_PATH . PHP_EOL;
echo count($productos) . " platos, " . count($categorias) . " categorías, " . count($adiciones) . " adiciones, "
   . count($pedidosDemo) . " pedidos de ejemplo." . PHP_EOL;
echo "Admin: " . ADMIN_USER . " / " . ADMIN_PASS_PLAIN . PHP_EOL;
