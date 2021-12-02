<?php
declare(strict_types = 1);
namespace JavierLeon9966\BedrockBreaker;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\block\BlockFactory;
use pocketmine\tile\TileFactory;
use JavierLeon9966\BedrockBreaker\commands\{BBResistanceCommand, BBExplosionsCommand, BreakableCommand};
use CortexPE\Commando\PacketHooker;
class Main extends PluginBase implements Listener{
	public static array $players = [];
	public function onLoad(): void{
		$this->saveDefaultConfig();
		$this->registerBlock();
		TileFactory::getInstance()->register(TileBedrock::class, ['Bedrock']);
	}
	public function onEnable(): void{
		if(!PacketHooker::isRegistered()) PacketHooker::register($this);
		$this->getServer()->getCommandMap()->registerAll($this->getName(), [
			new BBResistanceCommand($this, 'bbresistance', 'Set the bedrock blast resistance'),
			new BBExplosionsCommand($this, 'bbhardness', 'Set the bedrock explosion count'),
			new BreakableCommand($this, 'breakable', 'Set the bedrock breakable state')
		]);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	public function onDisable(): void{
		$this->saveConfig();
	}
	public function registerBlock(): void{
		$maxExplodeCount = (int)$this->getConfig()->get('maxExplodeCount', 1);
		$blastResistance = (int)$this->getConfig()->get('blastResistance', 0);
		BlockFactory::getInstance()->register(new Bedrock($maxExplodeCount, $blastResistance), true);
	}
	/**
	 * @priority HIGHEST
	 */
	public function onInteractInBedrock(PlayerInteractEvent $event): void{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$players = self::$players[$player->getName()] ?? [false];
		if($player === array_shift($players)){
			$bool = array_shift($players);
			if(!$block instanceof Bedrock) $player->sendTip('§cPlease click a bedrock block');
			elseif($block->getBreakable() == $bool) $player->sendTip('§cThat block is already '.($bool ? 'breakable': 'unbreakable'));
			else{
				$block->setBreakable($bool);
				$player->sendTip('§aSuccessfully set bedrock block '.($bool ? 'breakable': 'unbreakable'));
			}
			$event->cancel();
		}
	}
	/**
	 * @priority MONITOR
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