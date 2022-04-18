<?php

namespace AntiCheat;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\player\Player;
use pocketmine\event\Listener;

class Main extends PluginBase implements Listener
{

    public $cfg;

    private $clicks;

    public function onEnable(): void{
        PermissionManager::getInstance()->addPermission(new Permission("fly.bypass","fly permission"));
        @mkdir($this->getDataFolder());
        $this->cfg = $this->getConfig();
        $this->saveDefaultConfig();
        @mkdir($this->getDataFolder()."players/");
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
        
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        if($event->getPlayer() instanceof Player){

            $config = new Config($this->getDataFolder()."players/".strtolower($event->getPlayer()->getName()).".yml", Config::YAML);

            $config->set('minerais',0);
            $config->save();

        }

    }

    public function onInteract(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        if($this->cfg->get("anticlick") == "true"){
        if($player instanceof Player){
            $this->addCPS($player);
        }
        if($this->getCPS($player) == $this->cfg->get("cpslimit")){
            $player->kick($this->cfg->get("clickkick"));
        }
      }
    }

    public function onFly(PlayerToggleFlightEvent $event){
        if($this->cfg->get("antifly") == "true"){
        if($event->getPlayer()->hasPermission("fly.bypass") && Server::getInstance->isOp($event->getPlayer()->getName())){
        }else{
            $event->getPlayer()->kick($this->cfg->get("flykick"));
        }
      }
    }

    public function onBreak(BlockBreakEvent $event){
        $config = new Config($this->getDataFolder()."players/".strtolower($event->getPlayer()->getName()).".yml", Config::YAML);
        $player = $event->getPlayer();
        if($this->cfg->get("antixray") == "true"){
        if($event->getBlock()->getId() == 14){
            $config->set('minerais', $config->get('minerais') + 1);
            $config->save();
        }
        if($event->getBlock()->getId() == 56){
            $config->set('minerais', $config->get('minerais') + 1);
            $config->save();
        }
        if($event->getBlock()->getId() == 129){
            $config->set('minerais', $config->get('minerais') + 1);
            $config->save();
        }
        if($config->get("minerais") === $this->cfg->get("orelimit")){
            $player->kick($this->cfg->get("xraykick"));
        }
      }
    }

    public function getCPS(Player $player): int{
        if(!isset($this->clicks[$player->getName()])){
            return 0;
        }
        $time = $this->clicks[$player->getName()][0];
        $clicks = $this->clicks[$player->getName()][1];
        if($time !== time()){
            unset($this->clicks[$player->getName()]);
            return 0;
        }
        return $clicks;
    }
	
    public function addCPS(Player $player): void{
        if(!isset($this->clicks[$player->getName()])){
            $this->clicks[$player->getName()] = [time(), 0];
        }
        $time = $this->clicks[$player->getName()][0];
        $clicks = $this->clicks[$player->getName()][1];
        if($time !== time()){
            $time = time();
            $clicks = 0;
        }
        $clicks++;
        $this->clicks[$player->getName()] = [$time, $clicks];
    }

    public function onDamage(EntityDamageByEntityEvent $event){
        if($this->cfg->get("antikb") == "true"){
        if($event->getKnockBack() == 0){
            $event->getPlayer()->kick($this->cfg->get("kbkick"));
        }
    }
    }
}
