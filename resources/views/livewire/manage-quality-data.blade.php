<x-filament::section>
    <x-slot name="heading">
        Isi data
    </x-slot>

    <x-slot name="description">
        . . . . . . .
    </x-slot>

    <div class="space-y-12">

        <div class="space-y-4">
            <div class="border-b border-gray-200 pb-2">
                <h3 class="text-xl font-bold tracking-tight text-gray-900">
                    Laporan Harian Scrap Produksi
                </h3>
            </div>

            @livewire('scrap-table-component')
        </div>

        <div class="space-y-4">
            <div class="border-b border-gray-200 pb-2">
                <h3 class="text-xl font-bold tracking-tight text-gray-900">
                    Laporan Harian Defect & Kartu Merah
                </h3>
            </div>

            @livewire('defect-table-component')
        </div>

    </div>
</x-filament::section>
