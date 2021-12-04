<?php

declare(strict_types = 1);

namespace JavierLeon9966\BedrockBreaker;

use pocketmine\block\{Bedrock as PMBedrock, BlockBreakInfo, BlockIdentifier as BID, BlockLegacyIds as Ids, VanillaBlocks};
use pocketmine\item\{Item, ItemFactory};
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use pocketmine\world\particle\DestroyBlockParticle;
use pocketmine\utils\TextFormat;

class Bedrock extends PMBedrock{
	protected static int $maxExplodeCount = 1;
	protected static float $blastResistance = 0.0;
	protected bool $isBreakable = false;
	protected int $explodeCount = 0;

	public static function getMaxExplodeCount(): int{
		return self::$maxExplodeCount;
	}

	public static function setMaxExplodeCount(int $maxExplodeCount): void{
		self::$maxExplodeCount = max($maxExplodeCount, 1);
	}

	public static function getBlastResistance(): float{
		return self::$blastResistance;
	}

	public static function setBlastResistance(float $blastResistance): void{
		self::$blastResistance = $blastResistance;
	}

	public function __construct(int $explosions = 1, float $resistance = 0){
		parent::__construct(new BID(Ids::BEDROCK, 0, null, TileBedrock::class), 'Bedrock', BlockBreakInfo::indestructible($resistance));
		self::setMaxExplodeCount($explosions);
	}

	public function readStateFromWorld(): void{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileBedrock){
			$this->isBreakable = $tile->isBreakable();
			$this->explodeCount = $tile->getExplodeCount();
		}
	}

	public function writeStateToWorld(): void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof TileBedrock);
		$tile->setBreakable($this->isBreakable);
		$tile->setExplodeCount($this->explodeCount);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){
			$player->sendTip($this->isBreakable ?
				TextFormat::YELLOW . 'Explode this block ' . TextFormat::RED . (self::$maxExplodeCount - $this->explodeCount) . TextFormat::YELLOW . ' time/s to destroy it!' :
				TextFormat::RED . 'This block can’t be broken!'
			);
		}
		return false;
	}

	/**
	 * @deprecated
	 * @see Bedrock::canBeExploded()
	 */
	public function onExplode(): void{
		if(!$this->isBreakable() or !$this->canBeExploded()){
			return;
		}
		$block = $this;
		$world = $this->position->getWorld();
		if($this->canBeExploded()){
			$block = VanillaBlocks::AIR();
			$centerPos = $this->position->add(0.5, 0.5, 0.5);
			foreach($this->getDrops(ItemFactory::air()) as $drop){
				$world->dropItem($centerPos, $drop);
			}
			$world->addParticle(new DestroyBlockParticle($centerPos, $this->position));
			$world->getTileAt($this->position->x, $this->position->y, $this->position->z)?->onBlockDestroyed();
		}
		$world->setBlockAt($this->position->x, $this->position->y, $this->position->z, $block);
	}

	public function canBeExploded(): bool{
		return $this->explodeCount >= self::$maxExplodeCount;
	}

	/** 
	 * @deprecated
	 * @see Bedrock::isBreakable()
	 */
	public function getBreakable(): bool{
		return $this->isBreakable();
	}

	/**
	 * @deprecated
	 * @see Bedrock::canBeExploded()
	 */
	public function getDuration(): int{
		return self::$maxExplodeCount - $this->explodeCount;
	}

	public function isBreakable(): bool{
		return $this->isBreakable;
	}

	public function setBreakable(bool $breakable): self{
		$this->isBreakable = $breakable;
		return $this;
	}

	public function getExplodeCount(): int{
		return $this->explodeCount;
	}

	public function setExplodeCount(int $count): void{
		$this->explodeCount = $count;
	}

	public function incrementExplodingCount(int $value = 1): void{
		$this->explodeCount += $value;
	}
}
