<?php

namespace App\EventSubscriber;

use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Repository\InventoryRepository;

class AfterPersistedSubscriber implements EventSubscriberInterface
{

    public function __construct(private InventoryRepository $inventoryRepository){

    }

    public function onAfterEntityPersistedEvent(AfterEntityPersistedEvent $event): void
    {
        dump($event);
        $sale = $event->getEntityInstance();

        if(!$sale instanceOf Sale){
            return;
        }
        //dump($sale);
        $id = $sale->getId();
        //dump($id);
        $sale->setExternalSalesOrderNumber($id + 100000);
        $this->entityManager->persist($sale);
        $this->entityManager->flush();
        $data = $sale->getSaleLineItems();
        
        if($data !== null){
            $prices = array();
            foreach($data as $item){
                //dump($item);
                $itm = $this->inventoryRepository->findOneBy(['id' => $item->getItem()]);
                dump($itm);
                $price = $itm->getPrice() /100;
                array_push($prices, $price);
            }
            $subtotal = array_sum($prices);
            //dump($subtotal);
            $ship = $sale->getShipViaCode();
            if($ship === 'OD'){
                $sale->setDeliveryAmount(95);
                $this->entityManager->persist($sale);
                $this->entityManager->flush();
            }
            else{
                $sale->setDeliveryAmount(0);
                $this->entityManager->persist($sale);
                $this->entityManager->flush();
            }
            $delivery = $sale->getDeliveryAmount();
            //dump($delivery);

            $salestax = $sale->getTaxPercentage();
            $tax = ($subtotal + $delivery) * $salestax;
            $total = $subtotal + $delivery + $tax;
            $sale->setSaleAmount($subtotal);
            $this->entityManager->persist($sale);
            $this->entityManager->flush();
            $sale->setTaxAmount($tax);
            $this->entityManager->persist($sale);
            $this->entityManager->flush();
            $sale->setTotalAmount($total);
            $this->entityManager->persist($sale);
            $this->entityManager->flush();
            
        }
        else{
            $subtotal = 0;
            $sale->setDeliveryAmount(0);
            $this->entityManager->persist($sale);
            $this->entityManager->flush();
            $sale->setSaleAmount($subtotal);
            $this->entityManager->persist($sale);
            $this->entityManager->flush();
            $sale->setTaxAmount($tax);
            $this->entityManager->persist($sale);
            $this->entityManager->flush();
            $sale->setTotalAmount($total);
            $this->entityManager->persist($sale);
            $this->entityManager->flush();
        };
        dump($sale);
        dd("This should work");
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => 'onAfterEntityPersistedEvent',
        ];
    }
}
