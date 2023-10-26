<?php
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("STOP_STATISTICS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$context = \Bitrix\Main\Application::getInstance()->getContext();

$response = new \Bitrix\Main\HttpResponse($context);
$response->addHeader("Content-Type", "application/json");

$request = $context->getRequest();
$request->addFilter(new Bitrix\Main\Web\PostDecodeFilter);

if(!$request->isAjaxRequest() && !$request->isPost() && !check_bitrix_sessid()) {
    exit;
}

$arResult = [
    'status' => '',
    'text' => '',
];

if(empty($request["email"]) || !filter_var($request["email"], FILTER_VALIDATE_EMAIL)) {
    $arResult = [
        'status' => 'error',
        'text' => 'не указан email, или не правильно набран'
    ];

    $response->flush(Bitrix\Main\Web\Json::encode($arResult));
    exit;
}

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('itc3gd.subscribe');
\CBitrixComponent::includeComponentClass('itc3gd.subscribe:subscribe');

$component = new \SubscribeOnNews();
$hlBlockId = $component->getHighloadBlockId(\Itc3gd\HlblockHelper::getHlblockName());

if($component->isEmailExists($hlBlockId, $request["email"])) {
    $arResult = [
        'status' => 'error',
        'text' => 'Данный email уже подписан'
    ];

    $response->flush(Bitrix\Main\Web\Json::encode($arResult));
    exit;
}

$resultAdding = $component->addEmail($hlBlockId, $request["email"]);

if($resultAdding) {
    $arResult = [
        'status' => 'success',
    ];

    $response->flush(Bitrix\Main\Web\Json::encode($arResult));
    exit;
} else {
    $arResult = [
        'status' => 'error',
        'text' => 'Ошибка добавления email'
    ];

    $response->flush(Bitrix\Main\Web\Json::encode($arResult));
    exit;
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
