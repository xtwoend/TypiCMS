<?php

// Blocks breadcrumbs

Breadcrumbs::register('admin.blocks.index', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(Str::title(trans('blocks::global.name')), route('admin.blocks.index'));
});

Breadcrumbs::register('admin.blocks.edit', function ($breadcrumbs, $block) {
    $breadcrumbs->parent('admin.blocks.index');
    $breadcrumbs->push($block->name, route('admin.blocks.edit', $block->id));
});

Breadcrumbs::register('admin.blocks.create', function ($breadcrumbs) {
    $breadcrumbs->parent('admin.blocks.index');
    $breadcrumbs->push(trans('blocks::global.New'), route('admin.blocks.create'));
});
