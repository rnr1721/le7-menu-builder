<?php

declare (strict_types=1);

namespace Core\Interfaces;

/**
 * Interface MenuRendererInterface
 *
 * This interface defines the methods required for a menu renderer.
 * A menu renderer is responsible for rendering the menu array into a string representation.
 */
interface MenuRendererInterface
{

    /**
     * Renders the menu array into a representation.
     *
     * @param array $menu The menu array to render.
     * @param array|null $options Additional options for rendering the menu (optional).
     * @return mixed The rendered menu as a string.
     */
    public function render(
            array $menu = [],
            ?array $options = null
    ): mixed;

    /**
     * Retrieves the options supported by the menu renderer.
     *
     * @return array The options supported by the menu renderer.
     */
    public function getOptions(): array;
}
