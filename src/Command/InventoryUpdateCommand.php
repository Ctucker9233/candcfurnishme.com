<?php

namespace App\Command;

use App\Entity\Inventory;
use App\Entity\ItemLocation;
use App\Entity\Vendors;
use App\Entity\Range;
use App\Helper\InventoryHelper;
use App\Helper\ItemMatcher;
use App\Http\InventoryApiClient;
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
    name: 'app:inventory-update',
    description: 'Update Item Data'   
)]
class InventoryUpdateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly InventoryApiClient $inventoryApiClient,
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
            ->addArgument('ProductsByVendor', InputArgument::REQUIRED, 'Products')
            ->addArgument('InventoriesByVendor', InputArgument::REQUIRED, 'locations')
            ->addArgument('Tenant', InputArgument::REQUIRED, 'Tenant')
            ->addArgument('vendor', InputArgument::REQUIRED, 'vendor')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            $vendor = $input->getArgument('vendor');
            $cache = new PhpFilesAdapter(
                $namespace = "inventory",
                $defaultLifetime = 0,
                // single file where values are cached
                $directory = $this->kernel->getProjectDir() . '/var/cache'
            );
            $itemIdCache = $cache->getItem('inventoryIds_'.$vendor);
            $ids = $itemIdCache->get();
            //foreach($ids as $id){
                //foreach($id as $i){
            $itemGet = $this->inventoryApiClient->fetchItems($input->getArgument('ProductsByVendor'), $input->getArgument('Tenant'), $vendor);
                    
            if($itemGet->getStatusCode() !==200){
                //dump($itemGet->getContent());
                return Command::FAILURE;
            }

            $items = json_decode($itemGet->getContent());
            //dump($items);
            foreach($items as $item){
                    //if($itm === 'no result'){
                        //dump("no item with id" . $itm->id . ".");
                    //}
                    //else{
                $Id = $item->itemId;
                //dump($Id);
                if($item->backorderCode === "B"){
                            
                            //$vendor = $itm->vendorId;
                            
                    $BcVid = $this->entityManager->getRepository(Vendors::class)->findOneBy(['vendorId' => $vendor])->getBCId();

                    $locations = $this->inventoryApiClient->fetchQuantity($input->getArgument('InventoriesByVendor'), $input->getArgument('Tenant'), $vendor);
                                
                    if($locations->getStatusCode() !==200 ){
                        //dump($locations->getContent());
                        return Command::FAILURE;
                    };

                    $quantity = json_decode($locations->getContent(), null, 512, JSON_THROW_ON_ERROR);
                    $quantities = $this->inventoryHelper->locationFilter($quantity, $Id);
                    $QOH = $this->inventoryHelper->quantityCounter($quantities);

                    $result = $this->itemMatcher->matcher($item, $QOH, $vendor, $Id);
                    if($result === 'match'){
                        $this->logger->info($Id . ' is a complete match. Updating quantities.');
                    }
                    else{
                        if($itm = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $Id]))
                        {
                        
                            $this->serializer->deserialize(json_encode($item), Inventory::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $itm]);
                            $itm->setQuantity($QOH);
                            $itm->setBCVendorId($BcVid);
                            //echo 'item quantity set';
                
                            //dump("Item id ".$Id." updated"); 
                        }
                        else
                        {
                            $itm = $this->serializer->deserialize(json_encode($item), Inventory::class, 'json');
                            $itm->setQuantity($QOH);
                            $itm->setBCVendorId($BcVid);
                            //dump("new item");
                            //dump("Item id ".$Id." added");           
                        }
                        $this->entityManager->persist($itm);
                        $this->entityManager->flush();
                    }  
                }
                else{
                    $this->logger->info("Item id ".$Id." discontinued");
                }
            }
                //} 
            //}
            
            return Command::SUCCESS;
        }
        catch(\Exception $exception){

            $error = $this->logger->warning($exception::class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile()
            . ' on line ' . $exception->getLine() . ' using [ProductsByVendor] ' . '[' . $input->getArgument('ProductsByVendor') .
            '/' . $input->getArgument('Tenant') . ']');

            dump($error);

            return Command::FAILURE;
        }   
    }
}
