# Admin bar

**since v0.12**

Adds a fixed Admin bar to the website (frontend). By default it shows:
* Home icon (links to the website's frontpage)
* Dashboard link (links to the admin dashboard)


Hide using:

**add_filter('contentpress/admin-bar/show', '__return_false');**


Structure:

* Single menu item
```php
$entries[ 'contact' ] = [
    'title' => __( 'Contact us!' ),
    'text' => __( 'Contact' ),
    'url' => route( 'app.contact' ),
];
```

* Dropdown menu item (Keep in mind that only one submenu level is supported)
```php
$entries[ 'blog' ] = [
    'title' => __( 'View our blog' ),
    'text' => __( 'Blog' ),
    'url' => '#',
    'submenu' => [
        'categories' => [
            'title' => __( 'Categories' ),
            'text' => __( 'Categories' ),
            'url' => route( 'route.to.categories' ),
        ],
        'tags' => [
            'title' => __( 'Tags' ),
            'text' => __( 'Tags' ),
            'url' => route( 'route.to.tags' ),
        ],
        'articles' => [
            'title' => __( 'Articles' ),
            'text' => __( 'Articles' ),
            'url' => route( 'route.to.articles' ),
        ],
    ]
];
```
