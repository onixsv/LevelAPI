<?php

declare(strict_types=1);

namespace alvin0319\LevelAPI\event;

use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class PlayerExpUpEvent extends PlayerEvent{
	protected int $exp;

	public function __construct(Player $player, int $exp){
		$this->player = $player;
		$this->exp = $exp;
	}

	public function getExp() : int{
		return $this->exp;
	}

	public function setExp(int $exp) : void{
		$this->exp = $exp;
	}
}