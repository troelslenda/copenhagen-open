Every variables in the data array is available prepending ssi_ some of the
variables are processed before displaying data.

Following variables are treated as DateTime and rendered using
Wordpress date formats configured in settings. Furthermore you can use the
property format=date_only to only show the date.
IE. [match prop=ssi_starts format=date_only]
 - ssi_registration_starts
 - ssi_starts
 - ssi_ends
 - ssi_last_update

The following variables a 0 value is considered not set so 0 results in TBA.
 - ssi_number_of_stages
 - ssi_minimum_rounds


