<?php


namespace App\Form;


use App\DTO\CategoryForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddCategoryForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название'
            ])
            ->add('description', TextareaType::class,[
                'label' => 'Описание',
                'required' => false,
                'attr' => [
                    'class' => 'summernote',
                ]
            ])
            ->add('seo_description', TextType::class, [
                'label' => 'SEO описание',
                'required' => false
            ])
            ->add('image', FileType::class,[
                'label' => 'Изображение',
                'required' => false,
            ])
            ->add('is_visible', CheckboxType::class,[
                'label' => 'Отображать',
                'required' => false
            ])
            ->add('usd', TextType::class,[
                'label' => 'Стоимость доллара, формат: "26.34"',
                'attr' => [
                    'step' => '0.001',
                    'pattern' => '[0-9]+([\.][0-9]+)?',
                    'min' => 0,
                ],
                'required' => false,
            ])
            ->add('eur', IntegerType::class,[
                'label' => 'Стоимость евро, формат: "26.34"',
                'attr' => [
                    'step' => '0.001',
                    'pattern' => '[0-9]+([\.][0-9]+)?',
                    'min' => 0,
                ],
                'required' => false,
            ])
            ->add('save', SubmitType::class, ['label' => 'Добавить/Изменить категорию'])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CategoryForm::class,
        ));
    }
}