<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NewsBundle\Block;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\ManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class RecentCommentsBlockService extends BaseBlockService
{
    protected $manager;

    protected $adminPool;

    /**
     * @param string           $name
     * @param EngineInterface  $templating
     * @param ManagerInterface $manager
     * @param Pool             $adminPool
     */
    public function __construct($name, EngineInterface $templating, ManagerInterface $manager, Pool $adminPool = null)
    {
        $this->manager = $manager;
        $this->adminPool = $adminPool;

        parent::__construct($name, $templating);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $criteria = array(
            'mode' => $blockContext->getSetting('mode'),
        );

        $parameters = array(
            'context'    => $blockContext,
            'settings'   => $blockContext->getSettings(),
            'block'      => $blockContext->getBlock(),
            'pager'      => $this->manager->getPager($criteria, 1, $blockContext->getSetting('number')),
            'admin_pool' => $this->adminPool,
        );

        if ($blockContext->getSetting('mode') === 'admin') {
            return $this->renderPrivateResponse($blockContext->getTemplate(), $parameters, $response);
        }

        return $this->renderResponse($blockContext->getTemplate(), $parameters, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('number', 'integer', array('required' => true)),
                array('title', 'text', array('required' => false)),
                array('mode', 'choice', array(
                    'choices' => array(
                        'public' => 'public',
                        'admin'  => 'admin',
                    ),
                )),
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Recent Comments';
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'number'     => 5,
            'mode'       => 'public',
            'title'      => 'Recent Comments',
            'template'   => 'SonataNewsBundle:Block:recent_comments.html.twig',
        ));
    }
}
