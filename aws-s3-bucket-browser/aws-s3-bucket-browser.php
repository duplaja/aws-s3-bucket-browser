<?php

/**
* Plugin Name: AWS S3 Bucket Browser
* Plugin URI: https://www.wptechguides.com
* Description: A custom Amazon S3 File Browser
* Version: 0.3
* Author: Dan Dulaney
* Author URI: https://www.convexcode.com
**/




require(dirname(__FILE__) . "/aws/aws-autoloader.php");

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;
use Aws\S3\Exception\S3Exception;

add_action( 'wp_enqueue_scripts', 's3_browse_register_scripts' );
function s3_browse_register_scripts() {
	wp_register_script( 'browse-s3-js', plugins_url( '/assets/js/script.js' , __FILE__ ), array(), '', true );


	wp_register_style( 'browse-s3-css', plugins_url( '/assets/css/styles.css' , __FILE__ ));
	wp_enqueue_style( 'browse-s3-css' );

}

//creates an entry on the admin menu for s3 uploader
add_action('admin_menu', 's3_browse_plugin_menu');

//creates a menu page with the following settings
function s3_browse_plugin_menu() {
	add_submenu_page('tools.php', 'S3 Browser', 'S3 Browser', 'manage_options', 's3-browse-settings', 's3_browse_display_settings');
}

//on-load, sets up the following settings for the plugin
add_action( 'admin_init', 's3_browse_settings' );

function s3_browse_settings() {
	register_setting( 'browse-s3-plugin-settings-group', 's3_browse_aws_access_key' );
	register_setting( 'browse-s3-plugin-settings-group', 's3_browse_aws_secret' );
	register_setting( 'browse-s3-plugin-settings-group', 's3_browse_aws_region' );
}

//displays the settings page
function s3_browse_display_settings() {

	//first part here displays a form to change the settings
	echo "<form method=\"post\" action=\"options.php\">";

	settings_fields( 'browse-s3-plugin-settings-group' );
	do_settings_sections( 'browse-s3-plugin-settings-group' );

    echo "
	
	<style>.seperator { border-bottom: 1px solid black; }</style>
	
	<div><h1>S3 Browser Settings</h1><p>
Welcome to the AWS S3 Browser Plugin. Please set your AWS access key and secret key that have the appripriate iams permissions to access the buckets you're going to wish to display.
</p><p>You can use the shortcode of the format: <b>[s3browse bucket=bucketname]</b> to display the files listing in a page or a post. The bucket attribute is required.</p>
<p><b>Features</b>
<ul><li>All files hosted on AWS S3: No local server traffic / space required.</li>
<li>Links auto-expire after 60 minutes. No worries about hot-linking</li>
<li>Searching without ever leaving the page.</li>
<li>Since this is run via shortcode, able to put in a password protected page or post</li>
</ul>
<b>Limitations:</b>
<ul>
<li>Must Use AWS</li>
<li>View / Download only (for now, uploading in a future version)</li>
<li>Only works for 3 levels deep. Bucket (and items in), Folders (and items in), and Subfolders (and items in). Any deeper and they will not display correctly, although they will still show in search. This is being worked on for a future version.</li>
</ul></p>
<table class=\"form-table\">
	<tr><td colspan=\"3\"><h2>General AWS S3 Settings (All REQUIRED)</h2></td></tr> 
       <tr valign=\"top\">
        <th scope=\"row\">AWS Access Key</th>
        <td><input type=\"text\" name=\"s3_browse_aws_access_key\" value=\"".esc_attr( get_option('s3_browse_aws_access_key') )."\" /></td>
<td><p>Enter your AWS access key here.</p></td>
        </tr>
         
        <tr valign=\"top\">
        <th scope=\"row\">AWS Secret Key</th>
        <td><input type=\"text\" name=\"s3_browse_aws_secret\" value=\"".esc_attr( get_option('s3_browse_aws_secret') )."\" /></td>
<td><p>Shown only when creating account.</p></td>
        </tr>
		
		<tr valign=\"top\" class=\"seperator\">
        <th scope=\"row\">AWS Region</th>
        <td><input type=\"text\" name=\"s3_browse_aws_region\" value=\"".esc_attr( get_option('s3_browse_aws_region') )."\" /></td>
<td><p>Get your region from your S3 url. Ex) us-west-1</p></td>
        </tr></table>";
    
    submit_button();

	echo "</form><br><br>";

	echo "</div>";

}



//shortcode-to-display-bucket
function s3_browse_shortcode_disp($atts){

	$aws_access_key = esc_attr( get_option('s3_browse_aws_access_key') );
	$aws_secret = esc_attr( get_option('s3_browse_aws_secret') );
	$aws_region = esc_attr( get_option('s3_browse_aws_region') );



	if ($aws_access_key == '' || $aws_secret == '' || $aws_region == '') {

		echo "Make sure your access key, secret key, and region are all entered.";
		return;

	}

	//Handles attribures. If none are specified, defaults to no scroll, 1st drive	
	$atts = shortcode_atts(
        array(
            'bucket' => 'none',
        ), $atts, 's3browse' );

	$bucket = $atts['bucket'];


	if ($bucket == 'none') {

		echo "You must enter a bucket name in your shortcode. [s3browse bucket=bucketname]";
		return;


	}

	echo "<div class=\"files-div\"><div class=\"filemanager\">

		<div class=\"search\">
			<input type=\"search\" placeholder=\"Find a file..\" />
		</div>

		<div class=\"breadcrumbs\"></div>

		<ul class=\"data\"></ul>

		<div class=\"nothingfound\">
			<div class=\"nofiles\"></div>
			<span>No files here.</span>
		</div>

	</div>
	</div>";

	

//function to allow sorting of results from AWS S3 List
	function s3_browse_array_orderby()
	{
	    $args = func_get_args();
	    $data = array_shift($args);
	    foreach ($args as $n => $field) {
	        if (is_string($field)) {

	            $tmp = array();
	            foreach ($data as $key => $row)
	                $tmp[$key] = $row[$field];
		            $args[$n] = $tmp;
	            }
	    }

	    $args[] = &$data;
	    call_user_func_array('array_multisort', $args);

	    return array_pop($args);
	}





$credentials = new Credentials("$aws_access_key", "$aws_secret");

//Instantiate the S3 client with your AWS credentials
 $s3Client = S3Client::factory(array(
	'credentials' => $credentials,
	'region' => "$aws_region",
	'version' => 'latest' ));

try {
	$objects = $s3Client->getIterator('ListObjects', array('Bucket' => $bucket));

	$dirarray[] = './';
	$dirlevel[] = '0';

	foreach ($objects as $object) {
		if (!isset($objectarray)) { $objectarray = array(); }
		//print_r($object);
		$tempitemarray = array();
		$tempitemarray['fullname'] = $object['Key'];
		$tempitemarray['size'] = $object['Size'];
		$tempitemarray['base'] = basename($object['Key']);
		$tempitemarray['dir'] = dirname($object['Key']).'/';


		if ($object['Size'] == '0') {

			$dirarray[] = $object['Key'];
			$tempdirlevel = substr_count(($object['Key']),'/');
			$dirlevel[] = $tempdirlevel;
			

		} else {

			

			$objectarray[] = $tempitemarray;

		}


	}

	
	$objectarray = s3_browse_array_orderby($objectarray, 'dir', SORT_DESC, 'base', SORT_DESC);


	$numfolders = count($dirarray);

	$major_folder_num = 0;

	for ($i=0; $i<$numfolders; $i++) {

		$result = array();

		$items_in_folder = array();

		$current_folder = $dirarray[$i];
		$current_level = $dirlevel[$i];

		
		foreach ($objectarray as $link) {
		    if (in_array($current_folder, $link)) {
		        $result[] = $link;
		    }
		}

		$in_folder_count=count($result);


		for ($z=0;$z<$in_folder_count;$z++) {
		
			$name = $result[$z]['fullname'];
			$basename = $result[$z]['base'];
			$dirname = $result[$z]['dir'];
			$size = $result[$z]['size'];

			$cmd = $s3Client->getCommand('GetObject', [
			    'Bucket' => "$bucket",
			    'Key' => "$name",
				'ResponseContentType'           => 'application/octet-stream',
				'ResponseContentDisposition'    => 'attachment; filename="'.$basename.'"',
			]);

			$request = $s3Client->createPresignedRequest($cmd, '+60 minutes');

			$link = (string) $request->getUri();
			//$link = 'fakelink';
			$items_in_folder[] = array(
					"name" => $basename,
					"type" => "file",
					"path" => 'Home/'.$name,
					"link" => $link,
					"size" => $size
				);


		}
		

		if ($dirname == './') { 

			$count_em = count($items_in_folder);
			for ($x=0;$x<$count_em;$x++) {
				$files[] = $items_in_folder[$x];
				$major_folder_num++;
			}



		} else {

			if($current_level == '2') {
				$y = $major_folder_num-1;
				//echo "Major folder Number: $y<br><br>";
				
				array_push($files[$y]['items'], array(
					"name" => basename($current_folder),
					"type" => "folder",
					"path" => 'Home/'.$current_folder,
					"items" => $items_in_folder,
					"level" => $current_level,

				));

				

			}
			elseif($current_level == '3') {
				
			}
			else {

				$files[] = array(
					"name" => basename($current_folder),
					"type" => "folder",
					"path" => 'Home/'.$current_folder,
					"items" => $items_in_folder,
					"level" => $current_level,
					"folder_num" => $z
				);

				$major_folder_num++;
			}

		}


	}


	//json encodes the whole object
	$json_final = json_encode(array(
		"name" => "Home",
		"type" => "folder",
		"path" => "Home",
	"items" => $files
	));

	//enques the js script and sends the json object to it.
	wp_enqueue_script( 'browse-s3-js' );
	wp_localize_script('browse-s3-js', 's3_browse_vars', array(
			'json_array' => __($json_final)
		)
	);
	
}
catch (S3Exception $e) {

	echo $e->getMessage() . "\n";
}


}
//shortcode for form
add_shortcode('s3browse', 's3_browse_shortcode_disp');

