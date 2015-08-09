<?php namespace Stevenmaguire\OAuth2\Client\Test\Provider;

use Mockery as m;

class BitbucketTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->provider = new \Stevenmaguire\OAuth2\Client\Provider\Bitbucket([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_client_secret',
            'redirectUri' => 'redirect_url',
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }


    public function testScopes()
    {
        $options = ['scope' => [uniqid(),uniqid()]];

        $url = $this->provider->getAuthorizationUrl($options);

        $this->assertContains(urlencode(implode(',', $options['scope'])), $url);
    }

    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/site/oauth2/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/site/oauth2/access_token', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token","scopes": "account","expires_in": 3600,"refresh_token": "mock_refresh_token","token_type": "bearer"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertEquals('mock_refresh_token', $token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testUserData()
    {
        $userId = rand(1000,9999);
        $name = uniqid();
        $username = uniqid();
        $location = uniqid();

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token","scopes": "account","expires_in": 3600,"refresh_token": "mock_refresh_token","token_type": "bearer"}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn('{"created_on": "2011-12-20T16:34:07.132459+00:00","display_name": "'.$name.'","location": "'.$location.'","type": "user","username": "'.$username.'","uuid": "'.$userId.'","website": "https://tutorials.bitbucket.org/"}');
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertEquals($userId, $user->getId());
        $this->assertEquals($userId, $user->toArray()['uuid']);
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($name, $user->toArray()['display_name']);
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($username, $user->toArray()['username']);
        $this->assertEquals($location, $user->getLocation());
        $this->assertEquals($location, $user->toArray()['location']);
    }

    /**
     *
     * @expectedException \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function testUserDataFails()
    {
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token","scopes": "account","expires_in": 3600,"refresh_token": "mock_refresh_token","token_type": "bearer"}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn('{"error":"mock_error","error_description": "mock_error_description"}');
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $userResponse->shouldReceive('getStatusCode')->andReturn(500);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);
    }
}
