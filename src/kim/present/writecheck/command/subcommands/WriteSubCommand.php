<?php

declare(strict_types=1);

namespace kim\present\writecheck\command\subcommands;

use kim\present\writecheck\command\{
	PoolCommand, SubCommand
};
use kim\present\writecheck\util\Translation;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class WriteSubCommand extends SubCommand{
	public function __construct(PoolCommand $owner){
		parent::__construct($owner, 'write');
	}

	/**
	 * @param CommandSender $sender
	 * @param String[]      $args
	 *
	 * @return bool
	 */
	public function onCommand(CommandSender $sender, array $args) : bool{
		if(isset($args[0]) && is_numeric($args[0])){
			if($sender instanceof Player){
				$amount = (int) $args[0];
				$count = max(isset($args[1]) && is_numeric($args[1]) ? (int) $args[1] : 1, 1);

				$economyApi = EconomyAPI::getInstance();
				$price = $amount * $count;
				if(($money = $economyApi->myMoney($sender)) < $price){
					$sender->sendMessage($this->translate('failure', $money));
				}else{
					$return = $economyApi->reduceMoney($sender, $price, false, $this->plugin->getName());
					if($return === EconomyAPI::RET_SUCCESS){
						$sender->getInventory()->addItem($this->plugin->getCheck($amount, $count));

						$sender->sendMessage($this->translate('success', $amount, $count, $price, $money - $price));
					}else{
						$sender->sendMessage(Translation::translate('economy-failure', $return));
					}
				}
			}else{
				$sender->sendMessage(Translation::translate('command-generic-failure@in-game'));
			}
			return true;
		}else{
			return false;
		}
	}
}