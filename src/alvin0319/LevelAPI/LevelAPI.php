<?php

declare(strict_types=1);

namespace alvin0319\LevelAPI;

use alvin0319\LevelAPI\command\LevelCommand;
use alvin0319\LevelAPI\command\TopLevelCommand;
use alvin0319\LevelAPI\event\PlayerExpUpEvent;
use alvin0319\LevelAPI\event\PlayerLevelUpEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\sound\Sound;
use pocketmine\world\sound\XpLevelUpSound;
use function arsort;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;
use function strtolower;

/** @param Player|string $player */
function convert($player) : string{
	return strtolower($player instanceof Player ? $player->getName() : $player);
}

class LevelAPI extends PluginBase implements Listener{
	use SingletonTrait;

	protected $db = [];

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		if(file_exists($file = $this->getDataFolder() . "LevelData.json")){
			$this->db = json_decode(file_get_contents($file), true);
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->getServer()->getCommandMap()->registerAll("levelapi", [
			new LevelCommand(),
			new TopLevelCommand()
		]);
	}

	protected function onDisable() : void{
		file_put_contents($this->getDataFolder() . "LevelData.json", json_encode($this->db));
	}

	public function addLevel(Player $player, int $level) : void{
		if(!$this->hasData($player)){
			$this->createData($player);
		}
		$ev = new PlayerLevelUpEvent($player, $this->getLevel($player), $this->getLevel($player) + $level);
		$ev->call();

		$this->db[convert($player)]["level"] += $level;

		$player->sendTitle("§aLEVEL §bUP", "{$this->getLevel($player)} 레벨이 되었습니다.");

		$player->getWorld()->addSound($player->getPosition(), new class() implements Sound{
			public function encode(?Vector3 $pos) : array{
				return [LevelSoundEventPacket::nonActorSound(LevelSoundEvent::LEVELUP, $pos, false)];
			}
		});
	}

	public function addExp(Player $player, int $exp) : void{
		if(!$this->hasData($player)){
			$this->createData($player);
		}
		($ev = new PlayerExpUpEvent($player, $exp))->call();
		$this->db[convert($player)]["exp"] += $ev->getExp();
		if($this->getExp($player) >= $this->getNextExp($player)){
			$this->setExp($player, $this->getExp($player) - $this->getNextExp($player));
			$this->addLevel($player, 1);

			$player->getWorld()->addSound($player->getPosition(), new XpLevelUpSound($this->getExp($player)));
		}
		$this->setXpAndProgress($player);
	}

	public function setLevel(Player $player, int $level) : void{
		if(!$this->hasData($player)){
			$this->createData($player);
		}
		$this->db[convert($player)]["level"] = $level;
	}

	public function setExp(Player $player, int $exp) : void{
		if(!$this->hasData($player)){
			$this->createData($player);
		}
		$this->db[convert($player)]["exp"] = $exp;
	}

	public function getLevel($player) : int{
		return $this->db[convert($player)]["level"] ?? -1;
	}

	public function getExp($player) : int{
		return $this->db[convert($player)]["exp"] ?? -1;
	}

	public function getNextExp($player) : int{
		return (int) (500 * $this->getLevel(convert($player)));
	}

	public function hasData($player) : bool{
		return isset($this->db[convert($player)]);
	}

	public function getAll() : array{
		$res = [];
		foreach($this->db as $name => $data){
			$res[$name] = $data["level"];
		}
		arsort($res);
		return $res;
	}

	public function createData(Player $player) : void{
		if(!$this->hasData($player)){
			$this->db[convert($player)] = [
				"level" => 1,
				"exp" => 0
			];
		}
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();

		if(!$this->hasData($player)){
			$this->createData($player);
		}

		$this->setXpAndProgress($player);
	}

	private function setXpAndProgress(Player $player) : void{
		/*
		$level = $this->getLevel($player);
		$exp = $this->getExp($player);

		$progress = ($exp > 0 ? $exp : 1) / $this->getNextExp($player);

		if($progress > 1)
			$progress = 1;

		$player->getXpManager()->setXpAndProgress($level, $progress);
		*/
	}

	public function onPlayerDeath(PlayerDeathEvent $event) : void{
		$event->setXpDropAmount(0);
	}

	public function onPlayerRespawn(PlayerRespawnEvent $event) : void{
		$player = $event->getPlayer();

		/*
		$this->getScheduler()->scheduleTask(new ClosureTask(function() use ($player) : void{
			$this->setXpAndProgress($player);
		}));
		*/
	}
}