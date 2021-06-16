<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandlordTenantsTable extends Migration
{
    public function up()
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('app', 5);
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('database')->unique();
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }
}
