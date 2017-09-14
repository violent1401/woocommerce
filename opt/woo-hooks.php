<?php

//Должна быть пустой
function woocommerce_breadcrumb() {

}

function woocommerce_output_content_wrapper(){
  echo '<section class="content">';
}

function woocommerce_output_content_wrapper_end(){
  echo '</section>';
}

/**
 * Show the product title in the product loop. By default this is an H3.
 */
function woocommerce_template_loop_product_title() {
  $len = mb_strlen(get_the_title());
  if($len > 36){
    echo mb_substr(get_the_title(),0, 36).'...';
  }else{
    echo get_the_title();
  }
}

/**
 * Insert the opening anchor tag for products in the loop.
 */
remove_action('woocommerce_before_shop_loop_item','woocommerce_template_loop_product_link_open');
add_action('woocommerce_before_shop_loop_item','thm_template_loop_product_link_open');
function thm_template_loop_product_link_open() {
  echo '<a href="'.get_the_permalink().'" class="product-item">';
}
/**
 * Insert the opening anchor tag for products in the loop.
 */
remove_action('woocommerce_after_shop_loop_item','woocommerce_template_loop_product_link_close');
add_action('woocommerce_after_shop_loop_item','thm_template_loop_product_link_close');
function thm_template_loop_product_link_close() {
  echo '</a>';
}

// Change number or products per row to 3
add_filter('loop_shop_columns', 'thm_loop_columns');
function thm_loop_columns() {
  return 3;
}

/*add function*/
add_filter( 'woocommerce_currencies', 'add_my_currency' );

function add_my_currency( $currencies ) {
	 $currencies['ABC'] = __( 'Российский рубль', 'woocommerce' );
	 return $currencies;
}

function woocommerce_product_in_stock() {
  global $product;
  if($product->is_in_stock()){
    echo '<span>В наличии</span>';
  }
}

add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );
function woo_remove_product_tabs( $tabs ) {
  if(isset($tabs['additional_information'])){
    $tabs['additional_information']['title'] = 'Характеристики';
  }

  if(isset($tabs['description'])){
    $tabs['description']['priority'] = 20;
  }

  return $tabs;
}

function woo_get_sku(){
  global $product;

  if($product->get_sku()){ ?>
    <span><b><?php _e( 'SKU', 'woocommerce' ) ?>:</b> <?php echo $product->get_sku(); ?></span>
<?php
  }
}

function woo_not_visible_attributs() {
  global $product;
  $attributes = $product->get_attributes();

  foreach ( $attributes as $attribute ) :
  if (!empty($attribute['is_visible']) || ( $attribute['is_taxonomy'] && ! taxonomy_exists( $attribute['name'] ) ) ) {
    continue;
  }
  ?>
  <span><b><?php echo wc_attribute_label( $attribute['name'] ); ?>:</b>
    <?php
        if ( $attribute['is_taxonomy'] ) {
          $values = wc_get_product_terms( $product->id, $attribute['name'], array( 'fields' => 'names' ) );
          echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values );
        } else {
          // Convert pipes to commas and display values
          $values = array_map('trim', explode( WC_DELIMITER, $attribute['value']));
          echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values );
        }
    ?>
  </span>
<?php endforeach;
}



function custom_recent_products_FX($atts) {
    global $woocommerce_loop, $woocommerce;

    extract(shortcode_atts(array(
        'per_page'  => '12',
        'columns'   => '4',
        'orderby' => 'date',
        'order' => 'desc'
    ), $atts));

    $meta_query = $woocommerce->query->get_meta_query();

    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'ignore_sticky_posts'   => 1,
        'posts_per_page' => $per_page,
        'orderby' => $orderby,
        'order' => $order,
        'meta_query' => $meta_query
    );

    ob_start();

    $products = new WP_Query( $args );

    $woocommerce_loop['columns'] = $columns;

    if ( $products->have_posts() ) : ?>
      <?php while ( $products->have_posts() ) : $products->the_post(); ?>

          <?php woocommerce_get_template_part( 'content', 'product-carousel' ); ?>

      <?php endwhile; // end of the loop. ?>

    <?php endif;

    wp_reset_postdata();

    return  ob_get_clean() ;
 }
 add_shortcode('custom_recent_products','custom_recent_products_FX');

function custom_featured_products_FX( $atts ) {
    $atts = shortcode_atts( array(
        'per_page' => '12',
        'columns'  => '4',
        'orderby'  => 'date',
        'order'    => 'desc',
        'category' => '',
        'operator' => 'IN'
    ), $atts );

    $meta_query   = WC()->query->get_meta_query();
    $meta_query[] = array(
        'key'   => '_featured',
        'value' => 'yes'
    );

    $query_args = array(
        'post_type'           => 'product',
        'post_status'         => 'publish',
        'ignore_sticky_posts' => 1,
        'posts_per_page'      => $atts['per_page'],
        'orderby'             => $atts['orderby'],
        'order'               => $atts['order'],
        'meta_query'          => $meta_query
    );

    ob_start();

    $products = new WP_Query( $query_args );

    $woocommerce_loop['columns'] = $atts['columns'];

    if ( $products->have_posts() ) : ?>
      <?php while ( $products->have_posts() ) : $products->the_post(); ?>

          <?php woocommerce_get_template_part( 'content', 'product-carousel' ); ?>

      <?php endwhile; ?>

    <?php endif;

    wp_reset_postdata();

    return  ob_get_clean() ;
}
add_shortcode('custom_featured_products','custom_featured_products_FX');

/**
 * Get the add to cart template for the loop.
 *
 * @subpackage	Loop
 */
function woocommerce_template_loop_add_to_cart( $args = array() ) {
    global $product;
    if ( $product ) {
      $defaults = array(
      'quantity' => 1,
      'class'    => implode( ' ',array_filter(array(
        'btn-add-to-cart',
        'product_type_' . $product->product_type,
        $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
        $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : ''
      ))));
      $args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );
      wc_get_template( 'loop/add-to-cart.php', $args );
    }
}

add_filter( 'woocommerce_add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment' );
function woocommerce_header_add_to_cart_fragment( $fragments ) {
  ob_start();
  ?>
  <div class="blockcart-cnt cart-contents">
    <a class="blockcart" href="<?php echo WC()->cart->get_cart_url(); ?>">
      <div class="cart-title">В корзине:</div>
      <div class="cart-count">
        <?php echo '<span>'.sprintf(_n('%d </span> товар', '%d </span> товаров',WC()->cart->get_cart_contents_count()),WC()->cart->get_cart_contents_count()); ?>
        - <?php echo WC()->cart->get_cart_total(); ?>
      </div>
    </a>
  </div>
  <?php

  $fragments['div.cart-contents'] = ob_get_clean();

  return $fragments;
}

/**
 * Show the subcategory title in the product loop.
 */
function woocommerce_template_loop_category_title($category){
	echo $category->name;
}

// Get all subcategorys by parent ID category
function woocommerce_subcats_from_parentcat($parent_cat_ID) {
	$args = array(
		 'hierarchical' => 1,
		 'show_option_none' => '',
		 'hide_empty' => 0,
		 'parent' => $parent_cat_ID,
		 'taxonomy' => 'product_cat'
	);
	$subcats = get_categories($args);

	echo '<ul class="wooc_sclist">';
		foreach ($subcats as $sc) {
			$link = get_term_link( $sc->slug, $sc->taxonomy );
				echo '<li><a href="'. $link .'">'.$sc->name.'</a></li>';
		}
	echo '</ul>';
}

remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
remove_action( 'woocommerce_after_single_product_summary','woocommerce_output_product_data_tabs',10 );



/* Remove Checkout Fields */
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
function custom_override_checkout_fields( $fields ) {
  unset($fields['billing']['billing_company']);
  unset($fields['billing']['billing_postcode']);
  unset($fields['billing']['billing_country']);
  unset($fields['billing']['billing_address_2']);

  $fields['billing']['billing_city']['class'][0] = 'form-row-last';
  $fields['billing']['billing_address_1']['custom_attributes'] = array('autocomplete'=>'off');

  $order = array(
    "billing_first_name",
    "billing_last_name",
    "billing_email",
    "billing_phone",
    "billing_state",
    "billing_city",
    "billing_address_1",
  );

  foreach($order as $field){
    $ordered_fields[$field] = $fields["billing"][$field];
  }

  $fields["billing"] = $ordered_fields;

  $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
  $chosen_shipping = $chosen_methods[0];
  if ($chosen_shipping == 'local_pickup') {
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_address_1']);
  }

  return $fields;
}

/**
 * Add new register fields for WooCommerce registration.
 *
 * @return string Register fields HTML.
 */
add_action( 'woocommerce_register_form_start', 'wooc_extra_register_fields' );
function wooc_extra_register_fields() {
	?>
	<p class="form-row form-row-first">
		<label for="reg_billing_first_name"><?php _e( 'First name', 'woocommerce' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" required>
	</p>
	<p class="form-row form-row-last">
		<label for="reg_billing_last_name"><?php _e( 'Last name', 'woocommerce' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" required>
	</p>
	<div class="clear"></div>
	<p class="form-row form-row-wide">
		<label for="reg_billing_phone"><?php _e( 'Phone', 'woocommerce' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php if ( ! empty( $_POST['billing_phone'] ) ) esc_attr_e( $_POST['billing_phone'] ); ?>" required>
	</p>

	<?php
}

/**
 * Validate the extra register fields.
 *
 * @param  string $username          Current username.
 * @param  string $email             Current email.
 * @param  object $validation_errors WP_Error object.
 *
 * @return void
 */
add_action( 'woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3 );
function wooc_validate_extra_register_fields( $username, $email, $validation_errors ) {
	if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
		$validation_errors->add( 'billing_first_name_error', __( '<strong>Error</strong>: First name is required!', 'woocommerce' ) );
	}
	if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
		$validation_errors->add( 'billing_last_name_error', __( '<strong>Error</strong>: Last name is required!.', 'woocommerce' ) );
	}
	if ( isset( $_POST['billing_phone'] ) && empty( $_POST['billing_phone'] ) ) {
		$validation_errors->add( 'billing_phone_error', __( '<strong>Error</strong>: Phone is required!.', 'woocommerce' ) );
	}
  if (!isset($_POST['password']) || empty($_POST['password']) || mb_strlen($_POST['password']) < 6 ) {
		$validation_errors->add('password', 'Введите пароль не менее 6 символов');
	}
}

/**
 * Save the extra register fields.
 *
 * @param  int  $customer_id Current customer ID.
 *
 * @return void
 */

add_action( 'woocommerce_created_customer', 'wooc_save_extra_register_fields' );
function wooc_save_extra_register_fields($customer_id) {
	if ( isset( $_POST['billing_first_name'] ) ) {
		// WordPress default first name field.
		update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );

		// WooCommerce billing first name.
		update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
	}

	if ( isset( $_POST['billing_last_name'] ) ) {
		// WordPress default last name field.
		update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );

		// WooCommerce billing last name.
		update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
	}

	if ( isset( $_POST['billing_phone'] ) ) {
		// WooCommerce billing phone
		update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
	}
}

/*
*Reduce the strength requirement on the woocommerce password.
*
* Strength Settings
* 3 = Strong (default)
* 2 = Medium
* 1 = Weak
* 0 = Very Weak / Anything
*/
function change_woocommerce_min_strength_requirement( $strength ) {
  return 1;
}
add_filter( 'woocommerce_min_password_strength', 'change_woocommerce_min_strength_requirement' );

add_action('init', 'change_role_name');
function change_role_name() {
	global $wp_roles;

	if (!isset($wp_roles))
		$wp_roles = new WP_Roles();

	$wp_roles->roles['customer']['name'] = 'Покупатель';
	$wp_roles->role_names['customer'] = 'Покупатель';
	$wp_roles->roles['shop_manager']['name'] = 'Менеджер магазина';
	$wp_roles->role_names['shop_manager'] = 'Менеджер магазина';
}

add_filter('editable_roles', 'exclude_editor_role');
function exclude_editor_role($roles) {
	unset($roles['author']);
	unset($roles['subscriber']);
	unset($roles['editor']);
	unset($roles['contributor']);
	return $roles;
}

function wc_ninja_remove_password_strength() {
	if ( wp_script_is( 'wc-password-strength-meter', 'enqueued' ) ) {
		wp_dequeue_script( 'wc-password-strength-meter' );
	}
}
add_action( 'wp_print_scripts', 'wc_ninja_remove_password_strength', 100 );

add_action( 'woocommerce_bacs_account_fields', 'nolo_custom_field_display_cust_order_meta', 10, 1 );

function nolo_custom_field_display_cust_order_meta($fields){
  $fields['ogrn_number'] = array(
    'label' => 'ОГРН',
    'value' => '1027739207462'
  );
  $order = array(
    "account_number",
    "sort_code",
    "ogrn_number",
    "iban",
    "bic",
  );

  foreach($order as $field){
    $ordered_fields[$field] = $fields[$field];
  }

  return $ordered_fields;
}
