#!/opt/local/bin/php
<?php

/*
 *
 * ALL OF THIS ASSUMES ATTACKS WITH SUBTLE DAGGER + 2
 *
 */

$atkRoll = roll(1, 20);

$qATK = array('base attack' => 16);
$qDMG = array('dagger damage (1d4)' => $atkRoll == 20 ? 4 : roll(1, 4),
              'dagger damage (base)' => 7);

// process command line arguments
$opts = getopt('csfb');
if ($opts === false) die("Bad arguments!\n");
if (array_key_exists('c', $opts)) {
  $qATK['combat advantage'] = 2;
  $qATK['nimble blade'] = 1;
  $qDMG['subtle weapon'] = 2;
}
if (array_key_exists('s', $opts)) {
  $qDMG['sneak attack (2d8)'] = roll(2, 8);
  $qDMG['sneak attack bonus'] = 4;
}
if (array_key_exists('f', $opts)) {
  $qDMG['furious assault (1d4)'] = roll(1, 4);
}
if (array_key_exists('b', $opts)) {
  $qDMG['brutal scoundral'] = 4;
}
if ($atkRoll == 20) {
  $qDMG['crital damage (2d6)'] = roll(2, 6);
}
$qATK['attack (1d20)'] = $atkRoll;

// print out the results
$atk = 0;
echo "ATTACK\n";
foreach ($qATK as $note => $amt) {
  echo "$amt\t$note\n";
  $atk += $amt;
}
echo "------------------\n";
if ($atkRoll == 1) $atk = "FAIL!";
echo "*** $atk ***";
if ($atkRoll == 20) echo " (CRIT!)";
echo "\n\n";
$dmg = 0;
echo "DAMAGE\n";
foreach ($qDMG as $note => $amt) {
  echo "$amt\t$note\n";
  $dmg += $amt;
}
echo "------------\n";
echo "*** $dmg ***\n";

function roll($num, $sides) {
  $sum = 0;
  for ($i = 0; $i < $num; $i++)
    $sum += rand(1, $sides);
  return $sum;
}

?>