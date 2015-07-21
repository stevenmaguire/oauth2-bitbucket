<?php namespace Stevenmaguire\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\GenericResourceOwner;

/**
 * @property array $response
 * @property string $resourceOwnerId
 */
class BitbucketResourceOwner extends GenericResourceOwner
{
    /**
     * Get resource owner id
     *
     * @return string
     */
    public function getId()
    {
        return $this->resourceOwnerId;
    }

    /**
     * Get resource owner name
     *
     * @return string
     */
    public function getName()
    {
        return $this->response['display_name'] ?: null;
    }

    /**
     * Get resource owner username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->response['username'] ?: null;
    }

    /**
     * Get resource owner location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->response['location'] ?: null;
    }
}
