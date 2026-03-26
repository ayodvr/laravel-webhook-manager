<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webhook Manager Demo Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-12">
        <header class="mb-12 flex justify-between items-end">
            <div>
                <h1 class="text-4xl font-bold text-slate-900 tracking-tight mb-2">Package Demo Dashboard</h1>
                <p class="text-slate-600 text-lg">Simulate and monitor webhooks in real-time.</p>
            </div>
            <div class="flex gap-4">
                <form action="{{ route('demo.simulate') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold shadow-sm transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        Simulate Paystack Payment
                    </button>
                </form>
            </div>
        </header>

        @if(session('success'))
            <div class="mb-8 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar: Stats -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Quick Stats</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">Total Received</span>
                            <span class="text-xl font-bold text-slate-900">{{ $events->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">Processed</span>
                            <span class="text-xl font-bold text-emerald-600">{{ $events->where('status', 'processed')->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">Failed</span>
                            <span class="text-xl font-bold text-rose-600">{{ $events->where('status', 'failed')->count() }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-900 p-6 rounded-xl shadow-lg text-white">
                    <h3 class="font-bold text-lg mb-2">Pro Tip 💡</h3>
                    <p class="text-indigo-100 text-sm leading-relaxed">
                        Incoming webhooks are verified via HMAC-SHA256 signatures before being stored and dispatched to background workers.
                    </p>
                </div>
            </div>

            <!-- Main Content: Event List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h2 class="font-bold text-slate-800">Recent Webhook Events</h2>
                    </div>
                    
                    @if($events->isEmpty())
                        <div class="p-12 text-center">
                            <div class="bg-slate-100 h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                            </div>
                            <h3 class="text-slate-800 font-medium mb-1">No webhooks yet</h3>
                            <p class="text-slate-500 text-sm">Click simulation button to trigger your first event.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50/50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-3">Provider</th>
                                        <th class="px-6 py-3">Event Type</th>
                                        <th class="px-6 py-3">Status</th>
                                        <th class="px-6 py-3">Received At</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($events as $event)
                                        <tr class="hover:bg-slate-50/80 transition-colors">
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $event->provider === 'paystack' ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-800' }}">
                                                    {{ ucfirst($event->provider) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 font-mono text-sm text-slate-600">
                                                {{ $event->event_type }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center gap-1.5">
                                                    @if($event->status === 'processed')
                                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                        <span class="text-sm text-slate-700">Processed</span>
                                                    @elseif($event->status === 'failed')
                                                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                                        <span class="text-sm text-slate-700">Failed</span>
                                                    @else
                                                        <span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
                                                        <span class="text-sm text-slate-700">Pending</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-slate-500">
                                                {{ $event->created_at->diffForHumans() }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
