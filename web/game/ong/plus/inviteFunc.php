<?php

function inviteFunc($D)
{
    $tfuid = trim($_GET["tfuid"] ?? null);

    if ($tfuid) {
        if ($_SESSION['tuid']) {
            unset($_SESSION['tuid']);
        }
        $isgo = $tfuid == 'go';
        if ($isgo || $D->where(["uid" => (int)$tfuid])->zhicha("uid")->find()) {
            $_SESSION['tuid'] = $isgo ? 0 : (int)$tfuid;
            return $tfuid;
        }

    }
    return false;
}