<?php

declare(strict_types=1);

namespace alvin0319\LevelAPI\command;

use alvin0319\LevelAPI\LevelAPI;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function alvin0319\LevelAPI\convert;
use function array_shift;
use function count;
use function trim;

class LevelCommand extends Command{

	public function __construct(){
		parent::__construct("레벨", "레벨을 확인합니다.");
		$this->setPermission("levelapi.command.use");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if($sender instanceof Player){
			$player = $sender;
			if(count($args) > 0){
				if((($name = array_shift($args)) ?? "") !== ""){
					$player = $name;
				}
			}
			if(!LevelAPI::getInstance()->hasData($player)){
				OnixUtils::message($sender, "해당 플레이어의 정보를 찾아볼 수 없습니다.");
				return false;
			}
			OnixUtils::message($sender, convert($player) . "님의 레벨: " . LevelAPI::getInstance()->getLevel($player));
		}else{
			if(count($args) < 1){
				OnixUtils::message($sender, "사용법: /레벨 <플레이어>");
				return false;
			}
			$player = array_shift($args);
			if(trim($player ?? "") === ""){
				OnixUtils::message($sender, "사용법: /레벨 <플레이어>");
				return false;
			}
			if(!LevelAPI::getInstance()->hasData($player)){
				OnixUtils::message($sender, "해당 플레이어의 정보를 찾을 수 없습니다.");
				return false;
			}
			OnixUtils::message($sender, "{$player}님의 레벨: " . LevelAPI::getInstance()->getLevel($player));
		}
		return true;
	}
}