# le7-menu-builder
HTML menu builder for le7 PHP MVC framework or any PHP 8 project

The PHP Menu Manager is a flexible and customizable class that allows you to
create, manage, and render menus in your PHP applications. It provides
functionality to add, remove, and modify menu items, as well as rendering
the menu in various formats.

Key Features:

- Create and manage hierarchical menus with unlimited levels of depth.
- Manage many different menus in one instance with different renderers
- Manage attributes and rels of links in menu
- Add, remove, and modify menu items with ease.
- Customize menu rendering by providing your own rendering engine or using the default one.
- Support for caching to improve performance when rendering menus.
- Easily import and export menu items from an external data source.

The Menu Manager class is designed to be simple to use and extend.
It uses an array-based data structure to represent menus and provides
methods to manipulate and render the menus.

## Install

```shell
composer require rnr1721/le7-menu-builder
```

## Testing

```shell
composer test
```

## Basic usage

```php
use Core\MenuManager\MenuBuilder;
use Core\MenuManager\Renderers\BootstrapMenuRenderer;

// Create MenuBuilder instance
$menuBuilder = new MenuBuilder(new BootstrapMenuRenderer());

// Add links to menu
// key, label, url, parent_key, attributes, rels, weight
$menuBuilder->addItem('home', 'Home', '/');
$menuBuilder->addItem('about', 'About', '/about');
// Here we add some class to menu link
$menuBuilder->addItem('services', 'Services', '/services', null, ['class' => 'myclass']);
// Here we add some rel to menu link
$menuBuilder->addItem('products', 'Products', '/products', null, [], ['nofollow']);
// Here we add weight for sorting
$menuBuilder->addItem('solutions', 'Solutions', '/solutions', null, [], [], 30);

// Add child links to existing menu items
$menuBuilder->addItem('contact', 'Contact', '/contact', 'about');
$menuBuilder->addItem('support', 'Support', '/support', 'services');

// Add some attribute for existing link in menu
// In this case we will add ID for link
$menuBuilder->addAttribute('support','id','myclass');

// Add attribute "class" => 'active' to link by key
$menuBuilder->makeActive('about');

// Remove link from menu by key
$menuBuilder->removeLink('services');

// Render the menu
$html = $menuBuilder->render();

// Optionally, you can render menu with some options or alternative renderer
$html = $menuBuilder->render($options, $renderer);

// Build raw menu array
$menu = $menuBuilder->build();
```

## Multiple different menus in one instance of MenuBuilder

You can build and render different menus in one instance.
When you create instance of MenuBuilder, it will create default menu named
'default'. But you can easy create another and make it active. Example:

```php
use Core\MenuManager\MenuBuilder;
use Core\MenuManager\Renderers\BootstrapMenuRenderer;

// Create MenuBuilder instance
$menuBuilder = new MenuBuilder(new BootstrapMenuRenderer());

// Add item to default menu
$menuBuilder->addItem('home', 'Home', '/');

// Create and set active another menu
$menuBuilder->setActiveMenu('newMenu');

// Add item to default menu or make some else
$menuBuilder->addItem('about', 'About', '/about');

// Switch to default menu
$menuBuilder->setActiveMenu('default');

// Render first menu (with Home item)
$menuBuilder->render();

```

## Raw links

This project use this implementation of PSR LinkInterface:
https://github.com/rnr1721/le7-links
This implementation of both PSR LinkInterface and UriInterface,
but contain own methods, such as render, getAnchor, withAnchor etc...

```php
use Core\Links\Link;
use Core\MenuManager\MenuBuilder;
use Core\MenuManager\Renderers\BootstrapMenuRenderer;

// Create MenuBuilder instance
$menuBuilder = new MenuBuilder(new BootstrapMenuRenderer());

// Here we create link with rel="nofollow" and class="myClass" and anchor "About"
$link = new Link(
    'https://example.com/about',
    ['nofollow'],
    ['class'=>'myClass'],
    'About'
);

// Parameters - key, link object, parentKey, weight
$menuBuilder->addRawItem('about', $link, null 80);

// Render the menu
$menuBuilder->render();
```

## Renderer options

Any MenuRenderer have own set of settings as array. You can see default settings
and change them when render. Example for bootstrap renderer:

```php
use Core\Links\Link;
use Core\MenuManager\MenuBuilder;
use Core\MenuManager\Renderers\BootstrapMenuRenderer;

// Create MenuBuilder instance
$menuBuilder = new MenuBuilder(new BootstrapMenuRenderer());
// Get array of default renderer options
$options = $menuBuilder->getRendererOptions();
```

Of course, you can change this options:

```php
use Core\Links\Link;
use Core\MenuManager\MenuBuilder;
use Core\MenuManager\Renderers\BootstrapMenuRenderer;

// Create MenuBuilder instance
$menuBuilder = new MenuBuilder(new BootstrapMenuRenderer());

// Add menu items
$menuBuilder->addItem('home', 'Home', '/');
$menuBuilder->addItem('about', 'About', '/about');

    $options = [
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

// Render with options
$menuBuilder->render($options);

```

IMPORTANT: not necessary to set all options, you can change only that you
need to change. Also, you can not change data type of option, it will call
exception. Every renderer can have own list of options or dont have them.

## Cache options

You can cache the menu render result.

```php
use Psr\SimpleCache\CacheInterface;

// Create an instance of MenuBuilder with cache support
$renderer = /* get an instance of a class implementing the MenuRendererInterface */;
$cache = /* get an instance of a class implementing the CacheInterface */;
$menuBuilder = new MenuBuilder($renderer, $cache);

// Set cache TTL (Time To Live)
$cacheTtl = 3600; // cache TTL in seconds can be null|int|DateInterval
$menuBuilder->setCacheTtl($cacheTtl);

// Set cache prefix (default is menu_)
$menuBuilder->setCacheKeyPrefix('menu_en_');

// Generate the menu and cache its result
$menuHtml = $menuBuilder->render();

```

## Renderers

These renderers are available now, but you can write own renderer that
implements MenuRendererInterface. Now out-of-the-box available this renderers:

- BootstrapMenuRenderer same as HtmlMenuRenderer but bootstrap cpecyfic
- HtmlMenuRenderer renders HTML menu
- ArrayMenuRenderer renders array menu
- JsonMenuRenderer renders JSON menu

MenuRendererInterface:

```php
<?php

declare (strict_types=1);

namespace Core\Interfaces;

interface MenuRendererInterface
{

    public function render(
            array $menu = [],
            ?array $options = null
    ): mixed;

    public function getOptions(): array;
}

```

You can customize the rendering of the menu by providing your own rendering
engine or modifying the default one. Additionally, caching can be enabled to
improve performance when rendering menus.

## Another example of usage

```php
use Core\MenuManager\MenuBuilder;
use Core\MenuManager\Renderers\HtmlMenuRenderer;

// Create MenuBuilder instance
$menuBuilder = new MenuBuilder(new HtmlMenuRenderer());

// Add links to menu
$menuBuilder->addItem('home', 'Home', '/');
$menuBuilder->addItem('about', 'About', '/about');
$menuBuilder->addItem('services', 'Services', '/services', null, ['class' => 'myclass']);
$menuBuilder->addItem('products', 'Products', '/products', null, [], ['nofollow']);
$menuBuilder->addItem('solutions', 'Solutions', '/solutions', null, [], [], 30);

// Add child links to existing menu items
$menuBuilder->addItem('contact', 'Contact', '/contact', 'about');
$menuBuilder->addItem('support', 'Support', '/support', 'services');

// Add some attribute for an existing link in the menu
$menuBuilder->addAttribute('support', 'id', 'myclass');

// Add attribute "class" => 'active' to a link by key
$menuBuilder->makeActive('about');

// Remove a link from the menu by key
$menuBuilder->removeLink('services');

// Render the menu
$html = $menuBuilder->render();

// Get the options for the renderer
$options = $menuBuilder->getRendererOptions();

// Customize the rendering options
$options['menuClass'] = 'my-menu';
$options['menuItemClass'] = 'my-menu-item';

// Render the menu with the customized options
$html = $menuBuilder->render($options);

```
