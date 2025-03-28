<div class="flex space-x-1">
    @if ($paginator->onFirstPage())
        <span class="border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded cursor-not-allowed">&laquo; Anterior</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded hover:bg-zinc-700">&laquo; Anterior</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-700 text-gray-900 dark:text-white p-2 rounded">{{ $element }}</span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-700 text-gray-900 dark:text-white p-2 rounded">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded hover:bg-zinc-700">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded hover:bg-zinc-700">Înainte &raquo;</a>
    @else
        <span class="border border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800 text-gray-900 dark:text-white p-2 rounded cursor-not-allowed">Înainte &raquo;</span>
    @endif
</div>
