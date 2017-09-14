<?php

function theme_register_sidebars() {
  register_sidebar(array(
      'id' => 'sidebar',
      'name' => __('Right Sidebar'),
      'description' => 'Используется на страницах',
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h4 class="widgettitle">',
      'after_title' => '</h4>',
  ));
}
add_action( 'widgets_init', 'theme_register_sidebars' );

// unregister all widgets
function unregister_default_widgets() {
  unregister_widget('WP_Widget_Pages');
  unregister_widget('WP_Widget_Calendar');
  unregister_widget('WP_Widget_Archives');
  unregister_widget('WP_Widget_Links');
  unregister_widget('WP_Widget_Meta');
  unregister_widget('WP_Widget_Search');
  unregister_widget('WP_Widget_Text');
  unregister_widget('WP_Widget_Categories');
  unregister_widget('WP_Widget_Recent_Posts');
  unregister_widget('WP_Widget_Recent_Comments');
  unregister_widget('WP_Widget_RSS');
  unregister_widget('WP_Widget_Tag_Cloud');
  unregister_widget('WP_Nav_Menu_Widget');
  unregister_widget('Twenty_Eleven_Ephemera_Widget');
}

add_action('widgets_init', 'unregister_default_widgets', 11);

?>