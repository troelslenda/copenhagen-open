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

if ( is_admin() ) {
  // We are in admin mode
  require_once( dirname(__file__).'/wp-ssi-admin.php' );
}

require_once( dirname(__file__).'/wp-ssi-widget.php' );


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
  $prop = substr($atts['prop'], 4);
  switch ($atts['prop']) {
    case 'ssi_registration_starts':
    case 'ssi_starts':
    case 'ssi_ends':
      return ssi_get_date_time(new DateTime($match_data['data'][$prop]), $atts);
    case 'ssi_last_update':
      return ssi_get_date_time($match_data['fetched_from_api'], $atts);
    // Treat 0 as not set.
    case 'ssi_number_of_stages':
    case 'ssi_minimum_rounds':
      if ($match_data['data'][$prop] <= 0) {
        return 'TBA';
      }
      else {
        return $match_data['data'][$prop];
      }
    case 'competitor_list':
      return ipsc_match_competitorlist($match_data['competitors']);
    default:
      // Try to get property directly.
      return $match_data['data'][$prop];

  }
}
add_shortcode('match', 'match_data_func');



function ssi_get_date_time($datetime, $attributes = NULL){
  if (isset($attributes['format']) && $attributes['format'] == 'date_only') {
    $format = get_option( 'date_format' );
  }
  else {
    $format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
  }
  return date_i18n($format, $datetime->getTimestamp());
}

function ipsc_match_competitorlist($competitors) {
  //$list = new Competitor_List_Table();
  return 'fisk';

  var_dump($competitors);
}

register_activation_hook( __FILE__, function(){
  wp_schedule_event( time(), '5min', 'ssi_fetch_api_data' );
});
register_deactivation_hook(__FILE__, function(){
  wp_clear_scheduled_hook('ssi_fetch_api_data');
});





function my_cron_schedules($schedules){
  if(!isset($schedules["5min"])){
    $schedules["5min"] = array(
      'interval' => 5*60,
      'display' => __('Once every 5 minutes'));
  }
  if(!isset($schedules["30min"])){
    $schedules["30min"] = array(
      'interval' => 30*60,
      'display' => __('Once every 30 minutes'));
  }
  return $schedules;
}
add_filter('cron_schedules','my_cron_schedules');



function ssi_fetch_api_data() {
  $match_key = get_option('ssi_match_id');
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
  mail("troelslenda@gmail.com","cronjob",'cronjob ran. tstamp for apidata: ' + $api_data['fetched_from_api']->format('H:i'));
}


/*
 * Implement some sort of CRON to update match_data option.
 */
if (get_option('match_data')['data']['pk'] != get_option('ssi_match_id')) {
  ssi_fetch_api_data();
}









