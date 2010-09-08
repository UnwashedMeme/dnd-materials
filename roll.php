#!/opt/local/bin/php
<?php

/**
 * A command-line tool to determine attack and damage dealt by Ammonia.
 *
 * @author Ross P. Davis
 */

$roll = new Roll();
$roll->weapon = Roll::WEAP_SUB_DAGGER_2; // default

// process command line arguments
$opts = getopt('csfbw:');
if ($opts === false) die("Bad arguments!\n");
if (array_key_exists('c', $opts)) $roll->ca = true;
if (array_key_exists('s', $opts)) $roll->sneak = true;
if (array_key_exists('f', $opts)) $roll->furious = true;
if (array_key_exists('b', $opts)) $roll->brutal = true;
if (array_key_exists('w', $opts)) {
  if ($opts['w'] == 'dag')
    $roll->weapon = Roll::WEAP_SUB_DAGGER_2;
  elseif ($opts['w'] == 'shuriken')
    $roll->weapon = Roll::WEAP_DIS_SHURIKEN_2;
  elseif ($opts['w'] == 'short')
    $roll->weapon = Roll::WEAP_VIC_SHORT_2;
  else throw new Exception("Invalid weapon: " . $opts['w']);
}

echo $roll->attack();

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

  /** True if this is a Furious Assault. */
  public $furious = false;

  /** The weapon being used; see the WEAP_ constants. */
  public $weapon = null;

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
      $baseATK = 17;
      $baseDMG = 7;
      if ($this->ca) $qDMG["subtle weapon"] = 2;
      if ($crit) $qDMG['critical damage (2d6)'] = self::roll(2, 6);
    }
    elseif ($this->weapon == self::WEAP_DIS_SHURIKEN_2) {
      $name = "distance shuriken +2";
      $sides = 6;
      $baseATK = 16;
      $baseDMG = 7;
    }
    elseif ($this->weapon == self::WEAP_VIC_SHORT_2) {
      $name = "vicious short sword +2";
      $sides = 6;
      $baseATK = 16;
      $baseDMG = 7;
      if ($crit) $qDMG['critical damage (2d12)'] = self::roll(2, 12);
    }
    else throw new Exception("Invalid weapon: $this->weapon");
    $qATK["base attack ($name)"] = $baseATK;
    $qDMG["$name damage (1d$sides)"] = self::roll(1, $sides, $crit);
    $qDMG["$name damage (base)"] = $baseDMG;
    if ($this->furious)
      $qDMG["furious assault (1d$sides)"] = self::roll(1, $sides, $crit);
    if ($this->brutal) $qDMG['brutal scoundral'] = 4;
    if ($this->ca) {
      $qATK["combat advantage"] = 2;
      $qATK["nimble blade"] = 1;
    }
    if ($this->sneak) {
      $qDMG['sneak attack (3d8)'] = self::roll(3, 8);
      $qDMG['sneak attack bonus'] = 4;
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