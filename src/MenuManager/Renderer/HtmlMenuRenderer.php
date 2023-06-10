<?php

declare (strict_types=1);

namespace Core\MenuManager\Renderer;

use Core\Interfaces\ULinkInterface;
use Core\Interfaces\MenuRendererInterface;
use Core\MenuManager\Renderer\Traits\MenuRendererTrait;

/**
 * Render menu to HTML format
 */
class HtmlMenuRenderer implements MenuRendererInterface
{

    use MenuRendererTrait;

    /**
     * Options for HTML rendering
     * 
     * @var array
     */
    protected array $options = [
        'menuId' => 'default',
        'menuClass' => 'navbar-nav',
        'menuItemClass' => 'nav-item',
        'menuAttributes' => [],
        'menuItemAttributes' => ['class' => 'nav-link'],
        'subMenuClass' => 'dropdown-menu',
        'subMenuStyle' => '',
        'openOnHover' => false,
        'animationSpeed' => 'fast',
        'whiteSpaces' => 0
    ];

    public function __construct(?array $options = null)
    {
        $this->setOptions($options);
    }

    /**
     * Renders the menu in HTML format.
     *
     * @param array $menu The menu array.
     * @param array|null $options The rendering options.
     * @return string The rendered menu HTML.
     */
    public function render(array $menu = [], ?array $options = null): string
    {
        $this->setOptions($options);

        $menuId = $this->options['menuId'];
        $menuClass = $this->options['menuClass'];
        $menuItemClass = $this->options['menuItemClass'];
        $menuAttributes = $this->buildAttributes($this->options['menuAttributes']);
        $menuItemAttributes = $this->options['menuItemAttributes'];
        $subMenuClass = $this->options['subMenuClass'];
        $subMenuStyle = $this->options['subMenuStyle'];
        $whiteSpaces = $this->options['whiteSpaces'];

        $idString = ($menuId === '' ? '' : " id=\"$menuId\"");
        $classString = ($menuClass === '' ? '' : " class=\"$menuClass\"");
        $html = str_repeat(" ", $whiteSpaces) . "<ul$idString$classString$menuAttributes>" . PHP_EOL;

        foreach ($menu as $item) {
            $html .= $this->renderMenuItem(
                            $item,
                            $menuItemClass,
                            $menuItemAttributes,
                            $subMenuClass,
                            $subMenuStyle,
                            $whiteSpaces + 4
                    ) . PHP_EOL;
        }

        $html .= str_repeat(" ", $whiteSpaces) . '</ul>' . PHP_EOL;

        return $html;
    }

    /**
     * Renders a menu item.
     *
     * @param array $item The menu item array.
     * @param string $menuItemClass The class for menu items.
     * @param array $menuItemAttributes The attributes for menu items.
     * @param string $subMenuClass The class for sub-menus.
     * @param string $subMenuStyle The style for sub-menus.
     * @param int $whiteSpaces for formatting
     * @return string The rendered menu item HTML.
     */
    protected function renderMenuItem(
            array $item,
            string $menuItemClass,
            array $menuItemAttributes,
            string $subMenuClass,
            string $subMenuStyle,
            int $whiteSpaces = 2
    ): string
    {
        /** @var ULinkInterface $menuItem */
        $menuItem = $item['item'];
        $children = $item['children'];

        $itemClassString = ($menuItemClass === '' ? '' : " class=\"$menuItemClass\"");

        $html = str_repeat(" ", $whiteSpaces + 4) . "<li$itemClassString>" . PHP_EOL;

        if ($this->isOpenOnHover()) {
            $menuItemAttributes['class'] = $this->mergeToValue($menuItemAttributes['class'], 'dropdown-toggle');
            $menuItemAttributes['data-toggle'] = $this->mergeToValue($menuItemAttributes['class'], 'dropdown');
        }

        $oldAttributes = $menuItem->getAttributes();

        $attributes = $this->mergeAttributes($oldAttributes, $menuItemAttributes);

        $html .= str_repeat(" ", $whiteSpaces + 6) . $menuItem->withAttributes($attributes)->render() . PHP_EOL;

        if (!empty($children)) {

            if ($this->isOpenOnHover()) {
                $subMenuClass .= $this->mergeToValue($subMenuClass, 'dropdown-menu');
            }

            if ($this->getAnimationSpeed() === 'fast') {
                $subMenuClass = $this->mergeToValue($subMenuClass, 'show');
            } elseif ($this->getAnimationSpeed() === 'slow') {
                $subMenuClass = $this->mergeToValue($subMenuClass, 'slow-animation-class');
            }

            $classString = ($subMenuClass === '' ? '' : " class=\"$subMenuClass\"");
            $styleString = ($subMenuStyle === '' ? '' : " style=\"$subMenuStyle\"");

            $html .= str_repeat(" ", $whiteSpaces + 6) . "<ul$classString$styleString>" . PHP_EOL;
            foreach ($children as $childItem) {
                $html .= $this->renderMenuItem(
                        $childItem,
                        $menuItemClass,
                        $menuItemAttributes,
                        $subMenuClass,
                        $subMenuStyle,
                        $whiteSpaces + 4
                );
            }
            $html .= str_repeat(" ", $whiteSpaces + 6) . '</ul>' . PHP_EOL;
        }

        $html .= str_repeat(" ", $whiteSpaces + 4) . '</li>' . PHP_EOL;

        return $html;
    }

    /**
     * Merges a value into an existing value, separated by a space, if not already present.
     *
     * @param string $value The original value.
     * @param string $dataToAdd The value to add.
     * @return string The merged value.
     */
    protected function mergeToValue(string $value, string $dataToAdd): string
    {
        if ($value === '') {
            return $dataToAdd;
        }
        if (!str_contains($value, $dataToAdd)) {
            return $value . ' ' . $dataToAdd;
        }
        return $dataToAdd;
    }

    /**
     * Build HTML attributes string from array.
     *
     * @param array $attributes The attributes array.
     * @return string The HTML attributes string.
     */
    protected function buildAttributes(array $attributes): string
    {
        $attributesString = '';
        foreach ($attributes as $key => $value) {
            $attributesString .= " $key=\"$value\"";
        }
        return $attributesString;
    }

    /**
     * Merges two arrays of attributes, combining class and rel attributes.
     *
     * @param array $oldAttributes The original attributes array.
     * @param array $newAttributes The new attributes array.
     * @return array The merged attributes array.
     */
    protected function mergeAttributes(array $oldAttributes, array $newAttributes): array
    {
        foreach ($newAttributes as $key => $value) {
            if (($key === 'class' || $key === 'rel') && isset($oldAttributes[$key])) {
                $oldValue = $oldAttributes[$key];
                $newAttributes[$key] = $this->mergeAttribute($oldValue, $value);
            } else {
                $newAttributes[$key] = $value;
            }
        }

        return $newAttributes;
    }

    /**
     * Merges two attribute values, separated by a space, and removes duplicates.
     *
     * @param string $oldValue The original attribute value.
     * @param string $newValue The new attribute value.
     * @return string The merged attribute value.
     */
    protected function mergeAttribute(string $oldValue, string $newValue): string
    {
        $oldClasses = explode(' ', $oldValue);
        $newClasses = explode(' ', $newValue);
        $mergedClasses = array_unique(array_merge($oldClasses, $newClasses));
        return implode(' ', $mergedClasses);
    }

    /**
     * Checks if the menu should open on hover.
     *
     * @return bool True if the menu should open on hover, false otherwise.
     */
    protected function isOpenOnHover(): bool
    {
        return $this->options['openOnHover'];
    }

    /**
     * Gets the animation speed for the menu.
     *
     * @return string The animation speed.
     */
    protected function getAnimationSpeed(): string
    {
        return $this->options['animationSpeed'];
    }
}
