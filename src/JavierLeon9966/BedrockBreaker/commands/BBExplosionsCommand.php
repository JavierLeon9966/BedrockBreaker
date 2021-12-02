<?php
declare(strict_types = 1);
namespace JavierLeon9966\BedrockBreaker\commands;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\IntegerArgument;
use pocketmine\command\CommandSender;
class BBExplosionsCommand extends BaseCommand {
	protected function prepare(): void {
		$this->setPermission('bedrockbreaker.command.bbexplosions');
		$this->registerArgument(0, new IntegerArgument('explosions', false));
	}
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$plugin = $this->getOwningPlugin();
		$plugin->getConfig()->set('maxExplodeCount', (int)$args[0]);
		$plugin->registerBlock();
		$sender->sendMessage("§aSuccessfully changed the bedrock Max Explode Count value to §e{$value}§a.");
	}
}