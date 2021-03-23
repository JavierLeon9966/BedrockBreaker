<?php
declare(strict_types = 1);
namespace JavierLeon9966\BedrockBreaker;
use pocketmine\block\{Bedrock as PMBedrock, BlockFactory, BlockIdentifier as BID, BlockLegacyIds as Ids, BlockBreakInfo};
use pocketmine\item\{Item, ItemFactory};
use pocketmine\player\Player;
use pocketmine\world\World;
use pocketmine\world\particle\DestroyBlockParticle;
class Bedrock extends PMBedrock{
	protected static $maxExplodeCount;
	public function __construct(int $explosions = 1, float $resistance = 0){
		parent::__construct(new BID(Ids::BEDROCK, 0, null, TileBedrock::class), 'Bedrock', BlockBreakInfo::indestructible($resistance));
		self::$maxExplodeCount = max($explosions, 1);
	}
	public function onActivate(Item $item, Player $player = null) : bool{
		if($player instanceof Player){
			$player->sendTip($this->getBreakable() ? ($this->getDuration() > 1 ? "§eExplode this block §c{$this->getDuration()} §etimes to destroy it!" : '§eExplode this block one more time to destroy it!') : '§cThis block can’t be broken!');
		}
		return false;
	}
	public function onExplode(): void{
		if($this->getBreakable()){
			$tile = $this->getTile();
			if($tile !== null){
				$tile->setExplodeCount($tile->getExplodeCount() + 1);
				if($this->getDuration() < 1){
					$vector = $this->pos->add(0.5, 0.5, 0.5);
					$this->pos->getWorld()->addParticle(new DestroyBlockParticle($vector, $this->pos));
					$this->onBreak(ItemFactory::air());
				}
			}
		}
	}
	public function getBreakable(): bool{
		$tile = $this->getTile();
		if($tile !== null){
			return $tile->isBreakable();
		}
		return false;
	}
	private function getTile(): ?TileBedrock{
		$tile = $this->pos->getWorld()->getTile($this->pos);
		if($tile !== null){
			return $tile;
		}
		return null;
	}
	public function getDuration(): int{
		$tile = $this->getTile();
		if($tile !== null){
			return self::$maxExplodeCount - $tile->getExplodeCount();
		}
		return self::$maxExplodeCount;
	}
	public function setBreakable(bool $value): self{
		$tile = $this->getTile();
		if($tile !== null){
			$tile->setBreakable((int)$value);
		}
		return $this;
	}
}
