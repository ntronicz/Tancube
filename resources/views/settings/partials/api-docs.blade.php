@php
$apiToken = auth()->user()->api_token ?? 'YOUR_API_TOKEN';
$baseUrl = url('/api');

// Pre-build all cURL examples as PHP strings, then json_encode for safe JS injection
$curlListLeads = "curl -X GET '{$baseUrl}/leads?status=NEW&limit=10' \\\n  -H 'X-API-TOKEN: {$apiToken}'";

$curlCreateLead = "curl -X POST '{$baseUrl}/leads' \\\n  -H 'X-API-TOKEN: {$apiToken}' \\\n  -H 'Content-Type: application/json' \\\n  -d '{\n    \"name\": \"John Doe\",\n    \"phone\": \"9876543210\",\n    \"email\": \"john@example.com\",\n    \"source\": \"Website\",\n    \"assigned_to\": \"AGENT_UUID_HERE\"\n  }'";

$curlGetLead = "curl -X GET '{$baseUrl}/leads/LEAD_UUID_HERE' \\\n  -H 'X-API-TOKEN: {$apiToken}'";

$curlUpdateLead = "curl -X PUT '{$baseUrl}/leads/LEAD_UUID_HERE' \\\n  -H 'X-API-TOKEN: {$apiToken}' \\\n  -H 'Content-Type: application/json' \\\n  -d '{\n    \"status\": \"CONTACTED\",\n    \"notes\": \"Called and discussed requirements\"\n  }'";

$curlDeleteLead = "curl -X DELETE '{$baseUrl}/leads/LEAD_UUID_HERE' \\\n  -H 'X-API-TOKEN: {$apiToken}'";

$curlFollowUp = "curl -X POST '{$baseUrl}/leads/LEAD_UUID_HERE/follow-up' \\\n  -H 'X-API-TOKEN: {$apiToken}' \\\n  -H 'Content-Type: application/json' \\\n  -d '{\"preset\": \"tomorrow\"}'";

$curlImport = "curl -X POST '{$baseUrl}/leads/import' \\\n  -H 'X-API-TOKEN: {$apiToken}' \\\n  -F 'file=@/path/to/leads.csv'";

$curlExport = "curl -X GET '{$baseUrl}/leads/export/csv' \\\n  -H 'X-API-TOKEN: {$apiToken}' \\\n  -o leads_export.csv";

$curlListTasks = "curl -X GET '{$baseUrl}/tasks?status=PENDING&priority=HIGH' \\\n  -H 'X-API-TOKEN: {$apiToken}'";

$curlCreateTask = "curl -X POST '{$baseUrl}/tasks' \\\n  -H 'X-API-TOKEN: {$apiToken}' \\\n  -H 'Content-Type: application/json' \\\n  -d '{\n    \"title\": \"Follow up with client\",\n    \"due_date\": \"2026-02-25\",\n    \"priority\": \"HIGH\",\n    \"description\": \"Call back regarding pricing\",\n    \"assigned_to\": \"AGENT_UUID_HERE\"\n  }'";

$curlUpdateTask = "curl -X PUT '{$baseUrl}/tasks/TASK_UUID_HERE' \\\n  -H 'X-API-TOKEN: {$apiToken}' \\\n  -H 'Content-Type: application/json' \\\n  -d '{\"status\": \"COMPLETED\"}'";

$curlDeleteTask = "curl -X DELETE '{$baseUrl}/tasks/TASK_UUID_HERE' \\\n  -H 'X-API-TOKEN: {$apiToken}'";

$curlCompleteTask = "curl -X POST '{$baseUrl}/tasks/TASK_UUID_HERE/complete' \\\n  -H 'X-API-TOKEN: {$apiToken}'";

$curlStats = "curl -X GET '{$baseUrl}/stats' \\\n  -H 'X-API-TOKEN: {$apiToken}'";

$curlInsights = "curl -X GET '{$baseUrl}/insights' \\\n  -H 'X-API-TOKEN: {$apiToken}'";

$curlLogin = "curl -X POST '{$baseUrl}/login' \\\n  -H 'Content-Type: application/json' \\\n  -d '{\"email\": \"you@example.com\", \"password\": \"your-password\"}'";

$curlMe = "curl -X GET '{$baseUrl}/me' \\\n  -H 'X-API-TOKEN: {$apiToken}'";

$curlLogout = "curl -X POST '{$baseUrl}/logout' \\\n  -H 'X-API-TOKEN: {$apiToken}'";

// n8n examples
$n8nCreateLead = json_encode([
    'method' => 'POST',
    'url' => $baseUrl . '/leads',
    'authentication' => 'genericCredentialType',
    'genericAuthType' => 'httpHeaderAuth',
    'sendBody' => true,
    'specifyBody' => 'json',
    'jsonBody' => '={ "name": $json.name, "phone": $json.phone, "email": $json.email, "source": "n8n" }',
    'options' => new \stdClass()
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

$n8nCreateTask = json_encode([
    'method' => 'POST',
    'url' => $baseUrl . '/tasks',
    'authentication' => 'genericCredentialType',
    'genericAuthType' => 'httpHeaderAuth',
    'sendBody' => true,
    'specifyBody' => 'json',
    'jsonBody' => '={ "title": $json.title, "due_date": $json.due_date, "priority": "HIGH" }',
    'options' => new \stdClass()
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
@endphp

<!-- Full API Documentation Modal -->
<div x-data="{ open: false, activeTab: 'leads' }" x-cloak>
    <button @click="open = true" class="w-full px-6 py-3 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium transition-all shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
        <i data-lucide="book-open" class="w-5 h-5"></i>
        View Full API Documentation
    </button>

    <!-- Modal Overlay -->
    <div x-show="open" style="display:none;" class="fixed inset-0 z-50 flex items-start justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl my-8" @click.away="open = false" @keydown.escape.window="open = false">
            
            <!-- Modal Header -->
            <div class="sticky top-0 bg-white rounded-t-2xl border-b border-slate-200 px-6 py-4 flex justify-between items-center z-10">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">API Documentation</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Tancube CRM REST API Reference</p>
                </div>
                <button @click="open = false" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-slate-500"></i>
                </button>
            </div>

            <!-- Auth Info Banner -->
            <div class="mx-6 mt-4 p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                <div class="flex items-start gap-3">
                    <i data-lucide="shield" class="w-5 h-5 text-indigo-600 mt-0.5 shrink-0"></i>
                    <div class="text-sm">
                        <p class="font-bold text-indigo-900 mb-1">Authentication Required</p>
                        <p class="text-indigo-700">All endpoints require the <code class="bg-white px-1.5 py-0.5 rounded text-xs font-mono border border-indigo-200">X-API-TOKEN</code> header.</p>
                        <code class="block mt-2 bg-white px-3 py-2 rounded-lg text-xs font-mono text-indigo-600 border border-indigo-200">X-API-TOKEN: {{ $apiToken }}</code>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="px-6 mt-4 flex gap-1 border-b border-slate-200 overflow-x-auto">
                <template x-for="tab in ['leads', 'tasks', 'dashboard', 'auth']" :key="tab">
                    <button @click="activeTab = tab" 
                        :class="activeTab === tab ? 'border-indigo-600 text-indigo-600 bg-indigo-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                        class="px-4 py-2.5 text-sm font-semibold border-b-2 rounded-t-lg transition-all capitalize whitespace-nowrap" x-text="tab">
                    </button>
                </template>
            </div>

            <!-- Tab Content -->
            <div class="p-6 max-h-[65vh] overflow-y-auto space-y-4">

                <!-- ===== LEADS TAB ===== -->
                <div x-show="activeTab === 'leads'" x-cloak>
                    
                    {{-- List Leads --}}
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700 font-bold font-mono w-14 text-center">GET</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/leads</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— List leads (paginated)</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">Query Parameters</h5>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">status</code> <span class="text-slate-400">— Filter by status</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">search</code> <span class="text-slate-400">— Search name/phone/email</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">start_date</code> <span class="text-slate-400">— From date (YYYY-MM-DD)</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">end_date</code> <span class="text-slate-400">— To date (YYYY-MM-DD)</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">limit</code> <span class="text-slate-400">— Per page (default: 15)</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">page</code> <span class="text-slate-400">— Page number</span></div>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">cURL Example</h5>
                                <div class="relative">
                                    <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlListLeads }}</pre>
                                    <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Create Lead --}}
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-700 font-bold font-mono w-14 text-center">POST</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/leads</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Create a new lead</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">Body Parameters (JSON)</h5>
                                <div class="space-y-1 text-sm">
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">name</code> <span class="text-red-500 text-xs font-bold">REQUIRED</span> <span class="text-slate-400">— string, max 255</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">phone</code> <span class="text-slate-400">— string, max 20 (duplicate check)</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">email</code> <span class="text-slate-400">— valid email</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">source</code> <span class="text-slate-400">— string (Website, Facebook, etc.)</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">assigned_to</code> <span class="text-slate-400">— UUID of the agent</span></div>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">cURL Example</h5>
                                <div class="relative">
                                    <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlCreateLead }}</pre>
                                    <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">n8n HTTP Request Node</h5>
                                <div class="relative">
                                    <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $n8nCreateLead }}</pre>
                                    <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                                </div>
                                <p class="text-xs text-slate-400 mt-2"><strong>Tip:</strong> In n8n, create a "Header Auth" credential with Name = <code class="text-indigo-500">X-API-TOKEN</code> and Value = your API token.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Get Single Lead --}}
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700 font-bold font-mono w-14 text-center">GET</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/leads/&#123;id&#125;</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Get single lead</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">cURL Example</h5>
                                <div class="relative">
                                    <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlGetLead }}</pre>
                                    <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Update Lead --}}
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-700 font-bold font-mono w-14 text-center">PUT</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/leads/&#123;id&#125;</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Update a lead</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">Body Parameters (JSON) — all optional</h5>
                                <div class="space-y-1 text-sm">
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">name</code> <span class="text-slate-400">— string</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">phone</code> / <code class="text-indigo-600">email</code> / <code class="text-indigo-600">source</code> / <code class="text-indigo-600">course</code></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">status</code> <span class="text-slate-400">— NEW, CONTACTED, QUALIFIED, etc.</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">assigned_to</code> <span class="text-slate-400">— UUID of agent</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">notes</code> <span class="text-slate-400">— text</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">next_follow_up</code> <span class="text-slate-400">— datetime (YYYY-MM-DD HH:MM:SS)</span></div>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">cURL Example</h5>
                                <div class="relative">
                                    <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlUpdateLead }}</pre>
                                    <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Delete Lead --}}
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-red-100 text-red-700 font-bold font-mono w-14 text-center">DELETE</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/leads/&#123;id&#125;</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Delete a lead</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div class="relative">
                                <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlDeleteLead }}</pre>
                                <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                            </div>
                        </div>
                    </div>

                    {{-- Set Follow-Up --}}
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-700 font-bold font-mono w-14 text-center">POST</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/leads/&#123;id&#125;/follow-up</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Set follow-up</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">Body Parameters (JSON)</h5>
                                <div class="space-y-1 text-sm">
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">preset</code> <span class="text-red-500 text-xs font-bold">REQUIRED</span> <span class="text-slate-400">— 1h, 3h, tomorrow, next_week, custom</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">datetime</code> <span class="text-slate-400">— required if preset=custom (YYYY-MM-DD HH:MM:SS)</span></div>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">cURL Example</h5>
                                <div class="relative">
                                    <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlFollowUp }}</pre>
                                    <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Import Leads --}}
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-700 font-bold font-mono w-14 text-center">POST</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/leads/import</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Import leads from CSV</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">Body (multipart/form-data)</h5>
                                <div class="bg-white p-2 rounded border border-slate-100 text-sm"><code class="text-indigo-600">file</code> <span class="text-red-500 text-xs font-bold">REQUIRED</span> <span class="text-slate-400">— CSV file (name, phone, email, source columns)</span></div>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">cURL Example</h5>
                                <div class="relative">
                                    <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlImport }}</pre>
                                    <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Export Leads --}}
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700 font-bold font-mono w-14 text-center">GET</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/leads/export/csv</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Export leads as CSV</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div class="relative">
                                <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlExport }}</pre>
                                <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== TASKS TAB ===== -->
                <div x-show="activeTab === 'tasks'" x-cloak>
                    
                    {{-- List Tasks --}}
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700 font-bold font-mono w-14 text-center">GET</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/tasks</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— List tasks (paginated)</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">Query Parameters</h5>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">status</code> <span class="text-slate-400">— PENDING, COMPLETED</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">priority</code> <span class="text-slate-400">— LOW, MEDIUM, HIGH</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">assigned_to</code> <span class="text-slate-400">— Agent UUID</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">due_date</code> <span class="text-slate-400">— YYYY-MM-DD</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">limit</code> <span class="text-slate-400">— Per page (max 100)</span></div>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">cURL Example</h5>
                                <div class="relative">
                                    <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlListTasks }}</pre>
                                    <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Create Task --}}
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-700 font-bold font-mono w-14 text-center">POST</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/tasks</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Create a new task</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">Body Parameters (JSON)</h5>
                                <div class="space-y-1 text-sm">
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">title</code> <span class="text-red-500 text-xs font-bold">REQUIRED</span> <span class="text-slate-400">— string, max 255</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">due_date</code> <span class="text-red-500 text-xs font-bold">REQUIRED</span> <span class="text-slate-400">— date (YYYY-MM-DD)</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">description</code> <span class="text-slate-400">— text</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">priority</code> <span class="text-slate-400">— LOW, MEDIUM (default), HIGH</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">assigned_to</code> <span class="text-slate-400">— UUID of agent</span></div>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">cURL Example</h5>
                                <div class="relative">
                                    <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlCreateTask }}</pre>
                                    <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">n8n HTTP Request Node</h5>
                                <div class="relative">
                                    <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $n8nCreateTask }}</pre>
                                    <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Update Task --}}
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-700 font-bold font-mono w-14 text-center">PUT</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/tasks/&#123;id&#125;</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Update a task</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">Body Parameters (JSON) — all optional</h5>
                                <div class="space-y-1 text-sm">
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">title</code> / <code class="text-indigo-600">description</code> / <code class="text-indigo-600">due_date</code></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">priority</code> <span class="text-slate-400">— LOW, MEDIUM, HIGH</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">status</code> <span class="text-slate-400">— PENDING, COMPLETED</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">assigned_to</code> <span class="text-slate-400">— UUID of agent</span></div>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">cURL Example</h5>
                                <div class="relative">
                                    <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlUpdateTask }}</pre>
                                    <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Delete Task --}}
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-red-100 text-red-700 font-bold font-mono w-14 text-center">DELETE</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/tasks/&#123;id&#125;</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Delete a task</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div class="relative">
                                <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlDeleteTask }}</pre>
                                <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                            </div>
                        </div>
                    </div>

                    {{-- Mark Complete --}}
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-700 font-bold font-mono w-14 text-center">POST</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/tasks/&#123;id&#125;/complete</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Mark task complete</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div class="relative">
                                <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlCompleteTask }}</pre>
                                <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== DASHBOARD TAB ===== -->
                <div x-show="activeTab === 'dashboard'" x-cloak>
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700 font-bold font-mono w-14 text-center">GET</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/stats</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Dashboard statistics</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <p class="text-sm text-slate-500">Returns total leads, status breakdown, task counts, and summary metrics.</p>
                            <div class="relative">
                                <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlStats }}</pre>
                                <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden mt-4">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700 font-bold font-mono w-14 text-center">GET</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/insights</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Detailed insights &amp; trends</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <p class="text-sm text-slate-500">Returns conversion funnels, trends, agent performance data and more.</p>
                            <div class="relative">
                                <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlInsights }}</pre>
                                <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== AUTH TAB ===== -->
                <div x-show="activeTab === 'auth'" x-cloak>
                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-700 font-bold font-mono w-14 text-center">POST</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/login</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Authenticate (get token)</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">Body Parameters (JSON)</h5>
                                <div class="space-y-1 text-sm">
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">email</code> <span class="text-red-500 text-xs font-bold">REQUIRED</span></div>
                                    <div class="bg-white p-2 rounded border border-slate-100"><code class="text-indigo-600">password</code> <span class="text-red-500 text-xs font-bold">REQUIRED</span></div>
                                </div>
                            </div>
                            <div class="bg-amber-50 p-3 rounded-lg border border-amber-100 text-xs text-amber-700">
                                <strong>Note:</strong> For most integrations (n8n, Zapier, etc.), use the API Token method instead. Tokens don't expire.
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">cURL Example</h5>
                                <div class="relative">
                                    <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlLogin }}</pre>
                                    <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700 font-bold font-mono w-14 text-center">GET</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/me</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Get current user profile</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div class="relative">
                                <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlMe }}</pre>
                                <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div x-data="{ expanded: false }" class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-700 font-bold font-mono w-14 text-center">POST</span>
                                <span class="font-semibold text-slate-900 text-sm">/api/logout</span>
                                <span class="text-slate-400 text-sm hidden sm:inline">— Logout / invalidate token</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-180'"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="border-t border-slate-100 p-4 bg-slate-50/50 space-y-4">
                            <div class="relative">
                                <pre class="bg-slate-900 text-slate-300 p-4 rounded-lg text-xs font-mono overflow-x-auto api-code-block">{{ $curlLogout }}</pre>
                                <button onclick="copyApiCode(this)" class="absolute top-2 right-2 text-xs text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /tab content -->

            <!-- Modal Footer -->
            <div class="border-t border-slate-200 px-6 py-4 bg-slate-50 rounded-b-2xl">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-slate-400">Base URL: <code class="text-indigo-500">{{ $baseUrl }}</code></p>
                    <button @click="open = false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyApiCode(btn) {
    const pre = btn.closest('.relative').querySelector('pre');
    navigator.clipboard.writeText(pre.textContent);
    btn.textContent = 'Copied!';
    setTimeout(() => btn.textContent = 'Copy', 2000);
}
</script>
