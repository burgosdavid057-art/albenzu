<?php
/**
 * Configuración global — Albenzu · Tradición Paisa
 * Restaurante de comida típica antioqueña · Medellín, desde 1982.
 */

// ── Negocio ──────────────────────────────────────────────────────────────────
const SITE_NAME    = 'Albenzu';
const SITE_LEGAL   = 'Restaurante Albenzu Tradición Paisa';
const SITE_TAGLINE = 'Frijoles con alma paisa';
const SITE_DESC    = 'Albenzu, tradición y sabor paisa desde 1982 en la Avenida 80 de Medellín. Bandejas, cazuelas, arepas Re-Paisas y asados a la parrilla. Pide en línea para domicilio o para recoger.';
const SITE_FUNDADO = 1982;
const SITE_HISTORIA = 'Alicia y Benjamín Zuluaga abrieron la casa en 1982. De sus nombres salió Albenzu, y de su fogón, los frijoles que hoy reconoce medio Medellín.';

// Contacto real
const CONTACT_PHONE   = '+57 604 444 9921';
const CONTACT_PHONE_TEL = '+576044449921';
const CONTACT_EMAIL   = 'pedidos@albenzu.co';
const CONTACT_ADDRESS = 'Av. 80 # 32 - 98, Nueva Villa de Aburrá';
const CONTACT_CITY    = 'Medellín, Antioquia';
const CONTACT_HOURS   = 'Lunes a domingo · 11:00 a. m. – 10:00 p. m.';
const MAPS_QUERY      = 'Restaurante Albenzu Tradicion Paisa, Av 80 %2332-98, Medellín';

// WhatsApp.
// El perfil público sólo expone el enlace corto wa.me/message/… (no permite texto
// precargado). Cuando el cliente entregue el celular comercial, ponerlo en
// WHATSAPP_NUMBER (formato internacional, sin + ni espacios) y los CTA pasarán a
// abrir el chat con el mensaje ya escrito.
const WHATSAPP_NUMBER = '';                                        // TODO cliente
const WHATSAPP_LINK   = 'https://wa.me/message/IX7A26RCQ5HUB1';    // enlace real del perfil
const WHATSAPP_LABEL  = 'Escríbenos por WhatsApp';

const SOCIAL_INSTAGRAM = 'https://instagram.com/albenzupaisa';
const SOCIAL_FACEBOOK  = 'https://facebook.com/AlbenzuPaisa';

// ── Admin (login simple del panel) ───────────────────────────────────────────
const ADMIN_USER       = 'admin';
const ADMIN_PASS_PLAIN = 'albenzu2025';

// ── Pagos ────────────────────────────────────────────────────────────────────
// La demo trae el flujo completo de pago con tarjeta, pero contra una pasarela
// SIMULADA (pantalla claramente marcada como demo, no captura datos de tarjeta).
// Al integrar Wompi/Bold sólo se reemplaza `pasarela.php` por el redirect real.
const PAYMENT_ENABLED  = false;      // true cuando haya llaves del cliente
const PAYMENT_PROVIDER = 'wompi';    // 'wompi' | 'bold' | 'mercadopago'
const CURRENCY         = 'COP';

// ── Rutas ────────────────────────────────────────────────────────────────────
const DB_PATH    = __DIR__ . '/../data/albenzu.sqlite';
const UPLOAD_DIR = __DIR__ . '/../assets/img/platos';
const UPLOAD_URL = 'assets/img/platos';

/** URL base del sitio, válida desde cualquier subcarpeta */
function base_url(string $path = ''): string {
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    foreach (['/admin', '/api'] as $sub) {
        if (str_ends_with($dir, $sub)) {
            $dir = substr($dir, 0, -strlen($sub));
            break;
        }
    }
    return $dir . '/' . ltrim($path, '/');
}

date_default_timezone_set('America/Bogota');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
