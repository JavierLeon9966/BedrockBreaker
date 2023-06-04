<?php

namespace JavierLeon9966\BedrockBreaker;

use cosmicpe\blockdata\world\BlockDataWorldManager;
use pocketmine\block\Bedrock;
use pocketmine\block\Block;
use pocketmine\world\World;

final class BedrockDatabase{

	public function __construct(private readonly BlockDataWorldManager $blockDataWorldManager){
	}

	public function isWorldLoaded(World $world): bool{
		return $this->blockDataWorldManager->isLoaded($world);
	}

	public function setBedrockData(Bedrock $block, BedrockData $data): void{
		$pos = $block->getPosition();
		$this->blockDataWorldManager->get($pos->getWorld())->setBlockDataAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $data);
	}

	public function getBedrockData(Block $block): ?BedrockData{
		$pos = $block->getPosition();
		/** @var ?BedrockData $bedrockData */
		$bedrockData = $this->blockDataWorldManager->get($pos->getWorld())->getBlockDataAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
		return $bedrockData;
	}

	public function removeBedrockData(Block $block): void{
		$pos = $block->getPosition();
		$this->blockDataWorldManager->get($pos->getWorld())->setBlockDataAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), null);
	}
}