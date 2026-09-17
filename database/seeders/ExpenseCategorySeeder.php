<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Material categories
            ['name' => 'Bricks',             'slug' => 'bricks',       'icon' => 'ti-wall',              'color' => '#e85d04', 'type' => 'material', 'sort_order' => 1],
            ['name' => 'Cement',             'slug' => 'cement',       'icon' => 'ti-droplet-filled',    'color' => '#6b7280', 'type' => 'material', 'sort_order' => 2],
            ['name' => 'Soil / Mitti',       'slug' => 'soil',         'icon' => 'ti-layers-linked',     'color' => '#92400e', 'type' => 'material', 'sort_order' => 3],
            ['name' => 'Sand / Rait',        'slug' => 'sand',         'icon' => 'ti-beach',             'color' => '#d97706', 'type' => 'material', 'sort_order' => 4],
            ['name' => 'Gravel / Bajri',     'slug' => 'gravel',       'icon' => 'ti-circle-dotted',     'color' => '#78716c', 'type' => 'material', 'sort_order' => 5],
            ['name' => 'Steel / Sarya',      'slug' => 'steel',        'icon' => 'ti-align-justified',   'color' => '#4b5563', 'type' => 'material', 'sort_order' => 6],
            ['name' => 'Sanitary',           'slug' => 'sanitary',     'icon' => 'ti-bath',              'color' => '#0ea5e9', 'type' => 'material', 'sort_order' => 7],
            ['name' => 'Wood / Timber',      'slug' => 'wood',         'icon' => 'ti-tree',              'color' => '#854d0e', 'type' => 'material', 'sort_order' => 8],
            ['name' => 'Paint',              'slug' => 'paint',        'icon' => 'ti-brush',             'color' => '#7c3aed', 'type' => 'material', 'sort_order' => 9],
            ['name' => 'Glass & Doors',      'slug' => 'glass-doors',  'icon' => 'ti-door',              'color' => '#06b6d4', 'type' => 'material', 'sort_order' => 10],
            ['name' => 'Tiles & Flooring',   'slug' => 'tiles',        'icon' => 'ti-grid-pattern',      'color' => '#be185d', 'type' => 'material', 'sort_order' => 11],
            ['name' => 'Electrical',         'slug' => 'electrical',   'icon' => 'ti-bolt',              'color' => '#ca8a04', 'type' => 'material', 'sort_order' => 12],
            ['name' => 'Plumbing',           'slug' => 'plumbing',     'icon' => 'ti-pipe',              'color' => '#1d4ed8', 'type' => 'material', 'sort_order' => 13],

            // Labor categories
            ['name' => 'Labor',              'slug' => 'labor',        'icon' => 'ti-hammer',            'color' => '#16a34a', 'type' => 'labor',    'sort_order' => 20],
            ['name' => 'Skilled Labor',      'slug' => 'skilled-labor', 'icon' => 'ti-tool',              'color' => '#15803d', 'type' => 'labor',    'sort_order' => 21],
            ['name' => 'Contractor Fee',     'slug' => 'contractor',   'icon' => 'ti-user-cog',          'color' => '#166534', 'type' => 'labor',    'sort_order' => 22],

            // Other categories
            ['name' => 'Transportation',     'slug' => 'transport',    'icon' => 'ti-truck',             'color' => '#0369a1', 'type' => 'other',    'sort_order' => 30],
            ['name' => 'Machinery / Rental', 'slug' => 'machinery',    'icon' => 'ti-crane',             'color' => '#dc2626', 'type' => 'other',    'sort_order' => 31],
            ['name' => 'Electricity',        'slug' => 'electricity',  'icon' => 'ti-plug-connected',    'color' => '#ca8a04', 'type' => 'other',    'sort_order' => 32],
            ['name' => 'Water',              'slug' => 'water',        'icon' => 'ti-droplet',           'color' => '#2563eb', 'type' => 'other',    'sort_order' => 33],
            ['name' => 'Other',              'slug' => 'other',        'icon' => 'ti-dots',              'color' => '#6b7280', 'type' => 'other',    'sort_order' => 99],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(
                ['slug' => $category['slug']],
                array_merge($category, ['is_active' => true])
            );
        }
    }
}
