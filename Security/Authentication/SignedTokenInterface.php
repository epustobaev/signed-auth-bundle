<?php
/**
 * @copyright 2015
 * @author    Edward Pustobaev <eduardpustobaev@gmail.com>
 */

namespace Dendy\SignedAuthBundle\Security\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Interface SignedTokenInterface
 *
 * @package Dendy\SignedAuthBundle\Security\Authentication
 */
interface SignedTokenInterface extends TokenInterface
{
    /**
     * @return mixed
     */
    public function getSignature();

    /**
     * @param string $signature
     *
     * @return mixed
     */
    public function setSignature($signature);

    /**
     * @return Request
     */
    public function getRequest();

    /**
     * @param Request $request
     */
    public function setRequest(Request $request);
}
