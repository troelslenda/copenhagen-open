<?php

/**
 * Returns the match data.
 *
 * @param $atts
 * @return string
 */
function match_data_func( $atts ){

  if (empty($atts['prop'])) return;

  if (empty(get_option('match_data'))) return;

  $match_data = current(get_option('match_data'));

  switch ($atts['prop']) {
    case 'name':
      return $match_data['name'];
    case 'regstart':
      return $match_data['registration_starts'];
    default:
      // Try to get property directly.
      return $match_data[$atts['prop']];

  }
}
add_shortcode('match', 'match_data_func');



/*
 * Implement some sort of CRON to update match_data option.
 */

if (empty(get_option('match_data'))) {
  $match_url = 'https://shootnscoreit.com/api/ipsc/match/3489/all';
  $api_response = wp_remote_get($match_url);
  $api_data = json_decode( wp_remote_retrieve_body($api_response), true);
  $api['fetched_from_api'] = new DateTime();


  update_option('match_data', $api_data);

}

