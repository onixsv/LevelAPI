<?php

declare(strict_types=1);

namespace alvin0319\LevelAPI\command;

use alvin0319\LevelAPI\LevelAPI;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use function array_slice;
use function ceil;
use function count;
use function floor;
use function is_numeric;

class TopLevelCommand extends Command{

	public function __construct(){
		parent::__construct("레벨순위", "레벨 순위를 확인합니다.");
		$this->setPermission("levelapi.command.toplevel");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		$page = 1;
		if(count($args) > 0){
			if(is_numeric($args[0] ?? "") && (int) $args[0] > 0){
				$page = (int) $args[0];
			}
		}
		$maxPage = ceil(count($data = LevelAPI::getInstance()->getAll()) / 5);
		$page = (int) floor($page);
		if($page > $maxPage)
			$page = $maxPage;
		OnixUtils::message($sender, "§d<§f 전체 §d{$maxPage}§f페이지중 §d{$page}§f페이지 §d>");
		$slice = array_slice(LevelAPI::getInstance()->getAll(), (int) (($page - 1) * 5), 5);
		$i = 0;
		foreach($slice as $name => $level){
			$i++;
			$rank = ($page - 1) * 5 + $i;
			$sender->sendMessage("§d<§f{$rank}위§d> §d{$name}§f: " . $level);
		}
		return true;
	}
}