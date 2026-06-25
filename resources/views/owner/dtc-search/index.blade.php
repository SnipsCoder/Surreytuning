<x-layouts.owner>
    <x-page-header title="DTC Search" subtitle="Search diagnostic trouble codes" />

    <div
        x-data="{
            query: '',
            results: [],
            total: 0,
            loading: false,
            timer: null,
            search() {
                clearTimeout(this.timer)
                this.timer = setTimeout(() => {
                    if (this.query.trim() === '') {
                        this.results = []
                        this.total = 0
                        return
                    }
                    this.loading = true
                    fetch('{{ route('owner.dtc-search.results') }}?q=' + encodeURIComponent(this.query))
                        .then(response => response.json())
                        .then(data => {
                            this.results = data.data
                            this.total = data.total
                            this.loading = false
                        })
                }, 500)
            },
        }"
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code or Description</label>
            <input type="text" x-model="query" x-on:input="search" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm" placeholder="e.g. P0420">
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2" x-show="query.trim() !== ''">
            <span x-show="loading">Searching...</span>
            <span x-show="!loading">Showing {{ '' }}<span x-text="results.length"></span> of <span x-text="total"></span> results</span>
        </p>

        <x-data-table :headers="['Code', 'Description']">
            <template x-for="item in results" :key="item.code">
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100" x-text="item.code"></td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400" x-text="item.description"></td>
                </tr>
            </template>
            <tr x-show="query.trim() !== '' && !loading && results.length === 0">
                <td colspan="2" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-gray-400">No results found.</td>
            </tr>
        </x-data-table>
    </div>
</x-layouts.owner>
