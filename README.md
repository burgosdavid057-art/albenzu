# Albenzu · Tradición Paisa — pedidos en línea + CRM

Maqueta funcional para **Restaurante Albenzu** (Av. 80 # 32 - 98, Nueva Villa de Aburrá,
Medellín · desde 1982). Cubre lo que pidió el cliente: una web donde la gente ve todos los
platos con precios, arma su pedido, elige **domicilio o para recoger**, paga **con tarjeta o
en efectivo**, y un **panel interno (CRM)** donde el restaurante gestiona todo.

---

**En línea:** https://davidburgos.dev/albenzu · panel https://davidburgos.dev/albenzu/admin

---

## Cómo levantarlo

```bash
C:\xampp\php\php.exe database/seed.php
```

```bash
C:\xampp\php\php.exe -S 127.0.0.1:8795 -t "C:\Users\USER\OneDrive\Documentos\Desarrollo\presentaciones para clientes\albenzu"
```

- Sitio → http://127.0.0.1:8795
- Panel → http://127.0.0.1:8795/admin · **admin / albenzu2025**

El `seed.php` **borra y recrea** la base de datos con la carta completa y 6 pedidos de ejemplo
para que el panel no arranque vacío.

---

## Qué hay

### Sitio público
| Página | Qué hace |
|---|---|
| `index.php` | Landing: hero animado, cómo pedir, destacados, historia, categorías, reseñas, mapa |
| `carta.php` | Carta completa agrupada por categoría, filtro pegajoso y buscador |
| `carrito.php` | Líneas del pedido, cantidades, cupón, domicilio/recoger, sugeridos |
| `checkout.php` | Datos del cliente, dirección, método de pago, validación server-side |
| `pasarela.php` | Pasarela de pago **simulada** (ver abajo) |
| `pedido.php` | Confirmación + línea de tiempo del estado + historial |
| `seguimiento.php` | Consulta por código `ALB-XXXX` |
| `nosotros.php`, `contacto.php` | Historia de la casa y ubicación |

Cada plato se puede agregar de un clic o abrir un modal para sumarle **adiciones**
(chicharrón, aguacate, hoga'o…) y una **nota para la cocina**.

### Panel / CRM (`/admin`)
| Módulo | Qué hace |
|---|---|
| **Resumen** | Ventas del día y del mes, ticket promedio, pedidos en curso, ventas de los últimos 7 días, reparto domicilio/recoger, platos más vendidos, pendiente de cobro |
| **Pedidos** | Listado con filtros (estado, tipo, pago, fechas, búsqueda), cambio de estado en un clic, **exportar CSV** |
| **Pedido (detalle)** | Comanda imprimible, cambio de estado con nota, marcar pago y referencia, editar dirección/domicilio/notas, trazabilidad completa, historial del cliente al lado |
| **Carta** | CRUD de platos: precio, oferta, foto, etiquetas, destacado, **agotado del día**, visible/oculto |
| **Categorías** | CRUD de categorías y de adiciones |
| **Clientes** | CRM: ficha por cliente con total gastado, ticket promedio, platos favoritos, historial y notas internas. Segmentación automática: recurrente / nuevo / dormido (+30 días) |
| **Cupones** | CRUD de cupones (% o valor fijo), compra mínima, usos máximos, vencimiento |
| **Ajustes** | Valor del domicilio, domicilio gratis desde, pedido mínimo, tiempos de entrega, horario, abrir/cerrar el canal en línea, activar o desactivar cada método de pago |

Los estados siguen el flujo real y cambian según el tipo de pedido:
`recibido → confirmado → en preparación → en camino → entregado` (domicilio)
`recibido → confirmado → en preparación → listo para recoger → entregado` (recoger)

---

## Sobre el pago con tarjeta

El flujo está completo de punta a punta, pero contra una **pasarela simulada**
(`pasarela.php`), claramente marcada como demo. **No pide ni guarda datos de tarjeta**:
muestra los dos desenlaces (aprobado / rechazado) para que el cliente vea cómo se comporta
el sistema en cada caso.

Para conectar la pasarela real (Wompi o Bold):

1. Reemplazar los botones de `pasarela.php` por el redirect firmado del proveedor.
2. En el retorno o webhook, llamar a `marcar_pago($pedidoId, 'pagado'|'fallido', $referencia)`.
3. Poner `PAYMENT_ENABLED = true` en `config/config.php`.

Los datos de la tarjeta viajan siempre directo al proveedor: el servidor del restaurante
nunca los toca.

---

## Pendientes que dependen del cliente

- **Logo.** El logo del perfil está protegido contra descarga, así que el wordmark está
  reconstruido en SVG (`logo_svg()` en `includes/functions.php`) siguiendo el original
  —círculo crema, «Albenzu» en script rojo, «Frijoles con alma paisa» debajo—. Al recibir el
  archivo original se reemplaza esa función y listo.
- **Fotos de los platos.** No hay fotos en el repo; mientras tanto cada plato muestra una
  ilustración generada con la paleta de la casa, y el hero y la sección de historia usan
  escenas vectoriales (`includes/arte.php`). Las fotos reales se suben desde el panel, plato
  por plato, o se dejan en `assets/img/fotos/`.
- **Celular de WhatsApp.** El perfil sólo publica el enlace corto `wa.me/message/…`, que no
  admite mensaje precargado. Al tener el celular comercial se pone en `WHATSAPP_NUMBER`
  (`config/config.php`) y todos los CTA pasan a abrir el chat con el mensaje ya escrito.
- **Correo y NIT** para las facturas y los pies de página legales.

## Sobre la carta

Los **precios y nombres de los platos son los reales** publicados por el restaurante en su
canal de domicilios (bandejas, cazuelas, arepas Re-Paisas, asados, antojitos, desayunos,
infantil, bebidas y adiciones). Las **descripciones son redacción propia** para la demo: el
cliente las ajusta desde el panel sin tocar código.

---

## Stack

PHP puro (8.0+) · SQLite vía PDO · Tailwind CDN · Alpine.js · GSAP + ScrollTrigger + Lenis.
Sin build, sin dependencias que instalar: se sube por FTP y funciona.

```
config/     configuración y conexión
database/   schema.sql + seed.php
includes/   functions.php, componentes.php, arte.php, header/footer
assets/     css, js, imágenes
admin/      panel + CRM
data/       albenzu.sqlite (ignorado por git)
```
