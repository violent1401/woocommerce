<?php
// Trim admin interface
add_action('init', 'moon_unregister_tags');
add_action('login_head', 'custom_login');
add_action('admin_head', 'trim_admin_interface');
add_action('admin_head', 'remove_adm_tab_help');
add_action('admin_menu', 'remove_menus', 10);
add_filter('admin_bar_menu', 'clear_admin_bar_menu', 5);

function trim_admin_interface(){
  //Remove call to core_update_footer filter.
  add_filter( 'admin_footer_text', '__return_false' );
  remove_filter('update_footer', 'core_update_footer' );

  //Hides the dashboard menu link.
  remove_menu_page( 'index.php' );
  remove_menu_page( 'separator1' );
}

//Hides help tabs.
function remove_adm_tab_help(){
  $screen = get_current_screen();
  if($screen){
    $screen->remove_help_tabs();
  }
}

//trim admin bar
function clear_admin_bar_menu( $wp_admin_bar ) {

  remove_action( 'admin_bar_menu', 'wp_admin_bar_wp_menu', 10 );

  $user_id = get_current_user_id();

  if ( ! $user_id ) {
    return;
  }

  $user_id = get_current_user_id();
  $current_user = wp_get_current_user();
  $profile_url  = get_edit_profile_url( $user_id );

  remove_action( 'admin_bar_menu', 'wp_admin_bar_my_account_item', 7 );

  $wp_admin_bar->add_menu(array(
      'id'     => 'my-account',
      'parent' => 'top-secondary',
      'title'  => $current_user->display_name,
      'href'   => $profile_url,
      'meta'   => array('class' => 'with-avatar'),
  ));
}

// remove wp logo on login page
function custom_login() {
  echo '<style>
  body{background-image:url('.TEMPLATE_URI.'/img/login/bg.png)}
  #loginform{box-shadow:0 0 260px #fff;background-image:url('.TEMPLATE_URI.'/img/login/gradient.png)}
  .login h1{display:none}
  .login #nav a,.login #backtoblog a{color:#f5f5f5}
  .login #nav a:hover,.login #backtoblog a:hover{color:#fff}
  </style>';
}

// Отключить пункт меню
function remove_menus(){
  //remove_menu_page( 'edit.php' );                   //Posts
  //remove_menu_page( 'upload.php' );                 //Media
  //remove_menu_page( 'edit.php?post_type=page' );    //Pages
  //remove_menu_page( 'edit-comments.php' );          //Comments
  //remove_menu_page( 'themes.php' );                 //Appearance
  //remove_menu_page( 'plugins.php' );                //Plugins
  //remove_menu_page( 'users.php' );                  //Users
  //remove_menu_page( 'tools.php' );                  //Tools
  //remove_menu_page( 'options-general.php' );        //Settings
  //remove_menu_page( 'edit.php?post_type=acf' );     //Advanced  custom fields
  //remove_menu_page( 'wpcf7' );                      //Contact form 7
  //remove_submenu_page('options-general.php','options-permalink.php');
  //remove_submenu_page('options-general.php','disable_comments_settings');
  remove_menu_page('wpseo_dashboard');
  remove_submenu_page('themes.php','themes.php'); // Выбор темы
}

// Удалить возможность добавлять метки у записей
function moon_unregister_tags() {
  unregister_taxonomy_for_object_type('post_tag', 'post');
}
