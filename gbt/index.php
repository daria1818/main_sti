<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\UserTable;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $USER;

$lecturerGroupID = 86;
$adminGroupID = 87;
$redirectUriLecturerAdmin = "/gbt/sda/";
$redirectUriDefault = "/gbt/courses/";

if ($USER->IsAuthorized()) {
    $userGroups = $USER->GetUserGroupArray();

    if (in_array($lecturerGroupID, $userGroups) || in_array($adminGroupID, $userGroups)) {
        LocalRedirect($redirectUriLecturerAdmin);
    } else {
        LocalRedirect($redirectUriDefault);
    }
} else {
    LocalRedirect($redirectUriDefault);
}
?>
