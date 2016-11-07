=== AWS S3 Bucket Browser ===
Contributors: duplaja
Donate link: https://www.wptechguides.com/donate/
Tags: aws, s3, files, download, Amazon, folders, search
Requires at least: 4.0.1
Tested up to: 4.6.1
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

S3 Bucket Browser allows users to search, view, and download files from AWS S3 in a lightweight format via shortcode.

== Description ==

S3 Bucket Browser allows you to harness the power of Amazon Web Service's S3 storage system to serve files to your visitors directly from S3's servers, in a fast, easy to navigate method. Folders can be navigated between via clicking or via breadcrumbs, all without leaving or reloading the page. File searches are near instantaneous, and all download links are time limited (60 minutes), preventing your links from being widely shared or hotlinked.

Features:

* All files served are hosted offsite on S3 storage. No worries about disk space!

* Easy to use interface, as simple as clicking through folders.

* Searching is extremely fast

* Download links are signed and expire after 60 minutes, to prevent hot-linking

* Only one API call, on initial page load.

* Inserted via shortcode, so you can put on a password protected post or page if desired.

* Mobile Friendly


Shortcode Use: `[s3browse bucket=yourbucketname]`

**Notes** Before Use:

* You must have an AWS account, and know your access and secret keys, as well as your region.

* You must have a bucket created with correct IAM set to access the bucket.

* You must upload files via S3 prior to displaying, if you want them to show. (future version will likely allow upload as well)

* You must have the correct CORS configuration set for your bucket:

```<?xml version="1.0" encoding="UTF-8"?>
<CORSConfiguration xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
    <CORSRule>
        <AllowedOrigin>*</AllowedOrigin>
        <AllowedMethod>POST</AllowedMethod>
        <AllowedMethod>GET</AllowedMethod>
        <AllowedMethod>PUT</AllowedMethod>
        <MaxAgeSeconds>3000</MaxAgeSeconds>
        <AllowedHeader>*</AllowedHeader>
    </CORSRule>
</CORSConfiguration>```

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/dans-gcal` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Head over to the S3 Browser Settings, found under Tools on the Dashboard Sidebar.

== Frequently Asked Questions ==

= How do I sign up for AWS S3 / create a bucket / find my keys? =

* You can find a great guide to starting with AWS S3 here: http://docs.aws.amazon.com/AmazonS3/latest/gsg/SigningUpforS3.html

= How do I upload files through the plugin? =

* Unfortunately, for v1, you cannot. You must upload them through the AWS Console online, or some other method.

== Screenshots ==

1. Settings Page

2. Mobile View

3. Full View

4. File Search Example

== Dependencies and Licensing ==

AWS SDK for PHP

== Roadmap ==

Features Planned for Future Versions:

* Ability to Upload Files

* Ability to Delete Files

* Ability to Create a Bucket inside Plugin

== Changelog ==

= 1.0 =
* Initial Plugin Release

== Upgrade Notice ==

= 1.0 =
* Initial Plugin Release
