<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'site_name_np' => 'नेपाल मगर संघ',
            'site_name_en' => 'Nepal Magar Association',
            'tagline_np' => 'एकता • पहिचान • भाषा • संस्कृति • अधिकार • विकास',
            'tagline_en' => 'UNITY • IDENTITY • LANGUAGE • CULTURE • RIGHTS • DEVELOPMENT',
            'logo_url' => 'https://placehold.co/200x200/D4AF37/8B0000?text=NMS&font=noto-sans',
            'flag_url' => 'https://flagcdn.com/w320/np.png',
            'phone' => '01-4523456',
            'email' => 'info@nepalmagar.org.np',
            'address_np' => 'केन्द्रीय कार्यालय, नयाँ बानेश्वर, काठमाडौं, नेपाल',
            'address_en' => 'Central Office, Naya Baneshwor, Kathmandu, Nepal',
            'map_embed_url' => 'https://www.google.com/maps?q=Kathmandu,Nepal&output=embed',
            'facebook_url' => 'https://facebook.com/nepalmagarassociation',
            'instagram_url' => 'https://instagram.com/nepalmagarassociation',
            'youtube_url' => 'https://youtube.com/@nepalmagarassociation',
            'twitter_url' => 'https://twitter.com/nepalmagarassoc',
            'tiktok_url' => 'https://tiktok.com/@nepalmagarassociation',
            'footer_credit' => 'Designed & Developed by Chiran Studio',
            'history_content' => '<p>' . fake()->paragraphs(4, true) . '</p>',
            'mission_vision_content' => '<p>' . fake()->paragraphs(3, true) . '</p>',
            'constitution_content' => '<p>' . fake()->paragraphs(6, true) . '</p>',
            'about_short_np' => 'नेपाल मगर संघ मगर समुदायको भाषा, संस्कृति र पहिचानको संरक्षण एवं विकासका लागि क्रियाशील राष्ट्रिय छाता संगठन हो।',
            'about_short_en' => fake()->paragraph(3),
            'president_message_np' => 'मगर समुदायको भाषा, संस्कृति र पहिचानको संरक्षण गर्दै समुदायलाई एकताबद्ध पार्नु नै हाम्रो मूल उद्देश्य हो। ' . fake()->realText(200),
            'president_name_np' => 'कुल बहादुर थापा मगर, अध्यक्ष, नेपाल मगर संघ केन्द्रीय समिति',
            'president_photo_url' => 'https://i.pravatar.cc/400?img=52',
            'stat_members' => 75000,
            'stat_districts' => 77,
            'stat_countries' => 30,
            'stat_sister_orgs' => 6,
        ];
    }
}
