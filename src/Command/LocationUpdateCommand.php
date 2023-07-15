<?php

namespace App\Command;

use App\Entity\Inventory;
use App\Entity\ItemLocation;
use App\Helper\LocationMatcher;
use App\Helper\LocationHelper;
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
    name: 'app:location-update',
    description: 'Update Item Data'   
)]
class LocationUpdateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly InventoryApiClient $inventoryApiClient,
        private readonly LocationMatcher $locationMatcher,
        private readonly LocationHelper $locationHelper, 
        private readonly SerializerInterface $serializer,
        private readonly KernelInterface $kernel, 
        private readonly LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('Inventories', InputArgument::REQUIRED, 'Inventories')
            ->addArgument('Tenant', InputArgument::REQUIRED, 'Tenant')
            ->addArgument('vendor', InputArgument::REQUIRED, 'vendor')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            $vendor = $input->getArgument('vendor');

            //dump($Iid);

            $itemCache = new PhpFilesAdapter(
                $namespace = "inventory",
                $defaultLifetime = 0,
                // single file where values are cached
                $directory = $this->kernel->getProjectDir() . '/var/cache'
            );
            $itemList = $itemCache->getItem('inventoryIds_'.$vendor);
            $itemIdList = $itemList->get();

            $locationCache = new PhpFilesAdapter(
                $namespace = "location",
                $defaultLifetime = 0,
                // single file where values are cached
    
                $directory = $this->kernel->getProjectDir() . '/var/cache'
            );
            $locationsList = $locationCache->getItem('locations');
            $locsList = $locationsList->get();
            
            $locations = $this->inventoryApiClient->fetchQuantityById($input->getArgument('Inventories'), $input->getArgument('Tenant'));
            if($locations->getStatusCode() !==200){
                $output->writeln($locations->getContent());
                return Command::FAILURE;
            }

            $locs = json_decode($locations->getContent(), null, 512, JSON_THROW_ON_ERROR);

            foreach($itemIdList as $itemIds){
                foreach($itemIds as $Iid){
                    //dump($Iid);
                    $locationArray = $this->locationHelper->locationById($locs, $Iid);

                    $locs2 = $this->locationHelper->cachedById($locsList, $Iid);
                    //if api doesn't have any values for given id
                    if(($locationArray === [] && $locs2 !== []) )
                    {
                        //dump($Iid . " in cache and not in api");
                        foreach($locs2 as $loc2){
                            //dump($loc2);
                            //$output->writeln("removing location");
                            $id = $loc2['id'];
                            //dump($id);
                            $location = $this->entityManager->getRepository(ItemLocation::class)->findOneBy(['id' => $id]);
                            $item = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $Iid]);
                            $item->removeStock($location);
                            $this->entityManager->remove($location);
                            $this->entityManager->persist($location);
                            $this->entityManager->persist($item);
                            $this->entityManager->flush();
                            //dump($Iid ." location removed");

                        }
                            
                    }
                    else if($locationArray !== [] && $locs2 === []){
                        //dump($Iid . " location in api and not in cache");
                        foreach($locationArray as $loc){
                            $locItm = json_encode($loc);
                            $location = $this->serializer->deserialize($locItm, ItemLocation::class, 'json');
                            $item = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $Iid]);
                            $item->addStock($location);
                            $this->entityManager->persist($location);
                            $this->entityManager->persist($item);
                            //dump($Iid . ' location added and set');
                            $this->entityManager->flush(); 
                        }
                    }
                    else
                    {
                        //dump( $Iid . " location in api and maybe cache");
                        //dump($locationArray);
                        if(count($locationArray) >= count($locs2)){
                            //dump("count of api is greater than or equal to cache");
                            for($i=0; $i<count($locationArray); $i++){
                                $locItm = json_encode($locationArray[$i]);
                                if(isset($locs2[$i])){
                                    $result = $this->locationMatcher->matcher($locationArray[$i], $locs2[$i]);
                                    //dump('cache checkpoint');
                                    if($result === 'match'){
                                        //dump($Iid . ' location is a complete match. Skipping.');
                                    }
                                    else{
                                        if($location = $this->entityManager->getRepository(ItemLocation::class)->findOneBy(['id' => $locs2[$i]['id']])){
                                            $this->serializer->deserialize($locItm, ItemLocation::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $location]);
                                            $item = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $Iid]);
                                            $item->addStock($location);
                                            //dump('location exists and is set');
                                        }
                                        else{
                                            $location = $this->serializer->deserialize($locItm, ItemLocation::class, 'json');
                                            $item = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $Iid]);
                                            $item->addStock($location);
                                            //dump('location added and set');
                                        }
                                        $this->entityManager->persist($location);
                                        $this->entityManager->persist($item);
                                        $this->entityManager->flush();              
                                    }
                                }
                                else{
                                    //dump($Iid . ' theres more in api than cache');
                                    $location = $this->serializer->deserialize($locItm, ItemLocation::class, 'json');
                                    $item = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $Iid]);
                                    $item->addStock($location);
                                    $this->entityManager->persist($location);
                                    $this->entityManager->persist($item);
                                    $this->entityManager->flush();
                                    //dump('location added and set'); 
                                }
                            }
                        }

                        elseif(count($locationArray) < count($locs2))
                        {
                            //dump("cache count greater than api");
                            for($i=0; $i<count($locs2); $i++){
                                if(isset($locationArray[$i])){
                                    $locItm = json_encode($locationArray[$i]);
                                    $result = $this->locationMatcher->matcher($locationArray[$i], $locs2[$i]);
                                    //dump('api checkpoint');
                                    if($result === 'match'){
                                        //dump($Iid . ' location is a complete match. Skipping.');
                                    }
                                    else{
                                        if($location = $this->entityManager->getRepository(ItemLocation::class)->findOneBy(['id' => $locs2[$i]['id']])){
                                            $this->serializer->deserialize($locItm, ItemLocation::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $location]);
                                            $item = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $Iid]);
                                            $item->addStock($location);
                                            //dump('location exists and is set');
                                        }
                                        else{
                                            $location = $this->serializer->deserialize($locItm, ItemLocation::class, 'json');
                                            $item = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $Iid]);
                                            $item->addStock($location);
                                            //dump('location added and set');
                                        }
                                        $this->entityManager->persist($location);
                                        $this->entityManager->persist($item);
                                        $this->entityManager->flush();              
                                    }
                                }
                                else{
                                    //dump($Iid . ' theres more in cache than api');
                                    //dump($locs2[i]);
                                    //dump("removing location");
                                    $id = $locs2[$i]['id'];
                                    //dump($id);
                                    $location = $this->entityManager->getRepository(ItemLocation::class)->findOneBy(['id' => $id]);
                                    $item = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $Iid]);
                                    $item->removeStock($location);
                                    $this->entityManager->remove($location);
                                    $this->entityManager->persist($location);
                                    $this->entityManager->persist($item);
                                    $this->entityManager->flush();
                                    dump($id . "location removed"); 
                                }
                            }
                        }       
                    }
                }
            }
            return Command::SUCCESS;
        }
        
        catch(\Exception $exception){

            $error = $this->logger->warning($exception::class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile()
            . ' on line ' . $exception->getLine() . ' using [Location] ' . '[' . $input->getArgument('Inventories') .
            '/' . $input->getArgument('Tenant') . ']');

            dump($error);

            return Command::FAILURE;
        }
        
    }
}
