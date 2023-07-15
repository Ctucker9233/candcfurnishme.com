<?php

namespace App\Command\Bigcommerce;

use App\Entity\Inventory;
use App\Entity\Packages;
use App\Helper\ItemMatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
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
        name: 'app:bc-item-sync',
        description: 'send item information to bigcommerce'   
    )]
class BCItemSyncCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly SerializerInterface $serializer,
        private readonly ItemMatcher $itemMatcher,
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
            $command = $this->getApplication()->find('app:bc-item-cache');
            $arguments = [];
            $input = new ArrayInput($arguments);
            $returnCode = $command->run($input, $output);
            //dump($returnCode);
            dump("bc items cached");

            $allItems = $this->client->getItems();
            //$items = json_decode($items[0]);
            //dump($allItems);
            for($i=0; $i<count($allItems); $i++){
                $items = json_decode($allItems[$i]->getContent());
                //dump($items);
                foreach($items as $item){
                //dump($brand);
                    foreach($item as $itm){
                        //dump($itm);
                        if(isset($itm->sku)){
                            $images = $this->client->getImages($itm->id);
                            $BCmatch = $this->itemMatcher->BCmatcher($itm, $images);
                            if($BCmatch === "match"){
                                //dump("nothing to update");
                            }
                            else{
                                if(isset($BCmatch['itemId'])){
                                    if($BCmatch['isDeleted'] === false && $BCmatch['webHide'] === false && $BCmatch['backorderCode'] === 'B'){
                                        if($im = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $itm->sku])){
                                            if($itm->name !== $im->getBcItemDescription()){
                                                $im->setBcItemDescription($itm->name);
                                            }
                                
                                            if(isset(json_decode($images->getContent())->data[0]->url_thumbnail)){
                                                if($im->getPictureLink() === null || $im->getPictureLink() !== (json_decode($images->getContent())->data[0]->url_thumbnail)){
                                                    //dump("Bigcommerce id set");
                                                    //dump(json_decode($images->getContent())->data[0]->url_thumbnail);
                                                    $im->setPictureLink(json_decode($images->getContent())->data[0]->url_thumbnail);
                                                    $this->entityManager->persist($im);
                                                    $this->entityManager->flush();
                                                } 
                                            }
                                            if($im->getBcItemId() === null || $im->getBcItemId() !== $itm->id){
                                                $im->setBcItemId($itm->id);
                                                $this->entityManager->persist($im);
                                                $this->entityManager->flush();
                                                dump("Bigcommerce id set");
                                            }
                                            if($im->getGtin() === null || $im->getGtin() !== $itm->gtin){
                                                $im->setGtin($itm->gtin);
                                                $this->entityManager->persist($im);
                                                $this->entityManager->flush();
                                            }
                                        }
                                    }
                                }
                                if(isset($BCmatch['packageId'])){
                                    if($p=$this->entityManager->getRepository(Packages::class)->findOneBy(['packageId' => $itm->sku])){
                                        if($p->isActive() === true){
                                            if($p->getBcPackDescription() === null || $itm->name !== $p->getBcPackDescription()){
                                            $p->setBcPackDescription($itm->name);
                                            }
                                            if(isset(json_decode($images->getContent())->data[0]->url_thumbnail)){
                                                if($p->getPackPicture() !== json_decode($images->getContent())->data[0]->url_thumbnail){
                                                    $p->setPackPicture(json_decode($images->getContent())->data[0]->url_thumbnail);
                                                    $this->entityManager->persist($p);
                                                    $this->entityManager->flush();
                                                }
                                            }
                                            if($p->getBcPackId() !== $itm->id){
                                                $p->setBcPackId($itm->id);
                                                $this->entityManager->persist($p);
                                                $this->entityManager->flush();
                                                dump("Bigcommerce id set");
                                            }
                                        }
                                    }  
                                }
                            }
                        }
                        else{
                            continue;
                        }
                    } 
                }
            }
            //dump($items->meta->pagination);
            // 
            
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
}