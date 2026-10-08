# Albenzu · Tradición Paisa

**Pedidos en línea y CRM para Restaurante Albenzu**, cocina paisa en Medellín desde 1982. Los clientes ven la carta con precios, arman su pedido, eligen **domicilio o recoger** y pagan **con tarjeta o en efectivo**; el restaurante lo gestiona todo desde un panel interno.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-003B57?style=for-the-badge&logo=sqlite&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=black)
![GSAP](https://img.shields.io/badge/GSAP-88CE02?style=for-the-badge&logo=greensock&logoColor=black)

**En línea:** [davidburgos.dev/albenzu](https://davidburgos.dev/albenzu) · panel en [/albenzu/admin](https://davidburgos.dev/albenzu/admin)

---

## Características

### Sitio público
| Página | Qué hace |
|---|---|
| `index.php` | Landing: hero animado, cómo pedir, destacados, historia, categorías, reseñas y mapa |
| `carta.php` | Carta completa por categoría, filtro fijo y buscador |
| `carrito.php` | Cantidades, cupón, domicilio o recoger, sugeridos |
| `checkout.php` | Datos del cliente, dirección y método de pago, con validación en servidor |
| `pasarela.php` | Pasarela de pago **simulada** (aprobado / rechazado) |
| `pedido.php` | Confirmación con línea de tiempo del estado |
| `seguimiento.php` | Consulta del pedido por código `ALB-XXXX` |
| `nosotros.php`, `contacto.php` | Historia de la casa y ubicación |

Cada plato se agrega en un clic o con un modal para sumar **adiciones** (chicharrón, aguacate, hogao…) y una **nota para la cocina**.

Estados del pedido:
- Domicilio: `recibido → confirmado → en preparación → en camino → entregado`
- Recoger: `recibido → confirmado → en preparación → listo para recoger → entregado`

### Panel / CRM (`/admin`)
| Módulo | Qué hace |
|---|---|
| **Resumen** | Ventas del día y del mes, ticket promedio, pedidos en curso, últimos 7 días, domicilio vs. recoger, platos más vendidos |
| **Pedidos** | Filtros por estado, tipo, pago y fechas; cambio de estado en un clic; exportar CSV |
| **Detalle de pedido** | Comanda imprimible, estado con nota, registro de pago, trazabilidad e historial del cliente |
| **Carta** | CRUD de platos: precio, oferta, foto, etiquetas, destacado, agotado del día, visible/oculto |
| **Categorías** | CRUD de categorías y adiciones |
| **Clientes** | Ficha con total gastado, favoritos, historial y notas; segmentos recurrente / nuevo / dormido |
| **Cupones** | Porcentaje o valor fijo, compra mínima, usos máximos, vencimiento |
| **Ajustes** | Domicilio, pedido mínimo, tiempos, horario, abrir/cerrar el canal y métodos de pago |

La carta usa los **nombres y precios reales** publicados por el restaurante.

## Stack

PHP 8.0+ sin framework · SQLite vía PDO · Tailwind CSS (CDN) · Alpine.js · GSAP + ScrollTrigger + Lenis.
Sin build ni dependencias que instalar.

## Estructura del proyecto

```
albenzu/
├── index.php, carta.php, carrito.php, checkout.php, pasarela.php, pedido.php, seguimiento.php …
├── admin/      panel + CRM (pedidos, productos, categorías, clientes, cupones, ajustes)
├── config/     config.php (ajustes y credenciales demo) · db.php
├── database/   schema.sql · seed.php
├── includes/   functions.php, componentes.php, arte.php, header/footer
├── assets/     css, js, img
└── data/       albenzu.sqlite (se genera, no se versiona)
```

## Cómo correrlo en local

```bash
# 1. Crear la base con la carta completa y 6 pedidos de ejemplo
php database/seed.php

# 2. Levantar el servidor
php -S localhost:8000
```

- Sitio: http://localhost:8000
- Panel: http://localhost:8000/admin

> `seed.php` **borra y recrea** la base de datos cada vez que se ejecuta.

### Pago con tarjeta

El flujo completo funciona contra `pasarela.php`, una pasarela **simulada** que no pide ni guarda datos de tarjeta. Para conectar una real (Wompi o Bold): reemplazar los botones de `pasarela.php` por el redirect del proveedor, llamar a `marcar_pago()` en el retorno o webhook y activar `PAYMENT_ENABLED` en `config/config.php`.

## Acceso de demostración

| Usuario | Contraseña |
|---|---|
| `admin` | `albenzu2025` |

Credenciales **solo de demostración**, definidas en `config/config.php`. Cámbialas antes de cualquier uso real.

## Autor

**David Burgos** — desarrollador full-stack, Medellín.

- Portafolio: [davidburgos.dev](https://davidburgos.dev)
- GitHub: [@burgosdavid057-art](https://github.com/burgosdavid057-art)
- LinkedIn: [David Burgos](https://www.linkedin.com/in/david-burgos-ab673433a/)
