<?php 

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

return static function (RectorConfig $rectorConfig): void {


    $rectorConfig->paths([
        __DIR__ . '/frontend/default/views/templates/home.blade.php',
    ]);


    // register single rule
    $rectorConfig->rule(TypedPropertyFromStrictConstructorRector::class);
    $rectorConfig->rule(NullToStrictStringFuncCallArgRector::class);
    

    // here we can define, what sets of rules will be applied
    // tip: use "SetList" class to autocomplete sets with your IDE
    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::PHP_82
    ]);
};