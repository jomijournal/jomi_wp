<?php while (have_posts()) : the_post(); ?>
  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li class="active"><a href="#thoughts" role="tab" data-toggle="tab">Send your Thoughts</a></li>
    <li><a href="#request" role="tab" data-toggle="tab">Request a Topic</a></li>
    <li><a href="#propose" role="tab" data-toggle="tab">Propose a Procedure</a></li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div class="tab-pane active" id="thoughts"><?php the_block('thoughts'); ?></div>
    <div class="tab-pane" id="request"><?php the_block('request'); ?></div>
    <div class="tab-pane" id="propose"><?php the_block('propose'); ?></div>
  </div>

<?php endwhile; ?>