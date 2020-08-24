<?php
declare(strict_types = 1);
namespace JavierLeon9966\BedrockBreaker;
use pocketmine\block\{Bedrock as PMBedrock, Block, BlockFactory};
use pocketmine\item\{Item, ItemBlock};
use pocketmine\tile\Tile;
use pocketmine\Player;
use pocketmine\nbt\tag\{
	CompoundTag, IntTag, StringTag, ByteTag
};
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\level\particle\DestroyBlockParticle;
class Bedrock extends PMBedrock{
    protected static $maxExplodeCount;
    protected static $blastResistance;
    public function __construct(int $explosions = 1, float $resistance = 0){
        parent::__construct();
        self::$maxExplodeCount = max($explosions, 1);
        self::$blastResistance = max($resistance, 0);
    }
    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null): bool{
        $placed = parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
        self::createTile($blockReplace->asVector3(), $blockReplace->getLevelNonNull());
		return $placed;
	}
	public function onActivate(Item $item, Player $player = null) : bool{
	    if($player instanceof Player){
            $player->sendTip($this->getBreakable() ? ($this->getDuration() > 1 ? "§eExplode this block §c{$this->getDuration()} §etimes to destroy it!" : "§eExplode this block one more time to destroy it!") : "§cThis block can't be broken!");
	    }
		return false;
	}
    public function onExplode(): void{
        if($this->getBreakable()){
            $tile = $this->getTile();
            $tile->setExplodeCount($tile->getExplodeCount() + 1);
            if($this->getDuration() < 1){
                $level = $this->getLevelNonNull();
                $vector = $this->add(0.5, 0.5, 0.5);
                $level->addParticle(new DestroyBlockParticle($vector, $this));
                $level->setBlock($this, BlockFactory::get(Block::AIR), true);
                $tile->close();
                //$level->dropItem($vector, new ItemBlock($this->getId());
            }
        }
    }
    public function getBlastResistance(): float{
        return self::$blastResistance;
    }
    public function getBreakable(): bool{
        return $this->getTile()->isBreakable();
    }
    private function getTile(): TileBedrock{
        $tile = $this->getLevelNonNull()->getTile($this);
        if(!$tile instanceof TileBedrock) $tile = self::createTile($this->asVector3(), $this->getLevelNonNull());
        return $tile;
    }
    private static function createTile(Vector3 $pos, Level $level): TileBedrock{
        $bedrock = TileBedrock::createNBT($pos);
        return Tile::createTile("Bedrock", $level, $bedrock);
    }
    public function getDuration(): int{
        return self::$maxExplodeCount - $this->getTile()->getExplodeCount();
    }
    public function setBreakable(bool $value): self{
        $this->getTile()->setBreakable((int)$value);
        return $this;
    }
}