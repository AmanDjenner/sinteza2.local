@can('view objects')
<div class="flex-col w-full transition-all duration-300 md:flex md:flex-col min-h-screen relative">
    <!-- Includem componenta sidebar -->
    @livewire('sidebar-object-filter-prison')

    <!-- Main Content -->
    <div class="p-4 z-10">
        <h1 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Manager Obiecte interzise</h1>

        <div class="mb-4 flex justify-between items-center flex-wrap gap-4">
            <!-- Date Range Filter -->
            <div class="flex space-x-4">
                <div>
                    <label for="start-date" class="block mb-1 text-gray-900 dark:text-white">Data de început:</label>
                    <input type="date" id="start-date" wire:model.live="startDate" 
                           class="w-48 border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-zinc-500">
                </div>
                <div>
                    <label for="end-date" class="block mb-1 text-gray-900 dark:text-white">Data de sfârșit:</label>
                    <input type="date" id="end-date" wire:model.live="endDate" 
                           class="w-48 border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-zinc-500">
                </div>
            </div>

            <!-- Search Input -->
            <div>
                <label for="search" class="block mb-1 text-gray-900 dark:text-white">Caută:</label>
                <input type="text" id="search" wire:model.live="search" placeholder="Caută în tabel..." 
                       class="w-64 border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-zinc-500">
            </div>

            <!-- Buttons -->
            <div class="flex space-x-2">
                @can('create objects')
                    <button wire:click="$set('showModal', true)" 
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        Adaugă obiect
                    </button>
                @endcan
                <button onclick="printTable()" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                    Printează
                </button>
                <button onclick="downloadPDF()" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                    Descarcă PDF
                </button>
            </div>
        </div>

        <!-- Toast Messages -->
        <div id="toast-success" class="hidden flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white shadow-lg rounded-lg dark:text-gray-400 dark:bg-zinc-800" role="alert">
            <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg dark:bg-green-700 dark:text-green-200">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                </svg>
                <span class="sr-only">Check icon</span>
            </div>
            <div id="toast-success-message" class="ms-3 text-sm font-normal"></div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-zinc-800 dark:hover:bg-zinc-700" onclick="hideToast('toast-success')" aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
        <div id="toast-danger" class="hidden flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white shadow-lg rounded-lg dark:text-gray-400 dark:bg-zinc-800" role="alert">
            <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg dark:bg-red-700 dark:text-red-200">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"/>
                </svg>
                <span class="sr-only">Error icon</span>
            </div>
            <div id="toast-danger-message" class="ms-3 text-sm font-normal"></div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-zinc-800 dark:hover:bg-zinc-700" onclick="hideToast('toast-danger')" aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>

        <!-- Table -->
        @if (($startDate || $endDate || $search || !empty($selectedInstitution) || $eventFilters['Depistare'] || $eventFilters['Contracarare'] || !empty($selectedObjectIds)) && $objects->isNotEmpty())
            <div id="objects-table" class="overflow-x-auto">
                <h2 class="text-xl font-bold text-center mb-4 hidden print:block" id="print-title">
                    Lista obiecte pentru perioada {{ $startDate ? Carbon\Carbon::parse($startDate)->format('d-m-Y') : 'necunoscut' }} - {{ $endDate ? Carbon\Carbon::parse($endDate)->format('d-m-Y') : 'necunoscut' }}
                </h2>
                <div class="flex justify-center">
                    <table class="w-full bg-gray-100 dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700">
                        <thead>
                            <tr class="bg-gray-200 dark:bg-zinc-800">
                                <th class="w-fit py-1 px-2 text-gray-900 dark:text-white text-center truncate -indent-px  text-base">Nr.</th>
                                <th class="w-fit py-1 px-2 text-gray-900 dark:text-white text-center truncate -indent-px  text-base">Data</th>
                                <th class="w-fit  py-1 px-2 text-gray-900 dark:text-white text-center -indent-px  text-base cursor-pointer" wire:click="sortBy('institution')">
                                    Instituție 
                                    @if ($sortField === 'institution')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th class="w-fit py-1 px-2 text-gray-900 dark:text-white text-center truncate -indent-px  text-base cursor-pointer" wire:click="sortBy('eveniment')">
                                    Eveniment 
                                    @if ($sortField === 'eveniment')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th class="w-fit py-1 px-2 text-gray-900 dark:text-white text-center truncate -indent-px  text-base cursor-pointer" wire:click="sortBy('objects')">
                                    Obiecte 
                                    @if ($sortField === 'objects')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th class="w-fit py-1 px-2 text-gray-900 dark:text-white text-center   text-base">Detalii</th>
                                <th class="w-fit py-1 px-2 text-gray-900 dark:text-white text-center truncate -indent-px  text-base">Adăugat la</th>
                                <th class="w-fit py-1 px-2 text-gray-900 dark:text-white text-center truncate -indent-px  text-base">Adăugat de</th>
                                <th class="w-fit py-1 px-2 text-gray-900 dark:text-white text-center truncate -indent-px  text-base">Actualizat la</th>
                                <th class="w-fit py-1 px-2 text-gray-900 dark:text-white text-center truncate -indent-px  text-base">Modificat de</th>
                                <th class="w-24 py-1 px-2 text-gray-900 dark:text-white text-center truncate -indent-px  text-base">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($objects as $index => $object)
                                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700">
                                    <td class="w-fit py-1 px-2 text-gray-900 dark:text-white text-center truncate -indent-px text-xs">{{ $index + 1 }}</td>
                                    <td class="w-fit py-1 px-2 text-gray-900 dark:text-white text-center truncate -indent-px  text-xs">
                                        {{ $object->data ? Carbon\Carbon::parse($object->data)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td class="w-fit py-1 px-2 text-gray-900 dark:text-white text-left -indent-px text-sm">
                                        {{ $object->institution->name ?? '-' }}
                                    </td>
                                    <td class="w-fit py-1 px-2 text-gray-900 dark:text-white text-left  truncate -indent-px text-sm">
                                        {{ $object->eveniment ?? 'Depistare' }}
                                    </td>
                                    <td class="w-fit py-1 px-2 text-gray-900 dark:text-white text-left  truncate -indent-px text-xs">
                                        @if ($object->objectListItems && $object->objectListItems->isNotEmpty())
                                            <ul class="list-disc pl-5">
                                                @foreach ($object->objectListItems as $item)
                                                    <li>{{ $item->name }} ({{ $item->pivot->quantity }})</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="w-fit py-1 px-2 text-gray-900 dark:text-white text-left  -indent-px text-xs">
                                        {!! $object->obj_text ?? '-' !!}
                                    </td>
                                    <td class="w-fit py-1 px-2 text-gray-900 dark:text-white text-left -indent-px text-xs">
                                        {{ $object->created_at ? Carbon\Carbon::parse($object->created_at)->format('d-m-Y H:i') : '-' }}
                                    </td>
                                    <td class="w-fit py-1 px-2 text-gray-900 dark:text-white text-left -indent-px text-xs">
                                        {{ $object->createdBy ? $object->createdBy->name : '-' }}
                                    </td>
                                    <td class="w-fit py-1 px-2 text-gray-900 dark:text-white text-left -indent-px text-xs">
                                        {{ $object->updated_at ? Carbon\Carbon::parse($object->updated_at)->format('d-m-Y H:i') : '-' }}
                                    </td>
                                    <td class="w-fit py-1 px-2 text-gray-900 dark:text-white text-left  truncate -indent-px text-xs">
                                        {{ $object->updatedBy ? $object->updatedBy->name : '-' }}
                                    </td>
                                    <td class="w-fit py-1 px-2 text-gray-900 dark:text-white text-center">
                                        <div class="flex justify-center space-x-1">
                                            @can('edit objects')
                                                <button wire:click="editObject({{ $object->id }})" 
                                                        class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                            @endcan
                                            @can('delete objects')
                                                <button wire:click="deleteObject({{ $object->id }})" 
                                                        class="bg-red-500 hover:bg-red-600 text-white p-2 rounded">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4M9 7h6m-4 4v6m4-6v6"></path>
                                                    </svg>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif ($startDate || $endDate || $search || !empty($selectedInstitution) || $eventFilters['Depistare'] || $eventFilters['Contracarare'] || !empty($selectedObjectIds))
            <p class="text-gray-900 dark:text-white text-center">Nu există obiecte pentru criteriile selectate.</p>
        @else
            <p class="text-gray-900 dark:text-white text-center">Selectați o perioadă sau alte criterii pentru a afișa obiectele.</p>
        @endif

        <!-- Modal pentru creare/editare obiect -->
        @if ($showModal)
            <div id="modal-create" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50" wire:ignore.self>
                <div class="bg-white dark:bg-zinc-900 p-6 w-1/2 text-gray-900 dark:text-white border border-gray-200 dark:border-zinc-700 rounded-lg shadow-lg">
                    <h2 class="text-xl font-bold mb-4">{{ $editingObjectId ? 'Editează obiect' : 'Crează obiect' }}</h2>
                    <form wire:submit.prevent="{{ $editingObjectId ? 'updateObject' : 'createObject' }}">
                        <div class="mb-4">
                            <label class="block mb-1 text-gray-900 dark:text-white">Data</label>
                            <input type="date" wire:model="data" 
                                   max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                   class="w-full border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('data') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block mb-1 text-gray-900 dark:text-white">Instituție</label>
                            <select wire:model="id_institution" 
                                    class="w-full border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selectează o instituție</option>
                                @foreach ($institutions as $institution)
                                    <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                                @endforeach
                            </select>
                            @error('id_institution') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block mb-1 text-gray-900 dark:text-white">Eveniment</label>
                            <div class="flex space-x-6">
                                <label class="flex items-center mr-4 p-2">
                                    <input type="radio" wire:model="eveniment_type" value="Depistare" class="form-radio text-blue-600 mr-2 focus:ring-blue-500" checked>
                                    <span class="text-gray-900 dark:text-white">Depistare</span>
                                </label>
                                <label class="flex items-center mr-4 p-2">
                                    <input type="radio" wire:model="eveniment_type" value="Contracarare" class="form-radio text-blue-600 mr-2 focus:ring-blue-500">
                                    <span class="text-gray-900 dark:text-white">Contracarare</span>
                                </label>
                            </div>
                            @error('eveniment_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block mb-1 text-gray-900 dark:text-white">Obiecte</label>
                            <div class="space-y-2">
                                @if (!empty($selectedObjects))
                                    <ol class="list-decimal pl-5">
                                        @foreach ($selectedObjects as $index => $selectedObject)
                                            <li class="flex justify-between items-center text-gray-900 dark:text-white py-1">
                                                {{ $selectedObject['name'] ?? 'N/A' }} ({{ $selectedObject['quantity'] ?? 0 }})
                                                <button type="button" wire:click="removeSelectedObject({{ $index }})" 
                                                        class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm">Distruge</button>
                                            </li>
                                        @endforeach
                                    </ol>
                                @endif
                                <div class="flex items-center space-x-2">
                                    <button type="button" wire:click="openObjectListModal" 
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block mb-1 text-gray-900 dark:text-white">Detalii</label>
                            <textarea wire:model="obj_text" 
                                      class="w-full border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[200px]"></textarea>
                            @error('obj_text') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" wire:click="resetForm" 
                                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Anulează</button>
                            <button type="submit" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                {{ $editingObjectId ? 'Actualizează' : 'Crează' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Modal pentru selecție obiecte -->
        @if ($showObjectListModal)
            <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50" wire:ignore.self>
                <div class="bg-white dark:bg-zinc-900 p-6 w-1/3 text-gray-900 dark:text-white border border-gray-200 dark:border-zinc-700 rounded-lg shadow-lg">
                    <h2 class="text-xl font-bold mb-4">Selectează obiecte</h2>
                    <div class="space-y-4">
                        @if ($objectLists && $objectLists->isNotEmpty())
                            @foreach ($objectLists as $list)
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-900 dark:text-white">{{ $list->name ?? 'N/A' }}</span>
                                    <input type="number" wire:model.defer="tempQuantities.{{ $list->id }}" min="0" 
                                           class="w-20 border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-1 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="Cantitate">
                                </div>
                            @endforeach
                        @else
                            <p class="text-gray-900 dark:text-white">Nu există obiecte disponibile.</p>
                        @endif
                    </div>
                    <div class="flex justify-end mt-4 space-x-2">
                        <button type="button" wire:click="$set('showObjectListModal', false)" 
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Anulează</button>
                        <button type="button" wire:click="addAllSelectedObjects" 
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Adaugă</button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Biblioteci jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.3/jspdf.plugin.autotable.min.js"></script>
    <script src="{{ asset('js/roboto.js') }}"></script>

    <script>
        function printTable() {
            window.print();
        }

        function downloadPDF() {
            try {
                const startDateInput = document.getElementById('start-date').value;
                const endDateInput = document.getElementById('end-date').value;
                if (!startDateInput && !endDateInput) {
                    throw new Error('Vă rugăm să selectați cel puțin o dată.');
                }
                const startDate = startDateInput ? startDateInput.split('-').reverse().join('-') : 'necunoscut';
                const endDate = endDateInput ? endDateInput.split('-').reverse().join('-') : 'necunoscut';

                const table = document.querySelector('#objects-table table');
                if (!table) {
                    throw new Error('Tabelul nu este disponibil.');
                }

                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: 'a4'
                });

                doc.addFileToVFS('Roboto-Regular.ttf', robotoFont); 
                doc.addFont('Roboto-Regular.ttf', 'Roboto', 'regular');
                doc.setFont('Roboto');

                doc.setFontSize(16);
                const title = `Lista obiecte pentru perioada ${startDate} - ${endDate}`;
                doc.text(title, 148.5, 20, { align: 'center' });

                const headers = ['Nr.', 'Data', 'Instituție', 'Eveniment', 'Obiecte', 'Detalii', 'Adăugat la', 'Adăugat de', 'Actualizat la', 'Modificat de', 'Acțiuni'];
                const body = [];

                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    const rowData = Array.from(cells).map(cell => cell.textContent.trim());
                    body.push(rowData);
                });

                doc.autoTable({
                    head: [headers],
                    body: body,
                    startY: 30,
                    styles: { 
                        font: 'Roboto', 
                        fontSize: 8, 
                        cellPadding: 2,
                        overflow: 'linebreak',
                        halign: 'center' 
                    },
                    headStyles: { 
                        font: 'Roboto', 
                        fillColor: [100, 100, 100], 
                        textColor: [255, 255, 255], 
                        fontStyle: 'bold',
                        halign: 'center'
                    },
                    columnStyles: {
                        0: { cellWidth: 10 },
                        1: { cellWidth: 20 },
                        2: { cellWidth: 30, halign: 'left' },
                        3: { cellWidth: 25 },
                        4: { cellWidth: 40, halign: 'left' },
                        5: { cellWidth: 40, halign: 'left' },
                        6: { cellWidth: 25 },
                        7: { cellWidth: 25 },
                        8: { cellWidth: 25 },
                        9: { cellWidth: 25 },
                        10: { cellWidth: 30 }
                    },
                    margin: { top: 10, left: 10, right: 10, bottom: 10 }
                });

                doc.save(`Lista obiecte ${startDate}-${endDate}.pdf`);
            } catch (error) {
                console.error('Eroare la generarea PDF-ului:', error);
                alert(error.message || 'A apărut o eroare la generarea PDF-ului.');
            }
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
            if (toast) {
                toast.classList.add('hidden');
            }
        }

        document.addEventListener('livewire:init', () => {
            Livewire.on('showToast', ({ type, message }) => {
                showToast(type, message);
            });
            Livewire.on('closeModal', () => {
                const modal = document.getElementById('modal-create');
                if (modal) {
                    modal.classList.add('hidden');
                }
            });
            Livewire.on('consoleLog', (event) => {
                console.log(event.message);
            });
        });
    </script>

    <style>
        .object-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .object-name {
            flex-grow: 1;
        }

        .object-quantity {
            margin-left: 8px;
            min-width: 60px;
            text-align: right;
        }
        @media screen {
            .text-left { text-align: left !important; }
            .text-center { text-align: center !important; }
            table { border-collapse: collapse; }
            th, td { border: 1px solid #d1d5db; }
            .dark th, .dark td { border: 1px solid #3f3f46; }
        }
        @media print {
            body * { visibility: hidden; }
            #objects-table, #objects-table * { visibility: visible; }
            #objects-table { position: absolute; left: 0; top: 0; width: 100%; }
            #print-title { display: block !important; margin-bottom: 10px; }
            table { border-collapse: collapse; font-size: 10pt; width: 100%; }
            th, td { border: 1px solid #000; padding: 5px; }
            .text-left { text-align: left !important; }
            .text-center { text-align: center !important; }
        }
    </style>
</div>
@else
    <div class="text-center text-gray-900 dark:text-white">
        <p>Nu aveți permisiunea de a vizualiza obiectele.</p>
    </div>
@endcan