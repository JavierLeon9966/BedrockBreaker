<?php

declare(strict_types = 1);

namespace JavierLeon9966\BedrockBreaker;

use CortexPE\Commando\PacketHooker;
use cosmicpe\blockdata\BlockDataFactory;
use cosmicpe\blockdata\world\BlockDataWorldManager;
use JavierLeon9966\BedrockBreaker\commands\BBExplosionsCommand;
use JavierLeon9966\BedrockBreaker\commands\BBResistanceCommand;
use JavierLeon9966\BedrockBreaker\commands\BreakableCommand;
use libMarshal\exception\GeneralMarshalException;
use pocketmine\block\Bedrock;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\tile\TileFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\world\ChunkLoadEvent;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\ConfigLoadException;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener{

	private BedrockDatabase $bedrockDatabase;
	private TogglingBedrockManager $togglingBedrockManager;
	private BedrockConfig $bedrockConfig;

	public function onLoad(): void{
		$this->togglingBedrockManager = new TogglingBedrockManager();
		try{
			$this->bedrockConfig = BedrockConfig::unmarshal($this->getConfig()->getAll());
		}catch(ConfigLoadException $e){
			$this->getLogger()->error($e->getMessage());
			throw new DisablePluginException();
		}catch(GeneralMarshalException $e){
			$this->getLogger()->error("Configuration error: {$e->getMessage()}");
			throw new DisablePluginException();
		}
		TileFactory::getInstance()->register(TileBedrock::class, ['Bedrock']);
		BlockDataFactory::register('BreakableBedrock', BedrockData::class);

		$runtimeBlockStateRegistry = RuntimeBlockStateRegistry::getInstance();
		foreach(VanillaBlocks::BEDROCK()->generateStatePermutations() as $statePermutation){
			$runtimeBlockStateRegistry->blastResistance[$statePermutation->getStateId()] = $this->bedrockConfig->blastResistance;
		}
	}

	public function onEnable(): void{
		if(!PacketHooker::isRegistered()) PacketHooker::register($this);
		$this->bedrockDatabase = new BedrockDatabase(BlockDataWorldManager::create($this));
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
		$config->set('maxExplodeCount', $this->bedrockConfig->maxExplodeCount);
		$config->set('blastResistance', $this->bedrockConfig->blastResistance);
		$config->save();
	}

	public function getTogglingBedrockManager(): TogglingBedrockManager{
		return $this->togglingBedrockManager;
	}

	public function getBedrockConfig(): BedrockConfig{
		return $this->bedrockConfig;
	}

	public function getBedrockDatabase(): BedrockDatabase{
		return $this->bedrockDatabase;
	}

	public function onChunkLoad(ChunkLoadEvent $event): void{
		$world = $event->getWorld();
		if(!$this->bedrockDatabase->isWorldLoaded($world)){
			return;
		}
		foreach($event->getChunk()->getTiles() as $tile){
			if(!$tile instanceof TileBedrock){
				continue;
			}
			$block = $world->getBlock($tile->getPosition());
			if($block instanceof Bedrock){
				$this->bedrockDatabase->setBedrockData($block, new BedrockData($tile->isBreakable(), $tile->getExplodeCount()));
			}else{
				$this->bedrockDatabase->removeBedrockData($block);
			}
			$tile->close();
		}
	}

	/** @priority MONITOR */
	public function onBlockPlace(BlockPlaceEvent $event): void{
		foreach($event->getTransaction()->getBlocks() as [, , , $block]){
			if($block instanceof Bedrock){
				$this->bedrockDatabase->setBedrockData($block, new BedrockData(true, 0));
			}
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onInteractInBedrock(PlayerInteractEvent $event): void{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$world = $block->getPosition()->getWorld();
		$bool = $this->togglingBedrockManager->getTogglingBedrock($player);
		if($bool === null){
			if(!$block instanceof Bedrock){
				return;
			}
			if(!$this->bedrockDatabase->isWorldLoaded($world)){
				return;
			}
			$blockData = $this->bedrockDatabase->getBedrockData($block);
			if($blockData === null){
				$blockData = new BedrockData(false, 0);
				$this->bedrockDatabase->setBedrockData($block, $blockData);
			}
			$times = max(1, $this->bedrockConfig->maxExplodeCount - $blockData->getExplodeCount());
			$player->sendTip($blockData->isBreakable() ?
				TextFormat::YELLOW . 'Explode this block ' .
				TextFormat::RED . $times .
				TextFormat::YELLOW . ' time' . ($times === 1 ? '' : 's') . ' to destroy it!' :
				TextFormat::RED . 'This block can\'t be broken!'
			);
			return;
		}
		if(!$block instanceof Bedrock){
			$player->sendTip(TextFormat::RED . 'Please click a bedrock block');
			$event->cancel();
			return;
		}

		if(!$this->bedrockDatabase->isWorldLoaded($world)){
			$player->sendTip(TextFormat::RED . 'This world is not loaded in the database');
			return;
		}
		$blockData = $this->bedrockDatabase->getBedrockData($block);
		if($blockData === null){
			$blockData = new BedrockData(false, 0);
			$this->bedrockDatabase->setBedrockData($block, $blockData);
		}
		if($blockData->isBreakable() === $bool){
			$player->sendTip(TextFormat::RED . 'That block is already ' . ($bool ? '' : 'un') . 'breakable');
			return;
		}
		$this->bedrockDatabase->setBedrockData($block, $blockData->setBreakable($bool));
		$player->sendTip(TextFormat::GREEN . 'Successfully set bedrock block ' . ($bool ? '' : 'un') . 'breakable');
	}

	/**
	 * @priority HIGHEST
	 */
	public function onEntityExplode(EntityExplodeEvent $e): void{
		$world = $e->getPosition()->getWorld();
		if(!$this->bedrockDatabase->isWorldLoaded($world)){
			return;
		}
		$blockList = $e->getBlockList();
		foreach($blockList as $key => $b){
			$blockData = $this->bedrockDatabase->getBedrockData($b);
			if(!$b instanceof Bedrock){
				if($blockData !== null){
					$this->bedrockDatabase->removeBedrockData($b);
				}
				continue;
			}
			if($blockData === null){
				$blockData = new BedrockData(false, 0);
				$this->bedrockDatabase->setBedrockData($b, $blockData);
			}
			if(!$blockData->isBreakable()){
				unset($blockList[$key]);
				continue;
			}
			if($blockData->incrementExplodingCount(1)->getExplodeCount() < $this->bedrockConfig->maxExplodeCount){
				$this->bedrockDatabase->setBedrockData($b, $blockData);
				unset($blockList[$key]);
				continue;
			}
			$this->bedrockDatabase->removeBedrockData($b);
			$pos = $b->getPosition();
			$newBedrock = new Bedrock(new BlockIdentifier(BlockTypeIds::BEDROCK), 'Bedrock', new BlockTypeInfo(BlockBreakInfo::instant()));
			$newBedrock->position($world, $pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
			$blockList[$key] = $newBedrock;
		}
		$e->setBlockList($blockList);
	}
}