<?php
/**
 * Ilustraciones vectoriales de la casa.
 *
 * No hay fotos del restaurante en el repo (el cliente aún no las entrega), así
 * que en vez de dejar huecos grises se dibujan escenas planas con la paleta de
 * la marca. Al recibir las fotos reales basta con dejarlas en
 * `assets/img/fotos/` y referenciarlas desde el panel.
 */

/** Bandeja paisa vista desde arriba — arte principal del hero */
function arte_bandeja(string $class = ''): string {
    return <<<SVG
<svg class="$class" viewBox="0 0 620 620" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Ilustración de una bandeja paisa">
  <defs>
    <radialGradient id="mesa" cx="50%" cy="42%" r="62%">
      <stop offset="0%" stop-color="#8C4A22"/><stop offset="100%" stop-color="#4E2612"/>
    </radialGradient>
    <linearGradient id="plato" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#FDFBF4"/><stop offset="100%" stop-color="#E8DFC8"/>
    </linearGradient>
    <linearGradient id="frijol" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#7C3B1C"/><stop offset="100%" stop-color="#4A2110"/>
    </linearGradient>
    <linearGradient id="chichar" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#E5A84B"/><stop offset="100%" stop-color="#A9631D"/>
    </linearGradient>
  </defs>

  <rect width="620" height="620" rx="28" fill="url(#mesa)"/>
  <g opacity=".16" stroke="#F6F0E2" stroke-width="1.4" fill="none">
    <path d="M0 96 H620 M0 214 H620 M0 366 H620 M0 512 H620"/>
  </g>

  <!-- Plato -->
  <ellipse cx="310" cy="316" rx="252" ry="240" fill="rgba(0,0,0,.28)"/>
  <ellipse cx="310" cy="306" rx="252" ry="240" fill="url(#plato)"/>
  <ellipse cx="310" cy="306" rx="222" ry="210" fill="none" stroke="#B7202A" stroke-width="3" opacity=".35"/>

  <!-- Frijoles -->
  <path d="M150 258c0-42 40-72 92-72s92 30 92 72-40 74-92 74-92-32-92-74Z" fill="url(#frijol)"/>
  <g fill="#3A190C" opacity=".7">
    <ellipse cx="196" cy="240" rx="13" ry="9" transform="rotate(-18 196 240)"/>
    <ellipse cx="240" cy="266" rx="13" ry="9" transform="rotate(12 240 266)"/>
    <ellipse cx="284" cy="238" rx="13" ry="9" transform="rotate(-8 284 238)"/>
    <ellipse cx="218" cy="292" rx="13" ry="9" transform="rotate(22 218 292)"/>
    <ellipse cx="272" cy="292" rx="13" ry="9" transform="rotate(-24 272 292)"/>
  </g>

  <!-- Arroz -->
  <path d="M336 196c46-14 96 6 108 44 12 38-16 74-60 80-44 6-80-18-84-52-4-34 12-60 36-72Z" fill="#FBF6E8"/>
  <g stroke="#E2D6B4" stroke-width="4" stroke-linecap="round" opacity=".9">
    <path d="M366 224l16 10M398 214l18 8M420 244l16 6M372 262l18 8M404 268l16 10M386 292l18 6"/>
  </g>

  <!-- Chicharrón -->
  <path d="M168 356c62-26 132-24 186 8 14 8 12 26-4 32-56 22-124 20-176-6-16-8-18-26-6-34Z" fill="url(#chichar)"/>
  <g stroke="#7A3E10" stroke-width="5" stroke-linecap="round" opacity=".65">
    <path d="M206 356l6 30M248 350l4 34M292 354l4 32M334 364l2 26"/>
  </g>

  <!-- Huevo frito -->
  <path d="M366 336c40-16 84 2 88 34 4 32-30 54-68 48-38-6-58-34-48-58 6-14 16-20 28-24Z" fill="#FFFDF6"/>
  <circle cx="410" cy="380" r="26" fill="#F0A81C"/>
  <circle cx="402" cy="372" r="8" fill="#FFC94D" opacity=".8"/>

  <!-- Chorizo -->
  <path d="M196 424c58-16 118-14 170 6 12 4 12 22 0 26-54 18-116 18-172 0-12-4-12-28 2-32Z" fill="#A9231F"/>
  <g stroke="#6E1310" stroke-width="4" opacity=".55">
    <path d="M240 420v34M292 418v36M344 424v30"/>
  </g>

  <!-- Tajadas de maduro -->
  <g fill="#F0B33C" stroke="#C68415" stroke-width="3">
    <path d="M128 396c26-16 54-12 62 4 8 16-12 34-40 36-28 2-46-24-22-40Z"/>
    <path d="M112 448c26-16 54-12 62 4 8 16-12 34-40 36-28 2-46-24-22-40Z"/>
  </g>

  <!-- Aguacate -->
  <path d="M470 288c26 0 44 26 40 56-4 30-26 50-48 46-22-4-36-30-32-58 4-28 18-44 40-44Z" fill="#5E8C3A"/>
  <path d="M470 306c16 0 28 18 25 38-3 20-16 34-30 32-14-2-23-20-20-40 3-20 12-30 25-30Z" fill="#B9D07A"/>
  <ellipse cx="468" cy="344" rx="13" ry="16" fill="#7A4B22"/>

  <!-- Arepa -->
  <circle cx="298" cy="480" r="52" fill="#F2E6C4" stroke="#D8C393" stroke-width="4"/>
  <circle cx="298" cy="480" r="34" fill="none" stroke="#DCC79B" stroke-width="3" opacity=".8"/>
  <g stroke="#C9B075" stroke-width="3" opacity=".7">
    <path d="M272 464l14 10M310 462l14 12M280 500l16 8"/>
  </g>

  <!-- Cubiertos -->
  <g fill="#F6F0E2" opacity=".92">
    <rect x="574" y="180" width="9" height="230" rx="4"/>
    <path d="M566 150h26v34a13 13 0 0 1-26 0Z"/>
    <rect x="30" y="180" width="9" height="230" rx="4"/>
    <path d="M22 148h6v40h-6zM33 148h6v40h-6zM44 148h6v40h-6z"/>
  </g>
</svg>
SVG;
}

/** Fachada del restaurante — usado en la sección de historia */
function arte_fachada(string $class = ''): string {
    return <<<SVG
<svg class="$class" viewBox="0 0 640 460" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Ilustración de la fachada del restaurante">
  <defs>
    <linearGradient id="cielo" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#F6F0E2"/><stop offset="100%" stop-color="#E4D7BC"/>
    </linearGradient>
  </defs>
  <rect width="640" height="460" rx="22" fill="url(#cielo)"/>
  <g opacity=".2" stroke="#B7202A" fill="none" stroke-width="2">
    <circle cx="86" cy="76" r="34"/><circle cx="560" cy="60" r="22"/>
  </g>

  <!-- Montañas del valle -->
  <path d="M0 250 L120 160 L212 246 L300 148 L410 250 L500 176 L640 264 V460 H0 Z" fill="#3E5C3A" opacity=".22"/>

  <!-- Edificio -->
  <rect x="86" y="176" width="468" height="240" rx="10" fill="#FBF7EC" stroke="#D8C7A5" stroke-width="3"/>
  <!-- Toldo -->
  <path d="M70 176h500l-24 54H94Z" fill="#B7202A"/>
  <g fill="#F6F0E2" opacity=".85">
    <path d="M118 176l-12 54h-24l14-54zM202 176l-8 54h-24l10-54zM286 176l-4 54h-24l6-54zM370 176v54h-24l2-54zM454 176l4 54h-24l-2-54zM538 176l8 54h-24l-6-54z"/>
  </g>
  <!-- Letrero -->
  <rect x="216" y="86" width="208" height="76" rx="12" fill="#B7202A"/>
  <text x="320" y="130" text-anchor="middle" font-family="Georgia, serif" font-style="italic" font-weight="700"
        font-size="40" fill="#F6F0E2">Albenzu</text>
  <text x="320" y="150" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" letter-spacing="3"
        fill="#F6F0E2" opacity=".85">DESDE 1982</text>

  <!-- Ventanales -->
  <g fill="#DCEAF0" stroke="#C3B394" stroke-width="3">
    <rect x="118" y="252" width="128" height="112" rx="6"/>
    <rect x="394" y="252" width="128" height="112" rx="6"/>
  </g>
  <g stroke="#C3B394" stroke-width="3">
    <path d="M182 252v112M118 308h128M458 252v112M394 308h128"/>
  </g>
  <!-- Puerta -->
  <rect x="278" y="256" width="84" height="160" rx="6" fill="#7A3B1E"/>
  <circle cx="348" cy="336" r="5" fill="#E0A526"/>
  <!-- Mesitas -->
  <g fill="#7A3B1E">
    <rect x="106" y="392" width="56" height="6" rx="3"/><rect x="130" y="398" width="8" height="22" rx="3"/>
    <rect x="480" y="392" width="56" height="6" rx="3"/><rect x="504" y="398" width="8" height="22" rx="3"/>
  </g>
  <rect x="0" y="416" width="640" height="44" fill="#241A16" opacity=".08"/>
</svg>
SVG;
}

/** Olla de frijoles — acento decorativo */
function arte_olla(string $class = ''): string {
    return <<<SVG
<svg class="$class" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <g fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round">
    <path d="M44 84h112v58a34 34 0 0 1-34 34H78a34 34 0 0 1-34-34Z"/>
    <path d="M32 96h12M156 96h12"/>
    <path d="M74 62c0-12 12-12 12-24M100 56c0-12 12-12 12-24M126 62c0-12 12-12 12-24"/>
    <path d="M36 84h128"/>
  </g>
</svg>
SVG;
}
