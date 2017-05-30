<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlockRelationshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->relationshipsTableName(), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('blocker_id');
            $table->unsignedInteger('blocked_by_id');
            $table->timestamp('blocked_at');

            $table->unique(['blocker_id', 'blocked_by_id']);

            $key = $this->userKeyName();
            $tableName = $this->usersTableName();

            $table->foreign('blocker_id')
                ->references($key)
                ->on($tableName)
                ->onDelete('cascade');

            $table->foreign('blocked_by_id')
                ->references($key)
                ->on($tableName)
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->relationshipsTableName());
    }

    /**
     * Get a block relationships table name.
     *
     * @return string
     */
    protected function relationshipsTableName()
    {
        return config('blockable.table_name', 'block_relationships');
    }

    /**
     * Get a user model key name.
     *
     * @return string
     */
    protected function userKeyName()
    {
        $userModel = config('blockable.user');

        return (new $userModel)->getKeyName();
    }

    /**
     * Get a users table name.
     *
     * @return string
     */
    protected function usersTableName()
    {
        $userModel = config('blockable.user');

        return (new $userModel)->getTable();
    }
}
