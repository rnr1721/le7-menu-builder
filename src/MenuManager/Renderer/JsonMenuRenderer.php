<?php

namespace Core\MenuManager\Renderer;

use Core\Interfaces\MenuRendererInterface;
use Core\MenuManager\Renderer\Traits\MenuRendererTrait;
use Core\MenuManager\Renderer\Traits\MenuRendererArrayTrait;

/**
 * Render menu in JSON format
 */
class JsonMenuRenderer implements MenuRendererInterface
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
     * @return string The rendered menu as an array.
     * @psalm-suppress ImplementedReturnTypeMismatch
     * @psalm-suppress MethodSignatureMismatch
     */
    public function render(
            array $menu = [],
            mixed $options = null
    ): string
    {
        $this->setOptions($options);
        $result = $this->buildTree($menu);
        return json_encode($result, JSON_PRETTY_PRINT);
    }
}
