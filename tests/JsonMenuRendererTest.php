<?php

use Core\MenuManager\Renderer\JsonMenuRenderer;
use Core\MenuManager\MenuBuilder;
use PHPUnit\Framework\TestCase;

class JsonMenuRendererTest extends TestCase
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

        $renderer = new JsonMenuRenderer();

        $menuBuilder = new MenuBuilder($renderer);

        $menuBuilder->addItem('home', 'Home', '/', null, [], []);

        $result = json_decode($menuBuilder->render(), true);

        $this->assertEquals($expected, $result);
    }
}
