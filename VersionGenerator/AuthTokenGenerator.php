<?php
namespace Strontium\PjaxBundle\VersionGenerator;

use Travian\Bundle\Entity\Server;
use Application\Sonata\UserBundle\Entity\Group;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\SecurityContextInterface;

class AuthTokenGenerator implements VersionGeneratorInterface
{

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @param SecurityContextInterface $securityContext
     *
     */
    function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Request $request)
    {
        $user = $this->securityContext->getToken()->getUser();


        $version = sprintf(
            'u:%s',
            $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ? $user->getId() : $user
        );

        return $version;
    }
}
