<?php
namespace Strontium\PjaxBundle\VersionGenerator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthTokenGenerator implements VersionGeneratorInterface
{

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;


    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $version = sprintf(
            'u:%s',
            is_string($user) ? $user : $user->getId()
        );

        return $version;
    }
}
