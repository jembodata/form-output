<?php

namespace App\Livewire;

use App\Models\Scrap;
use Closure;
use Carbon\Carbon;
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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ScrapTableComponent extends Component implements HasActions, HasSchemas, HasTable
{

    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public static function calculateScrapRate(Set $set, Get $get): void
    {
        $outputFg = (float) $get('output_fg');
        $scrap = (float) $get('scrap');

        if ($outputFg > 0) {
            $rate = ($scrap / $outputFg) * 100;
            $set('scrap_rate_display', number_format($rate, 2) . '');
        } else {
            $set('scrap_rate_display', '0');
        }
    }

    protected function dataFormSchema(): array
    {
        return [
            Components\Grid::make(2)
                ->schema([

                    Components\Section::make('Section Input Data')
                        // ->description('Input tanggal dan total output utama harian.')
                        ->columnSpan(1)
                        ->schema([
                            Forms\Components\DatePicker::make('tanggal')
                                ->label('Tanggal')
                                ->disabledOn('edit')
                                ->disabledDates(function () {
                                    return \App\Models\Scrap::pluck('tanggal')->toArray();
                                })
                                ->required()
                                ->native(false)
                                ->default(now())
                                ->unique(table: 'scraps', column: 'tanggal', ignoreRecord: true),

                            Forms\Components\TextInput::make('output_fg')
                                ->default(0)
                                ->label('Output FG')
                                ->suffix('Ton Kabel')
                                ->numeric()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateScrapRate($set, $get)),
                            Forms\Components\TextInput::make('scrap')
                                ->default(0)
                                ->label('Scrap')
                                ->suffix('Ton Kabel')
                                ->numeric()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateScrapRate($set, $get)),
                        ]),

                    Components\Section::make('Automatis Kalkulasi')
                        // ->description('Kalkulasi real-time persentase limbah scrap.')
                        ->columnSpan(1)
                        ->schema([
                            Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('scrap_rate_display')
                                        ->label('Scrap Rate')
                                        ->extraInputAttributes(['class' => 'font-bold text-primary-600 bg-gray-50 dark:bg-gray-800']) // Membuat teks hasil auto sedikit menonjol
                                        ->disabled()
                                        ->default('0,00')
                                        ->suffix('%')
                                        ->dehydrated(false),

                                    Forms\Components\TextInput::make('target_scrap')
                                        ->label('Target Scrap')
                                        ->extraInputAttributes(['class' => 'font-bold text-primary-600 bg-gray-50 dark:bg-gray-800'])
                                        ->numeric()
                                        ->disabled()
                                        ->default(3.98)
                                        ->suffix('%')
                                        ->required(),
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
            ->query(Scrap::query())
            ->defaultSort('tanggal', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')->label('Tanggal')->date('d-M')->sortable(),
                Tables\Columns\TextColumn::make('output_fg')->label('Output FG (Ton)')->numeric(2),
                Tables\Columns\TextColumn::make('scrap')->label('Scrap (Ton)')->numeric(2),
                Tables\Columns\TextColumn::make('scrap_rate')
                    ->label('Scrap Rate')
                    ->state(function ($record) {
                        // Hitung dinamis langsung di tingkat tabel
                        return $record->output_fg > 0
                            ? ($record->scrap / $record->output_fg) * 100
                            : 0;
                    })
                    ->numeric(2)
                    ->suffix('%')
                    ->badge()
                    ->color(fn($record) => ($record->output_fg > 0 ? ($record->scrap / $record->output_fg) * 100 : 0) > $record->target_scrap ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('target_scrap')->label('Target')->suffix('%'),
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
                    ->model(Scrap::class)
                    ->form($this->dataFormSchema())
                    ->slideOver(),
            ])
            ->recordActions([
                Actions\EditAction::make()
                    ->form($this->dataFormSchema())
                    ->slideOver()
                    ->mutateRecordDataUsing(function (array $data): array {
                        $output = (float) ($data['output_fg'] ?? 0);
                        $scrap = (float) ($data['scrap'] ?? 0);
                        $data['scrap_rate_display'] = $output > 0 ? number_format(($scrap / $output) * 100, 2) . '%' : '0%';
                        return $data;
                    }),
                // Actions\DeleteAction::make(),
            ]);
    }

    public function render()
    {
        return view('livewire.scrap-table-component');
    }
}
