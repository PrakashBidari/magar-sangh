<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            MembershipTypeSeeder::class,
            HeroSlideSeeder::class,
            CommitteeMemberSeeder::class,
            NewsSeeder::class,
            ArticleSeeder::class,
            NotificationItemSeeder::class,
            PublicationSeeder::class,
            EventSeeder::class,
            GalleryPhotoSeeder::class,
            GalleryVideoSeeder::class,
            SisterOrganizationSeeder::class,
            DonationSeeder::class,
        ]);
    }
}
