<x-layouts::app :title="__('Shows')">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Shows') }}</flux:heading>
            <flux:subheading>{{ trans_choice(':count show found|:count shows found', $shows->total(), ['count' => $shows->total()]) }}</flux:subheading>
        </div>

        <form method="GET" action="{{ route('shows.index') }}">
            <flux:select name="genre" onchange="this.form.submit()">
                <flux:select.option value="">{{ __('All genres') }}</flux:select.option>
                @foreach ($genres as $genre)
                    <flux:select.option value="{{ $genre->id }}" :selected="request('genre') == $genre->id">
                        {{ $genre->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </form>
    </div>

    @if ($shows->isEmpty())
        <flux:text class="text-center">{{ __('No shows found.') }}</flux:text>
    @else
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @foreach ($shows as $show)
                <x-poster-card
                    :poster="$show->poster_path ? 'https://image.tmdb.org/t/p/w500'.$show->poster_path : null"
                    :title="$show->name"
                    :rating="$show->average_rating"
                />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $shows->links() }}
        </div>
    @endif
</x-layouts::app>