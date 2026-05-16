<?php
$dataTypeUsers = DB::table('data_types')->where('name', 'users')->first();
$dataTypeRedemptions = DB::table('data_types')->where('name', 'coupon_redemptions')->first();
echo "Users: \n";
print_r($dataTypeUsers);
echo "Redemptions: \n";
print_r($dataTypeRedemptions);

$rowsUsers = DB::table('data_rows')->where('data_type_id', $dataTypeUsers->id)->get();
echo "Users rows edit/add count: " . $rowsUsers->where('edit', 1)->count() . "\n";
