<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Filament\Resources\BookResource\RelationManagers;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use Filament\Forms;
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

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->required()->columnSpanFull(),
                TextInput::make('isbn')->required()->maxLength('32')->label('ISBN'),
                TextInput::make('year_published')->required()->numeric()->label('Year Published'),
                TextInput::make('stock')->required()->numeric(),
                Select::make('category_id')
                    ->options(Category::pluck('name', 'id')->toArray())
                    ->label('Category')
                    ->searchable(),
                Select::make('author_id')
                    ->options(Author::pluck('name', 'id')->toArray())
                    ->label('Author')
                    ->searchable(),
                Select::make('publisher_id')
                    ->options(Publisher::pluck('name', 'id')->toArray())
                    ->label('Publisher')
                    ->searchable(),
                Textarea::make('desc')->required()->label('Description')->columnSpanFull()->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('isbn')->searchable()->label('ISBN'),
                TextColumn::make('year_published')->searchable(),
                TextColumn::make('stock')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
