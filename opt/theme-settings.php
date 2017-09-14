<?php
define('WPTUTS_SHORTNAME', 'wpthm'); // used to prefix the individual setting field id see wptuts_options_page_fields()
define('WPTUTS_PAGE_BASENAME', 'wpthm-settings'); // the settings page slug

function wpthm_settings_page() {
?>
    <div class="wrap">
        <div class="icon32" id="icon-options-general"></div>
        <h2>Настройки темы</h2>

        <form action="options.php" method="post">
	        <?php
              settings_fields("wpthm_section");
	            do_settings_sections("wpthm_theme_options");      
	            submit_button(); 
	        ?>
        </form>
    </div>
<?php }

function wpthm_add_menu(){
    $wptuts_settings_page = add_theme_page('Дополнительные настройки темы', 'Настройки', 'manage_options', WPTUTS_PAGE_BASENAME, 'wpthm_settings_page');          
}
add_action( 'admin_menu', 'wpthm_add_menu' );



function display_phone_element() {?>
  <input type="text" name="numphone" id="numphone" value="<?php echo get_option('numphone'); ?>" style="width:400px" placeholder="Номер телефона" />

<?php
}

function display_theme_panel_fields() {
	add_settings_section("wpthm_section", "Все настройки", null, "wpthm_theme_options");

	add_settings_field("twitter_url", "Номер телефона", "display_phone_element", "wpthm_theme_options", "wpthm_section");

  register_setting("wpthm_section", "numphone");
}

add_action("admin_init", "display_theme_panel_fields");
?>