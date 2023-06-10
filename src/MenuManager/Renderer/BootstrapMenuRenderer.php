<?php

namespace Core\MenuManager\Renderer;

class BootstrapMenuRenderer extends HtmlMenuRenderer
{

    /**
     * Options for Bootstrap HTML rendering
     * 
     * @var array
     */
    protected array $options = [
        'menuId' => 'default',
        'navClass' => 'navbar navbar-expand-lg navbar-light bg-light',
        'containerClass' => 'container',
        'wrapperClass' => 'collapse navbar-collapse',
        'menuClass' => 'navbar-nav',
        'menuItemClass' => 'nav-item',
        'menuAttributes' => [],
        'menuItemAttributes' => ['class' => 'nav-link'],
        'subMenuClass' => 'dropdown-menu',
        'subMenuStyle' => '',
        'openOnHover' => false,
        'animationSpeed' => 'fast',
        'whiteSpaces' => 4
    ];

    /**
     * Renders the menu in Bootstrap HTML format.
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

        $navClass = $this->options['navClass'];
        $containerClass = $this->options['containerClass'];
        $wrapperClass = $this->options['wrapperClass'];

        $navIdString = ($menuId === '' ? '' : " id=\"$menuId\"");
        $navClassString = ($navClass === '' ? '' : " class=\"$navClass\"");
        $html = str_repeat(" ", $whiteSpaces) . "<nav$navIdString$navClassString>" . PHP_EOL;

        $cntClassString = ($containerClass === '' ? '' : " class=\"$containerClass\"");
        $html .= str_repeat(" ", $whiteSpaces + 2) . "<div$cntClassString>" . PHP_EOL;

        $wrpClassString = ($wrapperClass === '' ? '' : " class=\"$wrapperClass\"");
        $html .= str_repeat(" ", $whiteSpaces + 4) . "<div$wrpClassString>" . PHP_EOL;

        $menuClassString = ($menuClass === '' ? '' : " class=\"$menuClass\"");
        $html .= str_repeat(" ", $whiteSpaces + 6) . "<ul$menuClassString$menuAttributes>" . PHP_EOL;

        foreach ($menu as $item) {
            $html .= $this->renderMenuItem($item, $menuItemClass, $menuItemAttributes, $subMenuClass, $subMenuStyle, $whiteSpaces + 4) . PHP_EOL;
        }

        $html .= str_repeat(" ", $whiteSpaces + 6) . '</ul>' . PHP_EOL;

        $html .= str_repeat(" ", $whiteSpaces + 4) . '</div>' . PHP_EOL;
        $html .= str_repeat(" ", $whiteSpaces + 2) . '</div>' . PHP_EOL;
        $html .= str_repeat(" ", $whiteSpaces) . '</nav>' . PHP_EOL;

        return $html;
    }
}
