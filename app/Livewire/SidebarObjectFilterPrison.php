<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Institution;
use App\Models\ObjectList;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class SidebarObjectFilterPrison extends Component
{
    public $isOpen = true;
    public $selectedInstitution = [];
    public $eventFilters = ['Depistare' => false, 'Contracarare' => true];
    public $selectedObjectIds = [];
    public $institutions;
    public $objectLists;
    public $selectedObjectsWithQuantities;
    public $totalQuantity = 0;
    public $selectAll = false;
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->isOpen = Session::get('sidebar_open', true);
        $this->institutions = Institution::orderBy('name')->get();
        $this->objectLists = ObjectList::orderBy('name')->get();
        $this->selectedObjectsWithQuantities = collect();
        $this->selectedObjectIds = [];
        $this->startDate = Carbon::today()->subDays(7)->format('Y-m-d');
        $this->endDate = Carbon::today()->format('Y-m-d');
    }

    public function toggleSidebar()
    {
        $this->isOpen = !$this->isOpen;
        Session::put('sidebar_open', $this->isOpen);
    }

    public function updatedSelectedObjectIds($value)
    {
        if (!is_array($value)) {
            $value = is_string($value) ? [$value] : [];
        }

        $this->selectedObjectIds = array_unique($value);
        Log::info('Obiecte selectate în SidebarObjectFilterPrison: ' . json_encode($this->selectedObjectIds));
        $this->dispatch('logToConsole', ['message' => 'Obiecte selectate: ' . json_encode($this->selectedObjectIds)]);
        $this->updateFilters();
    }

    public function toggleObjectSelection($objectId)
    {
        $objectId = (string) $objectId;
        if (in_array($objectId, $this->selectedObjectIds)) {
            $this->selectedObjectIds = array_diff($this->selectedObjectIds, [$objectId]);
        } else {
            $this->selectedObjectIds[] = $objectId;
        }

        $this->selectedObjectIds = array_values($this->selectedObjectIds);
        $this->updateSelectAllState();
        Log::info('Obiecte selectate după toggle în SidebarObjectFilterPrison: ' . json_encode($this->selectedObjectIds));
        $this->dispatch('logToConsole', ['message' => 'Obiecte selectate după toggle: ' . json_encode($this->selectedObjectIds)]);
        $this->updateFilters();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedObjectIds = $this->objectLists->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedObjectIds = [];
        }

        Log::info('Obiecte selectate după Select All în SidebarObjectFilterPrison: ' . json_encode($this->selectedObjectIds));
        $this->dispatch('logToConsole', ['message' => 'Obiecte selectate după Select All: ' . json_encode($this->selectedObjectIds)]);
        $this->updateFilters();
    }

    private function updateSelectAllState()
    {
        $allIds = $this->objectLists->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $this->selectAll = count($this->selectedObjectIds) === count($allIds) && empty(array_diff($allIds, $this->selectedObjectIds));
    }

    public function calculateQuantities()
    {
        Log::info('Apelare calculateQuantities cu filtre: ' . json_encode($this->getFilters()));
        $this->dispatch('logToConsole', ['message' => 'Apelare calculateQuantities cu filtre: ' . json_encode($this->getFilters())]);
        $this->dispatch('calculateQuantities', [
            'filters' => $this->getFilters()
        ])->to('object-prison-manager');
    }

    protected $listeners = [
        'updateSelectedObjectsQuantities' => 'updateQuantities',
        'filtersUpdated' => 'syncFilters'
    ];

    public function updateQuantities($data)
    {
        if (isset($data['objects'])) {
            $this->selectedObjectsWithQuantities = collect($data['objects']);
            Log::info('Cantități actualizate în SidebarObjectFilterPrison: ' . json_encode($this->selectedObjectsWithQuantities));
            $this->dispatch('logToConsole', ['message' => 'Cantități actualizate: ' . json_encode($this->selectedObjectsWithQuantities)]);
        }

        if (isset($data['total'])) {
            $this->totalQuantity = $data['total'];
            Log::info('Total actualizat în SidebarObjectFilterPrison: ' . $this->totalQuantity);
            $this->dispatch('logToConsole', ['message' => 'Total actualizat: ' . $this->totalQuantity]);
        }
    }

    public function updateFilters()
    {
        $this->dispatch('filtersUpdated', $this->getFilters())->to('object-prison-manager');
    }

    public function syncFilters($filters)
    {
        $this->selectedInstitution = $filters['selectedInstitution'] ?? [];
        $this->eventFilters = $filters['eventFilters'] ?? ['Depistare' => false, 'Contracarare' => true];
        $this->selectedObjectIds = $filters['selectedObjectIds'] ?? [];
        $this->startDate = $filters['startDate'] ?? $this->startDate;
        $this->endDate = $filters['endDate'] ?? $this->endDate;
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

    public function clearFilters()
    {
        $this->selectedInstitution = [];
        $this->eventFilters = ['Depistare' => false, 'Contracarare' => true];
        $this->selectedObjectIds = [];
        $this->selectedObjectsWithQuantities = collect();
        $this->totalQuantity = 0;
        $this->selectAll = false;
        $this->startDate = Carbon::today()->subDays(7)->format('Y-m-d');
        $this->endDate = Carbon::today()->format('Y-m-d');
        Log::info('Filtre șterse în SidebarObjectFilterPrison: ' . json_encode($this->selectedObjectIds));
        $this->dispatch('logToConsole', ['message' => 'Filtre șterse: ' . json_encode($this->selectedObjectIds)]);
        $this->updateFilters();
        $this->dispatch('filtersCleared');
    }

    public function render()
    {
        return view('livewire.app.sidebar-object-filter-prison');
    }
}