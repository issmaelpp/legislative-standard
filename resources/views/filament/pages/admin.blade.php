<x-filament-panels::page>
    <div class="grid gap-4 md:gap-6 lg:gap-8">
        {{-- Aquí puedes agregar tus widgets y contenido personalizado --}}
        <x-filament-widgets::widgets
            :widgets="$this->getWidgets()"
            :columns="$this->getColumns()"
        />
    </div>
</x-filament-panels::page>