<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Thread;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text')
            ->add('createdDate')
            ->add('status')
            ->add('author', EntityType::class, [
                'class' => User::class,
'choice_label' => 'id',
            ])
            ->add('thread', EntityType::class, [
                'class' => Thread::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
