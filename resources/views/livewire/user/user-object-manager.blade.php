<div>
    <h1 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">
        Manager Obiecte Interzise - {{ Auth::user()->institution ? Auth::user()->institution->name : 'N/A' }}
    </h1>

    <div class="border-b border-gray-200 dark:border-zinc-700">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-gray-400">
            <li class="me-2">
                <flux:button wire:click="setTab('user-objects')" 
                             class="inline-flex items-center justify-center p-4 border-b-2 {{ $activeTab === 'user-objects' ? 'text-blue-600 border-blue-600 dark:text-zinc-500 dark:border-zinc-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }} group"
                             icon="user"
                             style="border-radius: 0;"
                             variant="ghost">
                    Obiectele Mele
                </flux:button>
            </li>
            <li class="me-2">
                <flux:button wire:click="setTab('all-objects')" 
                             class="inline-flex items-center justify-center p-4 border-b-2 {{ $activeTab === 'all-objects' ? 'text-zinc-600 border-blue-600 dark:text-zinc-500 dark:border-zinc-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }} group"
                             icon="list-bullet"
                             style="border-radius: 0;"
                             variant="ghost">
                    Toate Obiectele
                </flux:button>
            </li>
        </ul>
    </div>

    <div class="mt-4">
        @if ($activeTab === 'user-objects')
            <div>
                <div class="mb-4 flex justify-between items-center">
                    <div>
                        <label for="filterDate" class="mr-2 text-sm">Filtrează după dată:</label>
                        <input type="date" id="filterDate" wire:model.live="selectedDate" 
                               class="border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded text-sm">
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="$set('showModal', true)" class="bg-blue-500 hover:bg-zinc-600 text-white px-4 py-2 rounded">
                            Adaugă obiect
                        </button>
                        <button onclick="printTable('user-objects-table')" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                            Printează
                        </button>
                        <button onclick="exportToPDF('user-objects-table')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                            Export PDF
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table id="user-objects-table" class="w-full bg-gray-100 dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 text-sm">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">Nr.</th>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">Data</th>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">Eveniment</th>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">Obiecte</th>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">Detalii</th>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">Adăugat la</th>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">Adăugat de</th>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">Actualizat la</th>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">Modificat de</th>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($userObjects as $index => $object)
                                <tr>
                                    <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">{{ $userObjects->firstItem() + $index }}</td>
                                    <td class=" w-32 py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">
                                        {{ \Carbon\Carbon::parse($object->data)->format('d-m-Y') }}
                                    </td>
                                    <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">
                                        {{ $object->eveniment ?? 'Depistare' }}
                                    </td>
                                    <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">
                                        @if ($object->objectListItems->isNotEmpty())
                                            <ul class="list-disc pl-5">
                                                @foreach ($object->objectListItems as $item)
                                                    <li>{{ $item->name }} ({{ $item->pivot->quantity }})</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">
                                        {!! $object->obj_text ?? '-' !!}
                                    </td>
                                    <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">
                                        {{ $object->created_at ? \Carbon\Carbon::parse($object->created_at)->format('d-m-Y H:i') : '-' }}
                                    </td>
                                    <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">
                                        {{ $object->createdBy ? $object->createdBy->name : '-' }}
                                    </td>
                                    <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">
                                        {{ $object->updated_at ? \Carbon\Carbon::parse($object->updated_at)->format('d-m-Y H:i') : '-' }}
                                    </td>
                                    <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">
                                        {{ $object->updatedBy ? $object->updatedBy->name : '-' }}
                                    </td>
                                    <td class="flex-row flex-wrap self-center py-2 px-2 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">
                                    <button wire:click="editObject({{ $object->id }})" 
                                                class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="deleteObject({{ $object->id }})" 
                                                class="bg-red-500 hover:bg-red-600 text-white p-2 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4M9 7h6m-4 4v6m4-6v6"></path>
                                            </svg>
                                        </button>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">Niciun obiect găsit.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $userObjects->links() }}
                    </div>
                </div>
            </div>
        @elseif ($activeTab === 'all-objects')
            <div>
                <div class="mb-4 flex justify-between items-center">
                    <div>
                        <label for="filterDate" class="mr-2 text-sm">Filtrează după dată:</label>
                        <input type="date" id="filterDate" wire:model.live="selectedDate" 
                               class="border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded text-sm">
                    </div>
                    <div>
                        <button onclick="printTable('all-objects-table')" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded mr-2">Printează</button>
                        <button onclick="exportToPDF('all-objects-table')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Export PDF</button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table id="all-objects-table" class="w-full bg-gray-100 dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 text-sm">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">Data</th>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">Instituția</th>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">Eveniment</th>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">Obiecte</th>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">Total Obiecte</th>
                                <th class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">Detalii</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($allObjects as $group)
                                <tr>
                                    <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center" rowspan="{{ $group['count'] }}">
                                        {{ \Carbon\Carbon::parse($group['data'])->format('d-m-Y') }}
                                    </td>
                                    <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center" rowspan="{{ $group['count'] }}">
                                        {{ $group['institution_name'] }}
                                    </td>
                                    <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">
                                        {{ $group['eveniment'] }}
                                    </td>
                                    <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center" rowspan="{{ $group['count'] }}">
                                        @foreach ($group['objects'] as $object)
                                            {{ $object['name'] }} ({{ $object['quantity'] }})<br>
                                        @endforeach
                                    </td>
                                    <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center" rowspan="{{ $group['count'] }}">
                                        {{ $group['total_quantity'] }}
                                    </td>
                                    <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">
                                        {!! $group['obj_text'] ?: '-' !!}
                                    </td>
                                </tr>
                                @if ($group['count'] > 1)
                                    @for ($i = 1; $i < $group['count']; $i++)
                                        <tr>
                                            <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">
                                                {{ $group['eveniment'] }}
                                            </td>
                                            <td class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-left">
                                                {!! $group['obj_text'] ?: '-' !!}
                                            </td>
                                        </tr>
                                    @endfor
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="py-2 px-4 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">Niciun obiect găsit.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $allObjectsPaginated->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if ($showModal)
    <div class="fixed inset-0 bg-zinc-800 bg-opacity-75 flex items-center justify-center p-4">
        <div class="bg-gray-100 dark:bg-zinc-900 p-4 w-full max-w-lg max-h-[80vh] overflow-y-auto border border-gray-300 dark:border-zinc-700">
            <h2 class="text-lg font-bold mb-3">{{ $editingObjectId ? 'Editează obiect' : 'Crează obiect' }}</h2>
            <form wire:submit.prevent="{{ $editingObjectId ? 'updateObject' : 'createObject' }}">
                <div class="mb-3">
                    <label class="block mb-1 text-sm">Data (ziua curentă)</label>
                    <input type="text" value="{{ $currentDate }}" readonly
                           class="w-full border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded text-sm">
                    @error('data') <span class="text-red-500 text-xs">{{ $message }}</span> @endif
                </div>
                <div class="mb-3">
                    <label class="block mb-1 text-sm">Tip eveniment</label>
                    <div class="flex space-x-4" wire:key="eveniment_type_{{ uniqid() }}">
                        <label class="flex items-center p-2">
                            <input type="radio" wire:model.live="eveniment_type" name="eveniment_type" value="Depistare" class="form-radio text-zinc-600 mr-2">
                            Depistare
                        </label>
                        <label class="flex items-center p-2" >
                            <input type="radio" wire:model.live="eveniment_type" name="eveniment_type" value="Contracarare" class="form-radio text-zinc-600 mr-2">
                            Contracarare
                        </label>
                    </div>
                    @error('eveniment_type') <span class="text-red-500 text-xs">{{ $message }}</span> @endif
                </div>
                <div class="mb-3">
                    <label class="block mb-1 text-sm">Obiecte</label>
                    <button type="button" wire:click="openObjectListModal" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        Selectează obiecte
                    </button>
                    @if (!empty($selectedObjects))
                        <div class="mt-2">
                            @foreach ($selectedObjects as $index => $object)
                                <div class="flex items-center gap-2 mb-2">
                                    <span>{{ $object['name'] }} ({{ $object['quantity'] }})</span>
                                    <button type="button" wire:click="removeSelectedObject({{ $index }})" class="bg-red-500 hover:bg-red-600 text-white p-1 rounded group relative" title="Șterge">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4M9 7h6m-5 4v6m4-6v6"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @error('selectedObjects') <span class="text-red-500 text-xs">{{ $message }}</span> @endif
                </div>
                <div class="mb-3">
                    <label class="block mb-1 text-sm">Detalii</label>
                    <textarea wire:model="obj_text" 
                              class="w-full border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-zinc-500 min-h-[100px] text-sm"></textarea>
                    @error('obj_text') <span class="text-red-500 text-xs">{{ $message }}</span> @endif
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" wire:click="resetForm" 
                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">Anulează</button>
                    <button type="submit" 
                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">{{ $editingObjectId ? 'Actualizează' : 'Crează' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if ($showObjectListModal)
    <div class="fixed inset-0 bg-zinc-800 bg-opacity-75 flex items-center justify-center p-4">
        <div class="bg-gray-100 dark:bg-zinc-900 p-4 w-full max-w-lg max-h-[80vh] overflow-y-auto border border-gray-300 dark:border-zinc-700">
            <h2 class="text-lg font-bold mb-3">Selectează obiecte</h2>
            <div class="mb-3">
                @foreach ($objectLists as $objectList)
                    <div class="flex items-center gap-2 mb-2">
                        <label class="flex-1">{{ $objectList->name }}</label>
                        <input type="number" wire:model="tempQuantities.{{ $objectList->id }}" min="0" 
                               class="w-20 border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded text-sm">
                    </div>
                @endforeach
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" wire:click="$set('showObjectListModal', false)" 
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">Anulează</button>
                <button type="button" wire:click="addAllSelectedObjects" 
                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">Adaugă</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Toast-uri -->
    <div id="toast-success" class="hidden flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white shadow-sm dark:text-gray-400 dark:bg-zinc-900" role="alert">
        <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-green-500 bg-green-100 dark:bg-green-800 dark:text-green-200">
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
            </svg>
            <span class="sr-only">Check icon</span>
        </div>
        <div id="toast-success-message" class="ms-3 text-sm font-normal"></div>
        <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-zinc-900 dark:hover:bg-zinc-700" onclick="hideToast('toast-success')" aria-label="Close">
            <span class="sr-only">Close</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>
    <div id="toast-danger" class="hidden flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white shadow-sm dark:text-gray-400 dark:bg-zinc-900" role="alert">
        <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-red-500 bg-red-100 dark:bg-red-800 dark:text-red-200">
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"/>
            </svg>
            <span class="sr-only">Error icon</span>
        </div>
        <div id="toast-danger-message" class="ms-3 text-sm font-normal"></div>
        <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-zinc-900 dark:hover:bg-zinc-700" onclick="hideToast('toast-danger')" aria-label="Close">
            <span class="sr-only">Close</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>
</div>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.3/jspdf.plugin.autotable.min.js"></script>
    <script>
        function printTable(tableId) {
            const table = document.getElementById(tableId);
            if (!table) return;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Print</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            table { width: 100%; border-collapse: collapse; font-size: 12px; }
                            th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                            th { background-color: #f2f2f2; }
                            .text-center { text-align: center; }
                        </style>
                    </head>
                    <body>
                        <h1>Obiecte - ${document.getElementById('filterDate').value}</h1>
                        ${table.outerHTML}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
            printWindow.close();
        }

        function exportToPDF(tableId) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'landscape' });
            const table = document.getElementById(tableId);
            if (!table) return;

            const date = document.getElementById('filterDate').value;
            doc.setFontSize(16);
            doc.text(`Obiecte - ${date}`, 148.5, 10, { align: 'center' });

            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
            const body = Array.from(table.querySelectorAll('tbody tr')).map(row => 
                Array.from(row.querySelectorAll('td')).map(td => td.textContent.trim())
            );

            doc.autoTable({
                head: [headers],
                body: body,
                startY: 20,
                styles: { fontSize: 8, cellPadding: 2, overflow: 'linebreak' },
                headStyles: { fillColor: [100, 100, 100], textColor: [255, 255, 255] },
            });

            doc.save(`obiecte_${date}.pdf`);
        }

        function showToast(type, message) {
            const toast = document.getElementById(`toast-${type}`);
            const messageElement = document.getElementById(`toast-${type}-message`);
            if (toast && messageElement) {
                messageElement.textContent = message;
                toast.classList.remove('hidden');
                setTimeout(() => hideToast(`toast-${type}`), 5000);
            }
        }

        function hideToast(id) {
            const toast = document.getElementById(id);
            if (toast) toast.classList.add('hidden');
        }

        document.addEventListener('livewire:init', () => {
            Livewire.on('showToast', ({ type, message }) => showToast(type, message));
            Livewire.on('closeModal', () => {
                const modal = document.querySelector('.fixed.inset-0');
                if (modal) modal.classList.add('hidden');
            });
        });
    </script>
@endpush