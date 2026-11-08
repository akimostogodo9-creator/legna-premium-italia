<?php

use App\Enums\Availability;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Étape 4 §4.3
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // R-CAT-04 : on ne supprime pas une catégorie qui contient des produits
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->string('short_description', 300)->nullable();
            $table->longText('description')->nullable();
            // R-PRIX-01 : prix facultatif, en euros IVA inclusa, jamais FLOAT
            $table->decimal('price', 10, 2)->nullable();
            $table->foreignId('sale_unit_id')->nullable()->constrained()->restrictOnDelete();
            // R-CAR-01 : caractéristiques en colonnes fixes
            $table->string('wood_type', 100)->nullable();
            $table->string('dimensions', 150)->nullable();
            $table->string('packaging', 150)->nullable();
            $table->enum('availability', Availability::values())->default(Availability::Available->value);
            $table->text('extra_info')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            // Inactif à la création : activation contrôlée par R-PROD-02 (sprint 4)
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('meta_title', 191)->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'category_id', 'sort_order']);
        });

        if (DB::getDriverName() === 'mysql') {
            // R-PRIX-05 : un prix a toujours une unité ; un prix est strictement positif
            DB::statement('ALTER TABLE products ADD CONSTRAINT products_price_positive CHECK (price IS NULL OR price > 0)');
            DB::statement('ALTER TABLE products ADD CONSTRAINT products_price_needs_unit CHECK (price IS NULL OR sale_unit_id IS NOT NULL)');
            // F-CAT-04 : recherche dans le catalogue
            DB::statement('ALTER TABLE products ADD FULLTEXT products_search_fulltext (name, short_description, wood_type)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
