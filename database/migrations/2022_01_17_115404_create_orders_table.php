<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->unsignedBigInteger('order_by');
            $table->foreign('order_by')->references('id')->on('users');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->foreign('staff_id')->references('id')->on('users');
            $table->date('order_date');
            $table->double('expense',8,2)->default(0);
            $table->double('discount',8,2)->default(0);
            $table->double('amount',8,2);
            $table->string('house_no');
            $table->text('address');
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->comment('1-placed | 2 - accept by admin | 3-reject by admin | 4-accept by customer | 5-reject by customer | 6-inprogress | 7-paid | 8-completed');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
