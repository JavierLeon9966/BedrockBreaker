<?php

declare(strict_types = 1);

namespace JavierLeon9966\BedrockBreaker\commands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;

use JavierLeon9966\BedrockBreaker\Bedrock;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class BBExplosionsCommand extends BaseCommand {
	protected function prepare(): void {
		$this->setPermission('bedrockbreaker.command.bbexplosions');
		$this->registerArgument(0, new IntegerArgument('explosions', false));
	}
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		Bedrock::setMaxExplodeCount($args['explosions']);
		$sender->sendMessage(TextFormat::GREEN . 'Successfully changed the bedrock Max Explode Count value to ' . TextFormat::YELLOW . $args['explosions'] . TextFormat::GREEN . '.');
	}
}