<?php
require_once __DIR__ . '/../config/db.php';

// ── Utilidades básicas ───────────────────────────────────────────────────────

/** Escape para HTML */
function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

/** Formato de precio en COP: 65900 -> $65.900 */
function precio_cop($n): string {
    return '$' . number_format((int)$n, 0, ',', '.');
}

/** Slug (transliteración manual; iconv es poco fiable en Windows) */
function slugify(string $t): string {
    $map = ['á'=>'a','à'=>'a','ä'=>'a','â'=>'a','é'=>'e','è'=>'e','ë'=>'e','ê'=>'e',
            'í'=>'i','ì'=>'i','ï'=>'i','î'=>'i','ó'=>'o','ò'=>'o','ö'=>'o','ô'=>'o',
            'ú'=>'u','ù'=>'u','ü'=>'u','û'=>'u','ñ'=>'n','ç'=>'c',
            'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ñ'=>'n'];
    $t = strtr($t, $map);
    $t = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $t));
    return trim($t, '-');
}

/** Decodifica una columna JSON a array (tolerante) */
function json_col($val): array {
    if (is_array($val)) return $val;
    if (!is_string($val) || $val === '') return [];
    $d = json_decode($val, true);
    return is_array($d) ? $d : [];
}

/** Teléfono legible: 3104458821 -> 310 445 8821 */
function tel_humano(string $t): string {
    $d = preg_replace('/\D+/', '', $t);
    return strlen($d) === 10 ? trim(chunk_split($d, 3, ' ')) : $t;
}

/** Fecha corta en español: 2026-08-04 13:20:00 -> "4 ago, 1:20 p. m." */
function fecha_humana(string $ts, bool $conHora = true): string {
    $t = strtotime($ts);
    if (!$t) return $ts;
    $meses = [1=>'ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    $out = (int)date('j', $t) . ' ' . $meses[(int)date('n', $t)];
    if (date('Y', $t) !== date('Y')) $out .= ' ' . date('Y', $t);
    if ($conHora) {
        $out .= ', ' . date('g:i', $t) . ' ' . (date('a', $t) === 'am' ? 'a. m.' : 'p. m.');
    }
    return $out;
}

/** Token CSRF de la sesión */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok(): bool {
    return isset($_POST['_csrf']) && hash_equals(csrf_token(), (string)$_POST['_csrf']);
}

/** Mensaje flash entre redirecciones */
function flash(?string $msg = null, string $tipo = 'ok'): ?array {
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'tipo' => $tipo];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// ── Ajustes ──────────────────────────────────────────────────────────────────

function ajustes(): array {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT clave, valor FROM ajustes')->fetchAll() as $r) {
            $cache[$r['clave']] = $r['valor'];
        }
    }
    return $cache;
}

function ajuste(string $clave, string $def = ''): string {
    return ajustes()[$clave] ?? $def;
}

function ajuste_int(string $clave, int $def = 0): int {
    $v = ajuste($clave, '');
    return $v === '' ? $def : (int)$v;
}

function set_ajuste(string $clave, string $valor): void {
    $st = db()->prepare('INSERT INTO ajustes (clave, valor) VALUES (?,?)
                         ON CONFLICT(clave) DO UPDATE SET valor = excluded.valor');
    $st->execute([$clave, $valor]);
}

/** ¿Está el restaurante recibiendo pedidos ahora mismo? */
function esta_abierto(): bool {
    if (ajuste('abierto', '1') !== '1') return false;
    $ahora = date('H:i');
    return $ahora >= ajuste('hora_apertura', '11:00') && $ahora <= ajuste('hora_cierre', '22:00');
}

// ── Catálogo ─────────────────────────────────────────────────────────────────

function get_categorias(bool $soloActivas = true): array {
    $sql = 'SELECT * FROM categorias';
    if ($soloActivas) $sql .= ' WHERE activo = 1';
    $sql .= ' ORDER BY orden, id';
    return db()->query($sql)->fetchAll();
}

function get_categoria(int $id): ?array {
    $st = db()->prepare('SELECT * FROM categorias WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function get_productos(array $o = []): array {
    $sql  = 'SELECT p.*, c.nombre AS categoria, c.slug AS categoria_slug, c.emoji
             FROM productos p JOIN categorias c ON c.id = p.categoria_id WHERE 1=1';
    $args = [];
    if ($o['solo_activos'] ?? true)  $sql .= ' AND p.activo = 1 AND c.activo = 1';
    if (!empty($o['categoria_id'])) { $sql .= ' AND p.categoria_id = ?'; $args[] = (int)$o['categoria_id']; }
    if (!empty($o['destacado']))      $sql .= ' AND p.destacado = 1';
    if (!empty($o['buscar'])) {
        $sql .= ' AND (p.nombre LIKE ? OR p.descripcion LIKE ?)';
        $args[] = '%' . $o['buscar'] . '%';
        $args[] = '%' . $o['buscar'] . '%';
    }
    $sql .= match ($o['orden'] ?? '') {
        'precio_asc'  => ' ORDER BY COALESCE(p.precio_oferta, p.precio) ASC',
        'precio_desc' => ' ORDER BY COALESCE(p.precio_oferta, p.precio) DESC',
        'nombre'      => ' ORDER BY p.nombre ASC',
        default       => ' ORDER BY c.orden, p.orden, p.id',
    };
    if (!empty($o['limit'])) $sql .= ' LIMIT ' . (int)$o['limit'];

    $st = db()->prepare($sql);
    $st->execute($args);
    return $st->fetchAll();
}

function get_producto(int $id): ?array {
    $st = db()->prepare('SELECT p.*, c.nombre AS categoria FROM productos p
                         JOIN categorias c ON c.id = p.categoria_id WHERE p.id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function get_adiciones(bool $soloActivas = true): array {
    $sql = 'SELECT * FROM adiciones';
    if ($soloActivas) $sql .= ' WHERE activo = 1';
    $sql .= ' ORDER BY orden, id';
    return db()->query($sql)->fetchAll();
}

function precio_final(array $p): int {
    return !empty($p['precio_oferta']) ? (int)$p['precio_oferta'] : (int)$p['precio'];
}

function tiene_oferta(array $p): bool {
    return !empty($p['precio_oferta']) && (int)$p['precio_oferta'] < (int)$p['precio'];
}

// ── Imágenes ─────────────────────────────────────────────────────────────────

/** Resuelve la foto de un plato; si no hay archivo, devuelve un SVG generado */
function plato_img(array $p): string {
    $f = $p['imagen'] ?? '';
    if ($f !== '') {
        if (is_file(UPLOAD_DIR . '/' . $f))                         return base_url(UPLOAD_URL . '/' . $f);
        if (is_file(__DIR__ . '/../assets/img/fotos/' . $f))        return base_url('assets/img/fotos/' . $f);
        if (preg_match('#^https?://#', $f))                          return $f;
    }
    return placeholder_svg($p['nombre'] ?? SITE_NAME, $p['categoria'] ?? '');
}

/**
 * Placeholder SVG con la paleta de la casa (crema + rojo Albenzu), con un plato
 * dibujado y la inicial del producto. Se embebe como data-URI: cero peticiones.
 */
function placeholder_svg(string $nombre, string $sub = ''): string {
    $ini = strtoupper(mb_substr(trim($nombre), 0, 1));
    $sub = e(mb_strtoupper($sub));
    $nom = e(mb_strtoupper($nombre));
    // Tono variable por nombre para que la grilla no se vea plana
    $tonos = ['#B7202A', '#8E1A22', '#C8452C', '#7A3B1E', '#A5651C'];
    $tono  = $tonos[abs(crc32($nombre)) % count($tonos)];
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="900" height="700" viewBox="0 0 900 700">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#F6F0E2"/><stop offset="100%" stop-color="#E9DEC6"/>
    </linearGradient>
    <linearGradient id="plato" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="$tono"/><stop offset="100%" stop-color="#5E1216"/>
    </linearGradient>
  </defs>
  <rect width="900" height="700" fill="url(#bg)"/>
  <g opacity=".28" fill="none" stroke="$tono" stroke-width="2">
    <circle cx="120" cy="110" r="46"/><circle cx="790" cy="600" r="62"/>
    <path d="M0 640 Q225 590 450 640 T900 640"/>
    <path d="M0 76 Q225 26 450 76 T900 76"/>
  </g>
  <circle cx="450" cy="320" r="180" fill="url(#plato)"/>
  <circle cx="450" cy="320" r="180" fill="none" stroke="#F6F0E2" stroke-width="10" opacity=".85"/>
  <circle cx="450" cy="320" r="140" fill="none" stroke="#F6F0E2" stroke-width="2" opacity=".45"/>
  <text x="450" y="320" font-family="Georgia, 'Times New Roman', serif" font-size="170" font-weight="700"
        fill="#F6F0E2" text-anchor="middle" dominant-baseline="central">$ini</text>
  <text x="450" y="570" font-family="Georgia, serif" font-size="30" font-weight="700" letter-spacing="2"
        fill="#5E1216" text-anchor="middle">$nom</text>
  <text x="450" y="606" font-family="Arial, sans-serif" font-size="16" letter-spacing="6"
        fill="#8E1A22" text-anchor="middle" opacity=".8">$sub</text>
</svg>
SVG;
    return 'data:image/svg+xml;charset=utf-8,' . rawurlencode($svg);
}

/**
 * Wordmark de Albenzu reconstruido en SVG a partir del logo real (círculo crema,
 * "Albenzu" en script rojo y el descriptor debajo).
 * TODO: reemplazar por el PNG/SVG original cuando el cliente lo entregue.
 */
function logo_svg(int $size = 96, bool $conCirculo = true): string {
    $circulo = $conCirculo
        ? '<circle cx="130" cy="130" r="128" fill="#F6F0E2" stroke="#E2D5B8" stroke-width="2"/>'
        : '';
    return <<<SVG
<svg viewBox="0 0 260 260" width="$size" height="$size" role="img" aria-label="Albenzu" xmlns="http://www.w3.org/2000/svg">
  $circulo
  <text x="130" y="128" text-anchor="middle" font-family="Georgia, 'Times New Roman', serif"
        font-size="76" font-style="italic" font-weight="700" fill="#B7202A"
        letter-spacing="-1">Albenzu</text>
  <path d="M46 150 H214" stroke="#B7202A" stroke-width="3" stroke-linecap="round"/>
  <text x="130" y="178" text-anchor="middle" font-family="Arial, Helvetica, sans-serif"
        font-size="17" font-weight="700" letter-spacing="1.5" fill="#B7202A">FRIJOLES CON</text>
  <text x="130" y="200" text-anchor="middle" font-family="Arial, Helvetica, sans-serif"
        font-size="17" font-weight="700" letter-spacing="1.5" fill="#B7202A">ALMA PAISA</text>
</svg>
SVG;
}

// ── WhatsApp ─────────────────────────────────────────────────────────────────

function whatsapp_link(string $msg = ''): string {
    if (WHATSAPP_NUMBER === '') {
        return WHATSAPP_LINK;   // enlace corto del perfil: no admite texto precargado
    }
    $msg = $msg !== '' ? $msg : 'Hola ' . SITE_NAME . ', quiero hacer un pedido.';
    return 'https://wa.me/' . WHATSAPP_NUMBER . '?text=' . rawurlencode($msg);
}

// ── Carrito (sesión) ─────────────────────────────────────────────────────────

function carrito(): array {
    return $_SESSION['carrito'] ?? [];
}

function carrito_key(int $productoId, array $adiciones, string $nota): string {
    sort($adiciones);
    return substr(md5($productoId . '|' . implode(',', $adiciones) . '|' . trim($nota)), 0, 12);
}

/** Agrega un plato (con sus adiciones y nota) al carrito */
function carrito_add(int $productoId, int $cantidad = 1, array $adicionIds = [], string $nota = ''): bool {
    $p = get_producto($productoId);
    if (!$p || !$p['activo'] || !$p['disponible']) return false;

    $adiciones = [];
    if ($adicionIds) {
        $in = implode(',', array_fill(0, count($adicionIds), '?'));
        $st = db()->prepare("SELECT id, nombre, precio FROM adiciones WHERE activo = 1 AND id IN ($in)");
        $st->execute(array_map('intval', $adicionIds));
        foreach ($st->fetchAll() as $a) {
            $adiciones[] = ['id' => (int)$a['id'], 'nombre' => $a['nombre'], 'precio' => (int)$a['precio']];
        }
    }

    $key = carrito_key($productoId, array_column($adiciones, 'id'), $nota);
    $c   = carrito();
    if (isset($c[$key])) {
        $c[$key]['cantidad'] += max(1, $cantidad);
    } else {
        $c[$key] = [
            'producto_id' => $productoId,
            'nombre'      => $p['nombre'],
            'precio'      => precio_final($p),
            'imagen'      => plato_img($p),
            'cantidad'    => max(1, $cantidad),
            'adiciones'   => $adiciones,
            'nota'        => mb_substr(trim($nota), 0, 160),
        ];
    }
    $_SESSION['carrito'] = $c;
    return true;
}

function carrito_set_cantidad(string $key, int $cantidad): void {
    $c = carrito();
    if (!isset($c[$key])) return;
    if ($cantidad <= 0) {
        unset($c[$key]);
    } else {
        $c[$key]['cantidad'] = min(30, $cantidad);
    }
    $_SESSION['carrito'] = $c;
}

function carrito_quitar(string $key): void {
    $c = carrito();
    unset($c[$key]);
    $_SESSION['carrito'] = $c;
}

function carrito_vaciar(): void {
    unset($_SESSION['carrito'], $_SESSION['cupon']);
}

/** Precio de una línea (plato + adiciones) × cantidad */
function linea_total(array $item): int {
    $unit = (int)$item['precio'];
    foreach ($item['adiciones'] as $a) $unit += (int)$a['precio'];
    return $unit * (int)$item['cantidad'];
}

function carrito_unidades(): int {
    return array_sum(array_column(carrito(), 'cantidad'));
}

function carrito_subtotal(): int {
    $t = 0;
    foreach (carrito() as $i) $t += linea_total($i);
    return $t;
}

// ── Cupones ──────────────────────────────────────────────────────────────────

function get_cupon(string $codigo): ?array {
    $st = db()->prepare('SELECT * FROM cupones WHERE UPPER(codigo) = UPPER(?)');
    $st->execute([trim($codigo)]);
    return $st->fetch() ?: null;
}

/** Devuelve ['ok'=>bool, 'msg'=>string, 'descuento'=>int, 'cupon'=>array|null] */
function validar_cupon(string $codigo, int $subtotal): array {
    $c = get_cupon($codigo);
    if (!$c || !$c['activo'])                                return ['ok' => false, 'msg' => 'Ese cupón no existe o ya no está activo.', 'descuento' => 0, 'cupon' => null];
    if ($c['vence'] !== '' && $c['vence'] < date('Y-m-d'))   return ['ok' => false, 'msg' => 'Ese cupón ya venció.', 'descuento' => 0, 'cupon' => null];
    if ((int)$c['usos_max'] > 0 && (int)$c['usos'] >= (int)$c['usos_max'])
                                                             return ['ok' => false, 'msg' => 'Ese cupón ya se agotó.', 'descuento' => 0, 'cupon' => null];
    if ($subtotal < (int)$c['min_compra'])                   return ['ok' => false, 'msg' => 'El cupón aplica desde ' . precio_cop($c['min_compra']) . '.', 'descuento' => 0, 'cupon' => null];

    $desc = $c['tipo'] === 'pct'
        ? (int)round($subtotal * (int)$c['valor'] / 100)
        : (int)$c['valor'];
    $desc = min($desc, $subtotal);
    return ['ok' => true, 'msg' => 'Cupón aplicado: -' . precio_cop($desc), 'descuento' => $desc, 'cupon' => $c];
}

/** Costo del domicilio según ajustes y subtotal */
function costo_domicilio(string $tipo, int $subtotal): int {
    if ($tipo !== 'domicilio') return 0;
    $gratisDesde = ajuste_int('domicilio_gratis', 0);
    if ($gratisDesde > 0 && $subtotal >= $gratisDesde) return 0;
    return ajuste_int('domicilio_valor', 0);
}

/** Resumen económico del carrito para el checkout */
function resumen_carrito(string $tipo = 'domicilio'): array {
    $subtotal = carrito_subtotal();
    $descuento = 0;
    $cupon = '';
    if (!empty($_SESSION['cupon'])) {
        $v = validar_cupon($_SESSION['cupon'], $subtotal);
        if ($v['ok']) { $descuento = $v['descuento']; $cupon = strtoupper($_SESSION['cupon']); }
        else          { unset($_SESSION['cupon']); }
    }
    $envio = costo_domicilio($tipo, $subtotal - $descuento);
    return [
        'subtotal'  => $subtotal,
        'descuento' => $descuento,
        'cupon'     => $cupon,
        'domicilio' => $envio,
        'total'     => max(0, $subtotal - $descuento + $envio),
    ];
}

// ── Pedidos ──────────────────────────────────────────────────────────────────

function generar_codigo_pedido(): string {
    do {
        $code = 'ALB-' . strtoupper(bin2hex(random_bytes(2))) . random_int(10, 99);
        $st = db()->prepare('SELECT 1 FROM pedidos WHERE codigo = ?');
        $st->execute([$code]);
    } while ($st->fetch());
    return $code;
}

/** Crea o actualiza la ficha del cliente en el CRM y devuelve su id */
function upsert_cliente(array $d): int {
    $tel = preg_replace('/\D+/', '', $d['telefono']);
    $st  = db()->prepare('SELECT * FROM clientes WHERE telefono = ?');
    $st->execute([$tel]);
    $c = $st->fetch();

    if ($c) {
        $up = db()->prepare('UPDATE clientes SET nombre = ?, email = ?, direccion = ?, barrio = ? WHERE id = ?');
        $up->execute([
            $d['nombre'] ?: $c['nombre'],
            $d['email'] ?: $c['email'],
            $d['direccion'] ?: $c['direccion'],
            $d['barrio'] ?: $c['barrio'],
            $c['id'],
        ]);
        return (int)$c['id'];
    }

    $ins = db()->prepare('INSERT INTO clientes (nombre, telefono, email, direccion, barrio, creado) VALUES (?,?,?,?,?,?)');
    $ins->execute([$d['nombre'], $tel, $d['email'] ?? '', $d['direccion'] ?? '', $d['barrio'] ?? '', date('Y-m-d H:i:s')]);
    return (int)db()->lastInsertId();
}

/**
 * Persiste el pedido con sus líneas y su primer evento de trazabilidad.
 * Devuelve el pedido recién creado.
 */
function crear_pedido(array $d): array {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $res    = resumen_carrito($d['tipo']);
        $codigo = generar_codigo_pedido();
        $ahora  = date('Y-m-d H:i:s');
        $cid    = upsert_cliente($d);

        $st = $pdo->prepare(
            'INSERT INTO pedidos (codigo, cliente_id, tipo, estado, metodo_pago, pago_estado, nombre, telefono, email,
                                  direccion, barrio, indicaciones, notas, paga_con, cupon_codigo,
                                  subtotal, descuento, domicilio, total, creado, actualizado)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $st->execute([
            $codigo, $cid, $d['tipo'], 'recibido', $d['metodo_pago'], 'pendiente',
            $d['nombre'], preg_replace('/\D+/', '', $d['telefono']), $d['email'] ?? '',
            $d['direccion'] ?? '', $d['barrio'] ?? '', $d['indicaciones'] ?? '', $d['notas'] ?? '',
            (int)($d['paga_con'] ?? 0), $res['cupon'],
            $res['subtotal'], $res['descuento'], $res['domicilio'], $res['total'], $ahora, $ahora,
        ]);
        $pid = (int)$pdo->lastInsertId();

        $stI = $pdo->prepare(
            'INSERT INTO pedido_items (pedido_id, producto_id, nombre, precio, cantidad, adiciones, nota, subtotal)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        foreach (carrito() as $i) {
            $stI->execute([
                $pid, $i['producto_id'], $i['nombre'], $i['precio'], $i['cantidad'],
                json_encode(array_map(fn($a) => ['nombre' => $a['nombre'], 'precio' => $a['precio']], $i['adiciones']), JSON_UNESCAPED_UNICODE),
                $i['nota'], linea_total($i),
            ]);
        }

        $pdo->prepare('INSERT INTO pedido_eventos (pedido_id, estado, nota, creado) VALUES (?,?,?,?)')
            ->execute([$pid, 'recibido', 'Pedido creado desde la web', $ahora]);

        if ($res['cupon'] !== '') {
            $pdo->prepare('UPDATE cupones SET usos = usos + 1 WHERE UPPER(codigo) = ?')->execute([$res['cupon']]);
        }

        $pdo->commit();
        return get_pedido($pid);
    } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
    }
}

function get_pedido(int $id): ?array {
    $st = db()->prepare('SELECT * FROM pedidos WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function get_pedido_codigo(string $codigo): ?array {
    $st = db()->prepare('SELECT * FROM pedidos WHERE UPPER(codigo) = UPPER(?)');
    $st->execute([trim($codigo)]);
    return $st->fetch() ?: null;
}

function get_pedido_items(int $pedidoId): array {
    $st = db()->prepare('SELECT * FROM pedido_items WHERE pedido_id = ? ORDER BY id');
    $st->execute([$pedidoId]);
    return $st->fetchAll();
}

function get_pedido_eventos(int $pedidoId): array {
    $st = db()->prepare('SELECT * FROM pedido_eventos WHERE pedido_id = ? ORDER BY id');
    $st->execute([$pedidoId]);
    return $st->fetchAll();
}

function cambiar_estado_pedido(int $pedidoId, string $estado, string $nota = ''): void {
    $ahora = date('Y-m-d H:i:s');
    db()->prepare('UPDATE pedidos SET estado = ?, actualizado = ? WHERE id = ?')->execute([$estado, $ahora, $pedidoId]);
    db()->prepare('INSERT INTO pedido_eventos (pedido_id, estado, nota, creado) VALUES (?,?,?,?)')
        ->execute([$pedidoId, $estado, $nota, $ahora]);
}

function marcar_pago(int $pedidoId, string $estado, string $ref = ''): void {
    db()->prepare('UPDATE pedidos SET pago_estado = ?, pago_ref = ?, actualizado = ? WHERE id = ?')
        ->execute([$estado, $ref, date('Y-m-d H:i:s'), $pedidoId]);
}

// ── Etiquetas de estado ──────────────────────────────────────────────────────

function estados_pedido(): array {
    return [
        'recibido'    => 'Recibido',
        'confirmado'  => 'Confirmado',
        'preparacion' => 'En preparación',
        'camino'      => 'En camino',
        'listo'       => 'Listo para recoger',
        'entregado'   => 'Entregado',
        'cancelado'   => 'Cancelado',
    ];
}

function estado_label(string $e): string {
    return estados_pedido()[$e] ?? ucfirst($e);
}

/** Secuencia de estados según el tipo de pedido (para la línea de tiempo) */
function flujo_estados(string $tipo): array {
    return $tipo === 'recoger'
        ? ['recibido', 'confirmado', 'preparacion', 'listo', 'entregado']
        : ['recibido', 'confirmado', 'preparacion', 'camino', 'entregado'];
}

function metodo_pago_label(string $m): string {
    return match ($m) {
        'tarjeta'       => 'Tarjeta débito/crédito',
        'efectivo'      => 'Efectivo contra entrega',
        'transferencia' => 'Transferencia / Nequi',
        default         => ucfirst($m),
    };
}

function tipo_label(string $t): string {
    return $t === 'recoger' ? 'Para recoger' : 'Domicilio';
}
