<?php namespace Stevenmaguire\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class BitbucketResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get resource owner id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->response['uuid'] ?: null;
    }

    /**
     * Get resource owner name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->response['display_name'] ?: null;
    }

    /**
     * Get resource owner username
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->response['username'] ?: null;
    }

    /**
     * Get resource owner location
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->response['location'] ?: null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
