<?php

namespace WeEnd\ride;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\protocol\SetEntityLinkPacket;

use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
//如果你有使用ai插件
use pocketmine\entity\Pig;
//請自行取代
class Main extends PluginBase implements Listener{

  public $ride = array();

    public function onEnable(){
     $this->getServer()->getPluginManager()->registerEvents($this, $this);

     Item::addCreativeItem(new Item(329));
     Item::addCreativeItem(new Item(398));
}
    
    public function onPlayerQuit(PlayerQuitEvent $event){
     $name = $event->getPlayer()->getName();
      if(isset($this->ride[$name])){
       $this->remove($event->getPlayer());
       unset($this->ridepig[$name]);
      }
    }
    public function onPlayerDeath(PlayerDeathEvent $event){
     $player = $event->getEntity();
     $name = $player->getName();
      if($player instanceof Player){
       if(isset($this->ridepig[$name])){
        $this->remove($player);
        unset($this->ridepig[$name]);
       }
      }
    }
    public function remove($ride){
     if($ride instanceof Player){
       if(isset($this->ride[$ride->getName()])){
        $pk = new SetEntityLinkPacket();
         foreach($this->getServer()->getOnlinePlayers() as $player){
          if($player->getName() == $this->ride[$ride->getName()]){
           $pk->from = $player->getId();
          }
         }
         $pk->to = $ride->getId();
         $pk->type = SetEntityLinkPacket::TYPE_REMOVE;
         foreach($this->getServer()->getOnlinePlayers() as $players){
          if($player->getName() !== $ride->getName()){
           $players->dataPacket($pk);
          }
         }
         $pk = new SetEntityLinkPacket();
         foreach($this->getServer()->getOnlinePlayers() as $player){
          if($player->getName() == $this->ride[$ride->getName()]){
           $pk->from = $player->getId();
          }
         }
         $pk->to = 0;
         $pk->type = SetEntityLinkPacket::TYPE_REMOVE;
         $ride->dataPacket($pk);
       }
     }
    }
    public function onEntityDamage(EntityDamageEvent $event){
     if($event instanceof EntityDamageByEntityEvent){
      $ride = $event->getDamager();
      $player = $event->getEntity();
       if($ride instanceof Player){
        if($ride->getInventory()->getItemInHand()->getID() == 398){
         $event->setCancelled();
         if($player instanceof Pig){
          $this->ride[$ride->getName()] = $player->getId();
         
          $this->ridepig($ride);
         }
        }
       }
     }
    }
    public function ride($ride){
     if($ride instanceof Player){
       if(isset($this->ride[$ride->getName()])){
        $pk = new SetEntityLinkPacket();
         foreach($this->getServer()->getOnlinePlayers() as $player){
          if($player->getName() == $this->ride[$ride->getName()]){
           $pk->from = $player->getId();
          }
         }
         $pk->to = $ride->getId();
         $pk->type = SetEntityLinkPacket::TYPE_PASSENGER;
         foreach($this->getServer()->getOnlinePlayers() as $players){
          if($player->getName() !== $ride->getName()){
           $players->dataPacket($pk);
          }
         }
         $pk = new SetEntityLinkPacket();
         foreach($this->getServer()->getOnlinePlayers() as $player){
          if($player->getName() == $this->ride[$ride->getName()]){
           $pk->from = $player->getId();
          }
         }
         $pk->to = 0;
         $pk->type = SetEntityLinkPacket::TYPE_RIDE;
         $ride->dataPacket($pk);
       }
     }
    }
    public function ridepig($ride){
     if($ride instanceof Player){
       if(isset($this->ride[$ride->getName()])){
         $pk = new SetEntityLinkPacket();
         $pig = $ride->getLevel()->getEntity($this->ride[$ride->getName()]);
         $pk->from = $pig->getId();
         $pk->to = $ride->getId();
         $pk->type = SetEntityLinkPacket::TYPE_PASSENGER;
         foreach($this->getServer()->getOnlinePlayers() as $players){
          if($players->getName() !== $ride->getName()){
           $players->dataPacket($pk);
          }
         }
         $pk = new SetEntityLinkPacket();
         $pig = $ride->getLevel()->getEntity($this->ride[$ride->getName()]);
         $pk->from = $pig->getId();
         $pk->to = 0;
         $pk->type = SetEntityLinkPacket::TYPE_RIDE;
         $ride->dataPacket($pk);
       }
     }
    }

public function onJump(DataPacketReceiveEvent $event) {
		$packet = $event->getPacket ();
		if (! $packet instanceof PlayerActionPacket) {
			return;
		}
		$player = $event->getPlayer ();
		if ($packet->action === PlayerActionPacket::ACTION_JUMP && isset ( $this->ridepig [$player->getName ()] )) {
			$removepk = new RemoveEntityPacket ();
			$removepk->eid = $this->ridepig [$player->getName ()];
			$this->getServer ()->broadcastPacket ( $this->getServer ()->getOnlinePlayers (), $removepk );
			$this->remove($event->getPlayer());
			unset ( $this->ridepig [$player->getName ()] );
		}
	}
}
?>
