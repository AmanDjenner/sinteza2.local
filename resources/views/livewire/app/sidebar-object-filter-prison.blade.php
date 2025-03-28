<div>
    <!-- Toggle Button -->
    <div class="fixed top-1/6 right-0 z-50 transition-all duration-300 ease-in-out"
         style="transform: translate({{ $isOpen ? '-384px' : '0' }}, -50%);">
        <div class="bg-gray-200 text-gray-800 p-2 border-1 rounded-l-lg cursor-pointer shadow-md transition-colors duration-200"
             wire:click="toggleSidebar">
            <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M3 6a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L14 14.414V20a1 1 0 01-1.447.894l-4-2A1 1 0 018 18v-3.586L3.293 8.707A1 1 0 013 8V6zm2 0v1.586l5 5V18l2 1v-6.414l5-5V6H5z" clip-rule="evenodd"></path>
            </svg>
        </div>
    </div>

    <!-- Sidebar Content -->
    <div class="fixed top-0 right-0 z-40 h-screen w-96 p-4 overflow-y-auto transition-transform duration-300 ease-in-out rounded-l-lg bg-white dark:bg-zinc-900 border-gray-200 dark:border-1 border-gray-500"
         style="transform: translateX({{ $isOpen ? '0' : '100%' }});">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <div class="flex space-x-4">
                <div>
                    <label for="start-date" class="block mb-1 text-gray-900 dark:text-white">Data de început:</label>
                    <input type="date" id="start-date" wire:model.live="startDate" 
                           class="w-38 border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-zinc-500">
                </div>
                <div>
                    <label for="end-date" class="block mb-1 text-gray-900 dark:text-white">Data de sfârșit:</label>
                    <input type="date" id="end-date" wire:model.live="endDate" 
                           class="w-38 border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-zinc-500">
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="space-y-6">
            <!-- Institutions -->
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white mb-2">Instituții</h3>
                <select wire:model.live="selectedInstitution" multiple 
                        class="w-full h-40 border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach ($institutions as $institution)
                        <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Events -->
            <div class="m-0">
                <h3 class="font-bold text-gray-900 dark:text-white mb-2">Evenimente</h3>
                <ul class="space-y-2 flex flex-row">
                    <li>
                        <div class="flex items-center mr-2">
                            <input id="event-depistare" type="checkbox" wire:model.live="eventFilters.Depistare" 
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-zinc-900 focus:ring-2 dark:bg-zinc-700 dark:border-zinc-600">
                            <label for="event-depistare" class="ms-2 text-sm text-gray-900 dark:text-gray-300">Depistare</label>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <input id="event-contracarare" type="checkbox" wire:model.live="eventFilters.Contracarare" 
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-zinc-900 focus:ring-2 dark:bg-zinc-700 dark:border-zinc-600">
                            <label for="event-contracarare" class="ms-2 text-sm text-gray-900 dark:text-gray-300">Contracarare</label>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Objects -->
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white mb-2">
                    Total obiecte: 
                    <span class="text-sm font-medium {{ $totalQuantity > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400' }}">
                        {{ number_format($totalQuantity) }}
                    </span>
                </h3>
                
                @if(!empty($selectedInstitution))
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        Instituții selectate:  
                        <span class="text-sm font-medium {{ $selectedInstitution > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">
                            {{ count($selectedInstitution) }}
                        </span> 
                    </p>
                @endif
                
                @if(!empty(array_filter($eventFilters)))
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        Evenimente selectate: 
                        <span class="text-sm font-medium {{ $eventFilters > 0 ? 'text-blue-500 dark:text-blue-400' : 'text-gray-400' }}">
                            @foreach(array_filter($eventFilters) as $event => $active)
                                {{ $event }}@if(!$loop->last), @endif
                            @endforeach
                        </span>
                    </p>
                @endif
                
                <!-- Checkbox „Select all” -->
                <div class="flex items-center mb-2">
                    <input id="select-all" type="checkbox" wire:model.live="selectAll" 
                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-zinc-900 focus:ring-2 dark:bg-zinc-700 dark:border-zinc-600">
                    <label for="select-all" class="ms-2 text-sm text-gray-900 dark:text-gray-300">Selectează toate</label>
                </div>

                <ul class="space-y-1 max-h-100% overflow-y-auto">
                    @foreach ($objectLists as $object)
                        <li class="{{ in_array($object->id, $selectedObjectIds) ? 'bg-blue-50 dark:bg-zinc-800' : '' }}">
                            <div class="flex items-center justify-between p-1">
                                <div class="flex items-center">
                                    <input id="object-{{ $object->id }}" type="checkbox" 
                                           wire:change="toggleObjectSelection('{{ $object->id }}')"
                                           {{ in_array($object->id, $selectedObjectIds) ? 'checked' : '' }}
                                           value="{{ $object->id }}"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-zinc-900 focus:ring-2 dark:bg-zinc-700 dark:border-zinc-600">
                                    <label for="object-{{ $object->id }}" class="ms-2 text-sm text-gray-900 dark:text-gray-300">
                                        {{ $object->name }}
                                    </label>
                                </div>
                                @if(in_array($object->id, $selectedObjectIds))
                                    @php
                                        $selectedItem = $selectedObjectsWithQuantities->firstWhere('id', $object->id);
                                        $quantity = $selectedItem['quantity'] ?? 0;
                                    @endphp
                                    <span class="text-sm font-medium {{ $quantity > 0 ? 'text-blue-500 dark:text-blue-400' : 'text-gray-400' }}">
                                        {{ number_format($quantity) }}
                                    </span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                <!-- Buton Calculează -->
                <button wire:click="calculateQuantities" 
                        class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded w-full">
                    Calculează
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('filtersCleared', () => {
            document.querySelectorAll('input[type="checkbox"][wire\\:change^="toggleObjectSelection"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('select-all').checked = false;
        });
    });
</script>