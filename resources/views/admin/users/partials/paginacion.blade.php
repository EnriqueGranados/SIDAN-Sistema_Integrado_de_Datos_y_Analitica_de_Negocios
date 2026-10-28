{{-- Paginación de PostgreSQL; conserva los parámetros de la consulta. --}}
@if ($users->total() > 0)
    <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
        <p class="text-sm text-slate-600 dark:text-slate-400">
            Mostrando {{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ $users->total() }} usuarios
        </p>
        @if ($users->hasPages())
            <nav class="flex flex-wrap items-center gap-1.5" aria-label="Paginación de usuarios">
                @if ($users->onFirstPage())
                    <span aria-disabled="true" class="cursor-not-allowed rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-400 opacity-50 dark:border-white/10">« Primera</span>
                    <span aria-disabled="true" class="cursor-not-allowed rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-400 opacity-50 dark:border-white/10">Anterior</span>
                @else
                    <a href="{{ $users->url(1) }}" aria-label="Ir a la primera página" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 transition hover:border-sidan-500 hover:text-sidan-600 dark:border-white/10 dark:text-slate-300">« Primera</a>
                    <a href="{{ $users->previousPageUrl() }}" rel="prev" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 transition hover:border-sidan-500 hover:text-sidan-600 dark:border-white/10 dark:text-slate-300">Anterior</a>
                @endif

                @php
                    $primera = max(1, min($users->currentPage() - 2, $users->lastPage() - 4));
                    $ultima = min($users->lastPage(), $primera + 4);
                @endphp
                @if ($primera > 1)
                    <a href="{{ $users->url(1) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 dark:border-white/10 dark:text-slate-300">1</a>
                    @if ($primera > 2) <span class="px-1 text-slate-400">…</span> @endif
                @endif
                @for ($pagina = $primera; $pagina <= $ultima; $pagina++)
                    <a href="{{ $users->url($pagina) }}"
                        @if ($pagina === $users->currentPage()) aria-current="page" @endif
                        class="min-w-9 rounded-lg border px-2.5 py-2 text-center text-sm font-semibold transition {{ $pagina === $users->currentPage() ? 'border-sidan-500 bg-sidan-500 text-white' : 'border-slate-200 text-slate-600 hover:border-sidan-500 hover:text-sidan-600 dark:border-white/10 dark:text-slate-300' }}">{{ $pagina }}</a>
                @endfor
                @if ($ultima < $users->lastPage())
                    @if ($ultima < $users->lastPage() - 1) <span class="px-1 text-slate-400">…</span> @endif
                    <a href="{{ $users->url($users->lastPage()) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 dark:border-white/10 dark:text-slate-300">{{ $users->lastPage() }}</a>
                @endif

                @if ($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" rel="next" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 transition hover:border-sidan-500 hover:text-sidan-600 dark:border-white/10 dark:text-slate-300">Siguiente</a>
                    <a href="{{ $users->url($users->lastPage()) }}" aria-label="Ir a la última página" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 transition hover:border-sidan-500 hover:text-sidan-600 dark:border-white/10 dark:text-slate-300">Última »</a>
                @else
                    <span aria-disabled="true" class="cursor-not-allowed rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-400 opacity-50 dark:border-white/10">Siguiente</span>
                    <span aria-disabled="true" class="cursor-not-allowed rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-400 opacity-50 dark:border-white/10">Última »</span>
                @endif
                <span class="ml-1 whitespace-nowrap px-2 text-xs text-slate-500 dark:text-slate-400">Página {{ $users->currentPage() }} de {{ $users->lastPage() }}</span>
            </nav>
        @endif
    </div>
@endif
