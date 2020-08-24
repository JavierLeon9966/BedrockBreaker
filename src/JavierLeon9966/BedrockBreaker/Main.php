<?php
declare(strict_types = 1);
namespace JavierLeon9966\BedrockBreaker;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\block\BlockFactory;
use pocketmine\tile\Tile;
use JavierLeon9966\BedrockBreaker\commands\{BBResistanceCommand, BBExplosionsCommand, BreakableCommand};
use CortexPE\Commando\PacketHooker;
class Main extends PluginBase implements Listener{
    public static $players = [];
    public function onEnable(): void{
        $this->saveDefaultConfig();
        if(!PacketHooker::isRegistered()) PacketHooker::register($this);
        $commands = [
            new BBResistanceCommand($this, "bbresistance", "Set the bedrock blast resistance"),
            new BBExplosionsCommand($this, "bbhardness", "Set the bedrock explosion count"),
            new BreakableCommand($this, "breakable", "Set the bedrock breakable state")
        ];
        foreach($commands as $command){
            $this->getServer()->getCommandMap()->register("bedrockbreaker", $command);
        }
        $this->registerBlock();
        Tile::registerTile(TileBedrock::class, ["Bedrock"]);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    public function registerBlock(): void{
        $maxExplodeCount = $this->getNestedConfig("maxExplodeCount");
        $blastResistance = $this->getNestedConfig("blastResistance");
        BlockFactory::registerBlock(new Bedrock((int)$maxExplodeCount, $blastResistance), true);
    }
    public function getNestedConfig(string $nested): float{
        if(is_null($nest = $this->getConfig()->get($nested))) $this->getLogger()->notice("$nested is not defined setting to ".$nest = 0.0);
        return $nest;
    }
    /**
     * @ignoreCancelled true
     * 
     * @priority HIGHEST
     */
    public function onInteractInBedrock(PlayerInteractEvent $event): void{
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $players = self::$players[$player->getName()] ?? [false];
        if($player === array_shift($players)){
            $bool = array_shift($players);
            if(!$block instanceof Bedrock) $player->sendTip("§cPlease click a bedrock block");
            elseif($block->getBreakable() == $bool) $player->sendTip("§cThat block is already ".($bool ? "breakable.": "unbreakable."));
            else{
                $block->setBreakable($bool);
                $player->sendTip("§aSuccessfully set bedrock block ".($bool ? "breakable.": "unbreakable."));
            }
            $event->setCancelled();
        }
    }
    /**
     * @ignoreCancelled true
     * 
     * @priority HIGHEST
     */
    public function onExplodeEntity(EntityExplodeEvent $e): void{
        $list = [];
        foreach($e->getBlockList() as $b){
            if($b instanceof Bedrock) $b->onExplode();
            else $list[] = $b;
        }
        $e->setBlockList($list);
    }
}