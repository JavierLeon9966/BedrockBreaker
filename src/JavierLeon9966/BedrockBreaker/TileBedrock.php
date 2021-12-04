<?php

declare(strict_types = 1);

namespace JavierLeon9966\BedrockBreaker;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\block\tile\Tile;

/**
 * @deprecated
 * @see Bedrock
 */
class TileBedrock extends Tile{
	public const
		TAG_BREAKABLE = 'Breakable',
		TAG_EXPLODE_COUNT = 'ExplodeCount';

	private bool $isBreakable = false;
	private int $explodeCount = 0;

	public function getDefaultName(): string{
		return "Bedrock";
	}

	public function setBreakable(bool|int $breakable): self{
		$this->isBreakable = $breakable == 1;
		return $this;
	}

	public function isBreakable(): bool{
		return $this->isBreakable;
	}

	public function setExplodeCount(int $count = 0): self{
		$this->explodeCount = $count;
		return $this;
	}

	public function getExplodeCount(): int{
		return $this->explodeCount;
	}

	/**
	 * @deprecated
	 */
	public function getNBT(): CompoundTag{
		return $this->saveNBT();
	}

	public function readSaveData(CompoundTag $nbt): void{
		$this->isBreakable = $nbt->getByte(self::TAG_BREAKABLE, 0) === 1;
		$this->explodeCount = $nbt->getInt(self::TAG_EXPLODE_COUNT, 0);
	}

	protected function writeSaveData(CompoundTag $nbt): void{
		$nbt->setByte(self::TAG_BREAKABLE, $this->isBreakable ? 1 : 0);
		$nbt->setInt(self::TAG_EXPLODE_COUNT, $this->explodeCount);
	}
}
