<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    private const TEXT_FIELDS = [
        'site_name_np', 'site_name_en', 'tagline_np', 'tagline_en', 'phone', 'email', 'address_np', 'address_en',
        'map_embed_url', 'facebook_url', 'instagram_url', 'youtube_url', 'twitter_url', 'tiktok_url', 'footer_credit',
        'about_short_en', 'president_message_np', 'president_name_np',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->actingAs(tap(User::factory()->create(), fn ($u) => $u->assignRole('admin')));
    }

    public function test_every_field_can_be_left_blank(): void
    {
        $component = Livewire::test('dashboard.settings');

        foreach (self::TEXT_FIELDS as $field) {
            $component->set($field, '');
        }
        foreach (['stat_members', 'stat_districts', 'stat_countries', 'stat_sister_orgs'] as $field) {
            $component->set($field, null);
        }

        $component->call('save')->assertHasNoErrors()->assertDispatched('settings-result');

        $settings = Setting::query()->first();
        $this->assertSame('', $settings->site_name_en);
        $this->assertSame(0, $settings->stat_members);
    }

    public function test_bad_values_show_an_error_next_to_the_field_and_in_the_summary(): void
    {
        Livewire::test('dashboard.settings')
            ->set('email', 'not-an-email')
            ->set('facebook_url', 'facebook.com/page')
            ->set('stat_members', -5)
            ->set('tagline_en', str_repeat('x', 300))
            ->call('save')
            ->assertHasErrors(['email', 'facebook_url', 'stat_members', 'tagline_en'])
            ->assertSee('Settings were not saved')
            ->assertSee('Enter a full link starting with http')
            ->assertDispatched('settings-result');
    }

    public function test_about_editors_are_bootstrapped_by_an_intact_script(): void
    {
        $html = $this->get(route('dashboard.settings'))->assertOk()->getContent();

        foreach (['history_content', 'mission_vision_content', 'constitution_content'] as $field) {
            $this->assertStringContainsString('data-field="'.$field.'"', $html);
        }

        // A stray placeholder or missing line here is a JS syntax error that stops every editor from loading.
        $this->assertStringContainsString('const wire = $wire;', $html);
        $this->assertStringContainsString('NepalRichEditor.init(el', $html);
        $this->assertDoesNotMatchRegularExpression('/^\s*\$\d+\s*$/m', $html);
    }

    public function test_a_long_google_maps_embed_link_is_accepted(): void
    {
        $url = 'https://www.google.com/maps/embed?pb='.str_repeat('a1', 300);

        Livewire::test('dashboard.settings')->set('map_embed_url', $url)->call('save')->assertHasNoErrors();

        $this->assertSame($url, Setting::query()->first()->map_embed_url);
    }

    public function test_public_pages_work_with_blank_settings(): void
    {
        Setting::current()->update(collect(self::TEXT_FIELDS)->mapWithKeys(fn ($f) => [$f => ''])->all() + ['logo_url' => null, 'flag_url' => null, 'president_photo_url' => null]);

        $this->get(route('contact'))->assertOk()->assertDontSee('<iframe', false);
        $this->get(route('home'))->assertOk();
        $this->get(route('dashboard.index'))->assertOk();

        auth()->logout();
        $this->get(route('login'))->assertOk();
    }
}
