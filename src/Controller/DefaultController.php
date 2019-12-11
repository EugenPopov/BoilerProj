<?php


namespace App\Controller;



use App\DTO\Review as ReviewDto;
use App\Entity\Category;
use App\Entity\Image;
use App\Entity\NovaPoshtaCity;
use App\Entity\NovaPoshtaPostOffice;
use App\Entity\Product;
use App\Entity\Review;
use App\Entity\Specification;
use App\Entity\Visitors;
use App\Form\AddReview;
use App\Form\FilterForm;
use App\Mappers\Blog as BlogMapper;
use App\Mappers\Blog;
use App\Repository\BlogRepository;
use App\Repository\CategoryRepository;
use App\Repository\NovaPoshtaCityRepository;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Service\CommonInfoService;
use App\Service\FilterService;
use App\Service\ProductService;
use App\Service\SortService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CategoryService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DefaultController extends AbstractController
{

    public function index(CategoryRepository $categoryRepository, ProductRepository $productRepository, ProductService $productService, EntityManagerInterface $em)
    {
//        $ip = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_VALIDATE_IP)
//            ?: filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_VALIDATE_IP)
//                ?: $_SERVER['REMOTE_ADDR']
//                ?? '0.0.0.0';
//        $info = json_decode(file_get_contents('https://www.iplocate.io/api/lookup/'.$ip), true);
//        $visitor = new Visitors();
//        $visitor->setCity($info['city']);
//        $visitor->setCountry($info['country']);
//        $visitor->setIp($ip);
//        $visitor->setLat($info['latitude']);
//        $visitor->setLng($info['longitude']);
//        $visitor->setPage('Главная');
//        $visitor->setOrganization($info['org']);
//        $em->persist($visitor);
//        $em->flush();

        $mainCategories = $categoryRepository->findBy(['parent' => null]);
        $specialProducts = $productRepository->getSpecialProducts();
        $specialCollection = new ArrayCollection();
        foreach ($specialProducts as $specialProduct) {
            if($specialProduct->getIsVisible())
                $specialCollection->add($productService->getProductPrice($specialProduct));
        }
        $saleProducts = $productRepository->getSaleProducts();
        $saleCollection = new ArrayCollection();
        foreach ($saleProducts as $saleProduct) {
            if($saleProduct->getIsVisible())
                $saleCollection->add($productService->getProductPrice($saleProduct));
        }

        $mainPageProducts = $productRepository->getMainPageProducts();
        if(empty($mainPageProducts))
            $mainPageProducts = $productRepository->getRandomProducts();


        $mainPageCollection = new ArrayCollection();
        foreach ($mainPageProducts as $product) {
            if($product->getIsVisible())
                $mainPageCollection->add($productService->getProductPrice($product));
        }
        /**
         * @var $first \App\DTO\Product
         * @var $mainPageCollection \App\DTO\Product[]
         */
        $first = $mainPageCollection->get(0);
        $mainPageCollection->remove(0);

        return $this->render('index.html.twig',
            ['mainCategories' => $mainCategories,
             'specialProducts' => $specialCollection,
             'saleProducts' => $saleCollection,
             'mainPageProducts' => $mainPageCollection,
             'first' => $first,
            ]);
    }

    public function headerContacts(CommonInfoService $commonInfoService)
    {
        $phone = $commonInfoService->getParameter('phone_number');
        $address = $commonInfoService->getParameter('address');

        return $this->render('header_contacts.html.twig', ['phone' => $phone, 'address' => $address]);
    }

    public function responsiveContacts(CommonInfoService $service)
    {
        $phone = $service->getParameter('phone_number');
        $mail = $service->getParameter('mail');
        return $this->render('responsive_contacts.html.twig', ['phone' => $phone, 'mail' => $mail]);
    }

    public function footer(CommonInfoService $service)
    {
        $phone = $service->getParameter('phone_number');
        $address = $service->getParameter('address');
        $about_us = $service->getParameter('footer_about_us');
        $mail = $service->getParameter('mail');
        return $this->render('footer.html.twig', ['phone' => $phone, 'address' => $address, 'about_us' => $about_us, 'mail' => $mail]);
    }
    public function categoryAction($slug, CategoryRepository $categoryRepository, CategoryService $categoryService, ProductService $productService, FilterService $filterService, Request $request, PaginatorInterface $pagination, SortService $sortService)
    {
        $sort = $request->get('sort_by');
        $array = explode('/', rtrim($slug,'/'));
        if(empty($array[0])){
            return $this->render('catalog.html.twig', ['categories' => $categoryService->getCategories(), 'products' => null]);
        }
        $result = $categoryService->generateRoute($array);
        if (empty($result['cat']))
            throw $this->createNotFoundException();
        if ($result['gabela'])
            return $this->redirectToRoute('category', ['slug' => substr($categoryService->generateUrlFromCategory($result['cat']->getParent()), 1)]);

        $last_category = $categoryRepository->findOneBy(['slug' => $array[count($array)-1]]);
        if(!$last_category->getChildren()->isEmpty()) {
            list($categories, $products) = $categoryService->isLastCategory($array);
            $products = $products->toArray();
            $length = sizeof($products);
            if(!empty($sort))
                    $sortService->sort($sort, $products);
            else{
                usort($products, function($a, $b)
                {
                    return $a->isIsAvailable() < $b->isIsAvailable();
                });
            }
            $products = $pagination->paginate(
                $products,
                $request->query->getInt('page', 1),
                $request->query->getInt('limit', 20)
            );
            return $this->render('catalog.html.twig', ['categories' => $categories, 'category' => $last_category, 'products' => $products, 'sort' => $sort, 'length' => $length]);
        }
        elseif (!$last_category->getProducts()->isEmpty()){
            $filter = $filterService->buildFilter($last_category);
            $form = $this->createForm(FilterForm::class, null, ['filter' => $filter]);
            $form->handleRequest($request);
            if($form->isSubmitted()){
                $products = $filterService->isSubmited($form->getData(),$filter,$last_category, $request->get('page'), $sort)->toArray();
                $products = $pagination->paginate(
                    $products,
                    $request->query->getInt('page', 1),
                    $request->query->getInt('limit', 20)
                );
                $products->setTemplate('filter_pagination.html.twig');

                return $this->render('catalog.html.twig', ['categories' => null, 'products' => $products, 'category' => $last_category, 'form' => $form->createView(), 'sort' => $sort]);
            }


            $products = $productService->getProducts($last_category);
            $length = sizeof($products);
            $products = $products->toArray();
            if(!empty($sort)){
                    $sortService->sort($sort, $products);
            }
            else{
                usort($products, function($a, $b)
                {
                    return $a->isIsAvailable() < $b->isIsAvailable();
                });
            }


            $products = $pagination->paginate(
                $products,
                $request->query->getInt('page', 1),
                $request->query->getInt('limit', 20)
            );
            return $this->render('catalog.html.twig', ['categories' => null, 'products' => $products,  'category' => $last_category,'form' => $form->createView(), 'sort' => $sort, 'length' => $length]);
        }
        else{

            return $this->render('catalog.html.twig',['categories' => null, 'products' => null, 'sort' => $sort, 'category' => $last_category]);

        }
    }

    public function sendMail(Request $request, ProductRepository $productRepository, ProductService $productService, Swift_Mailer $mailer, SessionInterface $session, EntityManagerInterface $em, CommonInfoService $commonInfoService)
    {
        $ip = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_VALIDATE_IP)
            ?: filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_VALIDATE_IP)
                ?: $_SERVER['REMOTE_ADDR']
                ?? '0.0.0.0';
        $info = json_decode(file_get_contents('https://www.iplocate.io/api/lookup/'.$ip), true);
        $visitor = new Visitors();
        $visitor->setCity($info['city']);
        $visitor->setCountry($info['country']);
        $visitor->setIp($ip);
        $visitor->setLat($info['latitude']);
        $visitor->setLng($info['longitude']);
        $visitor->setPage('сделал заказ');
        $visitor->setOrganization($info['org']);
        $em->persist($visitor);
        $em->flush();


        $info['name'] = $request->get('name');
        $info['surname'] = $request->get('surname');
        $info['email'] = $request->get('email');
        $info['delivery']['type'] = $request->get('type');
        if ($request->get('type') == 2) {
            $info['delivery']['city'] = $request->get('city');
            $info['delivery']['data'] = $request->get('data');
        }
        $basket = $session->get('basket');

        $products = new ArrayCollection();
        $total = 0;

        foreach ($basket as $index => $item) {
            $product = $productService->getProductPrice($productRepository->findOneBy(['id' => $index]))->setAmount($item);
            $products->add($product);
            if(!empty($product->getProductValue())){
                if(!empty($product->getMinimumWholesale() && $product->getAmount() >= $product->getMinimumWholesale())){
                    if(!empty($product->getSale()))
                        $total += $product->getProductValue()*$product->getWholesalePrice()*$product->getAmount() - ($product->getProductValue()*$product->getWholesalePrice()*$product->getAmount()*$product->getSale()/100);
                    else
                        $total += $product->getProductValue()*$product->getWholesalePrice()*$product->getAmount();

                }
                else{
                    if(!empty($product->getSale()))
                        $total += $product->getProductValue()*$product->getRetailPrice()*$product->getAmount() - ($product->getProductValue()*$product->getRetailPrice()*$product->getAmount()*$product->getSale()/100);
                    else
                        $total += $product->getProductValue()*$product->getRetailPrice()*$product->getAmount();
                }
            }
            else{
                if(!empty($product->getMinimumWholesale() && $product->getAmount() >= $product->getMinimumWholesale())){
                    if(!empty($product->getSale()))
                        $total += $product->getWholesalePrice()*$product->getAmount() - ($product->getWholesalePrice()*$product->getAmount()*$product->getSale()/100);
                    else
                        $total += $product->getWholesalePrice()*$product->getAmount();

                }
                else{
                    if(!empty($product->getSale()))
                        $total += $product->getRetailPrice()*$product->getAmount() - ($product->getRetailPrice()*$product->getAmount()*$product->getSale()/100);
                    else
                        $total += $product->getRetailPrice()*$product->getAmount();
                }
            }
        }
        $email = $commonInfoService->getParameter('mail');
        $phone = $commonInfoService->getParameter('phone_number');
        $mail = new Swift_Message('Вы оформили заказ на сайте trubaTeplo!');
        $mail->setFrom('dzhezik@gmail.com')
            ->setTo('zhenya1995q@gmail.com')
            ->setBody(
                $this->renderView(
                    'confirmation.html.twig',
                    [
                        'name' => $info['name'],
                        'surname' =>$info['surname'],
                        'delivery' => $info['delivery'],
                        'products' => $products,
                        'total' => $total,
                        'mail' => $email,
                        'phone' => $phone
                    ]
                ),
                'text/html'
            );
        $mailer->send($mail);
        $session->remove('basket');
        return $this->redirectToRoute('index');
    }

    public function showAboutUs(CommonInfoService $commonInfoService)
    {
        $phone = $commonInfoService->getParameter('phone_number');
        $address = $commonInfoService->getParameter('address');
        $mail = $commonInfoService->getParameter('mail');
        $about = $commonInfoService->getParameter('about_us');
        return $this->render('about_us.html.twig', ['phone' => $phone, 'address' => $address, 'mail' => $mail, 'about' => $about]);
    }

    public function showBlog(BlogRepository $blogRepository)
    {
        $posts = $blogRepository->findBy(['is_visible' => true]);
        return $this->render('blog.html.twig', [
            'posts' => $posts
        ]);
    }

    public function showBlogPost(string $slug, BlogRepository $blogRepository)
    {
        $post = $blogRepository->findOneBy(['slug' => $slug]);
        if(empty($post))
            throw $this->createNotFoundException();
        $postDto = BlogMapper::entityToDto($post);
        $related = $blogRepository->findBy(['is_visible' => 1]);
        if (($key = array_search($post, $related)) !== false) {
            unset($related[$key]);
        }

        $related = array_reverse(array_slice($related,0, 3));

        return $this->render('blog_post.html.twig',[
            'post' => $postDto,
            'related' => $related
        ]);

    }

    public function showReviews(Request $request, EntityManagerInterface $manager, ReviewRepository $repository)
    {
        $reviews = $repository->findBy([],['date' => 'DESC']);
        $dto = new ReviewDto();
        $form = $this->createForm(AddReview::class,$dto);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $review = new Review();
            $review->setName($dto->getName())
                   ->setReview($dto->getReview())
                   ->setCons($dto->getCons())
                   ->setPros($dto->getPros())
                   ->setDate(new DateTime())
                   ->setRating($dto->getRating())
                   ->setIsVisible(true)
            ;
            $manager->persist($review);
            $manager->flush();
            return $this->redirectToRoute('showReviews');
        }
        return $this->render('reviews.html.twig',[
            'reviews' => $reviews,
            'form' => $form->createView()
        ]);
    }

    public function parceNP(EntityManagerInterface $em)
    {
        $string = file_get_contents('https://novaposhta.ua/shop/office/getjsonwarehouselist');
        $array = json_decode($string, true);
        $array = $array['response'];
        $cities = [];
        foreach ($array as $item) {
            if($item['warehouseTypeDescription'] == 'Поштове відділення' ||$item['warehouseTypeDescription'] == 'Вантажне відділення'){
                if(!(array_key_exists($item['city'],$cities))){
                    $city = new NovaPoshtaCity();
                    $city->setName($item['city']);
                    $em->persist($city);
                    $em->flush();
                    $cities[$item['city']] = $city;
                }

                $postOffice = new NovaPoshtaPostOffice();
                $postOffice
                    ->setCity($cities[$item['city']])
                    ->setAddress($item['address'])
                    ->setNumber($item['number']);
                $em->persist($postOffice);
            }
        }

        $em->flush();
        dd($cities);
    }

    public function reparce(CategoryRepository $categoryRepository, EntityManagerInterface $manager)
    {

        $batya = $categoryRepository->findOneBy(['id' => 259]);

//        $cat1 = new Category();
//        $cat1->setName('Трубы');
//        $cat1->setParent($batya);
//        $manager->persist($cat1);
//        $cat2 = new Category();
//        $cat2->setParent($batya);
//        $cat2->setName('Фитинги (Для внутренней канализации)');
//        $manager->persist($cat2);
//
//        $manager->flush();
//        dd();



        function callqq(Category $category, &$arr){
                foreach ($category->getChildren() as $child) {
                    callqq($child, $arr);
                    $arr[$child->getId()] = $child;
                }
//            if (!$category->getProducts()->isEmpty()){
//                foreach ($category->getProducts() as $product) {
//                    $arr[] = $product;
//                }
//            }
        }

        $arr = [];

        callqq($batya, $arr);

//        dd($arr);

        foreach ($arr as $item) {
//            $item->setCategory($batya);
//            $manager->persist($item);
            $manager->remove($item);
        }
        $manager->flush();
        dd();

//        $new_cat = new Category();
//        $new_cat->setName('Бойлер');
//        $manager->persist($new_cat);
//        $manager->flush();
//        $categories = $categoryRepository->findOneBy(['id' => 13]);
//        $arr = [];
//        foreach ($categories->getProducts() as $item) {
//            $arr[] = $item;
//        }
//        dd($arr);

//        $arr = [];
//
//        foreach ($categories->getProducts() as $product) {
//            $arr[] = $product;
//


//        $arr = [];
//        callqq($categories, $categories, $arr);
//        foreach ($arr as $item) {
//            $item->setCategory($new_cat);
//            $manager->persist($item);
//        }
//        $manager->flush();

//        foreach ($categories->getChildren() as $child) {
//            foreach ($child->getProducts() as $product) {
//                $product->setCategory($categories);
//                $manager->persist($product);
//            }
//            $manager->remove($child);
//        }
//        $manager->flush();






        function callback(Category $children, &$arr)
        {
//            foreach ($children->getProducts() as $product) {
//                $charac = [];
//                foreach ($product->getSpecifications() as $item) {
//                    $charac[$item->getId()] = $item->getName().' '.$item->getUnit().' '.$item->getValue();
//                }
//                $arr[$product->getId()] = [
//                    'product_name' => $product->getName(),
//                    'manufacturer' => $product->getManufacturer(),
//                    'producing_country' => $product->getProducingCountry(),
//                    'product_unit' => $product->getProductUnit(),
//                    'wholesale_price' => $product->getWholesalePrice().' '.$product->getCurrency()->getName().' / '.$product->getWholesalePrice()*$product->getCurrency()->getValue().' UAH',
//                    'wholesale_minimum' => $product->getMinimumWholesale(),
//                    'created_at' => $product->getCreatedAt(),
//                    'updated_at' => $product->getUpdatedAt(),
//                    'price' => $product->getRetailPrice().' '.$product->getCurrency()->getName().' / '.$product->getRetailPrice()*$product->getCurrency()->getValue().' UAH',
//                    'specification' => $charac,
//                    'description' => $product->getDescription(),
//                ];
//            }
//
//            if($children->getChildren()->isEmpty())
//                $arr = 'нет вложеностей';
//
//            foreach ($children->getChildren() as $child) {
//                $arr[$child->getName()] = [];
//                callback($child, $arr[$child->getName()]['вложеные категории']);
//            }
//        }
//
//        function parce_category(Category $category, &$arr, $categor){
//            foreach ($category->getChildren() as $child) {
//                parce_category($child, $arr, $categor);
//            }
//
//            foreach ($category->getProducts() as $product) {
//                $product->setCategory($categor);
//                $arr[] = $product->getCategory()->getName();
//            }
        }

        $arr = [];

//        foreach ($categories-> as $category) {
//            $arr[$category->getName()] = [];
//            callback($category, $arr[$category->getName()]);
//        }

        dd($arr);
    }

    public function addCategory(EntityManagerInterface $em)
    {
//        $client = new Client();
//
//        $currencies = $em->getRepository(Currency::class)->findAll();
//        $currencies_array = [];
//
//        foreach ($currencies as $currency) {
//            $currencies_array[$currency->getName()] = $currency;
//        }

        $reader = new Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load('base.xlsx');
        $categories = $spreadsheet->getActiveSheet()->toArray();
        dd($categories);
//        array_shift($categories);

        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load('import/BoilerProducts.xlsx');
        $products = $spreadsheet->getActiveSheet()->toArray();
        array_shift($products);


        $root = [];

        foreach ($categories as $key => $category) {
            if($category[2] == null){
                $rootCategory = new Category();
                $rootCategory
                    ->setName($category[1]);
                $em->persist($rootCategory);
                $em->flush();
                $root[$category[0]] = [$category[0], $category[1], $category[2], []];
                unset($categories[$key]);
                foreach ($categories as $index => $subcategory) {
                    if($subcategory[2] == $category[0]){
                        $root[$subcategory[2]][3][$subcategory[0]] = [$subcategory[0], $subcategory[1], $subcategory[2], []];
                        unset($categories[$index]);
                        $subCategoryRow = new Category();
                        $subCategoryRow
                            ->setParent($rootCategory)
                            ->setName($subcategory[1]);
                        $em->persist($subCategoryRow);
                        $em->flush();
                        foreach ($categories as $i => $item) {
                            if($item[2] == $subcategory[0]){
                                $root[$subcategory[2]][3][$subcategory[0]][3][$item[0]] = [$item[0], $item[1], $item[2], []];
                                unset($categories[$i]);
                                $subsubCategoryRow = new Category();
                                $subsubCategoryRow
                                    ->setParent($subCategoryRow)
                                    ->setName($item[1]);
                                $em->persist($subsubCategoryRow);
                                $em->flush();
                                foreach ($categories as $q => $last) {
                                    if($last[2] == $item[0]){
                                        $root[$subcategory[2]][3][$subcategory[0]][3][$item[0]][3][$last[0]] = [$last[0], $last[1], $last[2], []];
                                        unset($categories[$q]);
                                        $lastCategoryRow = new Category();
                                        $lastCategoryRow
                                            ->setParent($subsubCategoryRow)
                                            ->setName($last[1]);
                                        $em->persist($lastCategoryRow);
                                        $em->flush();
                                        if(empty($root[$subcategory[2]][3][$subcategory[0]][3][$item[0]][3][$last[0]][3])){
                                            foreach ($products as $indexqqq => $product) {
                                                if($product[14] == $last[0]){
                                                    $root[$subcategory[2]][3][$subcategory[0]][3][$item[0]][3][$last[0]][3][] = $product;

                                                    $newProduct1 = new Product();
                                                    $newProduct1
                                                        ->setDescription($product[3])
                                                        ->setName($product[1])
                                                        ->setCategory($lastCategoryRow)
                                                        ->setCurrency($currencies_array[$product[6]])
                                                        ->setProductUnit($product[7]);
                                                    if($product[12] == '-' || $product[12] == '0' || empty($product[12]))
                                                        $newProduct1->setIsAvailable(false);
                                                    else
                                                        $newProduct1->setIsAvailable(true);
                                                    $newProduct1
                                                        ->setManufacturer($product[24])
                                                        ->setProducingCountry($product[25])
                                                        ->setMinimumWholesale($product[10])
                                                        ->setSpecialOffer(false)
                                                        ->setRetailPrice($product[5])
                                                        ->setWholesalePrice($product[9]);

                                                    $em->persist($newProduct1);
                                                    $em->flush();

                                                    try{
                                                        $filename = md5(uniqid($product[11]));
                                                        $client->request('GET', $product[11],['save_to' => 'images/'.$filename]);
                                                        $image = new Image();
                                                        $image
                                                            ->setProduct($newProduct1)
                                                            ->setImagePath($filename);
                                                        $em->persist($image);

                                                    }
                                                    catch (Exception $exception){
                                                        echo $exception->getMessage();
                                                    }

                                                    $counter = 37;
                                                    while($counter != 154){
                                                        if(!empty($product[$counter])){
                                                            $specification = new Specification();
                                                            $specification
                                                                ->setName($product[$counter])
                                                                ->setUnit($product[$counter+1])
                                                                ->setValue($product[$counter+2])
                                                                ->setProduct($newProduct1);
                                                            $em->persist($specification);
                                                        }

                                                        $counter += 3;
                                                    }

                                                    unset($products[$indexqqq]);
                                                }
                                            }
                                        }
                                    }
                                }
                                if(empty($root[$subcategory[2]][3][$subcategory[0]][3][$item[0]][3])){
                                    foreach ($products as $indexqq => $product) {
                                        if($product[14] == $item[0]){
                                            $root[$subcategory[2]][3][$subcategory[0]][3][$item[0]][3][] = $product;

                                            $newProduct2 = new Product();
                                            $newProduct2
                                                ->setDescription($product[3])
                                                ->setName($product[1])
                                                ->setCategory($subsubCategoryRow)
                                                ->setCurrency($currencies_array[$product[6]])
                                                ->setProductUnit($product[7]);
                                            if($product[12] == '-' || $product[12] == '0' || empty($product[12]))
                                                $newProduct2->setIsAvailable(false);
                                            else
                                                $newProduct2->setIsAvailable(true);
                                            $newProduct2
                                                ->setManufacturer($product[24])
                                                ->setProducingCountry($product[25])
                                                ->setMinimumWholesale($product[10])
                                                ->setSpecialOffer(false)
                                                ->setRetailPrice($product[5])
                                                ->setWholesalePrice($product[9]);

                                            $em->persist($newProduct2);
                                            $em->flush();

                                            try{
                                                $filename = md5(uniqid($product[11]));
                                                $client->request('GET', $product[11],['save_to' => 'images/'.$filename]);
                                                $image = new Image();
                                                $image
                                                    ->setProduct($newProduct2)
                                                    ->setImagePath($filename);
                                                $em->persist($image);

                                            }
                                            catch (Exception $exception){
                                                echo $exception->getMessage();
                                            }

                                            $counter = 37;
                                            while($counter != 154){
                                                if(!empty($product[$counter])){
                                                    $specification = new Specification();
                                                    $specification
                                                        ->setName($product[$counter])
                                                        ->setUnit($product[$counter+1])
                                                        ->setValue($product[$counter+2])
                                                        ->setProduct($newProduct2);
                                                    $em->persist($specification);
                                                }

                                                $counter += 3;
                                            }

                                            unset($products[$indexqq]);
                                        }
                                    }
                                }
                            }
                        }
                        if(empty($root[$subcategory[2]][3][$subcategory[0]][3])){
                            foreach ($products as $indexq => $product) {
                                if($product[14] == $subcategory[0]){
                                    $root[$subcategory[2]][3][$subcategory[0]][3][] = $product;

                                    $newProduct3 = new Product();
                                    $newProduct3
                                        ->setDescription($product[3])
                                        ->setName($product[1])
                                        ->setCategory($subCategoryRow)
                                        ->setCurrency($currencies_array[$product[6]])
                                        ->setProductUnit($product[7]);
                                    if($product[12] == '-' || $product[12] == '0' || empty($product[12]))
                                        $newProduct3->setIsAvailable(false);
                                    else
                                        $newProduct3->setIsAvailable(true);
                                    $newProduct3
                                        ->setManufacturer($product[24])
                                        ->setProducingCountry($product[25])
                                        ->setMinimumWholesale($product[10])
                                        ->setSpecialOffer(false)
                                        ->setRetailPrice($product[5])
                                        ->setWholesalePrice($product[9]);

                                    try{
                                        $filename = md5(uniqid($product[11]));
                                        $client->request('GET', $product[11],['save_to' => 'images/'.$filename]);
                                        $image = new Image();
                                        $image
                                            ->setProduct($newProduct3)
                                            ->setImagePath($filename);
                                        $em->persist($image);

                                    }
                                    catch (Exception $exception){
                                        echo $exception->getMessage();
                                    }

                                    $em->persist($newProduct3);
                                    $em->flush();

                                    $counter = 37;
                                    while($counter != 154){
                                        if(!empty($product[$counter])){
                                            $specification = new Specification();
                                            $specification
                                                ->setName($product[$counter])
                                                ->setUnit($product[$counter+1])
                                                ->setValue($product[$counter+2])
                                                ->setProduct($newProduct3);
                                            $em->persist($specification);
                                        }

                                        $counter += 3;
                                    }

                                    unset($products[$indexq]);
                                }
                            }
                        }
                    }
                }
                if(empty($root[$category[0]][3])){
                    foreach ($products as $index => $product) {
                        if($product[14] == $category[0]){
                            $root[$category[0]][3][] = $product;
                            $newProduct4 = new Product();
                            $newProduct4
                                ->setDescription($product[3])
                                ->setName($product[1])
                                ->setCategory($rootCategory)
                                ->setCurrency($currencies_array[$product[6]])
                                ->setProductUnit($product[7]);
                                if($product[12] == '-' || $product[12] == '0' || empty($product[12]))
                                    $newProduct4->setIsAvailable(false);
                                else
                                    $newProduct4->setIsAvailable(true);
                                $newProduct4
                                    ->setManufacturer($product[24])
                                    ->setProducingCountry($product[25])
                                    ->setMinimumWholesale($product[10])
                                    ->setSpecialOffer(false)
                                    ->setRetailPrice($product[5])
                                    ->setWholesalePrice($product[9]);
                                $em->persist($newProduct4);
                                $em->flush();

                                try{
                                    $filename = md5(uniqid($product[11]));
                                    $client->request('GET', $product[11],['save_to' => 'images/'.$filename]);
                                    $image = new Image();
                                    $image
                                        ->setProduct($newProduct4)
                                        ->setImagePath($filename);
                                    $em->persist($image);

                                    }
                                catch (Exception $exception){
                                    echo $exception->getMessage();
                                }

                                $counter = 37;
                                while($counter != 154){
                                    if(!empty($product[$counter])){
                                        $specification = new Specification();
                                        $specification
                                            ->setName($product[$counter])
                                            ->setUnit($product[$counter+1])
                                            ->setValue($product[$counter+2])
                                            ->setProduct($newProduct4);
                                        $em->persist($specification);
                                    }

                                    $counter += 3;
                                }
                            unset($products[$index]);
                        }
                    }
                }
            }
        }

        $em->flush();

        dd($root, $categories, $products);

    }


    public function getNP(NovaPoshtaCityRepository $cityRepository, $slug, CategoryRepository $categoryRepository)
    {
        $slug = rtrim($slug,'/');
        $array = explode('/', $slug);
        if(empty($array[0]))
            throw $this->createNotFoundException();
        $count = count($array);
        foreach ($array as $key => $item) {
            if($count != $key+1){
                $found = false;
                $cat = $categoryRepository->findOneBy(['name' => $item]);
                if(empty($cat))
                    return new Response('ne nashol, pizda', 404);
                if(!empty($cat->getParent()) && $key == 0)
                    return new Response('u tebya batya est\'', 404);
                if($cat->getChildren()->isEmpty())
                    return new Response('bezdetniy', 404);
                echo $cat->getName().' id: '.$cat->getId().PHP_EOL;
                foreach ($categoryRepository->findOneBy(['name' => $item])->getChildren() as $child) {
                    echo $child->getName().' --- '.$array[$key+1].';;';
                    if($child->getName() == $array[$key+1]){
                        $found = true;
                        break;
                    }
                }
                if(!$found)
                    return new Response('huevaya cepochka', 404);
            }
        }

        $last_category = $categoryRepository->findOneBy(['name' => $array[count($array)-1]]);
//        dd($last_category);


        if(! $last_category->getChildren()->isEmpty())
            echo 'oh ebat\' u nego detey';
        elseif (!$last_category->getProducts()->isEmpty()){
            echo 'zaebumba, est\' tovari';
            foreach ($last_category->getProducts() as $product) {
                dump($product);
            }
        }
        dd('wow');
    }

    public function getHeaderCategories(CategoryRepository $categoryRepository, CategoryService $categoryService)
    {
        $categories = $categoryRepository->findBy(['parent'=>null]);
        $categories = $categoryService->generateUrlForAllCategories($categories);

        return new Response(json_encode($categories));
    }



}