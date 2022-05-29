<?php

declare(strict_types=1);

namespace alvin0319\LevelAPI\event;

use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class PlayerLevelUpEvent extends PlayerEvent{
	/** @var int */
	protected int $before;
	/** @var int */
	protected int $after;

	public function __construct(Player $player, int $before, int $after){
		$this->player = $player;
		$this->before = $before;
		$this->after = $after;
	}

	public function getBefore() : int{
		return $this->before;
	}

	public function getAfter() : int{
		return $this->after;
	}
}