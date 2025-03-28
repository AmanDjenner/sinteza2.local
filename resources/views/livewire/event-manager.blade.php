@can('view events')
<div>
    <h1 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Manager Evenimente</h1>

    <div class="mb-4">
        @can('create events')
            <button wire:click="$set('showModal', true)" 
                    class="bg-blue-500 hover:bg-zinc-600 text-white px-4 py-2 rounded">
                Adaugă eveniment
            </button>
        @endcan
    </div>

    @if (session()->has('message'))
        <div class="mb-4 text-green-500">{{ session('message') }}</div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 text-red-500">{{ session('error') }}</div>
    @endif

    <!-- Filtre și căutare -->
    <div class="w-full mb-4 flex flex-wrap items-center gap-4">
        <div class="flex items-center">
            <input type="date" wire:model.live="startDate" 
                   class="border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded">
            @error('startDate') <span class="text-red-500 ml-2">{{ $message }}</span> @enderror
        </div>
        <div class="flex items-center">
            <label class="mr-2 text-gray-900 dark:text-white">---</label>
            <input type="date" wire:model.live="endDate" 
                   class="border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded">
            @error('endDate') <span class="text-red-500 ml-2">{{ $message }}</span> @enderror
        </div>
        <div class="w-full flex items-center flex-grow">
            <input type="text" wire:model.live="search" placeholder="Caută în detalii..." 
                   class="w-[80%] border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded">
        </div>
        <div class="flex items-center">
            <label class="mr-2 text-gray-900 dark:text-white">Elemente pe pagină:</label>
            <select wire:model.live="perPage" class="border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded">
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    <div class="flex justify-center">
        <table class="bg-gray-100 dark:bg-zinc-900">
            <thead>
                <tr>
                    <th wire:click="sortBy('id')" class="py-2 px-4 text-gray-900 dark:text-white cursor-pointer text-center">
                        Nr. Ordine 
                        @if ($sortField === 'id') 
                            ({{ $sortDirection === 'asc' ? '↑' : '↓' }})
                        @endif
                    </th>
                    <th wire:click="sortBy('data')" class="py-2 px-4 text-gray-900 dark:text-white cursor-pointer text-center">
                        Data 
                        @if ($sortField === 'data') 
                            ({{ $sortDirection === 'asc' ? '↑' : '↓' }})
                        @endif
                    </th>
                    <th wire:click="sortBy('id_institution')" class="py-2 px-4 text-gray-900 dark:text-white cursor-pointer text-center">
                        Instituție 
                        @if ($sortField === 'id_institution') 
                            ({{ $sortDirection === 'asc' ? '↑' : '↓' }})
                        @endif
                    </th>
                    <th wire:click="sortBy('id_events_category')" class="py-2 px-4 text-gray-900 dark:text-white cursor-pointer text-center">
                        Categorie 
                        @if ($sortField === 'id_events_category') 
                            ({{ $sortDirection === 'asc' ? '↑' : '↓' }})
                        @endif
                    </th>
                    <th class="py-2 px-4 text-gray-900 dark:text-white text-center">Subcategorii</th>
                    <th wire:click="sortBy('persons_involved')" class="py-2 px-4 text-gray-900 dark:text-white cursor-pointer text-center">
                        Persoane 
                        @if ($sortField === 'persons_involved') 
                            ({{ $sortDirection === 'asc' ? '↑' : '↓' }})
                        @endif
                    </th>
                    <th class="py-2 px-4 text-gray-900 dark:text-white text-center">Detalii</th>
                    <th class="w-[80px] py-2 px-4 text-gray-900 dark:text-white text-center">Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($events as $key => $event)
                    <tr>
                        <td class="py-2 px-4 text-gray-900 dark:text-white text-center">
                            {{ ($events->perPage() * ($events->currentPage() - 1)) + $key + 1 }}
                        </td>
                        <td class="py-2 px-4 text-gray-900 dark:text-white text-center">
                            {{ $event->data ? $event->data->format('d.m.Y') : '-' }}
                        </td>
                        <td class="py-2 px-4 text-gray-900 dark:text-white text-center">
                            {{ $event->institution->name ?? '-' }}
                        </td>
                        <td class="py-2 px-4 text-gray-900 dark:text-white text-center">
                            {{ $event->category->name ?? '-' }}
                        </td>
                        <td class="py-2 px-4 text-gray-900 dark:text-white text-center">
                            @if ($event->subcategories->isNotEmpty())
                                <ul class="list-disc pl-5">
                                    @foreach ($event->subcategories as $subcategory)
                                        <li>{{ $subcategory->name }}</li>
                                    @endforeach
                                </ul>
                            @else
                                -
                            @endif
                        </td>
                        <td class="py-2 px-4 text-gray-900 dark:text-white text-center">
                            {{ $event->persons_involved ?? '-' }}
                        </td>
                        <td class="py-2 px-4 text-gray-900 dark:text-white text-left">
                            {!! $event->events_text ?? '-' !!}
                        </td>
                        <td class="flex-row flex-wrap self-center py-2 px-2 border border-gray-300 dark:border-zinc-700 text-gray-900 dark:text-white text-center">
                            <div class="flex justify-center space-x-2">
                                @can('edit events')
                                    <button wire:click="editEvent({{ $event->id }})" 
                                                class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                @endcan
                                @can('delete events')
                                
                                <button wire:click="deleteEvent({{ $event->id }})" 
                                                class="bg-red-500 hover:bg-red-600 text-white p-2 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4M9 7h6m-4 4v6m4-6v6"></path>
                                            </svg>
                                        </button>

                                    
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-2 px-4 text-center text-gray-900 dark:text-white">Niciun eveniment găsit.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex justify-center flex-col items-center">
        <div class="mb-2 text-gray-900 dark:text-white">
          Se afișează {{ $events->firstItem() }} până la {{ $events->lastItem() }} din {{ $events->total() }} rezultate
        </div>
        <div class="flex space-x-1">
            {{ $events->links('vendor.pagination.custom') }}
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center" wire:ignore.self>
            <div class="bg-gray-100 dark:bg-zinc-900 p-6 w-1/2 text-gray-900 dark:text-white border border-gray-300 dark:border-zinc-700">
                <h2 class="text-xl mb-4">{{ $editingEventId ? 'Editează eveniment' : 'Crează eveniment' }}</h2>
                <form wire:submit.prevent="{{ $editingEventId ? 'updateEvent' : 'createEvent' }}">
                    <div class="mb-4">
                        <label class="block mb-1">Data</label>
                        <input type="date" id="data-input" wire:model="data" 
                               class="w-full border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-zinc-500">
                        @error('data') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block mb-1">Instituție</label>
                        <select wire:model="id_institution" 
                                class="w-full border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-zinc-500">
                            <option value="">Selectează o instituție</option>
                            @foreach ($institutions as $institution)
                                <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                            @endforeach
                        </select>
                        @error('id_institution') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                    <label class="block mb-1 text-sm">Categorie</label>
                    <select wire:model="id_events_category" wire:change="updateSubcategories"
                            class="w-full border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-zinc-500 text-sm">
                        <option value="">Selectează o categorie</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('id_events_category') <span class="text-red-500 text-xs">{{ $message }}</span> @endif
                </div>
                @if ($subcategories->isNotEmpty())
                    <div class="mb-3">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($subcategories as $subcategory)
                                <label class="flex items-center w-1/3">
                                    <input type="checkbox" wire:model="id_subcategory" value="{{ $subcategory->id }}"
                                           class="form-checkbox h-4 w-4 text-zinc-600">
                                    <span class="ml-2 text-sm">{{ $subcategory->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('id_subcategory') <span class="text-red-500 text-xs">{{ $message }}</span> @endif
                    </div>
                @endif
                    <div class="mb-4">
                        <label class="block mb-1">Persoane implicate</label>
                        <input type="number" wire:model="persons_involved" min="0" 
                               class="w-full border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-zinc-500">
                        @error('persons_involved') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4" wire:ignore>
                        <label class="block mb-1">Detalii</label>
                        <textarea id="tiny-editor" wire:model.debounce.500ms="events_text" 
                                  class="w-full border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded focus:outline-none focus:ring-2 focus:ring-zinc-500 min-h-[200px]"></textarea>
                        @error('events_text') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end">
                        <button type="button" wire:click="resetForm" 
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded mr-2">Anulează</button>
                        <button type="submit" 
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                            {{ $editingEventId ? 'Actualizează' : 'Crează' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <style>
        @media screen {
            table {
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #d1d5db; /* gray-300 */
                padding: 8px;
            }
            .dark th, .dark td {
                border: 1px solid #3f3f46; /* zinc-700 */
            }
            .text-left {
                text-align: left !important;
            }
            .text-center {
                text-align: center !important;
            }
        }
        @media print {
            body * {
                visibility: hidden;
            }
            table, table * {
                visibility: visible;
            }
            table {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border-collapse: collapse;
                font-size: 10pt;
            }
            th, td {
                border: 1px solid #000; /* negru pentru imprimare */
                padding: 5px;
            }
            .text-left {
                text-align: left !important;
            }
            .text-center {
                text-align: center !important;
            }
        }
    </style>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                function initializeTinyMCE() {
                    if (tinymce.get('tiny-editor')) {
                        tinymce.get('tiny-editor').remove();
                    }
                    tinymce.init({
                        selector: '#tiny-editor',
                        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
                        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                        height: 300,
                        content_style: 'body { font-family: Instrument Sans, sans-serif; } .mce-content-body { padding: 10px; }',
                        skin: 'oxide',
                        skin_url: '/js/tinymce/skins/ui/oxide',
                        content_css: '/js/tinymce/skins/content/default/content.css',
                        setup: (editor) => {
                            editor.on('init', () => {
                                editor.setContent(@json($events_text) || '');
                            });
                            editor.on('Change Input Undo Redo', () => {
                                @this.set('events_text', editor.getContent());
                            });
                        }
                    });
                }

                Livewire.on('showModal', () => {
                    setTimeout(() => {
                        initializeTinyMCE();
                    }, 100);
                });

                Livewire.on('editEvent', (eventData) => {
                    if (eventData && eventData.length > 0) {
                        const data = eventData[0].data;
                        const dateInput = document.getElementById('data-input');
                        if (dateInput && data.data) {
                            const formattedDate = new Date(data.data).toISOString().split('T')[0];
                            dateInput.value = formattedDate;
                        }
                        setTimeout(() => {
                            if (tinymce.get('tiny-editor')) {
                                tinymce.get('tiny-editor').setContent(data.events_text || '');
                            }
                        }, 500);
                    }
                });
            });
        </script>
    @endpush
</div>
@else
    <div class="text-center text-gray-900 dark:text-white">
        <p>Nu aveți permisiunea de a vizualiza evenimentele.</p>
    </div>
@endcan