<?php

namespace Ds\Common\CDN;

use Carbon\Carbon;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageObject;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Arr;

class Manager
{
    /** @var \Illuminate\Foundation\Application */
    protected $app;

    /** @var \Google\Cloud\Storage\Bucket */
    protected $bucket;

    /** @var string */
    protected $prefix;

    /**
     * Create an instance
     *
     * @param \Illuminate\Foundation\Application $app
     * @param \Google\Cloud\Storage\Bucket $bucket
     * @param string $prefix
     */
    public function __construct($app, Bucket $bucket, $prefix)
    {
        $this->app = $app;
        $this->bucket = $bucket;
        $this->prefix = trim($prefix, '/');
    }

    /**
     * Get connection.
     *
     * @return \Google\Cloud\Storage\Connection\Rest
     */
    private function getConnection()
    {
        return $this->app['cdn.client']->getConnection();
    }

    /**
     * Get storage object.
     *
     * @param string $name
     * @return \Google\Cloud\Storage\StorageObject
     */
    public function getObject($name): StorageObject
    {
        return $this->bucket->object("{$this->prefix}/$name");
    }

    /**
     * Get a Signed URL.
     *
     * @param string $name
     * @return string
     */
    public function getSignedUrl($name, $expires, array $options = [])
    {
        return $this->getObject($name)->signedUrl($expires, $options);
    }

    /**
     * Get a Signed Upload URL.
     *
     * @param string $name
     * @return string
     */
    public function getSignedUploadUrl($name)
    {
        return $this->getObject($name)->signedUploadUrl(Carbon::parse('tomorrow'));
    }

    /**
     * Create a signed URL upload session.
     *
     * NOTE: We can't use `beginSignedUploadSession` from the Google libraraies because
     * there's no way to add the origin header.
     *
     * @param string $name
     * @return string
     */
    public function beginSignedUploadSession($name, $contentType = 'application/octet-stream')
    {
        $options = [
            'headers' => ['origin' => [secure_site_url()]],
            'contentType' => $contentType,
        ];

        $object = $this->getObject($name);

        $timestamp = new \DateTimeImmutable('+1 minute');
        $startUri = $object->signedUploadUrl($timestamp, $options);

        $headers = [
            'Content-Type' => Arr::get($options, 'contentType'),
            'Content-Length' => 0,
            'x-goog-resumable' => 'start',
            'origin' => Arr::get($options, 'headers.origin.0'),
        ];

        $request = new Request('POST', $startUri, $headers);

        $response = $this->getConnection()->requestWrapper()->send($request, []);

        return $response->getHeaderLine('Location');
    }
}
