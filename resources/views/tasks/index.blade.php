@extends('layouts.app')

@section('title', 'Tasks - Tancube CRM')

@section('content')
<div class="animate-fade-in pb-24">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-900">My Tasks</h1>
        <button @click="$dispatch('open-add-task-modal')" 
           class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors flex items-center shadow-lg shadow-indigo-200">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            New Task
        </button>
    </div>

    <!-- Tasks List (Cards) -->
    <div class="space-y-3">
        @forelse($tasks as $task)
        <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
            <!-- Priority Indicator (Right Side) -->
            @php
                $priorityClasses = [
                    'HIGH' => 'bg-red-50 text-red-600 border-red-100',
                    'MEDIUM' => 'bg-amber-50 text-amber-600 border-amber-100',
                    'LOW' => 'bg-green-50 text-green-600 border-green-100',
                ];
                $priorityClass = $priorityClasses[$task->priority] ?? 'bg-slate-50 text-slate-600 border-slate-100';
            @endphp
            <div class="absolute top-4 right-4">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border {{ $priorityClass }}">
                    {{ $task->priority }}
                </span>
            </div>

            <div class="flex items-start gap-4 pr-16">
                <!-- Checkbox (Circle) -->
                <form action="{{ route('tasks.toggle', $task->id) }}" method="POST" class="pt-1">
                    @csrf
                    <button type="submit" 
                        class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200 {{ $task->status === 'COMPLETED' ? 'bg-green-500 border-green-500' : 'border-slate-300 hover:border-indigo-500' }}">
                        @if($task->status === 'COMPLETED')
                            <i data-lucide="check" class="w-3.5 h-3.5 text-white"></i>
                        @endif
                    </button>
                </form>

                <div class="flex-1">
                    <h3 class="text-base font-semibold text-slate-900 {{ $task->status === 'COMPLETED' ? 'line-through text-slate-400' : '' }}">
                        {{ $task->title }}
                    </h3>
                    
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-2 text-xs">
                        <!-- Due Date -->
                         @php
                            $isOverdue = $task->isOverdue();
                            $isDue = \Carbon\Carbon::parse($task->due_date)->isToday();
                            $dateColor = $isOverdue ? 'text-red-500 font-medium' : ($isDue ? 'text-amber-500 font-medium' : 'text-slate-500');
                        @endphp
                        <span class="flex items-center {{ $dateColor }}">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 mr-1.5"></i>
                            {{ \Carbon\Carbon::parse($task->due_date)->format('M d, h:i A') }}
                            @if($isOverdue) <span class="ml-1 text-[10px] font-bold uppercase tracking-wide text-red-500">Overdue</span> @endif
                        </span>

                        <!-- Assignee -->
                        @if($task->assignedTo)
                        <span class="flex items-center text-slate-500 bg-slate-50 px-2 py-0.5 rounded border border-slate-100">
                            <span class="font-medium text-slate-400 mr-1">TO</span>
                            <i data-lucide="arrow-right" class="w-3 h-3 mx-0.5 text-slate-300"></i>
                            <span class="font-medium text-indigo-600">{{ $task->assignedTo->name }}</span>
                        </span>
                        @endif
                        
                        <!-- Creator (Optional, small) -->
                         <span class="text-slate-400 flex items-center">
                            {{ $task->createdBy->name ?? 'Unknown' }} <i data-lucide="arrow-right" class="w-3 h-3 mx-1"></i> {{ $task->assignedTo->name ?? 'Unassigned' }}
                         </span>
                    </div>
                </div>
            </div>

            <!-- Delete Button (Bottom Right of Card - absolute or flex?) -->
            <!-- Positioning it absolutely at top right next to priority or bottom right. Top right has priority. Let's put it next to priority or simple 'X' on top right. 
                 User asked for "task delete button". Standard is trash icon. 
                 Let's place it next to the priority badge or in a flex row at the top.
            -->
            <div class="absolute bottom-4 right-4">
                @if(auth()->user()->role === 'SUPER_ADMIN' || auth()->user()->role === 'ADMIN' || $task->created_by === auth()->id())
                 <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Delete this task?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-slate-300 hover:text-red-500 transition-colors p-1" title="Delete Task">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-12 bg-white rounded-xl border border-dashed border-slate-200">
            <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                <i data-lucide="check-square" class="w-6 h-6 text-slate-400"></i>
            </div>
            <p class="text-sm text-slate-500">No tasks found. Create one to get started!</p>
            <button @click="openModal()" class="mt-4 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors inline-flex items-center">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Create Task
            </button>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $tasks->appends(request()->query())->links() }}
    </div>
</div>
@endsection
