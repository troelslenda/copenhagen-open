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
    case 'ssi_competitor_list':
      return ipsc_match_competitorlist($match_data, $atts);
    case 'ssi_competitors_by_country':
      return ipsc_match_competitor_by_country($match_data);
    case 'ssi_competitors_by_team':
      return ipsc_match_competitor_by_team($match_data);
    case 'ssi_competitor_count_prop':
      return ssi_competitor_count_prop($match_data, $atts);
    default:
      // Try to get property directly.
      return $match_data['data'][$prop];

  }
}
add_shortcode('match', 'match_data_func');

/**
 * Returns calculated data.
 *
 * @return string
 */
function math_data_func( $atts, $content ){
  $str = 'return ' . strip_tags(do_shortcode($content)) . ';';
  return eval($str);
}
add_shortcode('math', 'math_data_func');

function ssi_competitor_count_prop($match_data, $atts) {
  $prop = 0;
  $value = next($atts);
  $key = next(array_keys($atts));
  foreach($match_data['competitors'] as $competitor) {
    // Skip deleted users.
    if (strtolower($competitor['status']) == 'x') {
      continue;
    }
    if (strtolower($value) == 'false') {
      $value = false;
    }
    if ($competitor[$key] == $value) {
      $prop++;
    }
  }
  return $prop;
}


function ipsc_match_competitor_by_country($match_data) {
  $competitors = $match_data['competitors'];
  $headers = array(
    array('data'=> __('Region'), 'class' => 'flag'),
    array('data'=> __('Competitors'), 'class' => 'call'),
  );
  $regions = array();

  foreach($competitors as $competitor) {
    $regions[$competitor['region']]++;
  }

  foreach ($regions as $region => $count) {
    $rows[] = array(
      array(
        'data' => '<img src="/wp-content/themes/twentyfifteen-child/img/' . get_region($region) . '.png" />',
        'class' => 'flag',
      ),
      array(
        'data' => $count,
        'class' => 'call',
      ),
    );
  }
  return render_table($headers, $rows, 'prematch_table');
}


function ssi_get_date_time($datetime, $attributes = NULL){
  if (isset($attributes['format']) && $attributes['format'] == 'date_only') {
    $format = get_option( 'date_format' );
  }
  else {
    $format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
  }
  return date_i18n($format, $datetime->getTimestamp());
}

function ipsc_match_competitorlist($match_data, $atts) {

  $competitors = $match_data['competitors'];
  $headers = array(
    array('data'=> __(''), 'class' => 'flag'),
    __('Name'),
    //array('data'=> __('Squad'), 'class' => 'squad'),
    //array('data'=> __('Call'), 'class' => 'call'),
  );

  // Remove first attribute.
  $atts = array_reverse($atts);
  array_pop($atts);
  foreach($competitors as $competitor) {

    if (!empty($atts)) {
      foreach($atts as $key => $value) {
        if($competitor[$key] != $value){
          continue 2;
        }
      }
    }

    $rows[] = array(
      array(
        'data' => '<img src="/wp-content/themes/twentyfifteen-child/img/' . get_region($competitor['region']) . '.png" />',
        'class' => 'flag',
      ),

      $competitor['first_name'] . ' ' . $competitor['last_name'] . '<p>' . $competitor['club'] . '</p>',
      //get_squad_by_pk($competitor['squad']),

      /*array(
        'data' => '<a href="tel://' . $competitor['get_phone_display'] . '">' . __('Phone') . '</a>',
        'class' => 'call',
      ),*/

    );

  }
  return render_table($headers, $rows, 'prematch_table');
}

function get_squad_by_pk($pk) {
  $match_data = get_option('match_data');

  foreach ($match_data['squads'] as $squad) {
    if ($squad['pk'] == $pk) {
      return $squad['number'];
    }
  }

}

function get_region($region) {
  $regions = array(
    'dnk' => 'danish',
    'swe' => 'swedish',
    'nor' => 'norway',
    'fin' => 'finland'

  );
  return $regions[strtolower($region)];
}
function render_table($headers, $rows, $class="table"){

  foreach ($headers as $header) {
    if (is_array($header)){
      $table_headers[] = '<th class="' . $header['class'] . '">' . $header['data'] . '</th>';
    }
    else {
      $table_headers[] = '<th>' . $header . '</th>';
    }

  }
  $table_rows[] = '<thead>' . implode($table_headers) . '</thead>';

  foreach ($rows as $row) {
    $columns = array();
    foreach ($row as $field) {
      if (is_array($field)){
        $columns[] = '<td class="' . $field['class'] . '">' . $field['data'] . '</td>';
      }
      else {
        $columns[] = '<td>' . $field . '</td>';
      }
    }
    $table_rows[] = '<tr>' . implode($columns) . '</tr>';
  }

  return '<table class="' . $class . '">' . implode($table_rows) . '</table>';
}

register_activation_hook( __FILE__, function(){
  wp_schedule_event( time(), '5min', 'shootnscoreit' );
});
register_deactivation_hook(__FILE__, function(){
  wp_clear_scheduled_hook('shootnscoreit');
});



add_action('shootnscoreit', 'ssi_fetch_api_data');


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
    'teams' => 'teams',
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


/*
 * Implement some sort of CRON to update match_data option.
 */
if (get_option('match_data')['data']['pk'] != get_option('ssi_match_id')) {
  ssi_fetch_api_data();
}









