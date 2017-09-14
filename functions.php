<?php
define("TEMPLATE_URI", get_template_directory_uri());

function setup_theme_support() {
  add_theme_support( 'html5', array(
    'search-form',
    'comment-list',
    'gallery',
    'caption'
  ));

  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');

  remove_action('wp_head','wp_generator');
  remove_action('wp_head','wlwmanifest_link');
  remove_action('wp_head','rsd_link');
  remove_action('wp_head','rest_output_link_wp_head',10);
  remove_action('wp_head','wp_oembed_add_discovery_links');
  remove_action('wp_head','wp_shortlink_wp_head', 10, 0);
  remove_action('wp_head', 'feed_links_extra', 3);
  remove_action('welcome_panel', 'wp_welcome_panel');
  remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');
  add_filter( 'emoji_svg_url', '__return_false' );

  register_nav_menus(array('main_nav' => 'Главное меню',));

  show_admin_bar(false);

  //support feed
  //add_theme_support('automatic-feed-links');
  // default thumb size
  //set_post_thumbnail_size(260, 260, true);
  //add_image_size( 'simple_featured', 1140, 1140, true);
  //load_theme_textdomain( 'textdomain', TEMPLATE_URI.'/languages' );
  //add_theme_support( 'custom-background', array('default-color' => '#ffffff',));
  //if(is_admin()){
    //Настройки страница темы
    //include 'opt/theme-settings.php';
  //}
  /*add_theme_support( 'custom-header',array(
	'flex-width'    => false,
	'width'         => 264,
	'flex-height'   => false,
	'height'        => 78,
	'default-image' => TEMPLATE_URI.'/img/logo.png',
  ));*/

  // Custom theme for admin interface
  include 'opt/trim-admin.php';
  // Widgets and sidebars
  include('opt/theme-widgets.php');
  // Ru translit slug
  include('opt/ru-translit.php');
  // Disabling WP REst Api
  include('opt/woo-hooks.php');
}

add_action('after_setup_theme','setup_theme_support');

// Now the CSS
function theme_style() {

  //wp_enqueue_style('font-pt-sans', '//fonts.googleapis.com/css?family=PT+Sans:400,700&subset=latin,cyrillic');

  wp_enqueue_style('bs', TEMPLATE_URI.'/css/bootstrap.min.css', array(), null, 'all' );
  wp_enqueue_style('main-style', TEMPLATE_URI.'/style.css', array(), null, 'all' );
}

// Now the JS
function theme_scripts() {
  // Deregister the included library
  wp_deregister_script('jquery');
  wp_deregister_script('jquery-migrate');

  wp_register_script('jquery', TEMPLATE_URI.'/js/jquery.min.js', array(), null, true );
  wp_enqueue_script('popper', TEMPLATE_URI.'/js/popper.min.js', array('jquery'), null);
  wp_enqueue_script('bootstrap',TEMPLATE_URI.'/js/bootstrap.min.js', array('jquery'),null);
  wp_enqueue_script('themejs',     TEMPLATE_URI.'/js/scripts.js', array('jquery'), null);

  wp_enqueue_script('gmap', '//maps.googleapis.com/maps/api/js?key='.get_option('googleapi_key'), array('themejs'), null);
  /*
  if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
    wp_enqueue_script( 'comment-reply' );
  }
  */
}

add_action('wp_print_styles', 'theme_style');
add_action('wp_enqueue_scripts', 'theme_scripts');

// Menu output mods
class bootstrap_walker extends Walker_Nav_Menu {

    function start_el(&$output, $object, $depth = 0, $args = Array(), $current_object_id = 0) {

        global $wp_query;
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $dropdown = $args->has_children && $depth == 0;

        $class_names = $value = '';

        // If the item has children, add the dropdown class for bootstrap
        if ( $dropdown ) {
            $class_names = "dropdown ";
        }

        $classes = empty( $object->classes ) ? array() : (array) $object->classes;

        $class_names .= join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $object ) );
        $class_names = ' class="nav-item '. esc_attr( $class_names ) . '"';

        $output .= $indent . '<li id="menu-item-'. $object->ID . '"' . $value . $class_names .'>';

        if ( $dropdown ) {
            $attributes = ' href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"';
        } else {
            $attributes  = ! empty( $object->attr_title ) ? ' title="'  . esc_attr( $object->attr_title ) .'"' : '';
            $attributes .= ! empty( $object->target )     ? ' target="' . esc_attr( $object->target     ) .'"' : '';
            $attributes .= ! empty( $object->xfn )        ? ' rel="'    . esc_attr( $object->xfn        ) .'"' : '';
            $attributes .= ! empty( $object->url )        ? ' href="'   . esc_attr( $object->url        ) .'"' : '';
        }

        $item_output = $args->before;
        $item_output .= '<a'. $attributes .' class="nav-link">';
        $item_output .= $args->link_before .apply_filters( 'the_title', $object->title, $object->ID );
        $item_output .= $args->link_after;

        // if the item has children add the caret just before closing the anchor tag
        if ( $dropdown ) {
            $item_output .= ' <b class="caret"></b>';
        }
        $item_output .= '</a>';

        $item_output .= $args->after;

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $object, $depth, $args );
    } // end start_el function

    function start_lvl(&$output, $depth = 0, $args = Array()) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class='dropdown-menu' role='menu'>\n";
    }

    function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ){
        $id_field = $this->db_fields['id'];
        if ( is_object( $args[0] ) ) {
            $args[0]->has_children = ! empty( $children_elements[$element->$id_field] );
        }
        return parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }
}

// Add Twitter Bootstrap's standard 'active' class name to the active nav link item
function add_active_class($classes, $item) {
  if(in_array('current-menu-item', $classes)) {
    $classes[] = "active";
  }
  return $classes;
}
add_filter('nav_menu_css_class', 'add_active_class', 10, 2 );

// display the main menu bootstrap-style
// this menu is limited to 2 levels (that's a bootstrap limitation)
function display_main_menu() {
  wp_nav_menu(
    array(
      'theme_location' => 'main_nav', /* where in the theme it's assigned */
      'menu' => 'main_nav', /* menu name */
      'menu_class' => 'navbar-nav',
      'container' => false, /* container class */
      'depth' => 2,
      'walker' => new bootstrap_walker(),
    )
  );
}

/*
  A function used in multiple places to generate the metadata of a post.
*/
function display_post_meta() {
?>
  <ul class="meta text-muted list-inline">
    <li><?php the_date();?></li>
    <?php edit_post_link('<span title="'.__( 'Edit').'" class="glyphicon glyphicon-pencil"></span>', '<li>', '</li>'); ?>
  </ul>
<?php
}

function page_navi() {
  global $wp_query;
  if (get_next_posts_link() || get_previous_posts_link()) { ?>
    <nav class="block">
      <ul class="pager pager-unspaced">
        <li class="previous"><?php next_posts_link("&laquo; " . __('Older posts')); ?></li>
        <li class="next"><?php previous_posts_link(__('Newer posts') . " &raquo;"); ?></li>
      </ul>
    </nav>
  <?php } ?>

    <?php
}

function display_post($multiple_on_page) { ?>

  <article id="post-<?php the_ID(); ?>" <?php post_class("block"); ?> role="article">

    <header>

        <?php if ($multiple_on_page) : ?>
        <div class="article-header">
            <h2 class="h1"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
        </div>
        <?php else: ?>
        <div class="article-header">
            <h1><?php the_title(); ?></h1>
        </div>
        <?php endif ?>

        <?php if (has_post_thumbnail()) { ?>
        <div class="featured-image">
            <?php if ($multiple_on_page) : ?>
            <a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail('simple_boostrap_featured'); ?></a>
            <?php else: ?>
            <?php the_post_thumbnail('simple_featured'); ?>
            <?php endif ?>
        </div>
        <?php } ?>

        <?php display_post_meta() ?>

    </header>

    <section class="post_content">
        <?php
        if ($multiple_on_page) {
            the_excerpt();
        } else {
            the_content();
            wp_link_pages();
        }
        ?>
    </section>

    <footer>
        <?php the_tags('<p class="tags">', ' ', '</p>'); ?>
    </footer>

  </article>

<?php }

function main_classes() {
    $nbr_sidebars = (is_active_sidebar('sidebar-left') ? 1 : 0) + (is_active_sidebar('sidebar-right') ? 1 : 0);
    $classes = "";
    if ($nbr_sidebars == 0) {
        $classes .= "col-sm-8 col-md-push-2";
    } else if ($nbr_sidebars == 1) {
        $classes .= "col-md-8";
    } else {
        $classes .= "col-md-6";
    }
    if (is_active_sidebar( 'sidebar-left' )) {
        $classes .= " col-md-push-".($nbr_sidebars == 2 ? 3 : 4);
    }
    echo $classes;
}

function disable_wp_emojicons() {
  // all actions related to emojis
  remove_action( 'admin_print_styles', 'print_emoji_styles' );
  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
  remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
  remove_action( 'wp_print_styles', 'print_emoji_styles' );
  remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
  remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
  remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
}
add_action( 'init', 'disable_wp_emojicons' );

// Replaces the excerpt "more" text by a link
function new_excerpt_more($more) {
  global $post;
	return '<a class="moretag" href="'. get_permalink($post->ID) . '" title="Подробнее">...</a>';
}
add_filter('excerpt_more', 'new_excerpt_more');

// Dasable social media filds in user profile
function hide_profile_fields($contactmethods) {
	return array();
}
add_filter('user_contactmethods','hide_profile_fields',10,1);

/*
//добавляем тип поста
add_action( 'init', 'add_post_type' );

function add_post_type() {
  register_post_type('projects', array(
      'labels' => array(
          'name' => 'Проекты',
          'add_new' => 'Добавить',
          'singular_name' => 'Проекты',
          'add_new' => 'Добавить',
          'add_new_item' => 'Добавление проекта',
          'edit_item' => 'Редактирование проекта',
          'new_item' => 'Добавление проекта',
          'all_items' => 'Все проекты',
          'view_item' => 'Просмотреть на сайте',
          'search_items' => 'Найти',
      ),
      'public' => true,
      'has_archive' => true,
      'menu_icon' => 'dashicons-archive',
      'show_ui' => true,
      'menu_position' => 8,
      'capability_type' => 'post',
      'hierarchical' => false,
      'query_var' => true,
      //перносит ссылку на второй уровень, первый уровень становится projects(пр. example.com/projects/post_name)
      //'rewrite' => array('slug'=>'projects'),
      'supports' => array(
          'title',
          'editor',
          'thumbnail'
      )
  ));

}

// добавляем тип поста
add_action('init', 'add_post_taxonomies');

// Custom Taxonomy
function add_post_taxonomies() {
  register_taxonomy( 'catprojects', array( 'projects', 'post' ),
    array(
      'labels' => array(
        'name'              => 'Тип проекта',
        'singular_name'     => 'Тип проекта',
        'search_items'      => 'Search Animal Families',
        'all_items'         => 'Все типы проектов',
        'edit_item'         => 'Редактирование типа проекта',
        'update_item'       => 'Обновить тип проекта',
        'add_new_item'      => 'Добавить тип проекта',
        'new_item_name'     => 'Название тип проекта',
        'menu_name'         => 'Типы проектов',
      ),
      'hierarchical' => true,
      'sort' => true,
      'args' => array( 'orderby' => 'term_order' ),
      'rewrite' => array( 'slug' => 'catprojects' ),
      'show_admin_column' => true
    )
  );
}
*/

/*
add_action('admin_menu', 'mt_add_pages');

function mt_add_pages() {
  add_menu_page('Меню', 'Меню', 'manage_options', 'nav-menus.php', null, 'dashicons-menu',40 );
  add_menu_page('Настройки сайта', 'Настройки сайта', 'manage_options', 'themes.php?page=wpthm-settings', null, '',90 );
}
*/

/*
// Запрет на удаление некоторых постов/страниц
function restrict_post_deletion($post_ID) {
    $restricted_pages = array(84,27,34);
    if(in_array($post_ID, $restricted_pages)){
      exit('Эту страницу нельзя удалить!');
    }
}
add_action('wp_trash_post', 'restrict_post_deletion', 10, 1);
add_action('before_delete_post', 'restrict_post_deletion', 10, 1);
*/

/*add_shortcode( 'getphone' , 'getnumphone' );
function getnumphone(){
  return get_field('phone');
}
*/

//Добавление API ключа Google Maps в административной панели Настройки -> Чтение

function callback_apikey(){
  echo "<input class='regular-text' type='text' name='googleapi_key' value='". esc_attr(get_option('googleapi_key'))."'>";
}

function google_api(){
  add_settings_field('google_key','Google Maps API Key','callback_apikey','reading');
  register_setting('reading','googleapi_key');
}

add_action('admin_init', 'google_api');

function googleapi_admin($api) {
  $api['key'] = get_option('googleapi_key');
  return $api;
}

add_filter('acf/fields/google_map/api', 'googleapi_admin');

if(!is_admin()){
  remove_action('wp_head', 'wp_print_scripts');
  remove_action('wp_head', 'wp_print_head_scripts', 9);
  remove_action('wp_head', 'wp_enqueue_scripts', 1);
  
  add_action('wp_footer', 'wp_print_scripts', 5);
  add_action('wp_footer', 'wp_enqueue_scripts', 5);
  add_action('wp_footer', 'wp_print_head_scripts', 5);

}