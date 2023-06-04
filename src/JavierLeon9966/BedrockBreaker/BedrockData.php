<?php

namespace JavierLeon9966\BedrockBreaker;

use cosmicpe\blockdata\BlockData;
use pocketmine\nbt\tag\CompoundTag;

class BedrockData implements BlockData{
	public const TAG_BREAKABLE = 'Breakable';
	public const TAG_EXPLODE_COUNT = 'ExplodeCount';

	public function __construct(private bool $breakable, private int $explodeCount){
	}

	public function setBreakable(bool $breakable): self{
		$this->breakable = $breakable;
		return $this;
	}

	public function isBreakable(): bool{
		return $this->breakable;
	}

	public function incrementExplodingCount(int $count): self{
		$this->explodeCount += $count;
		return $this;
	}

	public function getExplodeCount(): int{
		return $this->explodeCount;
	}

	public static function nbtDeserialize(CompoundTag $nbt): self{
		return new self(
			$nbt->getByte(self::TAG_BREAKABLE, 0) === 1,
			$nbt->getLong(self::TAG_EXPLODE_COUNT)
		);
	}

	public function nbtSerialize(): CompoundTag{
		return CompoundTag::create()
			->setByte(self::TAG_BREAKABLE, $this->breakable ? 1 : 0)
			->setLong(self::TAG_EXPLODE_COUNT, $this->explodeCount);
	}
}