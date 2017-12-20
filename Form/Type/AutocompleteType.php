<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\SearchBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdPropertyTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AutocompleteType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $allOptions = [
            'elastic_index',
            'elastic_type',
            'multiple',
            'required',
            'compound',
            'minimum_input_length',
            'ajax_item_id',
            'ajax_item_label',
            'allow_new_values',
        ];

        foreach ($allOptions as $opt) {
            $view->vars[$opt] = $options[$opt];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $admin = $options['sonata_field_description']->getAssociationAdmin();

        if ($admin !== null) {
            $builder->addViewTransformer(
                new ModelToIdPropertyTransformer(
                    $admin->getModelManager(),
                    $admin->getClass(),
                    $options['sonata_field_description']->getName(),
                    $options['multiple']
                ),
                true
            );
        }
        if ($options['multiple']) {
            $resizeListener = new ResizeFormListener(
                HiddenType::class, array(), true, true, true
            );

            $builder->addEventSubscriber($resizeListener);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('template', 'BlastSearchbundle:Form/Type:autocomplete.html.twig');
        $resolver->setDefault('elastic_index', 'global');
        $resolver->setDefault('elastic_type', null);
        $resolver->setDefault('class', 'form-control');
        $resolver->setDefault('multiple', false);
        $resolver->setDefault('compound', false);
        $resolver->setDefault('minimum_input_length', 3);
        $resolver->setDefault('ajax_item_id', 'id');
        $resolver->setDefault('ajax_item_label', 'text');
        $resolver->setDefault('allow_new_values', false);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'blast_search_autocomplete';
    }
}
