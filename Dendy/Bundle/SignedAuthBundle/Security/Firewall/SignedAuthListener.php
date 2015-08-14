<?php
/**
 * @copyright 2015
 * @author    Edward Pustobaev <eduardpustobaev@gmail.com>
 */

namespace Dendy\Bundle\SignedAuthBundle\Security\Firewall;

use Dendy\Bundle\SignedAuthBundle\Security\Authentication\SignedUserToken;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * Class SignedAuthListener
 *
 * @package Dendy\Bundle\SignedAuthBundle\Security\Firewall
 */
class SignedAuthListener implements ListenerInterface
{
    /** @var  TokenStorageInterface */
    protected $tokenStorage;
    /** @var  AuthenticationManagerInterface */
    protected $authManager;
    /** @var array */
    protected $config;

    const AUTH_TYPE_HEADER  = 'header';
    const AUTH_TYPE_REQUEST = 'request';

    /**
     * @param TokenStorageInterface          $tokenStorage
     * @param AuthenticationManagerInterface $authManager
     * @param array                          $config
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authManager,
        array $config
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authManager  = $authManager;
        $this->config       = $config;
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return bool
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        switch ($this->getAuthType()) {
            case self::AUTH_TYPE_HEADER:
                $requestToken = $request->headers->get($this->getAuthKeyName());
                break;
            case self::AUTH_TYPE_REQUEST:
                $requestToken = $request->get($this->getAuthKeyName());
                break;
            default:
                throw new InvalidConfigurationException("Unknown auth type".$this->getAuthType());
        }

        if (!$requestToken) {
            throw new AuthenticationException('Empty authentication token');
        }

        $requestTokenData = $this->parseAuthToken($requestToken);

        $token = new SignedUserToken();
        $token->setUser($requestTokenData[0]);
        $token->setSignature($requestTokenData[1]);
        $token->setRequest($request);
        try {
            $authenticatedToken = $this->authManager->authenticate($token);
            $this->tokenStorage->setToken($authenticatedToken);

            return true;
        } catch (AuthenticationException $failed) {
        }
        $response = new Response('', Response::HTTP_FORBIDDEN);
        $event->setResponse($response);

        return false;
    }

    /**
     * @param string $token
     *
     * @return array
     */
    protected function parseAuthToken($token)
    {
        $tokenData = explode($this->getTokenDelimiter(), $token);
        if (count($tokenData) !== 2) {
            throw new AuthenticationException('Invalid auth header format');
        }

        return $tokenData;
    }

    /**
     * @return mixed
     */
    protected function getAuthKeyName()
    {
        return $this->config['request_key'];
    }

    /**
     * @return mixed
     */
    protected function getAuthType()
    {
        return $this->config['auth_type'];
    }

    /**
     * @return mixed
     */
    protected function getTokenDelimiter()
    {
        return $this->config['token_delimiter'];
    }
}
