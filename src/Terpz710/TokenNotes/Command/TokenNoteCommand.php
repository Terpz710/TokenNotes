<?php

declare(strict_types=1);

namespace Terpz710\TokenNotes\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\item\Item;

use Terpz710\TokenNotes\Loader;
use Terpz710\TokensAPI\API\TokenAPI;
use Terpz710\TokensAPI\Tokens;

class TokenNoteCommand extends Command implements PluginOwned {

    private $plugin;

    public function __construct(Loader $plugin) {
        parent::__construct("tokennotes", "Give a player a token note!", "/tokennotes [amount]", ["tn"]);
        $this->plugin = $plugin;
        $this->setPermission("tokennotes.cmd");
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game!");
            return true;
        }

        if (count($args) !== 1) {
            $sender->sendMessage("Usage:§e /tokennote [amount]");
            return true;
        }

        $targetPlayer = $sender;
        $tokenAmount = (int)$args[0];

        if ($tokenAmount <= 0) {
            $sender->sendMessage("§l§c(!)§r§f Amount must be a positive number!");
            return true;
        }

        $senderTokens = TokenAPI::getPlayerToken($sender);
        if ($senderTokens < $tokenAmount) {
            $sender->sendMessage("§l§c(!)§r§f You don't have enough tokens to create this Token Note!");
            return true;
        }
        TokenAPI::removeToken($sender, $tokenAmount);

        $tokenNote = $this->plugin->createTokenNote($tokenAmount);
        $targetPlayer->getInventory()->addItem($tokenNote);

        $sender->sendMessage("§l§a(!)§r§f Token note successfully created and given to §e" . $targetPlayer->getName() . "§f!");

        return true;
    }
}
