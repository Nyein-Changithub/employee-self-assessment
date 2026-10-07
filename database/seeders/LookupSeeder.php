<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'Human Resources' => 'လူ့စွမ်းအားအရင်းအမြစ်',
            'Finance' => 'ဘဏ္ဍာရေး',
            'IT' => 'သတင်းအချက်အလက်နည်းပညာ',
            'Operations' => 'လုပ်ငန်းလည်ပတ်မှု',
            'Sales & Marketing' => 'ရောင်းချရေးနှင့် စျေးကွက်ရှာဖွေရေး',
            'Administration' => 'အုပ်ချုပ်ရေး',
        ];
        $positions = [
            'Staff' => 'ဝန်ထမ်း',
            'Senior Staff' => 'အကြီးတန်းဝန်ထမ်း',
            'Supervisor' => 'ကြီးကြပ်ရေးမှူး',
            'Manager' => 'မန်နေဂျာ',
            'Director' => 'ဒါရိုက်တာ',
        ];

        foreach ($departments as $en => $mm) {
            Department::firstOrCreate(['name_en' => $en], ['name_mm' => $mm]);
        }
        foreach ($positions as $en => $mm) {
            Position::firstOrCreate(['name_en' => $en], ['name_mm' => $mm]);
        }
    }
}
