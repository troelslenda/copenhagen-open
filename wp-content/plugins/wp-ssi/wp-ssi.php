<?php

  /**
   * Shoot'n'Score It
   *
   * @wordpress-plugin
   * Plugin Name:     Shoot'n'Score It
   * Plugin URI:      troelslenda.com/wp-ssi
   * Description:     Fetches match data from Shoot'n'Score It and provides it as shortcode.
   * Version:         1.0.0
   * Author:          Troels Lenda
   * Author URI:      http://troelslenda.com
   * Text Domain:     wpdevsclub
   * Requires WP:     3.5
   * Requires PHP:    5.3
   */
/*
Plugin Name: Shoot'n'Score It
*/

// Creating the widget
class ssi_widget extends WP_Widget {

  function __construct() {
    parent::__construct(
// Base ID of your widget
      'ssi_widget',

// Widget name will appear in UI
      __('WPBeginner Widget', 'wpb_widget_domain'),

// Widget description
      array( 'description' => __( 'Sample widget based on WPBeginner Tutorial', 'wpb_widget_domain' ), )
    );
  }

// Creating widget front-end
// This is where the action happens
  public function widget( $args, $instance ) {

    $api_data = get_option('match_data');



    $title = apply_filters( 'widget_title', $instance['title'] );
// before and after widget arguments are defined by themes
    echo $args['before_widget'];
    if ( ! empty( $title ) )
      echo $args['before_title'] . $title . $args['after_title'];

// This is where you run the code and display the output
    echo __($api_data['data']['name'], 'ssi_widget_domain');


    echo $args['after_widget'];
  }

// Widget Backend
  public function form( $instance ) {
    if ( isset( $instance[ 'title' ] ) ) {
      $title = $instance[ 'title' ];
    }
    else {
      $title = __( 'New title', 'wpb_widget_domain' );
    }
// Widget admin form
    ?>
    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
    </p>
    <?php
  }

// Updating widget replacing old instances with new
  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
    return $instance;
  }
} // Class wpb_widget ends here


// Register and load the widget
function ssi_load_widget() {
  register_widget('ssi_widget');
}
add_action( 'widgets_init', 'ssi_load_widget' );









/**
 * Returns the match data.
 *
 * @param $atts
 * @return string
 */
function match_data_func( $atts ){

  if (empty($atts['prop'])) return;

  if (empty(get_option('match_data'))) return;

  $match_data = get_option('match_data');

  switch ($atts['prop']) {
    case 'all':
      return d($match_data);
    case 'name':
      return $match_data['data']['name'];
    case 'ssi_reg_start':
      $date = new DateTime($match_data['data']['registration_starts']);
      return date_i18n(
        get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
        $date->getTimestamp());
    case 'ssi_match_start':
      $date = new DateTime($match_data['data']['starts']);
      return date_i18n(
        get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
        $date->getTimestamp());
    case 'competitor_list':
      return ipsc_match_competitorlist($match_data['competitors']);
    default:
      // Try to get property directly.
      return $match_data['data'][$atts['prop']];

  }
}
add_shortcode('match', 'match_data_func');




function ipsc_match_competitorlist($competitors) {
  //$list = new Competitor_List_Table();
  return 'fisk';

  var_dump($competitors);
}



/*
 * Implement some sort of CRON to update match_data option.
 */
$match_key = get_option('ssi_match_id');

if (get_option('match_data')['data']['pk'] != $match_key) {

  $parts = array(
    'data' => '',
    'competitors' => 'competitors',
    'squads' => 'squads',
    'stats' => 'stats',
    'stages' => 'stages',
  );

  foreach ($parts as $entry => $url_part) {
    $request_url = 'https://shootnscoreit.com/api/ipsc/match/' . $match_key . '/'. $url_part;
    $api_response = wp_remote_get($request_url);
    $api_data[$entry] = json_decode( wp_remote_retrieve_body($api_response), true);
    if ($entry == 'data' && isset($api_data[$entry])) {
      $api_data[$entry] = current($api_data[$entry]);
    }
  }

  $api_data['fetched_from_api'] = new DateTime();
  update_option('match_data', $api_data);
}









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

