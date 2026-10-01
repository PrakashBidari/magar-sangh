<?php

namespace Tests\Feature;

use App\Models\OfficeAssistant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfficeAssistantTest extends TestCase
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

    public function test_admin_manages_office_assistants_from_the_dashboard(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('dashboard.index'))->assertSee(route('dashboard.office-assistants.index'), false);
        $this->actingAs($admin)->get(route('dashboard.office-assistants.create'))->assertOk()->assertSee('up to 10 MB');

        // Name, image and phone are required; the e-mail is optional; the image may be up to 10 MB.
        $this->actingAs($admin)->post(route('dashboard.office-assistants.store'), ['email' => 'not-an-email'])
            ->assertSessionHasErrors(['name', 'photo_url', 'phone', 'email']);
        $this->actingAs($admin)->post(route('dashboard.office-assistants.store'), [
            'name' => 'Too Big', 'phone' => '9800000000', 'photo_url' => UploadedFile::fake()->image('big.jpg')->size(10241),
        ])->assertSessionHasErrors('photo_url');

        $this->actingAs($admin)->post(route('dashboard.office-assistants.store'), [
            'name' => 'मन बहादुर पुन मगर', 'phone' => '9851000000', 'photo_url' => UploadedFile::fake()->image('man.jpg')->size(9000),
        ])->assertRedirect(route('dashboard.office-assistants.index'))->assertSessionHasNoErrors();

        $assistant = OfficeAssistant::sole();
        $this->assertNull($assistant->email);
        Storage::disk('public')->assertExists(Str::after($assistant->photo_url, '/storage/'));

        $this->actingAs($admin)->put(route('dashboard.office-assistants.update', $assistant->id), [
            'name' => 'मन बहादुर पुन मगर', 'phone' => '9851000000', 'email' => 'office@example.com',
        ])->assertSessionHasNoErrors();
        $this->assertSame('office@example.com', $assistant->fresh()->email);
        $this->assertSame($assistant->photo_url, $assistant->fresh()->photo_url);

        $this->actingAs($admin)->delete(route('dashboard.office-assistants.destroy', $assistant->id))->assertRedirect();
        $this->assertSame(0, OfficeAssistant::count());
        Storage::disk('public')->assertMissing(Str::after($assistant->photo_url, '/storage/'));
    }

    public function test_public_page_lists_assistants_and_is_in_the_about_menu(): void
    {
        $this->get(route('home'))->assertOk()->assertSee(route('about.office-assistants'), false)->assertSee('Office Assistant');
        $this->get(route('about.office-assistants'))->assertOk()->assertSee('कार्यालय सहायकको विवरण उपलब्ध छैन।');

        OfficeAssistant::factory()->create(['name' => 'Second Person', 'sort_order' => 2, 'email' => null]);
        OfficeAssistant::factory()->create(['name' => 'First Person', 'sort_order' => 1, 'phone' => '9851-000000', 'email' => 'first@example.com']);

        $this->get(route('about.office-assistants'))->assertOk()
            ->assertSeeInOrder(['First Person', 'Second Person'])
            ->assertSee('tel:9851000000', false)->assertSee('mailto:first@example.com', false);
    }

    public function test_regular_users_cannot_manage_office_assistants(): void
    {
        $user = tap(User::factory()->create(), fn ($u) => $u->assignRole('user'));

        $this->actingAs($user)->get(route('dashboard.office-assistants.index'))->assertForbidden();
    }
}
