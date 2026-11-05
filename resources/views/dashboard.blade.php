<x-layouts::app :title="__('Inicio')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Bienvenido a FESC Security Insights') }}</flux:heading>
            <flux:text class="mt-2 max-w-2xl">
                {{ __('La plataforma está lista para autenticación, roles, trazabilidad y la incorporación progresiva de módulos de seguridad de la información.') }}
            </flux:text>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:heading size="sm">{{ __('Estado actual') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Fundamentos instalados: autenticación Fortify, Livewire, Tailwind, Spatie Permission y Activitylog.') }}
                </flux:text>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:heading size="sm">{{ __('Próximos dominios') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Organizaciones, marcos de control, evaluaciones, evidencias, hallazgos e informes se incorporarán por fases.') }}
                </flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
