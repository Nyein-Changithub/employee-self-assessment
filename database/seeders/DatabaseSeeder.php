<?php

namespace Database\Seeders;

use App\Models\AssessmentCycle;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LookupSeeder::class);

        User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin',
            'password' => 'password', // change after first login
            'role' => 'admin',
        ]);

        $cycle = AssessmentCycle::firstOrCreate(['slug' => '2026-q1'], ['title' => '2026 Q1 Assessment']);

        if ($cycle->questions()->doesntExist()) {
            $cycle->questions()->createMany([
                [
                    'question_en' => 'What were your key achievements this quarter?',
                    'question_mm' => 'ဤသုံးလပတ်အတွင်း သင်၏ အဓိကအောင်မြင်မှုများမှာ အဘယ်နည်း။',
                    'guide_en' => 'List concrete results with examples.',
                    'guide_mm' => 'ဥပမာများနှင့်တကွ ရလဒ်များကို ဖော်ပြပါ။',
                    'order_no' => 1,
                ],
                [
                    'question_en' => 'What challenges did you face and how did you handle them?',
                    'question_mm' => 'သင်ကြုံတွေ့ခဲ့ရသော စိန်ခေါ်မှုများနှင့် ၎င်းတို့ကို မည်သို့ကိုင်တွယ်ခဲ့သနည်း။',
                    'order_no' => 2,
                ],
                [
                    'question_en' => 'What support or training do you need next quarter?',
                    'question_mm' => 'နောက်သုံးလပတ်အတွက် သင်လိုအပ်သော ထောက်ပံ့မှု သို့မဟုတ် သင်တန်းမှာ အဘယ်နည်း။',
                    'order_no' => 3,
                ],
            ]);
        }
    }
}
