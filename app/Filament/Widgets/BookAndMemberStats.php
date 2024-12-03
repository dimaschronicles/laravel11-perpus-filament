<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookAndMemberStats extends BaseWidget
{
    protected function getColumns(): int
    {
        return 3; // Menentukan jumlah kolom
    }

    protected function getStats(): array
    {
        $bookCount = Book::count();
        $memberCount = Member::count();
        $loanCount = Loan::where('status', 'borrowed')->count();

        return [
            Stat::make('Total Books', $bookCount),
            Stat::make('Total Members', $memberCount),
            Stat::make('Total Loan', $loanCount),
        ];
    }
}
