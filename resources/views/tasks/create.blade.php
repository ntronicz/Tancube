@extends('layouts.app')

@section('title', 'Add Task - Tancube CRM')
@section('page-title', 'Add New Task')

@section('content')
<div class="max-w-xl mx-auto animate-fade-in">
    <div class="glass rounded-xl p-6">
        <form action="{{ route('tasks.store') }}" method="POST" class="space-y-5">
            @csrf
            
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-dark-300 mb-2">Title *</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="{{ old('title') }}"
                    required
                    class="w-full px-4 py-3 rounded-lg bg-dark-800 border border-dark-600 text-white placeholder-dark-400 focus:border-primary-500 focus:outline-none"
                    placeholder="Task title"
                >
                @error('title')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-dark-300 mb-2">Description</label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="3"
                    class="w-full px-4 py-3 rounded-lg bg-dark-800 border border-dark-600 text-white placeholder-dark-400 focus:border-primary-500 focus:outline-none resize-none"
                    placeholder="Task description..."
                >{{ old('description') }}</textarea>
            </div>
            
            <!-- Due Date & Priority -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="due_date" class="block text-sm font-medium text-dark-300 mb-2">Due Date *</label>
                    <input 
                        type="date" 
                        id="due_date" 
                        name="due_date" 
                        value="{{ old('due_date', now()->format('Y-m-d')) }}"
                        required
                        class="w-full px-4 py-3 rounded-lg bg-dark-800 border border-dark-600 text-white focus:border-primary-500 focus:outline-none"
                    >
                </div>
                
                <div>
                    <label for="priority" class="block text-sm font-medium text-dark-300 mb-2">Priority *</label>
                    <select 
                        id="priority" 
                        name="priority"
                        required
                        class="w-full px-4 py-3 rounded-lg bg-dark-800 border border-dark-600 text-white focus:border-primary-500 focus:outline-none"
                    >
                        <option value="LOW" {{ old('priority') === 'LOW' ? 'selected' : '' }}>Low</option>
                        <option value="MEDIUM" {{ old('priority', 'MEDIUM') === 'MEDIUM' ? 'selected' : '' }}>Medium</option>
                        <option value="HIGH" {{ old('priority') === 'HIGH' ? 'selected' : '' }}>High</option>
                    </select>
                </div>
            </div>
            
            <!-- Assign To -->
            <div>
                <label for="assigned_to" class="block text-sm font-medium text-dark-300 mb-2">Assign To</label>
                <select 
                    id="assigned_to" 
                    name="assigned_to"
                    class="w-full px-4 py-3 rounded-lg bg-dark-800 border border-dark-600 text-white focus:border-primary-500 focus:outline-none"
                >
                    <option value="">Unassigned</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ old('assigned_to') === $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-dark-700">
                <a href="{{ route('tasks.index') }}" class="px-6 py-2 rounded-lg bg-dark-700 hover:bg-dark-600 text-white transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-medium transition-colors">
                    Create Task
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
