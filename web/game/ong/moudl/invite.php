<?php if (!defined('ONGPHP'))
    exit('Error ONGSOFT');
plus(['txtcc',
      'mima',
      'jiami',
      'jianli',
      'x',
      'memcc',
      'isutf8',
      'shanchu',
      'setsession',
      'db',
      'ip',
      'post',
      'funciton',
      'inviteFunc'
     ]);
setsession($CONN['sessiontime']);
$Memsession = $Mem = new txtcc();
$Mem1 = new txtcc(ONGPHP . '/mode/');
$shezhi = $Mem1->g('shezhi');
$tfuid = inviteFunc(db('user'));
$urlEnd = $tfuid ? "?tfuid=" . $tfuid : '';
header("Location: {$shezhi["youxiyuming"]}" . $urlEnd);
