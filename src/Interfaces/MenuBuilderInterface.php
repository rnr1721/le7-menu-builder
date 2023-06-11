<?php

declare (strict_types=1);

namespace Core\Interfaces;

use \DateInterval;

/**
 * Interface MenuBuilderInterface
 *
 * This interface provides a menu builder functionality for creating and managing menus.
 * It allows adding links to the menu, adding child links to existing menu items,
 * removing links from the menu, and retrieving the built menu array.
 */
interface MenuBuilderInterface
{

    /**
     * Adds a link to the menu.
     *
     * @param string $key The key of the link.
     * @param string $label The label of the link.
     * @param string $url The URL of the link.
     * @param string|null $parentKey The parent key of the link.
     * @param array $attributes Additional attributes for the link.
     * @param array $rels Relations for this link.
     * @param int $weight Weight for this link for sorting.
     * @return self
     */
    public function addItem(
            string $key,
            string $label,
            string $url,
            string $parentKey = null,
            array $attributes = [],
            array $rels = [],
            int $weight = 50
    ): self;

    /**
     * Adds a link to the menu from ULinkInterface object.
     *
     * @param string $key The key of the link.
     * @param ULinkInterface $link The object of the link.
     * @param string|null $parentKey The parent key of the link.
     * @param int $weight Weight for this link for sorting.
     * @return self
     */
    public function addRawItem(
            string $key,
            ULinkInterface $link,
            ?string $parentKey = null,
            int $weight = 50
    ): self;

    /**
     * Retrieves a link from the menu based on its key.
     *
     * @param string $key The key of the link.
     * @return ULinkInterface|null The link or null if not found.
     */
    public function getLink(string $key): ?ULinkInterface;

    /**
     * Removes a link from the menu based on its key.
     *
     * @param string $key The key of the link to remove.
     * @return self
     */
    public function removeLink(string $key): self;

    /**
     * Sets the specified link as active by adding the 'active' class to its attributes.
     *
     * @param string $key The key of the link to make active.
     * @return self
     */
    public function makeActive(string $key): self;

    /**
     * Add attribute to link by key.
     * If attribute exists, it will be added to existing
     * 
     * @param string $key The key of the link to add attribute
     * @param string $attribute Attribute name, example: class
     * @param string $value Attribute value, example: active
     * @return self
     */
    public function addAttribute(string $key, string $attribute, string $value): self;

    /**
     * Returns the built menu array.
     *
     * @return array The menu array.
     */
    public function build(): array;

    /**
     * Adds menu items based on an array.
     *
     * @param array $menu The array of menu items.
     *  The array should be structured as follows:
     *  [
     *      'menu_id_1' => [
     *          'item_key_1' => [
     *              'label' => 'Item Label',
     *              'url' => 'item_url',
     *              'parentKey' => 'parent_item_key', // optional
     *              'attributes' => ['attr1' => 'value1'], // optional
     *              'rels' => ['rel1', 'rel2'], // optional
     *          ],
     *          'item_key_2' => [
     *              // ...
     *          ],
     *          // ...
     *      ],
     *      'menu_id_2' => [
     *          // ...
     *      ],
     *      // ...
     *  ]
     * @param string|null $id What menu for import from array? Null = all
     * @param array $searchUrl Optional array to search in URL
     * @param array $replaceUrl Optional array to replace in URL
     * @return self
     */
    public function importSource(
            array $menu,
            ?string $id = null,
            array $searchUrl = [],
            array $replaceUrl = []
    ): self;

    /**
     * Exports the menu source as an array.
     *
     * @param string|null $id What menu for export? Null = all
     * @return array|null The exported menu array.
     */
    public function exportSource(?string $id = null): array|null;

    /**
     * Renders the menu.
     *
     * @param array|null $options Additional options for rendering the menu.
     * @param MenuRendererInterface|null $renderer The menu renderer to use. If null, the default renderer will be used.
     * @return mixed The rendered menu.
     */
    public function render(
            ?array $options = null,
            ?MenuRendererInterface $renderer = null
    ): mixed;

    /**
     * Returns the options of the menu renderer.
     *
     * @return array The options of the menu renderer.
     */
    public function getRendererOptions(): array;

    /**
     * Set current menu ID
     * 
     * @param string $id
     * @return self
     */
    public function setCurrentId(string $id): self;

    /**
     * Return current menu ID
     * 
     * @return string
     */
    public function getCurrentId(): string;

    /**
     * Return array with menu Ids
     * 
     * @return array
     */
    public function getMenuIds(): array;

    /**
     * Reset menu to empty state
     * 
     * @return self
     */
    public function reset(): self;

    /**
     * Set key prefix for cached menus
     * if cache is available
     * 
     * @param string $cacheKeyPrefix Default is 'menu_'
     * @return self
     */
    public function setCacheKeyPrefix(string $cacheKeyPrefix): self;

    /**
     * Set Ttl for cache
     * 
     * @param int|null|DateInterval $ttl TTL for caching
     * @return self
     */
    public function setCacheTtl(int|null|DateInterval $ttl): self;

    /**
     * Set arrays with values that will be replaced in URLs
     * For example after this:
     * setUrlReplaceVars('url','https://example.com'),
     * you will can add links with urls like this: {url}/about
     * and in menu it will be normal links
     * 
     * @param string $search Items for search
     * @param string $replace
     * @return self
     */
    public function setUrlReplaceVars(string $search, string $replace): self;
}
