<?php

namespace App\Services;

/** A rule-of-the-game refusal ("not your turn", "room is full") — safe to show to players. */
class GameException extends \RuntimeException
{
}
