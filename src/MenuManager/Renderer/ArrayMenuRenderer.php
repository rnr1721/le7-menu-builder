<?php

declare (strict_types=1);

namespace Core\MenuManager\Renderer;

use Core\MenuManager\Renderer\Traits\MenuRendererTrait;
use Core\MenuManager\Renderer\Traits\MenuRendererArrayTrait;
use Core\Interfaces\MenuRendererInterface;

/**
 * Render menu to array
 */
class ArrayMenuRenderer implements MenuRendererInterface
{

    use MenuRendererArrayTrait;
    use MenuRendererTrait;

    /**
     * Options for array rendering
     * 
     * @var array
     */
    protected array $options = [
        'menuId' => 'default'
    ];

    /**
     * Creates a new instance of ArrayMenuRenderer.
     *
     * @param array|null $options The options for the menu renderer. Defaults to null.
     */
    public function __construct(?array $options = null)
    {
        $this->setOptions($options);
    }

    /**
     * Renders the menu based on the provided menu items and options.
     *
     * @param array $menu The array of menu items.
     * @param mixed $options The options for rendering the menu. Defaults to null.
     * @return array The rendered menu as an array.
     */
    public function render(array $menu = [], mixed $options = null): array
    {
        $this->setOptions($options);
        return $this->buildTree($menu);
    }
}
