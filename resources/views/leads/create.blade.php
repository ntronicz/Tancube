@extends('layouts.app')

@section('title', 'Add Lead - Tancube CRM')
@section('page-title', 'Add New Lead')

@section('content')
<div class="max-w-2xl mx-auto animate-fade-in">
    <div class="glass rounded-xl p-6">
        <form action="{{ route('leads.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-dark-300 mb-2">Name *</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name') }}"
                    required
                    class="w-full px-4 py-3 rounded-lg bg-dark-800 border border-dark-600 text-white placeholder-dark-400 focus:border-primary-500 focus:outline-none"
                    placeholder="Lead name"
                >
                @error('name')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Phone & Email -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-sm font-medium text-dark-300 mb-2">Phone</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        value="{{ old('phone') }}"
                        class="w-full px-4 py-3 rounded-lg bg-dark-800 border border-dark-600 text-white placeholder-dark-400 focus:border-primary-500 focus:outline-none"
                        placeholder="+91 XXXXX XXXXX"
                    >
                    @error('phone')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-dark-300 mb-2">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        class="w-full px-4 py-3 rounded-lg bg-dark-800 border border-dark-600 text-white placeholder-dark-400 focus:border-primary-500 focus:outline-none"
                        placeholder="email@example.com"
                    >
                    @error('email')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Source & Course -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="source" class="block text-sm font-medium text-dark-300 mb-2">Source</label>
                    <select 
                        id="source" 
                        name="source"
                        class="w-full px-4 py-3 rounded-lg bg-dark-800 border border-dark-600 text-white focus:border-primary-500 focus:outline-none"
                    >
                        <option value="">Select Source</option>
                        @foreach($sources as $source)
                        <option value="{{ $source }}" {{ old('source') === $source ? 'selected' : '' }}>{{ $source }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="course" class="block text-sm font-medium text-dark-300 mb-2">Course</label>
                    <select 
                        id="course" 
                        name="course"
                        class="w-full px-4 py-3 rounded-lg bg-dark-800 border border-dark-600 text-white focus:border-primary-500 focus:outline-none"
                    >
                        <option value="">Select Course</option>
                        @foreach($courses as $course)
                        <option value="{{ $course }}" {{ old('course') === $course ? 'selected' : '' }}>{{ $course }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Status & Assigned To -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-dark-300 mb-2">Status</label>
                    <select 
                        id="status" 
                        name="status"
                        class="w-full px-4 py-3 rounded-lg bg-dark-800 border border-dark-600 text-white focus:border-primary-500 focus:outline-none"
                    >
                        @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ old('status', 'NEW') === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                
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
            </div>
            
            <!-- Follow-up -->
            <div>
                <label for="next_follow_up" class="block text-sm font-medium text-dark-300 mb-2">Next Follow-up</label>
                <input 
                    type="datetime-local" 
                    id="next_follow_up" 
                    name="next_follow_up" 
                    value="{{ old('next_follow_up') }}"
                    class="w-full px-4 py-3 rounded-lg bg-dark-800 border border-dark-600 text-white focus:border-primary-500 focus:outline-none"
                >
            </div>
            
            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-dark-300 mb-2">Notes</label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    rows="3"
                    class="w-full px-4 py-3 rounded-lg bg-dark-800 border border-dark-600 text-white placeholder-dark-400 focus:border-primary-500 focus:outline-none resize-none"
                    placeholder="Additional notes..."
                >{{ old('notes') }}</textarea>
            </div>
            
            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-dark-700">
                <a href="{{ route('leads.index') }}" class="px-6 py-2 rounded-lg bg-dark-700 hover:bg-dark-600 text-white transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-medium transition-colors">
                    Create Lead
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
