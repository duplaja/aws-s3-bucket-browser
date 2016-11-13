<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
 
$option_name = 's3_browse_aws_access_key';
$option_name2 = 's3_browse_aws_secret';
$option_name3 = 's3_browse_aws_region';

delete_option($option_name);
delete_option($option_name2);
delete_option($option_name3);

// for site options in Multisite
delete_site_option($option_name);
delete_site_option($option_name2);
delete_site_option($option_name3);

