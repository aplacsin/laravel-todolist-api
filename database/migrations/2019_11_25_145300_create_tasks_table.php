<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->nullable();           
            $table->string('title');
            $table->text('description');
            $table->integer('priority');
            $table->string("status")->default('todo');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();            
        });

        DB::statement('ALTER TABLE tasks ADD FULLTEXT search(title)');

        Schema::table('tasks', function(Blueprint $table){
            $table->foreign('parent_id')->references('id')->on('tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
