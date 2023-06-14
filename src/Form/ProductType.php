<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductProperty;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    private $entityManager;

    public function __construct(
        private LoggerInterface $logger,
        private Security        $security,
        EntityManagerInterface  $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categoryRepository = $this->entityManager->getRepository(Category::class);

        $builder
            ->add('name')
            ->add('description')
            ->add('price')
            ->add('total')
            ->add('total_reserved')
            ->add('sku')
            ->add('category', EntityType::class, [
                'choice_label' => function ($category) {
                    return $category->getName();
                },
                'class' => Category::class,
                'label' => 'Category',
                'multiple' => false,
                'required' => true,
            ]);

        $user = $this->security->getUser();
        if (!$user) {
            throw new \LogicException(
                'The cannot be used without an authenticated user!'
            );
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {
            $form = $event->getForm();
            $form->add('owner', EntityType::class, [
                'choice_label' => function ($user) {
                    return $user->getName();
                },
                'choices' => [$user],
                'data' => $user,
                'class' => User::class,
                'label' => 'Owner',
                'required' => true,
            ]);
        });

        $formModifier = function (FormInterface $form, Category $category = null) {
            $properties = null === $category ? [] : $category->getProductProperties();

            $form->add('properties', EntityType::class, [
                'choice_label' => function ($prop) {
                    return $prop->getName();
                },
                'choices' => $properties,
                'class' => ProductProperty::class,
                'label' => 'Property',
                'multiple' => true,
                'required' => true,
                'group_by' => function ($choice, $key, $value) {
                    return $choice->getCode();
                },
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier, $categoryRepository) {
                $data = $event->getData();
                $category = $data->getCategory();
                if ($category == Null) {
                    $category = $categoryRepository -> getFirstCategory();
                }

                $formModifier($event->getForm(), $category);
            }
        );

        $builder->get('category')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $data);
            }
        );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
