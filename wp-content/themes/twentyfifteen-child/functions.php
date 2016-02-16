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

  $match_data = get_option('match_data');

  switch ($atts['prop']) {
    case 'all':
      return '<pre>' . var_export($match_data, true) . '</pre>';
    case 'name':
      return $match_data[0]['name'];
    case 'regstart':
      return $match_data[0]['registration_starts'];
    default:
      // Try to get property directly.
      return $match_data[0][$atts['prop']];

  }
}
add_shortcode('match', 'match_data_func');



/*
 * Implement some sort of CRON to update match_data option.
 */
$match_key = 3489;

//delete_option('match_data');


if (empty(get_option('match_data'))) {
  $match_url = 'https://shootnscoreit.com/api/ipsc/match/'. $match_key;
  $api_response = wp_remote_get($match_url);
  $api_data = json_decode( wp_remote_retrieve_body($api_response), true);
  $match_url = 'https://shootnscoreit.com/api/ipsc/match/' . $match_key . '/competitors';
  $api_response = wp_remote_get($match_url);
  $api_data['competitors'] = json_decode( wp_remote_retrieve_body($api_response), true);
  $match_url = 'https://shootnscoreit.com/api/ipsc/match/' . $match_key . '/squads';
  $api_response = wp_remote_get($match_url);
  $api_data['squads'] = json_decode( wp_remote_retrieve_body($api_response), true);
  $match_url = 'https://shootnscoreit.com/api/ipsc/match/' . $match_key . '/stages';
  $api_response = wp_remote_get($match_url);
  $api_data['stages'] = json_decode( wp_remote_retrieve_body($api_response), true);


  $api['fetched_from_api'] = new DateTime();
  update_option('match_data', $api_data);
}

