<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\ProductProperty;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductPropertyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('code')
            ->add('value')
            ->add('category', EntityType::class, [
                'choice_label' => function ($category) {
                    return $category->getName();
                },
                'class' => Category::class,
                'label' => 'Category',
                'multiple' => false,
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductProperty::class,
        ]);
    }
}
