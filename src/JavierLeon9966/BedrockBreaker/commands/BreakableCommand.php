<?php

declare(strict_types = 1);

namespace JavierLeon9966\BedrockBreaker\commands;

use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use JavierLeon9966\BedrockBreaker\Main;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class BreakableCommand extends BaseCommand {
	protected function prepare(): void {
		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->setPermission('bedrockbreaker.command.breakable');
		$this->registerArgument(0, new BooleanArgument('value', true));
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		assert($sender instanceof Player);
		$plugin = $this->getOwningPlugin();
		assert($plugin instanceof Main);
		/** @var ?bool $value */
		$value = $args['value'] ?? null;
		$togglingBedrockMap = $plugin->getTogglingBedrockManager();
		if($value !== null){
			$togglingBedrockMap->setTogglingBedrock($sender, $value);
			$sender->sendMessage(TextFormat::YELLOW . 'Click a bedrock block to make it ' . ($value ? '': 'un') . 'breakable.');
			return;
		}
		if(!$togglingBedrockMap->isTogglingBedrock($sender)){
			$sender->sendMessage(TextFormat::RED . 'You’re not currently setting bedrock states.');
			return;
		}
		$togglingBedrockMap->removeTogglingBedrock($sender);
		$sender->sendMessage(TextFormat::GREEN . 'Successfully left setting state.');
	}
}