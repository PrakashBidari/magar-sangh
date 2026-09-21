<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\GalleryVideo;
use App\Models\News;
use App\Models\Publication;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardResourcesTest extends TestCase
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

    private function member(): User
    {
        return tap(User::factory()->create(), fn ($u) => $u->assignRole('user'));
    }

    public function test_only_two_roles_exist(): void
    {
        $this->assertSame(['admin', 'user'], \Spatie\Permission\Models\Role::orderBy('name')->pluck('name')->all());
    }

    public function test_guests_are_redirected_and_members_are_blocked_from_management(): void
    {
        $this->get(route('dashboard.news.index'))->assertRedirect(route('login'));

        $member = $this->member();
        $this->actingAs($member)->get(route('dashboard.index'))->assertOk();
        $this->actingAs($member)->get(route('dashboard.news.index'))->assertForbidden();
        $this->actingAs($member)->get(route('dashboard.users.index'))->assertForbidden();
    }

    public function test_every_resource_list_and_form_renders_for_admin(): void
    {
        $admin = $this->admin();

        foreach (config('admin.resources') as $key => $cfg) {
            $this->actingAs($admin)->get(route("dashboard.$key.index"))->assertOk()->assertSee('resource-table', false);

            if (! ($cfg['readonly'] ?? false)) {
                $this->actingAs($admin)->get(route("dashboard.$key.create"))->assertOk();
            }
        }

        $this->actingAs($admin)->get(route('dashboard.index'))->assertOk();
        $this->actingAs($admin)->get(route('dashboard.settings'))->assertOk();
    }

    public function test_news_crud_with_image_and_generated_slug(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('dashboard.news.store'), [
            'title' => 'Big Festival Announced',
            'body' => '<p>Hello <strong>world</strong></p>',
            'image_url' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertRedirect(route('dashboard.news.index'));

        $news = News::firstOrFail();
        $this->assertSame('big-festival-announced', $news->slug);
        $this->assertNotNull($news->published_at);
        $this->assertStringStartsWith('/storage/news/', $news->image_url);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $news->image_url));

        $this->actingAs($admin)->get(route('dashboard.news.edit', $news->id))->assertOk()->assertSee('Big Festival Announced');

        $this->actingAs($admin)->put(route('dashboard.news.update', $news->id), [
            'title' => 'Renamed',
            'slug' => 'big-festival-announced',
            'body' => '<p>Updated</p>',
        ])->assertRedirect(route('dashboard.news.index'));

        $news->refresh();
        $this->assertSame('Renamed', $news->title);
        $this->assertNotNull($news->image_url, 'existing image must be kept when no new file is sent');

        $oldPath = str_replace('/storage/', '', $news->image_url);
        $this->actingAs($admin)->delete(route('dashboard.news.destroy', $news->id))->assertRedirect(route('dashboard.news.index'));
        $this->assertDatabaseCount('news', 0);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_news_requires_title_and_description(): void
    {
        $this->actingAs($this->admin())
            ->post(route('dashboard.news.store'), ['title' => '', 'body' => ''])
            ->assertSessionHasErrors(['title', 'body']);
    }

    public function test_nepali_titles_still_get_a_unique_slug(): void
    {
        $admin = $this->admin();

        foreach ([1, 2] as $i) {
            $this->actingAs($admin)->post(route('dashboard.news.store'), ['title' => 'नेपाल मगर संघ', 'body' => '<p>x</p>']);
        }

        $slugs = News::pluck('slug');
        $this->assertCount(2, $slugs->unique());
        $this->assertTrue($slugs->every(fn ($s) => filled($s)));
    }

    public function test_publication_needs_a_file_on_create(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('dashboard.publications.store'), ['title' => 'Annual Report'])
            ->assertSessionHasErrors('file_url');

        $this->actingAs($admin)->post(route('dashboard.publications.store'), [
            'title' => 'Annual Report',
            'file_url' => UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $this->assertStringStartsWith('/storage/publications/', Publication::firstOrFail()->file_url);
    }

    public function test_video_links_are_normalised_and_get_a_thumbnail(): void
    {
        $this->actingAs($this->admin())->post(route('dashboard.gallery-videos.store'), [
            'title' => 'Festival',
            'youtube_embed_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ&t=10s',
        ])->assertRedirect();

        $video = GalleryVideo::firstOrFail();
        $this->assertSame('https://www.youtube.com/embed/aqz-KE-bpKQ', $video->youtube_embed_url);
        $this->assertSame('https://img.youtube.com/vi/aqz-KE-bpKQ/hqdefault.jpg', $video->thumbnail_url);
    }

    public function test_photo_gallery_requires_a_photo_and_checkbox_fields_save(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('dashboard.gallery-photos.store'), ['title' => 'x'])->assertSessionHasErrors('image_url');

        $this->actingAs($admin)->post(route('dashboard.committee.store'), [
            'name' => 'Some Person', 'position_np' => 'अध्यक्ष', 'is_current' => '1', 'is_past_president' => '0',
        ])->assertRedirect();

        $this->assertDatabaseHas('committee_members', ['name' => 'Some Person', 'is_current' => 1, 'is_past_president' => 0, 'sort_order' => 0]);
    }

    public function test_contact_messages_can_be_viewed_and_deleted_but_not_created(): void
    {
        $admin = $this->admin();
        $message = ContactMessage::factory()->create(['subject' => 'Hello there']);

        $this->actingAs($admin)->get(route('dashboard.messages.show', $message->id))->assertOk()->assertSee('Hello there');
        $this->actingAs($admin)->get('/dashboard/manage/messages/create')->assertNotFound();

        $this->actingAs($admin)->delete(route('dashboard.messages.destroy', $message->id))->assertRedirect();
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_users_have_only_admin_or_user_roles(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('dashboard.users.store'), [
            'name' => 'New Person', 'email' => 'new@example.com', 'password' => 'secret-pass', 'role' => 'editor',
        ])->assertSessionHasErrors('role');

        $this->actingAs($admin)->post(route('dashboard.users.store'), [
            'name' => 'New Person', 'email' => 'new@example.com', 'password' => 'secret-pass', 'role' => 'user',
        ])->assertRedirect(route('dashboard.users.index'));

        $created = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertTrue($created->hasRole('user'));

        $this->actingAs($admin)->put(route('dashboard.users.update', $created->id), [
            'name' => 'New Person', 'email' => 'new@example.com', 'role' => 'admin',
        ])->assertRedirect();
        $this->assertTrue($created->fresh()->hasRole('admin'));
        $this->assertFalse($created->fresh()->hasRole('user'));
    }

    public function test_admin_cannot_delete_or_demote_themselves(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->delete(route('dashboard.users.destroy', $admin->id))
            ->assertRedirect(route('dashboard.users.index'))->assertSessionHas('dashboard-error');
        $this->assertModelExists($admin);

        $this->actingAs($admin)->put(route('dashboard.users.update', $admin->id), [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'user',
        ])->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    public function test_editor_image_upload_returns_a_url(): void
    {
        $response = $this->actingAs($this->admin())
            ->postJson(route('dashboard.editor-upload'), ['upload' => UploadedFile::fake()->image('inline.png')])
            ->assertOk();

        $this->assertStringStartsWith('/storage/editor/', $response->json('url'));

        $this->actingAs($this->member())
            ->postJson(route('dashboard.editor-upload'), ['upload' => UploadedFile::fake()->image('inline.png')])
            ->assertForbidden();
    }

    public function test_top_bar_shows_auth_links(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee(route('login'), false)->assertSee(route('register'), false)
            ->assertDontSee(route('dashboard.index'), false);

        $this->actingAs($this->member())->get(route('home'))->assertOk()
            ->assertSee(route('dashboard.index'), false)
            ->assertDontSee(route('login'), false);
    }
    public function test_hero_slider_is_managed_from_the_dashboard_and_shown_on_the_homepage(): void
    {
        $admin = $this->admin();
        \App\Models\Setting::current();

        // No slides yet: the homepage still renders its default banner.
        $this->get(route('home'))->assertOk()->assertSee('Our Culture', false);

        $this->actingAs($admin)->post(route('dashboard.hero-slides.store'), ['title_en' => 'No image'])
            ->assertSessionHasErrors('image_url');

        $this->actingAs($admin)->post(route('dashboard.hero-slides.store'), [
            'image_url' => UploadedFile::fake()->image('one.jpg'),
            'title_en' => 'Visible Slide Heading',
            'title_np' => 'पहिलो स्लाइड',
            'is_active' => '1',
        ])->assertRedirect(route('dashboard.hero-slides.index'));

        $this->actingAs($admin)->post(route('dashboard.hero-slides.store'), [
            'image_url' => UploadedFile::fake()->image('two.jpg'),
            'title_en' => 'Hidden Slide Heading',
            'is_active' => '0',
        ])->assertRedirect();

        $visible = \App\Models\HeroSlide::where('title_en', 'Visible Slide Heading')->firstOrFail();
        $this->assertStringStartsWith('/storage/hero/', $visible->image_url);

        $this->get(route('home'))->assertOk()
            ->assertSee('Visible Slide Heading')->assertSee($visible->image_url, false)
            ->assertDontSee('Hidden Slide Heading');

        $this->actingAs($admin)->delete(route('dashboard.hero-slides.destroy', $visible->id))->assertRedirect();
        $this->assertDatabaseMissing('hero_slides', ['id' => $visible->id]);
    }
}
