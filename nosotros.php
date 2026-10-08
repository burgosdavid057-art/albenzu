<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/arte.php';

$page_title = 'La casa · ' . SITE_NAME;
$page_desc  = 'La historia de Albenzu: Alicia y Benjamín Zuluaga, una olla de frijoles y la Avenida 80 desde 1982.';
$active     = 'nosotros.php';
$anios      = (int)date('Y') - SITE_FUNDADO;

include __DIR__ . '/includes/header.php';
?>

<section class="paper relative overflow-hidden">
  <div class="mx-auto max-w-4xl px-5 pt-16 pb-12 text-center">
    <p class="eyebrow justify-center mb-6">Desde <?= SITE_FUNDADO ?></p>
    <h1 class="h-display text-[clamp(2.8rem,8vw,5rem)] leading-[.94]">
      Una olla,<br>una familia,<br><em class="h-script text-rojo">una esquina.</em>
    </h1>
    <p class="mt-8 text-lg text-carbon/70 max-w-xl mx-auto leading-relaxed">
      <?= e(SITE_HISTORIA) ?>
    </p>
  </div>

  <div class="mx-auto max-w-5xl px-5 pb-16">
    <div class="rounded-3xl overflow-hidden shadow-2xl reveal"><?= arte_fachada('w-full h-auto') ?></div>
  </div>
</section>

<section class="bg-white border-y border-carbon/8">
  <div class="mx-auto max-w-5xl px-5 py-18 lg:py-24">
    <div class="grid md:grid-cols-3 gap-10">
      <?php
      $hitos = [
          ['1982', 'Abre la casa', 'Alicia y Benjamín Zuluaga montan un comedor pequeño en la Avenida 80. La receta del frijol es la de la casa, sin ajustes para restaurante.'],
          ['Los 90', 'Se corre la voz', 'La bandeja empieza a llenar los almuerzos del sector. Se amplía el salón y entra la parrilla.'],
          ['Hoy', 'La misma olla', 'Tres generaciones después seguimos cocinando el frijol igual. Lo nuevo es que ahora también te lo llevamos a la casa.'],
      ];
      foreach ($hitos as $h): ?>
        <div class="reveal">
          <p class="font-display font-black text-5xl text-rojo/20 leading-none"><?= e($h[0]) ?></p>
          <h3 class="font-display font-bold text-xl mt-4"><?= e($h[1]) ?></h3>
          <p class="text-sm text-carbon/60 mt-3 leading-relaxed"><?= e($h[2]) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="paper-dark text-crema">
  <div class="mx-auto max-w-5xl px-5 py-20 text-center">
    <div class="w-20 h-20 mx-auto text-maiz mb-8"><?= arte_olla('w-full h-full') ?></div>
    <h2 class="h-display text-4xl sm:text-5xl leading-tight max-w-2xl mx-auto">
      “El frijol no se apura.<br><em class="h-script text-maiz">Se espera.</em>”
    </h2>
    <p class="text-crema/60 mt-6 max-w-lg mx-auto leading-relaxed">
      Es lo que decía don Benjamín cada vez que alguien preguntaba por el secreto. <?= $anios ?> años
      después seguimos sin apurar nada.
    </p>
    <a href="<?= base_url('carta.php') ?>" class="btn btn-crema btn-lg mt-10" data-magnetic>Ver la carta</a>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
