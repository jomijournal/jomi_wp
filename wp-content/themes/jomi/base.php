<?php
/*
Base is the entry point for most pages. 
*/
?>
<?php get_template_part('templates/head'); ?>
<body <?php body_class(); ?>>

  <!--[if lt IE 8]>
    <div class="alert alert-warning">
      <?php _e('You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.', 'roots'); ?>
    </div>
  <![endif]-->

  <?php
    do_action('get_header');
    // Use Bootstrap's navbar if enabled in config.php
    /*if(!is_front_page()){
      get_template_part('templates/header-top-navbar');
    }*/
    get_template_part('templates/header-top-navbar');
  ?>

  <?php 
  if(is_single()) {?>

  <div class="container">
    <div class="video-area row">
      <div id="chapters" class="col-xs-4">
        <ul></ul>
      </div>
      <div class="video-holder col-xs-8" id="video">
        <div id="wistia" class="wistia_embed" style="width:720px;height:405px;display:none;">&nbsp;</div>
        <script charset="ISO-8859-1" src="//fast.wistia.com/assets/external/E-v1.js"></script>
      </div>
    </div>
  </div>

  <?php } ?>

  <?php if(is_page('support-us')){ ?>
    <?php include roots_template_path(); ?>
  <?php } else { ?>


  <div class="container wrap" role="document">
    <div class="content row">
      <main class="main <?php echo roots_main_class(); ?>" role="main">
        <?php include roots_template_path(); ?>
      </main><!-- /.main -->
      <?php if (roots_display_sidebar()) : ?>
        <aside class="sidebar <?php echo roots_sidebar_class(); ?>" role="complementary">
          <?php 
          if(is_page('about') || is_page('contact') || is_page('pricing') || is_page('area-notification-request')) {
            include about_sidebar_path();
          } else if ( is_single() ) {
            include article_sidebar_path();
          } else {
            include roots_sidebar_path();
          }
          ?>
        </aside><!-- /.sidebar -->
      <?php endif; ?>
    </div><!-- /.content -->

  <?php } ?>

  <?php if(is_front_page()){ ?>
    <?php get_template_part('templates/front', 'footer'); ?>
  <?php } ?>

  </div><!-- /.wrap -->

  <?php if(!is_front_page()){ ?>
    <?php get_template_part('templates/footer'); ?>
  <?php } ?>

</body>
</html>