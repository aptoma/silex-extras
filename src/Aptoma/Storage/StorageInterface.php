<?php


namespace Aptoma\Storage;

interface StorageInterface
{
    /**
     * A resource or string to store
     *
     * @param resource|string $resource
     * @internal param null $mimeType
     * @return string Asset identifier
     */
    public function put($resource);

    /**
     * @param $identifier
     * @return string Url where resource can be read
     */
    public function getUrl($identifier);

    /**
     * @param $identifier
     * @param bool $asResource
     * @return string|resource The raw content or a resource to read the content stream.
     */
    public function getRaw($identifier, $asResource = false);

    public function getMimeType($identifier);
}
