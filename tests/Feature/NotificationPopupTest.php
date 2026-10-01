<?php

namespace Tests\Feature;

use App\Models\NotificationItem;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationPopupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('public');
    }

    private function admin(): User
    {
        return tap(User::factory()->create(), fn ($u) => $u->assignRole('admin'));
    }

    public function test_admin_adds_a_notification_with_an_image_and_popup_options(): void
    {
        $this->actingAs($this->admin())->post(route('dashboard.notifications.store'), [
            'title' => 'General assembly notice',
            'body' => '<p>The assembly is on <strong>Saturday</strong>.</p>',
            'image_url' => UploadedFile::fake()->image('notice.jpg', 800, 1000),
            'show_popup' => '1',
            'popup_pages' => 'all',
            'popup_frequency' => 'always',
            'popup_on_exit' => '1',
            'popup_repeat_minutes' => '10',
        ])->assertRedirect(route('dashboard.notifications.index'))->assertSessionHasNoErrors();

        $notice = NotificationItem::sole();
        $this->assertStringStartsWith('/storage/notifications/', $notice->image_url);
        Storage::disk('public')->assertExists(Str::after($notice->image_url, '/storage/'));
        $this->assertTrue($notice->show_popup);
        $this->assertTrue($notice->popup_on_exit);
        $this->assertSame(['all', 'always', 10], [$notice->popup_pages, $notice->popup_frequency, $notice->popup_repeat_minutes]);

        // The image is on the notification page; the popup is on every public page.
        $this->get(route('media.notifications.show', $notice))->assertOk()->assertSee($notice->image_url, false);
        foreach ([route('home'), route('media.notifications.index')] as $page) {
            $this->get($page)->assertOk()->assertSee('notice-popup', false)
                ->assertSee('"always":true,"exit":true,"repeat":10', false)
                ->assertSee('The assembly is on Saturday.');
        }

        $this->actingAs($this->admin())->post(route('dashboard.notifications.store'), ['title' => 'Bad', 'popup_pages' => 'moon', 'popup_repeat_minutes' => '0'])
            ->assertSessionHasErrors(['popup_pages', 'popup_repeat_minutes']);
    }

    public function test_popup_defaults_to_the_home_page_and_the_first_visit_only(): void
    {
        $this->actingAs($this->admin())->post(route('dashboard.notifications.store'), ['title' => 'Home only', 'show_popup' => '1'])
            ->assertSessionHasNoErrors();

        $notice = NotificationItem::sole();
        $this->assertSame(['home', 'once', null], [$notice->popup_pages, $notice->popup_frequency, $notice->popup_repeat_minutes]);
        $this->assertFalse($notice->popup_on_exit);

        $this->get(route('home'))->assertSee('notice-popup', false)->assertSee('"always":false,"exit":false,"repeat":0', false);
        $this->get(route('media.notifications.index'))->assertDontSee('notice-popup', false);
    }

    public function test_only_published_popup_notifications_are_shown(): void
    {
        NotificationItem::factory()->create(['title' => 'Plain notice']);
        NotificationItem::factory()->create(['title' => 'Future notice', 'show_popup' => true, 'published_at' => now()->addDay()]);

        $this->get(route('home'))->assertOk()->assertDontSee('notice-popup', false);

        NotificationItem::factory()->count(5)->create(['show_popup' => true]);
        $this->assertCount(NotificationItem::MAX_POPUPS, NotificationItem::popupPayload(true));

        // The dashboard never shows the popup.
        $this->actingAs($this->admin())->get(route('dashboard.index'))->assertOk()->assertDontSee('notice-popup', false);
    }
}
