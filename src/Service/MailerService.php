<?php


namespace App\Service;


use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Swift_Mailer;
use Swift_Message;
use Twig\Environment;

class MailerService
{
        private $environment;
        private $mailer;
        private $productRepository;
        private $productService;


        public function __construct(Environment $environment, Swift_Mailer $mailer, ProductService $productService, ProductRepository $productRepository)
    {
        $this->environment = $environment;
        $this->mailer = $mailer;
        $this->productService = $productService;
        $this->productRepository = $productRepository;
    }


        public function mail(array $info, array $basket)
    {
        $products = new ArrayCollection();
        $total = 0;

        foreach ($basket as $index => $item) {
            $product = $this->productService->getProductPrice($this->productRepository->findOneBy(['id' => $index]))->setAmount($item);
            $products->add($product);
            if(!empty($product->getProductValue())){
                if(!empty($product->getMinimumWholesale() && $product->getAmount() >= $product->getMinimumWholesale()))
                    $total += $product->getProductValue()*$product->getWholesalePrice()*$product->getAmount();
                else
                    $total += $product->getProductValue()*$product->getRetailPrice()*$product->getAmount();
            }
            else{
                if(!empty($product->getMinimumWholesale() && $product->getAmount() >= $product->getMinimumWholesale()))
                    $total += $product->getWholesalePrice()*$product->getAmount();
                else
                    $total += $product->getRetailPrice()*$product->getAmount();
            }
        }

        $mail = new Swift_Message('Вы оформили заказ на сайте trubaTeplo!');
        $mail->setFrom('dzhezik@gmail.com')
            ->setTo('dzhezik@gmail.com ')
            ->setBody(
                $this->environment->render(
                    'confirmation.html.twig',
                    [
                        'name' => $info['name'],
                        'surname' =>$info['surname'],
                        'delivery' => $info['delivery'],
                        'products' => $products,
                        'total' =>$total
                    ]
                ),
                'text/html'
            );
        return $this->mailer->send($mail);

    }
}