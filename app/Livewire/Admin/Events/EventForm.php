<?php

namespace App\Livewire\Admin\Events;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class EventForm extends Component
{
    use WithFileUploads;

    public Group $group;
    public ?Event $event = null;

    public ?string $title = '';
    public ?string $description = null;
    public ?string $place = '';
    public ?string $eventDate = '';
    public ?string $eventTime = '';
    public ?string $timezone = '';
    public ?int $maxSpots = null;
    public ?string $status = EventStatus::Draft->value;

    public array $existingImages = [];
    public array $imageAltTexts = [];
    public array $imageOrder = [];
    public array $newImages = [];

    protected $listeners = ['reorderImages' => 'updateImageOrder'];

    public function mount(Group $group, $event = null): void
    {
        abort_unless($group->canManage(Auth::user()), 403);

        $this->group = $group;
        $this->event = $event instanceof Event
            ? $event
            : (is_numeric($event) ? Event::findOrFail($event) : null);

        $event = $this->event;

        $this->timezone = config('app.timezone');

        if ($event) {
            $this->title = $event->title;
            $this->description = $event->description;
            $localDate = $event->local_event_date;
            $this->eventDate = $localDate?->format('Y-m-d') ?? '';
            $this->eventTime = $localDate?->format('H:i') ?? '';
            $this->timezone = $event->timezone;
            $this->place = $event->place;
            $this->maxSpots = $event->max_spots;
            $this->status = $event->status?->value ?? EventStatus::Draft->value;
            $this->existingImages = $event->images->map(fn ($image) => [
                'id' => $image->id,
                'path' => $image->image_path,
                'url' => Storage::disk('public')->url($image->image_path),
                'alt_text' => $image->alt_text,
            ])->toArray();
            $this->imageAltTexts = collect($this->existingImages)->mapWithKeys(fn ($image) => [$image['id'] => $image['alt_text']])->toArray();
        } else {
            $this->eventDate = now()->format('Y-m-d');
            $this->eventTime = now()->format('H:i');
        }
    }

    public function updateImageOrder(array $order): void
    {
        $this->imageOrder = $order;
    }

    public function removeImage(int $imageId): void
    {
        if (! $this->event) {
            $this->existingImages = array_values(array_filter($this->existingImages, fn ($image) => $image['id'] !== $imageId));

            return;
        }

        $image = $this->event->images()->findOrFail($imageId);
        $image->delete();

        $this->event->refresh();
        $this->existingImages = $this->event->images->map(fn ($image) => [
            'id' => $image->id,
            'path' => $image->image_path,
            'url' => Storage::disk('public')->url($image->image_path),
            'alt_text' => $image->alt_text,
        ])->toArray();
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'place' => ['required', 'string', 'max:255'],
            'eventDate' => ['required', 'date'],
            'eventTime' => ['required'],
            'timezone' => ['required', 'timezone'],
            'maxSpots' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'status' => ['required', Rule::enum(EventStatus::class)],
            'newImages.*' => ['image', 'max:4096'],
        ]);

        $eventDateTime = Carbon::createFromFormat('Y-m-d H:i', $this->eventDate.' '.$this->eventTime, $this->timezone);

        $status = EventStatus::from($this->status);

        $maxSpots = $this->maxSpots === '' ? null : $this->maxSpots;

        $payload = [
            'group_id' => $this->group->id,
            'title' => $this->title,
            'description' => $this->description,
            'place' => $this->place,
            'event_datetime' => $eventDateTime,
            'timezone' => $this->timezone,
            'max_spots' => $maxSpots,
            'status' => $status,
            'reserved_spots' => $this->event?->reserved_spots ?? 0,
        ];

        if ($this->event && $this->event->exists) {
            $this->event->update($payload);
            $event = $this->event->refresh();
        } else {
            $event = Event::create($payload);
            $this->event = $event;
        }

        $nextOrder = $event->images()->count();

        foreach ($this->newImages as $upload) {
            $path = $upload->store('event-images', 'public');

            $event->images()->create([
                'image_path' => $path,
                'alt_text' => null,
                'order_column' => $nextOrder++,
            ]);
        }

        $this->newImages = [];

        foreach ($this->imageAltTexts as $id => $alt) {
            $event->images()->whereKey($id)->update(['alt_text' => $alt]);
        }

        if (! empty($this->imageOrder)) {
            foreach ($this->imageOrder as $position => $id) {
                $event->images()->whereKey($id)->update(['order_column' => $position]);
            }
        }

        // Recalculate RSVPs if capacity changed
        if (\Illuminate\Support\Facades\Schema::hasTable('event_rsvps')) {
            $event->recalcRsvps();
        }

        session()->flash('success', __('Event saved.'));
        $this->redirectRoute('admin.events.index', $this->group);
    }

    public function render()
    {
        return view('livewire.admin.events.event-form', [
            'statuses' => EventStatus::cases(),
            'timezones' => collect(timezone_identifiers_list())->values(),
        ]);
    }
}
