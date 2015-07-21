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
}
