<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Generic dashboard CRUD driven by config/admin.php.
 * The route name (dashboard.{resource}.{action}) tells us which config entry we are serving.
 */
class ResourceController extends Controller
{
    protected const FILE_TYPES = ['image', 'file'];

    // ------------------------------------------------------------------ config helpers

    protected function key(): string
    {
        return explode('.', (string) request()->route()->getName())[1] ?? '';
    }

    protected function cfg(): array
    {
        $cfg = config('admin.resources.'.$this->key());

        abort_unless($cfg, 404);

        return $cfg;
    }

    /** @return class-string<Model> */
    protected function modelClass(): string
    {
        return $this->cfg()['model'];
    }

    protected function fields(): array
    {
        return $this->cfg()['fields'];
    }

    protected function routeName(string $action): string
    {
        return 'dashboard.'.$this->key().'.'.$action;
    }

    protected function query(): Builder
    {
        [$column, $direction] = $this->cfg()['order'] ?? ['id', 'desc'];

        return $this->modelClass()::query()->orderBy($column, $direction);
    }

    protected function find(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    // ------------------------------------------------------------------ actions

    public function index(): View
    {
        return view('dashboard.resource.index', [
            'key' => $this->key(),
            'cfg' => $this->cfg(),
            'rows' => $this->query()->get(),
        ]);
    }

    public function create(): View
    {
        abort_if($this->cfg()['readonly'] ?? false, 404);

        return $this->formView(null);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($this->cfg()['readonly'] ?? false, 404);

        $request->validate($this->rules(null), [], $this->attributeLabels());

        $this->persist($request, null);

        return $this->done('created');
    }

    public function show(int|string $id): View
    {
        return view('dashboard.resource.show', [
            'key' => $this->key(),
            'cfg' => $this->cfg(),
            'model' => $this->find($id),
        ]);
    }

    public function edit(int|string $id): View
    {
        abort_if($this->cfg()['readonly'] ?? false, 404);

        return $this->formView($this->find($id));
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        abort_if($this->cfg()['readonly'] ?? false, 404);

        $model = $this->find($id);

        $request->validate($this->rules($model), [], $this->attributeLabels());

        $this->persist($request, $model);

        return $this->done('updated');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $model = $this->find($id);

        if ($message = $this->deleteBlockedReason($model)) {
            return redirect()->route($this->routeName('index'))->with('dashboard-error', $message);
        }

        foreach ($this->fields() as $field) {
            if (in_array($field['type'], self::FILE_TYPES, true)) {
                $this->deleteStoredFile($model->getAttribute($field['name']));
            }
        }

        $model->delete();

        return $this->done('deleted');
    }

    // ------------------------------------------------------------------ hooks (override in subclasses)

    protected function deleteBlockedReason(Model $model): ?string
    {
        return null;
    }

    protected function persist(Request $request, ?Model $model): Model
    {
        $data = $this->payload($request, $model);

        if ($model) {
            $model->update($data);

            return $model;
        }

        return $this->modelClass()::create($data);
    }

    protected function rules(?Model $model): array
    {
        $rules = [];

        foreach ($this->fields() as $field) {
            $name = $field['name'];
            $requiredNow = ($field['required_on_create'] ?? false) && ! $model;

            $rules[$name] = match ($field['type']) {
                'image' => array_values(array_filter([
                    $requiredNow ? 'required' : 'nullable',
                    'image',
                    'max:'.($field['max'] ?? 4096),
                ])),
                'file' => array_values(array_filter([
                    $requiredNow ? 'required' : 'nullable',
                    'file',
                    isset($field['mimes']) ? 'mimes:'.$field['mimes'] : null,
                    'max:'.($field['max'] ?? 10240),
                ])),
                'checkbox' => ['nullable', 'boolean'],
                default => explode('|', $field['rules'] ?? 'nullable'),
            };

            if ($name === 'slug') {
                $rules[$name][] = Rule::unique((new ($this->modelClass()))->getTable(), 'slug')->ignore($model?->getKey());
            }
        }

        return $rules;
    }

    protected function attributeLabels(): array
    {
        return collect($this->fields())->mapWithKeys(fn ($f) => [$f['name'] => Str::lower($f['label'])])->all();
    }

    /** Values shown in the create / edit form, keyed by field name. */
    protected function formValues(?Model $model): array
    {
        $values = [];

        foreach ($this->fields() as $field) {
            $name = $field['name'];
            $type = $field['type'];

            if ($model) {
                $raw = $model->getAttribute($name);
            } else {
                $raw = match ($field['default'] ?? null) {
                    'now' => now(),
                    'today' => today(),
                    default => $field['default'] ?? null,
                };
            }

            $values[$name] = match (true) {
                $raw instanceof Carbon && $type === 'datetime' => $raw->format('Y-m-d\TH:i'),
                $raw instanceof Carbon && $type === 'date' => $raw->format('Y-m-d'),
                $raw instanceof Carbon && $type === 'time' => $raw->format('H:i'),
                $type === 'time' && is_string($raw) => substr($raw, 0, 5),
                $type === 'checkbox' => (bool) $raw,
                default => $raw,
            };
        }

        return $values;
    }

    // ------------------------------------------------------------------ internals

    protected function formView(?Model $model): View
    {
        $suggestions = [];

        foreach ($this->fields() as $field) {
            if ($field['suggest'] ?? false) {
                $suggestions[$field['name']] = $this->modelClass()::query()
                    ->whereNotNull($field['name'])->distinct()->orderBy($field['name'])->pluck($field['name']);
            }
        }

        return view('dashboard.resource.form', [
            'key' => $this->key(),
            'cfg' => $this->cfg(),
            'model' => $model,
            'values' => $this->formValues($model),
            'suggestions' => $suggestions,
        ]);
    }

    protected function done(string $verb): RedirectResponse
    {
        return redirect()
            ->route($this->routeName('index'))
            ->with('dashboard-status', $this->cfg()['singular'].' '.$verb.' successfully.');
    }

    /** Turn the validated request into model attributes (handles uploads, checkboxes, slugs...). */
    protected function payload(Request $request, ?Model $model): array
    {
        $data = [];

        foreach ($this->fields() as $field) {
            $name = $field['name'];
            $type = $field['type'];

            switch ($type) {
                case 'image':
                case 'file':
                    if ($request->hasFile($name)) {
                        $this->deleteStoredFile($model?->getAttribute($name));
                        $data[$name] = $this->storeUpload($request->file($name), $field['folder'] ?? $this->key());
                    } elseif ($model && $request->boolean('remove_'.$name) && ! ($field['required_on_create'] ?? false)) {
                        $this->deleteStoredFile($model->getAttribute($name));
                        $data[$name] = null;
                    }
                    break;

                case 'checkbox':
                    $data[$name] = $request->boolean($name);
                    break;

                default:
                    $value = $request->input($name);

                    if (($field['transform'] ?? null) === 'youtube_embed' && $value) {
                        $value = $this->youtubeEmbedUrl($value);
                    }

                    $default = $field['default'] ?? null;

                    if ($value === null && $default !== null && ! in_array($default, ['now', 'today'], true)) {
                        $value = $default;
                    }

                    if ($value === null && $default === 'now' && $type === 'datetime') {
                        $value = $model?->getAttribute($name) ?? now();
                    }

                    $data[$name] = $value;
            }
        }

        $data = $this->withSlug($data, $model);

        return $this->withYoutubeThumbnail($data, $model);
    }

    protected function withSlug(array $data, ?Model $model): array
    {
        if (! array_key_exists('slug', $data) || filled($data['slug'])) {
            return $data;
        }

        if ($model && filled($model->slug)) {
            $data['slug'] = $model->slug;

            return $data;
        }

        $base = Str::slug($data['title'] ?? '') ?: 'item-'.Str::lower(Str::random(6));

        $slug = $base;
        $i = 2;
        while ($this->modelClass()::query()->where('slug', $slug)->when($model, fn ($q) => $q->whereKeyNot($model->getKey()))->exists()) {
            $slug = $base.'-'.$i++;
        }

        $data['slug'] = $slug;

        return $data;
    }

    /** Video gallery: fall back to the YouTube thumbnail when none is uploaded. */
    protected function withYoutubeThumbnail(array $data, ?Model $model): array
    {
        if (! array_key_exists('youtube_embed_url', $data) || ! collect($this->fields())->contains('name', 'thumbnail_url')) {
            return $data;
        }

        $current = array_key_exists('thumbnail_url', $data) ? $data['thumbnail_url'] : $model?->thumbnail_url;
        $videoChanged = $model && $model->youtube_embed_url !== $data['youtube_embed_url'];
        $isYoutubeThumb = is_string($current) && str_contains($current, 'img.youtube.com');

        if ((blank($current) || ($videoChanged && $isYoutubeThumb)) && preg_match('~/embed/([\w-]{6,})~', $data['youtube_embed_url'], $m)) {
            $data['thumbnail_url'] = 'https://img.youtube.com/vi/'.$m[1].'/hqdefault.jpg';
        }

        return $data;
    }

    protected function youtubeEmbedUrl(string $url): string
    {
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([\w-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        return $url;
    }

    protected function storeUpload(UploadedFile $file, string $folder): string
    {
        return '/storage/'.$file->store($folder, 'public');
    }

    protected function deleteStoredFile(?string $url): void
    {
        if ($url && str_starts_with($url, '/storage/')) {
            Storage::disk('public')->delete(Str::after($url, '/storage/'));
        }
    }
}
