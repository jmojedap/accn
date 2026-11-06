<script>
(function () {
    const initial = <?= json_encode(['search' => $search, 'url' => $productsUrl, 'apiUrl' => $productsApiUrl], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) ?>;
    let deleteModal = null;
    createApp({
        data() {
            return {
                filters: initial.search.filters,
                results: initial.search.results,
                settings: initial.search.settings,
                qtyResults: initial.search.qtyResults,
                loading: false, deleting: false, error: '', deleteError: '', notice: '', noticeWarning: false,
                selected: [], searchVersion: 0,
                displayFilters: Object.entries(initial.search.filters).some(([key, value]) => key !== 'q' && value !== ''),
                sort: initial.search.settings.orderField + ':' + initial.search.settings.orderType,
                sortOptions: ['id:DESC', 'id:ASC', 'name:ASC', 'name:DESC', 'base_price:ASC', 'base_price:DESC'],
            };
        },
        mounted() {
            deleteModal = new bootstrap.Modal(document.getElementById('productsDeleteModal'), {backdrop: 'static', keyboard: false});
        },
        beforeUnmount() {
            if (deleteModal) deleteModal.dispose();
        },
        computed: {
            allSelected() {
                return this.results.length > 0 && this.results.every(row => this.selected.includes(row.idcode));
            },
            paginationText() {
                if (!this.qtyResults) return '0 productos';
                const start = (this.settings.numPage - 1) * this.settings.perPage + 1;
                const end = Math.min(this.settings.numPage * this.settings.perPage, this.qtyResults);
                return start + '–' + end + ' de ' + this.qtyResults;
            },
        },
        methods: {
            async search(resetPage = false) {
                if (this.deleting) return;
                if (resetPage) this.settings.numPage = 1;
                const version = ++this.searchVersion;
                this.loading = true;
                this.error = '';
                try {
                    const response = await axios.get(initial.apiUrl + 'search', {params: {
                        ...this.filters,
                        numPage: this.settings.numPage,
                        perPage: this.settings.perPage,
                        orderField: this.settings.orderField,
                        orderType: this.settings.orderType,
                    }});
                    if (version !== this.searchVersion) return;
                    const data = response.data;
                    this.results = data.results;
                    this.filters = data.filters;
                    this.settings = data.settings;
                    this.qtyResults = data.qtyResults;
                    this.selected = [];
                    if (this.settings.numPage > this.settings.maxPage) {
                        this.settings.numPage = this.settings.maxPage;
                        return this.search(false);
                    }
                    history.replaceState(null, '', initial.url + 'explore' + (data.getString ? '?' + data.getString : ''));
                } catch (error) {
                    if (version === this.searchVersion) this.error = this.requestError(error, 'No fue posible consultar los productos. Intenta de nuevo.');
                } finally {
                    if (version === this.searchVersion) this.loading = false;
                }
            },
            applySort() {
                [this.settings.orderField, this.settings.orderType] = this.sort.split(':');
                this.search(true);
            },
            resetFilters() {
                Object.keys(this.filters).forEach(key => { this.filters[key] = ''; });
                this.notice = '';
                this.search(true);
            },
            sumPage(change) {
                const page = Math.max(1, Math.min(this.settings.maxPage, Number(this.settings.numPage) + change));
                if (page === this.settings.numPage) return;
                this.settings.numPage = page;
                this.search(false);
            },
            selectAll(checked) {
                this.selected = checked ? this.results.map(row => row.idcode) : [];
            },
            confirmDelete() {
                if (!this.selected.length || this.loading || this.deleting) return;
                this.deleteError = '';
                deleteModal.show();
            },
            async deleteSelected() {
                if (!this.selected.length || this.deleting) return;
                this.deleting = true;
                this.deleteError = '';
                try {
                    const response = await axios.delete(initial.apiUrl + 'delete_selected', {data: {ids: [...this.selected]}});
                    const summary = response.data.summary;
                    this.notice = summary.deleted + ' producto(s) eliminado(s).';
                    this.noticeWarning = summary.not_deleted > 0;
                    if (summary.not_deleted) this.notice += ' ' + summary.not_deleted + ' no se pudieron eliminar.';
                    deleteModal.hide();
                    this.selected = [];
                    this.deleting = false;
                    await this.search(false);
                } catch (error) {
                    this.deleteError = this.requestError(error, 'No fue posible eliminar la selección. Intenta de nuevo.');
                } finally {
                    this.deleting = false;
                }
            },
            requestError(error, fallback) {
                if (error.response && error.response.status === 403) return 'Tu sesión venció o no tienes permiso. Recarga la página para continuar.';
                return fallback;
            },
            infoUrl(id) { return initial.url + 'info/' + encodeURIComponent(id); },
            editUrl(id) { return initial.url + 'edit/' + encodeURIComponent(id); },
            kindName(kind) { return ({digital: 'Virtual', service: 'Servicio', access: 'Acceso', physical: 'Físico'})[kind] || kind; },
            statusName(status) { return ({0: 'Borrador', 1: 'Activo', 2: 'Archivado'})[status] || 'Sin estado'; },
            statusClass(status) { return ({0: 'bg-light text-dark border', 1: 'bg-success', 2: 'bg-secondary'})[status] || 'bg-light text-dark'; },
            money(value, currency) {
                try {
                    return new Intl.NumberFormat('es-CO', {style: 'currency', currency, maximumFractionDigits: 2}).format(Number(value));
                } catch (error) {
                    return value;
                }
            },
        },
    }).mount('#productsExplore');
})();
</script>
