<?php

namespace OzzyLuxx\Compensation;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\{Command, CommandSender};
use pocketmine\utils\Config;
use pocketmine\console\ConsoleCommandSender;
use OzzyLuxx\Compensation\Form\SimpleForm;

class Compensation extends PluginBase implements Listener {
  public Config $cfg;
  
  public function onEnable() : void {
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->saveResource("config.yml");
    $this->saveResource("data.yml");
    $this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
    $this->dt = new Config($this->getDataFolder() . "data.yml", Config::YAML);
    $this->prefix = $this->cfg->get("Prefix");
    $this->getLogger()->notice("Plugin Activated");
  }
  
  public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
    if($cmd->getName() == "compensation"){
      if($sender instanceof Player){
        $this->openCompUI($sender);
      } else {
        $sender->sendMessage($this->prefix . "Use Command In Game!");
      }
    }
    return true;
  }
  
  public function openCompUI(Player $player) : void {
    $form = new SimpleForm(function(Player $player, $data){
      if(is_null($data)){
        return true;
      }
      switch($data){
        case 0:
          if($this->dt->exists('"'.$player->getName().'"')){
            $player->sendMessage($this->prefix . $this->cfg->get("msg-claimed"));
          }else{
            foreach ($this->cfg->get("Reward") as $command) {
              $this->getServer()->dispatchCommand(new ConsoleCommandSender($this->getServer(), $this->getServer()->getLanguage()), str_replace('{player}', '"' . $player->getName() . '"', $command));
          }
          $this->getServer()->broadcastMessage(str_replace('{player}', $player->getName(), $this->prefix . $this->cfg->get("msg-success")));
          $this->dt->setNested('"'.$player->getName().'"', true);
          $this->dt->save();
         }
        break;
      case 1:
        break;
      }
    });
    $form->setTitle($this->cfg->get("Title-Form"));
    $form->setContent($this->cfg->get("Content-Form"));
    $form->addButton($this->cfg->get("Claim-Compensation"));
    $form->addButton($this->cfg->get("Exit"),0,"textures/ui/cancel");
    $player->sendForm($form);
  }
}