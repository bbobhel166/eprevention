<?php

namespace Wunderman\EpreventionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MetierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('titre');
        $builder->add('code');
        $builder->add('remote_id');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wunderman\EpreventionBundle\Entity\Metier',
            'csrf_protection' => false
        ]);
    }

    public function getName()
    {
        return 'eprevention_bundle_metier_type';
    }
}
