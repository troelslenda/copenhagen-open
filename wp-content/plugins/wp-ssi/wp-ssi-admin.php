<?php


/**
* Settings page.
*/


function wp_ssi_options_page() {

add_settings_section('wp_ssi_plugin_main', 'Shoot\'n\'Score It ', 'plugin_section_text', 'ssi_plugin');
add_settings_field('ssi_match_id', "Match ID", 'plugin_setting_string', 'ssi_plugin', 'wp_ssi_plugin_main');

?>
<div class="wrap">
  <h2>My Plugin Options</h2>
  <form action="options.php" method="post">
    <?php settings_fields('ssi_plugin_options'); ?>
    <?php do_settings_sections('ssi_plugin'); ?>
    <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
  </form>

</div>

<div class="wrap">
  <h2>My Plugin Options</h2>
  <?php

  $data = get_option('match_data');

  if (isset($data['data'])) {
    echo '<h2>Match data from ' . $data['data']['name'] . ' was fetched</h2>';
    echo '<p>The data was updated last at: ' . $data['fetched_from_api']->format('d-m-y H:i'). '</p>';
    d($data);
  }
  else {
    echo '<h2>No data was retrived from Shoot\'n\'Score It</h2>';
    echo '<p>Your Match key is maybe wrong or was deleted.</p>';
  }




  ?>
</div>
<?php
}

add_action('admin_menu', 'wp_ssi_admin_menu');





function plugin_section_text() {
  echo '<p>Here you have to input the match ID for the match you\'re about to provide shortcodes for.</p>';
}

function plugin_setting_string() {
  echo "<input id='plugin_text_string' name='ssi_match_id' size='40' type='text' value='" . get_option('ssi_match_id'). "' />";
}


function wp_ssi_admin_menu() {
  add_options_page(
    "Shoot'n'Score It",
    "Shoot'n'Score It",
    'manage_options',
    'wp-ssi-plugin',
    'wp_ssi_options_page'
  );
  add_action( 'admin_init', 'register_my_setting' );
}

function register_my_setting() {
  register_setting( 'ssi_plugin_options', 'ssi_match_id');
}












add_action('admin_menu', 'my_admin_menu');

function my_admin_menu() {
  add_menu_page('Neotone framework options', 'Neotone', 'add_users','neo_options', 'overview', get_bloginfo("template_url") .'/images/icon.png');
  add_action( 'admin_init', 'register_mysettings' ); //call register settings function
}

function register_mysettings() {
  register_setting( 'myoption-group', 'my_option_name' );
}

function overview() { ?>

  <div class="wrap">
    <h2> Theme Option Panel</h2>
    <form method="post" action="options.php">
      <?php settings_fields( 'myoption-group' ); ?>
      <input type="text" name="my_option_name" size="80" value="<?php echo get_option('my_option_name'); ?>" />
      <p class="submit">
        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
      </p>
    </form>
  </div>



<?php }

