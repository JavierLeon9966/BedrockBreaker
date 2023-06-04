<?php

namespace JavierLeon9966\BedrockBreaker;

use pocketmine\player\Player;

class TogglingBedrockManager{

	/**
	 * @var bool[]
	 * @phpstan-var array<string, bool>
	 */
	private array $playersTogglingBedrock = [];

	public function removeTogglingBedrock(Player $player): void{
		unset($this->playersTogglingBedrock[$player->getUniqueId()->getBytes()]);
	}

	public function getTogglingBedrock(Player $player): ?bool{
		return $this->playersTogglingBedrock[$player->getUniqueId()->getBytes()] ?? null;
	}

	public function setTogglingBedrock(Player $player, bool $value): void{
		$this->playersTogglingBedrock[$player->getUniqueId()->getBytes()] = $value;
	}

	public function isTogglingBedrock(Player $player): bool{
		return isset($this->playersTogglingBedrock[$player->getUniqueId()->getBytes()]);
	}
}