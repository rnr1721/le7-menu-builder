<?php

declare (strict_types=1);

namespace Core\MenuManager\Renderer\Traits;

use \InvalidArgumentException;

trait MenuRendererTrait
{

    /**
     * Validate and set the rendering options.
     *
     * @param array|null $options The rendering options.
     * @throws InvalidArgumentException If an option is invalid.
     */
    protected function setOptions(?array $options): void
    {
        if (!$options) {
            return;
        }
        foreach ($options as $optionKey => $optionValue) {
            if (!isset($this->options[$optionKey])) {
                throw new InvalidArgumentException("Invalid rendering option: $optionKey");
            }

            $expectedType = gettype($this->options[$optionKey]);
            $actualType = gettype($optionValue);

            if ($expectedType !== $actualType) {
                throw new InvalidArgumentException("Invalid type for rendering option $optionKey. Expected $expectedType, got $actualType");
            }
        }
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Returns the options for the menu renderer.
     *
     * @return array The options for the menu renderer.
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
