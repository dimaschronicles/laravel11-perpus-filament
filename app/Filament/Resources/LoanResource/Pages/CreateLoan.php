<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use App\Models\Book;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLoan extends CreateRecord
{
    protected static string $resource = LoanResource::class;

    protected function afterCreate(): void
    {
        // Ambil data buku berdasarkan ID yang dipilih
        $book = Book::find($this->record->book_id);

        if ($book) {
            // Kurangi stok buku
            $book->decrement('stock');
        }
    }
}
