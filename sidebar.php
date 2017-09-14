<?php if ( is_active_sidebar( 'sidebar' ) ) { ?>
<div id="sidebar" role="complementary">
  <div class="vertical-nav block">
    <?php dynamic_sidebar( 'sidebar' ); ?>
  </div>
</div>
<?php } ?>