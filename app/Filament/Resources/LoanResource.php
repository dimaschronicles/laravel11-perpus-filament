<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanResource\Pages;
use App\Filament\Resources\LoanResource\RelationManagers;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?string $navigationIcon = 'heroicon-c-arrows-right-left';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('member_id')
                    ->options(Member::pluck('name', 'id')->toArray())
                    ->label('Member')
                    ->searchable()
                    ->columnSpanFull(),
                Select::make('book_id')
                    ->required()
                    ->label('Book')
                    ->relationship('book', 'title')
                    ->options(
                        Book::all()->mapWithKeys(function ($book) {
                            return [$book->id => $book->title . ' (Stock: ' . $book->stock . ')'];
                        })
                    )
                    ->searchable()
                    ->columnSpanFull(),
                TextInput::make('loan_date')->default(Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'))
                    ->readOnly(),
                TextInput::make('due_date')->default(Carbon::now('Asia/Jakarta')->addDays(3)->format('Y-m-d H:i:s'))
                    ->readOnly(),
                Textarea::make('notes')->columnSpanFull()->rows(3),
                Hidden::make('status')->default('borrowed'),
                Hidden::make('user_id')->default(auth()->user()->id),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name')->searchable()->sortable(),
                TextColumn::make('book.title')->searchable(),
                TextColumn::make('loan_date')->searchable(),
                TextColumn::make('due_date')->searchable(),
                TextColumn::make('return_date')->searchable()
                    ->formatStateUsing(fn($state) => $state ? $state : '-'),
                TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'borrowed' => 'info',
                        'returned' => 'success',
                        'late' => 'danger',
                        default => 'secondary',
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('loan_date')
                    ->form([
                        Forms\Components\DatePicker::make('loan_date_from')
                            ->label('Loan Date From'),
                        Forms\Components\DatePicker::make('loan_date_to')
                            ->label('Loan Date To'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['loan_date_from'], fn($query, $date) => $query->where('loan_date', '>=', $date))
                            ->when($data['loan_date_to'], fn($query, $date) => $query->where('loan_date', '<=', $date));
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'borrowed' => 'Borrowed',
                        'returned' => 'Returned',
                        'late' => 'Late',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('return')
                    ->label('Return')
                    ->action(function ($record) {
                        $now = Carbon::now('Asia/Jakarta');
                        $status = $now->greaterThan($record->due_date) ? 'late' : 'returned';

                        $record->update([
                            'status' => $status,
                            'return_date' => $now,
                        ]);

                        // Kembalikan stok buku
                        $record->book->increment('stock');
                    })
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-o-backward')
                    ->hidden(fn($record) => $record->status !== 'borrowed'),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoans::route('/'),
            'create' => Pages\CreateLoan::route('/create'),
            'edit' => Pages\EditLoan::route('/{record}/edit'),
        ];
    }
}
