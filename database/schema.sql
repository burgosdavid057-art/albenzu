-- Albenzu · esquema SQLite
PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS pedido_items;
DROP TABLE IF EXISTS pedido_eventos;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS adiciones;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS cupones;
DROP TABLE IF EXISTS ajustes;

CREATE TABLE categorias (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  nombre      TEXT NOT NULL,
  slug        TEXT NOT NULL UNIQUE,
  descripcion TEXT DEFAULT '',
  emoji       TEXT DEFAULT '',
  orden       INTEGER DEFAULT 0,
  activo      INTEGER DEFAULT 1
);

CREATE TABLE productos (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  categoria_id  INTEGER NOT NULL REFERENCES categorias(id) ON DELETE CASCADE,
  nombre        TEXT NOT NULL,
  slug          TEXT NOT NULL UNIQUE,
  descripcion   TEXT DEFAULT '',
  precio        INTEGER NOT NULL DEFAULT 0,
  precio_oferta INTEGER,
  imagen        TEXT DEFAULT '',
  etiquetas     TEXT DEFAULT '[]',   -- JSON: ["picante","favorito",...]
  destacado     INTEGER DEFAULT 0,
  disponible    INTEGER DEFAULT 1,   -- agotado del día
  activo        INTEGER DEFAULT 1,
  orden         INTEGER DEFAULT 0
);

-- Adiciones que se pueden enganchar a cualquier plato desde el carrito
CREATE TABLE adiciones (
  id     INTEGER PRIMARY KEY AUTOINCREMENT,
  nombre TEXT NOT NULL,
  precio INTEGER NOT NULL DEFAULT 0,
  orden  INTEGER DEFAULT 0,
  activo INTEGER DEFAULT 1
);

CREATE TABLE clientes (
  id        INTEGER PRIMARY KEY AUTOINCREMENT,
  nombre    TEXT NOT NULL,
  telefono  TEXT NOT NULL UNIQUE,
  email     TEXT DEFAULT '',
  direccion TEXT DEFAULT '',
  barrio    TEXT DEFAULT '',
  notas     TEXT DEFAULT '',
  creado    TEXT NOT NULL
);

CREATE TABLE cupones (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  codigo     TEXT NOT NULL UNIQUE,
  tipo       TEXT NOT NULL DEFAULT 'pct',   -- pct | fijo
  valor      INTEGER NOT NULL DEFAULT 0,
  min_compra INTEGER DEFAULT 0,
  usos_max   INTEGER DEFAULT 0,             -- 0 = ilimitado
  usos       INTEGER DEFAULT 0,
  vence      TEXT DEFAULT '',               -- Y-m-d, '' = sin vencimiento
  activo     INTEGER DEFAULT 1
);

CREATE TABLE pedidos (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  codigo         TEXT NOT NULL UNIQUE,
  cliente_id     INTEGER REFERENCES clientes(id) ON DELETE SET NULL,
  tipo           TEXT NOT NULL DEFAULT 'domicilio',  -- domicilio | recoger
  estado         TEXT NOT NULL DEFAULT 'recibido',   -- recibido|confirmado|preparacion|camino|listo|entregado|cancelado
  metodo_pago    TEXT NOT NULL DEFAULT 'efectivo',   -- tarjeta | efectivo | transferencia
  pago_estado    TEXT NOT NULL DEFAULT 'pendiente',  -- pendiente | pagado | fallido
  pago_ref       TEXT DEFAULT '',
  nombre         TEXT NOT NULL,
  telefono       TEXT NOT NULL,
  email          TEXT DEFAULT '',
  direccion      TEXT DEFAULT '',
  barrio         TEXT DEFAULT '',
  indicaciones   TEXT DEFAULT '',
  notas          TEXT DEFAULT '',
  paga_con       INTEGER DEFAULT 0,   -- efectivo: con cuánto paga (para la devuelta)
  cupon_codigo   TEXT DEFAULT '',
  subtotal       INTEGER NOT NULL DEFAULT 0,
  descuento      INTEGER NOT NULL DEFAULT 0,
  domicilio      INTEGER NOT NULL DEFAULT 0,
  total          INTEGER NOT NULL DEFAULT 0,
  creado         TEXT NOT NULL,
  actualizado    TEXT NOT NULL
);

CREATE TABLE pedido_items (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  pedido_id   INTEGER NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
  producto_id INTEGER,
  nombre      TEXT NOT NULL,
  precio      INTEGER NOT NULL DEFAULT 0,   -- unitario del plato base
  cantidad    INTEGER NOT NULL DEFAULT 1,
  adiciones   TEXT DEFAULT '[]',            -- JSON [{nombre,precio}]
  nota        TEXT DEFAULT '',
  subtotal    INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE pedido_eventos (
  id        INTEGER PRIMARY KEY AUTOINCREMENT,
  pedido_id INTEGER NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
  estado    TEXT NOT NULL,
  nota      TEXT DEFAULT '',
  creado    TEXT NOT NULL
);

CREATE TABLE ajustes (
  clave TEXT PRIMARY KEY,
  valor TEXT NOT NULL DEFAULT ''
);

CREATE INDEX idx_prod_cat    ON productos(categoria_id);
CREATE INDEX idx_ped_estado  ON pedidos(estado);
CREATE INDEX idx_ped_creado  ON pedidos(creado);
CREATE INDEX idx_items_ped   ON pedido_items(pedido_id);
