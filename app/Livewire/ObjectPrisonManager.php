<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ObjectPrison;
use App\Models\Institution;
use App\Models\ObjectList;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ObjectPrisonManager extends Component
{
    public $objects;
    public $institutions;
    public $objectLists;
    public $showModal = false;
    public $showObjectListModal = false;
    public $editingObjectId = null;
    public $startDate;
    public $endDate;
    public $data;
    public $id_institution;
    public $eveniment_type = 'Depistare';
    public $obj_text;
    public $selectedObjects = [];
    public $tempQuantities = [];
    public $search = '';
    public $sortField = 'data';
    public $sortDirection = 'desc';
    public $selectedInstitution = [];
    public $eventFilters = ['Depistare' => false, 'Contracarare' => true];
    public $selectedObjectIds = [];
    public $selectedObjectsWithQuantities = [];

    protected $listeners = [
        'filtersUpdated' => 'updateFilters',
        'calculateQuantities' => 'handleCalculateQuantities',
        'logToConsole' => 'logToConsole'
    ];

    protected $rules = [
        'data' => 'required|date|before_or_equal:today',
        'id_institution' => 'required|exists:institutions,id',
        'eveniment_type' => 'nullable|in:Depistare,Contracarare',
        'obj_text' => 'nullable|string',
        'selectedObjects.*.object_list_id' => 'nullable|exists:object_list,id',
        'selectedObjects.*.quantity' => 'nullable|integer|min:0',
    ];

    public function mount()
    {
        if (!Schema::hasTable('object_prisons')) {
            Log::error('Tabela object_prisons nu există în baza de date.');
            $this->dispatch('showToast', ['type' => 'danger', 'message' => 'Tabela object_prisons nu există în baza de date.']);
            $this->objects = new Collection();
            return;
        }

        $this->institutions = Institution::orderBy('name')->get();
        $this->objectLists = ObjectList::orderBy('name')->get();
        $this->objects = ObjectPrison::with(['institution', 'objectListItems', 'createdBy', 'updatedBy'])->get();
        $this->data = Carbon::today()->format('Y-m-d');
        $this->startDate = Carbon::today()->subDays(7)->format('Y-m-d');
        $this->endDate = Carbon::today()->format('Y-m-d');
        $this->loadObjects();
        $this->dispatch('filtersUpdated', $this->getFilters())->to('sidebar-object-filter-prison');
    }

    public function handleCalculateQuantities($data)
    {
        $filters = $data['filters'] ?? [];
        $this->selectedInstitution = $filters['selectedInstitution'] ?? [];
        $this->eventFilters = $filters['eventFilters'] ?? ['Depistare' => false, 'Contracarare' => true];
        $this->selectedObjectIds = $filters['selectedObjectIds'] ?? [];
        $this->startDate = $filters['startDate'] ?? $this->startDate;
        $this->endDate = $filters['endDate'] ?? $this->endDate;

        Log::info('Obiecte selectate în handleCalculateQuantities: ' . json_encode($this->selectedObjectIds));
        $this->dispatch('consoleLog', ['message' => 'Obiecte selectate în handleCalculateQuantities: ' . json_encode($this->selectedObjectIds)]);
        if (empty($this->selectedObjectIds)) {
            $this->selectedObjectsWithQuantities = [];
            $this->dispatch('updateSelectedObjectsQuantities', [
                'objects' => [],
                'total' => 0
            ])->to('sidebar-object-filter-prison');
            return;
        }

        $this->selectedObjectsWithQuantities = collect($this->selectedObjectIds)->map(function ($objectId) {
            $object = ObjectList::find($objectId);
            if ($object) {
                return [
                    'id' => $object->id,
                    'name' => $object->name,
                    'quantity' => 0
                ];
            }
            return null;
        })->filter()->values()->toArray();

        $query = DB::table('object_prison_objects')
            ->join('object_prisons', 'object_prison_objects.object_prison_id', '=', 'object_prisons.id')
            ->select('object_list_id', DB::raw('SUM(quantity) as total'));

        if ($this->startDate) {
            $query->whereDate('object_prisons.data', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('object_prisons.data', '<=', $this->endDate);
        }

        if (!empty($this->selectedInstitution)) {
            $query->whereIn('object_prisons.id_institution', $this->selectedInstitution);
        }

        $activeEvents = array_keys(array_filter($this->eventFilters));
        if (!empty($activeEvents)) {
            $query->where(function ($q) use ($activeEvents) {
                $q->whereIn('object_prisons.eveniment', $activeEvents);
                if (in_array('Depistare', $activeEvents)) {
                    $q->orWhereNull('object_prisons.eveniment');
                }
            });
        }

        $quantities = $query->whereIn('object_list_id', $this->selectedObjectIds)
            ->groupBy('object_list_id')
            ->pluck('total', 'object_list_id');

        if ($quantities === null) {
            Log::error('Interogarea pentru cantități a returnat null.');
            $this->dispatch('showToast', ['type' => 'danger', 'message' => 'Eroare la calculul cantităților.']);
            return;
        }

        $updatedObjects = array_map(function ($object) use ($quantities) {
            $object['quantity'] = $quantities[$object['id']] ?? 0;
            return $object;
        }, $this->selectedObjectsWithQuantities);

        $this->selectedObjectsWithQuantities = $updatedObjects;

        Log::info('Cantități calculate în handleCalculateQuantities: ' . json_encode($this->selectedObjectsWithQuantities));
        $this->dispatch('consoleLog', ['message' => 'Cantități calculate: ' . json_encode($this->selectedObjectsWithQuantities)]);

        $this->dispatch('updateSelectedObjectsQuantities', [
            'objects' => $this->selectedObjectsWithQuantities,
            'total' => array_sum(array_column($updatedObjects, 'quantity'))
        ])->to('sidebar-object-filter-prison');

        $this->loadObjects();
        $this->dispatch('filtersUpdated', $this->getFilters())->to('sidebar-object-filter-prison');
    }

    public function updateFilters($filters)
    {
        $this->selectedInstitution = $filters['selectedInstitution'] ?? [];
        $this->eventFilters = $filters['eventFilters'] ?? ['Depistare' => false, 'Contracarare' => true];
        $this->selectedObjectIds = $filters['selectedObjectIds'] ?? [];
        $this->startDate = $filters['startDate'] ?? $this->startDate;
        $this->endDate = $filters['endDate'] ?? $this->endDate;

        $currentObjects = collect($this->selectedObjectsWithQuantities)->keyBy('id');
        $this->selectedObjectsWithQuantities = collect($this->selectedObjectIds)->map(function ($objectId) use ($currentObjects) {
            $object = ObjectList::find($objectId);
            if ($object) {
                $existing = $currentObjects->get($objectId);
                return [
                    'id' => $object->id,
                    'name' => $object->name,
                    'quantity' => $existing['quantity'] ?? 0
                ];
            }
            return null;
        })->filter()->values()->toArray();

        $this->loadObjects();
        $this->dispatch('filtersUpdated', $this->getFilters())->to('sidebar-object-filter-prison');
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['startDate', 'endDate', 'search', 'selectedInstitution', 'eventFilters', 'selectedObjectIds'])) {
            $this->loadObjects();
            $this->dispatch('filtersUpdated', $this->getFilters())->to('sidebar-object-filter-prison');
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->loadObjects();
    }

    public function loadObjects()
    {
        if (!Schema::hasTable('object_prisons')) {
            $this->objects = new Collection();
            return;
        }

        $query = ObjectPrison::with(['institution', 'objectListItems', 'createdBy', 'updatedBy']);

        if ($this->startDate) {
            $query->whereDate('data', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('data', '<=', $this->endDate);
        }

        if (!empty($this->selectedInstitution)) {
            $query->whereIn('id_institution', $this->selectedInstitution);
        }

        $activeEvents = array_keys(array_filter($this->eventFilters));
        if (!empty($activeEvents)) {
            $query->where(function ($q) use ($activeEvents) {
                $q->whereIn('object_prisons.eveniment', $activeEvents);
                if (in_array('Depistare', $activeEvents)) {
                    $q->orWhereNull('object_prisons.eveniment');
                }
            });
        }

        if (!empty($this->selectedObjectIds)) {
            $query->whereHas('objectListItems', function ($q) {
                $q->whereIn('object_list_id', $this->selectedObjectIds);
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('data', 'like', '%' . $this->search . '%')
                  ->orWhereHas('institution', function ($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhere('eveniment', 'like', '%' . $this->search . '%')
                  ->orWhere('obj_text', 'like', '%' . $this->search . '%')
                  ->orWhereHas('objectListItems', function ($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->sortField === 'institution') {
            $query->join('institutions', 'object_prisons.id_institution', '=', 'institutions.id')
                  ->orderBy('institutions.name', $this->sortDirection);
        } elseif ($this->sortField === 'eveniment') {
            $query->orderByRaw("COALESCE(eveniment, 'Depistare') " . $this->sortDirection);
        } elseif ($this->sortField === 'objects') {
            $query->leftJoin('object_prison_objects', 'object_prisons.id', '=', 'object_prison_objects.object_prison_id')
                  ->leftJoin('object_list', 'object_prison_objects.object_list_id', '=', 'object_list.id')
                  ->orderBy('object_list.name', $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $this->objects = $query->get();

        if ($this->objects->count() > 1000) {
            $this->objects = $this->objects->take(1000);
            Log::warning('Colecția de obiecte a fost limitată la 1000 de intrări.');
        }
    }

    public function logToConsole($data)
    {
        $this->dispatch('consoleLog', ['message' => $data['message']]);
    }

    public function createObject()
    {
        $this->validate();

        if (!Schema::hasTable('object_prisons')) {
            $this->dispatch('showToast', ['type' => 'danger', 'message' => 'Tabela object_prisons nu există.']);
            return;
        }

        $attributes = [
            'data' => $this->data,
            'id_institution' => $this->id_institution,
            'obj_text' => $this->obj_text,
            'eveniment' => $this->eveniment_type,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ];

        if ($this->eveniment_type && $this->eveniment_type !== 'Depistare') {
            $attributes['eveniment'] = $this->eveniment_type;
        }

        $object = ObjectPrison::create($attributes);

        if (!empty($this->selectedObjects)) {
            foreach ($this->selectedObjects as $selectedObject) {
                if ($selectedObject['quantity'] > 0 && isset($selectedObject['object_list_id'])) {
                    $object->objectListItems()->attach($selectedObject['object_list_id'], [
                        'quantity' => $selectedObject['quantity'],
                    ]);
                }
            }
        }

        $createdAt = Carbon::parse($object->created_at)->format('d-m-Y H:i');
        $this->resetForm();
        $this->loadObjects();
        $this->dispatch('showToast', [
            'type' => 'success',
            'message' => "Obiect creat cu succes la data: {$createdAt}"
        ]);
        $this->dispatch('closeModal');
    }

    public function editObject($id)
    {
        $object = ObjectPrison::with('objectListItems')->find($id);
        if (!$object) {
            Log::error("Obiectul cu ID-ul {$id} nu a fost găsit.");
            $this->dispatch('showToast', ['type' => 'danger', 'message' => "Obiectul cu ID-ul {$id} nu a fost găsit."]);
            return;
        }

        $this->editingObjectId = $id;
        $this->data = $object->data;
        $this->id_institution = $object->id_institution;
        $this->eveniment_type = $object->eveniment ?? 'Depistare';
        $this->obj_text = $object->obj_text;

        $this->selectedObjects = $object->objectListItems->isNotEmpty()
            ? $object->objectListItems->map(function ($item) {
                return [
                    'object_list_id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->pivot->quantity ?? 0,
                ];
            })->toArray()
            : [];

        $this->tempQuantities = collect($this->selectedObjects)->pluck('quantity', 'object_list_id')->all();
        $this->showModal = true;
    }

    public function updateObject()
    {
        $this->validate();

        $object = ObjectPrison::findOrFail($this->editingObjectId);
        if (!$object) {
            Log::error("Obiectul cu ID-ul {$this->editingObjectId} nu a fost găsit.");
            $this->dispatch('showToast', ['type' => 'danger', 'message' => "Obiectul cu ID-ul {$this->editingObjectId} nu a fost găsit."]);
            return;
        }

        $attributes = [
            'data' => $this->data,
            'id_institution' => $this->id_institution,
            'eveniment' => $this->eveniment_type,
            'obj_text' => $this->obj_text,
            'updated_by' => Auth::id(),
        ];

        if ($this->eveniment_type && $this->eveniment_type !== 'Depistare') {
            $attributes['eveniment'] = $this->eveniment_type;
        } else {
            $attributes['eveniment'] = null;
        }

        $object->update($attributes);

        $syncData = collect($this->selectedObjects)
            ->filter(function ($item) {
                return $item['quantity'] > 0 && isset($item['object_list_id']);
            })
            ->mapWithKeys(function ($item) {
                return [$item['object_list_id'] => ['quantity' => $item['quantity']]];
            })->all();
        $object->objectListItems()->sync($syncData);

        $this->resetForm();
        $this->loadObjects();
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Obiect actualizat cu succes!']);
        $this->dispatch('closeModal');
    }

    public function deleteObject($id)
    {
        $object = ObjectPrison::findOrFail($id);
        if (!$object) {
            Log::error("Obiectul cu ID-ul {$id} nu a fost găsit.");
            $this->dispatch('showToast', ['type' => 'danger', 'message' => "Obiectul cu ID-ul {$id} nu a fost găsit."]);
            return;
        }

        $object->delete();
        $this->loadObjects();
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Obiect șters cu succes!']);
    }

    public function addAllSelectedObjects()
    {
        foreach ($this->tempQuantities as $objectListId => $quantity) {
            if ($quantity !== null && $quantity >= 0) {
                $object = ObjectList::find($objectListId);
                if ($object) {
                    $existingIndex = collect($this->selectedObjects)->search(function ($item) use ($objectListId) {
                        return $item['object_list_id'] == $objectListId;
                    });

                    if ($existingIndex !== false) {
                        $this->selectedObjects[$existingIndex]['quantity'] = (int)$quantity;
                    } else {
                        $this->selectedObjects[] = [
                            'object_list_id' => $object->id,
                            'name' => $object->name,
                            'quantity' => (int)$quantity,
                        ];
                    }
                }
            }
        }
        $this->tempQuantities = [];
        $this->showObjectListModal = false;
    }

    public function removeSelectedObject($index)
    {
        if (isset($this->selectedObjects[$index])) {
            unset($this->selectedObjects[$index]);
            $this->selectedObjects = array_values($this->selectedObjects);
        }
    }

    public function resetForm()
    {
        $this->showModal = false;
        $this->showObjectListModal = false;
        $this->editingObjectId = null;
        $this->data = Carbon::today()->format('Y-m-d');
        $this->id_institution = null;
        $this->eveniment_type = 'Depistare';
        $this->obj_text = null;
        $this->selectedObjects = [];
        $this->tempQuantities = [];
        $this->resetErrorBag();
    }

    public function openObjectListModal()
    {
        $this->tempQuantities = collect($this->selectedObjects)->pluck('quantity', 'object_list_id')->all();
        $this->showObjectListModal = true;
    }

    protected function getFilters()
    {
        return [
            'selectedInstitution' => $this->selectedInstitution,
            'eventFilters' => $this->eventFilters,
            'selectedObjectIds' => $this->selectedObjectIds,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];
    }

    public function render()
    {
        return view('livewire.object-prison-manager', [
            'objects' => $this->objects,
        ]);
    }
}