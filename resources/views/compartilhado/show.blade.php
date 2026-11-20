<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head', ['title' => $titulo])
        <meta name="robots" content="noindex, nofollow">
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <main class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-6 md:p-10">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-medium">
                <x-app-logo-icon class="size-7 fill-current text-black dark:text-white" />
                <span>{{ config('app.name') }}</span>
            </a>

            <flux:heading size="xl" level="1">{{ $titulo }}</flux:heading>

            <ul class="flex flex-col gap-4">
                @forelse ($paginas as $pagina)
                    <li class="flex flex-col gap-1 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <a href="{{ $pagina->ds_url }}" target="_blank" rel="noopener noreferrer nofollow" class="font-medium underline">
                            {{ $pagina->ds_titulo }}
                        </a>
                        <flux:text size="sm">{{ $pagina->fonte->nm_fonte }}</flux:text>
                        @if ($pagina->ds_resumo)
                            <flux:text>{{ $pagina->ds_resumo }}</flux:text>
                        @endif
                    </li>
                @empty
                    <flux:text>{{ __('Este grupo ainda não tem favoritos.') }}</flux:text>
                @endforelse
            </ul>
        </main>

        @fluxScripts
    </body>
</html>
