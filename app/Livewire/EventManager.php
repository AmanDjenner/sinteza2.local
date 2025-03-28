<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Event;
use App\Models\Institution;
use App\Models\EventCategory;
use App\Models\EventSubcategory;
use Carbon\Carbon;

class EventManager extends Component
{
    use WithPagination;

    public $institutions;
    public $categories;
    public $subcategories;
    public $showModal = false;
    public $editingEventId = null;
    public $data;
    public $id_institution;
    public $id_events_category;
    public $id_subcategory = [];
    public $persons_involved;
    public $events_text;
    public $perPage = 20;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $startDate;
    public $endDate;
    public $search = '';

    protected $rules = [
        'data' => 'nullable|date_format:Y-m-d',
        'id_institution' => 'required|exists:institutions,id',
        'id_events_category' => 'required|exists:events_category,id',
        'id_subcategory' => 'nullable|array',
        'id_subcategory.*' => 'exists:events_subcategory,id',
        'persons_involved' => 'nullable|integer|min:0',
        'events_text' => 'nullable|string',
        'startDate' => 'nullable|date_format:Y-m-d',
        'endDate' => 'nullable|date_format:Y-m-d|after_or_equal:startDate',
    ];

    public function mount()
    {
        $this->institutions = Institution::all();
        $this->categories = EventCategory::all();
        $this->subcategories = collect();
    }

    public function updatingPerPage($value)
    {
        $this->perPage = $value;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function updateSubcategories()
    {
        if ($this->id_events_category) {
            $this->subcategories = EventSubcategory::where('id_events_category', $this->id_events_category)->get();
        } else {
            $this->subcategories = collect();
        }
        $this->id_subcategory = [];
    }

    public function createEvent()
    {
        $this->validate();

        try {
            $event = Event::create([
                'data' => $this->data,
                'id_institution' => $this->id_institution,
                'id_events_category' => $this->id_events_category,
                'persons_involved' => $this->persons_involved,
                'events_text' => $this->events_text,
            ]);

            if (!empty($this->id_subcategory) && is_array($this->id_subcategory)) {
                $event->subcategories()->sync(array_filter($this->id_subcategory));
            }

            $this->resetForm();
            session()->flash('message', 'Eveniment creat cu succes!');
        } catch (\Exception $e) {
            \Log::error('Eroare la crearea evenimentului: ' . $e->getMessage());
            session()->flash('error', 'Eroare la crearea evenimentului: ' . $e->getMessage());
        }
    }

    public function editEvent($id)
    {
        try {
            $event = Event::with('subcategories')->findOrFail($id);
            $this->editingEventId = $id;
            $this->data = $event->data ? $event->data->format('Y-m-d') : null;
            $this->id_institution = $event->id_institution;
            $this->id_events_category = $event->id_events_category;
            $this->updateSubcategories();
            $this->id_subcategory = $event->subcategories->pluck('id')->toArray();
            $this->persons_involved = $event->persons_involved;
            $this->events_text = $event->events_text;
            $this->showModal = true;

            $this->emit('editEvent', ['data' => $event->toArray()]);
        } catch (\Exception $e) {
            \Log::error('Eroare la editarea evenimentului: ' . $e->getMessage());
            session()->flash('error', 'Eroare la editarea evenimentului: ' . $e->getMessage());
        }
    }

    public function updateEvent()
    {
        $this->validate();

        try {
            $event = Event::findOrFail($this->editingEventId);
            $event->update([
                'data' => $this->data,
                'id_institution' => $this->id_institution,
                'id_events_category' => $this->id_events_category,
                'persons_involved' => $this->persons_involved,
                'events_text' => $this->events_text,
            ]);

            if (!empty($this->id_subcategory) && is_array($this->id_subcategory)) {
                $event->subcategories()->sync(array_filter($this->id_subcategory));
            } else {
                $event->subcategories()->detach();
            }

            $this->resetForm();
            session()->flash('message', 'Eveniment actualizat cu succes!');
        } catch (\Exception $e) {
            \Log::error('Eroare la actualizarea evenimentului: ' . $e->getMessage());
            session()->flash('error', 'Eroare la actualizarea evenimentului: ' . $e->getMessage());
        }
    }

    public function deleteEvent($id)
    {
        try {
            Event::findOrFail($id)->delete();
            session()->flash('message', 'Eveniment șters cu succes!');
        } catch (\Exception $e) {
            \Log::error('Eroare la ștergerea evenimentului: ' . $e->getMessage());
            session()->flash('error', 'Eroare la ștergerea evenimentului: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->showModal = false;
        $this->editingEventId = null;
        $this->data = null;
        $this->id_institution = null;
        $this->id_events_category = null;
        $this->id_subcategory = [];
        $this->persons_involved = null;
        $this->events_text = null;
        $this->subcategories = collect();
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = Event::with(['institution', 'category', 'subcategories'])
            ->orderBy($this->sortField, $this->sortDirection);

        if ($this->startDate) {
            $query->where('data', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->where('data', '<=', $this->endDate);
        }
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('events_text', 'like', '%' . $this->search . '%')
                  ->orWhere('data', 'like', '%' . $this->search . '%');
            });
        }

        $events = $query->paginate($this->perPage);

        return view('livewire.event-manager', [
            'events' => $events,
            'institutions' => $this->institutions,
            'categories' => $this->categories,
            'subcategories' => $this->subcategories,
        ]);
    }
}