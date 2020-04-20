<?php
/*
Plugin Name: WP Image Cop
Plugin URI:  https://partnercomm.net
Description: Syncs with S3 and auto-compresses with Sharp library.
Version:     0.1
Author:      Chris Arter
*/


// composer
require 'vendor/autoload.php';

// Load in main class
require_once('lib/ImageCop.php');

// helper functions :)
require_once('lib/helpers.php');

require_once('lib/options-page.php');

require_once('lib/image-cop-controller.php');