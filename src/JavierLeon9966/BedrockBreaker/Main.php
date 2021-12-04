<?php

declare(strict_types = 1);

namespace JavierLeon9966\BedrockBreaker;

use CortexPE\Commando\PacketHooker;

use JavierLeon9966\BedrockBreaker\commands\{BBExplosionsCommand, BBResistanceCommand, BreakableCommand};

use pocketmine\block\{Block, BlockFactory};
use pocketmine\block\tile\TileFactory;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener{

	/** 
	 * @deprecated
	 * @see Main::setTogglingBedrock()
	 * @var bool[]
	 * @phpstan-var array<string, array{0: Player, 1: bool}>
	 */
	public static array $players = [];

	/**
	 * @var bool[]
	 * @phpstan-var array<string, bool>
	 */
	private array $playersTogglingBedrock = [];

	public function onLoad(): void{
		$config = $this->getConfig();
		Bedrock::setMaxExplodeCount($config->get('maxExplodeCount', 1));
		Bedrock::setBlastResistance($config->get('blastResistance', 0));
		$this->registerBlock();
		TileFactory::getInstance()->register(TileBedrock::class, ['Bedrock']);
	}

	public function onEnable(): void{
		if(!PacketHooker::isRegistered()) PacketHooker::register($this);
		$server = $this->getServer();
		$server->getCommandMap()->registerAll($this->getName(), [
			new BBResistanceCommand($this, 'bbresistance', 'Set the bedrock blast resistance'),
			new BBExplosionsCommand($this, 'bbhardness', 'Set the bedrock explosion count'),
			new BreakableCommand($this, 'breakable', 'Set the bedrock breakable state')
		]);
		$server->getPluginManager()->registerEvents($this, $this);
	}

	public function onDisable(): void{
		$config = $this->getConfig();
		$config->set('maxExplodeCount', Bedrock::getMaxExplodeCount());
		$config->set('blastResistance', Bedrock::getBlastResistance());
		$this->saveConfig();
	}

	public function setTogglingBedrock(Player $player, bool $value): void{
		$this->playersTogglingBedrock[$player->getUniqueId()->getBytes()] = $value;
	}

	public function removeTogglingBedrock(Player $player): void{
		unset($this->playersTogglingBedrock[$player->getUniqueId()->getBytes()]);
	}

	public function isTogglingBedrock(Player $player): bool{
		return isset($this->playersTogglingBedrock[$player->getUniqueId()->getBytes()]);
	}

	/**
	 * @deprecated
	 */
	public function registerBlock(): void{
		BlockFactory::getInstance()->register(new Bedrock(Bedrock::getMaxExplodeCount(), Bedrock::getBlastResistance()), true);
	}

	/**
	 * @priority HIGHEST
	 */
	public function onInteractInBedrock(PlayerInteractEvent $event): void{
		$player = $event->getPlayer();
		$rawUUID = $player->getUniqueId()->getBytes();
		$block = $event->getBlock();
		$setBreakableState = static function(bool $bool) use($block, $event, $player): void{
			if(!$block instanceof Bedrock) $player->sendTip(TextFormat::RED . 'Please click a bedrock block');
			elseif($block->isBreakable() === $bool) $player->sendTip(TextFormat::RED . 'That block is already ' . ($bool ? '' : 'un') . 'breakable');
			else{
				$block->setBreakable($bool);
				$pos = $block->getPosition();
				$pos->getWorld()->setBlock($pos, $block);
				$player->sendTip(TextFormat::GREEN . 'Successfully set bedrock block ' . ($bool ? '' : 'un') . 'breakable');
			}
			$event->cancel();
		};
		$players = self::$players[$player->getName()] ?? [false];
		if($player === array_shift($players)){ // For backwards-compatibility
			$bool = array_shift($players);
			$setBreakableState($bool);
		}elseif(isset($this->playersTogglingBedrock[$rawUUID])){
			$bool = $this->playersTogglingBedrock[$rawUUID];
			$setBreakableState($bool);
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onExplodeEntity(EntityExplodeEvent $e): void{
		$e->setBlockList(array_filter($e->getBlockList(), function(Block $b): bool{
			if(!$b instanceof Bedrock){
				return true;
			}
			if(!$b->isBreakable()){
				return false;
			}
			$b->incrementExplodingCount();
			if($b->canBeExploded()){
				return true;
			}
			$pos = $b->getPosition();
			$pos->getWorld()->setBlock($pos, $b);
			return false;
		}));
	}
}