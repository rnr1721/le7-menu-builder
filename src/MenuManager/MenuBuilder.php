<?php

declare (strict_types=1);

namespace Core\MenuManager;

use Core\Interfaces\MenuRendererInterface;
use Core\Interfaces\MenuBuilderInterface;
use Core\Interfaces\ULinkInterface;
use Core\Links\Link;
use Psr\SimpleCache\CacheInterface;
use \InvalidArgumentException;
use \DateInterval;
use function array_search,
             array_key_exists,
             array_keys,
             is_int,
             is_string,
             is_null,
             is_array,
             uasort;

/**
 * Class MenuBuilder
 *
 * This class provides a menu builder functionality for creating and managing menus.
 * It allows adding links to the menu, adding child links to existing menu items,
 * removing links from the menu, and retrieving the built menu array.
 */
class MenuBuilder implements MenuBuilderInterface
{

    /**
     * Render engine for the menu.
     *
     * @var MenuRendererInterface|null
     */
    private ?MenuRendererInterface $renderer = null;

    /**
     * PSR cache for caching features
     * 
     * @var CacheInterface|null
     */
    private ?CacheInterface $cache = null;

    /**
     * Cache TTL value
     * 
     * @var int|null|DateInterval
     */
    private int|null|DateInterval $cacheTtl = null;

    /**
     * Prefix for cache key
     * 
     * @var string
     */
    private string $cacheKeyPrefix = 'menu_';

    /**
     * Current active menu
     * 
     * @var string
     */
    private string $currentId = 'default';

    /**
     * The menu array.
     *
     * @var array
     */
    private array $menu = [
        'default' => []
    ];

    /**
     * The menu source array.
     *
     * @var array
     */
    private array $menuSource = [];

    /**
     * MenuBuilder constructor.
     *
     * @param MenuRendererInterface|null $renderer The menu renderer to use.
     */
    public function __construct(
            ?MenuRendererInterface $renderer = null,
            ?CacheInterface $cache = null
    )
    {
        $this->renderer = $renderer;
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function addItem(
            string $key,
            string $label,
            string $url,
            ?string $parentKey = null,
            array $attributes = [],
            array $rels = [],
            int $weight = 50
    ): self
    {

        $link = new Link($url, $rels, $attributes, $label);

        $this->addRawItem($key, $link, $parentKey, $weight);

        return $this;
    }

    /**
     * @inheritdoc
     * @throws InvalidArgumentException
     */
    public function addRawItem(
            string $key,
            ULinkInterface $link,
            ?string $parentKey = null,
            int $weight = 50
    ): self
    {

        $currentItem = $this->recursiveFindItemByKey($key, $this->menu[$this->currentId]);
        if ($currentItem) {
            throw new InvalidArgumentException("Menu key exists: $key");
        }
        if (empty($link->getAnchor())) {
            throw new InvalidArgumentException("Empty link anchor");
        }

        if ($parentKey === null) {
            $this->menu[$this->currentId][$key] = [
                'item' => $link,
                'children' => [],
                'weight' => $weight
            ];
        } else {
            $this->addChildToMenu($key, $link, $parentKey, $weight);
        }

        $this->menuSource[$this->currentId][$key] = [
            'label' => $link->getAnchor(),
            'url' => $link->getHref(),
            'parentKey' => $parentKey,
            'attributes' => $link->getAttributes(),
            'rels' => $link->getRels(),
            'weight' => $weight
        ];
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLink(string $key): ?ULinkInterface
    {
        $item = $this->recursiveFindItemByKey($key, $this->menu[$this->currentId]);
        if ($item) {
            return $item['item'];
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function removeLink(string $key): self
    {
        $this->recursiveRemoveItem($key, $this->menu[$this->currentId]);
        return $this;
    }

    /**
     * Sets the specified link as active by adding the 'active' class to its attributes.
     *
     * @param string $key The key of the link to make active.
     * @return self
     */
    public function makeActive(string $key): self
    {
        if (!$this->recursiveFindItemByKey($key, $this->menu[$this->currentId])) {
            throw new InvalidArgumentException("Menu key not found: " . $key);
        }
        $this->recursiveAddAttribute($key, 'class', 'active', $this->menu[$this->currentId]);
        return $this;
    }

    /**
     * Add attribute to link by key.
     * If attribute exists, it will be added to existing
     * 
     * @param string $key The key of the link to add attribute
     * @param string $attribute Attribute name, example: class
     * @param string $value Attribute value, example: active
     * @return self
     * @throws InvalidArgumentException
     */
    public function addAttribute(string $key, string $attribute, string $value): self
    {
        if (!$this->recursiveFindItemByKey($key, $this->menu[$this->currentId])) {
            throw new InvalidArgumentException("Menu key not found: " . $key);
        }
        $this->recursiveAddAttribute($key, $attribute, $value, $this->menu[$this->currentId]);
        return $this;
    }

    /**
     * Adds a child link to the menu.
     *
     * @param string $childKey The key of the child link.
     * @param ULinkInterface $childLink The child link to add.
     * @param string $parentKey The parent key of the child link.
     * @param int $weight The weight of the child link for sorting.
     */
    private function addChildToMenu(
            string $childKey,
            ULinkInterface $childLink,
            string $parentKey,
            int $weight
    ): void
    {
        $this->recursiveAddChild(
                $childKey,
                $childLink,
                $this->menu[$this->currentId],
                $parentKey,
                $weight
        );
    }

    /**
     * Recursively finds a menu item by its key.
     *
     * @param string $key The key to search for.
     * @param array $menuItems The menu items to search within.
     * @return array|null The menu item or null if not found.
     */
    private function recursiveFindItemByKey(string $key, array $menuItems): ?array
    {
        if (isset($menuItems[$key])) {
            return $menuItems[$key];
        }

        foreach ($menuItems as $menuItem) {
            if (!empty($menuItem['children'])) {
                $childItem = $this->recursiveFindItemByKey($key, $menuItem['children']);
                if ($childItem !== null) {
                    return $childItem;
                }
            }
        }

        return null;
    }

    /**
     * Recursively adds a child to the menu.
     *
     * @param string $childKey The key of the child link.
     * @param ULinkInterface $childLink The child link to add.
     * @param array $menu The menu array.
     * @param string $parentKey The parent key of the child link.
     * @param int $weight The weight of the link for sorting.
     */
    private function recursiveAddChild(
            string $childKey,
            ULinkInterface $childLink,
            array &$menu,
            string $parentKey,
            int $weight
    ): void
    {
        foreach ($menu as &$item) {
            $itemKey = array_search($item, $menu, true);

            if ($itemKey === $parentKey) {
                $item['children'][$childKey] = [
                    'item' => $childLink,
                    'children' => [],
                    'weight' => $weight
                ];
                return;
            }

            if (!empty($item['children'])) {
                $this->recursiveAddChild(
                        $childKey,
                        $childLink,
                        $item['children'],
                        $parentKey,
                        $weight
                );
            }
        }
    }

    /**
     * Recursively removes an item from the menu.
     *
     * @param string $key The key of the item to remove.
     * @param array $menu The menu array.
     */
    private function recursiveRemoveItem(string $key, array &$menu): void
    {
        foreach ($menu as $itemKey => $item) {
            if ($itemKey === $key) {
                unset($menu[$itemKey]);
                return;
            }

            if (!empty($item['children'])) {
                $this->recursiveRemoveItem($key, $item['children']);
            }
        }
    }

    /**
     * Recursively add attribute by key.
     *
     * @param string $key The key of the link to change.
     * @param string $attribute The attribute to add.
     * @param string $value The value of attribute to add.
     * @param array $menuItems The menu items to search within.
     */
    private function recursiveAddAttribute(
            string $key,
            string $attribute,
            string $value,
            array &$menuItems
    ): void
    {
        foreach ($menuItems as $itemKey => &$menuItem) {
            if ($itemKey === $key) {
                $menuItem['item'] = $menuItem['item']->withAddedAttribute($attribute, $value);
            }
            if (!empty($menuItem['children'])) {
                $this->recursiveAddAttribute($key, $attribute, $value, $menuItem['children']);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function build(): array
    {
        $menu = $this->menu[$this->currentId];
        $sortedMenu = $this->sortMenu($menu);
        return $sortedMenu;
    }

    /**
     * @inheritdoc
     * @throws InvalidArgumentException If any of the keys are not strings, or if incorrect types are provided for the parentKey, attributes, or rels.
     */
    public function importSource(array $menu, ?string $id = null): self
    {
        if ($id) {
            if (!array_key_exists($id, $menu)) {
                throw new InvalidArgumentException("Menu ID not found while import: " . $id);
            }
            $this->setCurrentId($id);
            $this->importSourceItem($menu[$id]);
        } else {
            foreach ($menu as $currentMenuKey => $currentMenu) {
                $this->setCurrentId($currentMenuKey);
                $this->importSourceItem($currentMenu);
            }
        }
        return $this;
    }

    /**
     * Add one menu
     * 
     * @param array $menu
     * @return void
     * @throws InvalidArgumentException
     */
    protected function importSourceItem(array $menu): void
    {
        foreach ($menu as $item) {
            $key = $item['key'];
            $label = $item['label'] ?? null;
            $url = $item['url'] ?? null;
            $parentKey = $item['parentKey'] ?? null;
            $attributes = $item['attributes'] ?? [];
            $rels = $item['rels'] ?? [];
            $weight = $item['weight'] ?? 50;
            if (!$label) {
                throw new InvalidArgumentException("Menu item label is a required value!");
            }
            if (!$url) {
                throw new InvalidArgumentException("Menu item url is a required value!");
            }
            if (!is_string($key) || !is_string($label) || !is_string($url)) {
                throw new InvalidArgumentException("Menu key, label and url must be string");
            }
            if (!is_string($parentKey) && !is_null($parentKey)) {
                throw new InvalidArgumentException("Menu parent key must be string or null");
            }
            if (!is_array($attributes)) {
                throw new InvalidArgumentException("Menu link attributes must be array");
            }
            if (!is_array($rels)) {
                throw new InvalidArgumentException("Menu link rels must be array");
            }
            if (!is_int($weight)) {
                throw new InvalidArgumentException("Menu link weight must be int value");
            }
            $this->addItem(
                    $key,
                    $label,
                    $url,
                    $parentKey,
                    $attributes,
                    $rels,
                    $weight
            );
        }
    }

    /**
     * Sorts the menu items based on their weight.
     *
     * @param array $menuItems The menu items to sort.
     * @return array The sorted menu items.
     */
    private function sortMenu(array $menuItems): array
    {
        uasort($menuItems, function (array $a, array $b): int {
            $weightA = $a['weight'] ?? 0;
            $weightB = $b['weight'] ?? 0;
            return $weightA - $weightB;
        });

        foreach ($menuItems as &$item) {
            if (!empty($item['children'])) {
                $item['children'] = $this->sortMenu($item['children']);
            }
        }

        return $menuItems;
    }

    /**
     * @inheritdoc
     */
    public function exportSource(?string $id = null): array|null
    {
        if ($id) {
            return $this->menuSource[$id] ?? null;
        }
        return $this->menuSource;
    }

    /**
     * @inheritdoc
     */
    public function render(
            ?array $options = null,
            ?MenuRendererInterface $renderer = null
    ): mixed
    {
        $cacheKey = $this->cacheKeyPrefix . $this->currentId;
        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }
        $renderEngine = $renderer ?? $this->renderer;
        if ($renderEngine === null) {
            throw new InvalidArgumentException("Menu renderer not found");
        }
        if (is_array($options)) {
            if (!isset($options['menuId'])) {
                $options['menuId'] = $this->currentId;
            }
        }
        $result = $renderEngine->render($this->build(), $options);
        if ($this->cache) {
            $this->cache->set($cacheKey, $result, $this->cacheTtl);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getRendererOptions(): array
    {
        if ($this->renderer === null) {
            throw new InvalidArgumentException("Menu renderer not found");
        }
        return $this->renderer->getOptions();
    }

    /**
     * @inheritdoc
     */
    public function getCurrentId(): string
    {
        return $this->currentId;
    }

    /**
     * @inheritdoc
     */
    public function setCurrentId(string $id): self
    {
        if (!array_key_exists($id, $this->menu)) {
            $this->menu[$id] = [];
        }
        $this->currentId = $id;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMenuIds(): array
    {
        return array_keys($this->menu);
    }

    /**
     * @inheritdoc
     */
    public function reset(): self
    {
        $this->menu = [
            'default' => []
        ];
        $this->currentId = 'default';
        $this->menuSource = [];
        return $this;
    }

    public function setCacheKeyPrefix(string $cacheKeyPrefix): self
    {
        $this->cacheKeyPrefix = $cacheKeyPrefix;
        return $this;
    }

    public function setCacheTtl(int|null|DateInterval $ttl): self
    {
        $this->cacheTtl = $ttl;
        return $this;
    }
}
