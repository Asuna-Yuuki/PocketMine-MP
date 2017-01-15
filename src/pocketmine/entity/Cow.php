<?php
namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\Player;
use pocketmine\network\protocol\AddEntityPacket;

class Cow extends Animal{
    const NETWORK_ID = 11;

    public $width = 0.75;
    public $height = 1.562;
    public $lenght = 1.5;
	
	protected $exp_min = 1;
	protected $exp_max = 3;

    public function initEntity(){
        parent::initEntity();
        $this->setMaxHealth(10);
    }

    public function getName(){
        return "Cow";
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

    public function isBaby(){
        return $this->getDataFlag(self::DATA_AGEABLE_FLAGS, self::DATA_FLAG_BABY);
    }

    public function getDrops(){
        $drops = [
            ItemItem::get(ItemItem::LEATHER, 0, mt_rand(0, 2))
        ];

        if($this->getLastDamageCause() === EntityDamageEvent::CAUSE_FIRE){
            $drops[] = ItemItem::get(ItemItem::COOKED_BEEF, 0, mt_rand(1, 3));
        }else{
            $drops[] = ItemItem::get(ItemItem::RAW_BEEF, 0, mt_rand(1, 3));
        }

        return $drops;
    }
}
