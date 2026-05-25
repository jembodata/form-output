<?php

namespace App\Livewire;

use App\Models\Backorder;
use Closure;
use Filament\Forms;
use Filament\Tables;
use Filament\Actions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Components;
use Filament\Support\RawJs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\PaginationMode;
use Livewire\Component;

class BackorderData extends Component implements HasForms, HasTable, HasActions
{

    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    protected function dataFormSchema(): array
    {
        return [
            Components\Section::make('Data Laporan Backorder')
                ->description('Masukkan nilai nominal tanpa tanda titik (contoh: 1500000).')
                ->schema([
                    Forms\Components\DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->default(now())
                        ->required()
                        ->format('Y-m-d')
                        ->displayFormat('d F Y')
                        ->disabledOn('edit')
                        ->disabledDates(function () {
                            return \App\Models\Backorder::pluck('tanggal')->toArray();
                        })
                        ->unique(
                            table: 'backorders',
                            column: 'tanggal',
                            ignoreRecord: true,
                        )
                        ->native(false)
                        ->closeOnDateSelection()
                        ->columnSpanFull(),

                    Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('total_os')
                                ->label('Total OS')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->required(),

                            Forms\Components\TextInput::make('penerimaan_po_so')
                                ->label('Penerimaan PO-SO')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->required(),

                            Forms\Components\TextInput::make('penjualan')
                                ->label('Penjualan')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->required(),

                            Forms\Components\TextInput::make('penerimaan_um')
                                ->label('Penerimaan - UM')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->required(),

                            Forms\Components\TextInput::make('lpk')
                                ->label('LPK')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->required(),
                        ]),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginationMode(PaginationMode::Simple)
            ->query(Backorder::query())
            ->deferFilters(false)
            ->defaultSort('tanggal', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_os')
                    ->label('Total OS')
                    ->money('IDR', locale: 'id', decimalPlaces: 0),

                Tables\Columns\TextColumn::make('penerimaan_po_so')
                    ->label('Penerimaan PO-SO')
                    ->money('IDR', locale: 'id', decimalPlaces: 0),

                Tables\Columns\TextColumn::make('penjualan')
                    ->label('Penjualan')
                    ->money('IDR', locale: 'id', decimalPlaces: 0),

                Tables\Columns\TextColumn::make('penerimaan_um')
                    ->label('Penerimaan UM')
                    ->money('IDR', locale: 'id', decimalPlaces: 0),

                Tables\Columns\TextColumn::make('lpk')
                    ->label('LPK')
                    ->money('IDR', locale: 'id', decimalPlaces: 0),
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
                    ->label('Tambah Data')
                    ->model(Backorder::class)
                    ->form($this->dataFormSchema())
                    ->slideOver(),
            ])
            ->recordActions([
                Actions\EditAction::make()
                    ->form($this->dataFormSchema())
                    ->slideOver(),
                Actions\DeleteAction::make(),
            ]);
    }

    public function render()
    {
        return view('livewire.backorder-data')
            ->layout('components.layouts.app');
    }
}
