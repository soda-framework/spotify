<?php

class SessionTest extends PHPUnit_Framework_TestCase
{
    private $clientID = 'b777292af0def22f9257991fc770b520';
    private $clientSecret = '6a0419f43d0aa93b2ae881429b6b9bc2';
    private $redirectURI = 'https://example.com/callback';
    private $refreshToken = '3692bfa45759a67d83aedf0045f6cb63';

    public function testGetAuthorizeUrl()
    {
        $expected = sprintf(
            'https://accounts.spotify.com/authorize/?client_id=%s&redirect_uri=%s&response_type=%s&show_dialog=%s',
            $this->clientID,
            urlencode($this->redirectURI),
            'code',
            'true'
        );

        $session = new Soda\Spotify\Api\Session($this->clientID, $this->clientSecret, $this->redirectURI);
        $url = $session->getAuthorizeUrl([
            'show_dialog' => true,
        ]);

        $this->assertEquals($expected, $url);
    }

    public function testGetAuthorizeUrlScope()
    {
        $expected = sprintf(
            'https://accounts.spotify.com/authorize/?client_id=%s&redirect_uri=%s&response_type=%s&scope=%s',
            $this->clientID,
            urlencode($this->redirectURI),
            'code',
            'user-read-email'
        );

        $session = new Soda\Spotify\Api\Session($this->clientID, $this->clientSecret, $this->redirectURI);
        $url = $session->getAuthorizeUrl([
            'scope' => ['user-read-email'],
        ]);

        $this->assertEquals($expected, $url);
    }

    public function testGetAuthorizeUrlState()
    {
        $state = 'foobar';
        $expected = sprintf(
            'https://accounts.spotify.com/authorize/?client_id=%s&redirect_uri=%s&response_type=%s&state=%s',
            $this->clientID,
            urlencode($this->redirectURI),
            'code',
            $state
        );

        $session = new Soda\Spotify\Api\Session($this->clientID, $this->clientSecret, $this->redirectURI);
        $url = $session->getAuthorizeUrl([
            'state' => $state,
        ]);

        $this->assertEquals($expected, $url);
    }

    public function testGetClientId()
    {
        $session = new Soda\Spotify\Api\Session($this->clientID, $this->clientSecret, $this->redirectURI);
        $expected = $this->clientID;

        $session->setClientId($expected);

        $this->assertEquals($expected, $session->getClientId());
    }

    public function testGetClientSecret()
    {
        $session = new Soda\Spotify\Api\Session($this->clientID, $this->clientSecret, $this->redirectURI);
        $expected = $this->clientSecret;

        $session->setClientSecret($expected);

        $this->assertEquals($expected, $session->getClientSecret());
    }

    public function testGetRedirectUri()
    {
        $session = new Soda\Spotify\Api\Session($this->clientID, $this->clientSecret, $this->redirectURI);
        $expected = $this->redirectURI;

        $session->setRedirectUri($expected);

        $this->assertEquals($expected, $session->getRedirectUri());
    }

    public function testRefreshAccessToken()
    {
        $expected = [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->refreshToken,
        ];

        $headers = [
            'Authorization' => 'Basic Yjc3NzI5MmFmMGRlZjIyZjkyNTc5OTFmYzc3MGI1MjA6NmEwNDE5ZjQzZDBhYTkzYjJhZTg4MTQyOWI2YjliYzI=',
        ];

        $return = [
            'body' => get_fixture('refresh-token'),
        ];

        $stub = $this->setupStub(
            'POST',
            '/api/token',
            $expected,
            $headers,
            $return
        );

        $session = new Soda\Spotify\Api\Session($this->clientID, $this->clientSecret, $this->redirectURI, $stub);
        $session->refreshAccessToken($this->refreshToken);

        $this->assertNotEmpty($session->getAccessToken());
        $this->assertEquals(time() + 3600, $session->getTokenExpiration());
    }

    private function setupStub($expectedMethod, $expectedUri, $expectedParameters, $expectedHeaders, $expectedReturn)
    {
        $stub = $this->getMockBuilder('Request')
            ->setMethods(['account'])
            ->getMock();

        $stub->expects($this->once())
            ->method('account')
            ->with(
                $this->equalTo($expectedMethod),
                $this->equalTo($expectedUri),
                $this->equalTo($expectedParameters),
                $this->equalTo($expectedHeaders)
            )
            ->willReturn($expectedReturn);

        return $stub;
    }

    public function testRequestAccessToken()
    {
        $authorizationCode = 'd1e893a80f79d9ab5e7d322ed922da540964a63c';
        $expected = [
            'client_id'     => $this->clientID,
            'client_secret' => $this->clientSecret,
            'code'          => $authorizationCode,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->redirectURI,
        ];

        $return = [
            'body' => get_fixture('access-token'),
        ];

        $stub = $this->setupStub(
            'POST',
            '/api/token',
            $expected,
            [],
            $return
        );

        $session = new Soda\Spotify\Api\Session($this->clientID, $this->clientSecret, $this->redirectURI, $stub);
        $result = $session->requestAccessToken($authorizationCode);

        $this->assertTrue($result);
        $this->assertNotEmpty($session->getAccessToken());
        $this->assertNotEmpty($session->getRefreshToken());
        $this->assertEquals(time() + 3600, $session->getTokenExpiration());
    }

    public function testRequestCredentialsToken()
    {
        $expected = [
            'grant_type' => 'client_credentials',
            'scope'      => 'user-read-email',
        ];

        $headers = [
            'Authorization' => 'Basic Yjc3NzI5MmFmMGRlZjIyZjkyNTc5OTFmYzc3MGI1MjA6NmEwNDE5ZjQzZDBhYTkzYjJhZTg4MTQyOWI2YjliYzI=',
        ];

        $return = [
            'body' => get_fixture('access-token'),
        ];

        $stub = $this->setupStub(
            'POST',
            '/api/token',
            $expected,
            $headers,
            $return
        );

        $session = new Soda\Spotify\Api\Session($this->clientID, $this->clientSecret, $this->redirectURI, $stub);
        $result = $session->requestCredentialsToken(['user-read-email']);

        $this->assertTrue($result);
        $this->assertNotEmpty($session->getAccessToken());
        $this->assertEquals(time() + 3600, $session->getTokenExpiration());
    }

    public function testSetClientId()
    {
        $session = new Soda\Spotify\Api\Session($this->clientID, $this->clientSecret, $this->redirectURI);
        $expected = $this->clientID;

        $session->setClientId($expected);

        $this->assertEquals($expected, $session->getClientId());
    }

    public function testSetClientSecret()
    {
        $session = new Soda\Spotify\Api\Session($this->clientID, $this->clientSecret, $this->redirectURI);
        $expected = $this->clientSecret;

        $session->setClientSecret($expected);

        $this->assertEquals($expected, $session->getClientSecret());
    }

    public function testSetRedirectUri()
    {
        $session = new Soda\Spotify\Api\Session($this->clientID, $this->clientSecret, $this->redirectURI);
        $expected = $this->redirectURI;

        $session->setRedirectUri($expected);

        $this->assertEquals($expected, $session->getRedirectUri());
    }
}
