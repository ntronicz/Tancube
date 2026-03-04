@extends('layouts.app')

@section('title', 'Settings - Tancube CRM')
@section('page-title', 'Settings')

@section('content')
<div class="animate-fade-in">
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Tabs -->
        <div class="lg:w-64 shrink-0">
            <div class="bg-white rounded-xl p-4 space-y-1 shadow-sm border border-slate-200">
                <a href="{{ route('settings.index', ['tab' => 'general']) }}" 
                   class="flex items-center px-4 py-2.5 rounded-lg transition-colors {{ $tab === 'general' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i data-lucide="settings" class="w-5 h-5 mr-3"></i>
                    General
                </a>
                <a href="{{ route('settings.index', ['tab' => 'users']) }}" 
                   class="flex items-center px-4 py-2.5 rounded-lg transition-colors {{ $tab === 'users' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                    Users
                </a>
                <a href="{{ route('settings.index', ['tab' => 'masters']) }}" 
                   class="flex items-center px-4 py-2.5 rounded-lg transition-colors {{ $tab === 'masters' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i data-lucide="sliders" class="w-5 h-5 mr-3"></i>
                    Masters
                </a>
                <a href="{{ route('settings.index', ['tab' => 'api']) }}" 
                   class="flex items-center px-4 py-2.5 rounded-lg transition-colors {{ $tab === 'api' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i data-lucide="code" class="w-5 h-5 mr-3"></i>
                    API
                </a>
                <a href="{{ route('settings.index', ['tab' => 'webhooks']) }}" 
                   class="flex items-center px-4 py-2.5 rounded-lg transition-colors {{ $tab === 'webhooks' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i data-lucide="webhook" class="w-5 h-5 mr-3"></i>
                    Webhooks
                </a>
                <a href="{{ route('settings.index', ['tab' => 'activity']) }}" 
                   class="flex items-center px-4 py-2.5 rounded-lg transition-colors {{ $tab === 'activity' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i data-lucide="activity" class="w-5 h-5 mr-3"></i>
                    Activity Log
                </a>
                <a href="{{ route('settings.index', ['tab' => 'system']) }}" 
                   class="flex items-center px-4 py-2.5 rounded-lg transition-colors {{ $tab === 'system' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i data-lucide="database" class="w-5 h-5 mr-3"></i>
                    System Data
                </a>
            </div>
        </div>
        
        <!-- Content -->
        <div class="flex-1">
            @if($tab === 'general')
            <!-- General Settings -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Account Info</h3>
                <form action="{{ route('settings.profile.update') }}" method="POST" class="space-y-4 max-w-md">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Name</label>
                        <input type="text" name="name" value="{{ auth()->user()->name }}" required class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Email</label>
                        <input type="email" name="email" value="{{ auth()->user()->email }}" required class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">New Password <span class="text-xs font-normal text-slate-400">(Leave blank to keep current)</span></label>
                        <input type="password" name="password" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none" autocomplete="new-password">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none" autocomplete="new-password">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Role</label>
                        <span class="px-3 py-1 rounded-full text-sm font-bold bg-indigo-50 text-indigo-600 block w-fit">{{ auth()->user()->role }}</span>
                    </div>
                    
                    <button type="submit" class="px-6 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors shadow-sm shadow-indigo-200">Update Profile</button>
                </form>
                
                <h3 class="text-lg font-bold text-slate-900 mt-8 mb-4">Application Settings</h3>
                <form action="{{ route('settings.general.update') }}" method="POST" class="space-y-4 max-w-md">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Time Zone</label>
                        <select name="timezone" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                            @foreach(\DateTimeZone::listIdentifiers() as $timezone)
                            <option value="{{ $timezone }}" {{ ($generalSettings['timezone'] ?? 'Asia/Kolkata') == $timezone ? 'selected' : '' }}>
                                {{ $timezone }}
                            </option>
                            @endforeach
                        </select>
                         <p class="text-xs text-slate-400 mt-1">Default: Asia/Kolkata (+05:30)</p>
                    </div>
                    <button type="submit" class="px-6 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors shadow-sm shadow-indigo-200">Save Changes</button>
                </form>
            </div>
            
            @elseif($tab === 'users')
            <!-- Users Management -->
            <div class="space-y-6" x-data="{ editModalOpen: false, editingUser: {} }">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Add New User</h3>
                    <form action="{{ route('settings.users.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf
                        <input type="text" name="name" placeholder="Name" required class="px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none placeholder-slate-400">
                        <input type="email" name="email" placeholder="Email" required class="px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none placeholder-slate-400">
                        <input type="password" name="password" placeholder="Password" required class="px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none placeholder-slate-400">
                        <select name="role" required class="px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                            <option value="AGENT">Agent</option>
                            <option value="ADMIN">Admin</option>
                        </select>
                        <div class="md:col-span-2">
                            <button type="submit" class="px-6 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors shadow-sm shadow-indigo-200">Add User</button>
                        </div>
                    </form>
                </div>
                
                <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-slate-200">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Role</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Agent ID</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($users as $u)
                            <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-slate-900 font-medium">{{ $u->name }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $u->email }}</td>
                                <td class="px-4 py-3"><span class="px-2 py-1 rounded text-xs font-bold {{ $u->role === 'ADMIN' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">{{ $u->role }}</span></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2" x-data="{ id: '{{ $u->id }}' }">
                                        <code class="text-xs bg-slate-100 px-2 py-1 rounded font-mono text-slate-600" title="{{ $u->id }}">{{ Str::limit($u->id, 8) }}...</code>
                                        <button @click="navigator.clipboard.writeText(id); $el.innerHTML = '<i data-lucide=\'check\' class=\'w-3 h-3\'></i>'; setTimeout(() => $el.innerHTML = '<i data-lucide=\'copy\' class=\'w-3 h-3\'></i>', 2000)" class="text-slate-400 hover:text-indigo-600 p-1" title="Copy Agent ID">
                                            <i data-lucide="copy" class="w-3 h-3"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right flex items-center justify-end gap-2">
                                     <button @click="editModalOpen = true; editingUser = {{ json_encode($u) }}" class="text-indigo-600 hover:text-indigo-800 p-1 hover:bg-indigo-50 rounded" title="Edit User">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </button>
                                    @if($u->id !== auth()->id())
                                    <form action="{{ route('settings.users.destroy', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Edit User Modal -->
                <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6" @click.away="editModalOpen = false">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-slate-900">Edit User</h3>
                            <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                        </div>
                        <form :action="'/settings/users/' + editingUser.id" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-1">Name</label>
                                <input type="text" name="name" x-model="editingUser.name" required class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 border-none outline-none ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-1">Email</label>
                                <input type="email" name="email" x-model="editingUser.email" required class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 border-none outline-none ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-slate-500 mb-1">New Password (Optional)</label>
                                <input type="password" name="password" placeholder="Leave blank to keep current" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 border-none outline-none ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-1">Role</label>
                                <select name="role" x-model="editingUser.role" required class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 border-none outline-none ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500">
                                    <option value="AGENT">Agent</option>
                                    <option value="ADMIN">Admin</option>
                                </select>
                            </div>
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium transition-colors">Cancel</button>
                                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors">Update User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            @elseif($tab === 'masters')
            <!-- Masters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Sources</h3>
                    <form action="{{ route('settings.sources.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <textarea name="sources" rows="8" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none resize-none mb-3 placeholder-slate-400">{{ implode("\n", $sources) }}</textarea>
                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors shadow-sm shadow-indigo-200">Save</button>
                    </form>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Courses</h3>
                    <form action="{{ route('settings.courses.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <textarea name="courses" rows="8" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none resize-none mb-3 placeholder-slate-400">{{ implode("\n", $courses) }}</textarea>
                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors shadow-sm shadow-indigo-200">Save</button>
                    </form>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Statuses</h3>
                    <form action="{{ route('settings.statuses.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <textarea name="statuses" rows="8" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none resize-none mb-3 placeholder-slate-400">{{ implode("\n", $statuses) }}</textarea>
                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors shadow-sm shadow-indigo-200">Save</button>
                    </form>
                </div>
            </div>
            
            @elseif($tab === 'api')
            <!-- API Integration -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">API Credentials</h3>
                    <p class="text-slate-500 mb-6">Use these credentials to integrate with external systems.</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">API Base URL</label>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly value="{{ url('/api') }}" 
                                    class="flex-1 px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-600 font-mono text-sm" id="apiUrl">
                                <button onclick="navigator.clipboard.writeText(document.getElementById('apiUrl').value); this.textContent='Copied!'; setTimeout(() => this.textContent='Copy', 2000)" 
                                    class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 border border-slate-200 transition-colors">Copy</button>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Organization ID</label>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly value="{{ auth()->user()->organization_id }}" 
                                    class="flex-1 px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-600 font-mono text-sm" id="orgId">
                                <button onclick="navigator.clipboard.writeText(document.getElementById('orgId').value); this.textContent='Copied!'; setTimeout(() => this.textContent='Copy', 2000)" 
                                    class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 border border-slate-200 transition-colors">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- API Token Management -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                    <div class="flex flex-col lg:flex-row justify-between items-start gap-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">API Access</h3>
                            <p class="text-sm text-slate-500 mt-1">Manage your API token and integrate with external systems.</p>
                            <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-100">
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-1.5">Authentication Header</h5>
                                <code class="text-sm text-indigo-600 font-mono">X-API-TOKEN: &lt;your-api-token&gt;</code>
                                <p class="text-xs text-slate-400 mt-2"><strong>n8n:</strong> Create a "Header Auth" credential with Name = <code class="text-indigo-500">X-API-TOKEN</code></p>
                            </div>
                        </div>
                        
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 max-w-md w-full">
                            <h4 class="text-sm font-bold text-slate-900 mb-2">Your API Token</h4>
                            @if(auth()->user()->api_token)
                                <div class="flex items-center gap-2 mb-2" x-data="{ token: '{{ auth()->user()->api_token }}', visible: false }">
                                    <code class="flex-1 bg-white border border-slate-200 p-2 rounded text-xs font-mono break-all" x-text="visible ? token : '•'.repeat(20)"></code>
                                    <button @click="visible = !visible" class="p-2 text-slate-400 hover:text-indigo-600 transition-colors">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                    <button @click="navigator.clipboard.writeText(token); $el.textContent = 'Copied!'; setTimeout(() => $el.textContent = 'Copy', 2000)" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 p-2">Copy</button>
                                </div>
                                <p class="text-xs text-slate-500 mb-3">Keep this token secret. It grants full access to your account.</p>
                            @else
                                <p class="text-sm text-amber-600 mb-3">No API token generated yet.</p>
                            @endif
                            
                            <form action="{{ route('settings.profile.token') }}" method="POST" onsubmit="return confirm('Generate new API token? Old token will be invalidated.')">
                                @csrf
                                <button type="submit" class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded transition-colors">
                                    {{ auth()->user()->api_token ? 'Regenerate Token' : 'Generate Token' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-6">
                        @include('settings.partials.api-docs')
                    </div>
                </div>
            </div>
            
            @elseif($tab === 'webhooks')
            <!-- Webhooks -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Add Webhook</h3>
                    <form action="{{ route('settings.webhooks.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="url" name="url" placeholder="https://example.com/webhook" required class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none placeholder-slate-400">
                        <div class="flex flex-wrap gap-3">
                            @foreach(['lead.created', 'lead.updated', 'lead.deleted', 'lead.assigned', 'task.created', 'task.completed'] as $event)
                            <label class="flex items-center space-x-2 cursor-pointer p-2 rounded-lg hover:bg-slate-50">
                                <input type="checkbox" name="events[]" value="{{ $event }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-slate-600 text-sm font-medium">{{ $event }}</span>
                            </label>
                            @endforeach
                        </div>
                        <button type="submit" class="px-6 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors shadow-sm shadow-indigo-200">Add Webhook</button>
                    </form>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Active Webhooks</h3>
                    @forelse($webhooks as $wh)
                    <div class="flex items-center justify-between py-3 border-b border-slate-100 last:border-0 hover:bg-slate-50 -mx-6 px-6 transition-colors">
                        <div>
                            <p class="text-slate-900 font-medium">{{ $wh->url }}</p>
                            <p class="text-sm text-slate-500 mt-1">{{ implode(', ', $wh->events ?? []) }}</p>
                        </div>
                        <form action="{{ route('settings.webhooks.destroy', $wh->id) }}" method="POST" onsubmit="return confirm('Delete this webhook?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 p-2 hover:bg-red-50 rounded-lg transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </form>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="webhook" class="w-6 h-6 text-slate-400"></i>
                        </div>
                        <p class="text-slate-500">No webhooks configured</p>
                    </div>
                    @endforelse
                </div>
            </div>
            
            @elseif($tab === 'activity')
            <!-- Activity Log -->
            <div class="space-y-4">
                <!-- Filters -->
                <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
                    <form method="GET" action="{{ route('settings.index') }}" class="flex flex-wrap items-center gap-4">
                        <input type="hidden" name="tab" value="activity">
                        
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-slate-500">Action:</label>
                            <select name="action" class="rounded-lg border-slate-200 text-sm focus:ring-indigo-500">
                                <option value="">All Actions</option>
                                @foreach($actions as $act)
                                <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ $act }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-slate-500">From:</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="rounded-lg border-slate-200 text-sm focus:ring-indigo-500">
                        </div>

                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-slate-500">To:</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="rounded-lg border-slate-200 text-sm focus:ring-indigo-500">
                        </div>

                        <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-sm font-medium transition-colors">Apply</button>
                         @if(request()->hasAny(['action', 'start_date', 'end_date']))
                        <a href="{{ route('settings.index', ['tab' => 'activity']) }}" class="text-slate-500 hover:text-slate-700 text-sm">Clear</a>
                        @endif
                    </form>

                    <!-- Clear Logs -->
                    @if(auth()->user()->role === 'ADMIN' || auth()->user()->role === 'SUPER_ADMIN')
                    <form action="{{ route('settings.logs.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear ' + (new URLSearchParams(window.location.search).has('action') || new URLSearchParams(window.location.search).has('start_date') ? 'these filtered' : 'ALL') + ' activity logs? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="action" value="{{ request('action') }}">
                        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                        <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium flex items-center">
                            <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> {{ request()->hasAny(['action', 'start_date', 'end_date']) ? 'Clear filtered logs' : 'Clear all logs' }}
                        </button>
                    </form>
                    @endif
                </div>

                <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-slate-200">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($activityLogs as $log)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-slate-500 text-sm whitespace-nowrap">{{ $log->timestamp->format('M d, H:i') }}</td>
                            <td class="px-4 py-3 text-slate-900 font-medium">{{ $log->user->name ?? 'System' }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">{{ $log->action }}</span></td>
                            <td class="px-4 py-3 text-slate-600 text-sm">{{ $log->details }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-slate-500">
                                <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="activity" class="w-6 h-6 text-slate-400"></i>
                                </div>
                                <p>No activity logged yet</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($activityLogs->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $activityLogs->links() }}
                </div>
                @endif
            </div>
            @elseif($tab === 'system')
            <!-- System Data (Backup & Restore) -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <!-- Left Column: Backup Operations -->
                <div class="space-y-6">
                    <!-- Manual Backup -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 mb-2">Manual Backup</h3>
                                <p class="text-slate-500 text-sm">Download a snapshot of your organization's data (Leads, Tasks, Users, Settings).</p>
                            </div>
                            <div class="p-2 bg-indigo-50 rounded-lg">
                                <i data-lucide="download" class="w-5 h-5 text-indigo-600"></i>
                            </div>
                        </div>
                        <div class="mt-6">
                            <a href="{{ route('settings.backup') }}" class="w-full inline-flex justify-center items-center px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors shadow-sm shadow-indigo-200">
                                <i data-lucide="download-cloud" class="w-4 h-4 mr-2"></i>
                                Download Full Backup
                            </a>
                        </div>
                    </div>

                    <!-- Automated Backup -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 mb-2">Automated Backup</h3>
                                <p class="text-slate-500 text-sm">Schedule periodic backups to be saved securely.</p>
                            </div>
                            <div class="p-2 bg-indigo-50 rounded-lg">
                                <i data-lucide="clock" class="w-5 h-5 text-indigo-600"></i>
                            </div>
                        </div>
                        
                        @php
                            $backupSettings = \App\Models\AppSetting::getForOrganization(auth()->user()->organization_id, 'backup') ?? [];
                            $bgFrequency = $backupSettings['frequency'] ?? 'never';
                        @endphp

                        <form action="{{ route('settings.backup.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Frequency</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="frequency" value="never" class="peer sr-only" {{ $bgFrequency === 'never' ? 'checked' : '' }}>
                                            <div class="px-3 py-2 rounded-lg border border-slate-200 text-center text-sm font-medium text-slate-600 bg-white peer-checked:bg-slate-100 peer-checked:text-slate-900 peer-checked:border-slate-400 transition-all hover:bg-slate-50">Never</div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="frequency" value="daily" class="peer sr-only" {{ $bgFrequency === 'daily' ? 'checked' : '' }}>
                                            <div class="px-3 py-2 rounded-lg border border-slate-200 text-center text-sm font-medium text-slate-600 bg-white peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-checked:border-indigo-200 transition-all hover:bg-slate-50">Daily</div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="frequency" value="weekly" class="peer sr-only" {{ $bgFrequency === 'weekly' ? 'checked' : '' }}>
                                            <div class="px-3 py-2 rounded-lg border border-slate-200 text-center text-sm font-medium text-slate-600 bg-white peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-checked:border-indigo-200 transition-all hover:bg-slate-50">Weekly</div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="frequency" value="monthly" class="peer sr-only" {{ $bgFrequency === 'monthly' ? 'checked' : '' }}>
                                            <div class="px-3 py-2 rounded-lg border border-slate-200 text-center text-sm font-medium text-slate-600 bg-white peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-checked:border-indigo-200 transition-all hover:bg-slate-50">Monthly</div>
                                        </label>
                                    </div>
                                </div>
                                
                                @if(isset($backupSettings['last_backup_at']))
                                <div class="flex items-center gap-2 text-xs text-slate-500 bg-slate-50 p-2 rounded border border-slate-100">
                                    <i data-lucide="check-circle" class="w-3 h-3 text-green-500"></i>
                                    Last run: {{ \Carbon\Carbon::parse($backupSettings['last_backup_at'])->format('M d, H:i') }}
                                </div>
                                @endif

                                <button type="submit" class="w-full px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium transition-colors shadow-sm">
                                    Save Frequency
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Restore -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200 h-fit">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Restore System Data</h3>
                            <p class="text-slate-500 text-sm">Upload a backup file to restore your system.</p>
                        </div>
                        <div class="p-2 bg-amber-50 rounded-lg">
                            <i data-lucide="rotate-ccw" class="w-5 h-5 text-amber-600"></i>
                        </div>
                    </div>
                    
                    <div class="bg-amber-50 rounded-lg p-4 border border-amber-100 mb-6">
                        <div class="flex gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0"></i>
                            <div class="text-sm text-amber-800">
                                <p class="font-bold mb-1">Warning: Data Overwrite</p>
                                <p class="opacity-90">Restoring will update existing records that match IDs in the backup file. New records will be created. This action cannot be undone.</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('settings.restore') }}" method="POST" enctype="multipart/form-data"
                          onsubmit="return confirm('WARNING: This will overwrite data. Proceed?');">
                        @csrf
                        <div class="space-y-4">
                            <div class="border-2 border-dashed border-slate-200 rounded-xl p-8 text-center hover:bg-slate-50 transition-colors relative">
                                <input type="file" name="backup_file" accept=".json,application/json" required 
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="flex flex-col items-center pointer-events-none">
                                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mb-3">
                                        <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-sm font-medium text-slate-900">Click to upload backup file</p>
                                    <p class="text-xs text-slate-500 mt-1">JSON files only</p>
                                </div>
                            </div>

                            <button type="submit" class="w-full px-4 py-3 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition-colors shadow-sm shadow-red-200 flex items-center justify-center gap-2">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                Start Restore Process
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
