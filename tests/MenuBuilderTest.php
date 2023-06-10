<?php

use Core\MenuManager\MenuBuilder;
use Core\Links\Link;
use PHPUnit\Framework\TestCase;
use Core\Interfaces\ULinkInterface;

class MenuBuilderTest extends TestCase
{

    public function testAddLink(): void
    {
        $menuBuilder = new MenuBuilder();

        $menuBuilder->addItem('home', 'Home', '/', null, [], []);
        $menuBuilder->addItem('child', 'Home child', '/', 'home');

        $menu = $menuBuilder->build();
        $this->assertArrayHasKey('home', $menu);

        $link = $menu['home']['item'];

        $linkChildren = $menu['home']['children']['child']['item'];
        $this->assertInstanceOf(Link::class, $link);
        $this->assertInstanceOf(Link::class, $linkChildren);
        $this->assertEquals('Home', $link->getAnchor());
        $this->assertEquals('/', $link->getHref());
        $this->assertEquals('Home child', $menuBuilder->getLink('child')->getAnchor());
    }

    public function testAddLinSpecial(): void
    {
        $menuBuilder = new MenuBuilder();

        $menuBuilder->setCurrentId('newMenu');

        $menuBuilder->addItem('home', 'Home', '/', null, [], []);

        $menu = $menuBuilder->build();
        $this->assertArrayHasKey('home', $menu);

        $link = $menu['home']['item'];
        $this->assertInstanceOf(Link::class, $link);
        $this->assertEquals('Home', $link->getAnchor());
        $this->assertEquals('/', $link->getHref());
        $this->assertEquals('newMenu', $menuBuilder->getCurrentId());
        $menuBuilder->setCurrentId('default');
        $this->assertEquals([], $menuBuilder->build());
    }

    public function testGetLink(): void
    {
        $menuBuilder = new MenuBuilder();

        $menuBuilder->addItem('home', 'Home', '/', null, [], []);

        $link = $menuBuilder->getLink('home');
        $this->assertInstanceOf(ULinkInterface::class, $link);
        $this->assertEquals('Home', $link->getAnchor());
        $this->assertEquals('/', $link->getHref());
    }

    public function testRemoveLink(): void
    {
        $menuBuilder = new MenuBuilder();

        $menuBuilder->addItem('home', 'Home', '/', null, [], []);
        $this->assertArrayHasKey('home', $menuBuilder->build());

        $menuBuilder->removeLink('home');
        $this->assertArrayNotHasKey('home', $menuBuilder->build());
    }

    public function testMakeActive(): void
    {
        $menuBuilder = new MenuBuilder();

        $menuBuilder->addItem('home', 'Home', '/');
        $menuBuilder->makeActive('home');

        $menu = $menuBuilder->build();
        
        $this->assertArrayHasKey('home', $menu);

        $link = $menu['home']['item'];
        $this->assertInstanceOf(Link::class, $link);

        $this->assertEquals('active', $link->getAttribute('class'));
    }
}
