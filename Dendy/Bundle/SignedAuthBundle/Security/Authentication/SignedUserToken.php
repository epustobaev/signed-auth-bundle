<?php
/**
 * @copyright 2015
 * @author    Edward Pustobaev <eduardpustobaev@gmail.com>
 */

namespace Dendy\Bundle\SignedAuthBundle\Security\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Class SignedUserToken
 *
 * @package Dendy\Bundle\SignedAuthBundle\Security\Authentication
 */
class SignedUserToken extends AbstractToken implements SignedTokenInterface
{
    protected $signature;
    protected $request;
    protected $dataToSign;

    /**
     * @return array
     */
    public function getCredentials()
    {
        return ['login' => $this->getUser()->getLogin(), 'password' => $this->getUser()->getPassword()];
    }

    /**
     * @return mixed
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param string $signature
     *
     * @return void
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
}
