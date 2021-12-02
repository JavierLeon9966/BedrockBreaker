<?php
declare(strict_types = 1);
namespace JavierLeon9966\BedrockBreaker\commands;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\IntegerArgument;
use pocketmine\command\CommandSender;
class BBResistanceCommand extends BaseCommand {
	protected function prepare(): void {
		$this->setPermission('bedrockbreaker.command.bbresistance');
		$this->registerArgument(0, new IntegerArgument('blastResistance', false));
	}
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$plugin = $this->getOwningPlugin();
		$plugin->getConfig()->set('blastResistance', (int)$args[0]);
		$plugin->registerBlock();
		$sender->sendMessage("§aSuccessfully changed the bedrock blast resistance value to §e{$value}§a.");
	}
}