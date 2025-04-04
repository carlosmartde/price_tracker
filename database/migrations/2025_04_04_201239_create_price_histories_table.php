<?php
// database/migrations/xxxx_xx_xx_create_price_histories_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriceHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('retailer_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->string('currency')->default('MXN');
            $table->string('product_url')->nullable();
            $table->boolean('in_stock')->default(true);
            $table->timestamp('price_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('price_histories');
    }
}
