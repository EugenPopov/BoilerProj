<?php


namespace App\Form;


use App\DTO\CategoryForm;
use App\DTO\ProductFormDTO;
use App\Entity\Brand;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddProductForm extends AbstractType
{
    private $rep;

    /**
     * AddProductForm constructor.
     * @param $rep
     */
    public function __construct(CategoryRepository $rep)
    {
        $this->rep = $rep;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categories = $this->rep->findAll();
        foreach ($categories as $key => $category) {
            if(!$category->getChildren()->isEmpty())
                unset($categories[$key]);
        }


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
            ->add('seo_description', TextType::class,[
                'label' => 'SEO описание',
                'required' => false,
            ])
            ->add('category', ChoiceType::class,[
                'choices' => $categories,
                'choice_label' => 'name',
            ])
            ->add('images', FileType::class,[
                'label' => 'Изображения',
                'required' => false,
                'multiple' => true,
            ])
            ->add('is_visible', CheckboxType::class,[
                'label' => 'Отображение',
                'required' => false
            ])
            ->add('is_available', CheckboxType::class,[
                'label' => 'Наличие',
                'required' => false

            ])
            ->add('special_offer', CheckboxType::class,[
                'label' => 'Хит продаж',
                'required' => false

            ])
            ->add('is_on_main', CheckboxType::class,[
                'label' => 'Отображение на главной странице',
                'required' => false
            ])
            ->add('currency_name', ChoiceType::class,[
                'label' => 'Валюта',
                'choices'=>[
                    'UAH' => 'UAH',
                    'EUR' => 'EUR',
                    'USD' => 'USD',
                ],
            ])
            ->add('retail_price', TextType::class,[
                'label' => 'Розничная цена',
                'attr' => [
                    'step' => '0.001',
                    'pattern' => '[0-9]+([\.][0-9]+)?',
                    'min' => 0,
                ],
                'required' => true,
            ])
            ->add('wholesale_price', TextType::class,[
                'label' => 'Оптовая цена',
                'attr' => [
                    'step' => '0.001',
                    'pattern' => '[0-9]+([\.][0-9]+)?',
                    'min' => 0,
                ],
                'required' => false,
            ])
            ->add('minimumWholesale', IntegerType::class,[
                'label' => 'Минимальное количество для опта',
                'attr' => [
                    'min' => 1,
                    ],
                'required' => false
            ])
            ->add('product_unit', TextType::class, [
                'label' => 'Единица измерения',
                'required' => false,
            ])
            ->add('sale', IntegerType::class,[
                'label' => 'Скидка, значение в процентах',
                'attr' => [
                    'min' => 1,
                    'max' => 99
                ],
                'required' => false,
            ])
            ->add('weRecommend', HiddenType::class)
            ->add('specification', HiddenType::class)
            ->add('brand', EntityType::class, [
                'class' => Brand::class,
                'choice_label' => 'name',
            ])
            ->add('save', SubmitType::class, ['label' => 'Добавить/Изменить товар'])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ProductFormDTO::class,
        ));
    }
}