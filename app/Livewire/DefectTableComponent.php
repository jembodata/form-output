<?php

namespace App\Livewire;

use App\Models\Defect;
use Closure;
use Filament\Forms;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class DefectTableComponent extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public static function calculateDefectRates(Set $set, Get $get): void
    {
        $outputFg = (float) $get('output_fg');
        $defect = (float) $get('defect');
        $km = (float) $get('km');

        if ($outputFg > 0) {
            $set('defect_rate_display', number_format(($defect / $outputFg) * 100, 2) . '');
            $set('km_rate_display', number_format(($km / $outputFg) * 100, 2) . '');
        } else {
            $set('defect_rate_display', '0');
            $set('km_rate_display', '0');
        }
    }

    protected function dataFormSchema(): array
    {
        return [
            // Forms\Components\DatePicker::make('tanggal')
            //     ->label('Tanggal')
            //     ->native(false)
            //     ->disabledOn('edit')
            //     ->disabledDates(function () {
            //         return \App\Models\Defect::pluck('tanggal')->toArray();
            //     })
            //     ->closeOnDateSelection()
            //     ->required()
            //     ->default(now()),

            // Forms\Components\TextInput::make('output_fg')
            //     ->label('Output FG')
            //     ->suffix('Ton Kabel')
            //     ->numeric()
            //     ->required()
            //     ->live()
            //     ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateDefectRates($set, $get)),

            // Components\Fieldset::make('Metrik Defect')
            //     ->schema([
            //         Forms\Components\TextInput::make('defect')
            //             ->label('Defect')
            //             ->suffix('Ton Kabel')
            //             ->numeric()
            //             ->live()
            //             ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateDefectRates($set, $get)),
            //         Forms\Components\TextInput::make('defect_rate_display')
            //             ->label('Defect Rate')
            //             ->suffix('%')
            //             ->disabled()
            //             ->dehydrated(false),
            //     ])->columns(2),

            // Components\Fieldset::make('Metrik Kartu Merah (KM)')
            //     ->schema([
            //         Forms\Components\TextInput::make('km')
            //             ->label('Kartu Merah')
            //             ->suffix('Ton Kabel')
            //             ->default(0)
            //             ->numeric()
            //             ->live()
            //             ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateDefectRates($set, $get)),
            //         Forms\Components\TextInput::make('km_rate_display')
            //             ->label('KM Rate')
            //             ->suffix('%')
            //             ->disabled()
            //             ->dehydrated(false),
            //     ])->columns(2),

            // Forms\Components\TextInput::make('target_dc')
            //     ->label('Target DC')
            //     ->numeric()
            //     ->default(0.65)
            //     ->suffix('%')
            //     ->required(),

            Components\Grid::make(2) // Membagi form utama menjadi 2 kolom (Kanan & Kiri)
                ->schema([

                    // ================= SEKTI KIRI: INPUT DATA =================
                    Components\Section::make('Section Input Data')
                        ->columnSpan(1)
                        ->schema([
                            Forms\Components\DatePicker::make('tanggal')
                                ->label('Tanggal')
                                ->native(false)
                                ->disabledOn('edit')
                                ->disabledDates(function () {
                                    return \App\Models\Defect::pluck('tanggal')->toArray();
                                })
                                ->closeOnDateSelection()
                                ->required()
                                ->default(now()),

                            Forms\Components\TextInput::make('output_fg')
                                ->default(0)
                                ->label('Output FG')
                                ->suffix('Ton Kabel')
                                ->numeric()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateDefectRates($set, $get)),

                            Forms\Components\TextInput::make('defect')
                                ->default(0)
                                ->label('Defect')
                                ->suffix('Ton Kabel')
                                ->numeric()
                                ->live()
                                ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateDefectRates($set, $get)),

                            Forms\Components\TextInput::make('km')
                                ->default(0)
                                ->label('Kartu Merah (KM)')
                                ->suffix('Ton Kabel')
                                ->numeric()
                                ->live()
                                ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateDefectRates($set, $get)),
                        ]),

                    // ================= SEKTI KANAN: AUTOMATIS KALKULASI =================
                    Components\Section::make('Automatis Kalkulasi')
                        ->columnSpan(1)
                        ->schema([

                            // Baris 1: Hasil Persentase Defect Rate & KM Rate Berdampingan
                            Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('defect_rate_display')
                                        ->default('0')
                                        ->label('Defect Rate')
                                        ->suffix('%')
                                        ->extraInputAttributes(['class' => 'font-bold bg-gray-50 dark:bg-gray-800'])
                                        ->disabled()
                                        ->dehydrated(false),

                                    Forms\Components\TextInput::make('km_rate_display')
                                        ->default('0')
                                        ->label('KM Rate')
                                        ->suffix('%')
                                        ->extraInputAttributes(['class' => 'font-bold bg-gray-50 dark:bg-gray-800'])
                                        ->disabled()
                                        ->dehydrated(false),
                                ]),

                            // Baris 2: Kolom Target diletakkan di bawah hasil kalkulasi
                            Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('target_dc')
                                        ->label('Target DC')
                                        ->disabled()
                                        ->numeric()
                                        ->default(0.65)
                                        ->suffix('%')
                                        ->required()
                                        ->columnSpanFull()
                                        ->extraInputAttributes(['class' => 'font-bold bg-gray-50 dark:bg-gray-800']),
                                ]),
                        ]),

                ]),

        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginationMode(PaginationMode::Simple)
            ->deferFilters(false)
            ->query(Defect::query())
            ->defaultSort('tanggal', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')->label('Tanggal')->date('d-M')->sortable(),
                Tables\Columns\TextColumn::make('output_fg')->label('Output FG (Ton)')->numeric(2),
                Tables\Columns\TextColumn::make('defect')->label('Defect (Ton)')->numeric(2),
                Tables\Columns\TextColumn::make('defect_rate')
                    ->label('Defect Rate')
                    ->state(function ($record) {
                        return $record->output_fg > 0
                            ? ($record->defect / $record->output_fg) * 100
                            : 0;
                    })
                    ->numeric(2)
                    ->suffix('%')
                    ->badge()
                    ->color(fn($record) => ($record->output_fg > 0 ? ($record->defect / $record->output_fg) * 100 : 0) > $record->target_dc ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('km')->label('KM (Ton)')->numeric(2),
                Tables\Columns\TextColumn::make('km_rate')
                    ->label('KM Rate')
                    ->state(function ($record) {
                        return $record->output_fg > 0
                            ? ($record->km / $record->output_fg) * 100
                            : 0;
                    })
                    ->numeric(2)
                    ->suffix('%')
                    ->badge()
                    ->color(fn($record) => ($record->output_fg > 0 ? ($record->km / $record->output_fg) * 100 : 0) > $record->target_dc ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('target_dc')->label('Target DC')->suffix('%'),
            ])
            ->filters([
                Filter::make('date')
                    ->label('Tanggal')
                    ->schema([
                        Forms\Components\Select::make('mode')
                            ->label('Mode Filter')
                            ->options([
                                'per_hari' => 'Per Hari',
                                'per_bulan' => 'Per Bulan',
                                'rentang' => 'Rentang Tanggal',
                            ])
                            ->native(false)
                            ->default('per_hari')
                            ->live(),

                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->closeOnDateSelection()
                            ->native(false)
                            ->live()
                            ->visible(fn(callable $get) => $get('mode') === 'per_hari'),

                        Forms\Components\DatePicker::make('month')
                            ->label('Pilih Bulan')
                            ->displayFormat('F Y')
                            ->format('Y-m-d')
                            ->closeOnDateSelection()
                            ->native()
                            ->extraInputAttributes(['type' => 'month'])
                            ->live()
                            ->visible(fn(callable $get) => $get('mode') === 'per_bulan'),

                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari')
                            ->closeOnDateSelection()
                            ->native(false)
                            ->live()
                            ->visible(fn(callable $get) => $get('mode') === 'rentang')
                            // Menerapkan Custom Rule andalanmu
                            ->rules([
                                static function (Get $get): Closure {
                                    return static function (string $attribute, $value, Closure $fail) use ($get) {
                                        $dateTo = $get('date_to');
                                        if (!empty($value) && !empty($dateTo)) {
                                            if (strtotime($value) > strtotime($dateTo)) {
                                                $fail('Tanggal awal tidak boleh melebihi tanggal akhir.');
                                            }
                                        }
                                    };
                                }
                            ])
                            ->afterStateUpdated(function (object $livewire, Forms\Components\DatePicker $component) {
                                $livewire->validateOnly($component->getStatePath());
                            }),

                        Forms\Components\DatePicker::make('date_to')
                            ->label('Sampai')
                            ->closeOnDateSelection()
                            ->native(false)
                            ->live()
                            ->visible(fn(callable $get) => $get('mode') === 'rentang')
                            ->disabled(fn(callable $get) => empty($get('date_from')))
                            ->rules([
                                static function (Get $get): Closure {
                                    return static function (string $attribute, $value, Closure $fail) use ($get) {
                                        $dateFrom = $get('date_from');
                                        if (!empty($value) && !empty($dateFrom)) {
                                            if (strtotime($value) < strtotime($dateFrom)) {
                                                $fail('Tanggal akhir tidak boleh kurang dari tanggal awal.');
                                            }
                                        }
                                    };
                                }
                            ])
                            ->afterStateUpdated(function (object $livewire, Forms\Components\DatePicker $component) {
                                $livewire->validateOnly($component->getStatePath());
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $mode = $data['mode'] ?? 'per_hari';

                        if ($mode === 'per_hari' && !empty($data['date'])) {
                            return $query->whereDate('tanggal', $data['date']);
                        }

                        if ($mode === 'per_bulan' && !empty($data['month'])) {
                            $start = \Illuminate\Support\Carbon::parse($data['month'])->startOfMonth()->toDateString();
                            $end = \Illuminate\Support\Carbon::parse($data['month'])->endOfMonth()->toDateString();
                            return $query->whereBetween('tanggal', [$start, $end]);
                        }

                        if ($mode === 'rentang') {
                            $from = $data['date_from'] ?? null;
                            $to = $data['date_to'] ?? null;

                            if ($from && $to) {
                                if (strtotime($from) > strtotime($to)) {
                                    return $query;
                                }
                                return $query->whereBetween('tanggal', [$from, $to]);
                            }

                            if ($from) {
                                return $query->whereDate('tanggal', '>=', $from);
                            }
                            if ($to) {
                                return $query->whereDate('tanggal', '<=', $to);
                            }
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $mode = $data['mode'] ?? null;

                        if ($mode === 'per_hari' && !empty($data['date'])) {
                            return 'Tanggal: ' . \Illuminate\Support\Carbon::parse($data['date'])->translatedFormat('d M Y');
                        }

                        if ($mode === 'per_bulan' && !empty($data['month'])) {
                            return 'Bulan: ' . \Illuminate\Support\Carbon::parse($data['month'])->translatedFormat('F Y');
                        }

                        if ($mode === 'rentang') {
                            $from = $data['date_from'] ?? null;
                            $to = $data['date_to'] ?? null;

                            if ($from && $to) {
                                if (strtotime($from) > strtotime($to)) {
                                    return null;
                                }
                                return 'Rentang: ' . \Illuminate\Support\Carbon::parse($from)->translatedFormat('d M Y') . ' — ' . \Illuminate\Support\Carbon::parse($to)->translatedFormat('d M Y');
                            }

                            if ($from) {
                                return 'Mulai: ' . \Illuminate\Support\Carbon::parse($from)->translatedFormat('d M Y');
                            }
                            if ($to) {
                                return 'Sampai: ' . \Illuminate\Support\Carbon::parse($to)->translatedFormat('d M Y');
                            }
                        }

                        return null;
                    })
            ], layout: FiltersLayout::AfterContent)
            ->headerActions([
                Actions\CreateAction::make()
                    ->createAnother(false)
                    ->model(Defect::class)
                    ->form($this->dataFormSchema())
                    ->slideOver(),
            ])
            ->recordActions([
                Actions\EditAction::make()
                    ->form($this->dataFormSchema())
                    ->slideOver()
                    ->mutateRecordDataUsing(function (array $data): array {
                        $output = (float) ($data['output_fg'] ?? 0);
                        $data['defect_rate_display'] = $output > 0 ? number_format(((float)($data['defect'] ?? 0) / $output) * 100, 2) . '%' : '0%';
                        $data['km_rate_display'] = $output > 0 ? number_format(((float)($data['km'] ?? 0) / $output) * 100, 2) . '%' : '0%';
                        return $data;
                    }),
                // Actions\DeleteAction::make(),
            ]);
    }

    public function render()
    {
        return view('livewire.defect-table-component');
    }
}
