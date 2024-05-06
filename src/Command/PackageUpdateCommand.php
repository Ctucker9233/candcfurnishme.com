<?php

namespace App\Command;

use App\Entity\Packages;
use App\Entity\Inventory;
use App\Entity\ItemLocation;
use App\Helper\PackageMatcher;
use App\Helper\PackageHelper;
use App\Http\PackageApiClient;
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
    name: 'app:package-update',
    description: 'Update Package Data'   
)]
class PackageUpdateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly PackageApiClient $packageApiClient,
        private readonly PackageMatcher $packageMatcher,
        private readonly PackageHelper $packageHelper, 
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
            ->addArgument('Packages', InputArgument::REQUIRED, 'Packages')
            ->addArgument('Tenant', InputArgument::REQUIRED, 'Tenant')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            $command = $this->getApplication()->find('app:package-cache');
            $arguments3 = [];
            $input3 = new ArrayInput($arguments3);
            $returnCode2 = $command->run($input3, $output);
            $this->logger->info($returnCode2);
            
            $items = $this->packageApiClient->fetchPackageItems($input->getArgument('Packages'), $input->getArgument('Tenant'));

            if($items->getStatusCode() !==200){
                dump($items->getContent());
                return Command::FAILURE;
            }
            $inventory = json_decode($items->getContent(), null, 512, JSON_THROW_ON_ERROR);

            foreach($inventory as $package)
            {
                //dump($package);
            //     //dump($id);
                $checkPkg = json_decode(json_encode($package, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
                $pkgPrice = $this->packageApiClient->fetchPackageItem($input->getArgument('Products'), $input->getArgument('Tenant'), $checkPkg['packageId']);

                if($pkgPrice->getStatusCode() !==200){
                    //dump($pkgPrice->getContent());
                    return Command::FAILURE;
                }

                //dump(json_decode($pkgPrice->getContent())->Prices);
                $checkPrice = 0;
                if(null !== json_decode($pkgPrice->getContent())->Prices[0]->Price){
                    $checkPrice = (json_decode($pkgPrice->getContent())->Prices[0]->Price) * 100;
                }
                //dump($checkPrice);
                
            //     //dump($items->getContent());
        
            //     echo "Back in Command";

                $pack = json_encode($package, JSON_THROW_ON_ERROR);
            //     dump($pkg);
                
                
                $Ids = $package->componentIds;
                //dump($Ids);
            //     //dump("vendor id " . $Vid . "item id " . $Id);
                $components = [];

                foreach($Ids as $id)
                {
                    //dump($id);
                    if($component = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $id])){
                        array_push($components, $component->getQuantity());
                    };
                    
                }
                //dump($components);
                $PkgQuantity = $this->packageHelper->pkgQuantity($components, $package);
                //dump($quantity);
                $result = $this->packageMatcher->matcher($checkPkg, $PkgQuantity, $checkPrice);

                if($result === 'match')
                {
                    dump($checkPkg['packageId'] . ' is a complete match. Skipping.');
                }
                else
                {
                    if($packages = $this->entityManager->getRepository(Packages::class)->findOneBy(['packageId' => $checkPkg['packageId']]))
                    {
                        dump($checkPkg['packageId'] . "item exists");
                        //dump($checkPrice);
                        $this->serializer->deserialize($pack, Packages::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $packages]);
                        // //dump($quantity);
                        $packages->setPkgQuantity($PkgQuantity);
                        $packages->setPrice($checkPrice);
                        $pieces = $packages->getItemIds();
                        dump($pieces);
                        if($pieces !== null){
                            foreach($pieces as $piece)
                            {
                                $packages->removeItemId($piece);
                                $this->entityManager->persist($packages);
                                $this->entityManager->flush();
                            }   
                        }
                                               
                        foreach($Ids as $id)
                        {   
                            $item = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $id]);
                            $packages->addItemId($item);
                            $this->entityManager->persist($packages);
                            $this->entityManager->persist($item);
                            $this->entityManager->flush();
                        } 
                    }
                    else
                    {
                        dump("item doesn't exist");
                        $packages = $this->serializer->deserialize($pack, Packages::class, 'json');
                        $packages->setPkgQuantity($PkgQuantity);
                        $packages->setPrice($checkPrice);
                        $packages->setActive(false);
                        //echo 'bundle quantity set';
                        foreach($Ids as $id)
                        {
                            $item = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $id]);
                            $this->entityManager->persist($packages);
                            $this->entityManager->persist($item);
                            $this->entityManager->flush();
                        }
                    };
                    
                    $this->entityManager->persist($packages);
                    $this->entityManager->flush();   
                }   
            }
            dump("packages updated");
            return Command::SUCCESS;
        }
        catch(\Exception $exception){

            $error = $this->logger->warning($exception::class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile()
            . ' on line ' . $exception->getLine() . ' using [Packages] ' . '[' . $input->getArgument('Packages') .
            '/' . $input->getArgument('Tenant') . ']');

            dump($error);

            return Command::FAILURE;
        }
        
    }
}
