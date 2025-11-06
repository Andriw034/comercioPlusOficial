<?php

return [
    // ===================== BRAND =====================
    'title' => 'ComercioPlus — Panel',
    'bottom_title' => 'ComercioPlus',
    'logo' => '<b>Comercio</b>Plus',
    'logo_img_alt' => 'Logo ComercioPlus',

    'auth_logo' => [
        'enabled' => true,
        'img' => [
            'path'   => 'assets/comercioplus-logo.png',
            'alt'    => 'ComercioPlus',
            'class'  => 'rounded',
            'width'  => 64,
            'height' => 64,
        ],
    ],

    // Si vas a sobreescribir vistas de Tablar:
    'views_path' => null,

    // ===================== LAYOUT =====================
    'layout' => 'vertical',            // sidebar izquierda
    'layout_light_sidebar' => true,
    'layout_light_topbar' => true,
    'sticky_top_nav_bar' => true,
    'classes_body' => '',

    // ===================== URLS / RUTAS =====================
    'use_route_url' => true,           // 'url' del menú = NOMBRE DE RUTA
    'dashboard_url' => 'admin.dashboard', // inicio del panel

    // ===================== ALERTAS (opcional) =====================
    'display_alert' => false,

    // ===================== MENÚ LATERAL =====================
    'menu' => [
  ['text'=>'Inicio público','icon'=>'ti ti-home','url'=>'welcome','active'=>['/','welcome']],
  ['text'=>'Dashboard','icon'=>'ti ti-layout-dashboard','url'=>'admin.dashboard','active'=>['admin','admin/dashboard']],
  ['text'=>'Perfil','icon'=>'ti ti-user','url'=>'admin.profile.security','active'=>['admin/profile*']],
  ['text'=>'Configuración','icon'=>'ti ti-settings','url'=>'admin.settings.index','active'=>['admin/settings*','admin.settings.*']],
  ['text'=>'Productos','icon'=>'ti ti-package','url'=>'admin.products.index','active'=>['admin/products*','admin.productos.*']],
  ['text'=>'Categorías','icon'=>'ti ti-category','url'=>'admin.categories.index','active'=>['admin/categories*','admin.categories.*']],
  ['text'=>'Estadísticas','icon'=>'ti ti-chart-bar','url'=>'admin.stats.index','active'=>['admin/stats*','admin.stats.*']],
  [
    'text'=>'Tienda','icon'=>'ti ti-building-store','url'=>'#','active'=>['admin/store*','admin.store.*'],
    'submenu'=>[
      ['text'=>'Apariencia','url'=>'admin.store.appearance','icon'=>'ti ti-brush'],
      ['text'=>'Pagos','url'=>'admin.store.payments','icon'=>'ti ti-credit-card'],
      ['text'=>'Envíos','url'=>'admin.store.shipping','icon'=>'ti ti-truck'],
      ['text'=>'Dominio','url'=>'admin.store.domain','icon'=>'ti ti-world'],
    ],
  ],



        // Estadísticas
        [
            'text'   => 'Estadísticas',
            'icon'   => 'ti ti-chart-bar',
            'url'    => 'admin.stats.index',
            'active' => ['admin/stats*','admin.stats.*'],
        ],

        // Tienda (apariencia, pagos, envíos, dominio)
        [
            'text'   => 'Tienda',
            'icon'   => 'ti ti-building-store',
            'url'    => '#',
            'active' => ['admin/store*','admin.store.*'],
            'submenu'=> [
                [
                    'text' => 'Apariencia',
                    'url'  => 'admin.store.appearance',
                    'icon' => 'ti ti-brush',
                ],
                [
                    'text' => 'Pagos',
                    'url'  => 'admin.store.payments',
                    'icon' => 'ti ti-credit-card',
                ],
                [
                    'text' => 'Envíos',
                    'url'  => 'admin.store.shipping',
                    'icon' => 'ti ti-truck',
                ],
                [
                    'text' => 'Dominio',
                    'url'  => 'admin.store.domain',
                    'icon' => 'ti ti-world',
                ],
            ],
        ],
    ],

    // ===================== FILTROS DE MENÚ =====================
    'filters' => [
        TakiElias\Tablar\Menu\Filters\GateFilter::class,
        TakiElias\Tablar\Menu\Filters\HrefFilter::class,
        TakiElias\Tablar\Menu\Filters\SearchFilter::class,
        TakiElias\Tablar\Menu\Filters\ActiveFilter::class,
        TakiElias\Tablar\Menu\Filters\ClassesFilter::class,
        TakiElias\Tablar\Menu\Filters\LangFilter::class,
        TakiElias\Tablar\Menu\Filters\DataFilter::class,
    ],

    // ===================== VITE / LIVEWIRE =====================
    'vite' => true,
    'livewire' => false,
];
