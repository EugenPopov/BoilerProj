<?php


namespace App\Controller\Admin;


use App\DTO\ProductFormDTO;
use App\Entity\Image;
use App\Entity\Specification;
use App\Form\AddProductForm;
use App\Mappers\Product;
use App\Repository\CategoryRepository;
use App\Repository\ImageRepository;
use App\Repository\ProductRepository;
use App\Service\CategoryService;
use App\Service\ProductService;
use App\Service\UploadFileService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{


    public function getProducts($id, CategoryRepository $categoryRepository, PaginatorInterface $paginator, Request $request, ProductService $productService)
    {
        $sort = $request->get('sort_by');
        $category = $categoryRepository->findOneBy(['id' => $id]);
        if(empty($category))
            return new Response('Category not found', 404);

        $products = $productService->getProductsFromCategory($category);
        if(!empty($sort)){
            switch ($sort){
                case 'sale':
                    foreach ($products as $index => $product) {
                        if(empty($product->getSale()))
                            $products->remove($index);
                    }
                    break;
                case 'top':
                    foreach ($products as $index => $product) {
                        if(!$product->getSpecialOffer())
                            $products->remove($index);
                    }
                    break;
                case 'main':
                    foreach ($products as $index => $product) {
                        if(!$product->getIsOnMain())
                            $products->remove($index);
                    }
                    break;
            }
        }

        $products = $paginator->paginate(
            $products,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 30)
        );

        return $this->render('admin/admin_product.html.twig', [
            'products' => $products,
            'category' => $category,
            'sort' => $sort,
            'search_category' => $category->getId()]);
    }

    public function addProduct($id, CategoryRepository $categoryRepository, Request $request, EntityManagerInterface $entityManager, UploadFileService $fileService, ProductRepository $productRepository)
    {
        $category = $categoryRepository->findOneBy(['id' => $id]);
        if(empty($category))
            return new Response('Category not found', 404);

        $product = new ProductFormDTO();
        $product->setWeRecommend(json_encode($productRepository->getNameAndId()));
        $product->setCategory($category);
        $form = $this->createForm(AddProductForm::class, $product);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            /**
             * @var $data ProductFormDTO
             */
            $data = $form->getData();
            $product = Product::FormDTOToEntity($data);
            $product->setCategory($category);

            if(!empty($data->getWeRecommend())){
                $arr = json_decode($data->getWeRecommend());
                foreach ($arr as $id) {
                    $recomProduct = $productRepository->findOneBy(['id' => $id]);
                    if(!empty($recomProduct)){
                        $product->addRecommendProduct($recomProduct);
                    }
                }
            }

            $entityManager->persist($product);
            $entityManager->flush();

            foreach ($data->getImages() as $upload_image) {
                $image = new Image();
                $image->setProduct($product);
                $image->setImagePath($fileService->upload($upload_image));
                $entityManager->persist($image);
            }

            foreach (json_decode($data->getSpecification()) as $item) {
                $spec = new Specification();
                $spec->setName($item[0]);
                $spec->setUnit($item[1]);
                $spec->setValue($item[2]);
                $spec->setProduct($product);
                $entityManager->persist($spec);
            }
            $entityManager->flush();
            return $this->redirectToRoute('product', ['slug' => $product->getSlug()]);
        }
        return $this->render('admin/add_product.html.twig',['form' => $form->createView()]);

    }

    public function searchProduct($text, $sort,  ProductRepository $productRepository, Request $request, CategoryRepository $categoryRepository, CategoryService $categoryService)
    {
        $category = $request->get('category');
        $categories = null;
        if(!empty($category)){
            $category = $categoryRepository->findOneBy(['id' => $category]);
            $categories = $categoryService->getChildCategoriesId($category);
        }
        $products = $productRepository->searchProducts($text, $sort, $categories);
        $array = [];
        foreach ($products as $key => $product) {
                $array[$key]['name'] = $product->getName();
                if($product->getIsAvailable())
                    $array[$key]['is_available'] = 'Да';
                else
                    $array[$key]['is_available'] = 'Нет';
                $array[$key]['slug'] = $product->getSlug();
                $array[$key]['id'] = $product->getId();
                $array[$key]['maker'] = $product->getBrand()->getName();
                $array[$key]['price'] = $product->getRetailPrice().' '.$product->getCurrencyName();
                $array[$key]['category'] = $product->getCategory()->getName();
                $array[$key]['updated_at'] = $product->getUpdatedAt();
        }
        return new Response(json_encode($array));
    }

    public function deleteImage($id, ImageRepository $imageRepository, EntityManagerInterface $entityManager)
    {
        $image = $imageRepository->findOneBy(['id' => $id]);
        if(file_exists($this->getParameter('app.uploads_dir').$image->getImagePath()))
            unlink($this->getParameter('app.uploads_dir').$image->getImagePath());

        $entityManager->remove($image);
        $entityManager->flush();
        return new Response('', 200);
    }

    public function deleteProduct($id, ProductRepository $productRepository, EntityManagerInterface $entityManager)
    {
        $product = $productRepository->findOneBy(['id' => $id]);
        if(empty($product))
            return new Response('Product not found', 404);

        $entityManager->remove($product);
        $entityManager->flush();

        return $this->redirectToRoute('products');

    }

    public function editProduct($id, ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager, UploadFileService $fileService, Product $productMapper)
    {
        $product = $productRepository->findOneBy(['id' => $id]);
        if(empty($product))
            return new Response('Product not found', 404);
        $images =$product->getImages();
        $form = $this->createForm(AddProductForm::class, $productMapper->EntityToFormDTO($product));
        $form->handleRequest($request);


        if($form->isSubmitted()){
            $data = $form->getData();
            $product
                ->setCategory($data->getCategory())
                ->setName($data->getName())
                ->setDescription($data->getDescription())
                ->setWholesalePrice($data->getWholesalePrice())
                ->setRetailPrice($data->getRetailPrice())
                ->setIsAvailable($data->getIsAvailable())
                ->setIsVisible($data->getIsVisible())
                ->setSpecialOffer($data->getSpecialOffer())
                ->setProductUnit($data->getProductUnit())
                ->setSale($data->getSale())
                ->setCurrencyName($data->getCurrencyName())
                ->setBrand($data->getBrand())
                ->setMinimumWholesale($data->getMinimumWholesale())
                ->setIsOnMain($data->getIsOnMain())
                ->setSeoDescription($data->getSeoDescription());

            $recom = $product->getRecommendProduct();
            foreach ($recom as $item) {
                $product->removeRecommendProduct($item);
            }


            if(!empty($data->getWeRecommend())){
                $arr = json_decode($data->getWeRecommend());
                foreach ($arr as $id) {
                    $recommendProduct = $productRepository->findOneBy(['id' => $id]);
                    if(!empty($recommendProduct)){
                        $product->addRecommendProduct($recommendProduct);
                    }
                }
            }
            $entityManager->persist($product);

            foreach ($data->getImages() as $upload_image) {
                $image = new Image();
                $image->setProduct($product);
                $image->setImagePath($fileService->upload($upload_image));
                $entityManager->persist($image);
            }

            foreach ($product->getSpecifications() as $specification) {
                $entityManager->remove($specification);
            }

            foreach (json_decode($data->getSpecification()) as $item) {
                if(!empty($item)){
                    $spec = new Specification();
                    $spec->setName($item[0]);
                    if($item[1] != "")
                        $spec->setUnit($item[1]);
                    $spec->setValue($item[2]);
                    $spec->setProduct($product);
                    $entityManager->persist($spec);
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('product',['slug' => $product->getSlug()]);

        }

        return $this->render('admin/edit_product.html.twig',['form' => $form->createView(), 'images' => $images]);

    }
}