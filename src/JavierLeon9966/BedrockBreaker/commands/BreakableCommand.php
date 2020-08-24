<?php
declare(strict_types = 1);
namespace JavierLeon9966\BedrockBreaker\commands;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;
use pocketmine\command\CommandSender;
use pocketmine\Player;
class BreakableCommand extends BaseCommand {
	protected function prepare(): void {
		$this->registerArgument(0, new RawStringArgument("value", false));
	}
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
	    $value = $args[0];
	    $plugin = $this->getPlugin();
        if(!$sender instanceof Player){
            $plugin->getLogger()->notice("Please execute this command in-game.");
            return;
        }
        switch($value){
            case 'cancel':
                if(!isset($plugin::$players[$sender->getName()])){
                    $sender->sendMessage("§cYou're not currently setting bedrock states.");
                    return;
                }
                unset($plugin::$players[$sender->getName()]);
                $sender->sendMessage("§aSuccessfully left setting state.");
                return;
            case 'true':
            case 'false':
                $value = $value == 'true';
                $plugin::$players[$sender->getName()] = [$sender, $value];
                $sender->sendMessage("§eClick a bedrock block to make it ".($value ? "breakable.": "unbreakable."));
                return;
            default:
                $sender->sendMessage("§cInvalid $value value, options: 'cancel', 'true' or 'false'");
                return;
        }
	}
}