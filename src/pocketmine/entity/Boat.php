<?php
namespace pocketmine\entity;

use pocketmine\Player;
use pocketmine\item\Item as ItemItem;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\level\format\Chunk;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\network\protocol\AddEntityPacket;

class Boat extends Vehicle{
	const NETWORK_ID = 90;
	
	public $height = 0.7;
	public $width = 1.6;
	public $gravity = 0.5;
	public $drag = 0.1;
	
	public function __construct(Chunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->woodID)){
			$nbt->woodID = new ByteTag("woodID", 0);
		}
		parent::__construct($chunk, $nbt);
		$this->setDataProperty(self::DATA_BOAT_COLOR, self::DATA_TYPE_BYTE, $this->getWoodID());
	}
	
	public function initEntity(){
		parent::initEntity();
        $this->setMaxHealth(4);
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = self::NETWORK_ID;
		$pk->eid = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
	
	public function getWoodID(){
		return $this->namedtag["woodID"];
	}
	
	public function onUpdate($currentTick){
		if($this->isAlive()){
			$this->timings->startTiming();
			$hasUpdate = false;
			
			if($this->isInsideOfWater()){
				$hasUpdate = true;
				$this->move(0,0.1, 0);
				$this->updateMovement();
			}
			if($this->isLinked() && $this->getlinkedTarget() !== null){
				if(($player = $this->getlinkedTarget()) instanceof Player){
					$newyaw = $player->getYaw();
					$deltayaw = $newyaw - $this->getYaw();
					if($deltayaw < 0.1) $deltayaw * -1;
					$hasUpdate = $newyaw - $this->getYaw() > 0.1;
				}
				if($hasUpdate){
					$this->setRotation($newyaw, $this->pitch);
					$this->move(0, 1, 0);
				}
				$this->updateMovement();
			}
			if($this->getHealth() < $this->getMaxHealth()){
				$this->heal(0.1, new EntityRegainHealthEvent($this, 0.1, EntityRegainHealthEvent::CAUSE_CUSTOM));
				$hasUpdate = true;
			}
			
			$this->timings->stopTiming();

			return $hasUpdate;
		}
	}

	public function getDrops(){
		return [
			ItemItem::get(ItemItem::BOAT, $this->getWoodID(), 1)
		];
	}
}
