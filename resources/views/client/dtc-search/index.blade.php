<x-layouts.client>
    <x-page-header title="DTC Search" subtitle="Search diagnostic trouble codes" />

    <div
        x-data="{
            query: '',
            results: [],
            total: 0,
            loading: false,
            expanded: null,
            timer: null,
            search() {
                clearTimeout(this.timer)
                this.expanded = null
                this.timer = setTimeout(() => {
                    if (this.query.trim() === '') {
                        this.results = []
                        this.total = 0
                        return
                    }
                    this.loading = true
                    fetch('{{ route('client.dtc-search.results') }}?q=' + encodeURIComponent(this.query))
                        .then(response => response.json())
                        .then(data => {
                            this.results = data.data
                            this.total = data.total
                            this.loading = false
                        })
                }, 500)
            },
            severityClass(s) {
                if (!s) return 'bg-gray-700 text-gray-300'
                const v = s.toLowerCase()
                if (v === 'high')   return 'bg-red-900/60 text-red-300'
                if (v === 'medium') return 'bg-amber-900/60 text-amber-300'
                if (v === 'low')    return 'bg-green-900/60 text-green-300'
                return 'bg-gray-700 text-gray-300'
            },
        }"
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code or Description</label>
            <input type="text" x-model="query" x-on:input="search"
                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-[#e63012] focus:ring-[#e63012]"
                placeholder="e.g. P0420">
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2" x-show="query.trim() !== ''">
            <span x-show="loading">Searching...</span>
            <span x-show="!loading">Showing <span x-text="results.length"></span> of <span x-text="total"></span> results</span>
        </p>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full table-fixed">
                <colgroup>
                    <col class="w-28">
                    <col>
                    <col class="w-32">
                    <col class="w-10">
                </colgroup>
                <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Severity</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>

                {{-- Placeholder: no query --}}
                <tbody x-show="query.trim() === '' && !loading">
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-sm text-center text-gray-500 dark:text-gray-400">
                            Search the DTC code library — enter a code or keyword
                        </td>
                    </tr>
                </tbody>

                {{-- Placeholder: no results --}}
                <tbody x-show="query.trim() !== '' && !loading && results.length === 0">
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-sm text-center text-gray-500 dark:text-gray-400">
                            No results for "<span x-text="query"></span>"
                        </td>
                    </tr>
                </tbody>

                {{-- Results: one tbody per item so expanded row stays adjacent --}}
                <template x-for="(item, index) in results" :key="item.code">
                    <tbody class="border-t border-gray-200 dark:border-gray-700">
                        <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors"
                            @click="expanded = expanded === index ? null : index">
                            <td class="px-6 py-4 text-sm font-mono font-semibold text-gray-900 dark:text-gray-100 align-top" x-text="item.code"></td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 align-top" x-text="item.description"></td>
                            <td class="px-6 py-4 align-top">
                                <span x-show="item.severity_estimate"
                                    :class="severityClass(item.severity_estimate)"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium whitespace-nowrap"
                                    x-text="item.severity_estimate"></span>
                            </td>
                            <td class="px-3 py-4 align-top text-gray-400 dark:text-gray-500">
                                <svg :class="expanded === index ? 'rotate-180' : ''"
                                    class="w-4 h-4 transition-transform duration-200"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </td>
                        </tr>

                        <tr x-show="expanded === index" x-cloak>
                            <td colspan="4" class="bg-gray-50 dark:bg-gray-900/60 px-6 py-5 border-t border-gray-100 dark:border-gray-700/50">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div x-show="item.possible_causes">
                                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Possible Causes</h4>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed" x-text="item.possible_causes"></p>
                                    </div>
                                    <div x-show="item.possible_remedies">
                                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Possible Remedies</h4>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed" x-text="item.possible_remedies"></p>
                                    </div>
                                    <div x-show="!item.possible_causes && !item.possible_remedies" class="md:col-span-2">
                                        <p class="text-sm text-gray-400 italic">No additional detail available for this code.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </template>
            </table>
        </div>
    </div>
</x-layouts.client>
