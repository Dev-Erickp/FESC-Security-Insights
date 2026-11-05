<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-zinc-950">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="relative hidden h-full flex-col overflow-hidden p-10 text-white lg:flex">
                <div class="absolute inset-0 bg-linear-to-br from-[#0b2e3a] via-[#0f4c5c] to-[#134e4a]"></div>
                <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 20%, rgba(94,234,212,.25), transparent 40%), radial-gradient(circle at 80% 80%, rgba(14,116,144,.35), transparent 45%);"></div>

                <a href="{{ route('home') }}" class="relative z-20 flex items-center gap-3 text-lg font-semibold tracking-tight" wire:navigate>
                    <span class="flex size-10 items-center justify-center rounded-md bg-white/10 ring-1 ring-white/20">
                        <x-app-logo-icon class="size-6 text-teal-200" />
                    </span>
                    {{ config('app.name', 'FESC Security Insights') }}
                </a>

                <div class="relative z-20 mt-auto space-y-4">
                    <flux:heading size="xl" class="max-w-md text-white">
                        {{ __('Diagnósticos y seguimiento de seguridad de la información con rigor institucional.') }}
                    </flux:heading>
                    <flux:text class="max-w-md text-teal-100/90">
                        {{ __('Plataforma multiempresa de FESC para evaluar controles, evidencias y hallazgos con trazabilidad.') }}
                    </flux:text>
                </div>
            </div>

            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[370px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex size-10 items-center justify-center rounded-md bg-[#0f4c5c] text-white">
                            <x-app-logo-icon class="size-6" />
                        </span>
                        <span class="text-sm text-zinc-700 dark:text-zinc-200">{{ config('app.name', 'FESC Security Insights') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
