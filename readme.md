# WP Image Cop

WP Image Cop is an s3 sync and image compression client. All images uploaded to the wordpress media folder are uploaded to s3 & compressed.

Currently, the default behavior is:
  - Upload media
  - Media is hosted on S3
  - Media is compressed and served from public bucket.
  - Local files removed.
  - 
#### Get Started
You can define your AWS settings as constants in your `wp-config.php`

```
define('IMAGE_COP_AWS_ACCESS_KEY_ID', '<your-access-key-id>');
define('IMAGE_COP_AWS_SECRET_ACCESS_KEY', '<your-secret-access-key>');
```

Then, simply initialize with the `image_cop()` function in your functions file:
```
image_cop();
```

##### Options

`image_cop()` accepts an **array** of arguments:

 - `upload_directory` - **string** (optional) Default: `upload`. This option declares where in your s3 bucket the initial files will be uploaded.
 - `compressed_directory` - **string** (optional) Default: `compressed`.
 - `bucket` **string** (optional) Default: `image-cop`. This specifies what bucket will be used.
 - `keep_local_files` **boolean** (optional) Default: `false`. This toggles whether to keep a local version of uploaded files. By default, files will be removed from content folder.


### Alpha Version

This plug-in is still in alpha testing.

Next version to include:
 - Delete sync with s3
 - Configuration page
 - Different buckets for different staging environments

Future updates:
 - Control compression settings from WP Image Cop
 - AWS Rekognition for auto-captioning images for SEO.
 - Unit testing

