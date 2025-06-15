<!-- carousel.php -->
<style>
  /* Container sempre com 500px de altura e overflow oculto */
  #banners,
  #banners .carousel-inner,
  #banners .carousel-inner .item {
    height: 500px;
  }
  #banners .carousel-inner {
    position: relative;
    overflow: hidden;
  }

  /* Fade puro */
  .carousel.carousel-fade .item {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    display: block !important;
    opacity: 0;
    transition: opacity .7s ease-in-out;
    transform: none !important;
    z-index: 1;
  }
  .carousel.carousel-fade .item.active {
    opacity: 1;
    position: relative;
    z-index: 1;
  }

  /* classes customizadas para saída e entrada */
  .fade-out {
    opacity: 0 !important;
  }
  .fade-in {
    opacity: 1 !important;
  }

  /* Imagens alinhas pelo topo */
  #banners .carousel-inner .item img {
    width: 100%;
    height: 700px;
    object-fit: cover;
    object-position: top center;
  }

  /* Esconde as setas */
  #banners .carousel-control {
    display: none !important;
  }
</style>

<div id="banners"
     class="carousel slide carousel-fade"
     data-ride="carousel"
     data-interval="7000"
     data-pause="false"
     data-wrap="true">
  <ol class="carousel-indicators">
    <li data-target="#banners" data-slide-to="0" class="active"></li>
    <li data-target="#banners" data-slide-to="1"></li>
    <li data-target="#banners" data-slide-to="2"></li>
  </ol>

  <div class="carousel-inner" role="listbox">
    <div class="item active">
      <img src="image/banner1.jpg" alt="Banner 1">
    </div>
    <div class="item">
      <img src="image/psicologo.jpg" alt="Banner 2">
    </div>
    <div class="item">
      <img src="image/banner3.jpg" alt="Banner 3">
    </div>
  </div>

  <!-- As setas continuam aqui, mas estão escondidas no CSS acima -->
  <a class="left carousel-control" href="#banners" data-slide="prev">
    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
    <span class="sr-only">Anterior</span>
  </a>
  <a class="right carousel-control" href="#banners" data-slide="next">
    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
    <span class="sr-only">Próximo</span>
  </a>
</div>

<script>
  (function($) {
    var duration = 700; // deve bater com o .transition-duration do CSS

    $('#banners').on('slide.bs.carousel', function(e) {
      var $current = $(this).find('.item.active');
      var $next    = $(e.relatedTarget);

      // inicia fade-out no ativo
      $current.addClass('fade-out');

      // após o fade-out, limpa e adiciona fade-in na próxima imagem
      setTimeout(function() {
        $current.removeClass('fade-out');

        // forece o slide do bootstrap
        $next.addClass('fade-in');
      }, duration);

      // após fade-in, remove a classe extra
      setTimeout(function() {
        $next.removeClass('fade-in');
      }, duration * 2);
    });
  })(jQuery);
</script>
