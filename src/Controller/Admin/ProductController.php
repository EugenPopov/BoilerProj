<?php


namespace App\Controller\Admin;


use App\DTO\ProductFormDTO;
use App\Entity\Image;
use App\Entity\Specification;
use App\Form\AddProductForm;
use App\Mappers\Product;
use App\Repository\CategoryRepository;
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
        $category = $categoryRepository->findOneBy(['id' => $id]);
        if(empty($category))
            return new Response('Category not found', 404);

        $products = $productService->getProductsFromCategory($category);

        $products = $paginator->paginate(
            $products,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 30)
        );

        return $this->render('admin/admin_product.html.twig', ['products' => $products]);
    }

    public function addProduct($id, CategoryRepository $categoryRepository, Request $request, EntityManagerInterface $entityManager, UploadFileService $fileService)
    {
        $category = $categoryRepository->findOneBy(['id' => $id]);
        if(empty($category))
            return new Response('Category not found', 404);

        $product = new ProductFormDTO();
        $product->setCategory($category);
        $form = $this->createForm(AddProductForm::class, $product);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $data = $form->getData();
            $product = Product::FormDTOToEntity($data);
            $product->setCategory($category);
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

    public function editProduct($id, ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager, UploadFileService $fileService)
    {
        $product = $productRepository->findOneBy(['id' => $id]);
        if(empty($product))
            return new Response('Product not found', 404);
        $form = $this->createForm(AddProductForm::class, Product::EntityToFormDTO($product));
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
                ->setBrand($data->getBrand());
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

        return $this->render('admin/edit_product.html.twig',['form' => $form->createView()]);

    }
}