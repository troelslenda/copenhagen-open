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
      <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
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
    case 'regstart':
      return $match_data['data']['registration_starts'];
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
$match_key = 2611;

//delete_option('match_data');


if (empty(get_option('match_data'))) {

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
    if ($entry == 'data') {
      $api_data[$entry] = current($api_data[$entry]);
    }
  }

  $api_data['fetched_from_api'] = new DateTime();
  update_option('match_data', $api_data);
}






