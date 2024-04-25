<?php

use Spatie\TypeScriptTransformer\TypeScript\TypeScriptBoolean;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;
use Spatie\TypeScriptTransformer\Visitor\Visitor;
use Spatie\TypeScriptTransformer\Visitor\VisitorOperation;

it('can visit a simple tree', function () {
    $unionNode = new TypeScriptUnion([
        $stringNode = new TypeScriptString(),
        $numberNode = new TypeScriptNumber(),
    ]);

    $baseNode = null;
    $subNodes = [];

    $visited = Visitor::create()
        ->before(function (TypeScriptNode $node) use (&$baseNode, &$subNodes) {
            if ($node instanceof TypeScriptUnion) {
                $baseNode = $node;
            } else {
                $subNodes[] = $node;
            }
        })
        ->execute($unionNode);

    expect($visited)->toBe($unionNode);
    expect($baseNode)->toBe($unionNode);
    expect($subNodes)->toEqual([$stringNode, $numberNode]);
});

it('can change a single node', function () {
    $unionNode = new TypeScriptUnion([
        $stringNode = new TypeScriptString(),
        new TypeScriptNumber(),
    ]);

    $visited = Visitor::create()
        ->before(function (TypeScriptNode $node) use (&$baseNode) {
            if ($node instanceof TypeScriptUnion) {
                unset($node->types[1]);
            }
        })
        ->execute($unionNode);

    expect($visited)->toBe($unionNode);
    expect($unionNode->types)->toEqual([$stringNode]);
});

it('can remove a single node in an iterateable', function () {
    $unionNode = new TypeScriptUnion([
        $stringNode = new TypeScriptString(),
        new TypeScriptNumber(),
    ]);

    $visited = Visitor::create()
        ->before(function (TypeScriptNode $node) {
            if ($node instanceof TypeScriptNumber) {
                return VisitorOperation::remove();
            }
        })
        ->execute($unionNode);

    expect($visited)->toBe($unionNode);
    expect($unionNode->types)->toEqual([$stringNode]);
});

it('can replace a single node in an iterateable', function () {
    $unionNode = new TypeScriptUnion([
        $stringNode = new TypeScriptString(),
        new TypeScriptNumber(),
    ]);

    $visited = Visitor::create()
        ->before(function (TypeScriptNode $node) {
            if ($node instanceof TypeScriptNumber) {
                return VisitorOperation::replace(
                    new TypeScriptBoolean(),
                );
            }
        })
        ->execute($unionNode);

    expect($visited)->toBe($unionNode);
    expect($unionNode->types)->toEqual([$stringNode, new TypeScriptBoolean()]);
});
