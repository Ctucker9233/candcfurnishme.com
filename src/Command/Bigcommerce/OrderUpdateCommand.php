<?php

namespace App\Command\Bigcommerce;

use App\Entity\Sale;
use App\Entity\SaleLineItems;
use App\Entity\User;
use App\Entity\Customer;
use App\Entity\Inventory;
use App\Entity\Packages;
use App\Entity\Range;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use App\Http\Bigcommerce\Client;
use Psr\Log\LoggerInterface;

#[AsCommand(
        name: 'app:order-update',
        description: 'get order information from bigcommerce'   
    )]
class OrderUpdateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly SerializerInterface $serializer,
        private readonly KernelInterface $kernel,
        private readonly LoggerInterface $logger,
        private readonly Client $client
        )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('', InputArgument::OPTIONAL, '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        { 
            $min = $this->entityManager->getRepository(Range::class)->findOneBy(['id' => 1])->getMinId();
            $orders = $this->client->getOrders($min)->getContent();
            $orders = json_decode($orders);
            dump($orders);

            foreach($orders as $order)
            {
                if($order->is_deleted === false)
                {
                //dump($order);
                //dump($order->billing_address);
                    $shipping = "OD";

                    if($order->base_shipping_cost == 0)
                    {
                        $shipping = "CPU";
                    }

                    $custName = $order->billing_address->last_name .", ".$order->billing_address->first_name;
                    $orderId = $order->id;
                    $shipUrl = $order->shipping_addresses->resource;

                    $shippingAddress = json_decode($this->client->getShipping($shipUrl)->getContent());
                    //dump($shippingAddress[0]);

                    $state = $this->stateConverter($order->billing_address->state);
                    $shipState = $this->stateConverter($shippingAddress[0]->state);
                
                    $cust = array();
                    if($customer = $this->entityManager->getRepository(Customer::class)->findOneBy(['name' => $custName])){
                        //dump("customer found");
                        $cust = array(
                            'id'=> $customer->getId(),
                            'name' => $custName,
                            'firstname' => $order->billing_address->first_name,
                            'lastname' => $order->billing_address->last_name,
                            'address1' =>$customer->getAddress1(),
                            'address2' =>$customer->getAddress2(),
                            'city' =>$customer->getCity(),
                            'state' =>$customer->getState(),
                            'postalCode' =>$customer->getPostalCode(),
                            'email' => $order->billing_address->email,
                            'phone1' => $customer->getPhone1(),
                            'shippingAddress1' =>$shippingAddress[0]->street_1,
                            'shippingAddress2' =>$shippingAddress[0]->street_2,
                            'shippingCity' =>$shippingAddress[0]->city,
                            'shippingState' =>$shipState,
                            'shippingPostalCode' =>$shippingAddress[0]->zip, 
                        );
                        $cust = json_encode($cust);
                        $this->serializer->deserialize($cust, Customer::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $customer]);
                        $this->entityManager->persist($customer);
                        $this->entityManager->flush();
                    }
                    else{
                        $cust = array(
                            'name' => $custName,
                            'firstname' => $order->billing_address->first_name,
                            'lastname' => $order->billing_address->last_name,
                            'address1' =>$order->billing_address->street_1,
                            'address2' =>$order->billing_address->street_2,
                            'city' =>$order->billing_address->city,
                            'state' =>$state,
                            'postalCode' =>$order->billing_address->zip,
                            'email' => $order->billing_address->email,
                            'phone1' => $order->billing_address->phone,
                            'shippingAddress1' =>$shippingAddress[0]->street_1,
                            'shippingAddress2' =>$shippingAddress[0]->street_2,
                            'shippingCity' =>$shippingAddress[0]->city,
                            'shippingState' =>$shipState,
                            'shippingPostalCode' =>$shippingAddress[0]->zip,
                        );
                        $cust = json_encode($cust);
                        $customer = $this->serializer->deserialize($cust, Customer::class, 'json');
                        $this->entityManager->persist($customer);
                        $this->entityManager->flush();
                    }

                    $invoice = array(
                        'orderType' => "REGULAR SALE",
                        'externalSalesOrderNumber' => $order->id,
                        'shipViaCode' => $shipping,
                        'paymentMethod' => "Other",
                        'taxCode' => "EC",
                        'saleAmount' => floatval($order->total_ex_tax),
                        'taxAmount' => floatval($order->total_tax),
                        'totalAmount' => floatval($order->total_inc_tax),
                        'deliveryAmount' => floatval($order->shipping_cost_ex_tax),
                        'BcOrderNumber' => $order->id,
                        "BcStatus" => $order->status,
                        'taxPercentage' => ( floatval($order->total_tax) / floatval($order->total_ex_tax) ) * 100
                    );

                    $productsUrl = $order->products->resource;
                    //dump($productsUrl);
                    $items = json_decode($this->client->getOrderItems($productsUrl)->getContent());
                    //dump($items);

                    //dump("trying to get sale");
                    if($sale = $this->entityManager->getRepository(Sale::class)->findOneBy(['BcOrderNumber' => $order->id]))
                    {
                        dump("sale exists");
                        $so = json_encode($invoice);
                        $this->serializer->deserialize($so, Sale::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $sale]);
                        $this->entityManager->persist($sale);
                        $this->entityManager->flush();
                    }
                    else
                    {
                        //dump("no sale");
                        $so = json_encode($invoice);
                        $sale = $this->serializer->deserialize($so, Sale::class, 'json');
                        $customer = $this->entityManager->getRepository(Customer::class)->findOneBy(['name' => $custName]);
                        $sale->setCustomer($customer);
                        $salesPerson = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'websales']);
                        $sale->setSalesperson($salesPerson);
                        foreach($items as $item){
                            //dump("item value");
                            //dump($item->quantity);
                            $saleItem = array(
                                'quantity' => $item->quantity,
                                'price' => floatval($item->price_ex_tax));
                            $si = json_encode($saleItem);
                            //dump($item->quantity);
                            if($product = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $item->sku]))
                            {
                                //dump("got product");
                                if($saleitems = $this->entityManager->getRepository(SaleLineItems::class)->findBySaleId(['sale_id' => $sale->getId()]))
                                {
                                    //dump("items on sale");
                                    foreach($saleitems as $saleItem)
                                    {
                                        //dump($saleItem->getItem()->getItemID());
                                        if($saleItem->getItem()->getItemID() == $item->sku)
                                        {
                                            dump("item on sale");
                                        }
                                        else
                                        {
                                            $itm = $this->serializer->deserialize($si, SaleLineItems::class, 'json');
                                            $itm->setItem($product);
                                            $this->entityManager->persist($itm);
                                            $sale->addSaleLineItem($itm);
                                            $this->entityManager->persist($product);
                                            $this->entityManager->persist($sale);
                                        }
                                    }   
                                }
                                else
                                {
                                    $itm = $this->serializer->deserialize($si, SaleLineItems::class, 'json');
                                    $itm->setItem($product);
                                    $this->entityManager->persist($itm);
                                    $sale->addSaleLineItem($itm);
                                    $this->entityManager->persist($product);
                                    $this->entityManager->persist($sale);
                                }
                            }
                            elseif($product = $this->entityManager->getRepository(Packages::class)->findOneBy(['packageId' => $item->sku]))
                            {
                                $prodIds = $product->getComponentIds();
                                foreach($prodIds as $prodId)
                                {
                                    if($product = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $prodId]))
                                    {
                                        //dump("got product");
                                        if($saleitems = $this->entityManager->getRepository(SaleLineItems::class)->findBySaleId(['sale_id' => $sale->getId()]))
                                        {
                                            //dump("items on sale");
                                            foreach($saleitems as $saleItem)
                                            {
                                                //dump($saleItem->getItem()->getItemID());
                                                if($saleItem->getItem()->getItemID() == $item->sku)
                                                {
                                                    dump("package on sale");
                                                }
                                                else
                                                {
                                                    $itm = $this->serializer->deserialize($si, SaleLineItems::class, 'json');
                                                    $itm->setItem($product);
                                                    $this->entityManager->persist($itm);
                                                    $sale->addSaleLineItem($itm);
                                                    $this->entityManager->persist($product);
                                                    $this->entityManager->persist($sale);
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $itm = $this->serializer->deserialize($si, SaleLineItems::class, 'json');
                                            $itm->setItem($product);
                                            $this->entityManager->persist($itm);
                                            $sale->addSaleLineItem($itm);
                                            $this->entityManager->persist($product);
                                            $this->entityManager->persist($sale);
                                        }
                                    }    
                                }
                            }
                    

                            //echo "Adding customer";
                            //dump($sale);
                            
                        }
                        $sale->setPsStatus('Pending');
                            $this->entityManager->persist($sale);
                            $this->entityManager->persist($salesPerson);
                            $this->entityManager->persist($customer);
                            $this->entityManager->flush();
                    }
                
                }
            
            }
            return Command::SUCCESS;
            
        }
    
        catch(\Exception $exception)
        {
            $this->logger->warning($exception::class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile()
                . ' on line ' . $exception->getLine() . ']');

            $output->writeln('There has been an error. Check logs.');

            return Command::FAILURE;
        }
    }
    public function stateConverter($state){
        if($state === 'Pennsylvania'){
            $state = "PA";
            return $state;
        }
        elseif($state === 'California'){
            $state = "CA";
            return $state;
        }
        elseif($state === 'North Dakota'){
            $state = "ND";
            return $state;
        }
        elseif($state === "Texas"){
            $state = "TX";
            return $state;
        }
        elseif($state === "Maryland"){
            $state = "MD";
            return $state;
        }
        elseif($state === "Minnesota"){
            $state = "MN";
            return $state;
        }
        elseif($state === "Georgia"){
            $state = "GA";
            return $state;
        }
    }
      
}