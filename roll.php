#!/usr/bin/env php
<?php

/**
 * A command-line tool to determine attack and damage dealt by Ammonia.
 *
 * @author Ross P. Davis
 */

$roll = new Roll();
$roll->weapon = Roll::WEAP_SUB_DAGGER_2; // default

// process command line arguments
$opts = getopt('csbmhw:3');
$usage = "USAGE: $argv[0]\n" .
  "  [-c] Ammonia has Combat Advantage\n" .
  "  [-s] Ammonia deals Sneak Attack damage\n" .
  "  [-b] Ammonia deals Brutal Scoundrel damage (e.g. Nasty Backswing)\n" .
  "  [-m] Ammonia is using Mediation of the Blade\n" .
  "  [-3] Ammonia is dealing 3[W] instead of her usual 1[W]\n" . 
  "  [-h] Displays this help message\n" .
  "  [-w <weapon>] Ammonia's weapon (defaults to 'dag'). Weapons: \n" .
  "  \tdag = Subtle Dagger +2\n" . 
  "  \tshuriken = Distance Shuriken +2\n" . 
  "  \tshort = Vicious Short Sword +2\n";
if ($opts === false) die("Bad arguments!\n$usage");
$help = false;
if (array_key_exists('c', $opts)) $roll->ca = true;
if (array_key_exists('s', $opts)) $roll->sneak = true;
if (array_key_exists('b', $opts)) $roll->brutal = true;
if (array_key_exists('3', $opts)) $roll->multiplier = 3;
if (array_key_exists('m', $opts)) $roll->meditation = true;
if (array_key_exists('h', $opts)) $help = true;
if (array_key_exists('w', $opts)) {
  if ($opts['w'] == 'dag')
    $roll->weapon = Roll::WEAP_SUB_DAGGER_2;
  elseif ($opts['w'] == 'shuriken')
    $roll->weapon = Roll::WEAP_DIS_SHURIKEN_2;
  elseif ($opts['w'] == 'short')
    $roll->weapon = Roll::WEAP_VIC_SHORT_2;
  else throw new Exception("Invalid weapon: " . $opts['w']);
}

if ($help) die($usage);
else echo $roll->attack();

/**
 * A class to calculate Ammonia Puk's attack and damage values.
 */
class Roll {
  
  /**#@+ Weapons. */
  const WEAP_SUB_DAGGER_2 = 1;
  const WEAP_DIS_SHURIKEN_2 = 2;
  const WEAP_VIC_SHORT_2 = 3;
  /**#@-*/

  /** True if we have combat advantage, false if not. */
  public $ca = false;

  /** True if dealing Sneak Attack damage, false if not. */
  public $sneak = false;

  /** True if the damage is modified by Brutal Scoundrel. */
  public $brutal = false;

  /** True if this is a Meditation of the Blade. */
  public $meditation = false;

  /** The weapon being used; see the WEAP_ constants. */
  public $weapon = null;

  /** The 'x' in the x[W] damage. */
  public $multiplier = 1;

  /**
   * Class constructor.
   */
  public function __construct() {}

  /**
   * Rolls dice and returns the sum.
   * @param int $num the number of dice to roll.
   * @param int $sides the number of sides the dice has.
   * @param boolean $maximize true to maximize the roll.
   * @return int the sum of the rolls.
   */
  public static function roll($num, $sides, $maximize=false) {
    if ($maximize) return $num * $sides;
    $sum = 0;
    for ($i = 0; $i < $num; $i++) $sum += rand(1, $sides);
    return $sum;
  }

  /**
   * Determines the attack and damage values for a single strike.
   * @return string text describing the outcome of the attack.
   */
  public function attack() {
    $atkRoll = self::roll(1, 20);
    if ($atkRoll == 1) return "*** CRIT FAIL ***\n";
    $crit = false;
    if ($atkRoll >= 18 && $this->weapon == self::WEAP_SUB_DAGGER_2)
      $crit = true;
    $name = $baseATK = $baseDMG = $sides = null;
    // all components of the attack and damage will be placed in these queues
    $qATK = array(); $qDMG = array();
    $qATK['attack (1d20)'] = $atkRoll;
    if ($this->weapon == self::WEAP_SUB_DAGGER_2) {
      $name = "subtle dagger +2";
      $sides = 4;
      if ($this->meditation) $sides = 6;
      $baseATK = 22;
      $baseDMG = 8;
      if ($this->ca) $qDMG["subtle weapon"] = 2;
      if ($crit) $qDMG['critical damage (2d6)'] = self::roll(2, 6);
    }
    elseif ($this->weapon == self::WEAP_DIS_SHURIKEN_2) {
      $name = "distance shuriken +2";
      $sides = 6;
      $baseATK = 21;
      $baseDMG = 8;
    }
    elseif ($this->weapon == self::WEAP_VIC_SHORT_2) {
      $name = "vicious short sword +2";
      $sides = 6;
      $baseATK = 21;
      $baseDMG = 8;
      if ($crit) $qDMG['critical damage (2d12)'] = self::roll(2, 12);
    }
    else throw new Exception("Invalid weapon: $this->weapon");
    $qATK["base attack ($name)"] = $baseATK;
    $damKey = "$name damage ($this->multiplier" . "d$sides)";
    $qDMG[$damKey] = 0;
    for ($i = 0; $i < $this->multiplier; $i++)
      $qDMG[$damKey] += self::roll(1, $sides, $crit);
    $qDMG["$name damage (base)"] = $baseDMG;
    if ($this->brutal) $qDMG['brutal scoundral'] = 4;
    if ($this->ca) {
      $qATK["combat advantage"] = 2;
      $qATK["nimble blade"] = 1;
    }
    if ($this->sneak) {
      $qDMG['sneak attack (3d8)'] = self::roll(3, 8, $crit);
      $qDMG['sneak attack bonus'] = 5;
    }
    // build the description of what the hell happened
    $s = '';
    $atk = 0;
    $s .= "ATTACK\n";
    foreach ($qATK as $note => $amt) {
      $s .= "$amt\t$note\n";
      $atk += $amt;
    }
    $s .= "------------------\n";
    $s .= "*** $atk ***";
    if ($crit) $s .= " (CRIT!)";
    $s .= "\n\n";
    $dmg = 0;
    $s .= "DAMAGE\n";
    foreach ($qDMG as $note => $amt) {
      $s .= "$amt\t$note\n";
      $dmg += $amt;
    }
    $s .= "------------\n";
    $s .= "*** $dmg ***\n";
    return $s;
  }
}

?>