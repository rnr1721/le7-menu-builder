<?php

use Core\MenuManager\Renderer\BootstrapMenuRenderer;
use Core\MenuManager\MenuBuilder;
use PHPUnit\Framework\TestCase;

class BootstrapMenuRendererTest extends TestCase
{

    public function testRender()
    {
        // Создаем экземпляр MenuBuilder с BootstrapMenuRenderer
        $menuBuilder = new MenuBuilder(new BootstrapMenuRenderer());

        // Добавляем ссылки в меню
        $menuBuilder->addItem('home', 'Home', '/');
        $menuBuilder->addItem('about', 'About', '/about');
        $menuBuilder->addItem('services', 'Services', '/services', null, ['class' => 'myclass']);
        $menuBuilder->addItem('products', 'Products', '/products', null, [], ['nofollow']);
        $menuBuilder->addItem('solutions', 'Solutions', '/solutions', null, [], [], 30);

        // Добавляем дочерние ссылки к существующим элементам меню
        $menuBuilder->addItem('contact', 'Contact', '/contact', 'about');
        $menuBuilder->addItem('support', 'Support', '/support', 'services');

        // Добавляем атрибут к существующей ссылке в меню
        $menuBuilder->addAttribute('support', 'id', 'myclass');

        // Делаем ссылку "about" активной
        $menuBuilder->makeActive('about');

        // Удаляем ссылку "services" из меню
        $menuBuilder->removeLink('services');

        // Генерируем HTML-разметку меню с использованием BootstrapMenuRenderer
        $html = $menuBuilder->render();

        $expected = [
            ' <nav id="default" class="navbar navbar-expand-lg navbar-light bg-light">',
            '      <div class="container">',
            '        <div class="collapse navbar-collapse">',
            '          <ul class="navbar-nav">',
            '            <li class="nav-item">',
            '              <a href="/solutions" class="nav-link">Solutions</a>',
            '            </li>',
            '            <li class="nav-item">',
            '              <a href="/" class="nav-link">Home</a>',
            '            </li>',
            '            <li class="nav-item">',
            '              <a href="/about" class="active nav-link">About</a>',
            '              <ul class="dropdown-menu show">',
            '                <li class="nav-item">',
            '                  <a href="/contact" class="nav-link">Contact</a>',
            '                </li>',
            '              </ul>',
            '            </li>',
            '            <li class="nav-item">',
            '              <a href="/products" rel="nofollow"class="nav-link">Products</a>',
            '            </li>',
            '          </ul>',
            '        </div>',
            '      </div>',
            '    </nav>'
        ];

        foreach ($expected as $current) {
            $current = str_contains($html, trim($current));
            $this->assertTrue($current);
        }
    }
}
