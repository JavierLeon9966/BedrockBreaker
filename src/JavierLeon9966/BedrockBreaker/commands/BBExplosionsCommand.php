<?php

declare(strict_types = 1);

namespace JavierLeon9966\BedrockBreaker\commands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use JavierLeon9966\BedrockBreaker\Main;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class BBExplosionsCommand extends BaseCommand {
	protected function prepare(): void {
		$this->setPermission('bedrockbreaker.command.bbexplosions');
		$this->registerArgument(0, new IntegerArgument('explosions', false));
	}

	/** @param array<array-key, mixed> $args */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$plugin = $this->getOwningPlugin();
		assert($plugin instanceof Main);
		/** @var int $explosions */
		$explosions = $args['explosions'];
		if($explosions < 1) {
			$sender->sendMessage(TextFormat::RED . ' Max Explode Count must be a positive integer');
			return;
		}
		$plugin->getBedrockConfig()->maxExplodeCount = $explosions;
		$sender->sendMessage(TextFormat::GREEN . 'Successfully changed the bedrock Max Explode Count value to ' . TextFormat::YELLOW . $explosions . TextFormat::GREEN . '.');
	}
}