<?php

declare(strict_types = 1);

namespace JavierLeon9966\BedrockBreaker;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\block\tile\Tile;

/**
 * @deprecated
 * @see BedrockData
 */
class TileBedrock extends Tile{
	private bool $isBreakable = false;
	private int $explodeCount = 0;

	public function isBreakable(): bool{
		return $this->isBreakable;
	}

	public function getExplodeCount(): int{
		return $this->explodeCount;
	}

	public function readSaveData(CompoundTag $nbt): void{
		$this->isBreakable = $nbt->getByte(BedrockData::TAG_BREAKABLE, 0) === 1;
		$this->explodeCount = $nbt->getInt(BedrockData::TAG_EXPLODE_COUNT, 0);
	}

	protected function writeSaveData(CompoundTag $nbt): void{
	}
}
