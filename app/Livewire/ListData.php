<?php

namespace App\Livewire;

use App\Models\Data;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Forms;
use Filament\Actions;
use Filament\Support\RawJs;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Tables\Filters\Filter;

class ListData extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    protected function dataFormSchema(): array
    {
        return [
            Section::make('Data Utama')
                ->description('Isi data wajib harian berikut.')
                ->schema([
                    Forms\Components\DatePicker::make('date')
                        ->label('Tanggal')
                        ->default(now())
                        ->required()
                        ->format('Y-m-d')
                        ->displayFormat('d F Y')
                        ->disabledOn('edit')
                        ->disabledDates(function () {
                            return \App\Models\Data::pluck('date')->toArray();
                        })
                        ->unique(
                            table: 'data',
                            column: 'date',
                            ignoreRecord: true,
                        )
                        ->native(false)
                        ->closeOnDateSelection()
                        ->columnSpanFull(),

                    Fieldset::make('Metrik Nilai (Rupiah)')
                        ->schema([
                            Forms\Components\TextInput::make('planning')
                                ->label('Planning')
                                ->prefix('Rp')
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->numeric()
                                ->required(),

                            Forms\Components\TextInput::make('output_produksi_open')
                                ->label('Output Produksi (Open)')
                                ->prefix('Rp')
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->numeric()
                                ->required(),

                            Forms\Components\TextInput::make('output_qc_transfer')
                                ->label('Output QC (Transfer)')
                                ->prefix('Rp')
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->numeric()
                                ->required(),

                            Forms\Components\TextInput::make('under_testing')
                                ->label('Under Testing')
                                ->prefix('Rp')
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->numeric()
                                ->required(),
                        ])->columns(2),

                    Fieldset::make('Metrik Material (Ton Kabel)')
                        ->schema([
                            Forms\Components\TextInput::make('output_produksi_open_kabel')
                                ->label('Prod. (Open) Kabel')
                                ->numeric()
                                ->suffix('Ton Kabel')
                                ->required(),

                            Forms\Components\TextInput::make('output_produksi_open_cu')
                                ->label('Prod. (Open) Cu')
                                ->numeric()
                                ->suffix('Ton Kabel')
                                ->required(),

                            Forms\Components\TextInput::make('output_produksi_open_al')
                                ->label('Prod. (Open) AL')
                                ->numeric()
                                ->suffix('Ton Kabel')
                                ->required(),
                        ])->columns(3),
                ]),

            Section::make('Data Tambahan (Opsional)')
                ->description('Klik untuk mengisi metrik tambahan.')
                ->collapsed()
                ->schema([
                    Fieldset::make('Planning Tambahan')
                        ->schema([
                            Forms\Components\TextInput::make('planning_mtr')->label('Planning Mtr')->numeric(),
                            Forms\Components\TextInput::make('planning_ton_kabel')->label('Planning Ton Kabel')->numeric(),
                        ])->columns(2),

                    Fieldset::make('Output Produksi (Open) Tambahan')
                        ->schema([
                            Forms\Components\TextInput::make('output_produksi_open_mtr')->label('Output Produksi (Open) Mtr')->numeric(),
                            Forms\Components\TextInput::make('output_produksi_open_rp_ton_cu')->label('Output Produksi (Open) Rp Ton Cu')->numeric()->prefix('Rp'),
                            Forms\Components\TextInput::make('output_produksi_open_rp_ton_al')->label('Output Produksi (Open) Rp Ton AL')->numeric()->prefix('Rp'),
                        ])->columns(3),

                    Fieldset::make('Output QC (Transfer) Tambahan')
                        ->schema([
                            Forms\Components\TextInput::make('output_qc_transfer_mtr')->label('Output QC (Transfer) Mtr')->numeric(),
                            Forms\Components\TextInput::make('output_qc_transfer_ton_kabel')->label('Output QC (Transfer) Ton Kabel')->numeric(),
                            Forms\Components\TextInput::make('output_qc_transfer_cu')->label('Output QC (Transfer) Cu')->numeric(),
                            Forms\Components\TextInput::make('output_qc_transfer_al')->label('Output QC (Transfer) AL')->numeric(),
                        ])->columns(2),

                    Fieldset::make('Under Testing Tambahan')
                        ->schema([
                            Forms\Components\TextInput::make('under_testing_mtr')->label('Under Testing Mtr')->numeric(),
                            Forms\Components\TextInput::make('under_testing_ton_kabel')->label('Under Testing Ton Kabel')->numeric(),
                            Forms\Components\TextInput::make('undertesting_open_ton_cu')->label('Undertesting (Open) Ton Cu')->numeric(),
                            Forms\Components\TextInput::make('under_testing_open_ton_al')->label('Under Testing (Open) Ton AL')->numeric(),
                        ])->columns(2),

                    Section::make('Persentase Pencapaian (%)')
                        ->description('Nilai pada kolom ini akan dihitung otomatis oleh sistem setelah data disimpan.')
                        ->schema([
                            Forms\Components\TextInput::make('pct_output_produksi_vs_planning_mtr')
                                ->label('% Output Produksi vs Planning Mtr')
                                ->numeric()
                                ->suffix('%')
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('pct_output_produksi_vs_planning_ton_kabel')
                                ->label('% Output Produksi vs Planning Ton Kabel')
                                ->numeric()
                                ->suffix('%')
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('pct_output_produksi_vs_planning_rp')
                                ->label('% Output Produksi vs Planning Rp.')
                                ->numeric()
                                ->suffix('%')
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('pct_output_qc_vs_output_produksi_mtr')
                                ->label('% Output QC vs Output Produksi Mtr')
                                ->numeric()
                                ->suffix('%')
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('pct_output_qc_vs_output_produksi_ton_kabel')
                                ->label('% Output QC vs Output Produksi Ton Kabel')
                                ->numeric()
                                ->suffix('%')
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('pct_output_qc_vs_output_produksi_rp')
                                ->label('% Output QC vs Output Produksi Rp.')
                                ->numeric()
                                ->suffix('%')
                                ->disabled()
                                ->dehydrated(false),
                        ])->columns(3),
                ]),
        ];
    }

    protected function calculatePercentages(array $data, Data $record = null): array
    {
        $calc = function ($numerator, $denominator) {
            if (empty($denominator) || $denominator == 0) {
                return 0;
            }
            return ($numerator / $denominator) * 100;
        };

        // Ambil dari input form ($data), jika tidak ada/kosong ambil dari database ($record)
        $get = fn($key) => $data[$key] ?? ($record ? $record->{$key} : 0);

        $data['pct_output_produksi_vs_planning_mtr'] = $calc($get('output_produksi_open_mtr'), $get('planning_mtr'));
        $data['pct_output_produksi_vs_planning_ton_kabel'] = $calc($get('output_produksi_open_kabel'), $get('planning_ton_kabel'));
        $data['pct_output_produksi_vs_planning_rp'] = $calc($get('output_produksi_open'), $get('planning'));

        $data['pct_output_qc_vs_output_produksi_mtr'] = $calc($get('output_qc_transfer_mtr'), $get('output_produksi_open_mtr'));
        $data['pct_output_qc_vs_output_produksi_rp'] = $calc($get('output_qc_transfer'), $get('output_produksi_open'));

        // PERBAIKAN LOGIKA: Sebelumnya terbalik (Produksi / QC), yang benar adalah (QC / Produksi)
        $data['pct_output_qc_vs_output_produksi_ton_kabel'] = $calc($get('output_qc_transfer_ton_kabel'), $get('output_produksi_open_kabel'));

        return $data;
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferFilters(false)
            ->query(Data::query())
            ->defaultSort('created_at', direction: 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('planning')
                    ->label('Planning')
                    ->numeric()
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('output_produksi_open')
                    ->label('Output Produksi (Open)')
                    ->numeric()
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('output_qc_transfer')
                    ->label('Output QC (Transfer)')
                    ->numeric()
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('under_testing')
                    ->label('Under Testing')
                    ->numeric()
                    ->money('IDR', locale: 'id', decimalPlaces: 0)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('output_produksi_open_kabel')
                    ->label('Prod. Kabel')
                    ->numeric()
                    ->suffix(' Ton')
                    ->toggleable(),
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
                            ->reactive(),

                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->closeOnDateSelection()
                            ->native(false)
                            ->reactive()
                            ->visible(fn(callable $get) => $get('mode') === 'per_hari'),

                        Forms\Components\DatePicker::make('month')
                            ->label('Pilih Bulan')
                            ->displayFormat('F Y')
                            ->format('Y-m-d')
                            ->closeOnDateSelection()
                            ->native()
                            ->extraInputAttributes(['type' => 'month'])
                            ->reactive()
                            ->visible(fn(callable $get) => $get('mode') === 'per_bulan'),

                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari')
                            ->closeOnDateSelection()
                            ->native(false)
                            ->reactive()
                            ->visible(fn(callable $get) => $get('mode') === 'rentang'),

                        Forms\Components\DatePicker::make('date_to')
                            ->label('Sampai')
                            ->closeOnDateSelection()
                            ->native(false)
                            ->reactive()
                            ->visible(fn(callable $get) => $get('mode') === 'rentang'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $mode = $data['mode'] ?? 'per_hari';

                        if ($mode === 'per_hari' && !empty($data['date'])) {
                            return $query->whereDate('date', $data['date']);
                        }

                        if ($mode === 'per_bulan' && !empty($data['month'])) {
                            $start = Carbon::parse($data['month'])->startOfMonth()->toDateString();
                            $end = Carbon::parse($data['month'])->endOfMonth()->toDateString();
                            return $query->whereBetween('date', [$start, $end]);
                        }

                        if ($mode === 'rentang') {
                            $from = $data['date_from'] ?? null;
                            $to = $data['date_to'] ?? null;

                            if ($from && $to) {
                                return $query->whereBetween('date', [$from, $to]);
                            }
                            if ($from) {
                                return $query->whereDate('date', '>=', $from);
                            }
                            if ($to) {
                                return $query->whereDate('date', '<=', $to);
                            }
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $mode = $data['mode'] ?? null;

                        if ($mode === 'per_hari' && !empty($data['date'])) {
                            return 'Tanggal: ' . Carbon::parse($data['date'])->translatedFormat('d M Y');
                        }

                        if ($mode === 'per_bulan' && !empty($data['month'])) {
                            return 'Bulan: ' . Carbon::parse($data['month'])->translatedFormat('F Y');
                        }

                        if ($mode === 'rentang' && !empty($data['date_from']) && !empty($data['date_to'])) {
                            return 'Rentang: ' . Carbon::parse($data['date_from'])->translatedFormat('d M Y') . ' — ' . Carbon::parse($data['date_to'])->translatedFormat('d M Y');
                        }

                        return null;
                    }),
                // Filter::make('date')
                //     ->label('Tanggal')
                //     ->indicator('Administrators')
                //     ->schema([
                //         Forms\Components\DatePicker::make('date')
                //             ->label('Tanggal')
                //             ->closeOnDateSelection()
                //             ->native(false),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['date'] ?? null,
                //                 fn(Builder $query, $date): Builder =>
                //                 $query->whereDate('date', $date),
                //             );
                //     })
                //     ->indicateUsing(function (array $data): ?string {
                //         if (!($data['date'] ?? null)) {
                //             return null;
                //         }

                //         return 'Tanggal: ' . Carbon::parse($data['date'])->translatedFormat('d M Y');
                //     }),
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->createAnother(false)
                    ->schema($this->dataFormSchema())
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Kalkulasi otomatis saat Create
                        return $this->calculatePercentages($data);
                    }),
            ])
            ->recordActions([
                Actions\ViewAction::make()
                    ->schema($this->dataFormSchema())
                    ->slideOver(),
                Actions\EditAction::make()
                    ->schema($this->dataFormSchema())
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data, Data $record): array {
                        // Kalkulasi ulang otomatis saat Edit
                        return $this->calculatePercentages($data, $record);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.list-data');
    }
}
