<?php
namespace Strontium\PjaxBundle\Twig;

use Knp\Menu\ItemInterface;
use Knp\Menu\Iterator\RecursiveItemIterator;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Twig\Helper;

class MenuExtension extends \Twig_Extension
{

    /**
     * @var Helper
     */
    protected $menuHelper;

    /**
     * @var MatcherInterface
     */
    protected $matcher;

    /**
     * @param Helper           $menuHelper
     * @param MatcherInterface $matcher
     */
    public function __construct(Helper $menuHelper, MatcherInterface $matcher)
    {
        $this->menuHelper = $menuHelper;
        $this->matcher = $matcher;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('knp_menu_currents', [$this, 'getCurrents'], ['is_safe' => ['html']]),
        );
    }

    /**
     * @param ItemInterface|string $menu
     * @param array                $path
     * @param array                $options
     *
     * @return ItemInterface[]
     *
     * @throws \BadMethodCallException   when there is no menu provider and the menu is given by name
     * @throws \LogicException
     * @throws \InvalidArgumentException when the path is invalid
     */
    public function getCurrents($menu, array $path = array(), array $options = array())
    {
        $menu = $this->menuHelper->get($menu, $path, $options);

        $menuIterator = new \RecursiveIteratorIterator(
            new RecursiveItemIterator(new \ArrayIterator([$menu])),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $currents = [];
        foreach ($menuIterator as $item) {
            /** @var $item ItemInterface */
            if ($this->matcher->isCurrent($item)) {
                $currents[] = $item->getName();
            }
        }

        return $currents;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pjax_menu';
    }
}
