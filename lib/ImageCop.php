<?php

class ImageCop
{

    /**
     * Directory to upload files to in s3.
     * Files here will be automatically moved
     * to the $s3CompressedDirectory on upload.
     *
     * @var string
     */
    public $s3UploadDirectory;

    /**
     * Directory that will be public-facing.
     * This is where the compressed images will
     * be, and public urls will reflect this path.
     *
     * @var string
     */
    public $s3CompressedDirectory;

    /**
     * The secret key used for the s3 bucket
     *
     * @var string
     */
    public $secretKey;

    /**
     * The access key used or the s3 bucket.
     *
     * @var string
     */
    public $accessKeyId;

    /**
     * The bucket used for uploading.
     *
     * @var string
     */
    public $bucket;

    /**
     * Just a place to store the
     * generic amazon s3 url.
     *
     * @var string
     */
    public $prefix;

    /**
     * Toggle whether to delete the
     * local file uploads in wordpress.
     * Will default to false;
     *
     * @var [type]
     */
    public $keepLocalFiles;

    /**
     * Options param for constructor.
     *
     * @var array|null
     */
    protected $options;

    public function __construct(array $options = null)
    {
        $this->options = $options;

        add_option('image_cop_bucket');
        add_option('image_cop_upload_folder');
        add_option('image_cop_compressed_folder');
        add_option('image_cop_keep_local_files');

        $dbOptions = self::getOptions();
        // optional parameters.
        $this->s3UploadDirectory = $dbOptions['upload_folder'];
        $this->s3CompressedDirectory = $dbOptions['compressed_folder'];
        $this->bucket = $dbOptions['bucket'];
        $this->keepLocalFiles = $dbOptions['keep_local_files'];
        $this->prefix = 'https://s3.amazonaws.com/';

        // call all boot logic.
        $this->boot();
    }

    /**
     * Throw errors if keys are not defined.
     *
     * @return void
     */
    protected function checkKeys(array $options = null){

        // Check if aws key id is defined.
        if( ! defined('IMAGE_COP_AWS_ACCESS_KEY_ID') && ! isset($this->options['access_key_id']) ){
            throw new Exception('IMAGE_COP_AWS_ACCESS_KEY_ID is not defined, or access_key_id not defined in constructor.');
        }

        // Check if aws key id is defined.
        if( ! defined('IMAGE_COP_AWS_SECRET_ACCESS_KEY') && ! isset($this->options['secret_access_key']) ){
            throw new Exception('IMAGE_COP_AWS_SECRET_ACCESS_KEY is not defined, or secret_access_key not defined in constructor.');
        }
        return true;
    }

    /**
     * Checks keys and resolves from either defined
     * constant or passed into options.
     *
     * @return void
     */
    protected function resolveKeys(){

        if($this->checkKeys()){
            $this->accessKeyId = ( defined('IMAGE_COP_AWS_ACCESS_KEY_ID') ) ? IMAGE_COP_AWS_ACCESS_KEY_ID : $this->options['access_key_id'];
            $this->secretKey = ( defined('IMAGE_COP_AWS_SECRET_ACCESS_KEY') ) ? IMAGE_COP_AWS_SECRET_ACCESS_KEY : $this->options['secret_access_key'];
        }
    }

    /**
     * Register all required hooks.
     *
     * @return void
     */
    protected function boot()
    {
        $this->resolveKeys();

        // initialize the s3 client with given keys.
        $this->s3 = $this->initializeS3();

        /**
         * Register all filters and hooks.
         * These hooks are used to swap out media
         * urls in both admin areas and front end.
         */
        add_filter('the_content', [ $this, 'replacePublicUrlsWithS3']);
        add_filter('wp_generate_attachment_metadata', [ $this, 'handleMediaUpload']);
        add_filter('pre_option_upload_url_path', [ $this, 'alterAttachmentSrc']);
        add_filter('delete_attachment', [$this, 'handleMediaDelete']);
    }

    /**
     * Set the instance of s3 with the correct
     * keys.
     *
     * @return void
     */
    protected function initializeS3()
    {
        $credentials = new Aws\Credentials\Credentials($this->accessKeyId, $this->secretKey);
        return new Aws\S3\S3Client([
            'version'     => 'latest',
            'region'      => 'us-east-1',
            'credentials' => $credentials
        ]);
    }

    /**
     * Re-write the display urls for the correct bucket for public
     * display.
     *
     * @param string $content
     * @return void
     */
    public function replacePublicUrlsWithS3($content)
    {
        return str_replace(site_url(), $this->getPublicBucketUrl(), $content);
    }
    
    /**
     * Change the src of image in admin
     *
     * @param array $image
     * @param int $attachment_id
     * @param string $size
     * @param string $icon
     * @return void
     */
    public function alterAttachmentSrc()
    {
        return $this->getPublicBucketUrl() . '/wp-content/uploads';
    }

    protected function getPublicBucketUrl(){
        return $this->prefix . $this->bucket . '/' . $this->s3CompressedDirectory;
    }

    /**
     * This method gets called directly by the hoook
     * and pushes to s3.
     *
     * @param array $metadata
     * @param integer $attachment_id
     * @return array
     */
    public function handleMediaUpload($metadata, $attachment_id = null)
    {
        $baseDir = str_replace(basename($metadata['file']), '', $metadata['file']);
        $path = ABSPATH . 'wp-content/uploads/' . $metadata['file'];
    
        // upload the full sized image
        $this->upload($path);

        // delete the original file from the uploads folder.
        $this->delete($path);
    
        foreach ($metadata['sizes'] as $size) {
            $sizePath = ABSPATH . 'wp-content/uploads/' . $baseDir . $size['file'];

            $this->upload($sizePath);

            // delete from uploads folder.
            $this->delete($sizePath);
        }
        
        return $metadata;
    }

    /**
     * Handles the delete action of
     * media to sync in s3.
     *
     * @param integer $post_id
     * @return void
     */
    public function handleMediaDelete($post_id){

    }

    /**
     * Delete a file at the given
     * path.
     *
     * @param string $path
     * @return void
     */
    protected function delete(string $path)
    {
        if( ! $this->keepLocalFiles){
            wp_delete_file($path);
        }
    }

    /**
     * Upload the path to s3.
     *
     * @param string $path
     * @param string $acl
     * @return void
     */
    protected function upload($path, $acl = 'public-read')
    {
        try {
            $this->s3->putObject([
                'Bucket'        =>  $this->bucket,
                'Key'           =>  $this->s3UploadDirectory . '/' . str_replace(ABSPATH, '', $path),
                'ACL'           =>  $acl,
                'SourceFile'    =>  $path
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * Get options for ImageCop saved in options tables.
     *
     * @return void
     */
    public static function getOptions(){
        return [
            'bucket'            =>  get_option('image_cop_bucket', 'image-cop'),
            'upload_folder'     =>  get_option('image_cop_upload_folder', 'upload'),
            'compressed_folder' =>  get_option('image_cop_public_folder', 'compressed'),
            'keep_local_files'  =>  get_option('image_cop_keep_local_files', false),
        ];
    }
}
