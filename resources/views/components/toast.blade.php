<!-- Toast Notifications Container -->
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2 max-w-sm">
    @if(session('success'))
    <div class="flex items-center p-4 rounded-lg shadow-lg animate-fade-in bg-green-800 text-green-100">
        <i data-lucide="check-circle" class="w-5 h-5 mr-3 shrink-0"></i>
        <span class="flex-1">{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" class="ml-4 text-current opacity-70 hover:opacity-100">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @endif
    
    @if(session('error'))
    <div class="flex items-center p-4 rounded-lg shadow-lg animate-fade-in bg-red-800 text-red-100">
        <i data-lucide="alert-circle" class="w-5 h-5 mr-3 shrink-0"></i>
        <span class="flex-1">{{ session('error') }}</span>
        <button onclick="this.parentElement.remove()" class="ml-4 text-current opacity-70 hover:opacity-100">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @endif
    
    @if(session('warning'))
    <div class="flex items-center p-4 rounded-lg shadow-lg animate-fade-in bg-yellow-800 text-yellow-100">
        <i data-lucide="alert-triangle" class="w-5 h-5 mr-3 shrink-0"></i>
        <span class="flex-1">{{ session('warning') }}</span>
        <button onclick="this.parentElement.remove()" class="ml-4 text-current opacity-70 hover:opacity-100">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @endif
    
    @if(session('info'))
    <div class="flex items-center p-4 rounded-lg shadow-lg animate-fade-in bg-blue-800 text-blue-100">
        <i data-lucide="info" class="w-5 h-5 mr-3 shrink-0"></i>
        <span class="flex-1">{{ session('info') }}</span>
        <button onclick="this.parentElement.remove()" class="ml-4 text-current opacity-70 hover:opacity-100">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @endif
    
    @if($errors->any())
    <div class="flex items-start p-4 rounded-lg shadow-lg animate-fade-in bg-red-800 text-red-100">
        <i data-lucide="alert-circle" class="w-5 h-5 mr-3 shrink-0 mt-0.5"></i>
        <div class="flex-1">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button onclick="this.parentElement.remove()" class="ml-4 text-current opacity-70 hover:opacity-100">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @endif
</div>
