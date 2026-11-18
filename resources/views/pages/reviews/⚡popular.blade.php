<?php

use App\Models\Review;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;

new #[Title('Popular Reviews |')] class extends Component
{
    use WithPagination;

    public string $period = 'all';

    #[Computed]
    public function reviews(): LengthAwarePaginator
    {
        $since = match($this->period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => null,
        };

        return Review::query()
            ->when($since, fn($q, $date) => $q->where('created_at', '>=', $date))
            ->with(['user', 'show'])
            ->where('likes_count', '>', 0)
            ->orderByDesc('likes_count')
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    public function updated(string $property): void
    {
        if ($property === 'period') {
            $this->resetPage();
        }
    }
};
?>

<div>
    {{-- Simplicity is the consequence of refined emotions. - Jean D'Alembert --}}
</div>