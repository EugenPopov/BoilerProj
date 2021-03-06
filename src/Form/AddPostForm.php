<?php


namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddPostForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок поста'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Текст поста',
                'attr' => [
                    'rows' => 10,
                    'class' => 'summernote'
                ]
            ])
            ->add('image', FileType::class,[
                'label' => 'Фото',
            ])
            ->add('is_visible', CheckboxType::class,[
                'label' => 'Отображать',
                'required' => false
            ])
            ->add('save', SubmitType::class, ['label' => 'Добавить пост'])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }
}