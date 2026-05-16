<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;

return new class extends Migration
{
    public function up()
    {
        $dt = DataType::where('name', 'custom_forms')->first();
        if ($dt) {
            $dr = DataRow::where('data_type_id', $dt->id)->where('field', 'landing_page_id')->first();
            if ($dr) {
                $details = json_decode($dr->details);
                if (isset($details->relationship)) {
                    $details->relationship->label = 'name';
                    $dr->details = json_encode($details);
                    $dr->save();
                }
            }
        }
    }

    public function down()
    {
        // No need to revert specifically
    }
};
