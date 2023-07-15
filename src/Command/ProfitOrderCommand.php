<?php

namespace App\Command;

use App\Entity\Inventory;
use App\Entity\ItemLocation;
use App\Entity\Vendors;
use App\Entity\Range;
use App\Entity\Sale;
use App\Entity\Customer;
use App\Entity\User;
use App\Helper\InventoryHelper;
use App\Helper\ItemMatcher;
use App\Http\ProfitSendClient;
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
use Symfony\Component\Console\Input\ArrayInput;
use Psr\Log\LoggerInterface;

#[AsCommand(
    name: 'app:send-profit-order',
    description: 'Send order to Profit'   
)]
class ProfitOrderCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly ProfitSendClient $profitSendClient,
        private readonly InventoryHelper $inventoryHelper,
        private readonly ItemMatcher $itemMatcher, 
        private readonly SerializerInterface $serializer,
        private readonly KernelInterface $kernel, 
        private readonly LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('Tenant', InputArgument::REQUIRED, 'Tenant')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            $orders = $this->entityManager->getRepository(Sale::class)->findByStatus('Ready');
            foreach($orders as $order){
                $customer = $order->getCustomer();
                $salesPerson = $order->getSalesperson();
                $lineitems = $order->getSaleLineItems();
                $items = array();
                foreach($lineitems as $lineitem){
                    $fulfillmentLine = (object) array(
                        'ItemDescription' => $lineitem->getItem()->getItemDescription(),
                        'MFCSKU' => $lineitem->getItem()->getMfcsku(),
                        'Vendor' => $lineitem->getItem()->getVendorId(),
                        'QuantityOrdered' => $lineitem->getQuantity(),
                        "UnitPrice" => $lineitem->getItem()->getPrice()/100,
                    );
                    array_push($items, $fulfillmentLine);
                };

                $body = array(
                    'OrderType' => $order->getOrderType(),
                    'ExternalSalesOrderNumber' => strval($order->getExternalSalesOrderNumber()),
                    'ShipViaCode' => $order->getShipViaCode(),
                    'PaymentLines' => array(array (
                        'PaymentMethod' => $order->getPaymentMethod(),
                        'PaymentAmount' => $order->getTotalAmount()
                    )),
                    'TaxCode' => $order->getTaxCode(),
                    'SaleAmount' => $order->getSaleAmount(),
                    'TaxAmount' => $order->getTaxAmount(),
                    'TotalAmount' => $order->getTotalAmount(),
                    'DeliveryAmount' => $order->getDeliveryAmount(),
                    'Customer' => array(
                        'Name' => $customer->getFirstname(). " " . $customer->getLastname(),
                        'Address1' => $customer->getAddress1(),
                        'Address2' =>$customer->getAddress2(),
                        'City' => $customer->getCity(),
                        'State' => $customer->getState(),
                        'PostalCode' => $customer->getPostalCode(),
                        'EMail' => $customer->getEmail(),
                        'Phones' => array( array(
                            'Number' => $customer->getPhone1()
                            ),
                            array(
                            'Number' => $customer->getPhone2()
                            ),
                            array(
                            'Number' => $customer->getPhone3()
                            )
                        ),
                    ),
                    'FulfillmentLines' => $items,
                    'FulfillmentParties' => array( array(
                        'Id' => null,
                        'PartyType' => 0,
                        'Description' => 'BillTo',
                        'Name' => $customer->getFirstname(). " " . $customer->getLastname(),
                        'Address1' => $customer->getAddress1(),
                        'Address2' =>$customer->getAddress2(),
                        'City' => $customer->getCity(),
                        'State' => $customer->getState(),
                        'PostalCode' => $customer->getPostalCode(),
                        ),
                        array(
                        'Id' => null,
                        'PartyType' => 1,
                        'Description' => 'ShipTo',
                        'Name' => $customer->getFirstname(). " " . $customer->getLastname(),
                        'Address1' => $customer->getShippingAddress1(),
                        'Address2' =>$customer->getShippingAddress2(),
                        'City' => $customer->getShippingCity(),
                        'State' => $customer->getShippingState(),
                        'PostalCode' => $customer->getShippingPostalCode(),
                        ),
                        array(
                        'Id' => null,
                        'PartyType' => 2,
                        'Description' => 'SalesPerson',
                        'Name' => $order->getSalesperson()->getFullname(),
                        )
                    ),
                );

                //dump(json_encode($body),'"');
                $orderSend = $this->profitSendClient->sendProfitOrder($input->getArgument('Tenant'), json_encode($body));
                $sale = $this->entityManager->getRepository(Sale::class)->findOneBy(['id' => $order->getId()]);
                //dump($sale);
                
                if($orderSend->getStatusCode() !== 200){
                    dump($orderSend->getContent());
                    return Command::FAILURE;
                }

                //dump($orderSend->getStatusCode());

                $sale->setPsStatus("Complete");
                $this->entityManager->persist($sale);
                $this->entityManager->flush();
            }
            
                    
            return Command::SUCCESS;
        }
        catch(\Exception $exception){

            $error = $this->logger->warning($exception::class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile()
            . ' on line ' . $exception->getLine() . ' using [ProductsByVendor] ' . '/' . $input->getArgument('Tenant') . ']');

            dump($error);

            return Command::FAILURE;
        }   
    }
}
