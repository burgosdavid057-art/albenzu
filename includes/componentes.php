<?php
/**
 * Componentes de UI reutilizados por la landing y la carta.
 */
require_once __DIR__ . '/functions.php';

/** Píldoras de etiqueta del plato ("favorito", "picante"…) */
function chips_producto(array $p): string {
    $out = '';
    if (tiene_oferta($p)) {
        $pct = (int)round((1 - $p['precio_oferta'] / $p['precio']) * 100);
        $out .= '<span class="chip chip-rojo">-' . $pct . '%</span>';
    }
    foreach (array_slice(json_col($p['etiquetas']), 0, 2) as $t) {
        $cls = match ($t) {
            'insignia', 'favorito' => 'chip-rojo',
            'vegetariana'          => 'chip-hoja',
            default                => 'chip-maiz',
        };
        $out .= '<span class="chip ' . $cls . '">' . e($t) . '</span>';
    }
    return $out;
}

/**
 * Tarjeta de plato con dos vías de compra:
 *  · botón "Agregar" → POST directo (funciona incluso sin JS)
 *  · botón "＋" → abre el modal de adiciones y notas
 */
function dish_card(array $p, bool $reveal = true): void {
    $img       = plato_img($p);
    $precio    = precio_final($p);
    $agotado   = !$p['disponible'];
    $modalData = htmlspecialchars(json_encode([
        'id'     => (int)$p['id'],
        'nombre' => $p['nombre'],
        'desc'   => $p['descripcion'],
        'precio' => $precio,
        'img'    => $img,
    ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
    ?>
    <article class="dish <?= $reveal ? 'reveal' : '' ?> <?= $agotado ? 'dish--agotado' : '' ?>">
      <div class="dish__media">
        <img src="<?= $img ?>" alt="<?= e($p['nombre']) ?>" loading="lazy" width="900" height="700">
        <div class="absolute top-3 left-3 flex gap-1.5"><?= chips_producto($p) ?></div>
      </div>
      <div class="dish__body">
        <h3 class="font-display font-bold text-lg leading-tight"><?= e($p['nombre']) ?></h3>
        <p class="text-[.82rem] text-carbon/60 mt-1.5 leading-relaxed line-clamp-3 flex-1"><?= e($p['descripcion']) ?></p>

        <div class="flex items-end justify-between gap-3 mt-4">
          <div>
            <?php if (tiene_oferta($p)): ?>
              <span class="block text-[.7rem] text-carbon/40 line-through tabular"><?= precio_cop($p['precio']) ?></span>
            <?php endif; ?>
            <span class="font-display font-black text-xl text-rojo tabular"><?= precio_cop($precio) ?></span>
          </div>

          <?php if ($agotado): ?>
            <span class="text-xs font-bold text-carbon/45">No disponible</span>
          <?php else: ?>
            <div class="flex items-center gap-1.5">
              <button type="button" title="Agregar adiciones o una nota"
                      class="w-10 h-10 grid place-items-center rounded-full border border-carbon/15 text-carbon/60 hover:border-rojo hover:text-rojo transition-colors"
                      @click='$store.plato.abrir(<?= $modalData ?>)'>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
              </button>
              <form method="post" action="<?= base_url('carrito.php') ?>" data-add>
                <?= csrf_field() ?>
                <input type="hidden" name="accion" value="add">
                <input type="hidden" name="producto_id" value="<?= (int)$p['id'] ?>">
                <input type="hidden" name="cantidad" value="1">
                <button type="submit" class="btn btn-rojo !h-10 !px-4 !text-[.8rem]">Agregar</button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </article>
    <?php
}

/**
 * Modal único de la página: recibe el plato desde el store de Alpine y permite
 * elegir cantidad, adiciones y una nota para la cocina.
 */
function modal_plato(array $adiciones): void {
    ?>
    <div x-data x-show="$store.plato.abierto" x-cloak
         class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-6"
         @keydown.escape.window="$store.plato.cerrar()">
      <div class="absolute inset-0 bg-carbon/70 backdrop-blur-sm" @click="$store.plato.cerrar()"
           x-transition.opacity></div>

      <form method="post" action="<?= base_url('carrito.php') ?>"
            class="relative w-full sm:max-w-lg bg-crema rounded-t-3xl sm:rounded-3xl shadow-2xl max-h-[92vh] overflow-y-auto"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="add">
        <input type="hidden" name="producto_id" :value="$store.plato.p.id">

        <div class="relative">
          <img :src="$store.plato.p.img" :alt="$store.plato.p.nombre" class="w-full aspect-[16/9] object-cover rounded-t-3xl">
          <button type="button" @click="$store.plato.cerrar()" aria-label="Cerrar"
                  class="absolute top-3 right-3 w-10 h-10 grid place-items-center rounded-full bg-crema/90 hover:bg-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <div class="p-6">
          <h3 class="font-display font-black text-2xl leading-tight" x-text="$store.plato.p.nombre"></h3>
          <p class="text-sm text-carbon/60 mt-2 leading-relaxed" x-text="$store.plato.p.desc"></p>

          <?php if ($adiciones): ?>
            <p class="label mt-6">Adiciones</p>
            <div class="grid sm:grid-cols-2 gap-2">
              <?php foreach ($adiciones as $a): ?>
                <label class="flex items-center gap-3 rounded-xl border border-carbon/12 bg-white px-3 py-2.5 cursor-pointer hover:border-rojo/60 transition-colors has-[:checked]:border-rojo has-[:checked]:bg-rojo/5">
                  <input type="checkbox" name="adiciones[]" value="<?= (int)$a['id'] ?>"
                         class="w-4 h-4 accent-[#B7202A]"
                         @change="$store.plato.toggleAdicion(<?= (int)$a['precio'] ?>, $event.target.checked)">
                  <span class="flex-1 text-[.82rem] font-semibold leading-tight"><?= e($a['nombre']) ?></span>
                  <span class="text-[.78rem] font-bold text-rojo tabular">+<?= precio_cop($a['precio']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <label class="field mt-5">
            <span class="label">Nota para la cocina</span>
            <input type="text" name="nota" class="input" maxlength="160"
                   placeholder="Ej.: sin cebolla, término tres cuartos, aparte el hoga'o…">
          </label>

          <div class="flex items-center gap-4 mt-5">
            <div class="flex items-center rounded-full border border-carbon/15 bg-white h-12">
              <button type="button" class="w-11 h-full grid place-items-center text-xl font-bold text-carbon/60 hover:text-rojo" @click="$store.plato.menos()">−</button>
              <span class="w-8 text-center font-extrabold tabular" x-text="$store.plato.cantidad"></span>
              <button type="button" class="w-11 h-full grid place-items-center text-xl font-bold text-carbon/60 hover:text-rojo" @click="$store.plato.mas()">+</button>
            </div>
            <input type="hidden" name="cantidad" :value="$store.plato.cantidad">
            <button type="submit" class="btn btn-rojo btn-lg flex-1">
              Agregar · <span class="tabular" x-text="$store.plato.totalFmt()"></span>
            </button>
          </div>
        </div>
      </form>
    </div>

    <script>
      document.addEventListener('alpine:init', () => {
        Alpine.store('plato', {
          abierto: false,
          cantidad: 1,
          extras: 0,
          p: { id: 0, nombre: '', desc: '', precio: 0, img: '' },
          abrir(p) {
            this.p = p; this.cantidad = 1; this.extras = 0; this.abierto = true;
            document.body.style.overflow = 'hidden';
          },
          cerrar() { this.abierto = false; document.body.style.overflow = ''; },
          mas()   { if (this.cantidad < 30) this.cantidad++; },
          menos() { if (this.cantidad > 1)  this.cantidad--; },
          toggleAdicion(precio, on) { this.extras += on ? precio : -precio; },
          total() { return (this.p.precio + this.extras) * this.cantidad; },
          totalFmt() { return '$' + this.total().toLocaleString('es-CO'); },
        });
      });
    </script>
    <style>[x-cloak]{display:none !important;}</style>
    <?php
}

/** Barra inferior con el resumen del carrito (móvil) */
function barra_carrito(): void {
    $u = carrito_unidades();
    if ($u === 0) return;
    ?>
    <div class="cart-bar">
      <div class="flex-1 min-w-0">
        <p class="text-[.68rem] uppercase tracking-[.2em] text-crema/50"><?= $u ?> <?= $u === 1 ? 'producto' : 'productos' ?></p>
        <p class="font-display font-black text-lg tabular"><?= precio_cop(carrito_subtotal()) ?></p>
      </div>
      <a href="<?= base_url('carrito.php') ?>" class="btn btn-crema">Ver pedido</a>
    </div>
    <?php
}
