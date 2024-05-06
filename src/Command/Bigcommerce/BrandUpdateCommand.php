<?php

namespace App\Command\Bigcommerce;

use App\Entity\Vendors;
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
        name: 'app:bc-brand-update',
        description: 'send brand information to bigcommerce'   
    )]
class BrandUpdateCommand extends Command
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
            $brands = $this->client->getBrands()->getContent();
            $brands = json_decode($brands);
            foreach($brands as $brand){
                //dump($brand);
                foreach($brand as $brnd){
                    //dump($brnd);
                    if(isset($brnd->name)){
                        if($vendor = $this->entityManager->getRepository(Vendors::class)->findOneBy(['vendorName' => $brnd->name])){
                            $vendor->setPageTitle($brnd->page_title);
                            $vendor->setBrandImage($brnd->image_url);
                            $vendor->setBrandUrl($brnd->custom_url->url);
                            if($vendor->getBCId() === null){
                                $vendor->setBCId($brnd->id);
                                $this->entityManager->persist($vendor);
                                $this->entityManager->flush();
                                dump("Bigcommerce brand id set");
                            }  
                        }
                        else{
                            continue;
                        }
                    }
                } 
            }
            
            $vendor = $this->entityManager->getRepository(Vendors::class)->findAll();
            foreach($vendor as $v){
                if($v->getBCId() === null && $v->isActive()){
                    $body = json_encode(array(
                        "name" => $v->getVendorName(),
                        "page_title" => $v->getPageTitle()
                    ), JSON_FORCE_OBJECT);
                    //dump($body);
                    $response = $this->client->postBrand($body);
                }  
            }
            dump("Bigcommerce brands updated");
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