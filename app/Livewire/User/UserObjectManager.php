<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ObjectPrison;
use App\Models\Institution;
use App\Models\ObjectList;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserObjectManager extends Component
{
    use WithPagination;

    public $objectLists;
    public $selectedDate;
    public $currentDate;
    public $activeTab = 'user-objects';
    public $showModal = false;
    public $showObjectListModal = false;
    public $editingObjectId = null;
    public $data;
    public $eveniment_type = 'Depistare';
    public $obj_text;
    public $selectedObjects = [];
    public $tempQuantities = [];

    protected $rules = [
        'data' => 'required|date|before_or_equal:today',
        'eveniment_type' => 'nullable|in:Depistare,Contracarare',
        'obj_text' => 'nullable|string',
        'selectedObjects.*.object_list_id' => 'required_with:selectedObjects.*.quantity|exists:object_list,id',
        'selectedObjects.*.quantity' => 'nullable|integer|min:0',
    ];

    public function mount()
    {
        try {
            $this->objectLists = ObjectList::all();
            $this->selectedDate = Carbon::today()->format('Y-m-d');
            $this->currentDate = Carbon::today()->format('Y-m-d');
            $this->data = $this->currentDate;
        } catch (\Exception $e) {
            Log::error('Eroare la inițializarea componentei: ' . $e->getMessage());
            $this->dispatch('showToast', ['type' => 'danger', 'message' => 'Eroare la inițializarea componentei: ' . $e->getMessage()]);
        }
    }

    public function updatedSelectedDate($value)
    {
        $this->selectedDate = $value;
        $this->resetPage();
    }

    public function updatedEvenimentType($value)
    {
        $this->eveniment_type = $value;
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function createObject()
    {
        $this->data = Carbon::today()->format('Y-m-d');
        $this->validate();

        try {
            $attributes = [
                'data' => $this->data,
                'id_institution' => Auth::user()->institution->id,
                'obj_text' => $this->obj_text,
                'eveniment' => $this->eveniment_type,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];

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

            $this->resetForm();
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Obiect creat cu succes!']);
            $this->dispatch('closeModal');
        } catch (\Exception $e) {
            Log::error('Eroare la crearea obiectului: ' . $e->getMessage());
            $this->dispatch('showToast', ['type' => 'danger', 'message' => 'Eroare la crearea obiectului: ' . $e->getMessage()]);
        }
    }

    public function editObject($id)
    {
        try {
            $object = ObjectPrison::with('objectListItems')->find($id);
            if (!$object || $object->id_institution !== Auth::user()->institution->id) {
                throw new \Exception("Obiectul nu există sau nu aparține instituției dvs.");
            }

            $this->editingObjectId = $id;
            $this->data = Carbon::today()->format('Y-m-d');
            $this->currentDate = Carbon::today()->format('Y-m-d');
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
        } catch (\Exception $e) {
            Log::error('Eroare la editarea obiectului: ' . $e->getMessage());
            $this->dispatch('showToast', ['type' => 'danger', 'message' => 'Eroare la editarea obiectului: ' . $e->getMessage()]);
        }
    }

    public function updateObject()
    {
        $this->data = Carbon::today()->format('Y-m-d');
        $this->validate();

        try {
            $object = ObjectPrison::findOrFail($this->editingObjectId);
            if ($object->id_institution !== Auth::user()->institution->id) {
                throw new \Exception("Nu aveți permisiunea de a edita acest obiect.");
            }

            $attributes = [
                'data' => $this->data,
                'id_institution' => Auth::user()->institution->id,
                'eveniment' => $this->eveniment_type,
                'obj_text' => $this->obj_text,
                'updated_by' => Auth::id(),
            ];

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
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Obiect actualizat cu succes!']);
            $this->dispatch('closeModal');
        } catch (\Exception $e) {
            Log::error('Eroare la actualizarea obiectului: ' . $e->getMessage());
            $this->dispatch('showToast', ['type' => 'danger', 'message' => 'Eroare la actualizarea obiectului: ' . $e->getMessage()]);
        }
    }

    public function deleteObject($id)
    {
        try {
            $object = ObjectPrison::findOrFail($id);
            if ($object->id_institution !== Auth::user()->institution->id) {
                throw new \Exception("Nu aveți permisiunea de a șterge acest obiect.");
            }

            $object->delete();
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Obiect șters cu succes!']);
        } catch (\Exception $e) {
            Log::error('Eroare la ștergerea obiectului: ' . $e->getMessage());
            $this->dispatch('showToast', ['type' => 'danger', 'message' => 'Eroare la ștergerea obiectului: ' . $e->getMessage()]);
        }
    }

    public function addAllSelectedObjects()
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Eroare la adăugarea obiectelor selectate: ' . $e->getMessage());
            $this->dispatch('showToast', ['type' => 'danger', 'message' => 'Eroare la adăugarea obiectelor: ' . $e->getMessage()]);
        }
    }

    public function removeSelectedObject($index)
    {
        try {
            if (isset($this->selectedObjects[$index])) {
                unset($this->selectedObjects[$index]);
                $this->selectedObjects = array_values($this->selectedObjects);
            }
        } catch (\Exception $e) {
            Log::error('Eroare la eliminarea obiectului selectat: ' . $e->getMessage());
            $this->dispatch('showToast', ['type' => 'danger', 'message' => 'Eroare la eliminarea obiectului: ' . $e->getMessage()]);
        }
    }

    public function resetForm()
    {
        $this->showModal = false;
        $this->showObjectListModal = false;
        $this->editingObjectId = null;
        $this->data = Carbon::today()->format('Y-m-d');
        $this->currentDate = Carbon::today()->format('Y-m-d');
        $this->eveniment_type = 'Depistare';
        $this->obj_text = null;
        $this->selectedObjects = [];
        $this->tempQuantities = [];
        $this->resetErrorBag();
    }

    public function openObjectListModal()
    {
        try {
            $this->tempQuantities = collect($this->selectedObjects)->pluck('quantity', 'object_list_id')->all();
            $this->showObjectListModal = true;
        } catch (\Exception $e) {
            Log::error('Eroare la deschiderea modalului de selecție: ' . $e->getMessage());
            $this->dispatch('showToast', ['type' => 'danger', 'message' => 'Eroare la deschiderea selecției obiectelor: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $userInstitutionId = Auth::user()->institution ? Auth::user()->institution->id : null;

        $userObjects = $userInstitutionId
            ? ObjectPrison::with(['institution', 'objectListItems', 'createdBy', 'updatedBy'])
                ->where('id_institution', $userInstitutionId)
                ->whereDate('data', $this->selectedDate)
                ->orderBy('created_at', 'desc')
                ->paginate(10)
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);

        $allObjectsQuery = ObjectPrison::with(['institution', 'objectListItems'])
            ->whereDate('data', $this->selectedDate)
            ->orderBy('created_at', 'desc');
        $allObjectsPaginated = $allObjectsQuery->paginate(10);

        $allObjects = $allObjectsPaginated->groupBy(function ($object) {
            return $object->data . '|' . $object->id_institution;
        })->map(function ($group) {
            $first = $group->first();
            $totalQuantity = $group->pluck('objectListItems')->flatten()->sum('pivot.quantity');
            return [
                'data' => $first->data,
                'institution_name' => $first->institution ? $first->institution->name : 'N/A',
                'eveniment' => $group->pluck('eveniment')->unique()->filter()->implode(', ') ?: 'Depistare',
                'objects' => $group->pluck('objectListItems')->flatten()->groupBy('name')->map(function ($items, $name) {
                    return [
                        'name' => $name,
                        'quantity' => $items->sum('pivot.quantity'),
                    ];
                })->values(),
                'total_quantity' => $totalQuantity,
                'obj_text' => $group->pluck('obj_text')->filter()->implode(', '),
                'count' => $group->count(),
            ];
        })->values();

        return view('livewire.user.user-object-manager', [
            'userObjects' => $userObjects,
            'allObjects' => $allObjects,
            'allObjectsPaginated' => $allObjectsPaginated, // Pentru link-urile de paginare
        ]);
    }
}