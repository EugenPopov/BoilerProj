<?php


namespace App\Controller\Admin;


use App\Entity\Category;
use App\Form\AddCategoryForm;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use App\Service\UploadFileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends AbstractController
{
    public function categoriesView()
    {
        return $this->render('admin/admin_categories.html.twig');
    }

    public function delete_category($id, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager)
    {
        $category = $categoryRepository->findOneBy(['id' => $id]);
        if(empty($category))
            return new Response('Category not found', 404);
        $entityManager->remove($category);
        $entityManager->flush();
        return $this->redirectToRoute('categories');
    }

    public function edit_category($id, CategoryRepository $categoryRepository, Request $request, UploadFileService $fileService, EntityManagerInterface $manager)
    {
        $category = $categoryRepository->findOneBy(['id' => $id]);
        if(empty($category))
            return new Response('Category not found', 404);
        $form = $this->createForm(AddCategoryForm::class, \App\Mappers\Category::entityToFormDTO($category));
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $data = $form->getData();
            $category
                ->setName($data->getName())
                ->setDescription($data->getDescription())
                ->setUsdValue($data->getUsd())
                ->setEurValue($data->getEur())
                ->setIsVisible($data->getIsVisible())
                ->setSeoDescription($data->getSeoDescription());

            if(!empty($data->getImage()))
                $category->setImage($fileService->upload($data->getImage()));
            $manager->persist($category);
            $manager->flush();
            return $this->redirectToRoute('categories');
        }

        return $this->render('admin/edit_subcategory.html.twig', ['form' => $form->createView()]);
    }


    public function add_sub_category($id, CategoryRepository $categoryRepository, Request $request, UploadFileService $fileService, EntityManagerInterface $manager)
    {
        $parent = $categoryRepository->findOneBy(['id' => $id]);
        if(empty($parent))
            return new Response('Category not found', 404);
        $form = $this->createForm(AddCategoryForm::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $category = new Category();
            $category
                ->setParent($parent)
                ->setName($data->getName())
                ->setDescription($data->getDescription())
                ->setUsdValue($data->getUsd())
                ->setEurValue($data->getEur())
                ->setIsVisible($data->getIsVisible())
                ->setSeoDescription($data->getSeoDescription());
            if(!empty($data->getImage()))
                $category->setImage($fileService->upload($data->getImage()));
            $manager->persist($category);
            $manager->flush();
            return $this->redirectToRoute('categories');
        }

        return $this->render('admin/edit_subcategory.html.twig', ['form' => $form->createView()]);
    }

    public function addCategory(Request $request, UploadFileService $fileService, EntityManagerInterface $manager)
    {
        $form = $this->createForm(AddCategoryForm::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $category = new Category();
            $category
                ->setName($data->getName())
                ->setDescription($data->getDescription())
                ->setUsdValue($data->getUsd())
                ->setEurValue($data->getEur())
                ->setIsVisible($data->getIsVisible())
                ->setSeoDescription($data->getSeoDescription());
            if(!empty($data->getImage()))
                $category->setImage($fileService->upload($data->getImage()));
            $manager->persist($category);
            $manager->flush();
            return $this->redirectToRoute('categories');
        }

        return $this->render('admin/edit_subcategory.html.twig', ['form' => $form->createView()]);
    }
}