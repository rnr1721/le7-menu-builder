<?php

use Core\MenuManager\Renderer\ArrayMenuRenderer;
use Core\MenuManager\MenuBuilder;
use PHPUnit\Framework\TestCase;

class ArrayMenuRendererTest extends TestCase
{

    public function testAddLink(): void
    {

        $expected = [
            'home' => [
                'item' => [
                    'key' => 'home',
                    'label' => 'Home',
                    'url' => '/',
                    'attributes' => [],
                    'rels' => [],
                    'rendered' => '<a href="/" >Home</a>'
                ],
                'children' => []
            ]
        ];

        $renderer = new ArrayMenuRenderer();

        $menuBuilder = new MenuBuilder($renderer);

        $menuBuilder->addItem('home', 'Home', '/', null, [], []);

        $result = $menuBuilder->render();

        $this->assertEquals($expected, $result);
    }
}
