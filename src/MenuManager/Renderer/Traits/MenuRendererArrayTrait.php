<?php

declare (strict_types=1);

namespace Core\MenuManager\Renderer\Traits;

use Core\Interfaces\ULinkInterface;

trait MenuRendererArrayTrait
{

    /**
     * Builds the menu tree structure recursively based on the provided menu items.
     *
     * @param array $menuItems The array of menu items to build the tree from.
     * @return array The built menu tree structure as an array.
     */
    protected function buildTree(array $menuItems): array
    {
        $tree = [];

        foreach ($menuItems as $key => $menuItem) {
            /** @var ULinkInterface $item */
            $item = $menuItem['item'];
            $children = $menuItem['children'];

            $treeItem = [
                'item' => [
                    'key' => $key,
                    'label' => $item->getAnchor(),
                    'url' => $item->getHref(),
                    'attributes' => $item->getAttributes(),
                    'rels' => $item->getRels(),
                    'rendered' => $item->render()
                ],
                'children' => $this->buildTree($children),
            ];

            $tree[$key] = $treeItem;
        }

        return $tree;
    }
}
