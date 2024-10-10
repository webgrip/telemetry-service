<?php

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector;

require_once __DIR__ . '/vendor/autoload.php';

// Define what rule sets will the Rector apply
return static function (Rector\Config\RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/pub',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/tests/bootstrap.php', // for example, exclude test bootstrap file
    ]);

    // Define sets of rules
    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::PHP_82,
        SetList::TYPE_DECLARATION,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::PRIVATIZATION,
        SetList::EARLY_RETURN,
    ]);

    // Add specific rector with configuration
    $rectorConfig->ruleWithConfiguration(ClassPropertyAssignToConstructorPromotionRector::class, [
        'promote_only_existing_assigns' => true,
    ]);
};