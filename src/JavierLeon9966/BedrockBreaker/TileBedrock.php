<?php
declare(strict_types = 1);
namespace JavierLeon9966\BedrockBreaker;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\tile\Tile;

class TileBedrock extends Tile{
	/** @var string */
	public const
		TAG_BREAKABLE = "Breakable",
		TAG_EXPLODE_COUNT = "ExplodeCount";
	/** @var CompoundTag */
	private $nbt;
	public function getDefaultName(): string{
	    return "Bedrock";
	}
	public function setBreakable(int $byte): self{
		$this->getNBT()->setByte(self::TAG_BREAKABLE, $byte);
		return $this;
	}
	public function setExplodeCount(int $count = 0): self{
		$this->getNBT()->setInt(self::TAG_EXPLODE_COUNT, $count);
		return $this;
	}
	public function isBreakable(): bool{
	    if(!$this->getNBT()->hasTag(self::TAG_BREAKABLE)) $this->setBreakable((int)($this->y > 0));
	    return (bool)$this->getNBT()->getByte(self::TAG_BREAKABLE);
	}
	public function getExplodeCount(): int{
	    if(!$this->getNBT()->hasTag(self::TAG_EXPLODE_COUNT)) $this->setExplodeCount();
	    return $this->getNBT()->getInt(self::TAG_EXPLODE_COUNT);
	}
	public function getNBT(): CompoundTag{
		return $this->nbt;
	}
	protected function readSaveData(CompoundTag $nbt): void{
		$this->nbt = $nbt;
	}
	protected function writeSaveData(CompoundTag $nbt): void{
		$nbt->setByte(self::TAG_BREAKABLE, (int)$this->isBreakable());
		$nbt->setInt(self::TAG_EXPLODE_COUNT, $this->getExplodeCount());
	}
}