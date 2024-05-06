<?php

namespace App\Command;

use App\Entity\Range;
use App\Entity\ProductIdsByVendor;
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
    name: 'app:item-id-by-vendor-update',
    description: 'Update Id Data'   
)]
class ItemIdsByVendorUpdateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly InventoryApiClient $inventoryApiClient, 
        private readonly SerializerInterface $serializer,
        private readonly KernelInterface $kernel, 
        private readonly LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
        ->addArgument('Products', InputArgument::REQUIRED, 'Products')
        ->addArgument('InventoriesByVendor', InputArgument::REQUIRED, 'locations')
        ->addArgument('Tenant', InputArgument::REQUIRED, 'Tenant')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            $range = $this->entityManager->getRepository(Range::class)->findAll();
            $minId = intval($range[0]->getMinId());
            $maxId = intval($range[0]->getMaxId());
            $itemArray = array();
            for($i=$minId; $i<=$maxId; $i++){
                $itemGet = $this->inventoryApiClient->fetchItems($input->getArgument('Products'), $input->getArgument('Tenant'), $i);
                
                if($itemGet->getStatusCode() !==200){
                    dump($items->getContent());
                    return Command::FAILURE;
                }

                $item = json_decode(json_encode($itemGet->getContent()), true);
                array_push($itemArray, $item);
                
            }
            //dump($itemArray);
            return Command::SUCCESS;
        }
        catch(\Exception $exception){

            $error = $this->logger->warning($exception::class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile()
            . ' on line ' . $exception->getLine() . ' using [Products] ' . '[' . $input->getArgument('vendor') .
            '/' . ']');

            dump($error);

            return Command::FAILURE;
        }
        
    }

    public function matcher($search, $item, $QOH, $vendor, $BcVid)
    {

        if($search->itemId === $item->getItemID()){
            //dump($search['itemId']);
            //dump($item['itemId']);
            if($search->itemDescription === $item->getItemDescription()){
                //dump($search['itemDescription']);
                //dump($item['itemDescription']);
                if($search->mfcsku === $item->getMfcsku()){
                    //dump($search['mfcsku']);
                    //dump($item['mfcsku']);
                    if($search->price == $item->getPrice()){
                        //dump($search['price']);
                        //dump($item['price']);
                        if($search->upcCode === $item->getUpcCode()){
                            //dump($search['upcCode']);
                            //dump($item['upcCode']);
                            if($search->vendorId === $item->getVendorId()){
                                //dump($search['vendorId']);
                                //dump($item['vendorId']);
                                if($search->vendorName === $item->getVendorName()){
                                    //dump($search['vendorName']);
                                    //dump($item['vendorName']);
                                    if($search->webHide == $item->isWebHide()){
                                        //dump($search['webHide']);
                                        //dump($item['webHide']);
                                        if($search->backorderCode === $item->getBackorderCode()){
                                            //dump($search['backorderCode']);
                                            //dump($item['backorderCode']);
                                            if($QOH === $item->getQuantity()){
                                                // dump($QOH);
                                                //dump($item['quantity']);
                                                if($search->isDeleted === $item->isIsDeleted()){
                                                    if($search->isPackage === $item->isIsPackage()){
                                                        if($BcVid === $item->getBCVendorId()){
                                                            //dump('made it');
                                                            return 'match';
                                                        }                                                               
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}