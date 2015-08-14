<?php
/**
 * @copyright 2015
 * @author    Edward Pustobaev <eduardpustobaev@gmail.com>
 */

namespace Dendy\Bundle\SignedAuthBundle\Security\Authentication\Provider;

use Dendy\Bundle\SignedAuthBundle\Security\Authentication\Exception\RequestValueGetException;
use Dendy\Bundle\SignedAuthBundle\Security\Authentication\SignedTokenInterface;
use Dendy\Bundle\SignedAuthBundle\Security\Authentication\SignedUserToken;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class SignedAuthProvider
 *
 * @package Dendy\Bundle\SignedAuthBundle\Security\Authentication\Provider
 */
class SignedAuthProvider implements AuthenticationProviderInterface
{
    /** @var  UserProviderInterface */
    protected $userProvider;
    /** @var  array */
    protected $config;
    /** @var  Logger */
    protected $logger;

    /**
     * @param Logger                $logger
     * @param UserProviderInterface $userProvider
     * @param array                 $config
     */
    public function __construct(Logger $logger, UserProviderInterface $userProvider, array $config)
    {
        $this->userProvider = $userProvider;
        $this->config       = $config;
        $this->logger       = $logger;
    }

    /**
     * Attempts to authenticate a TokenInterface object.
     *
     * @param TokenInterface $token The TokenInterface instance to authenticate
     *
     * @return TokenInterface An authenticated TokenInterface instance, never null
     *
     * @throws AuthenticationException if the authentication fails
     */
    public function authenticate(TokenInterface $token)
    {
        /** @var SignedTokenInterface $token */
        $user       = $this->userProvider->loadUserByUsername($token->getUsername());
        $signData   = $this->getAuthSignData($token->getRequest());
        $signData[] = $user->getPassword();
        $expectedSignature = hash($this->config['hash_alg'], implode($this->config['data_delimiter'], $signData));
        if ($token->getSignature() == $expectedSignature) {
            $token->setUser($user);

            return $token;
        }

        $this->logger->critical(
            sprintf('Invalid auth signature. Expect "%s", got "%s"', $expectedSignature, $token->getSignature()),
            ['signData' => $signData]
        );
        throw new AuthenticationException("Invalid auth signature ".$token->getSignature());
    }

    /**
     * Checks whether this provider supports the given token.
     *
     * @param TokenInterface $token A TokenInterface instance
     *
     * @return bool true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof SignedUserToken;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getAuthSignData(Request $request)
    {
        $signedParams = $this->config['signed_params'];
        $data         = [];
        // Request headers
        foreach ($signedParams['headers'] as $headerName) {
            $data[] = $request->headers->get($headerName);
        }
        // Request GET|POST values
        foreach ($signedParams['query'] as $key) {
            $data[] = $request->request->get($key);
        }
        // Request params: requestUri, host, etc.
        foreach ($signedParams['request'] as $key) {
            $methodName = 'get'.lcfirst(ucwords($key));
            if (method_exists($request, $methodName)) {
                $data[] = $request->$methodName();
            } else {
                throw new RequestValueGetException("Method '$methodName' doesn't exists in request object");
            }
        }

        return $data;
    }
}
