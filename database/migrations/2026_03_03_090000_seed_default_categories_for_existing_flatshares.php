<?php

use App\Models\Category;
use App\Models\Flatshare;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Flatshare::query()->select('id')->each(function (Flatshare $flatshare): void {
            Category::ensureDefaultsForFlatshare($flatshare->id);
        });
    }

    public function down(): void
    {
        foreach (Category::defaultDefinitions() as $definition) {
            Category::query()
                ->where('name', $definition['name'])
                ->where('icon', $definition['icon'])
                ->delete();
        }
    }
};
