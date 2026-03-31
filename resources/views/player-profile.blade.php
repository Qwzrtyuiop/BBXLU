<x-layouts.public :title="($profileUser?->nickname ?? 'Player').' / Profile'" :full-bleed="true" :hide-footer="true">
    <div class="min-h-[calc(100svh-4.5rem)] w-full bg-slate-950 px-3 py-3 sm:px-4 sm:py-4 lg:px-5">
        @include('user-dashboard.partials.profile-board')
    </div>
</x-layouts.public>
