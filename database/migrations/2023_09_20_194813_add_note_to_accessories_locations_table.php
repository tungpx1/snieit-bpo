<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoteToAccessoriesLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accessories_locations', function (Blueprint $table) {
            //
            $table->text("notes")->nullable()->default(null);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accessories_locations', function (Blueprint $table) {
            //
            if (Schema::hasColumn('accessories_locations', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
}
